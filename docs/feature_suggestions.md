# Saran Peningkatan & Penambahan Fitur Tinfo Rasamala

Berdasarkan analisis mendalam terhadap seluruh file template (tinfo.inc.php, header.php, footer.php, _home.php, _navbar.php, _search-form.php, _result-search.php, style.css, app.js), berikut adalah saran peningkatan fitur yang dikelompokkan berdasarkan prioritas dan dampak.

---

## 🔥 Prioritas Tinggi — Dampak Besar, Implementasi Sedang

### 1. Pengaturan Jumlah Item New Collection & Top Reader
**Masalah**: Popular Collection sudah punya `Popular Items Count`, tapi **New Collection** dan **Top Reader** tidak punya pengaturan jumlah item. Keduanya menggunakan default dari API tanpa batas konfigurasi.

**Saran**: Tambahkan pengaturan di Section 6:
- `New Collection Items Count` (default: 6)  
- `Top Reader Items Count` (default: 5)

**Tingkat kesulitan**: ⭐ Rendah — hanya perlu menambah parameter `?limit=X` ke URL API di [_home.php](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L63-L77).

---

### 2. Pengaturan Urutan Seksi Homepage (Section Ordering)
**Masalah**: Urutan seksi di homepage saat ini *hardcoded*: Topics → Popular → New Collection → Top Reader → Map. Admin tidak bisa mengubah urutan tanpa edit kode.

**Saran**: Tambahkan pengaturan `Homepage Section Order` bertipe `longtext` di Section 6:
```
topic ; popular ; new-collection ; top-reader ; map
```
Dengan memindahkan urutan string, admin bisa mengatur urutan tampilan seksi tanpa coding.

**Tingkat kesulitan**: ⭐⭐ Sedang — perlu refactor `_home.php` agar merender seksi berdasarkan array urutan.

---

### 3. Custom CSS Injection
**Masalah**: Admin sering perlu tweak kecil (warna, ukuran font, spacing) tanpa harus edit file CSS langsung.

**Saran**: Tambahkan pengaturan baru di Section 1:
- `Custom CSS` (type: `longtext`) — CSS kustom yang di-inject ke `<head>` via `<style>` tag.

