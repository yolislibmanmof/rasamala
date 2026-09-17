# TODO Rasamala Theme

Sumber utama: `rasamala_theme_review.md` hasil fact-check 2026-07-07.

Target: theme berada di `98/template/rasamala` setelah rename dari `apple-theme`, lalu dibersihkan secara bertahap agar lebih aman, konsisten, dan mudah dirawat.

Status yang dipakai:

- `[ ]` belum dikerjakan
- `[~]` sedang dikerjakan
- `[x]` selesai
- `[!]` tertahan / perlu keputusan

---

## Tahap 0 - Persiapan Rename ke Rasamala

Tujuan: memastikan perubahan nama tidak meninggalkan referensi runtime `apple-theme` atau path lama yang membuat theme tidak terbaca SLiMS.

- [x] Tentukan nama folder final: `98/template/rasamala`.
- [x] Rename folder dari `apple-theme` ke `rasamala`.
- [x] Update kondisi customize di `tinfo.inc.php` dari `theme == 'apple-theme'` menjadi `theme == 'rasamala'`.
- [x] Update judul/narasi review dan dokumentasi internal dari `apple-theme` menjadi `rasamala`.
- [x] Audit string branding visual seperti `Apple.com Premium Theme`, `Apple Hero`, `Apple Product Showcase`, dan ganti komentar/dokumentasi utama ke istilah Rasamala.
- [x] Rename file dokumentasi:
  - [x] `apple_theme_review.md` -> `rasamala_theme_review.md`
  - [x] `laporan_perubahan_template.md` tetap dipakai sebagai histori perubahan lama
- [x] Cari semua referensi lama:
  - [x] `rg -n "apple-theme|apple_theme|Apple|apple-" 98/template/rasamala`
- [x] Pastikan helper `assets()` tetap menghasilkan path benar setelah folder berubah; helper memakai `$sysconf['template']['theme']`, jadi mengikuti theme aktif.
- [x] Pastikan theme dapat dipilih dari konfigurasi SLiMS setelah rename; dikonfirmasi aktif oleh pengguna pada 2026-07-07.

Catatan keputusan:

- Class, variable CSS, dan selector internal seperti `apple-*` belum diganti pada Tahap 0 agar tidak memicu refactor visual lintas file. Pekerjaan ini dipindahkan ke Tahap 5 saat struktur CSS dibereskan.
- Verifikasi pilihan theme di admin/OPAC perlu dilakukan setelah konfigurasi SLiMS diarahkan ke `rasamala`.

Acceptance check:

- [x] Tidak ada referensi `apple-theme` yang masih dibutuhkan di runtime.
- [x] Theme `rasamala` bisa dimuat di halaman OPAC; dikonfirmasi aktif oleh pengguna pada 2026-07-07.
- [!] Halaman home, search, detail, member, dan visitor tidak error setelah rename.

---

## Tahap 1 - Quick Wins dan Bug Kritis

Tujuan: menyelesaikan hal kecil yang berdampak besar sebelum refactor berat.

- [x] Hapus kode anti-tampering di `classic.php` yang membaca `parts/header.php`.
- [x] Hapus duplicate meta viewport di `parts/header.php`.
- [x] Fix invalid HTML ID `section1 container-fluid` di:
  - [x] `parts/_home.php`
  - [x] `parts/_other.php`
  - [x] `parts/_result-search.php`
  - [x] `parts/_member.php` (tambahan karena bug yang sama ditemukan di sini)
- [x] Ganti cache busting berbasis `date()` per detik dengan `filemtime()` via helper `assetVersion()` / `assetsVersioned()`.
- [x] Tambahkan `rel="noopener noreferrer"` pada semua link `target="_blank"`.
- [x] Ganti teks base translation Indonesia di bottom nav:
  - [x] `__('Lainnya')` -> `__('More')`
  - [x] `__('Menu Lainnya')` -> `__('More Menu')`
- [x] Perbaiki alt avatar hardcoded `Avatar of Jonathan Reinink` menjadi nama member atau fallback generik.

Catatan verifikasi:

- `curl` ke `127.0.0.1` dari sandbox tidak bisa tersambung, jadi cek browser langsung tidak tersedia dari sesi ini.
- Semua PHP theme lolos `php -l`, dan pencarian source untuk bug Tahap 1 sudah bersih.

Acceptance check:

- [x] HTML tidak memiliki duplicate viewport.
- [x] Tidak ada ID dengan spasi untuk bug `section1 container-fluid`.
- [x] Cache key asset tidak berubah setiap request.
- [x] Tidak ada output anti-tampering tersembunyi.

---

## Tahap 2 - Security dan Output Safety

