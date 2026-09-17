# Review Template Custom SLiMS Bulian - Rasamala

Tanggal review: 2026-07-22  
Lokasi review: `template/rasamala`  
Fokus: bug, risiko keamanan, performa, maintainability, aksesibilitas, dan ide peningkatan.

## Ringkasan Singkat

Template Rasamala sudah jauh lebih matang dibanding template klasik polos: sudah ada helper escaping (`themeEscape`), sanitasi HTML (`themeSanitizeHtml`), validasi URL menu/topik, pengaturan preset tema, mode gelap, kartu digital anggota, visitor log modern, running text, dan beberapa fitur OPAC yang cukup lengkap.

Hasil cek cepat:

- `php -l` untuk semua file PHP/INC di `template/rasamala` bersih: tidak ada syntax error.
- Aset sudah banyak dibuat lokal, termasuk font dan library utama.
- Folder template cukup besar: sekitar 25 MB, 1672 file total, dan 654 file di `assets`.

Prioritas utama berikutnya sebaiknya bukan menambah fitur dulu, tetapi merapikan beberapa bug perilaku dan hutang teknis kecil yang bisa mengganggu produksi.

## Temuan Bug Prioritas Tinggi

### 1. Status ketersediaan koleksi di mode grid tidak seakurat mode simple/list

Lokasi:

- `biblio_list_template.php:103-108`
- `biblio_list_template.php:304-317`
- `biblio_list_template.php:320-341`

Mode simple/list memakai `rasamalaGetItemsAndAvailability()` yang sudah memperhitungkan status item `no_loan` dan peminjaman aktif. Mode grid masih memakai `getAvailability()` yang hanya menghitung total item dikurangi item yang sedang dipinjam. Akibatnya, item dengan status tidak dapat dipinjam bisa tetap terlihat tersedia di grid.

Saran perbaikan:

- Pakai sumber data yang sama untuk semua view.
- Jadikan `rasamalaGetItemsAndAvailability()` sebagai fungsi utama, lalu grid memakai nilai `available` dari fungsi tersebut.
- Jika butuh ringkas, buat fungsi `rasamalaGetAvailabilityCount()` yang tetap memakai logika `no_loan`.

Contoh arah patch:

```php
$item_availability_data = rasamalaGetItemsAndAvailability($dbs, $biblio_id);
$availability = $item_availability_data['available'];
```

### 2. `getRandomBiblio()` berpotensi error ketika jumlah koleksi lebih kecil dari limit

Lokasi:

- `classic.php:188-223`
- Khususnya `classic.php:201-202`

Kode saat ini memanggil `rand(0, $count - $limit)`. Jika jumlah koleksi lebih kecil dari limit, nilai maksimum menjadi negatif. Pada PHP modern ini bisa menjadi `ValueError`.

Saran perbaikan:

```php
$max_offset = max(0, $count - $limit);
$offset = random_int(0, $max_offset);
```

Catatan tambahan: fungsi ini belum terlihat dipanggil oleh file template Rasamala saat ini, tetapi tetap sebaiknya diperbaiki karena helper publik template mudah dipakai lagi di kemudian hari.

### 3. Toolbar view hasil pencarian bisa notice/break jika konfigurasi layout invalid

Lokasi:

- `parts/_result-search.php:55-57`
- `parts/_result-search.php:95`
- `parts/_result-search.php:143-145`

`$current_view` diambil dari session atau konfigurasi `classic_search_result_layout`, lalu langsung dipakai sebagai indeks `$view_options[$current_view]`. Jika konfigurasi lama, hasil migrasi, atau session berisi nilai di luar `simple`, `list`, `grid`, halaman bisa memunculkan notice `Undefined array key`.

Saran perbaikan:

```php
if (!isset($view_options[$current_view])) {
    $current_view = 'simple';
}
```

Lebih baik lagi: samakan validasi ini dengan validasi di `index_template.inc.php` dan `biblio_list_template.php`.

### 4. Akses `$_SESSION['m_image']` di kartu anggota belum selalu aman

Lokasi:

- `parts/_member.php:314`
- `parts/_member.php:362`

Kode memakai `$_SESSION['m_image']` langsung. Jika member login tidak punya key session tersebut, PHP bisa memunculkan warning `Undefined array key`.

Saran perbaikan:

```php
$member_image_session = $_SESSION['m_image'] ?? '';
$member_image = $member_image_session && file_exists(IMGBS . 'persons/' . $member_image_session)
    ? $member_image_session
    : 'person.png';
```

