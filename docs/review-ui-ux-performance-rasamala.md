# Review UI/UX dan Performa Template Rasamala

**Target:** `template/rasamala` — custom OPAC SLiMS 9 Bulian  
**Tanggal audit:** 28 Juli 2026  
**Fokus:** bug, pengalaman pemustaka, aksesibilitas, dan performa  
**Prinsip utama:** pencarian katalog harus tetap menjadi fungsi paling cepat, jelas, dan stabil. Peningkatan visual tidak boleh menambah beban awal tanpa manfaat langsung.

## Ringkasan Eksekutif

Rasamala sudah memiliki fondasi yang baik: desain responsif, tampilan hasil pencarian sederhana sebagai default, sanitasi keluaran, aset lokal, tema terang/gelap, serta pemisahan helper dan partial PHP. Namun, versi saat ini belum tepat disebut sudah sempurna atau sepenuhnya performance-first.

Prioritas tertinggi bukan menambah fitur visual baru, melainkan:

1. Memperbaiki portal buku tamu yang saat ini menjalankan `visitor_counter.js` sebelum Vue tersedia.
2. Memperbaiki status ketersediaan, empty state, dan target modal yang salah.
3. Menghapus pemuatan skrip hasil pencarian ganda.
4. Mengganti autocomplete yang mengunduh satu halaman hasil pencarian penuh pada hampir setiap input.
5. Memecah aset berdasarkan halaman dan menonaktifkan efek dekoratif berat secara default.
6. Memutuskan ulang strategi PWA karena service worker saat ini tidak mengontrol halaman OPAC.

### Status singkat

| Area | Penilaian | Catatan |
|---|---|---|
| Fungsi inti | Perlu perbaikan segera | Ada bug buku tamu, empty state, modal, dan label ketersediaan |
| UI desktop | Cukup baik | Hierarki pencarian jelas, tetapi terlalu banyak kontrol dan efek global |
| UI mobile | Cukup | Bottom navigation membantu, tetapi dapat bertabrakan secara visual dengan ticker dan floating actions |
| Aksesibilitas | Perlu peningkatan | Beberapa pola terlihat aksesibel, tetapi combobox, accordion, dan lightbox belum lengkap |
| Performa awal | Perlu optimasi besar | Banyak CSS/JS dimuat global walaupun hanya dipakai di halaman tertentu |
| PWA/offline | Belum efektif | Scope service worker hanya berada di direktori aset JS |
| Maintainability | Menengah | Struktur modular baik, tetapi terdapat duplikasi, compatibility layer, dan handler modal yang saling tumpang tindih |

## Metode dan Batas Audit

Audit dilakukan terhadap kode aktif dan respons HTTP dari instalasi lokal `http://localhost/98/`.

- PHP lint berhasil pada **70 file** menggunakan PHP **7.4.30** dan **8.1.10**.
- Pemeriksaan sintaks berhasil pada **15 file JavaScript first-party**.
- Homepage, hasil pencarian, dan halaman visitor memberikan HTTP `200`.
- Respons HTML aktual dipakai untuk memeriksa urutan dan jumlah aset.
- Audit ini belum mencakup pengujian Lighthouse, axe, pembaca layar, throttling perangkat seluler nyata, dan pengujian pengguna. Nilai Core Web Vitals tidak boleh diklaim sebelum pengujian tersebut dilakukan.

## Temuan Bug

Skala prioritas:

- **P0:** fungsi utama rusak atau praktis tidak dapat digunakan.
- **P1:** berdampak besar pada akurasi, alur utama, atau performa.
- **P2:** masalah nyata dengan dampak sedang/kecil.

### BUG-01 — P0 — Portal buku tamu berhenti sebelum aplikasi Vue dibuat

**Bukti kode**

- `parts/visitor/visitor_ticker.php:70-71` memuat `axios.min.js`, lalu `visitor_counter.js`.
- `login_template.inc.php:14-18` baru memuat Vue setelah `$main_content` selesai dicetak.
- `assets/js/visitor_counter.js:22-25` langsung berhenti jika `window.Vue` belum tersedia.

