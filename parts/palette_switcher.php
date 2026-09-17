<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: palette_switcher.php

require_once __DIR__ . '/../helpers/theme_feature_flags.php';

$palette_switcher_show = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
if (!$palette_switcher_show) {
    ?>
<template id="rasamala-palette-switcher-config"><?= themeEscape(json_encode(['enabled' => false], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?></template>
    <?php
    return;
}

if (!function_exists('rasamalaPaletteForSwitcher')) {
    function rasamalaPaletteForSwitcher($palette)
    {
        return [
            'primary' => $palette['primary'] ?? '#6f5b43',
            'hover' => $palette['hover'] ?? themeAdjustHexColor($palette['primary'] ?? '#6f5b43', -28),
            'secondary' => $palette['secondary'] ?? '#a58a63',
            'accent' => $palette['accent'] ?? '#c8a24a',
            'background' => $palette['background'] ?? '#f4f1ec',
            'surface' => $palette['surface'] ?? '#ffffff',
            'text' => $palette['text'] ?? '#2f2a24',
            'muted' => $palette['muted'] ?? '#7a7167',
            'mutedOnBackground' => $palette['muted_on_background'] ?? ($palette['muted'] ?? '#7a7167'),
            'mutedOnSurface' => $palette['muted_on_surface'] ?? ($palette['muted'] ?? '#7a7167'),
            'rgb' => $palette['rgb'] ?? themeHexToRgbString($palette['primary'] ?? '#6f5b43'),
            'accentRgb' => $palette['accent_rgb'] ?? themeHexToRgbString($palette['accent'] ?? '#c8a24a'),
            'onPrimary' => $palette['on_primary'] ?? themeReadableTextColor($palette['primary'] ?? '#6f5b43'),
            'onPrimaryHover' => $palette['on_primary_hover'] ?? themeReadableTextColor($palette['hover'] ?? '#5d4b36'),
            'onSecondary' => $palette['on_secondary'] ?? themeReadableTextColor($palette['secondary'] ?? '#a58a63'),
            'onAccent' => $palette['on_accent'] ?? themeReadableTextColor($palette['accent'] ?? '#c8a24a'),
            'onBackground' => $palette['on_background'] ?? themeReadableTextColor($palette['background'] ?? '#f4f1ec'),
            'onSurface' => $palette['on_surface'] ?? themeReadableTextColor($palette['surface'] ?? '#ffffff'),
        ];
    }
}

$palette_options = [];
$palette_presets = function_exists('themeAccentPalettes') ? themeAccentPalettes() : [];
foreach ($palette_presets as $palette_key => $palette_data) {
    $palette_options[$palette_key] = [
        'label' => $palette_data['label'] ?? ucfirst($palette_key),
        'light' => rasamalaPaletteForSwitcher(themeSelectedAccentColor($palette_key, $sysconf)),
        'dark' => rasamalaPaletteForSwitcher(themeSelectedDarkAccentColor($palette_key, $sysconf)),
    ];
}

$current_palette_key = strtolower((string)themeEffectiveAccentColorKey($sysconf));
$custom_palette_value = themeSanitizeCustomPaletteString(themeEffectiveTemplateValue('classic_palette_custom', '', $sysconf));
$palette_options['custom'] = [
    'label' => __('Custom Palette'),
    'light' => rasamalaPaletteForSwitcher(themeSelectedAccentColor('custom', $sysconf)),
    'dark' => rasamalaPaletteForSwitcher(themeSelectedDarkAccentColor('custom', $sysconf)),
];

$font_options = [
    'system' => __('System Default (Outfit)'),
    'inter' => __('Inter'),
    'roboto' => __('Roboto'),
    'poppins' => __('Poppins'),
    'playfair' => __('Playfair Display (Serif)'),
];
$animation_options = [
    'none' => __('None'),
    'particles' => __('Floating Glyphs'),
    'rain' => __('Code Rain'),
    'grid' => __('Moving Grid'),
    'twinkle' => __('Twinkling Stars'),
    'zen-ripples' => __('Zen Ripples'),
    'neural-network' => __('Neural Network'),
    'starfield-warp' => __('Starfield Warp'),
    'floating-embers' => __('Floating Embers'),
];
$animation_speed_options = [
    'slow' => __('Slow'),
    'normal' => __('Normal'),
    'fast' => __('Fast'),
];
$cursor_particle_options = [
    'auto' => __('Auto (Deteksi Perangkat)'),
    'low' => __('Ringan'),
    'medium' => __('Sedang'),
    'high' => __('Optimal'),
    'none' => __('Nonaktif'),
];
$cursor_icon_options = [
    'default' => __('Default Browser'),
    'neon-comet' => __('Neon Comet'),
    'pixel-sword' => __('Pixel Sword'),
    'electric-bolt' => __('Electric Bolt'),
    'ink-brush' => __('Ink Brush'),
    'rainbow-ribbon' => __('Rainbow Ribbon'),
];
$home_section_options = [
    ['key' => 'topic', 'label' => __('Topics'), 'selector' => '.rasamala-home-section-topic'],
    ['key' => 'news', 'label' => __('Latest Content Cards (Berita / Artikel)'), 'selector' => '.rasamala-home-content-cards-section'],
    ['key' => 'popular', 'label' => __('Popular Collections'), 'selector' => '.rasamala-home-section-popular'],
    ['key' => 'new-collection', 'label' => __('New Collections (Koleksi Terbaru)'), 'selector' => '.rasamala-home-section-new-collection'],
    ['key' => 'top-reader', 'label' => __('Top Reader'), 'selector' => '.rasamala-home-section-top-reader'],
    ['key' => 'footer', 'label' => __('Footer'), 'selector' => 'footer'],
];

$palette_prompt = "Buat 1 custom palette OPAC perpustakaan dalam format persis berikut: #PRIMARY; #SECONDARY; #ACCENT; #BACKGROUND; #SURFACE; #TEXT; #MUTED | #DARK_PRIMARY; #DARK_SECONDARY; #DARK_ACCENT; #DARK_BACKGROUND; #DARK_SURFACE; #DARK_TEXT; #DARK_MUTED. Output hanya 1 baris kode warna hex 6 digit, tanpa markdown, tanpa bullet, tanpa nama variabel, tanpa penjelasan. Aturan wajib: untuk light palette, Background dan Surface harus sama-sama terang/soft, Text harus gelap dan memiliki contrast ratio minimal 4.5:1 terhadap Background serta Surface, Muted juga minimal 4.5:1. Untuk dark palette, Background dan Surface harus sama-sama gelap, Text harus terang dan minimal 4.5:1 terhadap keduanya, Muted juga tetap terbaca minimal 4.5:1. Primary dipakai untuk navbar/footer/tombol utama; pastikan teks putih atau hitam terbaca di atas Primary. Accent hanya untuk highlight/icon/status, bukan teks panjang. Jangan pilih Text/Muted yang mirip Background atau Surface. Jika ragu gunakan Text #111827 dan Muted #374151 untuk light, Text #f8fafc dan Muted #cbd5e1 untuk dark. Buat light palette dan dark palette yang berbeda tetapi tetap satu identitas visual. Tema visual yang diminta: [tulis konsep warna di sini].";
$background_prompt = "Buat 1 custom background CSS untuk OPAC perpustakaan. Output hanya 1 baris dengan format persis: LIGHT_BACKGROUND | DARK_BACKGROUND. Gunakan satu ekspresi CSS background yang aman, seperti linear-gradient(), radial-gradient(), conic-gradient(), color-mix(), atau var(--theme-primary). Light harus terang dan nyaman dibaca; Dark harus gelap dan tetap nyaman dibaca. Jangan gunakan URL, gambar eksternal, @import, selector, deklarasi CSS, tanda kurung kurawal, titik koma, script, atau markdown. Hindari animasi berat agar halaman tetap cepat. Tema visual yang diminta: [tulis konsep background di sini].";
$background_image_prompt = "Buat 1 gambar background untuk OPAC perpustakaan dengan gaya modern, elegan, dan tenang. Utamakan pola atau tekstur seamless/tileable yang dapat di-loop mulus secara horizontal dan vertikal tanpa garis sambungan terlihat, sehingga cukup memakai satu aset kecil dengan CSS background-repeat. Sisakan area tengah yang bersih untuk teks dan kotak pencarian, tanpa tulisan, logo, wajah, atau detail yang mengganggu. Gunakan komposisi abstrak yang keren tetapi ringan: bentuk lembut, mesh, grain halus, atau motif geometris sederhana. Prioritaskan SVG atau AVIF/WebP terkompresi; jika raster, maksimal 1600x900 px, kualitas 70-80, target ukuran file di bawah 250 KB. Hindari GIF/video, frame animasi banyak, dan detail mikro yang membuat file besar. Output hanya prompt gambar, tanpa markdown atau penjelasan. Tema visual yang diminta: [tulis konsep warna dan gaya di sini].";

$top_subjects = [];
if (isset($dbs) && $dbs) {
    try {
        $q = $dbs->query("SELECT mt.topic FROM mst_topic AS mt LEFT JOIN biblio_topic AS bt ON mt.topic_id = bt.topic_id GROUP BY mt.topic_id ORDER BY COUNT(bt.biblio_id) DESC, mt.topic ASC LIMIT 50");
        if ($q) {
            while ($row = $q->fetch_row()) {
                $topic_name = trim((string)$row[0]);
                if ($topic_name !== '') {
                    $top_subjects[] = $topic_name;
                }
            }
        }
    } catch (\Exception $e) {}
}
if (empty($top_subjects)) {
    $top_subjects = [
        'Kesusastraan', 'Teknologi', 'Sains', 'Sejarah', 'Filsafat', 'Seni', 'Agama', 'Bahasa', 'Ilmu Sosial', 'Ekonomi',
        'Komputer', 'Fisika', 'Kimia', 'Matematika', 'Biologi', 'Kedokteran', 'Hukum', 'Politik', 'Pendidikan', 'Psikologi'
    ];
}

$effective_palette_key = $current_palette_key ?: strtolower((string)themeEffectiveAccentColorKey($sysconf));
$current_hero_mode = function_exists('themeHomepageHeroMode') ? themeHomepageHeroMode($sysconf) : 'no';
$hero_mode_options = [
    'yes' => themeTranslate('Yes - Fullscreen Hero'),
    'no' => themeTranslate('No - Standard Homepage'),
];
$current_hero_topics_in_hero = function_exists('themeHomepageHeroInsideContent') ? themeHomepageHeroInsideContent($sysconf) : 'none';
$hero_topics_options = [
    'none' => themeTranslate('None - Keep Below Search'),
    'topics' => themeTranslate('Topics'),
    'news' => themeTranslate('Latest Content (Berita / Cards)'),
    'popular' => themeTranslate('Popular among our collections'),
    'new_update' => themeTranslate('New collections + updated'),
    'top_reader' => themeTranslate('Top Reader of the Year'),
];
$current_hero_background_style = function_exists('themeHeroBackgroundStyle') ? themeHeroBackgroundStyle($sysconf) : 'none';
$hero_background_style_options = [];
$hero_background_style_details = [];
if (function_exists('themeBackgroundStyles')) {
    foreach (themeBackgroundStyles() as $background_key => $background_definition) {
        $hero_background_style_details[$background_key] = [
            'image' => !empty($background_definition['image']),
            'imageUrl' => $background_definition['image_url'] ?? '',
        ];
    }
}
if (function_exists('themeBackgroundStyleOptionLabels')) {
    $hero_background_style_options = array_map('themeTranslate', themeBackgroundStyleOptionLabels());
} else {
    $hero_background_style_options = [
        'none' => themeTranslate('None / Standard'),
        'soft-gradient' => themeTranslate('Soft Gradient'),
        'aurora-glow' => themeTranslate('Aurora Glow'),
        'mesh-light' => themeTranslate('Mesh Light'),
        'solid-theme' => themeTranslate('Solid Theme'),
        'minimal-surface' => themeTranslate('Minimal Surface'),
        'custom' => themeTranslate('Custom Background Style'),
    ];
}
$current_hero_background_custom = function_exists('themeCustomBackgroundStyle')
    ? themeCustomBackgroundStyle($sysconf)
    : '';
$current_hero_background_image = function_exists('themeBackgroundImageSettings')
    ? themeBackgroundImageSettings($sysconf)
    : ['size' => 'crop', 'position' => 'center', 'filter' => 'none', 'blur' => 'none', 'overlay' => 'none'];
$hero_background_image_size_options = function_exists('themeBackgroundImageSizeOptions') ? themeBackgroundImageSizeOptions() : ['crop' => themeTranslate('Crop / Cover')];
$hero_background_image_position_options = function_exists('themeBackgroundImagePositionOptions') ? themeBackgroundImagePositionOptions() : ['center' => themeTranslate('Center')];
$hero_background_image_filter_options = function_exists('themeBackgroundImageFilterOptions') ? themeBackgroundImageFilterOptions() : ['none' => themeTranslate('No Filter')];
$hero_background_image_blur_options = function_exists('themeBackgroundImageBlurOptions') ? themeBackgroundImageBlurOptions() : ['none' => themeTranslate('No Blur')];
$hero_background_image_overlay_options = function_exists('themeBackgroundImageOverlayOptions') ? themeBackgroundImageOverlayOptions() : ['none' => themeTranslate('No Overlay')];
$current_home_layout = (int)themeEffectiveTemplateValue('classic_home_sections_tabs', 0, $sysconf) === 1 ? 'tabs' : 'standard';
$home_layout_options = [
    'tabs' => themeTranslate('Tab Mode'),
    'standard' => themeTranslate('Standard Mode'),
];
$hero_text_sizes = [
    'small' => themeTranslate('Small'),
    'medium' => themeTranslate('Medium'),
    'large' => themeTranslate('Large'),
];
$search_sizes = [
    'small' => themeTranslate('Small'),
    'medium' => themeTranslate('Medium'),
    'large' => themeTranslate('Large'),
];
$library_name_position_options = [
    'navbar' => themeTranslate('Logo & name in Navbar (default)'),
    'hero' => themeTranslate('Logo & name above Search Box (Desktop)'),
];
$current_library_name_position = strtolower((string)themeEffectiveTemplateValue('classic_library_name_position', 'navbar', $sysconf));
if (!array_key_exists($current_library_name_position, $library_name_position_options)) {
    $current_library_name_position = 'navbar';
}
$show_hide_options = [
    'show' => themeTranslate('Show'),
    'hide' => themeTranslate('Hide'),
];
$ticker_speed_options = [
    'fast' => themeTranslate('Fast (12s)'),
    'normal' => themeTranslate('Normal (18s)'),
    'slow' => themeTranslate('Slow (32s)'),
    'very_slow' => themeTranslate('Very slow (52s)'),
];
$search_panel_style_options = [
    'solid' => themeTranslate('Solid'),
    'transparent' => themeTranslate('Transparent'),
];
$map_visibility_options = [
    'all' => themeTranslate('Map and Social Media'),
    'hide_map' => themeTranslate('Social Media Only'),
    'hide_social' => themeTranslate('Map Only'),
    'hide_all' => themeTranslate('Hide Both'),
];

$topic_icon_options = [];
$language_options = [];
$tinfo_options_helper = __DIR__ . '/../helpers/tinfo_options_helper.php';
if (is_file($tinfo_options_helper)) {
    require_once $tinfo_options_helper;
}
if (function_exists('rasamalaTinfoTopicIconOptions')) {
    foreach (rasamalaTinfoTopicIconOptions() as $icon_option) {
        if (!is_array($icon_option) || empty($icon_option['value'])) {
            continue;
        }
        $topic_icon_options[] = [
            'value' => (string)$icon_option['value'],
            'label' => (string)($icon_option['label'] ?? $icon_option['value']),
        ];
    }
}
if (function_exists('rasamalaTinfoLanguageOptions')) {
    foreach (rasamalaTinfoLanguageOptions() as $language_option) {
        if (!is_array($language_option) || empty($language_option['code'])) {
            continue;
        }
        $language_options[] = [
            'code' => (string)$language_option['code'],
            'name' => (string)($language_option['name'] ?? $language_option['code']),
            'flag' => (string)($language_option['flag'] ?? ''),
        ];
    }
}
if (!$topic_icon_options) {
    $topic_icon_options = [
        ['value' => 'fas fa-home', 'label' => themeTranslate('Home')],
        ['value' => 'fas fa-book', 'label' => themeTranslate('Book')],
        ['value' => 'fas fa-users', 'label' => themeTranslate('Users')],
        ['value' => 'fas fa-info-circle', 'label' => themeTranslate('Info')],
        ['value' => 'fas fa-question-circle', 'label' => themeTranslate('Help')],
        ['value' => 'fas fa-newspaper', 'label' => themeTranslate('News')],
        ['value' => 'fas fa-th-large', 'label' => themeTranslate('Grid')],
        ['value' => 'fas fa-link', 'label' => themeTranslate('Link')],
    ];
}
$navbar_menu_value = (string)themeEffectiveTemplateValue(
    'classic_navbar_menu',
    function_exists('themeNavbarMenuDefault') ? themeNavbarMenuDefault() : '',
    $sysconf
);
$topic_items_value = (string)themeEffectiveTemplateValue(
    'classic_topic_items',
    function_exists('themeTopicItemsDefault') ? themeTopicItemsDefault() : '',
    $sysconf
);

// Keep the public viewer in sync with every administrator TInfo field. The
// visual controls above are intentionally compact; fields without a bespoke
// preview control are rendered in the searchable Advanced TInfo section below.
$theme_viewer_tinfo_options = [];
$theme_viewer_tinfo_generic_options = [];
$theme_viewer_tinfo_values = [];
$theme_viewer_tinfo_special_fields = [
    'classic_theme_color', 'classic_palette_custom', 'classic_font_family',
    'classic_library_name_position',
    'classic_hero_fullscreen_mode', 'classic_hero_topics_show',
    'classic_hero_background_style', 'classic_background_style_custom',
    'classic_background_image_size', 'classic_background_image_position',
    'classic_background_image_filter', 'classic_background_image_blur',
    'classic_background_image_overlay', 'classic_hero_background_animation',
    'classic_background_animation_speed', 'classic_cursor_particles',
    'classic_cursor_custom_icon', 'classic_home_sections_tabs',
    'classic_hero_text', 'classic_hero_text_size', 'classic_search_size',
    'classic_search_placeholder', 'classic_home_display_show',
    'classic_ticker_show', 'classic_ticker_speed', 'classic_search_panel_style',
    'classic_mobile_bottom_nav_show', 'classic_back_to_top', 'classic_map',
    'classic_topic_show', 'classic_home_content_cards_show',
    'classic_popular_collection', 'classic_new_collection',
    'classic_top_reader', 'classic_footer_show',
    'classic_navbar_menu', 'classic_topic_items', 'classic_language_visible_codes',
];
$theme_viewer_tinfo_helper = __DIR__ . '/../helpers/theme_viewer_tinfo.php';
if (is_file($theme_viewer_tinfo_helper)) {
    require_once $theme_viewer_tinfo_helper;
}
if (function_exists('rasamalaThemeViewerTinfoOptions')) {
    $theme_viewer_tinfo_options = rasamalaThemeViewerTinfoOptions($sysconf);
    foreach ($theme_viewer_tinfo_options as $tinfo_option) {
        $dbfield = (string)($tinfo_option['dbfield'] ?? '');
        if ($dbfield === '') {
            continue;
        }
        $theme_viewer_tinfo_values[$dbfield] = (string)($tinfo_option['value'] ?? '');
        if (in_array($dbfield, $theme_viewer_tinfo_special_fields, true)) {
            continue;
        }
        $theme_viewer_tinfo_generic_options[] = $tinfo_option;
    }
}

$palette_switcher_config = [
    'topSubjects' => $top_subjects,

    'enabled' => true,
    'currentHeroMode' => $current_hero_mode,
    'heroModes' => $hero_mode_options,
    'currentHeroTopicsInHero' => $current_hero_topics_in_hero,
    'heroTopicsInHero' => $hero_topics_options,
    'currentHeroBackgroundStyle' => $current_hero_background_style,
    'heroBackgroundStyles' => $hero_background_style_options,
    'heroBackgroundStyleDetails' => $hero_background_style_details,
    'currentHeroBackgroundCustom' => $current_hero_background_custom,
    'heroBackgroundCustomDefault' => 'linear-gradient(145deg, var(--theme-primary), var(--theme-surface)) | linear-gradient(145deg, var(--theme-dark-primary), var(--theme-dark-surface))',
    'currentHeroBackgroundImage' => $current_hero_background_image,
    'heroBackgroundImageSizes' => $hero_background_image_size_options,
    'heroBackgroundImagePositions' => $hero_background_image_position_options,
    'heroBackgroundImageFilters' => $hero_background_image_filter_options,
    'heroBackgroundImageBlurs' => $hero_background_image_blur_options,
    'heroBackgroundImageOverlays' => $hero_background_image_overlay_options,
    'currentHomeLayout' => $current_home_layout,
    'homeLayouts' => $home_layout_options,
    'heroText' => (string)themeEffectiveTemplateValue('classic_hero_text', 'Search Library Collection', $sysconf),
    'heroTextSize' => (string)themeEffectiveTemplateValue('classic_hero_text_size', 'small', $sysconf),
    'heroTextSizes' => $hero_text_sizes,
    'currentLibraryNamePosition' => $current_library_name_position,
    'libraryNamePositions' => $library_name_position_options,
    'searchPlaceholder' => (string)themeEffectiveTemplateValue('classic_search_placeholder', 'Enter keyword to search collection...', $sysconf),
    'searchSize' => (string)themeEffectiveTemplateValue('classic_search_size', 'medium', $sysconf),
    'searchSizes' => $search_sizes,
    'homeInfoShow' => ((string)themeEffectiveTemplateValue('classic_home_display_show', 'below', $sysconf) !== '0') ? 'show' : 'hide',
    'tickerShow' => ((string)themeEffectiveTemplateValue('classic_ticker_show', 'bottom', $sysconf) !== '0') ? 'show' : 'hide',
    'showHideOptions' => $show_hide_options,
    'tickerSpeed' => (string)themeEffectiveTemplateValue('classic_ticker_speed', 'slow', $sysconf),
    'tickerSpeeds' => $ticker_speed_options,
    'searchPanelStyle' => (string)themeEffectiveTemplateValue('classic_search_panel_style', 'solid', $sysconf),
    'searchPanelStyles' => $search_panel_style_options,
    // Feature flags keep unfinished controls out of the public viewer while
    // leaving all preview/runtime code available for a later release.
    'featureFlags' => [
        'panelBackground' => rasamalaThemeFeatureEnabled('panel_background'),
        'cursorIcon' => rasamalaThemeFeatureEnabled('cursor_icon'),
        'cursorParticles' => rasamalaThemeFeatureEnabled('cursor_particles'),
    ],
    'mapVisibility' => (string)themeEffectiveTemplateValue('classic_map', 'all', $sysconf),
    'mapVisibilityOptions' => $map_visibility_options,
    'mobileBottomNavShow' => ((int)themeEffectiveTemplateValue('classic_mobile_bottom_nav_show', 1, $sysconf) === 1) ? 'show' : 'hide',
    'backToTopShow' => ((int)themeEffectiveTemplateValue('classic_back_to_top', 1, $sysconf) === 1) ? 'show' : 'hide',
    'currentKey' => $effective_palette_key ?: 'custom',
    'customValue' => $custom_palette_value,
    'fontFamily' => themeEffectiveTemplateValue('classic_font_family', 'system', $sysconf),
    'backgroundAnimation' => themeEffectiveTemplateValue('classic_hero_background_animation', 'none', $sysconf),
    'backgroundAnimationSpeed' => themeEffectiveTemplateValue('classic_background_animation_speed', 'normal', $sysconf),
    'cursorParticles' => themeEffectiveTemplateValue('classic_cursor_particles', 'none', $sysconf),
    'cursorIcon' => themeEffectiveTemplateValue('classic_cursor_custom_icon', 'default', $sysconf),
    'prompt' => $palette_prompt,
    'backgroundPrompt' => $background_prompt,
    'backgroundImagePrompt' => $background_image_prompt,
    'labels' => [
        'promptCopied' => __('Copied'),
        'palettePasted' => __('Pasted'),
        'backgroundPasted' => __('Background pasted'),
        'clipboardUnavailable' => __('Clipboard unavailable'),
        'adminChecking' => themeTranslate('Checking administrator access...'),
        'adminLoginRequired' => themeTranslate('Log in to the admin area to save theme settings.'),
        'adminSaving' => themeTranslate('Saving theme settings...'),
        'adminSaved' => themeTranslate('Theme settings saved. Reload the OPAC to apply them permanently.'),
        'adminSaveError' => themeTranslate('Theme settings could not be saved. Please check your admin session.'),
    ],
    'fonts' => $font_options,
    'animations' => $animation_options,
    'animationSpeeds' => $animation_speed_options,
    'cursorParticlesOptions' => $cursor_particle_options,
    'cursorIcons' => $cursor_icon_options,
    'palettes' => $palette_options,
    'homeSections' => $home_section_options,
    'navbarMenu' => $navbar_menu_value,
    'topicItems' => $topic_items_value,
    'topicIconOptions' => $topic_icon_options,
    'languageOptions' => $language_options,
    // All remaining admin fields are represented here. The specialized
    // controls above cover the fields listed in $theme_viewer_tinfo_special_fields.
    'tinfoOptions' => $theme_viewer_tinfo_options,
    'tinfoGenericOptions' => $theme_viewer_tinfo_generic_options,
    'tinfoValues' => $theme_viewer_tinfo_values,
    // The admin cookie is scoped to /admin, so Theme Viewer probes this
    // existing, protected TInfo endpoint before revealing the save action.
    'adminThemeEndpoint' => defined('MWB') ? MWB . 'system/theme.php' : 'admin/modules/system/theme.php',
    'adminThemeDir' => (string)($sysconf['template']['theme'] ?? 'rasamala'),
];
?>

<template id="rasamala-palette-switcher-config"><?= themeEscape(json_encode($palette_switcher_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?></template>
<style id="rasamala-theme-viewer-background-style" nonce="<?= themeCspNonce(); ?>"></style>

<?php
$has_ticker_active = !empty($latest_content_ticker_items) || (function_exists('themeEffectiveTemplateValue') && themeEffectiveTemplateValue('classic_ticker_show', 0, $sysconf) === 'bottom');
?>
<div class="rasamala-palette-switcher" id="rasamala-palette-switcher">
    <button type="button"
            id="palette-switcher-toggle"
            class="btn-palette-switcher shadow-lg <?= $has_ticker_active ? 'has-latest-content-ticker' : '' ?>"
            aria-label="<?= themeEscape(__('Open theme palette menu')); ?>"
            aria-expanded="false"
            aria-controls="palette-switcher-panel">
        <i class="fas fa-paint-brush palette-switcher-icon" aria-hidden="true"></i>
    </button>
    <div id="palette-switcher-panel" class="palette-switcher-panel" hidden>
        <div class="palette-switcher-sticky-header">
            <div class="palette-switcher-header">
                <strong><?= themeEscape(themeTranslate('Pratinjau Tema')); ?></strong>
                <div class="palette-switcher-header-search">
                    <label class="visually-hidden" for="theme-tinfo-search"><?= themeEscape(themeTranslate('Cari Pengaturan TInfo')); ?></label>
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input id="theme-tinfo-search"
                           class="form-control palette-switcher-header-search-input"
                           type="search"
                           autocomplete="off"
                           placeholder="<?= themeEscape(themeTranslate('Cari pengaturan...')); ?>">
                </div>
                <div class="palette-switcher-header-actions">
                    <div class="palette-switcher-save-wrap" id="theme-viewer-save-wrap" hidden>
                        <button type="button"
                                id="theme-viewer-save"
                                class="palette-switcher-save-btn"
                                hidden>
                            <i class="fas fa-save" aria-hidden="true"></i>
                            <span><?= themeEscape(themeTranslate('Simpan perubahan')); ?></span>
                        </button>
                        <span class="palette-switcher-save-status" id="theme-viewer-save-status" role="status" aria-live="polite"></span>
                    </div>
                    <button type="button"
                            id="palette-color-mode-toggle"
                            class="palette-switcher-panel-action"
                            title="<?= themeEscape(__('Toggle dark/light mode')); ?>"
                            data-dark-title="<?= themeEscape(__('Dark mode')); ?>"
                            data-light-title="<?= themeEscape(__('Light mode')); ?>"
                            aria-label="<?= themeEscape(__('Toggle dark/light mode')); ?>"
                            aria-pressed="false">
                        <i class="fas fa-moon" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="palette-switcher-panel-action" id="palette-switcher-fullscreen" aria-label="<?= themeEscape(__('Fullscreen')); ?>" title="<?= themeEscape(__('Fullscreen')); ?>" aria-pressed="false"><i class="fas fa-expand" aria-hidden="true"></i></button>
                    <button type="button" class="palette-switcher-close" data-palette-close aria-label="<?= themeEscape(__('Close')); ?>">&times;</button>
                </div>
            </div>
            <div class="palette-switcher-intro" id="theme-viewer-intro-notice">
                <i class="fas fa-bolt text-primary me-1" aria-hidden="true"></i>
                <span><strong><?= themeEscape(themeTranslate('Pratinjau Langsung.')); ?></strong> <a href="index.php?p=login" class="fw-bold text-primary text-decoration-underline"><?= themeEscape(themeTranslate('Masuk sebagai admin')); ?></a> <?= themeEscape(themeTranslate('untuk menyimpan perubahan.')); ?></span>
            </div>
        </div>

        <!-- 1. Identitas Tampilan -->
        <details class="palette-switcher-group" open>
            <summary><span class="palette-switcher-group-step">1</span><span><?= themeEscape(themeTranslate('Identitas Tampilan')); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
            <div class="palette-switcher-group-body">
                <div class="palette-switcher-grid">
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="palette-switcher-select"><?= themeEscape(themeTranslate('Warna Tema')); ?></label>
                        <select id="palette-switcher-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-font-select"><?= themeEscape(themeTranslate('Font Tema')); ?></label>
                        <select id="theme-viewer-font-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field palette-switcher-field-wide">
                        <label class="palette-switcher-label" for="theme-background-style-select"><?= themeEscape(themeTranslate('Latar Belakang')); ?></label>
                        <select id="theme-background-style-select" class="form-control palette-switcher-select"></select>
                        <div class="palette-switcher-help-inline mt-1 text-xs">
                            <i class="fas fa-external-link-alt text-primary me-1" aria-hidden="true"></i>
                            <span>Buat background SVG lainnya: <a href="http://psb.feb.ui.ac.id/stools" target="_blank" rel="noopener noreferrer" class="fw-bold text-primary text-decoration-underline">psb.feb.ui.ac.id/stools</a></span>
                        </div>
                        <!-- Image treatment options (appears only when Latar Belakang = Image) -->
                        <div class="palette-switcher-image-options mt-2 mb-2" id="theme-background-image-options" hidden>
                            <div class="palette-switcher-image-options-head mb-2">
                                <span class="palette-switcher-label palette-switcher-label-flush"><?= themeEscape(themeTranslate('Pengaturan Gambar Latar')); ?></span>
                                <span class="palette-switcher-help-inline"><?= themeEscape(themeTranslate('Rekomendasi: Crop + Keterbacaan Tinggi')); ?></span>
                            </div>
                            <div class="palette-switcher-grid">
                                <div class="palette-switcher-field">
                                    <label class="palette-switcher-label" for="theme-background-image-size-select"><?= themeEscape(themeTranslate('Ukuran Gambar')); ?></label>
                                    <select id="theme-background-image-size-select" class="form-control palette-switcher-select"></select>
                                </div>
                                <div class="palette-switcher-field">
                                    <label class="palette-switcher-label" for="theme-background-image-position-select"><?= themeEscape(themeTranslate('Posisi Gambar')); ?></label>
                                    <select id="theme-background-image-position-select" class="form-control palette-switcher-select"></select>
                                </div>
                                <div class="palette-switcher-field">
                                    <label class="palette-switcher-label" for="theme-background-image-filter-select"><?= themeEscape(themeTranslate('Filter Gambar')); ?></label>
                                    <select id="theme-background-image-filter-select" class="form-control palette-switcher-select"></select>
                                </div>
                                <div class="palette-switcher-field">
                                    <label class="palette-switcher-label" for="theme-background-image-blur-select"><?= themeEscape(themeTranslate('Efek Blur')); ?></label>
                                    <select id="theme-background-image-blur-select" class="form-control palette-switcher-select"></select>
                                </div>
                                <div class="palette-switcher-field">
                                    <label class="palette-switcher-label" for="theme-background-image-overlay-select"><?= themeEscape(themeTranslate('Lapisan Overlay')); ?></label>
                                    <select id="theme-background-image-overlay-select" class="form-control palette-switcher-select"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-animation-select"><?= themeEscape(themeTranslate('Animasi Latar')); ?></label>
                        <select id="theme-viewer-animation-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-animation-speed-select"><?= themeEscape(themeTranslate('Kecepatan Animasi')); ?></label>
                        <select id="theme-viewer-animation-speed-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <?php if (rasamalaThemeFeatureEnabled('cursor_particles')): ?>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-cursor-particles-select"><?= themeEscape(themeTranslate('Efek Kursor')); ?></label>
                        <select id="theme-viewer-cursor-particles-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <?php endif; ?>
                    <?php if (rasamalaThemeFeatureEnabled('cursor_icon')): ?>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-cursor-icon-select"><?= themeEscape(themeTranslate('Ikon Kursor')); ?></label>
                        <select id="theme-viewer-cursor-icon-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Custom Palette Editor attached under Palette selector -->
                <div class="palette-switcher-custom-group" hidden>
                    <div class="palette-switcher-custom" id="palette-switcher-custom" hidden>
                        <div class="palette-switcher-custom-head mb-1">
                            <label class="palette-switcher-label palette-switcher-label-flush" for="palette-switcher-custom-input"><?= themeEscape(__('Warna Palette Kustom')); ?></label>
                        </div>
                        <textarea id="palette-switcher-custom-input"
                                  class="form-control palette-switcher-custom-input palette-switcher-custom-textarea"
                                  rows="4"
                                  maxlength="320"
                                  autocomplete="off"
                                  spellcheck="false"></textarea>
                        <div class="palette-switcher-help palette-switcher-format-help mt-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <span class="fw-bold text-xs"><i class="fas fa-magic text-primary me-1" aria-hidden="true"></i><?= themeEscape(__('Generate Warna via AI (ChatGPT/Gemini/AI Lainnya):')); ?></span>
                                <div class="palette-switcher-custom-actions d-inline-flex align-items-center gap-1">
                                    <button type="button"
                                            id="palette-switcher-copy-prompt"
                                            class="palette-switcher-tool-btn btn-prompt-action"
                                            title="<?= themeEscape(__('Salin Prompt Warna')); ?>"
                                            aria-label="<?= themeEscape(__('Salin Prompt Warna')); ?>">
                                        <i class="fas fa-copy" aria-hidden="true"></i><span><?= themeEscape(__('Salin Prompt')); ?></span>
                                    </button>
                                    <button type="button"
                                            id="palette-switcher-paste-palette"
                                            class="palette-switcher-tool-btn btn-prompt-action"
                                            title="<?= themeEscape(__('Tempel Palette')); ?>"
                                            aria-label="<?= themeEscape(__('Tempel Palette')); ?>">
                                        <i class="fas fa-paste" aria-hidden="true"></i><span><?= themeEscape(__('Tempel Palette')); ?></span>
                                    </button>
                                </div>
                            </div>
                            <div class="palette-switcher-ai-hint text-xs mb-2 p-2 rounded bg-light border">
                                <i class="fas fa-lightbulb text-warning me-1" aria-hidden="true"></i>
                                <span><strong>Langkah Singkat:</strong> Klik <u>Salin Prompt</u> ➔ Tempel di AI ➔ Salin balasan AI ➔ Klik <u>Tempel Palette</u>.</span>
                            </div>
                            <div class="text-muted text-xs mb-1"><?= themeEscape(__('Format manual: TERANG | GELAP')); ?></div>
                            <code class="palette-switcher-format-code d-block">#1e3a8a;#3b82f6;#10b981;#f3f4f6;#ffffff;#1f2937;#4b5563 | #0f172a;#1e293b;#10b981;#0f172a;#1e293b;#f9fafb;#94a3b8</code>
                        </div>
                    </div>
                </div>
            </div>
        </details>

        <!-- 2. Beranda dan Pencarian -->
        <details class="palette-switcher-group">
            <summary><span class="palette-switcher-group-step">2</span><span><?= themeEscape(themeTranslate('Beranda dan Pencarian')); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
            <div class="palette-switcher-group-body">
                <div class="palette-switcher-grid">
                    <div class="palette-switcher-field palette-switcher-field-wide">
                        <label class="palette-switcher-label" for="theme-viewer-library-name-position-select"><?= themeEscape(themeTranslate('Logo & Library Name Position (Desktop View)')); ?></label>
                        <select id="theme-viewer-library-name-position-select" class="form-control palette-switcher-select"></select>
                        <p class="palette-switcher-help palette-switcher-field-help mb-0"><?= themeEscape(themeTranslate('Saat logo dan nama library berada di atas kotak pencarian, judul pencarian kustom tidak diperlukan dan akan disembunyikan.')); ?></p>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-hero-text-input"><?= themeEscape(themeTranslate('Judul Beranda')); ?></label>
                        <input id="theme-viewer-hero-text-input" class="form-control palette-switcher-select" type="text" maxlength="120" value="<?= themeEscape((string)$palette_switcher_config['heroText']); ?>" autocomplete="off">
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-hero-text-size-select"><?= themeEscape(themeTranslate('Ukuran Judul Beranda')); ?></label>
                        <select id="theme-viewer-hero-text-size-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-search-placeholder-input"><?= themeEscape(themeTranslate('Placeholder Pencarian')); ?></label>
                        <input id="theme-viewer-search-placeholder-input" class="form-control palette-switcher-select" type="text" maxlength="160" value="<?= themeEscape((string)$palette_switcher_config['searchPlaceholder']); ?>" autocomplete="off">
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-search-size-select"><?= themeEscape(themeTranslate('Ukuran Kotak Pencarian')); ?></label>
                        <select id="theme-viewer-search-size-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-hero-mode-select"><?= themeEscape(themeTranslate('Tampilan Beranda')); ?></label>
                        <select id="theme-hero-mode-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-hero-topics-select"><?= themeEscape(themeTranslate('Konten di Area Utama')); ?></label>
                        <select id="theme-hero-topics-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-home-info-select"><?= themeEscape(themeTranslate('Informasi di Area Pencarian')); ?></label>
                        <select id="theme-viewer-home-info-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-ticker-select"><?= themeEscape(themeTranslate('Berita Berjalan')); ?></label>
                        <select id="theme-viewer-ticker-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-ticker-speed-select"><?= themeEscape(themeTranslate('Kecepatan Berita Berjalan')); ?></label>
                        <select id="theme-viewer-ticker-speed-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <?php if (rasamalaThemeFeatureEnabled('panel_background')): ?>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-search-panel-style-select"><?= themeEscape(themeTranslate('Latar Panel Pencarian')); ?></label>
                        <select id="theme-viewer-search-panel-style-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <?php endif; ?>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-mobile-nav-select"><?= themeEscape(themeTranslate('Navigasi Bawah Mobile')); ?></label>
                        <select id="theme-viewer-mobile-nav-select" class="form-control palette-switcher-select"></select>
                    </div>
                    <div class="palette-switcher-field">
                        <label class="palette-switcher-label" for="theme-viewer-back-to-top-select"><?= themeEscape(themeTranslate('Tombol Kembali ke Atas')); ?></label>
                        <select id="theme-viewer-back-to-top-select" class="form-control palette-switcher-select"></select>
                    </div>
                </div>
            </div>
        </details>

        <!-- 3. Bagian Beranda -->
        <details class="palette-switcher-group">
            <summary><span class="palette-switcher-group-step">3</span><span><?= themeEscape(themeTranslate('Bagian Beranda')); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
            <div class="palette-switcher-group-body">
                <div class="palette-switcher-field mb-3">
                    <label class="palette-switcher-label" for="theme-home-layout-select"><?= themeEscape(themeTranslate('Tata Letak Section')); ?></label>
                    <select id="theme-home-layout-select" class="form-control palette-switcher-select"></select>
                </div>
                <div class="palette-switcher-grid mb-3">
                    <div class="palette-switcher-field palette-switcher-field-wide">
                        <label class="palette-switcher-label" for="theme-viewer-map-visibility-select"><?= themeEscape(themeTranslate('Pengaturan Peta & Media Sosial')); ?></label>
                        <select id="theme-viewer-map-visibility-select" class="form-control palette-switcher-select"></select>
                    </div>
                </div>
                <div class="palette-switcher-section-tools" aria-label="<?= themeEscape(themeTranslate('Bagian Beranda')); ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2 palette-switcher-section-head">
                        <span class="palette-switcher-label palette-switcher-section-label palette-switcher-label-flush"><?= themeEscape(themeTranslate('Tampilkan / Sembunyikan Seksi Beranda')); ?></span>
                        <div class="palette-switcher-section-actions">
                            <button type="button"
                                    class="palette-switcher-tool-btn palette-switcher-tool-btn-compact"
                                    id="theme-viewer-show-sections"
                                    title="<?= themeEscape(__('Tampilkan semua bagian')); ?>"
                                    aria-label="<?= themeEscape(__('Tampilkan semua bagian')); ?>">
                                <i class="fas fa-eye palette-switcher-tool-icon-small" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    class="palette-switcher-tool-btn palette-switcher-tool-btn-compact"
                                    id="theme-viewer-hide-sections"
                                    title="<?= themeEscape(__('Sembunyikan semua bagian')); ?>"
                                    aria-label="<?= themeEscape(__('Sembunyikan semua bagian')); ?>">
                                <i class="fas fa-eye-slash palette-switcher-tool-icon-small" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    id="palette-switcher-reset"
                                    class="palette-switcher-tool-btn palette-switcher-tool-btn-compact"
                                    title="<?= themeEscape(__('Reset ke Awal')); ?>"
                                    aria-label="<?= themeEscape(__('Reset ke Awal')); ?>">
                                <i class="fas fa-undo palette-switcher-tool-icon-small" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="palette-switcher-section-list" id="theme-viewer-section-list"></div>
                </div>
            </div>
        </details>

        <!-- 4. Navigasi dan Bahasa -->
        <details class="palette-switcher-group palette-switcher-structured-group">
            <summary><span class="palette-switcher-group-step">4</span><span><?= themeEscape(themeTranslate('Navigasi dan Bahasa')); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
            <div class="palette-switcher-group-body">
                <p class="palette-switcher-help palette-switcher-tinfo-intro">
                    <?= themeEscape(themeTranslate('Gunakan editor terstruktur agar label, URL, dan ikon mudah diatur dengan aman. Nilai tersimpan otomatis dalam format TInfo.')); ?>
                </p>
                <section class="palette-switcher-builder" id="theme-viewer-navbar-builder" aria-labelledby="theme-viewer-navbar-builder-title">
                    <div class="palette-switcher-builder-head">
                        <div>
                            <h3 id="theme-viewer-navbar-builder-title" class="palette-switcher-builder-title"><?= themeEscape(themeTranslate('Navbar Menu')); ?></h3>
                            <p class="palette-switcher-help mb-0"><?= themeEscape(themeTranslate('Setiap menu memiliki label, URL, dan ikon pilihan.')); ?></p>
                        </div>
                        <button type="button" class="palette-switcher-tool-btn palette-switcher-builder-add" id="theme-viewer-navbar-add" title="<?= themeEscape(__('Tambah menu')); ?>" aria-label="<?= themeEscape(__('Tambah menu')); ?>"><i class="fas fa-plus me-1" aria-hidden="true"></i><span><?= themeEscape(__('Tambah Menu')); ?></span></button>
                    </div>
                    <div class="palette-switcher-builder-columns-head" aria-hidden="true"><span><?= themeEscape(themeTranslate('Label')); ?></span><span><?= themeEscape(themeTranslate('URL')); ?></span><span><?= themeEscape(themeTranslate('Ikon')); ?></span><span></span></div>
                    <div class="palette-switcher-builder-rows" id="theme-viewer-navbar-rows"></div>
                </section>
                <section class="palette-switcher-builder" id="theme-viewer-topic-builder" aria-labelledby="theme-viewer-topic-builder-title">
                    <div class="palette-switcher-builder-head">
                        <div>
                            <h3 id="theme-viewer-topic-builder-title" class="palette-switcher-builder-title"><?= themeEscape(themeTranslate('Shortcut Topic')); ?></h3>
                            <p class="palette-switcher-help mb-0"><?= themeEscape(themeTranslate('Atur nama topic, URL pencarian, dan ikon topic.')); ?></p>
                        </div>
                        <button type="button" class="palette-switcher-tool-btn palette-switcher-builder-add" id="theme-viewer-topic-add" title="<?= themeEscape(__('Tambah topic')); ?>" aria-label="<?= themeEscape(__('Tambah topic')); ?>"><i class="fas fa-plus me-1" aria-hidden="true"></i><span><?= themeEscape(__('Tambah Topic')); ?></span></button>
                    </div>
                    <div class="palette-switcher-builder-columns-head" aria-hidden="true"><span><?= themeEscape(themeTranslate('Label')); ?></span><span><?= themeEscape(themeTranslate('URL')); ?></span><span><?= themeEscape(themeTranslate('Ikon')); ?></span><span></span></div>
                    <div class="palette-switcher-builder-rows" id="theme-viewer-topic-rows"></div>
                </section>
                <section class="palette-switcher-builder" id="theme-viewer-language-builder" aria-labelledby="theme-viewer-language-builder-title">
                    <div class="palette-switcher-builder-head">
                        <div>
                            <h3 id="theme-viewer-language-builder-title" class="palette-switcher-builder-title"><?= themeEscape(themeTranslate('Bahasa Aktif')); ?></h3>
                            <p class="palette-switcher-help mb-0"><?= themeEscape(themeTranslate('Pilih bahasa yang ditampilkan di menu bahasa OPAC.')); ?></p>
                        </div>
                    </div>
                    <div class="palette-switcher-builder-actions" id="theme-viewer-language-actions">
                        <button type="button" class="palette-switcher-tool-btn palette-switcher-builder-action" data-language-action="all"><?= themeEscape(themeTranslate('Pilih semua')); ?></button>
                        <button type="button" class="palette-switcher-tool-btn palette-switcher-builder-action" data-language-action="id-en"><?= themeEscape(themeTranslate('Indonesia & Inggris')); ?></button>
                        <button type="button" class="palette-switcher-tool-btn palette-switcher-builder-action" data-language-action="none"><?= themeEscape(themeTranslate('Sembunyikan semua')); ?></button>
                    </div>
                    <div class="palette-switcher-language-options" id="theme-viewer-language-options"></div>
                </section>
            </div>
        </details>

        <!-- 5. Pengaturan Lanjutan -->
        <details class="palette-switcher-group palette-switcher-tinfo-group">
            <summary><span class="palette-switcher-group-step">5</span><span><?= themeEscape(themeTranslate('Pengaturan Lanjutan')); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
            <div class="palette-switcher-group-body">
                <div id="theme-tinfo-generic" class="palette-switcher-tinfo-list">
<?php
$theme_viewer_tinfo_groups = [];
foreach ($theme_viewer_tinfo_generic_options as $tinfo_option) {
    $group_key = (string)($tinfo_option['group'] ?? 'other');
    if (!isset($theme_viewer_tinfo_groups[$group_key])) {
        $theme_viewer_tinfo_groups[$group_key] = [
            'label' => (string)($tinfo_option['groupLabel'] ?? $group_key),
            'options' => [],
        ];
    }
    $theme_viewer_tinfo_groups[$group_key]['options'][] = $tinfo_option;
}
foreach ($theme_viewer_tinfo_groups as $group_key => $group) {
    ?>
                            <details class="palette-switcher-tinfo-category" data-tinfo-category="<?= themeEscape(strtolower($group['label'])); ?>">
                                <summary><span><?= themeEscape($group['label']); ?></span><i class="fas fa-chevron-right" aria-hidden="true"></i></summary>
                                <div class="palette-switcher-tinfo-category-body">
<?php foreach ($group['options'] as $tinfo_option) {
    $dbfield = (string)($tinfo_option['dbfield'] ?? '');
    $field_id = 'theme-tinfo-' . preg_replace('/[^a-z0-9_-]+/i', '-', $dbfield);
    $field_type = strtolower((string)($tinfo_option['type'] ?? 'text'));
    $field_value = (string)($tinfo_option['value'] ?? '');
    $field_max = (int)($tinfo_option['max'] ?? 0);
    $choices = (array)($tinfo_option['choices'] ?? []);
    ?>
                                    <div class="palette-switcher-tinfo-field" data-tinfo-item="<?= themeEscape(strtolower($tinfo_option['label'] . ' ' . $dbfield)); ?>">
                                        <label class="palette-switcher-label" for="<?= themeEscape($field_id); ?>"><?= themeEscape((string)($tinfo_option['label'] ?? $dbfield)); ?></label>
<?php if ($field_type === 'dropdown' && !empty($choices)) { ?>
                                        <select id="<?= themeEscape($field_id); ?>" class="form-control palette-switcher-select" data-tinfo-field="<?= themeEscape($dbfield); ?>" data-tinfo-initial="<?= themeEscape($field_value); ?>">
<?php foreach ($choices as $choice) { ?>
                                            <option value="<?= themeEscape((string)($choice['value'] ?? '')); ?>"<?= (string)($choice['value'] ?? '') === $field_value ? ' selected' : ''; ?>><?= themeEscape((string)($choice['label'] ?? $choice['value'] ?? '')); ?></option>
<?php } ?>
                                        </select>
<?php } elseif ($field_type === 'longtext') { ?>
                                        <textarea id="<?= themeEscape($field_id); ?>" class="form-control palette-switcher-select palette-switcher-tinfo-textarea" rows="3" data-tinfo-field="<?= themeEscape($dbfield); ?>" data-tinfo-initial="<?= themeEscape($field_value); ?>"<?= $field_max > 0 ? ' maxlength="' . $field_max . '"' : ''; ?>><?= themeEscape($field_value); ?></textarea>
<?php } else { ?>
                                        <input id="<?= themeEscape($field_id); ?>" class="form-control palette-switcher-select" type="text" data-tinfo-field="<?= themeEscape($dbfield); ?>" data-tinfo-initial="<?= themeEscape($field_value); ?>" value="<?= themeEscape($field_value); ?>"<?= $field_max > 0 ? ' maxlength="' . $field_max . '"' : ''; ?> autocomplete="off">
<?php } ?>
<?php if (trim((string)($tinfo_option['help'] ?? '')) !== '') { ?>
                                        <p class="palette-switcher-help palette-switcher-field-help"><?= themeEscape((string)$tinfo_option['help']); ?></p>
<?php } ?>
                                    </div>
<?php } ?>
                                </div>
                            </details>
<?php } ?>
                        </div>
                        <p id="theme-tinfo-empty" class="palette-switcher-help mt-2" hidden><?= themeEscape(themeTranslate('Tidak ada pengaturan TInfo yang cocok.')); ?></p>
                    </div>
            </div>
        </details>

    </div>
</div>
