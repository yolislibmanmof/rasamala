<?php
/**
 * Rasamala Template - Options: Sections 10 & 11 (Librarian Page, Book Detail & Search Results Settings)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
    // Section 10: Librarian Page Settings
    'librarian-display-mode' => [
        'dbfield' => 'classic_librarian_display_mode',
        'label' => themeTranslate('Displayed Librarians'),
        'type' => 'dropdown',
        'default' => 'all',
        'data' => [
            ['all', themeTranslate('All')],
            ['librarian_senior', themeTranslate('Librarians + Senior Librarians')],
            ['senior', themeTranslate('Senior Librarians Only')],
            ['custom', themeTranslate('Custom Username')]
        ]
    ],
    'librarian-custom-usernames' => [
        'dbfield' => 'classic_librarian_custom_usernames',
        'label' => themeTranslate('Custom Librarian Usernames'),
        'type' => 'text',
        'help' => themeTranslate('Satu username per baris atau titik koma. Posisi opsional: username (Pustakawan).'),
        'default' => ''
    ],

    // Section 11: Book Detail & Search Results Settings
    'search-result-layout' => [
        'dbfield' => 'classic_search_result_layout',
        'label' => themeTranslate('Default Search Result View'),
        'type' => 'dropdown',
        'default' => 'simple',
        'data' => [
            ['simple', __('Simple')],
            ['list', __('List')],
            ['grid', __('Grid')]
        ]
    ],
    'search-panel-style' => [
        'dbfield' => 'classic_search_panel_style',
        'label' => themeTranslate('Panel Background Style'),
        'type' => 'dropdown',
        'default' => 'solid',
        'data' => [
            ['transparent', themeTranslate('Transparent')],
            ['solid', themeTranslate('Solid')]
        ]
    ],
    'news-list-layout' => [
        'dbfield' => 'classic_news_list_layout',
        'label' => themeTranslate('News / Information List View'),
        'type' => 'dropdown',
        'default' => 'title_excerpt_thumbnail',
        'data' => [
            ['title_excerpt', themeTranslate('Title and Excerpt')],
            ['title_only', themeTranslate('Title Only')],
            ['title_excerpt_thumbnail', themeTranslate('Title, Excerpt, and Thumbnail')]
        ]
    ],
    'auto-cover-generator' => [
        'dbfield' => 'classic_auto_cover_generator',
        'label' => themeTranslate('Auto Generate Blank Book Covers'),
        'type' => 'dropdown',
        'default' => 'empty_missing',
        'data' => [
            ['empty_missing', themeTranslate('No cover and missing files')],
            ['empty_only', themeTranslate('No cover only')],
            ['none', themeTranslate('Disabled')]
        ]
    ],
    'breadcrumbs-show' => [
        'dbfield' => 'classic_breadcrumbs_show',
        'label' => themeTranslate('Breadcrumbs Navigation'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'detail-label-type' => [
        'dbfield' => 'classic_detail_label_type',
        'label' => themeTranslate('Label Above Book Title (Detail Page)'),
        'type' => 'dropdown',
        'default' => 'gmd',
        'data' => [
            ['gmd', 'GMD (General Material Designation)'],
            ['coll_type', themeTranslate('Collection Type')]
        ]
    ],
    'show-author-role' => [
        'dbfield' => 'classic_show_author_role',
        'label' => themeTranslate('Show Author Role/Type'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'title-chars' => [
        'dbfield' => 'classic_title_chars',
        'label' => themeTranslate('Main Title Character Limit'),
        'type' => 'text',
        'help' => themeTranslate('Isi angka batas karakter judul, misalnya 100.'),
        'default' => 100,
        'width' => '10',
        'max' => 4
    ],
    'parallel-title-separator' => [
        'dbfield' => 'classic_parallel_title_separator',
        'label' => themeTranslate('Parallel Title Separator Character'),
        'type' => 'text',
        'help' => themeTranslate('Masukkan karakter pemisah judul paralel, misalnya =.'),
        'default' => '=',
        'width' => '10',
        'max' => 12
    ],
];
