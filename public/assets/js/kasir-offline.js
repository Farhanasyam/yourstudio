/**
 * Offline support for the cashier.
 *
 * - Sales that could not reach the server are kept in a queue in this browser (per user)
 *   and sent again automatically when the connection is back. Every sale carries a
 *   client_uuid, so the server never records the same sale twice, however often it is retried.
 * - The item catalog (names, prices, barcodes, stock) is kept here too, so scanning works
 *   instantly and without a connection.
 *
 * Loaded on every page, so queued sales also sync while the cashier is on another page.
 */
(function () {
    'use strict';

    var userMeta = document.querySelector('meta[name="user-id"]');
    var userId = userMeta ? userMeta.content : null;
    if (!userId) return; // not logged in

    var QUEUE_KEY = 'kasir_queue_' + userId;
    var CATALOG_KEY = 'kasir_catalog_' + userId;
    var SYNC_INTERVAL_MS = 30000;
    var REQUEST_TIMEOUT_MS = 10000;

    var syncing = false;
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // ---------- storage (localStorage may be unavailable; never let that break the page) ----------
    function read(key, fallback) {
        try {
            var raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function write(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            return false;
        }
    }

    function emit() {
        document.dispatchEvent(new CustomEvent('kasir-offline:changed', { detail: status() }));
    }

    // ---------- queue ----------
    function getQueue() {
        return read(QUEUE_KEY, []);
    }

    function saveQueue(queue) {
        var ok = write(QUEUE_KEY, queue);
        emit();
        return ok;
    }

    function enqueue(sale) {
        var queue = getQueue();
        if (!queue.some(function (s) { return s.client_uuid === sale.client_uuid; })) {
            queue.push(sale);
        }
        return saveQueue(queue);
    }

    function status() {
        var queue = getQueue();
        return {
            online: navigator.onLine,
            syncing: syncing,
            pending: queue.filter(function (s) { return !s.failed; }).length,
            failed: queue.filter(function (s) { return s.failed; }),
            catalogAt: (read(CATALOG_KEY, null) || {}).generated_at || null,
        };
    }

    // ---------- helpers ----------
    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    // Receipt number for sales made offline; the server keeps it when syncing
    function offlineCode(date) {
        var d = date || new Date();
        var p = function (n) { return String(n).padStart(2, '0'); };
        return 'OFF-' + d.getFullYear() + p(d.getMonth() + 1) + p(d.getDate()) + p(d.getHours()) + p(d.getMinutes()) + p(d.getSeconds())
            + '-' + String(Math.floor(Math.random() * 10000)).padStart(4, '0');
    }

    function fetchWithTimeout(url, options, timeoutMs) {
        var controller = window.AbortController ? new AbortController() : null;
        var timer = controller ? setTimeout(function () { controller.abort(); }, timeoutMs || REQUEST_TIMEOUT_MS) : null;
        var opts = Object.assign({ credentials: 'same-origin' }, options || {});
        if (controller) opts.signal = controller.signal;
        return fetch(url, opts).finally(function () { if (timer) clearTimeout(timer); });
    }

    function postSale(payload) {
        return fetchWithTimeout('/kasir/transaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });
    }

    // ---------- catalog ----------
    function getCatalog() {
        return read(CATALOG_KEY, null);
    }

    function refreshCatalog() {
        return fetchWithTimeout('/kasir/catalog', { headers: { 'Accept': 'application/json' } })
            .then(function (res) {
                if (!res.ok || res.redirected) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (data.csrf_token) csrfToken = data.csrf_token;
                write(CATALOG_KEY, data);
                emit();
                document.dispatchEvent(new CustomEvent('kasir-offline:catalog', { detail: data }));
                return data;
            });
    }

    // After a sale, lower the local stock so the next scans see it (also while offline)
    function adjustCatalogStock(lines) {
        var catalog = getCatalog();
        if (!catalog) return;
        lines.forEach(function (line) {
            catalog.items.forEach(function (item) {
                if (item.id === line.id) item.stock -= line.quantity;
            });
        });
        write(CATALOG_KEY, catalog);
    }

    // ---------- sync ----------
    function syncQueue() {
        if (syncing || !navigator.onLine) return Promise.resolve(status());
        var pending = getQueue().filter(function (s) { return !s.failed; });
        if (!pending.length) return Promise.resolve(status());

        syncing = true;
        emit();
        var refreshedToken = false;

        function sendNext(i) {
            if (i >= pending.length) return Promise.resolve();
            var sale = pending[i];
            return postSale(sale).then(function (res) {
                if (res.status === 419 && !refreshedToken) {
                    // Page (and its CSRF token) may be older than the session: get a fresh one and retry
                    refreshedToken = true;
                    return refreshCatalog().then(function () { return sendNext(i); });
                }
                if (res.status === 401 || res.status === 419 || res.redirected) {
                    throw new Error('Sesi login berakhir. Login ulang untuk mengirim transaksi offline.');
                }
                return res.json().catch(function () { return {}; }).then(function (data) {
                    var queue = getQueue();
                    if (res.ok && data.success) {
                        saveQueue(queue.filter(function (s) { return s.client_uuid !== sale.client_uuid; }));
                    } else if (res.status >= 400 && res.status < 500) {
                        // The server looked at it and refused: retrying won't help, an admin must check it
                        queue.forEach(function (s) {
                            if (s.client_uuid === sale.client_uuid) {
                                s.failed = true;
                                s.error = data.message || ('HTTP ' + res.status);
                            }
                        });
                        saveQueue(queue);
                    } else {
                        throw new Error(data.message || ('HTTP ' + res.status)); // server error: try again later
                    }
                    return sendNext(i + 1);
                });
            });
        }

        return sendNext(0)
            .catch(function (e) {
                console.warn('Sinkron transaksi offline tertunda:', e.message);
                document.dispatchEvent(new CustomEvent('kasir-offline:error', { detail: e.message }));
            })
            .finally(function () {
                syncing = false;
                emit();
            })
            .then(status);
    }

    function retryFailed(clientUuid) {
        var queue = getQueue();
        queue.forEach(function (s) {
            if (!clientUuid || s.client_uuid === clientUuid) {
                delete s.failed;
                delete s.error;
            }
        });
        saveQueue(queue);
        return syncQueue();
    }

    window.addEventListener('online', function () { emit(); syncQueue(); });
    window.addEventListener('offline', emit);
    setInterval(syncQueue, SYNC_INTERVAL_MS);
    document.addEventListener('DOMContentLoaded', function () { setTimeout(syncQueue, 1500); });

    // Logging out would leave queued sales in this browser only; warn first
    document.addEventListener('submit', function (e) {
        var action = e.target.getAttribute('action') || '';
        if (!/\/logout$/.test(action)) return;
        var pendingCount = getQueue().length;
        if (pendingCount && !window.confirm(pendingCount + ' transaksi offline belum terkirim ke server.\n'
            + 'Jangan hapus data browser ini. Transaksi akan dikirim otomatis saat Anda login lagi di browser ini.\n\nTetap logout?')) {
            e.preventDefault();
        }
    }, true);

    window.KasirOffline = {
        getQueue: getQueue,
        enqueue: enqueue,
        status: status,
        syncQueue: syncQueue,
        retryFailed: retryFailed,
        getCatalog: getCatalog,
        refreshCatalog: refreshCatalog,
        adjustCatalogStock: adjustCatalogStock,
        postSale: postSale,
        uuid: uuid,
        offlineCode: offlineCode,
    };
})();
