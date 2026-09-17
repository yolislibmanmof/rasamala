# Laporan Perubahan Template Rasamala

Tanggal laporan: 2026-07-22  
Waktu laporan diperbarui: 2026-07-24 16:12:36 WIB  
Lokasi pekerjaan: `template/rasamala`  
Konteks: review dan perbaikan template custom SLiMS Bulian/Rasamala.

## Scope Pekerjaan

Perubahan yang dilakukan pada sesi ini dibatasi ke folder `template/rasamala`.

Catatan pemeriksaan:

- `git status` di root project memang menampilkan banyak perubahan di luar `template/rasamala`, tetapi itu sudah ada di working tree dan tidak berasal dari operasi edit/hapus yang saya jalankan.
- Operasi edit, tambah file, dan hapus file yang saya lakukan berada di `template/rasamala`.
- Ada file cache runtime di luar Rasamala pada `images/cache/...` dan `files/cache/...`; saya tidak mengedit file tersebut dan tidak melakukan perubahan source di luar folder Rasamala.

## Ringkasan

Pekerjaan dilakukan dalam dua puluh tiga tahap utama:

1. Membuat file review template Rasamala berisi temuan bug, keamanan, performa, maintainability, aksesibilitas, dan ide peningkatan.
2. Memperbaiki seluruh temuan pada bagian "Temuan Bug Prioritas Tinggi".
3. Memperbaiki bagian "Temuan Keamanan dan Hardening".
4. Memperbaiki bagian "Performa dan Stabilitas".
5. Menjalankan saran "Maintainability" yang aman dilakukan langsung.
6. Memperbaiki bagian "Aksesibilitas dan UX".
7. Memindahkan inline CSS besar halaman visitor ke asset stylesheet khusus.
8. Memodularisasi JavaScript hasil pencarian dan mengaudit inline CSS/JS tersisa.
9. Memindahkan inline style attribute dari file aktif ke class CSS asset.
10. Mengunci warna ikon love pada tombol "Keep SLiMS Alive" agar tidak berubah saat light/dark mode atau hover/focus.
11. Mereview dan memperkuat implementasi custom palette agar warna teks, muted text, surface, dan background tetap terbaca meskipun input warna dari AI/manual buruk.
12. Menambahkan pengaturan label select institusi visitor melalui `tinfo` agar teks pilihan awal bisa diubah dari pengaturan tema.
13. Menambahkan pengaturan isi dropdown `selectInstitution` visitor melalui `tinfo`, termasuk dukungan opsi manual yang tidak lagi hardcoded di JS.
14. Mengubah pengaturan "Langkah Petunjuk Layout Split Visitor" menjadi isian HTML aman seperti "Tentang Kami di Footer".
15. Memperbarui README publik untuk GitHub agar sinkron dengan fitur, struktur asset, dan dokumentasi teknis terbaru.
16. Memoles tampilan hero/search homepage: judul tanpa shadow, search bar lebih bersih, tombol kontrol lebih konsisten, dan info/ticker bawah search lebih ringan.
17. Menghapus glow/spotlight global yang mengikuti cursor pada background animation layer, tanpa mengubah desain mode ikon cursor dan trail.
18. Memperbaiki background animation agar renderer dimuat di semua halaman, bukan hanya homepage.
19. Merapikan sort toolbar hasil pencarian agar lebih clean, modern, dan memakai chip/button yang konsisten.
20. Menstandarkan tombol aksi menjadi capsule dan pagination menjadi bulat.
21. Merapikan alur Custom Palette di Theme Viewer: Copy/Paste Prompt dipindah ke area custom palette dan Paste langsung menerapkan palette.
22. Merapikan pop-up kontrol Filter, Sort, dan View hasil pencarian agar lebih kecil, seragam, dan mengikuti theme.
23. Memperbaiki tampilan mobile show detail agar call number lebih fokus, availability lebih jelas, dan area title/action lebih clean.

## Linimasa Perubahan

| Waktu WIB | Kegiatan | File Terkait |
| --- | --- | --- |
| 07:11:36 | Membuat dokumen review awal template Rasamala. | `review-template-rasamala-2026-07-22.md` |
| 07:19:23 | Memperbaiki bug ketersediaan koleksi dan random biblio. | `biblio_list_template.php`, `classic.php` |
| 07:19:51 | Memperbaiki validasi view hasil pencarian dan kartu anggota. | `parts/_result-search.php`, `parts/_member.php` |
| 07:27:59 | Memperketat validasi thumbnail gambar berita. | `news_template.php` |
| 07:29:33 | Memindahkan dan memperketat CSP, serta mengganti sanitasi CSS/output JS di header. | `parts/header.php` |
| 07:29:51 | Menyamakan validasi URL menu di builder admin dengan aturan render publik. | `tinfo_options.inc.php` |
| 07:30:26 | Menambahkan helper keamanan terpusat untuk URL, CSS, image source, dan tag aset core. | `theme_helpers.php` |
| 07:31:50 | Menyusun laporan perubahan awal. | `laporan-perubahan-rasamala-2026-07-22.md` |
| 07:36:24 | Memperbaiki cache dan fallback API waktu sholat. | `parts/waktu_sholat.php` |
| 07:36:34 | Mengubah default widget waktu sholat menjadi nonaktif. | `tinfo_defaults.inc.php` |
| 07:36:44 | Menyesuaikan opsi admin waktu sholat dengan default aman. | `tinfo_options.inc.php` |
| 07:37:01 | Menambahkan fallback `ResizeObserver`. | `assets/js/app_jquery.js` |
| 07:37:48 | Membatasi dan menormalisasi riwayat kata kunci pencarian lokal. | `assets/js/app.js` |
| 07:38-07:40 | Membersihkan aset produksi yang tidak dipakai. | `assets/css`, `assets/js`, `assets/plugin/font-awesome` |
| 08:07:47 | Memindahkan Vue visitor counter ke file JS terpisah. | `assets/js/visitor_counter.js` |
| 08:09:07 | Mengganti inline config visitor menjadi JSON config dan memperbaiki state non-member. | `visitor_template.php` |
| 08:09:39 | Membuat indeks dokumen review/arsip. | `docs/INDEX.md` |
| 08:10:41 | Menghapus nested repository template dan menjalankan verifikasi ulang. | `.git`, PHP/JS checks |
| 08:31-08:34 | Memperbaiki reduced motion, label tombol, mobile filter, visitor kiosk, dan status kartu digital. | `theme-components.css`, `visitor_template.php`, `_result-search.php`, `_member.php`, JS animasi |
| 08:35:54 | Memperbarui laporan perubahan ini. | `laporan-perubahan-rasamala-2026-07-22.md` |
| 08:54:01 | Memindahkan inline CSS besar visitor ke stylesheet khusus dan memuatnya kondisional pada halaman visitor. | `assets/css/visitor.css`, `visitor_template.php`, `parts/header.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 09:10:20 | Memindahkan JavaScript hasil pencarian ke modul eksternal dan menjalankan audit inline CSS/JS pada file PHP. | `assets/js/result_search.js`, `parts/_result-search.php`, `assets/css/opac-pages.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 09:26:26 | Memindahkan inline style attribute dari file aktif ke class CSS asset. | `assets/css/opac-pages.css`, `assets/css/theme-components.css`, `assets/css/tinfo-customizer.css`, `parts/_member.php`, `parts/palette_switcher.php`, `detail_template.php`, `parts/_search-form.php`, `parts/_navbar.php`, `parts/floating_actions.php`, `tinfo_helpers.php`, `tinfo_options.inc.php` |
| 09:34:25 | Memperbaiki warna ikon love "Keep SLiMS Alive" agar stabil di light/dark mode. | `parts/footer.php`, `assets/css/foundation.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 09:54:25 | Review mendalam custom palette, normalisasi kontras, perbaikan prompt generate palette, dan guard CSS final. | `theme_helpers.php`, `parts/header.php`, `parts/palette_switcher.php`, `tinfo_helpers.php`, `assets/js/theme_viewer.js`, `assets/css/foundation.css`, `assets/css/theme-components.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 10:51:01 | Menambahkan pengaturan label select institusi visitor di tinfo. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `visitor_template.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 10:57:18 | Menambahkan pengaturan isi dropdown `selectInstitution` visitor di tinfo. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `tinfo_helpers.php`, `theme_helpers.php`, `visitor_template.php`, `assets/js/visitor_counter.js`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:02:13 | Mengubah format pemisah opsi dropdown institusi visitor menjadi titik koma. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `tinfo_helpers.php`, `theme_helpers.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:07:07 | Memperbaiki parser dropdown institusi agar format titik koma satu baris berhasil. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `tinfo_helpers.php`, `theme_helpers.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:13:19 | Mengubah format dropdown institusi visitor menjadi `kode(label);other`. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `tinfo_helpers.php`, `theme_helpers.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:23:49 | Mengubah pengaturan langkah petunjuk split visitor menjadi HTML seperti field footer. | `tinfo_defaults.inc.php`, `tinfo_options.inc.php`, `tinfo_helpers.php`, `theme_helpers.php`, `visitor_template.php`, `assets/css/visitor.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:29:31 | Memperbaiki HTML polos `<br>` pada langkah petunjuk split visitor. | `visitor_template.php`, `tinfo_options.inc.php`, `assets/css/visitor.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 11:51:38 | Memperbarui README publik untuk GitHub dengan preview, sorotan fitur, format visitor terbaru, daftar asset modular, catatan keamanan, dan tautan dokumentasi. | `README.md`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 15:20:59 | Memoles tampilan hero/search homepage agar judul tidak memakai shadow dan kontrol pencarian terlihat lebih rapi. | `assets/css/theme-components.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 16:02:03 | Menghapus glow/spotlight global yang mengikuti cursor pada background animation layer. | `assets/js/hero_animation.js`, `assets/css/theme-components.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 08:39:46 | Memperbaiki background animation agar berjalan di semua halaman template, bukan hanya homepage. | `parts/footer.php`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 08:47:18 | Merapikan toolbar sort hasil pencarian menjadi panel modern dengan chip wrapping dan dropdown view yang lebih clean. | `parts/_result-search.php`, `assets/js/result_search.js`, `assets/css/opac-pages.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 08:58:53 | Menstandarkan tombol aksi menjadi capsule dan pagination menjadi bulat penuh. | `assets/css/foundation.css`, `assets/css/opac-pages.css`, `assets/css/theme-components.css`, `assets/css/visitor.css`, `assets/css/tinfo-customizer.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 09:07:27 | Memindahkan Copy Prompt dan Paste Palette ke area Custom Palette Colors serta membuat paste langsung apply. | `parts/palette_switcher.php`, `assets/js/theme_viewer.js`, `assets/css/theme-components.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 11:55:40 | Memperkuat auto-apply Custom Palette saat paste dan membuat parser paste lebih toleran terhadap output AI. | `assets/js/palette_switcher.js`, `assets/js/theme_viewer.js`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 15:25:19 | Meratakan tampilan show detail agar tanpa gradasi dan shadow, sambil mempertahankan border luar serta border tombol bookmark/share dan author. | `assets/css/opac-pages.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 15:33:27 | Merapikan toolbar ringkasan hasil pencarian agar lebih kecil, compact, dan mengikuti token theme/custom palette. | `parts/_result-search.php`, `assets/js/result_search.js`, `assets/css/foundation.css`, `assets/css/opac-pages.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 15:36:21 | Memperbaiki dimensi generated cover di show detail agar berbentuk buku, bukan kotak. | `assets/css/opac-pages.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 15:52:20 | Memperbesar cover buku di show detail pada mobile agar tidak terlalu kecil, tetap responsif, dan tetap berbentuk buku. | `assets/css/opac-pages.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 15:57:08 | Memperhalus call number dan availability row di sidebar show detail agar lebih smooth serta mengikuti theme. | `assets/css/opac-pages.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 16:04:12 | Merapikan pop-up Filter, Sort, dan View hasil pencarian agar lebih compact, tematik, dan tidak bergantung pada utility Bootstrap besar. | `parts/_result-search.php`, `assets/js/result_search.js`, `assets/css/opac-pages.css`, `assets/css/theme-dark.css`, `laporan-perubahan-rasamala-2026-07-22.md` |
| 2026-07-24 16:12:36 | Memperbaiki mobile show detail: call number dibuat lebih fokus, availability lebih terbaca, tombol aksi lebih seragam, dan author chip lebih proporsional. | `assets/css/opac-pages.css`, `laporan-perubahan-rasamala-2026-07-22.md` |

