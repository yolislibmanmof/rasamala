<?php
/**
 * Visitor Portal Component - Kiosk Centered Card Layout View
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: visitor_kiosk.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
?>
<div class="d-flex align-items-center justify-content-center min-vh-100 w-100 visitor-backdrop <?= !empty($visitor_ticker_items) ? 'visitor-has-running-text' : ''; ?>" id="visitor-counter">
    <div class="visitor-kiosk-card p-4 p-md-5 text-center shadow-lg">
        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-bold visitor-welcome-title mb-2"><?= themeEscape($visitor_title); ?></h2>
            <p class="text-muted visitor-subtitle mb-0"><?= themeEscape($visitor_subtitle); ?></p>
        </div>

        <!-- Form and Feedback Area -->
        <div class="visitor-card-body position-relative">
            <!-- Checking status card (Success/Error/Warning) -->
            <div v-if="textInfo !== ''" class="feedback-container mb-4" role="status" aria-live="polite" aria-atomic="true">
                <div class="feedback-card d-flex flex-column align-items-center" :class="'feedback-' + textInfoType">
                    <div class="visitor-avatar-wrap mb-3 shadow-sm">
                        <img :src="image" alt="<?= themeEscape(__('Visitor profile photo')) ?>" class="img-fluid rounded-circle visitor-avatar-img" @error="onImageError">
                    </div>
                    <h4 class="fw-bold mb-2 visitor-feedback-text" v-text="textInfo"></h4>
                    <p class="text-xs text-muted mb-0"><?= themeEscape(__('Auto resetting in 5 seconds...')) ?></p>
                </div>
            </div>

            <!-- Input Form -->
            <form v-show="textInfo === ''" @submit.prevent="onSubmit" :aria-busy="isSubmitting ? 'true' : 'false'">
                <div class="mb-3 text-start mb-4">
                    <input v-model="memberId" ref="memberId" autofocus type="text" class="form-control form-control-lg visitor-input" id="member-id-input"
                           placeholder="<?= themeEscape(__('Enter your member ID')) ?>" aria-label="<?= themeEscape(__('Enter your member ID')) ?>" autocomplete="off">
                </div>
                <div class="mb-3 text-start mb-4">
                    <input v-model="institution" type="text" class="form-control form-control-lg visitor-input" id="institution-input"
                           placeholder="<?= themeEscape(__('Enter your institution')) ?>" aria-label="<?= themeEscape(__('Enter your institution')) ?>" autocomplete="off">
                    <small class="form-text text-muted mt-2 text-center w-100 d-block"><?= themeEscape(__('Enough fill your member ID if you are member of ').$sysconf['library_name']); ?></small>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg btn-visitor-checkin mt-2 shadow-sm" :disabled="isSubmitting">
                    <i class="fas fa-sign-in-alt me-2" v-if="!isSubmitting" aria-hidden="true"></i>
                    <span>{{ isSubmitting ? submittingLabel : submitLabel }}</span>
                </button>
            </form>
        </div>

        <!-- Footer Clock & Toggle -->
        <div class="mt-4 pt-3 border-top visitor-card-footer text-center position-relative">
            <div class="visitor-clock fw-bold" v-text="currentTime"></div>
            <?php if ($visitor_theme_toggle_enabled) : ?>
            <button type="button" id="color-mode-toggle-desktop" class="visitor-toggle-btn" title="<?= themeEscape(__('Dark mode')) ?>" data-dark-title="<?= themeEscape(__('Dark mode')) ?>" data-light-title="<?= themeEscape(__('Light mode')) ?>" aria-label="<?= themeEscape(__('Toggle dark/light mode')) ?>" aria-pressed="false">
                <i class="fas fa-moon" aria-hidden="true"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
