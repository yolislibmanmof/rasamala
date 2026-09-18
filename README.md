<!-- markdownlint-disable MD013 -->

# 🌲 Rasamala Theme for SLiMS 9 Bulian

**Rasamala** adalah templat OPAC modern, cepat, dan kaya fitur untuk **SLiMS 9 Bulian**. Dikembangkan dengan pendekatan *mobile-first*, arsitektur PHP modular yang rapi, serta kontrol kustomisasi visual penuh melalui **TInfo (Pengaturan Tema)** tanpa menyentuh core SLiMS.

![Preview Rasamala](rasamala-preview.jpg)

> **Instalasi Mandiri:** Salin folder `template/rasamala/`, aktifkan melalui **System > Theme**, lalu atur via TInfo admin. Tidak membutuhkan berkas tambahan di luar direktori tema.

---

## ⚡ Fitur Utama

- 🎨 **Visual & Kustomisasi Real-Time (Theme Viewer):**
  14+ Preset Warna (Light/Dark Mode pintar), 15+ Pilihan Background (SVG/Pattern/Image), 9+ Animasi Background, serta **Interactive Theme Viewer** untuk mencoba dan menyimpan tampilan secara instan.
- 📱 **Desain Mobile-First & Aksesibilitas:**
  Navigasi bawah seluler (5-tombol), *Floating Action Pill* (Bookmark, Keranjang, Sitasi, Bagikan), target sentuh 48px+, dan kepatuhan WCAG/WAI-ARIA.
- 🔍 **Pencarian Katalog & Hasil Full-Width:**
  Form pencarian responsif dengan Autocomplete JSON super cepat (250ms debounce), pencarian modal tingkat lanjut (`#adv-modal`), 8 mode pengurutan (*Sort by*), serta tampilan *Simple, List, & Grid*.
- 📖 **Detail Buku & Sitasi Akademis:**
  Tampilan judul utuh tanpa batas karakter, Generator Kode QR SVG Offline, Generator Sitasi Akademis otomatis (APA, Chicago, MLA, Turabian), dan Keranjang Judul (*Title Basket*) permanen.
- 🪪 **Kartu Anggota Digital & Buku Tamu:**
  Kartu keanggotaan digital interaktif dengan indikator status aktif/expired, serta *Visitor Log* pendataan pengunjung mode Kiosk dan Split-Layout.
- 💬 **Tombol Layanan Integratif:**
  Tombol chat bergaya WhatsApp yang membuka aplikasi WhatsApp melalui tautan pesan siap isi, serta Widget Waktu Sholat otomatis untuk kota-kota di Indonesia lengkap dengan reminder azan. Fitur ini bukan chatbox di dalam OPAC.

---

## 🚀 Cara Instalasi Cepat

1. **Salin Tema:** Salin folder `rasamala` ke direktori templat SLiMS Anda:
   `/path/to/slims/template/rasamala`
2. **Aktifkan Tema:** Pilih dan aktifkan **Rasamala** dari menu admin SLiMS:
   **System > Theme**, atau tambahkan di `sysconfig.inc.php`:
   ```php
   $sysconf['template']['theme'] = 'rasamala';
   ```
3. **Kustomisasi:** Sesuaikan tampilan tema melalui menu **System > Theme > Customize/TInfo** atau langsung dari tombol Theme Viewer melayang.

---

## 🎛️ Pengaturan Tema (TInfo Admin & Theme Viewer)

Pengaturan Rasamala dikelompokkan ke dalam kategori tersentral di TInfo admin:
- **Hero & Background:** Mode Fullscreen Hero, Pilihan Konten Hero, Preset & Custom Background, dan Animasi Canvas.
- **Warna & Tampilan:** 14+ Palet Warna Preset / Custom Palette, Light/Dark Mode, Tipografi, dan Section Layout (Tab / Standard).
- **Komponen & Widget:** Visitor Log, Ticker Pengumuman, tombol Chat WhatsApp (tautan eksternal), Waktu Sholat, dan Footer Options.

---

## 📖 Dokumentasi Teknis

Untuk informasi arsitektur sistem dan spesifikasi teknis, silakan buka:

- 📘 **[Dokumentasi Detail & Spesifikasi Teknis (`readme-detail.md`)](readme-detail.md)**
  *Arsitektur berkas lengkap, skema TInfo, rute OPAC, aset lokal, proteksi keamanan SQL/XSS/CSP, dan changelog UI/UX.*

---

## 👥 Kredit & Pengembang

- **Pengembang Templat Rasamala:** **Ade Ismail Siregar** ([adeismailbox@gmail.com](mailto:adeismailbox@gmail.com))
- **Basis Pengembangan:** SLiMS Default / Classic Template by Waris Agung Widodo
- **Komunitas SLiMS:** [https://slims.web.id/](https://slims.web.id/)
