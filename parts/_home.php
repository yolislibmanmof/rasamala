<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _home.php

$hero_mode = function_exists('themeHomepageHeroMode') ? themeHomepageHeroMode($sysconf) : 'no';
$is_homepage_only_hero = $hero_mode !== 'no';
$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
$hero_inside_content = function_exists('themeHomepageHeroInsideContent') ? themeHomepageHeroInsideContent($sysconf) : 'none';
$home_topic_items = themeParseTopicItems($sysconf['template']['classic_topic_items'] ?? themeTopicItemsDefault());
$hero_topics_section_enabled = function_exists('themeHomepageSectionEnabled')
    ? ($theme_viewer_preview_enabled || themeHomepageSectionEnabled('topic', $sysconf))
    : $theme_viewer_preview_enabled;
$hero_topics_in_hero = $is_homepage_only_hero
    && function_exists('themeHomepageHeroShowsTopics')
    && themeHomepageHeroShowsTopics($sysconf)
    && $hero_topics_section_enabled
    && !empty($home_topic_items);
$hero_topics_preview_available = $theme_viewer_preview_enabled
    && $hero_topics_section_enabled
    && !empty($home_topic_items);
$home_tabs_configured = (int)themeEffectiveTemplateValue('classic_home_sections_tabs', 0, $sysconf) === 1;
$hero_has_below_content = false;
if ($is_homepage_only_hero && function_exists('themeHomepageSectionOrder') && function_exists('themeHomepageSectionEnabled')) {
    $hero_section_order = themeHomepageSectionOrder($sysconf);
    foreach ($hero_section_order as $hero_section_key) {
        // The selected hero section is rendered inside the fullscreen hero,
        // so do not reserve a duplicate copy below it.
        $hero_section_alias = $hero_section_key === 'new-collection' ? 'new_update' : ($hero_section_key === 'topic' ? 'topics' : $hero_section_key);
        if ($is_homepage_only_hero && $hero_inside_content !== 'none' && $hero_section_alias === $hero_inside_content) {
            continue;
        }
        if ($theme_viewer_preview_enabled || themeHomepageSectionEnabled($hero_section_key, $sysconf)) {
            $hero_has_below_content = true;
            break;
        }
    }
}
$background_animation = themeBackgroundAnimation();
$background_animation_class = $background_animation !== 'none' ? ' rasamala-search-banner-bg-' . themeEscape($background_animation) : '';
$parallel_title_separator = themeParallelTitleSeparator();
$title_character_limit = themeTitleCharacterLimit();
$home_content_source = $sysconf;
if ($theme_viewer_preview_enabled) {
    $home_content_source['template']['classic_home_content_cards_show'] = 1;
}
$home_content_cards = (isset($dbs) && $dbs && function_exists('themeHomeContentCards')) ? themeHomeContentCards($dbs, $home_content_source) : [];
?>

