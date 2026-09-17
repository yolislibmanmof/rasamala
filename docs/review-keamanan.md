# 🔒 Review Keamanan — Template Rasamala (SLiMS Bulian 9.5.1)

**Tanggal Audit**: 14 Juli 2026  
**Auditor**: Antigravity Security Review  
**Scope**: Seluruh file PHP dan JavaScript di `98/template/rasamala/`  
**Versi SLiMS**: Bulian 9.5.1-dev

---

## Ringkasan Eksekutif

Template Rasamala secara umum sudah menerapkan praktik keamanan yang **cukup baik** untuk ukuran template SLiMS. Fungsi-fungsi helper di `theme_helpers.php` sudah menyediakan escaping (`themeEscape`), sanitasi HTML (`themeSanitizeHtml`), validasi URL (`themeSafeMenuUrl`, `themeSafeHref`, `themeSafeHttpsUrl`), dan parameter binding yang aman. Namun, masih ditemukan beberapa celah keamanan yang perlu diperbaiki.

### Statistik Temuan

| Tingkat Keparahan | Jumlah |
|---|---|
| 🔴 **Kritis** (Critical) | 2 |
| 🟠 **Tinggi** (High) | 3 |
| 🟡 **Sedang** (Medium) | 5 |
| 🔵 **Rendah** (Low) | 4 |

---

## 🔴 Temuan Kritis

### K-01: Custom CSS Injection (Stored XSS via CSS)

**File**: [`header.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L210-L215)  
**CWE**: CWE-79 (Cross-site Scripting)

**Deskripsi**: Nilai `classic_custom_css` dari database langsung di-output tanpa sanitasi dalam tag `<style>`.

```php
// header.php baris 213
<?= $sysconf['template']['classic_custom_css']; ?>
```

**Dampak**: Admin yang memiliki akses ke pengaturan tema bisa menyisipkan kode JavaScript melalui teknik CSS injection, misalnya:

```css
/* payload berbahaya */
body { background: url("javascript:alert(1)"); }
/* atau memanfaatkan CSS expression di browser lama */
body { color: expression(alert(document.cookie)); }
```

Meskipun vektor serangan memerlukan akses admin, hal ini tetap berbahaya dalam skenario **privilege escalation** atau **admin account compromise**.

**Saran Perbaikan**:
```php
<?php
// Sanitasi CSS — hapus karakter berbahaya dan blok JS/expression
$custom_css = $sysconf['template']['classic_custom_css'] ?? '';
$custom_css = preg_replace('/(javascript|expression|url\s*\(\s*["\']?\s*javascript)\s*[:(\s]/i', '/* blocked */', $custom_css);
$custom_css = preg_replace('/[<>]/', '', $custom_css); // hapus tag HTML di dalam CSS
echo $custom_css;
?>
```

---

### K-02: Gambar Logo di Footer Tanpa Escaping (XSS)

**File**: [`footer.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L28-L32)  
**CWE**: CWE-79 (Cross-site Scripting)

**Deskripsi**: Atribut `src` gambar logo di footer menggunakan variabel `$path` tanpa melalui `themeEscape()`, berbeda dengan navbar yang sudah menggunakan escaping.

```php
// footer.php baris 29
echo '<img class="footer-brand-img" src="'.SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path.'&width=350">';
```

Bandingkan dengan **navbar** yang sudah aman:
```php
// _navbar.php baris 21 — sudah di-escape
echo '<img ... src="'.themeEscape(SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path.'&width=350').'" ...>';
```

**Dampak**: Jika `$path` (yang berasal dari `$sysconf['logo_image']`) mengandung karakter khusus HTML, bisa terjadi XSS via injeksi di atribut `src` tag `<img>`.

**Saran Perbaikan**:
```php
echo '<img class="footer-brand-img" src="'
    . themeEscape(SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=350')
    . '" alt="' . themeEscape($sysconf['library_name']) . '">';
```

---

## 🟠 Temuan Tinggi

### T-01: Cookie `select_lang` Tanpa Flag `Secure` di HTTPS