Respons HTTP aktual juga menampilkan urutan:

1. `axios.min.js`
2. `visitor_counter.js`
3. `vue.min.js`

Akibatnya `visitor_counter.js` melakukan `return` dan tidak pernah dijalankan ulang. Form terlihat, tetapi binding, submit, jam, feedback, dan check-in berbasis Vue tidak aktif.

**Perbaikan**

- Muat Vue dan Axios sebelum `visitor_counter.js`.
- Lebih baik: hapus injeksi skrip dari `visitor_ticker.php`, lalu susun satu blok footer khusus visitor dengan urutan eksplisit.
- Tambahkan smoke test: setelah halaman dimuat, `#visitor-counter` harus memiliki atribut `data-v-app`, input harus dapat diisi, dan klik submit harus mengubah `aria-busy`.

### BUG-02 — P1 — Service worker tidak mengontrol halaman OPAC

**Bukti kode**

- `assets/js/app.js:1067-1072` mendaftarkan `template/rasamala/assets/js/sw.js` tanpa `scope`.
- Scope default service worker menjadi `/template/rasamala/assets/js/`, bukan root OPAC.
- Manifest menyatakan scope root pada `assets/manifest.json.php:33-34`, tetapi scope manifest tidak memperluas scope service worker.
- Respons `sw.js` tidak memiliki header `Service-Worker-Allowed`.

Fitur offline pada `assets/js/sw.js:75-142` praktis tidak mencegat navigasi `index.php`.

**Perbaikan**

Pilih salah satu:

1. Nonaktifkan registrasi service worker sampai implementasi PWA benar; atau
2. Sajikan service worker dari root aplikasi dan daftarkan scope OPAC secara eksplisit.

Jika PWA dipertahankan:

- Jangan cache halaman admin, login, member area, respons POST, atau HTML personal.
- Cache hanya shell publik dan aset versi tertentu.
- Gunakan offline fallback sebagai file/shell yang teruji.
- Naikkan versi cache ketika aset tanpa query version berubah.

### BUG-03 — P1 — Empty state tidak dapat diandalkan

**Bukti kode**

`parts/_result-search.php:163-177` memutuskan tidak ada hasil dengan:

```php
trim(strip_tags($main_content)) === ''
```

Pada mode development, debug markup tetap berada di `$main_content`, meskipun `.biblioResult` kosong. Respons aktual untuk pencarian tanpa hasil tidak menampilkan `.search-empty-state`.

**Perbaikan**

- Gunakan sumber kebenaran yang sudah tersedia: `(int) $engine->getNumRows() === 0`.
- Pisahkan debug/error dari kondisi data kosong.
- Uji pada environment development dan production.

### BUG-04 — P1 — Tombol Advanced Search pada empty state mengarah ke modal yang tidak ada

**Bukti kode**

- `parts/_result-search.php:172` menargetkan `#adv-search`.
- Modal yang tersedia adalah `#adv-modal` pada `parts/modals.php:12`.

Bug ini akan langsung terlihat setelah BUG-03 diperbaiki.

**Perbaikan**

Gunakan satu konstanta/ID `adv-modal` untuk semua trigger. Tambahkan tes yang membuka modal dari search box dan empty state.

### BUG-05 — P1 — Label menulis “Available” walaupun stok 0

**Bukti kode**

Pada `biblio_list_template.php:202-204`, ikon dan warna mengikuti stok, tetapi teks selalu:

```php
themeEscape(__('Available'))
```

Pemustaka dapat melihat ikon silang merah dan angka `0/x`, tetapi tetap membaca “Available”.

**Perbaikan**

Buat satu nilai status:

```php
$availability_label = $availability > 0 ? __('Available') : __('Not Available');
```

Gunakan nilai yang sama untuk teks, `aria-label`, title, dan status class.

### BUG-06 — P1 — `result_search.js` dimuat dua kali pada halaman hasil

**Bukti kode**

