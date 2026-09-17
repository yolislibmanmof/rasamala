<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _search-form.php

if ($opac->invalid_token) {
    //die($opac->error('invalid CSRF token'));
}
?>
<?php
$search_size = $sysconf['template']['classic_search_size'] ?? 'medium';
if (!in_array($search_size, ['small', 'medium', 'large'], true)) {
    $search_size = 'medium';
}
$hero_text = trim((string)($sysconf['template']['classic_hero_text'] ?? ''));
$hero_text_size = $sysconf['template']['classic_hero_text_size'] ?? 'small';
if (!in_array($hero_text_size, ['small', 'medium', 'large'], true)) {
    $hero_text_size = 'small';
}
$is_homepage_search = !isset($_GET['p']) && !isset($_GET['search']);

// When library name position is 'hero', display library name above search box
$_search_lib_name_position = strtolower(trim((string)themeEffectiveTemplateValue('classic_library_name_position', 'navbar', $sysconf)));
$_search_lib_name_in_hero = ($_search_lib_name_position === 'hero');
$_search_hero_logo_html = '';
if ($_search_lib_name_in_hero) {
    $hero_text = trim((string)($sysconf['library_name'] ?? ''));

    $_search_hero_logo_html = themeLibraryLogoHtml($sysconf, $imagesDisk ?? null, 'hero-library-logo');
}

$show_hero_text = ($is_homepage_search || $_search_lib_name_in_hero) && $hero_text !== '';
$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
$latest_content_display = strtolower(trim((string)themeEffectiveTemplateValue('classic_home_display_show', 'below', $sysconf)));
if ($latest_content_display === '1') {
    $latest_content_display = 'below';
} elseif ($latest_content_display === 'show') {
    $latest_content_display = 'below';
} elseif ($latest_content_display === '0') {
    $latest_content_display = 'hide';
}
if (!in_array($latest_content_display, ['below', 'bottom', 'hide'], true)) {
    $latest_content_display = 'below';
}
$show_latest_content = $is_homepage_search && ($latest_content_display === 'below' || $theme_viewer_preview_enabled);
$latest_content_items = [];
$ticker_items_below = [];

if (isset($dbs) && $dbs) {
    include_once __DIR__ . '/../theme_helpers.php';
    if (function_exists('themeGetDisplayItems')) {
        if ($show_latest_content) {
            $home_limit = themeSafeLimit($sysconf['template']['classic_home_item_limit'] ?? 5, 5, 1, 12);
            $raw_home_limit = (int)($sysconf['template']['classic_home_char_limit'] ?? 48);
            $home_char_limit = ($raw_home_limit === 0) ? 0 : themeSafeInt($raw_home_limit, 48, 12, 160);
            $source = $sysconf['template']['classic_home_display_source'] ?? 'content';
            $content_filter = $sysconf['template']['classic_home_display_content_filter'] ?? 'all';
            $content_detail = $sysconf['template']['classic_home_display_content_detail'] ?? 'title';
            $biblio_filter = $sysconf['template']['classic_home_display_biblio_filter'] ?? 'all';

            $latest_content_items = themeGetDisplayItems(
                $dbs,
                $source,
                $content_filter,
                $content_detail,
                $biblio_filter,
                $home_limit,
                $home_char_limit
            );
        }

        $ticker_position = strtolower(trim((string)themeEffectiveTemplateValue('classic_ticker_show', 0, $sysconf)));
        $show_ticker_below = $is_homepage_search && ($ticker_position === 'below' || ($theme_viewer_preview_enabled && $ticker_position !== 'bottom'));
        if ($show_ticker_below) {
            $ticker_limit = themeSafeLimit($sysconf['template']['classic_ticker_item_limit'] ?? 5, 5, 1, 12);
            $raw_ticker_limit = (int)($sysconf['template']['classic_ticker_char_limit'] ?? 48);
            $ticker_char_limit = ($raw_ticker_limit === 0) ? 0 : themeSafeInt($raw_ticker_limit, 48, 12, 160);
            $source = $sysconf['template']['classic_ticker_source'] ?? 'content';
            $content_filter = $sysconf['template']['classic_ticker_content_filter'] ?? 'all';
            $content_detail = $sysconf['template']['classic_ticker_content_detail'] ?? 'title';
            $biblio_filter = $sysconf['template']['classic_ticker_biblio_filter'] ?? 'all';

            $ticker_speed = themeEffectiveTemplateValue('classic_ticker_speed', 'normal', $sysconf);
            $ticker_items_below = themeGetDisplayItems(
                $dbs,
                $source,
                $content_filter,
                $content_detail,
                $biblio_filter,
                $ticker_limit,
                $ticker_char_limit
            );
        }
    }
}

$col_class = ($search_size === 'large') ? 'col-lg-10' : 'col-lg-8';
?>

