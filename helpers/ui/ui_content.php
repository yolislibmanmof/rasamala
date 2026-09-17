<?php
/**
 * UI Helper Module - Content, News & Biblio Card Display Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeGetDisplayItems')) {
  function themeGetDisplayItems($dbs, $source, $content_filter, $content_detail, $biblio_filter, $limit, $char_limit)
  {
    $limit = themeSafeLimit($limit, 5, 1, 12);
    
    $raw_char_limit = (int)$char_limit;
    if ($raw_char_limit === 0) {
      $char_limit = 0;
    } else {
      $char_limit = themeSafeInt($char_limit, 48, 12, 160);
    }
    
    $items = [];

    if (!$dbs) {
      return $items;
    }

    $limit_text = static function ($text) use ($char_limit) {
      $text = trim(strip_tags((string)($text ?? '')));
      if ($char_limit <= 0) {
        return $text;
      }
      $suffix = '...';
      $available_length = max(0, $char_limit - strlen($suffix));

      if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $char_limit) {
          return $text;
        }
        return rtrim(mb_substr($text, 0, $available_length, 'UTF-8')) . $suffix;
      }

      if (strlen($text) <= $char_limit) {
        return $text;
      }
      return rtrim(substr($text, 0, $available_length)) . $suffix;
    };

    if ($source === 'custom_home' || $source === 'custom_ticker') {
      global $sysconf;
      $custom_key = ($source === 'custom_home') ? 'classic_home_display_custom_text' : 'classic_ticker_custom_text';
      $custom_val = trim((string)($sysconf['template'][$custom_key] ?? ''));
      if ($custom_val === '') {
        $custom_val = 'Selamat datang di perpustakaan kami!';
      }
      $items[] = [
        'title' => $custom_val,
        'display_title' => $limit_text($custom_val),
        'url' => '#'
      ];
      return $items;
    }

    if ($source === 'content') {
      $where = "COALESCE(is_draft,0)=0 AND content_title<>'' AND content_path<>'' AND (publish_date IS NULL OR publish_date <= CURDATE())";
      if ($content_filter === 'news') {
        $where .= " AND COALESCE(is_news,0)=1";
      }

      $sql = "SELECT content_title, content_desc, content_path, publish_date, input_date, last_update
              FROM content
              WHERE {$where}
              ORDER BY COALESCE(publish_date, DATE(last_update), DATE(input_date)) DESC,
                       last_update DESC,
                       input_date DESC,
                       content_id DESC
                    LIMIT ?";

                  $statement = $dbs->prepare($sql);
                  if (!$statement) {
              return $items;
                  }
                  $statement->bind_param('i', $limit);
                  $statement->execute();
                  $query = $statement->get_result();
      if ($query) {
        while ($row = $query->fetch_assoc()) {
          $path = trim((string)($row['content_path'] ?? ''));
          if (!preg_match('/^[a-z0-9_-]{1,20}$/i', $path)) {
            continue;
          }

          $full_title = $row['content_title'] ?? '';
          $display = $full_title;
          if ($content_detail === 'detail') {
            $desc = trim(strip_tags($row['content_desc'] ?? ''));
            if ($desc !== '') {
              $display .= ': ' . $desc;
            }
          }

          $items[] = [
            'title' => $full_title,
            'display_title' => $limit_text($display),
            'url' => 'index.php?p=' . rawurlencode($path)
          ];
        }
      }
      $statement->close();
    } else {
      $where = "COALESCE(b.opac_hide,0)=0 AND b.title<>''";
      $join = "";
      $filter_value = null;
      if ($biblio_filter !== 'all') {
        $join = " LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
                  LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id";
        $where .= " AND ct.coll_type_name=?";
        $filter_value = (string)$biblio_filter;
      }

      $sql = "SELECT DISTINCT b.biblio_id, b.title, b.last_update
              FROM biblio AS b
              {$join}
              WHERE {$where}
              ORDER BY b.last_update DESC, b.biblio_id DESC
              LIMIT ?";

      $statement = $dbs->prepare($sql);
      if (!$statement) {
        return $items;
      }
      if ($filter_value !== null) {
        $statement->bind_param('si', $filter_value, $limit);
      } else {
        $statement->bind_param('i', $limit);
      }
      $statement->execute();
      $query = $statement->get_result();
      if ($query) {
        while ($row = $query->fetch_assoc()) {
          $full_title = $row['title'] ?? '';
          $items[] = [
            'title' => $full_title,
            'display_title' => $limit_text($full_title),
            'url' => 'index.php?p=show_detail&id=' . urlencode($row['biblio_id'])
          ];
        }
      }
      $statement->close();
    }

    return $items;
  }
}

if (!function_exists('themeContentPathInput')) {
  function themeContentPathInput($raw)
  {
    $raw = trim((string)($raw ?? ''));
    if ($raw === '') {
      return '';
    }

    if (strpos($raw, '?') === 0) {
      $raw = 'index.php' . $raw;
    }

    $parts = parse_url($raw);
    if ($parts !== false && isset($parts['query'])) {
      parse_str((string)$parts['query'], $query);
      if (isset($query['p']) && is_scalar($query['p']) && trim((string)$query['p']) !== '') {
        $raw = (string)$query['p'];
      }
    } elseif (preg_match('/(?:^|[?&])p=([^&]+)/', $raw, $matches)) {
      $raw = rawurldecode($matches[1]);
    }

    $raw = trim($raw, " \t\n\r\0\x0B/");
    if (!preg_match('/^[a-z0-9_-]{1,50}$/i', $raw)) {
      return '';
    }

    return $raw;
  }
}

if (!function_exists('themeSafeContentImageSrc')) {
  function themeSafeContentImageSrc($src)
  {
    $src = trim(html_entity_decode((string)($src ?? ''), ENT_QUOTES, 'UTF-8'));
    if ($src === '' || preg_match('/[\x00-\x1f\x7f]/', $src)) {
      return '';
    }

    $lower = strtolower($src);
    if (strpos($lower, 'javascript:') === 0 || strpos($lower, 'vbscript:') === 0) {
      return '';
    }

    if (strpos($lower, 'data:') === 0 && !preg_match('/^data:image\/(?:png|jpe?g|gif|webp|svg\+xml);/i', $src)) {
      return '';
    }

    if (strpos($lower, 'data:') === 0) {
      return $src;
    }

    if (strpos($src, '//') === 0) {
      return '';
    }

    $parts = parse_url($src);
    if ($parts === false) {
      return '';
    }

    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    if ($scheme === 'http') {
      if (themeRequestIsHttps() || empty($parts['host']) || !themeUrlHostIsCurrent($parts['host'])) {
        return '';
      }
    } elseif ($scheme !== '' && $scheme !== 'https') {
      return '';
    }

    return $src;
  }
}

if (!function_exists('themeContentFirstImageSrc')) {
  function themeContentFirstImageSrc($html)
  {
    $html = (string)($html ?? '');
    if ($html === '') {
      return '';
    }

    if (preg_match('/<img\b[^>]*(?:src|data-src)=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
      return themeSafeContentImageSrc($matches[1]);
    }

    return '';
  }
}

if (!function_exists('themeHomeContentExcerpt')) {
  function themeHomeContentExcerpt($html, $limit = 110)
  {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)($html ?? ''))));
    $limit = themeSafeInt($limit, 110, 20, 260);
    if ($text === '') {
      return '';
    }

    $suffix = '...';
    $available_length = max(0, $limit - strlen($suffix));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
      }
      return rtrim(mb_substr($text, 0, $available_length, 'UTF-8')) . $suffix;
    }

    if (strlen($text) <= $limit) {
      return $text;
    }

    return rtrim(substr($text, 0, $available_length)) . $suffix;
  }
}

if (!function_exists('themeHomeContentFallbackTitle')) {
  function themeHomeContentFallbackTitle($path)
  {
    $path = themeContentPathInput($path);
    $labels = [
      'news' => __('News'),
      'libinfo' => __('Information'),
      'help' => __('Help'),
      'librarian' => __('Librarian'),
      'member' => __('Member Area'),
      'login' => __('Staff Area'),
    ];

    $label_key = strtolower($path);
    if (isset($labels[$label_key])) {
      return $labels[$label_key];
    }

    $title = trim(str_replace(['-', '_'], ' ', $path));
    return $title !== '' ? ucwords($title) : __('Content');
  }
}

if (!function_exists('themeHomeContentCardFromRow')) {
  function themeHomeContentCardFromRow($row, $fallback_path = '')
  {
    $path = themeContentPathInput($row['content_path'] ?? $fallback_path);
    if ($path === '') {
      return null;
    }

    $title = trim(strip_tags((string)($row['content_title'] ?? '')));
    if ($title === '') {
      $title = themeHomeContentFallbackTitle($path);
    }

    $date = trim((string)($row['publish_date'] ?? ''));
    if ($date === '' || $date === '0000-00-00') {
      $date = trim((string)($row['last_update'] ?? ($row['input_date'] ?? '')));
    }

    return [
      'title' => $title,
      'excerpt' => themeHomeContentExcerpt($row['content_desc'] ?? ''),
      'url' => 'index.php?p=' . rawurlencode($path),
      'path' => $path,
      'image_src' => themeContentFirstImageSrc($row['content_desc'] ?? ''),
      'date' => $date,
    ];
  }
}

if (!function_exists('themeHomeContentFallbackCard')) {
  function themeHomeContentFallbackCard($path)
  {
    $path = themeContentPathInput($path);
    if ($path === '') {
      return null;
    }

    return [
      'title' => themeHomeContentFallbackTitle($path),
      'excerpt' => '',
      'url' => 'index.php?p=' . rawurlencode($path),
      'path' => $path,
      'image_src' => '',
      'date' => '',
    ];
  }
}

if (!function_exists('themeHomeContentCards')) {
  function themeHomeContentCards($dbs, $sysconf_param = null)
  {
    if (!$dbs) {
      return [];
    }

    $source = is_array($sysconf_param) ? $sysconf_param : ($GLOBALS['sysconf'] ?? []);
    if ((string)themeEffectiveTemplateValue('classic_home_content_cards_show', 1, $source) === '0') {
      return [];
    }

    $mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_home_content_cards_source', 'news', $source)));
    if (!in_array($mode, ['news', 'all', 'custom'], true)) {
      $mode = 'news';
    }

    $items = [];
    if ($mode === 'custom') {
      foreach (['classic_home_content_path_1', 'classic_home_content_path_2', 'classic_home_content_path_3'] as $key) {
        $path = themeContentPathInput(themeEffectiveTemplateValue($key, '', $source));
        if ($path === '') {
          continue;
        }

        $sql = "SELECT content_title, content_desc, content_path, publish_date, input_date, last_update
                FROM content
                WHERE COALESCE(is_draft,0)=0
                  AND content_path=?
                  AND content_path<>''
                  AND (publish_date IS NULL OR publish_date <= CURDATE())
                ORDER BY COALESCE(publish_date, DATE(last_update), DATE(input_date)) DESC,
                         last_update DESC,
                         input_date DESC,
                         content_id DESC
                LIMIT 1";
        $statement = $dbs->prepare($sql);
        $query = false;
        if ($statement) {
          $statement->bind_param('s', $path);
          $statement->execute();
          $query = $statement->get_result();
        }
        $card = null;
        if ($query && ($row = $query->fetch_assoc())) {
          $card = themeHomeContentCardFromRow($row, $path);
        }
        if (!$card) {
          $card = themeHomeContentFallbackCard($path);
        }
        if ($card) {
          $items[] = $card;
        }
        if ($statement) {
          $statement->close();
        }
      }

      return array_slice($items, 0, 3);
    }

    $where = "COALESCE(is_draft,0)=0 AND content_title<>'' AND content_path<>'' AND (publish_date IS NULL OR publish_date <= CURDATE())";
    if ($mode === 'news') {
      $where .= " AND COALESCE(is_news,0)=1";
    }

    $sql = "SELECT content_title, content_desc, content_path, publish_date, input_date, last_update
            FROM content
            WHERE {$where}
            ORDER BY COALESCE(publish_date, DATE(last_update), DATE(input_date)) DESC,
                     last_update DESC,
                     input_date DESC,
                   content_id DESC
                LIMIT 3";
              $statement = $dbs->prepare($sql);
              if (!$statement) {
                return $items;
              }
              $statement->execute();
              $query = $statement->get_result();
    if ($query) {
      while ($row = $query->fetch_assoc()) {
        $card = themeHomeContentCardFromRow($row);
        if ($card) {
          $items[] = $card;
        }
      }
    }
    $statement->close();

    if (empty($items)) {
      $items = [
        [
          'title' => themeTranslate('Layanan Perpustakaan Digital Berbasis Web'),
          'url' => 'index.php?p=news',
          'excerpt' => themeTranslate('Akses koleksi digital dan jurnal ilmiah kapan saja dan di mana saja melalui portal OPAC kami.'),
          'image_src' => '',
        ],
        [
          'title' => themeTranslate('Panduan Bebas Pustaka & Pengembalian Buku'),
          'url' => 'index.php?p=news',
          'excerpt' => themeTranslate('Informasi tata cara pengurusan bebas pustaka bagi pemustaka dan mahasiswa tingkat akhir.'),
          'image_src' => '',
        ],
        [
          'title' => themeTranslate('Penambahan Koleksi Baru Bulan Ini'),
          'url' => 'index.php?p=news',
          'excerpt' => themeTranslate('Simak daftar rekomendasi buku dan bahan pustaka terbaru yang siap dipinjam minggu ini.'),
          'image_src' => '',
        ],
      ];
    }

    return $items;
  }
}

if (!function_exists('addEllipsis')) {
  function addEllipsis($string, $length, $end='…')
  {
      if (strlen($string??'') > $length)
      {
          $length -= strlen($end);
          $string  = substr($string, 0, $length);
          $string .= $end;
      }

      return $string;
  }
}

if (!function_exists('rasamalaBatchPrimeAvailability')) {
  function rasamalaBatchPrimeAvailability($dbs, array $biblio_ids, array &$cache)
  {
      $valid_ids = [];
      foreach ($biblio_ids as $id) {
          $clean_id = themeSafeInt($id);
          if ($clean_id > 0 && !isset($cache[$clean_id])) {
              $valid_ids[] = $clean_id;
              $cache[$clean_id] = [
                  'items' => [],
                  'total' => 0,
                  'available' => 0,
              ];
          }
      }

      if (empty($valid_ids)) {
          return;
      }

      $placeholders = implode(',', array_fill(0, count($valid_ids), '?'));
      $sql = "SELECT i.biblio_id, i.item_code, i.call_number, ml.location_name,
                     CASE
                         WHEN IFNULL(mis.no_loan, 0)=1 THEN 0
                         WHEN EXISTS(
                             SELECT 1 FROM loan AS l
                             WHERE l.item_code=i.item_code
                               AND l.is_lent=1
                               AND l.is_return=0
                         ) THEN 0
                         ELSE 1
                     END AS is_available
              FROM item AS i
              LEFT JOIN mst_location AS ml ON i.location_id=ml.location_id
              LEFT JOIN mst_item_status AS mis ON i.item_status_id=mis.item_status_id
                WHERE i.biblio_id IN (".$placeholders.")
              ORDER BY i.biblio_id ASC, i.call_number ASC, i.item_code ASC";

            $statement = $dbs->prepare($sql);
            if (!$statement) {
              return;
            }
            $bind_types = str_repeat('i', count($valid_ids));
            $bind_parameters = [$bind_types];
            foreach ($valid_ids as $index => $value) {
              $bind_parameters[] = &$valid_ids[$index];
            }
            call_user_func_array([$statement, 'bind_param'], $bind_parameters);
            $statement->execute();
            $query = $statement->get_result();
      if ($query) {
          while ($row = $query->fetch_assoc()) {
              $b_id = themeSafeInt($row['biblio_id'] ?? 0);
              if ($b_id <= 0 || !isset($cache[$b_id])) {
                  continue;
              }
              $row['is_available'] = themeSafeInt($row['is_available'] ?? 0);
              $cache[$b_id]['items'][] = $row;
              $cache[$b_id]['total']++;
              if ($row['is_available'] > 0) {
                  $cache[$b_id]['available']++;
              }
          }
      }
            $statement->close();
  }
}

if (!function_exists('rasamalaGetItemsAndAvailability')) {
  function rasamalaGetItemsAndAvailability($dbs, $biblio_id)
  {
      static $cache = [];

      if (is_array($biblio_id)) {
          rasamalaBatchPrimeAvailability($dbs, $biblio_id, $cache);
          return $cache;
      }

      $clean_id = themeSafeInt($biblio_id);
      if ($clean_id <= 0) {
          return ['items' => [], 'total' => 0, 'available' => 0];
      }

      if (isset($cache[$clean_id])) {
          return $cache[$clean_id];
      }

      rasamalaBatchPrimeAvailability($dbs, [$clean_id], $cache);

      return $cache[$clean_id] ?? ['items' => [], 'total' => 0, 'available' => 0];
  }
}

if (!function_exists('rasamalaBatchPrimeNotes')) {
  function rasamalaBatchPrimeNotes($dbs, array $biblio_ids, array &$cache)
  {
      $valid_ids = [];
      foreach ($biblio_ids as $id) {
          $clean_id = themeSafeInt($id);
          if ($clean_id > 0 && !isset($cache[$clean_id])) {
              $valid_ids[] = $clean_id;
              $cache[$clean_id] = '';
          }
      }

      if (empty($valid_ids)) {
          return;
      }

        $placeholders = implode(',', array_fill(0, count($valid_ids), '?'));
        $sql = "SELECT biblio_id, notes FROM biblio WHERE biblio_id IN (" . $placeholders . ")";
        $statement = $dbs->prepare($sql);
        if (!$statement) {
          return;
        }
        $bind_types = str_repeat('i', count($valid_ids));
        $bind_parameters = [$bind_types];
        foreach ($valid_ids as $index => $value) {
          $bind_parameters[] = &$valid_ids[$index];
        }
        call_user_func_array([$statement, 'bind_param'], $bind_parameters);
        $statement->execute();
        $query = $statement->get_result();

      if ($query) {
          while ($row = $query->fetch_assoc()) {
              $b_id = themeSafeInt($row['biblio_id'] ?? 0);
              if ($b_id <= 0 || !isset($cache[$b_id])) {
                  continue;
              }
              $raw_text = (string)($row['notes'] ?? '');
              if (function_exists('themeNormalizeStoredTextEscapes')) {
                  $raw_text = themeNormalizeStoredTextEscapes($raw_text);
              }
              $raw_text = str_replace(['\r\n', '\r', '\n', "\\r\\n", "\\r", "\\n"], ' ', $raw_text);
              $raw_text = str_replace(["\r\n", "\r", "\n"], ' ', $raw_text);
              $raw_text = strip_tags($raw_text);
              $raw_text = preg_replace('/\s+/', ' ', $raw_text);
              $cache[$b_id] = addEllipsis(trim($raw_text), 400);
          }
      }
            $statement->close();
  }
}

if (!function_exists('getNotes')) {
  function getNotes($dbs, $biblio_id, array $biblio_detail = [])
  {
      static $cache = [];

      if (is_array($biblio_id)) {
          rasamalaBatchPrimeNotes($dbs, $biblio_id, $cache);
          return '';
      }

      $clean_id = themeSafeInt($biblio_id);

      if (!empty($biblio_detail['notes'])) {
          $raw_text = (string)$biblio_detail['notes'];
          if (function_exists('themeNormalizeStoredTextEscapes')) {
              $raw_text = themeNormalizeStoredTextEscapes($raw_text);
          }
          $raw_text = str_replace(['\r\n', '\r', '\n', "\\r\\n", "\\r", "\\n"], ' ', $raw_text);
          $raw_text = str_replace(["\r\n", "\r", "\n"], ' ', $raw_text);
          $raw_text = strip_tags($raw_text);
          $raw_text = preg_replace('/\s+/', ' ', $raw_text);
          $processed = addEllipsis(trim($raw_text), 400);
          if ($clean_id > 0) {
              $cache[$clean_id] = $processed;
          }
          return $processed;
      }

      if ($clean_id <= 0) {
          return '';
      }

      if (isset($cache[$clean_id])) {
          return $cache[$clean_id];
      }

      rasamalaBatchPrimeNotes($dbs, [$clean_id], $cache);

      return $cache[$clean_id] ?? '';
  }
}

if (!function_exists('rasamalaNewsFirstImageSrc')) {
  function rasamalaNewsFirstImageSrc($html)
  {
      $html = (string)$html;
      if (trim($html) === '') {
          return '';
      }

      if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
          $src = trim($matches[1] ?? '');
          if ($src !== '') {
              return $src;
          }
      }

      return '';
  }
}

if (!function_exists('rasamalaSearchFilterHtml')) {
  function rasamalaSearchFilterHtml($html)
  {
      $html = (string)$html;
      $html = preg_replace('/class="list-group\s+list-group-flush"/i', 'class="list-group list-group-flush rasamala-filter-list"', $html);
      $html = preg_replace('/class="([^"]*)\blist-group-item\b([^"]*)"/i', 'class="$1list-group-item rasamala-filter-facet$2"', $html);
      $html = preg_replace('/\s*\bborder-top-0\b/i', '', $html);
      $html = preg_replace('/\s*\bborder-left\b|\s*\bborder-right\b/i', '', $html);

      return $html;
  }
}
