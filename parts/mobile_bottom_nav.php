<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date:   2026-07-16T10:08:00+07:00
# @Filename: mobile_bottom_nav.php
# @Last modified by:   Ade Ismail Siregar (adeismailbox@gmail.com)
# @Last modified time: 2026-07-22T12:54:00+07:00

// Bottom Navigation Bar for Mobile
$basket_count = (isset($_SESSION['m_mark_biblio']) && is_array($_SESSION['m_mark_biblio'])) ? count($_SESSION['m_mark_biblio']) : 0;
$current_p = $_GET['p'] ?? 'home';

// Icon map for menus
$icon_map = [
    'home' => 'fas fa-home',
    'libinfo' => 'fas fa-info-circle',
    'news' => 'fas fa-newspaper',
    'help' => 'fas fa-question-circle',
    'librarian' => 'fas fa-users',
    'login' => 'fas fa-university',
    'member' => 'fas fa-user',
    'basket' => 'fas fa-shopping-basket',
    'language' => 'fas fa-language',
];

$mobile_language_links = [];
$current_mobile_language = [
    'code' => '',
    'name' => __('Language'),
];
if (!empty($available_languages)) {
    $select_lang = $_COOKIE['select_lang'] ?? $sysconf['default_lang'];
    foreach ($available_languages as $lang_index) {
        $lang_code = $lang_index[0] ?? '';
        if (function_exists('themeLanguageIsVisible') && !themeLanguageIsVisible($lang_code, $sysconf)) {
            continue;
        }
        $lang_name = $lang_index[1] ?? $lang_code;
        $code_arr = explode('_', (string)$lang_code);
        $code_flag = strtolower($code_arr[1] ?? $code_arr[0] ?? '');
        $code_flag = preg_replace('/[^a-z]/', '', $code_flag);
        $is_active_lang = $lang_code === $select_lang;

        if ($current_mobile_language['code'] === '') {
            $current_mobile_language = [
                'code' => $code_flag,
                'name' => $lang_name,
            ];
        }

        if ($is_active_lang) {
            $current_mobile_language = [
                'code' => $code_flag,
                'name' => $lang_name,
            ];
        }

        $mobile_language_links[] = [
            'code' => $lang_code,
            'flag' => $code_flag,
            'name' => $lang_name,
            'active' => $is_active_lang,
        ];
    }
}

// Build bottom navigation items based on main_menus configured for top navbar
$bottom_items = [];
if (isset($main_menus) && is_array($main_menus)) {
    $bottom_items = $main_menus;
}

// Add Member Area to bottom items if it's enabled and not already in main menus
$has_member_in_main = false;
foreach ($bottom_items as $item) {
    if ($item['key'] === 'member') {
        $has_member_in_main = true;
        break;
    }
}
if (!$has_member_in_main && ($sysconf['template']['classic_member_area'] ?? 1) == 1) {
    $bottom_items[] = [
        'key' => 'member',
        'text' => __('Member Area'),
        'url' => 'index.php?p=member',
        'icon' => 'fas fa-user'
    ];
}

if (!empty($mobile_language_links)) {
    $bottom_items[] = [
        'key' => 'language',
        'text' => __('Language'),
        'url' => '#',
    ];
}

// Distribute items to primary bottom nav bar and bottom sheet menu
$primary_bottom_nav = [];
$sheet_bottom_nav = [];
$total_nav_count = count($bottom_items);
$is_logged_in_member = isset($_SESSION['mid']) && $_SESSION['mid'];

