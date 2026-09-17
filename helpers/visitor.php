<?php
/**
 * Helper Module for Rasamala Template - Visitor Portal Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeVisitorSetLanguage')) {
  function themeVisitorSetLanguage(&$sysconf, $available_languages = [])
  {
    if (isset($_GET['select_lang'])) {
        $select_lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['select_lang']);
        $is_valid_lang = false;
        if (isset($available_languages) && is_array($available_languages)) {
            foreach ($available_languages as $lang_index) {
                if (($lang_index[0] ?? '') === $select_lang) {
                    $is_valid_lang = true;
                    break;
                }
            }
        }
        if ($is_valid_lang) {
            if (isset($_COOKIE['select_lang'])) {
                @setcookie('select_lang', $select_lang, [
                    'expires' => time()-14400,
                    'path' => SWB,
                    'domain' => '',
                    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            @setcookie('select_lang', $select_lang, [
                'expires' => time()+14400,
                'path' => SWB,
                'domain' => '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $sysconf['default_lang'] = $select_lang;
        }
    } else if (isset($_COOKIE['select_lang'])) {
        $select_lang = preg_replace('/[^a-zA-Z0-9_-]/', '', $_COOKIE['select_lang']);
        $is_valid_lang = false;
        if (isset($available_languages) && is_array($available_languages)) {
            foreach ($available_languages as $lang_index) {
                if (($lang_index[0] ?? '') === $select_lang) {
                    $is_valid_lang = true;
                    break;
                }
            }
        }
        if ($is_valid_lang) {
            $sysconf['default_lang'] = $select_lang;
        }
    }
  }
}

if (!function_exists('themeVisitorDefaultInstitutionOptions')) {
  function themeVisitorDefaultInstitutionOptions()
  {
    return [
      ['value' => 'feb', 'label' => 'Fakultas Ekonomi dan Bisnis UI', 'manual' => false],
      ['value' => 'ff', 'label' => 'Fakultas Farmasi UI', 'manual' => false],
      ['value' => 'fh', 'label' => 'Fakultas Hukum UI', 'manual' => false],
      ['value' => 'fia', 'label' => 'Fakultas Ilmu Administrasi UI', 'manual' => false],
      ['value' => 'fib', 'label' => 'Fakultas Ilmu Budaya UI', 'manual' => false],
      ['value' => 'fik', 'label' => 'Fakultas Ilmu Keperawatan UI', 'manual' => false],
      ['value' => 'fasilkom', 'label' => 'Fakultas Ilmu Komputer UI', 'manual' => false],
      ['value' => 'fisip', 'label' => 'Fakultas Ilmu Sosial dan Ilmu Politik UI', 'manual' => false],
      ['value' => 'fk', 'label' => 'Fakultas Kedokteran UI', 'manual' => false],
      ['value' => 'fkg', 'label' => 'Fakultas Kedokteran Gigi UI', 'manual' => false],
      ['value' => 'fkm', 'label' => 'Fakultas Kesehatan Masyarakat UI', 'manual' => false],
      ['value' => 'fmipa', 'label' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam UI', 'manual' => false],
      ['value' => 'fpsi', 'label' => 'Fakultas Psikologi UI', 'manual' => false],
      ['value' => 'ft', 'label' => 'Fakultas Teknik UI', 'manual' => false],
      ['value' => 'vokasi', 'label' => 'Program Vokasi UI', 'manual' => false],
      ['value' => 'other', 'label' => 'Lainnya (ketik manual)', 'manual' => true],
    ];
  }
}

if (!function_exists('themeVisitorInstitutionOptionText')) {
  function themeVisitorInstitutionOptionText($value)
  {
    $value = preg_replace('/[\x00-\x1f\x7f]/', '', strip_tags((string)$value));
    $value = preg_replace('/\s+/', ' ', trim($value));

    return $value;
  }
}

if (!function_exists('themeVisitorInstitutionOptionIsManual')) {
  function themeVisitorInstitutionOptionIsManual($value, $label, $marker = '')
  {
    $marker = strtolower(themeVisitorInstitutionOptionText($marker));
    if (in_array($marker, ['1', 'yes', 'true', 'manual', 'other', 'lainnya', 'custom'], true)) {
      return true;
    }

    $hint = strtolower(str_replace(['_', '-', '(', ')'], ' ', themeVisitorInstitutionOptionText($value . ' ' . $label)));
    return preg_match('/(^|\s)(lainnya|other|manual)(\s|$)/i', $hint) === 1;
  }
}

if (!function_exists('themeVisitorInstitutionOptionMarkerIsManual')) {
  function themeVisitorInstitutionOptionMarkerIsManual($marker)
  {
    return in_array(strtolower(themeVisitorInstitutionOptionText($marker)), ['1', 'yes', 'true', 'manual', 'custom'], true);
  }
}

if (!function_exists('themeVisitorInstitutionOptionLooksCompact')) {
  function themeVisitorInstitutionOptionLooksCompact($entry)
  {
    $entry = themeVisitorInstitutionOptionText($entry);
    if (preg_match('/^([A-Za-z0-9_.-]+)\s*\(.+\)$/', $entry, $matches)
        && strtolower($matches[1] ?? '') === 'lainnya') {
      return false;
    }

    return preg_match('/^[A-Za-z0-9_.-]+\s*\(.+\)$/', $entry) === 1 || strtolower($entry) === 'other';
  }
}

if (!function_exists('themeVisitorInstitutionOptions')) {
  function themeVisitorInstitutionOptions($raw_options)
  {
    $raw_options = trim((string)($raw_options ?? ''));
    if ($raw_options === '') {
      return themeVisitorDefaultInstitutionOptions();
    }

    $options = [];
    $seen_values = [];
    $add_option = function ($value, $label = '', $marker = '') use (&$options, &$seen_values) {
      if (count($options) >= 100) {
        return;
      }

      $value = themeVisitorInstitutionOptionText($value);
      $label = themeVisitorInstitutionOptionText($label);
      if ($label === '') {
        $label = $value;
      }
      if ($value === '') {
        $value = $label;
      }
      if ($value === '' || isset($seen_values[$value])) {
        return;
      }

      $seen_values[$value] = true;
      $options[] = [
        'value' => $value,
        'label' => $label,
        'manual' => themeVisitorInstitutionOptionIsManual($value, $label, $marker),
      ];
    };
    $add_compact_entry = function ($entry) use (&$add_option) {
      $entry = themeVisitorInstitutionOptionText($entry);
      if ($entry === '') {
        return;
      }

      if (preg_match('/^([A-Za-z0-9_.-]+)\s*\((.*)\)$/', $entry, $matches)) {
        $value = themeVisitorInstitutionOptionText($matches[1] ?? '');
        $label = themeVisitorInstitutionOptionText($matches[2] ?? '');
        $add_option($value, $label !== '' ? $label : $value);
        return;
      }

      if (strtolower($entry) === 'other') {
        $add_option('other', 'Lainnya (ketik manual)', 'manual');
        return;
      }

      $add_option($entry, $entry);
    };

    foreach (preg_split('/\r\n|\r|\n/', $raw_options) as $line) {
      if (count($options) >= 100) {
        break;
      }

      $line = trim($line);
      if ($line === '') {
        continue;
      }

      $raw_tokens = array_values(array_filter(array_map('trim', explode(';', $line)), function ($token) {
        return $token !== '';
      }));
      $compact_format = false;
      foreach ($raw_tokens as $token) {
        if (themeVisitorInstitutionOptionLooksCompact($token)) {
          $compact_format = true;
          break;
        }
      }
      if ($compact_format) {
        foreach ($raw_tokens as $token) {
          $add_compact_entry($token);
        }
        continue;
      }

      if (strpos($line, ';') === false && strpos($line, '|') !== false) {
        $parts = array_map('trim', explode('|', $line, 3));
        $add_option($parts[0] ?? '', $parts[1] ?? ($parts[0] ?? ''), $parts[2] ?? '');
        continue;
      }

      $tokens = $raw_tokens;
      $token_count = count($tokens);
      for ($index = 0; $index < $token_count; $index += 2) {
        $value = $tokens[$index] ?? '';
        $label = $tokens[$index + 1] ?? $value;
        $marker = '';
        if (($index + 2) < $token_count && themeVisitorInstitutionOptionMarkerIsManual($tokens[$index + 2])) {
          $marker = $tokens[$index + 2];
          $index++;
        }

        $add_option($value, $label, $marker);
      }
    }

    return $options ?: themeVisitorDefaultInstitutionOptions();
  }
}

if (!function_exists('themeVisitorInstitutionManualValue')) {
  function themeVisitorInstitutionManualValue($options)
  {
    if (!is_array($options)) {
      return '';
    }

    foreach ($options as $option) {
      if (!empty($option['manual']) && isset($option['value'])) {
        return (string)$option['value'];
      }
    }

    return '';
  }
}

if (!function_exists('rasamalaVisitorSplitDefaultSteps')) {
    function rasamalaVisitorSplitDefaultSteps()
    {
        return [
            [
                'icon' => 'fas fa-id-card',
                'title' => 'Isi Identitas',
                'description' => 'Scan kartu anggota atau ketik identitas pengunjung pada kolom yang tersedia.'
            ],
            [
                'icon' => 'scan',
                'title' => 'Proses Kunjungan',
                'description' => 'Sistem akan memeriksa data dan menampilkan status kunjungan secara otomatis.'
            ],
            [
                'icon' => 'fas fa-check',
                'title' => 'Selesai',
                'description' => 'Setelah berhasil, pengunjung dapat melanjutkan aktivitas sesuai layanan yang tersedia.'
            ]
        ];
    }
}

if (!function_exists('rasamalaVisitorSplitSteps')) {
    function rasamalaVisitorSplitSteps($raw_steps)
    {
        $raw_steps = trim((string)($raw_steps ?? ''));
        if (stripos($raw_steps, 'psb.feb.ui.ac.id') !== false || stripos($raw_steps, 'Login Web PSB') !== false) {
            $raw_steps = '';
        }

        if ($raw_steps === '') {
            return rasamalaVisitorSplitDefaultSteps();
        }

        $steps = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw_steps) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 3));
            if (count($parts) === 1) {
                $icon = 'fas fa-info-circle';
                $title = $parts[0];
                $description = '';
            } elseif (count($parts) === 2) {
                $icon = $parts[0] !== '' ? $parts[0] : 'fas fa-info-circle';
                $title = $parts[1];
                $description = '';
            } else {
                [$icon, $title, $description] = $parts;
                $icon = $icon !== '' ? $icon : 'fas fa-info-circle';
            }

            if ($title === '' && $description === '') {
                continue;
            }

            $steps[] = [
                'icon' => $icon,
                'title' => $title !== '' ? $title : 'Info',
                'description' => $description
            ];
        }

        return $steps ?: rasamalaVisitorSplitDefaultSteps();
    }
}

if (!function_exists('rasamalaVisitorSplitDefaultHtml')) {
    function rasamalaVisitorSplitDefaultHtml()
    {
        return '<div class="inst-step">'
            . '<div class="inst-icon-box"><i class="fas fa-id-card"></i></div>'
            . '<div class="inst-content"><h3>1. Isi Identitas</h3><p>Scan kartu anggota atau ketik identitas pengunjung pada kolom yang tersedia.</p></div>'
            . '</div>'
            . '<div class="inst-step inst-step-featured">'
            . '<div class="inst-icon-box"><i class="fas fa-sync-alt"></i></div>'
            . '<div class="inst-content"><h3>2. Proses Kunjungan</h3><p>Sistem akan memeriksa data dan menampilkan status kunjungan secara otomatis.</p></div>'
            . '</div>'
            . '<div class="inst-step">'
            . '<div class="inst-icon-box"><i class="fas fa-check"></i></div>'
            . '<div class="inst-content"><h3>3. Selesai</h3><p>Setelah berhasil, pengunjung dapat melanjutkan aktivitas sesuai layanan yang tersedia.</p></div>'
            . '</div>';
    }
}

if (!function_exists('rasamalaVisitorSplitIcon')) {
    function rasamalaVisitorSplitIcon($icon)
    {
        $icon = preg_replace('/\s+/', ' ', trim((string)($icon ?? '')));
        if (preg_match('/^(scan|barcode|qr|qrcode)$/i', $icon)) {
            return [
                'is_scan' => true,
                'html' => '<div class="scan-anim-container" aria-hidden="true"><svg class="barcode-svg" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h4v56H4zM12 4h2v56h-2zM20 4h4v56h-4zM28 4h2v56h-2zM36 4h4v56h-4zM44 4h2v56h-2zM52 4h8v56h-8z"/></svg><div class="scan-laser"></div></div>'
            ];
        }

        if (preg_match('/^(fa[brs]?|fas|far|fab)\s+[a-z0-9 _-]+$/i', $icon)) {
            return [
                'is_scan' => false,
                'html' => '<i class="' . themeEscape($icon) . '" aria-hidden="true"></i>'
            ];
        }

        return [
            'is_scan' => false,
            'html' => themeEscape($icon !== '' ? $icon : 'i')
        ];
    }
}

if (!function_exists('rasamalaVisitorSplitLegacyHtml')) {
    function rasamalaVisitorSplitLegacyHtml($raw_steps)
    {
        $html = '';
        foreach (rasamalaVisitorSplitSteps($raw_steps) as $visitor_step_index => $visitor_step) {
            $visitor_step_icon = rasamalaVisitorSplitIcon($visitor_step['icon'] ?? '');
            $html .= '<div class="inst-step' . ($visitor_step_icon['is_scan'] ? ' inst-step-featured' : '') . '">';
            $html .= '<div class="inst-icon-box' . ($visitor_step_icon['is_scan'] ? ' inst-icon-box-scan' : '') . '">' . $visitor_step_icon['html'] . '</div>';
            $html .= '<div class="inst-content">';
            $html .= '<h3>' . themeEscape(($visitor_step_index + 1) . '. ' . ($visitor_step['title'] ?? 'Info')) . '</h3>';
            if (trim((string)($visitor_step['description'] ?? '')) !== '') {
                $html .= '<p>' . themeSanitizeHtml($visitor_step['description']) . '</p>';
            }
            $html .= '</div></div>';
        }

        return $html !== '' ? $html : rasamalaVisitorSplitDefaultHtml();
    }
}

if (!function_exists('rasamalaVisitorSplitHasHtml')) {
    function rasamalaVisitorSplitHasHtml($raw_steps)
    {
        return preg_match('/<\s*\/?\s*(div|p|ul|ol|li|h[1-6]|blockquote|table|span|strong|em|a|i|br|hr|img)\b/i', (string)$raw_steps) === 1;
    }
}

if (!function_exists('rasamalaVisitorSplitHasStepContainer')) {
    function rasamalaVisitorSplitHasStepContainer($raw_steps)
    {
        return preg_match('/class\s*=\s*(["\'])(?:(?!\1).)*\binst-step\b(?:(?!\1).)*\1/i', (string)$raw_steps) === 1;
    }
}

if (!function_exists('rasamalaVisitorSplitWrapHtml')) {
    function rasamalaVisitorSplitWrapHtml($raw_steps)
    {
        return '<div class="inst-step">'
            . '<div class="inst-icon-box"><i class="fas fa-info-circle"></i></div>'
            . '<div class="inst-content">' . $raw_steps . '</div>'
            . '</div>';
    }
}

if (!function_exists('rasamalaVisitorSplitStepsHtml')) {
    function rasamalaVisitorSplitStepsHtml($raw_steps)
    {
        $raw_steps = trim((string)($raw_steps ?? ''));
        if (stripos($raw_steps, 'psb.feb.ui.ac.id') !== false || stripos($raw_steps, 'Login Web PSB') !== false) {
            $raw_steps = '';
        }
        if ($raw_steps === '') {
            return themeSanitizeHtml(rasamalaVisitorSplitDefaultHtml());
        }
        $html_steps = html_entity_decode($raw_steps, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (rasamalaVisitorSplitHasHtml($html_steps)) {
            return themeSanitizeHtml(
                rasamalaVisitorSplitHasStepContainer($html_steps)
                    ? $html_steps
                    : rasamalaVisitorSplitWrapHtml($html_steps)
            );
        }

        return rasamalaVisitorSplitLegacyHtml($raw_steps);
    }
}
