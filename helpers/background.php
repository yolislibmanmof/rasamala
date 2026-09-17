<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: background.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeBackgroundStyles')) {
  function themeBackgroundStyles()
  {
    static $styles = null;
    if ($styles === null) {
      $styles = require __DIR__ . '/backgrounds/theme_background_styles.php';
      $image_directory = dirname(__DIR__) . '/assets/images/backgrounds';
      $image_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg'];
      $image_files = is_dir($image_directory) ? scandir($image_directory) : [];
      $used_keys = array_fill_keys(array_keys($styles), true);

      foreach ($image_files as $image_file) {
        if ($image_file === '.' || $image_file === '..' || !is_file($image_directory . DIRECTORY_SEPARATOR . $image_file)) {
          continue;
        }
        $extension = strtolower((string)pathinfo($image_file, PATHINFO_EXTENSION));
        if (!in_array($extension, $image_extensions, true)) {
          continue;
        }

        $base_name = pathinfo($image_file, PATHINFO_FILENAME);
        $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $base_name), '-')) ?: 'background';
        $key = 'image-' . $slug;

        // Built-in theme-colored SVG styles are handled by dynamic SVG layers
        // in parts/background_layers.php. Do not expose duplicate "-2" options.
        if (!empty($styles[$key]['theme_waves']) || !empty($styles[$key]['theme_svg'])) {
          continue;
        }


        $suffix = 2;
        while (isset($used_keys[$key])) {
          $key = 'image-' . $slug . '-' . $suffix++;
        }
        $used_keys[$key] = true;

        $styles[$key] = [
          'label' => 'Image: ' . $image_file,
          'image' => true,
          'image_file' => $image_file,
          'image_url' => 'template/rasamala/assets/images/backgrounds/' . rawurlencode($image_file),
        ];
      }
    }

    return is_array($styles) ? $styles : [];
  }
}

if (!function_exists('themeBackgroundStyleRuntimeCss')) {
  function themeBackgroundStyleRuntimeCss($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    $style = function_exists('themeHeroBackgroundStyle')
      ? themeHeroBackgroundStyle($source)
      : strtolower(trim((string)themeEffectiveTemplateValue('classic_hero_background_style', 'none', $source)));
    $definition = themeBackgroundStyles()[$style] ?? [];
    if (empty($definition['image']) || empty($definition['image_url'])) {
      return function_exists('themeCustomBackgroundStyleCss') && $style === 'custom'
        ? themeCustomBackgroundStyleCss($source)
        : '';
    }

    $image_url = str_replace(['"', "'", '\\', ')', '('], '', (string)$definition['image_url']);
    $settings = function_exists('themeBackgroundImageSettings')
      ? themeBackgroundImageSettings($source)
      : ['size' => 'crop', 'position' => 'center', 'filter' => 'none', 'blur' => 'none', 'overlay' => 'none'];
    $light_declarations = function_exists('themeBackgroundImageCss')
      ? themeBackgroundImageCss($image_url, $settings, false)
      : "  background-image: url(\"{$image_url}\") !important;\n";
    $dark_declarations = function_exists('themeBackgroundImageCss')
      ? themeBackgroundImageCss($image_url, $settings, true)
      : $light_declarations;

    // Dark mode loads a late base background rule. Keep the image selector
    // more specific so the selected image remains visible after the mode
    // toggle, including when the page is in fullscreen hero mode.
    return "body.rasamala-background-style-{$style}.rasamala-background-image-active { background-image: none !important; }\n"
      . "body.rasamala-background-image-active .rasamala-background-image-layer {" . $light_declarations . "}\n"
      . "html body.rasamala-dark.rasamala-background-style-{$style}.rasamala-background-image-active { background-image: none !important; }\n"
      . "html body.rasamala-dark.rasamala-background-image-active .rasamala-background-image-layer {" . $dark_declarations . "}";
  }
}

if (!function_exists('themeBackgroundStyleOptions')) {
  function themeBackgroundStyleOptions($include_custom = true)
  {
    $keys = array_keys(themeBackgroundStyles());
    if (!$include_custom) {
      $keys = array_values(array_filter($keys, function ($key) {
        return $key !== 'custom';
      }));
    }

    return $keys;
  }
}