- Pemuatan pertama: `parts/_result-search.php:289`, parser-blocking dan terjadi sebelum jQuery tersedia.
- Pemuatan kedua: `parts/footer.php:130`, menggunakan `defer`.
- Respons HTML aktual berisi dua tag `<script>` untuk URL yang sama.

Eksekusi pertama membaca seluruh berkas lalu berhenti di `assets/js/result_search.js:7-9` karena jQuery belum ada. Ini membuang parse/compile time dan bertentangan dengan klaim “zero render-blocking JS”.

**Perbaikan**

- Hapus pemuatan pada `_result-search.php`.
- Muat satu kali dari footer dan hanya saat halaman hasil pencarian aktif.
- Tambahkan guard inisialisasi agar event handler tidak dapat didaftarkan dua kali.

### BUG-07 — P1 — Autocomplete membebani server dan memiliki race condition

**Bukti kode**

`assets/js/app.js:839-904`:

- debounce hanya 250 ms;
- setiap query minimal dua karakter meminta halaman penuh `index.php?...&search=search`;
- respons HTML hasil pencarian diparsing dengan `DOMParser`;
- tidak menggunakan `AbortController` atau nomor urut request.

Dampaknya:

- query database, filter, layout, footer, dan seluruh template dirender hanya untuk mengambil maksimal lima judul;
- konsumsi data tinggi pada ponsel;
- respons query lama dapat datang belakangan dan menimpa saran query terbaru;
- lonjakan request terjadi saat banyak pengunjung mengetik bersamaan.

**Perbaikan**

- Buat endpoint JSON ringan, maksimal 5-8 item dengan field `id`, `title`, dan `author`.
- Minimal input 3 karakter, debounce 350-500 ms.
- Batalkan request sebelumnya dengan `AbortController`.
- Abaikan respons yang bukan query terbaru.
- Cache query pendek di browser dan server, lalu beri rate limit.
- Jika endpoint ringan belum tersedia, nonaktifkan live request dan pertahankan riwayat lokal saja.

### BUG-08 — P2 — Cover yang tersedia mendapat class HTML `1`

**Bukti kode**

`biblio_list_template.php:141-142` menggunakan:

```php
($availability > 0 ?: 'not-available')
```

Saat tersedia, ekspresi menghasilkan boolean `true` yang menjadi string `1`.

**Perbaikan**

Gunakan:

```php
$availability > 0 ? 'is-available' : 'not-available'
```

### BUG-09 — P2 — Manifest mendeklarasikan format dan ukuran ikon tanpa verifikasi

**Bukti kode**

`assets/manifest.json.php:19-50` dapat menggunakan `webicon.ico` atau logo biasa, tetapi selalu mendeklarasikannya sebagai `image/png` ukuran `192x192` dan `512x512`, sekaligus `maskable`.

**Dampak**

- ikon instalasi PWA dapat ditolak atau terlihat rusak;
- logo dapat terpotong pada perangkat yang menerapkan mask;
- metadata tidak sesuai file aktual.

**Perbaikan**

Sediakan file nyata:

- `icon-192.png`;
- `icon-512.png`;
- `icon-maskable-512.png` dengan safe zone.

Deklarasikan masing-masing menggunakan ukuran, MIME type, dan purpose yang benar.

### BUG-10 — P2 — Pemotongan teks belum aman untuk UTF-8

**Bukti kode**

- `biblio_list_template.php:57` memakai `substr`.
- `biblio_list_template.php:347-354` memakai `strlen` dan `substr`.
- `parts/_result-search.php:109-110` memotong keyword dengan fungsi byte-based.

Judul atau deskripsi dengan karakter multibyte dapat terpotong di tengah byte dan menghasilkan teks rusak.

**Perbaikan**

Gunakan helper tunggal berbasis `mb_strlen`/`mb_substr`, dengan fallback terkontrol jika ekstensi `mbstring` tidak tersedia.

### BUG-11 — P2 — Elemen interaktif tertentu bukan kontrol keyboard yang baik

**Bukti kode**

