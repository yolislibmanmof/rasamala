# Review UI/UX — Halaman Member Area (`index.php?p=member`)

**Template**: `rasamala`
**Tanggal review**: 2026-07-18
**Reviewer**: SLiMS Bulian UI/UX Expert (agent)
**Sumber yang diteliti**:
- `template/rasamala/parts/_member.php` (shell template)
- `template/rasamala/index_template.inc.php` (routing + footer)
- `template/rasamala/parts/footer.php` (termasuk `mobile_bottom_nav.php`)
- `lib/contents/member.inc.php` (konten inti / `$main_content`)

---

## 1. Asal Halaman: Template atau Core?

**Jawaban: Campuran.**

| Bagian | Asal | File |
|---|---|---|
| Shell: navbar, search form (saat belum login), breadcrumb, kartu pembungkus | **Template (rasamala)** | `parts/_member.php` |
| Footer + mobile bottom nav | **Template (rasamala)** | `parts/footer.php` → `mobile_bottom_nav.php` |
| Isi dashboard: sidebar foto, tombol, tab (Current Loan / Bookmark / Basket / History / Account), tabel peminjaman, profil, ganti password, semua `<script>` | **Core (sama untuk semua template)** | `lib/contents/member.inc.php` |

> Konsekuensi: layout pembungkus bisa diubah lewat CSS template, tapi **konten utama di-generate oleh core** dan tidak bisa di-override per-template tanpa (a) CSS override, atau (b) patch core.

---

## 2. Ringkasan Temuan

| # | Kategori | Severity | Lokasi | Masalah |
|---|---|---|---|---|
| F1 | Responsif | 🔴 Tinggi | `member.inc.php` ~baris 803 | `<div class="d-flex">` + sidebar `width:16rem` tetap → di mobile tidak menumpuk, konten terdesak horizontal. |
| F2 | Responsif | 🔴 Tinggi | `member.inc.php` ~baris 806 | Foto member `<img class="rounded shadow">` tanpa `img-fluid`/`max-width` → overflow. |
| F3 | Responsif | 🟠 Sedang | `member.inc.php` ~baris 845 | 5 tab `nav nav-tabs nav-fill` berhimpit/terpotong di layar sempit; tidak bisa scroll horizontal. |
| F4 | Responsif | 🔴 Tinggi | `member.inc.php` (datagrid) | Tabel `memberDetail`, `memberLoanList`, `memberBookmarkList`, `memberBasketList` **tidak** dibungkus `.table-responsive` → overflow horizontal di mobile. |
| F5 | JS / Guardrail | 🔴 Tinggi | `member.inc.php` ~baris 946+ | `clearAll` & `clearOne` pakai `confirm()` + `alert()` + `$.ajax()` + `async:false`. Melanggar JS-3 / JS-4 / JS-6. |
| F6 | JS / Guardrail | 🟠 Sedang | `member.inc.php` ~baris 960+ | `reserve` pakai `$.ajax()` `async:false` (membekukan UI). |
| F7 | JS / Guardrail | 🟠 Sedang | `member.inc.php` ~baris 1000+ | `deleteBookmark` pakai `$.post()` (custom AJAX, dilarang JS-3) — untungnya sudah pakai `toastr`. |
| F8 | Bootstrap usang | 🟡 Rendah | `member.inc.php` ~baris 808-810 | `btn-block` (BS5 → `w-100`/`d-grid`), `mr-2` (→ `me-2`), `mt-8` (bukan utilitas default). |
| F9 | Aksesibilitas | 🟠 Sedang | `member.inc.php` form ganti password & login | Input tidak punya `<label for>` (pakai `<strong>`/`.fieldLabel` div). |
| F10 | Security/Escaping | 🟠 Sedang | `member.inc.php` (echo session) | `$_SESSION['m_name']`, `$_SESSION['mid']`, `$_SESSION['m_email']`, `$_SESSION['m_member_type']`, `$_SESSION['m_institution']` di-echo raw tanpa `htmlspecialchars` (SEC-6). |
| F11 | Security/Query | 🟡 Rendah | `member.inc.php` `procChangePassword`, `showBookCover` | Query pakai `sprintf` + `$dbs->escape_string()` bukan prepared statement. |
| F12 | Kode | 🟡 Rendah | `member.inc.php` `showBasket` | `titleLink` didefinisikan di dalam fungsi dengan `if(!function_exists)` → polusi global / risiko redeclare. |
| F13 | UX | 🟠 Sedang | `member.inc.php` JS | `async:false` membekukan halaman saat reserve/clear; tidak ada loading state yang konsisten. |