if (!function_exists('themeBackgroundStyleOptionLabels')) {
  function themeBackgroundStyleOptionLabels()
  {
    $labels = [];
    foreach (themeBackgroundStyles() as $key => $definition) {
      $labels[$key] = $definition['label'] ?? ucfirst(str_replace('-', ' ', $key));
    }

    return $labels;
  }
}

if (!function_exists('themeBackgroundImageSizeOptions')) {
  function themeBackgroundImageSizeOptions()
  {
    $translate = function ($label) {
      return function_exists('themeTranslate') ? themeTranslate($label) : $label;
    };

    return [
      'normal' => $translate('Normal (Original Size)'),
      'crop' => $translate('Crop / Cover'),
      'contain' => $translate('Contain (Fit)'),
      'stretch' => $translate('Stretch / Fill'),
      'tile' => $translate('Tile / Repeat'),
      'width' => $translate('Full Width'),
      'height' => $translate('Full Height'),
    ];
  }
}

if (!function_exists('themeBackgroundImagePositionOptions')) {
  function themeBackgroundImagePositionOptions()
  {
    $translate = function ($label) {
      return function_exists('themeTranslate') ? themeTranslate($label) : $label;
    };

    return [
      'center' => $translate('Center'),
      'top' => $translate('Top'),
      'bottom' => $translate('Bottom'),
      'left' => $translate('Left'),
      'right' => $translate('Right'),
      'top-left' => $translate('Top Left'),
      'top-right' => $translate('Top Right'),
      'bottom-left' => $translate('Bottom Left'),
      'bottom-right' => $translate('Bottom Right'),
    ];
  }
}

if (!function_exists('themeBackgroundImageFilterOptions')) {
  function themeBackgroundImageFilterOptions()
  {
    $translate = function ($label) {
      return function_exists('themeTranslate') ? themeTranslate($label) : $label;
    };

    return [
      'none' => $translate('No Filter'),
      'soft' => $translate('Soft'),
      'readable' => $translate('Readable'),
      'vivid' => $translate('Vivid'),
      'monochrome' => $translate('Monochrome'),
      'warm' => $translate('Warm'),
      'cool' => $translate('Cool'),
    ];
  }
}

if (!function_exists('themeBackgroundImageBlurOptions')) {
  function themeBackgroundImageBlurOptions()
  {
    $translate = function ($label) {
      return function_exists('themeTranslate') ? themeTranslate($label) : $label;
    };

    return [
      'none' => $translate('No Blur'),
      '1' => '1px',
      '2' => '2px',
      '4' => '4px',
      '8' => '8px',
    ];
  }
}

if (!function_exists('themeBackgroundImageOverlayOptions')) {
  function themeBackgroundImageOverlayOptions()
  {
    $translate = function ($label) {
      return function_exists('themeTranslate') ? themeTranslate($label) : $label;
    };

    return [
      'none' => $translate('No Overlay'),
      'auto' => $translate('Adaptive Overlay'),
      'subtle' => $translate('Subtle'),
      'readable' => $translate('Readable Overlay'),
      'dim' => $translate('Dim'),
      'accent' => $translate('Accent Tint'),
      'frosted' => $translate('Frosted'),
    ];
  }
}

if (!function_exists('themeBackgroundStyleIsImage')) {
  function themeBackgroundStyleIsImage($style = null, $sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    if ($style === null || $style === '') {
      $style = function_exists('themeHeroBackgroundStyle')
        ? themeHeroBackgroundStyle($source)
        : '';
    }

    $definition = themeBackgroundStyles()[(string)$style] ?? [];
    return !empty($definition['image']) && !empty($definition['image_url']);
  }
}

