/*!
 * Rasamala theme - Color Mode controller.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const query = (selector, root = document) => root.querySelector(selector);
    const queryAll = (selector, root = document) => Array.prototype.slice.call(root.querySelectorAll(selector));

    const colorModeStorageKey = 'rasamala-color-mode';
    const colorModeMedia = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    
    const applyColorMode = (mode) => {
        const isDarkMode = mode === 'dark';

        document.documentElement.classList.toggle('rasamala-dark', isDarkMode);
        document.body.classList.toggle('rasamala-dark', isDarkMode);

        // Dynamically load or remove dark mode CSS link
        let darkLink = document.getElementById('rasamala-dark-css');
        if (isDarkMode) {
            if (!darkLink && window.rasamalaDarkCssUrl) {
                darkLink = document.createElement('link');
                darkLink.id = 'rasamala-dark-css';
                darkLink.rel = 'stylesheet';
                darkLink.href = window.rasamalaDarkCssUrl;
                document.head.appendChild(darkLink);
            }
        } else {
            if (darkLink) {
                darkLink.remove();
            }
        }

        const activeToggles = queryAll('#color-mode-toggle, #color-mode-toggle-nav, #color-mode-toggle-desktop, #palette-color-mode-toggle');
        activeToggles.forEach(toggle => {
            toggle.setAttribute('aria-pressed', isDarkMode ? 'true' : 'false');
            const nextLabel = isDarkMode
                ? (toggle.getAttribute('data-light-title') || 'Light mode')
                : (toggle.getAttribute('data-dark-title') || 'Dark mode');
            toggle.setAttribute('title', nextLabel);
            toggle.setAttribute('aria-label', nextLabel);

            const icon = query('i', toggle);
            if (icon) {
                icon.classList.toggle('fa-moon', !isDarkMode);
                icon.classList.toggle('fa-sun', isDarkMode);
            }
        });

        document.dispatchEvent(new CustomEvent('rasamala:color-mode-changed', {
            detail: { mode: isDarkMode ? 'dark' : 'light' }
        }));
    };

    const hasStoredColorMode = () => {
        if (window.rasamalaColorModeToggleVisible === false) {
            return false;
        }

        try {
            const stored = window.localStorage.getItem(colorModeStorageKey);
            return stored === 'dark' || stored === 'light';
        } catch (error) {
            return false;
        }
    };

    const resolveColorModePreference = () => {
        if (typeof window.rasamalaResolveColorMode === 'function') {
            return window.rasamalaResolveColorMode();
        }

        if (window.rasamalaColorModeToggleVisible !== false) {
            try {
                const stored = window.localStorage.getItem(colorModeStorageKey);
                if (stored === 'dark' || stored === 'light') {
                    return stored;
                }
            } catch (error) {
            }
        }

        const fallback = String(window.rasamalaColorModeDefault || 'auto');
        if (fallback === 'dark' || fallback === 'light') {
            return fallback;
        }

        return colorModeMedia && colorModeMedia.matches ? 'dark' : 'light';
    };

    applyColorMode(resolveColorModePreference());

    const syncSystemColorMode = () => {
        const fallback = String(window.rasamalaColorModeDefault || 'auto');
        if (fallback === 'auto' && !hasStoredColorMode()) {
            applyColorMode(resolveColorModePreference());
        }
    };

    if (colorModeMedia) {
        if (colorModeMedia.addEventListener) {
            colorModeMedia.addEventListener('change', syncSystemColorMode);
        } else if (colorModeMedia.addListener) {
            colorModeMedia.addListener(syncSystemColorMode);
        }
    }

    // Theme Viewer intentionally stops bubbling inside its panel so that
    // add/remove controls cannot trigger the outside-click closer. Listen in
    // the capture phase so the viewer's own dark/light button still reaches
    // this controller before that propagation guard runs.
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('#color-mode-toggle, #color-mode-toggle-nav, #color-mode-toggle-desktop, #palette-color-mode-toggle');
        if (!toggle) return;

        event.preventDefault();

        const nextMode = document.body.classList.contains('rasamala-dark') ? 'light' : 'dark';

        try {
            window.localStorage.setItem(colorModeStorageKey, nextMode);
        } catch (error) {}

        applyColorMode(nextMode);
    }, true);
});
