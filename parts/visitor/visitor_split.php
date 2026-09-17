<?php
/**
 * Visitor Portal Component - Split Layout (Form Left & Instruction Section Right)
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: visitor_split.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
?>
<div class="d-flex align-items-center justify-content-center min-vh-100 w-100 visitor-backdrop <?= !empty($visitor_ticker_items) ? 'visitor-has-running-text' : ''; ?>" id="visitor-counter">
    <main class="main-split-container">
        
        <section class="left-form-section">
            <div class="text-center mb-4">
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

                <!-- Input Form Tabs -->
                <div v-show="textInfo === ''">
                    <nav class="tabs" role="tablist">
                        <button type="button" id="visitor-tab-member" class="tab-link" :class="{ active: activeTab === 'member' }" @click="activeTab = 'member'" role="tab" aria-controls="visitor-panel-member" :aria-selected="activeTab === 'member' ? 'true' : 'false'"><?= __('Member') ?></button>
                        <button type="button" id="visitor-tab-non-member" class="tab-link" :class="{ active: activeTab === 'non-member' }" @click="activeTab = 'non-member'" role="tab" aria-controls="visitor-panel-non-member" :aria-selected="activeTab === 'non-member' ? 'true' : 'false'"><?= __('Non-Member') ?></button>
                    </nav>

                    <!-- Member Tab Form -->
                    <form id="visitor-panel-member" v-show="activeTab === 'member'" @submit.prevent="onSubmit" :aria-busy="isSubmitting ? 'true' : 'false'" role="tabpanel" aria-labelledby="visitor-tab-member">
                        <div class="mb-3 text-start mb-4">
                            <input v-model="memberId" ref="memberId" autofocus type="text" class="form-control form-control-lg visitor-input" id="member-id-input-split"
                                   placeholder="<?= themeEscape(__('Enter your member ID')) ?>" aria-label="<?= themeEscape(__('Enter your member ID')) ?>" autocomplete="off">
                        </div>
                        <p class="instruction-text text-muted text-center text-xs mb-3"><?= themeTranslate('Ensure the cursor is active in the field before scanning / typing.') ?></p>
                        <button type="submit" class="btn btn-primary w-100 btn-lg btn-visitor-checkin shadow-sm" :disabled="isSubmitting">
                            <i class="fas fa-sign-in-alt me-2" v-if="!isSubmitting" aria-hidden="true"></i>
                            <span>{{ isSubmitting ? submittingLabel : submitLabel }}</span>
                        </button>
                    </form>

                    <!-- Non-Member Tab Form -->
                    <form id="visitor-panel-non-member" v-show="activeTab === 'non-member'" @submit.prevent="onSubmit" :aria-busy="isSubmitting ? 'true' : 'false'" role="tabpanel" aria-labelledby="visitor-tab-non-member">
                        <div class="mb-3 text-start mb-4">
                            <input v-model="visitorName" ref="nonMemberNameInput" type="text" class="form-control form-control-lg visitor-input mb-3"
                                   placeholder="<?= themeEscape(themeTranslate('Full Name')) ?>" aria-label="<?= themeEscape(themeTranslate('Full Name')) ?>" autocomplete="off">
                            
                            <select v-model="selectInstitution" class="form-control form-control-lg visitor-input mb-3" aria-label="<?= themeEscape(themeTranslate($visitor_institution_select_label)) ?>">
                                <option value="" disabled selected><?= themeEscape(themeTranslate($visitor_institution_select_label)) ?></option>
                                <?php foreach ($visitor_institution_options as $visitor_institution_option) : ?>
                                <option value="<?= themeEscape($visitor_institution_option['value']); ?>"><?= themeEscape($visitor_institution_option['label']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input v-show="isManualInstitutionSelected()" v-model="manualInstitution" type="text" class="form-control form-control-lg visitor-input"
                                   placeholder="<?= themeEscape(themeTranslate('Enter Institution Name...')) ?>" aria-label="<?= themeEscape(themeTranslate('Enter Institution Name...')) ?>" autocomplete="off">
                        </div>
                        <p class="instruction-text text-muted text-center text-xs mb-3"><?= themeTranslate('Fill in personal data for non-member visitors') ?></p>
                        <button type="submit" class="btn btn-primary w-100 btn-lg btn-visitor-checkin shadow-sm" :disabled="isSubmitting">
                            <i class="fas fa-sign-in-alt me-2" v-if="!isSubmitting" aria-hidden="true"></i>
                            <span>{{ isSubmitting ? submittingLabel : submitLabel }}</span>
                        </button>
                    </form>
                </div>
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
        </section>

        <section class="right-instruction-section">
            <h2 class="inst-title"><?= themeEscape($visitor_split_title); ?></h2>
            <div class="inst-steps">
                <?= $visitor_split_steps_html; ?>
            </div>
        </section>
    </main>
</div>
