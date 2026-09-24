/**
 * Service worker: keeps the cashier page usable without a connection.
 *
 * - /kasir (the page itself): network first, the last good copy when offline.
 * - Static assets (own /assets, /img, fonts and the CDN scripts the layout loads):
 *   served from cache and refreshed in the background.
 * - Other pages while offline: a short "you are offline" page pointing to the cashier.
 * - API calls and form posts are never cached; kasir-offline.js queues sales instead.
 *
 * Bump CACHE_VERSION when changing this file's caching rules.
 */
const CACHE_VERSION = 'v1';
const PAGE_CACHE = 'kasir-page-' + CACHE_VERSION;
const ASSET_CACHE = 'kasir-assets-' + CACHE_VERSION;
const KASIR_URL = '/kasir';

const CDN_HOSTS = [
    'cdn.jsdelivr.net',
    'cdnjs.cloudflare.com',
    'fonts.googleapis.com',
    'fonts.gstatic.com',
];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        // Keep a copy of the cashier page right away (only if the user is logged in)
        try {
            const res = await fetch(KASIR_URL, { credentials: 'same-origin' });
            if (isGoodKasirPage(res)) {
                await (await caches.open(PAGE_CACHE)).put(KASIR_URL, res);
            }
        } catch (e) { /* offline during install: cached on the next visit */ }
        self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keep = [PAGE_CACHE, ASSET_CACHE];
        for (const key of await caches.keys()) {
            if (!keep.includes(key)) await caches.delete(key);
        }
        await self.clients.claim();
    })());
});

// A redirect (e.g. to /login) or an error must never replace the saved cashier page
function isGoodKasirPage(res) {
    return res && res.ok && !res.redirected && new URL(res.url).pathname === KASIR_URL;
}

function isStaticAsset(url) {
    if (url.origin === self.location.origin) {
        return url.pathname.startsWith('/assets/') || url.pathname.startsWith('/img/');
    }
    return CDN_HOSTS.includes(url.hostname) || url.hostname === 'buttons.github.io';
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        if (url.origin === self.location.origin && url.pathname === KASIR_URL) {
            event.respondWith(kasirPage(request));
        } else if (url.origin === self.location.origin) {
            event.respondWith(fetch(request).catch(offlinePage));
        }
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
    }
});

async function kasirPage(request) {
    const cache = await caches.open(PAGE_CACHE);
    try {
        const res = await fetchWithTimeout(request, 6000);
        if (isGoodKasirPage(res)) {
            cache.put(KASIR_URL, res.clone());
        }
        return res;
    } catch (e) {
        const cached = await cache.match(KASIR_URL);
        return cached || offlinePage();
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(ASSET_CACHE);
    const cached = await cache.match(request);
    const network = fetch(request)
        .then((res) => {
            // opaque (cross-origin, no-cors) responses have status 0 but are still usable
            if (res && (res.ok || res.type === 'opaque')) cache.put(request, res.clone());
            return res;
        })
        .catch(() => cached);
    return cached || network;
}

function fetchWithTimeout(request, ms) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('timeout')), ms);
        fetch(request).then((res) => { clearTimeout(timer); resolve(res); }, (err) => { clearTimeout(timer); reject(err); });
    });
}

function offlinePage() {
    const html = `<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title>Offline - YourStudio</title>
<style>body{font-family:system-ui,sans-serif;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;background:#f8f9fa;color:#344767}
.box{max-width:420px;padding:2rem;text-align:center;background:#fff;border-radius:1rem;box-shadow:0 .5rem 1.5rem rgba(0,0,0,.08)}
a{display:inline-block;margin-top:1rem;padding:.75rem 1.5rem;background:#8b6841;color:#fff;border-radius:.5rem;text-decoration:none;font-weight:600}</style></head>
<body><div class="box"><h2>Sedang offline</h2><p>Koneksi internet terputus. Halaman ini butuh koneksi,
tetapi Kasir tetap bisa dipakai untuk berjualan. Transaksi akan dikirim otomatis saat online kembali.</p>
<a href="${KASIR_URL}">Buka Kasir</a></div></body></html>`;
    return new Response(html, { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
}