Catatan waktu di atas diambil dari timestamp file dan catatan eksekusi pada sesi pengerjaan ini.

## Perbaikan Tampilan Hero/Search Homepage

File:

- `assets/css/theme-components.css`
- `assets/css/theme-dark.css`

Perubahan:

- Menghapus efek `text-shadow` pada judul hero/search, termasuk saat animasi background aktif.
- Menghapus glow/drop-shadow pada logo hero agar identitas perpustakaan terlihat lebih bersih.
- Merapikan search box homepage dengan border, radius, focus ring, dan shadow yang lebih halus.
- Mengubah ikon advanced search menjadi tombol ghost berbentuk lingkaran agar setara dengan tombol search.
- Menyesuaikan tombol search supaya memakai token `--theme-accent` dan `--theme-on-accent`, sehingga tetap terbaca pada custom palette.
- Merapikan pill/latest content dan ticker bawah search agar lebih ringan, tidak terlalu bercahaya, dan konsisten di light/dark mode.

Dampak:

- Area hero terlihat lebih bersih seperti permintaan pengguna.
- Teks utama tidak lagi berbayang sehingga lebih tajam pada background terang.
- Kontrol search tetap responsif di desktop dan mobile.

## Perbaikan Cursor Tanpa Glow

File:

- `assets/js/hero_animation.js`
- `assets/css/theme-components.css`
- `assets/css/theme-dark.css`

Perubahan:

- Menghapus listener `mousemove` global yang mengisi variable `--mouse-x` dan `--mouse-y`.
- Menghapus pseudo-element `background-animation-layer::after` yang membuat radial spotlight mengikuti pointer.
- Menghapus interaksi koordinat mouse pada mode background `neural-network`, `starfield-warp`, dan `zen-ripples` agar tidak ada garis/ripple yang menempel pada cursor.
- Menghapus override dark mode untuk spotlight tersebut.
- Mempertahankan desain mode ikon cursor dan trail/partikel sesuai pengaturan tema.

Dampak:

- Halaman tidak lagi menampilkan glow di sekitar cursor saat ikon cursor/trail tidak aktif.
- Background animation tetap berjalan, tetapi tidak lagi memiliki spotlight mouse-follow yang mengganggu.

## Perbaikan Background Animation Global

File:

- `parts/footer.php`

Perubahan:

- Mengubah pemuatan `assets/js/hero_animation.js` dari kondisi homepage-only menjadi global saat animasi background aktif.
- Script juga tetap dimuat saat palette switcher aktif, agar animasi bisa dinyalakan dari panel tema walaupun default awal `none`.

Dampak:

- Background animation tidak lagi terbatas di homepage.
- Halaman hasil pencarian, detail, member, dan halaman OPAC lain dapat memakai layer background animation yang sudah dirender dari header.

## Perapihan Sort Toolbar Hasil Pencarian

File:

- `parts/_result-search.php`
- `assets/js/result_search.js`
- `assets/css/opac-pages.css`
- `assets/css/theme-dark.css`

Perubahan:

- Mengganti wrapper sort desktop lama menjadi `search-sort-bar` yang lebih terstruktur.
- Mengelompokkan label, chip sort, dan dropdown view ke class khusus agar tidak bergantung pada utility Bootstrap berlebihan.
- Mengubah chip sort hasil pencarian ke `search-sort-chip` dengan state aktif, hover, focus, dan `aria-current`.
- Merapikan dropdown pilihan view dengan ikon dan label yang lebih konsisten.
- Menambahkan dukungan dark mode dan mode panel transparan untuk toolbar baru.

Dampak:

- Area sort hasil pencarian lebih clean, modern, dan mudah dipindai.
- Chip sort bisa wrap tanpa membuat panel terlihat berat.
- Markup dan JS lebih modular karena gaya toolbar tidak lagi tersebar di inline class utilitas.

## Standarisasi Tombol Capsule dan Pagination Bulat

File:

- `assets/css/foundation.css`
- `assets/css/opac-pages.css`
- `assets/css/theme-components.css`
- `assets/css/visitor.css`
- `assets/css/tinfo-customizer.css`

Perubahan:

- Menambahkan token radius terpusat `--rasamala-radius-button` dan `--rasamala-radius-pagination`.
- Mengarahkan radius Bootstrap button theme ke token capsule.
- Mengubah tombol aksi lokal seperti `btn-news-readmore`, `btn-modern-filter`, `detail-link-btn`, `btn-visitor-checkin`, tombol member/login, tombol footer, tombol libinfo, dan tombol palette ke bentuk capsule.
- Mengubah filter chip, sort chip, latest content link, tombol builder Tinfo, dan pilihan interaktif palette menjadi capsule.
- Mengubah pagination `.pagingList` dan `.pagination .page-link` menjadi tombol bulat 36px.
- Menjadikan tombol first/prev/next/last pada `.pagingList` bulat penuh karena sudah diganti menjadi ikon oleh `assets/js/app_jquery.js`.
- Menyesuaikan footer search sebagai satu kontrol pill: input sisi kiri dan button sisi kanan memakai radius capsule.
- Mempertahankan tombol ikon floating, social button, search submit icon, close button, dan color/palette floating sebagai lingkaran.
- Tidak mengubah radius card, panel, input, dropdown menu, badge, dan row interaktif karena elemen tersebut bukan tombol aksi.

Dampak:

- Bahasa visual tombol lebih konsisten: CTA/aksi teks berbentuk capsule, tombol ikon tetap bulat, dan pagination numerik menjadi bulat.
- Risiko visual regression lebih kecil karena perubahan dibatasi ke selector tombol/aksi yang jelas.
- Token radius pusat membuat perubahan bentuk tombol berikutnya cukup dilakukan dari `foundation.css`.

## Perapihan Custom Palette Theme Viewer

File:

- `parts/palette_switcher.php`
- `assets/js/theme_viewer.js`
- `assets/css/theme-components.css`

Perubahan:

