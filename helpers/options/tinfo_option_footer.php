<?php
/**
 * Rasamala Template - Options: Sections 7, 8, 9 (Maps, Social Media Links & Footer Settings)
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

return [
    // Section 7: Maps & Contact Info
    'map' => [
        'dbfield' => 'classic_map',
        'label' => themeTranslate('Map & Social Media Section'),
        'type' => 'dropdown',
        'default' => 'all',
        'data' => [
            ['all', themeTranslate('Show Map and Social Media')],
            ['hide_all', themeTranslate('Hide Map and Social Media')],
            ['hide_map', themeTranslate('Hide Map')],
            ['hide_social', themeTranslate('Hide Social Media')]
        ]
    ],
    'map-link' => [
        'dbfield' => 'classic_map_link',
        'label' => themeTranslate('Map Iframe Link'),
        'type' => 'text',
        'help' => themeTranslate('Tempel URL embed Google Maps lengkap (https://www.google.com/maps/embed...).'),
        'default' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.288723306273!2d106.80038831428296!3d-6.225610995493402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14efd9abf05%3A0x1659580cc6981749!2sPerpustakaan+Kemendikbud!5e0!3m2!1sid!2sid!4v1516601731218'
    ],
    'map-height' => [
        'dbfield' => 'classic_map_height',
        'label' => themeTranslate('Map Height (px)'),
        'type' => 'text',
        'help' => themeTranslate('Isi angka tinggi peta dalam piksel, misalnya 420.'),
        'default' => '420'
    ],
    'map-desc' => [
        'dbfield' => 'classic_map_desc',
        'label' => themeTranslate('Map / Contact Description'),
        'type' => 'longtext',
        'help' => themeTranslate('Tulis alamat atau kontak. Gunakan <br> untuk pindah baris.'),
        'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque et nunc mi. Donec vehicula turpis a quam venenatis posuere. Aliquam nibh lectus, gravida et leo sit amet, dignissim dapibus mauris.<br>Telp. (021) 9172638<br>Fax. (021) 9172638<br>'
    ],

    // Section 8: Social Media Links
    'fb-link' => [
        'dbfield' => 'classic_fb_link',
        'label' => __('Facebook Link'),
        'type' => 'text',
        'default' => 'https://www.facebook.com/groups/senayan.slims'
    ],
    'twitter-link' => [
        'dbfield' => 'classic_twitter_link',
        'label' => __('Twitter Link'),
        'type' => 'text',
        'default' => 'https://twitter.com/slims_official'
    ],
    'youtube-link' => [
        'dbfield' => 'classic_youtube_link',
        'label' => __('YouTube Link'),
        'type' => 'text',
        'default' => 'https://youtube.com'
    ],
    'instagram-link' => [
        'dbfield' => 'classic_instagram_link',
        'label' => __('Instagram Link'),
        'type' => 'text',
        'default' => 'https://instagram.com/slims.sdc'
    ],
    'tiktok-link' => [
        'dbfield' => 'classic_tiktok_link',
        'label' => __('TikTok Link'),
        'type' => 'text',
        'default' => ''
    ],
    'whatsapp-link' => [
        'dbfield' => 'classic_whatsapp_link',
        'label' => __('WhatsApp Link'),
        'type' => 'text',
        'default' => ''
    ],
    'telegram-link' => [
        'dbfield' => 'classic_telegram_link',
        'label' => __('Telegram Link'),
        'type' => 'text',
        'default' => ''
    ],
    'linkedin-link' => [
        'dbfield' => 'classic_linkedin_link',
        'label' => __('LinkedIn Link'),
        'type' => 'text',
        'default' => ''
    ],

    // Section 9: Footer Settings
    'footer-show' => [
        'dbfield' => 'classic_footer_show',
        'label' => themeTranslate('Footer'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, themeTranslate('Show')],
            [0, themeTranslate('Hide')]
        ]
    ],
];
