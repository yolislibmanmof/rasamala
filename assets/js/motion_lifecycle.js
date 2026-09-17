/*!
 * Rasamala motion lifecycle coordinator.
 *
 * Decorative work should stop when the document is not visible. Keeping the
 * lifecycle in one small module prevents each effect from inventing its own
 * visibility/timer rules.
 */
'use strict';

(function () {
    const subscribers = new Set();
    let pageVisible = !document.hidden;
    const reducedMotionMedia = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)')
        : null;
    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || null;
    const prefersReducedMotion = () => !!(reducedMotionMedia && reducedMotionMedia.matches);
    const prefersReducedData = () => !!(connection && connection.saveData === true);

    const syncMotionCapabilities = () => {
        const saveData = prefersReducedData();
        document.documentElement.classList.toggle('rasamala-reduced-motion', prefersReducedMotion());
        document.documentElement.classList.toggle('rasamala-save-data', saveData);

        const imageLayer = document.querySelector('.rasamala-background-image-layer');
        if (imageLayer && saveData) {
            imageLayer.style.setProperty('filter', 'none', 'important');
            imageLayer.style.setProperty('transform', 'none', 'important');
        } else if (imageLayer) {
            imageLayer.style.removeProperty('filter');
            imageLayer.style.removeProperty('transform');
        }
    };

    const notify = (visible) => {
        pageVisible = visible === true && !document.hidden;
        document.documentElement.classList.toggle('rasamala-page-hidden', !pageVisible);

        const event = new CustomEvent('rasamala:page-visibility', {
            detail: {visible: pageVisible}
        });
        document.dispatchEvent(event);

        subscribers.forEach((subscriber) => {
            try {
                subscriber(pageVisible);
            } catch (error) {
                // A decorative subscriber must never break other page code.
            }
        });
    };

    const subscribe = (subscriber) => {
        if (typeof subscriber !== 'function') return () => {};

        subscribers.add(subscriber);
        subscriber(pageVisible);

        return () => subscribers.delete(subscriber);
    };

    window.RasamalaMotionLifecycle = {
        isVisible: () => pageVisible,
        subscribe,
        prefersReducedMotion,
        prefersReducedData
    };

    document.addEventListener('visibilitychange', () => notify(!document.hidden), {passive: true});
    window.addEventListener('pagehide', () => notify(false), {passive: true});
    window.addEventListener('pageshow', () => notify(!document.hidden), {passive: true});

    // This script is deferred and loaded near the end of body, so the initial
    // state can be applied immediately without waiting for another DOM event.
    syncMotionCapabilities();
    notify(!document.hidden);

    if (reducedMotionMedia) {
        const onReducedMotionChange = () => syncMotionCapabilities();
        if (reducedMotionMedia.addEventListener) {
            reducedMotionMedia.addEventListener('change', onReducedMotionChange);
        } else if (reducedMotionMedia.addListener) {
            reducedMotionMedia.addListener(onReducedMotionChange);
        }
    }
    if (connection && typeof connection.addEventListener === 'function') {
        connection.addEventListener('change', syncMotionCapabilities);
    }

    const initializeFadeSlider = (slider) => {
        if (!slider || slider.dataset.motionLifecycleBound === '1') return;

        const items = Array.from(slider.querySelectorAll('.latest-content-fade-item'));
        if (items.length <= 1) return;

        slider.dataset.motionLifecycleBound = '1';
        let activeIndex = Math.max(0, items.findIndex((item) => item.classList.contains('active')));
        let timer = null;

        const stop = () => {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const advance = () => {
            if (document.hidden || (reducedMotionMedia && reducedMotionMedia.matches)) return;

            items[activeIndex].classList.remove('active');
            activeIndex = (activeIndex + 1) % items.length;
            items[activeIndex].classList.add('active');
        };

        const start = (visible) => {
            stop();
            if (!visible || (reducedMotionMedia && reducedMotionMedia.matches)) return;
            timer = window.setInterval(advance, 4000);
        };

        subscribe(start);
        if (reducedMotionMedia) {
            const onReducedMotionChange = () => start(pageVisible);
            if (reducedMotionMedia.addEventListener) {
                reducedMotionMedia.addEventListener('change', onReducedMotionChange);
            } else if (reducedMotionMedia.addListener) {
                reducedMotionMedia.addListener(onReducedMotionChange);
            }
        }
    };

    document.querySelectorAll('.latest-content-fade-slider').forEach(initializeFadeSlider);

    const syncTickerState = (visible) => {
        document.querySelectorAll('.latest-content-ticker-track').forEach((track) => {
            track.classList.toggle('is-page-hidden', !visible);
        });
    };

    subscribe(syncTickerState);
}());
