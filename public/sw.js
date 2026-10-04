const CACHE = 'lastplayed-static-v21';
const STATIC = ['/index.html', '/styles.css?v=21', '/app.js?v=21', '/icon.svg?v=15', '/spotify.svg'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(STATIC.map((url) => new Request(url, { cache: 'reload' })))).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((names) => Promise.all(names.filter((name) => name !== CACHE).map((name) => caches.delete(name))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin || url.pathname === '/api.php') return;
  if (event.request.mode === 'navigate') {
    event.respondWith(fetch(event.request, { cache: 'no-cache' }).catch(() => caches.match('/index.html')));
    return;
  }
  if (url.pathname === '/styles.css' || url.pathname === '/app.js' || url.pathname === '/spotify.svg') {
    event.respondWith(caches.match(event.request).then((cached) => cached || fetch(event.request)));
  }
});
