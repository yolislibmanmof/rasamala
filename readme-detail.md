<!-- markdownlint-disable MD013 -->

# 🌲 Dokumentasi Detail & Spesifikasi Teknis - Rasamala Theme

Dokumen ini berisi spesifikasi teknis lengkap, referensi konfigurasi TInfo, arsitektur arsitektur berkas, rute OPAC, daftar aset lokal, panduan keamanan/aksesibilitas, dan riwayat pembaruan UI/UX untuk **Rasamala Theme for SLiMS 9 Bulian**.

Dokumentasi ringkas perkenalan fitur utama tersedia di [`README.md`](README.md).

---

## 📑 Daftar Isi

- [⚡ Detail Sorotan Fitur Teknis](#-detail-sorotan-fitur-teknis)
  - [1. Desain Visual, Theme Viewer & Mode Gelap](#1-desain-visual-theme-viewer--mode-gelap)
  - [2. Performa dan Instalasi Portabel](#2-performa-dan-instalasi-portabel)
  - [3. Mobile UI/UX & Engine Modal Teratas](#3-mobile-uiux--engine-modal-teratas)
  - [4. Pencarian Katalog, Detail Buku & Sitasi](#4-pencarian-katalog-detail-buku--sitasi)
  - [5. Keanggotaan Digital, Buku Tamu & Widget Layanan](#5-keanggotaan-digital-buku-tamu--widget-layanan)
- [🎛️ Panduan Pengaturan Tema (Tinfo) & Referensi Lengkap](#️-panduan-pengaturan-tema-tinfo--referensi-lengkap)
- [🔁 Sinkronisasi Theme Viewer dan Tinfo](#-sinkronisasi-theme-viewer-dan-tinfo)
- [📁 Arsitektur Direktori Lengkap](#-arsitektur-direktori-lengkap)
- [🌐 Route OPAC Utama & Parameter](#-route-opac-utama--parameter)
- [📦 Aset Lokal dan Versi Library](#-aset-lokal-dan-versi-library)
- [🔒 Keamanan, Sanitasi & Aksesibilitas (WCAG)](#-keamanan-sanitasi--aksesibilitas-wcag)
- [📝 Pembaruan UI/UX Terbaru & Changelog](#-pembaruan-uiux-terbaru--changelog)

---

## ⚡ Detail Sorotan Fitur Teknis

### 1. Desain Visual, Theme Viewer & Mode Gelap

- **Kustomisasi Visual Lengkap (Tinfo):** Atur mode hero fullscreen, skema warna, logo, running text, hingga animasi latar belakang langsung dari admin SLiMS.
- **Dark / Light Mode Pintar:** Otomatis menyesuaikan preferensi OS/browser pengguna atau dikunci pada mode tertentu, lengkap dengan tombol toggle responsif.
- **Interactive Theme Viewer:** Pemustaka dapat menguji mode hero fullscreen (Yes/No), pilihan konten di dalam hero, section beranda, *Custom Palette*, preset background, tipografi, dan animasi secara *real-time* melalui menu kuas melayang (`fa-paint-brush`). Editor CSS background kustom tidak ditampilkan di Theme Viewer; opsi `Custom Background Style` tetap dikelola melalui TInfo admin. Kontrol yang berada dalam release gate didokumentasikan di [`docs/smoke-test-release-gate.md`](docs/smoke-test-release-gate.md).
- **Simpan dari Theme Viewer (Admin):** Setelah login di area admin, tombol **Simpan Pengaturan Tema** muncul di Theme Viewer. Tombol ini mengirim konfigurasi preview ke form TInfo resmi dengan tetap membawa seluruh field lain, sehingga pengaturan yang tidak sedang dipreview tidak ikut terhapus.
- **Sistem Logo Perpustakaan Bersyarat Admin:** Logo perpustakaan hanya tampil jika admin mengunggah file logo di Pengaturan Sistem Admin SLiMS (`$sysconf['logo_image']`), menjaga kebersihan tampilan jika belum ada logo.

### 2. Performa dan Instalasi Portabel

- **Pemuatan JS Non-Blocking:** Sebagian besar script tema dan library halaman dimuat dengan atribut `defer`, termasuk bundle pencarian, Theme Viewer, animasi, dan cleanup worker lama. Script core atau partial tertentu tetap mengikuti kebutuhan kompatibilitas SLiMS, jadi klaim ini bukan berarti setiap tag script di seluruh alur core selalu `defer`.
- **Aset Berdasarkan Halaman:** Vue dan `app.js` hanya untuk halaman dengan form pencarian; Masonry, Ion Slider, highlight, serta `result_search.js` hanya untuk halaman hasil pencarian. CSS CKEditor dan Colorbox tidak lagi dimuat di OPAC publik.
- **Autocomplete JSON Ringan:** Saran judul memakai endpoint JSON kecil (maksimal 6 judul), debounce 250 ms, dan pembatalan request sebelumnya. Browser tidak lagi mengunduh serta mem-parsing halaman hasil pencarian penuh pada setiap input.
- **Instalasi Satu Folder:** Rasamala tidak mendaftarkan service worker dan tidak membutuhkan file di luar `template/rasamala/`. Script cleanup lokal menghapus registrasi dan cache Rasamala dari rilis lama secara selektif.
- **Default Produksi:** Snapshot database aktif memakai Theme Viewer, hero fullscreen dengan Topics di dalam hero, background `Zen Bamboo & Drifting Leaves`, animasi `Floating Embers` berkecepatan `Fast`, homepage tab aktif, dan partikel cursor nonaktif. Admin dapat mengubahnya melalui Tinfo. Release gate tetap memaksa Cursor Icon, Cursor Particles, dan Panel Background ke nilai aman masing-masing.
- **Skeleton Shimmer Loading System:** Animasi shimmer presisi saat memuat data (sampul 1:1.35, avatar, pill topik) dengan indikator top progress bar NProgress-style.

### 3. Mobile UI/UX & Engine Modal Teratas

- **Mobile Bottom Navigation (5-Tombol):** Bilah navigasi bawah seluler responsif untuk akses cepat ke Beranda, Pencarian, Keranjang, Topik, dan Area Anggota.
- **Mobile Floating Action Pill (Kiri Bawah):** Kapsul aksi melayang (*Bookmark, Keranjang, Sitasi, Bagikan*) yang otomatis di-promosikan ke `document.body` (`z-index: 1045`). Mengambang secara mulus dan stabil di atas footer.
- **Top-Level Modal Hierarchy (`z-index: 100000`):** Penanganan modal teratas untuk Sort, Filter, View Mode, Advanced Search (`#adv-modal`), dan Floating WhatsApp (`#whatsappModal`), menjamin dialog selalu muncul di atas backdrop overlay.

### 4. Pencarian Katalog, Detail Buku & Sitasi

- **Full-Width Result Layout & Sticky Action Toolbar:** Tampilan hasil pencarian 100% *full-width* dengan toolbar melayang (*Filter, Sort by 8 pilihan, View Mode Simple/List/Grid*).
- **Status Hasil yang Konsisten:** Empty state menggunakan jumlah hasil mesin pencarian, tombol Advanced Search selalu membuka `#adv-modal`, dan status ketersediaan mengikuti angka item yang tersedia.
- **Generator Kode QR Vektor SVG Offline:** Generator QR Code 100% offline (`BaconQrCode\Writer`) tanpa ketergantungan API pihak ketiga, terintegrasi dengan **Native Mobile Web Share Sheet (`navigator.share`)**.
- **Modul Sitasi Akademis & Keranjang Buku:** Generator sitasi otomatis format **APA, Chicago, MLA, Turabian** pada detail buku, serta keranjang buku permanen (*Shopping Basket*) dengan counter AJAX real-time.
- **Global Keyboard Shortcut (`Ctrl+K` / `⌘K`):** Pintasan keyboard instan untuk memfokuskan pencarian dengan indikator visual `<kbd>`.

### 5. Keanggotaan Digital, Buku Tamu & Widget Layanan

- **Kartu Anggota Digital Cerdas:** Mendukung QR Code & Barcode, generator inisial avatar jika foto tidak ada, serta indikator visual **border merah melingkar (`4px solid #dc3545`)** untuk anggota expired.
- **Visitor Log Kiosk & Split Layout:** Form buku tamu mode Kiosk penuh atau Split Layout 2-kolom dengan dukungan format institusi `kode(label)` dan opsi input manual (`other`). Dependensi dimuat berurutan: Vue, Axios, lalu `visitor_counter.js`.
- **Widget Layanan WhatsApp & Waktu Sholat:** Modal obrolan WhatsApp interaktif dengan template pesan otomatis, serta jadwal waktu sholat kota-kota di Indonesia lengkap dengan pop-up reminder azan.

---

## 🎛️ Panduan Pengaturan Tema (Tinfo) & Referensi Lengkap

Akses menu admin SLiMS: **System > Theme > Customize/Tinfo**.

| Kategori Pengaturan | Opsi Utama | Deskripsi Ringkas |
| --- | --- | --- |
| **Hero Fullscreen** | `Yes - Fullscreen Hero`, `No - Standard Homepage` | Mengatur apakah area pencarian memenuhi layar. Saat ada konten di bawah, indikator panah membantu pemustaka menemukan section berikutnya. Topics, info search, dan section beranda mengikuti pengaturan show/hide masing-masing. |
| **Inside Fullscreen Hero** | `None - Keep Below Search`, `Topics`, `Popular among our collections`, `New collections + updated`, `Top Reader of the Year` | Menentukan satu konten yang dirender di dalam hero saat mode fullscreen aktif. `None` mempertahankan konten di bawah search. Pada homepage standar, konten hero-inside tidak dipasang ulang. |
| **Background Style** | `None / Standard`, `Soft Gradient`, `Aurora Glow`, `Ocean Waves`, `Aurora Neon Borealis`, `Galaxy Nebula`, `Quantum Cyber Mesh`, `Zen Bamboo & Drifting Leaves`, `Floating Books & Scattered Flying Pages`, `Library Desk & Lamp`, `Emerald Shelf & Plants`, `Cosmic Constellation Books`, `Aurora Wave Ribbons`, `Memphis Retro Pattern`, `Terrazzo Speckle`, `Custom`, serta gambar di `assets/images/backgrounds/` | Pilihan background diterapkan ke seluruh halaman dan tetap mengikuti token warna aktif. Tiga gaya referensi baru (Aurora Wave Ribbons, Memphis Retro Pattern, dan Terrazzo Speckle) dirender sebagai layer SVG inline agar otomatis menyesuaikan light/dark theme colors. Untuk gambar tersedia opsi Crop/Cover, Contain, Stretch, Tile, filter, blur, dan overlay. |
| **Theme Viewer Home Sections** | Topics, Latest Content, Popular Collections, New Collections, Top Reader, Footer | Saat Theme Viewer aktif, section dapat disembunyikan/ditampilkan dan disusun untuk preview. Map/Social Media memiliki kontrol terpisah pada `Map Visibility`; Running Text dan Info Search juga memiliki kontrol khusus. |
| **Home Section Layout** | `Tab Mode`, `Standard Mode` | Memilih satu panel tab aktif agar halaman ringkas, atau menampilkan seluruh section beranda yang diaktifkan secara berurutan. Saat berpindah mode, pane nonaktif tetap disembunyikan sehingga kartu tidak terduplikasi. |
| **Color Palette** | `Warm Gray`, `Minimal White`, `Dark Gray`, `Warm Library`, `Contemporary Tech Library`, `Deep Emerald & Bronze`, `Kraft Paper & Charcoal Ink`, `Midnight Navy & Gold Accent`, `Modern Charcoal & Warm Saffron`, `Royal Indigo & Bright Gold`, `Scandinavian Minimalist`, `High-Contrast Digital`, `Minimal Electric Lime`, `Warm Greige & Metallic Gold`, `Custom` | Pilihan kombinasi warna siap pakai atau custom palette 14-warna. Definisi preset tersentral di `helpers/palettes/theme_color_palettes.php`. |
| **Dark / Light Mode** | `Auto`, `Default Dark`, `Default Light` (with/without toggle) | Pengaturan mode tampilan default dan tombol pengubah mode. |
| **Header & Logo** | `Navbar`, `Hero Box`, Subname, Menu Navigation | Posisi penempatan logo/nama perpustakaan dan struktur navigasi atas. |
| **Hero & Search Box** | Hero Title, Title Size, Placeholder, Search Box Size, Info Search Display, Fullscreen Placement | Seluruh kontrol hero yang berdampak langsung ke OPAC tersedia di Theme Viewer dengan preview real-time; konten Popular/New/Top Reader memakai komponen homepage yang sama agar tidak berbeda saat disimpan. |
| **Animasi & Kursor** | `None`, Floating Glyphs, Code Rain, Moving Grid, Twinkling Stars, Zen Ripples, Neural Network, Starfield Warp, Floating Embers | Animasi background dan kecepatannya tersedia di Theme Viewer/Tinfo. Cursor Icon dan Cursor Particles didefinisikan di kode, tetapi kontrolnya disembunyikan sementara melalui release gate dan dipaksa ke `default`/`none`. |
| **Simpan Theme Viewer** | Khusus sesi admin | Menyimpan palette, hero/search, info area, running text, mobile navbar, back-to-top, background treatment, layout tab, font, animasi, dan visibility section ke pengaturan template aktif. Fitur yang sedang di-release gate tidak ikut ditampilkan/disimpan dari kontrol publik. |
| **All TInfo Settings di Theme Viewer** | General, Navbar, Hero, Content, Footer, Display, Visitor/Guestbook | Theme Viewer memuat metadata langsung dari `helpers/options/*.php`. Field yang belum memiliki preview khusus tersedia di panel **All TInfo Settings** dengan pencarian, dikelompokkan per kategori, dan perubahan langsung diterapkan ke DOM preview tanpa Save. Nilai dikonfirmasi ulang melalui form TInfo agar tidak ada pengaturan yang hilang atau tertimpa. |
| **Pengumuman & Ticker** | Announcement Banner (5 style), Running Text Ticker | Banner pesan penting di atas pencarian dan running text di bagian bawah. |
| **Section Manager** | Show/Hide section, Reorder Sections, Title Format, News Display Mode | Status tampil section dapat diatur dari TInfo atau langsung dipromosikan dari checklist Theme Viewer. |
| **Peta, Medsos & Footer** | Map Embed URL, Social Media Links, Footer Search, About Us Text | Peta/lini media sosial dapat dipreview sebagai kombinasi `Map + Social`, `Map only`, `Social only`, atau disembunyikan dari Theme Viewer; URL dan deskripsi tetap mengikuti TInfo. |
| **Waktu Sholat** | `Footer + Floating Reminder`, `Footer Only`, `Reminder Only`, `Hide` | Jadwal sholat kota-kota Indonesia dan countdown pengingat azan. |
| **Visitor Log** | `Kiosk Mode`, `Split Layout`, Institution List, Panel HTML | Format buku tamu pengunjung dan instruksi pengisian. |
| **Floating Action & WA** | `WhatsApp Mode`, `Libinfo Mode`, `Hide`, Number, Hours, Template | Tombol layanan melayang dan template awal pesan WhatsApp. |

---

## 🔁 Sinkronisasi Theme Viewer dan Tinfo

- Definisi field admin dan field yang dibaca Theme Viewer berasal dari file yang sama di `helpers/options/*.php`; kategori yang tersedia adalah **General & Layout**, **Navigation Bar**, **Hero & Background**, **Homepage & Content**, **Footer & Social Media**, **Display & Detail Pages**, dan **Visitor / Guestbook**.
- Theme Viewer publik menerapkan perubahan sebagai preview lokal di browser. Perubahan tersebut tidak ditulis ke database secara otomatis.
- Tombol **Simpan Pengaturan Tema** hanya muncul setelah sesi admin terdeteksi. Penyimpanan dilakukan melalui form TInfo resmi agar field lain tetap dibawa dan tidak terhapus.
- `helpers/theme_feature_flags.php` menyaring field yang sedang berada dalam release gate. Daftar gate dan checklist verifikasinya dipusatkan di [`docs/smoke-test-release-gate.md`](docs/smoke-test-release-gate.md). Saat gate aktif, nilai efektifnya adalah `classic_search_panel_style=solid`, `classic_cursor_custom_icon=default`, dan `classic_cursor_particles=none`.
- Preview yang tersimpan di browser dapat dihapus dengan membersihkan data situs untuk origin OPAC. Nilai database tetap menjadi sumber konfigurasi produksi.

---

## 📁 Arsitektur Direktori Lengkap

```text
rasamala/
├── index_template.inc.php       # Router/template utama OPAC publik
├── detail_template.php          # Template halaman detail bibliografi
├── biblio_list_template.php     # Template daftar katalog hasil pencarian
├── news_template.php            # Format halaman berita/informasi
├── visitor_template.php          # Halaman visitor log/kiosk
├── login_template.inc.php       # Wrapper halaman login tertentu
├── classic.php                  # Controller & Bootstrap tema Rasamala
├── tinfo.inc.php                # Entry point pengaturan tema admin
├── theme_helpers.php            # Loader helper bersama
├── helpers/                     # 📂 Helper PHP, opsi Tinfo, preset, security, dan UI
│   ├── options/                 # 📂 Skema field TInfo tersentral
│   ├── palettes/                # 📂 Preset palet warna tema (14+ preset)
│   ├── ui/                      # 📂 Helper rendering UI (text, navbar, header, footer)
│   ├── background.php           # 📂 Generator layer & efek background SVG/Canvas
│   ├── member.php               # 📂 Sanitizer & helper area anggota
│   ├── security.php             # 📂 Sanitizer XSS, CSP nonce, & SQL prep
│   ├── tinfo_defaults.php       # 📂 Fallback nilai default TInfo produksi
│   └── visitor.php              # 📂 Logika & endpoint visitor counter
├── parts/                       # 📂 UI Partials (Header, Footer, Navbar, Search, Modals, Detail)
│   ├── detail/                  # 📂 Komponen halaman detail (fields, sidebar)
│   ├── member/                  # 📂 Komponen area anggota & digital card
│   ├── visitor/                 # 📂 Komponen visitor log (kiosk, split, ticker)
│   ├── background_layers.php    # 📂 Layer background render
│   ├── floating_actions.php     # 📂 Floating action pill & WhatsApp modal
│   ├── modals.php               # 📂 Unified top-level modals (Adv search, share, topic)
│   └── mobile_bottom_nav.php    # 📂 Navigation bar seluler 5-tombol
├── citation/                    # 📂 Modul Sitasi (APA, Chicago, MLA, Turabian)
├── assets/                      # 📂 Aset statis lokal (CSS, JS, fonts, images, manifest)
└── docs/                        # 📂 Dokumentasi & Laporan Audit Internal
```

---

## 🌐 Route OPAC Utama & Parameter

| Route | Fungsi & Deskripsi |
| --- | --- |
| `index.php` | Homepage katalog |
| `index.php?search=search&keywords=...` | Hasil pencarian (Query keywords, author, subject, isbn) |
| `index.php?p=show_detail&id=...` | Detail bibliografi (Format utuh judul tanpa batas karakter) |
| `index.php?p=news` | Daftar berita/informasi perpustakaan |
| `index.php?p=librarian` | Daftar pustakawan |
| `index.php?p=visitor` | Visitor log/kiosk (Kiosk / Split layout) |
| `index.php?p=member` | Area anggota (Kartu digital, peminjaman, reservasi) |
| `index.php?p=login` | Login staf melalui alur core SLiMS |

---

## 📦 Aset Lokal dan Versi Library

Semua asset tema disimpan lokal dan tidak bergantung pada CDN untuk rendering utama.

| Library/Asset | Versi/Status | Penggunaan |
| --- | --- | --- |
| jQuery | 3.6.4 | Kompatibilitas dan helper UI |
| Vue | 3.5.39 | Koleksi homepage dan visitor UI |
| Axios | 1.19.0 | POST visitor log melalui `visitor_counter.js` |
| Bootstrap | 5.3.3 | Grid, modal, dropdown, dan komponen UI |
| Masonry | 4.2.2 | Layout hasil pencarian |
| `detail_page.js` | Asset tema lokal | Progress bar, availability popover, dan native lightbox |
| `service-worker-cleanup.js` | Asset tema lokal | Menghapus worker dan cache Rasamala dari rilis lama |

---

## 🔒 Keamanan, Sanitasi & Aksesibilitas (WCAG)

- **SQL Prepared Statements:** Query yang menerima ID, path, filter tipe koleksi, topic, atau username menggunakan `Prepared Statement` (`$dbs->prepare`) dan menutup statement setelah dipakai. Query yang tersisa dengan `$dbs->query()` hanya berisi konstanta internal tanpa input pengguna.
- **Sanitasi Data & Proteksi XSS:** Output HTML memakai escaping UTF-8, CSS melalui `themeSanitizeCustomCss()`, HTML melalui sanitizer/HTMLPurifier, URL melalui helper allowlist, serta atribut asset core melalui whitelist.
- **Native Lightbox Aman:** Preview gambar memakai DOM API, memvalidasi protocol `http/https` dan ekstensi pada pathname, lalu menetapkan `img.src` sebagai property DOM tanpa merangkai HTML dari `href`.
- **CSP Nonce:** Inline script/style tema memakai nonce per-request. Inline script core yang dilewatkan sanitizer juga diberi nonce; `unsafe-inline`/`unsafe-eval` masih dipertahankan untuk kompatibilitas library core SLiMS yang belum dimigrasikan.
- **Tanpa Service Worker Tema:** Rasamala tidak mengintersepsi atau menyimpan respons HTML, API, pencarian, area anggota, maupun admin. Cleanup hanya menargetkan registrasi dan cache bernama Rasamala dari rilis lama.
- **Dependency lokal:** Library pihak ketiga disimpan lokal, sehingga tidak ada kebutuhan CDN untuk tampilan utama dan versi dapat diaudit dari header asset.
- **Aksesibilitas WCAG & WAI-ARIA:** Structure semantik HTML5 (`<main>`, `<nav>`, `<footer>`), target sentuh seluler minimum **48x48px** dengan efek touch ripple, atribut `aria-label` & `<label class="visually-hidden">` untuk screen reader, serta atribut `inert` pada modal tertutup.

---

## 📝 Pembaruan UI/UX Terbaru & Changelog

- **Preservasi Parameter URL saat Ganti Bahasa:** Opsi pemilih bahasa pada navbar desktop (`parts/_navbar.php`) dan mobile dropdown (`assets/js/app_jquery.js`) kini mempertahankan seluruh query parameter halaman (`$_GET`), sehingga tidak me-redirect pengguna kembali ke home.
- **Restorasi Keranjang Judul (`sec=title_basket`):** Mengembalikan alur *native rendering* SLiMS core untuk keranjang judul sehingga pengubahan bahasa (English / Bahasa Indonesia) berfungsi 100% sempurna dengan terjemahan kamus resmi SLiMS.
- **Pembersihan Escaped Backslashes (`Sawyer's`):** Menambahkan `stripslashes()` pada breadcrumb, judul halaman, meta tags, serta kartu koleksi homepage (Vue Web Components `<slims-collection>`) sehingga karakter petik tidak lagi menampilkan `\`.
- **Judul Utuh pada Detail Buku (`show_detail`):** Menghapus limitasi pemotongan karakter judul (`themeParallelTitleHtml`) khusus konteks detail buku sehingga judul panjang dapat tampil utuh 100%.
- **Spinner pencarian stabil:** Submit ganda melalui tombol/Enter dicegah; state spinner di-reset saat halaman dipulihkan dari cache browser, navigasi gagal, atau timeout.
- **Sidebar detail terpusat:** `.detail-sidebar-col` beserta cover, heading ketersediaan, lokasi, dan jumlah eksemplar diratakan ke tengah pada desktop maupun seluler.
- **Homepage section tabs (opsional):** Tinfo > `Compact Homepage Sections as Tabs` dapat menggabungkan Popular Collections, New Collections, Top Reader, serta Map/Social Media menjadi tab ringkas.
