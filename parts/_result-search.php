<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _result-search.php

if (!function_exists('rasamalaSearchFilterHtml')) {
    function rasamalaSearchFilterHtml($html)
    {
        $html = (string)$html;
        $html = preg_replace('/class="list-group\s+list-group-flush"/i', 'class="list-group list-group-flush rasamala-filter-list"', $html);
        $html = preg_replace('/class="([^"]*)\blist-group-item\b([^"]*)"/i', 'class="$1list-group-item rasamala-filter-facet$2"', $html);
        $html = preg_replace('/\s*\bborder-top-0\b/i', '', $html);
        $html = preg_replace('/\s*\bborder-left\b|\s*\bborder-right\b/i', '', $html);

        return $html;
    }
}
?>

<div class="result-search rasamala-subpage-wrapper">
    <section id="section1" class="container-fluid">
        <header class="c-header rasamala-header-dark">
          <?php
          // ----------------------------------------------------------------------
          // include navbar part
          // ----------------------------------------------------------------------
          include '_navbar.php'; ?>
        </header>
      <?php
      // ------------------------------------------------------------------------
      // include search form part
      // ------------------------------------------------------------------------
      include '_search-form.php'; ?>
    </section>

    <section class="container mt-5">
        <?= themeBreadcrumbsHtml(__('Search')) ?>
        <div class="row">
            <div class="col-12">
                <?php
                $view_csrf = $opac->getCsrf();
                $view_options = [
                    'simple' => [
                        'label' => __('Simple'),
                        'title' => __('Simple View'),
                        'icon' => 'fas fa-align-left',
                    ],
                    'list' => [
                        'label' => __('List'),
                        'title' => __('List View'),
                        'icon' => 'fas fa-list',
                    ],
                    'grid' => [
                        'label' => __('Grid'),
                        'title' => __('Grid View'),
                        'icon' => 'fas fa-th-large',
                    ],
                ];
                $default_layout = $sysconf['template']['classic_search_result_layout'] ?? 'simple';
                $default_layout = is_scalar($default_layout) ? (string)$default_layout : 'simple';
                if (!isset($view_options[$default_layout])) {
                    $default_layout = 'simple';
                }
                $current_view = $_SESSION['LIST_VIEW'] ?? $default_layout;
                $current_view = is_scalar($current_view) ? (string)$current_view : $default_layout;
                if (!isset($view_options[$current_view])) {
                    $current_view = $default_layout;
                }
                $view_action = $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge(
                    ['csrf_token' => $view_csrf],
                    array_filter($_GET, fn($key) => $key !== 'csrf_token', ARRAY_FILTER_USE_KEY)
                ));

                if (empty($sort_select)) {
                    $sorts = [
                        ['most-relevant', __('Most relevant')],
                        ['recently-added', __('Recently Added')],
                        ['last-update', __('Last Update')],
                        ['most-loaned', __('Most Loaned')],
                        ['publish-year-newest', __('Publication Year [newest]')],
                        ['publish-year-oldest', __('Publication Year [oldest]')],
                        ['title-asc', __('Title Ascending')],
                        ['title-desc', __('Title Descending')],
                    ];
                    $current_sort = '';
                    if (!empty($_GET['sortby'])) {
                        $current_sort = (string)$_GET['sortby'];
                    } elseif (!empty($_GET['filter'])) {
                        $filterArr = json_decode((string)$_GET['filter'], true);
                        $current_sort = $filterArr['sort'] ?? '';
                    }
                    $sort_select_html = '';
                    foreach ($sorts as $sort) {
                        $selected = ($sort[0] === $current_sort) ? 'selected' : '';
                        $sort_select_html .= '<option value="' . themeEscape($sort[0]) . '" ' . $selected . '>' . themeEscape($sort[1]) . '</option>';
                    }
                    $sort_select = $sort_select_html;
                }

                // Clean up underscores and dashes from sort options
                $cleaned_sort_select = preg_replace_callback('/(<option[^>]*>)(.*?)(<\/option>)/i', function($matches) {
                    $label = str_replace(array('_', '-'), ' ', $matches[2]);
                    return $matches[1] . ucwords($label) . $matches[3];
                }, $sort_select ?? '');

                $raw_keywords = trim((string)($keywords ?? ''));
                $keywords_text = (strlen($raw_keywords) > 40) ? substr($raw_keywords, 0, 40) . '...' : $raw_keywords;
                $num_rows_val = themeSafeInt($engine->getNumRows());
                $has_active_keyword = (!empty($keywords_text) && $keywords_text !== '*');
                ?>

                <!-- Unified Sticky Search Info & Action Toolbar (Always Sticky on Scroll) -->
                <div class="rasamala-sticky-action-wrapper mb-4">
                    <div class="search-result-toolbar-card rasamala-search-action-bar rasamala-sticky-toolbar">
                        <div class="search-result-toolbar-grid">
                            <!-- Left: Found Count & Optional Keyword Badge -->
                            <div class="search-result-toolbar-summary">
                                <div class="search-found-info search-result-summary">
                                    <span><?= __('Found') ?></span>
                                    <span class="search-result-count-number"><?= number_format($num_rows_val, 0, ',', '.') ?></span>
                                    <?php if ($has_active_keyword) : ?>
                                        <span><?= __('for') ?>:</span>
                                        <span class="search-keyword-badge">
                                            <i class="fas fa-quote-left" aria-hidden="true"></i><?= themeEscape($keywords_text) ?><i class="fas fa-quote-right" aria-hidden="true"></i>
                                        </span>
                                    <?php else : ?>
                                        <span><?= __('titles') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Right: Action Controls (Filter, Sort, View Mode) -->
                            <div class="search-result-toolbar-actions">
                                <button type="button" class="btn search-toolbar-action" data-bs-toggle="modal" data-bs-target="#mobileFilterModal" data-toggle="modal" data-target="#mobileFilterModal" id="btn-open-filter-modal" aria-label="<?= themeEscape(__('Open filter options')) ?>">
                                    <i class="fas fa-filter search-toolbar-action-icon" aria-hidden="true"></i>
                                    <span class="search-toolbar-action-label"><?= __('Filter') ?></span>
                                    <span class="search-toolbar-badge d-none" id="active-filter-count">0</span>
                                </button>

                                <button type="button" class="btn search-toolbar-action" data-bs-toggle="modal" data-bs-target="#mobileSortModal" data-toggle="modal" data-target="#mobileSortModal" id="btn-open-sort-modal" aria-label="<?= themeEscape(__('Open sort options')) ?>">
                                    <i class="fas fa-sort search-toolbar-action-icon" aria-hidden="true"></i>
                                    <span id="current-sort-label" class="search-toolbar-action-label"><?= __('Sort by') ?></span>
                                </button>

                                <button type="button" class="btn search-toolbar-action" data-bs-toggle="modal" data-bs-target="#mobileViewModal" data-toggle="modal" data-target="#mobileViewModal" id="btn-open-view-modal" aria-label="<?= themeEscape(__('Change result view')) ?>">
                                    <i class="<?= themeEscape($view_options[$current_view]['icon']) ?> search-toolbar-action-icon" aria-hidden="true"></i>
                                    <span class="search-toolbar-action-label"><?= themeEscape($view_options[$current_view]['label']) ?></span>
                                </button>
                            </div>
                        </div>
                        <!-- Hidden SLiMS Sort Select for JS binding -->
                        <select class="d-none" id="search-order"><?= $cleaned_sort_select ?></select>
                    </div>
                </div>

                <div class="wrapper">
                    <?php
                    if (ENVIRONMENT == 'development' && !empty($engine->getError())) echo '<div class="alert alert-danger mt-2 text-center">' . themeEscape($engine->getError()) . '</div>';
                    // catch empty list
                    if ($num_rows_val === 0) {
                        echo '<div class="search-empty-state text-center py-5 px-3 border-top mt-3 rounded-4 bg-light shadow-sm">
                                <div class="empty-state-img mb-3">
                                  <img class="search-empty-state-image" src="'.assets('images/empty.svg').'" alt="'.themeEscape(__('No result illustration')).'" />
                                </div>
                                <h4 class="fw-bold mb-2 text-dark">'.__('No Result Found').'</h4>
                                <p class="text-muted small mb-4">'.__('We could not find any records matching your search query. Please try checking your spelling or using broader keywords.').'</p>
                                <div class="d-inline-flex flex-wrap justify-content-center gap-2">
                                  <a href="index.php?search=search" class="btn btn-primary btn-sm rounded-pill px-4"><i class="fas fa-undo me-1" aria-hidden="true"></i> '.__('Reset Search').'</a>
                                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#adv-modal"><i class="fas fa-sliders-h me-1" aria-hidden="true"></i> '.__('Advanced Search').'</button>
                                </div>
                              </div>';
                    } else {
                        echo $main_content;
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Unified Filter Pop-up Modal (Slideout Drawer) -->
<div class="modal fade" id="mobileFilterModal" tabindex="-1" role="dialog" aria-labelledby="mobileFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout" role="document">
        <div class="modal-content border-0 h-100 search-control-modal-content">
            <div class="modal-header search-control-modal-header">
                <div>
                    <h5 class="modal-title search-control-modal-title" id="mobileFilterModalLabel"><i class="fas fa-filter search-control-modal-title-icon" aria-hidden="true"></i><?= __('Filter by') ?></h5>
                    <small class="search-control-modal-subtitle"><?= __('Refine search results by criteria') ?></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body overflow-auto p-3 mobile-filter-modal-body">
                <div id="mobile-filter-container">
                    <?= rasamalaSearchFilterHtml($engine->getFilter($opac, true)) ?>
                </div>
            </div>
            <div class="modal-footer search-control-modal-footer">
                <div class="row w-100 g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-secondary w-100 py-2.5 fw-bold rounded-pill btn-modal-action" id="reset-mobile-filter">
                            <i class="fas fa-undo me-1" aria-hidden="true"></i><?= __('Reset') ?>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm btn-modal-action" id="apply-mobile-filter">
                            <i class="fas fa-check me-1" aria-hidden="true"></i><?= __('Apply Filter') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unified Sort Pop-up Modal -->
<div class="modal fade" id="mobileSortModal" tabindex="-1" role="dialog" aria-labelledby="mobileSortModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-3 search-control-modal-content">
            <div class="modal-header search-control-modal-header">
                <div>
                    <h5 class="modal-title search-control-modal-title" id="mobileSortModalLabel"><i class="fas fa-sort search-control-modal-title-icon" aria-hidden="true"></i><?= __('Sort by') ?></h5>
                    <small class="search-control-modal-subtitle"><?= __('Choose sorting order for search results') ?></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body search-control-modal-body">
                <div class="list-group list-group-flush search-sort-modal-options" id="mobile-sort-options"></div>
            </div>
        </div>
    </div>
</div>

<!-- Unified View Mode Pop-up Modal (Simple, List, Grid) -->
<div class="modal fade" id="mobileViewModal" tabindex="-1" role="dialog" aria-labelledby="mobileViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-3 search-control-modal-content">
            <div class="modal-header search-control-modal-header">
                <div>
                    <h5 class="modal-title search-control-modal-title" id="mobileViewModalLabel"><i class="fas fa-th-large search-control-modal-title-icon" aria-hidden="true"></i><?= __('View Mode') ?></h5>
                    <small class="search-control-modal-subtitle"><?= __('Choose display layout style') ?></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body search-control-modal-body">
                <form method="POST" action="<?= themeEscape($view_action) ?>" class="m-0" id="search-view-mode-form">
                    <input type="hidden" name="csrf_token" value="<?= themeEscape($view_csrf) ?>"/>
                    <input type="hidden" name="view" id="search-view-input-value" value=""/>
                    <div class="list-group list-group-flush search-view-modal-options">
                        <?php 
                        $view_descriptions = [
                            'simple' => __('Compact list view without cover thumbnails'),
                            'list' => __('Detailed list view with book cover thumbnails'),
                            'grid' => __('Modern card grid view layout'),
                        ];
                        foreach ($view_options as $view_key => $view_option) : 
                            $is_active = ($current_view === $view_key);
                            $view_desc = $view_descriptions[$view_key] ?? '';
                            $url_params = array_merge($_GET, ['view' => $view_key, 'csrf_token' => $view_csrf]);
                            $direct_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($url_params);
                        ?>
                        <a href="<?= themeEscape($direct_url) ?>" data-view-value="<?= themeEscape($view_key) ?>" class="list-group-item list-group-item-action search-control-option search-view-option-item <?= $is_active ? 'active' : '' ?>" role="button">
                            <div class="search-control-option-main">
                                <div class="search-control-option-icon view-option-icon-box">
                                    <i class="<?= themeEscape($view_option['icon']) ?>" aria-hidden="true"></i>
                                </div>
                                <div class="search-control-option-text">
                                    <div class="search-control-option-title"><?= themeEscape($view_option['label']) ?></div>
                                    <?php if ($view_desc) : ?>
                                    <small class="search-control-option-desc"><?= themeEscape($view_desc) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($is_active) : ?>
                            <i class="fas fa-check-circle search-control-option-check" aria-hidden="true"></i>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
