const CACHE_NAME = 'locknode-crm-v1';
const ASSETS = [
    '/favicon.ico'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(ASSETS);
        })
    );
});

self.addEventListener('fetch', event => {
    // Bypasear peticiones de navegación para evitar bugs de redirección en Safari
    if (event.request.mode === 'navigate') {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
