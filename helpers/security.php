<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: security.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeEscape')) {
  function themeEscape($value)
  {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('themeSafeInt')) {
  function themeSafeInt($value, $default = 0, $min = 0, $max = PHP_INT_MAX)
  {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false) {
      $int = $default;
    }
    return max($min, min($max, (int)$int));
  }
}

if (!function_exists('themeSafeLimit')) {
  function themeSafeLimit($value, $default = 5, $min = 1, $max = 50)
  {
    return themeSafeInt($value, $default, $min, $max);
  }
}

if (!function_exists('themeSafeYear')) {
  function themeSafeYear($value)
  {
    $year = (string)$value;
    return preg_match('/^\d{4}$/', $year) ? $year : date('Y');
  }
}

if (!function_exists('themeSafeHttpsUrl')) {
  function themeSafeHttpsUrl($url)
  {
    $url = trim((string)($url ?? ''));
    $parts = parse_url($url);
    if (!$url || !is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
      return '';
    }
    return $url;
  }
}

if (!function_exists('themeCspNonce')) {
  function themeCspNonce()
  {
    static $nonce = null;
    if ($nonce === null) {
      if (function_exists('random_bytes')) {
        $nonce = bin2hex(random_bytes(16));
      } else {
        $nonce = md5(uniqid(mt_rand(), true));
      }
    }
    return $nonce;
  }
}

if (!function_exists('themeRequestIsHttps')) {
  function themeRequestIsHttps()
  {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
      return true;
    }

    if ((string)($_SERVER['SERVER_PORT'] ?? '') === '443') {
      return true;
    }

    return strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
  }
}

if (!function_exists('themeUrlHostIsCurrent')) {
  function themeUrlHostIsCurrent($host)
  {
    $host = strtolower(preg_replace('/:\d+$/', '', trim((string)$host)));
    if ($host === '') {
      return false;
    }

    $candidates = [
      $_SERVER['HTTP_HOST'] ?? '',
      $_SERVER['SERVER_NAME'] ?? '',
    ];

    foreach ($candidates as $candidate) {
      $candidate = strtolower(preg_replace('/:\d+$/', '', trim((string)$candidate)));
      if ($candidate !== '' && $candidate === $host) {
        return true;
      }
    }

    return false;
  }
}

if (!function_exists('themeSafeCoreAssetUrl')) {
  function themeSafeCoreAssetUrl($url)
  {
    $url = trim(html_entity_decode((string)($url ?? ''), ENT_QUOTES, 'UTF-8'));
    if ($url === '' || preg_match('/[\x00-\x1f\x7f]/', $url) || strpos($url, '//') === 0) {
      return '';
    }

    $parts = parse_url($url);
    if ($parts === false) {
      return '';
    }

    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    if ($scheme !== '') {
      if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
        return '';
      }

      if (!themeUrlHostIsCurrent($parts['host'])) {
        return '';
      }

      if (themeRequestIsHttps() && $scheme !== 'https') {
        return '';
      }
    }

    return $url;
  }
}

if (!function_exists('themeSanitizeCustomCss')) {
  function themeSanitizeCustomCss($css, $max_length = 12000)
  {
    $css = (string)($css ?? '');
    $max_length = themeSafeInt($max_length, 12000, 1000, 50000);
    if (strlen($css) > $max_length) {
      $css = substr($css, 0, $max_length);
    }

    $css = preg_replace('/<[^>]*>/', '', $css);
    $css = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', $css);
    $css = preg_replace('/@\s*import\b[^;]*(;|$)/i', '/* blocked import */', $css);
    $css = preg_replace('/(?:javascript|vbscript)\s*:/i', 'blocked:', $css);
    $css = preg_replace('/expression\s*\(/i', 'blocked-expression(', $css);
    $css = preg_replace('/-moz-binding\s*:/i', 'blocked-binding:', $css);
    $css = preg_replace('/behavior\s*:/i', 'blocked-behavior:', $css);

    $css = preg_replace_callback('/url\s*\(\s*([\'"]?)(.*?)\1\s*\)/is', function ($matches) {
      $url = trim(html_entity_decode((string)($matches[2] ?? ''), ENT_QUOTES, 'UTF-8'));
      if ($url === '' || preg_match('/[\x00-\x1f\x7f]/', $url)) {
        return 'url("")';
      }

      if (preg_match('/^(?:javascript|vbscript):/i', $url) || preg_match('#^(?:https?:)?//#i', $url)) {
        return 'url("")';
      }

      if (preg_match('/^data:/i', $url) && !preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,[a-z0-9+\/=]+$/i', $url)) {
        return 'url("")';
      }

      return 'url("' . addcslashes($url, "\\\"\n\r") . '")';
    }, $css);

    return trim($css);
  }
}

