# CSS Review: Rasamala Theme

Review ini memeriksa CSS tema Rasamala pada SLiMS 9 Bulian OPAC. Bagian utama dokumen ini adalah revisi dari review lama yang sebagian sudah tidak akurat karena kondisi file berubah dan ada beberapa klaim yang terlalu umum.

Tanggal review: 2026-07-20

Catatan 2026-07-21: file runtime sudah dinamai ulang menjadi `foundation.css`, `opac-pages.css`, `theme-components.css`, dan `theme-dark.css`. Referensi lama seperti `style.css` atau `style-dark.css` di bawah ini bersifat historis untuk konteks review awal.

File utama:

- `98/template/rasamala/assets/css/foundation.css`
- `98/template/rasamala/assets/css/opac-pages.css`
- `98/template/rasamala/assets/css/theme-components.css`
- `98/template/rasamala/assets/css/theme-dark.css`

## Ringkasan

Tema Rasamala sudah feature-complete dan secara visual cukup matang. Sistem design token, palette, dark mode, search result layout, homepage sections, floating actions, dan theme viewer sudah jauh lebih maju dibanding template default.

Masalah utamanya bukan kekurangan fitur, tetapi akumulasi CSS yang tumbuh organik. Saat ini CSS terlalu besar, banyak aturan saling menimpa, dan `!important` dipakai sangat masif. Ini membuat debugging warna, mode gelap, mobile modal, search panel, dan custom palette menjadi mahal.

Prioritas terbaik bukan rewrite total. Yang paling aman adalah refactor bertahap dengan target mengurangi duplikasi, menata token, dan memindahkan override besar ke pola komponen yang lebih stabil.

## Panduan Cepat Untuk AI Agent

Bagian ini dibuat agar agent berikutnya bisa langsung memperbaiki/refactor tanpa membaca seluruh review lama. Gunakan bagian ini sebagai kontrak kerja utama.

### Scope Kerja

- Kerjakan hanya file di `98/template/rasamala/`.
- Fokus utama CSS:
  - `assets/css/style.css`
  - `assets/css/style-dark.css`
  - file PHP/JS terkait hanya jika class/attribute perlu disesuaikan.
- Jangan ubah core SLiMS di luar folder tema.
- Jangan rewrite total stylesheet.
- Jangan menghapus utility class hanya karena terlihat mirip Tailwind/Bootstrap sebelum audit pemakaian.

### Prinsip Refactor

1. Refactor per komponen kecil.
2. Pertahankan tampilan visual semirip mungkin.
3. Kurangi konflik cascade sebelum mengurangi ukuran file.
4. Jangan menghapus `!important` massal.
5. Jika menambah token baru, pakai token itu minimal di satu komponen agar tidak menjadi token mati.
6. Untuk dark mode, lebih baik ubah token daripada menambah selector panjang baru.
7. Untuk custom palette, selalu pakai pasangan `background` + `color` yang eksplisit.

### Urutan Paling Aman

1. Tambah token z-index.
2. Perluas Bootstrap variable bridge.
3. Rapikan pagination.
4. Rapikan button/chip kecil.
5. Rapikan form/dropdown.
6. Rapikan search result transparent/solid panels.
7. Rapikan dark mode token guards.
8. Baru pertimbangkan split file source.

### Validasi Wajib Setelah Setiap Perubahan

Jalankan:

```bash
git -C 98/template/rasamala diff --check
rg -o '!important' 98/template/rasamala/assets/css/style.css 98/template/rasamala/assets/css/style-dark.css | wc -l
rg -n 'z-index:\s*[0-9]+' 98/template/rasamala/assets/css/style.css 98/template/rasamala/assets/css/style-dark.css
```

Cek manual minimal:

- Beranda light dan dark.
- Search result mode `simple`, `list`, dan `grid`.
- Filter/sort desktop.
- Filter/sort mobile modal.
- Detail bibliografi.
- News list dan detail content.
- Librarian page.
- Member area.
- Visitor page.
- Floating actions: info, WhatsApp, theme viewer, back to top.
- Custom palette ekstrem: putih/kuning, dark gray, biru gelap.

### Definisi Selesai

Sebuah task refactor dianggap selesai jika:

- Tidak ada perubahan di luar scope task.
- Tidak ada whitespace error dari `git diff --check`.
- Tampilan light/dark tidak kehilangan kontras penting.
- Mobile modal tidak transparan ketika seharusnya solid.
- Jumlah `!important` tidak bertambah, kecuali ada alasan eksplisit.
- Jika selector lama dihapus, sudah ada selector pengganti yang lebih kecil atau token yang mengambil perannya.

## Task Cards Untuk Agent

### R-CSS-01: Z-Index Tokens

Tujuan: mengganti angka z-index langsung menjadi token agar layer mudah dilacak.

Status 2026-07-20: diterapkan untuk layer besar/berulang. Token sekarang tersedia di `:root` untuk header, dropdown, bottom nav, floating action, modal, mobile sheet, prayer toast, dan advanced search. Angka kecil lokal seperti `z-index: 1` atau `2` tetap dibiarkan.

File utama:

- `assets/css/style.css`
- `assets/css/style-dark.css`

Langkah:

1. Tambahkan token di `:root`.
2. Ganti angka yang jelas berulang: header, dropdown, bottom nav, floating action, modal.
3. Sisakan angka kecil lokal seperti `z-index: 1` atau `2` jika hanya untuk pseudo-element.
4. Jangan ubah stacking order.

Validasi:

```bash
rg -n 'z-index:\s*(1044|1045|1046|1047|1056|9999|12000|12030|13040|13050|99999)' 98/template/rasamala/assets/css
```

Target: angka besar berulang turun drastis, tampilan layer tetap sama.

### R-CSS-02: Bootstrap Variable Bridge

Tujuan: memindahkan sebagian override Bootstrap ke CSS variables.

Status 2026-07-20: diterapkan tahap awal. Blok Bootstrap 5 variables sudah mencakup body background/color, link, border, focus ring, button radius/font weight, dan pagination active state. Audit komponen spesifik seperti pagination/form masih bisa dilanjutkan di task berikutnya.

