// Retire the previously deployed offline worker. Keep this URL for returning visitors.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const names = [
                'pages',
                'osm-tiles',
                'google-fonts-stylesheets',
                'google-fonts-webfonts',
            ];
            const precache = 'workbox-precache-v2-' + self.registration.scope;
            const keys = await caches.keys();
            await Promise.all(
                keys
                    .filter((key) => names.includes(key) || key === precache)
                    .map((key) => caches.delete(key)),
            );
            await self.clients.claim();
            await self.registration.unregister();
        })(),
    );
});
