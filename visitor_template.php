<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: visitor_template.php
$main_template_path = __DIR__ . '/login_template.inc.php';
include_once __DIR__ . '/classic.php';
require_once __DIR__ . '/helpers/visitor.php';

// Set language based on query param or cookie
themeVisitorSetLanguage($sysconf, $available_languages ?? []);

$visitor_quote_enabled = ($sysconf['template']['visitor_quote'] ?? 1) == 1;
$visitor_title = themeEffectiveTemplateValue('visitor_title', '', $sysconf);
if (trim((string)$visitor_title) === '') {
    $visitor_title = $sysconf['library_name'] ?? 'SLiMS Library';
}
$visitor_subtitle = themeEffectiveTemplateValue('visitor_subtitle', 'Visitor Check-In Portal', $sysconf);
$visitor_theme_toggle_enabled = (themeEffectiveTemplateValue('visitor_theme_toggle', 1, $sysconf) == 1);
$visitor_layout_style = themeEffectiveTemplateValue('visitor_layout_style', 'kiosk', $sysconf);
$visitor_institution_select_label = trim((string)themeEffectiveTemplateValue('visitor_institution_select_label', __('Pilih Fakultas / Institusi'), $sysconf));
if ($visitor_institution_select_label === '') {
    $visitor_institution_select_label = __('Pilih Fakultas / Institusi');
}
$visitor_institution_options = themeVisitorInstitutionOptions(themeEffectiveTemplateValue('visitor_institution_options', '', $sysconf));
$visitor_other_institution_value = themeVisitorInstitutionManualValue($visitor_institution_options);

$visitor_split_title = trim((string)themeEffectiveTemplateValue('visitor_split_title', 'Petunjuk Penggunaan', $sysconf));
if ($visitor_split_title === '') {
    $visitor_split_title = 'Petunjuk Penggunaan';
}
$visitor_split_steps_html = rasamalaVisitorSplitStepsHtml(themeEffectiveTemplateValue('visitor_split_steps', '', $sysconf));

$visitor_ticker_items = [];
$visitor_ticker_speed = themeEffectiveTemplateValue('classic_ticker_speed', 'normal', $sysconf);
$visitor_ticker_setting = strtolower(trim((string)themeEffectiveTemplateValue('classic_ticker_show', 0, $sysconf)));
$visitor_ticker_enabled = !in_array($visitor_ticker_setting, ['', '0', 'hide', 'none'], true);

if ($visitor_ticker_enabled && isset($dbs) && $dbs && function_exists('themeGetDisplayItems')) {
    $visitor_ticker_limit = themeSafeLimit($sysconf['template']['classic_ticker_item_limit'] ?? 5, 5, 1, 12);
    $raw_visitor_ticker_limit = (int)($sysconf['template']['classic_ticker_char_limit'] ?? 48);
    $visitor_ticker_char_limit = ($raw_visitor_ticker_limit === 0) ? 0 : themeSafeInt($raw_visitor_ticker_limit, 48, 12, 160);
    $visitor_ticker_source = $sysconf['template']['classic_ticker_source'] ?? 'content';
    $visitor_ticker_content_filter = $sysconf['template']['classic_ticker_content_filter'] ?? 'all';
    $visitor_ticker_content_detail = $sysconf['template']['classic_ticker_content_detail'] ?? 'title';
    $visitor_ticker_biblio_filter = $sysconf['template']['classic_ticker_biblio_filter'] ?? 'all';

    $visitor_ticker_items = themeGetDisplayItems(
        $dbs,
        $visitor_ticker_source,
        $visitor_ticker_content_filter,
        $visitor_ticker_content_detail,
        $visitor_ticker_biblio_filter,
        $visitor_ticker_limit,
        $visitor_ticker_char_limit
    );
}
?>

<div class="visitor-bg-gradient"></div>

<h1 class="visually-hidden"><?= themeEscape($visitor_title); ?></h1>

<?php
if ($visitor_layout_style === 'split') {
    include __DIR__ . '/parts/visitor/visitor_split.php';
} else {
    include __DIR__ . '/parts/visitor/visitor_kiosk.php';
}

include __DIR__ . '/parts/visitor/visitor_ticker.php';
