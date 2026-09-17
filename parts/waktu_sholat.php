<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: waktu_sholat.php

if (!function_exists('rasamalaWaktuSholatFetchTimings')) {
    function rasamalaWaktuSholatFetchTimings($city)
    {
        global $sysconf;
        $today = date('Y-m-d');
        $city = trim((string)($city ?: 'Jakarta'));
        $country = trim((string)($sysconf['template']['classic_prayer_times_country'] ?? 'Indonesia'));
        $country = $country !== '' ? $country : 'Indonesia';
        $cache_dir = sys_get_temp_dir();
        $cache_key = md5(strtolower($city . '|' . $country));
        $cache_file = $cache_dir . '/rasamala_prayer_' . $cache_key . '.json';
        $legacy_cache_file = $cache_dir . '/rasamala_prayer_' . md5($city . '_' . $country) . '_' . $today . '.json';
        $failure_file = $cache_dir . '/rasamala_prayer_' . $cache_key . '.fail';
        $now = time();
        $stale_timings = null;
        $read_cache = function ($file, $legacy_date = null) {
            if (!is_readable($file)) {
                return null;
            }

            $cache_data = @file_get_contents($file);
            if (!$cache_data) {
                return null;
            }

            $payload = json_decode($cache_data, true);
            if (!is_array($payload)) {
                return null;
            }

            if (isset($payload['timings']) && is_array($payload['timings'])) {
                return $payload;
            }

            return [
                'date' => $legacy_date,
                'generated_at' => filemtime($file) ?: time(),
                'timings' => $payload
            ];
        };

        $cache_payload = $read_cache($cache_file);
        if (is_array($cache_payload) && ($cache_payload['date'] ?? '') === $today && is_array($cache_payload['timings'] ?? null)) {
            return $cache_payload['timings'];
        }

        if (is_array($cache_payload) && is_array($cache_payload['timings'] ?? null)
            && (int)($cache_payload['generated_at'] ?? 0) >= ($now - 604800)) {
            $stale_timings = $cache_payload['timings'];
        }

        $legacy_payload = $read_cache($legacy_cache_file, $today);
        if (is_array($legacy_payload) && ($legacy_payload['date'] ?? '') === $today && is_array($legacy_payload['timings'] ?? null)) {
            return $legacy_payload['timings'];
        }

        if (isset($_SESSION['prayer_cache'])
            && is_array($_SESSION['prayer_cache'])
            && ($_SESSION['prayer_cache']['date'] ?? '') === $today
            && ($_SESSION['prayer_cache']['city'] ?? '') === $city
            && ($_SESSION['prayer_cache']['country'] ?? $country) === $country
            && isset($_SESSION['prayer_cache']['timings'])
            && is_array($_SESSION['prayer_cache']['timings'])) {
            return $_SESSION['prayer_cache']['timings'];
        }

        if (is_readable($failure_file) && (filemtime($failure_file) ?: 0) >= ($now - 1800)) {
            return $stale_timings;
        }

        $url = 'https://api.aladhan.com/v1/timingsByCity?city=' . urlencode($city) . '&country=' . urlencode($country) . '&method=11';
        $response = false;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }

        if (!$response && ini_get('allow_url_fopen')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 1,
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                ]
            ]);
            $response = @file_get_contents($url, false, $ctx);
        }

        if (!$response) {
            @touch($failure_file);
            return $stale_timings;
        }

        $data = json_decode($response, true);
        if (!isset($data['code'], $data['data']['timings']) || $data['code'] !== 200) {
            @touch($failure_file);
            return $stale_timings;
        }

        $timings = $data['data']['timings'];

        // Save to file cache
        @file_put_contents($cache_file, json_encode([
            'date' => $today,
            'city' => $city,
            'country' => $country,
            'generated_at' => $now,
            'timings' => $timings
        ]), LOCK_EX);
        if (is_readable($failure_file)) {
            @unlink($failure_file);
        }

        // Save to session cache as fallback
        $_SESSION['prayer_cache'] = [
            'date' => $today,
            'city' => $city,
            'country' => $country,
            'timings' => $timings
        ];

        return $timings;
    }
}

if (!function_exists('rasamalaWaktuSholatData')) {
    function rasamalaWaktuSholatData($sysconf)
    {
        $widget_type = themeEffectiveTemplateValue('classic_prayer_times_show', 'hide', $sysconf);
        if ($widget_type == 1 || $widget_type === '1') {
            $widget_type = 'both';
        } elseif ($widget_type == 0 || $widget_type === '0') {
            $widget_type = 'hide';
        }

        $show_footer_times = ($widget_type === 'both' || $widget_type === 'footer');
        $show_reminder_toast = ($widget_type === 'both' || $widget_type === 'floating');
        $test_mode = false;
        $city = $sysconf['template']['classic_prayer_times_city'] ?? 'Jakarta';
        $result = [
            'show_footer_times' => $show_footer_times,
            'show_reminder_toast' => false,
            'city' => $city,
            'next_prayer' => null,
            'minutes_until' => null,
        ];

        if ($widget_type === 'hide') {
            return $result;
        }

        $timings = rasamalaWaktuSholatFetchTimings($city);
        if (!$timings) {
            return $result;
        }

        $names = [
            'Imsak' => 'Imsak',
            'Fajr' => 'Subuh',
            'Dhuhr' => 'Dzuhur',
            'Asr' => 'Ashar',
            'Maghrib' => 'Maghrib',
            'Isha' => 'Isya'
        ];
        $current_minutes = (int)date('H') * 60 + (int)date('i');
        $prayers = [];

        foreach ($names as $key => $display_name) {
            if (!isset($timings[$key])) {
                continue;
            }

            $time = preg_replace('/[^0-9:]/', '', $timings[$key]);
            $parts = explode(':', $time);
            if (count($parts) !== 2) {
                continue;
            }

            $minutes = (int)$parts[0] * 60 + (int)$parts[1];
            $prayers[] = [
                'name' => $display_name,
                'time' => $time,
                'minutes' => $minutes
            ];
        }

        usort($prayers, function ($a, $b) {
            return $a['minutes'] <=> $b['minutes'];
        });

        foreach ($prayers as $prayer) {
            if ($prayer['minutes'] >= $current_minutes) {
                $result['next_prayer'] = $prayer;
                break;
            }
        }

        if (!$result['next_prayer'] && !empty($prayers)) {
            $result['next_prayer'] = $prayers[0];
        }

        if ($result['next_prayer']) {
            $minutes_until = $result['next_prayer']['minutes'] - $current_minutes;
            if ($minutes_until < 0) {
                $minutes_until += 1440;
            }
            $result['minutes_until'] = $minutes_until;
            $result['show_reminder_toast'] = $show_reminder_toast && ($minutes_until <= 10 || $test_mode);
        }

        return $result;
    }
}

