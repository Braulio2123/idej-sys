const PORTAL_ALUMNO_CACHE = 'idej-portal-alumno-v1';
const CORE_ASSETS = [
    '/portal-alumno/login',
    '/portal-alumno/icons/icon.svg',
    '/portal-alumno/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PORTAL_ALUMNO_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .catch(() => null)
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== PORTAL_ALUMNO_CACHE)
                .map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (!url.pathname.startsWith('/portal-alumno')) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                const cloned = response.clone();
                caches.open(PORTAL_ALUMNO_CACHE).then((cache) => cache.put(request, cloned));
                return response;
            })
            .catch(() => caches.match(request).then((cached) => cached || caches.match('/portal-alumno/login')))
    );
});