- Memindahkan tombol `Copy Prompt` dan `Paste Palette` dari footer action panel ke header area `Custom Palette Colors`.
- Menghapus tombol `Apply` dari UI Custom Palette.
- Mengubah tombol `Paste Palette` agar langsung sanitize, normalisasi kontras, memilih palette `custom`, menyimpan ke localStorage, dan menerapkan warna.
- Menambahkan auto-apply saat pengguna paste langsung ke textarea custom palette melalui event `paste` dan `input` bertipe paste/drop.
- Menambahkan apply saat textarea custom palette berubah dan focus keluar, sehingga input manual tetap bisa digunakan tanpa tombol Apply.
- Memperbaiki parser paste agar bisa mengekstrak 7 atau 14 warna hex dari output AI yang berisi markdown, code block, atau penjelasan tambahan.
- Memastikan pilihan `Custom Palette` tetap menerapkan fallback custom palette jika textarea belum berisi nilai valid.
- Merapikan layout tombol custom palette agar tetap rapi pada panel kecil/mobile.

Dampak:

- Alur custom palette lebih singkat: copy prompt, paste hasil AI, langsung lihat hasil.
- Tombol terkait custom palette hanya muncul ketika palette `Custom Palette` aktif.
- Footer action panel Theme Viewer lebih ringan karena hanya menyisakan mode toggle dan reset.
- Tombol Apply tidak dikembalikan karena paste dan paste manual sudah menjadi aksi apply utama.

## Pembaruan Dokumentasi GitHub

File:

- `README.md`

Perubahan:

- Menambahkan preview image untuk tampilan GitHub.
- Memperbarui tanggal dokumentasi menjadi 2026-07-22.
- Menambahkan ringkasan sorotan cepat.
- Menyamakan daftar preset palette, animasi background, dan ikon cursor dengan opsi aktif di `tinfo_options.inc.php`.
- Menambahkan dokumentasi format dropdown institusi visitor `kode(label);other`.
- Menambahkan contoh HTML sederhana dan contoh kartu lengkap untuk petunjuk Split Layout visitor.
- Memperbarui daftar struktur file penting agar mencakup asset CSS/JS modular terbaru.
- Memperjelas catatan keamanan terkait sanitizer HTML, sanitasi custom CSS, validasi URL, dan CSP kompatibilitas.
- Menambahkan tautan ke dokumen teknis di folder `docs/`.

Dampak:

- README lebih siap untuk ditampilkan di GitHub.
- Pengguna baru mendapat petunjuk konfigurasi visitor dan custom palette yang sesuai implementasi terbaru.
- Dokumentasi publik tidak lagi menyebut opsi palette, animasi, dan cursor lama yang sudah tidak ada di Tinfo.

## Dokumen Review Awal

File dibuat:

- `review-template-rasamala-2026-07-22.md`

Isi utama dokumen:

- Ringkasan kondisi template Rasamala.
- Temuan bug prioritas tinggi.
- Temuan keamanan dan hardening.
- Performa dan stabilitas.
- Maintainability.
- Aksesibilitas dan UX.
- Ide peningkatan fitur.
- Rencana aksi bertahap.

## Perbaikan Bug Prioritas Tinggi

### 1. Status Ketersediaan Koleksi

File:

- `biblio_list_template.php`

Perubahan:

- Mode grid sekarang memakai sumber data ketersediaan yang sama dengan mode simple/list.
- Hitungan availability sekarang memakai `rasamalaGetItemsAndAvailability()`.
- Helper `getAvailability()` dibuat tetap kompatibel, tetapi didelegasikan ke logika availability baru.

Dampak:

- Item dengan status `no_loan` tidak lagi keliru terlihat tersedia di mode grid.
- Tampilan availability menjadi konsisten antar mode hasil pencarian.

### 2. Random Biblio Offset

File:

- `classic.php`

Perubahan:

- Mengganti kalkulasi offset random agar tidak memakai batas maksimum negatif.
- Menggunakan:

```php
$max_offset = max(0, $count - $limit);
$offset = function_exists('random_int') ? random_int(0, $max_offset) : mt_rand(0, $max_offset);
```

Dampak:

- `getRandomBiblio()` tidak memicu error saat jumlah koleksi lebih kecil dari limit.

### 3. Validasi View Hasil Pencarian

File:

- `parts/_result-search.php`

Perubahan:

- Menambahkan fallback untuk konfigurasi/session view yang invalid.
- Nilai view hanya boleh `simple`, `list`, atau `grid`.
- Jika nilai tidak valid, otomatis kembali ke `simple`.

Dampak:

- Menghindari notice `Undefined array key`.
- Toolbar view lebih aman saat konfigurasi lama atau session rusak.

### 4. Fallback Foto Member

File:

- `parts/_member.php`

Perubahan:

- `$_SESSION['m_image']` tidak lagi dibaca langsung tanpa fallback.
- Nama file foto member disanitasi dengan `basename()` dan regex.
- Jika foto kosong/tidak ada, fallback ke `person.png`.
- URL foto di-escape sebelum dirender.

Dampak:

- Menghindari warning `Undefined array key`.
- Mengurangi risiko path/file name injection pada avatar member.

### 5. Penggantian Konten Kartu Member

File:

- `parts/_member.php`

Perubahan:

- Menambahkan helper `rasamalaReplaceMemberCardContent()`.
- Penggantian konten kartu anggota tidak lagi bergantung pada regex pendek yang mudah salah saat ada nested `<div>`.

Dampak:

- Halaman `my_card` lebih stabil jika struktur HTML member area berubah.
- Risiko konten terpotong di penutup `</div>` yang salah menjadi lebih kecil.

## Perbaikan Keamanan dan Hardening

### 1. CSP di Header

File:

- `parts/header.php`

Perubahan:

- Meta CSP dipindahkan lebih awal di dalam `<head>`.
- Policy diperketat dengan:
  - `base-uri 'self'`
  - `object-src 'none'`
  - `form-action 'self'`
  - `font-src 'self' data:`
  - `connect-src 'self'`
  - `frame-src 'self' https://www.google.com https://maps.google.com`

Catatan:

- `unsafe-inline` masih dipertahankan karena template masih memiliki banyak inline script/style.
- `unsafe-eval` masih dipertahankan untuk kompatibilitas Vue runtime template compilation.

### 2. Sanitasi Custom CSS

File:

- `theme_helpers.php`
- `parts/header.php`

Perubahan:

- Menambahkan helper `themeSanitizeCustomCss()`.
- Custom CSS sekarang:
  - dibatasi panjangnya,
  - menghapus tag HTML,
  - memblokir `@import`,
  - memblokir `javascript:` dan `vbscript:`,
  - memblokir `expression()`,
  - memblokir `behavior:` dan `-moz-binding:`,
  - mengosongkan remote `url(http/https)` untuk mengurangi tracking/remote load dari CSS.

Dampak:

- Fitur custom CSS tetap tersedia, tetapi payload berisiko lebih banyak disaring.

### 3. Sanitasi Output `$js` dari Core

File:

- `theme_helpers.php`
- `parts/header.php`

Perubahan:

- Mengganti `strip_tags(..., '<script><link>')` dengan helper `themeSanitizeCoreAssetTags()`.
- Tag yang diizinkan hanya `script` dan `link`.
- Atribut event seperti `onload`, `onclick`, dan atribut `style` dibuang.
- URL aset dibatasi ke same-origin.
- External asset yang bukan domain saat ini ditolak.

Dampak:

- Extension point `$js` dari core masih berjalan, tetapi atribut/URL berisiko disaring lebih ketat.

### 4. Validasi Gambar Berita

File:

- `news_template.php`
- `theme_helpers.php`

Perubahan:

- `rasamalaNewsFirstImageSrc()` sekarang memakai `themeSafeContentImageSrc()`.
- `javascript:`, `vbscript:`, protocol-relative URL, dan external `http://` berisiko ditolak.
- `https://` tetap diizinkan.

Dampak:

- Thumbnail berita memakai sanitasi gambar yang lebih terpusat dan konsisten.

### 5. Validasi URL Menu Publik

File:

- `theme_helpers.php`
- `tinfo_options.inc.php`

Perubahan:

- `themeSafeMenuUrl()` sekarang menolak external `http://`.
- URL yang diizinkan:
  - URL relatif,
  - anchor `#...`,
  - `https://`,
  - `mailto:`,
  - `tel:`,
  - `http://` hanya untuk domain yang sama saat halaman tidak berjalan di HTTPS.
- Builder menu admin disesuaikan agar aturan validasi UI sama dengan aturan render publik.

Dampak:

- Menu publik lebih aman dari link eksternal non-HTTPS.
- Admin mendapat validasi yang lebih konsisten saat mengisi menu.

## Perbaikan Performa dan Stabilitas

### 1. Cache Waktu Sholat Lebih Tahan Gagal

File:

- `parts/waktu_sholat.php`

Perubahan:

- Cache sekarang menyimpan metadata status.
- Jika API gagal, widget dapat memakai cache stale yang masih ada.
- Request API diberi timeout pendek.
- Kegagalan API diberi throttle agar tidak dipanggil berulang-ulang pada setiap render.

Dampak:

- Halaman tidak terlalu bergantung pada API eksternal.
- Risiko render lambat saat jaringan/API bermasalah lebih kecil.

### 2. Default Widget Waktu Sholat Dinonaktifkan

File:

- `tinfo_defaults.inc.php`
- `tinfo_options.inc.php`

Perubahan:

- Default `classic_prayer_times_show` diubah menjadi `hide`.
- Opsi admin disesuaikan supaya nilai default mencerminkan perilaku baru.

Dampak:

- Instalasi baru tidak otomatis memanggil API eksternal sebelum admin mengaktifkannya.

### 3. Fallback Resize Observer

File:

- `assets/js/app_jquery.js`

Perubahan:

