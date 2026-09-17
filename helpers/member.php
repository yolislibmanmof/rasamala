<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: member.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (!function_exists('rasamalaReplaceMemberCardContent')) {
    function rasamalaReplaceMemberCardContent($content, $card_html)
    {
        $needle = '<div class="bg-white border-right border-bottom border-left p-4">';
        $start = strpos($content, $needle);
        if ($start === false) {
            return $content . "\n" . $needle . $card_html . '</div>';
        }

        $inner_start = $start + strlen($needle);
        $offset = $inner_start;
        $depth = 1;
        while ($depth > 0 && preg_match('/<\/?div\b[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $tag = $matches[0][0];
            $tag_pos = $matches[0][1];
            if (preg_match('/^<\s*\/\s*div\b/i', $tag)) {
                $depth--;
            } else {
                $depth++;
            }
            $offset = $tag_pos + strlen($tag);
        }

        if ($depth !== 0) {
            return $content . "\n" . $needle . $card_html . '</div>';
        }

        $closing_start = $tag_pos;
        return substr($content, 0, $inner_start)
            . "\n" . $card_html
            . substr($content, $closing_start);
    }
}

if (!function_exists('rasamalaMemberExpiryStatus')) {
    function rasamalaMemberExpiryStatus($expiry_date)
    {
        $expiry_date = trim((string)$expiry_date);
        if ($expiry_date === '') {
            return [
                'class' => 'unknown',
                'label' => __('Status unavailable'),
                'note' => __('Expiry date is not available.'),
                'days_until' => null,
            ];
        }

        $expiry_timestamp = strtotime($expiry_date);
        if ($expiry_timestamp === false) {
            return [
                'class' => 'unknown',
                'label' => __('Status unavailable'),
                'note' => __('Expiry date is not available.'),
                'days_until' => null,
            ];
        }

        $today_start = strtotime(date('Y-m-d') . ' 00:00:00');
        $expiry_end = strtotime(date('Y-m-d', $expiry_timestamp) . ' 23:59:59');
        $days_until = (int)floor(($expiry_end - $today_start) / 86400);

        if ($days_until < 0) {
            return [
                'class' => 'expired',
                'label' => __('Expired'),
                'note' => __('Please renew your membership.'),
                'days_until' => $days_until,
            ];
        }

        if ($days_until <= 30) {
            return [
                'class' => 'warning',
                'label' => __('Almost expired'),
                'note' => $days_until === 0
                    ? __('Membership expires today.')
                    : sprintf(__('Membership expires in %d day(s).'), $days_until),
                'days_until' => $days_until,
            ];
        }

        return [
            'class' => 'active',
            'label' => __('Active'),
            'note' => __('Membership is active.'),
            'days_until' => $days_until,
        ];
    }
}

if (!function_exists('rasamalaMemberRedirect')) {
    function rasamalaMemberRedirect($url)
    {
        $url = str_replace(["\r", "\n", "\0"], '', (string)$url);
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }

        echo '<meta http-equiv="refresh" content="0;url=' . themeEscape($url) . '">';
        echo '<p><a href="' . themeEscape($url) . '">' . themeEscape(__('Continue')) . '</a></p>';
        exit;
    }
}

if (!function_exists('rasamalaMemberAreaConfig')) {
    function rasamalaMemberAreaConfig($sysconf)
    {
        return [
            'reserveDirectDatabase' => !empty($sysconf['reserve_direct_database']),
            'urls' => [
                'basket' => 'index.php?p=member&sec=title_basket',
                'member' => 'index.php?p=member',
                'bookmark' => 'index.php?p=member&sec=bookmark',
            ],
            'labels' => [
                'confirm' => __('Confirm'),
                'cancel' => __('Cancel'),
                'close' => __('Close'),
                'clearBasketTitle' => __('Clear Basket'),
                'clearBasketMessage' => __('Clear your title(s) basket?'),
                'clearingBasket' => __('Clearing basket...'),
                'basketCleared' => __('Basket data cleared!'),
                'basketClearFailed' => __('Failed to clear basket.'),
                'removeBasketTitle' => __('Remove from Basket'),
                'removeBasketMessage' => __('Remove selected title(s) from basket?'),
                'removingBasket' => __('Removing selected titles...'),
                'basketRemoved' => __('Selected basket data removed!'),
                'basketRemoveFailed' => __('Failed to remove selected titles.'),
                'reservationSending' => __('Please wait, your reservation is being sent...'),
                'reservationInfo' => __('Please wait. your reservation is being sent'),
                'reservationSent' => __('Reservation request sent'),
                'reservationEmailSent' => __('Reservation e-mail sent'),
                'unexpectedError' => __('Unexpected error occurred.'),
                'removeBookmarkTitle' => __('Remove Bookmark'),
                'removeBookmarkMessage' => __('Remove this bookmark?'),
                'removingBookmark' => __('Removing bookmark...'),
                'bookmarkRemoveFailed' => __('Unexcpected error. Please tell it to the librarian'),
                'printMemberCard' => __('Print Member Card'),
            ],
        ];
    }
}

