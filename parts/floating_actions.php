<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: floating_actions.php

if (!function_exists('rasamalaWhatsappDefaultMessageTemplate')) {
    function rasamalaWhatsappDefaultMessageTemplate()
    {
        return "Nama:\nNomor Anggota (opsional):\nPertanyaan:";
    }
}

if (!function_exists('rasamalaWhatsappMessageTemplate')) {
    function rasamalaWhatsappMessageTemplate($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return rasamalaWhatsappDefaultMessageTemplate();
        }

        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = str_replace(["\\r\\n", "\\n\\r", "\\r", "\\n"], "\n", $raw);
        $raw = preg_replace("/\r\n|\r/", "\n", $raw);

        if (strpos($raw, '|') !== false) {
            $service_names = [];
            foreach (preg_split("/\n+/", $raw) as $line) {
                if (strpos($line, '|') === false) {
                    continue;
                }
                [$title] = explode('|', $line, 2);
                $title = trim(preg_replace('/\s+/', ' ', strip_tags($title)));
                if ($title !== '') {
                    $service_names[] = $title;
                }
            }

            $template = rasamalaWhatsappDefaultMessageTemplate();
            if (!empty($service_names)) {
                $template .= "\nKategori layanan (opsional): " . implode(', ', array_unique($service_names));
            }

            return $template;
        }

        if (preg_match('/[;,]/', $raw) === 1) {
            $fields = [];
            foreach (preg_split('/[;,]+/', $raw) as $field) {
                $field = trim(preg_replace('/\s+/', ' ', strip_tags($field)));
                $field = rtrim($field, " \t\n\r\0\x0B:.");
                if ($field !== '') {
                    $fields[] = $field . ':';
                }
            }

            if (!empty($fields)) {
                return implode("\n", $fields);
            }
        }

        return trim(strip_tags($raw));
    }
}

if (!function_exists('rasamalaWhatsappMemberContext')) {
    function rasamalaWhatsappMemberContext()
    {
        $clean = function ($value) {
            $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)));
            return $value;
        };

        $name = isset($_SESSION['m_name']) ? $clean($_SESSION['m_name']) : '';
        $id = isset($_SESSION['mid']) ? $clean($_SESSION['mid']) : '';

        return [
            'is_logged_in' => ($name !== '' || $id !== ''),
            'name' => $name,
            'id' => $id,
        ];
    }
}

if (!function_exists('rasamalaWhatsappBubbleContent')) {
    function rasamalaWhatsappBubbleContent($raw, array $member_context = [])
    {
        $fallback_author = 'Pustakawan';
        $fallback_message = 'Halo, silakan ketik pesan Anda langsung di kolom bawah. Agar kami dapat membantu lebih cepat, tuliskan nama, nomor anggota (jika ada), lalu pertanyaan Anda.';

        $raw = trim((string)$raw);
        if ($raw === '') {
            $raw = $fallback_author . '; ' . $fallback_message;
        }

        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = str_replace(["\\r\\n", "\\n\\r", "\\r", "\\n"], "\n", $raw);
        $raw = preg_replace("/\r\n|\r/", "\n", $raw);

        $line = '';
        foreach (preg_split("/\n+/", $raw) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '') {
                $line = $candidate;
                break;
            }
        }

        if ($line === '') {
            $line = $fallback_author . '; ' . $fallback_message;
        }

        $author = $fallback_author;
        $message = $line;
        if (strpos($line, ';') !== false) {
            [$author_raw, $message_raw] = explode(';', $line, 2);
            $author = trim(preg_replace('/\s+/', ' ', strip_tags($author_raw)));
            $message = trim(strip_tags($message_raw));
        }

        if ($author === '') {
            $author = $fallback_author;
        }

        // Strip duplicate author prefix if present in the message text
        $message = preg_replace('/^' . preg_quote($author, '/') . '\s*[:;]\s*/i', '', $message);

        if ($message === '') {
            $message = $fallback_message;
        }

        $member_name = trim((string)($member_context['name'] ?? ''));
        $member_id = trim((string)($member_context['id'] ?? ''));
        $member_display = $member_name !== '' ? $member_name : $member_id;
        $has_member_token = preg_match('/\{(?:member_name|nama_member|member_id|id_member)\}/i', $message) === 1;

        if (!$has_member_token && !empty($member_context['is_logged_in']) && $member_display !== '' && preg_match('/^Halo,\s*/i', $message)) {
            $message = preg_replace('/^Halo,\s*/i', 'Halo ' . $member_display . ', ', $message, 1);
        }

        $message = strtr($message, [
            '{member_name}' => $member_display,
            '{nama_member}' => $member_display,
            '{member_id}' => $member_id,
            '{id_member}' => $member_id,
        ]);
        $message = trim(preg_replace('/\s+/', ' ', strip_tags($message)));
        $message = preg_replace('/^' . preg_quote($author, '/') . '\s*[:;]\s*/i', '', $message);

        return [
            'author' => $author,
            'message' => $message !== '' ? $message : $fallback_message,
        ];
    }
}

