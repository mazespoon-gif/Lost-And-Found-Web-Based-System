const CACHE_VERSION = 'lookforit-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';
const PAGE_CACHE = CACHE_VERSION + '-pages';

const PRIVATE_PATHS = ['/dashboard', '/admin', '/manage_users', '/manage_lost_items', '/profile'];
const STATIC_EXT = /\.(css|js|png|jpe?g|webp|svg|ico|woff2?|ttf|gif)$/i;

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k.startsWith('lookforit-') && k !== STATIC_CACHE && k !== PAGE_CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

function isChallengeResponse(response) {
  if (!response) return true;
  const ct = (response.headers.get('content-type') || '').toLowerCase();
  if (ct.includes('text/html')) {
    return response.clone().text().then((body) =>
      body.indexOf('slowAES') !== -1 || body.indexOf('__test=') !== -1 || body.indexOf('toNumbers') !== -1
    );
  }
  return Promise.resolve(false);
}

function isPrivatePath(url) {
  return PRIVATE_PATHS.some((p) => url.pathname.indexOf(p) === 0);
}

function networkFirst(event) {
  const req = event.request;
  const url = new URL(req.url);

  if (isPrivatePath(url)) {
    return fetch(req);
  }

  event.respondWith(
    fetch(req)
      .then((response) => {
        if (!response || response.status !== 200) return response;
        const ct = (response.headers.get('content-type') || '').toLowerCase();
        if (!ct.includes('text/html')) return response;
        return isChallengeResponse(response).then((isChallenge) => {
          if (isChallenge) return caches.match(req).then((cached) => cached || response);
          const copy = response.clone();
          caches.open(PAGE_CACHE).then((cache) => cache.put(req, copy));
          return response;
        });
      })
      .catch(() => caches.match(req).then((cached) => cached || offlinePage()))
  );
}

function cacheFirst(event) {
  const req = event.request;
  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;
      return fetch(req).then((response) => {
        if (!response || response.status !== 200 || response.type === 'opaque') return response;
        const ct = (response.headers.get('content-type') || '').toLowerCase();
        if (ct.includes('text/html')) return response;
        const copy = response.clone();
        caches.open(STATIC_CACHE).then((cache) => cache.put(req, copy));
        return response;
      });
    })
  );
}

function offlinePage() {
  return new Response(
    '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline - LookForit</title><style>body{font-family:sans-serif;background:#f9fafb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}div{text-align:center;padding:2rem}h1{color:#1e3a8a}p{color:#6b7280}</style></head><body><div><h1>You are offline</h1><p>Check your connection and try again.</p></div></body></html>',
    { status: 503, headers: { 'Content-Type': 'text/html' } }
  );
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  if (req.mode === 'navigate') {
    networkFirst(event);
    return;
  }

  if (STATIC_EXT.test(url.pathname)) {
    cacheFirst(event);
    return;
  }

  networkFirst(event);
});
