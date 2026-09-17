<?php
/**
 * Dynamic PWA Web App Manifest for SLiMS Rasamala Theme
 */
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', 1);
}

$root_dir = dirname(__DIR__, 3);
$sysconf_path = $root_dir . '/sysconf.inc.php';
if (is_file($sysconf_path)) {
    @include $sysconf_path;
}

$library_name = trim((string)($sysconf['library_name'] ?? 'SLiMS Library OPAC'));
$library_subname = trim((string)($sysconf['library_subname'] ?? 'Senayan Library Management System'));
$short_name = mb_strimwidth($library_name, 0, 20, '');

$icon_rel = '../../../webicon.ico';
$icon_abs = $root_dir . '/webicon.ico';

if (!empty($sysconf['webicon']) && is_file($root_dir . '/images/default/' . $sysconf['webicon'])) {
    $icon_rel = '../../../images/default/' . rawurlencode($sysconf['webicon']);
    $icon_abs = $root_dir . '/images/default/' . $sysconf['webicon'];
} elseif (is_file($root_dir . '/images/default/logo.png')) {
    $icon_rel = '../../../images/default/logo.png';
    $icon_abs = $root_dir . '/images/default/logo.png';
}

$icon_type = preg_match('/\.ico$/i', $icon_rel) ? 'image/x-icon' : 'image/png';
$icon_sizes = '48x48';

if (is_file($icon_abs) && ($img_info = @getimagesize($icon_abs))) {
    if (!empty($img_info[0]) && !empty($img_info[1])) {
        $icon_sizes = (int)$img_info[0] . 'x' . (int)$img_info[1];
    }
}

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=86400');

echo json_encode([
    'name' => $library_name,
    'short_name' => $short_name ?: 'SLiMS OPAC',
    'description' => $library_subname,
    'start_url' => '../../../index.php',
    'scope' => '../../../',
    'display' => 'standalone',
    'background_color' => '#0f172a',
    'theme_color' => '#0f172a',
    'orientation' => 'any',
    'icons' => [
        [
            'src' => $icon_rel,
            'sizes' => $icon_sizes,
            'type' => $icon_type,
            'purpose' => 'any'
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