- Badge `Ctrl+K` pada `parts/_search-form.php:139-141` berupa `<kbd onclick>`, bukan tombol.
- Trigger accordion footer pada `parts/footer.php:29-30`, `44-45`, dan `56-57` berupa `<div>`/`<h2>` dengan atribut collapse.
- Tombol close mobile more menu pada `parts/mobile_bottom_nav.php:285` tidak menetapkan `type="button"`.

**Perbaikan**

- Gunakan `<button type="button">` untuk semua trigger.
- Pertahankan heading di luar atau di dalam button secara semantik benar.
- Sinkronkan `aria-expanded` saat state collapse berubah.
- Pastikan target fokus minimal 44×44 px tanpa memperbesar semua elemen dekoratif.

### BUG-12 — P2 — Lightbox native belum lengkap untuk aksesibilitas

**Bukti kode**

`detail_template.php:172-200` membuat modal melalui string HTML dan hanya menangani klik.

Belum ada:

- tombol Escape;
- focus trap;
- fokus awal dan pengembalian fokus;
- penguncian scroll yang konsisten;
- label dialog yang deskriptif.

**Perbaikan**

Gunakan satu modal Bootstrap yang sudah ada di DOM dan ganti `src`/`alt` saat dibuka. Hindari membuat modal baru melalui `insertAdjacentHTML`.

## Baseline Performa Saat Ini

Baseline berikut adalah ukuran sumber sebelum gzip/Brotli; ukuran transfer aktual bergantung pada konfigurasi server dan cache.

| Indikator | Hasil |
|---|---:|
| Script `src` pada homepage aktual | 22 |
| Tag stylesheet pada homepage aktual | 13 |
| CSS global tema + shared SLiMS yang teridentifikasi | sekitar 739 KB |
| JS tema/vendor + shared SLiMS pada konfigurasi default | sekitar 631 KB |
| Font lokal tersedia | 36 file, sekitar 785 KB total di repository |
| Flag SVG tersedia | 514 file, sekitar 4,6 MB di repository |

Repository size font dan flag tidak otomatis sama dengan transfer awal, tetapi struktur ini menambah biaya distribusi, pemeliharaan, dan peluang cache miss.

### Aset yang saat ini terlalu global

`parts/header.php:57-77` dan `parts/footer.php:103-157` memuat banyak aset di hampir semua halaman:

- Vue, jQuery, Bootstrap, Masonry;
- CKEditor CSS pada OPAC publik;
- Colorbox CSS/JS walaupun detail sudah memiliki lightbox native;
- Ion Range Slider pada halaman non-pencarian;
- `result_search.js` pada homepage/detail/member;
- `highlight.js`, `fancywebsocket.js`, theme viewer, drawer, dan palette scripts secara global;
- animasi hero dan partikel kursor aktif pada konfigurasi default.

`defer` membantu parsing HTML, tetapi tidak menghilangkan biaya download, parse, compile, eksekusi, dan event handler.

## Rekomendasi Performa-First

### 1. Terapkan asset loading per halaman

Gunakan matriks sederhana:

| Aset | Global | Home | Search | Detail | Member | Visitor |
|---|---:|---:|---:|---:|---:|---:|
| CSS foundation/core | Ya | Ya | Ya | Ya | Ya | Ya |
| Vue + `app.js` | Tidak | Jika komponen Vue aktif | Untuk search box bila masih Vue | Tidak | Tidak | Ya |
| Masonry | Tidak | Tidak | Hanya view grid | Tidak | Tidak | Tidak |
| Ion Range Slider | Tidak | Tidak | Jika filter tahun tampil | Tidak | Tidak | Tidak |
| `result_search.js` | Tidak | Tidak | Ya, satu kali | Tidak | Tidak | Tidak |
| `member_area.js` | Tidak | Tidak | Tidak | Tidak | Ya | Tidak |
| `visitor_counter.js` | Tidak | Tidak | Tidak | Tidak | Tidak | Ya |
| Hero/particles | Tidak | Kondisional | Sebaiknya tidak | Tidak | Tidak | Tidak |
| Theme viewer/drawer | Tidak | Hanya jika viewer diaktifkan | Hanya jika viewer diaktifkan | Hanya jika viewer diaktifkan | Tidak | Tidak |

