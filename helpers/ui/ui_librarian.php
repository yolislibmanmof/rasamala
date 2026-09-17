<?php
/**
 * UI Helper Module - Librarian Card & Page Rendering Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeLibrarianInitials')) {
  function themeLibrarianInitials($name)
  {
    $name = trim(preg_replace('/\s+/', ' ', strip_tags((string)($name ?? ''))));
    if ($name === '') {
      return 'U';
    }

    $parts = preg_split('/\s+/', $name);
    $letters = '';
    foreach ($parts as $part) {
      $part = trim($part);
      if ($part === '') {
        continue;
      }
      $letters .= function_exists('mb_substr') ? mb_substr($part, 0, 1, 'UTF-8') : substr($part, 0, 1);
      if ((function_exists('mb_strlen') ? mb_strlen($letters, 'UTF-8') : strlen($letters)) >= 2) {
        break;
      }
    }

    if ($letters === '') {
      $letters = function_exists('mb_substr') ? mb_substr($name, 0, 2, 'UTF-8') : substr($name, 0, 2);
    }

    return function_exists('mb_strtoupper') ? mb_strtoupper($letters, 'UTF-8') : strtoupper($letters);
  }
}

if (!function_exists('themeLibrarianImageUrl')) {
  function themeLibrarianImageUrl($image)
  {
    $image = trim((string)($image ?? ''));
    if ($image === '') {
      return '';
    }

    if (filter_var($image, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $image)) {
      return $image;
    }

    if (preg_match('/[\/\\\\]/', $image)) {
      return '';
    }

    $base_path = defined('IMGBS') ? IMGBS : (defined('SB') ? SB . 'images/' : dirname(__DIR__, 4) . '/images/');
    $image_path = rtrim($base_path, '/\\') . DIRECTORY_SEPARATOR . 'persons' . DIRECTORY_SEPARATOR . $image;
    if (!is_file($image_path)) {
      return '';
    }

    return SWB . 'lib/minigalnano/createthumb.php?filename=images/persons/' . rawurlencode($image) . '&width=240';
  }
}

if (!function_exists('themeLibrarianCustomUsernames')) {
  function themeLibrarianCustomUsernames($raw)
  {
    $items = preg_split('/[;\r\n]+/', (string)($raw ?? ''));
    $entries = [];
    foreach ($items as $item) {
      $item = trim($item);
      if ($item === '' || preg_match('/[\x00-\x1f\x7f]/', $item)) {
        continue;
      }

      $username = $item;
      $position = '';
      if (preg_match('/^(.+?)\(([^()]*)\)\s*$/', $item, $matches)) {
        $username = trim($matches[1]);
        $position = trim($matches[2]);
      }

      if ($username === '') {
        continue;
      }

      $entries[$username] = [
        'username' => $username,
        'position' => $position,
      ];
    }

    return array_values($entries);
  }
}

if (!function_exists('themeLibrarianFieldRow')) {
  function themeLibrarianFieldRow($label, $value_html, $value_class = 'span7')
  {
    $value_html = trim((string)$value_html);
    if ($value_html === '') {
      return '';
    }

    return '<div class="row-fluid">'
      . '<div class="span3 key">' . themeEscape($label) . '</div>'
      . '<div class="' . themeEscape($value_class) . '">' . $value_html . '</div>'
      . '</div>';
  }
}

if (!function_exists('themeRenderLibrarianCard')) {
  function themeRenderLibrarianCard($librarian, $sysconf_param = null)
  {
    $source = is_array($sysconf_param) ? $sysconf_param : ($GLOBALS['sysconf'] ?? []);
    $realname = trim((string)($librarian['realname'] ?? ''));
    $username = trim((string)($librarian['username'] ?? ''));
    $name = $realname !== '' ? $realname : $username;
    $position_override = trim((string)($librarian['_position_override'] ?? ''));
    $position = $position_override !== '' ? $position_override : ($source['system_user_type'][$librarian['user_type'] ?? ''] ?? '');
    $email = trim((string)($librarian['email'] ?? ''));
    $image_url = themeLibrarianImageUrl($librarian['user_image'] ?? '');

    $html = '<div class="row-fluid librarian rasamala-librarian-card">';
    $html .= '<div class="span2">';
    $html .= '<div class="librarian-image">';
    if ($image_url !== '') {
      $html .= '<img src="' . themeEscape($image_url) . '" alt="' . themeEscape($name) . '" loading="lazy">';
    } else {
      $html .= '<div class="rasamala-librarian-initials" aria-label="' . themeEscape($name) . '">' . themeEscape(themeLibrarianInitials($name)) . '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="span8">';
    $html .= themeLibrarianFieldRow(__('Name'), themeEscape($name));
    $html .= themeLibrarianFieldRow(__('Position'), themeEscape($position));
    if ($email !== '') {
      $email_html = filter_var($email, FILTER_VALIDATE_EMAIL)
        ? '<a href="mailto:' . themeEscape($email) . '">' . themeEscape($email) . '</a>'
        : themeEscape($email);
      $html .= themeLibrarianFieldRow(__('E-Mail'), $email_html);
    }

    $social_items = [];
    $social_data = [];
    if (!empty($librarian['social_media'])) {
      // S-02: restrict unserialize to prevent PHP Object Injection
      $unserialized = @unserialize($librarian['social_media'], ['allowed_classes' => false]);
      if (is_array($unserialized)) {
        $social_data = $unserialized;
      }
    }
    foreach (($source['social'] ?? []) as $id => $social_label) {
      if (!isset($social_data[$id])) {
        continue;
      }
      $social_value = trim((string)$social_data[$id]);
      if ($social_value === '') {
        continue;
      }
      $social_items[] = '<li><span class="librarian-social-label">' . themeEscape($social_label) . '</span><span class="librarian-social-value">' . themeEscape($social_value) . '</span></li>';
    }
    if ($social_items) {
      $html .= themeLibrarianFieldRow(__('Social'), '<ul class="librarian-social">' . implode('', $social_items) . '</ul>', 'span9');
    }

    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }
}

if (!function_exists('themeRenderLibrarianPage')) {
  function themeRenderLibrarianPage($dbs, $sysconf_param = null)
  {
    if (!$dbs) {
      return '<p>' . themeEscape(__('No librarian data yet')) . '</p>';
    }

    $source = is_array($sysconf_param) ? $sysconf_param : ($GLOBALS['sysconf'] ?? []);
    $mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_librarian_display_mode', 'librarian_senior', $source)));
    if (!in_array($mode, ['all', 'librarian_senior', 'senior', 'custom'], true)) {
      $mode = 'librarian_senior';
    }

    $where = '1=1';
    $order = 'user_type DESC, realname ASC, username ASC';
    $parameters = [];
    $parameter_types = '';
    if ($mode === 'librarian_senior') {
      $where = 'user_type IN (1,2)';
    } elseif ($mode === 'senior') {
      $where = 'user_type=2';
    } elseif ($mode === 'custom') {
      $custom_entries = themeLibrarianCustomUsernames(themeEffectiveTemplateValue('classic_librarian_custom_usernames', '', $source));
      if (!$custom_entries) {
        return '<p class="rasamala-librarian-empty">' . themeEscape(__('No librarian data yet')) . '</p>';
      }
      $usernames = array_map(static function ($entry) {
        return $entry['username'];
      }, $custom_entries);
      $username_placeholders = implode(',', array_fill(0, count($usernames), '?'));
      $where = 'username IN (' . $username_placeholders . ')';
      $order = 'FIELD(username, ' . $username_placeholders . ')';
      $parameters = array_merge($usernames, $usernames);
      $parameter_types = str_repeat('s', count($parameters));
      $position_overrides = [];
      foreach ($custom_entries as $entry) {
        if ($entry['position'] !== '') {
          $position_overrides[$entry['username']] = $entry['position'];
        }
      }
    }

    $sql = "SELECT user_id, username, realname, user_type, user_image, email, social_media
            FROM user
            WHERE {$where}
            ORDER BY {$order}";
    $statement = $dbs->prepare($sql);
    if (!$statement) {
      return '<p class="rasamala-librarian-empty">' . themeEscape(__('No librarian data yet')) . '</p>';
    }
    if ($parameters) {
      $bind_parameters = [$parameter_types];
      foreach ($parameters as $index => $value) {
        $bind_parameters[] = &$parameters[$index];
      }
      call_user_func_array([$statement, 'bind_param'], $bind_parameters);
    }
    $statement->execute();
    $query = $statement->get_result();
    if (!$query || $query->num_rows < 1) {
      $statement->close();
      return '<p class="rasamala-librarian-empty">' . themeEscape(__('No librarian data yet')) . '</p>';
    }

    $html = '';
    while ($librarian = $query->fetch_assoc()) {
      if ($mode === 'custom' && isset($position_overrides[$librarian['username'] ?? ''])) {
        $librarian['_position_override'] = $position_overrides[$librarian['username']];
      }
      $html .= themeRenderLibrarianCard($librarian, $source);
    }

    $statement->close();
    return $html;
  }
}
