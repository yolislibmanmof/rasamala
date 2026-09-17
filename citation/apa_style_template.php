<?php
/**
 * APA Style citation
 * Copyright (C) 2015  Arie Nugraha (dicarve@gmail.com)
 * Modification by Drajat Hasan 2023 (drajathasan20@gmail.com)
 *
 * Available data to use:
 * $author_list    : Array of authors <-- you must pre-proccess this to string first
 * $authors_string : String of authors name separated by comma if there is more than one
 * $title          : String of title
 * $publish_year   : String of publication year
 * $edition        : String of edition statement
 * $publish_place  : String of place of publication
 * $publisher_name : String of name of publisher
 * $gmd_name       : String of name of GMD/Document format
 *
 */

if (!defined('RASAMALA_CITE_STYLES_LOADED')) {
    define('RASAMALA_CITE_STYLES_LOADED', true);
    $csp_nonce = function_exists('themeCspNonce') ? themeCspNonce() : '';
    // The citation route is rendered before the Rasamala theme helpers are
    // loaded, so do not call themeEscape() here.  Keep this view usable when
    // it is opened directly (or in a popup) as well as from show_detail.
    $citation_nonce_attr = htmlspecialchars((string)$csp_nonce, ENT_QUOTES, 'UTF-8');
    $theme_dir = 'template/' . ($sysconf['template']['theme'] ?? 'rasamala');
    echo '<link rel="stylesheet" href="' . $theme_dir . '/assets/css/foundation.css">';
    echo '<link rel="stylesheet" href="' . $theme_dir . '/assets/css/header-runtime.css">';
    echo '<link rel="stylesheet" href="' . $theme_dir . '/assets/css/opac-pages.css">';
    echo '<link rel="stylesheet" href="' . $theme_dir . '/assets/css/theme-dark.css">';
    echo '<style nonce="' . $citation_nonce_attr . '">
    html, body {
        background-color: var(--theme-background, #f8f9fa) !important;
        color: var(--theme-text, #212529) !important;
        font-family: var(--theme-font-family), -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        padding: 20px 16px !important;
        margin: 0 !important;
        line-height: 1.6 !important;
    }
    .citation-card {
        background-color: var(--theme-surface, #ffffff) !important;
        color: var(--theme-text, #212529) !important;
        border: 1px solid color-mix(in srgb, var(--theme-muted-on-surface, #ccc) 22%, transparent) !important;
        border-radius: 12px !important;
        padding: 16px 20px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04) !important;
    }
    .citation-card h3 {
        color: var(--theme-primary, #007bff) !important;
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        margin-bottom: 8px !important;
        font-family: var(--theme-font-family), inherit !important;
    }
    .citation {
        color: var(--theme-text, #212529) !important;
        font-size: 0.92rem !important;
        line-height: 1.65 !important;
        font-family: var(--theme-font-family), inherit !important;
        margin-bottom: 0 !important;
    }
    /* Explicit Dark Mode Overrides for Cite Popup Window & Iframe */
    html.rasamala-dark,
    html.rasamala-dark body,
    body.rasamala-dark,
    body.rasamala-dark body {
        background-color: var(--theme-dark-background, #0f172a) !important;
        background: var(--theme-dark-background, #0f172a) !important;
        color: var(--theme-dark-text, #f1f5f9) !important;
    }
    html.rasamala-dark .citation-card,
    body.rasamala-dark .citation-card {
        background-color: var(--theme-dark-surface, #1e293b) !important;
        background: var(--theme-dark-surface, #1e293b) !important;
        color: var(--theme-dark-text, #f1f5f9) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
    }
    html.rasamala-dark .citation-card h3,
    body.rasamala-dark .citation-card h3 {
        color: var(--theme-dark-primary, #60a5fa) !important;
    }
    html.rasamala-dark .citation,
    body.rasamala-dark .citation {
        color: var(--theme-dark-text, #f1f5f9) !important;
    }
    </style>
    <script nonce="' . $citation_nonce_attr . '">
    (function() {
        var isDark = false;
        try {
            if (window.parent && window.parent.document && window.parent.document.documentElement.classList.contains("rasamala-dark")) {
                isDark = true;
            } else if (window.opener && window.opener.document && window.opener.document.documentElement.classList.contains("rasamala-dark")) {
                isDark = true;
            }
        } catch(e) {}

        if (!isDark) {
            var savedMode = localStorage.getItem("rasamala-color-mode") || localStorage.getItem("rasamala_color_mode");
            if (savedMode === "dark") {
                isDark = true;
            } else if (!savedMode || savedMode === "auto") {
                isDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
            }
        }

        if (isDark) {
            document.documentElement.classList.add("rasamala-dark");
            if (document.body) {
                document.body.classList.add("rasamala-dark");
            } else {
                document.addEventListener("DOMContentLoaded", function() {
                    document.body.classList.add("rasamala-dark");
                });
            }
        }
    })();
    </script>';
}

//  set pre-processor variable
$author_list = [];
$authors_string = '';

// iterate some author data
foreach ($authors as $order => $data) {
  // chunk author name as an array based on space
  $chunk_name = explode(' ', $data['author_name']);
  // get last key order
  $last_chunkname_order = array_key_last($chunk_name);
  // set lastname
  $last_name = $chunk_name[$last_chunkname_order];
  // set first name
  $first_name = $chunk_name[0]??'';

  // Check everthing first name ended with comma or not
  if (!str_ends_with(trim($first_name), ',')) {
    unset($chunk_name[$last_chunkname_order]); // remote last chunkname
    if ($order > 0 & count($authors) > 2) continue; // don't make it pain, just say it et al if author > 2
    // Process for inverting name
    $author_list[] = $last_name . ', ' . implode(', ', array_map(fn($name) => ucfirst(substr($name, 0,1)) . '', $chunk_name)) . '.';
  } else {
    // Same as above
    if ($order > 0 & count($authors) > 2) continue;
    unset($chunk_name[0]);
    // if author have comma/before it already inverted
    $author_list[] = $first_name . ' ' . implode(', ', array_map(fn($name) => ucfirst(substr($name, 0,1)) . '', $chunk_name)) . '.';
  }
}

// glue all author data into one string
$authors_string = implode(', ', $author_list) . (count($authors) > 2 ? ' et al' : '');

?>
<div class="citation-card">
  <h3><?php echo __('APA Style'); ?></h3>
  <p class="citation text-justify">
    <?php if ($authors_string) : ?>
      <span class="authors"><?php print $authors_string ?></span> <span class="year">(<?php print $publish_year ?>).</span>
      <span class="title"><em><?php print $title ?></em> <?php if ($edition) : ?>(<span class="edition"><?php print $edition ?>)</span><?php endif; ?>.</span>
    <?php else : ?>
      <span class="title"><em><?php print $title ?></em>.</span> <span class="year">(<?php print $publish_year ?>).</span>
    <?php endif; ?>
    <span class="publish_place"><?php print $publish_place ?>:</span>
    <span class="publisher"><?php print $publisher_name ?>.</span>
  </p>
</div>