$latest_content_ticker_items = $latest_content_ticker_items ?? [];
$theme_viewer_preview_enabled = (int)themeEffectiveTemplateValue('classic_palette_switcher_show', 0, $sysconf) === 1;
$show_back_to_top = themeEffectiveTemplateValue('classic_back_to_top', 1, $sysconf) || $theme_viewer_preview_enabled;
$floating_info_mode = themeEffectiveTemplateValue('classic_floating_info', 'libinfo', $sysconf);
// Fallback for legacy values
if ($floating_info_mode == '1') {
    $floating_info_mode = 'libinfo';
} elseif ($floating_info_mode == '0') {
    $floating_info_mode = 'hide';
}

$show_floating_libinfo = ($floating_info_mode === 'libinfo');
$show_floating_whatsapp = ($floating_info_mode === 'whatsapp');
// Render both preview targets while Theme Viewer is enabled so switching the
// floating-info mode is visible immediately without a PHP reload. The active
// button is still selected below with a hidden attribute.
if ($theme_viewer_preview_enabled) {
    $show_floating_libinfo = true;
    $show_floating_whatsapp = true;
}
$show_color_toggle = themeColorModeToggleVisible($sysconf) || $theme_viewer_preview_enabled;

$libinfo_title = __('Library Information');
$libinfo_desc = '';
if ($show_floating_libinfo && isset($dbs) && $dbs) {
    $libinfo_query = $dbs->query("SELECT content_title, content_desc FROM content WHERE content_path='libinfo' AND is_draft=0 LIMIT 1");
    if ($libinfo_query && $libinfo_query->num_rows > 0) {
        $libinfo_data = $libinfo_query->fetch_assoc();
        $libinfo_title = $libinfo_data['content_title'];
        $libinfo_desc = $libinfo_data['content_desc'];
    }
}
?>

<?php if ($show_color_toggle): ?>
    <button id="color-mode-toggle"
            class="btn-color-mode-toggle shadow-lg <?= !empty($latest_content_ticker_items) ? 'has-latest-content-ticker' : '' ?>"
            title="<?= themeEscape(__('Dark mode')) ?>"
            data-dark-title="<?= themeEscape(__('Dark mode')) ?>"
            data-light-title="<?= themeEscape(__('Light mode')) ?>"
            aria-label="<?= themeEscape(__('Toggle dark/light mode')) ?>"
            aria-pressed="false">
        <i class="fas fa-moon" aria-hidden="true"></i>
    </button>
<?php endif; ?>

