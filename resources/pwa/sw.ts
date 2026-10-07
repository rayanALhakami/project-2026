/// <reference lib="webworker" />

import { CacheableResponsePlugin } from 'workbox-cacheable-response';
import { clientsClaim } from 'workbox-core';
import { ExpirationPlugin } from 'workbox-expiration';
import {
    cleanupOutdatedCaches,
    precacheAndRoute,
    PrecacheFallbackPlugin,
} from 'workbox-precaching';
import type { PrecacheEntry } from 'workbox-precaching';
import { registerRoute, setCatchHandler } from 'workbox-routing';
import { CacheFirst, NetworkFirst, NetworkOnly } from 'workbox-strategies';

declare let self: ServiceWorkerGlobalScope & {
    __WB_MANIFEST: Array<PrecacheEntry | string>;
};

self.skipWaiting();
clientsClaim();
precacheAndRoute(self.__WB_MANIFEST);
cleanupOutdatedCaches();

registerRoute(
    ({ url }) =>
        url.pathname.startsWith('/assistant') ||
        url.pathname.startsWith('/translate'),
    new NetworkOnly(),
    'GET',
);

registerRoute(({ sameOrigin }) => sameOrigin, new NetworkOnly(), 'POST');

registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: 'pages',
        networkTimeoutSeconds: 4,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({
                maxEntries: 30,
                maxAgeSeconds: 7 * 24 * 60 * 60,
            }),
            new PrecacheFallbackPlugin({ fallbackURL: '/offline.html' }),
        ],
    }),
    'GET',
);

registerRoute(
    /^https:\/\/tile\.openstreetmap\.org\/.*/i,
    new CacheFirst({
        cacheName: 'osm-tiles',
        plugins: [
            new ExpirationPlugin({
                maxEntries: 600,
                maxAgeSeconds: 7 * 24 * 60 * 60,
            }),
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    }),
    'GET',
);

registerRoute(
    /^https:\/\/[a-z0-9-]+\.tile\.openstreetmap\.org\/.*/i,
    new CacheFirst({
        cacheName: 'osm-tiles',
        plugins: [
            new ExpirationPlugin({
                maxEntries: 600,
                maxAgeSeconds: 7 * 24 * 60 * 60,
            }),
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    }),
    'GET',
);

registerRoute(
    /^https:\/\/fonts\.googleapis\.com\/.*/i,
    new CacheFirst({
        cacheName: 'google-fonts-stylesheets',
        plugins: [
            new ExpirationPlugin({
                maxEntries: 10,
                maxAgeSeconds: 365 * 24 * 60 * 60,
            }),
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    }),
    'GET',
);

registerRoute(
    /^https:\/\/fonts\.gstatic\.com\/.*/i,
    new CacheFirst({
        cacheName: 'google-fonts-webfonts',
        plugins: [
            new ExpirationPlugin({
                maxEntries: 30,
                maxAgeSeconds: 365 * 24 * 60 * 60,
            }),
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    }),
    'GET',
);

setCatchHandler(async ({ event }) => {
    if (event.request.mode === 'navigate') {
        return (await caches.match('/offline.html')) ?? Response.error();
    }

    return Response.error();
});
