const CACHE_NAME = 'race-finance-v3';
const STATIC_ASSETS = [
  '/manifest.json',
  '/favicon.ico',
  '/logo.png',
  '/img/icon-192.png',
  '/img/icon-512.png',
  '/css/style.css?v=2',
  '/css/receipt.css',
  '/js/app.js?v=2',
  '/js/invoice-calc.js?v=6',
  '/js/barcode.js',
  '/js/offline.js',
  '/js/vendor/qrcode.min.js',
  '/js/vendor/JsBarcode.all.min.js',
  '/vendor/bootstrap/css/bootstrap.min.css',
  '/vendor/bootstrap/js/bootstrap.bundle.min.js',
  '/vendor/bootstrap-icons/bootstrap-icons.min.css',
  '/vendor/flatpickr/flatpickr.min.css',
  '/vendor/flatpickr/flatpickr.min.js'
];

const EXCLUDED_PREFIXES = ['/auth', '/admin', '/backup', '/api', '/c/'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS).catch(() => {}))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) return;

  for (let i = 0; i < EXCLUDED_PREFIXES.length; i++) {
    if (url.pathname.startsWith(EXCLUDED_PREFIXES[i])) return;
  }

  // App CSS/JS/Manifest: Network-first with cache fallback so updates apply immediately while offline still works
  if (
    url.pathname.startsWith('/css/') ||
    url.pathname.startsWith('/js/') ||
    url.pathname === '/manifest.json'
  ) {
    event.respondWith(
      fetch(event.request)
        .then((resp) => {
          if (resp && resp.status === 200) {
            const copy = resp.clone();
            caches.open(CACHE_NAME).then((c) => c.put(event.request, copy));
          }
          return resp;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  // Vendor & static images: Cache-first
  if (
    url.pathname.startsWith('/vendor/') ||
    url.pathname.startsWith('/img/')
  ) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) return cached;
        return fetch(event.request).then((resp) => {
          if (resp && resp.status === 200) {
            const copy = resp.clone();
            caches.open(CACHE_NAME).then((c) => c.put(event.request, copy));
          }
          return resp;
        });
      })
    );
    return;
  }

  // HTML / authenticated views: Network-first with cached fallback for offline billing screens
  event.respondWith(
    fetch(event.request)
      .then((resp) => {
        if (resp && resp.status === 200 && (url.pathname === '/sales/create' || url.pathname === '/sales' || url.pathname === '/items' || url.pathname === '/parties')) {
          const copy = resp.clone();
          caches.open(CACHE_NAME).then((c) => c.put(event.request, copy));
        }
        return resp;
      })
      .catch(() => caches.match(event.request))
  );
});
