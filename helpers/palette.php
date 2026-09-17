<?php
/**
 * Helper Module for Rasamala Template
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeAccentPalettes')) {
  function themeAccentPalettes()
  {
    static $palettes = null;
    if ($palettes === null) {
      $palettes = require __DIR__ . '/palettes/theme_color_palettes.php';
    }

    return $palettes;
  }
}

if (!function_exists('themeNormalizeAccentPaletteKey')) {
  function themeNormalizeAccentPaletteKey($color)
  {
    $key = strtolower(trim((string)($color ?? 'warmgray')));
    $aliases = [
      'cyan' => 'contemporarytechlibrary',
      'emerald' => 'warmgray',
      'forest' => 'warmgray',
      'orange' => 'warmlibrary',
      'gold' => 'warmlibrary',
      'pink' => 'warmlibrary',
      'monominimal' => 'darkgray',
    ];
    $key = $aliases[$key] ?? $key;

    if ($key === 'custom') {
      return 'custom';
    }

    $palettes = themeAccentPalettes();
    return array_key_exists($key, $palettes) ? $key : 'warmgray';
  }
}

if (!function_exists('themeNormalizeHexColor')) {
  function themeNormalizeHexColor($value, $fallback)
  {
    $value = trim((string)$value);
    if (preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
      return '#' . strtolower(ltrim($value, '#'));
    }

    return $fallback;
  }
}

if (!function_exists('themeNormalizeOptionalHexColor')) {
  function themeNormalizeOptionalHexColor($value)
  {
    $value = trim((string)$value);
    if (preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
      return '#' . strtolower(ltrim($value, '#'));
    }

    return '';
  }
}

if (!function_exists('themeHexToRgbString')) {
  function themeHexToRgbString($hex)
  {
    $hex = ltrim(themeNormalizeHexColor($hex, '#6f5b43'), '#');
    return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
  }
}

if (!function_exists('themeParseCustomPaletteString')) {
  function themeParseCustomPaletteString($value, $base)
  {
    if (strpos((string)$value, '|') !== false) {
      $value = explode('|', (string)$value, 2)[0];
    }

    $parts = array_slice(array_map('trim', preg_split('/[;\r\n]+/', substr((string)$value, 0, 320))), 0, 7);
    $keys = ['primary', 'secondary', 'accent', 'background', 'surface', 'text', 'muted'];
    $palette = [
      'label' => 'Custom Palette',
    ];

    foreach ($keys as $index => $key) {
      $palette[$key] = themeNormalizeHexColor($parts[$index] ?? '', $base[$key] ?? '#ffffff');
    }

    $palette['hover'] = themeAdjustHexColor($palette['primary'], -28);
    $palette['accent_hover'] = themeAdjustHexColor($palette['accent'], -28);
    $palette['rgb'] = themeHexToRgbString($palette['primary']);
    $palette['accent_rgb'] = themeHexToRgbString($palette['accent']);

    return themePaletteWithContrast($palette);
  }
}

if (!function_exists('themeSanitizeCustomPaletteSegment')) {
  function themeSanitizeCustomPaletteSegment($value)
  {
    $parts = array_slice(array_map('trim', preg_split('/[;\r\n]+/', substr((string)$value, 0, 320))), 0, 7);
    $clean = [];

    foreach ($parts as $part) {
      $clean[] = preg_match('/^#?[0-9a-fA-F]{6}$/', $part) ? '#' . strtolower(ltrim($part, '#')) : '';
    }

    while (!empty($clean) && end($clean) === '') {
      array_pop($clean);
    }

    return implode('; ', $clean);
  }
}

if (!function_exists('themeSanitizeCustomPaletteString')) {
  function themeSanitizeCustomPaletteString($value)
  {
    $segments = explode('|', substr((string)$value, 0, 320), 2);
    $light = themeSanitizeCustomPaletteSegment($segments[0] ?? '');
    $dark = themeSanitizeCustomPaletteSegment($segments[1] ?? '');

    return ($light !== '' && $dark !== '') ? $light . ' | ' . $dark : $light;
  }
}

if (!function_exists('themeSelectedDarkAccentColor')) {
  function themeSelectedDarkAccentColor($color, $sysconf_param = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    $key = themeNormalizeAccentPaletteKey($color);
    $light_palette = themeSelectedAccentColor($key, $source);
    $palette_definitions = themeAccentPalettes();
    $dark_definition = (isset($palette_definitions[$key]['dark']) && is_array($palette_definitions[$key]['dark']))
      ? $palette_definitions[$key]['dark']
      : [];
    $base = [
      'label' => 'Dark Palette',
      'primary' => $dark_definition['primary'] ?? ($light_palette['primary'] ?? '#1a2e40'),
      'hover' => $dark_definition['hover'] ?? themeAdjustHexColor($dark_definition['primary'] ?? ($light_palette['primary'] ?? '#1a2e40'), -28),
      'secondary' => $dark_definition['secondary'] ?? ($light_palette['secondary'] ?? '#b38f4d'),
      'accent' => $dark_definition['accent'] ?? ($light_palette['accent'] ?? '#d9534f'),
      'background' => $dark_definition['background'] ?? '#101318',
      'surface' => $dark_definition['surface'] ?? '#161a22',
      'text' => $dark_definition['text'] ?? '#f4f6f8',
      'muted' => $dark_definition['muted'] ?? '#b6bec8',
      'rgb' => themeHexToRgbString($dark_definition['primary'] ?? ($light_palette['primary'] ?? '#1a2e40')),
      'accent_rgb' => themeHexToRgbString($dark_definition['accent'] ?? ($light_palette['accent'] ?? '#d9534f')),
    ];

    if ($key === 'custom') {
      $custom_palette = (string)($source['template']['classic_palette_custom'] ?? '');
      if (strpos($custom_palette, '|') !== false) {
        $segments = explode('|', $custom_palette, 2);
        $dark_segment = trim($segments[1] ?? '');
        if ($dark_segment !== '') {
          return themeParseCustomPaletteString($dark_segment, $base);
        }
      }
    }

    return themePaletteWithContrast($base);
  }
}

if (!function_exists('themeHexColorLuminance')) {
  function themeHexColorLuminance($hex)
  {
    $hex = ltrim(themeNormalizeHexColor($hex, '#ffffff'), '#');
    $channels = [];
    for ($i = 0; $i < 3; $i++) {
      $value = hexdec(substr($hex, $i * 2, 2)) / 255;
      $channels[] = ($value <= 0.03928) ? ($value / 12.92) : pow(($value + 0.055) / 1.055, 2.4);
    }

    return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
  }
}

if (!function_exists('themeContrastRatio')) {
  function themeContrastRatio($background, $foreground)
  {
    $background_luminance = themeHexColorLuminance($background);
    $foreground_luminance = themeHexColorLuminance($foreground);
    $lighter = max($background_luminance, $foreground_luminance);
    $darker = min($background_luminance, $foreground_luminance);

    return ($lighter + 0.05) / ($darker + 0.05);
  }
}

if (!function_exists('themeReadableTextColor')) {
  function themeReadableTextColor($background, $preferred_dark = '#111827', $preferred_light = '#ffffff')
  {
    $background = themeNormalizeHexColor($background, '#ffffff');
    $dark = themeNormalizeHexColor($preferred_dark, '#111827');
    $light = themeNormalizeHexColor($preferred_light, '#ffffff');

    return themeContrastRatio($background, $dark) >= themeContrastRatio($background, $light) ? $dark : $light;
  }
}

if (!function_exists('themeAccessibleTextColor')) {
  function themeAccessibleTextColor($background, $preferred = '', $fallback = '', $minimum_ratio = 4.5)
  {
    $background = themeNormalizeHexColor($background, '#ffffff');
    $candidates = [
      themeNormalizeOptionalHexColor($preferred),
      themeNormalizeOptionalHexColor($fallback),
      '#111827',
      '#ffffff',
    ];
    $seen = [];
    $best_color = '#111827';
    $best_ratio = 0;

    foreach ($candidates as $candidate) {
      if ($candidate === '' || isset($seen[$candidate])) {
        continue;
      }

      $seen[$candidate] = true;
      $ratio = themeContrastRatio($background, $candidate);
      if ($ratio >= (float)$minimum_ratio) {
        return $candidate;
      }

      if ($ratio > $best_ratio) {
        $best_ratio = $ratio;
        $best_color = $candidate;
      }
    }

    return $best_color;
  }
}

if (!function_exists('themePaletteWithContrast')) {
  function themePaletteWithContrast($palette)
  {
    if (!is_array($palette)) {
      return [];
    }

    $palette['primary'] = themeNormalizeHexColor($palette['primary'] ?? '', '#6f5b43');
    $palette['hover'] = themeNormalizeHexColor($palette['hover'] ?? '', themeAdjustHexColor($palette['primary'], -28));
    $palette['secondary'] = themeNormalizeHexColor($palette['secondary'] ?? '', '#a58a63');
    $palette['accent'] = themeNormalizeHexColor($palette['accent'] ?? '', '#c8a24a');
    $palette['background'] = themeNormalizeHexColor($palette['background'] ?? '', '#f4f1ec');
    $palette['surface'] = themeNormalizeHexColor($palette['surface'] ?? '', '#ffffff');
    $text_candidate = themeNormalizeHexColor($palette['text'] ?? '', '#2f2a24');
    $muted_candidate = themeNormalizeHexColor($palette['muted'] ?? '', '#7a7167');
    $palette['text'] = $text_candidate;
    $palette['muted'] = $muted_candidate;
    $palette['accent_hover'] = themeNormalizeHexColor($palette['accent_hover'] ?? '', themeAdjustHexColor($palette['accent'], -28));
    $palette['rgb'] = themeHexToRgbString($palette['primary']);
    $palette['accent_rgb'] = themeHexToRgbString($palette['accent']);
    $palette['on_primary'] = themeAccessibleTextColor($palette['primary']);
    $palette['on_primary_hover'] = themeAccessibleTextColor($palette['hover']);
    $palette['on_secondary'] = themeAccessibleTextColor($palette['secondary']);
    $palette['on_accent'] = themeAccessibleTextColor($palette['accent']);
    $palette['on_background'] = themeAccessibleTextColor($palette['background'], $text_candidate);
    $palette['on_surface'] = themeAccessibleTextColor($palette['surface'], $text_candidate, $palette['on_background']);
    $palette['muted_on_background'] = themeAccessibleTextColor($palette['background'], $muted_candidate, $palette['on_background']);
    $palette['muted_on_surface'] = themeAccessibleTextColor($palette['surface'], $muted_candidate, $palette['on_surface']);
    $palette['text'] = $palette['on_background'];
    $palette['muted'] = $palette['muted_on_background'];

    return $palette;
  }
}

if (!function_exists('themePaletteIsDark')) {
  function themePaletteIsDark($palette)
  {
    if (!is_array($palette)) {
      return false;
    }

    $background_luminance = themeHexColorLuminance($palette['background'] ?? '#ffffff');
    $surface_luminance = themeHexColorLuminance($palette['surface'] ?? '#ffffff');

    return $background_luminance < 0.28 || $surface_luminance < 0.28;
  }
}

if (!function_exists('themeAdjustHexColor')) {
  function themeAdjustHexColor($hex, $amount = -22)
  {
    $hex = ltrim(themeNormalizeHexColor($hex, '#6f5b43'), '#');
    $result = '#';
    for ($i = 0; $i < 3; $i++) {
      $channel = hexdec(substr($hex, $i * 2, 2));
      $channel = max(0, min(255, $channel + (int)$amount));
      $result .= str_pad(dechex($channel), 2, '0', STR_PAD_LEFT);
    }
    return $result;
  }
}

if (!function_exists('themeSelectedAccentColor')) {
  function themeSelectedAccentColor($color, $sysconf_param = null)
  {
    global $sysconf;

    $palettes = themeAccentPalettes();
    $key = themeNormalizeAccentPaletteKey($color);
    if ($key === 'custom') {
      $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
      $base = $palettes['minimalwhite'];
      $custom_palette = trim((string)($source['template']['classic_palette_custom'] ?? ''));
      if ($custom_palette !== '') {
        return themePaletteWithContrast(themeParseCustomPaletteString($custom_palette, $base));
      }

      $primary = themeNormalizeHexColor($source['template']['classic_palette_primary'] ?? '', $base['primary']);
      $palette = [
        'label' => 'Custom Palette',
        'primary' => $primary,
        'hover' => themeAdjustHexColor($primary, -28),
        'secondary' => themeNormalizeHexColor($source['template']['classic_palette_secondary'] ?? '', $base['secondary']),
        'accent' => themeNormalizeHexColor($source['template']['classic_palette_accent'] ?? '', $base['accent']),
        'background' => themeNormalizeHexColor($source['template']['classic_palette_background'] ?? '', $base['background']),
        'surface' => themeNormalizeHexColor($source['template']['classic_palette_surface'] ?? '', $base['surface']),
        'text' => themeNormalizeHexColor($source['template']['classic_palette_text'] ?? '', $base['text']),
        'muted' => themeNormalizeHexColor($source['template']['classic_palette_muted'] ?? '', $base['muted']),
      ];
      $palette['rgb'] = themeHexToRgbString($palette['primary']);
      return themePaletteWithContrast($palette);
    }

    return themePaletteWithContrast($palettes[$key] ?? $palettes['warmgray']);
  }
}

if (!function_exists('themeEffectiveAccentColorKey')) {
  function themeEffectiveAccentColorKey($sysconf_param = null)
  {
    return themeNormalizeAccentPaletteKey(themeEffectiveTemplateValue('classic_theme_color', 'warmgray', $sysconf_param));
  }
}

if (!function_exists('themeEffectiveFontFamilyKey')) {
  function themeEffectiveFontFamilyKey($sysconf_param = null)
  {
    return (string)themeEffectiveTemplateValue('classic_font_family', 'system', $sysconf_param);
  }
}