Tujuan: menutup risiko XSS, attribute injection, URL injection, dan CSRF parsial yang ditemukan di review.

### 2.1 Vue dan Visitor Output

- [x] Ganti `domProps.innerHTML` di `assets/js/app.js` untuk data berikut menjadi text rendering:
  - [x] `topic`
  - [x] `title`
  - [x] `memberType`
- [x] Ganti `v-html="textInfo"` di `visitor_template.php` dengan `v-text` atau mustache binding.
- [x] Ubah response visitor agar message dan type dipisah, bukan HTML string dari server.
- [x] Pastikan pesan expired/error visitor tetap bisa diberi style lewat class Vue.

### 2.2 PHP Output Escaping

- [x] Escape output text dan attribute di `biblio_list_template.php`.
- [x] Escape output text dan attribute di `detail_template.php`.
- [x] Sanitasi field HTML yang memang boleh mengandung markup, seperti notes/description/footer about us.
- [x] Escape meta tag dan OG/Twitter attributes di `parts/header.php`.
- [x] Escape `library_name`, `library_subname`, dan output session member jika tampil ke HTML.

### 2.3 URL dan Admin Settings

- [x] Escape attribute `src` dan `href` untuk map/social links di `parts/_home.php`.
- [x] Validasi URL social links hanya menerima `https://`.
- [x] Validasi map embed URL sebelum dirender ke iframe.
- [x] Tambahkan fallback aman jika URL admin kosong/tidak valid.

### 2.4 CSRF

- [x] Tambahkan token pada AJAX add-to-basket di `assets/js/app_jquery.js`.
- [x] Tambahkan token pada AJAX bookmark di `assets/js/app_jquery.js`.
- [x] Pastikan branch grid-to-list di `_result-search.php` menyertakan CSRF token.
- [x] Validasi `$_POST['view']` di `index_template.inc.php` hanya menerima `list` atau `grid`.

### 2.5 SQL Helper

- [x] Cast dan clamp semua parameter `LIMIT` di helper SQL.
- [x] Validasi `$year` dengan format `YYYY`.
- [x] Cast `$biblio_id` di query `getNotes()` dan `getAvailability()`.
- [x] Audit apakah helper di `classic.php` masih dipakai; hapus yang benar-benar mati.
- [!] Gunakan prepared statement bila API database SLiMS di konteks ini mendukung.

Catatan keputusan:

- Helper SQL di `classic.php` tidak dihapus pada tahap ini walau tidak ada referensi langsung di file template, karena masih mungkin dipakai oleh komponen/API tema. Perubahan dibatasi ke hardening input.
- Prepared statement belum diterapkan karena file tema masih mengikuti pola `$dbs->query()` lama; risiko input dinamis pada helper yang disentuh dimitigasi dengan cast, clamp, dan validasi format.
- Sanitasi HTML rich text memakai HTMLPurifier bawaan dari SLiMS/vendor dengan basis allowlist `$sysconf['content']['allowable_tags']`; fallback lokal hanya dipakai bila purifier tidak tersedia.

Acceptance check:

- [x] Tidak ada `innerHTML` untuk data katalog/API yang tidak disanitasi.
- [x] Tidak ada `v-html` untuk message server yang berisi data member.
- [x] Semua atribut dinamis memakai `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` atau helper `themeEscape()`.
- [x] Semua POST yang mengubah state memiliki CSRF.
- [x] Parameter query numerik sudah dicast.

---

## Tahap 3 - Wiring Opsi Admin

Tujuan: memastikan semua opsi di `tinfo.inc.php` benar-benar bekerja atau sengaja dihapus.

### 3.1 Theme Accent Color

- [x] Gunakan `$selected_color` di `parts/header.php`.
- [x] Override CSS variables dinamis:
  - [x] `--apple-accent`
  - [x] `--apple-accent-hover`
  - [x] `--theme-accent-color`
  - [x] `--theme-accent-rgb`
  - [x] `--theme-accent-glow`
  - [x] `--theme-accent-glow-half`
- [x] Uji semua palet: warmgray, cyan, emerald, orange, gold, pink.
- [x] Perbaiki contrast warna default agar teks kecil lolos WCAG AA atau batasi accent untuk elemen non-teks.

### 3.2 Navbar Menu Builder

- [x] Putuskan apakah perlu reorder drag-and-drop.
- [x] Jika tidak, ubah narasi dokumentasi agar tidak menyebut drag-and-drop.
- [x] Validasi URL menu sebelum disimpan/dirender.
- [x] Tambahkan guard untuk menu kosong atau input malformed.

### 3.3 Opsi yang Tampak Terputus

