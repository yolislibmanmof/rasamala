<?php
/**
 * Rasamala Template - Options: Sections 4, 5, 6 (Running Text, Hero Info, Homepage Sections & Topics)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
    // Section 4: Running Text Settings
    'ticker-show' => [
        'dbfield' => 'classic_ticker_show',
        'label' => themeTranslate('Running Text Position'),
        'type' => 'dropdown',
        'default' => 'bottom',
        'data' => [
            ['bottom', themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'ticker-source' => [
        'dbfield' => 'classic_ticker_source',
        'label' => themeTranslate('Running Text Source'),
        'type' => 'dropdown',
        'default' => 'content',
        'data' => [
            ['content', themeTranslate('Latest Content (News/Info)')],
            ['biblio', themeTranslate('Latest Bibliography (Books/Items)')],
            ['custom_ticker', themeTranslate('Custom Text (Manual Input)')]
        ]
    ],
    'ticker-custom-text' => [
        'dbfield' => 'classic_ticker_custom_text',
        'label' => themeTranslate('Custom Running Text'),
        'type' => 'text',
        'help' => themeTranslate('Tulis satu teks singkat untuk running text; tidak perlu format khusus.'),
        'default' => 'Selamat datang di perpustakaan kami!'
    ],
    'ticker-content-filter' => [
        'dbfield' => 'classic_ticker_content_filter',
        'label' => themeTranslate('Running Text Content Filter'),
        'type' => 'dropdown',
        'default' => 'all',
        'data' => [
            ['all', themeTranslate('All Content')],
            ['news', themeTranslate('News Only')]
        ]
    ],
    'ticker-content-detail' => [
        'dbfield' => 'classic_ticker_content_detail',
        'label' => themeTranslate('Running Text Content Detail'),
        'type' => 'dropdown',
        'default' => 'title',
        'data' => [
            ['title', themeTranslate('Title Only')],
            ['detail', themeTranslate('Title and Content Excerpt')]
        ]
    ],
    'ticker-biblio-filter' => [
        'dbfield' => 'classic_ticker_biblio_filter',
        'label' => themeTranslate('Running Text Collection Filter'),
        'type' => 'dropdown',
        'default' => 'all',
        'data' => $coll_type_data ?? [['all', themeTranslate('All Collection Types')]]
    ],
    'ticker-speed' => [
        'dbfield' => 'classic_ticker_speed',
        'label' => themeTranslate('Running Text Speed'),
        'type' => 'dropdown',
        'default' => 'slow',
        'data' => [
            ['fast', themeTranslate('Fast (12s)')],
            ['normal', themeTranslate('Normal (18s)')],
            ['slow', themeTranslate('Slow (32s)')],
            ['very_slow', themeTranslate('Very Slow (52s)')]
        ]
    ],
    'ticker-item-limit' => [
        'dbfield' => 'classic_ticker_item_limit',
        'label' => themeTranslate('Running Text Item Limit'),
        'type' => 'text',
        'help' => themeTranslate('Isi angka jumlah item, misalnya 5.'),
        'default' => 5
    ],
    'ticker-char-limit' => [
        'dbfield' => 'classic_ticker_char_limit',
        'label' => themeTranslate('Running Text Character Limit (0 for unlimited)'),
        'type' => 'text',
        'help' => themeTranslate('Isi angka batas karakter; gunakan 0 agar tidak dibatasi.'),
        'default' => 0
    ],

    // Section 5: Search Area Info Settings
    'home-display-show' => [
        'dbfield' => 'classic_home_display_show',
        'label' => themeTranslate('Hero Info Search Area Position'),
        'type' => 'dropdown',
        'default' => 'below',
        'data' => [
            ['below', themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'home-sections-tabs' => [
        'dbfield' => 'classic_home_sections_tabs',
        'label' => themeTranslate('Compact Homepage Sections as Tabs'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show Popular, New, Top Reader, and Map/Social as Tabs')],
            [0, themeTranslate('Show Sections Separately')]
        ]
    ],
    // Homepage section visibility is shared with the Theme Viewer checklist.
    'topic-section-show' => [
        'dbfield' => 'classic_topic_show',
        'label' => themeTranslate('Topics Section'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'topic-items' => [
        'dbfield' => 'classic_topic_items',
        'label' => themeTranslate('Topics: Label, URL, and Icon'),
        'type' => 'longtext',
        'default' => function_exists('themeTopicItemsDefault') ? themeTopicItemsDefault() : 'Literature | index.php?callnumber=8&search=search | fas fa-book ; Social Sciences | index.php?callnumber=3&search=search | fas fa-users ; Applied Sciences | index.php?callnumber=6&search=search | fas fa-flask ; Art & Recreation | index.php?callnumber=7&search=search | fas fa-paint-brush ; Language | index.php?callnumber=4&search=search | fas fa-language ; see more.. | #exampleModal | fas fa-th-large',
        'width' => '100',
        'max' => 5000
    ],
    'latest-content-section-show' => [
        'dbfield' => 'classic_home_content_cards_show',
        'label' => themeTranslate('Latest Content Section (News / Cards)'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'popular-section-show' => [
        'dbfield' => 'classic_popular_collection',
        'label' => themeTranslate('Popular Collections Section'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'new-collection-section-show' => [
        'dbfield' => 'classic_new_collection',
        'label' => themeTranslate('New Collections Section (Koleksi Terbaru)'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'top-reader-section-show' => [
        'dbfield' => 'classic_top_reader',
        'label' => themeTranslate('Top Reader Section'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
];