if (!function_exists('themeBackgroundImageSettings')) {
  function themeBackgroundImageSettings($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    $read = function ($key, $default) use ($source) {
      return function_exists('themeEffectiveTemplateValue')
        ? themeEffectiveTemplateValue($key, $default, $source)
        : ($source['template'][$key] ?? $default);
    };
    $fit_options = themeBackgroundImageSizeOptions();
    $position_options = themeBackgroundImagePositionOptions();
    $filter_options = themeBackgroundImageFilterOptions();
    $blur_options = themeBackgroundImageBlurOptions();
    $overlay_options = themeBackgroundImageOverlayOptions();
    $fit = strtolower(trim((string)$read('classic_background_image_size', 'crop')));
    $position = strtolower(trim((string)$read('classic_background_image_position', 'center')));
    $filter = strtolower(trim((string)$read('classic_background_image_filter', 'none')));
    $blur = strtolower(trim((string)$read('classic_background_image_blur', 'none')));
    $overlay = strtolower(trim((string)$read('classic_background_image_overlay', 'none')));

    return [
      'size' => array_key_exists($fit, $fit_options) ? $fit : 'crop',
      'position' => array_key_exists($position, $position_options) ? $position : 'center',
      'filter' => array_key_exists($filter, $filter_options) ? $filter : 'none',
      'blur' => array_key_exists($blur, $blur_options) ? $blur : 'none',
      'overlay' => array_key_exists($overlay, $overlay_options) ? $overlay : 'none',
    ];
  }
}

if (!function_exists('themeBackgroundImageCss')) {
  function themeBackgroundImageCss($image_url, $settings, $dark = false)
  {
    $settings = is_array($settings) ? $settings : [];
    $size = $settings['size'] ?? 'crop';
    $position = $settings['position'] ?? 'center';
    $filter = $settings['filter'] ?? 'none';
    $blur = $settings['blur'] ?? 'none';
    $overlay = $settings['overlay'] ?? 'none';
    $size_css = [
      'normal' => ['size' => 'auto', 'repeat' => 'no-repeat'],
      'crop' => ['size' => 'cover', 'repeat' => 'no-repeat'],
      'contain' => ['size' => 'contain', 'repeat' => 'no-repeat'],
      'stretch' => ['size' => '100% 100%', 'repeat' => 'no-repeat'],
      'tile' => ['size' => 'auto', 'repeat' => 'repeat'],
      'width' => ['size' => '100% auto', 'repeat' => 'no-repeat'],
      'height' => ['size' => 'auto 100%', 'repeat' => 'no-repeat'],
    ][$size] ?? ['size' => 'cover', 'repeat' => 'no-repeat'];
    $position_css = [
      'center' => 'center center',
      'top' => 'center top',
      'bottom' => 'center bottom',
      'left' => 'left center',
      'right' => 'right center',
      'top-left' => 'left top',
      'top-right' => 'right top',
      'bottom-left' => 'left bottom',
      'bottom-right' => 'right bottom',
    ][$position] ?? 'center center';
    $filter_css = [
      'none' => 'none',
      'soft' => 'brightness(0.96) saturate(0.92) contrast(0.98)',
      'readable' => 'brightness(0.78) saturate(0.84) contrast(1.06)',
      'vivid' => 'brightness(1.04) saturate(1.18) contrast(1.08)',
      'monochrome' => 'grayscale(0.86) contrast(1.04)',
      'warm' => 'sepia(0.18) saturate(1.08) hue-rotate(-8deg)',
      'cool' => 'saturate(0.9) hue-rotate(10deg)',
    ][$filter] ?? 'none';
    $blur_css = [
      'none' => '0px',
      '1' => '1px',
      '2' => '2px',
      '4' => '4px',
      '8' => '8px',
    ][$blur] ?? '0px';
    if ($blur_css !== '0px') {
      $filter_css .= ' blur(' . $blur_css . ')';
    }
    $overlay_css = [
      'none' => '',
      'auto' => $dark ? 'linear-gradient(rgba(0, 0, 0, 0.30), rgba(0, 0, 0, 0.30)), ' : 'linear-gradient(rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.06)), ',
      'subtle' => 'linear-gradient(rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.12)), ',
      'readable' => 'linear-gradient(rgba(0, 0, 0, ' . ($dark ? '0.36' : '0.16') . '), rgba(0, 0, 0, ' . ($dark ? '0.36' : '0.16') . ')), ',
      'dim' => 'linear-gradient(rgba(0, 0, 0, 0.42), rgba(0, 0, 0, 0.42)), ',
      'accent' => 'linear-gradient(rgba(var(--theme-primary-rgb), ' . ($dark ? '0.24' : '0.16') . '), rgba(var(--theme-primary-rgb), ' . ($dark ? '0.24' : '0.16') . ')), ',
      'frosted' => 'linear-gradient(' . ($dark ? 'rgba(15, 23, 42, 0.34)' : 'rgba(255, 255, 255, 0.24)') . ', ' . ($dark ? 'rgba(15, 23, 42, 0.34)' : 'rgba(255, 255, 255, 0.24)') . '), ',
    ][$overlay] ?? '';
    $image_url = str_replace(['"', "'", '\\', ')', '('], '', (string)$image_url);
    $background_image = $overlay_css . 'url("' . $image_url . '")';
    $scale = $blur_css !== '0px' ? 'scale(1.03)' : 'none';

    return "  background-image: {$background_image} !important;\n"
      . "  background-position: {$position_css} !important;\n"
      . "  background-repeat: {$size_css['repeat']} !important;\n"
      . "  background-size: {$size_css['size']} !important;\n"
      . "  background-attachment: fixed !important;\n"
      . "  filter: {$filter_css} !important;\n"
      . "  transform: {$scale} !important;\n"
      . "  transform-origin: center center !important;\n";
  }
}

