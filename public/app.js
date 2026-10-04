(() => {
  'use strict';

  const stations = [
    { code: 'kexp', shortName: 'KEXP', frequency: '90.3', name: 'KEXP', city: 'Seattle, WA', website: 'https://kexp.org', listenUrl: 'https://www.kexp.org/playlist/' },
    { code: 'kndd', shortName: 'The End', frequency: '107.7', name: '107.7 The End', city: 'Seattle, WA', website: 'https://www.audacy.com/1077theend', listenUrl: 'https://www.audacy.com/stations/1077theend' },
    { code: 'kbcs', shortName: 'KBCS', frequency: '91.3', name: '91.3 KBCS', city: 'Bellevue, WA', website: 'https://www.kbcs.fm', listenUrl: 'https://www.kbcs.fm/listen/live/' },
    { code: 'kbfg', shortName: 'KBFG', frequency: '107.3', name: 'KBFG 107.3', city: 'Seattle, WA', website: 'https://kbfgseattle.org', listenUrl: 'https://kbfgseattle.org/how-to-listen/' },
    { code: 'kmgp', shortName: 'SPACE', frequency: '101.1', name: 'SPACE 101.1 FM', city: 'Seattle, WA', website: 'https://www.space101fm.org', listenUrl: 'https://www.space101fm.org/listen-live/' },
    { code: 'kxsu', shortName: 'KXSU', frequency: '102.1', name: 'KXSU 102.1 FM', city: 'Seattle, WA', website: 'https://www.kxsu.org', listenUrl: 'https://www.kxsu.org/#head-column-2' },
    { code: 'kzok', shortName: 'KZOK', frequency: '102.5', name: '102.5 KZOK', city: 'Seattle, WA', website: 'https://kzok.iheart.com', listenUrl: 'https://www.iheart.com/live/1025-kzok-7787/' },
    { code: 'kutx', shortName: 'KUTX', frequency: '98.9', name: 'KUTX 98.9', city: 'Austin, TX', website: 'https://kutx.org', listenUrl: 'https://kutx.org/streams/' },
    { code: 'ckkq', shortName: 'The Q!', frequency: '100.3', name: '100.3 The Q!', city: 'Victoria, BC', website: 'https://www.theq.fm', listenUrl: 'https://www.theq.fm/listen/' }
  ].sort((a, b) => Number(a.frequency) - Number(b.frequency));
  const pollInterval = 20000;
  const recentInterval = 120000;
  const maxHistory = 1000;
  const pageSize = 8;
  const $ = (selector) => document.querySelector(selector);
  const state = {
    code: '',
    current: null,
    history: [],
    favorites: [],
    updated: null,
    searched: '',
    savedOnly: false,
    visibleCount: pageSize,
    fetching: false,
    storageError: false,
    requestError: '',
    offline: !navigator.onLine
  };

  function readJSON(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      return raw === null ? fallback : JSON.parse(raw);
    } catch (_) {
      return fallback;
    }
  }

  function writeJSON(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      state.storageError = false;
      return true;
    } catch (_) {
      state.storageError = true;
      showNotice();
      return false;
    }
  }

  function stationUrl(code) {
    const host = location.hostname.toLowerCase();
    if (host === 'currently.rocks' || host.endsWith('.currently.rocks')) {
      return 'https://' + code + '.currently.rocks/';
    }
    return '/' + code;
  }

  function resolveStation() {
    const hostPart = location.hostname.toLowerCase().split('.')[0];
    const pathPart = location.pathname.split('/').filter(Boolean)[0];
    const queryPart = new URLSearchParams(location.search).get('station');
    const hashPart = location.hash.replace(/^#\/?/, '').split('/')[0];
    for (const part of [hostPart, pathPart, queryPart, hashPart]) {
      if (stations.some((station) => station.code === String(part).toLowerCase())) {
        return String(part).toLowerCase();
      }
    }
    return '';
  }

  function stationFor(code) {
    return stations.find((station) => station.code === code);
  }

  function safeExternalUrl(value) {
    if (typeof value !== 'string') return '';
    try {
      const url = new URL(value);
      return url.protocol === 'https:' ? url.href : '';
    } catch (_) {
      return '';
    }
  }

  function externalLink(label, url) {
    const link = make('a', '', label);
    link.href = url;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    return link;
  }

  function setStationLinks(metadata) {
    const fallback = stationFor(state.code);
    $('#listen-link').href = safeExternalUrl(metadata && metadata.listenUrl) || fallback.listenUrl;
    $('#website-link').href = safeExternalUrl(metadata && metadata.website) || fallback.website;
  }

  function trackKey(item) {
    return item.song + ':::' + item.artist;
  }

  function hasTrack(item) {
    return item && typeof item.song === 'string' && item.song.trim() &&
      typeof item.artist === 'string' && item.artist.trim();
  }

  function spotifyUrl(item) {
    return 'https://open.spotify.com/search/' + encodeURIComponent(item.artist + ' ' + item.song);
  }

  function normalizeTrack(item) {
    if (!hasTrack(item)) return null;
    const timestamp = item.timestamp && !isNaN(Date.parse(item.timestamp)) ? item.timestamp : null;
    return {
      song: item.song.trim(),
      artist: item.artist.trim(),
      timestamp: timestamp,
      timestampSource: timestamp ? (['station', 'observed', 'estimated'].includes(item.timestampSource) ? item.timestampSource : 'unknown') : 'unknown',
      spotifyUrl: spotifyUrl(item)
    };
  }

  function sortHistory(items) {
    return items.sort((a, b) => (b.timestamp ? Date.parse(b.timestamp) : 0) -
      (a.timestamp ? Date.parse(a.timestamp) : 0));
  }

  function saveHistory() {
    const starred = state.history.filter((item) => state.favorites.includes(trackKey(item)));
    const regular = state.history.filter((item) => !state.favorites.includes(trackKey(item))).slice(0, maxHistory);
    state.history = sortHistory(starred.concat(regular));
    writeJSON('history_' + state.code, state.history);
    renderHistory();
  }

  function observeTrack(data) {
    const item = normalizeTrack({ song: data.song, artist: data.artist, timestamp: new Date().toISOString(), timestampSource: 'observed' });
    if (!item) return;
    const sameCurrent = hasTrack(state.current) && trackKey(state.current) === trackKey(item);
    const recentlyChecked = state.updated && Date.now() - state.updated.getTime() < 1800000;
    if (sameCurrent && recentlyChecked && state.history.some((old) => trackKey(old) === trackKey(item))) return;
    const recentMatch = state.history.find((old) => trackKey(old) === trackKey(item) &&
      old.timestamp && Math.abs(Date.parse(old.timestamp) - Date.parse(item.timestamp)) < 900000);
    if (recentMatch) return;
    state.history.unshift(item);
    saveHistory();
  }

  function mergeRecent(recent) {
    if (!Array.isArray(recent)) return;
    let changed = false;
    for (const candidate of recent) {
      const item = normalizeTrack(candidate);
      if (!item) continue;
      const found = state.history.find((old) => {
        if (trackKey(old) !== trackKey(item)) return false;
        if (!item.timestamp || !old.timestamp) return true;
        return Math.abs(Date.parse(old.timestamp) - Date.parse(item.timestamp)) < 900000;
      });
      if (found && item.timestamp && ['station', 'estimated'].includes(item.timestampSource) && !['station', 'estimated'].includes(found.timestampSource)) {
        found.timestamp = item.timestamp;
        found.timestampSource = item.timestampSource;
        changed = true;
      }
      if (!found) {
        state.history.push(item);
        changed = true;
      }
    }
    if (changed) saveHistory();
  }

  function make(tag, className, content) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (content !== undefined) element.textContent = content;
    return element;
  }

  function renderStations() {
    const grid = $('#station-grid');
    stations.forEach((station) => {
      const row = make('div', 'station-item');
      const link = make('a', 'station-item-main');
      link.href = stationUrl(station.code);
      const label = make('span', 'station-item-label');
      label.append(make('span', 'station-item-name', station.shortName));
      label.append(make('span', 'station-item-place', station.city));
      const frequency = make('span', 'station-item-frequency', station.frequency);
      frequency.append(make('small', '', 'FM'));
      link.append(frequency, label);
      const links = make('span', 'station-item-links');
      links.append(externalLink('Listen live', station.listenUrl), externalLink('Homepage', station.website));
      row.append(link, links);
      grid.append(row);
    });
  }

  function setConnection(label, kind) {
    const status = $('#connection-status');
    const live = label === 'Live';
    status.className = 'connection-status' + (live ? ' live' : kind ? ' ' + kind : '');
    $('#connection-label').textContent = live ? 'Auto-updating' : label;
    status.hidden = label === 'Ready';
    status.title = live ? 'Song information updates automatically every 20 seconds.' : label;
  }

  function showNotice() {
    const notices = [];
    if (state.offline) notices.push(state.current
      ? 'You’re offline. Showing your last saved song and earlier songs.'
      : 'You’re offline. Songs will appear when you’re back online.');
    else if (state.requestError && state.current) notices.push(state.requestError);
    if (state.storageError) notices.push('Browser storage is unavailable or full. New tracks may not be saved here.');
    const notice = $('#notice');
    notice.hidden = notices.length === 0;
    notice.textContent = notices.join(' ');
  }

  function formatTime(timestamp) {
    if (!timestamp) return 'Time unknown';
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return 'Time unknown';
    const today = new Date();
    const sameDay = date.toDateString() === today.toDateString();
    return sameDay
      ? date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
      : date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' · ' +
        date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  }

  function historyTime(item) {
    const time = formatTime(item.timestamp);
    if (!item.timestamp) return time;
    if (item.timestampSource === 'observed') return 'Seen ' + time;
    if (item.timestampSource === 'estimated') return '≈ ' + time;
    if (item.timestampSource === 'station') return time;
    return 'Saved ' + time;
  }

  function updateRelativeTime() {
    if (!state.updated) {
      $('#last-updated').textContent = 'Waiting for station…';
      return;
    }
    const minutes = Math.max(0, Math.floor((Date.now() - state.updated.getTime()) / 60000));
    $('#last-updated').textContent = minutes === 0 ? 'Checked just now' :
      'Checked ' + minutes + ' min ago';
  }

  let renderedTrackKey = null;

  function renderCurrent() {
    const current = state.current;
    const playable = hasTrack(current);
    const nextTrackKey = playable ? trackKey(current) : null;
    const trackChanged = nextTrackKey && renderedTrackKey && nextTrackKey !== renderedTrackKey;
    if (nextTrackKey) renderedTrackKey = nextTrackKey;
    const waiting = !current;
    const unavailable = waiting && (state.requestError || state.offline);
    $('#current-title').textContent = unavailable ? 'Song unavailable' :
      waiting ? 'Checking the station…' : playable ? current.song : 'No track information right now';
    $('#current-artist').textContent = unavailable ? 'Try Refresh in a moment.' :
      waiting ? '' :
      playable ? current.artist : 'The station may be between songs.';
    if (trackChanged && !document.hidden && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      for (const selector of ['#current-title', '#current-artist']) {
        const element = $(selector);
        if (typeof element.animate !== 'function') continue;
        element.getAnimations().forEach((animation) => animation.cancel());
        element.animate([
          { opacity: 0.35, transform: 'translateY(4px)' },
          { opacity: 1, transform: 'translateY(0)' }
        ], { duration: 450, easing: 'ease-out' });
      }
    }
    $('#now-label').textContent = playable
      ? (state.offline || state.requestError ? 'Last received' : 'Playing now')
      : 'On the air';
    updateRelativeTime();
    const spotify = $('#spotify-link');
    spotify.hidden = !playable;
    if (playable) {
      spotify.href = spotifyUrl(current);
      const label = 'Search Spotify for ' + current.song + ' by ' + current.artist;
      spotify.setAttribute('aria-label', label);
      spotify.title = label;
    }
    const favorite = $('#favorite-button');
    favorite.hidden = !playable;
    if (playable) {
      const saved = state.favorites.includes(trackKey(current));
      favorite.setAttribute('aria-pressed', String(saved));
      favorite.querySelector('.favorite-icon').textContent = saved ? '★' : '☆';
      favorite.lastElementChild.textContent = saved ? 'Favorited' : 'Favorite';
    }
    if (current && current.station) {
      const station = current.station;
      $('#station-heading').textContent = station.name || state.code.toUpperCase();
      const location = typeof station.location === 'string' ? station.location.trim() : '';
      $('#station-subheading').textContent = location && location.toLowerCase() !== 'unknown location'
        ? location : stationFor(state.code).city;
      setStationLinks(station);
      document.title = (station.name || state.code.toUpperCase()) + ' — currently.rocks';
    }
  }

  function toggleFavorite(item) {
    const key = trackKey(item);
    state.favorites = state.favorites.includes(key)
      ? state.favorites.filter((saved) => saved !== key)
      : state.favorites.concat(key);
    writeJSON('starred_songs', state.favorites);
    renderCurrent();
    renderHistory();
  }

  function renderHistory() {
    if (!state.code) return;
    const earlier = [];
    const lastSeen = new Map();
    for (const item of state.history) {
      const key = trackKey(item);
      const time = item.timestamp ? Date.parse(item.timestamp) : null;
      const previous = lastSeen.get(key);
      if (time && previous && previous - time < 900000) continue;
      earlier.push(item);
      if (time) lastSeen.set(key, time);
    }
    if (hasTrack(state.current)) {
      const currentIndex = earlier.findIndex((item) => trackKey(item) === trackKey(state.current));
      // The current song belongs in Favorites, even though it is not an earlier song.
      if (!state.savedOnly && currentIndex !== -1) earlier.splice(currentIndex, 1);
      if (state.savedOnly && currentIndex === -1) {
        earlier.unshift(normalizeTrack(state.current));
      }
    }
    $('#export-button').hidden = state.history.length === 0;
    const query = state.searched.toLocaleLowerCase().trim();
    const favoriteKeys = new Set();
    const filtered = earlier.filter((item) => {
      if (state.savedOnly) {
        const key = trackKey(item);
        if (!state.favorites.includes(key) || favoriteKeys.has(key)) return false;
        favoriteKeys.add(key);
      }
      return !query || (item.song + ' ' + item.artist).toLocaleLowerCase().includes(query);
    });
    $('#history-label').textContent = state.savedOnly ? 'Favorites on ' + stationFor(state.code).shortName : 'Earlier songs';
    $('#history-count').textContent = String(filtered.length);
    $('#search-state').hidden = !query;
    $('#search-query').textContent = state.searched;
    const shown = filtered.slice(0, state.visibleCount);
    const list = $('#history-list');
    const focused = list.contains(document.activeElement) ? document.activeElement : null;
    const existingRows = new Map([...list.children].map(row => [row.dataset.key, row]));
    const occurrences = new Map();
    list.hidden = shown.length === 0;
    const fragment = document.createDocumentFragment();
    shown.forEach((item) => {
      const key = trackKey(item);
      const occurrence = occurrences.get(key) || 0;
      occurrences.set(key, occurrence + 1);
      const rowKey = JSON.stringify([key, occurrence]);
      const row = make('div', 'history-row');
      row.dataset.key = rowKey;
      const track = make('div', 'track-cell');
      track.append(make('div', 'track-name', item.song));
      track.append(make('p', 'track-artist', item.artist));
      row.append(track);
      const isCurrent = state.savedOnly && hasTrack(state.current) && trackKey(item) === trackKey(state.current);
      const metadata = make('div', 'track-meta');
      metadata.append(make('span', 'time-cell', isCurrent
        ? (state.offline || state.requestError ? 'Last received' : 'Playing now')
        : historyTime(item)));
      const search = externalLink('', spotifyUrl(item));
      const icon = make('img', 'spotify-icon');
      icon.src = '/spotify.svg';
      icon.alt = '';
      icon.setAttribute('aria-hidden', 'true');
      search.append(icon);
      search.className = 'history-spotify';
      search.title = 'Search Spotify for ' + item.song + ' by ' + item.artist;
      search.setAttribute('aria-label', search.title);
      metadata.append(search);
      row.append(metadata);
      const actions = make('div', 'row-actions');
      const saved = state.favorites.includes(trackKey(item));
      const favorite = make('button', 'icon-button', saved ? '★' : '☆');
      favorite.type = 'button';
      favorite.title = saved ? 'Remove from favorites' : 'Add to favorites';
      favorite.setAttribute('aria-label', favorite.title + ': ' + item.song + ' by ' + item.artist);
      favorite.setAttribute('aria-pressed', String(saved));
      favorite.addEventListener('click', () => toggleFavorite(item));
      actions.append(favorite);
      row.append(actions);
      const existing = existingRows.get(rowKey);
      if (existing) {
        for (const selector of ['.time-cell', '.icon-button']) {
          const oldNode = existing.querySelector(selector);
          const newNode = row.querySelector(selector);
          if (oldNode.textContent !== newNode.textContent) oldNode.textContent = newNode.textContent;
          for (const attribute of ['title', 'aria-label', 'aria-pressed']) {
            const value = newNode.getAttribute(attribute);
            if (value !== null && oldNode.getAttribute(attribute) !== value) oldNode.setAttribute(attribute, value);
          }
        }
        row._retained = existing;
      }
      fragment.append(row);
    });
    const desired = [...fragment.children].map(row => row._retained || row);
    const keep = new Set(desired);
    for (const row of [...list.children]) if (!keep.has(row)) row.remove();
    desired.forEach((row, index) => {
      if (list.children[index] !== row) list.insertBefore(row, list.children[index] || null);
    });
    if (focused && document.activeElement !== focused) {
      (focused.isConnected ? focused : $('#filter-button')).focus({ preventScroll: true });
    }
    $('#history-empty').hidden = filtered.length > 0;
    $('#history-empty h3').textContent = query ? 'No songs found' :
      state.savedOnly ? 'No favorites yet' : 'No songs yet';
    $('#history-empty p').textContent = query ? 'Try another song or artist.' :
      state.savedOnly ? 'Star a song to add it to your favorites.' : 'Songs appear here as the station plays them.';
    $('#history-showing').textContent = shown.length < filtered.length
      ? 'Showing ' + shown.length + ' of ' + filtered.length : '';
    $('#load-more').hidden = shown.length >= filtered.length;
  }

  async function fetchJSON(url) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 12000);
    try {
      const response = await fetch(url, { cache: 'no-store', signal: controller.signal });
      let data;
      try { data = await response.json(); } catch (_) { data = null; }
      if (!response.ok || !data) throw new Error((data && data.error) || 'The station could not be reached.');
      return data;
    } finally {
      clearTimeout(timeout);
    }
  }

  async function refresh() {
    if (!state.code || state.fetching || !navigator.onLine) return;
    state.fetching = true;
    $('#refresh-button').disabled = true;
    if (!state.updated) setConnection('Connecting');
    try {
      const data = await fetchJSON('/api.php?call_sign=' + encodeURIComponent(state.code));
      observeTrack(data);
      state.current = data;
      state.updated = new Date();
      state.requestError = '';
      writeJSON('nowplaying_' + state.code, { data: data, timestamp: state.updated.toISOString() });
      renderCurrent();
      renderHistory();
      setConnection('Live');
      showNotice();
    } catch (_) {
      state.requestError = 'Could not check the station right now. Your earlier songs are still here.';
      setConnection(state.current ? 'Showing saved data' : 'Unavailable', 'error');
      renderCurrent();
      showNotice();
    } finally {
      state.fetching = false;
      $('#refresh-button').disabled = false;
    }
  }

  async function fetchRecent() {
    if (!state.code || !navigator.onLine) return;
    try {
      const data = await fetchJSON('/api.php?call_sign=' + encodeURIComponent(state.code) + '&recent=1');
      mergeRecent(data.recent);
    } catch (_) {
      // Recent tracks are optional; the current song and local history still work.
    }
  }

  function exportCSV() {
    const escape = (value) => '"' + String(value == null ? '' : value).replace(/"/g, '""') + '"';
    const rows = [['Station', 'Song', 'Artist', 'Time', 'Time source', 'Favorite', 'Spotify']];
    state.history.forEach((item) => rows.push([
      state.code.toUpperCase(), item.song, item.artist, item.timestamp || '', item.timestampSource || 'unknown',
      state.favorites.includes(trackKey(item)) ? 'Yes' : 'No', spotifyUrl(item)
    ]));
    const csv = '\uFEFF' + rows.map((row) => row.map(escape).join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    const link = make('a');
    link.href = url;
    link.download = state.code + '-history.csv';
    document.body.append(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  function init() {
    state.code = resolveStation();
    if (location.hostname.toLowerCase().endsWith('.currently.rocks')) {
      $('.brand').href = 'https://currently.rocks/';
      $('#change-station').href = 'https://currently.rocks/';
    }
    renderStations();
    if (!state.code) {
      $('#welcome-view').hidden = false;
      setConnection('Ready');
      return;
    }
    $('#station-view').hidden = false;
    $('#station-picker').hidden = false;
    const station = stationFor(state.code);
    $('#selected-station').textContent = station.frequency + ' FM · ' + station.shortName;
    $('#station-heading').textContent = station.name;
    $('#station-subheading').textContent = station.city;
    setStationLinks(station);
    document.title = station.name + ' — currently.rocks';
    const storedHistory = readJSON('history_' + state.code, []);
    state.history = Array.isArray(storedHistory) ? sortHistory(storedHistory.map(normalizeTrack).filter(Boolean)) : [];
    const storedFavorites = readJSON('starred_songs', []);
    state.favorites = Array.isArray(storedFavorites) ? storedFavorites.filter((value) => typeof value === 'string') : [];
    state.savedOnly = readJSON('show_starred_preference', false) === true;
    $('#filter-button').textContent = state.savedOnly ? 'Show all songs' : '☆ Favorites';
    const cached = readJSON('nowplaying_' + state.code, null);
    if (cached && cached.data) {
      state.current = cached.data;
      if (cached.timestamp && !isNaN(Date.parse(cached.timestamp))) state.updated = new Date(cached.timestamp);
    }
    renderCurrent();
    renderHistory();
    showNotice();
    if (state.offline) setConnection('Offline', 'offline');
    else { refresh(); fetchRecent(); }

    $('#refresh-button').addEventListener('click', () => { refresh(); fetchRecent(); });
    $('#favorite-button').addEventListener('click', () => { if (hasTrack(state.current)) toggleFavorite(state.current); });
    $('#history-search').addEventListener('input', (event) => {
      state.searched = event.target.value;
      state.visibleCount = pageSize;
      renderHistory();
    });
    $('#filter-button').addEventListener('click', () => {
      state.savedOnly = !state.savedOnly;
      state.visibleCount = pageSize;
        $('#filter-button').textContent = state.savedOnly ? 'Show all songs' : '☆ Favorites';
      writeJSON('show_starred_preference', state.savedOnly);
      renderHistory();
    });
    $('#clear-search').addEventListener('click', () => {
      state.searched = '';
      $('#history-search').value = '';
      renderHistory();
      $('#history-search').closest('details').open = true;
      $('#history-search').focus();
    });
    $('#load-more').addEventListener('click', () => { state.visibleCount += pageSize; renderHistory(); });
    $('#export-button').addEventListener('click', exportCSV);
    window.addEventListener('online', () => {
      state.offline = false;
      setConnection('Connecting');
      showNotice();
      refresh();
      fetchRecent();
    });
    window.addEventListener('offline', () => {
      state.offline = true;
      setConnection('Offline', 'offline');
      renderCurrent();
      showNotice();
    });
    window.addEventListener('storage', (event) => {
      if (event.key === 'history_' + state.code) {
        const items = readJSON(event.key, []);
        state.history = Array.isArray(items) ? sortHistory(items.map(normalizeTrack).filter(Boolean)) : [];
        renderHistory();
      }
      if (event.key === 'starred_songs') {
        const items = readJSON(event.key, []);
        state.favorites = Array.isArray(items) ? items : [];
        renderCurrent();
        renderHistory();
      }
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    setInterval(() => { if (!document.hidden) refresh(); }, pollInterval);
    setInterval(() => { if (!document.hidden) fetchRecent(); }, recentInterval);
    setInterval(updateRelativeTime, 30000);
  }

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).catch(() => {});
    });
  }
  init();
})();