File utama:

- `assets/css/style.css`

Langkah:

1. Perluas blok `/* Bootstrap 5 Variables Binding */`.
2. Tambahkan body/link/border/focus variables.
3. Jangan langsung hapus semua override `.btn-*`.
4. Setelah variabel aktif, audit komponen kecil seperti pagination atau form control.

Contoh target:

```css
--bs-body-bg: var(--theme-background);
--bs-body-color: var(--theme-text);
--bs-link-color: var(--theme-link);
--bs-link-hover-color: var(--theme-link-hover);
--bs-border-color: var(--rasamala-border);
--bs-focus-ring-color: rgba(var(--theme-accent-rgb), 0.28);
```

Validasi:

- Button Bootstrap tetap terbaca.
- Dropdown tetap sesuai dark/light mode.
- Form focus ring tetap terlihat.

### R-CSS-03: Pagination Component

Tujuan: menjadikan pagination sebagai komponen pertama untuk pengurangan `!important`.

Status 2026-07-20: diterapkan tahap awal. Token pagination sudah ditambahkan dan dipakai oleh `.pagingList` serta `.pagination .page-link`. Dark mode mendapat final token bridge karena selector legacy di `style-dark.css` masih kuat; ini menambah beberapa `!important`, tetapi menjaga pagination tetap konsisten sampai blok dark lama direduksi pada refactor berikutnya.

File utama:

- `assets/css/style.css`
- `assets/css/style-dark.css`

Langkah:

1. Cari selector `.pagination`, `.page-link`, `.pagingList`.
2. Satukan token warna/radius.
3. Hindari duplikasi light/dark dengan token.
4. Kurangi `!important` hanya jika selector scoped masih menang.

Validasi:

- Search result pagination desktop/mobile.
- Active, hover, disabled, next, last page.
- Light, dark, custom palette.

### R-CSS-04: Buttons And Chips

Tujuan: menstandarkan tombol dan chip kecil tanpa merusak tombol khusus.

Status 2026-07-20: diterapkan tahap awal. Token button/control/outline sudah ditambahkan dan dipakai pada button utama, outline button, `search-type-btn`, `btn-news-readmore`, `btn-modal-action`, `libinfo-modal-btn`, dan `sort-chip`. Floating action dan tombol WhatsApp tetap tidak disentuh.

File utama:

- `assets/css/style.css`
- `assets/css/style-dark.css`

Jangan sentuh dulu:

- `.btn-floating-info`
- `.btn-back-to-top`
- `.btn-color-mode-toggle`
- `.btn-palette-switcher`
- `.footer-social-btn`
- tombol WhatsApp yang memang punya brand color.

Langkah:

1. Buat token component: button bg/text/border/hover.
2. Terapkan pada `.btn-primary`, `.btn-outline-*`, `.sort-chip`, `.search-type-btn`.
3. Pastikan active state memakai `--theme-primary` + `--theme-on-primary`.

Validasi:

- Search by buttons.
- Sort chips.
- Read more news.
- Advanced search submit.
- Detail action buttons.

### R-CSS-05: Search Panels Transparent/Solid

Tujuan: menyederhanakan blok search panels yang sekarang banyak final override.

File utama:

- `assets/css/style.css` sekitar blok `Filter, sort, and search result panel background setting`
- `assets/css/style-dark.css` sekitar blok search filter panel dan transparent panel

Langkah:

1. Definisikan token panel:
   - `--search-panel-bg`
   - `--search-panel-border`
   - `--search-panel-text`
   - `--search-chip-bg`
   - `--search-chip-text`
2. Mode solid dan transparent cukup mengubah token.
3. Mobile modal harus selalu solid walaupun desktop transparent.
4. Hapus duplikasi hanya setelah token terbukti bekerja.

Validasi:

- Desktop transparent search result.
- Desktop solid search result.
- Mobile filter modal tidak tembus/transparan.
- Dark mode grid/list/simple.

### R-CSS-06: Dark Mode Token Guard

Tujuan: mengurangi patch selector panjang untuk dark mode dan custom palette.

File utama:

- `assets/css/style-dark.css`
- `parts/header.php` hanya jika token inline dark palette perlu diselaraskan.

Langkah:

1. Cari fixed color `#ffffff`, `#111827`, `rgba(255`, `rgba(0`.
2. Jangan ganti semua. Mulai dari komponen yang sering bermasalah: filter, dropdown, detail, footer.
3. Gunakan `--theme-on-background`, `--theme-on-surface`, `--theme-on-primary`, dan `--theme-muted`.
4. Jika memakai `color-mix`, pastikan fallback warna tetap kontras.

Validasi:

- Minimal White dark mode.
- Dark Gray light mode dan dark mode.
- Custom palette kuning/putih.
- Dropdown bahasa dan advanced search.

### R-CSS-07: Accessibility Guards

Tujuan: menambah guard aksesibilitas yang belum ada tanpa mengubah desain normal.

Status 2026-07-20: diterapkan tahap awal di `style.css` melalui `prefers-contrast: more` dan `forced-colors: active`. Link non-button diberi underline dan kontrol form/button mendapat border lebih tegas hanya pada mode kontras tinggi.

File utama:

- `assets/css/style.css`
- `assets/css/style-dark.css`

Tambahkan:

```css
@media (prefers-contrast: more), (forced-colors: active) {
  .rasamala-theme a:not(.btn) {
    text-decoration: underline;
    text-underline-offset: 0.18em;
  }

  .rasamala-theme button,
  .rasamala-theme .btn,
  .rasamala-theme .form-control,
  .rasamala-theme select {
    border-width: 2px;
  }
}
```

Validasi:

- Tidak mengubah tampilan normal.
- Focus ring tetap terlihat.
- Link lebih jelas di high contrast mode.

## Data Aktual

| Metrik | Nilai |
|---|---:|
| `style.css` | 10.066 baris / 290.126 byte |
| `style-dark.css` | 3.377 baris / 120.855 byte |
| Total CSS tema | 13.443 baris / 410.981 byte |
| Total `!important` | 5.074 |
| `!important` di `style.css` | 4.226 |
| `!important` di `style-dark.css` | 848 |
| Deklarasi `z-index` numerik | 19 |
| Penggunaan `color-mix()` | 155 |
| Penggunaan `backdrop-filter` | 61 |
| Container queries | 0 |
| `prefers-contrast` / `forced-colors` | 2 |

