<?php
/**
 * Rasamala Template - Options: Section 2 (Navigation Bar Options)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
    'navbar-menu' => [
        'dbfield' => 'classic_navbar_menu',
        'label' => themeTranslate('Navbar Menu'),
        'type' => 'longtext',
        'default' => "Home | index.php | fas fa-home ; Information | index.php?p=libinfo | fas fa-info-circle ; News | index.php?p=news | fas fa-newspaper ; Help | index.php?p=help | fas fa-question-circle ; Librarian | index.php?p=librarian | fas fa-users ; Staff Area | index.php?p=login | fas fa-university",
        'width' => '100',
        'max' => 2000
    ],
    'member-area' => [
        'dbfield' => 'classic_member_area',
        'label' => themeTranslate('Member Area in Navbar'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'member-default-page' => [
        'dbfield' => 'classic_member_default_page',
        'label' => themeTranslate('Default Member Page'),
        'type' => 'dropdown',
        'default' => 'my_card',
        'data' => [
            ['my_card', themeTranslate('Digital Card')],
            ['current_loan', themeTranslate('Current Loans')],
            ['bookmark', themeTranslate('Bookmarked Titles')],
            ['title_basket', themeTranslate('Title Basket')],
            ['loan_history', themeTranslate('Loan History')],
            ['my_account', themeTranslate('My Account')]
        ]
    ],
    'card-show-fields' => [
        'dbfield' => 'classic_card_show_fields',
        'label' => themeTranslate('Digital Card: Visible Fields (comma separated)'),
        'type' => 'text',
        'help' => themeTranslate('Pisahkan nama field dengan koma, misalnya: name,member_id,institution.'),
        'default' => 'name,member_id,institution,member_type'
    ],
    'card-code-type' => [
        'dbfield' => 'classic_card_code_type',
        'label' => themeTranslate('Digital Card: Code Type'),
        'type' => 'dropdown',
        'default' => 'qr',
        'data' => [
            ['qr', __('QR Code')],
            ['barcode', __('Barcode')]
        ]
    ],
    'library-name-position' => [
        'dbfield' => 'classic_library_name_position',
        'label' => themeTranslate('Logo & Library Name Position (Desktop View)'),
        'type' => 'dropdown',
        'default' => 'hero',
        'data' => [
            ['navbar', themeTranslate('Logo & name in Navbar (default)')],
            ['hero', themeTranslate('Logo & name above Search Box (Desktop)')]
        ]
    ],
    'subtitle' => [
        'dbfield' => 'classic_library_subname',
        'label' => themeTranslate('Library Subtitle in Navbar'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
    'language-visible-codes' => [
        'dbfield' => 'classic_language_visible_codes',
        'label' => themeTranslate('Visible Languages'),
        'type' => 'longtext',
        'default' => 'en_US, id_ID, ja_JP',
        'width' => '100',
        'max' => 1000
    ],
    'mobile-bottom-nav-show' => [
        'dbfield' => 'classic_mobile_bottom_nav_show',
        'label' => themeTranslate('Mobile Bottom Navbar'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
];
