/**
 * Service worker for the public website (registered by resources/site/js/pwa.js).
 *
 *  - Built CSS/JS (/build/*, hashed names): cache first, kept for good.
 *  - Images, icons and the self-hosted fonts: served from cache, refreshed in the background.
 *  - Pages: always from the network (they can contain a signed-in visitor or a CSRF token),
 *    with the offline page as fallback when there is no connection.
 *  - Admin, API, auth routes and non-GET requests are never touched.
 *
 * Bump VERSION to drop every old cache on the next visit.
 */
const VERSION = 'v2';
const STATIC_CACHE = `static-${VERSION}`;
const RUNTIME_CACHE = `runtime-${VERSION}`;
const OFFLINE_URL = '/offline';
const RUNTIME_LIMIT = 80;

const BYPASS = /^\/(admin|api|login|register|logout|lang|sanctum|up)(\/|$)/;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then((cache) => cache.addAll([OFFLINE_URL, '/favicon.svg', '/icons/icon-192.png']))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((key) => ![STATIC_CACHE, RUNTIME_CACHE].includes(key)).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

async function trimCache(name, limit) {
  const cache = await caches.open(name);
  const keys = await cache.keys();
  await Promise.all(keys.slice(0, Math.max(0, keys.length - limit)).map((key) => cache.delete(key)));
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  const response = await fetch(request);
  if (response.ok) (await caches.open(STATIC_CACHE)).put(request, response.clone());
  return response;
}

async function staleWhileRevalidate(event) {
  const cache = await caches.open(RUNTIME_CACHE);
  const cached = await cache.match(event.request);
  const network = fetch(event.request)
    .then((response) => {
      if (response.ok || response.type === 'opaque') {
        cache.put(event.request, response.clone()).then(() => trimCache(RUNTIME_CACHE, RUNTIME_LIMIT));
      }
      return response;
    })
    .catch(() => cached);
  event.waitUntil(network.then(() => undefined));
  return cached || network;
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin || BYPASS.test(url.pathname)) return;

  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }
  if (url.pathname.startsWith('/build/')) {
    event.respondWith(cacheFirst(request));
    return;
  }
  if (request.destination === 'image' || /^\/(storage|icons|fonts)\//.test(url.pathname)) {
    event.respondWith(staleWhileRevalidate(event));
  }
});
