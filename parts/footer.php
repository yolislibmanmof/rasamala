<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: footer.php
?>


<?php
$is_homepage = themeIsHomepage();
$show_footer = themeFooterEnabled($sysconf, $is_homepage);
if ($show_footer): ?>
<footer class="py-1 border-top">
    <div class="container">
        <?php
        $footer_search_show = ($sysconf['template']['classic_footer_search_show'] ?? 0) == 1;
        $rasamala_waktu_sholat_footer_html = '';
        $rasamala_waktu_sholat_reminder_html = '';
        include_once __DIR__ . '/waktu_sholat.php';
        $col_logo = 'col-md-3';
        $col_about = 'col-md-5';
        $col_search = 'col-md-4';
        ?>
        <div class="row py-4" id="rasamala-footer-accordion">
            <div class="<?= $col_logo ?> mb-3 mb-md-0">
              <?php echo themeLibraryLogoHtml($sysconf, $imagesDisk ?? null, 'footer-brand-img'); ?>
                <div class="mb-3 fw-bold footer-section-title footer-library-name d-flex justify-content-between align-items-center"
                     data-bs-toggle="collapse" data-bs-target="#footer-nav-collapse" aria-expanded="true">
                    <span><?php echo themeEscape($sysconf['library_name']); ?></span>
                    <i class="fas fa-chevron-down d-md-none text-muted transition-icon" aria-hidden="true"></i>
                </div>
                <div class="collapse show d-md-block" id="footer-nav-collapse" data-bs-parent="#rasamala-footer-accordion">
                    <ul class="list-unstyled">
                        <li class="mb-2"><a class="footer-link" href="index.php?p=libinfo"><?= __('Information'); ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="index.php?p=services"><?= __('Services'); ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="index.php?p=librarian"><?= __('Librarian'); ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="index.php?p=member"><?= __('Member Area'); ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="<?= $col_about ?> pt-4 pt-md-0 mb-3 mb-md-0">
                <h2 class="mb-3 fw-bold text-uppercase tracking-wider footer-section-title d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" data-bs-target="#footer-about-collapse" aria-expanded="true">
                    <span><?= __('About Us'); ?></span>
                    <i class="fas fa-chevron-down d-md-none text-muted transition-icon" aria-hidden="true"></i>
                </h2>
                <div class="collapse show d-md-block" id="footer-about-collapse" data-bs-parent="#rasamala-footer-accordion">
                    <div class="footer-about-text">
                        <?= themeSanitizeHtml($sysconf['template']['classic_footer_about_us'] ?? ''); ?>
                    </div>
                </div>
            </div>
            <div class="<?= $col_search ?> pt-4 pt-md-0">
                <h2 class="mb-3 fw-bold text-uppercase tracking-wider footer-section-title d-flex justify-content-between align-items-center d-md-none"
                    data-bs-toggle="collapse" data-bs-target="#footer-actions-collapse" aria-expanded="true">
                    <span><?= __('Quick Links'); ?></span>
                    <i class="fas fa-chevron-down d-md-none text-muted transition-icon" aria-hidden="true"></i>
                </h2>
                <div class="collapse show d-md-block" id="footer-actions-collapse" data-bs-parent="#rasamala-footer-accordion">
                    <?php if ($footer_search_show) : ?>
                        <form action="index.php" class="footer-search-form">
                            <input type="hidden" name="csrf_token" value="<?= themeEscape($_SESSION['csrf_token']??'') ?>">
                            <div class="input-group mb-3">
                                <label for="footer-keywords-input" class="visually-hidden"><?= themeEscape(__('Search Keywords')) ?></label>
                                <input id="footer-keywords-input" name="keywords" type="text" class="form-control footer-search-input"
                                       placeholder="<?= __('start it by typing one or more keywords for title, author or subject'); ?>"
                                       aria-label="<?= themeEscape(__('start it by typing one or more keywords for title, author or subject')); ?>"
                                       aria-describedby="button-addon2">
                                <button class="btn btn-outline-secondary footer-search-btn" type="submit" value="search" name="search" id="button-addon2"><?= __('Find'); ?>
                                    </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <?= $rasamala_waktu_sholat_footer_html; ?>
                    <hr class="rasamala-divider">
                    <a target="_blank" rel="noopener noreferrer" title="Support Us" class="btn btn-outline-secondary btn-sm me-2 mb-2 px-3 footer-action-btn"
                       href="https://slims.web.id/web/pages/support-us/"><i
                                class="fas fa-heart me-2 footer-support-heart" aria-hidden="true"></i><?= __('Keep SLiMS Alive'); ?></a>
                    <a target="_blank" rel="noopener noreferrer" title="Contribute" class="btn btn-outline-secondary btn-sm mb-2 px-3 footer-action-btn"
                       href="https://github.com/slims/"><i
                                class="fab fa-github me-2" aria-hidden="true"></i><?= __('Contribute'); ?></a>
                </div>
            </div>
        </div>
        <hr class="rasamala-divider my-1">
        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center">
            <p class="footer-copyright m-0">&copy; <?php echo date('Y'); ?> &mdash; <?= themeEscape($sysconf['template']['classic_footer_copyright'] ?? 'Senayan Developer Community'); ?></p>
            <div class="footer-powered text-end"><?= __('Powered by '); ?><a class="footer-powered-link" target="_blank" rel="noopener noreferrer" href="https://slims.web.id/">SLiMS</a> &amp; template by <a class="footer-powered-link" target="_blank" rel="noopener noreferrer" href="https://feb.ui.ac.id/">FEB UI</a></div>
        </div>
	    </div>
	</footer>
    <?= $rasamala_waktu_sholat_reminder_html; ?>
	<?php endif; ?>