## Koreksi Review Lama

| Klaim lama | Status | Catatan revisi |
|---|---|---|
| `!important` sekitar 2.500+ | Salah/rendah | Hasil aktual 5.057 di `style.css` + `style-dark.css`. |
| `style.css` 284.542 byte / 9.953 baris | Stale | Saat ini 285.836 byte / 9.989 baris. |
| Total CSS sekitar 285 KB | Tidak lengkap | Jika `style-dark.css` dihitung, total sekitar 403,8 KB. |
| Modern CSS termasuk container queries | Salah | Tidak ditemukan `@container`, `container-type`, atau `container-name`. |
| Tidak ada integrasi `prefers-color-scheme` | Kurang tepat | Di CSS memang tidak ada, tetapi mode auto memakai JS melalui `matchMedia('(prefers-color-scheme: dark)')`. |
| `@import ../../../../css/core.css` adalah dependency external | Salah konteks | Itu file lokal SLiMS, bukan third-party web/CDN. |
| Bootstrap 5 CSS variables belum dipakai | Kurang tepat | Sebagian sudah dipakai (`--bs-primary`, `--bs-primary-rgb`, `--bs-body-font-family`, `--bs-border-radius`). Masih bisa diperluas. |
| Tailwind compatibility classes kemungkinan unused | Belum terbukti | Beberapa utility seperti `flex-row`, `mx-auto`, dan `w-100` muncul di markup. Perlu audit selektif sebelum dihapus. |

## Kekuatan

1. Sistem token sudah cukup kuat.

   Ada token untuk primary, secondary, accent, background, surface, text, muted, on-primary, on-accent, link, radius, shadow, dan beberapa bridge ke Bootstrap.

2. Dukungan palette dan custom palette sudah matang.

   Tema tidak lagi bergantung pada satu warna saja. Sudah ada primary, secondary, accent, background, surface, text, muted, dan varian dark palette.

3. Dark mode aktif tidak hanya kosmetik.

   `style-dark.css` menangani banyak area: navbar, footer, filter, dropdown, detail, search result, topic, modal, pagination, member area, dan custom palette guard.

4. Responsif cukup luas.

   Banyak guard untuk mobile view, modal, bottom nav, search result, topic grid, dan safe-area mobile.

5. Aksesibilitas dasar sudah ada.

   Ada `:focus-visible`, `visually-hidden`, `prefers-reduced-motion`, placeholder handling, dan beberapa guard kontras untuk custom palette.

6. Integrasi dengan fitur tema sudah luas.

   CSS mendukung Theme Viewer, background animation, cursor, prayer time, latest content, search panel transparency, visitor page, WhatsApp popup, dan mobile bottom navigation.

## Masalah Utama

### 1. CSS terlalu besar untuk dipelihara manual

Total 403,8 KB untuk CSS tema masih bisa diterima secara teknis di jaringan lokal, tetapi sulit dipelihara. Masalah paling terasa adalah cascade yang panjang: perbaikan di bawah file sering dibuat untuk menimpa aturan sebelumnya.

Gejala yang terlihat:

- Banyak blok "final override" di akhir file.
- Banyak selector panjang untuk komponen yang sama.
- Perbaikan dark mode dan custom palette tersebar di beberapa blok.
- Perubahan kecil warna bisa berdampak ke banyak halaman.

### 2. `!important` terlalu masif

`!important` sebanyak 5.057 adalah masalah kritis untuk maintainability. Ini bukan sekadar angka besar; dampaknya nyata:

- Setiap fix baru cenderung butuh `!important` lagi.
- Urutan file menjadi terlalu menentukan.
- Komponen plugin/SLiMS legacy lebih sulit distandarkan.
- Debug di DevTools menjadi melelahkan karena banyak rule saling mengalahkan.

Catatan: menghapus `!important` sekaligus tidak aman. Banyak override dipakai untuk melawan Bootstrap lama, class SLiMS legacy, dan inline style. Pengurangan harus bertahap per komponen.

### 3. Token terlalu banyak dan sebagian saling tumpang tindih

Contoh alias yang membuat cognitive load naik:

```css
--theme-primary
--color-primary
--rasamala-accent
--theme-accent
--theme-accent-color
--rasamala-readable-accent
--rasamala-chrome-bg
--rasamala-chrome-text
```

Alias tidak salah, tetapi perlu hirarki yang jelas:

- Palette tokens: warna mentah dari Tinfo/custom palette.
- Semantic tokens: background, surface, text, muted, link, border.
- Component tokens: navbar, footer, card, input, button.
- State tokens: hover, focus, active, disabled.

Saat ini beberapa token berfungsi ganda sehingga sulit menebak token mana yang benar untuk elemen tertentu.

### 4. Dark mode masih terlalu override-heavy

Ada pendekatan token di akhir `style-dark.css`, tetapi masih banyak blok lama di atasnya. Akibatnya dark mode menjadi campuran:

- token inversion,
- selector guard,
- fixed rgba,
- fixed hex,
- override per halaman,
- override per plugin/dropdown.

Ini menjelaskan kenapa masalah "teks tidak terlihat" sering muncul setelah custom palette baru.

### 5. Bootstrap 5 belum dimanfaatkan optimal

Tema sudah mulai memakai variabel Bootstrap:

```css
--bs-primary: var(--rasamala-accent);
--bs-primary-rgb: var(--theme-accent-rgb);
--bs-body-font-family: var(--rasamala-font-stack);
--bs-border-radius: var(--rasamala-radius-card);
--bs-border-radius-lg: var(--rasamala-radius-panel);
```

Namun masih banyak override langsung untuk `.btn-*`, `.text-*`, `.bg-*`, `.form-control`, `.dropdown-menu`, `.pagination`, dan `.modal-content`.

