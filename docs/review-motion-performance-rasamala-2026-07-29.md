# Review Motion & Rendering Performance Template Rasamala

**Tanggal audit:** 2026-07-29
**Lokasi:** `template/rasamala`
**Peran audit:** Web Motion Performance Specialist
**Fokus:** animasi, transisi, compositing, paint, lifecycle, reduced motion, dan performa mobile.

## Motion Audit

Rasamala sudah memiliki fondasi motion yang sehat: sebagian besar perpindahan memakai `transform`/`opacity`, DPR canvas dibatasi, cursor effect hanya aktif pada pointer fine, dan beberapa efek sudah menghormati `prefers-reduced-motion`. Namun, efek global masih dapat berjalan sepanjang umur halaman dan beberapa jalur memakai properti yang memaksa paint/layout.

| Area | Current issue | Rendering cost | Fix |
|---|---|---|---|
| Global hero/background canvas | `assets/js/hero_animation.js:58-104` membuat canvas viewport dan menjadwalkan `requestAnimationFrame` tanpa memeriksa `document.visibilityState` atau apakah layer sedang terlihat. | **Tinggi**: full-screen clear/draw tiap frame tetap menjadi kerja CPU/GPU ketika tab berpindah atau halaman tidak terlihat. | Tambahkan lifecycle pause/resume untuk `visibilitychange`, `pagehide/pageshow`, dan `IntersectionObserver`; hentikan loop, bukan hanya menyembunyikan canvas. |
| Neural network canvas | `assets/js/hero_animation.js:125-176` memeriksa pasangan node dengan loop O(n²) pada setiap frame. | **Tinggi bersyarat**: desktop memakai sampai 46 node dan ribuan pemeriksaan pasangan per frame. | Batasi 30 fps pada mode ini, gunakan spatial grid/quadtree atau kurangi node/distance pada perangkat rendah, lalu ukur ulang dengan CPU throttling. |
| Starfield/zen canvas | `assets/js/hero_animation.js:178-269` membersihkan dan menggambar ulang seluruh viewport setiap frame. | **Sedang–tinggi**: biaya meningkat mengikuti resolusi layar; layer bersifat fixed di semua halaman. | Pause ketika hidden/offscreen, turunkan fps/DPR berdasarkan `saveData`, dan sediakan fallback statis untuk perangkat rendah. |
| DOM hero animation | `assets/css/theme-components.css:854-1276` menempatkan `will-change: transform, opacity` pada seluruh token, node, ripple, orb, dan ember, sementara banyak animasi berjalan `infinite`. | **Tinggi pada halaman panjang**: setiap elemen dapat dipromosikan menjadi compositing layer; memory dan raster cache cepat bertambah. | Hapus `will-change` global; pasang hanya saat animasi aktif/terlihat, atau gunakan satu canvas untuk efek dekoratif. Batasi jumlah elemen. |
| Grid/waves CSS | `theme-components.css:929-944` menganimasikan `background-position` dan pseudo-element gradient. | **Sedang**: perubahan background-position memicu paint berulang, bukan jalur transform-only. | Gerakkan pseudo-element dengan `transform: translate3d()`; jadikan grid statis pada `liteMode`, `saveData`, atau reduced motion. |
| Running text ticker | `assets/css/opac-pages.css:3199-3247` sudah memakai `transform`, tetapi track selalu `infinite` dan memiliki `will-change: transform` permanen. | **Sedang**: satu loop compositor terus aktif di setiap halaman yang menampilkan ticker. | Pertahankan transform, tetapi pause saat tab hidden, hapus `will-change` default, dan aktifkan hanya ketika benar-benar berjalan. Sediakan kontrol pause keyboard yang tetap terlihat. |
| Fade slider info search | `parts/_search-form.php:248-257` membuat `setInterval(..., 4000)` tanpa cleanup, tanpa pause saat tab hidden, dan tanpa guard reduced motion. | **Rendah–sedang**: timer kecil, tetapi tetap membangunkan halaman dan dapat menumpuk pada re-init/preview. | Ganti dengan CSS animation atau satu timer yang dihentikan saat hidden; clear timer saat destroy dan tampilkan item statis pada reduced motion. |
| Visitor scan laser | `assets/css/visitor.css:394-412` mengubah `top` dari 10% ke 90% pada animasi berulang. | **Sedang**: properti layout berpotensi memicu layout/paint pada setiap frame. | Ubah menjadi `transform: translate3d(0, var(--scan-distance), 0)`; pertahankan `top` statis. Ganti semua `transition: all` di file ini dengan daftar properti eksplisit. |
| `transition: all` | Ada pada visitor, foundation, theme-components, dan opac-pages (contoh `visitor.css:18`, `foundation.css:841`, `theme-components.css:2066`, `opac-pages.css:5805`). | **Sedang saat interaksi**: perubahan layout/ukuran yang tidak sengaja ikut dianimasikan dan sulit diprediksi. | Gunakan hanya `transform`, `opacity`, `color`, `background-color`, `border-color`, dan `box-shadow` yang memang berubah. |
| Floating actions mobile | `theme-components.css:2497` menganimasikan `bottom` bersama `transform`; posisi juga berubah saat ticker/nav berubah. | **Sedang**: `bottom` adalah properti layout dan dapat menyebabkan reflow saat viewport/ticker berubah. | Gunakan satu anchor layout tetap lalu geser dengan `transform: translateY()`; hitung offset hanya saat state berubah, bukan sepanjang motion. |
| Backdrop/filter surfaces | Header/footer, palette panel, modal, bottom sheet, floating action, dan visitor card memakai beberapa `backdrop-filter: blur(16–25px)` (contoh `foundation.css:592-593,1243-1244`, `theme-components.css:17-18,192-193`, `visitor.css:14`). | **Sedang–tinggi** pada mobile/GPU rendah: blur area besar diraster ulang ketika konten di belakang bergerak atau modal dibuka. | Batasi satu blur layer per konteks, gunakan warna semi-opaque sebagai fallback/default mobile, dan hindari menumpuk blur pada panel di atas panel. |
| Background image treatment | `helpers/background.php:312-320` menerapkan filter/blur/scale pada fixed full-screen image layer. Opsi blur 8px dan filter diproses oleh browser pada area viewport besar. | **Sedang–tinggi** bila dipakai bersama canvas animation; biaya raster dan memory meningkat, khususnya saat scroll. | Default `none`, batasi blur maksimal pada low-end, pertimbangkan gambar yang sudah diproses, dan matikan blur ketika `saveData`/perangkat rendah. |
| Availability hover blur | `assets/css/opac-pages.css:487-494` menganimasikan `filter: blur(5px)` pada isi kartu ketika hover. | **Sedang saat pointer bergerak**: blur paint-heavy dan mengurangi keterbacaan informasi. | Gunakan overlay/opacity atau ganti isi dengan tooltip; jangan blur data inti katalog. |
| Skeleton shimmer | `foundation.css:715-818` dan `theme-components.css:2314-2317` menggerakkan `background-position` secara infinite. Tidak semua aturan skeleton memiliki blok reduced-motion khusus. | **Sedang** bila banyak kartu skeleton; dapat terus berjalan jika state loading tidak dibersihkan. | Gunakan satu pseudo-element transform-based, hentikan segera setelah data/error diterima, dan sediakan static skeleton untuk reduced motion. |
| Loading/citation state | Spinner citation hanya disembunyikan pada event `iframe.load` (`assets/js/app_jquery.js:81-83`). Tidak ada `error`/timeout fallback; overlay hasil pencarian juga bergantung pada class loading. | **Sedang + bug UX**: `fa-spin` dapat terlihat tidak pernah selesai ketika iframe diblokir/error atau state tidak di-reset. | Buat state machine `idle/loading/success/error`, tangani `load` dan `error`, beri timeout, dan pastikan cleanup dalam `finally`/`pageshow`. Spinner fungsional boleh tetap bergerak, tetapi harus punya pesan error yang jelas. |
| Progress bar navigasi | `assets/js/app_jquery.js:746-787` memperbarui `style.width` setiap 120–200 ms; CSS juga mentransisikan `width` (`foundation.css:868`). | **Rendah–sedang**: elemen fixed kecil, tetapi width adalah layout/paint path dan timer tetap aktif selama navigasi. | Gunakan `transform: scaleX()` dengan `transform-origin: left`, satu timer idempotent, dan reset pada `pageshow`. |
| Duplicate loading handlers | `result_search.js:409-419` dan `app_jquery.js:792-825` sama-sama memulai progress/loading untuk navigasi hasil pencarian. | **Rendah–sedang**: handler dan timer ganda dapat mereset state, memicu scroll halus dua kali, dan menyulitkan diagnosis freeze. | Pilih satu pemilik loading state; beri guard `data-loading-bound`/namespace event dan satu fungsi `startNavigationFeedback`. |
| Reduced motion coverage | Ada blok reduced-motion di `theme-components.css:1478-1511`, `opac-pages.css:2869-2872,6471-6475,6558-6570`, dan `visitor.css:418-440`, tetapi page fade (`theme-components.css:2612-2620`), shimmer, smooth-scroll, serta sebagian transition belum ikut dinonaktifkan. | **Aksesibilitas dan kenyamanan**: pengguna yang meminta minim motion masih menerima perpindahan halaman, smooth-scroll, atau paint shimmer. | Buat kebijakan global reduced-motion; matikan page entrance, ticker/fade/skeleton dekoratif, dan ubah semua smooth-scroll menjadi instant ketika media query aktif. |
| Cursor canvases | `cursor-particles.js` dan `cursor-icons.js` sebelumnya tetap menjadwalkan rAF saat hidden/leave; renderer partikel juga tidak dimuat bila nilai server `none`, sehingga pilihan Theme Viewer tidak punya listener. | **Rendah–sedang**: loop no-op membebani event loop dan toggle runtime tampak tidak bekerja. | Cancel loop ketika hidden/leave, muat renderer bila Theme Viewer tersedia, lalu restart pada `visibilitychange`/mouseenter; pertahankan guard pointer fine dan reduced motion. |
| Visitor clock interval | `assets/js/visitor_counter.js:93` membuat interval 1 detik tanpa menyimpan handle untuk cleanup atau pause ketika halaman hidden. | **Rendah**, tetapi lifecycle tidak lengkap pada mount/re-init. | Simpan handle, clear saat unmount/cleanup, dan pause saat hidden; gunakan `requestTimeout` hanya bila tab aktif. |

