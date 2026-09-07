const CACHE_NAME = 'scanner-cache-v1';
const URLS_TO_CACHE = [
    '/scanner',
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(URLS_TO_CACHE))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Only GET requests get served from cache. POST calls (verify, and
    // sync in a later phase) must always hit the real network or fail
    // naturally - never silently served from a cache.
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            return cached || fetch(event.request).catch(() => undefined);
        })
    );
});