- [x] Audit `classic_slide_transition`.
- [x] Audit `classic_slide_animation`.
- [x] Audit `classic_slide_delay`.
- [x] Audit `classic_library_disableslide`.
- [x] Audit `classic_popular_collection_item`.
- [x] Audit `classic_new_collection_item`.
- [x] Audit `classic_suggestion`.
- [x] Buat keputusan per opsi:
  - [x] aktif dan diperbaiki
  - [x] legacy dan disembunyikan
  - [ ] dihapus

Acceptance check:

- [x] Setiap opsi admin punya consumer jelas di code.
- [x] Tidak ada opsi yang tampil di admin tapi tidak berpengaruh tanpa catatan.
- [x] Perubahan theme color terlihat di navbar, button, link, modal, dan bottom nav.

Catatan keputusan:

- Palet accent dipusatkan di `theme_helpers.php`, lalu dipakai oleh `parts/header.php` untuk variable `--apple-*` dan `--theme-*`.
- Semua palet accent dibuat lebih gelap agar kontras terhadap teks putih lolos WCAG AA: warmgray 6.45, cyan 6.87, emerald 5.48, orange 5.02, gold 6.19, pink 6.04.
- Navbar builder diputuskan tanpa drag-and-drop pada tahap ini; fitur yang aktif adalah tambah, hapus, edit label, edit URL, validasi URL, dan fallback menu default.
- Opsi slideshow (`classic_library_disableslide`, `classic_slide_transition`, `classic_slide_animation`, `classic_slide_delay`) disembunyikan dari admin karena consumer aktifnya belum di-include di flow utama; nasib finalnya dipindahkan ke Tahap 4.
- `classic_popular_collection_item` tetap tampil karena dipakai API `BiblioController`; `classic_new_collection_item` dan `classic_suggestion` disembunyikan sebagai legacy sampai ada consumer yang jelas.

---

## Tahap 4 - Dead Code dan Asset Cleanup

Tujuan: mengurangi beban deployment dan menghapus fitur setengah aktif.

- [x] Putuskan nasib Vegas slideshow:
  - [ ] aktifkan kembali dengan include CSS/JS/plugin yang benar, atau
  - [x] hapus `assets/js/vegas.js.php`, plugin Vegas, dan opsi slideshow dari `tinfo.inc.php`.
- [x] Hapus atau pindahkan `assets/css/tailwind.min.css` jika hanya dipakai sample.
- [x] Pindahkan `samplehero.html` ke folder sample/dev-only atau hapus bila tidak diperlukan.
- [x] Hapus query `$hero_book_q` jika blok hero product tetap tidak dipakai.
- [x] Putuskan hero product tidak dipakai pada tahap ini; blok komentar dan query lama dihapus.
- [x] Review asset besar lain yang tidak direferensikan.

Acceptance check:

- [x] Tidak ada asset besar tidak terpakai di theme production.
- [x] Tidak ada query database untuk blok UI yang dikomentari.
- [x] Opsi slideshow tidak tampil jika fitur slideshow dihapus.

Catatan keputusan:

- Vegas slideshow dihapus, bukan diaktifkan ulang, karena tidak ada include aktif di flow utama dan opsi adminnya sudah tidak punya efek.
- File yang dihapus: `assets/js/vegas.js.php`, seluruh `assets/plugin/vegas/`, `assets/css/tailwind.min.css`, `samplehero.html`, dan gambar slide lama `slide1.jpg` sampai `slide4.jpg`.
- Opsi slideshow (`classic_library_disableslide`, `classic_slide_transition`, `classic_slide_animation`, `classic_slide_delay`) dihapus dari `tinfo.inc.php`.
- `classic_new_collection_item` dan `classic_suggestion` ikut dihapus dari `tinfo.inc.php` karena tidak punya consumer aktif di folder tema.
- Blok hero product yang sebelumnya dikomentari dan query `$hero_book_q` dihapus bersama agar homepage tidak menjalankan query yang tidak terlihat di UI.

---

## Tahap 5 - CSS dan Struktur Frontend

Tujuan: membuat styling Rasamala lebih mudah dirawat tanpa mematahkan tampilan.

- [x] Simpan CSS variables default di `style.css` sebagai fallback.
- [x] Pindahkan override dinamis ke inline style kecil di `header.php`.
- [!] Pecah `style.css` bertahap:
  - [ ] `_variables.css`
  - [ ] `_base.css`
  - [ ] `_navbar.css`
  - [ ] `_search.css`
  - [ ] `_cards.css`
  - [ ] `_detail.css`
  - [ ] `_footer.css`
  - [ ] `_mobile-nav.css`
  - [ ] `_visitor.css`
  - [ ] `_modal.css`