**Tingkat kesulitan**: ⭐ Rendah — tambahkan field di tinfo, lalu echo di [header.php](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/header.php#L93) setelah inline style yang ada.

---

### 4. Navbar Style (Transparent / Solid / Glassmorphism)
**Masalah**: Navbar saat ini selalu `bg-transparent`. Di beberapa halaman (terutama non-homepage), ini bisa membuat teks sulit dibaca tergantung konten di belakangnya.

**Saran**: Tambahkan pengaturan di Section 2:
- `Navbar Style` — pilihan: `Transparent` (default), `Solid`, `Glassmorphism (Blur)`

**Tingkat kesulitan**: ⭐⭐ Sedang — perlu menambah class kondisional di [_navbar.php](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_navbar.php#L16) dan beberapa CSS rule baru.

---

### 5. Footer Copyright Text Kustom
**Masalah**: Teks copyright di footer saat ini *hardcoded*: `© 2025 — Senayan Developer Community`. Admin tidak bisa menggantinya dengan nama institusi mereka sendiri.

**Saran**: Tambahkan pengaturan di Section 9:
- `Footer Copyright Text` (type: `text`, default: `Senayan Developer Community`)

**Tingkat kesulitan**: ⭐ Rendah — ganti hardcoded string di [footer.php:L76](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/footer.php#L76).

---

### 6. Ticker Speed / Kecepatan Running Text
**Masalah**: Kecepatan ticker saat ini *hardcoded* di CSS (`animation: latestContentTicker 32s`). Admin tidak bisa mengatur kecepatan gerak teks berjalan.

**Saran**: Tambahkan pengaturan di Section 4:
- `Ticker Speed` — pilihan: `Slow (48s)`, `Normal (32s)` (default), `Fast (18s)`

**Tingkat kesulitan**: ⭐ Rendah — inject inline CSS `--ticker-speed: Xs` ke header, lalu ubah CSS animation duration ke `var(--ticker-speed)`.

---

## 🟡 Prioritas Sedang — Nilai Tambah Bagus

### 7. Dark Mode Toggle
**Masalah**: Tema hanya punya mode terang. Banyak pengguna modern menginginkan opsi dark mode.

**Saran**: Tambahkan toggle dark/light di OPAC, lalu sediakan pengaturan Tinfo hanya untuk menampilkan atau menyembunyikan tombolnya:
- `Dark/Light Mode Toggle` — pilihan: `Show` (default), `Hide`

**Tingkat kesulitan**: ⭐⭐⭐ Tinggi — perlu menambah set CSS variable untuk dark mode dan toggle logic di JavaScript.

---

### 8. Homepage Welcome Banner / Announcement
**Masalah**: Tidak ada cara bagi admin untuk menampilkan pengumuman sementara (misalnya: jadwal tutup, event, dsb.) di bagian atas homepage tanpa edit konten.

**Saran**: Tambahkan pengaturan di Section 3:
- `Announcement Banner` (type: `longtext`) — HTML/teks pengumuman
- `Announcement Banner Show` — `Show` / `Hide` (default: Hide)
- `Announcement Banner Style` — `Info (Blue)`, `Warning (Yellow)`, `Danger (Red)`, `Success (Green)`

**Tingkat kesulitan**: ⭐⭐ Sedang — render kondisional setelah navbar di [_home.php](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L28).

---

### 9. Search Placeholder Text Kustom
**Masalah**: Placeholder teks di kotak pencarian saat ini *hardcoded*: `"Enter keyword to search collection..."`. Admin mungkin ingin mengubahnya sesuai konteks perpustakaan.

**Saran**: Tambahkan pengaturan di Section 3:
- `Search Placeholder Text` (type: `text`, default: `Enter keyword to search collection...`)

**Tingkat kesulitan**: ⭐ Rendah — ganti string di [_search-form.php:L118](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_search-form.php).

---

### 10. Social Media Tambahan (TikTok, WhatsApp, Telegram, LinkedIn)
**Masalah**: Hanya ada 4 social media (Facebook, Twitter, YouTube, Instagram). Banyak perpustakaan modern juga punya TikTok, WhatsApp, Telegram, atau LinkedIn.

**Saran**: Tambahkan di Section 8:
- `TikTok Link`
- `WhatsApp Link` (format: `https://wa.me/62xxx`)
- `Telegram Link`
- `LinkedIn Link`

**Tingkat kesulitan**: ⭐ Rendah — tambahkan field tinfo + entry di array `$social_links` di [_home.php:L97-L102](file:///var/www/html/slims/s951dev/98/template/rasamala/parts/_home.php#L97).

---

### 11. Background Animation Speed
**Masalah**: Sudah ada 5 pilihan animasi background, tapi tidak ada kontrol kecepatan animasinya.

**Saran**: Tambahkan pengaturan di Section 3:
- `Animation Speed` — `Slow`, `Normal` (default), `Fast`

**Tingkat kesulitan**: ⭐ Rendah — inject CSS variable yang mengontrol `animation-duration` di canvas/CSS.

---

### 12. Ticker / Home Display Item Count & Char Limit Terpisah
**Masalah**: Saat ini Ticker dan Home Display berbagi satu `Homepage Display Item Count` dan `Homepage Display Character Limit` yang sama. Padahal keduanya adalah fitur terpisah yang mungkin butuh jumlah item dan batas karakter berbeda.

**Saran**: Pisahkan pengaturan ini:
- **Section 4 (Ticker)**: `Ticker Item Count` + `Ticker Character Limit`
- **Section 5 (Home Display)**: `Home Display Item Count` + `Home Display Character Limit`

**Tingkat kesulitan**: ⭐ Rendah — duplikasi field dan arahkan masing-masing section ke dbfield-nya sendiri.

---

## 🟢 Prioritas Rendah — Nice to Have

### 13. Font Family Selection
**Saran**: Tambahkan pilihan font family (Inter, Roboto, Poppins, Outfit, System Default) di Section 1. Inject via Google Fonts di header.

### 14. Favicon/Webicon Override di Tinfo
**Saran**: Walaupun SLiMS sudah punya pengaturan webicon global, mungkin perlu override di level template.

### 15. Search Result Layout Toggle
**Saran**: Tambahkan pilihan layout hasil pencarian: `List View` (default), `Grid View`, `Compact View`.

### 16. Mobile Bottom Navigation Show/Hide
**Saran**: Tambahkan toggle untuk menampilkan/menyembunyikan bottom navigation bar di mobile.

### 17. Map Height Kustom
**Saran**: Saat ini tinggi iframe map *hardcoded* di 420px. Tambahkan field `Map Height (px)` di Section 7.

---

## 📊 Ringkasan Prioritas

| # | Fitur | Prioritas | Kesulitan | Dampak |
|---|-------|-----------|-----------|--------|
| 1 | Item Count untuk New Collection & Top Reader | 🔥 Tinggi | ⭐ | Tinggi |
| 2 | Section Ordering Homepage | 🔥 Tinggi | ⭐⭐ | Tinggi |
| 3 | Custom CSS Injection | 🔥 Tinggi | ⭐ | Tinggi |
| 4 | Navbar Style | 🔥 Tinggi | ⭐⭐ | Sedang |
| 5 | Footer Copyright Kustom | 🔥 Tinggi | ⭐ | Sedang |
| 6 | Ticker Speed | 🔥 Tinggi | ⭐ | Sedang |
| 7 | Dark Mode | 🟡 Sedang | ⭐⭐⭐ | Tinggi |
| 8 | Announcement Banner | 🟡 Sedang | ⭐⭐ | Tinggi |
| 9 | Search Placeholder | 🟡 Sedang | ⭐ | Rendah |
| 10 | Social Media Tambahan | 🟡 Sedang | ⭐ | Sedang |
| 11 | Animation Speed | 🟡 Sedang | ⭐ | Rendah |
| 12 | Ticker/Display Item Split | 🟡 Sedang | ⭐ | Sedang |
| 13 | Font Family | 🟢 Rendah | ⭐⭐ | Sedang |
| 14 | Favicon Override | 🟢 Rendah | ⭐ | Rendah |
| 15 | Search Result Layout | 🟢 Rendah | ⭐⭐⭐ | Sedang |
| 16 | Mobile Bottom Nav Toggle | 🟢 Rendah | ⭐ | Rendah |
| 17 | Map Height | 🟢 Rendah | ⭐ | Rendah |

---

> [!TIP]
> Saran saya: mulai dari fitur **Prioritas Tinggi yang kesulitannya ⭐ (Rendah)** — yaitu #1, #3, #5, #6 — karena dampaknya besar dan bisa diselesaikan cepat. Setelah itu lanjutkan ke #8 (Announcement Banner) dan #10 (Social Media tambahan) yang sangat berguna untuk perpustakaan aktif.

Pilih fitur mana yang ingin diimplementasikan, dan saya akan langsung kerjakan!