Rekomendasi lama "gunakan Bootstrap 5 variables" benar, tetapi statusnya bukan "belum dilakukan". Status yang tepat: sudah mulai, perlu diperluas dan distandarkan.

### 6. Animasi dan efek visual perlu budget performa

Ada banyak fitur modern seperti `color-mix`, `backdrop-filter`, animated gradients, cursor effect, dan background animation. Ini bagus secara visual, tetapi harus punya mode ringan yang konsisten.

Area yang perlu dijaga:

- `backdrop-filter` dipakai 61 kali.
- Background animation memakai layer dan pseudo-element besar.
- Beberapa efek glow/blur bisa mahal di HP rendah.
- Dark/light/custom palette dapat memicu repaint lebih sering.

Sudah ada `prefers-reduced-motion`, tapi sebaiknya ada standar performa:

- mobile default lebih ringan,
- low-end mode mengurangi blur,
- animation speed mempengaruhi JS dan CSS,
- background effect canvas/layer tidak aktif kalau tidak terlihat.

### 7. Aksesibilitas perlu ditingkatkan lagi

Yang belum ada:

- `prefers-contrast: more`
- `forced-colors: active`
- audit sistematis untuk link yang hanya dibedakan oleh warna
- audit kontras otomatis untuk custom palette
- target tap 44px di semua tombol kecil

Beberapa guard kontras sudah ada, tetapi belum menjadi sistem yang konsisten.

## Rekomendasi Arsitektur Token

Gunakan 3 lapis token agar lebih mudah dipahami.

### 1. Palette tokens

Ini hasil dari Tinfo atau custom palette:

```css
--palette-primary
--palette-secondary
--palette-accent
--palette-background
--palette-surface
--palette-text
--palette-muted
```

### 2. Semantic tokens

Ini yang dipakai halaman umum:

```css
--color-bg
--color-surface
--color-text
--color-muted
--color-border
--color-link
--color-link-hover
--color-action
--color-action-text
```

### 3. Component tokens

Ini yang dipakai komponen spesifik:

```css
--navbar-bg
--navbar-text
--footer-bg
--footer-text
--card-bg
--card-text
--input-bg
--input-text
--button-bg
--button-text
--focus-ring
```

Dengan pola ini, komponen tidak perlu tahu palette mentah. Komponen hanya membaca semantic/component token.

## Rekomendasi Bootstrap 5

Perluas binding Bootstrap variable sebelum menulis override class baru:

```css
:root {
  --bs-primary: var(--color-action);
  --bs-primary-rgb: var(--color-action-rgb);
  --bs-body-bg: var(--color-bg);
  --bs-body-color: var(--color-text);
  --bs-body-font-family: var(--rasamala-font-stack);
  --bs-link-color: var(--color-link);
  --bs-link-hover-color: var(--color-link-hover);
  --bs-border-color: var(--color-border);
  --bs-border-radius: var(--radius-md);
  --bs-border-radius-lg: var(--radius-lg);
  --bs-focus-ring-color: var(--focus-ring);
}
```

Setelah itu baru evaluasi override `.btn`, `.dropdown-menu`, `.modal-content`, dan `.form-control`.

## Rekomendasi Z-Index

Saat ini ada banyak angka langsung, misalnya `1044`, `1045`, `1046`, `1047`, `1056`, `9999`, `12000`, `12030`, `13050`, dan `99999`.

Ganti dengan token:

```css
:root {
  --z-base: 1;
  --z-header: 12000;
  --z-header-dropdown: 12030;
  --z-bottom-nav: 9999;
  --z-floating-action: 1045;
  --z-floating-panel: 1056;
  --z-modal-backdrop: 13040;
  --z-modal: 13050;
  --z-emergency: 99999;
}
```

Lalu komponen memakai token. Ini tidak mengurangi ukuran banyak, tapi sangat membantu debugging.

## Rekomendasi Pengurangan `!important`

Jangan hapus global. Lakukan per komponen:

1. Pilih satu komponen stabil, misalnya pagination.
2. Tambahkan wrapper scope yang jelas, misalnya `.rasamala-theme .pagination`.
3. Turunkan selector yang terlalu panjang.
4. Hapus `!important` hanya jika hasil visual sama.
5. Test light/dark/custom palette/mobile.
6. Baru lanjut ke komponen berikutnya.

Urutan komponen yang disarankan:

1. Pagination
2. Buttons
3. Cards
4. Form controls
5. Dropdowns
6. Search result panels
7. Detail page
8. Footer/navbar
9. Modal
10. Dark mode guards

## Rekomendasi Struktur File

Untuk source development, pisahkan file. Untuk production, tetap boleh digabung/minify.

```text
assets/css/source/
  tokens.css
  base.css
  bootstrap-bridge.css
  components/
    buttons.css
    cards.css
    forms.css
    dropdowns.css
    modals.css
    pagination.css
    floating-actions.css
  layout/
    navbar.css
    footer.css
    hero.css
    sections.css
  pages/
    home.css
    search.css
    detail.css
    news.css
    member.css
    visitor.css
  themes/
    light.css
    dark.css
    custom-palette-guards.css
  utilities.css
```

Output production:

```text
assets/css/style.css
assets/css/style-dark.css
```

Ini menjaga kompatibilitas SLiMS, tapi source tetap lebih rapi.

## Quick Wins

Prioritas 1-2 hari:

| Task | Dampak | Risiko |
|---|---|---|
| Jadikan z-index sebagai token | Debug lebih mudah | Rendah |
| Perluas Bootstrap variable binding | Kurangi override baru | Rendah-sedang |
| Audit blok akhir `style.css` mulai line 8178+ | Kurangi duplikasi | Sedang |
| Audit blok akhir `style-dark.css` mulai line 2425+ | Kurangi konflik dark mode | Sedang |
| Tambahkan `prefers-contrast` / `forced-colors` guard | Aksesibilitas | Rendah |
| Tambahkan checklist visual regression manual | Aman saat refactor | Rendah |

## Refactor Bertahap

### Tahap 1: Stabilkan fondasi

- Tambah z-index tokens.
- Tambah radius/spacing/shadow tokens yang lebih ringkas.
- Dokumentasikan token mana yang boleh dipakai komponen.
- Jangan ubah tampilan besar dulu.

