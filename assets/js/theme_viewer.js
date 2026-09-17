/*!
 * Rasamala theme - Theme Viewer / Switcher Bundle Entry Point
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const query = (selector, root = document) => root.querySelector(selector);
    const queryAll = (selector, root = document) => Array.prototype.slice.call(root.querySelectorAll(selector));

    const paletteSwitcherConfig = (window.RasamalaPaletteSwitcher && window.RasamalaPaletteSwitcher.config) || null;
    const serverRevisionStorageKey = 'rasamala-theme-server-revision';
    const hashServerConfig = (value) => {
        let hash = 2166136261;
        const input = String(value || '');
        for (let index = 0; index < input.length; index += 1) {
            hash ^= input.charCodeAt(index);
            hash = Math.imul(hash, 16777619);
        }
        return (hash >>> 0).toString(36);
    };
    const serverRevision = paletteSwitcherConfig
        ? hashServerConfig(JSON.stringify({
            currentKey: paletteSwitcherConfig.currentKey || '',
            customValue: paletteSwitcherConfig.customValue || '',
            navbarMenu: paletteSwitcherConfig.navbarMenu || '',
            topicItems: paletteSwitcherConfig.topicItems || '',
            tinfoValues: paletteSwitcherConfig.tinfoValues || {}
        }))
        : '';
    if (!paletteSwitcherConfig || paletteSwitcherConfig.enabled === false) {
        // A disabled Viewer means the server is authoritative.  Clear every
        // draft written by an earlier Viewer session before returning; this
        // prevents a stale browser preference from leaking into a page after
        // an administrator hides the Viewer in TInfo.
        try {
            const storageKeys = [];
            if (window.RasamalaPaletteSwitcher && window.RasamalaPaletteSwitcher.storageKeys) {
                Object.keys(window.RasamalaPaletteSwitcher.storageKeys).forEach(key => {
                    storageKeys.push(window.RasamalaPaletteSwitcher.storageKeys[key]);
                });
            }
            if (window.RasamalaThemeDrawer && window.RasamalaThemeDrawer.storageKeys) {
                Object.keys(window.RasamalaThemeDrawer.storageKeys).forEach(key => {
                    storageKeys.push(window.RasamalaThemeDrawer.storageKeys[key]);
                });
            }
            storageKeys.push(
                'rasamala-theme-palette-key',
                'rasamala-theme-custom-palette',
                'rasamala-theme-tinfo-generic',
                'rasamala-theme-structured-settings',
                serverRevisionStorageKey
            );
            // Also remove keys introduced by future Viewer controls without
            // having to maintain a second allow-list in this disabled path.
            Object.keys(window.localStorage).forEach(key => {
                if (/^rasamala-(?:theme|palette)-/i.test(key)) storageKeys.push(key);
            });
            Array.from(new Set(storageKeys)).forEach(key => window.localStorage.removeItem(key));
        } catch (error) {}
        return;
    }

    // Release-gated controls are absent from the UI and must not be revived
    // by a stale localStorage draft. Runtime values stay at safe defaults until
    // the corresponding feature flag is enabled by the server.
    const featureEnabled = name => {
        const flags = paletteSwitcherConfig.featureFlags || {};
        return flags[name] !== false;
    };

    const wrap = query('#rasamala-palette-switcher');
    const toggle = query('#palette-switcher-toggle');
    const panel = query('#palette-switcher-panel');
    const fullscreenButton = query('#palette-switcher-fullscreen');
    const select = query('#palette-switcher-select');
    const swatches = query('#palette-switcher-swatches');
    const customWrap = query('#palette-switcher-custom');
    const customGroup = query('.palette-switcher-custom-group');
    const customInput = query('#palette-switcher-custom-input');
    const resetButton = query('#palette-switcher-reset');
    const copyPromptButton = query('#palette-switcher-copy-prompt');
    const pastePaletteButton = query('#palette-switcher-paste-palette');
    const copyBackgroundPromptButton = query('#theme-background-copy-prompt');
    const pasteBackgroundButton = query('#theme-background-paste');
    const copyBackgroundImagePromptButton = query('#theme-background-copy-image-prompt');
    const fontSelect = query('#theme-viewer-font-select');
    const animationSelect = query('#theme-viewer-animation-select');
    const animationSpeedSelect = query('#theme-viewer-animation-speed-select');
    const cursorParticlesSelect = query('#theme-viewer-cursor-particles-select');
    const cursorIconSelect = query('#theme-viewer-cursor-icon-select');
    const sectionList = query('#theme-viewer-section-list');
    const showSectionsButton = query('#theme-viewer-show-sections');
    const hideSectionsButton = query('#theme-viewer-hide-sections');
    const saveWrap = query('#theme-viewer-save-wrap');
    const saveButton = query('#theme-viewer-save');
    const saveStatus = query('#theme-viewer-save-status');
    const heroModeSelect = query('#theme-hero-mode-select');
    const heroTopicsSelect = query('#theme-hero-topics-select');
    const heroBackgroundStyleSelect = query('#theme-background-style-select') || query('#theme-hero-background-style-select');
    const heroBackgroundImageOptionsWrap = query('#theme-background-image-options');
    const heroBackgroundImageSizeSelect = query('#theme-background-image-size-select');
    const heroBackgroundImagePositionSelect = query('#theme-background-image-position-select');
    const heroBackgroundImageFilterSelect = query('#theme-background-image-filter-select');
    const heroBackgroundImageBlurSelect = query('#theme-background-image-blur-select');
    const heroBackgroundImageOverlaySelect = query('#theme-background-image-overlay-select');
    const heroBackgroundCustomWrap = query('#theme-background-style-custom');
    const heroBackgroundCustomInput = query('#theme-background-style-custom-input');
    const heroBackgroundOpenSettingsButton = query('#theme-background-open-settings');
    const homeLayoutSelect = query('#theme-home-layout-select');
    const libraryNamePositionSelect = query('#theme-viewer-library-name-position-select');
    const heroTextInput = query('#theme-viewer-hero-text-input');
    const heroTextSizeSelect = query('#theme-viewer-hero-text-size-select');
    const searchSizeSelect = query('#theme-viewer-search-size-select');
    const searchPlaceholderInput = query('#theme-viewer-search-placeholder-input');
    const homeInfoSelect = query('#theme-viewer-home-info-select');
    const tickerSelect = query('#theme-viewer-ticker-select');
    const tickerSpeedSelect = query('#theme-viewer-ticker-speed-select');
    const searchPanelStyleSelect = query('#theme-viewer-search-panel-style-select');
    const mobileNavSelect = query('#theme-viewer-mobile-nav-select');
    const backToTopSelect = query('#theme-viewer-back-to-top-select');
    const mapVisibilitySelect = query('#theme-viewer-map-visibility-select');
    const tinfoGenericWrap = query('#theme-tinfo-generic');
    const tinfoSearchInput = query('#theme-tinfo-search');
    const tinfoEmptyMessage = query('#theme-tinfo-empty');
    const tinfoGenericStorageKey = 'rasamala-theme-tinfo-generic';
    const navbarBuilderRows = query('#theme-viewer-navbar-rows');
    const navbarBuilderAdd = query('#theme-viewer-navbar-add');
    const topicBuilderRows = query('#theme-viewer-topic-rows');
    const topicBuilderAdd = query('#theme-viewer-topic-add');
    const languageBuilderOptions = query('#theme-viewer-language-options');
    const languageBuilderActions = query('#theme-viewer-language-actions');
    const structuredStorageKey = 'rasamala-theme-structured-settings';

    const clearThemeViewerDrafts = () => {
        try {
            const keys = [
                'rasamala-theme-palette-key',
                'rasamala-theme-custom-palette',
                tinfoGenericStorageKey,
                structuredStorageKey,
                serverRevisionStorageKey
            ];
            if (window.RasamalaPaletteSwitcher && window.RasamalaPaletteSwitcher.storageKeys) {
                Object.keys(window.RasamalaPaletteSwitcher.storageKeys).forEach(key => {
                    keys.push(window.RasamalaPaletteSwitcher.storageKeys[key]);
                });
            }
            if (window.RasamalaThemeDrawer && window.RasamalaThemeDrawer.storageKeys) {
                Object.keys(window.RasamalaThemeDrawer.storageKeys).forEach(key => {
                    keys.push(window.RasamalaThemeDrawer.storageKeys[key]);
                });
            }
            Array.from(new Set(keys)).forEach(key => window.localStorage.removeItem(key));
        } catch (error) {}
    };

    // Drafts are useful while the Viewer is open, but they must never mask a
    // newer server setting (for example after an administrator saves from a
    // different browser).  A compact fingerprint of the server-rendered
    // configuration lets us invalidate stale drafts without a second request.
    try {
        const storedRevision = window.localStorage.getItem(serverRevisionStorageKey);
        if (storedRevision !== serverRevision) {
            clearThemeViewerDrafts();
            window.localStorage.setItem(serverRevisionStorageKey, serverRevision);
        }
    } catch (error) {}

    if (!wrap || !toggle || !panel || !select || !customInput || !resetButton) return;

    // Keep Custom Palette visually attached to the Palette selector. It is a
    // contextual editor, not a separate numbered settings section.
    if (customGroup && select) {
        const paletteField = select.closest('.palette-switcher-field');
        if (paletteField) paletteField.appendChild(customGroup);
    }

    const palettes = paletteSwitcherConfig.palettes || {};
    const fillSelect = (element, options) => {
        if (!element || !options) return;
        element.textContent = '';
        Object.keys(options).forEach(key => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = options[key] || key;
            element.appendChild(option);
        });
    };

    fillSelect(heroModeSelect, paletteSwitcherConfig.heroModes);
    fillSelect(heroTopicsSelect, paletteSwitcherConfig.heroTopicsInHero);
    fillSelect(heroBackgroundStyleSelect, paletteSwitcherConfig.heroBackgroundStyles);
    fillSelect(heroBackgroundImageSizeSelect, paletteSwitcherConfig.heroBackgroundImageSizes);
    fillSelect(heroBackgroundImagePositionSelect, paletteSwitcherConfig.heroBackgroundImagePositions);
    fillSelect(heroBackgroundImageFilterSelect, paletteSwitcherConfig.heroBackgroundImageFilters);
    fillSelect(heroBackgroundImageBlurSelect, paletteSwitcherConfig.heroBackgroundImageBlurs);
    fillSelect(heroBackgroundImageOverlaySelect, paletteSwitcherConfig.heroBackgroundImageOverlays);
    fillSelect(homeLayoutSelect, paletteSwitcherConfig.homeLayouts);
    fillSelect(fontSelect, paletteSwitcherConfig.fonts);
    fillSelect(animationSelect, paletteSwitcherConfig.animations);
    fillSelect(animationSpeedSelect, paletteSwitcherConfig.animationSpeeds);
    fillSelect(cursorParticlesSelect, paletteSwitcherConfig.cursorParticlesOptions);
    fillSelect(cursorIconSelect, paletteSwitcherConfig.cursorIcons);
    fillSelect(heroTextSizeSelect, paletteSwitcherConfig.heroTextSizes);
    fillSelect(searchSizeSelect, paletteSwitcherConfig.searchSizes);
    fillSelect(homeInfoSelect, paletteSwitcherConfig.showHideOptions || {show: 'Show', hide: 'Hide'});
    fillSelect(tickerSelect, paletteSwitcherConfig.showHideOptions || {show: 'Show', hide: 'Hide'});
    fillSelect(tickerSpeedSelect, paletteSwitcherConfig.tickerSpeeds);
    fillSelect(searchPanelStyleSelect, paletteSwitcherConfig.searchPanelStyles);
    fillSelect(mobileNavSelect, paletteSwitcherConfig.showHideOptions || {show: 'Show', hide: 'Hide'});
    fillSelect(backToTopSelect, paletteSwitcherConfig.showHideOptions || {show: 'Show', hide: 'Hide'});
    fillSelect(mapVisibilitySelect, paletteSwitcherConfig.mapVisibilityOptions);
    fillSelect(libraryNamePositionSelect, paletteSwitcherConfig.libraryNamePositions);

    const readTinfoGenericSettings = () => {
        const values = {};
        if (!tinfoGenericWrap) return values;
        queryAll('[data-tinfo-field]', tinfoGenericWrap).forEach(field => {
            const name = field.getAttribute('data-tinfo-field');
            if (name) values[name] = field.value == null ? '' : String(field.value);
        });
        return values;
    };
    const syncTinfoGenericControls = (values) => {
        if (!tinfoGenericWrap || !values) return;
        queryAll('[data-tinfo-field]', tinfoGenericWrap).forEach(field => {
            const name = field.getAttribute('data-tinfo-field');
            if (name && Object.prototype.hasOwnProperty.call(values, name)) {
                field.value = values[name] == null ? '' : String(values[name]);
            }
        });
    };

    // TInfo intentionally keeps all option definitions in one place, but a
    // number of fields only make sense when their parent option is active.
    // Mirror the admin customizer's dependency rules here so the public
    // Viewer stays compact and never asks an administrator to configure an
    // option that is currently disabled.
    const viewerSettingControls = {
        classic_theme_color: select,
        classic_palette_custom: customInput,
        classic_hero_fullscreen_mode: heroModeSelect,
        classic_hero_topics_show: heroTopicsSelect,
        classic_hero_background_style: heroBackgroundStyleSelect,
        classic_background_style_custom: heroBackgroundCustomInput,
        classic_background_image_size: heroBackgroundImageSizeSelect,
        classic_background_image_position: heroBackgroundImagePositionSelect,
        classic_background_image_filter: heroBackgroundImageFilterSelect,
        classic_background_image_blur: heroBackgroundImageBlurSelect,
        classic_background_image_overlay: heroBackgroundImageOverlaySelect,
        classic_hero_background_animation: animationSelect,
        classic_background_animation_speed: animationSpeedSelect,
        classic_home_sections_tabs: homeLayoutSelect,
        classic_library_name_position: libraryNamePositionSelect,
        classic_home_display_show: homeInfoSelect,
        classic_ticker_show: tickerSelect,
        classic_ticker_speed: tickerSpeedSelect,
        classic_map: mapVisibilitySelect
    };

    const viewerSettingValue = (name, settings = {}) => {
        const control = viewerSettingControls[name];
        if (control) return String(control.value == null ? '' : control.value);
        if (tinfoGenericWrap) {
            const generic = queryAll('[data-tinfo-field]', tinfoGenericWrap)
                .find(field => field.getAttribute('data-tinfo-field') === name);
            if (generic) return String(generic.value == null ? '' : generic.value);
        }
        if (settings.tinfoGeneric && Object.prototype.hasOwnProperty.call(settings.tinfoGeneric, name)) {
            return String(settings.tinfoGeneric[name] == null ? '' : settings.tinfoGeneric[name]);
        }
        return String((paletteSwitcherConfig.tinfoValues || {})[name] || '');
    };

    const viewerValueIsShown = value => !['', '0', 'false', 'hide', 'none'].includes(String(value || '').toLowerCase());

    const themeSearchAliases = {
        bottom: 'bawah',
        bawah: 'bottom',
        navigation: 'navigasi',
        navigasi: 'navigation',
        nav: 'navigasi',
        background: 'latar',
        latar: 'background',
        image: 'gambar',
        images: 'gambar',
        gambar: 'image',
        color: 'warna',
        colors: 'warna',
        warna: 'color',
        search: 'pencarian',
        pencarian: 'search',
        settings: 'pengaturan',
        setting: 'pengaturan',
        pengaturan: 'settings',
        theme: 'tema',
        tema: 'theme',
        home: 'beranda',
        beranda: 'home',
        topics: 'topik',
        topic: 'topik',
        topik: 'topic',
        map: 'peta',
        peta: 'map',
        social: 'sosial',
        sosial: 'social',
        collection: 'koleksi',
        collections: 'koleksi',
        koleksi: 'collection',
        reader: 'pembaca',
        readers: 'pembaca',
        pembaca: 'reader',
        latest: 'terbaru',
        terbaru: 'latest',
        footer: 'footer',
        back: 'kembali',
        kembali: 'back',
        top: 'atas',
        atas: 'top'
    };

    const normalizeThemeSearchText = value => String(value || '')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();

    // Each query word is represented by alternatives, so an English search
    // such as "bottom navigation" also matches Indonesian labels such as
    // "Navigasi Bawah" without requiring both languages in the UI.
    const themeSearchTokens = value => normalizeThemeSearchText(value)
        .split(/\s+/).filter(Boolean)
        .map(token => [token, themeSearchAliases[token]].filter((candidate, index, candidates) => candidate && candidates.indexOf(candidate) === index));

    const themeSearchHaystack = element => {
        if (!element) return '';
        const values = [element.textContent || '', element.id || ''];
        queryAll('[id], [name], [value], [placeholder], [aria-label], [title], [data-tinfo-field], [data-tinfo-item], select option', element).forEach(child => {
            values.push(
                child.id || '', child.getAttribute('name') || '', child.getAttribute('value') || '',
                child.getAttribute('placeholder') || '', child.getAttribute('aria-label') || '',
                child.getAttribute('title') || '', child.getAttribute('data-tinfo-field') || '',
                child.getAttribute('data-tinfo-item') || '', child.textContent || '', child.value || ''
            );
        });
        return normalizeThemeSearchText(values.join(' '));
    };

    const themeSearchMatches = (element, tokenGroups) => {
        const haystack = themeSearchHaystack(element);
        return tokenGroups.length > 0 && tokenGroups.every(group => group.some(token => haystack.includes(token)));
    };

    const themeControlConditionHidden = element => !!(element && element.closest('[data-theme-condition-hidden]'));

    const refreshTinfoSearch = () => {
        if (!tinfoGenericWrap) return;
        const rawNeedle = String(tinfoSearchInput ? tinfoSearchInput.value || '' : '').trim();
        const tokens = themeSearchTokens(rawNeedle);
        let visibleCount = 0;
        queryAll('.palette-switcher-tinfo-category', tinfoGenericWrap).forEach(category => {
            let categoryCount = 0;
            queryAll('.palette-switcher-tinfo-field', category).forEach(field => {
                const conditionHidden = field.hasAttribute('data-tinfo-condition-hidden');
                const visible = !conditionHidden && (!tokens.length || themeSearchMatches(field, tokens));
                field.hidden = !visible;
                if (visible) categoryCount += 1;
            });
            category.hidden = categoryCount === 0;
            if (categoryCount > 0 && tokens.length) category.open = true;
            visibleCount += categoryCount;
        });
        if (tinfoEmptyMessage) tinfoEmptyMessage.hidden = visibleCount !== 0;
    };

    const filterThemeViewerControls = value => {
        if (!panel) return [];
        const tokens = themeSearchTokens(value);
        const controls = queryAll('.palette-switcher-field, .palette-switcher-builder, .palette-switcher-section-tools, .palette-switcher-advanced-block:not(.palette-switcher-tinfo-block), .palette-switcher-custom, .palette-switcher-image-options', panel)
            .filter(element => element.id !== 'palette-switcher-custom' && !element.closest('#theme-tinfo-generic'));
        const matches = [];
        controls.forEach(control => {
            const conditionHidden = themeControlConditionHidden(control);
            const visible = !conditionHidden && (!tokens.length || themeSearchMatches(control, tokens));
            control.hidden = !visible;
            control.toggleAttribute('data-theme-search-hidden', tokens.length > 0 && !visible);
            if (visible && tokens.length) matches.push(control);
        });
        return matches;
    };

    const themeSearchRoutes = [
        {terms: ['warna', 'palette', 'color'], group: 0, target: () => select},
        {terms: ['font'], group: 0, target: () => fontSelect},
        {terms: ['latar', 'background', 'gambar'], group: 0, target: () => heroBackgroundStyleSelect},
        {terms: ['animasi'], group: 0, target: () => animationSelect},
        {terms: ['kursor'], group: 0, target: () => cursorParticlesSelect || cursorIconSelect},
        {terms: ['judul', 'pencarian', 'search', 'hero', 'placeholder', 'fullscreen'], group: 1, target: () => heroModeSelect},
        {terms: ['section', 'beranda', 'popular', 'collection', 'reader', 'footer', 'map', 'social', 'topic'], group: 2, target: () => homeLayoutSelect},
        {terms: ['navbar', 'bahasa', 'language', 'menu', 'shortcut'], group: 3, target: () => navbarBuilderRows},
        {terms: ['tinfo', 'teknis', 'lanjutan', 'visitor', 'whatsapp', 'librarian'], group: 4, target: () => tinfoGenericWrap}
    ];

    const revealThemeSearchResult = (value) => {
        const needle = String(value || '').trim();
        if (!panel) return;
        // Always reset specialized controls when the query is cleared. This is
        // intentionally done before the early return so a previous search
        // never leaves unrelated controls hidden.
        const specializedMatches = filterThemeViewerControls(needle);
        if (!needle) return;
        const groups = queryAll('.palette-switcher-group', panel);
        const specializedMatch = specializedMatches[0] || null;
        const genericMatch = tinfoGenericWrap
            ? queryAll('.palette-switcher-tinfo-field', tinfoGenericWrap).find(field => {
                if (field.hasAttribute('data-tinfo-condition-hidden')) return false;
                return themeSearchMatches(field, themeSearchTokens(needle));
            })
            : null;
        let group = specializedMatch
            ? specializedMatch.closest('.palette-switcher-group')
            : (genericMatch ? tinfoGenericWrap.closest('.palette-switcher-group') : null);
        let target = specializedMatch || genericMatch;

        if (!group) {
            const route = themeSearchRoutes.find(item => item.terms.some(term => term.includes(needle) || needle.includes(term)));
            if (!route) return;
            group = groups[route.group] || null;
            target = route.target ? route.target() : null;
        }
        if (!group) return;
        groups.forEach(otherGroup => {
            if (otherGroup !== group) otherGroup.removeAttribute('open');
        });
        group.setAttribute('open', 'open');
        queryAll('details', group).forEach(detail => {
            if (target && (detail === target || detail.contains(target))) detail.setAttribute('open', 'open');
        });
        window.setTimeout(() => {
            const element = target && target.nodeType === 1
                ? (target.matches('.palette-switcher-tinfo-field, .palette-switcher-field') ? target : target.closest('.palette-switcher-field, .palette-switcher-tinfo-field'))
                : null;
            if (element && typeof element.scrollIntoView === 'function') {
                element.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }, 60);
    };

    const setTinfoConditionalFields = (names, visible) => {
        if (!tinfoGenericWrap) return;
        const wanted = new Set(names);
        queryAll('.palette-switcher-tinfo-field', tinfoGenericWrap).forEach(field => {
            const input = query('[data-tinfo-field]', field);
            if (!input || !wanted.has(input.getAttribute('data-tinfo-field'))) return;
            const hidden = !visible;
            field.hidden = hidden;
            field.toggleAttribute('data-tinfo-condition-hidden', hidden);
            if (hidden) field.setAttribute('aria-hidden', 'true');
            else field.removeAttribute('aria-hidden');
        });
    };

    const setViewerControlVisibility = (control, visible) => {
        if (!control) return;
        const field = control.closest('.palette-switcher-field');
        if (!field) return;
        field.hidden = !visible;
        field.toggleAttribute('data-theme-condition-hidden', !visible);
        if (!visible) field.setAttribute('aria-hidden', 'true');
        else field.removeAttribute('aria-hidden');
    };

    const syncTinfoConditionalVisibility = (settings = {}) => {
        const heroFullscreen = viewerSettingValue('classic_hero_fullscreen_mode', settings) === 'yes';
        const animation = viewerSettingValue('classic_hero_background_animation', settings);
        const tickerOn = viewerValueIsShown(viewerSettingValue('classic_ticker_show', settings));
        const tickerSource = viewerSettingValue('classic_ticker_source', settings);
        const homeInfoOn = viewerValueIsShown(viewerSettingValue('classic_home_display_show', settings));
        const homeInfoSource = viewerSettingValue('classic_home_display_source', settings);
        const homeCardsOn = viewerValueIsShown(viewerSettingValue('classic_home_content_cards_show', settings));
        const homeCardsSource = viewerSettingValue('classic_home_content_cards_source', settings);
        const mapMode = viewerSettingValue('classic_map', settings);
        const mapVisible = ['1', 'all', 'hide_social'].includes(mapMode);
        const socialVisible = ['1', 'all', 'hide_map'].includes(mapMode);
        const floatingInfo = viewerSettingValue('classic_floating_info', settings);
        const visitorSplit = viewerSettingValue('visitor_layout_style', settings) === 'split';
        const librarianMode = viewerSettingValue('classic_librarian_display_mode', settings);
        const footerOn = viewerValueIsShown(viewerSettingValue('classic_footer_show', settings));
        const libraryNameInHero = viewerSettingValue('classic_library_name_position', settings) === 'hero';

        // Specialized controls live in the compact sections above the generic
        // TInfo list. Hide their dependent controls at the same time.
        setViewerControlVisibility(heroTopicsSelect, heroFullscreen);
        setViewerControlVisibility(heroTextInput, !libraryNameInHero);
        setViewerControlVisibility(heroTextSizeSelect, !libraryNameInHero);
        setViewerControlVisibility(tickerSpeedSelect, tickerOn);
        setViewerControlVisibility(animationSpeedSelect, animation !== 'none');

        setTinfoConditionalFields([
            'classic_whatsapp_number', 'classic_whatsapp_title',
            'classic_service_hours', 'classic_whatsapp_desc',
            'classic_whatsapp_categories'
        ], floatingInfo === 'whatsapp');
        setTinfoConditionalFields([
            'classic_ticker_source', 'classic_ticker_speed',
            'classic_ticker_item_limit', 'classic_ticker_char_limit'
        ], tickerOn);
        setTinfoConditionalFields(['classic_ticker_custom_text'], tickerOn && tickerSource === 'custom_ticker');
        setTinfoConditionalFields([
            'classic_ticker_content_filter', 'classic_ticker_content_detail'
        ], tickerOn && tickerSource === 'content');
        setTinfoConditionalFields(['classic_ticker_biblio_filter'], tickerOn && tickerSource === 'biblio');

        setTinfoConditionalFields([
            'classic_home_display_source', 'classic_home_display_style',
            'classic_home_item_limit', 'classic_home_char_limit'
        ], homeInfoOn);
        setTinfoConditionalFields(['classic_home_display_custom_text'], homeInfoOn && homeInfoSource === 'custom_home');
        setTinfoConditionalFields([
            'classic_home_display_content_filter', 'classic_home_display_content_detail'
        ], homeInfoOn && homeInfoSource === 'content');
        setTinfoConditionalFields(['classic_home_display_biblio_filter'], homeInfoOn && homeInfoSource === 'biblio');

        setTinfoConditionalFields(['classic_home_content_cards_source'], homeCardsOn);
        setTinfoConditionalFields([
            'classic_home_content_path_1', 'classic_home_content_path_2',
            'classic_home_content_path_3'
        ], homeCardsOn && homeCardsSource === 'custom');
        setTinfoConditionalFields(['classic_librarian_custom_usernames'], librarianMode === 'custom');

        setTinfoConditionalFields(['classic_visitor_institution_select_label', 'visitor_institution_select_label'], visitorSplit);
        setTinfoConditionalFields(['visitor_institution_options', 'visitor_split_title', 'visitor_split_steps'], visitorSplit);

        setTinfoConditionalFields(['classic_map_link', 'classic_map_height'], mapVisible);
        setTinfoConditionalFields(['classic_map_desc'], mapVisible || socialVisible);
        setTinfoConditionalFields([
            'classic_fb_link', 'classic_twitter_link', 'classic_youtube_link',
            'classic_instagram_link', 'classic_tiktok_link', 'classic_whatsapp_link',
            'classic_telegram_link', 'classic_linkedin_link'
        ], socialVisible);
        setTinfoConditionalFields([
            'classic_footer_about_us', 'classic_footer_search_show',
            'classic_footer_copyright'
        ], footerOn);
        refreshTinfoSearch();
    };

    const getStoredTinfoGeneric = () => {
        try {
            const raw = window.localStorage.getItem(tinfoGenericStorageKey);
            return raw ? (JSON.parse(raw) || {}) : {};
        } catch (error) {
            return {};
        }
    };
    const persistTinfoGeneric = (values) => {
        try {
            window.localStorage.setItem(tinfoGenericStorageKey, JSON.stringify(values || {}));
        } catch (error) {}
    };
    const applyTinfoGenericPreview = (values) => {
        const settings = values || {};
        const body = document.body;
        if (!body) return;
        // Data attributes let the existing CSS/components opt into the same
        // setting without rebuilding the page. They are harmless when a page
        // does not render the related component.
        Object.keys(settings).forEach(name => {
            if (name.indexOf('classic_') !== 0) return;
            const key = name.slice(8).replace(/_/g, '-');
            body.setAttribute(`data-tinfo-${key}`, String(settings[name]));
        });
        const hideBySetting = (value, hideValues) => hideValues.indexOf(String(value)) !== -1;
        const setHidden = (selectors, hidden) => {
            selectors.forEach(selector => queryAll(selector).forEach(element => {
                element.toggleAttribute('hidden', hidden);
                element.classList.toggle('d-none', hidden);
            }));
        };
        const safeHttpUrl = value => {
            const raw = String(value || '').trim();
            if (!raw) return '';
            try {
                const parsed = new URL(raw, window.location.href);
                return (parsed.protocol === 'http:' || parsed.protocol === 'https:') ? parsed.href : '';
            } catch (error) {
                return '';
            }
        };
        const settingChanged = name => Object.prototype.hasOwnProperty.call(settings, name);

        // Ticker controls: update the already-rendered track immediately.
        // The server still remains responsible for fetching a different data
        // source after Save, but text, limit and character changes are visible
        // without a network request.
        if (settingChanged('classic_ticker_custom_text') || settingChanged('classic_ticker_item_limit') || settingChanged('classic_ticker_char_limit')) {
            const customText = String(settings.classic_ticker_custom_text || '').trim();
            const itemLimit = Math.max(0, parseInt(settings.classic_ticker_item_limit, 10) || 0);
            const charLimit = Math.max(0, parseInt(settings.classic_ticker_char_limit, 10) || 0);
            queryAll('.latest-content-ticker .latest-content-ticker-group').forEach(group => {
                queryAll('.latest-content-ticker-item', group).forEach((item, index) => {
                    item.hidden = itemLimit > 0 && index >= itemLimit;
                    const title = query('.latest-content-title', item);
                    if (!title) return;
                    if (!title.hasAttribute('data-tinfo-original-text')) {
                        title.setAttribute('data-tinfo-original-text', title.textContent || '');
                    }
                    const sourceText = customText || title.getAttribute('data-tinfo-original-text') || '';
                    const visibleText = charLimit > 0 && sourceText.length > charLimit
                        ? `${sourceText.slice(0, charLimit).replace(/\s+$/, '')}...`
                        : sourceText;
                    title.textContent = visibleText;
                    item.setAttribute('title', visibleText);
                });
            });
        }

        // Map, description, and social links are present in the homepage DOM,
        // so their values can be updated safely while the admin types.
        if (settingChanged('classic_map_link')) {
            const mapUrl = safeHttpUrl(settings.classic_map_link);
            queryAll('.rasamala-home-section-map iframe').forEach(frame => {
                if (mapUrl) frame.src = mapUrl;
            });
        }
        if (settingChanged('classic_map_height')) {
            const mapHeight = Math.min(2000, Math.max(100, parseInt(settings.classic_map_height, 10) || 420));
            queryAll('.rasamala-home-section-map iframe').forEach(frame => frame.height = String(mapHeight));
        }
        if (settingChanged('classic_map_desc')) {
            queryAll('.home-map-description').forEach(element => {
                // Use textContent for live typing so a custom description can
                // never inject markup or scripts into the public preview.
                element.textContent = String(settings.classic_map_desc || '');
            });
        }
        const socialDefinitions = {
            classic_fb_link: ['Facebook', 'fab fa-facebook-square'],
            classic_twitter_link: ['Twitter', 'fab fa-twitter-square'],
            classic_youtube_link: ['YouTube', 'fab fa-youtube'],
            classic_instagram_link: ['Instagram', 'fab fa-instagram'],
            classic_tiktok_link: ['TikTok', 'fab fa-tiktok'],
            classic_whatsapp_link: ['WhatsApp', 'fab fa-whatsapp'],
            classic_telegram_link: ['Telegram', 'fab fa-telegram-plane'],
            classic_linkedin_link: ['LinkedIn', 'fab fa-linkedin']
        };
        if (Object.keys(socialDefinitions).some(settingChanged)) {
            queryAll('.home-map-social-links').forEach(container => {
                const links = [];
                Object.keys(socialDefinitions).forEach(name => {
                    const url = safeHttpUrl(settings[name]);
                    if (!url) return;
                    const [label, iconClass] = socialDefinitions[name];
                    const link = document.createElement('a');
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.href = url;
                    link.className = 'btn btn-primary me-2';
                    link.setAttribute('aria-label', `Open ${label} social media`);
                    link.title = label;
                    const icon = document.createElement('i');
                    icon.className = `${iconClass} text-white`;
                    icon.setAttribute('aria-hidden', 'true');
                    link.appendChild(icon);
                    links.push(link);
                });
                container.textContent = '';
                links.forEach(link => container.appendChild(link));
                container.hidden = links.length === 0;
            });
        }

        // WhatsApp modal fields are rendered once in the footer. Reflect title,
        // hours, description, categories and number immediately in that modal.
        if (settingChanged('classic_whatsapp_title')) {
            queryAll('.whatsapp-modal-name span').forEach(element => element.textContent = String(settings.classic_whatsapp_title || ''));
        }
        if (settingChanged('classic_service_hours')) {
            queryAll('.whatsapp-modal-status').forEach(element => element.textContent = String(settings.classic_service_hours || ''));
        }
        if (settingChanged('classic_whatsapp_desc')) {
            const raw = String(settings.classic_whatsapp_desc || '').trim();
            let author = 'Pustakawan';
            let message = raw;
            if (raw.includes(';')) {
                const parts = raw.split(';');
                author = parts.shift().trim();
                message = parts.join(';').trim();
            }
            if (!author) author = 'Pustakawan';
            message = message.replace(new RegExp('^' + author.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*[:;]\\s*', 'i'), '');
            message = message.replace(/^pustakawan\s*[:;]\s*/i, '');
            queryAll('.chat-bubble-author').forEach(element => element.textContent = author);
            queryAll('.chat-bubble-text').forEach(element => element.textContent = message || 'Halo, ada yg bisa kami bantu ?');
        }
        if (settingChanged('classic_whatsapp_number')) {
            const number = String(settings.classic_whatsapp_number || '').replace(/[^0-9]/g, '');
            queryAll('[data-whatsapp-form]').forEach(form => {
                form.setAttribute('data-whatsapp-number', number);
                const button = query('.whatsapp-send-button', form);
                if (button) button.disabled = number === '';
            });
        }
        if (settingChanged('classic_whatsapp_categories')) {
            let raw = String(settings.classic_whatsapp_categories || '').trim();
            let template = raw;
            if (raw.includes(';') || raw.includes(',')) {
                const fields = raw.split(/[;,]+/).map(f => f.trim().replace(/[:.]*$/, '')).filter(Boolean);
                if (fields.length > 0) {
                    template = fields.map(f => f + ':').join('\n');
                }
            }
            queryAll('[data-whatsapp-form] .whatsapp-message-input').forEach(input => {
                input.value = template;
                input.dataset.tinfoPreviewManaged = '1';
            });
        }

        // Navigation and language visibility can be previewed without a page
        // request. Existing member/basket controls are kept intact.
        if (settingChanged('classic_library_subname')) {
            setHidden(['.navbar-lib-subname'], hideBySetting(settings.classic_library_subname, ['0', 'hide', 'none']));
        }
        if (settingChanged('classic_library_name_position')) {
            const position = String(settings.classic_library_name_position || 'navbar') === 'hero' ? 'hero' : 'navbar';
            body.setAttribute('data-library-name-position', position);
            queryAll('.rasamala-navbar-main').forEach(navbar => navbar.classList.toggle('rasamala-navbar-centered', position === 'hero'));
            const heroHeadingWrap = query('.hero-search-heading');
            const heroHeading = query('.hero-search-heading h1');
            const libraryName = query('.navbar-lib-name');
            if (heroHeadingWrap && heroHeading) {
                if (position === 'hero' && libraryName) {
                    heroHeadingWrap.classList.add('hero-search-heading-library');
                    heroHeading.textContent = libraryName.textContent || '';
                } else {
                    heroHeadingWrap.classList.remove('hero-search-heading-library');
                    heroHeading.textContent = heroTextInput ? heroTextInput.value : (paletteSwitcherConfig.heroText || 'Search Library Collection');
                }
                const heroLogo = query('.hero-library-logo-wrap', heroHeadingWrap);
                if (heroLogo) heroLogo.hidden = position !== 'hero';
            }
        }
        if (settingChanged('classic_language_visible_codes')) {
            const visibleCodes = String(settings.classic_language_visible_codes || '')
                .split(/[\s,;]+/).map(code => code.trim().toLowerCase()).filter(Boolean);
            queryAll('#languageMenuButton + .dropdown-menu a[href*="select_lang="]').forEach(link => {
                const match = link.href.match(/[?&]select_lang=([^&#]+)/i);
                const code = match ? decodeURIComponent(match[1]).toLowerCase() : '';
                // An empty selection means “Hide all”, matching the admin
                // language builder instead of silently showing every locale.
                link.hidden = visibleCodes.length === 0 || visibleCodes.indexOf(code) === -1;
            });
        }
        if (settingChanged('classic_navbar_menu')) {
            const navbarMenu = query('.rasamala-navbar-menu');
            if (navbarMenu) {
                queryAll('[data-rasamala-navbar-main-item], .rasamala-theme-viewer-menu-item', navbarMenu).forEach(item => item.remove());
                const firstPersistentItem = navbarMenu.querySelector('li:not([data-rasamala-navbar-main-item]):not(.rasamala-theme-viewer-menu-item)');
                const menuItems = String(settings.classic_navbar_menu || '')
                    .split(';')
                    .map(item => item.trim())
                    .filter(Boolean)
                    .map(item => item.split('|').map(part => part.trim()))
                    .map(parts => ({label: parts[0] || '', url: parts[1] || '#', icon: parts[2] || 'fas fa-link'}))
                    .filter(item => item.label !== '');
                menuItems.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.className = 'nav-item rasamala-theme-viewer-menu-item';
                    const link = document.createElement('a');
                    link.className = 'nav-link';
                    const rawUrl = String(item.url || '#').trim();
                    link.href = (/^(?:https?:\/\/|index\.php(?:[?#]|$)|#)/i.test(rawUrl)) ? rawUrl : '#';
                    const icon = document.createElement('i');
                    icon.className = /^[a-z0-9 _-]+$/i.test(item.icon) ? item.icon : 'fas fa-link';
                    icon.classList.add('navbar-menu-icon');
                    icon.setAttribute('aria-hidden', 'true');
                    const text = document.createElement('span');
                    text.className = 'navbar-menu-label';
                    text.textContent = item.label;
                    link.appendChild(icon);
                    link.appendChild(text);
                    listItem.appendChild(link);
                    if (firstPersistentItem) navbarMenu.insertBefore(listItem, firstPersistentItem);
                    else navbarMenu.appendChild(listItem);
                });
            }
        }

        // Rebuild topic links in-place when the structured Topics editor is
        // changed. This updates both the normal homepage section and the
        // fullscreen-hero template without injecting HTML from user input.
        if (settingChanged('classic_topic_items')) {
            const topicItems = String(settings.classic_topic_items || '')
                .split(/[;\n\r]+/)
                .map(line => line.trim())
                .filter(Boolean)
                .map(line => {
                    const parts = line.split('|');
                    return {
                        label: String(parts.shift() || '').trim(),
                        url: String(parts.shift() || '').trim(),
                        icon: String(parts.join('|') || '').trim()
                    };
                })
                .filter(item => item.label !== '');
            const safeTopicUrl = value => {
                const raw = String(value || '').trim();
                if (!raw || /[\x00-\x1f\x7f]/.test(raw) || /^(?:javascript|data|vbscript):/i.test(raw)) return '#';
                if (raw.charAt(0) === '#') return raw;
                try {
                    const parsed = new URL(raw, window.location.href);
                    return ['http:', 'https:'].indexOf(parsed.protocol) !== -1 ? parsed.href : '#';
                } catch (error) {
                    return '#';
                }
            };
            const safeTopicIcon = value => {
                const raw = String(value || '').trim();
                return /^(?:fa[brs]?|fas|far|fab)\s+[a-z0-9 _-]+$/i.test(raw) ? raw : 'fas fa-th-large';
            };
            const topicLists = [];
            queryAll('.rasamala-home-section-topic ul.topic, #rasamala-hero-inside-topics ul.topic').forEach(list => topicLists.push(list));
            const topicTemplate = query('#rasamala-hero-inside-templates [data-hero-inside-template="topics"]');
            if (topicTemplate && topicTemplate.content) {
                const templateList = topicTemplate.content.querySelector('ul.topic');
                if (templateList) topicLists.push(templateList);
            }
            topicLists.forEach(list => {
                list.textContent = '';
                topicItems.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.className = 'd-flex justify-content-center align-items-center m-2';
                    const link = document.createElement('a');
                    link.className = 'd-flex flex-column';
                    link.href = safeTopicUrl(item.url);
                    link.setAttribute('aria-label', item.label);
                    if (item.url.charAt(0) === '#' && item.url.length > 1) {
                        link.setAttribute('data-bs-toggle', 'modal');
                        link.setAttribute('data-bs-target', item.url);
                    }
                    const icon = document.createElement('i');
                    icon.className = `${safeTopicIcon(item.icon)} topic-icon-fa mb-3 mx-auto`;
                    icon.setAttribute('aria-hidden', 'true');
                    const label = document.createElement('span');
                    label.textContent = item.label;
                    link.appendChild(icon);
                    link.appendChild(label);
                    listItem.appendChild(link);
                    list.appendChild(listItem);
                });
            });
        }

        // News cards can switch between the three existing server-rendered
        // variants instantly. A reload is only needed for cards not rendered
        // in the current page.
        if (settingChanged('classic_news_list_layout')) {
            const layout = ['title_excerpt', 'title_only', 'title_excerpt_thumbnail'].indexOf(String(settings.classic_news_list_layout)) !== -1
                ? String(settings.classic_news_list_layout)
                : 'title_excerpt';
            queryAll('.news-list-card').forEach(card => {
                card.classList.remove('news-list-card--title_excerpt', 'news-list-card--title_only', 'news-list-card--title_excerpt_thumbnail');
                card.classList.add(`news-list-card--${layout}`);
                const summary = query('.news-list-summary', card);
                const thumbnail = query('.news-list-thumbnail', card);
                const readMore = query('.btn-news-readmore', card);
                if (summary) summary.hidden = layout === 'title_only';
                if (thumbnail) thumbnail.hidden = layout !== 'title_excerpt_thumbnail';
                if (readMore) readMore.hidden = layout === 'title_only';
            });
        }

        if (settingChanged('classic_search_result_layout')) {
            body.setAttribute('data-search-result-layout', String(settings.classic_search_result_layout || 'simple'));
            const resultRoot = query('.result-search');
            if (resultRoot) resultRoot.setAttribute('data-search-result-layout', String(settings.classic_search_result_layout || 'simple'));
        }
        if (Object.prototype.hasOwnProperty.call(settings, 'classic_breadcrumbs_show')) {
            setHidden(['.breadcrumb', '.breadcrumb-nav', '[aria-label="breadcrumb"]'], hideBySetting(settings.classic_breadcrumbs_show, ['0', 'hide', 'none']));
        }
        if (Object.prototype.hasOwnProperty.call(settings, 'classic_member_area')) {
            setHidden(['a[href*="p=member"]', '[data-nav-key="member"]'], hideBySetting(settings.classic_member_area, ['0', 'hide', 'none']));
        }
        if (Object.prototype.hasOwnProperty.call(settings, 'classic_floating_info')) {
            const mode = String(settings.classic_floating_info || 'whatsapp');
            setHidden(['#floating-info-btn', '.btn-floating-info', '.rasamala-floating-info'], mode !== 'libinfo');
            setHidden(['#floating-whatsapp-btn', '.btn-floating-whatsapp'], mode !== 'whatsapp');
            body.setAttribute('data-floating-info-mode', mode);
        }
        if (Object.prototype.hasOwnProperty.call(settings, 'classic_color_toggle')) {
            const mode = String(settings.classic_color_toggle || 'auto_show');
            setHidden(['#color-mode-toggle-nav', '#color-mode-toggle-desktop', '#color-mode-toggle'], /hide$/.test(mode));
            const desiredDark = mode.indexOf('dark_') === 0 ? true : (mode.indexOf('light_') === 0 ? false : null);
            if (desiredDark !== null && body.classList.contains('rasamala-dark') !== desiredDark) {
                const colorToggle = query('#color-mode-toggle') || query('#color-mode-toggle-nav') || query('#color-mode-toggle-desktop') || query('#palette-color-mode-toggle');
                if (colorToggle) colorToggle.click();
            }
        }
    };

    const structuredIconOptions = Array.isArray(paletteSwitcherConfig.topicIconOptions)
        ? paletteSwitcherConfig.topicIconOptions
        : [];
    const parseStructuredItems = raw => String(raw || '')
        .split(/[;\n\r]+/)
        .map(line => line.trim())
        .filter(Boolean)
        .map(line => {
            const parts = line.split('|');
            return {
                label: String(parts.shift() || '').trim(),
                url: String(parts.shift() || '').trim(),
                icon: String(parts.join('|') || '').trim()
            };
        })
        .filter(item => item.label !== '' || item.url !== '' || item.icon !== '');
    const cleanStructuredPart = value => String(value || '').replace(/[|;\r\n]+/g, ' ').replace(/\s+/g, ' ').trim();
    const serializeStructuredItems = (rows) => {
        if (!rows) return '';
        return queryAll('[data-builder-row]', rows).map(row => {
            const label = cleanStructuredPart(query('[data-builder-label]', row)?.value);
            const url = String(query('[data-builder-url]', row)?.value || '').trim();
            const icon = cleanStructuredPart(query('[data-builder-icon]', row)?.value);
            if (!label && !url && !icon) return '';
            return `${label} | ${url} | ${icon}`;
        }).filter(Boolean).join(' ; ');
    };
    const iconClassIsSafe = value => /^(?:fa[brs]?|fas|far|fab)\s+[a-z0-9 _-]+$/i.test(String(value || '').trim());
    const buildIconGlyph = (value, className = '') => {
        const icon = document.createElement('i');
        icon.className = `${iconClassIsSafe(value) ? String(value).trim() : 'fas fa-link'} ${className}`.trim();
        icon.setAttribute('aria-hidden', 'true');
        return icon;
    };
    const syncBuilderIconDropdown = row => {
        if (!row) return;
        const selectElement = query('[data-builder-icon]', row);
        const trigger = query('[data-builder-icon-trigger]', row);
        if (!selectElement || !trigger) return;
        const value = String(selectElement.value || '').trim();
        trigger.textContent = '';
        trigger.appendChild(buildIconGlyph(value));
        trigger.title = value || 'Font Awesome icon';
    };
    const closeBuilderIconDropdowns = except => {
        queryAll('[data-builder-icon-menu]').forEach(menu => {
            if (menu !== except) menu.hidden = true;
        });
    };
    const buildIconDropdown = (currentValue, row) => {
        const value = String(currentValue || '').trim();
        const control = document.createElement('div');
        control.className = 'palette-switcher-builder-icon-control';
        const selectElement = document.createElement('select');
        selectElement.className = 'palette-switcher-builder-icon-value';
        selectElement.setAttribute('data-builder-icon', '1');
        selectElement.setAttribute('aria-hidden', 'true');
        selectElement.tabIndex = -1;
        const values = structuredIconOptions.map(optionData => String(optionData.value || '').trim()).filter(Boolean);
        if (value && values.indexOf(value) === -1) values.push(value);
        values.forEach(iconValue => {
            const option = document.createElement('option');
            option.value = iconValue;
            option.textContent = iconValue;
            option.selected = iconValue === value;
            selectElement.appendChild(option);
        });
        if (!selectElement.value && values.length) selectElement.value = values[0];
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'palette-switcher-builder-icon-trigger';
        trigger.setAttribute('data-builder-icon-trigger', '1');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        const menu = document.createElement('div');
        menu.className = 'palette-switcher-builder-icon-menu';
        menu.setAttribute('data-builder-icon-menu', '1');
        menu.hidden = true;
        structuredIconOptions.forEach(optionData => {
            const iconValue = String(optionData.value || '').trim();
            if (!iconValue) return;
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'palette-switcher-builder-icon-option';
            optionButton.setAttribute('data-builder-icon-option', iconValue);
            optionButton.setAttribute('role', 'option');
            optionButton.appendChild(buildIconGlyph(iconValue));
            const optionLabel = document.createElement('span');
            optionLabel.textContent = optionData.label || iconValue;
            optionButton.appendChild(optionLabel);
            menu.appendChild(optionButton);
        });
        if (value && values.indexOf(value) === values.length - 1 && structuredIconOptions.every(optionData => String(optionData.value || '') !== value)) {
            const customOption = document.createElement('button');
            customOption.type = 'button';
            customOption.className = 'palette-switcher-builder-icon-option';
            customOption.setAttribute('data-builder-icon-option', value);
            customOption.setAttribute('role', 'option');
            customOption.appendChild(buildIconGlyph(value));
            const customLabel = document.createElement('span');
            customLabel.textContent = `Custom: ${value}`;
            customOption.appendChild(customLabel);
            menu.appendChild(customOption);
        }
        trigger.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const open = menu.hidden;
            closeBuilderIconDropdowns(menu);
            menu.hidden = !open;
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        menu.addEventListener('click', event => {
            const optionButton = event.target.closest('[data-builder-icon-option]');
            if (!optionButton) return;
            event.preventDefault();
            event.stopPropagation();
            selectElement.value = optionButton.getAttribute('data-builder-icon-option') || '';
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            syncBuilderIconDropdowns(row);
            selectElement.dispatchEvent(new Event('change', {bubbles: true}));
        });
        control.appendChild(selectElement);
        control.appendChild(trigger);
        control.appendChild(menu);
        return control;
    };
    const syncBuilderIconDropdowns = row => {
        if (!row) return;
        syncBuilderIconDropdown(row);
    };
    const addStructuredRow = (rows, item = {}) => {
        if (!rows) return null;
        const row = document.createElement('div');
        row.className = 'palette-switcher-builder-row';
        row.setAttribute('data-builder-row', '1');
        const label = document.createElement('input');
        label.type = 'text';
        label.className = 'form-control palette-switcher-select';
        label.setAttribute('data-builder-label', '1');
        label.placeholder = 'Label';
        label.value = item.label || '';
        const url = document.createElement('input');
        url.type = 'text';
        url.className = 'form-control palette-switcher-select';
        url.setAttribute('data-builder-url', '1');
        url.placeholder = 'URL';
        url.value = item.url || '';
        const iconControl = buildIconDropdown(item.icon || 'fas fa-link', row);

        const actionsWrap = document.createElement('div');
        actionsWrap.className = 'palette-switcher-builder-row-actions d-inline-flex align-items-center gap-1';

        const moveUp = document.createElement('button');
        moveUp.type = 'button';
        moveUp.className = 'palette-switcher-tool-btn palette-switcher-tool-btn-compact';
        moveUp.setAttribute('data-builder-up', '1');
        moveUp.title = 'Naik';
        moveUp.setAttribute('aria-label', 'Naik');
        moveUp.innerHTML = '<i class="fas fa-arrow-up" aria-hidden="true"></i>';

        const moveDown = document.createElement('button');
        moveDown.type = 'button';
        moveDown.className = 'palette-switcher-tool-btn palette-switcher-tool-btn-compact';
        moveDown.setAttribute('data-builder-down', '1');
        moveDown.title = 'Turun';
        moveDown.setAttribute('aria-label', 'Turun');
        moveDown.innerHTML = '<i class="fas fa-arrow-down" aria-hidden="true"></i>';

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'palette-switcher-tool-btn palette-switcher-builder-remove';
        remove.setAttribute('data-builder-remove', '1');
        remove.title = 'Hapus';
        remove.setAttribute('aria-label', 'Hapus');
        remove.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';

        actionsWrap.appendChild(moveUp);
        actionsWrap.appendChild(moveDown);
        actionsWrap.appendChild(remove);

        row.appendChild(label);
        row.appendChild(url);
        row.appendChild(iconControl);
        row.appendChild(actionsWrap);
        rows.appendChild(row);
        syncBuilderIconDropdowns(row);
        return row;
    };
    const renderStructuredBuilder = (rows, rawValue) => {
        if (!rows) return;
        rows.textContent = '';
        const items = parseStructuredItems(rawValue);
        (items.length ? items : [{label: '', url: '', icon: 'fas fa-link'}]).forEach(item => addStructuredRow(rows, item));
    };
    const parseLanguageCodes = value => String(value || '')
        .split(/[,;\s]+/)
        .map(code => code.trim().toLowerCase())
        .filter(Boolean);
    const renderLanguageBuilder = rawValue => {
        if (!languageBuilderOptions) return;
        const selected = parseLanguageCodes(rawValue);
        languageBuilderOptions.textContent = '';
        const options = Array.isArray(paletteSwitcherConfig.languageOptions)
            ? paletteSwitcherConfig.languageOptions
            : [];
        options.forEach(language => {
            const code = String(language.code || '').trim();
            if (!code) return;
            const codeLower = code.toLowerCase();
            const label = document.createElement('label');
            label.className = 'palette-switcher-language-option';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.value = codeLower;
            input.checked = selected.indexOf(codeLower) !== -1;
            input.setAttribute('data-language-code', codeLower);
            const flag = document.createElement('span');
            if (language.flag) flag.className = `flag-icon flag-icon-${String(language.flag).toLowerCase()} flag-icon-rounded`;
            const codeLabel = document.createElement('span');
            codeLabel.className = 'palette-switcher-language-option-code';
            codeLabel.textContent = code.toUpperCase();
            label.appendChild(input);
            label.appendChild(flag);
            label.appendChild(codeLabel);
            languageBuilderOptions.appendChild(label);
        });
    };
    const readLanguageBuilder = () => languageBuilderOptions
        ? queryAll('[data-language-code]:checked', languageBuilderOptions).map(input => input.value).join(', ')
        : '';
    const setLanguageBuilder = value => {
        if (!languageBuilderOptions) return;
        const selected = parseLanguageCodes(value);
        queryAll('[data-language-code]', languageBuilderOptions).forEach(input => {
            input.checked = selected.indexOf(String(input.value).toLowerCase()) !== -1;
        });
    };
    const readStoredStructuredSettings = () => {
        try {
            const raw = window.localStorage.getItem(structuredStorageKey);
            return raw ? (JSON.parse(raw) || {}) : {};
        } catch (error) {
            return {};
        }
    };
    const persistStructuredSettings = values => {
        try {
            window.localStorage.setItem(structuredStorageKey, JSON.stringify(values || {}));
        } catch (error) {}
    };
    const readStructuredSettings = () => ({
        navbarMenu: serializeStructuredItems(navbarBuilderRows),
        topicItems: serializeStructuredItems(topicBuilderRows),
        languageCodes: readLanguageBuilder()
    });
    const syncStructuredControls = values => {
        const settings = values || {};
        if (navbarBuilderRows) renderStructuredBuilder(navbarBuilderRows, settings.navbarMenu || paletteSwitcherConfig.navbarMenu || '');
        if (topicBuilderRows) renderStructuredBuilder(topicBuilderRows, settings.topicItems || paletteSwitcherConfig.topicItems || '');
        if (languageBuilderOptions) renderLanguageBuilder(settings.languageCodes || (paletteSwitcherConfig.tinfoValues || {}).classic_language_visible_codes || '');
    };
    const applyStructuredPreview = () => {
        const structured = readStructuredSettings();
        persistStructuredSettings(structured);
        applyTinfoGenericPreview(Object.assign({}, readTinfoGenericSettings(), {
            classic_navbar_menu: structured.navbarMenu,
            classic_topic_items: structured.topicItems,
            classic_language_visible_codes: structured.languageCodes
        }));
        if (window.RasamalaThemeDrawer && typeof window.RasamalaThemeDrawer.applyChoice === 'function') {
            window.RasamalaThemeDrawer.applyChoice(currentThemeViewerSettings(), true);
        }
    };

    const sectionOptions = Array.isArray(paletteSwitcherConfig.homeSections) ? paletteSwitcherConfig.homeSections : [];
    if (sectionList) {
        sectionList.textContent = '';
        sectionOptions.forEach(section => {
            const label = document.createElement('label');
            const input = document.createElement('input');
            const span = document.createElement('span');
            label.className = 'palette-switcher-section-option';
            input.type = 'checkbox';
            input.value = section.key;
            input.checked = true;
            input.setAttribute('data-theme-viewer-section', section.key);
            span.textContent = section.label || section.key;
            label.appendChild(input);
            label.appendChild(span);
            sectionList.appendChild(label);
        });
    }

    Object.keys(palettes).forEach(key => {
        const item = palettes[key];
        const option = document.createElement('option');
        option.value = key;
        option.textContent = item.label || key;
        select.appendChild(option);

        if (swatches) {
            const swatch = document.createElement('button');
            swatch.type = 'button';
            swatch.className = 'palette-switcher-swatch';
            swatch.setAttribute('data-palette-key', key);
            swatch.setAttribute('aria-label', item.label || key);
            swatch.title = item.label || key;
            swatch.style.setProperty('--swatch-primary', (item.light && item.light.primary) || '#6f5b43');
            swatch.style.setProperty('--swatch-secondary', (item.light && item.light.secondary) || '#a58a63');
            swatch.style.setProperty('--swatch-accent', (item.light && item.light.accent) || '#c8a24a');
            swatches.appendChild(swatch);
        }
    });

    const setPanelOpen = (open) => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        wrap.classList.toggle('is-open', open);
    };

    const syncPanelSizeButtons = () => {
        const fullscreen = panel.classList.contains('is-fullscreen');
        if (fullscreenButton) {
            fullscreenButton.setAttribute('aria-pressed', fullscreen ? 'true' : 'false');
            fullscreenButton.title = fullscreen ? 'Exit fullscreen' : 'Fullscreen';
            fullscreenButton.setAttribute('aria-label', fullscreen ? 'Exit fullscreen' : 'Fullscreen');
            const icon = query('i', fullscreenButton);
            if (icon) icon.className = fullscreen ? 'fas fa-compress' : 'fas fa-expand';
        }
    };

    const setTemporaryButtonLabel = (button, label, delay = 1400) => {
        if (!button || !label) return;
        const textNode = query('span', button);
        if (textNode) {
            const previous = textNode.textContent;
            textNode.textContent = label;
            window.setTimeout(() => { textNode.textContent = previous; }, delay);
        } else {
            const previous = button.getAttribute('title') || '';
            const previousAria = button.getAttribute('aria-label') || '';
            button.setAttribute('title', label);
            button.setAttribute('aria-label', label);
            window.setTimeout(() => {
                button.setAttribute('title', previous);
                button.setAttribute('aria-label', previousAria);
            }, delay);
        }
    };

    const adminThemeEndpoint = String(paletteSwitcherConfig.adminThemeEndpoint || '').trim();
    const adminThemeDir = String(paletteSwitcherConfig.adminThemeDir || 'rasamala').trim() || 'rasamala';
    let adminFormData = null;
    let adminProbePromise = null;

    const adminLabel = (key, fallback) => (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels[key]) || fallback;

    const setSaveStatus = (message, state = '') => {
        if (!saveStatus) return;
        saveStatus.textContent = message || '';
        saveStatus.setAttribute('data-state', state);
    };

    const setSaveVisibility = (visible) => {
        if (saveWrap) saveWrap.hidden = !visible;
        if (saveButton) saveButton.hidden = !visible;
    };

    const copyFormData = (source) => {
        const copy = new FormData();
        if (!source) return copy;
        source.forEach((value, key) => copy.append(key, value));
        return copy;
    };

    // The admin session cookie is path-scoped to /admin. Use the existing
    // protected TInfo form as the authorization probe instead of exposing an
    // admin flag in the public OPAC HTML.
    const probeAdminAccess = () => {
        if (!saveButton || !adminThemeEndpoint) return Promise.resolve(false);
        if (adminProbePromise) return adminProbePromise;

        setSaveVisibility(false);
        setSaveStatus(adminLabel('adminChecking', 'Checking administrator access...'), 'checking');
        const endpoint = new URL(adminThemeEndpoint, window.location.href);
        endpoint.searchParams.set('customize', 'public');
        endpoint.searchParams.set('theme', adminThemeDir);
        adminProbePromise = fetch(endpoint.toString(), {
            credentials: 'include',
            cache: 'no-store',
            headers: {Accept: 'text/html'}
        }).then(response => response.text().then(html => ({response, html})))
            .then(({response, html}) => {
                const documentParser = new DOMParser();
                const parsed = documentParser.parseFromString(html, 'text/html');
                const form = parsed.querySelector('#mainForm');
                // Do not match a generic "p=login" here: the valid TInfo
                // form itself contains a Staff Area menu link with that URL.
                // A valid TInfo page can contain navigation JavaScript that
                // mentions top.location.  Only treat the actual access-denied
                // response as unauthorized.
                const unauthorized = /you are not authorized to view this section|session has timed out/i.test(html);
                if (!response.ok || !form || unauthorized) {
                    adminFormData = null;
                    setSaveVisibility(false);
                    setSaveStatus(adminLabel('adminLoginRequired', 'Log in to the admin area to save theme settings.'), 'unauthorized');
                    return false;
                }

                adminFormData = new FormData(form);
                setSaveVisibility(true);
                setSaveStatus('', 'ready');
                return true;
            })
            .catch(() => {
                adminFormData = null;
                setSaveVisibility(false);
                setSaveStatus(adminLabel('adminLoginRequired', 'Log in to the admin area to save theme settings.'), 'unauthorized');
                return false;
            })
            .finally(() => {
                adminProbePromise = null;
            });

        return adminProbePromise;
    };

    const copyText = (text) => {
        const value = String(text || '');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(value);
        }
        const field = document.createElement('textarea');
        field.value = value;
        field.setAttribute('readonly', 'readonly');
        field.className = 'rasamala-clipboard-fallback';
        document.body.appendChild(field);
        field.select();
        const copied = document.execCommand('copy');
        field.remove();
        return copied ? Promise.resolve() : Promise.reject(new Error('copy failed'));
    };

    const readText = () => {
        if (navigator.clipboard && navigator.clipboard.readText) {
            return navigator.clipboard.readText();
        }
        return Promise.reject(new Error('clipboard unavailable'));
    };

    const syncControls = (key) => {
        select.value = key;
        if (customWrap) {
            customWrap.hidden = key !== 'custom';
        }
        if (customGroup) customGroup.hidden = key !== 'custom';
        if (swatches) {
            queryAll('.palette-switcher-swatch', swatches).forEach(swatch => {
                swatch.classList.toggle('is-active', swatch.getAttribute('data-palette-key') === key);
            });
        }
    };

    const currentThemeViewerSettings = () => {
        const structured = readStructuredSettings();
        return ({
        paletteKey: select ? select.value : (paletteSwitcherConfig.currentKey || 'custom'),
        paletteCustom: customInput ? customInput.value : (paletteSwitcherConfig.customValue || ''),
        heroMode: heroModeSelect ? heroModeSelect.value : (paletteSwitcherConfig.currentHeroMode || 'no'),
        heroTopicsInHero: heroTopicsSelect ? heroTopicsSelect.value : (paletteSwitcherConfig.currentHeroTopicsInHero || 'none'),
        heroBackgroundStyle: heroBackgroundStyleSelect ? heroBackgroundStyleSelect.value : (paletteSwitcherConfig.currentHeroBackgroundStyle || 'none'),
        heroBackgroundCustom: heroBackgroundCustomInput ? heroBackgroundCustomInput.value : (paletteSwitcherConfig.currentHeroBackgroundCustom || paletteSwitcherConfig.heroBackgroundCustomDefault || ''),
        backgroundImage: {
            size: heroBackgroundImageSizeSelect ? heroBackgroundImageSizeSelect.value : ((paletteSwitcherConfig.currentHeroBackgroundImage || {}).size || 'crop'),
            position: heroBackgroundImagePositionSelect ? heroBackgroundImagePositionSelect.value : ((paletteSwitcherConfig.currentHeroBackgroundImage || {}).position || 'center'),
            filter: heroBackgroundImageFilterSelect ? heroBackgroundImageFilterSelect.value : ((paletteSwitcherConfig.currentHeroBackgroundImage || {}).filter || 'none'),
            blur: heroBackgroundImageBlurSelect ? heroBackgroundImageBlurSelect.value : ((paletteSwitcherConfig.currentHeroBackgroundImage || {}).blur || 'none'),
            overlay: heroBackgroundImageOverlaySelect ? heroBackgroundImageOverlaySelect.value : ((paletteSwitcherConfig.currentHeroBackgroundImage || {}).overlay || 'none')
        },
        homeLayout: homeLayoutSelect ? homeLayoutSelect.value : (paletteSwitcherConfig.currentHomeLayout || 'standard'),
        libraryNamePosition: libraryNamePositionSelect ? libraryNamePositionSelect.value : (paletteSwitcherConfig.currentLibraryNamePosition || 'navbar'),
        heroText: heroTextInput ? heroTextInput.value : (paletteSwitcherConfig.heroText || 'Search Library Collection'),
        heroTextSize: heroTextSizeSelect ? heroTextSizeSelect.value : (paletteSwitcherConfig.heroTextSize || 'small'),
        searchSize: searchSizeSelect ? searchSizeSelect.value : (paletteSwitcherConfig.searchSize || 'medium'),
        searchPlaceholder: searchPlaceholderInput ? searchPlaceholderInput.value : (paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...'),
        homeInfoShow: homeInfoSelect ? homeInfoSelect.value : (paletteSwitcherConfig.homeInfoShow || 'show'),
        tickerShow: tickerSelect ? tickerSelect.value : (paletteSwitcherConfig.tickerShow || 'show'),
        tickerSpeed: tickerSpeedSelect ? tickerSpeedSelect.value : (paletteSwitcherConfig.tickerSpeed || 'slow'),
        searchPanelStyle: featureEnabled('panelBackground')
            ? (searchPanelStyleSelect ? searchPanelStyleSelect.value : (paletteSwitcherConfig.searchPanelStyle || 'solid'))
            : 'solid',
        mobileNavShow: mobileNavSelect ? mobileNavSelect.value : (paletteSwitcherConfig.mobileBottomNavShow || 'show'),
        backToTopShow: backToTopSelect ? backToTopSelect.value : (paletteSwitcherConfig.backToTopShow || 'show'),
        mapVisibility: mapVisibilitySelect ? mapVisibilitySelect.value : (paletteSwitcherConfig.mapVisibility || 'all'),
        font: fontSelect ? fontSelect.value : (paletteSwitcherConfig.fontFamily || 'system'),
        animation: animationSelect ? animationSelect.value : (paletteSwitcherConfig.backgroundAnimation || 'none'),
        animationSpeed: animationSpeedSelect ? animationSpeedSelect.value : (paletteSwitcherConfig.backgroundAnimationSpeed || 'normal'),
        cursorParticles: featureEnabled('cursorParticles')
            ? (cursorParticlesSelect ? cursorParticlesSelect.value : (paletteSwitcherConfig.cursorParticles || 'auto'))
            : 'none',
        cursorIcon: featureEnabled('cursorIcon')
            ? (cursorIconSelect ? cursorIconSelect.value : (paletteSwitcherConfig.cursorIcon || 'default'))
            : 'default',
        navbarMenu: structured.navbarMenu || paletteSwitcherConfig.navbarMenu || '',
        topicItems: structured.topicItems || paletteSwitcherConfig.topicItems || '',
        languageCodes: structured.languageCodes || (paletteSwitcherConfig.tinfoValues || {}).classic_language_visible_codes || 'id_ID, en_US',
        tinfoGeneric: readTinfoGenericSettings(),
        sectionsHidden: document.body.classList.contains('rasamala-opac-sections-hidden'),
        hiddenSections: queryAll('[data-theme-viewer-section]', sectionList || document)
            .filter(input => !input.checked)
            .map(input => input.value)
        });
    };

    const syncThemeViewerControls = (settings) => {
        const hiddenSections = settings.sectionsHidden === true || settings.sectionsHidden === '1'
            ? sectionOptions.map(section => section.key)
            : (Array.isArray(settings.hiddenSections) ? settings.hiddenSections : String(settings.hiddenSections || '').split(',').filter(Boolean));
        if (heroModeSelect) {
            const heroModes = paletteSwitcherConfig.heroModes || {};
            const configuredHeroMode = paletteSwitcherConfig.currentHeroMode || 'no';
            const requestedHeroMode = settings.heroMode || configuredHeroMode;
            heroModeSelect.value = Object.prototype.hasOwnProperty.call(heroModes, requestedHeroMode)
                ? requestedHeroMode
                : configuredHeroMode;
        }
        if (heroTopicsSelect) {
            const heroTopicsOptions = paletteSwitcherConfig.heroTopicsInHero || {};
            const configuredHeroTopicsInHero = paletteSwitcherConfig.currentHeroTopicsInHero || 'none';
            const requestedHeroTopicsInHero = settings.heroTopicsInHero || configuredHeroTopicsInHero;
            heroTopicsSelect.value = Object.prototype.hasOwnProperty.call(heroTopicsOptions, requestedHeroTopicsInHero)
                ? requestedHeroTopicsInHero
                : configuredHeroTopicsInHero;
        }
        if (heroBackgroundStyleSelect) {
            const heroBackgroundStyles = paletteSwitcherConfig.heroBackgroundStyles || {};
            const configuredHeroBackgroundStyle = paletteSwitcherConfig.currentHeroBackgroundStyle || 'none';
            const requestedHeroBackgroundStyle = settings.heroBackgroundStyle || configuredHeroBackgroundStyle;
            heroBackgroundStyleSelect.value = Object.prototype.hasOwnProperty.call(heroBackgroundStyles, requestedHeroBackgroundStyle)
                ? requestedHeroBackgroundStyle
                : configuredHeroBackgroundStyle;
        }
        if (heroBackgroundCustomInput) {
            heroBackgroundCustomInput.value = settings.heroBackgroundCustom || paletteSwitcherConfig.currentHeroBackgroundCustom || paletteSwitcherConfig.heroBackgroundCustomDefault || '';
        }
        if (heroBackgroundCustomWrap) {
            const customBackgroundActive = (heroBackgroundStyleSelect ? heroBackgroundStyleSelect.value : settings.heroBackgroundStyle) === 'custom';
            heroBackgroundCustomWrap.hidden = !customBackgroundActive;
            heroBackgroundCustomWrap.toggleAttribute('data-theme-condition-hidden', !customBackgroundActive);
            if (heroBackgroundOpenSettingsButton) heroBackgroundOpenSettingsButton.hidden = customBackgroundActive;
        }
        const activeBackgroundStyle = heroBackgroundStyleSelect ? heroBackgroundStyleSelect.value : settings.heroBackgroundStyle;
        const selectedOptionText = heroBackgroundStyleSelect && heroBackgroundStyleSelect.options && heroBackgroundStyleSelect.selectedIndex >= 0
            ? String(heroBackgroundStyleSelect.options[heroBackgroundStyleSelect.selectedIndex].text || '')
            : '';
        const isImageBackground = !!(
            (paletteSwitcherConfig.heroBackgroundStyleDetails &&
             paletteSwitcherConfig.heroBackgroundStyleDetails[activeBackgroundStyle] &&
             paletteSwitcherConfig.heroBackgroundStyleDetails[activeBackgroundStyle].image === true) ||
            (selectedOptionText.trim().indexOf('Image:') === 0)
        );
        const configuredImageSettings = Object.assign({size: 'crop', position: 'center', filter: 'none', blur: 'none', overlay: 'none'}, paletteSwitcherConfig.currentHeroBackgroundImage || {});
        const imageSettings = settings.backgroundImage || configuredImageSettings;
        if (heroBackgroundImageSizeSelect) heroBackgroundImageSizeSelect.value = imageSettings.size || configuredImageSettings.size;
        if (heroBackgroundImagePositionSelect) heroBackgroundImagePositionSelect.value = imageSettings.position || configuredImageSettings.position;
        if (heroBackgroundImageFilterSelect) heroBackgroundImageFilterSelect.value = imageSettings.filter || configuredImageSettings.filter;
        if (heroBackgroundImageBlurSelect) heroBackgroundImageBlurSelect.value = imageSettings.blur || configuredImageSettings.blur;
        if (heroBackgroundImageOverlaySelect) heroBackgroundImageOverlaySelect.value = imageSettings.overlay || configuredImageSettings.overlay;
        if (heroBackgroundImageOptionsWrap) {
            heroBackgroundImageOptionsWrap.hidden = !isImageBackground;
            heroBackgroundImageOptionsWrap.toggleAttribute('data-theme-condition-hidden', !isImageBackground);
        }
        if (homeLayoutSelect) {
            const homeLayouts = paletteSwitcherConfig.homeLayouts || {};
            const configuredHomeLayout = paletteSwitcherConfig.currentHomeLayout || 'standard';
            const requestedHomeLayout = settings.homeLayout || configuredHomeLayout;
            homeLayoutSelect.value = Object.prototype.hasOwnProperty.call(homeLayouts, requestedHomeLayout)
                ? requestedHomeLayout
                : configuredHomeLayout;
        }
        if (libraryNamePositionSelect) {
            const libraryNamePositions = paletteSwitcherConfig.libraryNamePositions || {};
            const configuredPosition = paletteSwitcherConfig.currentLibraryNamePosition || 'navbar';
            const requestedPosition = settings.libraryNamePosition || configuredPosition;
            libraryNamePositionSelect.value = Object.prototype.hasOwnProperty.call(libraryNamePositions, requestedPosition)
                ? requestedPosition
                : configuredPosition;
        }
        if (heroTextInput) heroTextInput.value = settings.heroText != null ? String(settings.heroText) : String(paletteSwitcherConfig.heroText || 'Search Library Collection');
        if (heroTextSizeSelect) {
            const heroTextSizes = paletteSwitcherConfig.heroTextSizes || {};
            const requested = settings.heroTextSize || paletteSwitcherConfig.heroTextSize || 'small';
            heroTextSizeSelect.value = Object.prototype.hasOwnProperty.call(heroTextSizes, requested) ? requested : (paletteSwitcherConfig.heroTextSize || 'small');
        }
        if (searchSizeSelect) {
            const searchSizes = paletteSwitcherConfig.searchSizes || {};
            const requested = settings.searchSize || paletteSwitcherConfig.searchSize || 'medium';
            searchSizeSelect.value = Object.prototype.hasOwnProperty.call(searchSizes, requested) ? requested : (paletteSwitcherConfig.searchSize || 'medium');
        }
        if (searchPlaceholderInput) searchPlaceholderInput.value = settings.searchPlaceholder != null ? String(settings.searchPlaceholder) : String(paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...');
        const isHiddenSetting = value => ['hide', '0', 'false', 'none', 'off'].includes(String(value == null ? '' : value).trim().toLowerCase());
        if (homeInfoSelect) homeInfoSelect.value = isHiddenSetting(settings.homeInfoShow) ? 'hide' : 'show';
        if (tickerSelect) tickerSelect.value = isHiddenSetting(settings.tickerShow) ? 'hide' : 'show';
        if (tickerSpeedSelect) {
            const tickerSpeeds = paletteSwitcherConfig.tickerSpeeds || {};
            const requested = settings.tickerSpeed || paletteSwitcherConfig.tickerSpeed || 'slow';
            tickerSpeedSelect.value = Object.prototype.hasOwnProperty.call(tickerSpeeds, requested) ? requested : (paletteSwitcherConfig.tickerSpeed || 'slow');
        }
        if (searchPanelStyleSelect) {
            const panelStyles = paletteSwitcherConfig.searchPanelStyles || {};
            const requested = settings.searchPanelStyle || paletteSwitcherConfig.searchPanelStyle || 'solid';
            searchPanelStyleSelect.value = Object.prototype.hasOwnProperty.call(panelStyles, requested) ? requested : (paletteSwitcherConfig.searchPanelStyle || 'solid');
        }
        if (mobileNavSelect) mobileNavSelect.value = isHiddenSetting(settings.mobileNavShow) ? 'hide' : 'show';
        if (backToTopSelect) backToTopSelect.value = isHiddenSetting(settings.backToTopShow) ? 'hide' : 'show';
        if (mapVisibilitySelect) {
            const mapOptions = paletteSwitcherConfig.mapVisibilityOptions || {};
            const requested = settings.mapVisibility || paletteSwitcherConfig.mapVisibility || 'all';
            mapVisibilitySelect.value = Object.prototype.hasOwnProperty.call(mapOptions, requested) ? requested : (paletteSwitcherConfig.mapVisibility || 'all');
        }
        if (fontSelect) fontSelect.value = settings.font;
        if (animationSelect) {
            animationSelect.value = settings.animation;
            if (animationSpeedSelect) {
                animationSpeedSelect.value = settings.animationSpeed;
                animationSpeedSelect.disabled = settings.animation === 'none';
            }
        }
        if (cursorParticlesSelect) cursorParticlesSelect.value = settings.cursorParticles;
        if (cursorIconSelect) cursorIconSelect.value = settings.cursorIcon;
        syncTinfoConditionalVisibility(settings);
        wrap.classList.toggle('is-sections-hidden', hiddenSections.length === sectionOptions.length && hiddenSections.length > 0);
        queryAll('[data-theme-viewer-section]', sectionList || document).forEach(input => {
            input.checked = hiddenSections.indexOf(input.value) === -1;
        });
    };

    const validateBuilderRows = (rows, name) => {
        if (!rows) return null;
        const seen = new Set();
        const rowElements = queryAll('[data-builder-row]', rows);
        for (let i = 0; i < rowElements.length; i += 1) {
            const row = rowElements[i];
            const labelInput = query('[data-builder-label]', row);
            const urlInput = query('[data-builder-url]', row);
            const label = String(labelInput ? labelInput.value : '').trim();
            const url = String(urlInput ? urlInput.value : '').trim();
            if (!label && !url) continue;
            if (!label) return `Baris ${i + 1} pada ${name} harus memiliki Label.`;
            if (!url) return `Baris ${i + 1} pada ${name} harus memiliki URL.`;
            const key = `${label.toLowerCase()}|${url.toLowerCase()}`;
            if (seen.has(key)) return `Entri duplikat pada ${name}: "${label}".`;
            seen.add(key);
        }
        return null;
    };

    const saveThemeSettings = async () => {
        if (!saveButton || !adminThemeEndpoint) return;
        if (!adminFormData && !(await probeAdminAccess())) return;

        const navbarErr = validateBuilderRows(navbarBuilderRows, 'Navbar Menu');
        if (navbarErr) {
            setSaveStatus(navbarErr, 'error');
            return;
        }
        const topicErr = validateBuilderRows(topicBuilderRows, 'Shortcut Topic');
        if (topicErr) {
            setSaveStatus(topicErr, 'error');
            return;
        }

        const settings = currentThemeViewerSettings();
        const formData = copyFormData(adminFormData);
        const setField = (name, value) => formData.set(name, String(value == null ? '' : value));
        const isShownSetting = value => !['hide', '0', 'false', 'none', 'off'].includes(String(value == null ? '' : value).trim().toLowerCase());

        // Keep every field from the real TInfo form so saving the Theme Viewer
        // never clears unrelated administrator settings.
        setField('updateData', '1');
        setField('themeType', 'public');
        setField('themeDir', adminThemeDir);
        setField('classic_theme_color', settings.paletteKey);
        setField('classic_palette_custom', settings.paletteCustom);
        setField('classic_font_family', settings.font);
        setField('classic_hero_fullscreen_mode', settings.heroMode);
        setField('classic_hero_topics_show', settings.heroTopicsInHero);
        setField('classic_hero_background_style', settings.heroBackgroundStyle);
        setField('classic_background_style_custom', settings.heroBackgroundCustom);
        setField('classic_background_image_size', settings.backgroundImage.size);
        setField('classic_background_image_position', settings.backgroundImage.position);
        setField('classic_background_image_filter', settings.backgroundImage.filter);
        setField('classic_background_image_blur', settings.backgroundImage.blur);
        setField('classic_background_image_overlay', settings.backgroundImage.overlay);
        setField('classic_hero_background_animation', settings.animation);
        setField('classic_background_animation_speed', settings.animationSpeed);
        setField('classic_cursor_particles', settings.cursorParticles);
        setField('classic_cursor_custom_icon', settings.cursorIcon);
        setField('classic_navbar_menu', settings.navbarMenu);
        setField('classic_topic_items', settings.topicItems);
        setField('classic_language_visible_codes', settings.languageCodes);
        setField('classic_home_sections_tabs', settings.homeLayout === 'tabs' ? '1' : '0');
        setField('classic_library_name_position', settings.libraryNamePosition);
        setField('classic_hero_text', settings.heroText);
        setField('classic_hero_text_size', settings.heroTextSize);
        setField('classic_search_size', settings.searchSize);
        setField('classic_search_placeholder', settings.searchPlaceholder);
        setField('classic_search_panel_style', settings.searchPanelStyle);
        setField('classic_ticker_speed', settings.tickerSpeed);
        setField('classic_mobile_bottom_nav_show', isShownSetting(settings.mobileNavShow) ? '1' : '0');
        setField('classic_back_to_top', isShownSetting(settings.backToTopShow) ? '1' : '0');

        // The viewer's section checklist mirrors the corresponding TInfo
        // show/hide fields, so an admin can promote the preview to the real
        // homepage without opening every section in TInfo again.
        const hiddenSections = settings.sectionsHidden === true || settings.sectionsHidden === '1'
            ? sectionOptions.map(section => section.key)
            : (Array.isArray(settings.hiddenSections)
                ? settings.hiddenSections
                : String(settings.hiddenSections || '').split(',').filter(Boolean));
        const sectionIsVisible = (key) => hiddenSections.indexOf(key) === -1;
        setField('classic_map', sectionIsVisible('map') ? (settings.mapVisibility || 'all') : 'hide_all');
        setField('classic_home_display_show', sectionIsVisible('hero-info') && isShownSetting(settings.homeInfoShow) ? 'below' : '0');
        setField('classic_ticker_show', sectionIsVisible('ticker') && isShownSetting(settings.tickerShow) ? 'bottom' : '0');
        setField('classic_topic_show', sectionIsVisible('topic') ? '1' : '0');
        setField('classic_home_content_cards_show', sectionIsVisible('news') ? '1' : '0');
        setField('classic_popular_collection', sectionIsVisible('popular') ? '1' : '0');
        setField('classic_new_collection', sectionIsVisible('new-collection') ? '1' : '0');
        setField('classic_top_reader', sectionIsVisible('top-reader') ? '1' : '0');
        setField('classic_footer_show', sectionIsVisible('footer') ? '1' : '0');

        // Every remaining option comes directly from the searchable Advanced
        // TInfo controls. Specialized fields above intentionally stay out of
        // this list so an untouched generic value cannot overwrite a live
        // preview choice with stale data.
        Object.keys(settings.tinfoGeneric || {}).forEach(name => {
            if (name) {
                setField(name, settings.tinfoGeneric[name]);
            }
        });

        // Verify the complete metadata inventory returned by helpers/options.
        // Fields not present in an older admin form are ignored, while every
        // field that was actually submitted must round-trip successfully.
        const allTinfoFields = Array.isArray(paletteSwitcherConfig.tinfoOptions)
            ? paletteSwitcherConfig.tinfoOptions.map(option => String(option.dbfield || '')).filter(Boolean)
            : [];
        const viewerFieldsToVerify = allTinfoFields.filter(name => formData.has(name));

        saveButton.disabled = true;
        setSaveStatus(adminLabel('adminSaving', 'Saving theme settings...'), 'saving');
        try {
            const response = await fetch(adminThemeEndpoint, {
                method: 'POST',
                credentials: 'include',
                cache: 'no-store',
                headers: {
                    Accept: 'text/html',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams(formData)
            });
            const html = await response.text();
            if (!response.ok || /you are not authorized to view this section|session has timed out|error saving|not customizable/i.test(html)) {
                throw new Error('theme save rejected');
            }

            // Read the protected TInfo form back and compare every Viewer field.
            // This catches partial saves, stale sessions, wrong theme paths, and
            // field-name mismatches instead of showing a false success message.
            const verifyEndpoint = new URL(adminThemeEndpoint, window.location.href);
            verifyEndpoint.searchParams.set('customize', 'public');
            verifyEndpoint.searchParams.set('theme', adminThemeDir);
            const verifyResponse = await fetch(verifyEndpoint.toString(), {
                credentials: 'include',
                cache: 'no-store',
                headers: {Accept: 'text/html'}
            });
            const verifyHtml = await verifyResponse.text();
            const verifyDocument = new DOMParser().parseFromString(verifyHtml, 'text/html');
            const verifyForm = verifyDocument.querySelector('#mainForm');
            if (!verifyResponse.ok || !verifyForm || /you are not authorized to view this section|session has timed out/i.test(verifyHtml)) {
                throw new Error('theme save verification unavailable');
            }
            const verifiedFormData = new FormData(verifyForm);
            const normalizeTinfoValue = value => String(value == null ? '' : value).replace(/\r\n/g, '\n').trim();
            const mismatches = viewerFieldsToVerify.filter((name) => {
                const expected = normalizeTinfoValue(formData.get(name));
                const actual = normalizeTinfoValue(verifiedFormData.get(name));
                return expected !== actual;
            });
            // The important Viewer controls are hard persistence checks. A
            // mismatch now stops the reload instead of showing false success;
            // this makes a rejected mobile/nav or homepage setting visible to
            // the administrator so it can be retried safely.
            const criticalFields = [
                'classic_navbar_menu', 'classic_topic_items', 'classic_language_visible_codes',
                'classic_mobile_bottom_nav_show', 'classic_back_to_top',
                'classic_home_display_show', 'classic_ticker_show', 'classic_map',
                'classic_topic_show', 'classic_home_content_cards_show',
                'classic_popular_collection', 'classic_new_collection',
                'classic_top_reader', 'classic_footer_show',
                'classic_theme_color', 'classic_font_family',
                'classic_hero_fullscreen_mode', 'classic_hero_topics_show',
                'classic_hero_background_style', 'classic_hero_background_animation',
                'classic_home_sections_tabs', 'classic_library_name_position',
                'classic_hero_text', 'classic_search_size', 'classic_search_placeholder'
            ];
            const criticalMismatches = mismatches.filter(name => criticalFields.indexOf(name) !== -1);
            if (criticalMismatches.length > 0) {
                throw new Error(`theme save mismatch: ${criticalMismatches.join(', ')}`);
            }
            adminFormData = verifiedFormData;
            setSaveStatus(adminLabel('adminSaved', 'Theme settings saved. Reload the OPAC to apply them permanently.'), 'success');
            // Do not let this browser's temporary draft win over the values
            // just confirmed by the server during the automatic reload.
            clearThemeViewerDrafts();
            // The server has confirmed the complete TInfo round-trip. Reload
            // the current OPAC automatically so PHP-rendered sections, footer,
            // navigation, and background assets immediately use the saved
            // values without requiring a second manual refresh.
            window.setTimeout(() => {
                window.location.reload();
            }, 850);
        } catch (error) {
            // Keep the authenticated form available for a retry.  Hiding the
            // button here made a transient verification/network failure look
            // like the menu had been accepted while nothing was persisted.
            const detail = String(error && error.message || '');
            setSaveStatus(detail.indexOf('theme save mismatch:') === 0
                ? 'Server tidak mengonfirmasi sebagian pengaturan. Periksa sesi admin lalu simpan lagi.'
                : adminLabel('adminSaveError', 'Theme settings could not be saved. Please check your admin session.'), 'error');
        } finally {
            saveButton.disabled = false;
        }
    };

    let customPaletteApplyTimer = 0;
    let customBackgroundApplyTimer = 0;

    const applyCustomPaletteValue = (value, persist = true, shouldFocus = false, allowFallback = false) => {
        const sanitizedValue = window.RasamalaPaletteSwitcher.sanitizeInput(value);
        if (!sanitizedValue && !allowFallback) {
            return false;
        }

        const fallback = palettes.custom || palettes[paletteSwitcherConfig.currentKey] || palettes.warmgray || {};
        const fallbackValue = window.RasamalaPaletteSwitcher.sanitizeInput(paletteSwitcherConfig.customValue || '');
        const sourceValue = sanitizedValue || fallbackValue;
        const cleanValue = window.RasamalaPaletteSwitcher.normalizeInput(sourceValue, fallback.light || {}, fallback.dark || {});

        customInput.value = cleanValue;
        select.value = 'custom';
        syncControls('custom');
        window.RasamalaPaletteSwitcher.applyChoice('custom', cleanValue, persist);
        window.RasamalaThemeDrawer.applyChoice(currentThemeViewerSettings(), persist);
        if (shouldFocus) {
            customInput.focus();
        }
        return true;
    };

    const scheduleApplyCustomPaletteFromInput = (delay = 40) => {
        window.clearTimeout(customPaletteApplyTimer);
        customPaletteApplyTimer = window.setTimeout(() => {
            applyCustomPaletteValue(customInput.value, true, false);
        }, delay);
    };

    let initialKey = paletteSwitcherConfig.currentKey || 'warmgray';
    let initialCustom = window.RasamalaPaletteSwitcher.sanitizeInput(paletteSwitcherConfig.customValue || '');
    try {
        initialKey = window.localStorage.getItem(window.RasamalaPaletteSwitcher.storageKeys.key) || initialKey;
        initialCustom = window.RasamalaPaletteSwitcher.sanitizeInput(window.localStorage.getItem(window.RasamalaPaletteSwitcher.storageKeys.custom) || initialCustom);
    } catch (error) {}
    if (!palettes[initialKey]) {
        initialKey = paletteSwitcherConfig.currentKey || 'warmgray';
    }

    // Navbar and homepage topics are edited as rows (label, URL, icon) rather
    // than as an opaque pipe-delimited string. Keep a small local draft so a
    // live preview survives opening/closing the viewer without touching the
    // saved TInfo value until the administrator presses Save.
    const storedStructuredSettings = readStoredStructuredSettings();
    const initialStructuredSettings = {
        navbarMenu: storedStructuredSettings.navbarMenu || paletteSwitcherConfig.navbarMenu || '',
        topicItems: storedStructuredSettings.topicItems || paletteSwitcherConfig.topicItems || '',
        languageCodes: storedStructuredSettings.languageCodes || (paletteSwitcherConfig.tinfoValues || {}).classic_language_visible_codes || 'id_ID, en_US'
    };
    syncStructuredControls(initialStructuredSettings);

    customInput.value = initialCustom;
    syncControls(initialKey);
    window.RasamalaPaletteSwitcher.applyChoice(initialKey, initialCustom, false);

    // Start the Viewer from the server-rendered TInfo values. Previously
    // several controls defaulted to "show"/"all" here, so a saved Hide choice
    // was immediately overwritten by the live preview on the next reload.
    const serverTinfoValues = paletteSwitcherConfig.tinfoValues || {};
    const initialHiddenSections = [];
    const hideSectionWhenZero = {
        topic: 'classic_topic_show',
        news: 'classic_home_content_cards_show',
        popular: 'classic_popular_collection',
        'new-collection': 'classic_new_collection',
        'top-reader': 'classic_top_reader',
        'hero-info': 'classic_home_display_show',
        ticker: 'classic_ticker_show',
        footer: 'classic_footer_show'
    };
    Object.keys(hideSectionWhenZero).forEach(sectionKey => {
        const fieldName = hideSectionWhenZero[sectionKey];
        if (['0', 'false', 'hide', 'none'].includes(String(serverTinfoValues[fieldName] == null ? '' : serverTinfoValues[fieldName]).trim().toLowerCase())) {
            initialHiddenSections.push(sectionKey);
        }
    });
    if (String(serverTinfoValues.classic_map || '').trim().toLowerCase() === 'hide_all') {
        initialHiddenSections.push('map');
    }

    const initialThemeSettings = {
        heroMode: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroMode, paletteSwitcherConfig.currentHeroMode || 'no'),
        heroTopicsInHero: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroTopicsInHero, paletteSwitcherConfig.currentHeroTopicsInHero || 'none'),
        heroBackgroundStyle: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundStyle, paletteSwitcherConfig.currentHeroBackgroundStyle || 'none'),
        heroBackgroundCustom: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundCustom, paletteSwitcherConfig.currentHeroBackgroundCustom || paletteSwitcherConfig.heroBackgroundCustomDefault || ''),
        backgroundImage: {
            size: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundImageSize, (paletteSwitcherConfig.currentHeroBackgroundImage || {}).size || 'crop'),
            position: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundImagePosition, (paletteSwitcherConfig.currentHeroBackgroundImage || {}).position || 'center'),
            filter: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundImageFilter, (paletteSwitcherConfig.currentHeroBackgroundImage || {}).filter || 'none'),
            blur: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundImageBlur, (paletteSwitcherConfig.currentHeroBackgroundImage || {}).blur || 'none'),
            overlay: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroBackgroundImageOverlay, (paletteSwitcherConfig.currentHeroBackgroundImage || {}).overlay || 'none')
        },
        homeLayout: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.homeLayout, paletteSwitcherConfig.currentHomeLayout || 'standard'),
        libraryNamePosition: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.libraryNamePosition, paletteSwitcherConfig.currentLibraryNamePosition || 'navbar'),
        heroText: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroText, paletteSwitcherConfig.heroText || 'Search Library Collection'),
        heroTextSize: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.heroTextSize, paletteSwitcherConfig.heroTextSize || 'small'),
        searchSize: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.searchSize, paletteSwitcherConfig.searchSize || 'medium'),
        searchPlaceholder: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.searchPlaceholder, paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...'),
        // Respect the server value on first load. The section checklist and
        // live controls can still be changed for preview, but a saved Hide
        // choice must not be overwritten by a hard-coded Viewer default.
        homeInfoShow: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.homeInfoShow, paletteSwitcherConfig.homeInfoShow || 'show'),
        tickerShow: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.tickerShow, paletteSwitcherConfig.tickerShow || 'show'),
        tickerSpeed: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.tickerSpeed, paletteSwitcherConfig.tickerSpeed || 'slow'),
        searchPanelStyle: featureEnabled('panelBackground')
            ? window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.searchPanelStyle, paletteSwitcherConfig.searchPanelStyle || 'solid')
            : 'solid',
        mobileNavShow: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.mobileNavShow, paletteSwitcherConfig.mobileBottomNavShow || 'show'),
        backToTopShow: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.backToTopShow, paletteSwitcherConfig.backToTopShow || 'show'),
        mapVisibility: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.mapVisibility, paletteSwitcherConfig.mapVisibility || 'all'),
        font: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.font, paletteSwitcherConfig.fontFamily || 'system'),
        animation: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.animation, paletteSwitcherConfig.backgroundAnimation || 'none'),
        animationSpeed: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.animationSpeed, paletteSwitcherConfig.backgroundAnimationSpeed || 'normal'),
        cursorParticles: featureEnabled('cursorParticles')
            ? window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.cursorParticles, paletteSwitcherConfig.cursorParticles || 'auto')
            : 'none',
        cursorIcon: featureEnabled('cursorIcon')
            ? window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.cursorIcon, paletteSwitcherConfig.cursorIcon || 'default')
            : 'default',
        navbarMenu: initialStructuredSettings.navbarMenu,
        topicItems: initialStructuredSettings.topicItems,
        languageCodes: initialStructuredSettings.languageCodes,
        tinfoGeneric: Object.assign({}, paletteSwitcherConfig.tinfoValues || {}, getStoredTinfoGeneric()),
        sectionsHidden: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.sectionsHidden, '') === '1',
        hiddenSections: window.RasamalaThemeDrawer.getStoredValue(window.RasamalaThemeDrawer.storageKeys.hiddenSections, initialHiddenSections.join(','))
    };
    syncTinfoGenericControls(initialThemeSettings.tinfoGeneric);
    applyTinfoGenericPreview(Object.assign({}, initialThemeSettings.tinfoGeneric, {
        classic_library_name_position: initialThemeSettings.libraryNamePosition,
        classic_navbar_menu: initialThemeSettings.navbarMenu,
        classic_topic_items: initialThemeSettings.topicItems,
        classic_language_visible_codes: initialThemeSettings.languageCodes
    }));
    syncThemeViewerControls(initialThemeSettings);
    window.RasamalaThemeDrawer.applyChoice(initialThemeSettings, false);

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        const open = panel.hidden;
        setPanelOpen(open);
        if (open) probeAdminAccess();
    });

    // Keep interactions inside the viewer from reaching the document-level
    // outside-click handler. This prevents remove/add/icon actions from
    // unexpectedly closing the panel while the preview is being rebuilt.
    panel.addEventListener('click', event => {
        if (event.target.closest('[data-palette-close]')) setPanelOpen(false);
        if (!event.target.closest('[data-builder-icon-control]')) closeBuilderIconDropdowns(null);
        event.stopPropagation();
    });

    if (fullscreenButton) {
        fullscreenButton.addEventListener('click', event => {
            event.preventDefault();
            panel.classList.toggle('is-fullscreen');
            syncPanelSizeButtons();
        });
    }
    syncPanelSizeButtons();

    select.addEventListener('change', () => {
        const key = select.value;
        syncControls(key);
        if (key === 'custom') {
            applyCustomPaletteValue(customInput.value, true, false, true);
        } else {
            window.RasamalaPaletteSwitcher.applyChoice(key, customInput.value, true);
        }
    });

    if (swatches) {
        swatches.addEventListener('click', (event) => {
            const swatch = event.target.closest('.palette-switcher-swatch');
            if (!swatch) return;
            const key = swatch.getAttribute('data-palette-key');
            syncControls(key);
            if (key === 'custom') {
                applyCustomPaletteValue(customInput.value, true, false, true);
            } else {
                window.RasamalaPaletteSwitcher.applyChoice(key, customInput.value, true);
            }
        });
    }

    customInput.addEventListener('change', () => {
        if (select.value !== 'custom') return;
        applyCustomPaletteValue(customInput.value, true);
    });

    customInput.addEventListener('paste', () => {
        select.value = 'custom';
        syncControls('custom');
        scheduleApplyCustomPaletteFromInput();
    });

    customInput.addEventListener('input', (event) => {
        if (!event.inputType || event.inputType.indexOf('insertFrom') !== 0) return;
        select.value = 'custom';
        syncControls('custom');
        scheduleApplyCustomPaletteFromInput();
    });

    resetButton.addEventListener('click', () => {
        try {
            window.localStorage.removeItem(window.RasamalaThemeDrawer.storageKeys.heroMode);
            window.localStorage.removeItem(window.RasamalaPaletteSwitcher.storageKeys.key);
            window.localStorage.removeItem(window.RasamalaPaletteSwitcher.storageKeys.custom);
            window.localStorage.removeItem(tinfoGenericStorageKey);
            window.localStorage.removeItem(structuredStorageKey);
            Object.keys(window.RasamalaThemeDrawer.storageKeys).forEach(key => window.localStorage.removeItem(window.RasamalaThemeDrawer.storageKeys[key]));
        } catch (error) {}
        const key = paletteSwitcherConfig.currentKey || 'warmgray';
        customInput.value = window.RasamalaPaletteSwitcher.sanitizeInput(paletteSwitcherConfig.customValue || '');
        syncControls(key);
        window.RasamalaPaletteSwitcher.applyChoice(key, customInput.value, false);
        const fallbackThemeSettings = {
            heroMode: paletteSwitcherConfig.currentHeroMode || 'no',
            heroTopicsInHero: paletteSwitcherConfig.currentHeroTopicsInHero || 'none',
            heroBackgroundStyle: paletteSwitcherConfig.currentHeroBackgroundStyle || 'none',
            heroBackgroundCustom: paletteSwitcherConfig.currentHeroBackgroundCustom || paletteSwitcherConfig.heroBackgroundCustomDefault || '',
            backgroundImage: Object.assign({size: 'crop', position: 'center', filter: 'none', blur: 'none', overlay: 'none'}, paletteSwitcherConfig.currentHeroBackgroundImage || {}),
            homeLayout: paletteSwitcherConfig.currentHomeLayout || 'standard',
            libraryNamePosition: paletteSwitcherConfig.currentLibraryNamePosition || 'navbar',
            heroText: paletteSwitcherConfig.heroText || 'Search Library Collection',
            heroTextSize: paletteSwitcherConfig.heroTextSize || 'small',
            searchSize: paletteSwitcherConfig.searchSize || 'medium',
            searchPlaceholder: paletteSwitcherConfig.searchPlaceholder || 'Enter keyword to search collection...',
            homeInfoShow: paletteSwitcherConfig.homeInfoShow || 'show',
            tickerShow: paletteSwitcherConfig.tickerShow || 'show',
            tickerSpeed: paletteSwitcherConfig.tickerSpeed || 'slow',
            searchPanelStyle: featureEnabled('panelBackground') ? (paletteSwitcherConfig.searchPanelStyle || 'solid') : 'solid',
            mobileNavShow: paletteSwitcherConfig.mobileBottomNavShow || 'show',
            backToTopShow: paletteSwitcherConfig.backToTopShow || 'show',
            mapVisibility: paletteSwitcherConfig.mapVisibility || 'all',
            font: paletteSwitcherConfig.fontFamily || 'system',
            animation: paletteSwitcherConfig.backgroundAnimation || 'none',
            animationSpeed: paletteSwitcherConfig.backgroundAnimationSpeed || 'normal',
            cursorParticles: featureEnabled('cursorParticles') ? (paletteSwitcherConfig.cursorParticles || 'auto') : 'none',
            cursorIcon: featureEnabled('cursorIcon') ? (paletteSwitcherConfig.cursorIcon || 'default') : 'default',
            navbarMenu: paletteSwitcherConfig.navbarMenu || '',
            topicItems: paletteSwitcherConfig.topicItems || '',
            languageCodes: (paletteSwitcherConfig.tinfoValues || {}).classic_language_visible_codes || 'id_ID, en_US',
            tinfoGeneric: Object.assign({}, paletteSwitcherConfig.tinfoValues || {}),
            sectionsHidden: false,
            hiddenSections: []
        };
        syncStructuredControls(fallbackThemeSettings);
        syncTinfoGenericControls(fallbackThemeSettings.tinfoGeneric);
        applyTinfoGenericPreview(Object.assign({}, fallbackThemeSettings.tinfoGeneric, {
            classic_library_name_position: fallbackThemeSettings.libraryNamePosition,
            classic_navbar_menu: fallbackThemeSettings.navbarMenu,
            classic_topic_items: fallbackThemeSettings.topicItems,
            classic_language_visible_codes: fallbackThemeSettings.languageCodes
        }));
        syncThemeViewerControls(fallbackThemeSettings);
        window.RasamalaThemeDrawer.applyChoice(fallbackThemeSettings, false);
    });

    if (saveButton) {
        saveButton.addEventListener('click', (event) => {
            event.preventDefault();
            saveThemeSettings();
        });
    }

    if (heroModeSelect) {
        heroModeSelect.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (heroTopicsSelect) {
        heroTopicsSelect.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (heroBackgroundStyleSelect) {
        heroBackgroundStyleSelect.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (heroBackgroundOpenSettingsButton) {
        heroBackgroundOpenSettingsButton.addEventListener('click', () => {
            const groups = queryAll('.palette-switcher-group', panel);
            const identityGroup = groups[0];
            if (identityGroup) {
                groups.forEach(group => { if (group !== identityGroup) group.removeAttribute('open'); });
                identityGroup.setAttribute('open', 'open');
                identityGroup.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
            if (heroBackgroundStyleSelect) heroBackgroundStyleSelect.focus();
        });
    }

    [heroBackgroundImageSizeSelect, heroBackgroundImagePositionSelect, heroBackgroundImageFilterSelect, heroBackgroundImageBlurSelect, heroBackgroundImageOverlaySelect].forEach(control => {
        if (!control) return;
        control.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    });

    if (heroBackgroundCustomInput) {
        heroBackgroundCustomInput.addEventListener('input', () => {
            window.clearTimeout(customBackgroundApplyTimer);
            customBackgroundApplyTimer = window.setTimeout(() => {
                const settings = currentThemeViewerSettings();
                window.RasamalaThemeDrawer.applyChoice(settings, true);
            }, 120);
        });
        heroBackgroundCustomInput.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (homeLayoutSelect) {
        homeLayoutSelect.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (libraryNamePositionSelect) {
        libraryNamePositionSelect.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            applyTinfoGenericPreview(Object.assign({}, settings.tinfoGeneric, {
                classic_library_name_position: settings.libraryNamePosition
            }));
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    const applyContentPreview = () => {
        const settings = currentThemeViewerSettings();
        syncThemeViewerControls(settings);
        window.RasamalaThemeDrawer.applyChoice(settings, true);
    };

    [heroTextSizeSelect, searchSizeSelect, homeInfoSelect, tickerSelect, tickerSpeedSelect, searchPanelStyleSelect, mobileNavSelect, backToTopSelect, mapVisibilitySelect].forEach(control => {
        if (!control) return;
        control.addEventListener('change', applyContentPreview);
    });
    [heroTextInput, searchPlaceholderInput].forEach(control => {
        if (!control) return;
        control.addEventListener('input', () => {
            window.clearTimeout(control._rasamalaPreviewTimer);
            control._rasamalaPreviewTimer = window.setTimeout(applyContentPreview, 100);
        });
        control.addEventListener('change', applyContentPreview);
    });

    if (tinfoGenericWrap) {
        tinfoGenericWrap.addEventListener('input', event => {
            if (!event.target.matches('[data-tinfo-field]')) return;
            const values = readTinfoGenericSettings();
            persistTinfoGeneric(values);
            applyTinfoGenericPreview(values);
            syncThemeViewerControls(currentThemeViewerSettings());
            if (window.RasamalaThemeDrawer && typeof window.RasamalaThemeDrawer.applyChoice === 'function') {
                window.RasamalaThemeDrawer.applyChoice(currentThemeViewerSettings(), true);
            }
        });
        tinfoGenericWrap.addEventListener('change', event => {
            if (!event.target.matches('[data-tinfo-field]')) return;
            const values = readTinfoGenericSettings();
            persistTinfoGeneric(values);
            applyTinfoGenericPreview(values);
            syncThemeViewerControls(currentThemeViewerSettings());
            if (window.RasamalaThemeDrawer && typeof window.RasamalaThemeDrawer.applyChoice === 'function') {
                window.RasamalaThemeDrawer.applyChoice(currentThemeViewerSettings(), true);
            }
        });
    }

    // Structured editors use delegated listeners so newly-added rows behave
    // exactly like the initial rows. Typing a label/URL or choosing an icon
    // immediately updates the public navbar/topic preview.
    const handleStructuredInput = event => {
        if (!event.target.matches('[data-builder-label], [data-builder-url], [data-builder-icon], [data-language-code]')) return;
        event.stopPropagation();
        if (event.target.matches('[data-builder-icon]')) {
            syncBuilderIconDropdowns(event.target.closest('[data-builder-row]'));
        }
        applyStructuredPreview();
    };
    const handleStructuredClick = event => {
        event.stopPropagation();
        const up = event.target.closest('[data-builder-up]');
        if (up) {
            const row = up.closest('[data-builder-row]');
            if (row && row.previousElementSibling) {
                row.parentNode.insertBefore(row, row.previousElementSibling);
                applyStructuredPreview();
            }
            return;
        }
        const down = event.target.closest('[data-builder-down]');
        if (down) {
            const row = down.closest('[data-builder-row]');
            if (row && row.nextElementSibling) {
                row.parentNode.insertBefore(row.nextElementSibling, row);
                applyStructuredPreview();
            }
            return;
        }
        const remove = event.target.closest('[data-builder-remove]');
        if (!remove) return;
        const row = remove.closest('[data-builder-row]');
        if (row) row.remove();
        applyStructuredPreview();
    };
    [navbarBuilderRows, topicBuilderRows].forEach(rows => {
        if (!rows) return;
        rows.addEventListener('input', handleStructuredInput);
        rows.addEventListener('change', handleStructuredInput);
        rows.addEventListener('click', handleStructuredClick);
    });
    if (languageBuilderOptions) {
        languageBuilderOptions.addEventListener('change', handleStructuredInput);
    }
    if (languageBuilderActions && languageBuilderOptions) {
        languageBuilderActions.addEventListener('click', event => {
            const action = event.target.closest('[data-language-action]');
            if (!action) return;
            const mode = action.getAttribute('data-language-action');
            if (mode === 'all') {
                queryAll('[data-language-code]', languageBuilderOptions).forEach(input => { input.checked = true; });
            } else if (mode === 'none') {
                queryAll('[data-language-code]', languageBuilderOptions).forEach(input => { input.checked = false; });
            } else if (mode === 'id-en') {
                const preferred = ['id_id', 'id', 'en_us', 'en_gb', 'en'];
                queryAll('[data-language-code]', languageBuilderOptions).forEach(input => {
                    input.checked = preferred.indexOf(String(input.value).toLowerCase()) !== -1;
                });
            }
            applyStructuredPreview();
        });
    }
    if (navbarBuilderAdd && navbarBuilderRows) {
        navbarBuilderAdd.addEventListener('click', () => {
            addStructuredRow(navbarBuilderRows, {label: '', url: 'index.php', icon: 'fas fa-link'});
            applyStructuredPreview();
        });
    }
    if (topicBuilderAdd && topicBuilderRows) {
        topicBuilderAdd.addEventListener('click', () => {
            addStructuredRow(topicBuilderRows, {label: '', url: '', icon: 'fas fa-th-large'});
            applyStructuredPreview();
        });
    }
    if (tinfoSearchInput) {
        tinfoSearchInput.addEventListener('input', () => {
            refreshTinfoSearch();
            revealThemeSearchResult(tinfoSearchInput.value);
        });
    }

    [fontSelect, animationSelect, animationSpeedSelect, cursorParticlesSelect, cursorIconSelect].forEach(control => {
        if (!control) return;
        control.addEventListener('change', () => {
            const settings = currentThemeViewerSettings();
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    });

    if (showSectionsButton) {
        showSectionsButton.addEventListener('click', () => {
            const settings = Object.assign(currentThemeViewerSettings(), {sectionsHidden: false, hiddenSections: []});
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (hideSectionsButton) {
        hideSectionsButton.addEventListener('click', () => {
            const settings = Object.assign(currentThemeViewerSettings(), {
                sectionsHidden: true,
                hiddenSections: sectionOptions.map(section => section.key)
            });
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (sectionList) {
        sectionList.addEventListener('change', (event) => {
            if (!event.target.matches('[data-theme-viewer-section]')) return;
            const settings = Object.assign(currentThemeViewerSettings(), {sectionsHidden: false});
            syncThemeViewerControls(settings);
            window.RasamalaThemeDrawer.applyChoice(settings, true);
        });
    }

    if (copyPromptButton) {
        copyPromptButton.addEventListener('click', () => {
            copyText(paletteSwitcherConfig.prompt || '').then(() => {
                setTemporaryButtonLabel(copyPromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.promptCopied) || 'Copied');
            }).catch(() => {
                setTemporaryButtonLabel(copyPromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
            });
        });
    }

    if (pastePaletteButton) {
        pastePaletteButton.addEventListener('click', () => {
            readText().then((clipboardText) => {
                if (!applyCustomPaletteValue(clipboardText, true, true)) {
                    setTemporaryButtonLabel(pastePaletteButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
                    return;
                }
                setTemporaryButtonLabel(pastePaletteButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.palettePasted) || 'Pasted');
            }).catch(() => {
                setTemporaryButtonLabel(pastePaletteButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
            });
        });
    }

    if (copyBackgroundPromptButton) {
        copyBackgroundPromptButton.addEventListener('click', () => {
            copyText(paletteSwitcherConfig.backgroundPrompt || '').then(() => {
                setTemporaryButtonLabel(copyBackgroundPromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.promptCopied) || 'Copied');
            }).catch(() => {
                setTemporaryButtonLabel(copyBackgroundPromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
            });
        });
    }

    if (pasteBackgroundButton && heroBackgroundCustomInput) {
        pasteBackgroundButton.addEventListener('click', () => {
            readText().then((clipboardText) => {
                const pastedValue = String(clipboardText || '')
                    .replace(/^\s*```(?:css)?\s*/i, '')
                    .replace(/\s*```\s*$/i, '')
                    .replace(/[\r\n]+/g, ' ')
                    .trim();
                if (!pastedValue) {
                    throw new Error('empty background');
                }
                heroBackgroundCustomInput.value = pastedValue;
                if (heroBackgroundStyleSelect) {
                    heroBackgroundStyleSelect.value = 'custom';
                }
                syncThemeViewerControls(currentThemeViewerSettings());
                window.RasamalaThemeDrawer.applyChoice(currentThemeViewerSettings(), true);
                setTemporaryButtonLabel(pasteBackgroundButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.backgroundPasted) || 'Pasted');
            }).catch(() => {
                setTemporaryButtonLabel(pasteBackgroundButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
            });
        });
    }

    if (copyBackgroundImagePromptButton) {
        copyBackgroundImagePromptButton.addEventListener('click', () => {
            copyText(paletteSwitcherConfig.backgroundImagePrompt || '').then(() => {
                setTemporaryButtonLabel(copyBackgroundImagePromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.promptCopied) || 'Copied');
            }).catch(() => {
                setTemporaryButtonLabel(copyBackgroundImagePromptButton, (paletteSwitcherConfig.labels && paletteSwitcherConfig.labels.clipboardUnavailable) || 'Clipboard unavailable', 1800);
            });
        });
    }

    queryAll('.palette-switcher-group', panel).forEach(group => {
        const summary = query('summary', group);
        if (!summary) return;
        summary.addEventListener('click', () => {
            const willBeOpen = !group.hasAttribute('open');
            if (willBeOpen) {
                queryAll('.palette-switcher-group', panel).forEach(otherGroup => {
                    if (otherGroup !== group) {
                        otherGroup.removeAttribute('open');
                    }
                });
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (panel.hidden || wrap.contains(event.target)) return;
        setPanelOpen(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setPanelOpen(false);
        }
    });
});