### Temuan positif

- `hero_animation.js` sudah membatasi DPR ke 1 atau 1.35 dan menurunkan jumlah objek pada viewport/perangkat kecil.
- Cursor particles/icons hanya aktif pada `pointer: fine`, memiliki caps jumlah partikel, dan merespons reduced motion.
- Ticker menggunakan `translate3d`, dapat dipause melalui hover/focus, dan backdrop-filter ticker sengaja dihapus untuk performa.
- Banyak kartu dan modal memakai transisi `transform`/`opacity`, serta cleanup canvas dilakukan ketika konfigurasi diinisialisasi ulang.
- Aset sudah terpisah secara modular (`foundation.css`, `opac-pages.css`, `theme-components.css`, `theme-dark.css`) sehingga optimasi per halaman memungkinkan.

### Batas bukti

Ini adalah audit kode dan smoke test runtime, bukan klaim skor performa. Belum ada flame chart Chrome Performance, CPU throttling, pengukuran FPS/INP/LCP pada perangkat nyata, Lighthouse, atau uji screen reader. Biaya pada tabel adalah klasifikasi jalur rendering berdasarkan kode dan harus dikonfirmasi dengan profiling sebelum menetapkan angka target.

## Implementation Plan

1. **P0 — ukur baseline.** Rekam homepage, hasil pencarian, detail, visitor, dan Theme Viewer pada desktop serta mobile dengan CPU 4× slowdown. Catat LCP, INP, long task, frame time, layer count, dan memory ketika animation/blur/image aktif.
2. **P1 — lifecycle motion.** Tambahkan satu controller untuk pause/resume canvas, cursor, ticker, fade slider, clock, dan progress bar pada `visibilitychange`, `pagehide/pageshow`, serta kondisi offscreen. Hilangkan handler loading ganda.
3. **P1 — jalur compositor.** Ganti `top`, `bottom`, `background-position`, dan `width` yang dianimasikan dengan transform atau state swap. Hilangkan `transition: all` dan `will-change` permanen.
4. **P2 — kurangi paint.** Gunakan fallback warna semi-opaque untuk blur, batasi filter/blur image, matikan efek dekoratif berat pada low-end/`saveData`, dan kurangi node neural/starfield.
5. **P2 — aksesibilitas.** Satukan kebijakan `prefers-reduced-motion`, nonaktifkan smooth-scroll/page fade/shimmer dekoratif, dan pastikan spinner selalu memiliki success/error/timeout state.
6. **P3 — verifikasi regresi.** Uji keyboard/focus, modal citation, mobile bottom nav/ticker, dark mode background image, Theme Viewer preview, visitor kiosk, serta navigasi back-forward cache.

