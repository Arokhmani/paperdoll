// PaperDoll Shop - Service Worker v1.0 (WordPress)
const PD_CACHE = 'paperdoll-wp-v1';

self.addEventListener('install', e => {
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== PD_CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  if (e.request.method !== 'GET') return;
  // Hanya cache file statis tema paperdoll, bukan halaman WP dinamis
  const url = e.request.url;
  const isStatic = url.includes('/wp-content/themes/') || url.includes('fonts.googleapis') || url.includes('cdn.jsdelivr');
  if (!isStatic) return;

  e.respondWith(
    caches.open(PD_CACHE).then(cache =>
      cache.match(e.request).then(cached => {
        const network = fetch(e.request).then(res => {
          if (res.ok) cache.put(e.request, res.clone());
          return res;
        });
        return cached || network;
      })
    )
  );
});
