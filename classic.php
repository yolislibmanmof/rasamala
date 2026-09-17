<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: classic.php

// ----------------------------------------------------------------------------
// Be sure that this file not accessed directly
// ----------------------------------------------------------------------------
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

include_once __DIR__ . '/theme_helpers.php';
include_once __DIR__ . '/helpers/core.php';

// ----------------------------------------------------------------------------
// Define member login state
// ----------------------------------------------------------------------------
$is_login = utility::isMemberLogin();
$member_image_name = $_SESSION['m_image'] ?? 'person.png';
$member_image_path = getImagePath($sysconf, $member_image_name, 'persons');