### Saran perubahan bertahap

Gunakan satu tahap sebagai satu release kecil. Jangan mengaktifkan beberapa eksperimen motion baru sebelum tahap sebelumnya memiliki baseline dan rollback yang jelas.

#### Tahap 0 — Baseline dan feature flag *(belum dijalankan)*

- Simpan rekaman Chrome Performance untuk homepage, hasil pencarian, detail, visitor, dan Theme Viewer.
- Catat frame time, long task, LCP/INP, jumlah layer, dan memory pada desktop serta mobile CPU 4× slowdown.
- Tambahkan flag konfigurasi internal untuk mematikan background animation, blur, cursor effect, dan ticker secara terpisah.
- **Checkpoint:** tidak ada error console dan setiap flag dapat dikembalikan ke perilaku saat ini dalam satu perubahan konfigurasi.

#### Tahap 1 — Lifecycle tanpa perubahan visual *(selesai)*

- Pause/resume canvas hero, cursor canvas, ticker, fade slider, clock, dan progress timer saat tab hidden atau page lifecycle berhenti.
- Satukan handler loading navigasi agar hanya ada satu pemilik state.
- Tambahkan timeout/error state pada citation iframe dan loading overlay.
- **Checkpoint:** visual normal saat tab aktif, tidak ada rAF/timer dekoratif pada tab hidden, dan spinner selalu berakhir pada success/error/timeout.

