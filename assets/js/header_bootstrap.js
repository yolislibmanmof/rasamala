/*!
 * Rasamala theme - early header bootstrap.
 */
'use strict';

(function () {
    const readJsonTemplate = (id, fallback = {}) => {
        const element = document.getElementById(id);
        if (!element) return fallback;

        const text = (element.content ? element.content.textContent : element.textContent) || '';
        if (!text.trim()) return fallback;

        try {
            return JSON.parse(text);
        } catch (error) {
            return fallback;
        }
    };

    const config = readJsonTemplate('rasamala-header-config', {});
    const colorModeStorageKey = 'rasamala-color-mode';

    const applyRuntimeCssVars = () => {
        const themeVars = config.themeVars || {};
        Object.keys(themeVars).forEach((name) => {
            if (name.indexOf('--') !== 0) return;
            document.documentElement.style.setProperty(name, String(themeVars[name]));
        });
    };

    window.rasamalaColorModeDefault = config.colorModeDefault || 'auto';
    window.rasamalaColorModeToggleVisible = config.colorModeToggleVisible !== false;
    window.rasamalaDarkCssUrl = config.darkCssUrl || '';
    window.rasamalaAutoCoverMode = config.autoCoverMode || 'empty_missing';
    window.rasamalaAutoCoverGenerator = window.rasamalaAutoCoverMode !== 'none';

    window.rasamalaResolveColorMode = function () {
        const fallback = window.rasamalaColorModeDefault || 'auto';

        if (window.rasamalaColorModeToggleVisible !== false) {
            try {
                const stored = window.localStorage.getItem(colorModeStorageKey);
                if (stored === 'dark' || stored === 'light') {
                    return stored;
                }
            } catch (error) {}
        }

        if (fallback === 'dark' || fallback === 'light') {
            return fallback;
        }

        try {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        } catch (error) {}

        return 'light';
    };

    const applyInitialColorMode = () => {
        let mode = 'light';

        try {
            mode = window.rasamalaResolveColorMode ? window.rasamalaResolveColorMode() : window.localStorage.getItem(colorModeStorageKey);
        } catch (error) {}

        if (mode !== 'dark') return;

        document.documentElement.classList.add('rasamala-dark');

        if (document.body) {
            document.body.classList.add('rasamala-dark');
        }

        if (!document.getElementById('rasamala-dark-css') && window.rasamalaDarkCssUrl) {
            const darkCss = document.createElement('link');
            darkCss.id = 'rasamala-dark-css';
            darkCss.rel = 'stylesheet';
            darkCss.href = window.rasamalaDarkCssUrl;
            document.head.appendChild(darkCss);
        }
    };

    const removeDebugHider = () => {
        document.documentElement.classList.remove('rasamala-debug-hidden');
        if (document.body) {
            document.body.classList.remove('rasamala-debug-hidden');
        }
    };

    const initDebugHiderProbe = () => {
        if (!config.debugHiderEnabled || !window.fetch) return;

        window.fetch('admin/index.php')
            .then((response) => {
                if (response.url && response.url.indexOf('p=login') === -1) {
                    removeDebugHider();
                    document.addEventListener('DOMContentLoaded', removeDebugHider, {once: true});
                }
            })
            .catch(() => {});
    };

    applyRuntimeCssVars();
    applyInitialColorMode();
    initDebugHiderProbe();

    document.addEventListener('DOMContentLoaded', () => {
        applyRuntimeCssVars();
        applyInitialColorMode();
    }, {once: true});
}());
