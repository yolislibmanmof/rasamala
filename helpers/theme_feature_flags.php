<?php
/**
 * Temporary Rasamala feature flags.
 *
 * Keep the implementation of unfinished features in place, but hide their
 * controls until they are ready for production.  Re-enable a feature by
 * changing its value to true below; no database migration is required.
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

if (!function_exists('rasamalaThemeFeatureFlags')) {
    function rasamalaThemeFeatureFlags()
    {
        return [
            // Temporary release gate: these controls are not ready for publish.
            'panel_background' => false,
            'cursor_icon' => false,
            'cursor_particles' => false,
        ];
    }
}

if (!function_exists('rasamalaThemeFeatureEnabled')) {
    function rasamalaThemeFeatureEnabled($feature)
    {
        $flags = rasamalaThemeFeatureFlags();
        return !array_key_exists((string)$feature, $flags) || $flags[(string)$feature] === true;
    }
}

if (!function_exists('rasamalaThemeDisabledTinfoFields')) {
    function rasamalaThemeDisabledTinfoFields()
    {
        return [
            'classic_search_panel_style' => 'solid',
            'classic_cursor_custom_icon' => 'default',
            'classic_cursor_particles' => 'none',
        ];
    }
}

if (!function_exists('rasamalaFilterDisabledTinfoOptions')) {
    function rasamalaFilterDisabledTinfoOptions($definitions)
    {
        if (!is_array($definitions)) {
            return [];
        }

        $disabled = rasamalaThemeDisabledTinfoFields();
        $featureByField = [
            'classic_search_panel_style' => 'panel_background',
            'classic_cursor_custom_icon' => 'cursor_icon',
            'classic_cursor_particles' => 'cursor_particles',
        ];
        foreach ($definitions as $key => $definition) {
            $dbfield = is_array($definition) ? (string)($definition['dbfield'] ?? '') : '';
            if ($dbfield !== '' && array_key_exists($dbfield, $disabled)
                && !rasamalaThemeFeatureEnabled($featureByField[$dbfield])) {
                unset($definitions[$key]);
            }
        }
        return $definitions;
    }
}

if (!function_exists('rasamalaDisabledTinfoValue')) {
    function rasamalaDisabledTinfoValue($key, $fallback = null)
    {
        $disabled = rasamalaThemeDisabledTinfoFields();
        $featureByField = [
            'classic_search_panel_style' => 'panel_background',
            'classic_cursor_custom_icon' => 'cursor_icon',
            'classic_cursor_particles' => 'cursor_particles',
        ];
        $field = (string)$key;
        if (array_key_exists($field, $disabled)
            && !rasamalaThemeFeatureEnabled($featureByField[$field])) {
            return $disabled[$field];
        }
        return $fallback;
    }
}
