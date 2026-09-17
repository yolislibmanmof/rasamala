<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: tinfo_defaults.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}
// Production snapshot: transient Theme Viewer test rows are intentionally not
// promoted to defaults.
$rasamala_default_topic_items = "Literature | index.php?callnumber=8&search=search | fas fa-book ; Social Sciences | index.php?callnumber=3&search=search | fas fa-users ; Applied Sciences | index.php?callnumber=6&search=search | fas fa-flask ; Art & Recreation | index.php?callnumber=7&search=search | fas fa-paint-brush ; Language | index.php?callnumber=4&search=search | fas fa-language ; see more.. | #exampleModal | fas fa-th-large";
$rasamala_default_announcement_text = <<<HTML
<strong>Info layanan:</strong> Perpustakaan buka Senin-Jumat, pukul 08.00-16.00 WIB.
<a href="index.php?p=libinfo">Lihat informasi lengkap</a>.
HTML;
$rasamala_default_custom_css = <<<CSS
/* Custom CSS Rasamala
   Edit contoh di bawah ini sesuai kebutuhan. */

/* Contoh: ubah ukuran nama perpustakaan di navbar */
/* .navbar-lib-name {
  font-size: 14px !important;
} */

/* Contoh: beri jarak tambahan pada judul hero */
/* .hero-search-heading h1 {
  margin-bottom: 16px !important;
} */

/* Contoh: custom warna tombol utama */
/* .btn-primary {
  background-color: var(--theme-accent-color) !important;
  border-color: var(--theme-accent-color) !important;
} */
CSS;
$rasamala_default_visitor_split_steps = <<<HTML
<div class="inst-step">
  <div class="inst-icon-box"><i class="fas fa-id-card"></i></div>
  <div class="inst-content">
    <h3>1. Isi Identitas</h3>
    <p>Scan kartu anggota atau ketik identitas pengunjung pada kolom yang tersedia.</p>
  </div>
</div>
<div class="inst-step inst-step-featured">
  <div class="inst-icon-box"><i class="fas fa-sync-alt"></i></div>
  <div class="inst-content">
    <h3>2. Proses Kunjungan</h3>
    <p>Sistem akan memeriksa data dan menampilkan status kunjungan secara otomatis.</p>
  </div>
</div>
<div class="inst-step">
  <div class="inst-icon-box"><i class="fas fa-check"></i></div>
  <div class="inst-content">
    <h3>3. Selesai</h3>
    <p>Setelah berhasil, pengunjung dapat melanjutkan aktivitas sesuai layanan yang tersedia.</p>
  </div>
</div>
HTML;
$rasamala_default_visitor_institution_options = 'feb(Fakultas Ekonomi dan Bisnis UI);ff(Fakultas Farmasi UI);fh(Fakultas Hukum UI);fia(Fakultas Ilmu Administrasi UI);fib(Fakultas Ilmu Budaya UI);fik(Fakultas Ilmu Keperawatan UI);fasilkom(Fakultas Ilmu Komputer UI);fisip(Fakultas Ilmu Sosial dan Ilmu Politik UI);fk(Fakultas Kedokteran UI);fkg(Fakultas Kedokteran Gigi UI);fkm(Fakultas Kesehatan Masyarakat UI);fmipa(Fakultas Matematika dan Ilmu Pengetahuan Alam UI);fpsi(Fakultas Psikologi UI);ft(Fakultas Teknik UI);vokasi(Program Vokasi UI);other';

$sysconf['template']['base'] = 'php';
$sysconf['template']['responsive'] = true;

