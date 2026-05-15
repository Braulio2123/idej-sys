const PORTAL_ALUMNO_CACHE = 'idej-portal-alumno-v2';

const CORE_ASSETS = [
    '/portal-alumno',
    '/portal-alumno/login',
    '/portal-alumno-assets/manifest.json',
    '/portal-alumno-assets/icons/icon.svg',
    '/portal-alumno-assets/icons/icon-192.png',
    '/portal-alumno-assets/icons/icon-512.png'
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
            keys
                .filter((key) => key.startsWith('idej-portal-alumno-') && key !== PORTAL_ALUMNO_CACHE)
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
                if (!response || response.status !== 200 || response.type === 'opaque') {
                    return response;
                }

                const clonedResponse = response.clone();

                caches.open(PORTAL_ALUMNO_CACHE)
                    .then((cache) => cache.put(request, clonedResponse))
                    .catch(() => null);

                return response;
            })
            .catch(() => {
                return caches.match(request)
                    .then((cachedResponse) => cachedResponse || caches.match('/portal-alumno/login'));
            })
    );
});
