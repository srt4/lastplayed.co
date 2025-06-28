const CACHE_NAME = 'nowplaying-v1';
const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/thirdParty/spotify.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
});

// Fetch event - serve from cache, falling back to network
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Handle API requests differently
    if (url.pathname === '/api.php') {
        // For API requests, try network first, fall back to cache
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );
    } else {
        // For static assets, try cache first, fall back to network
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    if (response) {
                        return response;
                    }
                    return fetch(event.request).then(response => {
                        // Cache successful responses for static assets
                        if (response.ok && event.request.method === 'GET') {
                            const responseToCache = response.clone();
                            caches.open(CACHE_NAME).then(cache => {
                                cache.put(event.request, responseToCache);
                            });
                        }
                        return response;
                    });
                })
        );
    }
}); 