<?php if ($show_floating_libinfo): ?>
    <button id="floating-info-btn" class="btn-floating-info shadow-lg <?= !empty($latest_content_ticker_items) ? 'has-latest-content-ticker' : '' ?>" data-bs-toggle="modal" data-bs-target="#libinfoModal" title="Library Info" aria-label="<?= themeEscape(__('Library Information')) ?>"<?= ($theme_viewer_preview_enabled && $floating_info_mode !== 'libinfo') ? ' hidden' : '' ?>>
        <i class="fas fa-info-circle" aria-hidden="true"></i>
    </button>

    <div class="modal fade" id="libinfoModal" tabindex="-1" role="dialog" aria-labelledby="libinfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content libinfo-modal-content">
                <div class="modal-header libinfo-modal-header">
                    <h5 class="modal-title fw-bold text-uppercase tracking-wider libinfo-modal-title" id="libinfoModalLabel">
                        <?= htmlspecialchars($libinfo_title, ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= themeEscape(__('Close')) ?>"></button>
                </div>
                <div class="modal-body libinfo-modal-body">
                    <div class="libinfo-content libinfo-modal-desc">
                        <?= themeSanitizeHtml($libinfo_desc) ?>
                    </div>
                </div>
                <div class="modal-footer libinfo-modal-footer">
                    <button type="button" class="btn btn-outline-cyan libinfo-modal-btn" data-bs-dismiss="modal">
                        <?= __('Close') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($show_floating_whatsapp): ?>
    <?php
    $wa_title = themeEffectiveTemplateValue('classic_whatsapp_title', 'Layanan Chat WhatsApp', $sysconf);
    $wa_hours = themeEffectiveTemplateValue('classic_service_hours', 'Senin - Jumat (08:00 - 16:00)', $sysconf);
    $wa_desc_raw = themeEffectiveTemplateValue('classic_whatsapp_desc', 'Pustakawan; Halo, silakan ketik pesan Anda langsung di kolom bawah. Agar kami dapat membantu lebih cepat, tuliskan nama, nomor anggota (jika ada), lalu pertanyaan Anda.', $sysconf);
    $wa_num = preg_replace('/[^0-9]/', '', themeEffectiveTemplateValue('classic_whatsapp_number', '628123456789', $sysconf));
    $wa_member = rasamalaWhatsappMemberContext();
    $wa_bubble = rasamalaWhatsappBubbleContent($wa_desc_raw, $wa_member);
    $wa_author = $wa_bubble['author'];
    $wa_desc = $wa_bubble['message'];
    if ($wa_member['is_logged_in']) {
        $wa_message_template = '';
        $wa_placeholder = __('Tulis pertanyaan Anda...');
    } else {
        $wa_message_template = rasamalaWhatsappMessageTemplate(themeEffectiveTemplateValue('classic_whatsapp_categories', '', $sysconf));
        $wa_placeholder = __('Tulis nama, nomor anggota, dan pertanyaan Anda.');
    }
    ?>
    <button id="floating-whatsapp-btn" class="btn-floating-whatsapp shadow-lg <?= !empty($latest_content_ticker_items) ? 'has-latest-content-ticker' : '' ?>" data-bs-toggle="modal" data-bs-target="#whatsappModal" data-toggle="modal" data-target="#whatsappModal" title="WhatsApp Chat" aria-label="WhatsApp Chat"<?= ($theme_viewer_preview_enabled && $floating_info_mode !== 'whatsapp') ? ' hidden' : '' ?>>
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
    </button>

    <div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog" aria-labelledby="whatsappModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content whatsapp-modal-content">
                <div class="modal-header whatsapp-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex flex-column text-start">
                        <span class="whatsapp-modal-name d-flex align-items-center gap-2">
                            <i class="fab fa-whatsapp fs-5 text-white" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($wa_title, ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <?php if (!empty($wa_hours)) : ?>
                        <span class="whatsapp-modal-status mt-1"><i class="far fa-clock me-1 whatsapp-modal-status-icon" aria-hidden="true"></i> <?= themeEscape(__('Service Hours')); ?>: <?= htmlspecialchars($wa_hours, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" data-dismiss="modal" aria-label="<?= themeEscape(__('Close')) ?>"></button>
                </div>
                <div class="modal-body whatsapp-modal-body">
                    <div class="whatsapp-chat-container d-flex flex-column gap-3">
                        <div class="chat-bubble-incoming d-flex flex-column">
                            <div class="chat-bubble-author"><?= htmlspecialchars($wa_author, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="chat-bubble-text"><?= htmlspecialchars($wa_desc, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>

                        <form class="whatsapp-message-form"
                              data-whatsapp-form
                              data-whatsapp-number="<?= themeEscape($wa_num); ?>"
                              data-member-name="<?= themeEscape($wa_member['name']); ?>"
                              data-member-id="<?= themeEscape($wa_member['id']); ?>">
                            <label class="whatsapp-message-label" for="whatsapp-message-input"><?= themeEscape(__('Tulis pesan Anda')) ?></label>
                            <textarea id="whatsapp-message-input"
                                      class="form-control whatsapp-message-input"
                                      rows="5"
                                      placeholder="<?= themeEscape($wa_placeholder) ?>"><?= htmlspecialchars($wa_message_template, ENT_QUOTES, 'UTF-8') ?></textarea>
                            <button type="submit" class="btn whatsapp-send-button rounded-pill"<?= $wa_num === '' ? ' disabled' : '' ?>>
                                <i class="fab fa-whatsapp fs-5 me-1" aria-hidden="true"></i>
                                <span><?= themeEscape(__('Kirim via WhatsApp')) ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script nonce="<?= themeCspNonce(); ?>">
        (function () {
            var form = document.querySelector('[data-whatsapp-form]');
            if (!form) {
                return;
            }

            var textarea = form.querySelector('.whatsapp-message-input');
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var number = form.getAttribute('data-whatsapp-number') || '';
                var memberName = (form.getAttribute('data-member-name') || '').trim();
                var memberId = (form.getAttribute('data-member-id') || '').trim();
                var message = textarea ? textarea.value.trim() : '';
                if (!number || !message) {
                    if (textarea) {
                        textarea.classList.add('is-invalid');
                        textarea.focus();
                    }
                    return;
                }

                if (textarea) {
                    textarea.classList.remove('is-invalid');
                }

                if (memberName || memberId) {
                    var finalMessage = [];
                    if (memberName) {
                        finalMessage.push('Nama: ' + memberName);
                    }
                    if (memberId) {
                        finalMessage.push('Member ID: ' + memberId);
                    }
                    finalMessage.push('Pertanyaan:\n' + message);
                    message = finalMessage.join('\n');
                }

                window.open('https://wa.me/' + number + '?text=' + encodeURIComponent(message), '_blank', 'noopener,noreferrer');
            });

            if (textarea) {
                textarea.addEventListener('input', function () {
                    textarea.classList.remove('is-invalid');
                });
            }
        })();
    </script>
<?php endif; ?>

<?php if ($show_back_to_top): ?>
    <button id="back-to-top" title="Go to top" class="btn-back-to-top shadow-lg <?= ($show_floating_libinfo || $show_floating_whatsapp) ? 'has-floating-info' : '' ?> <?= !empty($latest_content_ticker_items) ? 'has-latest-content-ticker' : '' ?>" aria-label="<?= themeEscape(__('Go to top')) ?>">
        <i class="fas fa-chevron-up" aria-hidden="true"></i>
    </button>
<?php endif; ?>
