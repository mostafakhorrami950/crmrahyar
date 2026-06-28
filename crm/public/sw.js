/**
 * Service Worker - علاءالدین سفیر اسمان CRM
 * Version: 1.0.0
 */

const CACHE_NAME = 'safir-aseman-crm-v1';
const STATIC_CACHE = 'safir-static-v1';
const DYNAMIC_CACHE = 'safir-dynamic-v1';
const OFFLINE_URL = '/crm/offline.html';

// Static assets to cache immediately on install
const STATIC_ASSETS = [
    '/crm/',
    '/crm/offline.html',
    '/crm/manifest.json',
    '/crm/assets/css/app.css',
    '/crm/assets/js/app.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    '/crm/assets/icons/icon-192x192.svg',
    '/crm/assets/icons/icon-512x512.svg'
];

// URL patterns that should NOT be cached (API calls, POST requests, etc.)
const NEVER_CACHE = [
    '/api/',
    '/search/api',
    '/notifications/unread',
    '/pipelines/api',
    '/bulk/',
    '/toggle-done'
];

// Install event - cache static assets
self.addEventListener('install', function(event) {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(function(cache) {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(function(err) {
                console.log('[SW] Cache failed:', err);
            })
    );
    // Skip waiting to activate immediately
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', function(event) {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Claim all clients immediately
    self.clients.claim();
});

// Fetch event - Network-first for HTML, Cache-first for static
self.addEventListener('fetch', function(event) {
    var request = event.request;
    var url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip never-cache URLs
    for (var i = 0; i < NEVER_CACHE.length; i++) {
        if (url.pathname.indexOf(NEVER_CACHE[i]) !== -1) return;
    }

    // Skip cross-origin requests except CDN
    if (url.origin !== self.location.origin && 
        !url.hostname.includes('cdn.jsdelivr.net') &&
        !url.hostname.includes('cdnjs.cloudflare.com')) {
        return;
    }

    // Strategy: Cache-first for static assets, Network-first for HTML
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
    } else {
        event.respondWith(networkFirst(request));
    }
});

// Check if URL is a static asset
function isStaticAsset(pathname) {
    var extensions = ['.css', '.js', '.svg', '.png', '.jpg', '.jpeg', '.gif', '.woff', '.woff2', '.ttf', '.eot'];
    for (var i = 0; i < extensions.length; i++) {
        if (pathname.endsWith(extensions[i])) return true;
    }
    return false;
}

// Cache-first strategy (for static assets)
function cacheFirst(request) {
    return caches.match(request).then(function(cachedResponse) {
        if (cachedResponse) {
            // Update cache in background
            fetch(request).then(function(networkResponse) {
                caches.open(STATIC_CACHE).then(function(cache) {
                    cache.put(request, networkResponse);
                });
            }).catch(function() {});
            return cachedResponse;
        }
        return fetch(request).then(function(networkResponse) {
            return caches.open(STATIC_CACHE).then(function(cache) {
                cache.put(request, networkResponse.clone());
                return networkResponse;
            });
        });
    });
}

// Network-first strategy (for HTML pages)
function networkFirst(request) {
    return fetch(request)
        .then(function(networkResponse) {
            // Cache successful responses
            if (networkResponse && networkResponse.status === 200) {
                var responseClone = networkResponse.clone();
                caches.open(DYNAMIC_CACHE).then(function(cache) {
                    cache.put(request, responseClone);
                });
            }
            return networkResponse;
        })
        .catch(function() {
            // Network failed, try cache
            return caches.match(request).then(function(cachedResponse) {
                if (cachedResponse) {
                    return cachedResponse;
                }
                // If requesting a page, show offline page
                if (request.headers.get('accept').indexOf('text/html') !== -1) {
                    return caches.match(OFFLINE_URL);
                }
            });
        });
}

// Push notification event
self.addEventListener('push', function(event) {
    var data = { title: 'اعلان جدید', body: 'شما یک اعلان جدید دارید', icon: '/crm/assets/icons/icon-192x192.svg' };
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon || '/crm/assets/icons/icon-192x192.svg',
            badge: '/crm/assets/icons/icon-192x192.svg',
            vibrate: [200, 100, 200],
            data: data.url || '/crm/dashboard',
            actions: [
                { action: 'open', title: 'باز کردن' },
                { action: 'close', title: 'بستن' }
            ]
        })
    );
});

// Notification click event
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    var targetUrl = event.notification.data || '/crm/dashboard';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // If window already open, focus it
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url.includes('/crm/') && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            // Otherwise open new window
            return clients.openWindow(targetUrl);
        })
    );
});