// Service Worker untuk PWA Siap Siaga PMR
const CACHE_NAME = 'siap-siaga-pmr-v1';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/assets/pmr-icon.webp',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  '/assets/icons/apple-touch-icon.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Hanya cache GET requests yang bukan request API/live-polling agar data status darurat selalu fresh
  if (event.request.method !== 'GET' || event.request.url.includes('/live-status') || event.request.url.includes('/quick-login')) {
    return;
  }

  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