- Menambahkan fallback berbasis event `resize` dan `orientationchange` saat browser tidak mendukung `ResizeObserver`.

Dampak:

- UI tetap stabil di browser lama.

### 4. Pembatasan Riwayat Pencarian Lokal

File:

- `assets/js/app.js`

Perubahan:

- Riwayat kata kunci pencarian di `localStorage` dinormalisasi.
- Jumlah item dibatasi 25.
- Panjang keyword dibatasi 120 karakter.
- Data rusak/non-array otomatis difallback menjadi array kosong.

Dampak:

- `localStorage` tidak tumbuh tanpa batas.
- Komponen pencarian lebih tahan terhadap data lama/rusak.

### 5. Pembersihan Aset Produksi

Folder:

- `assets/css`
- `assets/js`
- `assets/plugin/font-awesome`

Perubahan:

- Menghapus source map yang tidak dipakai.
- Menghapus Bootstrap non-minified/varian lama yang tidak dimuat.
- Menghapus `popper.min.js`, `vue.js` lama, dan source LESS/SCSS Font Awesome.
- Menghapus referensi `sourceMappingURL` yang menunjuk ke file map yang sudah dibuang.

Dampak:

- Jumlah file aset turun dari 654 menjadi 604 setelah penambahan `visitor_counter.js`.
- Ukuran `assets` saat verifikasi: 9.9M.
- Ukuran `assets/js` saat verifikasi: 540K.

## Perbaikan Maintainability

### 1. Visitor Counter Dipisah ke Asset JS

File:

- `visitor_template.php`
- `assets/js/visitor_counter.js`

Perubahan:

- Blok besar `Vue.createApp(...)` dipindahkan dari PHP ke `assets/js/visitor_counter.js`.
- PHP sekarang hanya mengirim konfigurasi lewat `<script type="application/json" id="rasamala-visitor-config">`.
- Config yang dipindahkan meliputi label tombol, pesan error, CSRF token, URL submit, default image, quote lokal, setting voice, dan bahasa speech.

Dampak:

- `visitor_template.php` lebih pendek dan lebih mudah dibaca.
- Logika visitor counter bisa dicek dengan `node --check`.
- Arah perbaikan CSP menjadi lebih jelas karena inline JS berkurang.

### 2. State Non-Member Dibuat Eksplisit

File:

- `visitor_template.php`
- `assets/js/visitor_counter.js`

Perubahan:

- Input nama non-member tidak lagi memakai `memberId`.
- Menambahkan state `visitorName`.
- Saat submit, `memberID` backend tetap diisi, tetapi sumbernya dipilih dari `memberId` untuk member dan `visitorName` untuk non-member.

Dampak:

- Alur form lebih mudah dipahami.
- Risiko salah baca data antara member dan non-member berkurang.

### 3. Indeks Dokumen Review

File:

- `docs/INDEX.md`

Perubahan:

- Menambahkan indeks dokumen utama dan dokumen historis.
- Review terbaru dan laporan perubahan terbaru ditandai sebagai rujukan aktif.
- Dokumen lama tetap disimpan sebagai arsip.

Dampak:

- Dokumentasi review tidak lagi tersebar tanpa konteks.
- Pembaca baru lebih mudah menemukan dokumen yang paling relevan.

### 4. Nested Repository Dihapus

Folder:

- `.git`

Perubahan:

- Menghapus nested `.git` di `template/rasamala`.

Dampak:

- Folder template tidak lagi dianggap repository terpisah saat deployment, backup, atau ZIP.
- Ukuran folder `template/rasamala` turun menjadi 12M pada verifikasi terakhir.

## Perbaikan Aksesibilitas dan UX

### 1. Reduced Motion Lebih Menyeluruh

File:

- `assets/css/theme-components.css`
- `visitor_template.php`
- `assets/js/hero_animation.js`
- `assets/js/cursor-particles.js`
- `assets/js/visitor_counter.js`

Perubahan:

- Menambahkan aturan `prefers-reduced-motion: reduce` untuk hero animation, background animation, ticker, reminder, mobile sheet, visitor form, dan laser scan.
- Canvas hero dan cursor particles disembunyikan saat reduced motion aktif.
- Hero animation dan cursor particles kini merespons perubahan preferensi reduced motion saat runtime.
- Visitor counter memakai delay reset lebih cepat pada reduced motion agar mode scan terasa lebih ringan.

Dampak:

- Pengguna yang sensitif terhadap animasi mendapat pengalaman yang lebih tenang.
- Kiosk visitor tetap auto-clear dan auto-focus, tetapi tanpa efek visual berat.

### 2. Label dan Semantik Komponen Interaktif

File:

- `visitor_template.php`
- `parts/_result-search.php`
- `assets/js/color_mode.js`

Perubahan:

- Menambahkan `aria-label` pada input visitor yang sebelumnya hanya mengandalkan placeholder.
- Menambahkan `role="status"`, `aria-live`, dan alt text yang lebih jelas pada feedback visitor.
- Tab visitor sekarang memiliki `role="tab"`, `aria-selected`, dan `aria-controls`.
- Icon-only atau icon-led controls pada mobile filter/sort/view diberi label dan ikon diberi `aria-hidden`.
- Label tombol dark/light mode kini ikut berubah bersama title saat mode berubah.

Dampak:

- Navigasi keyboard dan screen reader lebih jelas.
- Tombol berbasis ikon tidak bergantung pada visual saja.

### 3. Mobile Filter Menutup Setelah Apply

File:

- `parts/_result-search.php`

Perubahan:

- Tombol `Apply Filter` sekarang menutup `mobileFilterModal` setelah filter dipicu.
- Pilihan sort mobile juga menutup modal setelah nilai sort dipilih.

Dampak:

- Alur filter mobile terasa selesai, tidak meninggalkan modal terbuka setelah aksi diterapkan.

### 4. Status Masa Berlaku Kartu Digital

File:

- `parts/_member.php`
- `assets/css/opac-pages.css`

Perubahan:

- Menambahkan helper `rasamalaMemberExpiryStatus()`.
- Kartu digital sekarang menampilkan badge status:
  - `Active`
  - `Almost expired`
  - `Expired`
  - `Status unavailable`
- Badge status diberi `role="status"` dan `aria-label` berisi status serta catatan singkat.

Dampak:

- Masa berlaku kartu tidak hanya berupa tanggal kecil di footer.
- Member dapat langsung melihat apakah kartu masih aktif, hampir habis, atau sudah kadaluarsa.

## Perapian Inline CSS Visitor

File:

- `visitor_template.php`
- `parts/header.php`
- `assets/css/visitor.css`

Perubahan:

- Memindahkan blok CSS besar visitor dari `visitor_template.php` ke `assets/css/visitor.css`.
- Menambahkan stylesheet visitor secara kondisional di `parts/header.php` hanya saat halaman `p=visitor` dibuka.
- Mempertahankan cache-busting lewat `assetsVersioned('css/visitor.css')`.

Dampak:

- `visitor_template.php` kembali lebih fokus ke data dan markup.
- Style visitor lebih mudah dirawat, dicari, dan diuji.
- Beban inline style halaman visitor berkurang sebagai langkah menuju CSP yang lebih ketat.

## Modularisasi JavaScript Hasil Pencarian

File:

- `parts/_result-search.php`
- `assets/js/result_search.js`
- `assets/css/opac-pages.css`

Perubahan:

- Memindahkan JavaScript Masonry grid, popover availability, sort chip, mobile sort, mobile filter, dan responsive filter relocation dari `_result-search.php` ke `assets/js/result_search.js`.
- `_result-search.php` sekarang hanya memuat script eksternal lewat `assetsVersioned('js/result_search.js')`.
- Mengganti inline style `min-width: 140px` pada dropdown view menjadi class `.search-view-menu`.
- Pembuatan item sort di JavaScript sekarang memakai DOM/jQuery API, bukan string HTML gabungan.

Dampak:

- `_result-search.php` lebih fokus pada markup dan data hasil pencarian.
- Logika interaksi hasil pencarian lebih mudah diuji dan dirawat.
- Risiko escaping pada sort option lebih kecil.
- Inline script/style di `_result-search.php` sudah bersih kecuali tag script eksternal.

## Pemindahan Inline Style Attribute

File:

- `parts/_member.php`
- `parts/palette_switcher.php`
- `detail_template.php`
- `parts/_search-form.php`
- `parts/_navbar.php`
- `parts/floating_actions.php`
- `tinfo_helpers.php`
- `tinfo_options.inc.php`
- `assets/css/opac-pages.css`
- `assets/css/theme-components.css`
- `assets/css/tinfo-customizer.css`

Perubahan:

- Memindahkan style inline kartu digital member ke class CSS seperti `.rasamala-digital-card`, `.rasamala-digital-card-header`, `.rasamala-digital-card-avatar`, dan `.rasamala-digital-card-code`.
- Memindahkan style inline panel Theme Viewer/palette switcher ke class CSS seperti `.palette-switcher-section-head`, `.palette-switcher-tool-btn-compact`, `.palette-switcher-apply-btn`, dan `.palette-switcher-format-code`.
- Mengganti style inline kecil pada tombol close announcement, tombol color mode desktop, ikon status WhatsApp, dan baris availability tersembunyi dengan class CSS.
- Menambahkan asset `assets/css/tinfo-customizer.css` untuk class utilitas builder admin yang sebelumnya dibuat dengan style inline di string jQuery.
- Membersihkan fallback builder di `tinfo_options.inc.php` agar string jQuery lama juga tidak lagi membawa style attribute.

