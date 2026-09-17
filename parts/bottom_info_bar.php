<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: bottom_info_bar.php

$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
$show_home_display_footer = themeEffectiveTemplateValue('classic_home_display_show', 'below', $sysconf) === 'bottom' && !$theme_viewer_preview_enabled;
$show_ticker_footer = themeEffectiveTemplateValue('classic_ticker_show', 0, $sysconf) === 'bottom';

$latest_content_ticker_items = [];
$home_display_footer_items = [];

include_once __DIR__ . '/../theme_helpers.php';

if (isset($dbs) && $dbs && function_exists('themeGetDisplayItems')) {
    if ($show_ticker_footer) {
        $ticker_limit = themeSafeLimit($sysconf['template']['classic_ticker_item_limit'] ?? 5, 5, 1, 12);
        $raw_ticker_limit = (int)($sysconf['template']['classic_ticker_char_limit'] ?? 48);
        $ticker_char_limit = ($raw_ticker_limit === 0) ? 0 : themeSafeInt($raw_ticker_limit, 48, 12, 160);
        $source = $sysconf['template']['classic_ticker_source'] ?? 'content';
        $content_filter = $sysconf['template']['classic_ticker_content_filter'] ?? 'all';
        $content_detail = $sysconf['template']['classic_ticker_content_detail'] ?? 'title';
        $biblio_filter = $sysconf['template']['classic_ticker_biblio_filter'] ?? 'all';

        $ticker_speed = themeEffectiveTemplateValue('classic_ticker_speed', 'normal', $sysconf);
        $latest_content_ticker_items = themeGetDisplayItems(
            $dbs,
            $source,
            $content_filter,
            $content_detail,
            $biblio_filter,
            $ticker_limit,
            $ticker_char_limit
        );
    }

    if ($show_home_display_footer) {
        $home_limit = themeSafeLimit($sysconf['template']['classic_home_item_limit'] ?? 5, 5, 1, 12);
        $raw_home_limit = (int)($sysconf['template']['classic_home_char_limit'] ?? 48);
        $home_char_limit = ($raw_home_limit === 0) ? 0 : themeSafeInt($raw_home_limit, 48, 12, 160);
        $source = $sysconf['template']['classic_home_display_source'] ?? 'content';
        $content_filter = $sysconf['template']['classic_home_display_content_filter'] ?? 'all';
        $content_detail = $sysconf['template']['classic_home_display_content_detail'] ?? 'title';
        $biblio_filter = $sysconf['template']['classic_home_display_biblio_filter'] ?? 'all';

        $home_display_footer_items = themeGetDisplayItems(
            $dbs,
            $source,
            $content_filter,
            $content_detail,
            $biblio_filter,
            $home_limit,
            $home_char_limit
        );
    }
}
?>

<?php if (!empty($latest_content_ticker_items)) : ?>
    <div class="latest-content-ticker" data-speed="<?= themeEscape($ticker_speed ?? 'normal'); ?>" role="status">
        <div class="latest-content-ticker-track">
            <?php for ($ticker_repeat = 0; $ticker_repeat < 2; $ticker_repeat++) : ?>
                <div class="latest-content-ticker-group" <?= $ticker_repeat === 1 ? 'inert' : '' ?>>
                    <?php foreach ($latest_content_ticker_items as $latest_content_ticker_item) : ?>
                        <a class="latest-content-ticker-item"
                           href="<?= themeEscape($latest_content_ticker_item['url']); ?>"
                           title="<?= themeEscape($latest_content_ticker_item['title']); ?>">
                            <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                            <span class="latest-content-title"><?= themeEscape($latest_content_ticker_item['display_title']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($home_display_footer_items)) : ?>
    <div class="latest-content-strip">
        <ul class="latest-content-list">
            <?php foreach ($home_display_footer_items as $item) : ?>
                <li>
                    <a class="latest-content-link"
                       href="<?= themeEscape($item['url']); ?>"
                       title="<?= themeEscape($item['title']); ?>">
                        <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                        <span class="latest-content-title"><?= themeEscape($item['display_title']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
