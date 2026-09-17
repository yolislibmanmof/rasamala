<?php
/**
 * Member Area Component - Digital Member Card Generator
 *
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: digital_card.php
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('rasamalaRenderDigitalMemberCard')) {
    function rasamalaRenderDigitalMemberCard(&$main_content, $sysconf)
    {
        if (!isset($_GET['sec']) || $_GET['sec'] !== 'my_card') {
            return;
        }

        $member_image_session = preg_replace('/[^a-zA-Z0-9._-]/', '', basename((string)($_SESSION['m_image'] ?? '')));
        $has_custom_image = $member_image_session !== '' && file_exists(IMGBS . 'persons/' . $member_image_session);
        $member_image = $has_custom_image ? $member_image_session : 'person.png';
        $member_image_url = SWB . 'images/persons/' . $member_image;

        $card_fields = explode(',', strtolower(str_replace(' ', '', $sysconf['template']['classic_card_show_fields'] ?? 'name,member_id,institution,member_type')));
        $show_name = in_array('name', $card_fields);
        $show_member_id = in_array('member_id', $card_fields);
        $show_institution = in_array('institution', $card_fields);
        $show_member_type = in_array('member_type', $card_fields);
        $expiry_date_display = trim((string)($_SESSION['m_expire_date'] ?? ''));
        $expiry_status = rasamalaMemberExpiryStatus($expiry_date_display);
        $expiry_status_class = preg_replace('/[^a-z0-9_-]/i', '', $expiry_status['class']);
        $expiry_status_label = (string)$expiry_status['label'];
        $expiry_status_note = (string)$expiry_status['note'];
        $expiry_status_aria = sprintf(
            '%s: %s. %s',
            __('Membership status'),
            $expiry_status_label,
            $expiry_status_note
        );

        $member_code_value = trim((string)($_SESSION['mid'] ?? ''));
        $card_code_type = strtolower(trim((string)($sysconf['template']['classic_card_code_type'] ?? 'qr')));
        if (!in_array($card_code_type, ['qr', 'barcode'], true)) {
            $card_code_type = 'qr';
        }

        $card_code_class = 'empty';
        $card_code_html = '<span class="member-card-code-empty">' . __('No member ID data') . '</span>';
        if ($member_code_value !== '' && $card_code_type === 'barcode') {
            $barcode_encoding = $sysconf['barcode_encoding'] ?? 'code128';
            if (is_array($barcode_encoding)) {
                $barcode_encoding = reset($barcode_encoding);
            }
            $barcode_encoding = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$barcode_encoding);
            if ($barcode_encoding === '') {
                $barcode_encoding = 'code128';
            }

            $barcode_url = SWB . 'lib/phpbarcode/barcode.php?code=' . rawurlencode($member_code_value)
                . '&encoding=' . rawurlencode($barcode_encoding)
                . '&scale=2&mode=png&act=show';
            $card_code_class = 'barcode';
            $card_code_html = '<img class="member-card-barcode-img" src="' . themeEscape($barcode_url) . '" alt="' . themeEscape(sprintf(__('Barcode for %s'), $member_code_value)) . '">';
        } elseif ($member_code_value !== '') {
            $qrcode_svg = '';
            try {
                $renderer = new BaconQrCode\Renderer\ImageRenderer(
                    new BaconQrCode\Renderer\RendererStyle\RendererStyle(280),
                    new BaconQrCode\Renderer\Image\SvgImageBackEnd()
                );
                $writer = new BaconQrCode\Writer($renderer);
                $qrcode_svg = $writer->writeString($member_code_value);
                $qrcode_svg = preg_replace('/<\?xml[^>]*\?>/', '', $qrcode_svg);
            } catch (Exception $e) {
                $qrcode_svg = '<!-- Error generating QR Code: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ' -->';
            }
            $card_code_class = 'qr';
            $card_code_html = $qrcode_svg;
        }
        $is_member_active = ($expiry_status_class === 'active');
        $avatar_status_class = $is_member_active ? '' : ' is-inactive is-expired';

        $avatar_html = '';
        if ($has_custom_image) {
            $avatar_html = '<img src="' . themeEscape($member_image_url) . '" alt="Member Photo" class="rasamala-digital-card-avatar' . $avatar_status_class . '">';
        } else {
            $name = $_SESSION['m_name'] ?? '';
            $words = explode(' ', trim($name));
            $initials = '';
            if (count($words) >= 2) {
                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            } elseif (count($words) == 1 && !empty($words[0])) {
                $initials = strtoupper(substr($words[0], 0, 2));
            } else {
                $initials = 'M';
            }
            $avatar_html = '<div class="initials-avatar rasamala-digital-card-initials' . $avatar_status_class . '">' . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $card_html = '<div id="digital-card-page" class="py-0 text-center">';
        $card_html .= '
        <div id="card-container" class="card rasamala-digital-card">
            <div class="header rasamala-digital-card-header">
                <div class="rasamala-digital-card-orb rasamala-digital-card-orb-top"></div>
                <div class="rasamala-digital-card-orb rasamala-digital-card-orb-bottom"></div>
                <div class="rasamala-digital-card-library-name">' . htmlspecialchars($sysconf['library_name'] ?? 'SLiMS Library', ENT_QUOTES, 'UTF-8') . '</div>
                <div class="rasamala-digital-card-library-subname">' . htmlspecialchars($sysconf['library_subname'] ?? '', ENT_QUOTES, 'UTF-8') . '</div>
                ' . $avatar_html . '
            </div>

            <div class="body rasamala-digital-card-body">';

        if ($show_name) {
            $card_html .= '
                <h3 class="rasamala-digital-card-name">' . htmlspecialchars($_SESSION['m_name'] ?? '', ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        if ($show_member_id) {
            $card_html .= '
                <p class="rasamala-digital-card-id">' . htmlspecialchars($_SESSION['mid'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if ($show_member_type) {
            $card_html .= '
                <p class="rasamala-digital-card-type">' . htmlspecialchars($_SESSION['m_member_type'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if ($show_institution) {
            $card_html .= '
                <p class="rasamala-digital-card-institution">' . htmlspecialchars($_SESSION['m_institution'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if (!$is_member_active) {
            $card_html .= '
                <div class="member-card-expiry-status member-card-expiry-status--' . htmlspecialchars($expiry_status_class, ENT_QUOTES, 'UTF-8') . '" role="status" aria-label="' . htmlspecialchars($expiry_status_aria, ENT_QUOTES, 'UTF-8') . '">
                    <span class="member-card-expiry-status-label">' . htmlspecialchars($expiry_status_label, ENT_QUOTES, 'UTF-8') . '</span>
                    <span class="member-card-expiry-status-note">' . htmlspecialchars($expiry_status_note, ENT_QUOTES, 'UTF-8') . '</span>
                </div>';
        }

        $card_html .= '
                <div class="member-card-code-wrap"><div id="member-card-code" class="rasamala-card-code rasamala-digital-card-code rasamala-card-code--' . htmlspecialchars($card_code_class, ENT_QUOTES, 'UTF-8') . '">' . $card_code_html . '</div></div>
            </div>

            <div class="card-footer rasamala-digital-card-footer">
                ' . __('Expiry Date') . ': ' . htmlspecialchars($expiry_date_display !== '' ? $expiry_date_display : '-', ENT_QUOTES, 'UTF-8') . '
            </div>
        </div>

        <div class="rasamala-digital-card-actions">
            <button id="fullscreen-btn" class="btn btn-success mx-1"><i class="fas fa-expand mr-2" aria-hidden="true"></i>' . __('Fullscreen') . '</button>
            <button id="minimize-btn" class="btn btn-danger mx-1 rasamala-digital-card-minimize"><i class="fas fa-compress mr-2" aria-hidden="true"></i>' . __('Minimize') . '</button>
            <button id="print-btn" class="btn btn-primary mx-1"><i class="fas fa-print mr-2" aria-hidden="true"></i>' . __('Print') . '</button>
        </div>';

        $card_html .= '
        </div>';

        $main_content = rasamalaReplaceMemberCardContent($main_content, $card_html);
    }
}