if ($current_p === 'member' && $is_logged_in_member) {
    $primary_bottom_nav = [
        [
            'key' => 'home_back',
            'text' => __('Home'),
            'url' => 'index.php',
            'icon' => 'fas fa-home'
        ],
        [
            'key' => 'member_loans',
            'text' => __('Loans'),
            'url' => 'index.php?p=member&sec=current_loan',
            'icon' => 'fas fa-exchange-alt'
        ],
        [
            'key' => 'member_basket',
            'text' => __('Basket'),
            'url' => 'index.php?p=member&sec=title_basket',
            'icon' => 'fas fa-shopping-basket',
            'badge' => $basket_count
        ],
        [
            'key' => 'member_card',
            'text' => __('My Card'),
            'url' => 'index.php?p=member&sec=my_card',
            'icon' => 'fas fa-id-card'
        ],
        [
            'key' => 'more',
            'text' => __('More'),
            'url' => '#',
            'icon' => 'fas fa-ellipsis-h'
        ]
    ];
    $sheet_bottom_nav = [
        [
            'key' => 'member_account',
            'text' => __('Account'),
            'url' => 'index.php?p=member&sec=my_account',
            'icon' => 'fas fa-user-circle'
        ],
        [
            'key' => 'member_bookmarks',
            'text' => __('Bookmarks'),
            'url' => 'index.php?p=member&sec=bookmark',
            'icon' => 'fas fa-bookmark'
        ],
        [
            'key' => 'member_history',
            'text' => __('History'),
            'url' => 'index.php?p=member&sec=loan_history',
            'icon' => 'fas fa-history'
        ],
        [
            'key' => 'member_logout',
            'text' => __('Logout'),
            'url' => 'index.php?p=member&logout=1',
            'icon' => 'fas fa-sign-out-alt'
        ]
    ];
} else {
    // Primary bottom keys:
    // If member is logged in: 1. home, 2. news, 3. basket (Tengah), 4. member, 5. more
    // If logged out: 1. home, 2. news, 3. member, 4. help, 5. more
    $primary_keys = $is_logged_in_member ? ['home', 'news', 'basket', 'member'] : ['home', 'news', 'member', 'help'];

    foreach ($primary_keys as $pkey) {
        if ($pkey === 'basket') {
            if ($is_logged_in_member) {
                $primary_bottom_nav[] = [
                    'key' => 'basket',
                    'text' => __('Basket'),
                    'url' => 'index.php?p=member&sec=title_basket',
                    'icon' => 'fas fa-shopping-basket',
                    'badge' => $basket_count
                ];
            }
        } else {
            $found = false;
            foreach ($bottom_items as $item) {
                if ($item['key'] === $pkey) {
                    $primary_bottom_nav[] = $item;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                if ($pkey === 'home') {
                    $primary_bottom_nav[] = ['key' => 'home', 'text' => __('Home'), 'url' => 'index.php', 'icon' => 'fas fa-home'];
                } elseif ($pkey === 'news') {
                    $primary_bottom_nav[] = ['key' => 'news', 'text' => __('News'), 'url' => 'index.php?p=news', 'icon' => 'fas fa-newspaper'];
                } elseif ($pkey === 'member') {
                    $primary_bottom_nav[] = ['key' => 'member', 'text' => __('Member Area'), 'url' => 'index.php?p=member', 'icon' => 'fas fa-user'];
                } elseif ($pkey === 'help') {
                    $primary_bottom_nav[] = ['key' => 'help', 'text' => __('Help'), 'url' => 'index.php?p=help', 'icon' => 'fas fa-question-circle'];
                }
            }
        }
    }

    // Append More button
    $primary_bottom_nav[] = [
        'key' => 'more',
        'text' => __('More'),
        'url' => '#',
        'icon' => 'fas fa-ellipsis-h'
    ];

    // Put remaining menu items in the sheet bottom nav
    $primary_keys_set = array_flip($primary_keys);
    foreach ($bottom_items as $item) {
        if (!isset($primary_keys_set[$item['key']])) {
            if ($item['key'] === 'basket' && !$is_logged_in_member) {
                continue;
            }
            $sheet_bottom_nav[] = $item;
        }
    }
}
?>

<?php
$mobile_bottom_nav_enabled = (int)themeEffectiveTemplateValue('classic_mobile_bottom_nav_show', 1, $sysconf) === 1;
$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
?>
<?php if ($mobile_bottom_nav_enabled || $theme_viewer_preview_enabled) : ?>
<div class="mobile-bottom-nav d-lg-none"<?= $mobile_bottom_nav_enabled ? '' : ' hidden'; ?>>
    <?php
    foreach ($primary_bottom_nav as $item) {
        $key = $item['key'];
        $url = themeEscape(themeSafeHref($item['url'] ?? '#'));
        $text = themeEscape(__($item['text'] ?? ''));
        $icon_raw = $item['icon'] ?? null;
        $icon_val = (is_string($icon_raw) && $icon_raw !== '') ? $icon_raw : themeNavbarMenuIconClass($item, $icon_map[$key] ?? 'fas fa-link');
        $icon = themeEscape($icon_val);

        // Active state detection
        $active_class = '';
        $current_sec = $_GET['sec'] ?? 'current_loan';
        if ($key === 'more' || $key === 'language' || $key === 'home_back') {
            $active_class = '';
        } elseif ($key === 'member_loans') {
            $active_class = ($current_p === 'member' && ($current_sec === 'current_loan' || $current_sec === '')) ? 'active' : '';
        } elseif ($key === 'member_card') {
            $active_class = ($current_p === 'member' && $current_sec === 'my_card') ? 'active' : '';
        } elseif ($key === 'member_basket') {
            $active_class = ($current_p === 'member' && $current_sec === 'title_basket') ? 'active' : '';
        } elseif ($key === 'home') {
            $active_class = ($current_p === 'home' && !isset($_GET['search'])) ? 'active' : '';
        } elseif ($key === 'basket') {
            $active_class = ($current_p === 'member' && ($_GET['sec'] ?? '') === 'title_basket') ? 'active' : '';
        } else {
            $active_class = ($current_p === $key) ? 'active' : '';
        }

        $item_id = $key === 'more' ? 'id="mobile-more-btn"' : '';
        $trigger_attr = ($key === 'more' || $key === 'language') ? 'data-mobile-more-trigger="1"' : '';
        $badge = themeSafeInt($item['badge'] ?? 0);
        ?>
        <a href="<?= $url ?>" <?= $item_id ?> <?= $trigger_attr ?> class="nav-item <?= themeEscape($active_class) ?>">
            <div class="position-relative">
                <?php if ($key === 'language' && !empty($current_mobile_language['code'])): ?>
                <span class="flag-icon flag-icon-<?= themeEscape($current_mobile_language['code']) ?> flag-icon-rounded mobile-bottom-language-flag" aria-hidden="true"></span>
                <?php else: ?>
                <i class="<?= $icon ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php if ($key === 'basket' || $key === 'member_basket'): ?>
                    <span class="badge text-bg-danger position-absolute basket-badge-mobile count-basket" id="count-basket-mobile"><?= themeEscape($badge) ?></span>
                <?php elseif ($badge > 0): ?>
                    <span class="badge text-bg-danger position-absolute basket-badge-mobile count-basket"><?= themeEscape($badge) ?></span>
                <?php endif; ?>
            </div>
            <span><?= $text ?></span>
        </a>
        <?php
    }
    ?>
</div>
<?php endif; ?>

<?php if (!empty($sheet_bottom_nav) || !empty($mobile_language_links)): ?>
<!-- Mobile More Menu Overlay Bottom Sheet -->
<div id="mobile-more-menu-overlay" class="mobile-more-menu-overlay d-lg-none">
    <div class="mobile-more-menu-sheet">
        <div class="mobile-more-menu-header">
            <span class="mobile-more-menu-title"><?= themeEscape(__('More Menu')) ?></span>
            <button id="close-mobile-more-menu" class="mobile-more-menu-close" aria-label="<?= themeEscape(__('Close')) ?>">&times;</button>
        </div>
        <div class="mobile-more-menu-body">
            <?php
            foreach ($sheet_bottom_nav as $item) {
                $key = $item['key'];
                if ($key === 'language') {
                    continue;
                }
                $url = themeEscape(themeSafeHref($item['url'] ?? '#'));
                $text = themeEscape(__($item['text'] ?? ''));
                $icon_raw = $item['icon'] ?? null;
                $icon_val = (is_string($icon_raw) && $icon_raw !== '') ? $icon_raw : themeNavbarMenuIconClass($item, $icon_map[$key] ?? 'fas fa-link');
                $icon = themeEscape($icon_val);

                $active_class = '';
                $current_sec = $_GET['sec'] ?? '';
                if ($key === 'member_account') {
                    $active_class = ($current_p === 'member' && $current_sec === 'my_account') ? 'text-primary' : '';
                } elseif ($key === 'member_bookmarks') {
                    $active_class = ($current_p === 'member' && $current_sec === 'bookmark') ? 'text-primary' : '';
                } elseif ($key === 'member_history') {
                    $active_class = ($current_p === 'member' && $current_sec === 'loan_history') ? 'text-primary' : '';
                } elseif ($key === 'member_logout') {
                    $active_class = '';
                } elseif ($key === 'basket') {
                    $active_class = ($current_p === 'member' && ($_GET['sec'] ?? '') === 'title_basket') ? 'text-primary' : '';
                } else {
                    $active_class = ($current_p === $key) ? 'text-primary' : '';
                }

                ?>
                <a href="<?= $url ?>" class="mobile-more-item <?= themeEscape($active_class) ?>">
                    <i class="<?= $icon ?>" aria-hidden="true"></i> <?= $text ?>
                </a>
                <?php
            }
            ?>
            <?php if (!empty($mobile_language_links)): ?>
            <div class="mobile-language-section">
                <div class="mobile-language-title">
                    <i class="fas fa-language" aria-hidden="true"></i>
                    <span><?= themeEscape(__('Select Language')); ?></span>
                </div>
                <div class="mobile-language-select-wrap">
                    <?php if (!empty($current_mobile_language['code'])): ?>
                    <span class="flag-icon flag-icon-<?= themeEscape($current_mobile_language['code']); ?> flag-icon-rounded mobile-language-current-flag" aria-hidden="true"></span>
                    <?php endif; ?>
                    <select class="form-control mobile-language-select" aria-label="<?= themeEscape(__('Select Language')); ?>">
                        <?php foreach ($mobile_language_links as $mobile_language): ?>
                        <option value="<?= themeEscape($mobile_language['code']); ?>" <?= $mobile_language['active'] ? 'selected' : '' ?>>
                            <?= themeEscape($mobile_language['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