if (!function_exists('themeParseHtmlAttributes')) {
  function themeParseHtmlAttributes($raw_attrs)
  {
    $attrs = [];
    if (!preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/', (string)$raw_attrs, $matches, PREG_SET_ORDER)) {
      return $attrs;
    }

    foreach ($matches as $match) {
      $name = strtolower((string)$match[1]);
      if ($name === '' || strpos($name, 'on') === 0 || $name === 'style') {
        continue;
      }

      $attrs[$name] = isset($match[2]) && $match[2] !== ''
        ? $match[2]
        : (isset($match[3]) && $match[3] !== '' ? $match[3] : ($match[4] ?? true));
    }

    return $attrs;
  }
}

if (!function_exists('themeInjectCspNonceToScripts')) {
  function themeInjectCspNonceToScripts($html)
  {
    $html = (string)($html ?? '');
    if ($html === '' || stripos($html, '<script') === false) {
      return $html;
    }
    $nonce = themeCspNonce();
    return preg_replace_callback(
      '/<script\b([^>]*)>(.*?)<\/script>/is',
      function ($matches) use ($nonce) {
        $attrs = $matches[1];
        $code = trim($matches[2]);

        if (stripos($attrs, 'nonce=') === false) {
          $attrs .= ' nonce="' . themeEscape($nonce) . '"';
        }

        // If inline script is an execution call (and not declaring a function) that depends on core JS (e.g. gui.js or jQuery),
        // defer its execution until DOMContentLoaded when footer scripts have finished loading.
        if ($code !== '' && stripos($attrs, 'src=') === false) {
          if (!preg_match('/^\s*function\s+[a-z0-9_$]+\s*\(/i', $code) && strpos($code, 'DOMContentLoaded') === false) {
            $code = "document.addEventListener('DOMContentLoaded', function() {\n  " . $code . "\n});";
          }
        }

        return '<script' . $attrs . '>' . $code . '</script>';
      },
      $html
    );
  }
}