if (!function_exists('themeSanitizeCustomBackgroundSegment')) {
  function themeSanitizeCustomBackgroundSegment($value, $max_length = 1200)
  {
    $value = trim(substr((string)($value ?? ''), 0, (int)$max_length));
    if ($value === '') {
      return '';
    }

    // Background values intentionally accept gradients, color-mix(), CSS
    // variables, and color functions, but never declarations, URLs, or at-
    // rules. This keeps the custom value a single safe background expression.
    if (preg_match('/[{};<>`]|(?:url|expression|javascript|vbscript|@import|behavior)\s*[:(]?/i', $value)) {
      return '';
    }
    if (!preg_match('/^[a-z0-9#(),.%\/:+\-_*\s\'"\[\]]+$/i', $value)) {
      return '';
    }

    return $value;
  }
}

if (!function_exists('themeSanitizeCustomBackgroundStyle')) {
  function themeSanitizeCustomBackgroundStyle($value)
  {
    $segments = explode('|', substr((string)($value ?? ''), 0, 2500), 2);
    $light = themeSanitizeCustomBackgroundSegment($segments[0] ?? '');
    $dark = themeSanitizeCustomBackgroundSegment($segments[1] ?? '');

    if ($light === '' && $dark !== '') {
      $light = $dark;
    }
    if ($dark === '') {
      $dark = $light;
    }

    return ($light !== '' && $dark !== '') ? $light . ' | ' . $dark : '';
  }
}

if (!function_exists('themeCustomBackgroundStyle')) {
  function themeCustomBackgroundStyle($sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : null;
    return themeSanitizeCustomBackgroundStyle(
      themeEffectiveTemplateValue('classic_background_style_custom', '', $source)
    );
  }
}

if (!function_exists('themeCustomBackgroundStyleCss')) {
  function themeCustomBackgroundStyleCss($sysconf_param = null)
  {
    $custom = themeCustomBackgroundStyle($sysconf_param);
    if ($custom === '') {
      return '';
    }

    $segments = explode('|', $custom, 2);
    $light = trim($segments[0] ?? '');
    $dark = trim($segments[1] ?? $light);

    return "body.rasamala-background-style-custom {\n"
      . "  background: {$light} !important;\n"
      . "  background-attachment: fixed !important;\n"
      . "}\n"
      // The extra html qualifier keeps the custom dark value above the late
      // theme-dark fallback rule without relying on unsafe inline styles.
      . "html body.rasamala-dark.rasamala-background-style-custom {\n"
      . "  background: {$dark} !important;\n"
      . "  background-attachment: fixed !important;\n"
      . "}";
  }
}
