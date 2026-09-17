<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: _member.php
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require_once dirname(__DIR__) . '/helpers/member.php';
require_once __DIR__ . '/member/digital_card.php';

$rasamala_member_area_config = rasamalaMemberAreaConfig($sysconf);

if (isset($main_content)) {
    rasamalaSanitizeMemberSessionContent($main_content);
    rasamalaRenderDigitalMemberCard($main_content, $sysconf);
}

include __DIR__ . '/member/member_layout.php';
