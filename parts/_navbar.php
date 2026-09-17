<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _navbar.php

if (!isset($is_login)) {
    $is_login = class_exists('utility') && method_exists('utility', 'isMemberLogin')
        ? (bool)utility::isMemberLogin()
        : (isset($_SESSION['m_login']) && (bool)$_SESSION['m_login']);
}

if (!isset($member_image_path)) {
    $member_image_name = $_SESSION['m_image'] ?? 'person.png';
    $member_image_path = function_exists('getImagePath') && isset($sysconf)
        ? getImagePath($sysconf, $member_image_name, 'persons')
        : 'images/persons/' . $member_image_name;
}

$is_homepage = themeIsHomepage();
$is_hero_only = $is_homepage && themeHomepageOnlyHero($sysconf);
$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
$show_color_mode_toggle = function_exists('themeColorModeToggleVisible')
    ? (themeColorModeToggleVisible($sysconf) || $theme_viewer_preview_enabled)
    : $theme_viewer_preview_enabled;
$show_member_area = ((int)($sysconf['template']['classic_member_area'] ?? 1) === 1) || $theme_viewer_preview_enabled;
$member_area_preview_hidden = $theme_viewer_preview_enabled && (int)($sysconf['template']['classic_member_area'] ?? 1) !== 1;

$menu_raw = $sysconf['template']['classic_navbar_menu'] ?? themeNavbarMenuDefault();
$main_menus = themeParseNavbarMenus($menu_raw);

$lib_name_position = strtolower(trim((string)themeEffectiveTemplateValue('classic_library_name_position', 'navbar', $sysconf)));
if (!in_array($lib_name_position, ['navbar', 'hero'], true)) {
    $lib_name_position = 'navbar';
}
$lib_name_in_hero = ($lib_name_position === 'hero');

?>