**File**: [`visitor_template.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/visitor_template.php#L21-L44)  
**CWE**: CWE-614 (Sensitive Cookie in HTTPS Session Without 'Secure' Attribute)

**Deskripsi**: Cookie bahasa (`select_lang`) diset dengan `'secure' => false`, yang berarti cookie akan dikirim melalui koneksi HTTP biasa (tanpa enkripsi), meskipun situs menggunakan HTTPS.

```php
@setcookie('select_lang', $select_lang, [
    'expires' => time()+14400,
    'path' => SWB,
    'domain' => '',
    'secure' => false,   // ⚠️ Harus true jika HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

**Saran Perbaikan**:
```php
@setcookie('select_lang', $select_lang, [
    'expires' => time()+14400,
    'path' => SWB,
    'domain' => '',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

---

### T-02: Inline Rendering `$metadata` Tanpa Sanitasi

**File**: [`header.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L36)  
**CWE**: CWE-79 (Cross-site Scripting)

**Deskripsi**: Variabel `$metadata` langsung di-echo tanpa sanitasi:

```php
<?php echo $metadata;?>
```

Variabel `$metadata` berasal dari SLiMS core dan biasanya berisi tag `<meta>`. Namun tidak ada jaminan bahwa isinya telah disanitasi, terutama jika ada plugin atau modul yang memanipulasi variabel ini.

**Saran Perbaikan**: Idealnya `$metadata` disanitasi di SLiMS core. Di level template, bisa ditambahkan fallback:

```php
<?php echo strip_tags((string)($metadata ?? ''), '<meta><link>'); ?>
```

---

### T-03: Highlight.js Tanpa Escaping Input

**File**: [`footer.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L108-L111)  
**CWE**: CWE-79 (Cross-site Scripting)

**Deskripsi**: Variabel `$searchableInJsArray` langsung dimasukkan ke dalam konteks JavaScript tanpa escaping:

```php
<?php if(isset($engine) && $searchableInJsArray = $this->generateKeywords($engine->searchable_fields)) : ?>
<script>
  $('.card-body > *').highlight(<?= $searchableInJsArray ?>);
</script>
```

Jika `generateKeywords()` menghasilkan output yang mengandung `</script>`, penyerang bisa keluar dari konteks script.

**Saran Perbaikan**:
```php
<script>
  $('.card-body > *').highlight(<?= json_encode(json_decode($searchableInJsArray), JSON_HEX_TAG | JSON_HEX_AMP) ?>);
</script>
```

---

## 🟡 Temuan Sedang

### S-01: Potensi SQL Injection di Fungsi `getTopic()`

**File**: [`classic.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/classic.php#L206-L217)  
**CWE**: CWE-89 (SQL Injection)

**Deskripsi**: Parameter `$biblio_id` diterima dan disanitasi di awal fungsi, namun **tidak digunakan dalam query**. Query mengambil semua topic tanpa filter berdasarkan `$biblio_id`.

```php
function getTopic($dbs, $biblio_id)
{
    $biblio_id = themeSafeInt($biblio_id);

    // ⚠️ biblio_id tidak dipakai di WHERE clause
    $query = $dbs->query("SELECT topic FROM biblio_topic AS bt 
                          JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id");
    ...
}
```

**Dampak**: Bukan SQL Injection langsung (karena `themeSafeInt` sudah dipakai), tetapi **bug logika** yang menyebabkan data topic untuk SEMUA buku ditampilkan, bukan hanya buku yang dimaksud.

**Saran Perbaikan**:
```php
$query = $dbs->query("SELECT topic FROM biblio_topic AS bt 
    JOIN mst_topic AS mt ON bt.topic_id=mt.topic_id
    WHERE bt.biblio_id=" . $biblio_id);
```

---

### S-02: Penggunaan `@unserialize()` Tanpa Validasi Kelas

**File**: [`theme_helpers.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/theme_helpers.php#L1931)  
**CWE**: CWE-502 (Deserialization of Untrusted Data)

**Deskripsi**: Data `social_media` dari database di-unserialize tanpa opsi `allowed_classes`:

```php
$unserialized = @unserialize($librarian['social_media']);
```

**Dampak**: Jika database disusupi atau data di-inject, `unserialize()` tanpa restriksi bisa memicu **PHP Object Injection** yang berpotensi menyebabkan Remote Code Execution (tergantung class yang tersedia di autoloader).

**Saran Perbaikan**:
```php
$unserialized = @unserialize($librarian['social_media'], ['allowed_classes' => false]);
```

---

### S-03: Variabel `$js` di-Echo Tanpa Sanitasi

**File**: [`header.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L241-L243)  
**CWE**: CWE-79 (Cross-site Scripting)

**Deskripsi**:

```php
<?php
if (isset($js)):
    echo $js;
endif;
?>
```

Variabel `$js` yang berasal dari SLiMS core langsung di-echo. Sama seperti `$metadata`, tidak ada jaminan bahwa isinya aman. Namun risiko lebih rendah karena variabel ini biasanya berisi tag `<script src="...">` dari core.

**Saran Perbaikan**: Monitoring via review core; tidak bisa sepenuhnya diperbaiki di level template tanpa mempengaruhi fungsionalitas. Pertimbangkan untuk mem-filter hanya tag `<script>` dan `<link>`:
```php
<?php echo strip_tags((string)($js ?? ''), '<script><link>'); ?>
```

---

### S-04: Missing `alt` Attribute pada Gambar Logo Footer

**File**: [`footer.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L29-L32)  
**CWE**: CWE-1004 (Sensitive information accessible)

**Deskripsi**: Tag `<img>` logo di footer tidak memiliki atribut `alt`, padahal di navbar sudah ada:

```php
// footer.php baris 29 — tanpa alt
echo '<img class="footer-brand-img" src="...">'; 

// footer.php baris 32 — tanpa alt
echo '<img class="footer-brand-img" src="' . assets(v('images/logo.png')) . '">';
```

**Dampak**: Bukan celah keamanan langsung, tetapi ini adalah **best practice aksesibilitas** dan memberikan informasi konteks kepada screen reader. Terkait keamanan, jika gambar gagal dimuat, `alt` bisa memperlihatkan informasi sensitif tergantung apa yang diisikan.

**Saran Perbaikan**: Tambahkan atribut `alt` yang di-escape:
```php
echo '<img class="footer-brand-img" src="..." alt="' . themeEscape($sysconf['library_name']) . '">';
```

---

### S-05: `REQUEST_URI` Dipakai dalam og:url Meskipun Sudah di-Encode

**File**: [`header.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L13)  
**CWE**: CWE-79 (Reflected XSS via Meta Tags)

**Deskripsi**:

```php
$request_uri = urlencode(strip_tags(urldecode($_SERVER['REQUEST_URI'])));
```

Lalu digunakan di meta tags:
```html
<meta property="og:url" content="//<?php echo themeEscape($_SERVER["SERVER_NAME"] . $request_uri); ?>"/>
```

Meskipun sudah ada `themeEscape()` dan `urlencode()`, pola `urldecode` → `strip_tags` → `urlencode` bisa melewatkan karakter yang sudah di-double-encode. Selain itu, `$_SERVER['SERVER_NAME']` bisa dimanipulasi via header `Host` pada konfigurasi server tertentu.

**Saran Perbaikan**: Gunakan `$_SERVER['HTTP_HOST']` yang lebih reliable, atau lebih baik gunakan konfigurasi base URL dari `$sysconf`:
```php
$base_url = rtrim($sysconf['baseurl'] ?? '', '/');
$safe_uri = htmlspecialchars($base_url . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ENT_QUOTES, 'UTF-8');
```

---

## 🔵 Temuan Rendah

### R-01: Penggunaan `var` Bukan `const/let` di JavaScript

**File**: [`visitor_template.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/visitor_template.php#L777)  
**CWE**: CWE-457 (Use of Uninitialized Variable)

**Deskripsi**: Beberapa blok JavaScript masih menggunakan `var` yang memiliki *function scope*, meningkatkan risiko variabel bentrok (variable hoisting).

```javascript
var message = new SpeechSynthesisUtterance(message);  // variabel shadowing parameter
var voices = speechSynthesis.getVoices();
```

Fungsi `textToSpeech` secara tidak sengaja menimpa parameter `message` dengan objek baru karena `var` shadowing.

**Saran Perbaikan**:
```javascript
textToSpeech: function(text) {
    const utterance = new SpeechSynthesisUtterance(text);
    ...
}
```

---

### R-02: Console.log Aktif di Production Code

**File**: [`visitor_template.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/visitor_template.php#L747)  
**CWE**: CWE-532 (Insertion of Sensitive Information into Log File)

**Deskripsi**: `console.log(err)` di handler catch AJAX bisa mengekspos informasi sensitif di browser console pengguna.

```javascript
.catch(err => {
    console.log(err);  // ⚠️ Informasi error lengkap terlihat di console
    ...
})
```

**Saran Perbaikan**: Hapus atau ganti dengan `console.warn` yang lebih selektif, atau cukup abaikan di production:
```javascript
.catch(err => {
    // Tidak perlu log error detail di production
    this.textInfo = this.plainText(...)
    ...
})
```

---

### R-03: Penggunaan Komentar Debug yang Tersisa

**File**: [`index_template.inc.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/index_template.inc.php#L11-L15)  
**CWE**: CWE-615 (Inclusion of Sensitive Information in Source Code Comments)

**Deskripsi**: Kode debug yang di-comment masih ada di file production:

```php
//$a = get_defined_vars();
//$a['sysconf'] = null;
//$a['main_content'] = null;
//echo '<pre>'; print_r($a); echo '</pre>'; die();
//echo '<pre>'; print_r($_SESSION); echo '</pre>'; die();
```

**Dampak**: Jika secara tidak sengaja di-uncomment, akan mengekspos seluruh variabel dan session data.

**Saran Perbaikan**: Hapus baris-baris komentar debug tersebut dari kode production.

---

### R-04: Tidak Ada CSP (Content Security Policy) Header

**File**: [`header.php`](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php)  
**CWE**: CWE-1021 (Improper Restriction of Rendered UI Layers or Frames)

**Deskripsi**: Template tidak mengirimkan header **Content-Security-Policy**. Meskipun ini biasanya diatur di level web server atau core SLiMS, template bisa memberikan fallback via meta tag.

**Saran Perbaikan**: Tambahkan meta tag CSP minimal di `<head>`:
```html
<meta http-equiv="Content-Security-Policy" 
      content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; frame-src https://www.google.com;">
```

> **Catatan**: `unsafe-inline` masih dibutuhkan karena banyak inline script/style di template ini. Idealnya, migrasi ke external file dan gunakan nonce-based CSP.

---

## ✅ Praktik Keamanan yang Sudah Baik

Berikut hal-hal yang sudah diimplementasikan dengan baik di template Rasamala:

| Aspek | Detail |
|---|---|
| **Output Escaping** | Fungsi `themeEscape()` (`htmlspecialchars` ENT_QUOTES UTF-8) digunakan secara konsisten di hampir semua output |
| **CSRF Protection** | Token CSRF (`Volnix\CSRF\CSRF`) diterapkan di form visitor dan search view |
| **URL Sanitization** | `themeSafeMenuUrl()`, `themeSafeHref()`, `themeSafeHttpsUrl()` memblokir protokol berbahaya (`javascript:`, `data:`) |
| **SQL Parameter Safety** | `themeSafeInt()`, `themeSafeLimit()`, `themeSafeYear()` memvalidasi integer sebelum dimasukkan ke query |
| **HTML Sanitization** | `themeSanitizeHtml()` menggunakan HTMLPurifier dengan fallback regex |
| **Cookie SameSite** | Cookie `select_lang` sudah diset `SameSite=Lax` |
| **Cookie HttpOnly** | Cookie `select_lang` sudah diset `HttpOnly=true` |
| **Path Traversal Prevention** | `themeNormalizeTopicIcon()` memblokir `..` dan karakter kontrol di path |
| **Image Safety** | `themeSafeContentImageSrc()` memblokir `javascript:`, `vbscript:`, dan `data:` yang bukan gambar |
| **JSON Encoding** | Penggunaan flag `JSON_HEX_TAG`, `JSON_HEX_AMP` dll. saat embed data PHP ke JavaScript |
| **Librarian Image Path** | `themeLibrarianImageUrl()` memvalidasi path, memblokir path traversal, dan mengecek file exists |

---

## 📋 Prioritas Perbaikan

### Fase 1 — Segera (Critical + High)

| ID | Temuan | Effort |
|---|---|---|
| K-01 | CSS Injection via Custom CSS | Rendah |
| K-02 | Logo footer tanpa escaping | Rendah |
| T-01 | Cookie `select_lang` tanpa flag `Secure` | Rendah |
| T-02 | `$metadata` tanpa sanitasi | Rendah |
| T-03 | Highlight.js tanpa escaping | Rendah |

### Fase 2 — Dijadwalkan (Medium)

| ID | Temuan | Effort |
|---|---|---|
| S-01 | `getTopic()` query tanpa filter | Rendah |
| S-02 | `unserialize()` tanpa `allowed_classes` | Rendah |
| S-03 | Variabel `$js` tanpa sanitasi | Rendah |
| S-04 | Missing `alt` attribute di footer | Rendah |
| S-05 | `REQUEST_URI` di og:url | Sedang |

### Fase 3 — Peningkatan (Low)

| ID | Temuan | Effort |
|---|---|---|
| R-01 | `var` shadowing di JavaScript | Rendah |
| R-02 | `console.log` di production | Rendah |
| R-03 | Komentar debug tersisa | Rendah |
| R-04 | Tidak ada CSP header | Sedang |

---

## 📝 Catatan Tambahan

1. **Dependency**: Template ini meng-include `axios.min.js` secara lokal dari folder `assets/js/`. Pastikan versi axios yang digunakan adalah versi terbaru yang tidak memiliki CVE terbuka.

2. **Vue.js Version**: Template menggunakan Vue 3 (`Vue.createApp`). Pastikan versi `vue.min.js` di folder `assets/js/` adalah versi Vue 3.x yang ter-patch.

3. **Bootstrap Version**: Template sudah bermigrasi ke Bootstrap 5 (`data-bs-toggle`). Pastikan `bootstrap.bundle.min.js` adalah versi terbaru.

4. **HTMLPurifier**: Template sudah memiliki fallback jika HTMLPurifier tidak tersedia (regex-based sanitization di `themeSanitizeHtml`). Pastikan library HTMLPurifier di `lib/ezyang/htmlpurifier/` selalu ter-update.

5. **Inline Style/Script**: Template masih banyak menggunakan inline `<style>` dan `<script>`. Untuk keamanan jangka panjang, pertimbangkan migrasi ke file eksternal agar bisa menerapkan CSP yang lebih ketat.

---

*Dokumen ini dibuat sebagai bagian dari review keamanan template Rasamala untuk SLiMS Bulian 9.5.1-dev.*
