# Rasamala Dependency Notes

Last modified by: Ade Ismail Siregar (adeismailbox@gmail.com)  
Last modified time: 2026-07-15T08:25:01+07:00

## Keputusan Dependency Terkini

- Vue lokal yang dimuat theme adalah Vue.js v3.5.39 melalui `assets/js/vue.min.js`.
- Bootstrap yang dimuat theme adalah Bootstrap v5.3.3 melalui `assets/css/bootstrap.min.css` dan `assets/js/bootstrap.bundle.min.js`.
- Markup utama theme sudah dimigrasikan ke pola Bootstrap 5 untuk utility spacing (`ms/me/ps/pe`), text alignment, weight utility, badge, modal trigger, dropdown display, button block, grid gutter, form spacing, `form-select`, dan struktur input group.
- Theme masih menyediakan compatibility layer terbatas untuk markup lama Bootstrap 4 yang mungkin dihasilkan core SLiMS/plugin:
  - `assets/js/bootstrap_compat.js` memetakan atribut seperti `data-toggle`, `data-target`, dan `data-dismiss` ke atribut Bootstrap 5.
  - `parts/header.php` menambahkan beberapa utility class lama seperti `mr-*`, `ml-*`, `pr-*`, `pl-*`, `float-left`, `text-right`, `btn-block`, `custom-select`, dan `close`.
- Custom JavaScript theme diarahkan ke vanilla JS ketika aman. `assets/js/app_jquery.js` sudah dipindah ke DOM API dan `fetch()` untuk handler custom seperti basket, bookmark, modal share, oembed, mobile menu, chat toggle, back-to-top, dan paging cleanup.
- jQuery tetap dimuat karena masih dibutuhkan dependency lama SLiMS/plugin seperti Colorbox, IonRangeSlider, dan beberapa integrasi hasil pencarian yang masih plugin-based.
- Dependency sample yang sebelumnya tidak dipakai production sudah dibersihkan pada Tahap 4: Tailwind sample, Vegas, sample hero, dan gambar slide lama.

## Arah Berikutnya

- Saat komponen Vue disentuh lagi, prioritaskan pengurangan scope atau migrasi kecil ke vanilla JS sebelum menambah framework baru.
- Pertahankan compatibility layer Bootstrap 4 hanya selama masih ada markup lama dari core SLiMS/plugin yang membutuhkannya.
- Hindari menambahkan Alpine.js atau dependency frontend baru kecuali ada kebutuhan UI yang jelas dan sulit dicapai dengan vanilla JS.
