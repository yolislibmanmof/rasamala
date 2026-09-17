<?php
/**
 * Helper Module for Rasamala Template - Theme Presets Entry Point
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require_once __DIR__ . '/presets/preset_definitions.php';
require_once __DIR__ . '/presets/preset_resolvers.php';
require_once __DIR__ . '/presets/preset_display.php';