#### Tahap 2 — Ganti jalur rendering mahal *(selesai untuk jalur utama)*

- Ubah animasi `top`, `bottom`, `background-position`, dan `width` menjadi `transform`/`scaleX`.
- Hilangkan `transition: all` dan `will-change` permanen; aktifkan `will-change` hanya selama animasi yang benar-benar terlihat.
- Ganti hover blur kartu availability dengan overlay/opacity.
- **Checkpoint:** frame time interaksi tidak memburuk, tidak ada layout shift baru, dan focus/klik tetap identik di desktop maupun mobile.

#### Tahap 3 — Kurangi efek berdasarkan kemampuan perangkat *(selesai untuk fallback kode)*

- Default low-end/mobile/`saveData` ke animasi statis atau 30 fps, tanpa blur full-screen dan tanpa cursor decoration.
- Batasi node neural/starfield dan gunakan aset gambar yang sudah dioptimalkan; pertahankan background animation di atas background style.
- Terapkan fallback warna semi-opaque ketika `backdrop-filter` tidak tersedia atau terlalu mahal.
- **Checkpoint:** uji perangkat Android/iOS kelas rendah, dark mode, gambar background, tab/standard homepage, dan Theme Viewer tanpa duplikasi.

#### Tahap 4 — Reduced motion dan rollout final *(selesai untuk aturan kode; rollout menunggu profiling)*

