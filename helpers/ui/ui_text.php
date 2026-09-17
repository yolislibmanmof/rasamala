<?php
/**
 * UI Helper Module - Text Formatting & Title Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeNormalizeStoredTextEscapes')) {
  function themeNormalizeStoredTextEscapes($value)
  {
    if (is_array($value)) {
      $value = implode("\n", $value);
    }

    $text = (string)($value ?? '');
    return str_replace(["\\'", "\\\""], ["'", '"'], $text);
  }
}

if (!function_exists('themeNormalizeTextLineBreaks')) {
  function themeNormalizeTextLineBreaks($value, $replacement = "\n")
  {
    $text = themeNormalizeStoredTextEscapes($value);
    $text = str_replace(["\\r\\n", "\\n\\r", "\\r", "\\n"], "\n", $text);
    $text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

    return $replacement === "\n" ? $text : str_replace("\n", $replacement, $text);
  }
}

if (!function_exists('themeExcerpt')) {
  function themeExcerpt($value, $length = 152, $end = '...')
  {
    $text = themeNormalizeTextLineBreaks($value, ' ');
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    if (strlen($text) <= $length) {
      return $text;
    }
    $length = max(0, $length - strlen($end));
    return substr($text, 0, $length) . $end;
  }
}

if (!function_exists('themeParallelTitleSeparator')) {
  function themeParallelTitleSeparator()
  {
    global $sysconf;

    $separator = trim((string)($sysconf['template']['classic_parallel_title_separator'] ?? '='));
    if ($separator === '0' || strtolower($separator) === 'none') {
      return '';
    }

    return $separator;
  }
}

if (!function_exists('themeSplitParallelTitle')) {
  function themeSplitParallelTitle($title, $separator = null)
  {
    $title = trim(stripslashes((string)($title ?? '')));
    $separator = $separator === null ? themeParallelTitleSeparator() : trim((string)$separator);

    if ($title === '' || $separator === '') {
      return [
        'main' => $title,
        'parallel' => '',
        'has_parallel' => false,
      ];
    }

    $separator_position = strpos($title, $separator);
    if ($separator_position === false) {
      return [
        'main' => $title,
        'parallel' => '',
        'has_parallel' => false,
      ];
    }

    $main_title = trim(substr($title, 0, $separator_position));
    $parallel_title = trim(substr($title, $separator_position + strlen($separator)));

    if ($main_title === '' || $parallel_title === '') {
      return [
        'main' => $title,
        'parallel' => '',
        'has_parallel' => false,
      ];
    }

    return [
      'main' => $main_title,
      'parallel' => $parallel_title,
      'has_parallel' => true,
    ];
  }
}

if (!function_exists('themeTitleCharacterLimit')) {
  function themeTitleCharacterLimit()
  {
    global $sysconf;

    return themeSafeInt($sysconf['template']['classic_title_chars'] ?? 100, 100, 1, 300);
  }
}

if (!function_exists('themeLimitTitleText')) {
  function themeLimitTitleText($title, $length = null, $end = '...')
  {
    $title = trim(stripslashes(strip_tags((string)($title ?? ''))));
    $length = $length === null ? themeTitleCharacterLimit() : themeSafeInt($length, themeTitleCharacterLimit(), 1, 300);
    $end = (string)$end;

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($title, 'UTF-8') <= $length) {
        return $title;
      }

      $available_length = max(0, $length - mb_strlen($end, 'UTF-8'));
      return rtrim(mb_substr($title, 0, $available_length, 'UTF-8')) . $end;
    }

    if (strlen($title) <= $length) {
      return $title;
    }

    $available_length = max(0, $length - strlen($end));
    return rtrim(substr($title, 0, $available_length)) . $end;
  }
}

if (!function_exists('themeParallelTitleHtml')) {
  function themeParallelTitleHtml($title, $context = 'search', $title_length = null)
  {
    $title_parts = themeSplitParallelTitle(stripslashes((string)($title ?? '')));
    if ($context === 'detail' || $title_length === 0 || $title_length === false) {
      $main_title = $title_parts['main'];
      $parallel_title = $title_parts['parallel'];
    } else {
      $title_length = $title_length === null ? themeTitleCharacterLimit() : themeSafeInt($title_length, themeTitleCharacterLimit(), 1, 300);
      $main_title = themeLimitTitleText($title_parts['main'], $title_length);
      $parallel_title = themeLimitTitleText($title_parts['parallel'], $title_length);
    }

    $context = preg_replace('/[^a-z0-9_-]+/i', '-', (string)$context) ?: 'search';
    $html = '<span class="parallel-title parallel-title-' . themeEscape($context) . '">';
    $html .= '<span class="parallel-title-main">' . themeEscape($main_title) . '</span>';

    if ($title_parts['has_parallel']) {
      $html .= '<span class="parallel-title-alt"><i class="fas fa-language" aria-hidden="true"></i>' . themeEscape($parallel_title) . '</span>';
    }

    $html .= '</span>';

    return $html;
  }
}

if (!function_exists('themeCleanAuthorRoles')) {
  function themeCleanAuthorRoles($authors) {
    $authors = (string)($authors ?? '');
    // Clean SLiMS core link role format: </a> - Role
    $authors = preg_replace('/<\/a>\s*-\s*[^<]+/i', '</a>', $authors);
    // Clean generic parenthesized format: Name (Role)
    $authors = preg_replace('/\s*\([^)]+\)/', '', $authors);
    return $authors;
  }
}

if (!function_exists('themeFormatDetailAuthors')) {
  function themeFormatDetailAuthors($authors, $show_role) {
    $authors = (string)($authors ?? '');
    
    // Match each author block: link and optional role
    // Example: <a href="?author=...">Name</a> - Role<br />
    preg_match_all('/(<a[^>]+>)(.*?)(<\/a>)(?:\s*-\s*([^<]+))?/i', $authors, $matches, PREG_SET_ORDER);
    
    if (empty($matches)) {
      return $authors; // Fallback if parsing fails
    }
    
    $output = '';
    foreach ($matches as $match) {
      $link_open = $match[1];
      $name = trim($match[2]);
      $link_close = $match[3];
      $role = isset($match[4]) ? trim($match[4]) : '';
      
      // Remove any trailing html or br from role
      $role = strip_tags($role);
      
      $display_text = $name;
      if ($show_role && !empty($role)) {
        $display_text .= ' <span class="detail-author-role-text">(' . $role . ')</span>';
      }
      
      $output .= $link_open . $display_text . $link_close;
    }
    
    return $output;
  }
}

if (!function_exists('themeFormatDetailSubjects')) {
  function themeFormatDetailSubjects($subjects) {
    $subjects = trim((string)($subjects ?? ''));
    if ($subjects === '') {
      return '';
    }

    $raw_parts = preg_split('/\s*(?:<br\s*\/?>|;|\r?\n)\s*/i', $subjects);
    $output_tags = [];

    foreach ($raw_parts as $part) {
      $clean_text = trim(strip_tags($part));
      if ($clean_text === '' || $clean_text === '-') {
        continue;
      }

      if (preg_match('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i', $part, $m)) {
        $url = $m[1];
        $text = trim(strip_tags($m[2]));
        $output_tags[] = '<a href="' . themeEscape($url) . '" class="btn btn-outline-secondary btn-sm rounded-pill detail-subject-tag me-1 mb-1">' . themeEscape($text) . '</a>';
      } else {
        $sub_parts = preg_split('/\s*,\s*/', $clean_text);
        foreach ($sub_parts as $sub) {
          $sub = trim($sub);
          if ($sub === '' || $sub === '-') continue;
          $url = 'index.php?subject=' . urlencode($sub) . '&search=Search';
          $output_tags[] = '<a href="' . themeEscape($url) . '" class="btn btn-outline-secondary btn-sm rounded-pill detail-subject-tag me-1 mb-1">' . themeEscape($sub) . '</a>';
        }
      }
    }

    return implode(' ', $output_tags);
  }
}
