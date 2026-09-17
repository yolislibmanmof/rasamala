<?php
/**
 * Helper Module for Rasamala Template - Theme Section & Display Modes
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeColorModeSetting')) {
  function themeColorModeSetting($sysconf_param = null)
  {
    $value = strtolower(trim((string)themeEffectiveTemplateValue('classic_color_toggle', 'auto_show', $sysconf_param)));

    if ($value === '1' || $value === 'show') {
      return 'auto_show';
    }
    if ($value === '0' || $value === 'hide') {
      return 'auto_hide';
    }

    $allowed = ['auto_show', 'auto_hide', 'dark_show', 'dark_hide', 'light_show', 'light_hide'];
    return in_array($value, $allowed, true) ? $value : 'auto_show';
  }
}

if (!function_exists('themeColorModeDefault')) {
  function themeColorModeDefault($sysconf_param = null)
  {
    $setting = themeColorModeSetting($sysconf_param);

    if (strpos($setting, 'dark_') === 0) {
      return 'dark';
    }
    if (strpos($setting, 'light_') === 0) {
      return 'light';
    }

    return 'auto';
  }
}

if (!function_exists('themeColorModeToggleVisible')) {
  function themeColorModeToggleVisible($sysconf_param = null)
  {
    return substr(themeColorModeSetting($sysconf_param), -5) === '_show';
  }
}

if (!function_exists('themeHomepageHeroModeOptions')) {
  function themeHomepageHeroModeOptions()
  {
    return [
      'yes',
      'no',
    ];
  }
}

if (!function_exists('themeHeroBackgroundStyleOptions')) {
  function themeHeroBackgroundStyleOptions()
  {
    return function_exists('themeBackgroundStyleOptions')
      ? themeBackgroundStyleOptions(true)
      : ['none', 'soft-gradient', 'aurora-glow', 'mesh-light', 'solid-theme', 'minimal-surface', 'custom'];
  }
}

if (!function_exists('themeHeroBackgroundStyle')) {
  function themeHeroBackgroundStyle($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    $style = strtolower(trim((string)themeEffectiveTemplateValue('classic_hero_background_style', 'none', $source)));
    $aliases = [
      'default' => 'none',
      'gradient' => 'soft-gradient',
      'aurora' => 'aurora-glow',
      'mesh' => 'mesh-light',
      'solid' => 'solid-theme',
      'minimal' => 'minimal-surface',
    ];
    $style = $aliases[$style] ?? $style;

    return in_array($style, themeHeroBackgroundStyleOptions(), true) ? $style : 'none';
  }
}

if (!function_exists('themeHomepageHeroMode')) {
  function themeHomepageHeroMode($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    $mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_hero_fullscreen_mode', '', $source)));
    if ($mode === '1' || $mode === 'show' || $mode === 'fullscreen' || $mode === 'yes') {
      return 'yes';
    }
    if ($mode === '0' || $mode === 'hide' || $mode === 'no') {
      return 'no';
    }
    return (int)themeEffectiveTemplateValue('classic_homepage_only_hero', 0, $source) === 1
      ? 'yes'
      : 'no';
  }
}

if (!function_exists('themeHomepageHeroInsideContent')) {
  function themeHomepageHeroInsideContent($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    $value = strtolower(trim((string)themeEffectiveTemplateValue('classic_hero_topics_show', 'none', $source)));

    if (in_array($value, ['1', 'yes', 'show', 'hero', 'topics', 'true', 'on'], true)) {
      return 'topics';
    }
    if (in_array($value, ['news', 'latest', 'latest_content', 'news_cards'], true)) {
      return 'news';
    }
    if (in_array($value, ['popular', 'new_update', 'top_reader'], true)) {
      return $value;
    }
    return 'none';
  }
}

if (!function_exists('themeHomepageHeroShowsTopics')) {
  function themeHomepageHeroShowsTopics($sysconf_param = null)
  {
    return themeHomepageHeroInsideContent($sysconf_param) === 'topics';
  }
}

if (!function_exists('themeHomepageHeroShowsInfo')) {
  function themeHomepageHeroShowsInfo($sysconf_param = null)
  {
    return false;
  }
}

if (!function_exists('themeHomepageOnlyHero')) {
  function themeHomepageOnlyHero($sysconf_param = null)
  {
    return themeHomepageHeroMode($sysconf_param) !== 'no';
  }
}

if (!function_exists('themeHomepageSectionOrder')) {
  function themeHomepageSectionOrder($sysconf_param = null)
  {
    $order_raw = (string)themeEffectiveTemplateValue('classic_homepage_section_order', 'topic;news;popular;new-collection;top-reader;map', $sysconf_param);
    $sections = array_filter(array_map(function ($item) {
      $clean = strtolower(trim(str_replace(' ', '', $item)));
      if (in_array($clean, ['new_collection', 'new_update', 'new-collection', 'latest', 'latest_content'], true)) {
        return 'new-collection';
      }
      if (in_array($clean, ['top_reader', 'top-reader'], true)) {
        return 'top-reader';
      }
      if (in_array($clean, ['topics', 'topic'], true)) {
        return 'topic';
      }
      return $clean;
    }, explode(';', $order_raw)));

    return array_values(array_unique($sections));
  }
}

if (!function_exists('themeHomepageSectionEnabled')) {
  function themeHomepageSectionEnabled($section, $sysconf_param = null)
  {
    $section = strtolower(trim((string)$section));
    switch ($section) {
      case 'topic':
      case 'topics':
        return (int)themeEffectiveTemplateValue('classic_topic_show', 1, $sysconf_param) === 1;
      case 'news':
        return (int)themeEffectiveTemplateValue('classic_home_content_cards_show', 1, $sysconf_param) === 1;
      case 'popular':
        return (int)themeEffectiveTemplateValue('classic_popular_collection', 1, $sysconf_param) === 1;
      case 'new-collection':
      case 'new_collection':
      case 'new_update':
      case 'latest':
      case 'latest_content':
        return (int)themeEffectiveTemplateValue('classic_new_collection', 1, $sysconf_param) === 1;
      case 'top-reader':
      case 'top_reader':
        return (int)themeEffectiveTemplateValue('classic_top_reader', 1, $sysconf_param) === 1;
      case 'map':
        return themeShowMap($sysconf_param) || themeShowSocialMedia($sysconf_param);
      default:
        return false;
    }
  }
}

if (!function_exists('themeFooterEnabled')) {
  function themeFooterEnabled($sysconf_param = null, $is_homepage = false)
  {
    $footer_show = (int)themeEffectiveTemplateValue('classic_footer_show', 1, $sysconf_param) === 1;
    $theme_viewer_preview = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf_param) === 1;
    if (!$footer_show && !$theme_viewer_preview) {
      return false;
    }

    if ($is_homepage && !themePresetIsCustom($sysconf_param)) {
      $definition = themePresetDefinition($sysconf_param);
      return (bool)$definition['footer_home'];
    }

    // Fullscreen hero no longer suppresses the homepage sections or footer;
    // each section follows its own TInfo show/hide setting.
    return true;
  }
}

if (!function_exists('themeBackgroundAnimationOptions')) {
  function themeBackgroundAnimationOptions()
  {
    return ['none', 'particles', 'rain', 'grid', 'twinkle', 'zen-ripples', 'neural-network', 'starfield-warp', 'floating-embers'];
  }
}

if (!function_exists('themeBackgroundAnimation')) {
  function themeBackgroundAnimation()
  {
    global $sysconf;

    $animation = strtolower(trim((string)themeEffectiveTemplateValue('classic_hero_background_animation', 'none', $sysconf)));
    if (!in_array($animation, themeBackgroundAnimationOptions(), true)) {
      return 'particles';
    }

    return $animation;
  }
}

if (!function_exists('themeMapSocialMode')) {
  function themeMapSocialMode($sysconf)
  {
    $mode = themeEffectiveTemplateValue('classic_map', 'all', $sysconf);
    if ($mode === 1 || $mode === true || $mode === '1' || $mode === 'show') {
      return 'all';
    }
    if ($mode === 0 || $mode === false || $mode === '0' || $mode === 'hide') {
      return 'hide_all';
    }

    $mode = strtolower(trim((string)$mode));
    $valid_modes = ['all', 'hide_all', 'hide_map', 'hide_social'];
    return in_array($mode, $valid_modes, true) ? $mode : 'all';
  }
}

if (!function_exists('themeShowMap')) {
  function themeShowMap($sysconf)
  {
    return in_array(themeMapSocialMode($sysconf), ['all', 'hide_social'], true);
  }
}

if (!function_exists('themeShowSocialMedia')) {
  function themeShowSocialMedia($sysconf)
  {
    return in_array(themeMapSocialMode($sysconf), ['all', 'hide_map'], true);
  }
}
