<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-30 00:58
 * @File name           : detail_template.php
 * @Last modified       : 2026-03-02 (Session 2: clean cover, inline authors/subjects,
 *                        hide empty fields, custom availability with color, no duplications)
 */

function countAttachment($dbs, $biblio_id) {
    $stmt = $dbs->prepare("SELECT count(*) FROM biblio_attachment WHERE biblio_id = ? AND access_type = 'private'");
    $stmt->bind_param('i', $biblio_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_row();
    return $data[0];
}

function truncateText($text, $maxLength) {
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $maxLength) {
        return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
    } elseif (strlen($text) > $maxLength) {
        return substr($text, 0, $maxLength) . '...';
    }
    return $text;
}

/** Check if a metadata value is actually present (not empty/NONE/dash) */
function isValuePresent($val) {
    if ($val === null || $val === false) return false;
    $clean = trim(strip_tags((string)$val));
    if ($clean === '' || $clean === '-' || strtoupper($clean) === 'NONE') return false;
    return true;
}

$cover_width = '200px';

// Prepare item data with individual loan status + item status (no_loan flag)
$_item_stmt = $this->db->prepare("
    SELECT i.item_code, i.call_number, ml.location_name, mct.coll_type_name,
           IFNULL(mis.item_status_name, '') AS item_status_name,
           IFNULL(mis.no_loan, 0) AS no_loan,
           CASE
               WHEN IFNULL(mis.no_loan, 0) = 1 THEN 0
               WHEN EXISTS(
                   SELECT 1 FROM loan l WHERE l.item_code = i.item_code AND l.is_lent=1 AND l.is_return=0
               ) THEN 0
               ELSE 1
           END as is_available,
           (SELECT l2.due_date FROM loan l2
            WHERE l2.item_code = i.item_code AND l2.is_lent=1 AND l2.is_return=0
            LIMIT 1) AS due_date
    FROM item i
    LEFT JOIN mst_location ml ON i.location_id = ml.location_id
    LEFT JOIN mst_coll_type mct ON i.coll_type_id = mct.coll_type_id
    LEFT JOIN mst_item_status mis ON i.item_status_id = mis.item_status_id
    WHERE i.biblio_id = ?
    ORDER BY ml.location_name ASC, i.call_number ASC
");
$_item_stmt->bind_param('i', $biblio_id);
$_item_stmt->execute();
$item_query = $_item_stmt->get_result();
$items = [];
$total_items = 0;
$available_count = 0;
while ($item_data = $item_query->fetch_assoc()) {
    $items[] = $item_data;
    $total_items++;
    if ($item_data['is_available']) $available_count++;
}
$first_item = $items[0] ?? [];
$call_number = $first_item['call_number'] ?? '-';

// Group items by location for compact display
$locationGroups = [];
foreach ($items as $itm) {
    $loc = $itm['location_name'] ?? 'Unknown';
    if (!isset($locationGroups[$loc])) {
        $locationGroups[$loc] = ['total' => 0, 'available' => 0, 'call_number' => $itm['call_number'] ?? '-', 'items' => []];
    }
    $locationGroups[$loc]['total']++;
    if ($itm['is_available']) $locationGroups[$loc]['available']++;
    $locationGroups[$loc]['items'][] = $itm;
}
$maxLocationRows = 3; // Show max 3 location rows before "show more"

// Prepare titles
$displayMainTitle = $title;
$displayParallelTitle = '';
if (strpos($title, '=') !== false) {
    [$displayMainTitle, $displayParallelTitle] = array_map('trim', explode('=', $title, 2));
} else {
    $displayMainTitle = trim($title);
}

$display_title = truncateText(trim(explode('=', $title, 2)[0]), 80);
$display_author = truncateText(strip_tags(str_replace('<br />', ', ', $authors)), 50);

// Clean publisher
$clean_publisher = $publisher_name;
$clean_publisher = str_ireplace('Program Studi ', '', $clean_publisher);
$clean_publisher = str_ireplace('Universitas Indonesia', 'UI', $clean_publisher);
$clean_publisher = trim($clean_publisher);
$display_publisher = truncateText($clean_publisher, 40);

$has_real_cover_image = !empty($image) && strpos($image, 'images/default/image.png') === false && strpos($image, '<img') !== false;

// Prepare inline subjects (replace <br /> with semicolons)
$inline_subjects = '';
if (isValuePresent($subjects)) {
    $inline_subjects = str_replace(['<br />', '<br>', '<br/>'], ' <span style="color:#adb5bd; margin:0 2px;">;</span> ', $subjects);
}
?>

<!-- Detail page responsive styles are in style.css -->

<div class="container pb-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0 mb-3 text-sm">
            <li class="breadcrumb-item"><a href="index.php" class="text-muted"><i class="fas fa-home mr-2"></i><?= __('Home') ?></a></li>
            <?php if (isset($_SESSION['last_search_url'])): ?>
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($_SESSION['last_search_url'], ENT_QUOTES, 'UTF-8') ?>" class="text-muted"><?= __('Pencarian') ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-secondary" aria-current="page"><?= __('Detail') ?></li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap detail-layout">
        <!-- ========== LEFT SIDEBAR: Cover + Availability ========== -->
        <div class="detail-cover-sidebar mb-4">

            <?php
            // Prepare distinct call numbers
            $distinct_call_numbers = [];
            foreach ($items as $itm) {
                $cn = trim($itm['call_number']);
                if (!empty($cn) && $cn !== '-' && !in_array($cn, $distinct_call_numbers)) {
                    $distinct_call_numbers[] = $cn;
                }
            }
            ?>

            <!-- Cover -->
            <div class="detail-cover-area">
                <div class="detail-generated-cover">
                    <!-- Call Numbers inside Cover -->
                    <?php if (!empty($distinct_call_numbers)): ?>
                    <div class="detail-callnumber-strip">
                        <div style="font-size: 0.65rem; font-weight: 600; color: #6c757d; text-transform: uppercase; width: 100%; margin-bottom: 2px; letter-spacing: 0.5px;">
                            <?= __('Call Number') ?>
                        </div>
                        <?php foreach ($distinct_call_numbers as $dcn): ?>
                            <span class="detail-callnumber-tag" title="<?= __('Call Number') ?>"><?= htmlspecialchars($dcn, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($has_real_cover_image): ?>
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; border-radius: 4px; overflow: hidden; background: #f8f9fa;">
                            <?= $image; ?>
                        </div>
                    <?php else: ?>
                        <!-- Text Cover -->
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                            <div style="margin-bottom: 12px;">
                                <div style="font-size: 0.875rem; font-weight: 700; color: #1a1a1a; line-height: 1.3; text-align: center; word-wrap: break-word;">
                                    <?= htmlspecialchars($display_title, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            <div style="border-top: 2px solid #333; margin: 8px 0;"></div>
                            <div style="font-size: 0.6875rem; color: #2c2c2c; font-style: italic; line-height: 1.4; text-align: center;">
                                <?= htmlspecialchars($display_author, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php if (!empty($clean_publisher)): ?>
                            <div style="font-size: 0.625rem; color: #555; margin-top: 10px; text-align: center;">
                                <?= htmlspecialchars($display_publisher, ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($publish_year, ENT_QUOTES, 'UTF-8'); ?>)
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Request Access Button -->
            <?php if (countAttachment($this->db, $biblio_id) > 0): ?>
            <div class="detail-cover-area" style="padding: 0 8px;">
                <a class="add-to-access btn btn-dark btn-block" style="font-size: 0.8125rem; border-radius: 6px;"
                   data-biblio="<?= $biblio_id ?>" href="index.php?p=member"
                   title="Request Access for member only">
                    <i class="fas fa-lock mr-2"></i>Request Access
                </a>
            </div>
            <?php endif; ?>

            <!-- ===== AVAILABILITY (grouped by location, compact) ===== -->
            <div class="detail-availability-sidebar">
                <h6 style="font-size: 0.8125rem; font-weight: 700; color: #343a40; margin-bottom: 0.5rem; padding: 0 8px;">
                    <i class="fas fa-box-open" style="color: #6c757d; margin-right: 4px;"></i><?= __('Availability'); ?>
                </h6>
                <?php if ($total_items > 0): ?>
                <div style="padding: 0 8px;">
                    <!-- Location rows (grouped, compact) -->
                    <div class="detail-avail-rows">
                        <?php
                        $locIndex = 0;
                        $totalLocations = count($locationGroups);
                        foreach ($locationGroups as $locName => $locData):
                            $locIndex++;
                            $hiddenClass = ($locIndex > $maxLocationRows) ? ' style="display:none;" data-avail-hidden="1"' : '';
                            $locSafe = htmlspecialchars($locName, ENT_QUOTES, 'UTF-8');
                            $countClass = ($locData['available'] > 0) ? 'avail-count-ok' : 'avail-count-no';

                            // Build the item detail popover table rows for hover
                            $itemRows = '';
                            foreach ($locData['items'] as $itm) {
                                $ic = htmlspecialchars($itm['item_code'] ?? '-', ENT_QUOTES, 'UTF-8');
                                $cn = htmlspecialchars($itm['call_number'] ?? '-', ENT_QUOTES, 'UTF-8');
                                $lo = htmlspecialchars($itm['location_name'] ?? '-', ENT_QUOTES, 'UTF-8');
                                $st = $itm['is_available'] ? '<i class="fas fa-check-circle" style="color:#28a745;"></i>' : '<i class="fas fa-times-circle" style="color:#dc3545;"></i>';
                                $itemRows .= '<tr><td style="font-size:0.7rem; padding:2px 4px;">'.$ic.'</td>'
                                           . '<td style="font-size:0.7rem; padding:2px 4px;">'.$cn.'</td>'
                                           . '<td style="font-size:0.7rem; padding:2px 4px;">'.$lo.'</td>'
                                           . '<td style="font-size:0.7rem; padding:2px 4px; text-align:center;">'.$st.'</td></tr>';
                            }
                        ?>
                        <div class="detail-avail-row biblio-avail-wrap" data-loc="<?= $locSafe ?>"<?= $hiddenClass ?>>
                            <div class="avail-loc-callno">
                                <span class="avail-loc-name"><?= $locSafe ?></span>
                            </div>
                            <span class="avail-count <?= $countClass ?>">
                                <?php if ($locData['available'] > 0): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php endif; ?>
                                <?= $locData['available'] ?>/<?= $locData['total'] ?>
                            </span>

                            <?php if (!empty($itemRows)): ?>
                            <div class="biblio-avail-popover detail-avail-popover">
                                <div class="detail-avail-popover-title">Detail Item (<?= $locData['total'] ?>) — <?= $locSafe ?></div>
                                <table class="detail-avail-popover-table">
                                    <thead>
                                        <tr><th>Kode</th><th>No. Panggil</th><th>Lokasi</th><th style="text-align:center;">Status</th></tr>
                                    </thead>
                                    <tbody><?= $itemRows ?></tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($totalLocations > $maxLocationRows): ?>
                    <div style="text-align: center; margin-top: 4px;">
                        <button type="button" class="btn btn-link btn-sm p-0" id="btnShowMoreLoc" style="font-size: 0.7rem; color: #6c757d; text-decoration: none;">
                            <i class="fas fa-chevron-down mr-1"></i><?= sprintf(__('Show %d more locations'), $totalLocations - $maxLocationRows) ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>



                <?php else: ?>
                    <p style="font-size: 0.8125rem; color: #6c757d; padding: 0 8px;"><?= __('No copy data'); ?></p>
                <?php endif; ?>
            </div>

        </div>

        <!-- ========== RIGHT: Detail Info ========== -->
        <div class="detail-main-content px-md-4 pt-2">

            <!-- Collection Type Badge + Share Button -->
            <?php
            $share_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                          . '://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8')
                          . '/index.php?p=show_detail&id=' . (int)$biblio_id;
            $share_title = htmlspecialchars($displayMainTitle, ENT_QUOTES, 'UTF-8');
            ?>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 0.75rem;">
            <?php if (!empty($first_item['coll_type_name'])): ?>
            <span style="display: inline-flex; align-items: center; background: #e9ecef; color: #495057; font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 12px;">
                <i class="fas fa-bookmark" style="margin-right: 3px; color: #6c757d;"></i><?= htmlspecialchars($first_item['coll_type_name'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <?php endif; ?>
            <!-- Share button -->
            <div class="detail-share-wrap" style="position:relative; display:inline-flex;">
                <button type="button" class="detail-share-btn" title="Bagikan"
                        data-url="<?= $share_url ?>"
                        data-title="<?= $share_title ?>"
                        aria-label="Bagikan halaman ini"
                        style="display:inline-flex; align-items:center; gap:5px; background:#fff; border:1px solid #dee2e6; color:#495057; font-size:0.75rem; font-weight:600; padding:3px 10px; border-radius:12px; cursor:pointer; transition:background .15s;">
                    <i class="fas fa-share-alt" style="font-size:0.75rem;"></i><span>Bagikan</span>
                </button>
                <!-- Fallback dropdown (shown when Web Share API unavailable) -->
                <div class="detail-share-menu" role="menu"
                     style="display:none; position:absolute; top:calc(100% + 6px); left:0; z-index:1000; background:#fff; border:1px solid #dee2e6; border-radius:10px; min-width:180px; box-shadow:0 4px 16px rgba(0,0,0,.12); padding:6px 0;">
                    <a class="detail-share-item" href="https://wa.me/?text=<?= rawurlencode($share_title . ' — ' . $share_url) ?>"
                       target="_blank" rel="noopener noreferrer"
                       style="display:flex; align-items:center; gap:8px; padding:8px 14px; font-size:0.8rem; color:#212529; text-decoration:none;">
                        <i class="fab fa-whatsapp" style="color:#25d366; width:16px;"></i> WhatsApp
                    </a>
                    <a class="detail-share-item" href="https://t.me/share/url?url=<?= rawurlencode($share_url) ?>&text=<?= rawurlencode($share_title) ?>"
                       target="_blank" rel="noopener noreferrer"
                       style="display:flex; align-items:center; gap:8px; padding:8px 14px; font-size:0.8rem; color:#212529; text-decoration:none;">
                        <i class="fab fa-telegram-plane" style="color:#0088cc; width:16px;"></i> Telegram
                    </a>
                    <div style="border-top:1px solid #f1f3f5; margin:4px 0;"></div>
                    <button type="button" class="detail-share-copy"
                            data-url="<?= $share_url ?>"
                            style="display:flex; align-items:center; gap:8px; padding:8px 14px; font-size:0.8rem; color:#212529; background:none; border:none; width:100%; cursor:pointer; text-align:left;">
                        <i class="fas fa-link" style="color:#6c757d; width:16px;"></i> Salin tautan
                    </button>
                </div>
            </div>
            </div>
            <script>
            (function(){
              var allWraps = document.querySelectorAll('.detail-share-wrap');
              allWraps.forEach(function(wrap){
                var btn  = wrap.querySelector('.detail-share-btn');
                var menu = wrap.querySelector('.detail-share-menu');
                var copyBtn = wrap.querySelector('.detail-share-copy');
                if (!btn) return;
                btn.addEventListener('click', function(e){
                  e.stopPropagation();
                  var url   = btn.dataset.url;
                  var title = btn.dataset.title;
                  if (navigator.share) {
                    navigator.share({ title: title, url: url }).catch(function(){});
                  } else {
                    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                  }
                });
                if (copyBtn) {
                  copyBtn.addEventListener('click', function(){
                    var url = this.dataset.url;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                      navigator.clipboard.writeText(url).then(function(){
                        copyBtn.innerHTML = '<i class="fas fa-check" style="color:#28a745; width:16px;"></i> Tersalin!';
                        setTimeout(function(){ copyBtn.innerHTML = '<i class="fas fa-link" style="color:#6c757d; width:16px;"></i> Salin tautan'; }, 2000);
                      });
                    } else {
                      var ta = document.createElement('textarea');
                      ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
                      document.body.appendChild(ta); ta.select();
                      document.execCommand('copy');
                      document.body.removeChild(ta);
                      copyBtn.innerHTML = '<i class="fas fa-check" style="color:#28a745; width:16px;"></i> Tersalin!';
                      setTimeout(function(){ copyBtn.innerHTML = '<i class="fas fa-link" style="color:#6c757d; width:16px;"></i> Salin tautan'; }, 2000);
                    }
                    menu.style.display = 'none';
                  });
                }
              });
              document.addEventListener('click', function(){
                document.querySelectorAll('.detail-share-menu').forEach(function(m){ m.style.display='none'; });
              });
            })();
            </script>

            <!-- Main Title -->
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; line-height: 1.35; margin-bottom: 0.25rem;">
                <?= htmlspecialchars($displayMainTitle, ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <!-- Parallel Title (separated) -->
            <?php if ($displayParallelTitle !== ''): ?>
                <div style="font-size: 1rem; color: #495057; font-style: italic; margin-bottom: 0.5rem; padding-left: 0.75rem; border-left: 3px solid #dee2e6;">
                    <?= htmlspecialchars($displayParallelTitle, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Authors (INLINE: "Pengarang: Name1 ; Name2") -->
            <?php
            $authArr = explode('; ', str_replace('<br />', '; ', $authors));
            $authLinks = [];
            foreach ($authArr as $au) {
                $au = trim(strip_tags($au));
                if (!empty($au)) {
                    $authLinks[] = '<a href="?search=Search&author=' . urlencode($au) . '" style="color: #0056b3; text-decoration: none;">'
                        . htmlspecialchars($au, ENT_QUOTES, 'UTF-8') . '</a>';
                }
            }
            if (!empty($authLinks)):
            ?>
            <div style="font-size: 0.875rem; color: #495057; margin-bottom: 1rem; line-height: 1.6;">
                <i class="fas fa-user-edit" style="margin-right: 4px; color: #6c757d;"></i><strong style="color: #495057;"><?= __('Author'); ?>:</strong>
                <?= implode(' <span style="color:#adb5bd;">;</span> ', $authLinks); ?>
            </div>
            <?php endif; ?>

            <!-- Description / Notes -->
            <?php if (!empty($notes)): ?>
            <div style="background: #f8f9fa; border-radius: 8px; padding: 0.875rem 1rem; margin-bottom: 1.25rem; border: 1px solid #e9ecef;">
                <div style="font-size: 0.75rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.375rem;">
                    <i class="fas fa-align-left" style="margin-right: 4px;"></i><?= __('Description'); ?>
                </div>
                <div style="font-size: 0.875rem; color: #495057; line-height: 1.6;">
                    <?= $notes; ?>
                </div>
            </div>
            <?php else: ?>
            <p style="font-size: 0.875rem; color: #adb5bd; font-style: italic; margin-bottom: 1.25rem;">
                <i class="fas fa-info-circle" style="margin-right: 4px;"></i><?= __('Description Not Available'); ?>
            </p>
            <?php endif; ?>

            <!-- Metadata Table -->
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #343a40; margin-bottom: 0.75rem;">
                <i class="fas fa-info-circle" style="color: #6c757d; margin-right: 6px;"></i><?= __('Detail Information'); ?>
            </h2>
            <table class="table table-striped detail-meta-table" style="border-radius: 8px; overflow: hidden;">
            <tbody>
               <?php if (isValuePresent($publisher_name)): ?>
               <tr>
                   <th><?= __('Publisher'); ?></th>
                   <td>
                       <?php if (isValuePresent($publish_place)): ?>
                       <span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope>
                           <?= htmlspecialchars(trim($publish_place), ENT_QUOTES, 'UTF-8'); ?>:
                       </span>
                       <?php endif; ?>
                       <span itemprop="publisher" property="publisher">
                           <a href="?search=Search&publisher=<?= urlencode($publisher_name); ?>">
                               <?= htmlspecialchars($publisher_name, ENT_QUOTES, 'UTF-8'); ?>
                           </a>
                       </span>
                       <?php if (isValuePresent($publish_year)): ?>
                       <span itemprop="datePublished" property="datePublished"> <?= $publish_year; ?></span>
                       <?php endif; ?>
                   </td>
               </tr>
               <?php endif; ?>
               <?php if (isValuePresent($edition)): ?>
               <tr><th><?= __('Edition'); ?></th><td><?= $edition; ?></td></tr>
               <?php endif; ?>
               <?php if (!empty($inline_subjects)): ?>
               <tr><th><?= __('Subject(s)'); ?></th><td style="line-height: 1.8;"><?= $inline_subjects; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($isbn_issn)): ?>
               <tr><th><?= __('ISBN/ISSN'); ?></th><td><?= $isbn_issn; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($classification)): ?>
               <tr><th><?= __('Classification'); ?></th><td><?= $classification; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($collation)): ?>
               <tr><th><?= __('Collation'); ?></th><td><?= $collation; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($spec_detail_info ?? '')): ?>
               <tr><th><?= __('Specific Detail Info'); ?></th><td><?= $spec_detail_info; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($related ?? '')): ?>
               <tr><th><?= __('Other Version/Related'); ?></th><td><?= $related; ?></td></tr>
               <?php endif; ?>
               <?php if (isValuePresent($file_att ?? '')): ?>
               <tr><th><?= __('File Attachment'); ?></th><td><?= $file_att; ?></td></tr>
               <?php endif; ?>
                <?php if (isset($biblio_custom) && count($biblio_custom) > 0): ?>
                    <?php foreach ($biblio_custom as $item): ?>
                        <?php if (isValuePresent($item['value'])): ?>
                            <tr>
                                <th><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                                <td><?= htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Availability interaction JS -->
<script>
(function(){
    // Show more locations toggle
    var btnMore = document.getElementById('btnShowMoreLoc');
    if (btnMore) {
        btnMore.addEventListener('click', function() {
            var hidden = document.querySelectorAll('[data-avail-hidden="1"]');
            hidden.forEach(function(el) { el.style.display = ''; el.removeAttribute('data-avail-hidden'); });
            btnMore.style.display = 'none';
        });
    }
})();
</script>