- Satukan aturan `prefers-reduced-motion` untuk page fade, smooth-scroll, shimmer, ticker/fade dekoratif, modal, hero, cursor, dan visitor.
- Uji keyboard-only, zoom 200%, screen reader, modal citation, serta back-forward cache.
- Rilis bertahap: internal/admin preview → sebagian pengguna → default seluruh pengguna.
- **Rollback:** matikan flag efek yang bermasalah tanpa mengubah pencarian, navigasi, atau data katalog.

## Code Changes

Implementasi bertahap yang sudah diterapkan pada Tahap 1–4:

- `assets/js/motion_lifecycle.js` — coordinator visibility/page lifecycle; pause fade slider dan ticker ketika halaman hidden; menghapus timer fade inline.
- `assets/js/hero_animation.js` — canvas hero menghentikan rAF saat hidden/page lifecycle berhenti dan memulai kembali ketika visible.
- `assets/js/cursor-particles.js`, `assets/js/cursor-icons.js` — rAF benar-benar dibatalkan saat hidden/leave, bukan hanya melewati draw.
- `parts/footer.php`, `assets/js/cursor-particles.js`, `assets/js/cursor-icons.js` — renderer cursor tetap dimuat ketika Theme Viewer aktif, kualitas adaptif diturunkan saat Save-Data tanpa menghilangkan efek, dan style dinamis membawa nonce CSP.
- `assets/js/app_jquery.js`, `assets/js/result_search.js` — loading navigation idempotent, citation memiliki load/error/timeout, dan state dibersihkan saat `pageshow`.
- `assets/js/visitor_counter.js`, `login_template.inc.php` — lifecycle visitor clock dibersihkan/dipause dan coordinator dimuat sebelum aplikasi visitor.
- `assets/css/foundation.css`, `theme-components.css`, `opac-pages.css`, `visitor.css` — progress bar memakai `scaleX`, scan laser memakai transform, grid memakai pseudo-element transform, pause state ditambahkan, `transition: all` dihapus pada jalur yang diaudit, serta `will-change` permanen dikurangi.
- `assets/js/motion_lifecycle.js`, `hero_animation.js`, `cursor-particles.js`, `cursor-icons.js` — mode adaptif/auto menurunkan kualitas saat Save-Data, canvas lite mode dibatasi 30 fps, dan reduced-motion capability diekspos terpusat.
- `assets/css/theme-components.css`, `visitor.css` — fallback tanpa backdrop blur pada `saveData`, pause laser/ticker/hero ketika hidden, dan aturan reduced-motion untuk page fade, shimmer, fade slider, serta progress bar.
- `assets/js/app_jquery.js`, `result_search.js` — smooth-scroll otomatis menjadi instant ketika reduced motion aktif.

Tahap 0 (flame chart, Lighthouse, dan feature-flag rollout) masih menunggu profiling browser/perangkat nyata.

## Verification Checklist

- [x] Seluruh sumber motion first-party yang relevan di CSS, JavaScript, dan partial PHP diinventarisasi.
- [x] `node --check` lulus untuk 18 file JavaScript non-minified.
- [x] PHP lint lulus untuk 74 file PHP menggunakan PHP Laragon 8.1.10.
- [x] Homepage, visitor, dan detail (`p=show_detail&id=6`) merespons HTTP 200 tanpa `Fatal error`, `Parse error`, `Warning`, atau `Notice` pada HTML.
- [x] `git diff --check -- template/rasamala` tidak menemukan whitespace error.
- [x] Tahap 1–2: lifecycle hidden/pagehide, loading cleanup, transform-only progress/laser/grid, dan pengurangan `will-change` sudah diterapkan.
- [x] Tahap 3–4: fallback `saveData`, canvas 30 fps/lite mode, cursor opt-out, dan reduced-motion global sudah diterapkan pada kode.
- [ ] Chrome Performance trace, Lighthouse, axe, screen reader, dan perangkat mobile nyata belum dijalankan.
- [ ] Setelah implementasi, verifikasi target: tidak ada rAF/timer aktif pada tab hidden, reduced motion menghentikan efek dekoratif, dan loading state selalu berakhir pada success/error/timeout.