### 5. Penggantian HTML member area dengan regex masih rapuh

Lokasi:

- `parts/_member.php:493-504`

Regex `#(<div class="bg-white border-right border-bottom border-left p-4">).*?(</div>)#is` berisiko memotong konten pada `</div>` pertama, bukan penutup container yang benar, terutama jika struktur HTML inti SLiMS berubah atau berisi nested div kompleks.

Saran perbaikan:

- Hindari replace blok HTML besar dengan regex.
- Jika harus transform HTML hasil core, pakai marker yang lebih spesifik atau `DOMDocument`.
- Alternatif lebih sehat: render halaman `my_card` sebagai branch template sendiri, bukan memodifikasi `$main_content` hasil core.

## Temuan Keamanan dan Hardening

### 1. CSP masih longgar dan ditempatkan setelah banyak script

Lokasi:

- `parts/header.php:385-407`

Meta CSP diletakkan setelah beberapa `<script>` sudah dimuat. Selain itu `script-src` masih memakai `'unsafe-inline'` dan `'unsafe-eval'`. Ini membatasi manfaat CSP sebagai lapisan mitigasi XSS.

Saran:

- Letakkan CSP sedekat mungkin di awal `<head>`.
- Inventaris inline script yang masih dibutuhkan.
- Jika ingin CSP ketat, pindahkan inline JS ke file aset dan hindari Vue in-DOM template yang membutuhkan compiler/eval.
- Untuk tahap awal, tetap dokumentasikan alasan `unsafe-eval` masih dibutuhkan.

### 2. Output `$js` dari core masih hanya difilter dengan `strip_tags`

Lokasi:

- `parts/header.php:399-403`

`strip_tags(..., '<script><link>')` masih mengizinkan tag script/link lengkap beserta atributnya. Ini mungkin dibutuhkan oleh core SLiMS, tetapi dari sisi hardening lebih aman jika sumber `$js` dianggap trusted-only dan dikomentari jelas.

Saran:

- Validasi bahwa `$js` hanya berasal dari core/admin SLiMS.
- Jika memungkinkan, whitelist atribut `src`, `href`, `rel`, `type`.
- Hindari mencampur string script dinamis dari konten publik.

### 3. Sanitasi custom CSS masih berbasis regex sederhana

Lokasi:

- `parts/header.php:363-373`

Custom CSS admin dibersihkan dari tag HTML, `javascript`, `vbscript`, `expression`, dan `@import`. Ini sudah membantu, tetapi CSS tetap bisa dipakai untuk remote tracking via `url(https://...)`, overlay UI, atau style yang merusak layout.

Saran:

- Anggap custom CSS sebagai fitur admin-trusted.
- Batasi panjang input dan dokumentasikan risiko.
- Pertimbangkan allowlist property untuk mode aman, atau setidaknya opsi "reset custom CSS".

### 4. Validasi gambar berita belum memakai helper terpusat

Lokasi:

- `news_template.php:10-31`

`rasamalaNewsFirstImageSrc()` menolak `javascript:` dan `data:text/html`, tetapi belum memakai helper `themeSafeContentImageSrc()` yang sudah lebih terpusat.

Saran:

- Ganti validasi src gambar berita agar memakai `themeSafeContentImageSrc()`.
- Pertimbangkan menolak `http://` untuk menghindari mixed content.

### 5. `themeSafeMenuUrl()` masih memperbolehkan URL eksternal `http://`

Lokasi:

- `theme_helpers.php:1385-1411`

Ini tidak selalu bug, tetapi untuk OPAC publik lebih aman jika link eksternal default-nya `https://`, sementara `mailto:` dan `tel:` tetap boleh.

Saran:

- Untuk menu publik, pertimbangkan mode strict: relative URL, anchor, `https`, `mailto`, `tel`.
- Tambahkan indikator external link di navbar jika URL menuju domain luar.

## Performa dan Stabilitas

### 1. Widget waktu sholat melakukan request eksternal pada cache miss

Lokasi:

- `parts/waktu_sholat.php:36-59`
- `parts/waktu_sholat.php:72-80`
- Default opsi di `tinfo_options.inc.php:774-784`

Pada cache miss, halaman bisa menunggu request ke `api.aladhan.com` sampai 3 detik. Ini berdampak langsung ke render footer. Di jaringan sekolah/kampus yang memblokir koneksi luar, pengalaman bisa terasa lambat.

Saran:

