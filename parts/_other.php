<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _other.php

?>

<div class="result-search pb-5 rasamala-subpage-wrapper <?= ($_GET['p'] ?? '') === 'login' ? 'page-member-area' : '' ?>">
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
      <?php
      $display_page_title = stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('_', ' ', (string)($page_title ?? '')))));
      if ($display_page_title === '') {
        $display_page_title = stripslashes((string)($page_title ?? ''));
      }

      $breadcrumb_label = $display_page_title;
      $breadcrumb_parents = [];
      $current_p = (string)($_GET['p'] ?? '');

      if ($current_p === 'show_detail') {
        $breadcrumb_label = !empty($display_page_title) ? $display_page_title : __('Detail');
      } elseif ($current_p === 'login') {
        $breadcrumb_label = __('Staff Area');
      } elseif ($current_p === 'news') {
        $breadcrumb_label = __('Library News');
      } elseif (strpos($current_p, 'news') === 0 || strpos($current_p, 'news/') === 0 || (isset($_GET['p']) && strpos($_GET['p'], 'news') !== false)) {
        $breadcrumb_parents[] = [
          'label' => __('Library News'),
          'url' => 'index.php?p=news'
        ];
      }

      echo themeBreadcrumbsHtml($breadcrumb_label, $breadcrumb_parents);

      if ($current_p !== 'show_detail') {
        if ($current_p === 'login') {
          echo '<div class="row"><div class="col-md-8 mx-auto">';
          echo '<div class="rasamala-main-content-card p-4 shadow-sm">';
          echo '<div class="tagline">' . themeEscape(__('Librarian Login')) . '</div>';
          echo '<div class="loginInfo">' . __('Please insert your username and password given by library system administrator.') . '</div>';
          echo $main_content;
          echo '</div>';
          echo '</div></div>';
        } else {
          $title_divider = ($current_p === 'news') ? '' : '<hr class="rasamala-divider mb-4">';
          echo '<h2 class="mb-4 fw-bold detail-title">' . themeEscape($display_page_title) . '</h2>' . $title_divider;
          if ($current_p === 'librarian') {
            $librarian_content = function_exists('themeRenderLibrarianPage') ? themeRenderLibrarianPage($dbs, $sysconf) : $main_content;
            echo '<div class="d-flex flex-row flex-wrap rasamala-librarian-list">' . $librarian_content . '</div>';
          } elseif ($current_p === 'news') {
            echo '<div class="d-flex flex-column">' . $main_content . '</div>';
          } else {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $request_uri = function_exists('themeHeaderRequestUri')
              ? themeHeaderRequestUri()
              : preg_replace('/[^a-zA-Z0-9\/?=&_.-]/', '', strip_tags((string)($_SERVER['REQUEST_URI'] ?? '')));
            $current_page_url = $scheme . $host . $request_uri;

            // Fetch or format day and date with clock icon
            $content_date_html = '';
            $date_str = '';
            if (isset($dbs) && method_exists($dbs, 'prepare')) {
              $path_value = trim((string)($current_p ?? ''));
              $id_safe = themeSafeInt($_GET['id'] ?? 0);
              $sql = '';
              if ($path_value !== '' && $id_safe > 0) {
                $sql = 'SELECT input_date, last_update FROM content WHERE content_path=? OR content_id=? LIMIT 1';
              } elseif ($path_value !== '') {
                $sql = 'SELECT input_date, last_update FROM content WHERE content_path=? LIMIT 1';
              } elseif ($id_safe > 0) {
                $sql = 'SELECT input_date, last_update FROM content WHERE content_id=? LIMIT 1';
              }

              if ($sql !== '') {
                $statement = $dbs->prepare($sql);
                if ($statement) {
                  if ($path_value !== '' && $id_safe > 0) {
                    $statement->bind_param('si', $path_value, $id_safe);
                  } elseif ($path_value !== '') {
                    $statement->bind_param('s', $path_value);
                  } else {
                    $statement->bind_param('i', $id_safe);
                  }
                  $statement->execute();
                  $content_result = $statement->get_result();
                  if ($content_result && ($row = $content_result->fetch_assoc())) {
                    $date_str = !empty($row['input_date']) ? $row['input_date'] : ($row['last_update'] ?? '');
                  }
                  $statement->close();
                }
              }
            }
            if ($date_str === '') {
              $date_str = date('Y-m-d H:i:s');
            }
            $user_lang = $_SESSION['select_lang'] ?? ($_COOKIE['select_lang'] ?? ($sysconf['default_lang'] ?? 'id'));
            $locale = (strpos($user_lang, 'en') === 0 || $user_lang === 'english') ? 'en' : 'id';
            try {
              $formatted_date = \Carbon\Carbon::parse($date_str)->locale($locale)->isoFormat('dddd, LL');
            } catch (\Exception $e) {
              $formatted_date = (string)$date_str;
            }
            $content_date_html = '<div class="content-date news-list-date"><i class="far fa-clock me-2" aria-hidden="true"></i>' . themeEscape($formatted_date) . '</div>';

            $content_actions = '
            <div class="content-detail-action-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-3 border-bottom">
                ' . $content_date_html . '
                <div class="d-inline-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-content-share" data-url="' . themeEscape($current_page_url) . '" data-title="' . themeEscape($display_page_title) . '" title="' . themeEscape(__('Share')) . '">
                        <i class="fas fa-share-alt" aria-hidden="true"></i> <span>' . themeEscape(__('Share')) . '</span>
                    </button>
                </div>
            </div>';

            echo '<div class="rasamala-main-content-card p-4 shadow-sm">' . $content_actions . themeInjectCspNonceToScripts($main_content) . '</div>';
          }
        }
      } else {
        echo themeInjectCspNonceToScripts($main_content);
      }
      ?>
    </section>
</div>
