<?php
/**
 * Rasamala Template - Options: Section 12 (Visitor Log Page Settings)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
    'visitor-log-voice' => [
        'dbfield' => 'visitor_log_voice',
        'label' => themeTranslate('Visitor Log Voice'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Enable')],
            [0, themeTranslate('Disable')]
        ]
    ],
    'visitor-quote' => [
        'dbfield' => 'visitor_quote',
        'label' => themeTranslate('Visitor Page Greeting Quote'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Enable')],
            [0, themeTranslate('Disable')]
        ]
    ],
    'visitor-title' => [
        'dbfield' => 'visitor_title',
        'label' => themeTranslate('Kiosk Page Main Title'),
        'type' => 'text',
        'default' => ''
    ],
    'visitor-subtitle' => [
        'dbfield' => 'visitor_subtitle',
        'label' => themeTranslate('Visitor Kiosk Page Subtitle'),
        'type' => 'text',
        'default' => 'Visitor Check-In Portal'
    ],
    'visitor-institution-select-label' => [
        'dbfield' => 'visitor_institution_select_label',
        'label' => themeTranslate('Visitor Faculty / Institution Label'),
        'type' => 'text',
        'default' => 'Pilih Fakultas / Institusi'
    ],
    'visitor-institution-options' => [
        'dbfield' => 'visitor_institution_options',
        'label' => themeTranslate('Visitor Faculty / Institution List'),
        'type' => 'longtext',
        'help' => themeTranslate('Format: kode(label); kode(label); other. Contoh: feb(Fakultas Ekonomi); fpsi(Psikologi); other.'),
        'default' => $rasamala_default_visitor_institution_options ?? '',
        'width' => '100',
        'max' => 5000
    ],
    'visitor-theme-toggle' => [
        'dbfield' => 'visitor_theme_toggle',
        'label' => themeTranslate('Visitor Page Dark Mode Toggle Button'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, themeTranslate('Enable')],
            [0, themeTranslate('Disable')]
        ]
    ],
    'visitor-layout-style' => [
        'dbfield' => 'visitor_layout_style',
        'label' => themeTranslate('Visitor Page Design (Guestbook)'),
        'type' => 'dropdown',
        'default' => 'kiosk',
        'data' => [
            ['kiosk', themeTranslate('Kiosk Mode (Center Card with Large Clock)')],
            ['split', themeTranslate('Split Layout (Left Form & Right Guide)')]
        ]
    ],
    'visitor-split-title' => [
        'dbfield' => 'visitor_split_title',
        'label' => themeTranslate('Visitor Split Layout Guide Title'),
        'type' => 'text',
        'default' => 'Petunjuk Penggunaan'
    ],
    'visitor-split-steps' => [
        'dbfield' => 'visitor_split_steps',
        'label' => themeTranslate('Visitor Split Layout Guide Steps'),
        'type' => 'longtext',
        'help' => themeTranslate('Satu langkah per baris dengan format: ikon | judul | keterangan. Contoh: fas fa-id-card | Isi Identitas | Scan kartu anggota.'),
        'default' => $rasamala_default_visitor_split_steps ?? '',
        'width' => '100',
        'max' => 5000
    ],
];