- Jadikan default widget eksternal `hide` atau `footer` saja, bukan `both`, jika target produksi ingin ringan.
- Tambahkan fallback statis atau cache database.
- Simpan cache dengan TTL dan logging sederhana.
- Tampilkan status "jadwal belum tersedia" tanpa menahan render halaman.

### 2. Aset template masih gemuk untuk deployment

Data cepat:

- Folder `template/rasamala`: sekitar 25 MB.
- Total file: 1672.
- File assets: 654.
- Masih ada file `.map`, LESS/SCSS Font Awesome, Bootstrap non-minified, `popper.min.js`, dan `vue.js` versi 2.6.11 yang tidak dimuat oleh template utama.

Lokasi contoh:

- `assets/js/vue.min.js` adalah Vue 3.5.39 dan dipakai.
- `assets/js/vue.js` adalah Vue 2.6.11 dan tampaknya hanya artefak lama.
- `assets/js/bootstrap.bundle.min.js` dipakai.
- `assets/js/bootstrap.js`, `bootstrap.bundle.js`, `bootstrap.min.js`, beberapa `.map`, LESS, dan SCSS tampak tidak diperlukan untuk produksi.

Saran:

- Buat daftar aset yang benar-benar dipakai.
- Hapus source map dan source library dari paket produksi.
- Simpan source SCSS/LESS di repo pengembangan saja, bukan di template deploy.
- Pertimbangkan subset flag icon hanya untuk bahasa yang ditampilkan.

### 3. `ResizeObserver` dipakai tanpa fallback

Lokasi:

- `assets/js/app_jquery.js:391-404`

Jika browser/kiosk lama belum mendukung `ResizeObserver`, script bisa berhenti dan fitur setelahnya tidak berjalan.

Saran:

```js
if ('ResizeObserver' in window) {
    // observe ticker
} else {
    window.addEventListener('resize', adjustTickerMarqueeSpeeds);
}
```

### 4. Riwayat pencarian di `localStorage` tidak dibatasi ukuran totalnya

Lokasi:

- `assets/js/app.js:619-705`

UI hanya membaca 5 item terakhir, tetapi data yang disimpan bisa terus bertambah.

Saran:

- Batasi maksimal 20-30 keyword.
- Abaikan keyword kosong atau terlalu panjang.
- Bungkus semua akses `localStorage` dengan `try/catch` untuk mode privat browser.

## Maintainability

### 1. Inline CSS/JS di PHP sudah terlalu banyak

Lokasi contoh:

- `parts/header.php`
- `parts/_result-search.php`
- `parts/_member.php`
- `visitor_template.php`
- `tinfo_helpers.php`

Inline script/style membuat CSP sulit diketatkan, review lebih berat, dan perubahan UI rawan tersebar.

Saran:

- Pindahkan script besar ke file `assets/js`.
- Kirim konfigurasi dari PHP lewat `data-*` atau satu objek JSON kecil.
- Pindahkan style besar ke `assets/css`, sisakan CSS variable dinamis saja di PHP.

### 2. Folder `template/rasamala/.git` masih ada

Lokasi:

- `template/rasamala/.git`

Ini bisa membuat Git utama, backup, zip template, atau proses deploy membaca Rasamala sebagai repo nested. Status nested repo juga menunjukkan banyak perubahan lokal.

Saran:

- Jika folder ini hanya template di dalam repo utama, keluarkan `.git` dari paket template.
- Jika memang submodule, deklarasikan sebagai submodule resmi.
- Jangan ikutkan `.git` saat distribusi template produksi.

### 3. Nama state visitor non-member masih membingungkan

Lokasi:

- `visitor_template.php:611-614`
- `visitor_template.php:882-893`

Form non-member memakai `memberId` sebagai model untuk nama lengkap. Secara runtime bisa jalan, tetapi membingungkan saat debugging.

Saran:

- Pisahkan state `memberId`, `visitorName`, dan `institution`.
- Saat submit, map ke field backend yang memang dibutuhkan SLiMS.

### 4. Beberapa dokumen review lama masih tersisa

Lokasi:

- `rasamala_theme_review.md`
- `review-keamanan.md`
- `member-area-improvement.md`
- `todo.md`
- `todo2.md`

Ini bagus sebagai jejak kerja, tetapi bisa membingungkan mana dokumen paling baru.

Saran:

- Tambahkan `docs/INDEX.md` atau `README.md` bagian "Dokumen review".
- Tandai dokumen lama sebagai superseded bila sudah digantikan.

