<?php
/**
 * Rasamala Template - Options: Section 1 (General & Layout Settings)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

$palette_file = __DIR__ . '/../palette.php';
if (is_file($palette_file)) {
    require_once $palette_file;
}
$theme_color_palette_options = [];
if (function_exists('themeAccentPalettes')) {
    foreach (themeAccentPalettes() as $palette_key => $palette_info) {
        $theme_color_palette_options[] = [
            $palette_key,
            themeTranslate($palette_info['label'] ?? ucfirst($palette_key)),
        ];
    }
}
$theme_color_palette_options[] = ['custom', themeTranslate('Custom Palette')];

return [
    'theme-color' => [
        'dbfield' => 'classic_theme_color',
        'label' => themeTranslate('Theme Color Palette'),
        'type' => 'dropdown',
        'default' => 'midnightnavygold',
        'data' => $theme_color_palette_options
    ],
    'palette-custom' => [
        'dbfield' => 'classic_palette_custom',
        'label' => themeTranslate('Custom Palette Colors'),
        'type' => 'longtext',
        'default' => '#44403c; #292524; #ca8a04; #fafaf9; #f5f5f4; #111827; #374151 | #57534e; #44403c; #eab308; #121110; #1c1917; #f8fafc; #cbd5e1',
    ],
    'color-toggle' => [
        'dbfield' => 'classic_color_toggle',
        'label' => themeTranslate('Dark/Light Mode Button'),
        'type' => 'dropdown',
        'default' => 'dark_hide',
        'data' => [
            ['auto_show', themeTranslate('Auto - Show Button (System Mode)')],
            ['auto_hide', themeTranslate('Auto - Hide Button (System Mode)')],
            ['dark_show', themeTranslate('Default Dark - Show Button')],
            ['dark_hide', themeTranslate('Default Dark - Hide Button')],
            ['light_show', themeTranslate('Default Light - Show Button')],
            ['light_hide', themeTranslate('Default Light - Hide Button')]
        ]
    ],
    'palette-switcher-show' => [
        'dbfield' => 'classic_palette_switcher_show',
        'label' => __('Theme Viewer'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'font-family' => [
        'dbfield' => 'classic_font_family',
        'label' => themeTranslate('Theme Font'),
        'type' => 'dropdown',
        'default' => 'system',
        'data' => [
            ['system', __('System Default (Outfit)')],
            ['inter', __('Inter')],
            ['roboto', __('Roboto')],
            ['poppins', __('Poppins')],
            ['playfair', __('Playfair Display (Serif)')]
        ]
    ],
    'back-to-top' => [
        'dbfield' => 'classic_back_to_top',
        'label' => themeTranslate('Back to Top Button'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'floating-info' => [
        'dbfield' => 'classic_floating_info',
        'label' => __('Tombol Info Melayang'),
        'type' => 'dropdown',
        'default' => 'libinfo',
        'data' => [
            ['libinfo', __('Show Library Info (Libinfo)')],
            ['whatsapp', __('WhatsApp Mode')],
            ['hide', __('Hide')]
        ]
    ],
    'whatsapp-number' => [
        'dbfield' => 'classic_whatsapp_number',
        'label' => __('Nomor WhatsApp (dengan Kode Negara)'),
        'type' => 'text',
        'help' => __('Gunakan format internasional tanpa tanda + atau spasi, misalnya 628123456789.'),
        'default' => '628123456789'
    ],
    'whatsapp-title' => [
        'dbfield' => 'classic_whatsapp_title',
        'label' => __('Judul Layanan WhatsApp'),
        'type' => 'text',
        'default' => 'Layanan Chat WhatsApp'
    ],
    'service-hours' => [
        'dbfield' => 'classic_service_hours',
        'label' => __('Jam Layanan'),
        'type' => 'text',
        'default' => 'Senin - Jumat (08:00 - 16:00)'
    ],
    'whatsapp-desc' => [
        'dbfield' => 'classic_whatsapp_desc',
        'label' => __('Deskripsi Singkat WhatsApp (Format: nama_orang; pesan singkat)'),
        'type' => 'longtext',
        'help' => __('Format: nama pengirim; pesan singkat. Contoh: Pustakawan; Halo, ada yang bisa kami bantu?'),
        'default' => 'Pustakawan; Halo, ada yg bisa kami bantu ?'
    ],
    'whatsapp-categories' => [
        'dbfield' => 'classic_whatsapp_categories',
        'label' => __('Template Pesan WhatsApp (pisahkan field dengan titik koma, contoh: Nama; Nomor Anggota (opsional); Pertanyaan)'),
        'type' => 'longtext',
        'default' => 'Nama; Nomor Anggota (opsional); Pertanyaan'
    ],
];
