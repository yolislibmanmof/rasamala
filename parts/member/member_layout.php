<?php
/**
 * Member Area Component - Logged-in / Logged-out Page Layout
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: member_layout.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
if (!isset($is_login)) {
  $is_login = class_exists('utility') && method_exists('utility', 'isMemberLogin')
    ? (bool)utility::isMemberLogin()
    : (isset($_SESSION['m_login']) && (bool)$_SESSION['m_login']);
}
?>

<?php if ($is_login) : ?>

    <?php
    if (isset($_GET['sec']) && $_GET['sec'] === 'membercard') {
        rasamalaMemberRedirect('index.php?p=member&sec=my_card');
    }
    $member_sec = isset($_GET['sec']) ? trim($_GET['sec']) : 'current_loan';
    $default_page = $sysconf['template']['classic_member_default_page'] ?? 'current_loan';
    if (!isset($_GET['sec']) && $default_page !== 'current_loan') {
        rasamalaMemberRedirect('index.php?p=member&sec=' . urlencode($default_page));
    }
    ?>
    <template id="rasamala-member-area-config"><?= themeEscape(json_encode($rasamala_member_area_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?></template>
    <div class="member-area rasamala-subpage-wrapper member-sec-<?= htmlspecialchars($member_sec, ENT_QUOTES, 'UTF-8') ?>">
        <section id="section1" class="container-fluid">
            <header class="c-header rasamala-header-dark">
              <?php
              // ----------------------------------------------------------------------
              // include navbar part
              // ----------------------------------------------------------------------
              include dirname(__DIR__) . '/_navbar.php'; ?>
            </header>
        </section>

        <div class="container py-5">
          <div class="rasamala-main-content-card p-4 shadow-sm">
             <?php echo themeInjectCspNonceToScripts($main_content); ?>
          </div>
        </div>

    </div>
    <script src="<?php echo assetsVersioned('js/member_area.js'); ?>"></script>

<?php else: ?>

    <div class="result-search page-member-area rasamala-subpage-wrapper">
        <section id="section1" class="container-fluid">
            <header class="c-header rasamala-header-dark">
              <?php
              // ----------------------------------------------------------------------
              // include navbar part
              // ----------------------------------------------------------------------
              include dirname(__DIR__) . '/_navbar.php'; ?>
            </header>
          <?php
          // ------------------------------------------------------------------------
          // include search form part
          // ------------------------------------------------------------------------
          include dirname(__DIR__) . '/_search-form.php'; ?>
        </section>

        <div class="container py-5">
          <div class="row">
              <div class="col-md-8 mx-auto">
                <div class="rasamala-main-content-card p-4 shadow-sm">
                  <?php echo themeInjectCspNonceToScripts($main_content); ?>
                </div>
              </div>
          </div>
        </div>
    </div>

<?php endif; ?>
