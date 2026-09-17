<?php
/**
 * Visitor Portal Component - Bottom Ticker Bar & Script Config Injector
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: visitor_ticker.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
?>
<?php if (!empty($visitor_ticker_items)) : ?>
<div class="latest-content-ticker visitor-latest-content-ticker" data-speed="<?= themeEscape($visitor_ticker_speed); ?>" role="status">
    <div class="latest-content-ticker-track">
        <div class="latest-content-ticker-group">
            <?php foreach ($visitor_ticker_items as $visitor_ticker_item) : ?>
                <a class="latest-content-ticker-item"
                   href="<?= themeEscape($visitor_ticker_item['url']); ?>"
                   title="<?= themeEscape($visitor_ticker_item['title']); ?>">
                    <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                    <span class="latest-content-title"><?= themeEscape($visitor_ticker_item['display_title']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$visitor_room_query = trim(isset($_GET['room']) ? '&room=' . simbio_security::xssFree($_GET['room']) : '');
$visitor_js_config = [
    'submitLabel' => __('Check In'),
    'submittingLabel' => __('Checking in...'),
    'failureMessage' => __('Check in failed'),
    'defaultImage' => './images/persons/photo.png',
    'feedbackResetDelay' => 5000,
    'quickFeedbackResetDelay' => 1800,
    'quotesEnabled' => $visitor_quote_enabled,
    'quoteFallback' => [
        'content' => 'Sing penting madhiang.',
        'author' => 'Pai-Jo',
    ],
    'localQuotes' => [
        [
            'content' => __('Libraries store the memory of a community and open the door to its future.'),
            'author' => $sysconf['library_name'] ?? 'SLiMS Library',
        ],
        [
            'content' => __('Reading is a quiet way to travel farther than the room you are in.'),
            'author' => 'Rasamala',
        ],
        [
            'content' => __('Good information helps people make better decisions.'),
            'author' => $sysconf['library_name'] ?? 'SLiMS Library',
        ],
        [
            'content' => __('A library grows each time someone finds what they need.'),
            'author' => 'Rasamala',
        ],
    ],
    'csrfName' => \Volnix\CSRF\CSRF::getTokenName(),
    'csrfToken' => \Volnix\CSRF\CSRF::getToken(),
    'visitorUrl' => 'index.php?p=visitor' . $visitor_room_query,
    'otherInstitutionValue' => $visitor_other_institution_value,
    'voiceEnabled' => !empty($sysconf['template']['visitor_log_voice']),
    'speechLang' => str_replace('_', '-', $sysconf['default_lang']),
];
?>
<script id="rasamala-visitor-config" type="application/json"><?= json_encode($visitor_js_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
