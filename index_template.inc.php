<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: index_template.inc.php

if (isset($_GET['rasamala_suggest']) && (string)$_GET['rasamala_suggest'] === '1') {
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: private, max-age=30');
  header('X-Content-Type-Options: nosniff');

  $query = trim((string)($_GET['q'] ?? ''));
  $query = function_exists('mb_substr') ? mb_substr($query, 0, 80, 'UTF-8') : substr($query, 0, 80);
  $query = str_replace(['%', '_'], '', $query);
  $query_length = function_exists('mb_strlen') ? mb_strlen($query, 'UTF-8') : strlen($query);
  $suggestions = [];

  if ($query_length >= 2 && isset($dbs) && $dbs instanceof mysqli) {
    $statement = $dbs->prepare(
      "SELECT b.biblio_id, b.title,
              GROUP_CONCAT(DISTINCT ma.author_name ORDER BY ma.author_name SEPARATOR '; ') AS author
       FROM biblio AS b
       LEFT JOIN biblio_author AS ba ON ba.biblio_id = b.biblio_id
       LEFT JOIN mst_author AS ma ON ma.author_id = ba.author_id
       WHERE b.title LIKE CONCAT(?, '%')
       GROUP BY b.biblio_id, b.title
       ORDER BY b.last_update DESC
       LIMIT 6"
    );

    if ($statement) {
      $statement->bind_param('s', $query);
      $statement->execute();
      $result = $statement->get_result();
      while ($result && ($row = $result->fetch_assoc())) {
        $suggestions[] = [
          'title' => (string)($row['title'] ?? ''),
          'author' => (string)($row['author'] ?? '')
        ];
      }
      $statement->close();
    }
  }

  echo json_encode($suggestions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
  exit;
}

$imagesDisk = \SLiMS\Filesystems\Storage::images();

// setup list view
$available_list_views = ['simple', 'list', 'grid'];
$list_view_default_marker = 'rasamala-simple-default-20260708';
$req_view = $_POST['view'] ?? $_GET['view'] ?? null;

if ($req_view !== null && in_array((string)$req_view, $available_list_views, true)) {
    $_SESSION['LIST_VIEW'] = (string)$req_view;
    $_SESSION['RASAMALA_LIST_VIEW_DEFAULT'] = $list_view_default_marker;
} elseif (($_SESSION['RASAMALA_LIST_VIEW_DEFAULT'] ?? '') !== $list_view_default_marker) {
    $_SESSION['LIST_VIEW'] = 'simple';
    $_SESSION['RASAMALA_LIST_VIEW_DEFAULT'] = $list_view_default_marker;
} else {
    $_SESSION['LIST_VIEW'] = in_array(($_SESSION['LIST_VIEW'] ?? ''), $available_list_views, true)
        ? $_SESSION['LIST_VIEW']
        : 'simple';
}

// ----------------------------------------------------------------------------
// load function library for classic template
// ----------------------------------------------------------------------------
include_once 'classic.php';

// ----------------------------------------------------------------------------
// load header
// ----------------------------------------------------------------------------
include 'parts/header.php';

// ----------------------------------------------------------------------------
// load content by URI
// ----------------------------------------------------------------------------
?>
<main id="main-content" class="rasamala-main" role="main">
<?php
if (isset($_GET['p']) || isset($_GET['search'])) {
  // --------------------------------------------------------------------------
  // handle result search
  if (isset($_GET['search'])) {
    // ------------------------------------------------------------------------
    // load parts result search template
    include 'parts/_result-search.php';
  } else {
    // --------------------------------------------------------------------------
    // handle member page
    if (($_GET['p'] ?? '') === 'member') {
      include 'parts/_member.php';
    } else {
      include 'parts/_other.php';
    }
  }
} else {
  // --------------------------------------------------------------------------
  // not found query string: load home page
  include 'parts/_home.php';
}
?>
</main>
<?php

// ----------------------------------------------------------------------------
// load footer
// ----------------------------------------------------------------------------
include 'parts/footer.php';
