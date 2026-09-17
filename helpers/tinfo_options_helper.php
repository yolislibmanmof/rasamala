<?php
/**
 * Helper Module for Rasamala Template
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('rasamalaTinfoTopicIconOptions')) {
  function rasamalaTinfoTopicIconOptions()
  {
    static $options = null;
    if ($options !== null) {
      return $options;
    }

    $options = [
      ['value' => 'fas fa-book', 'label' => 'Book'],
      ['value' => 'fas fa-bookmark', 'label' => 'Bookmark'],
      ['value' => 'fas fa-users', 'label' => 'Users'],
      ['value' => 'fas fa-user', 'label' => 'User'],
      ['value' => 'fas fa-user-circle', 'label' => 'Profile'],
      ['value' => 'fas fa-user-secret', 'label' => 'Staff/Admin'],
      ['value' => 'fas fa-id-card', 'label' => 'ID Card'],
      ['value' => 'fas fa-flask', 'label' => 'Science'],
      ['value' => 'fas fa-paint-brush', 'label' => 'Art'],
      ['value' => 'fas fa-th-large', 'label' => 'Grid'],
      ['value' => 'fas fa-th', 'label' => 'Tiles'],
      ['value' => 'fas fa-list', 'label' => 'List'],
      ['value' => 'fas fa-align-left', 'label' => 'Simple List'],
      ['value' => 'fas fa-bars', 'label' => 'Menu'],
      ['value' => 'fas fa-desktop', 'label' => 'Computer'],
      ['value' => 'fas fa-university', 'label' => 'University'],
      ['value' => 'fas fa-building', 'label' => 'Building'],
      ['value' => 'fas fa-graduation-cap', 'label' => 'Education'],
      ['value' => 'fas fa-globe', 'label' => 'Globe'],
      ['value' => 'fas fa-history', 'label' => 'History'],
      ['value' => 'fas fa-map', 'label' => 'Map'],
      ['value' => 'fas fa-map-marker-alt', 'label' => 'Location'],
      ['value' => 'fas fa-language', 'label' => 'Language'],
      ['value' => 'fas fa-calculator', 'label' => 'Calculator'],
      ['value' => 'fas fa-archive', 'label' => 'Archive'],
      ['value' => 'fas fa-folder-open', 'label' => 'Folder'],
      ['value' => 'fas fa-database', 'label' => 'Database'],
      ['value' => 'fas fa-newspaper', 'label' => 'News'],
      ['value' => 'fas fa-file-alt', 'label' => 'Document'],
      ['value' => 'fas fa-file-pdf', 'label' => 'PDF'],
      ['value' => 'fas fa-print', 'label' => 'Print'],
      ['value' => 'fas fa-info-circle', 'label' => 'Info'],
      ['value' => 'fas fa-search', 'label' => 'Search'],
      ['value' => 'fas fa-filter', 'label' => 'Filter'],
      ['value' => 'fas fa-sort', 'label' => 'Sort'],
      ['value' => 'fas fa-sliders-h', 'label' => 'Advanced'],
      ['value' => 'fas fa-star', 'label' => 'Star'],
      ['value' => 'fas fa-heart', 'label' => 'Heart'],
      ['value' => 'fas fa-thumbs-up', 'label' => 'Like'],
      ['value' => 'fas fa-leaf', 'label' => 'Leaf'],
      ['value' => 'fas fa-lightbulb', 'label' => 'Idea'],
      ['value' => 'fas fa-cog', 'label' => 'Settings'],
      ['value' => 'fas fa-wrench', 'label' => 'Tools'],
      ['value' => 'fas fa-tags', 'label' => 'Tags'],
      ['value' => 'fas fa-link', 'label' => 'Link'],
      ['value' => 'fas fa-external-link-alt', 'label' => 'External Link'],
      ['value' => 'fas fa-share-alt', 'label' => 'Share'],
      ['value' => 'fas fa-download', 'label' => 'Download'],
      ['value' => 'fas fa-upload', 'label' => 'Upload'],
      ['value' => 'fas fa-shopping-basket', 'label' => 'Basket'],
      ['value' => 'fas fa-calendar-alt', 'label' => 'Calendar'],
      ['value' => 'fas fa-clock', 'label' => 'Clock'],
      ['value' => 'fas fa-bell', 'label' => 'Bell'],
      ['value' => 'fas fa-bullhorn', 'label' => 'Announcement'],
      ['value' => 'fas fa-comments', 'label' => 'Comments'],
      ['value' => 'fas fa-envelope', 'label' => 'Email'],
      ['value' => 'fas fa-phone', 'label' => 'Phone'],
      ['value' => 'fas fa-rss', 'label' => 'RSS'],
      ['value' => 'fas fa-question', 'label' => 'Question'],
      ['value' => 'fas fa-question-circle', 'label' => 'Help'],
      ['value' => 'fas fa-exclamation-circle', 'label' => 'Alert'],
      ['value' => 'fas fa-check-circle', 'label' => 'Available'],
      ['value' => 'fas fa-times-circle', 'label' => 'Unavailable'],
      ['value' => 'fas fa-lock', 'label' => 'Locked'],
      ['value' => 'fas fa-unlock', 'label' => 'Unlocked'],
      ['value' => 'fas fa-key', 'label' => 'Key'],
      ['value' => 'fas fa-shield-alt', 'label' => 'Security'],
      ['value' => 'fas fa-sign-in-alt', 'label' => 'Login'],
      ['value' => 'fas fa-home', 'label' => 'Home'],
      ['value' => 'fas fa-wifi', 'label' => 'Wi-Fi'],
      ['value' => 'fab fa-facebook', 'label' => 'Facebook'],
      ['value' => 'fab fa-twitter', 'label' => 'Twitter'],
      ['value' => 'fab fa-instagram', 'label' => 'Instagram'],
      ['value' => 'fab fa-youtube', 'label' => 'YouTube'],
      ['value' => 'fab fa-whatsapp', 'label' => 'WhatsApp'],
      ['value' => 'fab fa-telegram', 'label' => 'Telegram'],
      ['value' => 'fab fa-github', 'label' => 'GitHub'],
      ['value' => 'fab fa-linkedin', 'label' => 'LinkedIn'],
      ['value' => 'fas fa-ellipsis-h', 'label' => 'More'],
    ];

    return $options;
  }
}

if (!function_exists('rasamalaTinfoLanguageOptions')) {
  function rasamalaTinfoLanguageOptions()
  {
    static $language_options = null;
    if ($language_options !== null) {
      return $language_options;
    }

    $language_names = [
      'ar_SA' => ['Arabic', 'العربية'],
      'bn_BD' => ['Bengali', 'বাংলা'],
      'de_DE' => ['German', 'Deutsch'],
      'en_US' => ['English', 'English'],
      'es_ES' => ['Spanish', 'Español'],
      'fa_IR' => ['Persian', 'فارسی'],
      'id_ID' => ['Indonesian', 'Indonesia'],
      'ja_JP' => ['Japanese', '日本語'],
      'ms_MY' => ['Malay', 'Bahasa Melayu'],
      'pt_BR' => ['Brazilian Portuguese', 'Português do Brasil'],
      'ru_RU' => ['Russian', 'Русский'],
      'th_TH' => ['Thai', 'ไทย'],
      'tr_TR' => ['Turkish', 'Türkçe'],
      'ur_PK' => ['Urdu', 'اردو'],
    ];
    $languages = [];
    $locale_dir = defined('LANG') ? LANG . 'locale' : (defined('SB') ? SB . 'lib/lang/locale' : realpath(__DIR__ . '/../../../lib/lang/locale'));

    if ($locale_dir && is_dir($locale_dir)) {
      $locale_codes = glob($locale_dir . '/*', GLOB_ONLYDIR);
      if (is_array($locale_codes)) {
        sort($locale_codes);
        foreach ($locale_codes as $locale_path) {
          $lang_code = basename($locale_path);
          if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $lang_code)) {
            continue;
          }

          $lang_name = $language_names[$lang_code][0] ?? str_replace('_', '-', $lang_code);
          $native_name = $language_names[$lang_code][1] ?? '';
          $languages[] = [$lang_code, $lang_name, $native_name];
        }
      }
    }

    $language_options = [];
    foreach ($languages as $language_item) {
      $lang_code = $language_item[0] ?? '';
      $lang_name = $language_item[1] ?? $lang_code;
      $native_name = $language_item[2] ?? '';
      if ($lang_code === '') {
        continue;
      }

      $code_arr = explode('_', (string)$lang_code);
      $flag_code = strtolower($code_arr[1] ?? $code_arr[0] ?? '');
      $flag_code = preg_replace('/[^a-z]/', '', $flag_code);
      $label = trim((string)$native_name) !== '' && $native_name !== $lang_name ? $lang_name . ' / ' . $native_name : $lang_name;
      $language_options[] = [
        'code' => $lang_code,
        'name' => $label,
        'flag' => $flag_code,
      ];
    }

    return $language_options;
  }
}
