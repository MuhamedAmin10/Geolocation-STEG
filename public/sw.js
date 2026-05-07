const CACHE_NAME = 'steg-missions-v3';
const STATIC_ASSETS = ['/manifest.json', '/images/logo.png'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  if (requestUrl.origin !== self.location.origin) {
    return;
  }

  const isBuildAsset = requestUrl.pathname.startsWith('/build/');
  if (isBuildAsset) {
    // Versioned build assets should prefer network to avoid stale UI after deployments.
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response.ok) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
          }
          return response;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  const isNavigationRequest = event.request.mode === 'navigate';

  // Keep navigation network-first to avoid serving stale authenticated pages after logout.
  if (isNavigationRequest) {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('/login'))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) {
        return cached;
      }

      return fetch(event.request).then((response) => {
        const responseClone = response.clone();
        if (response.ok) {
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
        }
        return response;
      });
    })
  );
});