Setelah pemisahan, hapus CSS/JS global yang tidak memiliki pemakai pada halaman tersebut.

### 2. Gunakan default yang ringan

Konfigurasi awal saat ini mengaktifkan:

- `neural-network` pada `helpers/tinfo_defaults.php:124`;
- cursor particles `auto` pada `helpers/tinfo_defaults.php:126`;
- palette switcher pada `helpers/tinfo_defaults.php:180`.

Default performance-first yang disarankan:

```text
font               = system
hero animation     = none
cursor particles   = none
custom cursor      = default
palette viewer     = hidden
map                = click-to-load atau lazy
ticker             = off, kecuali benar-benar dibutuhkan
```

Admin tetap dapat mengaktifkan efek, tetapi pengguna pertama tidak menanggung biaya fitur demo.

### 3. Kurangi request homepage

`parts/_home.php:142-167` dapat memicu sampai lima request API segera setelah mount:

- popular subject;
- popular collection;
- latest subject;
- latest collection;
- top reader.

Saran:

- muat section saat mendekati viewport memakai `IntersectionObserver`;
- gabungkan subject dan collection bila sumber datanya berkaitan;
- cache respons publik di server;
- prioritaskan satu section utama, sisanya progressive enhancement;
- pertimbangkan server-side rendering untuk koleksi pertama agar konten terlihat tanpa menunggu Vue.

### 4. Evaluasi kebutuhan Vue

`vue.min.js` sekitar 161 KB sumber dipakai untuk beberapa komponen kecil dan form visitor. Opsi jangka menengah:

- pertahankan Vue hanya pada visitor jika biaya migrasi tinggi;
- gunakan server-rendered HTML + vanilla JS ringan untuk homepage dan search box;
- jangan memuat Vue pada detail, content, librarian, dan member page jika tidak ada komponen Vue.

### 5. Pecah dan rapikan CSS

Saat ini `opac-pages.css` sekitar 220 KB, `foundation.css` sekitar 75 KB, dan `theme-components.css` sekitar 73 KB.

Saran pembagian:

```text
core.css
home.css
search.css
detail.css
member.css
visitor.css
theme-tools.css
```

- Minify hasil produksi.
- Hapus selector lama setelah memastikan tidak dipakai.
- Jangan melakukan purge otomatis terhadap class dinamis PHP/Vue tanpa safelist.
- Inline hanya critical CSS yang benar-benar kecil; jangan menggandakan seluruh stylesheet.

### 6. Optimalkan font dan ikon

- Karena default sudah mendukung system font, jangan selalu memuat deklarasi semua keluarga font.
- Muat stylesheet font sesuai pilihan admin.
- Batasi weight ke `400`, `600`, dan `700` jika desain tidak memerlukan yang lain.
- Gunakan subset Latin/Latin Extended untuk instalasi Indonesia bila bahasa lain tidak diperlukan.
- Pertimbangkan SVG sprite atau subset Font Awesome untuk ikon yang benar-benar digunakan.
- Jangan preload font brand pada halaman tanpa ikon brand.

### 7. Stabilkan gambar dan cegah layout shift

Beberapa gambar pada `biblio_list_template.php:141-142`, `parts/_home.php:115`, dan helper logo tidak memiliki dimensi intrinsik.

- Tetapkan `width` dan `height`, atau `aspect-ratio`, pada cover, logo, avatar, dan thumbnail.
- Gunakan `loading="lazy"` untuk gambar di bawah fold.
- Jangan lazy-load gambar yang menjadi kandidat LCP.
- Gunakan thumbnail dengan ukuran sesuai slot, format WebP/AVIF jika pipeline SLiMS mendukung, dan fallback standar.

### 8. Hindari network call eksternal pada critical path

