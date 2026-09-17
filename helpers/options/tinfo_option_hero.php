<?php
/**
 * Rasamala Template - Options: Section 3 (Hero & Search Bar Settings)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

$background_helper = __DIR__ . '/../background.php';
if (is_file($background_helper)) {
    require_once $background_helper;
}
$background_style_options = [];
if (function_exists('themeBackgroundStyles')) {
    foreach (themeBackgroundStyles() as $background_key => $background_definition) {
        $background_style_options[] = [
            $background_key,
            themeTranslate($background_definition['label'] ?? ucfirst(str_replace('-', ' ', $background_key))),
        ];
    }
}

return [
    'hero-text' => [
        'dbfield' => 'classic_hero_text',
        'label' => themeTranslate('Search Title Text'),
        'type' => 'text',
        'default' => 'Search Library Collection'
    ],
    'hero-text-size' => [
        'dbfield' => 'classic_hero_text_size',
        'label' => themeTranslate('Search Title Text Size'),
        'type' => 'dropdown',
        'default' => 'small',
        'data' => [
            ['small', themeTranslate('Small')],
            ['medium', themeTranslate('Medium')],
            ['large', themeTranslate('Large')]
        ]
    ],
    'search-size' => [
        'dbfield' => 'classic_search_size',
        'label' => themeTranslate('Search Box Size'),
        'type' => 'dropdown',
        'default' => 'small',
        'data' => [
            ['small', themeTranslate('Small')],
            ['medium', themeTranslate('Medium')],
            ['large', themeTranslate('Large')]
        ]
    ],
    'search-placeholder' => [
        'dbfield' => 'classic_search_placeholder',
        'label' => themeTranslate('Search Placeholder'),
        'type' => 'text',
        'default' => 'silakan cari disini'
    ],
    'hero-fullscreen-mode' => [
        'dbfield' => 'classic_hero_fullscreen_mode',
        'label' => themeTranslate('Hero Section Fullscreen Mode'),
        'type' => 'dropdown',
        'default' => 'yes',
        'data' => [
            ['yes', themeTranslate('Yes - Fullscreen Hero')],
            ['no', themeTranslate('No - Standard Homepage')]
        ]
    ],
    'hero-topics-placement' => [
        'dbfield' => 'classic_hero_topics_show',
        'label' => themeTranslate('Inside Fullscreen Hero'),
        'type' => 'dropdown',
        'default' => 'topics',
        'data' => [
            ['none', themeTranslate('None - Keep Below Search')],
            ['topics', themeTranslate('Topics')],
            ['news', themeTranslate('Latest Content (News Cards)')],
            ['popular', themeTranslate('Popular Among Our Collection')],
            ['new_update', themeTranslate('New Collection + Update')],
            ['top_reader', themeTranslate('Top Reader of the Year')]
        ]
    ],
    'hero-background-style' => [
        'dbfield' => 'classic_hero_background_style',
        'label' => themeTranslate('Background Style'),
        'type' => 'dropdown',
        'default' => 'aurora-glow',
        'data' => $background_style_options
    ],
    'background-image-size' => [
        'dbfield' => 'classic_background_image_size',
        'label' => themeTranslate('Background Image Size'),
        'type' => 'dropdown',
        'default' => 'crop',
        'data' => function_exists('themeBackgroundImageSizeOptions')
            ? array_map(function ($label, $key) { return [$key, $label]; }, themeBackgroundImageSizeOptions(), array_keys(themeBackgroundImageSizeOptions()))
            : [['crop', themeTranslate('Crop / Cover')]]
    ],
    'background-image-position' => [
        'dbfield' => 'classic_background_image_position',
        'label' => themeTranslate('Background Image Position'),
        'type' => 'dropdown',
        'default' => 'center',
        'data' => function_exists('themeBackgroundImagePositionOptions')
            ? array_map(function ($label, $key) { return [$key, $label]; }, themeBackgroundImagePositionOptions(), array_keys(themeBackgroundImagePositionOptions()))
            : [['center', themeTranslate('Center')]]
    ],
    'background-image-filter' => [
        'dbfield' => 'classic_background_image_filter',
        'label' => themeTranslate('Background Image Filter'),
        'type' => 'dropdown',
        'default' => 'readable',
        'data' => function_exists('themeBackgroundImageFilterOptions')
            ? array_map(function ($label, $key) { return [$key, $label]; }, themeBackgroundImageFilterOptions(), array_keys(themeBackgroundImageFilterOptions()))
            : [['none', themeTranslate('No Filter')]]
    ],
    'background-image-blur' => [
        'dbfield' => 'classic_background_image_blur',
        'label' => themeTranslate('Background Image Blur'),
        'type' => 'dropdown',
        'default' => '8',
        'data' => function_exists('themeBackgroundImageBlurOptions')
            ? array_map(function ($label, $key) { return [$key, $label]; }, themeBackgroundImageBlurOptions(), array_keys(themeBackgroundImageBlurOptions()))
            : [['none', themeTranslate('No Blur')]]
    ],
    'background-image-overlay' => [
        'dbfield' => 'classic_background_image_overlay',
        'label' => themeTranslate('Background Image Overlay'),
        'type' => 'dropdown',
        'default' => 'auto',
        'data' => function_exists('themeBackgroundImageOverlayOptions')
            ? array_map(function ($label, $key) { return [$key, $label]; }, themeBackgroundImageOverlayOptions(), array_keys(themeBackgroundImageOverlayOptions()))
            : [['none', themeTranslate('No Overlay')]]
    ],
    'background-style-custom' => [
        'dbfield' => 'classic_background_style_custom',
        'label' => themeTranslate('Custom Background Style'),
        'type' => 'longtext',
        'default' => 'linear-gradient(145deg, color-mix(in srgb, var(--theme-primary) 32%, var(--theme-background)) 0%, color-mix(in srgb, var(--theme-accent) 22%, var(--theme-surface)) 100%) | linear-gradient(145deg, color-mix(in srgb, var(--theme-dark-primary) 36%, var(--theme-dark-background)) 0%, color-mix(in srgb, var(--theme-dark-accent) 24%, var(--theme-dark-surface)) 100%)'
    ],
    'hero-background-animation' => [
        'dbfield' => 'classic_hero_background_animation',
        'label' => themeTranslate('Background Animation'),
        'type' => 'dropdown',
        'default' => 'neural-network',
        'data' => [
            ['none', themeTranslate('None')],
            ['particles', themeTranslate('Floating Glyphs')],
            ['rain', themeTranslate('Code Rain')],
            ['grid', themeTranslate('Moving Grid')],
            ['twinkle', themeTranslate('Twinkling Stars')],
            ['zen-ripples', themeTranslate('Zen Ripples')],
            ['neural-network', themeTranslate('Neural Network')],
            ['starfield-warp', themeTranslate('Starfield Warp')],
            ['floating-embers', themeTranslate('Floating Embers')]
        ]
    ],
    'background-animation-speed' => [
        'dbfield' => 'classic_background_animation_speed',
        'label' => themeTranslate('Background Animation Speed'),
        'type' => 'dropdown',
        'default' => 'fast',
        'data' => [
            ['slow', themeTranslate('Slow')],
            ['normal', themeTranslate('Normal')],
            ['fast', themeTranslate('Fast')]
        ]
    ],
    'cursor-particles' => [
        'dbfield' => 'classic_cursor_particles',
        'label' => themeTranslate('Cursor Particle Effect'),
        'type' => 'dropdown',
        'default' => 'none',
        'data' => [
            ['auto', themeTranslate('Auto (Device Detection)')],
            ['low', themeTranslate('Light')],
            ['medium', themeTranslate('Medium')],
            ['high', themeTranslate('Optimal')],
            ['none', themeTranslate('Disabled')]
        ]
    ],
    'cursor-custom-icon' => [
        'dbfield' => 'classic_cursor_custom_icon',
        'label' => themeTranslate('Cursor Icon'),
        'type' => 'dropdown',
        'default' => 'default',
        'data' => [
            ['default', themeTranslate('Default Browser')],
            ['neon-comet', themeTranslate('Neon Comet')],
            ['pixel-sword', themeTranslate('Pixel Sword')],
            ['electric-bolt', themeTranslate('Electric Bolt')],
            ['ink-brush', themeTranslate('Ink Brush')],
            ['rainbow-ribbon', themeTranslate('Rainbow Ribbon')]
        ]
    ],
];