Dampak:

- File PHP aktif tidak lagi memiliki atribut `style="..."`.
- Styling komponen lebih konsisten lewat asset CSS.
- Satu langkah lagi menuju CSP yang lebih ketat karena inline style attribute aktif sudah berkurang signifikan.

## File yang Berubah

File baru:

- `review-template-rasamala-2026-07-22.md`
- `laporan-perubahan-rasamala-2026-07-22.md`
- `assets/css/tinfo-customizer.css`
- `assets/css/visitor.css`
- `assets/js/result_search.js`
- `assets/js/visitor_counter.js`
- `docs/INDEX.md`

File kode yang diubah:

- `biblio_list_template.php`
- `classic.php`
- `parts/_result-search.php`
- `parts/_member.php`
- `parts/_navbar.php`
- `parts/_search-form.php`
- `parts/floating_actions.php`
- `parts/palette_switcher.php`
- `theme_helpers.php`
- `tinfo_helpers.php`
- `parts/header.php`
- `detail_template.php`
- `news_template.php`
- `tinfo_options.inc.php`
- `parts/waktu_sholat.php`
- `tinfo_defaults.inc.php`
- `assets/js/app.js`
- `assets/js/app_jquery.js`
- `assets/js/axios.min.js`
- `assets/js/bootstrap.bundle.min.js`
- `assets/js/color_mode.js`
- `assets/js/cursor-particles.js`
- `assets/js/hero_animation.js`
- `assets/css/opac-pages.css`
- `assets/css/theme-components.css`
- `visitor_template.php`

File/folder yang dihapus:

- `assets/js/axios.min.map`
- `assets/js/bootstrap.bundle.js`
- `assets/js/bootstrap.bundle.js.map`
- `assets/js/bootstrap.bundle.min.js.map`
- `assets/js/bootstrap.js`
- `assets/js/bootstrap.js.map`
- `assets/js/bootstrap.min.js`
- `assets/js/bootstrap.min.js.map`
- `assets/js/popper.min.js`
- `assets/js/vue.js`
- source map dan varian Bootstrap CSS non-produksi di `assets/css`
- source CSS non-minified Font Awesome yang tidak dimuat
- source LESS/SCSS Font Awesome
- `.git`

## Verifikasi

### PHP Lint

Perintah yang dijalankan:

```bash
find template/rasamala \( -name '*.php' -o -name '*.inc.php' \) -print | sort | xargs -n 1 php -l
```

Hasil:

- Semua file PHP dan INC PHP di `template/rasamala` lolos tanpa syntax error.

### JavaScript Syntax Check

Perintah yang dijalankan:

```bash
node --check template/rasamala/assets/js/visitor_counter.js
node --check template/rasamala/assets/js/app.js
node --check template/rasamala/assets/js/app_jquery.js
node --check template/rasamala/assets/js/bootstrap.bundle.min.js
node --check template/rasamala/assets/js/axios.min.js
node --check template/rasamala/assets/js/color_mode.js
node --check template/rasamala/assets/js/cursor-particles.js
node --check template/rasamala/assets/js/hero_animation.js
node --check template/rasamala/assets/js/result_search.js
```

Hasil:

- Semua file JS yang dicek lolos tanpa syntax error.

### Pemeriksaan Aset

Pemeriksaan:

- `rg -n "sourceMappingURL=.*\.map" template/rasamala/assets`
- `find template/rasamala/assets -type f | wc -l`
- `du -sh template/rasamala`
- `du -sh template/rasamala/assets`
- `find template/rasamala/.git -maxdepth 0 -print`

Hasil:

- Tidak ada referensi `sourceMappingURL` ke file `.map` yang tersisa.
- Jumlah file aset final: 607.
- Ukuran `template/rasamala`: 12M.
- Ukuran `template/rasamala/assets`: 10M.
- Nested `.git` sudah tidak ada.

### Smoke Test Helper Keamanan

Pemeriksaan kecil dilakukan untuk memastikan:

- external `http://` menu diblokir,
- `https://` menu tetap diizinkan,
- `javascript:` pada image source diblokir,
- remote CSS `url(https://...)` dikosongkan,
- atribut event seperti `onload` pada tag core dibuang.

Hasil smoke test:

```text
menu_http_external_blocked
menu_https_allowed
image_js_blocked
css_remote_url_blocked
core_attrs_filtered
```

### Smoke Test Waktu Sholat

Pemeriksaan kecil dilakukan untuk memastikan default `hide` tidak merender widget waktu sholat.

Hasil smoke test:

```text
prayer_hide_no_output
```

### Verifikasi Pemindahan CSS Visitor

Perintah yang dijalankan:

```bash
rg -n "<style|</style>|visitor\\.css" template/rasamala/visitor_template.php template/rasamala/parts/header.php
php -l template/rasamala/visitor_template.php
php -l template/rasamala/parts/header.php
```

Hasil:

- `visitor_template.php` tidak lagi memiliki blok `<style>`.
- `parts/header.php` memuat `assets/css/visitor.css` secara kondisional untuk halaman visitor.
- `visitor_template.php` dan `parts/header.php` lolos tanpa syntax error.

### Verifikasi Modularisasi Result Search

Perintah yang dijalankan:

```bash
rg -n "<script\\b|</script>|<style\\b|</style>|\\bstyle=\\\"|\\bstyle='" template/rasamala/parts/_result-search.php
php -l template/rasamala/parts/_result-search.php
node --check template/rasamala/assets/js/result_search.js
```

Hasil:

- `_result-search.php` tinggal memuat `assets/js/result_search.js` sebagai script eksternal.
- Blok inline JavaScript dan inline style attribute di `_result-search.php` sudah tidak ada.
- `_result-search.php` lolos PHP lint.
- `assets/js/result_search.js` lolos syntax check.

### Audit Inline CSS/JS Tersisa

Pemeriksaan dilakukan pada file PHP/INC PHP di `template/rasamala`.

Ringkasan awal sebelum pemindahan style attribute:

- `parts/_member.php`: 6 inline script, 1 style block, 17 inline style attribute.
- `parts/palette_switcher.php`: 2 inline script, 15 inline style attribute.
- `tinfo_options.inc.php`: 1 inline script, 1 style block, 11 inline style attribute.
- `tinfo_helpers.php`: 1 inline script, 1 style block, 11 inline style attribute.
- `parts/header.php`: 4 inline script, 3 style block.
- `detail_template-sample.php`: 2 inline script, 60 inline style attribute; ini file sample sehingga prioritasnya lebih rendah dari template aktif.

### Verifikasi Pemindahan Style Attribute

Perintah yang dijalankan:

```bash
rg -n "\\bstyle=(\\\"|')" template/rasamala -g '*.php' -g '*.inc.php' -g '!detail_template-sample.php'
php -l template/rasamala/parts/_member.php
php -l template/rasamala/parts/palette_switcher.php
php -l template/rasamala/detail_template.php
php -l template/rasamala/tinfo_helpers.php
php -l template/rasamala/tinfo_options.inc.php
```

Hasil:

- Tidak ada atribut `style="..."` atau `style='...'` tersisa pada file PHP/INC PHP aktif, di luar `detail_template-sample.php`.
- File PHP yang tersentuh lolos tanpa syntax error.
- `detail_template-sample.php` masih memiliki inline style karena merupakan file contoh/sampel dan tidak direferensikan oleh template aktif.

### Perbaikan Ikon Keep SLiMS Alive

Perubahan:

- Ikon love pada tombol `Keep SLiMS Alive` tidak lagi memakai class `text-danger`, karena class tersebut di tema Rasamala dipetakan ulang ke warna teks tema.
- Ditambahkan class `footer-support-heart` dengan warna merah tetap pada stylesheet light dan dark.
- Warna ikon sekarang tidak ikut berubah saat mode terang/gelap, hover, atau focus tombol footer.

### Review dan Perbaikan Custom Palette

Temuan review:

- Backend sudah menghitung `on_background` dan `on_surface`, tetapi `text` dan `muted` dari input custom/AI masih bisa tetap terlalu mirip dengan `background` atau `surface`.
- Theme Viewer menerapkan palette melalui JavaScript/localStorage, sehingga logika kontras harus sama dengan backend agar preview tidak berbeda dari render server.
- Switcher membaca fallback dari field `classic_palette_custom_colors`, padahal field aktif adalah `classic_palette_custom`.
- Prompt generate palette belum cukup tegas meminta rasio kontras dan kesamaan keluarga terang/gelap antara `Background` dan `Surface`.
- Untuk kombinasi ekstrem seperti `Background` gelap dan `Surface` terang, satu warna teks tidak mungkin selalu aman di dua permukaan. Karena itu template harus memakai token berbeda: `on-background` untuk halaman dan `on-surface` untuk card/panel.

Perubahan:

- Menambahkan helper `themeAccessibleTextColor()` dan normalisasi `text`, `muted`, `muted_on_background`, serta `muted_on_surface` di `theme_helpers.php`.
- Menambahkan CSS variable `--theme-muted-on-background` dan `--theme-muted-on-surface` di output header.
- Menyamakan normalisasi kontras di `assets/js/theme_viewer.js`, termasuk saat apply/paste custom palette.
- Memperbaiki config switcher agar memakai `classic_palette_custom` sebagai sumber `customValue`.
- Menghapus normalisasi ganda pada custom dark palette agar kandidat warna asli tetap dipakai secara konsisten.
- Memperkuat prompt Copy Prompt agar AI menghasilkan palette dengan contrast ratio minimal 4.5:1 untuk `Text` dan `Muted`.
- Menambahkan guard CSS final di `theme-components.css` dan memperbarui guard dark mode di `theme-dark.css`.