Fitur waktu salat pada `parts/waktu_sholat.php:82-101` dapat menunggu cURL satu detik lalu fallback `file_get_contents` satu detik pada cache miss/gagal.

- Default `hide` sudah benar dan harus dipertahankan.
- Jika aktif, isi cache melalui cron/background job, bukan saat request pengguna.
- Tampilkan data cache terakhir dengan penanda waktu pembaruan.
- Jangan menambahkan API eksternal baru ke critical rendering path.

### 9. Konfigurasi delivery aset

- Aktifkan Brotli atau gzip untuk HTML/CSS/JS/SVG/JSON.
- Gunakan cache panjang `immutable` untuk URL fingerprinted/versioned.
- Gunakan cache pendek atau revalidation untuk HTML.
- Hindari versi aset yang hanya mengandalkan cache service worker.
- Tambahkan pengukuran Server-Timing untuk query homepage/search yang mahal.

## Saran UI/UX dengan Biaya Performa Rendah

### 1. Jadikan pencarian sebagai satu-satunya fokus hero

Pertahankan:

- satu heading;
- satu input utama;
- satu tombol search;
- advanced search sebagai aksi sekunder.

Kurangi elemen yang bersaing dengan input: ticker, palette viewer, partikel, custom cursor, announcement, dan floating button tidak perlu aktif bersamaan.

### 2. Batasi satu permukaan navigasi persisten di mobile

Saat ini potensi elemen bawah meliputi:

- mobile bottom navigation;
- ticker;
- floating quick actions;
- WhatsApp/library info;
- back-to-top;
- palette switcher.

Aturan yang disarankan:

- bottom navigation menjadi permukaan utama;
- aksi detail hanya muncul pada halaman detail;
- ticker tidak fixed atau dapat ditutup;
- WhatsApp menjadi item “Lainnya” jika bottom navigation aktif;
- back-to-top baru muncul setelah scroll panjang dan tidak menutup aksi lain.

### 3. Perjelas informasi hasil pencarian

- Tampilkan jumlah hasil dan keyword tanpa sticky toolbar yang terlalu tinggi.
- Gunakan label status `Tersedia`, `Dipinjam`, `Tidak untuk dipinjam`, atau `Tidak ada eksemplar`; jangan mengandalkan merah/hijau.
- Pada mobile, tampilkan Filter dan Sort sebagai dua aksi utama. View mode dapat ditempatkan di menu sekunder.
- Pertahankan simple view sebagai default karena paling cepat dipindai dan tidak memuat cover.

### 4. Sederhanakan empty, loading, dan error state

- Empty: jelaskan tidak ada hasil, tawarkan koreksi keyword, reset, dan advanced search.
- Loading: tampilkan skeleton hanya jika respons belum selesai setelah sekitar 150-250 ms agar tidak berkedip.
- Error: bedakan masalah jaringan dari hasil kosong.
- Retry harus mengulang request yang gagal saja, bukan reload seluruh halaman.

### 5. Benahi search combobox

`parts/_search-form.php:126-206` sudah mendukung tombol panah, tetapi semantik screen reader belum lengkap.

Tambahkan:

- `role="combobox"` pada input/wrapper yang tepat;
- `aria-autocomplete="list"`;
- `aria-expanded`;
- `aria-controls="search-suggestions-list"`;
- ID unik setiap option;
- `aria-activedescendant` mengikuti item aktif.

Jangan mengumumkan spinner ikon saja; sediakan live region singkat seperti “5 saran tersedia”.

### 6. Konsisten dalam bahasa dan istilah

Saat ini terdapat campuran Indonesia dan Inggris, misalnya `Bagikan Koleksi`, `Visitor Check-In Portal`, `Scan for Link`, dan `see more..`.

- Gunakan fungsi translasi untuk semua teks UI.
- Pilih bahasa sesuai `default_lang`.
- Hindari hardcoded fallback dengan bahasa berbeda dari halaman.
- Gunakan istilah konsisten: “Keranjang”, “Simpan”, “Sitasi”, “Bagikan”, dan “Area Anggota”.

