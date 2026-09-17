<?php
/**
 * UI Helper Module - Header, Meta & Logo Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeHeaderRequestUri')) {
  function themeHeaderRequestUri()
  {
    $parsed_uri = parse_url($_SERVER['REQUEST_URI'] ?? '');
    $raw_path = $parsed_uri['path'] ?? '/';
    $raw_query = isset($parsed_uri['query']) ? '?' . $parsed_uri['query'] : '';

    return preg_replace('/[^a-zA-Z0-9\/=?&_.-]/', '', strip_tags(urldecode($raw_path . $raw_query)));
  }
}

if (!function_exists('themeHeaderDocumentLang')) {
  function themeHeaderDocumentLang($sysconf_param = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    $document_lang = (string)($_COOKIE['select_lang'] ?? $source['default_lang'] ?? 'en_US');
    $document_lang = preg_replace('/[^A-Za-z0-9_-]/', '', $document_lang);

    return str_replace('_', '-', $document_lang ?: 'en-US');
  }
}

if (!function_exists('themeHeaderMetaImageSrc')) {
  function themeHeaderMetaImageSrc($sysconf_param = null, $image_src = null, $opac = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    $meta_image_src = ($source['template']['dir'] ?? 'template/default') . '/default/img/logo.png';
    if (isset($_GET['p']) && $_GET['p'] === 'show_detail') {
      if (trim((string)$image_src) !== '') {
        $meta_image_src = (string)$image_src;
      } elseif (isset($opac) && isset($opac->image_src) && trim((string)$opac->image_src) !== '') {
        $meta_image_src = (string)$opac->image_src;
      }
    }

    return $meta_image_src;
  }
}

if (!function_exists('themeHeaderTickerSpeedValue')) {
  function themeHeaderTickerSpeedValue($sysconf_param = null)
  {
    $ticker_speed = themeEffectiveTemplateValue('classic_ticker_speed', 'normal', $sysconf_param);
    if ($ticker_speed === 'fast') {
      return '12s';
    }
    if ($ticker_speed === 'slow') {
      return '32s';
    }
    if ($ticker_speed === 'very_slow') {
      return '52s';
    }

    return '18s';
  }
}

if (!function_exists('themeHeaderFontStack')) {
  function themeHeaderFontStack($sysconf_param = null)
  {
    $font_family = themeEffectiveFontFamilyKey($sysconf_param);
    if ($font_family === 'inter') {
      return "'Inter', system-ui, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    }
    if ($font_family === 'roboto') {
      return "'Roboto', Arial, Helvetica, sans-serif";
    }
    if ($font_family === 'poppins') {
      return "'Poppins', 'Trebuchet MS', Arial, sans-serif";
    }
    if ($font_family === 'playfair') {
      return "'Playfair Display', Georgia, 'Times New Roman', serif";
    }

    return 'system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
  }
}

if (!function_exists('themeHeaderFavicon')) {
  function themeHeaderFavicon($sysconf_param = null, $imagesDisk = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    if (!$imagesDisk) {
      $imagesDisk = $GLOBALS['imagesDisk'] ?? null;
    }

    $icon = SWB . 'webicon.ico';
    if (!empty($source['webicon']) && $imagesDisk && $imagesDisk->isExists($path = 'default/' . $source['webicon'])) {
      $icon = SWB . 'images/' . $path;
    }

    return $icon;
  }
}

if (!function_exists('themeHeaderBackgroundSpeedMultiplier')) {
  function themeHeaderBackgroundSpeedMultiplier($sysconf_param = null)
  {
    $anim_speed = themeEffectiveTemplateValue('classic_background_animation_speed', 'normal', $sysconf_param);
    if ($anim_speed === 'slow') {
      return 1.5;
    }
    if ($anim_speed === 'fast') {
      return 0.65;
    }

    return 1.0;
  }
}

if (!function_exists('themeHeaderBodyClasses')) {
  function themeHeaderBodyClasses($sysconf_param, $selected_color, $effective_palette_key, $background_animation, $is_login)
  {
    $is_homepage = !isset($_GET['p']) && !isset($_GET['search']);
    $hero_mode = function_exists('themeHomepageHeroMode') ? themeHomepageHeroMode($sysconf_param) : 'no';
    $hero_background_style = function_exists('themeHeroBackgroundStyle') ? themeHeroBackgroundStyle($sysconf_param) : 'none';
    $is_homepage_only_hero = $is_homepage && themeHomepageOnlyHero($sysconf_param);
    $home_tabs_configured = (int)themeEffectiveTemplateValue('classic_home_sections_tabs', 0, $sysconf_param) === 1;
    $palette_switcher_show = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf_param) === 1;
    $search_panel_style = strtolower(trim((string)themeEffectiveTemplateValue('classic_search_panel_style', 'transparent', $sysconf_param)));
    if (!in_array($search_panel_style, ['transparent', 'solid'], true)) {
      $search_panel_style = 'transparent';
    }

    $body_classes = 'bg-light rasamala-theme';
    $body_classes .= ' rasamala-preset-' . preg_replace('/[^a-z0-9_-]+/i', '-', themePresetKey($sysconf_param));
    $body_classes .= ' rasamala-palette-' . preg_replace('/[^a-z0-9_-]+/i', '-', $effective_palette_key ?: 'warmgray');
    if ($palette_switcher_show) {
      $body_classes .= ' rasamala-has-palette-switcher';
    }

    $floating_info_body_mode = themeEffectiveTemplateValue('classic_floating_info', 'libinfo', $sysconf_param);
    if ($floating_info_body_mode == '1') {
      $floating_info_body_mode = 'libinfo';
    } elseif ($floating_info_body_mode == '0') {
      $floating_info_body_mode = 'hide';
    }
    if (!in_array($floating_info_body_mode, ['libinfo', 'whatsapp', 'hide'], true)) {
      $floating_info_body_mode = 'libinfo';
    }
    $body_classes .= ' rasamala-floating-info-' . $floating_info_body_mode;

    if (function_exists('themePaletteIsDark') && themePaletteIsDark($selected_color ?? [])) {
      $body_classes .= ' rasamala-palette-dark';
    }

    $body_classes .= ' rasamala-search-panels-' . $search_panel_style;
    $current_page_class = isset($_GET['p']) ? (string)$_GET['p'] : ($is_homepage ? 'home' : 'search');
    $body_classes .= ' rasamala-page-' . preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($current_page_class));

    if ($background_animation !== 'none') {
      $body_classes .= ' rasamala-background-animation-active rasamala-background-animation-' . $background_animation;
    }
    if ($is_homepage_only_hero) {
      $body_classes .= ' rasamala-home-hero-only';
    }
    $body_classes .= ' rasamala-home-hero-mode-' . preg_replace('/[^a-z0-9_-]+/i', '-', $hero_mode);
    $background_style_class = preg_replace('/[^a-z0-9_-]+/i', '-', $hero_background_style);
    // Keep the legacy hero-prefixed class for compatibility while exposing a
    // page-wide class for the Background Style control.
    $body_classes .= ' rasamala-background-style-' . $background_style_class;
    $body_classes .= ' rasamala-hero-bg-style-' . $background_style_class;
    if (function_exists('themeBackgroundStyleIsImage') && themeBackgroundStyleIsImage($hero_background_style, $sysconf_param)) {
      $body_classes .= ' rasamala-background-image-active';
    }
    if ($is_homepage) {
      $body_classes .= ' rasamala-home-layout-' . ($home_tabs_configured ? 'tabs' : 'standard');
    }
    if (!$is_login) {
      $body_classes .= ' rasamala-debug-hidden';
    }

    if ($is_homepage && !$is_homepage_only_hero && function_exists('themeHomepageSectionOrder') && function_exists('themeHomepageSectionEnabled')) {
      $home_visible_sections = [];
      foreach (themeHomepageSectionOrder($sysconf_param) as $home_section_key) {
        if (themeHomepageSectionEnabled($home_section_key, $sysconf_param)) {
          $home_visible_sections[] = $home_section_key;
        }
      }

      if (count($home_visible_sections) > 0 && count($home_visible_sections) <= 1) {
        $body_classes .= ' rasamala-home-few-sections';
      }
      if (count($home_visible_sections) === 1 && $home_visible_sections[0] === 'topic') {
        $body_classes .= ' rasamala-home-topic-only';
      }
    }

    $mobile_bottom_nav_enabled = (($sysconf_param['template']['classic_mobile_bottom_nav_show'] ?? 1) == 1);
    $body_classes .= $mobile_bottom_nav_enabled ? ' mobile-bottom-nav-enabled' : ' mobile-bottom-nav-hidden';

    return $body_classes;
  }
}

if (!function_exists('themeHeaderRuntimeConfig')) {
  function themeHeaderRuntimeConfig($sysconf_param, $selected_color, $selected_dark_color, $font_stack, $ticker_speed_val, $is_login)
  {
    return [
      'colorModeDefault' => themeColorModeDefault($sysconf_param),
      'colorModeToggleVisible' => themeColorModeToggleVisible($sysconf_param),
      'darkCssUrl' => assetsVersioned('css/theme-dark.css'),
      'autoCoverMode' => themeAutoCoverMode($sysconf_param),
      'debugHiderEnabled' => !$is_login,
      'themeVars' => [
        '--theme-primary' => $selected_color['primary'],
        '--theme-primary-hover' => $selected_color['hover'],
        '--theme-secondary' => $selected_color['secondary'],
        '--theme-accent' => $selected_color['accent'],
        '--theme-background' => $selected_color['background'],
        '--theme-surface' => $selected_color['surface'],
        '--theme-text' => $selected_color['text'],
        '--theme-muted' => $selected_color['muted'],
        '--theme-muted-on-background' => $selected_color['muted_on_background'] ?? $selected_color['muted'],
        '--theme-muted-on-surface' => $selected_color['muted_on_surface'] ?? $selected_color['muted'],
        '--theme-primary-rgb' => $selected_color['rgb'],
        '--theme-accent-rgb-value' => $selected_color['accent_rgb'],
        '--theme-on-primary' => $selected_color['on_primary'],
        '--theme-on-primary-hover' => $selected_color['on_primary_hover'],
        '--theme-on-secondary' => $selected_color['on_secondary'],
        '--theme-on-accent' => $selected_color['on_accent'],
        '--theme-on-background' => $selected_color['on_background'],
        '--theme-on-surface' => $selected_color['on_surface'],
        '--theme-dark-primary' => $selected_dark_color['primary'],
        '--theme-dark-primary-hover' => $selected_dark_color['hover'],
        '--theme-dark-secondary' => $selected_dark_color['secondary'],
        '--theme-dark-accent' => $selected_dark_color['accent'],
        '--theme-dark-background' => $selected_dark_color['background'],
        '--theme-dark-surface' => $selected_dark_color['surface'],
        '--theme-dark-text' => $selected_dark_color['text'],
        '--theme-dark-muted' => $selected_dark_color['muted'],
        '--theme-dark-muted-on-background' => $selected_dark_color['muted_on_background'] ?? $selected_dark_color['muted'],
        '--theme-dark-muted-on-surface' => $selected_dark_color['muted_on_surface'] ?? $selected_dark_color['muted'],
        '--theme-dark-primary-rgb' => $selected_dark_color['rgb'],
        '--theme-dark-accent-rgb-value' => $selected_dark_color['accent_rgb'],
        '--theme-dark-on-primary' => $selected_dark_color['on_primary'],
        '--theme-dark-on-primary-hover' => $selected_dark_color['on_primary_hover'],
        '--theme-dark-on-secondary' => $selected_dark_color['on_secondary'],
        '--theme-dark-on-accent' => $selected_dark_color['on_accent'],
        '--theme-dark-on-background' => $selected_dark_color['on_background'],
        '--theme-dark-on-surface' => $selected_dark_color['on_surface'],
        '--color-primary' => 'var(--theme-primary)',
        '--color-secondary' => 'var(--theme-secondary)',
        '--color-accent' => 'var(--theme-accent)',
        '--color-background' => 'var(--theme-background)',
        '--color-surface' => 'var(--theme-surface)',
        '--color-text' => 'var(--theme-on-background)',
        '--color-muted' => 'var(--theme-muted-on-background)',
        '--color-on-primary' => 'var(--theme-on-primary)',
        '--color-on-secondary' => 'var(--theme-on-secondary)',
        '--color-on-accent' => 'var(--theme-on-accent)',
        '--rasamala-light-bg' => 'var(--theme-background)',
        '--rasamala-text-primary' => 'var(--theme-on-background)',
        '--rasamala-text-secondary' => 'var(--theme-secondary)',
        '--rasamala-text-muted' => 'var(--theme-muted-on-background)',
        '--rasamala-surface' => 'var(--theme-surface)',
        '--rasamala-accent' => $selected_color['accent'],
        '--rasamala-accent-hover' => $selected_color['accent_hover'],
        '--rasamala-readable-accent' => 'color-mix(in srgb, var(--theme-accent) 72%, var(--theme-text) 28%)',
        '--theme-accent-color' => $selected_color['accent'],
        '--theme-accent-rgb' => $selected_color['accent_rgb'],
        '--theme-accent-glow' => 'rgba(' . $selected_color['accent_rgb'] . ', 0.8)',
        '--theme-accent-glow-half' => 'rgba(' . $selected_color['accent_rgb'] . ', 0.4)',
        '--rasamala-chrome-bg' => 'color-mix(in srgb, var(--theme-primary) 92%, #000 8%)',
        '--rasamala-chrome-border' => 'color-mix(in srgb, var(--theme-primary) 38%, transparent)',
        '--rasamala-chrome-text' => 'var(--theme-on-primary)',
        '--rasamala-chrome-text-muted' => 'color-mix(in srgb, var(--theme-on-primary) 76%, transparent)',
        '--bs-body-bg' => 'var(--theme-background)',
        '--bs-body-color' => 'var(--theme-on-background)',
        '--bs-secondary-color' => 'var(--theme-muted-on-background)',
        '--rasamala-font-stack' => $font_stack,
        '--ticker-speed' => $ticker_speed_val,
      ],
    ];
  }
}

if (!function_exists('themeHeaderContext')) {
  function themeHeaderContext($sysconf_param = null, $imagesDisk = null, $is_login = false, $image_src = null, $opac = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    $effective_palette_key = strtolower((string)themeEffectiveAccentColorKey($source));
    $selected_color = themeSelectedAccentColor($effective_palette_key, $source);
    $selected_dark_color = themeSelectedDarkAccentColor($effective_palette_key, $source);
    $font_stack = themeHeaderFontStack($source);
    $ticker_speed_val = themeHeaderTickerSpeedValue($source);
    $is_login = !empty($is_login);
    $background_animation = themeBackgroundAnimation();
    $palette_switcher_show = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $source) === 1;
    $custom_css = themeSanitizeCustomCss($source['template']['classic_custom_css'] ?? '');
    $custom_background_css = function_exists('themeBackgroundStyleRuntimeCss')
      ? themeBackgroundStyleRuntimeCss($source)
      : '';
    if ($custom_background_css !== '') {
      $custom_css = trim($custom_css . "\n" . $custom_background_css);
    }

    return [
      'request_uri' => themeHeaderRequestUri(),
      'meta_image_src' => themeHeaderMetaImageSrc($source, $image_src, $opac),
      'document_lang' => themeHeaderDocumentLang($source),
      'selected_color' => $selected_color,
      'selected_dark_color' => $selected_dark_color,
      'header_config' => themeHeaderRuntimeConfig($source, $selected_color, $selected_dark_color, $font_stack, $ticker_speed_val, $is_login),
      'custom_css' => $custom_css,
      'icon' => themeHeaderFavicon($source, $imagesDisk),
      'body_classes' => themeHeaderBodyClasses($source, $selected_color, $effective_palette_key, $background_animation, $is_login),
      'background_animation' => $background_animation,
      'background_animation_enabled' => $background_animation !== 'none',
      'palette_switcher_show' => $palette_switcher_show,
      'speed_mult' => themeHeaderBackgroundSpeedMultiplier($source),
    ];
  }
}

if (!function_exists('themeLibraryLogoHtml')) {
  function themeLibraryLogoHtml($sysconf, $imagesDisk = null, $class = 'navbar-brand-img')
  {
    $logo_image = trim((string)($sysconf['logo_image'] ?? ''));
    if ($logo_image === '') {
        return '';
    }

    if (!$imagesDisk) {
        $imagesDisk = $GLOBALS['imagesDisk'] ?? null;
    }

    $path = 'default/' . $logo_image;
    if ($imagesDisk && $imagesDisk->isExists($path)) {
        $src = themeEscape(SWB . 'images/' . $path);
        return '<img class="' . themeEscape($class) . '" src="' . $src . '" alt="" aria-hidden="true" loading="eager">';
    }

    if (defined('SB') && is_file(SB . 'images/' . $path)) {
        $src = themeEscape(SWB . 'images/' . $path);
        return '<img class="' . themeEscape($class) . '" src="' . $src . '" alt="" aria-hidden="true" loading="eager">';
    }

    return '';
  }
}
