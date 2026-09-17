# Review Template Rasamala - SLiMS 9 Bulian

Berikut adalah hasil review menyeluruh dari template `rasamala` pada SLiMS 9 Bulian beserta saran dan tips perbaikan. Review ini berasal dari fact-check tema lama `apple-theme` sebelum rename ke Rasamala.

> **Fact-check update — 2026-07-07**
>
> Bagian di bawah adalah review awal yang sekarang sudah dicek ulang terhadap source code. Beberapa klaim perlu dikoreksi: `Theme Accent Color` belum benar-benar dinamis, navbar builder bukan drag-and-drop, loading state Vue sebenarnya sudah ada, `style.css` berisi 2723 baris bukan 2724, ada 1361 `!important`, dan `date('this')` bukan string kosong melainkan cache key yang berubah per detik dengan format tanggal PHP yang salah.
>
> Temuan tambahan yang perlu masuk prioritas: beberapa komponen Vue memakai `innerHTML` untuk data katalog/API, beberapa output katalog/detail belum di-escape, `visitor_template.php` memakai `v-html` untuk pesan server yang dapat berisi nama anggota, beberapa opsi `tinfo.inc.php` terlihat belum tersambung ke output, dan konfigurasi slideshow Vegas tampak mati karena `vegas.js.php` tidak terlihat di-include oleh flow utama.

---

## 🔎 Ringkasan Fact-Check Terverifikasi

| Klaim / Area | Status | Bukti & Koreksi |
|--------------|--------|-----------------|
| `style.css` 2724 baris | ⚠️ Hampir benar | Hasil cek saat ini: **2723 baris**, **74,126 byte** |
| `!important` ratusan | ✅ Benar, perlu angka | Ada **1361** kemunculan `!important` di `assets/css/style.css` |
| `tailwind.min.css` 298KB tidak diload | ✅ Benar | File **298,158 byte** dan tidak direferensikan oleh PHP/theme utama. `samplehero.html` memakai CDN Tailwind, bukan file lokal |
| Theme Accent Color 6 palet | ❌ Fitur ada, output belum bekerja | `tinfo.inc.php` punya opsi warna, `header.php` menghitung `$selected_color`, tetapi CSS variables tetap hardcoded warm gray |
| Navbar builder drag-and-drop | ❌ Tidak akurat | Builder bisa add/remove/edit row, tetapi tidak ada drag/reorder |
| Loading state tidak ada | ❌ Tidak akurat | `slims-collection`, `slims-group-subject`, dan `slims-group-member` punya Bootstrap spinner saat loading |
| `date('this')` kosong | ❌ Tidak akurat | `date('this')` menghasilkan gabungan token tanggal PHP (`t`, `h`, `i`, `s`) dan berubah per detik; masalahnya adalah cache busting salah/terlalu agresif |
| Vue 2 EOL Desember 2023 | ✅ Benar | Dokumentasi resmi Vue menyebut Vue 2 EOL pada **31 Desember 2023**: https://v2.vuejs.org/lts/ |
| Duplicate viewport | ✅ Benar | Ada dua `<meta name="viewport">` di `parts/header.php` |
| Anti-tampering tersembunyi | ✅ Benar | `classic.php` membaca `parts/header.php` dan menyisipkan `<div id="vio"></div>` jika string author hilang |

---

## 🚨 Temuan Tambahan Hasil Fact-Check

### A. Theme Accent Color belum terhubung ke CSS dinamis

**File**: `parts/header.php#L81-L113`, `tinfo.inc.php#L342-L355`

`header.php` menghitung:

```php
$selected_color = $colors[$theme_color] ?? $colors['warmgray'];
```

Namun CSS yang dicetak tetap:

```css
--theme-accent-color: var(--apple-accent);
--theme-accent-rgb: 139, 115, 85;
```

**Saran**:

```php
--apple-accent: <?= htmlspecialchars($selected_color['primary'], ENT_QUOTES, 'UTF-8') ?>;
--theme-accent-color: <?= htmlspecialchars($selected_color['primary'], ENT_QUOTES, 'UTF-8') ?>;
--theme-accent-rgb: <?= htmlspecialchars($selected_color['rgb'], ENT_QUOTES, 'UTF-8') ?>;
--theme-accent-glow: rgba(<?= htmlspecialchars($selected_color['rgb'], ENT_QUOTES, 'UTF-8') ?>, 0.8);
--theme-accent-glow-half: rgba(<?= htmlspecialchars($selected_color['rgb'], ENT_QUOTES, 'UTF-8') ?>, 0.4);
```

### B. Vue `innerHTML` pada data API/katalog berisiko XSS

**File**: `assets/js/app.js#L129-L147`, `#L149-L206`, `#L208-L310`

Komponen Vue memakai `domProps.innerHTML` untuk `topic`, `title`, dan `memberType`:

```js
innerHTML: this.topic
innerHTML: this.title
innerHTML: this.memberType
```

Jika data katalog/topik/tipe anggota mengandung HTML berbahaya, browser akan merendernya.

**Saran**: render sebagai text node:

```js
createElement('span', this.topic)
createElement('div', { attrs: { class: 'card-text mt-2' } }, this.title)
createElement('small', { attrs: { class: 'text-secondary' } }, this.memberType)
```

### C. Output katalog dan detail banyak yang belum di-escape

**File**: `biblio_list_template.php`, `detail_template.php`

Contoh:

```php
$output .= '<h5><a ...>'.stripslashes(addEllipsis($title, 80)).'</a></h5>';
$output .= '<p>'.$notes.'</p>';
$output .= '<a ... data-title="'.$title.'">';
<?= stripslashes($title); ?>
<?= $notes ? $notes : '<i>'.__('Description Not Available').'</i>'; ?>
```

**Saran**:

- Teks biasa: `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`
- Atribut HTML: selalu `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
- Field HTML admin seperti notes/description: gunakan sanitizer allowlist, bukan echo mentah

### D. `visitor_template.php` memakai `v-html` untuk pesan server

**File**: `visitor_template.php#L91`, `lib/contents/visitor.inc.php#L66-L72`

Template:

```html
<h3 class="font-weight-bold mb-0 visitor-welcome-title" v-html="textInfo"></h3>
```

Backend menyusun pesan dengan nama anggota:

```php
$message = $memberName . __(', thank you for inserting your data to our visitor log');
```

Jika nama anggota tidak dijamin aman, `v-html` dapat merender HTML dari data anggota.

**Saran**: return JSON dengan `message` dan `type`, render pesan dengan `{{ textInfo }}`/`v-text`, lalu styling error/success dilakukan via class Vue.

### E. Beberapa opsi `tinfo.inc.php` tampak belum tersambung

Opsi yang perlu audit:

- `classic_theme_color`: opsi ada, tetapi CSS belum berubah
- `classic_slide_transition`, `classic_slide_animation`, `classic_slide_delay`, `classic_library_disableslide`: `assets/js/vegas.js.php` ada, tetapi tidak ditemukan include aktif di flow utama
- `classic_popular_collection_item` dan `classic_new_collection_item`: tidak terlihat dipakai oleh `_home.php`; komponen Vue memanggil API tanpa parameter limit
- `classic_suggestion`: ada di konfigurasi, tetapi tidak ditemukan pemakaian di theme

**Saran**: buat tabel audit `option -> consumer -> status` untuk memutuskan mana yang aktif, legacy, atau perlu dihapus.

### F. URL admin untuk map/social link dirender langsung

**File**: `parts/_home.php#L160-L171`

```php
src="<?= $sysconf['template']['classic_map_link']; ?>"
href="<?= $sysconf['template']['classic_fb_link'] ?>"
```

**Saran**: escape atribut, validasi skema URL (`https://`), dan tambahkan `rel="noopener noreferrer"` untuk semua `target="_blank"`.

### G. CSRF protection masih parsial

Klaim “CSRF token diterapkan” perlu dipersempit:

