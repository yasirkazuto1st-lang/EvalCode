const CACHE_NAME = 'evalcode-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/images/logo.png',
    '/manifest.json'
];

// Install Event - Pre-cache essential files
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// Activate Event - Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event - Network First with Cache Fallback for static assets
self.addEventListener('fetch', (event) => {
    // Only handle standard GET requests
    if (event.request.method !== 'GET') return;

    // Do not cache API endpoints, CSRF status, or live updates
    const url = new URL(event.request.url);
    if (url.pathname.includes('/api/') || url.pathname.includes('/status') || url.pathname.includes('/token')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cache valid static responses
                if (response && response.status === 200 && response.type === 'basic') {
                    const fileExtension = url.pathname.split('.').pop();
                    const staticExtensions = ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
                    
                    if (staticExtensions.includes(fileExtension) || ASSETS_TO_CACHE.includes(url.pathname)) {
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                }
                return response;
            })
            .catch(() => {
                // If offline, serve from cache
                return caches.match(event.request);
            })
    );
});
