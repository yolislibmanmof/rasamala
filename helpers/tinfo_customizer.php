<?php
/**
 * Helper Module for Rasamala Template - Admin Customizer Assets Loader
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('rasamalaTinfoCustomizeAssets')) {
  function rasamalaTinfoCustomizeAssets()
  {
    global $sysconf;

    $theme_dir = 'rasamala';
    $template_dir = $sysconf['template']['dir'] ?? 'template';
    $asset_base = (defined('SWB') ? SWB : '') . $template_dir . '/' . $theme_dir . '/assets/';
    $fontawesome_css = htmlspecialchars($asset_base . 'plugin/font-awesome/css/fontawesome-all.min.css', ENT_QUOTES, 'UTF-8');
    $flag_icon_css = htmlspecialchars($asset_base . 'css/flag-icon.min.css', ENT_QUOTES, 'UTF-8');

    $tinfo_customizer_css_path = dirname(__DIR__) . '/assets/css/tinfo-customizer.css';
    $tinfo_customizer_css_version = is_file($tinfo_customizer_css_path) ? filemtime($tinfo_customizer_css_path) : (defined('SENAYAN_VERSION') ? SENAYAN_VERSION : time());
    $tinfo_customizer_css = htmlspecialchars($asset_base . 'css/tinfo-customizer.css?v=' . $tinfo_customizer_css_version, ENT_QUOTES, 'UTF-8');

    $tinfo_customizer_js_path = dirname(__DIR__) . '/assets/js/tinfo-customizer.js';
    $tinfo_customizer_js_version = is_file($tinfo_customizer_js_path) ? filemtime($tinfo_customizer_js_path) : (defined('SENAYAN_VERSION') ? SENAYAN_VERSION : time());
    $tinfo_customizer_js = htmlspecialchars($asset_base . 'js/tinfo-customizer.js?v=' . $tinfo_customizer_js_version, ENT_QUOTES, 'UTF-8');

    include_once dirname(__DIR__) . '/theme_helpers.php';
    $palette_definitions = [];
    if (function_exists('themeAccentPalettes')) {
      foreach (themeAccentPalettes() as $palette_key => $palette_info) {
        $palette_definitions[$palette_key] = [
          'label' => $palette_info['label'] ?? $palette_key,
          'primary' => $palette_info['primary'] ?? '#6f5b43',
          'secondary' => $palette_info['secondary'] ?? '#a58a63',
          'accent' => $palette_info['accent'] ?? '#c8a24a',
          'background' => $palette_info['background'] ?? '#f4f1ec',
          'surface' => $palette_info['surface'] ?? '#ffffff',
          'text' => $palette_info['text'] ?? '#2f2a24',
          'muted' => $palette_info['muted'] ?? '#7a7167',
        ];
      }
    }

    $customizer_config = [
      'assetBase' => $asset_base,
      'iconOptions' => rasamalaTinfoTopicIconOptions(),
      'languageOptions' => rasamalaTinfoLanguageOptions(),
      'palettes' => $palette_definitions,
      'defaults' => [
        'announcement' => "<strong>Info layanan:</strong> Perpustakaan buka Senin-Jumat, pukul 08.00-16.00 WIB.\n<a href=\"index.php?p=libinfo\">Lihat informasi lengkap</a>.",
        'customCss' => "/* Custom CSS Rasamala\n   Edit contoh di bawah ini sesuai kebutuhan. */\n\n/* Contoh: ubah ukuran nama perpustakaan di navbar */\n/* .navbar-lib-name {\n  font-size: 14px !important;\n} */\n\n/* Contoh: beri jarak tambahan pada judul hero */\n/* .hero-search-heading h1 {\n  margin-bottom: 16px !important;\n} */\n\n/* Contoh: custom warna tombol utama */\n/* .btn-primary {\n  background-color: var(--theme-accent-color) !important;\n  border-color: var(--theme-accent-color) !important;\n} */",
        'visitorSteps' => "<div class=\"inst-step\">\n  <div class=\"inst-icon-box\"><i class=\"fas fa-id-card\"></i></div>\n  <div class=\"inst-content\">\n    <h3>1. Isi Identitas</h3>\n    <p>Scan kartu anggota atau ketik identitas pengunjung pada kolom yang tersedia.</p>\n  </div>\n</div>\n<div class=\"inst-step inst-step-featured\">\n  <div class=\"inst-icon-box\"><i class=\"fas fa-sync-alt\"></i></div>\n  <div class=\"inst-content\">\n    <h3>2. Proses Kunjungan</h3>\n    <p>Sistem akan memeriksa data dan menampilkan status kunjungan secara otomatis.</p>\n  </div>\n</div>\n<div class=\"inst-step\">\n  <div class=\"inst-icon-box\"><i class=\"fas fa-check\"></i></div>\n  <div class=\"inst-content\">\n    <h3>3. Selesai</h3>\n    <p>Setelah berhasil, pengunjung dapat melanjutkan aktivitas sesuai layanan yang tersedia.</p>\n  </div>\n</div>",
        'visitorInstitutions' => "feb(Fakultas Ekonomi dan Bisnis UI);ff(Fakultas Farmasi UI);fh(Fakultas Hukum UI);fia(Fakultas Ilmu Administrasi UI);fib(Fakultas Ilmu Budaya UI);fik(Fakultas Ilmu Keperawatan UI);fasilkom(Fakultas Ilmu Komputer UI);fisip(Fakultas Ilmu Sosial dan Ilmu Politik UI);fk(Fakultas Kedokteran UI);fkg(Fakultas Kedokteran Gigi UI);fkm(Fakultas Kesehatan Masyarakat UI);fmipa(Fakultas Matematika dan Ilmu Pengetahuan Alam UI);fpsi(Fakultas Psikologi UI);ft(Fakultas Teknik UI);vokasi(Program Vokasi UI);other"
      ],
      'backgroundPrompt' => "Buat 1 custom background CSS untuk OPAC perpustakaan. Output hanya 1 baris dengan format persis: LIGHT_BACKGROUND | DARK_BACKGROUND. Gunakan satu ekspresi CSS background yang aman, seperti linear-gradient(), radial-gradient(), conic-gradient(), color-mix(), atau var(--theme-primary). Light harus terang dan nyaman dibaca; Dark harus gelap dan tetap nyaman dibaca. Jangan gunakan URL, gambar eksternal, @import, selector, deklarasi CSS, tanda kurung kurawal, titik koma, script, atau markdown. Hindari animasi berat agar halaman tetap cepat. Tema visual yang diminta: [tulis konsep background di sini].",
      'backgroundImagePrompt' => "Buat 1 gambar background untuk OPAC perpustakaan dengan gaya modern, elegan, dan tenang. Utamakan pola atau tekstur seamless/tileable yang dapat di-loop mulus secara horizontal dan vertikal tanpa garis sambungan terlihat, sehingga cukup memakai satu aset kecil dengan CSS background-repeat. Sisakan area tengah yang bersih untuk teks dan kotak pencarian, tanpa tulisan, logo, wajah, atau detail yang mengganggu. Gunakan komposisi abstrak yang keren tetapi ringan: bentuk lembut, mesh, grain halus, atau motif geometris sederhana. Prioritaskan SVG atau AVIF/WebP terkompresi; jika raster, maksimal 1600x900 px, kualitas 70-80, target ukuran file di bawah 250 KB. Hindari GIF/video, frame animasi banyak, dan detail mikro yang membuat file besar. Output hanya prompt gambar, tanpa markdown atau penjelasan. Tema visual yang diminta: [tulis konsep warna dan gaya di sini]."
    ];

    $config_json = json_encode($customizer_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $csp_nonce = function_exists('themeCspNonce') ? themeCspNonce() : '';

    return <<<HTML
<link rel="stylesheet" href="{$fontawesome_css}">
<link rel="stylesheet" href="{$flag_icon_css}">
<link rel="stylesheet" href="{$tinfo_customizer_css}">
<script nonce="{$csp_nonce}">window.rasamalaCustomizerConfig = {$config_json};</script>
<script src="{$tinfo_customizer_js}"></script>
HTML;
  }
}
