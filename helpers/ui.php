<?php
/**
 * Helper Module for Rasamala Template - UI Helpers Entry Point
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require_once __DIR__ . '/ui/ui_text.php';
require_once __DIR__ . '/ui/ui_cover.php';
require_once __DIR__ . '/ui/ui_content.php';
require_once __DIR__ . '/ui/ui_librarian.php';
require_once __DIR__ . '/ui/ui_header.php';