### Tahap 2: Komponen kecil

- Pagination.
- Buttons.
- Badges/filter chips.
- Form inputs.
- Dropdown/select.

Target: kurangi `!important` di komponen kecil tanpa mengubah desain.

### Tahap 3: Search result dan detail

- Satukan style list/simple/grid.
- Satukan filter/sort panel.
- Pastikan transparent/solid mode punya satu sumber aturan.
- Pastikan mobile modal selalu solid walaupun desktop transparent.

### Tahap 4: Dark mode dan custom palette

- Ubah dark mode menjadi token-driven.
- Kurangi fixed color di `style-dark.css`.
- Buat satu guard kontras custom palette.
- Tambah test palette ekstrem: putih, kuning, hitam, biru gelap, emerald.

### Tahap 5: Build pipeline opsional

Jika ingin lebih rapi:

- gunakan Stylelint,
- gunakan PostCSS/cssnano,
- generate minified CSS,
- simpan source modular.

Tetapi ini opsional. Jangan menambah build step jika deployment SLiMS di server user menjadi lebih ribet.

## Stylelint Awal

Konfigurasi lint jangan langsung melarang semua `!important`, karena file saat ini belum siap. Mulai dari mode warning:

```json
{
  "extends": ["stylelint-config-standard"],
  "rules": {
    "declaration-no-important": null,
    "selector-max-id": null,
    "selector-max-compound-selectors": null,
    "color-no-invalid-hex": true,
    "no-duplicate-selectors": true,
    "block-no-empty": true,
    "property-no-unknown": true
  }
}
```

Setelah komponen stabil, baru aktifkan aturan lebih ketat per folder source.

## Catatan Tentang Utility Classes

Jangan hapus utility seperti `.flex`, `.flex-row`, `.mx-auto`, atau `.w-full` hanya berdasarkan asumsi. Beberapa class Bootstrap/utility muncul di markup tema dan file visitor/member/search.

Cara aman:

1. Cari pemakaian di PHP, JS, HTML.
2. Bedakan utility custom dan utility Bootstrap.
3. Hapus hanya class yang tidak dipakai dan tidak berasal dari Bootstrap.
4. Test halaman home, search result, detail, news, librarian, member, visitor.

## Target Akhir

Target realistis setelah refactor:

- `style.css` turun dari 285 KB menjadi 170-210 KB.
- `style-dark.css` turun dari 118 KB menjadi 55-80 KB.
- `!important` turun bertahap dari 5.057 menjadi di bawah 2.000.
- Dark/custom palette lebih stabil.
- Perubahan warna tidak perlu patch selector panjang.
- Komponen baru mengikuti token, bukan menambah override global.

## Kesimpulan

Rasamala sudah punya fondasi desain yang kuat, tetapi CSS-nya sudah masuk fase perlu dirapikan. Review lama benar soal arah besar, tetapi angka dan beberapa klaim perlu diperbaiki.

Rekomendasi terbaik: jangan rewrite total. Lakukan refactor bertahap berbasis token dan komponen, mulai dari z-index, Bootstrap bridge, pagination/buttons/forms, lalu search result dan dark mode. Dengan pendekatan ini, tema tetap aman dipakai sambil kualitas CSS naik pelan-pelan.

<details>
<summary>Arsip review lama, jangan digunakan sebagai acuan refactor</summary>

Catatan: bagian arsip di bawah ini disimpan hanya untuk histori. Untuk pekerjaan agent, gunakan bagian "Panduan Cepat Untuk AI Agent" dan "Task Cards Untuk Agent" di atas.

# CSS Code Review: Rasamala Theme (SLiMS 9 Bulian OPAC)

## Executive Summary

**Strengths:**
- Well-organized with clear section comments and numbering
- Comprehensive design token system using CSS custom properties
- Good responsive coverage with mobile-first approach
- Extensive dark mode / palette support
- Modern CSS features (color-mix, backdrop-filter, container queries)
- Thoughtful accessibility (focus-visible, reduced motion, ARIA patterns)

**Critical Issues:**
- **Excessive `!important` usage** (~2,500+ occurrences) — creates maintenance debt and specificity wars
- **File size** (285KB) is very large for a single stylesheet — impacts load performance
- **Redundant/duplicate rules** scattered throughout (especially at the end)
- **Hardcoded magic numbers** for z-index, spacing, breakpoints
- **Bootstrap override soup** — fighting the framework instead of extending it

---

## Detailed Analysis

### 1. Architecture & Organization

#### ✅ Good
```
Sections are clearly labeled:
1. Design System Variables & Global Overrides
2. Layout Elements
3. Search and Inputs
4. Cards and Collections Grid
5. Search Results & Search Detail
6. Custom Filters & Sorting
7. Detail Page Styling
8. Mobile Bottom Navigation & Floating Buttons
9. Tailwind Compatibility & Availability Badges
10. Subpage & Global Custom Styled Classes
11. Visitor Counter Page Styling
12. Standardized theme buttons
13. Rasamala Search Hero
14. Premium UI & UX Enhancements
```

#### ⚠️ Concerns
- **Sections 10-14 appear to be appendices** added over time — not a clean architecture
- **Duplicate rules** at the end (lines ~8000-9953) re-declare earlier patterns
- **No clear separation** between "core theme" and "page-specific overrides"
- **Import chain**: `@import url('../../../../css/core.css');` — external dependency not visible

---

### 2. CSS Custom Properties (Design Tokens)

#### ✅ Excellent token system
```css
:root {
  --theme-primary: #6f5b43;
  --theme-primary-hover: #5d4b36;
  --theme-secondary: #a58a63;
  --theme-accent: #c8a24a;
  --theme-background: #f4f1ec;
  --theme-surface: #ffffff;
  --theme-text: #2f2a24;
  --theme-muted: #7a7167;
  /* ... semantic aliases ... */
  --rasamala-font-stack: system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, ...;
  --rasamala-radius-control: 10px;
  --rasamala-radius-panel: 14px;
  --rasamala-radius-card: 14px;
  --rasamala-radius-search: 999px;
  --rasamala-card-shadow: 0 8px 30px rgba(66, 66, 66, 0.06);
}
```

