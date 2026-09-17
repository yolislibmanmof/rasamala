<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: modals.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
?>

<!-- Advanced Search Modal -->
<div class="modal fade" id="adv-modal" tabindex="-1" aria-labelledby="advancedSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <form class="modal-content border-0 rounded-4 shadow-lg" action="index.php" method="get">
            <div class="modal-header border-0 pb-0 px-4 pt-4 justify-content-between align-items-center">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="advancedSearchModalLabel">
                    <i class="fas fa-sliders-h me-2 text-theme-accent" aria-hidden="true"></i><?= __('Advanced Search'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-titles" class="form-label text-muted small fw-bold mb-1"><?= __('Title'); ?></label>
                            <input type="text" name="title" class="form-control" id="adv-titles"
                                   aria-label="<?= __('Title'); ?>"
                                   placeholder="<?= __('Title'); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-author" class="form-label text-muted small fw-bold mb-1"><?= __('Author(s)'); ?></label>
                            <input type="text" name="author" class="form-control" id="adv-author"
                                   aria-label="<?= __('Author(s)'); ?>"
                                   placeholder="<?= __('Author(s)'); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-subject" class="form-label text-muted small fw-bold mb-1"><?= __('Subject(s)'); ?></label>
                            <input type="text" name="subject" class="form-control" id="adv-subject"
                                   aria-label="<?= __('Subject(s)'); ?>"
                                   placeholder="<?= __('Subject(s)'); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-isbn" class="form-label text-muted small fw-bold mb-1"><?= __('ISBN/ISSN'); ?></label>
                            <input type="text" name="isbn" class="form-control" id="adv-isbn"
                                   aria-label="<?= __('ISBN/ISSN'); ?>"
                                   placeholder="<?= __('ISBN/ISSN'); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-publishyear" class="form-label text-muted small fw-bold mb-1"><?= __('Publish Year'); ?></label>
                            <input type="text" name="publishyear" class="form-control" id="adv-publishyear"
                                   aria-label="<?= __('Publish Year'); ?>"
                                   placeholder="<?= __('Publish Year'); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-location" class="form-label text-muted small fw-bold mb-1"><?= __('Location'); ?></label>
                            <select id="adv-location" name="location"
                                    class="form-select" aria-label="<?= __('Location'); ?>"><?= commonList('location'); ?></select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-gmd" class="form-label text-muted small fw-bold mb-1"><?= __('GMD / Media'); ?></label>
                            <select id="adv-gmd" name="gmd" class="form-select" aria-label="<?= __('GMD / Media'); ?>"><?= commonList('gmd'); ?></select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-0">
                            <label for="adv-coll-type" class="form-label text-muted small fw-bold mb-1"><?= __('Collection Type'); ?></label>
                            <select name="colltype" class="form-select"
                                    id="adv-coll-type" aria-label="<?= __('Collection Type'); ?>"><?= commonList('collection'); ?></select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-end">
                <button type="submit" name="search" value="search" class="btn btn-primary rounded-pill px-4 py-2-5 fw-bold w-100 w-md-auto d-flex align-items-center justify-content-center">
                    <i class="fas fa-search me-2" aria-hidden="true"></i><?= __('Find Collection'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Topic Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="topicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="topicModalLabel"><?= __('Select the topic you are interested in'); ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="topic d-flex flex-wrap justify-content-center p-0">
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=0&search=search" class="d-flex flex-column">
                            <i class="fas fa-desktop topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Computer Science, Information & General Works'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=1&search=search" class="d-flex flex-column">
                            <i class="fas fa-lightbulb topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Philosophy & Psychology'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=2&search=search" class="d-flex flex-column">
                            <i class="fas fa-heart topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Religion'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=3&search=search" class="d-flex flex-column">
                            <i class="fas fa-users topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Social Sciences'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=4&search=search" class="d-flex flex-column">
                            <i class="fas fa-language topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Language'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=5&search=search" class="d-flex flex-column">
                            <i class="fas fa-calculator topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Pure Science'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=6&search=search" class="d-flex flex-column">
                            <i class="fas fa-flask topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Applied Sciences'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=7&search=search" class="d-flex flex-column">
                            <i class="fas fa-paint-brush topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Art & Recreation'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=8&search=search" class="d-flex flex-column">
                            <i class="fas fa-book topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('Literature'); ?></span>
                        </a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center m-2">
                        <a href="index.php?callnumber=9&search=search" class="d-flex flex-column">
                            <i class="fas fa-history topic-icon-fa mb-3 mx-auto" aria-hidden="true"></i>
                            <span><?= __('History & Geography'); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Social Share Modal -->
<div class="modal fade social-share-modal" id="mediaSocialModal" tabindex="-1" role="dialog" aria-labelledby="mediaSocialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content rounded-4 border-0 p-3 shadow-lg">
            <div class="modal-header border-0 pb-2 justify-content-between align-items-center">
                <h6 class="modal-title fw-bold" id="mediaSocialModalLabel">
                    <i class="fas fa-share-alt me-2 text-theme-accent" aria-hidden="true"></i><?= __('Bagikan Koleksi') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="mediaSocialModalBody" class="modal-body py-2">
                <p id="shareModalBookTitle" class="small text-muted mb-3 text-truncate fw-medium"></p>
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <a href="#" id="shareWaBtn" target="_blank" rel="noopener" class="share-platform-item wa-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-whatsapp text-white mb-1"><i class="fab fa-whatsapp" aria-hidden="true"></i></div>
                            <span class="share-platform-label">WhatsApp</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="#" id="shareFbBtn" target="_blank" rel="noopener" class="share-platform-item fb-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-facebook text-white mb-1"><i class="fab fa-facebook-f" aria-hidden="true"></i></div>
                            <span class="share-platform-label">Facebook</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="#" id="shareTwBtn" target="_blank" rel="noopener" class="share-platform-item tw-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-twitter text-white mb-1"><i class="fab fa-twitter" aria-hidden="true"></i></div>
                            <span class="share-platform-label">Twitter / X</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="#" id="shareTelegramBtn" target="_blank" rel="noopener" class="share-platform-item tg-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-telegram text-white mb-1"><i class="fab fa-telegram-plane" aria-hidden="true"></i></div>
                            <span class="share-platform-label">Telegram</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="#" id="shareLinkedinBtn" target="_blank" rel="noopener" class="share-platform-item li-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-linkedin text-white mb-1"><i class="fab fa-linkedin-in" aria-hidden="true"></i></div>
                            <span class="share-platform-label">LinkedIn</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="#" id="shareEmailBtn" target="_blank" rel="noopener" class="share-platform-item email-item d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none">
                            <div class="share-icon-box bg-email text-white mb-1"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                            <span class="share-platform-label">Email</span>
                        </a>
                    </div>
                </div>

                <div class="share-copy-bar input-group input-group-sm mb-2">
                    <input type="text" id="shareModalInput" class="form-control form-control-sm text-muted bg-light border-end-0" readonly>
                    <button type="button" id="shareCopyBtn" class="btn btn-primary btn-sm px-3 rounded-end">
                        <i class="far fa-copy me-1" aria-hidden="true"></i> <?= themeEscape(__('Copy')); ?>
                    </button>
                </div>
                <div id="shareCopySuccess" class="alert alert-success py-1 px-2 small text-center mb-0 d-none">
                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i> <?= themeEscape(__('Link copied!')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content / News QR Code Modal -->
<div class="modal fade" id="contentQrModal" tabindex="-1" aria-labelledby="contentQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 text-center p-3 shadow-lg">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
                <h6 class="modal-title fw-bold" id="contentQrModalLabel">
                    <i class="fas fa-qrcode me-2 content-qr-icon" aria-hidden="true"></i><?= themeEscape(__('Scan for Link')); ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div id="contentQrModalImage" class="detail-qr-modal-img content-qr-modal-image mx-auto mb-3 p-2 bg-white rounded border d-flex justify-content-center align-items-center">
                </div>
                <p id="contentQrModalTitle" class="small fw-bold mb-2 text-truncate px-2"></p>
                <div class="input-group input-group-sm mb-3">
                    <input type="text" id="contentQrModalInput" class="form-control form-control-sm text-muted small" readonly>
                    <a href="#" id="contentQrModalLink" target="_blank" class="btn btn-outline-primary btn-sm" title="<?= themeEscape(__('Open Link')); ?>">
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm w-100 rounded-pill" data-bs-dismiss="modal"><?= themeEscape(__('Close')); ?></button>
            </div>
        </div>
    </div>
</div>
