# Rasamala — Smoke Test dan Release Gate

> **TL;DR:** Jalankan lint PHP, cek parity default TInfo dengan database aktif, lalu uji homepage, pencarian, detail, visitor, member, login, Theme Viewer, background, mobile, dan cleanup worker lama. Sebelum publish, tiga kontrol tetap di-release gate: Panel Background, Cursor Icon, dan Cursor Particles.

## Status fact-check

Dokumen ini adalah checklist operasional dan sumber ringkas release gate Rasamala.
Fact-check terakhir: **2026-08-06**.

Sumber verifikasi:

- `helpers/tinfo_defaults.php`
- `helpers/options/*.php`
- `helpers/theme_feature_flags.php`
- `helpers/backgrounds/theme_background_styles.php`
- `parts/background_layers.php`
- `assets/js/theme_viewer.js` dan `assets/js/theme_drawer.js`
- snapshot database aktif dari profile `SLiMS` pada `config/database.php`

Hasil yang terverifikasi:

- Default TInfo yang memiliki nilai aktif di database sudah disamakan: background `image-bg-zen-bamboo`, animasi `floating-embers`, dan bahasa terlihat `en_us, id_id, ja_jp`.
- Background yang tersedia saat ini tidak lagi mendaftarkan Glass Surface, Glassmorphism Orbs & Bokeh, Topographic Contour Waves, Ambient Library Archives, atau Zen Reading Nook & Lamp.
- Empat background referensi baru tetap tersedia: Aurora Wave Ribbons, Memphis Retro Pattern, Isometric Cubes, dan Terrazzo Speckle.
- Tidak ada pendaftaran service worker aktif di tema. `assets/js/service-worker-cleanup.js` hanya membersihkan worker/cache Rasamala dari rilis lama secara best-effort.
- Release gate aktif untuk `panel_background`, `cursor_icon`, dan `cursor_particles`; field efektifnya dipaksa ke `solid`, `default`, dan `none`.

Catatan batasan: checklist browser di bawah ini belum dijalankan otomatis pada dokumen ini. Lint dan pemeriksaan source/database bukan pengganti uji rendering browser, console, network, atau perangkat nyata.

## Smoke test

### 1. Preflight source

Dari root instalasi SLiMS, jalankan PHP lint pada file PHP utama tema:

```powershell
$php = 'D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe'
& $php -l template/rasamala/helpers/tinfo_defaults.php
& $php -l template/rasamala/helpers/tinfo_options.php
& $php -l template/rasamala/helpers/theme_feature_flags.php
& $php -l template/rasamala/helpers/backgrounds/theme_background_styles.php
& $php -l template/rasamala/parts/background_layers.php
```

Pastikan semua hasilnya `No syntax errors detected`.

### 2. TInfo dan Theme Viewer

1. Aktifkan Rasamala melalui **System > Theme**.
2. Buka TInfo, simpan satu perubahan, reload, lalu pastikan nilai tetap tersimpan.
3. Pastikan default instalasi baru mengikuti snapshot aktif: theme color `midnightnavygold`, Theme Viewer aktif, hero fullscreen dengan Topics, background `image-bg-zen-bamboo`, animasi `floating-embers`, speed `fast`, tab homepage aktif, dan search size `small`.
4. Pada Theme Viewer, ubah palette, hero, background, animasi, section visibility, ticker, map/social, font, mobile navigation, dan back-to-top. Preview harus berubah tanpa reload.
5. Pastikan kontrol yang berada di release gate tidak muncul dan tidak dapat diaktifkan dari localStorage lama.

### 3. Route dan fitur utama

Uji route berikut:

- `index.php` — navbar tunggal, search, hero, section aktif, tabs/standard layout.
- `index.php?search=search&keywords=...` — autocomplete, filter, sort, Simple/List/Grid, pagination.
- `index.php?p=show_detail&id=...` — cover, availability, QR/fallback, citation, bookmark, basket, share, lightbox.
- `index.php?p=news` dan `index.php?p=librarian` — konten dan layout responsif.
- `index.php?p=visitor` — Kiosk dan Split, institution options, submit, voice bila aktif.
- `index.php?p=member` — login/member area dan mobile navigation.
- `index.php?p=login` — login staf melalui alur core SLiMS.
- WhatsApp/Libinfo, Map/Social, footer, language switcher, prayer reminder bila diaktifkan.

### 4. Background dan mode warna

Uji `None / Standard`, `Soft Gradient`, `Aurora Glow`, Ocean Waves, seluruh background referensi baru, background gambar, dan `Custom` bila tersedia. Pastikan:

- hanya layer terpilih yang terlihat;
- background tetap terbaca pada light dan dark mode;
- cube reference terlihat padat dan berulang, bukan hanya empat objek besar;
- background yang sudah dihapus tidak muncul di pilihan TInfo maupun Theme Viewer;
- tidak ada error SVG/CSS di console.

### 5. Cleanup rilis lama dan regresi browser

Di DevTools:

1. Pastikan tidak ada worker dengan script `rasamala-sw.js` atau `template/rasamala/assets/js/sw.js`.
2. Pastikan cache bernama `rasamala-static-*` dan `rasamala-opac-*` dihapus setelah halaman selesai dimuat.
3. Pastikan tidak ada 404 asset, error parse JSON, error Vue/Axios, atau exception JavaScript setelah hard refresh.
4. Ulangi pemeriksaan pada viewport mobile dan desktop; periksa overflow horizontal, modal, focus keyboard, label form, dan target sentuh.

## Release gate

Gate saat ini didefinisikan di `helpers/theme_feature_flags.php`:

| Fitur | Flag | Kontrol disembunyikan | Nilai aman efektif |
| --- | --- | --- | --- |
| Panel Background (Transparent/Solid) | `panel_background = false` | `classic_search_panel_style` | `solid` |
| Cursor Icon | `cursor_icon = false` | `classic_cursor_custom_icon` | `default` |
| Cursor Particles | `cursor_particles = false` | `classic_cursor_particles` | `none` |

Gate tidak menghapus renderer atau nilai database. Filter TInfo, Theme Viewer, preset resolver, dan runtime fallback tetap mencegah konfigurasi lama atau draft localStorage mengaktifkan fitur tersebut.

### Syarat membuka gate

Sebelum mengubah flag menjadi `true`, pastikan:

1. Smoke test di atas lulus pada desktop dan mobile.
2. Preview Theme Viewer dan form TInfo menampilkan nilai yang sama.
3. Nilai tersimpan tetap aman setelah reload, logout/login, dan penghapusan localStorage.
4. Tidak ada regressi console/network dan tidak ada perubahan pada route pencarian, member, visitor, atau login.
5. Deploy PHP dan JavaScript secara bersamaan, lalu bersihkan cache asset/browser.

Setelah rilis, dokumentasikan hasil uji, browser/perangkat yang dipakai, dan rollback yang tersedia.