if (!function_exists('rasamalaWaktuSholatFooterHtml')) {
    function rasamalaWaktuSholatFooterHtml($data, $footer_search_show = false)
    {
        if (empty($data['show_footer_times']) || empty($data['next_prayer'])) {
            return '';
        }

        $next_prayer = $data['next_prayer'];
        $class = 'footer-next-prayer' . ($footer_search_show ? ' mt-2' : '');
        return '<div class="' . themeEscape($class) . '">'
            . themeEscape($next_prayer['name']) . ' (' . themeEscape($data['city']) . ') <strong>' . themeEscape($next_prayer['time']) . '</strong>'
            . '</div>';
    }
}

if (!function_exists('rasamalaWaktuSholatReminderHtml')) {
    function rasamalaWaktuSholatReminderHtml($data)
    {
        if (empty($data['show_reminder_toast']) || empty($data['next_prayer'])) {
            return '';
        }

        $next_prayer = $data['next_prayer'];
        $seconds_until = max(0, (int)($data['minutes_until'] ?? 0) * 60);
        ob_start();
        ?>
        <div class="prayer-reminder-toast" role="status" aria-live="polite" 
             data-prayer-countdown="<?= themeEscape($seconds_until); ?>"
             data-prayer-name="<?= themeEscape($next_prayer['name']); ?>"
             data-prayer-city="<?= themeEscape($data['city']); ?>">
            <div class="prayer-reminder-icon">
                <i class="fas fa-mosque" aria-hidden="true"></i>
            </div>
            <div class="prayer-reminder-text">
                <strong><?= themeEscape($next_prayer['name']) ?> (<?= themeEscape($data['city']) ?>) <span class="prayer-reminder-countdown">-<?= themeEscape(sprintf('%d:00', (int)($data['minutes_until'] ?? 0))); ?></span></strong>
            </div>
            <button type="button" class="prayer-reminder-close" aria-label="<?= themeEscape(__('Close')) ?>">&times;</button>
        </div>
        <script nonce="<?= themeCspNonce(); ?>">
        (function () {
            var reminder = document.querySelector('.prayer-reminder-toast');
            if (!reminder) return;
            var counter = reminder.querySelector('.prayer-reminder-countdown');
            var textContainer = reminder.querySelector('.prayer-reminder-text');
            var secondsLeft = parseInt(reminder.getAttribute('data-prayer-countdown') || '0', 10);
            var prayerName = reminder.getAttribute('data-prayer-name') || '';
            var cityName = reminder.getAttribute('data-prayer-city') || '';
            var timerId = null;
            var transitionExecuted = false;

            var triggerTransitionAndHide = function () {
                if (transitionExecuted) return;
                transitionExecuted = true;
                if (timerId) {
                    clearInterval(timerId);
                    timerId = null;
                }
                
                // Smooth transition to Waktunya Sholat text
                if (textContainer) {
                    textContainer.style.transition = 'opacity 0.25s ease';
                    textContainer.style.opacity = '0';
                    setTimeout(function () {
                        textContainer.innerHTML = '<strong>Waktunya Sholat ' + prayerName + ' (' + cityName + ')!</strong>';
                        textContainer.style.opacity = '1';
                    }, 250);
                }

                // 3 seconds later, trigger fade out animation
                setTimeout(function () {
                    reminder.classList.add('is-hidden');
                }, 3250); // 3000ms delay + 250ms for transition
            };

            var renderCountdown = function () {
                if (secondsLeft <= 0) {
                    triggerTransitionAndHide();
                    return;
                }
                if (!counter) return;
                var minutes = Math.floor(secondsLeft / 60);
                var seconds = secondsLeft % 60;
                counter.textContent = '-' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            };

            renderCountdown();

            if (secondsLeft > 0) {
                timerId = setInterval(function () {
                    secondsLeft -= 1;
                    renderCountdown();
                }, 1000);
            }

            var close = reminder.querySelector('.prayer-reminder-close');
            if (close) {
                close.addEventListener('click', function () {
                    if (timerId) {
                        clearInterval(timerId);
                    }
                    reminder.classList.add('is-hidden');
                });
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

$rasamala_waktu_sholat_footer_html = '';
$rasamala_waktu_sholat_reminder_html = '';

if (isset($sysconf) && is_array($sysconf)) {
    $waktu_sholat_data = rasamalaWaktuSholatData($sysconf);
    $rasamala_waktu_sholat_footer_html = rasamalaWaktuSholatFooterHtml(
        $waktu_sholat_data,
        !empty($footer_search_show)
    );
    $rasamala_waktu_sholat_reminder_html = rasamalaWaktuSholatReminderHtml($waktu_sholat_data);
}
