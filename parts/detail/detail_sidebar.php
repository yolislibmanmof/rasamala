<?php
/**
 * Book Detail Component - Left Sidebar (Cover, Call Number Tags, Side Availability)
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: detail_sidebar.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
?>
<div class="col-md-3 mb-4 text-center detail-sidebar-col">
    <div class="detail-cover-wrapper">
        <div class="detail-cover">
          <?= themeSanitizeHtml($image); ?>
        </div>
        <?= themeDetailCallNumberTags($dbs ?? null, $biblio_id_safe, $call_number ?? ''); ?>
    </div>
    <div class="detail-side-availability">
        <div class="detail-avail-heading-row d-flex align-items-center justify-content-between mb-2">
            <h5 class="detail-side-heading mb-0"><i class="fas fa-book me-2 text-theme-accent" aria-hidden="true"></i><?= __('Availability'); ?></h5>
        </div>
        <?php
        $availability_output = themeDetailAvailabilityHtml($dbs ?? null, $biblio_id_safe, $availability_html);
        echo themeDetailHasValue($availability_output) ? $availability_output : '<p class="text-muted">' . themeEscape(__('No copy data')) . '</p>';
        ?>

        <!-- Desktop QR Code Box below Availability -->
        <div class="detail-qr-card-desktop d-none d-md-block mt-4 text-center">
            <div class="detail-qr-box p-3">
                <div class="detail-qr-img-wrap mb-2">
                    <?= $qrcode_svg ?? ''; ?>
                </div>
                <span class="detail-qr-label text-muted d-block small fw-medium">
                    <i class="fas fa-qrcode me-1 text-theme-accent"></i> Scan for Link
                </span>
            </div>
        </div>
    </div>
</div>