### 7. Hormati preferensi reduced motion dan save data

- Semua animasi dekoratif harus berhenti pada `prefers-reduced-motion: reduce`.
- Jangan memulai partikel/cursor effect pada perangkat touch.
- Bila `navigator.connection.saveData === true`, jangan memuat animasi, live autocomplete, atau map iframe.
- Animasi status maksimal menggunakan opacity/transform; hindari layout animation.

### 8. Rapikan modal

Gunakan Bootstrap 5 sebagai satu sumber perilaku modal. `assets/js/app_jquery.js:323-378` saat ini ikut memaksa show/hide dan menghapus semua backdrop.

- Hindari kombinasi `data-toggle` Bootstrap 4, `data-bs-toggle` Bootstrap 5, jQuery modal, dan force handler kecuali ada kebutuhan kompatibilitas yang dibuktikan.
- Jangan menghapus seluruh `.modal-backdrop` secara global.
- Pastikan fokus kembali ke trigger.
- Gunakan satu komponen share modal dan satu komponen QR modal.

## Performance Budget yang Disarankan

Ini adalah target internal proyek, bukan hasil pengukuran versi saat ini.

| Metrik | Target |
|---|---:|
| LCP mobile p75 | ≤ 2,5 detik |
| INP mobile p75 | ≤ 200 ms |
| CLS p75 | ≤ 0,10 |
| TTFB warm cache | ≤ 600 ms |
| Initial JS terkompresi | ≤ 250 KB |
| Initial CSS terkompresi | ≤ 150 KB |
| Payload autocomplete | ≤ 10 KB per request |
| Request API homepage sebelum scroll | maksimal 2 |
| Script first-party yang sama | tidak boleh dimuat lebih dari sekali |
| Third-party request critical path | 0 |

Untuk jaringan lambat, pencarian dasar dan navigasi harus tetap berfungsi meskipun Vue, animasi, map, theme viewer, atau service worker gagal.

## Urutan Implementasi

### Tahap 0 — Hotfix fungsi

1. Betulkan urutan skrip visitor.
2. Gunakan `getNumRows()` untuk empty state.
3. Betulkan target `#adv-modal`.
4. Betulkan teks status ketersediaan.
5. Hapus pemuatan ganda `result_search.js`.
6. Betulkan ternary class cover.
7. Ganti helper pemotongan teks menjadi UTF-8 safe.

### Tahap 1 — Kurangi beban awal

1. Buat asset loader per halaman.
2. Hapus CKEditor CSS dari OPAC publik jika tidak dipakai.
3. Muat Masonry, Ion Slider, result search, member, dan visitor script hanya pada halaman terkait.
4. Matikan animasi, partikel, ticker, dan palette viewer secara default.
5. Ubah autocomplete menjadi endpoint JSON ringan atau local history only.
6. Lazy-mount section homepage di bawah fold.

### Tahap 2 — Aksesibilitas dan konsistensi UI

1. Benahi combobox ARIA.
2. Ganti trigger non-button.
3. Konsolidasikan modal dan focus management.
4. Rapikan bahasa, label status, loading, empty, dan error state.
5. Uji keyboard-only, zoom 200%, high contrast, reduced motion, serta screen reader.

### Tahap 3 — PWA dan optimasi lanjutan

1. Putuskan apakah PWA memang diperlukan.
2. Jika ya, perbaiki lokasi/scope service worker dan keamanan cache.
3. Buat ikon PWA valid.
4. Terapkan cache server, compression, dan versioned immutable assets.
5. Jalankan Lighthouse dan uji perangkat nyata setelah cache kosong dan cache hangat.

## Checklist Verifikasi

### Fungsi

- [ ] Visitor form benar-benar termount dan submit bekerja.
- [ ] Pencarian tanpa hasil selalu menampilkan empty state.
- [ ] Tombol Advanced Search dari semua lokasi membuka modal yang sama.
- [ ] Label ketersediaan cocok dengan angka dan ikon.
- [ ] Pergantian simple/list/grid bekerja dengan keyboard dan touch.
- [ ] Bookmark, keranjang, sitasi, dan share menampilkan feedback yang benar.
- [ ] Tidak ada skrip yang dimuat dua kali.