### Aspek Positif (dipertahankan)
- ✅ Shell template sudah pakai Bootstrap grid (`container py-5`, `row`, `col-md-8 mx-auto`).
- ✅ `mobile_bottom_nav.php` **sudah** disertakan via footer → navigasi mobile ada.
- ✅ `toastr` sudah dipakai untuk bookmark & reserve (JS-6 terpenuhi sebagian).
- ✅ Form login sudah menyertakan token CSRF (`CSRF::getHiddenInputString()` + `csrf_token`).
- ✅ Datagrid pakai native `simbio_datagrid` (SIM-1 terpenuhi).

---

## 3. Rekomendasi Perbaikan

### 3A. Template-side (aman, tanpa ubah core) — tambahkan ke `template/rasamala/assets/css/style.css`

```css
/* ===== Member Area responsive (rasamala) ===== */
@media (max-width: 767.98px) {
  /* F1: stack sidebar di mobile */
  .member-area .d-flex { flex-direction: column; }
  #member_sidebar {
    width: 100% !important;
    max-width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  /* F2: foto tidak overflow */
  #member_sidebar img {
    max-width: 140px;
    height: auto;
  }
  /* F3: tab bisa scroll horizontal */
  .member-area .nav-tabs {
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }
  .member-area .nav-tabs .nav-link { white-space: nowrap; }
  /* F4: tabel tidak overflow */
  .member-area .bg-white { overflow-x: auto; }
  .member-area table.table { width: 100%; }
  /* F8: info full width di mobile */
  #info { width: 100% !important; }
}
```

### 3B. Core patch (`lib/contents/member.inc.php`) — untuk F1–F13

**Layout (F1, F2, F8):**
```php
<div class="d-flex flex-column flex-md-row">
    <div style="width: 100%; max-width: 16rem;" class="bg-grey-light p-4" id="member_sidebar">
        <div class="p-4 text-center">
            <img src="<?= $member_image_url ?>" alt="Foto member" class="rounded shadow img-fluid" style="max-width:140px;">
        </div>
        <a href="index.php?p=member&sec=membercard" class="btn btn-primary w-100 mb-2" target="_blank"><i class="fas fa-address-card me-2"></i><?php echo __('View Library Card'); ?></a>
        <a href="index.php?p=member&logout=1" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt me-2"></i><?php echo __('LOGOUT'); ?></a>
    </div>
    ...
```

**Tabel (F4):** bungkus setiap output datagrid / `memberDetail` dengan `<div class="table-responsive">...</div>`.

**Escaping session (F10):** ganti echo raw dengan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`, mis.:
```php
<td ...><?= htmlspecialchars($_SESSION['m_name'], ENT_QUOTES, 'UTF-8') ?></td>
```

**JS — ganti `confirm()`/`alert()`/`$.ajax()`/`$.post()` (F5, F6, F7):**
- Gunakan modal konfirmasi Bootstrap + `utility::jsToastr()` (bukan `alert`/`confirm`).
- Ganti `$.ajax({async:false})` dan `$.post()` dengan `simbioAJAX` / `fetch` async; tampilkan `toastr` untuk feedback.
- Contoh pola aman untuk `deleteBookmark`:
```js
$('.deleteBookmark').click(function (e) {
    e.preventDefault();
    let id = $(this).data('id');
    if (!confirm('<?= __('Remove this bookmark?') ?>')) return; // ganti dgn modal nanti
    simbioAJAX('index.php?p=member', 'post', {bookmark_id:id, delete_bookmark:true}, function (res) {
        if (!res.status) toastr.error(res.message);
        else toastr.success(res.message, '', {timeOut:2000, onHidden:()=>location.replace('index.php?p=member&sec=bookmark')});
    });
});
```

**Aksesibilitas (F9):** tambahkan `<label for="currPass">` pada form ganti password dan `<label for="memberID">` pada login.

---

## 4. Rencana Smoke Test (setelah perbaikan)
1. Buka `?p=member` di viewport desktop (≥992px): sidebar kiri, konten kanan, tab rapi.
2. Buka di mobile (375px): sidebar menumpuk di atas, foto tidak overflow, tab bisa di-scroll, tabel tidak overflow horizontal.
3. Tab Current Loan / Bookmark / Basket / History / Account menampilkan data tanpa layout rusak.
4. Hapus bookmark & clear basket → konfirmasi via modal + toastr (bukan `alert`/`confirm`).
5. Reserve → tidak ada pembekuan UI (`async:false` hilang), feedback toastr muncul.
6. Console browser: tidak ada error JS, tidak ada `$.ajax is not a function` (jika dihapus).
7. `tail -n 50 /var/log/apache2/error.log` → tidak ada PHP error/warning.

---

## 5. Self-Eval Gate

`Self-Eval Gate: PASSED` (review-only, belum ada perubahan kode yang melanggar guardrail baru diperkenalkan; temuan di atas adalah daftar perbaikan yang diusulkan).