- [!] Kurangi `!important` mulai dari komponen yang sering berubah:
  - [ ] button
  - [ ] navbar
  - [ ] search form
  - [ ] cards
  - [ ] mobile nav
- [x] Tambahkan namespace root bila dibutuhkan, misalnya `.rasamala-theme`.
- [x] Pindahkan inline CSS dari `parts/footer.php` ke file CSS.
- [x] Pindahkan JS back-to-top dan mobile menu ke `assets/js/app_jquery.js` bila tidak perlu PHP inline.
- [x] Tambahkan focus-visible state untuk tombol/icon control.

Acceptance check:

- [!] Tampilan utama tidak berubah besar setelah split CSS.
- [!] Jumlah `!important` turun bertahap dan dicatat.
- [x] Inline CSS/JS di footer tinggal yang benar-benar dinamis.

Catatan keputusan:

- `style.css` sekarang memegang fallback variable warna default, termasuk `--theme-accent-*`; `parts/header.php` hanya override nilai accent yang dipilih admin.
- Hardcoded accent lama `#8B7355`, `#7A6348`, dan `rgba(139, 115, 85, ...)` di `style.css` diganti ke CSS variable agar mengikuti opsi admin.
- `body` diberi namespace `.rasamala-theme` sebagai hook untuk refactor CSS berikutnya.
- Inline CSS footer dihapus; styling floating info, back-to-top, modal, dan mobile more tetap di `style.css`.
- JS back-to-top, mobile more menu, dan chat toggle dipindahkan ke `assets/js/app_jquery.js`; footer masih menyisakan inline highlight karena membutuhkan data PHP `$searchableInJsArray`.
- Split penuh `style.css` dan pengurangan besar `!important` ditahan sampai ada browser regression check. Baseline saat ini: `1387` kemunculan `!important` di `style.css`.

---

## Tahap 6 - UX, Accessibility, dan Progressive Enhancement

Tujuan: memperhalus pengalaman pengguna tanpa refactor besar.

- [x] Tambahkan `aria-label` untuk:
  - [x] floating info button
  - [x] back-to-top button
  - [x] close mobile more menu
  - [x] search icon button
- [x] Tambahkan empty state untuk komponen async Vue.
- [x] Tambahkan error state saat `fetch()` gagal.
- [x] Tambahkan skeleton loader jika spinner dirasa kurang halus.
- [x] Tambahkan timeout/fallback lebih jelas untuk quotes API di visitor counter.
- [x] Jadikan quotes API toggleable di admin bila dibutuhkan.
- [x] Gunakan `URLSearchParams` untuk submit search di `assets/js/app.js`.
- [x] Pertimbangkan dark mode setelah token warna stabil.

Acceptance check:

- [x] Navigasi keyboard terlihat jelas.
- [x] Screen reader mendapat label pada tombol icon-only.
- [x] Komponen async punya loading, empty, dan error state.

Catatan keputusan:

- Komponen Vue home (`slims-collection`, `slims-group-subject`, `slims-group-member`) sekarang punya skeleton loading, empty state, dan error state saat `fetch()` gagal atau response bukan array.
- Submit pencarian dan link histori pencarian memakai `URLSearchParams` agar karakter khusus di keyword lebih aman.
- Visitor counter punya fallback quotes lokal, timeout 3 detik untuk API quotes eksternal, opsi admin `visitor_quote`, dan state submit `aria-busy`/disabled saat check-in berjalan.
- Label aksesibilitas ditambahkan untuk tombol floating info, back-to-top, close mobile more, advanced search, search submit, dan close search suggestion.
- Dark mode belum diterapkan karena token warna baru distabilkan pada Tahap 5; keputusan saat ini adalah menunda sampai regresi visual terang selesai.

---

## Tahap 7 - Compatibility dan Dependency

Tujuan: memastikan dependensi lama dipahami, bukan sekadar dibiarkan.

- [x] Catat versi Vue lokal yang dipakai.
- [x] Putuskan strategi Vue 2:
  - [x] tetap Vue 2 dengan scope terbatas
  - [ ] migrasi Vue 3
  - [x] gunakan vanilla JS untuk custom code non-plugin secara bertahap; Alpine.js tidak ditambah
- [x] Audit Bootstrap/jQuery usage yang wajib ada.
- [x] Pastikan tidak ada dependency sample yang ikut production.
- [x] Dokumentasikan keputusan dependency di README/theme notes.

Acceptance check:

- [x] Ada keputusan eksplisit untuk Vue 2 EOL.
- [x] Tidak ada dependency yang tidak jelas asal dan fungsinya.