#### ⚠️ Issues
| Issue | Example | Impact |
|-------|---------|--------|
| **Token proliferation** | `--rasamala-chrome-bg`, `--rasamala-chrome-border`, `--rasamala-chrome-text`, `--rasamala-chrome-text-muted` | 4 tokens for one concept |
| **Duplicate aliases** | `--color-primary` = `--theme-primary`, `--theme-accent-color` = `--rasamala-accent` | Confusion, bloat |
| **Hardcoded RGB values** | `--theme-accent-rgb-value: 200, 162, 74` | Must stay in sync with hex |
| **Inconsistent naming** | `--rasamala-radius-control` vs `--bs-border-radius` | Cognitive load |

#### 💡 Recommendations
```css
/* Consolidate chrome tokens */
--chrome-bg: rgba(var(--accent-rgb), 0.92);
--chrome-border: rgba(var(--accent-rgb), 0.38);
--chrome-text: var(--on-primary);
--chrome-text-muted: color-mix(in srgb, var(--on-primary) 76%, transparent);

/* Derive RGB from hex automatically (modern browsers) */
/* Or use a build step: postcss-custom-properties --preserve=false */

/* Use a single radius scale */
--radius-xs: 4px;
--radius-sm: 8px;
--radius-md: 10px;
--radius-lg: 14px;
--radius-xl: 18px;
--radius-pill: 999px;
--radius-circle: 50%;
```

---

### 3. `!important` Abuse — Critical

**Count: ~2,500+ occurrences**

```css
/* Typical pattern — almost every rule uses !important */
.card,
.dropdown-menu,
.modal-content,
.list-group-item,
.biblio-list-card,
.topic-card,
.home-content-card,
.section-card,
.desktop-sort-bar,
#search-filter {
  background-color: var(--theme-surface) !important;
  color: var(--theme-text) !important;
  border-color: var(--rasamala-border) !important;
}
```

#### Why this is problematic:
1. **Specificity wars** — you can't override these without more `!important`
2. **Debugging nightmare** — DevTools shows "overridden" but you can't tell why
3. **Third-party integration breaks** — SLiMS plugins, Bootstrap updates, JS-injected styles
4. **Performance** — browser must re-evaluate cascade constantly

#### Root cause:
Fighting Bootstrap's specificity and SLiMS's inline/legacy styles.

#### 💡 Better approach:
```css
/* 1. Increase specificity WITHOUT !important */
.rasamala-theme .card,
.rasamala-theme .dropdown-menu,
.rasamala-theme .modal-content { ... }

/* 2. Use cascade layers (modern) */
@layer base, bootstrap, theme, overrides;
@layer theme {
  .card { background: var(--surface); }
}

/* 3. Scope to theme root */
:root.rasamala-theme { ... }
.rasamala-theme .card { ... }

/* 4. Only use !important for:
   - User agent overrides (print, accessibility)
   - Third-party widget isolation
   - Critical state (focus-visible outlines) */
```

---

### 4. Responsive Design

#### ✅ Good coverage
- Mobile-first base styles
- Breakpoints: 576px, 768px, 992px, 1199px (Bootstrap 5 standard)
- Safe-area-inset support for notched devices
- `100dvh` / `100vh` fallbacks for mobile modals
- Touch-friendly targets (44px minimum)

#### ⚠️ Inconsistencies
```css
/* Mixed breakpoint usage */
@media (max-width: 575.98px) { }  /* Bootstrap xs */
@media (max-width: 767.98px) { }  /* Bootstrap sm */
@media (max-width: 991.98px) { }  /* Bootstrap md */
@media (min-width: 768px) { }     /* Bootstrap md+ */
@media (min-width: 992px) { }     /* Bootstrap lg */
@media (min-width: 1199.98px) { } /* Bootstrap xl */

/* Some components use custom breakpoints */
@media (max-width: 575.98px) { .topic li { width: calc(50% - 16px) } }
@media (min-width: 992px) { .topic li { flex: 0 0 150px } }
```

#### 💡 Recommendation
```css
/* Define breakpoint tokens */
--bp-xs: 576px;
--bp-sm: 768px;
--bp-md: 992px;
--bp-lg: 1200px;
--bp-xl: 1400px;

/* Use consistently */
/* Or better: use container queries for component-level responsiveness */
```

---

### 5. Dark Mode / Palette System

#### ✅ Comprehensive
```css
body.rasamala-palette-dark:not(.rasamala-dark) {
  --rasamala-light-bg: var(--theme-background);
  --rasamala-text-primary: var(--theme-text);
  --rasamala-border: rgba(255, 255, 255, 0.15);
  --rasamala-card-shadow: 0 18px 46px rgba(0, 0, 0, 0.32);
  /* ... 200+ lines of overrides ... */
}
```

#### ⚠️ Issues
- **Single massive selector** with 100+ comma-separated rules
- **Duplicates light-mode rules** instead of inverting tokens
- **`not(.rasamala-dark)`** suggests conflicting dark mode implementations
- **No `prefers-color-scheme` integration** — purely class-based

#### 💡 Better architecture
```css
/* 1. Define palette modes as data attributes */
:root[data-palette="light"] { --bg: #fff; --text: #111; }
:root[data-palette="dark"]  { --bg: #111; --text: #fff; }
:root[data-palette="auto"]  { /* prefers-color-scheme */ }

/* 2. Single token set, inverted by mode */
:root {
  --surface: var(--palette-surface);
  --text: var(--palette-text);
  --border: var(--palette-border);
}

/* 3. Component styles NEVER change — only tokens do */
.card { background: var(--surface); color: var(--text); }
```

---

### 6. Component Patterns

#### Search Bar — Over-engineered
```css
/* 4 size variants, each with 3+ selectors */
.search-size-small .card-body { padding: 8px 16px !important; }
.search-size-small input { font-size: 14px !important; }
.search-size-small .btn, .search-size-small a { font-size: 16px !important; }

.search-size-medium .card-body { padding: 12px 24px !important; }
.search-size-medium input { font-size: 16px !important; }
.search-size-medium .btn, .search-size-medium a { font-size: 18px !important; }

.search-size-large .card-body { padding: 22px 36px !important; }
.search-size-large input { font-size: 22px !important; }
.search-size-large .btn, .search-size-large a { font-size: 26px !important; }
```

