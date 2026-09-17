<?php
/**
 * Metadata bridge for the public Theme Viewer.
 *
 * The administrator TInfo page and the Theme Viewer must use the same option
 * definitions.  We deliberately load only the option definition files here;
 * tinfo_options.php also boots the administrator customizer and may emit
 * markup, which is not appropriate inside an OPAC page.
 */
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) {
    die("can not access this file directly");
}

require_once __DIR__ . '/theme_feature_flags.php';

if (!function_exists('rasamalaThemeViewerTinfoOptions')) {
    function rasamalaThemeViewerTinfoOptions($sysconf = [])
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $files = [
            'general' => __DIR__ . '/options/tinfo_option_general.php',
            'navbar' => __DIR__ . '/options/tinfo_option_navbar.php',
            'hero' => __DIR__ . '/options/tinfo_option_hero.php',
            'content' => __DIR__ . '/options/tinfo_option_content.php',
            'footer' => __DIR__ . '/options/tinfo_option_footer.php',
            'display' => __DIR__ . '/options/tinfo_option_display.php',
            'visitor' => __DIR__ . '/options/tinfo_option_visitor.php',
        ];
        $groupLabels = [
            'general' => themeTranslate('General & Layout'),
            'navbar' => themeTranslate('Navigation Bar'),
            'hero' => themeTranslate('Hero & Background'),
            'content' => themeTranslate('Homepage & Content'),
            'footer' => themeTranslate('Footer & Social Media'),
            'display' => themeTranslate('Display & Detail Pages'),
            'visitor' => themeTranslate('Visitor / Guestbook'),
        ];

        $options = [];
        foreach ($files as $group => $file) {
            if (!is_file($file)) {
                continue;
            }
            $definitions = rasamalaFilterDisabledTinfoOptions(require $file);
            if (!is_array($definitions)) {
                continue;
            }
            foreach ($definitions as $key => $definition) {
                if (!is_array($definition) || empty($definition['dbfield'])) {
                    continue;
                }
                $dbfield = (string)$definition['dbfield'];
                $type = strtolower((string)($definition['type'] ?? 'text'));
                $choices = [];
                foreach ((array)($definition['data'] ?? []) as $choice) {
                    if (is_array($choice)) {
                        $value = $choice[0] ?? '';
                        $label = $choice[1] ?? $value;
                    } else {
                        $value = $choice;
                        $label = $choice;
                    }
                    if (is_scalar($value)) {
                        $choices[] = [
                            'value' => (string)$value,
                            'label' => is_scalar($label) ? (string)$label : (string)$value,
                        ];
                    }
                }

                $default = $definition['default'] ?? '';
                $value = function_exists('themeEffectiveTemplateValue')
                    ? themeEffectiveTemplateValue($dbfield, $default, $sysconf)
                    : (($sysconf['template'][$dbfield] ?? null) !== null ? $sysconf['template'][$dbfield] : $default);
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                $options[] = [
                    'key' => (string)$key,
                    'dbfield' => $dbfield,
                    'label' => (string)($definition['label'] ?? $dbfield),
                    'help' => (string)($definition['help'] ?? ''),
                    'type' => $type,
                    'value' => (string)($value ?? ''),
                    'default' => is_scalar($default) ? (string)$default : '',
                    'max' => (string)($definition['max'] ?? ($type === 'longtext' ? '4000' : '250')),
                    'choices' => $choices,
                    'group' => $group,
                    'groupLabel' => (string)($groupLabels[$group] ?? $group),
                ];
            }
        }

        $cache = $options;
        return $cache;
    }
}
