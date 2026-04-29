const CACHE_NAME = 'piura-noticias-v2';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/css/style.css',
  '/img/logo.webp',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap',
  'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Opened cache');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Solo aplicamos caché pasivo para peticiones GET estáticas o de página principal
  if (event.request.method !== 'GET') return;
  
  // Estrategia Network First, fallback to cache (Ideal para sitios de noticias que cambian seguido)
  event.respondWith(
    fetch(event.request).then((networkResponse) => {
      return caches.open(CACHE_NAME).then((cache) => {
        // Obviamos cacheados a peticiones admin o ajax
        if (!event.request.url.includes('admin') && !event.request.url.includes('ajax')) {
          cache.put(event.request, networkResponse.clone());
        }
        return networkResponse;
      });
    }).catch(() => {
      return caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        // Fallback genérico visual si no hay internet ni caché
        return caches.match('/index.php');
      });
    })
  );
});