- Visitor counter memakai token dan backend memvalidasi
- Footer search punya hidden `csrf_token`
- View switcher list-to-grid punya token
- Tetapi AJAX add-to-basket/bookmark di `app_jquery.js` tidak mengirim token eksplisit
- `index_template.inc.php#L18` menerima `$_POST['view']` langsung ke session
- Branch grid-to-list di `_result-search.php#L76-L80` tidak menyertakan token

### H. Contrast warm gray gagal WCAG AA untuk teks normal

Kombinasi `#8B7355` pada background `#F5F5F5` punya contrast ratio sekitar **4.11:1**.

- Gagal AA untuk teks normal, minimal 4.5:1
- Lolos untuk teks besar, minimal 3:1

### I. Hero product block di-comment tetapi query tetap jalan

**File**: `_home.php#L16-L28`, `_home.php#L40-L62`

Query `$hero_book_q` tetap dieksekusi untuk mencari buku terbaru dengan cover, tetapi blok hero product yang memakai hasilnya dikomentari penuh. Ini membuat query database tidak terpakai pada homepage.

### J. Visitor counter mengambil quote dari API eksternal

**File**: `visitor_template.php#L130-L142`

```js
axios.get('https://slims.web.id/kutipan/')
```

Ada fallback lokal, tetapi setiap halaman visitor counter mencoba request eksternal.

**Saran**: jadikan toggleable, cache quote, atau tambahkan timeout eksplisit.

---

## 📊 Ringkasan Penilaian

| Aspek | Skor | Keterangan |
|-------|------|------------|
| **Desain Visual** | ⭐⭐⭐⭐ | Konsisten Apple-style, warm gray palette elegan |
| **Arsitektur Kode** | ⭐⭐⭐ | Modular via `parts/`, tapi beberapa file terlalu besar |
| **Keamanan** | ⭐⭐½ | XSS prevention ada di navbar/bottom nav, tetapi output katalog, Vue `innerHTML`, dan CSRF masih parsial |
| **Responsivitas** | ⭐⭐⭐⭐ | Mobile bottom nav, filter modal, adaptif |
| **Kustomisasi** | ⭐⭐⭐ | Banyak opsi di `tinfo.inc.php`, tetapi sebagian belum tersambung ke output |
| **Performa** | ⭐⭐⭐ | Ada ruang perbaikan caching & asset loading |
| **SEO** | ⭐⭐⭐⭐ | OG tags & Twitter cards sudah ada |
| **Maintainability** | ⭐⭐⭐ | CSS 2700+ baris, perlu refactoring |

---

## ✅ Hal-Hal yang Sudah Baik

### 1. Design System yang Konsisten
CSS variables di `:root` sudah terdefinisi rapi dengan naming convention `--apple-*`:
```css
--apple-dark-bg: #424242;
--apple-light-bg: #F5F5F5;
--apple-accent: #8B7355;
--apple-font-stack: -apple-system, BlinkMacSystemFont, ...
```