#### 💡 Simplify with CSS custom properties
```css
#search-wraper {
  --search-padding: 12px 24px;
  --search-input-size: 16px;
  --search-btn-size: 18px;
}

#search-wraper.search-size-small {
  --search-padding: 8px 16px;
  --search-input-size: 14px;
  --search-btn-size: 16px;
}

#search-wraper.search-size-large {
  --search-padding: 22px 36px;
  --search-input-size: 22px;
  --search-btn-size: 26px;
}

#search-wraper .card-body { padding: var(--search-padding); }
#search-wraper input { font-size: var(--search-input-size); }
#search-wraper .btn { font-size: var(--search-btn-size); }
```

#### Card System — Inconsistent
```css
/* Multiple card patterns with different radii/shadows */
.card { border-radius: 18px; box-shadow: var(--rasamala-card-shadow); }
.biblio-list-card { border-radius: 18px; box-shadow: 0 16px 36px...; }
.topic li { border-radius: 18px; width: 150px; height: 150px; }
.slims-book-card { border-radius: 14px; }
.member-card { border-radius: 16px; }
.detail-record { border-radius: 16px; }
.availability-summary-card { border-radius: 12px; }
```

#### 💡 Unified card system
```css
.card {
  --card-radius: var(--radius-lg);
  --card-shadow: var(--shadow-md);
  --card-border: var(--border-subtle);
  border-radius: var(--card-radius);
  box-shadow: var(--card-shadow);
  border: 1px solid var(--card-border);
}

.card--elevated { --card-shadow: var(--shadow-lg); }
.card--flat { --card-shadow: none; --card-border: var(--border-strong); }
.card--interactive { transition: transform .3s, box-shadow .3s; }
.card--interactive:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); }
```

---

### 7. Animation & Performance

#### ✅ Good practices
- `prefers-reduced-motion` respected for prayer reminder
- `will-change` used on animated elements
- `contain: strict` on background animation layer
- GPU-friendly transforms (translate3d, scale)

#### ⚠️ Concerns
```css
/* Expensive filters on many elements */
.hero-orb {
  filter: blur(80px);  /* Very expensive! */
  mix-blend-mode: plus-lighter;
}

/* Multiple background gradients on body */
body.rasamala-background-animation-active {
  background:
    radial-gradient(circle at 12% 18%, rgba(...), transparent 30%),
    radial-gradient(circle at 88% 4%, rgba(...), transparent 34%),
    radial-gradient(circle at 62% 94%, rgba(...), transparent 38%),
    linear-gradient(...);
}

/* Animation on pseudo-elements with large areas */
.hero-animation-waves::before,
.hero-animation-waves::after { /* large gradients animating */ }
```

#### 💡 Optimizations
```css
/* 1. Replace blur with pre-rendered gradient or SVG */
.hero-orb {
  /* Use radial-gradient with stops instead of blur() */
  background: radial-gradient(circle at center, var(--accent) 0%, transparent 70%);
}

/* 2. Limit animation to transform/opacity only */
@keyframes heroOrbMove1 {
  0% { transform: translate3d(0, 0, 0) scale(1); }
  100% { transform: translate3d(15vw, 10vh, 0) scale(1.15); }
}

/* 3. Use containment */
.hero-animation-layer {
  contain: paint layout style;
}

/* 4. Consider CSS @property for smoother color animations */
@property --accent-glow {
  syntax: '<color>';
  inherits: false;
  initial-value: transparent;
}
```

---

### 8. Accessibility

#### ✅ Good
```css
/* Focus visible for keyboard navigation */
a:focus-visible,
button:focus-visible,
.btn:focus-visible,
[tabindex]:focus-visible {
  outline: 3px solid rgba(var(--theme-accent-rgb), 0.45) !important;
  outline-offset: 2px !important;
  box-shadow: 0 0 0 4px rgba(var(--theme-accent-rgb), 0.15) !important;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .prayer-reminder-toast,
  .prayer-reminder-icon,
  .prayer-reminder-icon::after {
    animation: none !important;
  }
}

/* Screen reader only utility */
.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}
```

#### ⚠️ Gaps
- **No high contrast mode** (`prefers-contrast: more`)
- **Focus styles inconsistent** — some components lose focus ring
- **Color contrast** — some muted text on light backgrounds may fail WCAG AA
- **Touch targets** — some icons/buttons < 44px on mobile

---

### 9. Bootstrap Integration

#### Current approach: Override everything
```css
/* 200+ lines of Bootstrap class overrides */
.btn-primary,
.btn-info,
.btn-success,
.btn-secondary,
.search-type-btn.btn-primary,
.btn-news-readmore,
.btn-modal-action,
.libinfo-modal-btn {
  background-color: var(--rasamala-accent) !important;
  border-color: var(--rasamala-accent) !important;
  color: var(--theme-on-primary) !important;
}

.text-primary { color: var(--rasamala-accent) !important; }
.text-secondary { color: var(--rasamala-text-secondary) !important; }
.text-muted { color: var(--theme-muted) !important; }
.bg-primary { background-color: var(--rasamala-accent) !important; }
.bg-info { background-color: var(--rasamala-accent) !important; }
.bg-success { background-color: var(--rasamala-accent) !important; }
.bg-danger { background-color: var(--rasamala-text-secondary-dark) !important; }
.border-primary { border-color: var(--rasamala-accent) !important; }

.form-control:focus {
  border-color: var(--rasamala-accent) !important;
  box-shadow: 0 0 0 0.2rem rgba(var(--theme-accent-rgb), 0.25) !important;
}
```

