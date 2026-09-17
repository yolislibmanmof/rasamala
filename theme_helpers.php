<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: theme_helpers.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require_once __DIR__ . '/helpers/theme_feature_flags.php';
require_once __DIR__ . '/helpers/security.php';
require_once __DIR__ . '/helpers/palette.php';
require_once __DIR__ . '/helpers/background.php';
require_once __DIR__ . '/helpers/preset.php';
require_once __DIR__ . '/helpers/navigation.php';
require_once __DIR__ . '/helpers/visitor.php';
require_once __DIR__ . '/helpers/core.php';
require_once __DIR__ . '/helpers/ui.php';