Smoke test kontras:

```text
text_bg=#111827 ratio=17.74
muted_bg=#111827 ratio=17.74
surface=#111827 ratio=17.74
muted_surface=#111827 ratio=17.74
primary=#111827 ratio=17.74
```

Kasus ekstrem background gelap dan surface terang:

```text
text_bg=#777777 ratio=4.55
muted_bg=#888888 ratio=5.75
surface=#111827 ratio=17.74
muted_surface=#111827 ratio=17.74
```

Preset bawaan:

```text
palette_contrast_ok
```

Verifikasi syntax:

- Semua file PHP/INC PHP di `template/rasamala` lolos `php -l`.
- `assets/js/theme_viewer.js` lolos `node --check`.

### Ekstraksi Inline Script Member, Palette Switcher, dan Footer

Waktu perubahan: 2026-07-22 10:11:20 WIB.

Perubahan:

- Memindahkan logic footer untuk membersihkan atribut `inert` modal dan menjalankan highlight hasil pencarian ke `assets/js/footer_helpers.js`.
- Mengganti inline highlight JavaScript di `parts/footer.php` menjadi payload JSON non-eksekusi melalui `<template id="rasamala-highlight-keywords">`.
- Mengganti inline config `window.rasamalaPaletteSwitcher` di `parts/palette_switcher.php` menjadi `<template id="rasamala-palette-switcher-config">`.
- Memperbarui `assets/js/theme_viewer.js` agar membaca config switcher dari template JSON, dengan fallback ke global lama bila masih ada.
- Memindahkan handler member area dari script string di `parts/_member.php` ke `assets/js/member_area.js`, termasuk clear basket, remove selected basket, reserve, delete bookmark, modal konfirmasi, dan table responsive wrapper.
- Memindahkan logic fullscreen/minimize/print kartu member digital ke `assets/js/member_area.js`.
- Mengganti redirect inline JavaScript di `parts/_member.php` menjadi `rasamalaMemberRedirect()` berbasis header PHP dengan fallback meta refresh jika header sudah terkirim.
- Menambahkan state CSS `.rasamala-digital-card.is-fullscreen` dan tampilan tombol minimize tanpa inline style runtime.

Verifikasi:

```text
php -l template/rasamala/parts/_member.php
php -l template/rasamala/parts/palette_switcher.php
php -l template/rasamala/parts/footer.php
node --check template/rasamala/assets/js/member_area.js
node --check template/rasamala/assets/js/footer_helpers.js
node --check template/rasamala/assets/js/theme_viewer.js
```

Hasil:

- Semua file PHP target lolos tanpa syntax error.
- Semua file JS target lolos `node --check`.
- `parts/footer.php`, `parts/palette_switcher.php`, dan `parts/_member.php` tidak lagi memiliki inline script eksekusi untuk fitur yang diekstraksi; yang tersisa adalah script eksternal dan payload `<template>` non-eksekusi.

### Ekstraksi Inline Script Header

Waktu perubahan: 2026-07-22 10:17:09 WIB.

Perubahan:

- Memindahkan bootstrap awal header ke `assets/js/header_bootstrap.js`.
- Mengganti inline config color mode dan auto cover di `parts/header.php` menjadi payload JSON non-eksekusi melalui `<template id="rasamala-header-config">`.
- `header_bootstrap.js` tetap mengisi global kompatibilitas lama: `rasamalaColorModeDefault`, `rasamalaColorModeToggleVisible`, `rasamalaDarkCssUrl`, `rasamalaResolveColorMode`, `rasamalaAutoCoverMode`, dan `rasamalaAutoCoverGenerator`.
- Memindahkan probe debug hider dari inline script header ke `header_bootstrap.js`.
- Menghapus script inline setelah pembukaan `<body>`; class dark mode awal sekarang disinkronkan oleh `header_bootstrap.js`.
- Memperbarui komentar CSP agar tidak menyebut inline script header sebagai alasan utama, tetapi tetap mempertahankan catatan inline style, extension point lama, dan Vue runtime compilation.

Verifikasi:

```text
php -l template/rasamala/parts/header.php
node --check template/rasamala/assets/js/header_bootstrap.js
```

Hasil:

- `parts/header.php` lolos tanpa syntax error.
- `assets/js/header_bootstrap.js` lolos `node --check`.
- `parts/header.php` tidak lagi memiliki inline script eksekusi eksplisit; hasil pencarian `<script>` hanya menunjukkan script eksternal.

### Ekstraksi Inline CSS Header

Waktu perubahan: 2026-07-22 10:31:37 WIB.

Perubahan:

- Memindahkan CSS runtime statis header ke `assets/css/header-runtime.css`.
- Memindahkan mapping variable dark mode, font stack global, class kompatibilitas Bootstrap lama, `.custom-select`, `.close`, dan debug hider ke asset CSS.
- Mengubah variable palette/font/ticker yang sebelumnya dicetak sebagai blok `<style>` besar menjadi `themeVars` pada payload JSON non-eksekusi `rasamala-header-config`.
- Memperbarui `assets/js/header_bootstrap.js` agar menerapkan `themeVars` ke `document.documentElement.style`.
- Mengganti debug hider inline style menjadi class body `rasamala-debug-hidden` yang dikontrol dari CSS asset dan dilepas oleh `header_bootstrap.js` ketika admin login terdeteksi.

Verifikasi:

```text
php -l template/rasamala/parts/header.php
node --check template/rasamala/assets/js/header_bootstrap.js
rg -n "<script|</script>|<style|</style>|style=|onclick=|onchange=|onload=" template/rasamala/parts/header.php
```

Hasil:

- `parts/header.php` lolos tanpa syntax error.
- `assets/js/header_bootstrap.js` lolos `node --check`.
- Inline CSS besar dan debug hider inline sudah tidak ada di header.
- Sisa `<style>` di header hanya blok Custom CSS dari pengaturan admin (`classic_custom_css`) dan hanya muncul jika admin mengisinya.

### Koreksi Modularisasi Header

Waktu perubahan: 2026-07-22 10:45:25 WIB.

Perubahan:

- Mengoreksi pendekatan modularisasi header agar tidak menambah banyak file part baru.
- Memindahkan perhitungan runtime header ke helper yang sudah ada, yaitu `theme_helpers.php`, melalui `themeHeaderContext()` dan helper kecil terkait metadata, bahasa dokumen, favicon, body class, font stack, ticker speed, dan konfigurasi runtime.
- Mengembalikan `parts/header.php` menjadi satu file markup header/body-open yang memanggil helper, bukan include `header_context.php`, `header_meta.php`, `header_assets.php`, dan `header_body_open.php`.
- Menghapus file sementara `parts/header_context.php`, `parts/header_meta.php`, `parts/header_assets.php`, dan `parts/header_body_open.php`.
- Tetap mempertahankan asset hasil ekstraksi yang memang sesuai: `assets/css/header-runtime.css` dan `assets/js/header_bootstrap.js`.

Verifikasi:

```text
php -l template/rasamala/theme_helpers.php
php -l template/rasamala/parts/header.php
node --check template/rasamala/assets/js/header_bootstrap.js
rg -n "header_context|header_meta|header_assets|header_body_open" template/rasamala
rg -n "<script|</script>|<style|</style>|style=|onclick=|onchange=|onload=" template/rasamala/parts/header.php
```

Hasil:

- `theme_helpers.php` dan `parts/header.php` lolos tanpa syntax error.
- `assets/js/header_bootstrap.js` lolos `node --check`.
- Tidak ada referensi tersisa ke file part header sementara.
- File part header sementara sudah tidak ada di `template/rasamala/parts`.
- `parts/header.php` masih hanya memiliki script eksternal dan satu blok `<style>` dinamis untuk Custom CSS admin.

### Pengaturan Label Select Institusi Visitor

Waktu perubahan: 2026-07-22 10:51:01 WIB.

Perubahan:

- Menambahkan default `visitor_institution_select_label` di `tinfo_defaults.inc.php`.
- Menambahkan field pengaturan tema `visitor-institution-select-label` di `tinfo_options.inc.php`.
- Menghubungkan nilai pengaturan tersebut ke `selectInstitution` pada `visitor_template.php`.
- Label yang dikonfigurasi sekarang dipakai untuk `aria-label` select dan option placeholder awal.
- Menambahkan fallback ke `Pilih Fakultas / Institusi` jika field dikosongkan agar select tetap punya label aksesibel.

Verifikasi:

```text
php -l template/rasamala/tinfo_defaults.inc.php
php -l template/rasamala/tinfo_options.inc.php
php -l template/rasamala/visitor_template.php
```

Hasil:

- Semua file PHP target lolos tanpa syntax error.

### Pengaturan Isi Dropdown Institusi Visitor

Waktu perubahan: 2026-07-22 10:57:18 WIB.

Perubahan:

- Menambahkan default `visitor_institution_options` di `tinfo_defaults.inc.php` dengan daftar institusi yang sebelumnya hardcoded.
- Menambahkan field longtext `visitor-institution-options` di `tinfo_options.inc.php`.
- Format isi dropdown saat ini: `kode(label);kode(label);other`.
- Token `other` membuka input bebas/manual untuk pengunjung.
- Contoh: `feb(Fakultas Ekonomi dan Bisnis);ft(Fakultas Teknik);other`.
- Menambahkan helper parser `themeVisitorInstitutionOptions()` dan `themeVisitorInstitutionManualValue()` di `theme_helpers.php`.
- Mengubah `visitor_template.php` agar option `selectInstitution` dirender dari pengaturan tema.
- Mengubah `assets/js/visitor_counter.js` agar opsi manual dibaca dari config JSON `otherInstitutionValue`, bukan hardcoded `Lainnya`.
- Memperbarui `tinfo_helpers.php` agar field baru masuk quick setting, textarea kosong terisi default, dan tampil bersama setting visitor split.
- Parser tetap menerima format pipa lama sebagai fallback kompatibilitas bila sudah ada data tersimpan sebelumnya.
- Parser diperbaiki agar daftar satu baris berbasis titik koma tidak terbaca sebagai satu opsi besar.
- Parser juga tetap menerima format titik koma lama `kode; label; kode; label; Lainnya; Lainnya (ketik manual); manual` sebagai fallback.

Verifikasi:

```text
php -l template/rasamala/tinfo_defaults.inc.php
php -l template/rasamala/tinfo_options.inc.php
php -l template/rasamala/tinfo_helpers.php
php -l template/rasamala/theme_helpers.php
php -l template/rasamala/visitor_template.php
node --check template/rasamala/assets/js/visitor_counter.js
```

Hasil:

- Semua file PHP target lolos tanpa syntax error.
- `assets/js/visitor_counter.js` lolos `node --check`.
- Logika manual institution tidak lagi bergantung pada string hardcoded `Lainnya`; string tersebut hanya tersisa sebagai nilai default konfigurasi.

### Pengaturan HTML Langkah Petunjuk Split Visitor

Waktu perubahan: 2026-07-22 11:23:49 WIB.

Perubahan:

- Mengubah default `visitor_split_steps` di `tinfo_defaults.inc.php` menjadi HTML, setara pola `classic_footer_about_us`.
- Mengubah label field di `tinfo_options.inc.php` menjadi `Langkah Petunjuk Layout Split Visitor / HTML`.
- Mengubah default pengisi otomatis di `tinfo_helpers.php` agar textarea admin berisi contoh HTML.
- Mengubah `visitor_template.php` agar `visitor_split_steps` dirender sebagai HTML aman melalui `themeSanitizeHtml()`.
- Menambahkan fallback di `visitor_template.php` agar format lama `ikon | judul | deskripsi` tetap bisa dirender jika masih tersimpan.
- Menambahkan styling HTML umum di `assets/css/visitor.css` untuk `<p>`, `<ul>`, `<ol>`, `<li>`, dan `<a>` pada area petunjuk.
- Menyesuaikan sanitizer HTML di `theme_helpers.php` agar class Font Awesome pada tag `<i>` tetap dipertahankan, serta menghindari warning HTMLPurifier untuk tag unsupported.
- Memperbaiki deteksi HTML agar input sederhana seperti `baris 1 <br> baris 2` dianggap HTML dan dibungkus otomatis ke kartu petunjuk.
- Menambahkan decode entity aman agar `baris 1 &lt;br&gt; baris 2` juga diproses sebagai HTML setelah disanitasi.
- Menambahkan contoh sederhana `baris 1 &lt;br&gt; baris 2` pada label pengaturan tema.

Contoh isi:

```html
<div class="inst-step">
  <div class="inst-icon-box"><i class="fas fa-id-card"></i></div>
  <div class="inst-content">
    <h3>1. Isi Identitas</h3>
    <p>Scan kartu anggota atau ketik identitas pengunjung.</p>
  </div>
</div>
```

Verifikasi:

```text
php -l template/rasamala/tinfo_defaults.inc.php
php -l template/rasamala/tinfo_options.inc.php
php -l template/rasamala/tinfo_helpers.php
php -l template/rasamala/theme_helpers.php
php -l template/rasamala/visitor_template.php
```

Hasil:

- Semua file PHP target lolos tanpa syntax error.
- Sanitizer mempertahankan `<i class="fas fa-id-card"></i>` tanpa warning HTMLPurifier.
- Sanitizer mempertahankan `<br>` sebagai line break pada HTML polos.

### Perbaikan Description Detail Literal Line Break

Waktu perubahan: 2026-07-24 12:46:44 WIB.

Perubahan:

- Menambahkan helper `themeNormalizeTextLineBreaks()` di `helpers/ui/ui_text.php` untuk menormalisasi line break asli dan literal seperti `\r\n`, `\n`, `\r`.
- Memakai helper tersebut pada `themeExcerpt()` agar meta description detail tidak menampilkan karakter `\r\n`.
- Memakai helper tersebut pada `themeDetailHasValue()` dan `themeDetailNotesHtml()` agar deskripsi detail bibliografi merender jeda paragraf/baris secara natural.
- Menjaga sanitasi HTML tetap berjalan setelah normalisasi melalui `themeSanitizeHtml()`.

Verifikasi:

```text
php -l template/rasamala/helpers/ui/ui_text.php
php -l template/rasamala/helpers/detail.php
php -r "define('INDEX_AUTH', 1); require 'template/rasamala/theme_helpers.php'; require 'template/rasamala/helpers/detail.php'; echo themeDetailNotesHtml('baris 1\\r\\n\\r\\nbaris 2'), PHP_EOL; echo themeExcerpt('baris 1\\r\\n\\r\\nbaris 2', 152), PHP_EOL;"
```

Hasil:

- `themeDetailNotesHtml()` mengubah contoh literal `\r\n\r\n` menjadi dua paragraf HTML.
- `themeExcerpt()` mengubah contoh literal `\r\n\r\n` menjadi spasi bersih untuk metadata.

### Perbaikan Escape Apostrophe Description

Waktu perubahan: 2026-07-24 12:52:56 WIB.

Perubahan:

- Menambahkan `themeNormalizeStoredTextEscapes()` untuk membersihkan escape quote tersimpan seperti `text\'s` dan `you\'ll`.
- Menghubungkan normalisasi tersebut ke `themeNormalizeTextLineBreaks()` agar berlaku konsisten pada excerpt/meta description.
- Menghubungkan normalisasi tersebut ke `themeDetailNotesHtml()` setelah `html_entity_decode()` agar deskripsi detail bibliografi tidak menampilkan backslash pada apostrophe.
- Menghubungkan normalisasi tersebut ke `getNotes()` di `biblio_list_template.php` agar preview hasil pencarian ikut bersih.

Verifikasi:

```text
php -l template/rasamala/helpers/ui/ui_text.php
php -l template/rasamala/helpers/detail.php
php -l template/rasamala/biblio_list_template.php
```

Hasil uji contoh:

```text
<p>The text's primary objective. You'll be able.</p>
The text's primary objective. You'll be able.
```

### Koreksi Underline Judul Berita Light Mode

Waktu perubahan: 2026-07-24 13:07:08 WIB.

Perubahan:

- Membatalkan perubahan helper judul halaman sebelumnya karena masalah sebenarnya bukan karakter underscore di teks, melainkan underline link pada judul berita.
- Menemukan sumber masalah di `assets/css/foundation.css`: rule umum `.rasamala-main-content-card a:not(...)` memberi underline pada semua link konten dan menang karena `!important` serta spesifisitas tinggi.
- Menurunkan spesifisitas rule link konten dengan `:where()` dan menghapus `!important` pada rule tersebut.
- Mengecualikan `.news-card-title-link` dan `.news-list-thumbnail` dari rule underline konten agar judul berita light mode tetap bersih secara natural.
- Merapikan override judul berita di `assets/css/opac-pages.css` dan `assets/css/theme-components.css` agar tidak bergantung pada `!important` untuk menghapus underline.

Verifikasi:

```text
php -l template/rasamala/parts/_other.php
git diff --check -- template/rasamala/assets/css/foundation.css template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/theme-components.css template/rasamala/parts/_other.php
```

### Perapihan Tampilan Detail Bibliografi

Waktu perubahan: 2026-07-24 15:16:43 WIB.

Perubahan:

- Menjaga border paling luar `.detail-record` tetap ada, namun dibuat lebih halus agar desain tetap clean.
- Mengubah wrapper utama detail menjadi surface lembut dengan shadow dan aksen warna ringan, tanpa menambah border internal yang ramai.
- Mengembalikan garis/outline pada author chip di bawah judul, termasuk state hover/focus.
- Menambahkan `detail-title-block`, `detail-sidebar-col`, `detail-content-col`, dan `detail-info-list` agar styling detail tidak bergantung pada utility Bootstrap atau urutan heading.
- Merapikan `Detail Information` menjadi grid konsisten: kolom label tetap 200px dan kolom data fleksibel, dengan reset `width/max-width/flex` dari class `col-sm-3/col-sm-9`.
- Menjaga label dan data sejajar di desktop, serta tetap stacked rapi di mobile.
- Menyesuaikan dark mode agar `detail-info-list` tidak mendapat border-bottom lama dari rule global detail.

Verifikasi:

```text
php -l template/rasamala/detail_template.php
php -l template/rasamala/parts/detail/detail_sidebar.php
php -l template/rasamala/parts/detail/detail_fields.php
git diff --check -- template/rasamala/detail_template.php template/rasamala/parts/detail/detail_sidebar.php template/rasamala/parts/detail/detail_fields.php template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/foundation.css template/rasamala/assets/css/theme-dark.css
```

