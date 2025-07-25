import { useState, useEffect, useCallback } from 'react';
import { useLocalStorage } from './useLocalStorage';

export const useNowPlaying = (callSign) => {
  const [currentSong, setCurrentSong] = useState(null);
  const [history, setHistory] = useLocalStorage(`history_${callSign}`, []);
  const [starredSongs, setStarredSongs] = useLocalStorage('starred_songs', []);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [lastUpdated, setLastUpdated] = useState(null);
  const [isOffline, setIsOffline] = useState(!navigator.onLine);

  const POLL_INTERVAL = 20000;
  const MAX_HISTORY_ITEMS = 100;

  const fetchNowPlaying = useCallback(async () => {
    try {
      setIsLoading(true);
      const response = await fetch(`/api.php?call_sign=${callSign.toLowerCase()}`);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || `HTTP error! status: ${response.status}`);
      }

      // Cache the successful response
      localStorage.setItem(`nowplaying_${callSign}`, JSON.stringify({
        data,
        timestamp: new Date().toISOString()
      }));

      // Only update if the song has changed
      if (!currentSong || currentSong.song !== data.song || currentSong.artist !== data.artist) {
        setCurrentSong(data);
        
        // Add to history
        if (data.song && data.artist) {
          setHistory(prev => {
            const newHistory = prev.filter(
              item => !(item.song === data.song && item.artist === data.artist)
            );
            return [{
              song: data.song,
              artist: data.artist,
              timestamp: new Date().toISOString(),
              spotifyUrl: data.spotifySearchUrl
            }, ...newHistory].slice(0, MAX_HISTORY_ITEMS);
          });
        }
      }

      setLastUpdated(new Date());
      setError(null);
    } catch (err) {
      console.error('Error fetching now playing:', err);
      if (isOffline) {
        loadFromCache();
      } else {
        setError(err.message);
      }
    } finally {
      setIsLoading(false);
    }
  }, [callSign, currentSong, isOffline, setHistory]);

  const loadFromCache = useCallback(() => {
    try {
      const cached = localStorage.getItem(`nowplaying_${callSign}`);
      if (cached) {
        const { data, timestamp } = JSON.parse(cached);
        setCurrentSong(data);
        setLastUpdated(new Date(timestamp));
      }
    } catch (err) {
      console.error('Error loading from cache:', err);
    }
  }, [callSign]);

  const toggleStar = useCallback((songKey) => {
    setStarredSongs(prev => {
      const index = prev.indexOf(songKey);
      if (index === -1) {
        return [...prev, songKey];
      } else {
        return prev.filter(key => key !== songKey);
      }
    });
  }, [setStarredSongs]);

  const isSongStarred = useCallback((songKey) => {
    return starredSongs.includes(songKey);
  }, [starredSongs]);

  // Handle online/offline status
  useEffect(() => {
    const handleOnline = () => {
      setIsOffline(false);
      fetchNowPlaying();
    };

    const handleOffline = () => {
      setIsOffline(true);
      loadFromCache();
    };

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, [fetchNowPlaying, loadFromCache]);

  // Polling effect
  useEffect(() => {
    fetchNowPlaying();
    const interval = setInterval(fetchNowPlaying, POLL_INTERVAL);
    return () => clearInterval(interval);
  }, [fetchNowPlaying]);

  return {
    currentSong,
    history,
    starredSongs,
    isLoading,
    error,
    lastUpdated,
    isOffline,
    toggleStar,
    isSongStarred
  };
};