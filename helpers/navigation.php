<?php
/**
 * Helper Module for Rasamala Template
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeTopicItemsDefault')) {
  function themeTopicItemsDefault()
  {
    return 'Literature | index.php?callnumber=8&search=search | fas fa-book ; Social Sciences | index.php?callnumber=3&search=search | fas fa-users ; Applied Sciences | index.php?callnumber=6&search=search | fas fa-flask ; Art & Recreation | index.php?callnumber=7&search=search | fas fa-paint-brush ; Language | index.php?callnumber=4&search=search | fas fa-language ; see more.. | #exampleModal | fas fa-th-large';
  }
}

if (!function_exists('themeTopicItemsLegacyDefault')) {
  function themeTopicItemsLegacyDefault()
  {
    return 'Literature | index.php?callnumber=8&search=search | images/8-books.png ; Social Sciences | index.php?callnumber=3&search=search | images/3-diploma.png ; Applied Sciences | index.php?callnumber=6&search=search | images/6-blackboard.png ; Art & Recreation | index.php?callnumber=7&search=search | images/7-quill.png ; see more.. | #exampleModal | images/icon/grid_icon.png';
  }
}

if (!function_exists('themeNormalizeTopicIcon')) {
  function themeNormalizeTopicIcon($icon)
  {
    $icon = trim(strip_tags((string)($icon ?? '')));
    if ($icon === '') {
      return [
        'type' => 'font',
        'value' => 'fas fa-th-large',
      ];
    }

    if (preg_match('/^(fa[brs]?|fas|far|fab)\s+[a-z0-9 _-]+$/i', $icon)) {
      return [
        'type' => 'font',
        'value' => preg_replace('/\s+/', ' ', $icon),
      ];
    }

    if (themeSafeHttpsUrl($icon)) {
      return [
        'type' => 'image_url',
        'value' => $icon,
      ];
    }

    $icon = ltrim($icon, '/');
    if (preg_match('/\.\.|[\x00-\x1f\x7f]/', $icon) || !preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $icon)) {
      return [
        'type' => 'font',
        'value' => 'fas fa-th-large',
      ];
    }

    return [
      'type' => 'image',
      'value' => $icon,
    ];
  }
}

if (!function_exists('themeNormalizeTopicItem')) {
  function themeNormalizeTopicItem($label, $url, $icon)
  {
    $label = trim(preg_replace('/[|;\r\n]+/', ' ', strip_tags((string)$label)));
    $url = themeSafeMenuUrl($url);

    if ($label === '' || $url === '') {
      return null;
    }

    return [
      'label' => $label,
      'url' => $url,
      'icon' => themeNormalizeTopicIcon($icon),
    ];
  }
}

if (!function_exists('themeParseTopicItems')) {
  function themeParseTopicItems($raw)
  {
    $raw = trim((string)($raw ?? ''));
    if ($raw === themeTopicItemsLegacyDefault()) {
      $raw = themeTopicItemsDefault();
    }
    $items = [];

    if ($raw !== '') {
      $lines = preg_split('/[;\n\r]+/', $raw);
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }

        $parts = array_map('trim', explode('|', $line, 3));
        if (count($parts) < 2) {
          continue;
        }

        $item = themeNormalizeTopicItem($parts[0], $parts[1], $parts[2] ?? '');
        if ($item) {
          $items[] = $item;
        }
      }
    }

    if (!$items && $raw !== themeTopicItemsDefault()) {
      return themeParseTopicItems(themeTopicItemsDefault());
    }

    return $items;
  }
}

if (!function_exists('themeTopicIconHtml')) {
  function themeTopicIconHtml($icon)
  {
    $type = $icon['type'] ?? 'image';
    $value = $icon['value'] ?? 'images/icon/grid_icon.png';

    if ($type === 'font') {
      return '<i class="' . themeEscape($value) . ' topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>';
    }

    $src = $type === 'image_url' ? $value : (function_exists('assets') ? assets($value) : $value);
    return '<img src="' . themeEscape($src) . '" width="80" class="mb-3 mx-auto topic-icon-img" alt="">';
  }
}

if (!function_exists('themeTopicItemHtml')) {
  function themeTopicItemHtml($item)
  {
    $url = $item['url'] ?? '#';
    $modal_attrs = '';
    if (strpos($url, '#') === 0 && strlen($url) > 1) {
      $modal_attrs = ' data-bs-toggle="modal" data-bs-target="' . themeEscape($url) . '"';
    }

    $label = themeEscape(__($item['label'] ?? ''));

    return '<li class="d-flex justify-content-center align-items-center m-2">'
      . '<a href="' . themeEscape(themeSafeHref($url)) . '" class="d-flex flex-column" aria-label="' . $label . '"' . $modal_attrs . '>'
      . themeTopicIconHtml($item['icon'] ?? [])
      . '<span>' . $label . '</span>'
      . '</a>'
      . '</li>';
  }
}

if (!function_exists('themeBreadcrumbsEnabled')) {
  function themeBreadcrumbsEnabled()
  {
    global $sysconf;

    return (string)($sysconf['template']['classic_breadcrumbs_show'] ?? 1) !== '0';
  }
}

if (!function_exists('themeBreadcrumbCurrentLabel')) {
  function themeBreadcrumbCurrentLabel($label = '')
  {
    $label = stripslashes(trim(html_entity_decode(strip_tags((string)($label ?? '')), ENT_QUOTES, 'UTF-8')));
    if ($label !== '') {
      if (strpos($label, '=') !== false) {
        $label = trim(explode('=', $label)[0]);
      }
      return $label;
    }

    if (isset($_GET['search'])) {
      return __('Search Result');
    }

    $page = strtolower(trim((string)($_GET['p'] ?? '')));
    $labels = [
      'libinfo' => __('Information'),
      'news' => __('News'),
      'help' => __('Help'),
      'librarian' => __('Librarian'),
      'member' => __('Member Area'),
      'login' => __('Staff Area'),
      'show_detail' => __('Detail'),
    ];

    if (isset($labels[$page])) {
      return $labels[$page];
    }

    $page = str_replace(['_', '-'], ' ', $page);
    return $page !== '' ? ucwords($page) : __('Page');
  }
}

if (!function_exists('themeRememberSearchBreadcrumb')) {
  function themeRememberSearchBreadcrumb()
  {
    if (!isset($_GET['search'])) {
      return;
    }

    $query_string = trim((string)($_SERVER['QUERY_STRING'] ?? ''));
    $_SESSION['RASAMALA_LAST_SEARCH_URL'] = $query_string !== ''
      ? 'index.php?' . preg_replace('/[\x00-\x1f\x7f]/', '', $query_string)
      : 'index.php?search=Search';
  }
}

if (!function_exists('themeDetailHasSearchContext')) {
  function themeDetailHasSearchContext()
  {
    if (($_GET['p'] ?? '') !== 'show_detail') {
      return false;
    }

    if (array_key_exists('keywords', $_GET)) {
      return true;
    }

    $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    if ($referer === '') {
      return false;
    }

    $referer_parts = parse_url($referer);
    $current_host = $_SERVER['HTTP_HOST'] ?? '';
    if (!is_array($referer_parts) || (($referer_parts['host'] ?? $current_host) !== $current_host)) {
      return false;
    }

    parse_str($referer_parts['query'] ?? '', $referer_query);
    return isset($referer_query['search']);
  }
}

if (!function_exists('themeSearchBreadcrumbUrl')) {
  function themeSearchBreadcrumbUrl()
  {
    $session_url = themeSafeLocalUrl($_SESSION['RASAMALA_LAST_SEARCH_URL'] ?? '');
    if ($session_url !== '' && (strpos($session_url, 'search=') !== false || strpos($session_url, 'keywords=') !== false)) {
      return $session_url;
    }

    if (array_key_exists('keywords', $_GET)) {
      return 'index.php?' . http_build_query([
        'keywords' => (string)($_GET['keywords'] ?? ''),
        'search' => 'Search',
      ]);
    }

    return 'index.php?search=Search';
  }
}

if (!function_exists('themeBreadcrumbsHtml')) {
  function themeBreadcrumbsHtml($label = '', $parents = [])
  {
    if (!themeBreadcrumbsEnabled()) {
      return '';
    }

    themeRememberSearchBreadcrumb();

    $current_label = themeBreadcrumbCurrentLabel($label);
    $home_label = __('Home');
    $parents = is_array($parents) ? $parents : [];

    if (themeDetailHasSearchContext()) {
      array_unshift($parents, [
        'label' => __('Search'),
        'url' => themeSearchBreadcrumbUrl(),
      ]);
    }

    $html = '<nav class="rasamala-breadcrumbs" aria-label="' . themeEscape(__('Breadcrumbs')) . '">';
    $html .= '<a class="rasamala-breadcrumb-link" href="index.php"><i class="fas fa-home" aria-hidden="true"></i><span>' . themeEscape($home_label) . '</span></a>';

    foreach ($parents as $parent) {
      $parent_label = stripslashes(trim(strip_tags((string)($parent['label'] ?? ''))));
      $parent_url = themeSafeLocalUrl($parent['url'] ?? '');
      if ($parent_label === '' || $parent_url === '') {
        continue;
      }

      $html .= '<i class="fas fa-chevron-right rasamala-breadcrumb-separator" aria-hidden="true"></i>';
      $html .= '<a class="rasamala-breadcrumb-link" href="' . themeEscape($parent_url) . '">' . themeEscape($parent_label) . '</a>';
    }

    $html .= '<i class="fas fa-chevron-right rasamala-breadcrumb-separator" aria-hidden="true"></i>';
    $html .= '<span class="rasamala-breadcrumb-current" aria-current="page">' . themeEscape($current_label) . '</span>';
    $html .= '</nav>';

    return $html;
  }
}

if (!function_exists('themeNavbarMenuDefault')) {
  function themeNavbarMenuDefault()
  {
    return 'Home | index.php | fas fa-home ; Information | index.php?p=libinfo | fas fa-info-circle ; News | index.php?p=news | fas fa-newspaper ; Help | index.php?p=help | fas fa-question-circle ; Librarian | index.php?p=librarian | fas fa-users ; Staff Area | index.php?p=login | fas fa-university';
  }
}

if (!function_exists('themeNavbarMenuLegacyDefault')) {
  function themeNavbarMenuLegacyDefault()
  {
    return 'Home | index.php ; Information | index.php?p=libinfo ; News | index.php?p=news ; Help | index.php?p=help ; Librarian | index.php?p=librarian';
  }
}

if (!function_exists('themeNavbarMenuPlainDefault')) {
  function themeNavbarMenuPlainDefault()
  {
    return 'Home | index.php ; Information | index.php?p=libinfo ; News | index.php?p=news ; Help | index.php?p=help ; Librarian | index.php?p=librarian ; Staff Area | index.php?p=login';
  }
}

if (!function_exists('themeNavbarMenuKey')) {
  function themeNavbarMenuKey($label, $url)
  {
    $label_key = strtolower(trim((string)$label));
    $menu_key = $label_key === 'home' ? 'home' : preg_replace('/[^a-z0-9_-]+/i', '-', $label_key);
    $parsed_url = parse_url((string)$url);

    if (isset($parsed_url['query'])) {
      parse_str($parsed_url['query'], $query_params);
      if (!empty($query_params['p'])) {
        $menu_key = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower((string)$query_params['p']));
      }
    }

    return trim((string)$menu_key, '-') ?: 'menu';
  }
}

if (!function_exists('themeNormalizeNavbarMenuItem')) {
  function themeNormalizeNavbarMenuItem($label, $url, $icon = '')
  {
    $label = trim(strip_tags((string)$label));
    $url = themeSafeMenuUrl($url);

    if ($label === '' || $url === '') {
      return null;
    }

    $key = themeNavbarMenuKey($label, $url);
    $icon = trim((string)$icon);
    if ($icon === 'fas fa-user-shield') {
      $icon = 'fas fa-university';
    }
    if ($icon === '') {
      $default_icons = [
        'home' => 'fas fa-home',
        'libinfo' => 'fas fa-info-circle',
        'news' => 'fas fa-newspaper',
        'help' => 'fas fa-question-circle',
        'librarian' => 'fas fa-users',
        'login' => 'fas fa-university',
        'member' => 'fas fa-user',
      ];
      $icon = $default_icons[$key] ?? 'fas fa-link';
    }

    return [
      'key' => $key,
      'text' => $label,
      'url' => $url,
      'icon' => themeNormalizeTopicIcon($icon),
    ];
  }
}

if (!function_exists('themeNavbarMenuIconClass')) {
  function themeNavbarMenuIconClass($item, $fallback = 'fas fa-link')
  {
    $icon = $item['icon'] ?? [];
    if (is_array($icon) && ($icon['type'] ?? '') === 'font' && !empty($icon['value'])) {
      return $icon['value'];
    }

    return $fallback;
  }
}

if (!function_exists('themeParseNavbarMenus')) {
  function themeParseNavbarMenus($raw)
  {
    $raw = trim((string)($raw ?? ''));
    if ($raw === themeNavbarMenuLegacyDefault() || $raw === themeNavbarMenuPlainDefault()) {
      $raw = themeNavbarMenuDefault();
    }
    $menus = [];

    if ($raw !== '') {
      $lines = preg_split('/[;\n\r]+/', $raw);
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }

        $parts = array_map('trim', explode('|', $line, 3));
        if (count($parts) < 2) {
          continue;
        }

        $item = themeNormalizeNavbarMenuItem($parts[0], $parts[1], $parts[2] ?? '');
        if ($item) {
          $menus[] = $item;
        }
      }
    }

    if (!$menus && $raw !== themeNavbarMenuDefault()) {
      return themeParseNavbarMenus(themeNavbarMenuDefault());
    }

    return $menus;
  }
}

if (!function_exists('themeLanguageIsVisible')) {
  function themeLanguageIsVisible($lang_code, $sysconf)
  {
    $lang_code = strtolower(trim((string)$lang_code));
    if ($lang_code === '') {
      return false;
    }

    $visible_raw = trim((string)($sysconf['template']['classic_language_visible_codes'] ?? ''));
    if ($visible_raw === '') {
      return false;
    }

    $visible_codes = array_filter(array_map(function ($item) {
      return strtolower(trim($item));
    }, preg_split('/[,;\s]+/', $visible_raw)));

    return in_array($lang_code, $visible_codes, true);
  }
}
