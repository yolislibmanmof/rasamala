<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: tinfo.inc.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require_once __DIR__ . '/helpers/language.php';
require_once __DIR__ . '/helpers/theme_feature_flags.php';
require_once __DIR__ . '/helpers/tinfo_options_helper.php';
require_once __DIR__ . '/helpers/tinfo_defaults.php';
require_once __DIR__ . '/helpers/tinfo_options.php';
