<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: language.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('themeTranslate')) {
  /**
   * Translate a string between English and Indonesian based on current active language.
   * Source keys are always in English.
   *
   * @param string $string English source text
   * @param string|null $lang Optional explicit language code ('en_US', 'id_ID')
   * @return string Translated string
   */
  function themeTranslate($string, $lang = null) {
    if ($string === null || $string === '') return '';

    // Check if SLiMS gettext __() translated it to something different than source
    if (function_exists('__')) {
      $translated = __($string);
      if ($translated !== $string) {
        return $translated;
      }
    }

    // Determine current language from cookie, session, or sysconf
    if (empty($lang)) {
      $lang = $_COOKIE['select_lang'] ?? $_SESSION['select_lang'] ?? $GLOBALS['sysconf']['default_lang'] ?? 'en_US';
    }

    $is_id = (strpos(strtolower((string)$lang), 'id') === 0 || strtolower((string)$lang) === 'indonesia');

    // Dictionary of English keys => Indonesian translations
    static $dict = [
      // Theme Viewer & Palette Switcher
      'Theme Viewer' => 'Penguji Tema',
      'Hero Section Fullscreen Mode' => 'Mode Fullscreen Hero Section',
      'Yes - Fullscreen Hero' => 'Ya - Hero Fullscreen',
      'No - Standard Homepage' => 'Tidak - Homepage Standar',
      'Topics in Fullscreen Hero' => 'Topics di Hero Fullscreen',
      'Show Topics in Fullscreen Hero' => 'Tampilkan Topics di Hero Fullscreen',
      'Keep Topics Below Search' => 'Pertahankan Topics di Bawah Search',
      'Hero Background Style' => 'Gaya Background Hero',
      'Background Style' => 'Gaya Background',
      'Custom Background Style' => 'Gaya Background Kustom',
      'Image Treatment' => 'Perlakuan Gambar',
      'Image Size' => 'Ukuran Gambar',
      'Image Position' => 'Posisi Gambar',
      'Image Filter' => 'Filter Gambar',
      'Image Blur' => 'Blur Gambar',
      'Image Overlay' => 'Overlay Gambar',
      'Recommended: Crop + Readable + Adaptive Overlay' => 'Rekomendasi: Crop + Readable + Overlay Adaptif',
      'Normal (Original Size)' => 'Normal (Ukuran Asli)',
      'Crop / Cover' => 'Crop / Cover',
      'Contain (Fit)' => 'Contain (Sesuaikan)',
      'Stretch / Fill' => 'Stretch / Fill',
      'Tile / Repeat' => 'Tile / Ulangi',
      'Full Width' => 'Lebar Penuh',
      'Full Height' => 'Tinggi Penuh',
      'Center' => 'Tengah',
      'Top' => 'Atas',
      'Bottom' => 'Bawah',
      'Left' => 'Kiri',
      'Right' => 'Kanan',
      'Top Left' => 'Kiri Atas',
      'Top Right' => 'Kanan Atas',
      'Bottom Left' => 'Kiri Bawah',
      'Bottom Right' => 'Kanan Bawah',
      'No Filter' => 'Tanpa Filter',
      'Soft' => 'Lembut',
      'Readable' => 'Mudah Dibaca',
      'Vivid' => 'Vivid',
      'Monochrome' => 'Monokrom',
      'Warm' => 'Hangat',
      'Cool' => 'Dingin',
      'No Blur' => 'Tanpa Blur',
      'No Overlay' => 'Tanpa Overlay',
      'Adaptive Overlay' => 'Overlay Adaptif',
      'Subtle' => 'Halus',
      'Readable Overlay' => 'Overlay Readable',
      'Dim' => 'Redup',
      'Accent Tint' => 'Tint Aksen',
      'Frosted' => 'Frosted',
      'Use one CSS background expression, or separate light and dark values with |.' => 'Gunakan satu ekspresi background CSS, atau pisahkan nilai light dan dark dengan |.',
      'Home Section Layout' => 'Layout Section Beranda',
      'Tab Mode' => 'Mode Tab',
      'Standard Mode' => 'Mode Standar',
      'Soft Gradient' => 'Gradient Lembut',
      'Aurora Glow' => 'Aurora Glow',
      'Mesh Light' => 'Mesh Light',
      'Solid Theme' => 'Warna Solid Tema',
      'Minimal Surface' => 'Surface Minimalis',
      'Explore more content' => 'Jelajahi konten lainnya',
      'Palette' => 'Palette Warna',
      'Theme Font' => 'Font Tema',
      'Background Animation' => 'Animasi Background',
      'Animation Speed' => 'Kecepatan Animasi',
      'Cursor Particles' => 'Partikel Cursor',
      'Cursor Icon' => 'Ikon Cursor',
      'Home Section' => 'Section Beranda',
      'Running Text' => 'Running Text',
      'Show all sections' => 'Tampilkan semua section',
      'Hide all sections' => 'Sembunyikan semua section',
      'Custom Palette Colors' => 'Warna Palette Kustom',
      'Apply' => 'Terapkan',
      'Click the Copy Prompt button below to generate color code via ChatGPT/AI, or fill in manually with format:' => 'Klik tombol Copy Prompt di bawah untuk generate kode warna via ChatGPT/AI, atau isi manual dengan format:',
      'Toggle dark/light mode' => 'Beralih mode gelap/terang',
      'Copy Prompt' => 'Salin Prompt AI',
      'Paste Palette' => 'Tempel Palette',
      'Copy Background Prompt' => 'Salin Prompt Background',
      'Paste Background' => 'Tempel Background',
      'Copy Image Prompt' => 'Salin Prompt Gambar',
      'Background pasted' => 'Background tertempel',
      'Use Copy Image Prompt to request a seamless, lightweight image.' => 'Gunakan Copy Image Prompt untuk meminta gambar seamless yang ringan.',
      'Generate Background via AI (ChatGPT/Gemini/AI Lainnya):' => 'Generate background via AI (ChatGPT/Gemini/AI lainnya):',
      'Quick steps:' => 'Langkah singkat:',
      'Copy the prompt, ask an AI for one valid line, then paste the result here.' => 'Salin prompt, minta AI membuat satu baris yang valid, lalu tempel hasilnya di sini.',
      'Reset' => 'Reset Tema',
      'Copied' => 'Tersalin',
      'Pasted' => 'Tertempel',
      'Clipboard unavailable' => 'Papan klip tidak tersedia',
      'Open theme palette menu' => 'Buka menu penguji tema',
      'Dark mode' => 'Mode gelap',
      'Light mode' => 'Mode terang',
      'Save Theme Settings' => 'Simpan Pengaturan Tema',
      'Checking administrator access...' => 'Memeriksa akses administrator...',
      'Log in to the admin area to save theme settings.' => 'Login ke area admin untuk menyimpan pengaturan tema.',
      'Saving theme settings...' => 'Menyimpan pengaturan tema...',
      'Theme settings saved. Reload the OPAC to apply them permanently.' => 'Pengaturan tema tersimpan. Muat ulang OPAC agar diterapkan permanen.',
      'Theme settings could not be saved. Please check your admin session.' => 'Pengaturan tema tidak dapat disimpan. Periksa sesi admin Anda.',

      // Navbar Settings
      'Navbar Menu' => 'Menu Navbar',
      'Member Area in Navbar' => 'Area Anggota di Navbar',
      'Default Member Page' => 'Halaman Default Area Anggota',
      'Digital Card' => 'Kartu Digital',
      'Current Loans' => 'Pinjaman Terkini',
      'Bookmarked Titles' => 'Judul Tertandai',
      'Title Basket' => 'Keranjang Judul',
      'Loan History' => 'Sejarah Peminjaman',
      'My Account' => 'Akun Saya',
      'Digital Card: Visible Fields (comma separated)' => 'Kartu Digital: Tampilkan Field (pisahkan dengan koma)',
      'Digital Card: Code Type' => 'Kartu Digital: Tipe Kode',
      'Logo & Library Name Position (Desktop View)' => 'Posisi Logo & Nama Perpustakaan (Desktop View)',
      'Logo & name in Navbar (default)' => 'Logo & nama di Navbar (default)',
      'Logo & name above Search Box (Desktop)' => 'Logo & nama di atas Search Box (Desktop)',
      'Library Subtitle in Navbar' => 'Subnama Perpustakaan di Navbar',
      'Visible Languages' => 'Bahasa yang Ditampilkan',
      'Mobile Bottom Navbar' => 'Navbar Mobile Bawah',

      // Hero & Search Bar
      'Search Title Text' => 'Teks Judul Search',
      'Search Title Text Size' => 'Ukuran Teks Judul Search',
      'Search Box Size' => 'Ukuran Search Box',
      'Search Placeholder' => 'Placeholder Search',
      'Background Animation Speed' => 'Kecepatan Animasi Background',
      'Cursor Particle Effect' => 'Efek Partikel Cursor',
      'Auto (Device Detection)' => 'Auto (Deteksi Perangkat)',
      'Light' => 'Ringan',
      'Medium' => 'Sedang',
      'Optimal' => 'Optimal',
      'Disabled' => 'Nonaktif',
      'Default Browser' => 'Default Browser',

      // Content & Sections
      'Running Text Position' => 'Posisi Running Text',
      'Running Text Source' => 'Sumber Running Text',
      'Latest Content (News/Info)' => 'Konten Terbaru (Berita/Info)',
      'Latest Bibliography (Books/Items)' => 'Koleksi Terbaru (Buku/Item)',
      'Custom Text (Manual Input)' => 'Teks Kustom (Ketik Manual)',
      'Custom Running Text' => 'Teks Kustom Running Text',
      'Running Text Content Filter' => 'Filter Konten Running Text',
      'All Content' => 'Semua Konten',
      'News Only' => 'Hanya Berita',
      'Running Text Content Detail' => 'Detail Konten Running Text',
      'Title Only' => 'Hanya Judul',
      'Title and Content Excerpt' => 'Judul dan Kutipan Konten',
      'Running Text Collection Filter' => 'Filter Koleksi Running Text',
      'All Collection Types' => 'Semua Tipe Koleksi',
      'Running Text Speed' => 'Kecepatan Running Text',
      'Fast (12s)' => 'Cepat (12s)',
      'Normal (18s)' => 'Normal (18s)',
      'Slow (32s)' => 'Lambat (32s)',
      'Very Slow (52s)' => 'Sangat Lambat (52s)',
      'Running Text Item Limit' => 'Jumlah Item Running Text',
      'Running Text Character Limit (0 for unlimited)' => 'Batas Karakter Running Text (0 untuk tidak terbatas)',
      'Hero Info Search Area Position' => 'Posisi Info Area Search (Hero Info)',
      'Topics Section' => 'Section Topics',
      'Latest Content Section' => 'Section Konten Terbaru',
      'Popular Collections Section' => 'Section Koleksi Populer',
      'New Collections Section' => 'Section Koleksi Terbaru',
      'Top Reader Section' => 'Section Pembaca Teratas',

      // Maps & Footer
      'Map & Social Media Section' => 'Section Peta & Sosial Media',
      'Show Map and Social Media' => 'Tampilkan Peta dan Sosial Media',
      'Hide Map and Social Media' => 'Sembunyikan Peta dan Sosial Media',
      'Hide Map' => 'Sembunyikan Peta',
      'Hide Social Media' => 'Sembunyikan Sosial Media',
      'Map Iframe Link' => 'Link Iframe Peta',
      'Map Height (px)' => 'Tinggi Peta (px)',
      'Map / Contact Description' => 'Deskripsi Peta / Kontak',

      // Librarian & Display Settings
      'Displayed Librarians' => 'Pustakawan yang Ditampilkan',
      'All' => 'Semua',
      'Librarians + Senior Librarians' => 'Pustakawan + Pustakawan Senior',
      'Senior Librarians Only' => 'Pustakawan Senior Saja',
      'Custom Username' => 'Username Custom',
      'Custom Librarian Usernames' => 'Username Custom Pustakawan',
      'Default Search Result View' => 'Tampilan Default Hasil Pencarian',
      'Panel Background Style' => 'Gaya Background Panel',
      'Transparent' => 'Transparan',
      'Solid' => 'Solid',
      'News / Information List View' => 'Tampilan List Berita / Informasi',
      'Title and Excerpt' => 'Judul dan Kutipan',
      'Title, Excerpt, and Thumbnail' => 'Judul, Kutipan, dan Thumbnail',
      'Auto Generate Blank Book Covers' => 'Auto Generate Cover Buku Kosong',
      'No cover and missing files' => 'Tanpa cover & file hilang',
      'No cover only' => 'Tanpa cover saja',
      'Breadcrumbs Navigation' => 'Navigasi Breadcrumbs',
      'Label Above Book Title (Detail Page)' => 'Label di Atas Judul Buku (Halaman Detail)',
      'Collection Type' => 'Tipe Koleksi',
      'Show Author Role/Type' => 'Tampilkan Peran/Tipe Pengarang',
      'Main Title Character Limit' => 'Batas Karakter Judul Utama',
      'Parallel Title Separator Character' => 'Karakter Pemisah Judul Paralel',

      // Visitor Log Settings
      'Visitor Log Voice' => 'Suara Visitor Log',
      'Enable' => 'Aktif',
      'Disable' => 'Nonaktif',
      'Visitor Page Greeting Quote' => 'Kutipan Salam Visitor Page',
      'Kiosk Page Main Title' => 'Judul Utama Halaman Kiosk',
      'Visitor Kiosk Page Subtitle' => 'Sub-judul Halaman Kiosk Visitor',
      'Visitor Faculty / Institution Label' => 'Label Pilihan Fakultas / Institusi Visitor',
      'Visitor Faculty / Institution List' => 'Daftar Pilihan Fakultas / Institusi Visitor',
      'Visitor Page Dark Mode Toggle Button' => 'Tombol Toggle Mode Gelap di Halaman Visitor',
      'Visitor Page Design (Guestbook)' => 'Desain Halaman Visitor (Buku Tamu)',
      'Kiosk Mode (Center Card with Large Clock)' => 'Kiosk Mode (Kartu Tengah dengan Jam Besar)',
      'Split Layout (Left Form & Right Guide)' => 'Split Layout (Form Kiri & Petunjuk Kanan)',
      'Visitor Split Layout Guide Title' => 'Judul Petunjuk Layout Split Visitor',
      'Visitor Split Layout Guide Steps' => 'Langkah Petunjuk Layout Split Visitor',

      // General Settings
      'Dark/Light Mode Button' => 'Tombol Dark/Light Mode',
      'Auto - Show Button (System Mode)' => 'Auto - Tampilkan Tombol (Mengikuti Sistem)',
      'Auto - Hide Button (System Mode)' => 'Auto - Sembunyikan Tombol (Mengikuti Sistem)',
      'Default Dark - Show Button' => 'Default Dark - Tampilkan Tombol',
      'Default Dark - Hide Button' => 'Default Dark - Sembunyikan Tombol',
      'Default Light - Show Button' => 'Default Light - Tampilkan Tombol',
      'Default Light - Hide Button' => 'Default Light - Sembunyikan Tombol',
      'Back to Top Button' => 'Tombol Kembali ke Atas',
      'Floating Info Button' => 'Tombol Info Melayang',
      'Show Library Info (Libinfo)' => 'Tampilkan Info Perpustakaan (Libinfo)',
      'WhatsApp Mode' => 'Mode WhatsApp',
      'WhatsApp Number (with Country Code)' => 'Nomor WhatsApp (dengan Kode Negara)',
      'WhatsApp Service Title' => 'Judul Layanan WhatsApp',
      'Service Hours' => 'Jam Layanan',
      'Short WhatsApp Description' => 'Deskripsi Singkat WhatsApp',
      'WhatsApp Message Template' => 'Template Pesan WhatsApp',

      // Navigation & General
      'Home' => 'Beranda',
      'Member Area' => 'Area Anggota',
      'Visitor Portal' => 'Portal Pengunjung',
      'Information' => 'Informasi',
      'News' => 'Berita',
      'Help' => 'Bantuan',
      'Librarian Login' => 'Masuk Pustakawan',
      'My Card' => 'Kartu Saya',
      'Bookmarks' => 'Markah Buku',
      'History' => 'Riwayat',
      'Logout' => 'Keluar',
      'More' => 'Lainnya',
      'More Menu' => 'Menu Lainnya',
      'Select Language' => 'Pilih Bahasa',
    ];

    if ($is_id) {
      return $dict[$string] ?? $string;
    }

    return $string;
  }
}