<script nonce="<?= themeCspNonce(); ?>">
window.rasamalaParallelTitleSeparator = <?= json_encode($parallel_title_separator, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
window.rasamalaTitleCharacterLimit = <?= themeSafeInt($title_character_limit, 100, 1, 300); ?>;
</script>

<section id="section1" class="container-fluid position-relative rasamala-hero-section <?= $is_homepage_only_hero ? 'rasamala-hero-section-only' : '' ?>">
    <header class="c-header position-relative">
      <?php
      // ------------------------------------------------------------------------
      // include navbar
      // ------------------------------------------------------------------------
      include '_navbar.php'; ?>
    </header>

    <!-- Search form section inside its own clean wrapper below the hero book -->
    <div class="rasamala-search-banner-section py-4 <?= $is_homepage_only_hero ? 'rasamala-search-banner-hero-only' . $background_animation_class : '' ?>" id="search-section">
        <div class="container rasamala-search-hero-content">
            <?php include '_search-form.php'; ?>
            <?php if ($is_homepage_only_hero || $theme_viewer_preview_enabled) : ?>
            <div id="rasamala-hero-inside-content" class="rasamala-hero-inside-content mt-3" <?= ($is_homepage_only_hero && $hero_inside_content !== 'none') || $theme_viewer_preview_enabled ? '' : 'hidden'; ?>>
                <!-- 1. Topics -->
                <div id="rasamala-hero-inside-topics" class="rasamala-hero-inside-item" data-inside="topics" <?= ($is_homepage_only_hero && $hero_inside_content === 'topics') ? '' : 'hidden'; ?>>
                    <div data-hero-inside-mount
                         data-popular-limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-new-limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-top-reader-limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>">
                        <?php if ($is_homepage_only_hero && $hero_inside_content === 'topics' && !empty($home_topic_items)) : ?>
                        <div class="rasamala-hero-topics" aria-label="<?= themeEscape(__('Topics')); ?>">
                            <ul class="topic d-flex flex-wrap justify-content-center px-0 mb-0" role="list">
                                <?php foreach ($home_topic_items as $home_topic_item) echo themeTopicItemHtml($home_topic_item); ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- 2. Popular Among Our Collection -->
                <div id="rasamala-hero-inside-popular" class="rasamala-hero-inside-item" data-inside="popular" <?= ($is_homepage_only_hero && $hero_inside_content === 'popular') ? '' : 'hidden'; ?>>
                    <div data-hero-inside-mount
                         data-popular-limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-new-limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-top-reader-limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>">
                        <?php if ($is_homepage_only_hero && $hero_inside_content === 'popular') : ?>
                        <div class="rasamala-hero-inline-section">
                            <h2 class="rasamala-hero-inline-title"><i class="fas fa-fire" aria-hidden="true"></i> <?= themeEscape(__('Popular among our collections')); ?></h2>
                            <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
                            <slims-collection url="index.php?p=api/biblio/popular" limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- 3. New Collection + Update -->
                <div id="rasamala-hero-inside-new_update" class="rasamala-hero-inside-item" data-inside="new_update" <?= ($is_homepage_only_hero && $hero_inside_content === 'new_update') ? '' : 'hidden'; ?>>
                    <div data-hero-inside-mount
                         data-popular-limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-new-limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-top-reader-limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>">
                        <?php if ($is_homepage_only_hero && $hero_inside_content === 'new_update') : ?>
                        <div class="rasamala-hero-inline-section">
                            <h2 class="rasamala-hero-inline-title"><i class="fas fa-book" aria-hidden="true"></i> <?= themeEscape(__('New collections + updated')); ?></h2>
                            <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
                            <slims-collection url="index.php?p=api/biblio/latest" limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- 4. Top Reader of the Year -->
                <div id="rasamala-hero-inside-top_reader" class="rasamala-hero-inside-item" data-inside="top_reader" <?= ($is_homepage_only_hero && $hero_inside_content === 'top_reader') ? '' : 'hidden'; ?>>
                    <div data-hero-inside-mount
                         data-popular-limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-new-limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-top-reader-limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>">
                        <?php if ($is_homepage_only_hero && $hero_inside_content === 'top_reader') : ?>
                        <div class="rasamala-hero-inline-section">
                            <h2 class="rasamala-hero-inline-title"><i class="fas fa-trophy" aria-hidden="true"></i> <?= themeEscape(__('Top reader of the year')); ?></h2>
                            <slims-group-member url="index.php?p=api/member/top" limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>"></slims-group-member>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- 5. Latest Content / News Cards -->
                <div id="rasamala-hero-inside-news" class="rasamala-hero-inside-item" data-inside="news" <?= ($is_homepage_only_hero && $hero_inside_content === 'news') ? '' : 'hidden'; ?>>
                    <div data-hero-inside-mount
                         data-popular-limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-new-limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"
                         data-top-reader-limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>">
                        <?php if ($is_homepage_only_hero && $hero_inside_content === 'news') : ?>
                        <div class="rasamala-hero-inline-section">
                            <h2 class="rasamala-hero-inline-title"><i class="fas fa-newspaper" aria-hidden="true"></i> <?= themeEscape(__('Latest Content')); ?></h2>
                            <div class="rasamala-home-content-cards">
                                <?php foreach ($home_content_cards as $content_card) :
                                    $card_title = $content_card['title'] ?? '';
                                    $card_url = $content_card['url'] ?? '#';
                                    $card_excerpt = $content_card['excerpt'] ?? '';
                                    $card_image = $content_card['image_src'] ?? '';
                                ?>
                                <a class="rasamala-home-content-card" href="<?= themeEscape($card_url); ?>" title="<?= themeEscape($card_title); ?>">
                                    <span class="rasamala-home-content-thumb" aria-hidden="<?= $card_image ? 'false' : 'true'; ?>">
                                        <?php if ($card_image) : ?>
                                        <img loading="lazy" src="<?= themeEscape($card_image); ?>" alt="" aria-hidden="true">
                                        <?php else : ?>
                                        <span class="rasamala-home-content-thumb-placeholder">
                                            <i class="fas fa-newspaper" aria-hidden="true"></i>
                                        </span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="rasamala-home-content-body">
                                        <span class="rasamala-home-content-title"><?= themeEscape($card_title); ?></span>
                                        <?php if ($card_excerpt) : ?>
                                        <span class="rasamala-home-content-excerpt"><?= themeEscape($card_excerpt); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($theme_viewer_preview_enabled) : ?>
            <div id="rasamala-hero-inside-templates" hidden aria-hidden="true">
                <template data-hero-inside-template="topics">
                    <div class="rasamala-hero-topics" aria-label="<?= themeEscape(__('Topics')); ?>">
                        <ul class="topic d-flex flex-wrap justify-content-center px-0 mb-0" role="list">
                            <?php foreach ($home_topic_items as $home_topic_item) echo themeTopicItemHtml($home_topic_item); ?>
                        </ul>
                    </div>
                </template>
                <template data-hero-inside-template="popular">
                    <div class="rasamala-hero-inline-section">
                        <h2 class="rasamala-hero-inline-title"><i class="fas fa-fire" aria-hidden="true"></i> <?= themeEscape(__('Popular among our collections')); ?></h2>
                        <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
                        <slims-collection url="index.php?p=api/biblio/popular" limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
                    </div>
                </template>
                <template data-hero-inside-template="new_update">
                    <div class="rasamala-hero-inline-section">
                        <h2 class="rasamala-hero-inline-title"><i class="fas fa-book" aria-hidden="true"></i> <?= themeEscape(__('New collections + updated')); ?></h2>
                        <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
                        <slims-collection url="index.php?p=api/biblio/latest" limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
                    </div>
                </template>
                <template data-hero-inside-template="top_reader">
                    <div class="rasamala-hero-inline-section">
                        <h2 class="rasamala-hero-inline-title"><i class="fas fa-trophy" aria-hidden="true"></i> <?= themeEscape(__('Top reader of the year')); ?></h2>
                        <slims-group-member url="index.php?p=api/member/top" limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>"></slims-group-member>
                    </div>
                </template>
                <template data-hero-inside-template="news">
                    <div class="rasamala-hero-inline-section">
                        <h2 class="rasamala-hero-inline-title"><i class="fas fa-newspaper" aria-hidden="true"></i> <?= themeEscape(__('Latest Content')); ?></h2>
                        <div class="rasamala-home-content-cards">
                            <?php foreach ($home_content_cards as $content_card) :
                                $card_title = $content_card['title'] ?? '';
                                $card_url = $content_card['url'] ?? '#';
                                $card_excerpt = $content_card['excerpt'] ?? '';
                                $card_image = $content_card['image_src'] ?? '';
                            ?>
                            <a class="rasamala-home-content-card" href="<?= themeEscape($card_url); ?>" title="<?= themeEscape($card_title); ?>">
                                <span class="rasamala-home-content-thumb" aria-hidden="<?= $card_image ? 'false' : 'true'; ?>">
                                    <?php if ($card_image) : ?>
                                    <img loading="lazy" src="<?= themeEscape($card_image); ?>" alt="" aria-hidden="true">
                                    <?php else : ?>
                                    <span class="rasamala-home-content-thumb-placeholder">
                                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                                    </span>
                                    <?php endif; ?>
                                </span>
                                <span class="rasamala-home-content-body">
                                    <span class="rasamala-home-content-title"><?= themeEscape($card_title); ?></span>
                                    <?php if ($card_excerpt) : ?>
                                    <span class="rasamala-home-content-excerpt"><?= themeEscape($card_excerpt); ?></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </template>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <a class="rasamala-hero-scroll-indicator"
       href="#slims-home"
       data-hero-scroll-indicator
       aria-label="<?= themeEscape(__('Explore more content')); ?>"
       <?= $hero_has_below_content ? '' : 'hidden'; ?>>
        <i class="fas fa-chevron-down" aria-hidden="true"></i>
    </a>
</section>



<div id="slims-home" data-home-layout="<?= $home_tabs_configured ? 'tabs' : 'standard'; ?>">
<?php
$map_url = themeSafeHttpsUrl($sysconf['template']['classic_map_link'] ?? '');
$home_map_visible = ($theme_viewer_preview_enabled || themeShowMap($sysconf)) && $map_url;
$home_social_visible = $theme_viewer_preview_enabled || themeShowSocialMedia($sysconf);
$social_links = [
    'classic_fb_link' => 'fab fa-facebook-square',
    'classic_twitter_link' => 'fab fa-twitter-square',
    'classic_youtube_link' => 'fab fa-youtube',
    'classic_instagram_link' => 'fab fa-instagram',
    'classic_tiktok_link' => 'fab fa-tiktok',
    'classic_whatsapp_link' => 'fab fa-whatsapp',
    'classic_telegram_link' => 'fab fa-telegram-plane',
    'classic_linkedin_link' => 'fab fa-linkedin',
];

$sections = themeHomepageSectionOrder($sysconf);
$home_section_enabled = function ($section_key) use ($sysconf, $theme_viewer_preview_enabled) {
    return $theme_viewer_preview_enabled || themeHomepageSectionEnabled($section_key, $sysconf);
};
if ($theme_viewer_preview_enabled) {
    foreach (['topic', 'news', 'popular', 'new-collection', 'top-reader', 'map'] as $preview_section_key) {
        if (!in_array($preview_section_key, $sections, true)) {
            $sections[] = $preview_section_key;
        }
    }
}
$home_tabs_enabled = $theme_viewer_preview_enabled || $home_tabs_configured;
$home_tab_headings_hidden = $home_tabs_configured && !$theme_viewer_preview_enabled;
$home_tab_keys = ['popular', 'new-collection', 'top-reader', 'map'];
$home_tab_labels = [
    'popular' => __('Popular among our collections'),
    'new-collection' => __('New collections + updated'),
    'top-reader' => __('Top reader of the year'),
    'map' => __('Map & Social Media'),
];
$home_tab_icons = [
    'popular' => 'fas fa-fire',
    'new-collection' => 'fas fa-book',
    'top-reader' => 'fas fa-trophy',
    'map' => 'fas fa-map-marker-alt',
];
$home_tab_candidates = [
    'popular' => $home_section_enabled('popular') && !($is_homepage_only_hero && $hero_inside_content === 'popular'),
    'new-collection' => $home_section_enabled('new-collection') && !($is_homepage_only_hero && $hero_inside_content === 'new_update'),
    'top-reader' => $home_section_enabled('top-reader') && !($is_homepage_only_hero && $hero_inside_content === 'top_reader'),
    'map' => $home_section_enabled('map') && ($home_map_visible || $home_social_visible),
];
$home_tab_visible = array_values(array_filter($home_tab_keys, function ($key) use ($home_tab_candidates) {
    return !empty($home_tab_candidates[$key]);
}));
$home_tabs_enabled = $home_tabs_enabled && count($home_tab_visible) > 0;
$home_tab_active = $home_tab_visible[0] ?? '';
$home_tabs_inserted = false;
$home_tab_class = $home_tabs_enabled ? ' rasamala-home-tab-pane' : '';
$home_tab_attrs = function ($key) use ($home_tabs_enabled, $home_tab_active, $home_tabs_configured) {
    if (!$home_tabs_enabled) {
        return '';
    }
    $is_initial_active = !$home_tabs_configured || $home_tab_active === $key;
    return ' id="rasamala-home-tab-pane-' . themeEscape($key) . '" data-home-tab-pane="' . themeEscape($key) . '" role="tabpanel" aria-labelledby="rasamala-home-tab-' . themeEscape($key) . '" aria-hidden="' . ($is_initial_active ? 'false' : 'true') . '"' . (!$is_initial_active ? ' hidden' : '');
};
$home_section_heading = function ($section_key, $title, $subtitle = '', $extra_class = '') use ($sysconf) {
    $heading_key = 'classic_' . $section_key . '_heading_display';
    $mode = strtolower(trim((string)themeEffectiveTemplateValue($heading_key, 'all', $sysconf)));

    if (in_array($mode, ['all', 'both', 'title_subject', 'title', 'hide'], true)) {
        $show_title = in_array($mode, ['all', 'both', 'title_subject', 'title'], true);
        $show_subtitle = in_array($mode, ['all', 'both'], true) && trim((string)$subtitle) !== '';
    } else {
        $title_key = 'classic_' . $section_key . '_title_show';
        $subtitle_key = 'classic_' . $section_key . '_subtitle_show';
        $show_title = (int)themeEffectiveTemplateValue($title_key, 1, $sysconf) === 1;
        $show_subtitle = (int)themeEffectiveTemplateValue($subtitle_key, 1, $sysconf) === 1 && trim((string)$subtitle) !== '';
    }

    if (!$show_title && !$show_subtitle) {
        return;
    }
    ?>
    <h2 class="mb-4 text-center rasamala-home-section-title <?= themeEscape($extra_class); ?>">
        <?php if ($show_title) : ?>
            <?= themeEscape($title); ?>
        <?php endif; ?>
        <?php if ($show_subtitle) : ?>
            <?php if ($show_title) : ?><br><?php endif; ?>
            <small class="subtitle-section"><?= themeEscape($subtitle); ?></small>
        <?php endif; ?>
    </h2>
    <?php
};

foreach ($sections as $section) {
    if ($home_tabs_enabled && !$home_tabs_inserted && in_array($section, $home_tab_visible, true)) :
        $home_tabs_inserted = true;
        ?>
        <nav class="rasamala-home-section-tabs" aria-label="<?= themeEscape(__('Homepage sections')); ?>" role="tablist" aria-orientation="horizontal"<?= $home_tabs_configured ? '' : ' hidden'; ?>>
            <?php foreach ($home_tab_visible as $tab_key) : ?>
            <button type="button"
                    class="rasamala-home-tab<?= $tab_key === $home_tab_active ? ' is-active' : ''; ?>"
                    id="rasamala-home-tab-<?= themeEscape($tab_key); ?>"
                    role="tab"
                    data-home-tab-target="<?= themeEscape($tab_key); ?>"
                    aria-controls="rasamala-home-tab-pane-<?= themeEscape($tab_key); ?>"
                    aria-selected="<?= $tab_key === $home_tab_active ? 'true' : 'false'; ?>"
                    tabindex="<?= $tab_key === $home_tab_active ? '0' : '-1'; ?>">
                <i class="<?= themeEscape($home_tab_icons[$tab_key] ?? 'fas fa-layer-group'); ?>" aria-hidden="true"></i>
                <span><?= themeEscape($home_tab_labels[$tab_key] ?? $tab_key); ?></span>
            </button>
            <?php endforeach; ?>
        </nav>
        <?php
    endif;

    // Keep the below-search Topics section in the DOM while Theme Viewer is
    // active. The viewer can switch from Fullscreen Hero (Topics inside hero)
    // to Standard Homepage without a reload, so removing this node server-side
    // would make Topics impossible to restore in the live preview.
    if ($section === 'topic'
        && (!($is_homepage_only_hero && $hero_inside_content === 'topics') || $theme_viewer_preview_enabled)
        && $home_section_enabled('topic')
        && $home_topic_items) {
        ?>
        <section id="rasamala-home-topic-section"
                 class="container rasamala-home-section rasamala-home-section-topic"
                 <?= $hero_topics_in_hero ? 'hidden' : ''; ?>>
            <?php $home_section_heading('topic', __('Select the topic you are interested in'), __('Choose a topic to explore the collection faster.'), 'text-thin'); ?>
            <ul class="topic d-flex flex-wrap justify-content-center px-0">
                <?php foreach ($home_topic_items as $home_topic_item) echo themeTopicItemHtml($home_topic_item); ?>
            </ul>
        </section>
        <?php
    }
    elseif ($section === 'news' && !($is_homepage_only_hero && $hero_inside_content === 'news') && $home_section_enabled('news')) {
        ?>
        <section class="container rasamala-home-section rasamala-home-content-cards-section">
            <div class="rasamala-home-content-cards">
                <?php foreach ($home_content_cards as $content_card) :
                    $card_title = $content_card['title'] ?? '';
                    $card_url = $content_card['url'] ?? '#';
                    $card_excerpt = $content_card['excerpt'] ?? '';
                    $card_image = $content_card['image_src'] ?? '';
                ?>
                <a class="rasamala-home-content-card" href="<?= themeEscape($card_url); ?>" title="<?= themeEscape($card_title); ?>">
                    <span class="rasamala-home-content-thumb" aria-hidden="<?= $card_image ? 'false' : 'true'; ?>">
                        <?php if ($card_image) : ?>
                        <img loading="lazy" src="<?= themeEscape($card_image); ?>" alt="" aria-hidden="true">
                        <?php else : ?>
                        <span class="rasamala-home-content-thumb-placeholder">
                            <i class="fas fa-newspaper" aria-hidden="true"></i>
                        </span>
                        <?php endif; ?>
                    </span>
                    <span class="rasamala-home-content-body">
                        <span class="rasamala-home-content-title"><?= themeEscape($card_title); ?></span>
                        <?php if ($card_excerpt) : ?>
                        <span class="rasamala-home-content-excerpt"><?= themeEscape($card_excerpt); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }
    elseif ($section === 'popular' && !($is_homepage_only_hero && $hero_inside_content === 'popular') && $home_section_enabled('popular')) {
        $heading_mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_popular_collection_heading_display', 'all', $sysconf)));
        $show_subject = in_array($heading_mode, ['all', 'title_subject'], true);
        $section_class = $home_tabs_enabled ? 'mt-3' : (($heading_mode === 'hide' && !$show_subject) ? 'mt-3' : 'mt-5');
        ?>
        <section class="<?= themeEscape($section_class); ?> container rasamala-home-section rasamala-home-section-popular<?= $home_tab_class; ?>"<?= $home_tab_attrs('popular'); ?>>
            <?php if (!$home_tab_headings_hidden) $home_section_heading('popular_collection', __('Popular among our collections'), __('Our library\'s line of collection that have been favoured by our users were shown here. Look for them. Borrow them. Hope you also like them')); ?>
            <?php if ($show_subject && !$home_tab_headings_hidden) : ?>
            <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
            <?php endif; ?>
            <slims-collection url="index.php?p=api/biblio/popular" limit="<?= themeSafeInt($sysconf['template']['classic_popular_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
        </section>
        <?php
    }
    elseif (in_array($section, ['new-collection', 'new_collection', 'new_update', 'latest', 'latest_content'], true) && !($is_homepage_only_hero && $hero_inside_content === 'new_update') && $home_section_enabled('new-collection')) {
        $heading_mode = strtolower(trim((string)themeEffectiveTemplateValue('classic_new_collection_heading_display', 'all', $sysconf)));
        $show_subject = in_array($heading_mode, ['all', 'title_subject'], true);
        $section_class = $home_tabs_enabled ? 'mt-3' : (($heading_mode === 'hide' && !$show_subject) ? 'mt-3' : 'mt-5');
        ?>
        <section class="<?= themeEscape($section_class); ?> container rasamala-home-section rasamala-home-section-new-collection<?= $home_tab_class; ?>"<?= $home_tab_attrs('new-collection'); ?>>
            <?php if (!$home_tab_headings_hidden) $home_section_heading('new_collection', __('New collections + updated'), __('These are new collections list. Hope you like them. Maybe not all of them are new. But in term of time, we make sure that these are fresh from our processing oven')); ?>
            <?php if ($show_subject && !$home_tab_headings_hidden) : ?>
            <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
            <?php endif; ?>
            <slims-collection url="index.php?p=api/biblio/latest" limit="<?= themeSafeInt($sysconf['template']['classic_new_collection_item'] ?? 6, 6, 1, 100); ?>"></slims-collection>
        </section>
        <?php
    }
    elseif ($section === 'top-reader' && !($is_homepage_only_hero && $hero_inside_content === 'top_reader') && $home_section_enabled('top-reader')) {
        ?>
        <section class="<?= $home_tabs_enabled ? 'mt-3' : 'mt-5'; ?> rasamala-home-section rasamala-home-section-top-reader<?= $home_tab_class; ?>"<?= $home_tab_attrs('top-reader'); ?>>
            <div class="container <?= $home_tabs_enabled ? 'py-3' : 'py-5'; ?>">
                <?php if (!$home_tab_headings_hidden) $home_section_heading('top_reader', __('Top reader of the year'), __('Our best users, readers, so far. Continue to read if you want your name being mentioned here')); ?>
                <slims-group-member url="index.php?p=api/member/top" limit="<?= themeSafeInt($sysconf['template']['classic_top_reader_item'] ?? 5, 5, 1, 100); ?>"></slims-group-member>
            </div>
        </section>
        <?php
    }
    elseif ($section === 'map' && $home_section_enabled('map') && ($home_map_visible || $home_social_visible)) {
        ?>
        <section class="<?= $home_tabs_enabled ? 'my-3' : 'my-5'; ?> container rasamala-home-section rasamala-home-section-map<?= $home_tab_class; ?>"<?= $home_tab_attrs('map'); ?>>
            <div class="row align-items-center">
                <?php if ($home_map_visible) : ?>
                <div class="col-md-6">
                    <iframe class="embed-responsive border-0"
                            src="<?= themeEscape($map_url); ?>"
                            title="<?= themeEscape(sprintf(__('Map location for %s'), $sysconf['library_name'] ?? __('Library'))); ?>"
                            height="<?= themeEscape(themeSafeInt($sysconf['template']['classic_map_height'] ?? 420, 420, 100, 2000)) ?>"
                            frameborder="0" loading="lazy" allowfullscreen></iframe>
                </div>
                <?php endif; ?>
                <div class="<?= $home_map_visible ? 'col-md-6' : 'col-md-12' ?> pt-5 pt-md-0 home-map-contact">
                    <h2 class="home-library-name"><?= themeEscape($sysconf['library_name']); ?></h2>
                    <div class="home-map-description"><?= themeSanitizeHtml($sysconf['template']['classic_map_desc'] ?? ''); ?></div>
                    <?php if ($home_social_visible) : ?>
                    <p class="d-flex flex-row flex-wrap pt-2 home-map-social-links">
                        <?php
                        $social_labels = [
                            'classic_fb_link' => 'Facebook',
                            'classic_twitter_link' => 'Twitter',
                            'classic_youtube_link' => 'YouTube',
                            'classic_instagram_link' => 'Instagram',
                            'classic_tiktok_link' => 'TikTok',
                            'classic_whatsapp_link' => 'WhatsApp',
                            'classic_telegram_link' => 'Telegram',
                            'classic_linkedin_link' => 'LinkedIn',
                        ];
                        foreach ($social_links as $setting_key => $icon_class) :
                            $social_url = themeSafeHttpsUrl($sysconf['template'][$setting_key] ?? '');
                            if (!$social_url) continue;
                            $social_label = $social_labels[$setting_key] ?? __('Social media');
                        ?>
                        <a target="_blank" rel="noopener noreferrer" href="<?= themeEscape($social_url) ?>" class="btn btn-primary me-2" name="button"
                           aria-label="<?= themeEscape(sprintf(__('Open %s social media'), $social_label)); ?>"
                           title="<?= themeEscape($social_label); ?>">
                            <i class="<?= themeEscape($icon_class) ?> text-white" aria-hidden="true"></i>
                        </a>
                        <?php endforeach; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
if ($home_tabs_enabled) :
?>
<script nonce="<?= themeCspNonce(); ?>">
(function () {
    function initHomeTabs() {
        var root = document.getElementById('slims-home');
        if (!root || root.getAttribute('data-home-tabs-ready') === '1') return;

        // Delegate from the Vue mount element so listeners survive Vue re-renders.
        root.setAttribute('data-home-tabs-ready', '1');

        function getTabs() {
            return Array.prototype.slice.call(root.querySelectorAll('[data-home-tab-target]'))
                .filter(function (tab) { return !tab.hidden; });
        }

        function getPanes() {
            return Array.prototype.slice.call(root.querySelectorAll('[data-home-tab-pane]'));
        }

        function activateTab(target, shouldFocus) {
            var tabs = getTabs();
            var panes = getPanes();
            var selected = null;
            for (var tabIndex = 0; tabIndex < tabs.length; tabIndex++) {
                if (tabs[tabIndex].getAttribute('data-home-tab-target') === target) {
                    selected = tabs[tabIndex];
                    break;
                }
            }
            if (!selected) return;

            tabs.forEach(function (tab) {
                var isActive = tab === selected;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                tab.setAttribute('tabindex', isActive ? '0' : '-1');
            });
            panes.forEach(function (pane) {
                var isActive = pane.getAttribute('data-home-tab-pane') === target;
                pane.hidden = !isActive;
                pane.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });
            if (shouldFocus) selected.focus();
        }

        root.addEventListener('click', function (event) {
            var tab = event.target && event.target.closest ? event.target.closest('[data-home-tab-target]') : null;
            if (!tab || !root.contains(tab)) return;
            event.preventDefault();
            activateTab(tab.getAttribute('data-home-tab-target'), false);
        });

        root.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            var tab = event.target && event.target.closest ? event.target.closest('[data-home-tab-target]') : null;
            if (!tab || !root.contains(tab)) return;
            var tabs = getTabs();
            var index = tabs.indexOf(tab);
            if (index < 0 || tabs.length < 2) return;
            event.preventDefault();
            var nextIndex = event.key === 'ArrowRight' ? index + 1 : index - 1;
            if (nextIndex < 0) nextIndex = tabs.length - 1;
            if (nextIndex >= tabs.length) nextIndex = 0;
            activateTab(tabs[nextIndex].getAttribute('data-home-tab-target'), true);
        });

        if (root.getAttribute('data-home-layout') === 'tabs') {
            var initial = root.querySelector('[data-home-tab-target][aria-selected="true"]');
            if (initial) activateTab(initial.getAttribute('data-home-tab-target'), false);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHomeTabs);
    } else {
        initHomeTabs();
    }
})();
</script>
<?php endif; ?>
</div>
