/* Remove service workers and caches shipped by older Rasamala releases. */
(function () {
    'use strict';

    const isRasamalaWorker = function (registration) {
        const worker = registration.installing || registration.waiting || registration.active;
        if (!worker || !worker.scriptURL) return false;

        try {
            const pathname = new URL(worker.scriptURL, window.location.href).pathname;
            return pathname.endsWith('/rasamala-sw.js')
                || pathname.endsWith('/template/rasamala/assets/js/sw.js');
        } catch (error) {
            return false;
        }
    };

    const unregisterWorkers = function () {
        if (!('serviceWorker' in navigator)) return Promise.resolve();

        return navigator.serviceWorker.getRegistrations().then(function (registrations) {
            return Promise.all(registrations.filter(isRasamalaWorker).map(function (registration) {
                return registration.unregister();
            }));
        });
    };

    const removeCaches = function () {
        if (!('caches' in window)) return Promise.resolve();

        return window.caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (key) {
                return key.indexOf('rasamala-static-') === 0
                    || key.indexOf('rasamala-opac-') === 0;
            }).map(function (key) {
                return window.caches.delete(key);
            }));
        });
    };

    const cleanup = function () {
        Promise.all([unregisterWorkers(), removeCaches()]).catch(function () {
            // Cleanup is best-effort and must never block catalog browsing.
        });
    };

    if (document.readyState === 'complete') {
        cleanup();
    } else {
        window.addEventListener('load', cleanup, { once: true });
    }
}());