if (!function_exists('rasamalaSanitizeMemberSessionContent')) {
    function rasamalaSanitizeMemberSessionContent(&$main_content)
    {
        if (!isset($main_content)) return;

        // Escaping session variables to prevent XSS (F10)
        $escape_session_var = function ($session_val, &$content) {
            $session_val = trim((string)$session_val);
            if ($session_val === '') return;
            $escaped = htmlspecialchars($session_val, ENT_QUOTES, 'UTF-8');
            $pattern = '/(<td\b[^>]*>)' . preg_quote($session_val, '/') . '(<\/td>)/i';
            $content = preg_replace_callback($pattern, function ($m) use ($escaped) {
                return $m[1] . $escaped . $m[2];
            }, $content);
        };

        if (isset($_SESSION['m_name'])) {
            $escape_session_var($_SESSION['m_name'], $main_content);
            $main_content = str_replace(
                'Hi, ' . $_SESSION['m_name'] . ' <a',
                'Hi, ' . htmlspecialchars($_SESSION['m_name'], ENT_QUOTES, 'UTF-8') . ' <a',
                $main_content
            );
        }
        if (isset($_SESSION['mid'])) {
            $escape_session_var($_SESSION['mid'], $main_content);
        }
        if (isset($_SESSION['m_email'])) {
            $escape_session_var($_SESSION['m_email'], $main_content);
        }
        if (isset($_SESSION['m_member_type'])) {
            $escape_session_var($_SESSION['m_member_type'], $main_content);
            $main_content = str_replace(
                'text-green font-weight-bold">' . $_SESSION['m_member_type'],
                'text-green font-weight-bold">' . htmlspecialchars($_SESSION['m_member_type'], ENT_QUOTES, 'UTF-8'),
                $main_content
            );
            $main_content = str_replace(
                'text-green">' . $_SESSION['m_member_type'],
                'text-green">' . htmlspecialchars($_SESSION['m_member_type'], ENT_QUOTES, 'UTF-8'),
                $main_content
            );
        }
        if (isset($_SESSION['m_register_date'])) {
            $escape_session_var($_SESSION['m_register_date'], $main_content);
        }
        if (isset($_SESSION['m_expire_date'])) {
            $escape_session_var($_SESSION['m_expire_date'], $main_content);
        }
        if (isset($_SESSION['m_institution'])) {
            $escape_session_var($_SESSION['m_institution'], $main_content);
        }

        // Replace empty table cells in Member Detail with a clean '-' fallback indicator
        $main_content = preg_replace(
            '/(<td\b[^>]*class="[^"]*value\b[^"]*"[^>]*>)\s*(<\/td>)/i',
            '$1-<span class="visually-hidden">' . themeEscape(__('Not provided')) . '</span>$2',
            $main_content
        );

        // Accessible labels (F9)
        $member_id_label = __('Member ID');
        $password_label = __('Password');
        $main_content = str_replace(
            '<div class="fieldLabel">' . $member_id_label . '</div>' . "\n" . '                <div class="login_input"><input class="form-control" type="text" name="memberID"',
            '<label class="fieldLabel d-block" for="memberID">' . $member_id_label . '</label>' . "\n" . '                <div class="login_input"><input class="form-control" id="memberID" type="text" name="memberID"',
            $main_content
        );
        $main_content = str_replace(
            '<div class="fieldLabel marginTop">' . $password_label . '</div>' . "\n" . '                <div class="login_input"><input class="form-control" type="password" name="memberPassWord"',
            '<label class="fieldLabel marginTop d-block" for="memberPassWord">' . $password_label . '</label>' . "\n" . '                <div class="login_input"><input class="form-control" id="memberPassWord" type="password" name="memberPassWord"',
            $main_content
        );

        $curr_pass_label = __('Current Password');
        $new_pass_label = __('New Password');
        $conf_pass_label = __('Confirm Password');
        $main_content = str_replace(
            '<td class="key alterCell" width="20%"><strong>' . $curr_pass_label . '</strong></td><td class="value alterCell2"><input type="password" name="currPass"',
            '<td class="key alterCell" width="20%"><label for="currPass" class="m-0 font-weight-bold">' . $curr_pass_label . '</label></td><td class="value alterCell2"><input type="password" id="currPass" name="currPass"',
            $main_content
        );
        $main_content = str_replace(
            '<td class="key alterCell" width="20%"><strong>' . $new_pass_label . '</strong></td><td class="value alterCell2"><input type="password" name="newPass"',
            '<td class="key alterCell" width="20%"><label for="newPass" class="m-0 font-weight-bold">' . $new_pass_label . '</label></td><td class="value alterCell2"><input type="password" id="newPass" name="newPass"',
            $main_content
        );
        $main_content = str_replace(
            '<td class="key alterCell" width="20%"><strong>' . $conf_pass_label . '</strong></td><td class="value alterCell2"><input type="password" name="newPass2"',
            '<td class="key alterCell" width="20%"><label for="newPass2" class="m-0 font-weight-bold">' . $conf_pass_label . '</label></td><td class="value alterCell2"><input type="password" id="newPass2" name="newPass2"',
            $main_content
        );

        // Replace JS script tags to remove blocking confirms/alerts; behavior is handled by assets/js/member_area.js.
        $script_tag_open = '<scr' . 'ipt type="text\/javascript">';
        $script_tag_close = '<\/scr' . 'ipt>';
        $pattern = '/' . $script_tag_open . '\s*\$\(document\)\.ready\(function \(\) \{\s*\$\(\'\.clearAll\'\).+?' . $script_tag_close . '/s';
        $main_content = preg_replace($pattern, '', $main_content);

        // Inject "My Card" tab link
        $my_card_active = (isset($_GET['sec']) && $_GET['sec'] === 'my_card') ? 'active' : '';
        $my_card_text = '<i class="fas fa-id-card mr-2" aria-hidden="true"></i>' . __('My Card');

        $my_account_label = __('My Account');
        $my_account_label_icon = '<i class="fas fa-user-circle mr-2" aria-hidden="true"></i>' . $my_account_label;
        $main_content = str_replace('sec=my_account">' . $my_account_label . '</a>', 'sec=my_account">' . $my_account_label_icon . '</a>', $main_content);

        $main_content = preg_replace_callback(
            '/(<li\s+class="nav-item">[^<]*<a\s+[^>]*href=["\'][^"\']*sec=my_account[^"\']*["\'][^>]*>.*?<\/a>[^<]*<\/li>)/is',
            function($matches) use ($my_card_active, $my_card_text) {
                return $matches[1] . "\n" . '                    <li class="nav-item"><a class="nav-link ' . $my_card_active . '" href="index.php?p=member&sec=my_card">' . $my_card_text . '</a></li>';
            },
            $main_content
        );

        // Fix "Current Loan" redirection loop when default page is my_card
        $main_content = str_replace('href="index.php?p=member"', 'href="index.php?p=member&amp;sec=current_loan"', $main_content);
        $main_content = str_replace("href='index.php?p=member'", "href='index.php?p=member&amp;sec=current_loan'", $main_content);
        $main_content = str_replace('href="index.php?p=member&amp;sec="', 'href="index.php?p=member&amp;sec=current_loan"', $main_content);

        // Replace SLiMS core membercard references with my_card
        $main_content = str_replace('sec=membercard', 'sec=my_card', $main_content);

        // Strip target="_blank" from links containing sec=my_card or sec=membercard to prevent opening in a new tab
        $main_content = preg_replace_callback('/<a\s+[^>]*href=["\'][^"\']*(?:sec=my_card|sec=membercard)[^"\']*["\'][^>]*>/i', function($matches) {
            return str_replace(['target="_blank"', "target='_blank'"], '', $matches[0]);
        }, $main_content);
    }
}