### Perapihan Visual Flat Detail Bibliografi

Waktu perubahan: 2026-07-24 15:25:19 WIB.

Perubahan:

- Menghapus gradasi dan shadow dari wrapper utama `.detail-record` tanpa menghilangkan border paling luar.
- Menghapus shadow dari cover wrapper, cover image, placeholder cover, call number tag, baris availability, popover availability, description box, author chip, dan tombol aksi detail.
- Mengubah surface cover dan description box menjadi warna flat berbasis token theme agar tetap clean di light/dark mode.
- Mempertahankan border tombol bookmark/share melalui `.detail-link-btn`, termasuk border state hover dan state bookmarked.
- Mempertahankan border/outline author chip dan garis pemisah di bawah area title-author.
- Menyesuaikan dark mode agar `.detail-record`, `.detail-cover-wrapper`, `.detail-notes-box`, dan popover availability tidak mendapat shadow/gradasi dari override global.

Verifikasi:

```text
git diff --check -- template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/theme-dark.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Perapihan Toolbar Ringkasan Hasil Pencarian

Waktu perubahan: 2026-07-24 15:33:27 WIB.

Perubahan:

- Mengganti utility Bootstrap besar pada toolbar hasil pencarian (`fs-6`, `py-2`, `shadow-sm`, `bg-primary`, `bg-light`) dengan class semantik khusus.
- Membuat panel toolbar lebih compact dengan padding 10-13px dan tombol aksi tinggi 32-34px.
- Mengubah badge jumlah hasil, keyword badge, tombol Filter, Sort, dan View agar memakai token `--theme-*` sehingga mengikuti custom palette.
- Mengubah state filter aktif di `result_search.js` dari toggle `btn-primary/btn-light` menjadi class `is-active`.
- Merapikan dark mode dan transparent panel mode agar toolbar tidak kembali memakai warna hardcoded atau shadow lama.

Verifikasi:

```text
php -l template/rasamala/parts/_result-search.php
node --check template/rasamala/assets/js/result_search.js
git diff --check -- template/rasamala/parts/_result-search.php template/rasamala/assets/js/result_search.js template/rasamala/assets/css/foundation.css template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/theme-dark.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Perbaikan Dimensi Generated Cover Detail

Waktu perubahan: 2026-07-24 15:36:21 WIB.

Perubahan:

- Menemukan sumber generated cover show detail terlihat kotak: rule global `.book-cover-placeholder` memakai `width`, `height`, dan `min-height` dengan `!important`.
- Mengubah rule global placeholder agar ukuran memakai custom property `--book-cover-width`, `--book-cover-height`, `--book-cover-min-height`, dan `--book-cover-aspect-ratio`.
- Mengisi custom property tersebut khusus di `.detail-cover .book-cover-placeholder` menjadi `145px x 215px` di desktop dan `115px x 170px` di mobile.
- Mempertahankan perilaku placeholder di card/list lain karena default global tetap `100%`.

Verifikasi:

```text
git diff --check -- template/rasamala/assets/css/opac-pages.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Penyesuaian Ukuran Cover Detail Mobile

Waktu perubahan: 2026-07-24 15:52:20 WIB.

Perubahan:

- Memperbesar wrapper cover detail mobile dari batas `150px` menjadi `min(184px, 56vw)`.
- Memperbesar cover gambar nyata dari `115px x 170px` menjadi responsif hingga `145px x 215px`.
- Memperbesar generated cover mobile dengan custom property responsif `min(145px, 44vw)` dan `min(215px, 65vw)`.
- Menyesuaikan font judul generated cover mobile dari `0.72rem` ke `0.78rem` agar proporsional dengan cover yang lebih besar.

Verifikasi:

```text
git diff --check -- template/rasamala/assets/css/opac-pages.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Perapihan Call Number dan Availability Detail

Waktu perubahan: 2026-07-24 15:57:08 WIB.

Perubahan:

- Mengubah call number tag menjadi pill dengan border lembut, ukuran teks lebih proporsional, dan warna berbasis token theme.
- Mengubah availability row menjadi kartu kecil beradius `14px` dengan background surface-accent tipis, bukan blok gelap/garis bawah keras.
- Menambahkan aksen tipis di sisi kiri availability row untuk memberi struktur tanpa border ramai.
- Mengubah label lokasi availability agar memakai warna teks surface sehingga tidak terlalu menyala di dark mode.
- Mengubah count availability menjadi chip kecil agar status `20/20` terlihat rapi dan tidak terasa menempel.
- Menyesuaikan dark mode override paling bawah agar tidak lagi memaksa `border-radius: 0` dan `border-bottom`.

Verifikasi:

```text
git diff --check -- template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/theme-dark.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Perapihan Pop-up Kontrol Hasil Pencarian

Waktu perubahan: 2026-07-24 16:04:12 WIB.

Perubahan:

- Mengganti struktur modal Filter, Sort, dan View agar memakai class semantik `search-control-modal-*`.
- Menghapus ketergantungan visual pada utility Bootstrap besar seperti `shadow-lg`, `bg-light`, `text-primary`, dan padding besar di item modal.
- Memindahkan ukuran ikon, warna, spacing, dan state aktif ke CSS asset agar pop-up mengikuti custom palette.
- Mengubah JS sort modal agar state aktif cukup memakai class `active` dan icon check semantik, bukan toggle utility warna hardcoded.
- Menyamakan dark mode dan mode panel transparan untuk `mobileSortModal` dan `mobileViewModal`.

Verifikasi:

```text
php -l template/rasamala/parts/_result-search.php
node --check template/rasamala/assets/js/result_search.js
git diff --check -- template/rasamala/parts/_result-search.php template/rasamala/assets/js/result_search.js template/rasamala/assets/css/opac-pages.css template/rasamala/assets/css/theme-dark.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

### Perapihan Show Detail Mobile

Waktu perubahan: 2026-07-24 16:12:36 WIB.

Perubahan:

- Memperbesar pill call number di mobile dan memberi pemisah tipis dari area cover agar nomor panggil menjadi fokus utama.
- Membuat label `CALL NUMBER` lebih terbaca dengan ukuran dan letter spacing yang lebih proporsional.
- Merapikan heading `Availability` dengan ikon bulat kecil yang mengikuti token theme.
- Membuat availability row mobile lebih nyaman dipindai: tinggi sedikit lebih besar, radius konsisten, count chip lebih jelas, dan aksen kiri tetap halus.
- Merapikan area `Text`, Bookmark, Share, judul, dan author chip agar ukuran, spacing, serta capsule button terasa seragam di mobile.
- Menyamakan warna ikon mobile yang sebelumnya bisa terbawa utility Bootstrap agar mengikuti token theme, dengan state bookmarked tetap putih.

Verifikasi:

```text
git diff --check -- template/rasamala/assets/css/opac-pages.css template/rasamala/docs/laporan-perubahan-rasamala-2026-07-22.md
```

## Catatan Lanjutan

- Inline CSS besar di `visitor_template.php` sudah dipindahkan ke `assets/css/visitor.css`.
- Inline JavaScript di `_result-search.php` sudah dipindahkan ke `assets/js/result_search.js`.
- Inline style attribute pada file aktif sudah dipindahkan ke class CSS asset.
- Inline script di `parts/_member.php`, `parts/palette_switcher.php`, dan `parts/footer.php` sudah diekstraksi ke asset JS dengan konfigurasi JSON non-eksekusi.
- Inline script eksplisit di `parts/header.php` sudah diekstraksi ke `assets/js/header_bootstrap.js`.
- Inline CSS runtime besar di `parts/header.php` sudah diekstraksi ke `assets/css/header-runtime.css`; sisa inline style header hanya Custom CSS admin yang bersifat dinamis.
- Modularisasi header sudah disatukan ke `theme_helpers.php`, bukan file part header tambahan.
- Label dan isi dropdown select institusi visitor sudah bisa diubah dari pengaturan tema melalui field `visitor_institution_select_label` dan `visitor_institution_options`.
- Langkah petunjuk layout split visitor sudah bisa diisi HTML seperti field Tentang Kami di Footer.
- Custom palette sekarang memiliki normalisasi kontras server-side dan client-side untuk `Text`, `Muted`, `Background`, `Surface`, dan warna tombol/chrome.
- Sort toolbar hasil pencarian sudah dirapikan menjadi komponen modular `search-sort-bar` dengan chip sort dan dropdown view yang lebih clean.
- Pop-up kontrol Filter, Sort, dan View hasil pencarian sudah distandarkan sebagai komponen `search-control-modal-*` yang compact dan mengikuti theme.
- Tombol aksi umum sudah distandarkan ke bentuk capsule; pagination `.pagingList` dan `.pagination .page-link` sudah dibuat bulat.
- Mobile show detail sudah dipoles ulang untuk call number, availability, action button, title, dan author chip.
- Prioritas ekstraksi berikutnya: inline script di `parts/waktu_sholat.php` dan `parts/_search-form.php`.
- CSP belum dibuat ketat penuh karena masih ada inline style/script di beberapa area template dan Vue masih memakai runtime template compilation.
- Jika inline style/script tersisa sudah dipindah dan Vue runtime compilation sudah tidak dibutuhkan, `unsafe-inline` dan `unsafe-eval` bisa dievaluasi ulang.
- Perubahan dilakukan tanpa membatalkan perubahan lain yang sudah ada di working tree.