### 2. Tema Warna Dinamis
Fitur **Theme Accent Color** di [tinfo.inc.php](file:///var/www/html/slims/s951dev/98/template/rasamala/tinfo.inc.php#L342-L355) menyediakan 6 palet warna (warmgray, cyan, emerald, orange, gold, pink), tetapi fact-check menunjukkan output CSS di `header.php` masih hardcoded warm gray. Jadi ini adalah fitur yang desainnya sudah ada, namun implementasinya belum selesai.

### 3. Navbar Menu Builder
GUI menu builder di bagian [tinfo.inc.php#L366-L514](file:///var/www/html/slims/s951dev/98/template/rasamala/tinfo.inc.php#L366-L514) memberikan pengalaman admin yang lebih nyaman daripada textarea mentah. Catatan fact-check: builder ini mendukung add/remove/edit row, tetapi belum drag-and-drop atau reorder menu.

### 4. Mobile Bottom Navigation
Implementasi bottom nav bar ala iOS di [footer.php#L124-L304](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L124-L304) lengkap dengan:
- Overflow menu "More" sheet
- Badge untuk basket
- Dynamic menu berdasarkan konfigurasi navbar

### 5. XSS Protection
Sudah ada sanitasi yang baik di navbar dan bottom nav:
```php
$safe_url = htmlspecialchars($main_menu['url'], ENT_QUOTES, 'UTF-8');
// Prevent dangerous protocol injection (javascript:, data:)
$lower_url = strtolower(trim($main_menu['url']));
if (strpos($lower_url, 'javascript:') === 0 || strpos($lower_url, 'data:') === 0) {
    $safe_url = '#';
}
```

### 6. Glassmorphism Effects
Modal dan header menggunakan `backdrop-filter: blur()` yang konsisten ala Apple.

---

## ⚠️ Masalah yang Ditemukan & Saran Perbaikan

---

### 🔴 Prioritas Tinggi (Harus Diperbaiki)

#### 1. SQL Injection di `classic.php`

> [!CAUTION]
> Fungsi-fungsi di [classic.php](file:///var/www/html/slims/s951dev/98/template/rasamala/classic.php) menggunakan interpolasi variabel langsung ke query SQL tanpa prepared statements.

**File**: [classic.php#L41-L50](file:///var/www/html/slims/s951dev/98/template/rasamala/classic.php#L41-L50)
```php
// ❌ RENTAN SQL Injection
function getPopularBiblio($dbs, $limit = 5) {
    $sql = "... LIMIT {$limit}";
}

function getActiveMembers($dbs, $year, $limit = 3) {
    $sql = "... WHERE l.loan_date LIKE '{$year}-%' ...";
}
```

**Saran**: Gunakan `intval()` untuk `$limit` dan prepared statements untuk parameter string:
```php
// ✅ Aman
function getPopularBiblio($dbs, $limit = 5) {
    $limit = intval($limit);
    $sql = "... LIMIT {$limit}";
}
```

#### 2. Kode Anti-Tampering Tersembunyi

**File**: [classic.php#L182](file:///var/www/html/slims/s951dev/98/template/rasamala/classic.php#L182)
```php
$content = file_get_contents(__DIR__ . '/parts/header.php');
if (!strpos(strtolower($content), implode('', ['i', 'd', 'o', '.', 'a', 'l', 'i', 't'])))
    echo '<div id="' . implode('', ['v', 'i', 'o']) . '"></div>';
```

Ini membaca file header saat runtime dan memeriksa keberadaan string tertentu (`ido.alit`). Ini:
- Menambah overhead I/O setiap request
- Merupakan pola anti-tampering yang tidak perlu di template open-source
- Sebaiknya dihapus atau diganti dengan meta tag proper

#### 3. Unescaped Output di `footer.php`

**File**: [footer.php#L44](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L44)
```php
// ❌ Output langsung tanpa escaping
<?= $sysconf['template']['classic_footer_about_us']; ?>
```

Karena konten ini berasal dari CKEditor (yang disimpan admin), risikonya rendah. Tapi jika ada stored XSS dari admin yang compromised, ini akan tereksekusi. Pertimbangkan sanitasi menggunakan `htmlspecialchars()` atau library purifier.

#### 4. Duplikat Meta Viewport Tag

**File**: [header.php#L20 & L35](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L20-L35)
```html
<!-- Line 20 -->
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Line 35 - DUPLIKAT! -->
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
```

**Saran**: Hapus salah satu, gunakan satu yang lengkap:
```html
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
```

---

### 🟡 Prioritas Sedang (Sebaiknya Diperbaiki)

#### 5. CSS Terlalu Besar (2723 Baris, 74KB)

[style.css](file:///var/www/html/slims/s951dev/98/template/rasamala/assets/css/style.css) berisi **2723 baris** dan **74,126 byte** dalam satu file. Ini menyulitkan maintenance.

**Saran**: Pecah menjadi beberapa file modular:
```
assets/css/
├── _variables.css       ← Design tokens
├── _base.css            ← Typography, scrollbar, reset
├── _navbar.css          ← Navbar + header
├── _search.css          ← Search form + filters
├── _cards.css           ← Cards, grid items
├── _detail.css          ← Detail page
├── _footer.css          ← Footer
├── _mobile.css          ← Mobile bottom nav
├── _visitor.css         ← Visitor counter page
├── _modal.css           ← All modals
└── style.css            ← Import all via @import
```

#### 6. Terlalu Banyak `!important` (1361 Instance)

Ada **1361** kemunculan `!important` di `style.css`. Ini membuat customization menjadi sangat sulit dan rentan cascade conflict.

**Saran**: 
- Tingkatkan specificity menggunakan selector yang lebih spesifik daripada `!important`
- Gunakan class-based namespacing: `.rasamala-theme .navbar { ... }` daripada `.navbar { ... } !important`
- `!important` sebaiknya hanya untuk utility classes

#### 7. Duplikat CSS Variable Definitions

CSS variables didefinisikan di **dua tempat**:
1. [style.css#L11-L25](file:///var/www/html/slims/s951dev/98/template/rasamala/assets/css/style.css#L11-L25) — statik
2. [header.php#L92-L113](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L92-L113) — dinamis dari PHP

Ini menyebabkan duplikasi dan potensi konflik. Nilai di `header.php` akan menimpa `style.css`, membuat definisi di `style.css` **mubazir**.

**Saran**: jangan hapus semua variable dari `style.css` secara mentah. Simpan default/fallback static variables di `style.css`, lalu di `header.php` override hanya variable yang benar-benar dinamis seperti accent color dan RGB.

#### 8. Inline CSS di `footer.php` (150+ baris)

**File**: [footer.php#L359-L494](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L359-L494)

Ada `<style>` block **150+ baris** untuk floating buttons, modal genie transition, dan mobile overrides yang ditulis inline.

**Saran**: Pindahkan ke `style.css`. Kalau perlu data dinamis PHP (seperti `$show_floating_info`), gunakan CSS custom property atau data attribute:
```html
<body data-floating-info="<?= $show_floating_info ? 'true' : 'false' ?>">
```
```css
body[data-floating-info="true"] .btn-back-to-top.visible {
    transform: scale(1) translateY(-60px) rotate(360deg);
}
```

#### 9. Hero Section yang Di-comment

**File**: [_home.php#L40-L62](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L40-L62)

Seluruh blok hero product di-comment out (`<!-- ... -->`). Fact-check tambahan: query `$hero_book_q` di [_home.php#L16-L28](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L16-L28) tetap berjalan, padahal hasilnya hanya dipakai oleh blok yang sedang di-comment.

**Saran**: Jadikan fitur yang toggleable via `tinfo.inc.php`, atau hapus sepenuhnya jika tidak digunakan.

#### 10. File Tailwind CSS Ada Tapi Tidak Digunakan Theme Utama

Ada file [tailwind.min.css](file:///var/www/html/slims/s951dev/98/template/rasamala/assets/css/tailwind.min.css) (**298,158 byte**) di folder assets, tapi tidak diload di `header.php` dan tidak direferensikan oleh `style.css`. `samplehero.html` memakai CDN Tailwind, bukan file lokal ini. Jika memang tidak digunakan, hapus atau pindahkan ke folder sample/dev-only.

#### 11. Cache Busting Tidak Konsisten

**File**: [header.php#L69 vs L78](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L69-L78)
```php
// ❌ Typo: date('this') bukan cache key yang bermakna
href="<?php echo JWB; ?>toastr/toastr.min.css?<?php echo date('this') ?>"

// ❌ Ini juga berubah per detik, sehingga browser sulit cache
href="<?php echo assets('css/style.css?v=' . date('Ymd-his')); ?>"
```

`date('this')` tidak kosong; ia menghasilkan gabungan token format tanggal PHP (`t`, `h`, `i`, `s`) dan berubah per detik. `date('Ymd-his')` juga berubah per detik. Keduanya terlalu agresif untuk cache busting asset statis.

**Saran**: Gunakan file modification time:
```php
'style.css?v=' . filemtime(__DIR__ . '/../assets/css/style.css')
```

---

### 🟢 Prioritas Rendah (Nice to Have)

#### 12. Teks Hardcoded Bahasa Indonesia di Bottom Nav

**File**: [footer.php#L181 & L254](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L181-L254)
```php
'text' => __('Lainnya'),     // Sebagian ID
__('Menu Lainnya')           // Sebagian ID
```

**Saran**: Ganti dengan key bahasa Inggris sebagai base, biarkan translation handle-nya:
```php
'text' => __('More'),
__('More Menu')
```

#### 13. ID Attribute Invalid di HTML

**File**: [_home.php#L31](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L31) dan [_other.php#L11](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_other.php#L11)
```html
<!-- ❌ ID tidak boleh mengandung spasi -->
<section id="section1 container-fluid">
```

**Saran**: Pisahkan ID dan class:
```html
<section id="section1" class="container-fluid">
```

#### 14. Accessibility Improvements

- Navbar toggler tidak memiliki focus indicator yang terlihat
- Floating buttons kurang `aria-label` yang deskriptif
- Color contrast ratio `#8B7355` (warm gray accent) pada background `#F5F5F5` mungkin gagal WCAG AA untuk teks kecil

**Saran**:
```html
<button id="floating-info-btn" aria-label="<?= __('Show library information') ?>">
<button id="back-to-top" aria-label="<?= __('Scroll to top') ?>">
```

#### 15. Vue.js 2 yang Sudah EOL

Template menggunakan [Vue.js 2](file:///var/www/html/slims/s951dev/98/template/rasamala/assets/js/vue.min.js) yang sudah End-of-Life sejak Desember 2023.

**Saran**: Karena template ini hanya menggunakan Vue untuk fitur sederhana (search form, visitor counter), pertimbangkan:
- Migrasi ke Vue 3 (minimal effort)
- Atau ganti dengan vanilla JS / Alpine.js yang lebih ringan

#### 16. Tingkatkan Loading State / Skeleton

Saat komponen SLiMS async (`<slims-collection>`, `<slims-group-subject>`, `<slims-group-member>`) memuat data, sudah ada visual loading state berupa Bootstrap spinner. Yang belum ada adalah skeleton loader, empty state, dan error state saat `fetch()` gagal.

**Saran**: Tambahkan skeleton loader CSS:
```css
.skeleton-loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-pulse 1.5s ease-in-out infinite;
}
```

#### 17. Dark Mode Support

Tema ini sudah punya variabel `--apple-dark-bg` dan `--apple-text-primary-dark` tapi **belum diimplementasi**. Ini peluang besar untuk value-add.

**Saran**: Tambahkan `prefers-color-scheme: dark` support:
```css
@media (prefers-color-scheme: dark) {
    :root {
        --apple-light-bg: #1d1d1f;
        --apple-text-primary: #f5f5f7;
        /* ... */
    }
}
```

#### 18. Optimasi Font Loading

Saat ini menggunakan system font stack (baik!), tapi jika ingin menambah Google Fonts, gunakan:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preload" as="style" href="...">
```

---

## 🏗️ Arsitektur & Saran Refactoring

### Struktur File Saat Ini vs Rekomendasi

```diff
 template/rasamala/
 ├── assets/
 │   ├── css/
-│   │   ├── style.css           (2723 baris, monolitik)
-│   │   ├── tailwind.min.css    (298KB, tidak digunakan?)
+│   │   ├── _variables.css
+│   │   ├── _base.css
+│   │   ├── _components.css
+│   │   ├── _pages.css
+│   │   ├── _responsive.css
+│   │   └── style.css           (hanya @import)
 │   └── js/
 │       ├── app.js
 │       └── app_jquery.js
 ├── parts/
-│   ├── footer.php              (515 baris, terlalu banyak)
+│   ├── footer.php              (80 baris, footer saja)
+│   ├── _bottom-nav.php         (mobile bottom nav)
+│   ├── _floating-buttons.php   (back-to-top, info)
 │   ├── header.php
 │   └── ...
-├── classic.php                 (274 baris, helper + anti-tampering)
+├── classic.php                 (helper functions, cleaned)
 └── tinfo.inc.php
```

---

## 📝 Checklist Perbaikan

| # | Item | Prioritas | Effort |
|---|------|-----------|--------|
| 1 | Fix `classic_theme_color` agar benar-benar mengubah CSS variables | 🔴 Tinggi | Rendah |
| 2 | Ganti Vue `innerHTML`/visitor `v-html` yang tidak perlu dengan text rendering | 🔴 Tinggi | Rendah-Sedang |
| 3 | Escape/sanitasi output katalog, detail, footer HTML, dan atribut URL | 🔴 Tinggi | Sedang |
| 4 | Escape dan validasi map/social URL admin settings | 🔴 Tinggi | Rendah |
| 5 | Hapus anti-tampering code | 🔴 Tinggi | Rendah |
| 6 | Cast/validasi parameter SQL helper; hapus helper usang jika tidak dipakai | 🔴 Tinggi | Rendah-Sedang |
| 7 | Lengkapi CSRF untuk semua POST yang mengubah state | 🟡 Sedang | Sedang |
| 8 | Fix duplikat viewport meta | 🟡 Sedang | Rendah |
| 9 | Fix invalid HTML ID `section1 container-fluid` | 🟡 Sedang | Rendah |
| 10 | Pindahkan inline CSS dari `footer.php` ke `style.css` | 🟡 Sedang | Sedang |
| 11 | Jadikan CSS variables static sebagai fallback dan override hanya variable dinamis | 🟡 Sedang | Rendah |
| 12 | Hapus/pindahkan `tailwind.min.css` jika hanya asset sample | 🟡 Sedang | Rendah |
| 13 | Audit opsi `tinfo.inc.php` yang tidak terpakai/terputus | 🟡 Sedang | Sedang |
| 14 | Hapus atau aktifkan Vegas slideshow assets/config | 🟡 Sedang | Sedang |
| 15 | Ganti cache busting berbasis `date()` per detik dengan `filemtime()`/helper versi | 🟡 Sedang | Rendah |
| 16 | Pecah `style.css` menjadi modular | 🟡 Sedang | Tinggi |
| 17 | Kurangi penggunaan `!important` | 🟡 Sedang | Tinggi |
| 18 | Hapus query hero yang tidak dipakai atau aktifkan/toggle hero product | 🟢 Rendah | Rendah |
| 19 | Ganti teks hardcoded ID ke base EN | 🟢 Rendah | Rendah |
| 20 | Tambahkan accessibility (aria-label, focus, alt dinamis) | 🟢 Rendah | Sedang |
| 21 | Perbaiki contrast accent untuk teks kecil | 🟢 Rendah | Rendah |
| 22 | Tambahkan skeleton/empty/error state untuk komponen async | 🟢 Rendah | Sedang |
| 23 | Pertimbangkan Vue 3 atau Alpine.js | 🟢 Rendah | Tinggi |
| 24 | Implementasi dark mode | 🟢 Rendah | Tinggi |

---

## 🎯 Kesimpulan

Template **rasamala** secara visual dan mobile experience sudah kuat, tetapi hasil fact-check menunjukkan beberapa klaim lama perlu diturunkan: fitur kustomisasi belum semuanya tersambung, dan coverage keamanan belum merata di semua output.

**Area utama yang perlu perhatian:**
1. **Keamanan output dan input** — Vue `innerHTML`, output katalog/detail, URL admin, CSRF parsial, dan helper SQL perlu dibersihkan
2. **Maintainability CSS** — file monolitik 2723 baris dengan 1361 `!important` perlu refactoring
3. **Opsi dan dead code cleanup** — theme color belum aktif, Vegas/slideshow tampak mati, hero product di-comment tapi query tetap jalan, Tailwind lokal tidak dipakai, dan anti-tampering code perlu dihapus

Dengan perbaikan pada item prioritas tinggi dan audit opsi admin, template ini akan jauh lebih siap untuk production use.
