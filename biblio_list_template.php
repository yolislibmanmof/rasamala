<?php
/**
 * Template for Biblio List
 * name of memberID text field must be: memberID
 * name of institution text field must be: institution
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com)
 * Create by Eddy Subratha (eddy.subratha@slims.web.id)
 * @Last modified by    : Ade Ismail Siregar (adeismailbox@gmail.com)
 * @Last modified time  : 2026-07-15T15:16:37+07:00
 *
 * Slims 8 (Akasia)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
use SLiMS\Url;
include_once __DIR__ . '/theme_helpers.php';
$label_cache = array();
/**
 *
 * Format bibliographic item list for OPAC display
 *
 * @param   object $dbs
 * @param   array $biblio_detail
 * @param   int $n
 * @param   array $settings
 * @param   array $return_back
 *
 * @return string
 */
function biblio_list_format($dbs, $biblio_detail, $n, $settings = array(), &$return_back = array()) {
    global $label_cache, $sysconf;
    // init output var
    $output     = '';

    $title      = (string)($biblio_detail['title'] ?? '');
    $biblio_id  = themeSafeInt($biblio_detail['biblio_id'] ?? 0);
    $keywords   = (string)($settings['keywords'] ?? '');
    $detail_url_raw = SWB.'index.php?p=show_detail&id='.$biblio_id.'&keywords='.urlencode($keywords);
    $cite_url_raw   = SWB.'index.php?p=cite&id='.$biblio_id.'&keywords='.urlencode($keywords);
    $detail_url = themeEscape($detail_url_raw);
    $cite_url   = themeEscape($cite_url_raw);
    $title_search_html = themeParallelTitleHtml($title, 'search');
    $title_grid_html = themeParallelTitleHtml($title, 'grid');
    $title_attr = themeEscape(str_replace('{title}', substr(strip_tags($title), 0, 50), __('Citation for: {title}')));
    $current_view = $_POST['view'] ?? $_GET['view'] ?? $_SESSION['LIST_VIEW'] ?? 'simple';
    if (!in_array($current_view, ['simple', 'list', 'grid'], true)) {
        $current_view = 'simple';
    }
    // image thumbnail
    $thumb_url = '';
    if ($current_view !== 'simple') {
        $images_loc = 'images/docs/'.basename((string)($biblio_detail['image'] ?? ''));
        if(($biblio_detail['image'] ?? '') == '' || ($biblio_detail['image'] ?? '') == NULL){
            $images_loc = 'images/default/image.png';
        }
        $thumb_url = './lib/minigalnano/createthumb.php?filename='.urlencode($images_loc).'&width=240';
    }

    $cover_html_list = '';
    $cover_html_grid = '';

    // notes
    $notes = '';
    $custom_field = '';
    $grid_item_content = '';
    $i = 0;
    $expand = true;
    if ($current_view !== 'simple') {
        $notes = themeSanitizeHtml(getNotes($dbs, $biblio_id, $biblio_detail));
    }
    if ($current_view !== 'simple' && $settings['enable_custom_frontpage'] AND $settings['custom_fields']) {
        $custom_field = '<dl class="row text-sm">';
        foreach ($settings['custom_fields'] as $field => $field_opts) {
            if ($field_opts[0] == 1) {
                $field_value = (trim($biblio_detail[$field]??'') !== '' ? $biblio_detail[$field] : '-');
                $custom_field .= '<dt class="col-sm-3">'.themeEscape($field_opts[1]).'</dt><dd class="col-sm-9">'.themeEscape($field_value).'</dd>';
                $grid_item_content .= '<li class="list-group-item"><label>'.themeEscape($field_opts[1]).'</label><span class="text-end">'.themeEscape($field_value).'</span></li>';
                $i++;
            }
        }
        $custom_field .= '</dl>';
    }
    if ($current_view !== 'simple' && empty($notes)) {
        $notes = $custom_field;
        $expand = false;
    }

    // availability
    $item_availability_data = rasamalaGetItemsAndAvailability($dbs, $biblio_id);
    $availability = themeSafeInt($item_availability_data['available'] ?? 0);
    $class_avail = ($availability > 0) ? '' : 'text-danger';
    $availability_total = themeSafeInt($item_availability_data['total'] ?? 0);
    $availability_summary = $availability_total > 0
        ? themeSafeInt($availability) . '/' . $availability_total
        : themeSafeInt($availability);
    $availability_state_class = $availability > 0 ? 'is-available' : 'is-unavailable';
    $availability_side_icon = $availability > 0 ? 'fas fa-check-circle' : 'fas fa-times-circle';
    $availability_label = $availability > 0 ? __('Available') : __('Not Available');

    // authors
    $_authors = isset($biblio_detail['author'])?$biblio_detail['author']:biblio_list_model::getAuthors($dbs, $biblio_id, true);
    $_authors_string = '';
    $_authors_plain = themeEscape('-');
    if ($_authors) {
        if (!is_array($_authors)) {
            $_authors = explode('-', $_authors);
        }
        $_author_names = [];
        foreach ($_authors as $a) {
            $a = trim($a);
            if ($a === '') {
                continue;
            }
            $_author_names[] = $a;
            $_authors_string .= '<a href="index.php?author='.urlencode($a).'&search=Search" itemprop="name" property="name" class="btn btn-outline-secondary btn-rounded">'.themeEscape($a).'</a>';
        }
        if ($_author_names) {
            $_authors_plain = themeEscape(implode('; ', $_author_names));
        }
    }

    if ($current_view !== 'simple') {
        if (themeShouldGenerateBookCover($biblio_detail['image'] ?? '', $sysconf)) {
            $generated_cover = themeGenerateBookCoverHtml($biblio_detail['title'] ?? '', $_authors_plain);
            $cover_html_list = $generated_cover;
            $cover_html_grid = $generated_cover;
        } else {
            $cover_alt = themeEscape(sprintf(__('Cover of %s'), trim(strip_tags((string)($biblio_detail['title'] ?? __('collection'))))));
            $cover_html_list = '<img loading="lazy" src="'.themeEscape($thumb_url).'" alt="'.$cover_alt.'" class="img-fluid rounded '.($availability > 0 ? 'is-available' : 'not-available').'" title="' . themeEscape($availability > 0 ? '' :  __('Items is not available')) . '"/>';
            $cover_html_grid = '<img loading="lazy" src="'.themeEscape($thumb_url).'" alt="'.$cover_alt.'" class="img-fluid img-thumbnail shadow '.($availability > 0 ? 'is-available' : 'not-available').'" title="' . themeEscape($availability > 0 ? '' :  __('Items is not available')) . '"/>';
        }
    }

    if ($current_view === 'simple'):
        $availability_icon = $availability > 0 ? 'fas fa-check-circle' : 'fas fa-times-circle';
        $availability_status_class = $availability > 0 ? 'biblio-avail-ok' : 'biblio-avail-no';
        $availability_title = $availability_label;
        $item_rows = '';

        foreach ($item_availability_data['items'] as $item) {
            $item_code = themeEscape($item['item_code'] ?? '-');
            $call_number = themeEscape($item['call_number'] ?? '-');
            $location = themeEscape($item['location_name'] ?? '-');
            $status_icon = ((int)($item['is_available'] ?? 0) === 1)
                ? '<i class="fas fa-check-circle biblio-avail-row-ok" aria-label="'.themeEscape(__('Available')).'"></i>'
                : '<i class="fas fa-times-circle biblio-avail-row-no" aria-label="'.themeEscape(__('Not Available')).'"></i>';
            $item_rows .= '<tr><td>'.$item_code.'</td><td>'.$call_number.'</td><td>'.$location.'</td><td class="text-center">'.$status_icon.'</td></tr>';
        }

        $output .= '<article id="card-' . $biblio_id . '" class="biblio-simple-item">';
        $output .= '<div class="biblio-simple-main">';
        $output .= '<a title="'.themeEscape(__('View record detail description for this title')).'" class="biblio-simple-title" href="'.$detail_url.'">'.$title_search_html.'</a>';
        $output .= '<div class="biblio-simple-author">'.$_authors_plain.'</div>';
        $output .= '</div>';
        $output .= '<div class="biblio-simple-availability biblio-avail-wrap '.$class_avail.'" aria-label="'.themeEscape(__('Availability')).'">';
        $output .= '<button type="button" class="biblio-avail-badge '.$availability_status_class.'" title="'.themeEscape($availability_title).'">';
        $output .= '<i class="'.$availability_icon.'" aria-hidden="true"></i>';
        $output .= '<span class="biblio-simple-availability-count">'.themeSafeInt($availability).'</span>';
        $output .= '</button>';
        if ($item_rows !== '') {
            $output .= '<div class="biblio-avail-popover" role="dialog" aria-label="'.themeEscape(__('Item Detail')).'">';
            $output .= '<div class="biblio-avail-popover-title">'.themeEscape(__('Item Detail')).' ('.$availability_total.')</div>';
            $output .= '<table class="biblio-avail-popover-table">';
            $output .= '<thead><tr><th>'.themeEscape(__('Code')).'</th><th>'.themeEscape(__('Call Number')).'</th><th>'.themeEscape(__('Location')).'</th><th class="text-center">'.themeEscape(__('Status')).'</th></tr></thead>';
            $output .= '<tbody>'.$item_rows.'</tbody>';
            $output .= '</table>';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</article>';

    elseif ($current_view === 'list'):

        $output .= '<div id="card-' . $biblio_id . '" class="card item border-0 elevation-1 mb-4 biblio-list-card">';
        $output .= '<div class="card-body">';
        $output .= '<div class="biblio-list-layout">';
        $output .= '<a class="biblio-list-cover" href="'.$detail_url.'" title="'.themeEscape(__('View record detail description for this title')).'">';
        $output .= $cover_html_list;
        $output .= '</a>';
        $output .= '<div class="biblio-list-content">';
        $output .= '<h5 class="biblio-list-title"><a title="'.themeEscape(__('View record detail description for this title')).'" class="card-link" href="'.$detail_url.'">'.$title_search_html.'</a></h5>';
        $output .= createButton($biblio_id, $biblio_detail['title']);
        $output .= '<div class="d-flex authors flex-wrap py-2">';
        $output .= $_authors_string;
        $output .= '</div>'; // -- close d-flex authors flex-wrap
        $output .= '<p class="biblio-list-notes">'.$notes.'</p>';
        $output .= '<div id="expand-'.$biblio_id.'" class="collapse py-2 collapse-detail">'.$custom_field.'</div>';
        $output .= '</div>'; // -- close biblio-list-content
        $output .= '<aside class="biblio-list-side">';
        $output .= '<div class="biblio-list-availability '.$availability_state_class.'">';
        $output .= '<span class="biblio-list-availability-label"><i class="'.$availability_side_icon.'" aria-hidden="true"></i>'.themeEscape($availability_label).'</span>';
        $output .= '<strong class="'.$class_avail.'">'.$availability_summary.'</strong>';
        $output .= '</div>';
        $output .= '<button type="button" class="biblio-list-basket add-to-chart-button" data-biblio="'.$biblio_id.'"><i class="fas fa-plus" aria-hidden="true"></i><span>'.themeEscape(__('Add to basket')).'</span></button>';
        $output .= '<a class="biblio-list-action-link" href="'.themeEscape($detail_url_raw.'&MARC=true').'" title="'.themeEscape(__('Download detail data in MARC')).'" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-download" aria-hidden="true"></i><span>'.themeEscape(__('MARC')).'</span></a>';
        $output .= '<a class="biblio-list-action-link citationLink" href="'.$cite_url.'" title="'.$title_attr.'" target="_blank" rel="noopener noreferrer"><i class="fas fa-quote-right" aria-hidden="true"></i><span>'.themeEscape(__('Cite')).'</span></a>';
        $output .= '</aside>';
        $output .= '</div>'; // -- close biblio-list-layout
        if ($i > 0 && $expand) {
            $output .= '<div class="expand"><a id="btn-expand-'.$biblio_id.'" class="d-flex justify-content-center text-decoration-none py-2" data-bs-toggle="collapse" href="#expand-'.$biblio_id.'" role="button" aria-expanded="false" aria-controls="expand-'.$biblio_id.'" aria-label="'.themeEscape(__('Show item details')).'"><i class="fas fa-angle-double-down" aria-hidden="true"></i></a></div>';
        }
        $output .= '</div>';
        $output .= '</div>';

    else:

        $output .= '<div class="col-md-3 px-2 grid-item">';
        $output .= '<div class="card p-0 mb-3">';
        $title_cite = $title_attr;
        $marc_url = themeEscape($detail_url_raw.'&MARC=true');
        $add_to_basket_text = themeEscape(__('Add to basket'));
        $marc_text = themeEscape(__('MARC Download'));
        $cite_text = themeEscape(__('Cite'));
        $output .= <<<HTML
<div class="grid-item--menu dropdown">
    <a class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
        </svg>
    </a>
    <div class="dropdown-menu dropdown-menu-end text-sm">
        <a class="dropdown-item text-start px-3" href="{$marc_url}">{$marc_text}</a>
        <a class="dropdown-item text-start px-3 citationLink" href="{$cite_url}" title="{$title_cite}" target="_blank" rel="noopener noreferrer">{$cite_text}</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-start px-3 add-to-chart-button" data-biblio="{$biblio_id}" href="index.php?p=member&sec=title_basket">{$add_to_basket_text}</a>
    </div>
</div>
HTML;
        $output .= '<div class="p-4 d-flex justify-content-center align-items-center bg-rasamala-light">';
        $output .= $cover_html_grid;
        $output .= '</div>';
        $output .= '<div class="card-body p-2">';
        $output .= '<a href="'.$detail_url.'" class="text-sm text-decoration-none grid-item--title m-0">'.$title_grid_html.'</a>';
        $output .= '</div>';
        $output .= '<ul class="list-group list-group-flush">';
        $output .= $grid_item_content;
        if ($availability < 1) {
            $output .= '<li class="list-group-item text-danger"><span></span><span class="text-center">'.__('Item Not Available').'</span></li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';

    endif;

    // debug
    // $output .= '<code>'.json_encode($biblio_detail).'</code>';

    return $output;
}

if (!function_exists('rasamalaBatchPrimeNotes')) {
  function rasamalaBatchPrimeNotes($dbs, array $biblio_ids, array &$cache)
  {
      $valid_ids = [];
      foreach ($biblio_ids as $id) {
          $clean_id = themeSafeInt($id);
          if ($clean_id > 0 && !isset($cache[$clean_id])) {
              $valid_ids[] = $clean_id;
              $cache[$clean_id] = '';
          }
      }

      if (empty($valid_ids)) {
          return;
      }

      $placeholders = implode(',', array_fill(0, count($valid_ids), '?'));
      $sql = "SELECT biblio_id, notes FROM biblio WHERE biblio_id IN (" . $placeholders . ")";
      $statement = $dbs->prepare($sql);
      if (!$statement) {
          return;
      }
      $bind_types = str_repeat('i', count($valid_ids));
      $bind_parameters = [$bind_types];
      foreach ($valid_ids as $index => $value) {
          $bind_parameters[] = &$valid_ids[$index];
      }
      call_user_func_array([$statement, 'bind_param'], $bind_parameters);
      $statement->execute();
      $query = $statement->get_result();

      if ($query) {
          while ($row = $query->fetch_assoc()) {
              $b_id = themeSafeInt($row['biblio_id'] ?? 0);
              if ($b_id <= 0 || !isset($cache[$b_id])) {
                  continue;
              }
              $raw_text = (string)($row['notes'] ?? '');
              if (function_exists('themeNormalizeStoredTextEscapes')) {
                  $raw_text = themeNormalizeStoredTextEscapes($raw_text);
              }
              $raw_text = str_replace(['\r\n', '\r', '\n', "\\r\\n", "\\r", "\\n"], ' ', $raw_text);
              $raw_text = str_replace(["\r\n", "\r", "\n"], ' ', $raw_text);
              $raw_text = strip_tags($raw_text);
              $raw_text = preg_replace('/\s+/', ' ', $raw_text);
              $cache[$b_id] = addEllipsis(trim($raw_text), 400);
          }
      }
      $statement->close();
  }
}

if (!function_exists('getNotes')) {
  function getNotes($dbs, $biblio_id, array $biblio_detail = [])
  {
      static $cache = [];

      if (is_array($biblio_id)) {
          rasamalaBatchPrimeNotes($dbs, $biblio_id, $cache);
          return '';
      }

      $clean_id = themeSafeInt($biblio_id);

      if (!empty($biblio_detail['notes'])) {
          $raw_text = (string)$biblio_detail['notes'];
          if (function_exists('themeNormalizeStoredTextEscapes')) {
              $raw_text = themeNormalizeStoredTextEscapes($raw_text);
          }
          $raw_text = str_replace(['\r\n', '\r', '\n', "\\r\\n", "\\r", "\\n"], ' ', $raw_text);
          $raw_text = str_replace(["\r\n", "\r", "\n"], ' ', $raw_text);
          $raw_text = strip_tags($raw_text);
          $raw_text = preg_replace('/\s+/', ' ', $raw_text);
          $processed = addEllipsis(trim($raw_text), 400);
          if ($clean_id > 0) {
              $cache[$clean_id] = $processed;
          }
          return $processed;
      }

      if ($clean_id <= 0) {
          return '';
      }

      if (isset($cache[$clean_id])) {
          return $cache[$clean_id];
      }

      rasamalaBatchPrimeNotes($dbs, [$clean_id], $cache);

      return $cache[$clean_id] ?? '';
  }
}

if (!function_exists('addEllipsis')) {
  function addEllipsis($string, $length, $end='…')
  {
      if (strlen($string??'') > $length)
      {
          $length -= strlen($end);
          $string  = substr($string, 0, $length);
          $string .= $end;
      }

      return $string;
  }
}

if (!function_exists('rasamalaBatchPrimeAvailability')) {
  function rasamalaBatchPrimeAvailability($dbs, array $biblio_ids, array &$cache)
  {
      $valid_ids = [];
      foreach ($biblio_ids as $id) {
          $clean_id = themeSafeInt($id);
          if ($clean_id > 0 && !isset($cache[$clean_id])) {
              $valid_ids[] = $clean_id;
              $cache[$clean_id] = [
                  'items' => [],
                  'total' => 0,
                  'available' => 0,
              ];
          }
      }

      if (empty($valid_ids)) {
          return;
      }

    $placeholders = implode(',', array_fill(0, count($valid_ids), '?'));
    $sql = "SELECT i.biblio_id, i.item_code, i.call_number, ml.location_name,
                     CASE
                         WHEN IFNULL(mis.no_loan, 0)=1 THEN 0
                         WHEN EXISTS(
                             SELECT 1 FROM loan AS l
                             WHERE l.item_code=i.item_code
                               AND l.is_lent=1
                               AND l.is_return=0
                         ) THEN 0
                         ELSE 1
                     END AS is_available
              FROM item AS i
              LEFT JOIN mst_location AS ml ON i.location_id=ml.location_id
              LEFT JOIN mst_item_status AS mis ON i.item_status_id=mis.item_status_id
              WHERE i.biblio_id IN (".$placeholders.")
              ORDER BY i.biblio_id ASC, i.call_number ASC, i.item_code ASC";

      $statement = $dbs->prepare($sql);
      if (!$statement) {
          return;
      }
      $bind_types = str_repeat('i', count($valid_ids));
      $bind_parameters = [$bind_types];
      foreach ($valid_ids as $index => $value) {
          $bind_parameters[] = &$valid_ids[$index];
      }
      call_user_func_array([$statement, 'bind_param'], $bind_parameters);
      $statement->execute();
      $query = $statement->get_result();
      if ($query) {
          while ($row = $query->fetch_assoc()) {
              $b_id = themeSafeInt($row['biblio_id'] ?? 0);
              if ($b_id <= 0 || !isset($cache[$b_id])) {
                  continue;
              }
              $row['is_available'] = themeSafeInt($row['is_available'] ?? 0);
              $cache[$b_id]['items'][] = $row;
              $cache[$b_id]['total']++;
              if ($row['is_available'] > 0) {
                  $cache[$b_id]['available']++;
              }
          }
      }
      $statement->close();
  }
}

if (!function_exists('rasamalaGetItemsAndAvailability')) {
  function rasamalaGetItemsAndAvailability($dbs, $biblio_id)
  {
      static $cache = [];

      if (is_array($biblio_id)) {
          rasamalaBatchPrimeAvailability($dbs, $biblio_id, $cache);
          return $cache;
      }

      $clean_id = themeSafeInt($biblio_id);
      if ($clean_id <= 0) {
          return ['items' => [], 'total' => 0, 'available' => 0];
      }

      if (isset($cache[$clean_id])) {
          return $cache[$clean_id];
      }

      rasamalaBatchPrimeAvailability($dbs, [$clean_id], $cache);

      return $cache[$clean_id] ?? ['items' => [], 'total' => 0, 'available' => 0];
  }
}

function createButton(int $biblio_id, string $title)
{
    $biblio_id = themeSafeInt($biblio_id);
    $commentUrlCondition = (utility::isMemberLogin() ? 
                                Url::getSlimsBaseUri('?p=show_detail&id=' . $biblio_id . '#comment') : 
                                Url::getSlimsBaseUri('?p=member&destination=' . Url::getSlimsBaseUri('?p=show_detail&id=' . $biblio_id . '#comment')->encode()));

    list($comment,$bookmark,$share) = [__('Comment'), (in_array($biblio_id, $_SESSION['bookmark']??[]) ? __('Bookmarked') : __('Bookmark')),__('Share')];

    $setBookmarked = isset($_SESSION['bookmark'][$biblio_id]) ? 'bg-success text-white rounded-3 is-bookmarked' : 'text-muted';
    $commentUrlCondition = themeEscape((string)$commentUrlCondition);
    $comment = themeEscape($comment);
    $bookmark = themeEscape($bookmark);
    $share = themeEscape($share);
    $title_attr = themeEscape($title);
    return <<<HTML
    <div class="biblio-list-quick-actions">
        <a href="{$commentUrlCondition}" class="biblio-list-quick-action">
            <i class="far fa-comment-dots" aria-hidden="true"></i>
            <span>{$comment}</span>
        </a>
        <a href="index.php?p=member&sec=bookmark" data-id="{$biblio_id}" class="bookMarkBook biblio-list-quick-action {$setBookmarked}">
            <i class="far fa-bookmark" aria-hidden="true"></i>
            <span id="label-{$biblio_id}">{$bookmark}</span>
        </a>
        <button type="button" class="btn btn-theme-share detail-share-btn biblio-list-quick-action" data-id="{$biblio_id}" data-title="{$title_attr}" title="{$share}">
            <i class="fas fa-share-alt" aria-hidden="true"></i>
            <span>{$share}</span>
        </button>
    </div>
    HTML;
}
