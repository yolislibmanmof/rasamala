<?php
/**
 * Book Detail Page Template Entry Point
 */
include_once __DIR__ . '/theme_helpers.php';
require_once __DIR__ . '/helpers/detail.php';

global $dbs;

$biblio_id_safe = themeSafeInt($biblio_id ?? 0);
$title_attr = themeEscape(strip_tags($title ?? ''));
$setBookmarked = trim(isset($_SESSION['bookmark'][$biblio_id_safe]) ? 'bg-success text-white rounded-3 px-2 py-1' : 'text-muted px-2 py-1');
$detail_title_html = themeParallelTitleHtml($title ?? '', 'detail');
if (themeShouldGenerateBookCover($image ?? '', $sysconf)) {
    $image = themeGenerateBookCoverHtml($title ?? '', $authors ?? '');
}

$availability_html = $availability ?? '';
$publisher_html = '';
if (themeDetailHasValue($publish_place ?? '')) {
    $publisher_html .= '<span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope>' . themeEscape($publish_place) . '</span>';
}
if (themeDetailHasValue($publisher_name ?? '')) {
    $publisher_html .= ($publisher_html !== '' ? ' : ' : '') . '<span itemprop="publisher" property="publisher">' . themeEscape($publisher_name) . '</span>';
}
if (themeDetailHasValue($publish_year ?? '')) {
    $publisher_html .= ($publisher_html !== '' ? '., ' : '') . '<span itemprop="datePublished" property="datePublished">' . themeEscape($publish_year) . '</span>';
}

$subjects_inline_html = themeFormatDetailSubjects($subjects ?? '');

// Generate Full Absolute Canonical URL for QR Code Scanning
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
if (preg_match('/^https?:\/\//i', SWB)) {
    $detail_share_url = SWB . 'index.php?p=show_detail&id=' . $biblio_id_safe;
} else {
    $swb_clean = '/' . ltrim(SWB, '/');
    $detail_share_url = $scheme . $host . $swb_clean . 'index.php?p=show_detail&id=' . $biblio_id_safe;
}

$qrcode_svg = '';
if (class_exists('BaconQrCode\Writer')) {
    try {
        $renderer = new BaconQrCode\Renderer\ImageRenderer(
            new BaconQrCode\Renderer\RendererStyle\RendererStyle(160, 1),
            new BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new BaconQrCode\Writer($renderer);
        $qrcode_svg = $writer->writeString($detail_share_url);
        $qrcode_svg = preg_replace('/<\?xml[^>]*\?>/', '', $qrcode_svg);
    } catch (Exception $e) {
        $qrcode_svg = '';
    }
}
if (empty($qrcode_svg)) {
    $qrcode_svg = '<div class="detail-qr-fallback text-center p-3"><i class="fas fa-link fa-2x mb-2 detail-qr-fallback-icon" aria-hidden="true"></i><div class="small text-muted">' . themeEscape(__('Scan QR not available')) . '</div></div>';
}
?>

<!-- UX-09: Scroll Reading Progress Bar -->
<div class="reading-progress-bar" id="reading-progress-bar" aria-hidden="true"></div>

<div class="detail-record"
  data-lightbox-close-label="<?= themeEscape(__('Close')); ?>"
  data-lightbox-preview-label="<?= themeEscape(__('Preview')); ?>">
    <div class="row detail-main-row">
        <?php include __DIR__ . '/parts/detail/detail_sidebar.php'; ?>
        <?php include __DIR__ . '/parts/detail/detail_fields.php'; ?>
    </div>
</div>

<!-- Mobile Floating Quick Actions (Icon Only - Bottom Left) -->
<div class="detail-floating-quick-actions d-flex d-md-none" id="detail-floating-quick-actions">
  <a href="index.php?p=member&sec=bookmark" data-id="<?= $biblio_id_safe ?>" data-detail="true" class="bookMarkBook btn-floating-action-icon <?= in_array($biblio_id_safe, $_SESSION['bookmark']??[]) ? 'is-bookmarked' : '' ?>" title="<?= themeEscape(in_array($biblio_id_safe, $_SESSION['bookmark']??[]) ? __('Bookmarked') : __('Bookmark')) ?>" aria-label="<?= themeEscape(__('Bookmark')) ?>">
    <i class="<?= in_array($biblio_id_safe, $_SESSION['bookmark']??[]) ? 'fas' : 'far' ?> fa-bookmark" aria-hidden="true"></i>
  </a>
  <button type="button" class="btn-floating-action-icon addToBasket add-to-chart-button" data-biblio="<?= $biblio_id_safe ?>" title="<?= themeEscape(__('Add to Basket')) ?>" aria-label="<?= themeEscape(__('Add to Basket')) ?>">
    <i class="fas fa-shopping-basket" aria-hidden="true"></i>
  </button>
  <a href="index.php?p=cite&id=<?= $biblio_id_safe ?>" data-title="<?= $title_attr ?>" class="btn-floating-action-icon citationLink" target="_blank" rel="noopener noreferrer" title="<?= themeEscape(__('Cite')) ?>" aria-label="<?= themeEscape(__('Cite')) ?>">
    <i class="fas fa-quote-right" aria-hidden="true"></i>
  </a>
  <button type="button" class="btn-floating-action-icon detail-share-btn" data-url="<?= themeEscape($detail_share_url ?? '') ?>" data-id="<?= $biblio_id_safe ?>" data-title="<?= $title_attr ?>" title="<?= themeEscape(__('Share')) ?>" aria-label="<?= themeEscape(__('Share')) ?>">
    <i class="fas fa-share-alt" aria-hidden="true"></i>
  </button>
</div>

<!-- Mobile QR Code Pop-up Modal -->
<div class="modal fade" id="detailQrModal" tabindex="-1" aria-labelledby="detailQrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content rounded-4 border-0 text-center p-3">
      <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
        <h6 class="modal-title fw-bold" id="detailQrModalLabel"><i class="fas fa-qrcode me-2 content-qr-icon" aria-hidden="true"></i><?= themeEscape(__('Scan for Link')); ?></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-3">
        <div class="detail-qr-modal-img content-qr-modal-image mx-auto mb-3">
          <?= $qrcode_svg; ?>
        </div>
        <p class="small text-dark fw-bold mb-2"><?= themeEscape(strip_tags($title ?? '')); ?></p>
        <div class="input-group input-group-sm mb-3">
          <input type="text" class="form-control form-control-sm text-muted small detail-qr-modal-input" value="<?= themeEscape($detail_share_url); ?>" readonly>
          <a href="<?= themeEscape($detail_share_url); ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="<?= themeEscape(__('Open Link')); ?>"><i class="fas fa-external-link-alt" aria-hidden="true"></i></a>
        </div>
        <button type="button" class="btn btn-secondary btn-sm w-100 rounded-pill" data-bs-dismiss="modal"><?= themeEscape(__('Close')); ?></button>
      </div>
    </div>
  </div>
</div>