#### 💡 Better: Use Bootstrap 5's CSS variables
```css
/* Bootstrap 5.3+ supports this natively */
:root {
  --bs-primary: var(--rasamala-accent);
  --bs-primary-rgb: var(--theme-accent-rgb);
  --bs-body-font-family: var(--rasamala-font-stack);
  --bs-border-radius: var(--radius-lg);
  --bs-border-radius-lg: var(--radius-xl);
  --bs-btn-border-radius: var(--radius-md);
  --bs-btn-padding-x: 1rem;
  --bs-btn-padding-y: 0.5rem;
  --bs-btn-font-weight: 600;
  --bs-link-color: var(--theme-link);
  --bs-link-hover-color: var(--theme-link-hover);
  --bs-focus-ring-color: rgba(var(--theme-accent-rgb), 0.45);
}
```

This eliminates ~150 lines of overrides.

---

### 10. Code Quality Issues

#### Duplicate Rules (End of File)
Lines ~8000-9953 re-declare:
- Card styles (again)
- Topic card styles (again)
- Homepage section styles (again)
- News card styles (again)
- Pagination styles (again)
- Mobile modal styles (again)
- Member area styles (again)

#### Dead/Unused Code
```css
/* Tailwind compatibility classes — likely unused */
.flex { display: flex !important; }
.flex-col { flex-direction: column !important; }
.flex-row { flex-direction: row !important; }
.w-full { width: 100% !important; }
.block { display: block !important; }
.border-gray-300 { border-color: rgba(66, 66, 66, 0.08) !important; }
.mx-auto { margin-left: auto !important; margin-right: auto !important; }
.mx-2 { margin-left: 0.5rem !important; margin-right: 0.5rem !important; }
```

#### Magic Numbers
```css
z-index: 12000;  /* header */
z-index: 12020;  /* dropdown */
z-index: 12030;  /* dropdown menu */
z-index: 1044;   /* back to top */
z-index: 1045;   /* floating info */
z-index: 1046;   /* color mode toggle */
z-index: 1047;   /* palette switcher */
z-index: 1056;   /* palette panel */
z-index: 1065;   /* prayer toast */
z-index: 13050;  /* modals */
z-index: 13040;  /* modal backdrop */
z-index: 9999;   /* mobile bottom nav */
z-index: 99999;  /* advanced search */
```

---

## Refactoring Roadmap

### Phase 1: Quick Wins (1-2 days)
| Task | Effort | Impact |
|------|--------|--------|
| Remove duplicate rules at end of file | Low | -30KB, readability |
| Remove unused Tailwind compat classes | Low | -2KB |
| Consolidate z-index into tokens | Low | Maintainability |
| Convert Bootstrap overrides to CSS vars | Medium | -150 lines, future-proof |

### Phase 2: Architecture (1 week)
| Task | Effort | Impact |
|------|--------|--------|
| Remove `!important` via specificity scoping | High | Maintainability, debuggability |
| Unify card/component token system | Medium | Consistency, easier theming |
| Restructure dark mode as token inversion | Medium | Simplify 200+ lines |
| Extract page-specific CSS to separate files | Medium | Caching, code splitting |

### Phase 3: Modernization (Ongoing)
| Task | Effort | Impact |
|------|--------|--------|
| Adopt cascade layers (`@layer`) | Medium | Eliminate specificity wars |
| Use container queries for components | Medium | True component responsiveness |
| Add `@property` for animated colors | Low | Smoother animations |
| Build-time CSS optimization (PurgeCSS, cssnano) | Low | Production bundle size |

---

## File Size Breakdown (Estimated)

| Section | Lines | Est. Size |
|---------|-------|-----------|
| Design tokens & globals | ~300 | 12 KB |
| Layout & navbar | ~800 | 28 KB |
| Search & inputs | ~400 | 14 KB |
| Cards & grids | ~600 | 20 KB |
| Search results | ~800 | 28 KB |
| Filters & sorting | ~400 | 14 KB |
| Detail page | ~600 | 20 KB |
| Mobile nav & floating | ~500 | 18 KB |
| Hero & animations | ~1000 | 35 KB |
| Footer & misc | ~400 | 14 KB |
| **Duplicates (end)** | **~2000** | **~60 KB** |
| **Total** | **~9953** | **~285 KB** |

**Target after refactor: ~120-150 KB** (50% reduction)

---

## Specific Recommendations

### 1. Split into Multiple Files
```
css/
├── tokens.css           # Design tokens only (~5 KB)
├── base.css             # Reset, typography, globals (~15 KB)
├── components/
│   ├── buttons.css
│   ├── cards.css
│   ├── forms.css
│   ├── modals.css
│   ├── dropdowns.css
│   └── pagination.css
├── layout/
│   ├── header.css
│   ├── footer.css
│   ├── hero.css
│   └── grid.css
├── pages/
│   ├── home.css
│   ├── search.css
│   ├── detail.css
│   ├── member.css
│   └── news.css
├── themes/
│   ├── light.css
│   ├── dark.css
│   └── palette.css
└── utilities.css        # visually-hidden, focus-visible, etc.
```

**Build step:** Concatenate + minify for production.

### 2. Adopt a Methodology
- **BEM** or **CUBE CSS** for class naming consistency
- **Cascade Layers** for specificity management
- **Design Tokens** as single source of truth (export to JSON for JS)

### 3. Linting & CI
```json
// stylelint.config.js
{
  "extends": ["stylelint-config-standard", "stylelint-config-recess-order"],
  "rules": {
    "declaration-no-important": true,
    "selector-max-compound-selectors": 3,
    "selector-max-id": 0,
    "custom-property-pattern": "^(theme|rasamala|bs|color|radius|shadow|font|bp)-"
  }
}
```

---

## Conclusion

The Rasamala theme is **feature-complete and visually polished** but suffers from **organic growth without refactoring**. The design token system is a strong foundation — the main work is:

1. **Eliminate `!important`** via proper scoping
2. **Remove duplicates** at end of file
3. **Leverage Bootstrap 5 CSS variables** instead of overriding classes
4. **Split into modular files** for maintainability
5. **Consolidate token aliases** to single source of truth

With focused refactoring, this could be a **showcase-quality** theme that's also maintainable long-term.

---

*Review generated: 2026-07-20*
*File analyzed: `/home/user/uploads/style.css` (284,542 bytes, 9,953 lines)*

</details>