if (!function_exists('themeSanitizeCoreAssetTags')) {
  function themeSanitizeCoreAssetTags($html)
  {
    $html = (string)($html ?? '');
    if ($html === '' || !preg_match_all('#<\s*(script|link)\b([^>]*)\s*(?:>(.*?)<\s*/\s*script\s*>|/?>)#is', $html, $matches, PREG_SET_ORDER)) {
      return '';
    }

    $output = '';
    foreach ($matches as $match) {
      $tag = strtolower((string)$match[1]);
      $attrs = themeParseHtmlAttributes($match[2] ?? '');

      if ($tag === 'script') {
        $safe_attrs = [];
        $src = themeSafeCoreAssetUrl($attrs['src'] ?? '');
        $type = strtolower(trim((string)($attrs['type'] ?? '')));
        if ($src !== '') {
          $safe_attrs[] = 'src="' . themeEscape($src) . '"';
        }
        if ($type !== '' && preg_match('#^(module|text/(java|ecma)script|application/(java|ecma)script)$#i', $type)) {
          $safe_attrs[] = 'type="' . themeEscape($type) . '"';
        }
        foreach (['async', 'defer', 'nomodule'] as $boolean_attr) {
          if (array_key_exists($boolean_attr, $attrs)) {
            $safe_attrs[] = $boolean_attr;
          }
        }
        if (isset($attrs['crossorigin']) && in_array(strtolower((string)$attrs['crossorigin']), ['anonymous', 'use-credentials'], true)) {
          $safe_attrs[] = 'crossorigin="' . themeEscape($attrs['crossorigin']) . '"';
        }
        if (isset($attrs['integrity']) && preg_match('/^[a-z0-9+\/=._ -]+$/i', (string)$attrs['integrity'])) {
          $safe_attrs[] = 'integrity="' . themeEscape($attrs['integrity']) . '"';
        }
        if (isset($attrs['referrerpolicy']) && preg_match('/^[a-z0-9-]+$/i', (string)$attrs['referrerpolicy'])) {
          $safe_attrs[] = 'referrerpolicy="' . themeEscape($attrs['referrerpolicy']) . '"';
        }
        if (isset($attrs['id']) && preg_match('/^[a-z][a-z0-9_-]{0,80}$/i', (string)$attrs['id'])) {
          $safe_attrs[] = 'id="' . themeEscape($attrs['id']) . '"';
        }

        $script_content = (string)($match[3] ?? '');
        if ($src === '' && trim($script_content) === '') {
          continue;
        }
        if ($src === '' && trim($script_content) !== '') {
          $safe_attrs[] = 'nonce="' . themeEscape(themeCspNonce()) . '"';
        }

        $output .= '<script' . ($safe_attrs ? ' ' . implode(' ', $safe_attrs) : '') . '>' . $script_content . '</script>' . "\n";
        continue;
      }

      if ($tag === 'link') {
        $href = themeSafeCoreAssetUrl($attrs['href'] ?? '');
        $rel = strtolower(trim(preg_replace('/\s+/', ' ', (string)($attrs['rel'] ?? ''))));
        if ($href === '' || $rel === '') {
          continue;
        }

        $allowed_rels = ['stylesheet', 'preload', 'modulepreload', 'prefetch', 'preconnect'];
        $rel_tokens = preg_split('/\s+/', $rel);
        if (array_diff($rel_tokens, $allowed_rels)) {
          continue;
        }

        $safe_attrs = [
          'rel="' . themeEscape($rel) . '"',
          'href="' . themeEscape($href) . '"',
        ];
        if (isset($attrs['as']) && preg_match('/^(style|script|font|image)$/i', (string)$attrs['as'])) {
          $safe_attrs[] = 'as="' . themeEscape(strtolower((string)$attrs['as'])) . '"';
        }
        if (isset($attrs['type']) && preg_match('#^[a-z0-9.+-]+/[a-z0-9.+-]+$#i', (string)$attrs['type'])) {
          $safe_attrs[] = 'type="' . themeEscape($attrs['type']) . '"';
        }
        if (isset($attrs['media']) && preg_match('/^[a-z0-9\s_:.,()\/-]+$/i', (string)$attrs['media'])) {
          $safe_attrs[] = 'media="' . themeEscape($attrs['media']) . '"';
        }
        if (isset($attrs['crossorigin']) && in_array(strtolower((string)$attrs['crossorigin']), ['anonymous', 'use-credentials'], true)) {
          $safe_attrs[] = 'crossorigin="' . themeEscape($attrs['crossorigin']) . '"';
        }
        if (isset($attrs['integrity']) && preg_match('/^[a-z0-9+\/=._ -]+$/i', (string)$attrs['integrity'])) {
          $safe_attrs[] = 'integrity="' . themeEscape($attrs['integrity']) . '"';
        }
        if (isset($attrs['referrerpolicy']) && preg_match('/^[a-z0-9-]+$/i', (string)$attrs['referrerpolicy'])) {
          $safe_attrs[] = 'referrerpolicy="' . themeEscape($attrs['referrerpolicy']) . '"';
        }
        if (isset($attrs['id']) && preg_match('/^[a-z][a-z0-9_-]{0,80}$/i', (string)$attrs['id'])) {
          $safe_attrs[] = 'id="' . themeEscape($attrs['id']) . '"';
        }

        $output .= '<link ' . implode(' ', $safe_attrs) . '>' . "\n";
      }
    }

    return $output;
  }
}

if (!function_exists('themeAllowedHtmlTags')) {
  function themeAllowedHtmlTags()
  {
    global $sysconf;

    return $sysconf['content']['allowable_tags'] ?? '<p><a><cite><code><em><strong><blockquote><fieldset><legend><h3><hr><br><table><tr><td><th><thead><tbody><tfoot><div><span><img><ul><ol><li><i>';
  }
}

if (!function_exists('themeAllowedHtmlElements')) {
  function themeAllowedHtmlElements()
  {
    preg_match_all('/<\s*([a-z0-9]+)/i', themeAllowedHtmlTags(), $matches);
    $elements = array_map('strtolower', $matches[1] ?? []);

    return array_values(array_diff(array_unique($elements), ['object', 'param', 'fieldset', 'legend']));
  }
}

if (!function_exists('themeLoadHtmlPurifier')) {
  function themeLoadHtmlPurifier()
  {
    if (class_exists('HTMLPurifier') && class_exists('HTMLPurifier_Config')) {
      return true;
    }

    $purifier = defined('LIB')
      ? LIB . 'ezyang/htmlpurifier/library/HTMLPurifier.auto.php'
      : dirname(__DIR__, 3) . '/lib/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';

    if (is_file($purifier)) {
      require_once $purifier;
    }

    return class_exists('HTMLPurifier') && class_exists('HTMLPurifier_Config');
  }
}

