<?php
/**
 * UI Helper Module - Book Cover & Auto-Generator Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeIsCoverPlaceholder')) {
  function themeIsCoverPlaceholder($image)
  {
    return themeCoverState($image) !== 'valid';
  }
}

if (!function_exists('themeAutoCoverMode')) {
  function themeAutoCoverMode($sysconf_param = null)
  {
    global $sysconf;

    $source = is_array($sysconf_param) ? $sysconf_param : $sysconf;
    $mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_auto_cover_generator', 'empty_missing', $source)));
    $legacy_on = ['1', 'true', 'yes', 'on', 'all', 'both'];
    $legacy_off = ['0', 'false', 'no', 'off', 'disable', 'disabled'];

    if (in_array($mode, $legacy_on, true)) {
      return 'empty_missing';
    }
    if (in_array($mode, $legacy_off, true)) {
      return 'none';
    }
    if (!in_array($mode, ['empty_missing', 'empty_only', 'none'], true)) {
      return 'empty_missing';
    }

    return $mode;
  }
}

if (!function_exists('themeCoverState')) {
  function themeCoverState($image)
  {
    $image = trim((string)($image ?? ''));
    if ($image === '') {
      return 'empty';
    }

    $lower_image = strtolower($image);
    $empty_patterns = ['default/image.png', 'images/default/image.png', 'no-image', 'no-cover'];
    foreach ($empty_patterns as $pattern) {
      if (strpos($lower_image, $pattern) !== false) {
        return 'empty';
      }
    }

    $missing_patterns = ['notfound', 'not-found', 'file-not-found'];
    foreach ($missing_patterns as $pattern) {
      if (strpos($lower_image, $pattern) !== false) {
        return 'missing';
      }
    }

    $src = '';
    if (preg_match('/src=["\'](.*?)["\']/i', $image, $matches)) {
      $src = html_entity_decode($matches[1]);
    } else {
      $src = $image;
    }

    if (!empty($src)) {
      if (stripos($src, 'createthumb.php') !== false) {
        if (preg_match('/filename=(.*?)(&|$)/i', $src, $fn_matches)) {
          $src = urldecode($fn_matches[1]);
        }
      }

      $src = str_replace('\\', '/', trim($src));
      if (filter_var($src, FILTER_VALIDATE_URL) && stripos($src, 'images/docs/') === false) {
        return 'valid';
      }

      if (strpos($src, '/') === false && $src !== '') {
        $src = 'images/docs/' . basename($src);
      }

      $src = ltrim($src, './');
      $docs_pos = stripos($src, 'images/docs/');
      if ($docs_pos !== false) {
        $relative_path = substr($src, $docs_pos);
        $file_name = basename($relative_path);

        // Check SLiMS Storage API first if available
        if (class_exists('\\SLiMS\\Filesystems\\Storage')) {
          try {
            if (\SLiMS\Filesystems\Storage::images()->isExists('docs/' . $file_name)) {
              return 'valid';
            }
          } catch (\Throwable $e) {}
        }

        // Fallback to absolute file check against SLiMS root directory
        $slims_root = defined('SB') ? SB : (dirname(__DIR__, 4) . '/');
        $abs_path = $slims_root . $relative_path;
        if (!file_exists($abs_path)) {
          return 'missing';
        }
      }
    }

    return 'valid';
  }
}

if (!function_exists('themeShouldGenerateBookCover')) {
  function themeShouldGenerateBookCover($image, $sysconf_param = null)
  {
    $mode = themeAutoCoverMode($sysconf_param);
    if ($mode === 'none') {
      return false;
    }

    $state = themeCoverState($image);
    if ($mode === 'empty_only') {
      return $state === 'empty';
    }

    return in_array($state, ['empty', 'missing'], true);
  }
}

if (!function_exists('themeGenerateBookCoverHtml')) {
  function themeGenerateBookCoverHtml($title, $authors = '')
  {
    $clean_title = trim(strip_tags((string)($title ?? '')));
    $title_hash = 0;
    $len = mb_strlen($clean_title, 'UTF-8');
    for ($i = 0; $i < $len; $i++) {
      $char = mb_substr($clean_title, $i, 1, 'UTF-8');
      // Convert UTF-8 character to its code point
      $code = unpack('V', iconv('UTF-8', 'UTF-32LE', $char))[1];
      $title_hash = $code + (($title_hash << 5) - $title_hash);
    }
    $gradient_index = abs($title_hash) % 6;
    
    $display_title = $clean_title;
    if (mb_strlen($display_title, 'UTF-8') > 45) {
      $display_title = mb_substr($display_title, 0, 42, 'UTF-8') . '...';
    }
    
    $clean_authors = '';
    if (!empty($authors)) {
      // If it contains links, parse them out
      if (strpos($authors, '<a') !== false) {
        preg_match_all('/<a[^>]*>(.*?)<\/a>/i', $authors, $matches);
        if (!empty($matches[1])) {
          $clean_authors = implode('; ', array_map('trim', $matches[1]));
        } else {
          $clean_authors = trim(strip_tags($authors));
        }
      } else {
        $clean_authors = trim(strip_tags($authors));
      }
      
      // Clean roles or dates if appended with dash
      $clean_authors = preg_replace('/\s*-\s*[^;]+/i', '', $clean_authors);
      
      // Limit authors length on cover
      if (mb_strlen($clean_authors, 'UTF-8') > 36) {
        $clean_authors = mb_substr($clean_authors, 0, 33, 'UTF-8') . '...';
      }
    }
    
    $html = '<div class="book-cover-placeholder book-cover-gradient-' . $gradient_index . '">';
    $html .= '<div class="book-cover-content">';
    $html .= '<div class="book-cover-title-text">' . themeEscape($display_title) . '</div>';
    if (!empty($clean_authors) && $clean_authors !== '-') {
      $html .= '<div class="book-cover-author-text">' . themeEscape($clean_authors) . '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
  }
}