<nav class="navbar navbar-expand-lg navbar-light bg-transparent rasamala-navbar-main<?= $lib_name_in_hero ? ' rasamala-navbar-centered' : '' ?>">
    <a class="navbar-brand d-inline-flex align-items-center flex-nowrap<?= $lib_name_in_hero ? ' rasamala-navbar-brand-home-link' : '' ?>" href="index.php">
        <?php echo themeLibraryLogoHtml($sysconf, $imagesDisk ?? null, 'navbar-brand-img'); ?>
        <div class="d-inline-flex flex-column ms-1 navbar-brand-text">
            <span class="navbar-lib-name"><?php echo themeEscape($sysconf['library_name']); ?></span>
            <?php if ($sysconf['template']['classic_library_subname'] || $theme_viewer_preview_enabled) : ?>
            <span class="navbar-lib-subname"<?= !$sysconf['template']['classic_library_subname'] ? ' hidden' : ''; ?>><?php echo themeEscape($sysconf['library_subname']); ?></span>
            <?php endif; ?>
        </div>
    </a>
    <div class="navbar-mobile-controls d-lg-none">
        <?php if ($show_color_mode_toggle): ?>
        <button id="color-mode-toggle-nav"
                class="btn-color-mode-toggle-nav"
                title="<?= themeEscape(__('Dark mode')) ?>"
                data-dark-title="<?= themeEscape(__('Dark mode')) ?>"
                data-light-title="<?= themeEscape(__('Light mode')) ?>"
                aria-label="<?= themeEscape(__('Toggle dark/light mode')) ?>"
                aria-pressed="false">
            <i class="fas fa-moon" aria-hidden="true"></i>
        </button>
        <?php endif; ?>
        <button id="rasamala-mobile-menu-toggle" class="navbar-toggler" type="button"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>

    <div class="navbar-collapse" id="navbarSupportedContent" aria-hidden="false">
        <div class="rasamala-mobile-menu-topbar d-lg-none">
            <span class="rasamala-mobile-menu-heading"><?= themeEscape($sysconf['library_name'] ?? __('Navigation')); ?></span>
            <button id="rasamala-mobile-menu-close" class="rasamala-mobile-menu-close" type="button"
                    aria-label="<?= themeEscape(__('Close')); ?>">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <ul class="navbar-nav ms-auto flex-lg-nowrap align-items-lg-center rasamala-navbar-menu">
          <?php
          foreach ($main_menus as $main_menu) {
            $active = '';
            $key = $main_menu['key'];
            if (isset($_GET['p'])) {
              if ($key === $_GET['p']) $active = 'active';
            } elseif ($key === 'home') {
              $active = 'active';
            }

            $safe_url = themeEscape(themeSafeMenuUrl($main_menu['url']) ?: '#');
            $safe_text = themeEscape(__($main_menu['text']));
            $safe_active = themeEscape($active);
            $safe_icon = themeEscape(themeNavbarMenuIconClass($main_menu, 'fas fa-link'));

            $menu_str = <<<HTML
<li class="nav-item {$safe_active}" data-rasamala-navbar-main-item="1">
    <a class="nav-link" href="{$safe_url}"><i class="{$safe_icon} navbar-menu-icon" aria-hidden="true"></i><span class="navbar-menu-label">{$safe_text}</span></a>
</li>
HTML;
            echo $menu_str;
          }
          ?>
          <?php
          $menu_basket_active = (isset($_GET['p'], $_GET['sec']) && $_GET['p'] === 'member' && $_GET['sec'] === 'title_basket') ? 'active' : '';
          $menu_member_active = (isset($_GET['p']) && $_GET['p'] === 'member' && ($_GET['sec'] ?? '') !== 'title_basket') ? 'active' : '';
          $count_basket = (isset($_SESSION['m_mark_biblio']) && is_array($_SESSION['m_mark_biblio'])) ? count($_SESSION['m_mark_biblio']) : 0;
          if ($is_login) :
          ?>
          <li class="nav-item <?= $menu_basket_active; ?>">
              <a class="nav-link" href="index.php?p=member&sec=title_basket" aria-label="<?= themeEscape(__('Basket')) ?>" title="<?= themeEscape(__('Basket')) ?>">
                  <i class="fas fa-shopping-basket navbar-menu-icon" aria-hidden="true"></i>
                  <sup id="count-basket" class="badge text-bg-danger"><?php echo themeEscape(themeSafeInt($count_basket)); ?></sup>
              </a>
          </li>
          <?php endif; ?>
          <?php
          if ($show_member_area) {
            if ($is_login) {
              ?>
                <li class="nav-item dropdown <?= $menu_member_active; ?>"<?= $member_area_preview_hidden ? ' hidden' : ''; ?>>
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-full ms-2 me-2 navbar-user-avatar"
                             src="<?php echo themeEscape($member_image_path ?? ''); ?>"
                             alt="<?= htmlspecialchars(sprintf(__('Avatar of %s'), $_SESSION['m_name'] ?? __('Member')), ENT_QUOTES, 'UTF-8') ?>">
                      <?php echo themeEscape($_SESSION['m_name'] ?? ''); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="index.php?p=member"><i class="fas fa-user-circle me-3" aria-hidden="true"></i> <?= themeEscape(__('Profile'));?></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?p=member&sec=bookmark"><i class="fas fa-bookmark me-3" aria-hidden="true"></i> <?= themeEscape(__('Bookmark'));?></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="index.php?p=member&logout=1"><i class="fas fa-sign-out-alt me-3" aria-hidden="true"></i> <?= themeEscape(__('Logout')); ?></a>
                    </div>
                </li>
            <?php } else { ?>
                <li class="nav-item <?= $menu_member_active; ?>"<?= $member_area_preview_hidden ? ' hidden' : ''; ?>>
                    <a class="nav-link" href="index.php?p=member">
                        <i class="fas fa-user navbar-menu-icon" aria-hidden="true"></i>
                        <span class="navbar-menu-label"><?= themeEscape(__('Member Area')) ?></span>
                    </a>
                </li>
            <?php }
          } ?>
            <?php
              $langstr = '';
              $current_lang = '';
              $select_lang = $_COOKIE['select_lang'] ?? $sysconf['default_lang'];
              // require_once(LANG . 'localisation.php');
              foreach ($available_languages??[] AS $lang_index) {
                $lang_code = $lang_index[0];
                if (function_exists('themeLanguageIsVisible') && !themeLanguageIsVisible($lang_code, $sysconf)) {
                  continue;
                }
                $lang_name = $lang_index[1];
                $code_arr = explode('_', $lang_code);
                $code_flag = strtolower($code_arr[1] ?? $code_arr[0] ?? '');
                if ($current_lang === '') {
                  $current_lang = [
                    'name' => $lang_name,
                    'code' => $code_flag
                  ];
                }
                if ($lang_code == $select_lang) {
                  $current_lang = [
                    'name' => $lang_name,
                    'code' => $code_flag
                  ];
                }
                $safe_lang_code = themeEscape($lang_code);
                $safe_code_flag = themeEscape(preg_replace('/[^a-z]/', '', $code_flag));
                $safe_lang_name = themeEscape($lang_name);

                $lang_params = $_GET ?? [];
                $lang_params['select_lang'] = $lang_code;
                $safe_lang_url = themeEscape('index.php?' . http_build_query($lang_params));

                $langstr .= <<<HTML
    <a class="dropdown-item" href="{$safe_lang_url}">
        <span class="flag-icon flag-icon-{$safe_code_flag} me-2 flag-icon-rounded" aria-hidden="true"></span> {$safe_lang_name}
    </a>
HTML;
              }
            if ($langstr !== '') { ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle cursor-pointer" href="#" role="button" id="languageMenuButton"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                   aria-label="<?= themeEscape(__('Select Language')); ?>" title="<?= themeEscape(__('Select Language')); ?>">
                    <span class="flag-icon flag-icon-<?= themeEscape($current_lang['code'] ?? '') ?> flag-icon-rounded" aria-hidden="true"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="languageMenuButton">
                    <h6 class="dropdown-header"><?= themeEscape(__('Select Language')); ?> : </h6>
                  <?= $langstr; ?>
	                </div>
	            </li>
	            <?php } ?>
            <?php if ($show_color_mode_toggle): ?>
            <li class="nav-item d-none d-lg-block nav-color-toggle-wrapper-desktop">
                <button id="color-mode-toggle-desktop"
                        class="nav-link btn-color-mode-toggle-desktop bg-transparent border-0 px-2"
                        title="<?= themeEscape(__('Dark mode')) ?>"
                        data-dark-title="<?= themeEscape(__('Dark mode')) ?>"
                        data-light-title="<?= themeEscape(__('Light mode')) ?>"
                        aria-label="<?= themeEscape(__('Toggle dark/light mode')) ?>"
                        aria-pressed="false">
                    <i class="fas fa-moon" aria-hidden="true"></i>
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