### Aksesibilitas

- [ ] Semua kontrol dapat dicapai dan diaktifkan dengan keyboard.
- [ ] Focus visible tidak hilang.
- [ ] Modal mengunci fokus dan mengembalikan fokus ke trigger.
- [ ] Combobox diumumkan benar oleh screen reader.
- [ ] Status tidak disampaikan melalui warna saja.
- [ ] Zoom 200% tidak menyebabkan horizontal scroll pada viewport 320 CSS px.
- [ ] `prefers-reduced-motion` menonaktifkan efek dekoratif.

### Performa

- [ ] Lighthouse diuji pada mobile, cache kosong, minimal tiga kali.
- [ ] Network log menunjukkan maksimal satu request per file.
- [ ] Homepage tidak meminta endpoint section di bawah fold sebelum diperlukan.
- [ ] Autocomplete membatalkan request lama.
- [ ] Tidak ada Vue/Masonry/Ion Slider pada halaman yang tidak menggunakannya.
- [ ] Gambar memiliki dimensi stabil.
- [ ] Brotli/gzip dan cache header sudah aktif.
- [ ] Pencarian dasar tetap bekerja ketika JavaScript dinonaktifkan.

## Keputusan Produk yang Disarankan

Rasamala sebaiknya memiliki dua mode resmi:

1. **Performance mode sebagai default:** system font, tanpa partikel, tanpa custom cursor, tanpa theme viewer publik, request homepage minimal, dan semua fitur inti tetap berjalan tanpa JavaScript berat.
2. **Showcase mode sebagai pilihan admin:** animasi, palette viewer, ticker, map, dan efek dekoratif boleh diaktifkan dengan peringatan dampak performa.

Dengan pendekatan ini, Rasamala tetap fleksibel dan menarik secara visual, tetapi mayoritas pemustaka memperoleh pengalaman OPAC yang cepat, stabil, mudah dipahami, dan hemat data.

## Status Implementasi - 28 Juli 2026

Perbaikan prioritas performa dan fungsi telah diterapkan.

- Portal buku tamu kini memuat Vue, Axios, lalu `visitor_counter.js` dalam urutan `defer` yang terjamin; aset UI umum yang tidak dipakai portal tidak lagi dimuat.
- Empty state memakai jumlah hasil dari mesin pencarian, tombolnya menargetkan `#adv-modal`, dan label serta class ketersediaan mengikuti jumlah item yang benar.
- `result_search.js` hanya dimuat pada halaman hasil pencarian.
- Autocomplete menggunakan endpoint JSON `?rasamala_suggest=1&q=...`, dibatasi enam judul, memiliki debounce 250 ms, dan membatalkan request sebelumnya. Tidak ada lagi parsing HTML halaman hasil pencarian di browser.
- Vue/App, Masonry, Ion Slider, highlight, serta CSS slider dimuat hanya pada halaman yang memerlukannya. Theme viewer, animasi hero, dan partikel cursor sekarang nonaktif sebagai nilai default; admin tetap dapat mengaktifkannya secara sadar.
- Service worker baru berada di akar aplikasi (`/rasamala-sw.js`) sehingga scope-nya mencakup OPAC. Strategi cache hanya mencakup aset Rasamala; halaman, akun, pencarian, dan respons API selalu network-only. Registrasi worker lama di bawah `assets/js` dibersihkan.

Validasi yang sudah dilakukan: lint PHP 7.4 dan 8.1, pemeriksaan sintaks JavaScript, serta respons HTTP lokal untuk endpoint suggestion, urutan skrip visitor, empty state/modal, pemuatan satu `result_search.js`, dan pemisahan aset homepage/detail. Uji keyboard, pembaca layar, serta instalasi/offline PWA tetap perlu dilakukan di browser perangkat nyata.