if (!function_exists('themePurifierAllowedHtml')) {
  function themePurifierAllowedHtml()
  {
    $safe_attrs = [
      'a' => 'href|title|target|rel',
      'blockquote' => 'cite|class',
      'code' => 'class',
      'div' => 'class',
      'fieldset' => 'class',
      'h3' => 'class',
      'hr' => 'class',
      'i' => 'class',
      'img' => 'src|alt|title|width|height|class',
      'li' => 'class',
      'ol' => 'class',
      'p' => 'class',
      'pre' => 'class',
      'span' => 'class',
      'table' => 'class|summary',
      'tbody' => 'class',
      'td' => 'class|colspan|rowspan',
      'tfoot' => 'class',
      'th' => 'class|colspan|rowspan|scope',
      'thead' => 'class',
      'tr' => 'class',
      'ul' => 'class',
    ];

    return implode(',', array_map(function ($element) use ($safe_attrs) {
      return isset($safe_attrs[$element]) ? $element . '[' . $safe_attrs[$element] . ']' : $element;
    }, themeAllowedHtmlElements()));
  }
}

if (!function_exists('themeSanitizeHtml')) {
  function themeSanitizeHtml($html)
  {
    $html = (string)($html ?? '');
    if (themeLoadHtmlPurifier()) {
      $config = HTMLPurifier_Config::createDefault();
      $config->set('Core.Encoding', 'UTF-8');
      $config->set('Cache.DefinitionImpl', null);
      $config->set('HTML.Allowed', themePurifierAllowedHtml());
      $config->set('Attr.AllowedFrameTargets', ['_blank']);
      $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);

      foreach (['HTML.TargetBlank', 'HTML.TargetNoopener', 'HTML.TargetNoreferrer'] as $directive) {
        if ($config->def->info[$directive] ?? false) {
          $config->set($directive, true);
        }
      }

      return (new HTMLPurifier($config))->purify($html);
    }

    $html = preg_replace('#<\s*(script|style|iframe|object|embed)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
    $html = preg_replace('#<\s*/?\s*(script|style|iframe|object|embed)\b[^>]*>#is', '', $html);
    $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s+(href|src)\s*=\s*(["\'])\s*(javascript:|data:)[^"\']*\2/i', ' $1="#"', $html);
    $html = preg_replace('/\s+(href|src)\s*=\s*(javascript:|data:)[^\s>]*/i', ' $1="#"', $html);

    return strip_tags($html, themeAllowedHtmlTags());
  }
}

if (!function_exists('themeSafeHref')) {
  function themeSafeHref($url, $fallback = '#')
  {
    $url = trim((string)($url ?? ''));
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    if ($scheme === 'javascript' || $scheme === 'data') {
      return $fallback;
    }
    return $url !== '' ? $url : $fallback;
  }
}

if (!function_exists('themeSafeLocalUrl')) {
  function themeSafeLocalUrl($url)
  {
    $url = trim((string)($url ?? ''));
    if ($url === '' || preg_match('/[\x00-\x1f\x7f]/', $url) || strpos($url, '//') === 0) {
      return '';
    }

    $parts = parse_url($url);
    if ($parts === false || !empty($parts['scheme']) || !empty($parts['host'])) {
      return '';
    }

    return $url;
  }
}

if (!function_exists('themeSafeMenuUrl')) {
  function themeSafeMenuUrl($url)
  {
    $url = trim((string)($url ?? ''));
    if ($url === '' || preg_match('/[\x00-\x1f\x7f]/', $url)) {
      return '';
    }

    if ($url[0] === '#') {
      return $url;
    }

    $parts = parse_url($url);
    if ($parts === false) {
      return '';
    }

    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    if ($scheme !== '') {
      if (in_array($scheme, ['https', 'mailto', 'tel'], true)) {
        return $url;
      }

      if ($scheme === 'http' && !empty($parts['host']) && !themeRequestIsHttps() && themeUrlHostIsCurrent($parts['host'])) {
        return $url;
      }

      return '';
    }

    if (strpos($url, '//') === 0) {
      return '';
    }

    return $url;
  }
}

if (!function_exists('themeSanitizeMetadata')) {
  function themeSanitizeMetadata($metadata)
  {
    $metadata = (string)$metadata;
    // Strip tags except <meta> and <link>
    $clean = strip_tags($metadata, '<meta><link>');
    // Remove javascript: and data: protocols, and events like onload/onerror/onclick
    $clean = preg_replace('/\b(on\w+|src|href)\s*=\s*["\']\s*(javascript|data):[^"\']*["\']/i', '', $clean);
    $clean = preg_replace('/\b(on\w+)\s*=\s*/i', 'data-stripped-event=', $clean);
    return $clean;
  }
}
