<?php
/**
 * Helper Module for Rasamala Template - Book Detail Page Utilities
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeDetailHasValue')) {
  function themeDetailHasValue($value)
  {
    if (is_array($value)) {
      $value = implode(' ', $value);
    }

    if (function_exists('themeNormalizeTextLineBreaks')) {
      $value = themeNormalizeTextLineBreaks($value, ' ');
    }

    $clean_value = trim(html_entity_decode(strip_tags((string) ($value ?? '')), ENT_QUOTES, 'UTF-8'));
    return $clean_value !== '' && $clean_value !== '-';
  }
}

if (!function_exists('themeDetailRow')) {
  function themeDetailRow($label, $html, $value = null)
  {
    $check_value = $value ?? $html;
    if (!themeDetailHasValue($check_value)) {
      return;
    }
    ?>
    <dt class="col-sm-3"><?= themeEscape($label); ?></dt>
    <dd class="col-sm-9"><?= $html; ?></dd>
    <?php
  }
}

if (!function_exists('themeDetailNotesHtml')) {
  function themeDetailNotesHtml($value)
  {
    $raw = (string) ($value ?? '');
    if (!themeDetailHasValue($raw)) {
      return '';
    }

    $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('themeNormalizeStoredTextEscapes')) {
      $raw = themeNormalizeStoredTextEscapes($raw);
    }
    $raw = function_exists('themeNormalizeTextLineBreaks')
      ? themeNormalizeTextLineBreaks($raw)
      : str_replace(["\\r\\n", "\\n\\r", "\\r", "\\n", "\r\n", "\n\r", "\r"], "\n", $raw);
    $raw = preg_replace("/[ \t]+\n/", "\n", $raw);
    $raw = preg_replace("/\n[ \t]+/", "\n", $raw);
    $raw = trim($raw);

    $has_html = preg_match('/<\s*[a-z][^>]*>/i', $raw);
    if ($has_html) {
      $has_structured_html = preg_match('/<(p|br|div|ul|ol|li|table|blockquote|h[1-6])\b/i', $raw);
      $raw = preg_replace("/\n{3,}/", "\n\n", $raw);
      if (!$has_structured_html) {
        $raw = nl2br($raw, false);
      }

      return themeSanitizeHtml($raw);
    }

    $paragraphs = preg_split("/\n{2,}/", $raw);
    $html = '';
    foreach ($paragraphs as $paragraph) {
      $paragraph = trim($paragraph);
      if ($paragraph === '') {
        continue;
      }

      $html .= '<p>' . nl2br(themeEscape($paragraph), false) . '</p>';
    }

    return themeSanitizeHtml($html);
  }
}

if (!function_exists('themeDetailCallNumberTags')) {
  function themeDetailCallNumberTags($dbs, $biblio_id, $fallback_call_number = '')
  {
    $call_numbers = [];
    $location_names = [];
    if ($dbs && method_exists($dbs, 'prepare')) {
      $biblio_id = themeSafeInt($biblio_id);
      $stmt = $dbs->prepare("SELECT DISTINCT i.call_number, ml.location_name, i.site
          FROM item AS i
          LEFT JOIN mst_location AS ml ON i.location_id=ml.location_id
          WHERE i.biblio_id=?
          ORDER BY ml.location_name ASC, i.call_number ASC");
      if ($stmt) {
        $stmt->bind_param('i', $biblio_id);
        $stmt->execute();
        $query = $stmt->get_result();
        while ($row = $query->fetch_assoc()) {
          $call_number = trim($row['call_number'] ?? '');
          if (themeDetailHasValue($call_number)) {
            $location = trim($row['location_name'] ?? '');
            if ($location === '') {
              $location = __('Location name is not set');
            }
            if (trim($row['site'] ?? '') !== '') {
              $location .= ' (' . trim($row['site']) . ')';
            }
            $call_numbers[] = [
              'call_number' => $call_number,
              'location' => $location,
            ];
            $location_names[$location] = true;
          }
        }
        $stmt->close();
      }
    }

    if (!$call_numbers && themeDetailHasValue($fallback_call_number)) {
      $call_numbers[] = [
        'call_number' => trim((string) $fallback_call_number),
        'location' => '',
      ];
    }

    if (!$call_numbers) {
      return '';
    }

    $has_multiple_locations = count($location_names) > 1;
    $seen = [];
    $output = '<div class="detail-callnumber-strip">';
    $output .= '<div class="detail-callnumber-label"><i class="fas fa-barcode me-2 text-theme-accent" aria-hidden="true"></i>' . themeEscape(__('Call Number')) . '</div>';
    foreach ($call_numbers as $item) {
      $display_call_number = $item['call_number'];
      if ($has_multiple_locations && $item['location'] !== '') {
        $display_call_number .= ' - ' . $item['location'];
      }
      if (isset($seen[$display_call_number])) {
        continue;
      }
      $seen[$display_call_number] = true;
      $output .= '<span class="detail-callnumber-tag" title="' . themeEscape(__('Call Number')) . '">' . themeEscape($display_call_number) . '</span>';
    }
    $output .= '</div>';

    return $output;
  }
}

if (!function_exists('themeDetailAvailabilityHtml')) {
  function themeDetailAvailabilityHtml($dbs, $biblio_id, $fallback_html = '')
  {
    if (!$dbs || !method_exists($dbs, 'prepare')) {
      if (themeDetailHasValue($fallback_html)) {
        $clean_fallback = (string)$fallback_html;
        $clean_fallback = preg_replace('/(<h[1-6][^>]*>)\s*(Availability)\s*(<\/h[1-6]>)/i', '$1<i class="fas fa-book me-2 text-theme-accent" aria-hidden="true"></i>$2$3', $clean_fallback);
        return themeSanitizeHtml($clean_fallback);
      }
      return '';
    }

    $biblio_id = themeSafeInt($biblio_id);
    $sql = "SELECT i.item_code, i.call_number, ml.location_name, i.site,
                   mis.item_status_name, IFNULL(mis.no_loan, 0) AS no_loan,
                   CASE
                       WHEN EXISTS (
                           SELECT 1 FROM loan AS l
                           WHERE l.item_code=i.item_code
                             AND l.is_lent=1
                             AND l.is_return=0
                       ) THEN 1
                       ELSE 0
                   END AS is_onloan
            FROM item AS i
            LEFT JOIN mst_location AS ml ON i.location_id=ml.location_id
            LEFT JOIN mst_item_status AS mis ON i.item_status_id=mis.item_status_id
            WHERE i.biblio_id=?
            ORDER BY ml.location_name ASC, i.call_number ASC, i.item_code ASC";
    $stmt = $dbs->prepare($sql);
    if (!$stmt) {
      return '';
    }
    $stmt->bind_param('i', $biblio_id);
    $stmt->execute();
    $query = $stmt->get_result();
    if (!$query || $query->num_rows < 1) {
      $stmt->close();
      return '';
    }

    $locations = [];
    while ($item = $query->fetch_assoc()) {
      $location = trim($item['location_name'] ?? '');
      if ($location === '') {
        $location = __('Location name is not set');
      }
      if (trim($item['site'] ?? '') !== '') {
        $location .= ' (' . trim($item['site']) . ')';
      }

      $key = md5($location);
      if (!isset($locations[$key])) {
        $locations[$key] = [
          'name' => $location,
          'total' => 0,
          'available' => 0,
          'call_numbers' => [],
          'statuses' => [],
          'items' => [],
        ];
      }

      $is_available = themeSafeInt($item['is_onloan'] ?? 0) < 1 && themeSafeInt($item['no_loan'] ?? 0) < 1;
      $status = $is_available ? __('Available') : trim($item['item_status_name'] ?? '');
      if (!$is_available && themeSafeInt($item['is_onloan'] ?? 0) > 0) {
        $status = __('Currently On Loan');
      } elseif ($status === '') {
        $status = __('Not Available');
      }

      $call_number = trim($item['call_number'] ?? '');
      if ($call_number !== '') {
        $locations[$key]['call_numbers'][$call_number] = true;
      }

      $locations[$key]['total']++;
      $locations[$key]['available'] += $is_available ? 1 : 0;
      $locations[$key]['statuses'][$status] = ($locations[$key]['statuses'][$status] ?? 0) + 1;
      $locations[$key]['items'][] = [
        'item_code' => $item['item_code'] ?? '-',
        'call_number' => $call_number !== '' ? $call_number : '-',
        'is_available' => $is_available,
        'status' => $status,
      ];
    }
    $stmt->close();

    $max_location_rows = 3;
    $location_index = 0;
    $total_locations = count($locations);
    $output = '<div class="detail-avail-rows">';
    foreach ($locations as $location) {
      $location_index++;
      $hidden_class = $location_index > $max_location_rows ? ' detail-avail-row-hidden' : '';
      $hidden_attr = $location_index > $max_location_rows ? ' data-avail-hidden="1"' : '';
      $count_class = $location['available'] > 0 ? 'avail-count-ok' : 'avail-count-no';
      $item_rows = '';

      foreach ($location['items'] as $item) {
        $item_code = themeEscape($item['item_code'] ?? '-');
        $call_number = themeEscape($item['call_number'] ?? '-');
        $item_location = themeEscape($location['name']);
        $is_available = !empty($item['is_available']);
        $status_icon = $is_available ? 'fas fa-check-circle avail-badge-ok' : 'fas fa-times-circle avail-badge-no';
        $status_text = $is_available ? __('Available') : ($item['status'] ?? __('Not Available'));
        $item_rows .= '<tr>';
        $item_rows .= '<td>' . $item_code . '</td>';
        $item_rows .= '<td>' . $call_number . '</td>';
        $item_rows .= '<td>' . $item_location . '</td>';
        $item_rows .= '<td class="text-center"><i class="' . themeEscape($status_icon) . '" title="' . themeEscape($status_text) . '" aria-label="' . themeEscape($status_text) . '"></i></td>';
        $item_rows .= '</tr>';
      }

      $output .= '<div class="detail-avail-row biblio-avail-wrap' . $hidden_class . '" tabindex="0"' . $hidden_attr . '>';
      $output .= '<div class="avail-loc-callno"><span class="avail-loc-name">' . themeEscape($location['name']) . '</span></div>';
      $output .= '<span class="avail-count ' . themeEscape($count_class) . '">';
      $output .= '<i class="' . ($location['available'] > 0 ? 'fas fa-check-circle' : 'fas fa-times-circle') . '" aria-hidden="true"></i> ';
      $output .= themeSafeInt($location['available']) . '/' . themeSafeInt($location['total']);
      $output .= '</span>';

      if ($item_rows !== '') {
        $output .= '<div class="biblio-avail-popover detail-avail-popover">';
        $output .= '<div class="detail-avail-popover-title">' . themeEscape(__('Item Detail')) . ' (' . themeSafeInt($location['total']) . ') - ' . themeEscape($location['name']) . '</div>';
        $output .= '<table class="detail-avail-popover-table">';
        $output .= '<thead><tr><th>' . themeEscape(__('Code')) . '</th><th>' . themeEscape(__('Call Number')) . '</th><th>' . themeEscape(__('Location')) . '</th><th class="text-center">' . themeEscape(__('Status')) . '</th></tr></thead>';
        $output .= '<tbody>' . $item_rows . '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
      }
      $output .= '</div>';
    }
    $output .= '</div>';

    if ($total_locations > $max_location_rows) {
      $output .= '<div class="detail-avail-more">';
      $output .= '<button type="button" class="btn btn-link btn-sm p-0 detail-avail-more-btn" data-detail-avail-more>';
      $output .= '<i class="fas fa-chevron-down me-1" aria-hidden="true"></i>' . themeEscape(sprintf(__('Show %d more locations'), $total_locations - $max_location_rows));
      $output .= '</button>';
      $output .= '</div>';
    }

    return $output;
  }
}
