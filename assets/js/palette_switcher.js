/*!
 * Rasamala theme - Color Palette Switcher & WCAG Contrast Calculator Module
 */
'use strict';

(function(window, document) {
    const query = (selector, root = document) => root.querySelector(selector);
    const queryAll = (selector, root = document) => Array.prototype.slice.call(root.querySelectorAll(selector));

    const readJsonConfig = (id, fallback = null) => {
        const element = query(`#${id}`);
        if (!element) return fallback;
        const text = (element.content ? element.content.textContent : element.textContent) || '';
        if (!text.trim()) return fallback;
        try {
            return JSON.parse(text);
        } catch (error) {
            return fallback;
        }
    };

    const getConfig = () => readJsonConfig('rasamala-palette-switcher-config', window.rasamalaPaletteSwitcher || null);
    const paletteStorageKey = 'rasamala-theme-palette-key';
    const paletteCustomStorageKey = 'rasamala-theme-custom-palette';
    const paletteKeys = ['primary', 'secondary', 'accent', 'background', 'surface', 'text', 'muted'];
    const paletteInputMaxLength = 320;
    const minimumTextContrast = 4.5;

    const normalizePaletteToken = (value) => {
        const color = String(value || '').trim();
        if (/^#?[0-9a-fA-F]{6}$/.test(color)) {
            return `#${color.replace('#', '').toLowerCase()}`;
        }
        return '';
    };

    const extractPaletteTokens = (value) => {
        const tokens = [];
        const pattern = /(^|[^0-9a-fA-F])#?([0-9a-fA-F]{6})(?![0-9a-fA-F])/g;
        let match;

        while ((match = pattern.exec(String(value || ''))) !== null && tokens.length < paletteKeys.length * 2) {
            tokens.push(`#${match[2].toLowerCase()}`);
        }

        return tokens;
    };

    const sanitizePaletteSegment = (value) => {
        const parts = String(value || '')
            .slice(0, paletteInputMaxLength)
            .split(/[;\n\r]+/)
            .slice(0, paletteKeys.length)
            .map(normalizePaletteToken);

        while (parts.length > 0 && parts[parts.length - 1] === '') {
            parts.pop();
        }

        return parts.join('; ');
    };

    const sanitizePaletteInput = (value) => {
        const extractedTokens = extractPaletteTokens(value);
        if (extractedTokens.length >= paletteKeys.length) {
            const light = extractedTokens.slice(0, paletteKeys.length).join('; ');
            const dark = extractedTokens.length >= paletteKeys.length * 2
                ? extractedTokens.slice(paletteKeys.length, paletteKeys.length * 2).join('; ')
                : '';

            return dark ? `${light} | ${dark}` : light;
        }

        const segments = String(value || '').slice(0, paletteInputMaxLength).split('|');
        const light = sanitizePaletteSegment(segments[0] || '');
        const dark = sanitizePaletteSegment(segments[1] || '');

        if (light && dark) {
            return `${light} | ${dark}`;
        }

        return light;
    };

    const normalizeHex = (value, fallback) => normalizePaletteToken(value) || fallback;

    const hexToRgb = (hex) => {
        const color = normalizeHex(hex, '#6f5b43').replace('#', '');
        return [
            parseInt(color.slice(0, 2), 16),
            parseInt(color.slice(2, 4), 16),
            parseInt(color.slice(4, 6), 16)
        ].join(', ');
    };

    const adjustHex = (hex, amount) => {
        const color = normalizeHex(hex, '#6f5b43').replace('#', '');
        let output = '#';
        for (let index = 0; index < 3; index += 1) {
            const channel = Math.max(0, Math.min(255, parseInt(color.slice(index * 2, index * 2 + 2), 16) + amount));
            output += channel.toString(16).padStart(2, '0');
        }
        return output;
    };

    const paletteLuminance = (hex) => {
        const color = normalizeHex(hex, '#ffffff').replace('#', '');
        const channels = [0, 1, 2].map(index => {
            const value = parseInt(color.slice(index * 2, index * 2 + 2), 16) / 255;
            return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
        });
        return (0.2126 * channels[0]) + (0.7152 * channels[1]) + (0.0722 * channels[2]);
    };

    const contrastRatio = (background, foreground) => {
        const backgroundLuminance = paletteLuminance(background);
        const foregroundLuminance = paletteLuminance(foreground);
        const lighter = Math.max(backgroundLuminance, foregroundLuminance);
        const darker = Math.min(backgroundLuminance, foregroundLuminance);
        return (lighter + 0.05) / (darker + 0.05);
    };

    const accessibleTextColor = (background, preferred = '', fallback = '', minimumRatio = minimumTextContrast) => {
        const bg = normalizeHex(background, '#ffffff');
        const candidates = [normalizePaletteToken(preferred), normalizePaletteToken(fallback), '#111827', '#ffffff'];
        const seen = new Set();
        let bestColor = '#111827';
        let bestRatio = 0;

        for (const candidate of candidates) {
            if (!candidate || seen.has(candidate)) continue;
            seen.add(candidate);
            const ratio = contrastRatio(bg, candidate);
            if (ratio >= minimumRatio) {
                return candidate;
            }
            if (ratio > bestRatio) {
                bestRatio = ratio;
                bestColor = candidate;
            }
        }

        return bestColor;
    };

    const hydratePaletteContrast = (palette) => {
        const output = Object.assign({}, palette);
        output.primary = normalizeHex(output.primary, '#6f5b43');
        output.hover = normalizeHex(output.hover, adjustHex(output.primary, -28));
        output.secondary = normalizeHex(output.secondary, '#a58a63');
        output.accent = normalizeHex(output.accent, '#c8a24a');
        output.background = normalizeHex(output.background, '#f4f1ec');
        output.surface = normalizeHex(output.surface, '#ffffff');
        const textCandidate = normalizeHex(output.text, '#2f2a24');
        const mutedCandidate = normalizeHex(output.muted, '#7a7167');
        output.text = textCandidate;
        output.muted = mutedCandidate;
        output.accentHover = normalizeHex(output.accentHover, adjustHex(output.accent, -28));
        output.rgb = output.rgb || hexToRgb(output.primary);
        output.accentRgb = output.accentRgb || hexToRgb(output.accent);
        output.onPrimary = output.onPrimary || accessibleTextColor(output.primary);
        output.onPrimaryHover = output.onPrimaryHover || accessibleTextColor(output.hover);
        output.onSecondary = output.onSecondary || accessibleTextColor(output.secondary);
        output.onAccent = output.onAccent || accessibleTextColor(output.accent);
        output.onBackground = accessibleTextColor(output.background, textCandidate);
        output.onSurface = accessibleTextColor(output.surface, textCandidate, output.onBackground);
        output.mutedOnBackground = accessibleTextColor(output.background, mutedCandidate, output.onBackground);
        output.mutedOnSurface = accessibleTextColor(output.surface, mutedCandidate, output.onSurface);
        output.text = output.onBackground;
        output.muted = output.mutedOnBackground;
        return output;
    };

    const serializePaletteSegment = (palette) => paletteKeys
        .map(key => palette[key] || '#111827')
        .join('; ');

    const parsePaletteSegment = (value, fallback = {}) => {
        const parts = String(value || '')
            .slice(0, paletteInputMaxLength)
            .split(/[;\n\r]+/)
            .slice(0, paletteKeys.length)
            .map(part => part.trim());
        const palette = {};
        paletteKeys.forEach((key, index) => {
            palette[key] = normalizeHex(parts[index], fallback[key] || '#ffffff');
        });
        palette.hover = adjustHex(palette.primary, -28);
        palette.accentHover = adjustHex(palette.accent, -28);
        palette.rgb = hexToRgb(palette.primary);
        palette.accentRgb = hexToRgb(palette.accent);
        return hydratePaletteContrast(palette);
    };

    const parsePalettePair = (value, fallbackLight = {}, fallbackDark = {}) => {
        const segments = String(value || '').split('|');
        const light = parsePaletteSegment(segments[0] || '', fallbackLight);
        const dark = parsePaletteSegment(segments[1] || '', fallbackDark);
        return { light, dark };
    };

    const normalizePaletteInputForContrast = (value, fallbackLight = {}, fallbackDark = {}) => {
        const rawValue = String(value || '');
        const hasDarkSegment = rawValue.indexOf('|') !== -1;
        const pair = parsePalettePair(rawValue, fallbackLight, fallbackDark);
        const light = serializePaletteSegment(pair.light);
        const dark = serializePaletteSegment(pair.dark);
        return hasDarkSegment ? `${light} | ${dark}` : light;
    };

    const setRootThemeVars = (light, dark) => {
        const root = document.documentElement;
        const values = {
            '--theme-primary': light.primary,
            '--theme-primary-hover': light.hover,
            '--theme-secondary': light.secondary,
            '--theme-accent': light.accent,
            '--theme-background': light.background,
            '--theme-surface': light.surface,
            '--theme-text': light.text,
            '--theme-muted': light.muted,
            '--theme-muted-on-background': light.mutedOnBackground,
            '--theme-muted-on-surface': light.mutedOnSurface,
            '--theme-primary-rgb': light.rgb,
            '--theme-accent-rgb-value': light.accentRgb,
            '--theme-on-primary': light.onPrimary,
            '--theme-on-primary-hover': light.onPrimaryHover,
            '--theme-on-secondary': light.onSecondary,
            '--theme-on-accent': light.onAccent,
            '--theme-on-background': light.onBackground,
            '--theme-on-surface': light.onSurface,
            '--theme-dark-primary': dark.primary,
            '--theme-dark-primary-hover': dark.hover,
            '--theme-dark-secondary': dark.secondary,
            '--theme-dark-accent': dark.accent,
            '--theme-dark-background': dark.background,
            '--theme-dark-surface': dark.surface,
            '--theme-dark-text': dark.text,
            '--theme-dark-muted': dark.muted,
            '--theme-dark-muted-on-background': dark.mutedOnBackground,
            '--theme-dark-muted-on-surface': dark.mutedOnSurface,
            '--theme-dark-primary-rgb': dark.rgb,
            '--theme-dark-accent-rgb-value': dark.accentRgb,
            '--theme-dark-on-primary': dark.onPrimary,
            '--theme-dark-on-primary-hover': dark.onPrimaryHover,
            '--theme-dark-on-secondary': dark.onSecondary,
            '--theme-dark-on-accent': dark.onAccent,
            '--theme-dark-on-background': dark.onBackground,
            '--theme-dark-on-surface': dark.onSurface,
            '--color-primary': 'var(--theme-primary)',
            '--color-secondary': 'var(--theme-secondary)',
            '--color-accent': 'var(--theme-accent)',
            '--color-background': 'var(--theme-background)',
            '--color-surface': 'var(--theme-surface)',
            '--color-text': 'var(--theme-on-background)',
            '--color-muted': 'var(--theme-muted-on-background)',
            '--color-on-primary': 'var(--theme-on-primary)',
            '--color-on-secondary': 'var(--theme-on-secondary)',
            '--color-on-accent': 'var(--theme-on-accent)',
            '--rasamala-light-bg': 'var(--theme-background)',
            '--rasamala-text-primary': 'var(--theme-on-background)',
            '--rasamala-text-secondary': 'var(--theme-secondary)',
            '--rasamala-text-muted': 'var(--theme-muted-on-background)',
            '--rasamala-surface': 'var(--theme-surface)',
            '--rasamala-accent': light.accent,
            '--rasamala-accent-hover': light.accentHover,
            '--rasamala-readable-accent': 'color-mix(in srgb, var(--theme-accent) 72%, var(--theme-text) 28%)',
            '--theme-accent-color': light.accent,
            '--theme-accent-rgb': light.accentRgb,
            '--theme-accent-glow': `rgba(${light.accentRgb}, 0.8)`,
            '--theme-accent-glow-half': `rgba(${light.accentRgb}, 0.4)`,
            '--rasamala-chrome-bg': 'color-mix(in srgb, var(--theme-primary) 92%, #000 8%)',
            '--rasamala-chrome-border': 'color-mix(in srgb, var(--theme-primary) 38%, transparent)',
            '--rasamala-chrome-text': 'var(--theme-on-primary)',
            '--rasamala-chrome-text-muted': 'color-mix(in srgb, var(--theme-on-primary) 76%, transparent)',
            '--bs-body-bg': 'var(--theme-background)',
            '--bs-body-color': 'var(--theme-on-background)',
            '--bs-secondary-color': 'var(--theme-muted-on-background)'
        };

        Object.entries(values).forEach(([name, value]) => {
            root.style.setProperty(name, value);
        });
    };

    const updatePaletteBodyClass = (key, light) => {
        Array.from(document.body.classList)
            .filter(name => name.indexOf('rasamala-palette-') === 0)
            .forEach(name => document.body.classList.remove(name));

        const safeKey = String(key || 'custom').replace(/[^a-z0-9_-]+/ig, '-').toLowerCase();
        document.body.classList.add(`rasamala-palette-${safeKey}`);

        if (paletteLuminance(light.background) < 0.28 || paletteLuminance(light.surface) < 0.28) {
            document.body.classList.add('rasamala-palette-dark');
        }
    };

    const applyPaletteChoice = (key, customValue, persist = true) => {
        const paletteSwitcherConfig = getConfig();
        if (!paletteSwitcherConfig || paletteSwitcherConfig.enabled === false || !paletteSwitcherConfig.palettes) return;
        const palettes = paletteSwitcherConfig.palettes;
        const selectedKey = palettes[key] ? key : (paletteSwitcherConfig.currentKey || 'warmgray');
        let cleanCustomValue = sanitizePaletteInput(customValue);
        let pair = palettes[selectedKey] || palettes.warmgray;

        if (selectedKey === 'custom') {
            const fallback = palettes.custom || palettes[paletteSwitcherConfig.currentKey] || palettes.warmgray;
            const fallbackCustomValue = sanitizePaletteInput(paletteSwitcherConfig.customValue || '');
            cleanCustomValue = normalizePaletteInputForContrast(cleanCustomValue || fallbackCustomValue, fallback.light || {}, fallback.dark || {});
            pair = parsePalettePair(cleanCustomValue, fallback.light || {}, fallback.dark || {});
        }

        pair = {
            light: hydratePaletteContrast(pair.light || {}),
            dark: hydratePaletteContrast(pair.dark || {})
        };

        setRootThemeVars(pair.light, pair.dark);
        updatePaletteBodyClass(selectedKey, pair.light);

        if (persist) {
            try {
                window.localStorage.setItem(paletteStorageKey, selectedKey);
                if (selectedKey === 'custom') {
                    window.localStorage.setItem(paletteCustomStorageKey, cleanCustomValue);
                }
            } catch (error) {}
        }

        document.dispatchEvent(new CustomEvent('rasamala:palette-changed', {
            detail: { key: selectedKey, light: pair.light, dark: pair.dark }
        }));
    };

    window.RasamalaPaletteSwitcher = {
        getConfig: getConfig,
        get config() { return getConfig(); },
        storageKeys: { key: paletteStorageKey, custom: paletteCustomStorageKey },
        sanitizeInput: sanitizePaletteInput,
        normalizeInput: normalizePaletteInputForContrast,
        applyChoice: applyPaletteChoice
    };

})(window, document);