<div class="search position-relative search-size-<?= themeEscape($search_size) ?> <?= themeHomepageOnlyHero($sysconf) ? 'mt-0' : '' ?>" id="search-wraper">
    <div class="container">
        <div class="row">
            <div class="<?= themeEscape($col_class) ?> mx-auto">
                <?php if ((int)themeEffectiveTemplateValue('classic_announcement_show', 0, $sysconf) === 1 && !empty($sysconf['template']['classic_announcement_text'])) : ?>
                <div class="alert alert-<?= themeEscape($sysconf['template']['classic_announcement_style'] ?? 'info'); ?> alert-dismissible fade show shadow-sm px-4 mb-4 text-center rounded-3" role="alert">
                    <?= themeSanitizeHtml($sysconf['template']['classic_announcement_text']); ?>
                    <button type="button" class="btn-close rasamala-alert-close-centered" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php if ($show_hero_text) : ?>
                <div class="hero-search-heading hero-search-heading-<?= themeEscape($hero_text_size) ?><?= $_search_lib_name_in_hero ? ' hero-search-heading-library' : '' ?> text-center">
                    <?php if ($_search_lib_name_in_hero && $_search_hero_logo_html !== '') : ?>
                    <div class="hero-library-logo-wrap" aria-hidden="true">
                        <?= $_search_hero_logo_html; ?>
                    </div>
                    <?php endif; ?>
                    <h1><?= themeEscape($hero_text); ?></h1>
                </div>
                <?php endif; ?>
                <div class="position-relative" v-click-outside="hideSuggestions">
                    <div class="card border-0 shadow-sm rounded-pill">
                        <div class="card-body">
                            <form id="search-form" class="d-flex align-items-center" action="index.php" method="get" @submit.prevent="searchSubmit">
                                <input type="hidden" name="search" value="search">
                                <input ref="keywords" value="<?= themeEscape(getQuery('keywords')) ?>" v-model.trim="keywords"
                                       type="text" id="search-input"
                                       name="keywords" class="input-transparent flex-grow-1" autocomplete="off"
                                       @focus="searchOnFocus"
                                       @click="searchOnClickArea"
                                       @mousedown="searchOnClickArea"
                                       @blur="searchOnBlur"
                                       @keydown.down.prevent="navigateSuggestions(1)"
                                       @keydown.up.prevent="navigateSuggestions(-1)"
                                       @keydown.enter.prevent="handleEnterKey"
                                       @keydown.esc="showSuggestions = false"
                                       aria-label="<?= themeEscape(__('Search keyword')); ?>"
                                       placeholder="<?= themeEscape(__($sysconf['template']['classic_search_placeholder'] ?? 'Enter keyword to search collection...'));?>"/>
                                <div class="d-flex align-items-center gap-2 ms-2">
                                    <!-- Keyboard Shortcut Badge -->
                                    <kbd class="search-kbd-badge shadow-sm" id="search-kbd-badge" title="<?= themeEscape(__('Press Ctrl+K to search')) ?>" aria-label="Ctrl K" tabindex="0" role="button">
                                        <span id="search-kbd-modifier">Ctrl</span> K
                                    </kbd>
                                    <!-- Advanced Search Icon -->
                                    <a href="#" class="d-inline-flex open-adv-modal-btn" data-bs-toggle="modal" data-bs-target="#adv-modal" data-toggle="modal" data-target="#adv-modal" title="<?= themeEscape(__('Advanced Search')) ?>" aria-label="<?= themeEscape(__('Advanced Search')) ?>">
                                        <i class="fas fa-sliders-h" aria-hidden="true"></i>
                                    </a>
                                    <!-- Search Button -->
                                    <button type="submit" class="btn p-0 border-0 bg-transparent" aria-label="<?= themeEscape(__('Search')) ?>" :aria-busy="loading ? 'true' : 'false'" :disabled="loading">
                                        <i v-if="loading" class="fas fa-spinner fa-spin text-primary" aria-hidden="true"></i>
                                        <i v-else class="fas fa-search" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Live Search Autocomplete Suggestions Panel (UX-05) -->
                    <div v-if="showSuggestions && (suggestions.length > 0 || (keywords === '' && lastKeywords.length > 0))"
                         class="search-suggestion-dropdown shadow-lg" role="listbox" id="search-suggestions-list">

                        <!-- Live Suggestions Group -->
                        <template v-if="suggestions.length > 0">
                            <div class="search-suggestion-group-title d-flex justify-content-between align-items-center">
                                <span><?= __('Search Suggestions') ?></span>
                                <span v-if="suggestLoading" class="badge bg-light text-muted fw-normal"><i class="fas fa-spinner fa-spin me-1"></i><?= __('Loading...') ?></span>
                            </div>
                            <div v-for="(item, idx) in suggestions"
                                 :key="'sug-' + idx"
                                 :class="['search-suggestion-item', { 'is-selected': selectedIndex === idx }]"
                                 role="option"
                                 :aria-selected="selectedIndex === idx"
                                 @mousedown.prevent="selectSuggestion(item)">
                                <div class="search-suggestion-icon">
                                    <i :class="item.icon || 'fas fa-search'" aria-hidden="true"></i>
                                </div>
                                <div class="search-suggestion-content">
                                    <div class="search-suggestion-text">{{ item.text }}</div>
                                    <div class="search-suggestion-meta" v-if="item.type === 'history'"><?= __('Recent Search') ?></div>
                                    <div class="search-suggestion-meta" v-else-if="item.type === 'title'"><?= __('Collection Title') ?></div>
                                </div>
                            </div>
                        </template>

                        <!-- Search History Fallback Group -->
                        <template v-else-if="keywords === '' && lastKeywords.length > 0">
                            <div class="search-suggestion-group-title d-flex justify-content-between align-items-center">
                                <span><?= __('Search History') ?></span>
                                <button type="button" class="btn btn-link btn-xs text-danger text-decoration-none p-0" @click.stop="clearHistory">
                                    <i class="fas fa-trash-alt me-1"></i><?= __('Clear') ?>
                                </button>
                            </div>
                            <div v-for="(key, idx) in lastKeywords"
                                 :key="'hist-' + idx"
                                 :class="['search-suggestion-item', { 'is-selected': selectedIndex === idx }]"
                                 role="option"
                                 :aria-selected="selectedIndex === idx"
                                 @mousedown.prevent="selectSuggestion(idx)">
                                <div class="search-suggestion-icon">
                                    <i class="fas fa-history" aria-hidden="true"></i>
                                </div>
                                <div class="search-suggestion-content">
                                    <div class="search-suggestion-text" v-if="tmpObj[key]">{{ tmpObj[key].text }}</div>
                                    <div class="search-suggestion-meta"><?= __('Saved in browser') ?></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <?php if (!empty($ticker_items_below) && $show_ticker_below) : ?>
                <div class="latest-content-ticker mt-3 mb-2" data-speed="<?= themeEscape($ticker_speed); ?>" role="status">
                    <div class="latest-content-ticker-track">
                        <?php for ($ticker_repeat = 0; $ticker_repeat < 2; $ticker_repeat++) : ?>
                            <div class="latest-content-ticker-group" <?= $ticker_repeat === 1 ? 'inert' : '' ?>>
                                <?php foreach ($ticker_items_below as $ticker_item) : ?>
                                    <a class="latest-content-ticker-item"
                                       href="<?= themeEscape($ticker_item['url']); ?>"
                                       title="<?= themeEscape($ticker_item['title']); ?>">
                                        <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                                        <span class="latest-content-title"><?= themeEscape($ticker_item['display_title']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($latest_content_items) && ($latest_content_display === 'below' || $theme_viewer_preview_enabled)) :
                    $home_display_style = themeEffectiveTemplateValue('classic_home_display_style', 'badges', $sysconf);
                ?>
                <div class="latest-content-strip latest-content-style-<?= themeEscape($home_display_style); ?>">
                    <?php if ($home_display_style === 'fade') : ?>
                        <div class="latest-content-fade-slider" id="hero-info-fade-slider">
                            <?php foreach ($latest_content_items as $index => $latest_content_item) : ?>
                            <div class="latest-content-fade-item <?= $index === 0 ? 'active' : '' ?>">
                                <a class="latest-content-link"
                                   href="<?= themeEscape($latest_content_item['url']); ?>"
                                   title="<?= themeEscape($latest_content_item['title']); ?>">
                                    <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                                    <span class="latest-content-title"><?= themeEscape($latest_content_item['display_title']); ?></span>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($home_display_style === 'ticker') : ?>
                        <div class="latest-content-ticker mt-1 mb-1" data-speed="normal" role="status">
                            <div class="latest-content-ticker-track">
                                <?php for ($ticker_repeat = 0; $ticker_repeat < 2; $ticker_repeat++) : ?>
                                    <div class="latest-content-ticker-group" <?= $ticker_repeat === 1 ? 'inert' : '' ?>>
                                        <?php foreach ($latest_content_items as $ticker_item) : ?>
                                            <a class="latest-content-ticker-item"
                                               href="<?= themeEscape($ticker_item['url']); ?>"
                                               title="<?= themeEscape($ticker_item['title']); ?>">
                                                <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                                                <span class="latest-content-title"><?= themeEscape($ticker_item['display_title']); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php else : 
                        $scroll_class = (count($latest_content_items) > 3) ? 'scrollable' : '';
                    ?>
                        <ul class="latest-content-list <?= $scroll_class; ?>">
                            <?php foreach ($latest_content_items as $latest_content_item) : ?>
                            <li>
                                <a class="latest-content-link"
                                   href="<?= themeEscape($latest_content_item['url']); ?>"
                                   title="<?= themeEscape($latest_content_item['title']); ?>">
                                    <i class="fas fa-volume-up latest-content-icon" aria-hidden="true"></i>
                                    <span class="latest-content-title"><?= themeEscape($latest_content_item['display_title']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