<?php include __DIR__ . '/chat_widget.php'; ?>

<!-- // Load modals -->
<?php include 'modals.php'; ?>

<?php
$rasamala_page = (string)($_GET['p'] ?? '');
$rasamala_is_search_page = isset($_GET['search']);
$rasamala_has_search_form = $rasamala_is_search_page
    || $rasamala_page === ''
    || $rasamala_page === 'show_detail'
    || ($rasamala_page === 'member' && empty($is_login));
?>

<!-- // Core JS non-render-blocking scripts -->
<?php if ($rasamala_has_search_form) : ?>
<script src="<?php echo assets('js/vue.min.js'); ?>" defer></script>
<?php endif; ?>
<script src="<?php echo assets('js/jquery.min.js'); ?>"></script>
<script src="<?php echo assets('js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo assetsVersioned('js/header_bootstrap.js'); ?>"></script>
<?php if ($rasamala_is_search_page) : ?>
<script src="<?php echo assets('js/masonry.pkgd.min.js'); ?>" defer></script>
<?php endif; ?>
<script src="<?php echo assetsVersioned('js/bootstrap_compat.js'); ?>"></script>
<script src="<?php echo JWB; ?>toastr/toastr.min.js"></script>
<script src="<?php echo JWB . v('gui.js'); ?>"></script>
<script src="<?php echo JWB; ?>fancywebsocket.js" defer></script>
<?php if ($rasamala_is_search_page) : ?>
<script src="<?php echo JWB; ?>ion.rangeSlider/js/ion.rangeSlider.min.js" defer></script>
<?php endif; ?>
<?php
if (!empty($rasamala_core_extension_js)):
    echo $rasamala_core_extension_js;
endif;
?>

<!-- // Load highlight -->
<?php if ($rasamala_is_search_page) : ?>
<script src="<?= themeEscape(JWB); ?>highlight.js" defer></script>
<?php if(isset($engine) && $searchableInJsArray = $this->generateKeywords($engine->searchable_fields)) : ?>
<template id="rasamala-highlight-keywords"><?= themeEscape(json_encode(json_decode($searchableInJsArray), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?></template>
<?php endif; ?>
<?php endif; ?>
<script src="<?php echo assetsVersioned('js/footer_helpers.js'); ?>" defer></script>
<script src="<?php echo assetsVersioned('js/motion_lifecycle.js'); ?>" defer></script>

<!-- Search enhancement assets -->
<?php if ($rasamala_has_search_form) : ?>
<script src="<?php echo assetsVersioned('js/app.js'); ?>" defer></script>
<?php endif; ?>
<script src="<?php echo assetsVersioned('js/color_mode.js'); ?>" defer></script>
<?php
$hero_animation = themeEffectiveTemplateValue('classic_hero_background_animation', 'none', $sysconf);
$palette_switcher_show = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
if ($hero_animation !== 'none' || $palette_switcher_show):
?>
<script src="<?php echo assetsVersioned('js/hero_animation.js'); ?>" defer></script>
<?php endif; ?>
<?php if ($rasamala_is_search_page) : ?>
<script src="<?php echo assetsVersioned('js/result_search.js'); ?>" defer></script>
<?php endif; ?>
<?php include __DIR__ . '/mobile_bottom_nav.php'; ?>
<?php include __DIR__ . '/bottom_info_bar.php'; ?>
<?php include __DIR__ . '/floating_actions.php'; ?>
<?php if (isset($_GET['p']) && $_GET['p'] === 'show_detail') : ?>
<script src="<?php echo assetsVersioned('js/detail_page.js'); ?>" defer></script>
<?php endif; ?>
<?php if ($palette_switcher_show) : ?>
<?php include __DIR__ . '/palette_switcher.php'; ?>

<script src="<?php echo assetsVersioned('js/palette_switcher.js'); ?>" defer></script>
<script src="<?php echo assetsVersioned('js/theme_drawer.js'); ?>" defer></script>
<script src="<?php echo assetsVersioned('js/theme_viewer.js'); ?>" defer></script>
<?php endif; ?>
<script src="<?php echo assetsVersioned('js/app_jquery.js'); ?>" defer></script>
<?php
$cursor_icon = themeEffectiveTemplateValue('classic_cursor_custom_icon', 'default', $sysconf);
// Load the renderer whenever the Theme Viewer is available. The viewer can
// enable a cursor effect at runtime, so a server-side "default" value must
// not prevent the client script from being present to receive its event.
if ($cursor_icon !== 'default' || $palette_switcher_show):
?>
<script src="<?php echo assetsVersioned('js/cursor-icons.js'); ?>" defer></script>
<?php endif; ?>
<?php
$cursor_particles = themeEffectiveTemplateValue('classic_cursor_particles', 'none', $sysconf);
// The Theme Viewer updates data-cursor-particles without a page reload. Keep
// the renderer available so that its rasamala:cursor-settings-changed event
// can initialize particles even when the saved setting is "none".
if ($cursor_particles !== 'none' || $palette_switcher_show):
?>
<script src="<?php echo assetsVersioned('js/cursor-particles.js'); ?>" defer></script>
<?php endif; ?>
<script src="<?php echo assetsVersioned('js/service-worker-cleanup.js'); ?>" defer></script>


</body>
</html>
