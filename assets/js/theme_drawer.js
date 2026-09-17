/*!
 * Rasamala theme - Floating Theme Viewer / Drawer Interactivity Module
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
    const featureEnabled = (config, name) => {
        const flags = config && config.featureFlags;
        return !flags || flags[name] !== false;
    };
    const themeViewerStorageKeys = {
        heroMode: 'rasamala-theme-hero-mode',
        heroTopicsInHero: 'rasamala-theme-hero-topics-in-hero',
        heroBackgroundStyle: 'rasamala-theme-hero-background-style',
        heroBackgroundCustom: 'rasamala-theme-hero-background-custom',
        heroBackgroundImageSize: 'rasamala-theme-hero-background-image-size',
        heroBackgroundImagePosition: 'rasamala-theme-hero-background-image-position',
        heroBackgroundImageFilter: 'rasamala-theme-hero-background-image-filter',
        heroBackgroundImageBlur: 'rasamala-theme-hero-background-image-blur',
        heroBackgroundImageOverlay: 'rasamala-theme-hero-background-image-overlay',
        homeLayout: 'rasamala-theme-home-layout',
        libraryNamePosition: 'rasamala-theme-library-name-position',
        heroText: 'rasamala-theme-hero-text',
        heroTextSize: 'rasamala-theme-hero-text-size',
        searchSize: 'rasamala-theme-search-size',
        searchPlaceholder: 'rasamala-theme-search-placeholder',
        homeInfoShow: 'rasamala-theme-home-info-show',
        tickerShow: 'rasamala-theme-ticker-show',
        tickerSpeed: 'rasamala-theme-ticker-speed',
        searchPanelStyle: 'rasamala-theme-search-panel-style',
        mobileNavShow: 'rasamala-theme-mobile-nav-show',
        backToTopShow: 'rasamala-theme-back-to-top-show',
        mapVisibility: 'rasamala-theme-map-visibility',
        font: 'rasamala-theme-font-family',
        animation: 'rasamala-theme-background-animation',
        animationSpeed: 'rasamala-theme-background-animation-speed',
        cursorParticles: 'rasamala-theme-cursor-particles',
        cursorIcon: 'rasamala-theme-cursor-icon',
        sectionsHidden: 'rasamala-theme-sections-hidden',
        hiddenSections: 'rasamala-theme-hidden-sections'
    };

    const fontStacks = {
        system: 'system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        inter: '"Inter", system-ui, BlinkMacSystemFont, "Segoe UI", sans-serif',
        roboto: '"Roboto", Arial, Helvetica, sans-serif',
        poppins: '"Poppins", "Trebuchet MS", Arial, sans-serif',
        playfair: '"Playfair Display", Georgia, "Times New Roman", serif'
    };

    const optionValueExists = (options, value) => !!options && Object.prototype.hasOwnProperty.call(options, value);

    const sanitizeCustomBackgroundValue = (value) => {
        const parts = String(value || '').slice(0, 2500).split('|', 2).map(part => part.trim());
        const clean = parts.map(part => {
            if (!part || /[{};<>`]/.test(part) || /(?:url|expression|javascript|vbscript|@import|behavior)\s*[:(]?/i.test(part)) return '';
            return /^[a-z0-9#(),.%\/:+\-_*\s'"\[\]]+$/i.test(part) ? part : '';
        });
        if (!clean[0] && clean[1]) clean[0] = clean[1];
        if (!clean[1]) clean[1] = clean[0];
        return clean[0] && clean[1] ? `${clean[0]} | ${clean[1]}` : '';
    };

    const applyCustomBackgroundStyle = (value, enabled) => {
        const styleElement = query('#rasamala-theme-viewer-background-style');
        if (!styleElement) return;
        const sanitized = sanitizeCustomBackgroundValue(value);
        if (!enabled || !sanitized) {
            styleElement.textContent = '';
            return;
        }
        const [light, dark] = sanitized.split('|').map(part => part.trim());
        styleElement.textContent = `body.rasamala-background-style-custom { background: ${light} !important; background-attachment: fixed !important; }\nhtml body.rasamala-dark.rasamala-background-style-custom { background: ${dark} !important; background-attachment: fixed !important; }`;
    };

    const ensureBackgroundImageLayer = () => {
        let layer = query('#rasamala-background-image-layer');
        if (layer) return layer;
        layer = document.createElement('div');
        layer.id = 'rasamala-background-image-layer';
        layer.className = 'rasamala-background-image-layer';
        layer.setAttribute('aria-hidden', 'true');
        document.body.insertBefore(layer, document.body.firstChild);
        return layer;
    };

    const imageCssDeclarations = (imageUrl, settings, dark = false) => {
        const sizes = {
            normal: ['auto', 'no-repeat'],
            crop: ['cover', 'no-repeat'],
            contain: ['contain', 'no-repeat'],
            stretch: ['100% 100%', 'no-repeat'],
            tile: ['auto', 'repeat'],
            width: ['100% auto', 'no-repeat'],
            height: ['auto 100%', 'no-repeat']
        };
        const positions = {
            center: 'center center',
            top: 'center top',
            bottom: 'center bottom',
            left: 'left center',
            right: 'right center',
            'top-left': 'left top',
            'top-right': 'right top',
            'bottom-left': 'left bottom',
            'bottom-right': 'right bottom'
        };
        const filters = {
            none: 'none',
            soft: 'brightness(0.96) saturate(0.92) contrast(0.98)',
            readable: 'brightness(0.78) saturate(0.84) contrast(1.06)',
            vivid: 'brightness(1.04) saturate(1.18) contrast(1.08)',
            monochrome: 'grayscale(0.86) contrast(1.04)',
            warm: 'sepia(0.18) saturate(1.08) hue-rotate(-8deg)',
            cool: 'saturate(0.9) hue-rotate(10deg)'
        };
        const blurs = {none: '0px', '1': '1px', '2': '2px', '4': '4px', '8': '8px'};
        const size = sizes[settings.size] || sizes.crop;
        const position = positions[settings.position] || positions.center;
        let filter = filters[settings.filter] || filters.none;
        const blur = blurs[settings.blur] || blurs.none;
        if (blur !== '0px') filter += ` blur(${blur})`;
        const overlayValue = settings.overlay || 'none';
        const opacity = dark ? '0.30' : '0.06';
        const overlayMap = {
            none: '',
            auto: `linear-gradient(rgba(${dark ? '0, 0, 0' : '255, 255, 255'}, ${opacity}), rgba(${dark ? '0, 0, 0' : '255, 255, 255'}, ${opacity})), `,
            subtle: 'linear-gradient(rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.12)), ',
            readable: `linear-gradient(rgba(0, 0, 0, ${dark ? '0.36' : '0.16'}), rgba(0, 0, 0, ${dark ? '0.36' : '0.16'})), `,
            dim: 'linear-gradient(rgba(0, 0, 0, 0.42), rgba(0, 0, 0, 0.42)), ',
            accent: `linear-gradient(rgba(var(--theme-primary-rgb), ${dark ? '0.24' : '0.16'}), rgba(var(--theme-primary-rgb), ${dark ? '0.24' : '0.16'})), `,
            frosted: `linear-gradient(${dark ? 'rgba(15, 23, 42, 0.34)' : 'rgba(255, 255, 255, 0.24)'}, ${dark ? 'rgba(15, 23, 42, 0.34)' : 'rgba(255, 255, 255, 0.24)'}), `
        };
        const overlay = overlayMap[overlayValue] || '';
        return `background-image: ${overlay}url("${imageUrl}") !important; background-position: ${position} !important; background-repeat: ${size[1]} !important; background-size: ${size[0]} !important; background-attachment: fixed !important; filter: ${filter} !important; transform: ${blur !== '0px' ? 'scale(1.03)' : 'none'} !important; transform-origin: center center !important;`;
    };

    const applyRuntimeBackgroundStyle = (style, customValue, styleDetails, imageSettings) => {
        const styleElement = query('#rasamala-theme-viewer-background-style');
        if (!styleElement) return;
        document.body.classList.remove('rasamala-background-image-active');
        if (style === 'custom') {
            applyCustomBackgroundStyle(customValue, true);
            return;
        }
        const detail = styleDetails && styleDetails[style];
        const imageUrl = detail && detail.image ? String(detail.imageUrl || '').replace(/["'()\\]/g, '') : '';
        if (!imageUrl) {
            styleElement.textContent = '';
            return;
        }
        ensureBackgroundImageLayer();
        document.body.classList.add('rasamala-background-image-active');
        const settings = imageSettings || {size: 'crop', position: 'center', filter: 'none', blur: 'none', overlay: 'none'};
        styleElement.textContent = `body.rasamala-background-style-${style}.rasamala-background-image-active { background-image: none !important; }\nbody.rasamala-background-image-active .rasamala-background-image-layer { ${imageCssDeclarations(imageUrl, settings, false)} }\nhtml body.rasamala-dark.rasamala-background-style-${style}.rasamala-background-image-active { background-image: none !important; }\nhtml body.rasamala-dark.rasamala-background-image-active .rasamala-background-image-layer { ${imageCssDeclarations(imageUrl, settings, true)} }`;
    };

    const getStoredValue = (key, fallback) => {
        try {
            const value = window.localStorage.getItem(key);
            return value == null || value === '' ? fallback : value;
        } catch (error) {
            return fallback;
        }
    };

    const setStoredValue = (key, value, fallback) => {
        try {
            if (value === fallback || value == null || value === '') {
                window.localStorage.removeItem(key);
                return;
            }
            window.localStorage.setItem(key, value);
        } catch (error) {}
    };

    const sectionOptions = () => {
        const config = getConfig();
        return Array.isArray(config && config.homeSections) ? config.homeSections : [];
    };

    const normalizeSectionKeys = (value) => {
        const allowed = sectionOptions().map(section => section.key);
        const raw = Array.isArray(value) ? value : String(value || '').split(',');
        return raw
            .map(item => String(item || '').trim())
            .filter((item, index, items) => item && allowed.indexOf(item) !== -1 && items.indexOf(item) === index);
    };

    const backgroundSpeedMultiplier = (speed) => {
        if (speed === 'slow') return '1.5';
        if (speed === 'fast') return '0.65';
        return '1';
    };

    const ensureBackgroundAnimationLayer = () => {
        let layer = query('#background-animation-layer') || query('#hero-animation-layer');
        if (layer) return layer;

        layer = document.createElement('div');
        layer.id = 'background-animation-layer';
        layer.className = 'background-animation-layer hero-animation-layer';
        layer.setAttribute('aria-hidden', 'true');
        document.body.insertBefore(layer, document.body.firstChild);
        return layer;
    };

    const heroInsideSectionMap = {
        topics: 'topic',
        popular: 'popular',
        new_update: 'new-collection',
        top_reader: 'top-reader'
    };

    const clearElement = (element) => {
        if (!element) return;
        while (element.firstChild) element.removeChild(element.firstChild);
    };

    const mountHeroInsideContent = (selection, isOnlyHero) => {
        const wrap = query('#rasamala-hero-inside-content');
        if (!wrap) return;
        const templates = query('#rasamala-hero-inside-templates');
        const template = templates && templates.querySelector(`[data-hero-inside-template="${selection}"]`);

        queryAll('.rasamala-hero-inside-item', wrap).forEach(item => {
            const type = item.getAttribute('data-inside');
            const mount = query('[data-hero-inside-mount]', item);
            if (!mount) return;
            const isActive = isOnlyHero && type === selection && selection !== 'none';
            if (!isActive) {
                if (window.RasamalaHeroRenderer && mount._rasamalaHeroApp) {
                    window.RasamalaHeroRenderer.clear(mount);
                } else {
                    clearElement(mount);
                }
                mount.removeAttribute('data-hero-mounted-type');
                return;
            }

            if (type !== 'topics' && type !== 'news' && window.RasamalaHeroRenderer) {
                if (mount._rasamalaHeroType === type && mount._rasamalaHeroApp) return;
                window.RasamalaHeroRenderer.mount(mount, type);
                return;
            }
            if (!template) return;
            if (mount.getAttribute('data-hero-mounted-type') === type) return;
            clearElement(mount);
            mount.appendChild(template.content.cloneNode(true));
            mount.setAttribute('data-hero-mounted-type', type);
        });
    };

    const applyThemeViewerChoice = (settings, persist = true) => {
        const paletteSwitcherConfig = getConfig();
        if (!paletteSwitcherConfig || paletteSwitcherConfig.enabled === false) return;

        const font = optionValueExists(paletteSwitcherConfig.fonts, settings.font)
            ? settings.font
            : (paletteSwitcherConfig.fontFamily || 'system');
        const animation = optionValueExists(paletteSwitcherConfig.animations, settings.animation)
            ? settings.animation
            : (paletteSwitcherConfig.backgroundAnimation || 'none');
        const animationSpeed = optionValueExists(paletteSwitcherConfig.animationSpeeds, settings.animationSpeed)
            ? settings.animationSpeed
            : (paletteSwitcherConfig.backgroundAnimationSpeed || 'normal');
        const cursorParticles = !featureEnabled(paletteSwitcherConfig, 'cursorParticles')
            ? 'none'
            : (optionValueExists(paletteSwitcherConfig.cursorParticlesOptions, settings.cursorParticles)
                ? settings.cursorParticles
                : (paletteSwitcherConfig.cursorParticles || 'auto'));
        const cursorIcon = !featureEnabled(paletteSwitcherConfig, 'cursorIcon')
            ? 'default'
            : (optionValueExists(paletteSwitcherConfig.cursorIcons, settings.cursorIcon)
                ? settings.cursorIcon
                : (paletteSwitcherConfig.cursorIcon || 'default'));
        const sectionsHidden = settings.sectionsHidden === true || settings.sectionsHidden === '1';
        const hiddenSections = sectionsHidden
            ? sectionOptions().map(section => section.key)
            : normalizeSectionKeys(settings.hiddenSections);

        document.documentElement.style.setProperty('--rasamala-font-stack', fontStacks[font] || fontStacks.system);
        document.body.style.setProperty('--rasamala-font-stack', fontStacks[font] || fontStacks.system);

        document.body.classList.toggle('rasamala-opac-sections-hidden', sectionsHidden);

        const heroModeSelect = query('#theme-hero-mode-select');
        const configuredHeroMode = optionValueExists(paletteSwitcherConfig.heroModes, paletteSwitcherConfig.currentHeroMode)
            ? paletteSwitcherConfig.currentHeroMode
            : 'no';
        const requestedHeroMode = heroModeSelect
            ? heroModeSelect.value
            : (getStoredValue(themeViewerStorageKeys.heroMode) || configuredHeroMode);
        const activeHeroMode = optionValueExists(paletteSwitcherConfig.heroModes, requestedHeroMode)
            ? requestedHeroMode
            : configuredHeroMode;
        const heroTopicsSelect = query('#theme-hero-topics-select');
        const configuredHeroTopicsInHero = optionValueExists(paletteSwitcherConfig.heroTopicsInHero, paletteSwitcherConfig.currentHeroTopicsInHero)
            ? paletteSwitcherConfig.currentHeroTopicsInHero
            : 'none';
        const requestedHeroTopicsInHero = heroTopicsSelect
            ? heroTopicsSelect.value
            : getStoredValue(themeViewerStorageKeys.heroTopicsInHero, configuredHeroTopicsInHero);
        const activeHeroTopicsInHero = optionValueExists(paletteSwitcherConfig.heroTopicsInHero, requestedHeroTopicsInHero)
            ? requestedHeroTopicsInHero
            : configuredHeroTopicsInHero;
        const heroBackgroundStyleSelect = query('#theme-background-style-select') || query('#theme-hero-background-style-select');
        const heroBackgroundCustomInput = query('#theme-background-style-custom-input');
        const configuredHeroBackgroundCustom = sanitizeCustomBackgroundValue(paletteSwitcherConfig.currentHeroBackgroundCustom || paletteSwitcherConfig.heroBackgroundCustomDefault || '');
        const requestedHeroBackgroundCustom = heroBackgroundCustomInput
            ? heroBackgroundCustomInput.value
            : getStoredValue(themeViewerStorageKeys.heroBackgroundCustom, configuredHeroBackgroundCustom);
        const activeHeroBackgroundCustom = sanitizeCustomBackgroundValue(requestedHeroBackgroundCustom) || configuredHeroBackgroundCustom;
        const configuredHeroBackgroundStyle = optionValueExists(paletteSwitcherConfig.heroBackgroundStyles, paletteSwitcherConfig.currentHeroBackgroundStyle)
            ? paletteSwitcherConfig.currentHeroBackgroundStyle
            : 'none';
        const requestedHeroBackgroundStyle = heroBackgroundStyleSelect
            ? heroBackgroundStyleSelect.value
            : getStoredValue(themeViewerStorageKeys.heroBackgroundStyle, configuredHeroBackgroundStyle);
        const activeHeroBackgroundStyle = optionValueExists(paletteSwitcherConfig.heroBackgroundStyles, requestedHeroBackgroundStyle)
            ? requestedHeroBackgroundStyle
            : configuredHeroBackgroundStyle;
        const configuredHeroBackgroundImage = Object.assign({size: 'crop', position: 'center', filter: 'none', blur: 'none', overlay: 'none'}, paletteSwitcherConfig.currentHeroBackgroundImage || {});
        const imageSizeOptions = paletteSwitcherConfig.heroBackgroundImageSizes || {};
        const imagePositionOptions = paletteSwitcherConfig.heroBackgroundImagePositions || {};
        const imageFilterOptions = paletteSwitcherConfig.heroBackgroundImageFilters || {};
        const imageBlurOptions = paletteSwitcherConfig.heroBackgroundImageBlurs || {};
        const imageOverlayOptions = paletteSwitcherConfig.heroBackgroundImageOverlays || {};
        const imageSizeSelect = query('#theme-background-image-size-select');
        const imagePositionSelect = query('#theme-background-image-position-select');
        const imageFilterSelect = query('#theme-background-image-filter-select');
        const imageBlurSelect = query('#theme-background-image-blur-select');
        const imageOverlaySelect = query('#theme-background-image-overlay-select');
        const activeHeroBackgroundImage = {
            size: optionValueExists(imageSizeOptions, imageSizeSelect ? imageSizeSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageSize, configuredHeroBackgroundImage.size))
                ? (imageSizeSelect ? imageSizeSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageSize, configuredHeroBackgroundImage.size))
                : configuredHeroBackgroundImage.size,
            position: optionValueExists(imagePositionOptions, imagePositionSelect ? imagePositionSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImagePosition, configuredHeroBackgroundImage.position))
                ? (imagePositionSelect ? imagePositionSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImagePosition, configuredHeroBackgroundImage.position))
                : configuredHeroBackgroundImage.position,
            filter: optionValueExists(imageFilterOptions, imageFilterSelect ? imageFilterSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageFilter, configuredHeroBackgroundImage.filter))
                ? (imageFilterSelect ? imageFilterSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageFilter, configuredHeroBackgroundImage.filter))
                : configuredHeroBackgroundImage.filter,
            blur: optionValueExists(imageBlurOptions, imageBlurSelect ? imageBlurSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageBlur, configuredHeroBackgroundImage.blur))
                ? (imageBlurSelect ? imageBlurSelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageBlur, configuredHeroBackgroundImage.blur))
                : configuredHeroBackgroundImage.blur,
            overlay: optionValueExists(imageOverlayOptions, imageOverlaySelect ? imageOverlaySelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageOverlay, configuredHeroBackgroundImage.overlay))
                ? (imageOverlaySelect ? imageOverlaySelect.value : getStoredValue(themeViewerStorageKeys.heroBackgroundImageOverlay, configuredHeroBackgroundImage.overlay))
                : configuredHeroBackgroundImage.overlay
        };
        const homeLayoutSelect = query('#theme-home-layout-select');
        const configuredHomeLayout = optionValueExists(paletteSwitcherConfig.homeLayouts, paletteSwitcherConfig.currentHomeLayout)
            ? paletteSwitcherConfig.currentHomeLayout
            : 'standard';
        const requestedHomeLayout = homeLayoutSelect
            ? homeLayoutSelect.value
            : getStoredValue(themeViewerStorageKeys.homeLayout, configuredHomeLayout);
        const activeHomeLayout = optionValueExists(paletteSwitcherConfig.homeLayouts, requestedHomeLayout)
            ? requestedHomeLayout
            : configuredHomeLayout;
        const libraryNamePositions = paletteSwitcherConfig.libraryNamePositions || {};
        const configuredLibraryNamePosition = optionValueExists(libraryNamePositions, paletteSwitcherConfig.currentLibraryNamePosition)
            ? paletteSwitcherConfig.currentLibraryNamePosition
            : 'navbar';
        const requestedLibraryNamePosition = settings.libraryNamePosition || getStoredValue(themeViewerStorageKeys.libraryNamePosition, configuredLibraryNamePosition);
        const activeLibraryNamePosition = optionValueExists(libraryNamePositions, requestedLibraryNamePosition)
            ? requestedLibraryNamePosition
            : configuredLibraryNamePosition;
        const configuredHeroText = String(paletteSwitcherConfig.heroText || 'Search Library Collection');
        const activeHeroText = settings.heroText == null ? configuredHeroText : String(settings.heroText).slice(0, 120);
        const heroTextSizes = paletteSwitcherConfig.heroTextSizes || {};
        const activeHeroTextSize = optionValueExists(heroTextSizes, settings.heroTextSize)
            ? settings.heroTextSize
            : (paletteSwitcherConfig.heroTextSize || 'small');
        const searchSizes = paletteSwitcherConfig.searchSizes || {};
        const activeSearchSize = optionValueExists(searchSizes, settings.searchSize)
            ? settings.searchSize
            : (paletteSwitcherConfig.searchSize || 'medium');
        const activeSearchPlaceholder = settings.searchPlaceholder == null
            ? String(paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...')
            : String(settings.searchPlaceholder).slice(0, 160);
        const isShownSetting = value => !['hide', '0', 'false', 'none', 'off'].includes(String(value == null ? '' : value).trim().toLowerCase());
        const activeHomeInfoShow = isShownSetting(settings.homeInfoShow) ? 'show' : 'hide';
        const tickerSpeeds = paletteSwitcherConfig.tickerSpeeds || {};
        const activeTickerSpeed = optionValueExists(tickerSpeeds, settings.tickerSpeed)
            ? settings.tickerSpeed
            : (paletteSwitcherConfig.tickerSpeed || 'slow');
        const activeTickerShow = isShownSetting(settings.tickerShow) ? 'show' : 'hide';
        const panelStyles = paletteSwitcherConfig.searchPanelStyles || {};
        const activeSearchPanelStyle = !featureEnabled(paletteSwitcherConfig, 'panelBackground')
            ? 'solid'
            : (optionValueExists(panelStyles, settings.searchPanelStyle)
                ? settings.searchPanelStyle
                : (paletteSwitcherConfig.searchPanelStyle || 'solid'));
        const activeMobileNavShow = isShownSetting(settings.mobileNavShow) ? 'show' : 'hide';
        const activeBackToTopShow = isShownSetting(settings.backToTopShow) ? 'show' : 'hide';
        const mapVisibilityOptions = paletteSwitcherConfig.mapVisibilityOptions || {};
        const activeMapVisibility = optionValueExists(mapVisibilityOptions, settings.mapVisibility)
            ? settings.mapVisibility
            : (paletteSwitcherConfig.mapVisibility || 'all');
        const heroInsideSectionMap = {
            topics: 'topic',
            '1': 'topic',
            yes: 'topic',
            news: 'news',
            popular: 'popular',
            new_update: 'new-collection',
            top_reader: 'top-reader'
        };
        const isOnlyHero = activeHeroMode === 'yes';
        const showHeroTopics = isOnlyHero && (activeHeroTopicsInHero === 'topics' || activeHeroTopicsInHero === '1' || activeHeroTopicsInHero === 'yes');
        const heroSectionKey = isOnlyHero ? (heroInsideSectionMap[activeHeroTopicsInHero] || '') : '';

        document.body.classList.toggle('rasamala-home-hero-only', isOnlyHero);
        document.body.classList.toggle('rasamala-home-topics-in-hero', showHeroTopics);
        const heroHeading = query('.hero-search-heading h1');
        const heroHeadingWrap = query('.hero-search-heading');
        if (heroHeading && (!heroHeadingWrap || !heroHeadingWrap.classList.contains('hero-search-heading-library'))) {
            heroHeading.textContent = activeHeroText;
        }
        if (heroHeadingWrap) {
            heroHeadingWrap.classList.remove('hero-search-heading-small', 'hero-search-heading-medium', 'hero-search-heading-large');
            heroHeadingWrap.classList.add(`hero-search-heading-${activeHeroTextSize}`);
        }
        const searchWrap = query('#search-wraper');
        if (searchWrap) {
            searchWrap.classList.remove('search-size-small', 'search-size-medium', 'search-size-large');
            searchWrap.classList.add(`search-size-${activeSearchSize}`);
        }
        const searchInput = query('#search-input');
        if (searchInput) searchInput.setAttribute('placeholder', activeSearchPlaceholder);
        queryAll('.latest-content-strip').forEach(element => {
            element.hidden = activeHomeInfoShow === 'hide';
        });
        queryAll('.latest-content-ticker').forEach(element => {
            element.hidden = activeTickerShow === 'hide';
            element.setAttribute('data-speed', activeTickerSpeed);
        });
        document.body.classList.toggle('rasamala-search-panels-solid', activeSearchPanelStyle === 'solid');
        document.body.classList.toggle('rasamala-search-panels-transparent', activeSearchPanelStyle === 'transparent');
        document.body.classList.toggle('mobile-bottom-nav-enabled', activeMobileNavShow === 'show');
        document.body.classList.toggle('mobile-bottom-nav-hidden', activeMobileNavShow === 'hide');
        const mobileBottomNav = query('.mobile-bottom-nav');
        if (mobileBottomNav) mobileBottomNav.hidden = activeMobileNavShow === 'hide';
        const backToTop = query('#back-to-top');
        if (backToTop) backToTop.hidden = activeBackToTopShow === 'hide';
        queryAll('.rasamala-home-section-map').forEach(section => {
            const hideMap = activeMapVisibility === 'hide_map' || activeMapVisibility === 'hide_all';
            const hideSocial = activeMapVisibility === 'hide_social' || activeMapVisibility === 'hide_all';
            const mapFrame = query('iframe', section);
            const socialLinks = query('.home-map-social-links', section);
            const contact = query('.home-map-contact', section);
            const mapColumn = mapFrame && mapFrame.closest('.col-md-6, .col-md-12');
            if (mapColumn) mapColumn.hidden = hideMap;
            if (socialLinks) socialLinks.hidden = hideSocial;
            if (contact) {
                const hideContact = activeMapVisibility === 'hide_all';
                contact.hidden = hideContact;
                contact.classList.toggle('col-md-12', hideMap && !hideContact);
                contact.classList.toggle('col-md-6', !hideMap && !hideContact);
            }
            section.hidden = activeMapVisibility === 'hide_all';
        });
        if (typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new Event('resize'));
        }
        document.body.classList.remove('rasamala-home-hero-mode-yes', 'rasamala-home-hero-mode-no');
        document.body.classList.add(`rasamala-home-hero-mode-${activeHeroMode}`);
        Array.from(document.body.classList)
            .filter(name => name.indexOf('rasamala-hero-bg-style-') === 0 || name.indexOf('rasamala-background-style-') === 0)
            .forEach(name => document.body.classList.remove(name));
        document.body.classList.add(`rasamala-background-style-${activeHeroBackgroundStyle}`);
        document.body.classList.add(`rasamala-hero-bg-style-${activeHeroBackgroundStyle}`);
        applyRuntimeBackgroundStyle(activeHeroBackgroundStyle, activeHeroBackgroundCustom, paletteSwitcherConfig.heroBackgroundStyleDetails || {}, activeHeroBackgroundImage);
        document.body.classList.remove('rasamala-home-layout-tabs', 'rasamala-home-layout-standard');
        document.body.classList.add(`rasamala-home-layout-${activeHomeLayout}`);
        queryAll('.rasamala-hero-section').forEach(element => {
            element.classList.toggle('rasamala-hero-section-only', isOnlyHero);
        });
        queryAll('.rasamala-search-banner-section').forEach(element => {
            element.classList.toggle('rasamala-search-banner-hero-only', isOnlyHero);
        });

        queryAll('#slims-home').forEach(element => {
            element.hidden = false;
        });

        const homepageRoot = query('#slims-home');
        const homeTabNav = query('.rasamala-home-section-tabs', homepageRoot || document);
        const homeTabButtons = queryAll('[data-home-tab-target]', homepageRoot || document);
        const homeTabPanes = queryAll('[data-home-tab-pane]', homepageRoot || document);
        const availableHomeTabs = homeTabButtons.filter(tab => hiddenSections.indexOf(tab.getAttribute('data-home-tab-target')) === -1 && tab.getAttribute('data-home-tab-target') !== heroSectionKey);
        let activeHomeTab = availableHomeTabs.find(tab => tab.getAttribute('aria-selected') === 'true') || availableHomeTabs[0] || null;
        if (homepageRoot) homepageRoot.setAttribute('data-home-layout', activeHomeLayout);
        if (homeTabNav) {
            homeTabNav.hidden = activeHomeLayout !== 'tabs' || sectionsHidden || availableHomeTabs.length === 0;
        }
        homeTabButtons.forEach(tab => {
            const tabKey = tab.getAttribute('data-home-tab-target');
            const isActive = activeHomeLayout === 'tabs' && tab === activeHomeTab;
            tab.hidden = hiddenSections.indexOf(tabKey) !== -1 || (heroSectionKey && tabKey === heroSectionKey);
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.setAttribute('tabindex', isActive ? '0' : '-1');
        });
        homeTabPanes.forEach(pane => {
            const tabKey = pane.getAttribute('data-home-tab-pane');
            const isActive = activeHomeLayout === 'tabs' && activeHomeTab && tabKey === activeHomeTab.getAttribute('data-home-tab-target');
            const shouldHidePane = sectionsHidden || hiddenSections.indexOf(tabKey) !== -1 || (activeHomeLayout === 'tabs' && !isActive);
            pane.hidden = shouldHidePane;
            pane.setAttribute('aria-hidden', shouldHidePane ? 'true' : 'false');
        });

        sectionOptions().forEach(section => {
            queryAll(section.selector).forEach(element => {
                const isHeroSection = heroSectionKey && section.key === heroSectionKey;
                if (activeHomeLayout === 'tabs' && element.matches('[data-home-tab-pane]')) {
                    if (hiddenSections.indexOf(section.key) !== -1 || isHeroSection) element.hidden = true;
                    return;
                }
                element.hidden = hiddenSections.indexOf(section.key) !== -1 || isHeroSection;
            });
        });

        const insideContentWrap = query('#rasamala-hero-inside-content');
        const homeTopicsElement = query('#rasamala-home-topic-section');
        const topicsHidden = sectionsHidden || hiddenSections.indexOf('topic') !== -1;

        if (insideContentWrap) {
            const hasActiveSelection = isOnlyHero && activeHeroTopicsInHero && activeHeroTopicsInHero !== 'none' && activeHeroTopicsInHero !== '0';
            insideContentWrap.hidden = !hasActiveSelection;
            queryAll('.rasamala-hero-inside-item', insideContentWrap).forEach(item => {
                const type = item.getAttribute('data-inside');
                const isMatch = hasActiveSelection && ((type === activeHeroTopicsInHero) || (type === 'topics' && (activeHeroTopicsInHero === '1' || activeHeroTopicsInHero === 'yes')));
                item.hidden = !isMatch;
            });
            mountHeroInsideContent(activeHeroTopicsInHero, isOnlyHero);
        }

        if (homeTopicsElement) {
            const isTopicsInHero = activeHeroTopicsInHero === 'topics' || activeHeroTopicsInHero === '1' || activeHeroTopicsInHero === 'yes';
            homeTopicsElement.hidden = topicsHidden || (isOnlyHero && isTopicsInHero);
        }

        const homepage = query('#slims-home');
        const hasVisibleHomepageContent = !!homepage && Array.from(homepage.children).some(element =>
            !element.hidden && element.classList.contains('rasamala-home-section')
        );
        queryAll('[data-hero-scroll-indicator]').forEach(element => {
            element.hidden = !(isOnlyHero && hasVisibleHomepageContent);
        });

        Array.from(document.body.classList)
            .filter(name => name.indexOf('rasamala-background-animation-') === 0)
            .forEach(name => document.body.classList.remove(name));

        let layer = query('#background-animation-layer') || query('#hero-animation-layer');
        if (animation === 'none') {
            document.body.classList.remove('rasamala-background-animation-active');
            if (layer) {
                if (typeof layer._rasamalaAnimationCleanup === 'function') {
                    layer._rasamalaAnimationCleanup();
                    layer._rasamalaAnimationCleanup = null;
                }
                while (layer.firstChild) layer.removeChild(layer.firstChild);
                layer.hidden = true;
                layer.setAttribute('data-animation', 'none');
            }
        } else {
            layer = ensureBackgroundAnimationLayer();
            layer.hidden = false;
            Array.from(layer.classList)
                .filter(name => name.indexOf('hero-animation-') === 0 && name !== 'hero-animation-layer')
                .forEach(name => layer.classList.remove(name));
            layer.classList.add(`hero-animation-${animation}`);
            layer.setAttribute('data-animation', animation);
            layer.setAttribute('data-speed-multiplier', backgroundSpeedMultiplier(animationSpeed));
            document.body.classList.add('rasamala-background-animation-active', `rasamala-background-animation-${animation}`);
        }

        document.body.setAttribute('data-cursor-particles', cursorParticles);
        document.body.setAttribute('data-cursor-custom-icon', cursorIcon);
        // A deliberate Theme Viewer selection is an explicit opt-in. Keep the
        // normal server/default configuration respectful of prefers-reduced-
        // motion, but allow the user to preview the selected effect immediately.
        if (persist) {
            document.body.setAttribute('data-cursor-particles-explicit', '1');
            document.body.setAttribute('data-cursor-icon-explicit', '1');
        } else {
            document.body.removeAttribute('data-cursor-particles-explicit');
            document.body.removeAttribute('data-cursor-icon-explicit');
        }

        if (persist) {
            setStoredValue(themeViewerStorageKeys.heroMode, activeHeroMode, configuredHeroMode);
            setStoredValue(themeViewerStorageKeys.heroTopicsInHero, activeHeroTopicsInHero, configuredHeroTopicsInHero);
            setStoredValue(themeViewerStorageKeys.heroBackgroundStyle, activeHeroBackgroundStyle, configuredHeroBackgroundStyle);
            setStoredValue(themeViewerStorageKeys.heroBackgroundCustom, activeHeroBackgroundCustom, configuredHeroBackgroundCustom);
            setStoredValue(themeViewerStorageKeys.heroBackgroundImageSize, activeHeroBackgroundImage.size, configuredHeroBackgroundImage.size);
            setStoredValue(themeViewerStorageKeys.heroBackgroundImagePosition, activeHeroBackgroundImage.position, configuredHeroBackgroundImage.position);
            setStoredValue(themeViewerStorageKeys.heroBackgroundImageFilter, activeHeroBackgroundImage.filter, configuredHeroBackgroundImage.filter);
            setStoredValue(themeViewerStorageKeys.heroBackgroundImageBlur, activeHeroBackgroundImage.blur, configuredHeroBackgroundImage.blur);
            setStoredValue(themeViewerStorageKeys.heroBackgroundImageOverlay, activeHeroBackgroundImage.overlay, configuredHeroBackgroundImage.overlay);
            setStoredValue(themeViewerStorageKeys.homeLayout, activeHomeLayout, configuredHomeLayout);
            setStoredValue(themeViewerStorageKeys.libraryNamePosition, activeLibraryNamePosition, configuredLibraryNamePosition);
            setStoredValue(themeViewerStorageKeys.heroText, activeHeroText, configuredHeroText);
            setStoredValue(themeViewerStorageKeys.heroTextSize, activeHeroTextSize, paletteSwitcherConfig.heroTextSize || 'small');
            setStoredValue(themeViewerStorageKeys.searchSize, activeSearchSize, paletteSwitcherConfig.searchSize || 'medium');
            setStoredValue(themeViewerStorageKeys.searchPlaceholder, activeSearchPlaceholder, paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...');
            setStoredValue(themeViewerStorageKeys.homeInfoShow, activeHomeInfoShow, paletteSwitcherConfig.homeInfoShow || 'show');
            setStoredValue(themeViewerStorageKeys.tickerShow, activeTickerShow, paletteSwitcherConfig.tickerShow || 'show');
            setStoredValue(themeViewerStorageKeys.tickerSpeed, activeTickerSpeed, paletteSwitcherConfig.tickerSpeed || 'slow');
            setStoredValue(themeViewerStorageKeys.searchPanelStyle, activeSearchPanelStyle, paletteSwitcherConfig.searchPanelStyle || 'solid');
            setStoredValue(themeViewerStorageKeys.mobileNavShow, activeMobileNavShow, paletteSwitcherConfig.mobileBottomNavShow || 'show');
            setStoredValue(themeViewerStorageKeys.backToTopShow, activeBackToTopShow, paletteSwitcherConfig.backToTopShow || 'show');
            setStoredValue(themeViewerStorageKeys.mapVisibility, activeMapVisibility, paletteSwitcherConfig.mapVisibility || 'all');
            setStoredValue(themeViewerStorageKeys.font, font, paletteSwitcherConfig.fontFamily || 'system');
            setStoredValue(themeViewerStorageKeys.animation, animation, paletteSwitcherConfig.backgroundAnimation || 'none');
            setStoredValue(themeViewerStorageKeys.animationSpeed, animationSpeed, paletteSwitcherConfig.backgroundAnimationSpeed || 'normal');
            setStoredValue(themeViewerStorageKeys.cursorParticles, cursorParticles, paletteSwitcherConfig.cursorParticles || 'auto');
            setStoredValue(themeViewerStorageKeys.cursorIcon, cursorIcon, paletteSwitcherConfig.cursorIcon || 'default');
            setStoredValue(themeViewerStorageKeys.sectionsHidden, sectionsHidden ? '1' : '', '');
            setStoredValue(themeViewerStorageKeys.hiddenSections, hiddenSections.join(','), '');
        }

        document.dispatchEvent(new CustomEvent('rasamala:theme-viewer-changed', {
            detail: {font, animation, animationSpeed, cursorParticles, cursorIcon, sectionsHidden, hiddenSections, heroMode: activeHeroMode, heroTopicsInHero: activeHeroTopicsInHero, showHeroTopics, backgroundStyle: activeHeroBackgroundStyle, heroBackgroundStyle: activeHeroBackgroundStyle, heroBackgroundCustom: activeHeroBackgroundCustom, backgroundImage: activeHeroBackgroundImage, homeLayout: activeHomeLayout, libraryNamePosition: activeLibraryNamePosition, heroText: activeHeroText, heroTextSize: activeHeroTextSize, searchSize: activeSearchSize, searchPlaceholder: activeSearchPlaceholder, homeInfoShow: activeHomeInfoShow, tickerShow: activeTickerShow, tickerSpeed: activeTickerSpeed, searchPanelStyle: activeSearchPanelStyle, mobileNavShow: activeMobileNavShow, backToTopShow: activeBackToTopShow, mapVisibility: activeMapVisibility}
        }));
        document.dispatchEvent(new CustomEvent('rasamala:cursor-settings-changed', {
            detail: {cursorParticles, cursorIcon}
        }));
    };

    window.RasamalaThemeDrawer = {
        getConfig: getConfig,
        get config() { return getConfig(); },
        storageKeys: themeViewerStorageKeys,
        applyChoice: applyThemeViewerChoice,
        getStoredValue: getStoredValue,
        setStoredValue: setStoredValue
    };

})(window, document);