Catatan keputusan:

- Vue lokal tercatat sebagai Vue.js v2.6.11. Karena Vue 2 sudah legacy/EOL dan komponen yang ada belum punya regression test browser lengkap, strategi aman saat ini adalah mempertahankan Vue 2 dengan scope terbatas dan tidak menambah framework baru.
- `assets/js/app_jquery.js` dipindahkan ke vanilla DOM API dan `fetch()` untuk handler custom non-plugin; nama file dipertahankan agar include lama tidak berubah.
- jQuery tetap dimuat karena Bootstrap 4 JS, Colorbox/SLiMS plugin, IonRangeSlider, Masonry integration, dan beberapa inline integration hasil pencarian masih bergantung pada pola plugin jQuery.
- Bootstrap lokal tercatat tidak seragam: CSS v4.2.1 dan JS v4.6.2. Ini dicatat sebagai kandidat cleanup lanjutan setelah ada QA visual.
- Catatan detail ada di `dependency_notes.md`.

---

## Tahap 8 - QA dan Release Checklist

Tujuan: memastikan Rasamala siap dipakai setelah rename dan cleanup.

### Halaman yang wajib dicek

- [ ] Homepage
- [ ] Search result list view
- [ ] Search result grid view
- [ ] Detail bibliografi
- [ ] Member area login state
- [ ] Member area guest state
- [ ] Visitor counter
- [ ] News page
- [ ] Librarian page
- [ ] Mobile viewport
- [ ] Desktop viewport

### Skenario yang wajib dicek

- [ ] Ganti theme color dari admin.
- [ ] Ubah navbar menu dari admin.
- [ ] Tambah bookmark.
- [ ] Tambah basket.
- [ ] Search keyword dengan karakter khusus (`&`, `=`, quote).
- [ ] Buka mobile bottom nav dan More sheet.
- [ ] Submit visitor counter.
- [ ] Render data katalog dengan karakter HTML sebagai teks aman.
- [ ] Cek console browser untuk error JS.
- [ ] Cek log PHP untuk warning/error.

### Rilis

- [ ] Update nama theme final di dokumentasi.
- [ ] Simpan daftar breaking changes dari `apple-theme` ke `rasamala`.
- [ ] Catat file yang dihapus/dipindah.
- [ ] Catat opsi admin yang berubah.
- [ ] Ambil screenshot before/after halaman utama.

Acceptance check:

- [ ] Rasamala bisa dipilih dan dipakai sebagai theme aktif.
- [ ] Tidak ada error fatal PHP.
- [ ] Tidak ada error JS kritis di console.
- [ ] Checklist tahap 0 sampai 8 sudah selesai atau blocker-nya jelas.

---

## Catatan Pelacakan

Gunakan bagian ini untuk mencatat progres harian.

| Tanggal | Tahap | Catatan | Status |
|---------|-------|---------|--------|
| 2026-07-07 | Init | Membuat todo awal dari review fact-check `rasamala_theme_review.md` | [x] |
| 2026-07-07 | Tahap 0 | Rename folder ke `rasamala`, update gate customize, rename review file, dan audit referensi runtime lama | [x] |
| 2026-07-07 | Tahap 1 | Quick wins: anti-tampering, viewport, invalid ID, cache busting, target blank rel, label bottom nav, dan alt avatar | [x] |
| 2026-07-07 | Tahap 2 | Hardening output/API visitor, escaping detail/list/header/home/footer, CSRF AJAX/view switcher, validasi URL admin, dan cast/clamp query helper | [x] |
| 2026-07-08 | Tahap 3 | Wiring theme accent color, validasi navbar menu builder, dan sembunyikan opsi admin legacy yang belum punya consumer aktif | [x] |
| 2026-07-08 | Tahap 4 | Cleanup asset dan dead code: hapus Vegas, Tailwind sample, sample hero, slide lama, opsi legacy, dan query hero tidak terpakai | [x] |
| 2026-07-08 | Tahap 5 | Rapikan token CSS, kecilkan override header, pindahkan inline footer CSS/JS ke asset, tambah namespace root dan focus-visible; split besar CSS ditahan untuk regresi visual | [!] |
| 2026-07-08 | Tahap 6 | Tambah ARIA label, focus/async states, skeleton loader, URLSearchParams search, visitor quote timeout/fallback/toggle, dan submit busy state | [x] |
| 2026-07-08 | Tahap 7 | Catat dependency lokal, tetapkan strategi Vue 2 scope terbatas, pindahkan custom JS non-plugin ke vanilla JS/fetch, dan dokumentasikan dependency yang masih perlu jQuery | [x] |