## Aksesibilitas dan UX

### Hal yang sudah baik

- Banyak output teks sudah memakai `themeEscape()`.
- Banyak icon sudah diberi `aria-hidden`.
- Search input sudah punya `aria-label`.
- Tombol dark/light mode memakai `aria-pressed`.
- Modal memakai struktur Bootstrap yang familiar.

### Saran peningkatan

- Tambahkan mode `reduced motion` untuk animasi hero, ticker, cursor particles, dan reminder.
- Pastikan semua tombol icon-only punya label yang jelas dan konsisten.
- Pada mobile filter, tombol "Apply Filter" sebaiknya menutup modal setelah filter diterapkan.
- Untuk visitor kiosk, tambahkan mode "scan cepat" yang auto-clear dan auto-focus tanpa efek visual berat.
- Untuk kartu digital, tambahkan status kadaluarsa yang jelas: aktif, hampir habis, sudah kadaluarsa.

## Ide Fitur Lanjutan

### 1. Export/import konfigurasi tema

Tambahkan tombol export/import JSON untuk semua `classic_*` setting. Ini akan memudahkan memindahkan preset dari server dev ke produksi.

### 2. Preview preset di admin

Customizer sudah kaya. Tambahkan preview kecil untuk preset: simple, office, full, custom. Admin akan lebih cepat memilih tanpa trial-error.

### 3. Health check template

Buat halaman kecil khusus admin untuk mengecek:

- Versi Vue/Bootstrap yang dimuat.
- Aset hilang.
- Konfigurasi warna invalid.
- Custom CSS aktif.
- Koneksi API waktu sholat.
- Ukuran folder cache cover/thumb.

### 4. Rekomendasi koleksi terkait

Di halaman detail, tampilkan koleksi terkait berdasarkan subject, author, atau call number. Batasi 4-6 item agar tetap ringan.

### 5. Shelf/location UX

Availability sudah cukup detail. Bisa ditingkatkan dengan:

- Group lokasi dan nomor panggil.
- Badge "bisa dipinjam", "sedang dipinjam", "referensi saja".
- Link peta rak atau teks petunjuk lokasi.

### 6. Visitor log kiosk mode yang lebih operasional

Ide:

- Mode layar penuh otomatis.
- Statistik hari ini: total kunjungan, member, non-member.
- Pilihan fakultas/institusi dari konfigurasi admin, bukan hard-coded.
- Offline queue jika jaringan backend putus sementara.

### 7. Member area lebih mandiri

Kartu digital bisa ditingkatkan dengan:

- Download PNG/PDF.
- Copy member ID.
- QR payload configurable: hanya ID, URL profil, atau format custom.
- Validasi visual masa berlaku.

### 8. Pipeline kualitas ringan

Tambahkan script sederhana:

```bash
find template/rasamala -name '*.php' -o -name '*.inc.php' | sort | xargs -n 1 php -l
```

Lalu lanjutkan dengan:

- Cek aset yang tidak dipakai.
- Budget ukuran template.
- Cek `console.log`.
- Cek `target="_blank"` tanpa `rel`.
- Cek `$_SESSION[...]` tanpa fallback.

## Urutan Perbaikan yang Disarankan

1. Samakan logika availability grid dengan simple/list.
2. Tambahkan whitelist `$current_view` di `_result-search.php`.
3. Amankan akses `$_SESSION['m_image']` di `_member.php`.
4. Ganti `rand()` di `getRandomBiblio()` menjadi offset aman.
5. Hapus `console.log` production di member area.
6. Tambahkan fallback `ResizeObserver`.
7. Rapikan aset produksi: source map, Bootstrap/Vue lama, LESS/SCSS, dan `.git` nested.
8. Refactor bertahap inline JS/CSS ke file assets.
9. Ketatkan CSP setelah inline script berkurang.
10. Buat export/import konfigurasi tema dan health check admin.

## Catatan Penutup

Rasamala sudah punya fondasi yang kuat: escaping sudah banyak diterapkan, fitur customizer luas, dan tampilan OPAC/member/visitor sudah lebih modern. Risiko terbesar saat ini ada di inkonsistensi logika antar view, beberapa akses session tanpa fallback, dependency/aset yang terlalu gemuk, dan inline script/style yang membuat hardening keamanan lebih sulit.

Dengan memperbaiki daftar prioritas di atas, template ini akan lebih siap dipakai sebagai template produksi SLiMS Bulian yang stabil, ringan, dan mudah dirawat.