$sysconf['template']['classic_library_name_position'] = 'hero';
$sysconf['template']['classic_library_subname'] = 0;
$sysconf['template']['classic_popular_collection'] = 1;
$sysconf['template']['classic_popular_collection_heading_display'] = 'all';
$sysconf['template']['classic_popular_collection_title_show'] = 1;
$sysconf['template']['classic_popular_collection_subtitle_show'] = 1;
$sysconf['template']['classic_popular_collection_item'] = 6;
$sysconf['template']['classic_new_collection'] = 1;
$sysconf['template']['classic_new_collection_heading_display'] = 'all';
$sysconf['template']['classic_new_collection_title_show'] = 1;
$sysconf['template']['classic_new_collection_subtitle_show'] = 1;
$sysconf['template']['classic_new_collection_item'] = 6;
$sysconf['template']['classic_top_reader'] = 1;
$sysconf['template']['classic_top_reader_heading_display'] = 'both';
$sysconf['template']['classic_top_reader_title_show'] = 1;
$sysconf['template']['classic_top_reader_subtitle_show'] = 1;
$sysconf['template']['classic_top_reader_item'] = 5;
$sysconf['template']['classic_homepage_section_order'] = 'topic;news;popular;new-collection;top-reader;map';
$sysconf['template']['classic_map'] = 'all';
$sysconf['template']['classic_map_link'] = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.288723306273!2d106.80038831428296!3d-6.225610995493402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14efd9abf05%3A0x1659580cc6981749!2sPerpustakaan+Kemendikbud!5e0!3m2!1sid!2sid!4v1516601731218';
$sysconf['template']['classic_map_height'] = '420';
$sysconf['template']['classic_map_desc'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque et nunc mi. Donec vehicula turpis a quam venenatis posuere. Aliquam nibh lectus, gravida et leo sit amet, dignissim dapibus mauris.<br>Telp. (021) 9172638<br>Fax. (021) 9172638<br>';
$sysconf['template']['classic_fb_link'] = 'https://www.facebook.com/groups/senayan.slims';
$sysconf['template']['classic_twitter_link'] = 'https://twitter.com/slims_official';
$sysconf['template']['classic_youtube_link'] = 'https://youtube.com';
$sysconf['template']['classic_instagram_link'] = 'https://instagram.com/slims.sdc';
$sysconf['template']['classic_tiktok_link'] = '';
$sysconf['template']['classic_whatsapp_link'] = '';
$sysconf['template']['classic_telegram_link'] = '';
$sysconf['template']['classic_linkedin_link'] = '';
$sysconf['template']['visitor_log_voice'] = 1;
$sysconf['template']['visitor_quote'] = 1;
$sysconf['template']['visitor_title'] = '';
$sysconf['template']['visitor_subtitle'] = 'Visitor Check-In Portal';
$sysconf['template']['visitor_institution_select_label'] = 'Pilih Fakultas / Institusi';
$sysconf['template']['visitor_institution_options'] = $rasamala_default_visitor_institution_options;
$sysconf['template']['visitor_theme_toggle'] = 0;
$sysconf['template']['visitor_layout_style'] = 'kiosk';
$sysconf['template']['visitor_split_title'] = 'Petunjuk Penggunaan';
$sysconf['template']['visitor_split_steps'] = $rasamala_default_visitor_split_steps;
$sysconf['template']['classic_footer_about_us'] = <<<HTML
<p>As a complete Library Management System, SLiMS (Senayan Library Management System) has many features that will help libraries and librarians to do their job easily 
and quickly. Follow <a target="_blank" rel="noopener noreferrer" href="https://slims.web.id/web/pages/about/">this link</a> to show some features provided by SLiMS.</p>
HTML;
$sysconf['template']['classic_footer_show'] = 1;
$sysconf['template']['classic_footer_search_show'] = 0;
$sysconf['template']['classic_footer_copyright'] = 'Senayan Developer Community';
$sysconf['template']['classic_prayer_times_show'] = 'hide';
$sysconf['template']['classic_prayer_times_city'] = 'Jakarta';
$sysconf['template']['classic_prayer_times_country'] = 'Indonesia';
$sysconf['template']['classic_theme_preset'] = 'custom';
$sysconf['template']['classic_topic_show'] = 1;
$sysconf['template']['classic_topic_heading_display'] = 'title';
$sysconf['template']['classic_topic_title_show'] = 1;
$sysconf['template']['classic_topic_subtitle_show'] = 0;
$sysconf['template']['classic_topic_items'] = $rasamala_default_topic_items;
$sysconf['template']['classic_search_size'] = 'small';
$sysconf['template']['classic_homepage_only_hero'] = 0;
$sysconf['template']['classic_hero_fullscreen_mode'] = 'yes';
$sysconf['template']['classic_hero_topics_show'] = 'topics';
$sysconf['template']['classic_hero_text'] = 'Search Library Collection';
$sysconf['template']['classic_hero_text_size'] = 'small';
$sysconf['template']['classic_search_placeholder'] = 'silakan cari disini';
$sysconf['template']['classic_hero_background_style'] = 'image-bg-zen-bamboo';
$sysconf['template']['classic_background_image_size'] = 'crop';
$sysconf['template']['classic_background_image_position'] = 'center';
$sysconf['template']['classic_background_image_filter'] = 'readable';
$sysconf['template']['classic_background_image_blur'] = '8';
$sysconf['template']['classic_background_image_overlay'] = 'auto';
$sysconf['template']['classic_background_style_custom'] = 'linear-gradient(145deg, color-mix(in srgb, var(--theme-primary) 32%, var(--theme-background)) 0%, color-mix(in srgb, var(--theme-accent) 22%, var(--theme-surface)) 100%) | linear-gradient(145deg, color-mix(in srgb, var(--theme-dark-primary) 36%, var(--theme-dark-background)) 0%, color-mix(in srgb, var(--theme-dark-accent) 24%, var(--theme-dark-surface)) 100%)';
$sysconf['template']['classic_hero_background_animation'] = 'floating-embers';
$sysconf['template']['classic_background_animation_speed'] = 'fast';
$sysconf['template']['classic_cursor_particles'] = 'none';
$sysconf['template']['classic_cursor_custom_icon'] = 'default';
$sysconf['template']['classic_announcement_show'] = 0;
$sysconf['template']['classic_announcement_text'] = $rasamala_default_announcement_text;
$sysconf['template']['classic_announcement_style'] = 'theme';
$sysconf['template']['classic_home_display_show'] = 'below';
$sysconf['template']['classic_home_sections_tabs'] = 1;
$sysconf['template']['classic_home_display_style'] = 'badges';
$sysconf['template']['classic_home_display_source'] = 'content';
$sysconf['template']['classic_home_display_content_filter'] = 'all';
$sysconf['template']['classic_home_display_content_detail'] = 'title';
$sysconf['template']['classic_home_display_biblio_filter'] = 'all';
$sysconf['template']['classic_home_display_custom_text'] = 'Selamat datang di perpustakaan kami!';
$sysconf['template']['classic_ticker_show'] = 'bottom';
$sysconf['template']['classic_ticker_source'] = 'content';
$sysconf['template']['classic_ticker_content_filter'] = 'all';
$sysconf['template']['classic_ticker_content_detail'] = 'title';
$sysconf['template']['classic_ticker_biblio_filter'] = 'all';
$sysconf['template']['classic_ticker_speed'] = 'slow';
$sysconf['template']['classic_ticker_custom_text'] = 'Selamat datang di perpustakaan kami!';
$sysconf['template']['classic_latest_content_show'] = 'below';
$sysconf['template']['classic_latest_content_item'] = 5;
$sysconf['template']['classic_latest_content_title_chars'] = 0;
$sysconf['template']['classic_ticker_item_limit'] = 5;
$sysconf['template']['classic_ticker_char_limit'] = 0;
$sysconf['template']['classic_home_item_limit'] = 1;
$sysconf['template']['classic_home_char_limit'] = 0;
$sysconf['template']['classic_home_content_cards_show'] = 1;
$sysconf['template']['classic_home_content_cards_source'] = 'news';
$sysconf['template']['classic_home_content_path_1'] = '';
$sysconf['template']['classic_home_content_path_2'] = '';
$sysconf['template']['classic_home_content_path_3'] = '';
$sysconf['template']['classic_parallel_title_separator'] = '=';
$sysconf['template']['classic_title_chars'] = 100;
$sysconf['template']['classic_show_author_role'] = 0;
$sysconf['template']['classic_detail_label_type'] = 'gmd';
$sysconf['template']['classic_breadcrumbs_show'] = 1;
$sysconf['template']['classic_back_to_top'] = 1;
$sysconf['template']['classic_floating_info'] = 'libinfo';
$sysconf['template']['classic_whatsapp_number'] = '628123456789';
$sysconf['template']['classic_whatsapp_title'] = 'Layanan Chat WhatsApp';
$sysconf['template']['classic_service_hours'] = 'Senin - Jumat (08:00 - 16:00)';
$sysconf['template']['classic_whatsapp_desc'] = 'Pustakawan; Halo, ada yg bisa kami bantu ?';
$sysconf['template']['classic_whatsapp_categories'] = 'Nama; Nomor Anggota (opsional); Pertanyaan';
$sysconf['template']['classic_member_area'] = 1;
$sysconf['template']['classic_theme_color'] = 'midnightnavygold';
$sysconf['template']['classic_palette_custom'] = '#44403c; #292524; #ca8a04; #fafaf9; #f5f5f4; #111827; #374151 | #57534e; #44403c; #eab308; #121110; #1c1917; #f8fafc; #cbd5e1';
$sysconf['template']['classic_palette_primary'] = '#1E293B';
$sysconf['template']['classic_palette_secondary'] = '#475569';
$sysconf['template']['classic_palette_accent'] = '#EAB308';
$sysconf['template']['classic_palette_background'] = '#F8FAFC';
$sysconf['template']['classic_palette_surface'] = '#F1F5F9';
$sysconf['template']['classic_palette_text'] = '#111827';
$sysconf['template']['classic_palette_muted'] = '#374151';
$sysconf['template']['classic_color_toggle'] = 'dark_hide';
$sysconf['template']['classic_palette_switcher_show'] = 1;
$sysconf['template']['classic_font_family'] = 'system';
$sysconf['template']['classic_search_result_layout'] = 'simple';
$sysconf['template']['classic_search_panel_style'] = 'solid';
$sysconf['template']['classic_news_list_layout'] = 'title_excerpt_thumbnail';
$sysconf['template']['classic_custom_css'] = $rasamala_default_custom_css;
$sysconf['template']['classic_mobile_bottom_nav_show'] = 1;
$sysconf['template']['classic_member_default_page'] = 'my_card';
$sysconf['template']['classic_card_show_fields'] = 'name,member_id,institution,member_type';
$sysconf['template']['classic_card_code_type'] = 'qr';
$sysconf['template']['classic_auto_cover_generator'] = 'empty_missing';
$sysconf['template']['classic_language_visible_codes'] = 'en_us, id_id, ja_jp';
$sysconf['template']['classic_librarian_display_mode'] = 'all';
$sysconf['template']['classic_librarian_custom_usernames'] = '';
$sysconf['template']['classic_navbar_menu'] = "Home | index.php | fas fa-home ; Information | index.php?p=libinfo | fas fa-info-circle ; News | index.php?p=news | fas fa-newspaper ; Help | index.php?p=help | fas fa-question-circle ; Librarian | index.php?p=librarian | fas fa-users ; Staff Area | index.php?p=login | fas fa-university";
$coll_type_data = [['all', __('All Collection Types')]];
if (isset($dbs) && $dbs) {
    $coll_q = $dbs->query("SELECT coll_type_id, coll_type_name FROM mst_coll_type ORDER BY coll_type_name ASC");
    if ($coll_q) {
        while ($coll_r = $coll_q->fetch_assoc()) {
            $coll_type_data[] = [$coll_r['coll_type_name'], $coll_r['coll_type_name']];
        }
    }
}
$rasamala_section_heading_display_data = [
    ['all', __('Title + Subtitle + Subject')],
    ['both', __('Title + Subtitle')],
    ['title_subject', __('Title + Subject')],
    ['title', __('Title Only')],
    ['hide', __('Hide All')]
];
