<!--
# ======================================================================
# Rasamala SLiMS Template
# ======================================================================
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# ======================================================================
-->
<?php
$rasamala_header = themeHeaderContext($sysconf, $imagesDisk ?? null, $is_login ?? false, $image_src ?? null, $opac ?? null);
$rasamala_host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$rasamala_is_search_page = isset($_GET['search']);
if (isset($page_title)) {
    $page_title = stripslashes((string)$page_title);
}
?>
<!DOCTYPE html>
<html lang="<?= themeEscape($rasamala_header['document_lang']); ?>">
<head>
    <meta charset="utf-8">
    <!-- Cryptographic CSP (SEC-01) -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; base-uri 'self'; object-src 'none'; form-action 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:; frame-src 'self' https://www.google.com https://maps.google.com;">
    <title><?= themeEscape($page_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="camera=(), microphone=(), geolocation=()">

    <?= themeSanitizeMetadata($metadata ?? ''); ?>
    <?php if (isset($_GET['p']) && $_GET['p'] === 'show_detail'): ?>
        <meta name="description" content="<?= themeEscape(themeExcerpt($notes ?? '', 152)); ?>">
        <meta name="keywords" content="<?= themeEscape(strip_tags($subject ?? '')); ?>">
    <?php else: ?>
        <meta name="description" content="<?= themeEscape($page_title); ?>">
        <meta name="keywords" content="<?= themeEscape($sysconf['library_subname']); ?>">
    <?php endif; ?>
    <meta name="csrf-token" content="<?= themeEscape(isset($opac) ? $opac->getCsrf() : ($_SESSION['csrf_token'] ?? '')); ?>">
    <meta name="generator" content="<?= themeEscape(SENAYAN_VERSION) ?>">
    <meta name="theme-color" content="#F5F5F5">

    <meta property="og:locale" content="<?= themeEscape(str_replace('-', '_', $sysconf['default_lang'])); ?>"/>
    <meta property="og:type" content="book"/>
    <meta property="og:title" content="<?= themeEscape($page_title); ?>"/>
    <?php if (isset($_GET['p']) && $_GET['p'] === 'show_detail'): ?>
        <meta property="og:description" content="<?= themeEscape(themeExcerpt($notes ?? '', 152)); ?>"/>
    <?php else: ?>
        <meta property="og:description" content="<?= themeEscape($sysconf['library_subname']); ?>"/>
    <?php endif; ?>
    <meta property="og:url" content="//<?= themeEscape($rasamala_host . $rasamala_header['request_uri']); ?>"/>
    <meta property="og:site_name" content="<?= themeEscape($sysconf['library_name']); ?>"/>
    <meta property="og:image" content="//<?= themeEscape(($_SERVER['SERVER_NAME'] ?? '') . SWB . $rasamala_header['meta_image_src']) ?>"/>

    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="//<?= themeEscape(($_SERVER['SERVER_NAME'] ?? '') . $rasamala_header['request_uri']); ?>"/>
    <meta name="twitter:title" content="<?= themeEscape($page_title); ?>"/>
    <meta property="twitter:image" content="//<?= themeEscape(($_SERVER['SERVER_NAME'] ?? '') . SWB . $rasamala_header['meta_image_src']) ?>"/>

    <!-- // load bootstrap style -->
    <link rel="stylesheet" href="<?= assets('css/bootstrap.min.css'); ?>">
    <!-- // font awesome preload & optimization (PERF-04) -->
    <link rel="preload" href="<?= assets('plugin/font-awesome/css/fontawesome-all.min.css'); ?>" as="style">
    <link rel="preload" href="<?= assets('plugin/font-awesome/webfonts/fa-solid-900.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= assets('plugin/font-awesome/webfonts/fa-brands-400.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= assets('plugin/font-awesome/css/fontawesome-all.min.css'); ?>">
    <link href="<?= themeEscape(JWB); ?>toastr/toastr.min.css?v=<?= themeEscape(assetVersion(SB . 'js/toastr/toastr.min.css')); ?>" rel="stylesheet" type="text/css"/>
    <!-- Search-only filter stylesheet -->
    <?php if ($rasamala_is_search_page) : ?>
    <link rel="stylesheet" href="<?= themeEscape(JWB); ?>ion.rangeSlider/css/ion.rangeSlider.min.css">
    <?php endif; ?>
<!-- Flag icons are visible in the header, so load their stylesheet normally. -->
<link rel="stylesheet" href="<?= assetsVersioned('css/flag-icon.min.css'); ?>">
    <!-- // local font faces -->
    <link rel="stylesheet" href="<?= assetsVersioned('fonts/google-fonts.css'); ?>">
    <!-- // my custom style -->
    <link rel="stylesheet" href="<?= assetsVersioned('css/foundation.css'); ?>">
    <link rel="stylesheet" href="<?= assetsVersioned('css/opac-pages.css'); ?>">
    <link rel="stylesheet" href="<?= assetsVersioned('css/theme-components.css'); ?>">
    <link rel="stylesheet" href="<?= assetsVersioned('css/header-runtime.css'); ?>">
    <?php if (isset($_GET['p']) && $_GET['p'] === 'visitor') : ?>
    <link rel="stylesheet" href="<?= themeEscape(assetsVersioned('css/visitor.css')); ?>">
    <?php endif; ?>

    <template id="rasamala-header-config"><?= themeEscape(json_encode($rasamala_header['header_config'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?></template>

    <?php if ($rasamala_header['custom_css'] !== '') : ?>
    <style nonce="<?= themeCspNonce(); ?>">
      /* Custom CSS */
      <?= themeSanitizeCustomCss($rasamala_header['custom_css']); ?>
    </style>
    <?php endif; ?>

    <link rel="shortcut icon" href="<?= themeEscape($rasamala_header['icon']) ?>" type="image/x-icon"/>

    <!-- Web App Manifest & Mobile Meta Tags -->
    <link rel="manifest" href="<?= assetsVersioned('manifest.json.php'); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= themeEscape($sysconf['library_name'] ?? 'SLiMS OPAC'); ?>">
    <link rel="apple-touch-icon" href="<?= themeEscape($rasamala_header['icon']); ?>">

    <?php
    // Store core extension JS tags for footer deferred output
    $rasamala_core_extension_js = '';
    if (isset($js)):
        $rasamala_core_extension_js = themeSanitizeCoreAssetTags($js);
    endif;
    ?>
</head>
<body class="<?= themeEscape($rasamala_header['body_classes']); ?>" data-cursor-particles="<?= themeEscape(themeEffectiveTemplateValue('classic_cursor_particles', 'none', $sysconf)); ?>" data-cursor-custom-icon="<?= themeEscape(themeEffectiveTemplateValue('classic_cursor_custom_icon', 'default', $sysconf)); ?>">
<a href="#main-content" class="skip-to-content"><?= themeEscape(__('Skip to main content')); ?></a>
<?php include __DIR__ . '/background_layers.php'; ?>
