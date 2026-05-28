<?php
/**
 * AWX_Theme_JSON_Integration
 *
 * Monomyth FSE theme's integration with the Awesome CSS token system.
 *
 * Reads token defaults from the Awesome XP plugin (core/tokens module)
 * and injects them into WordPress Global Styles via the
 * wp_theme_json_data_theme filter.
 *
 * This makes Awesome CSS tokens appear in:
 * - Gutenberg color picker (role-based palette)
 * - Font size dropdown (fluid modular scale)
 * - Font family selector
 * - Spacing controls
 * - Shadow presets
 *
 * User overrides in Appearance → Editor → Styles WIN over these defaults.
 *
 * DEPENDENCY: Requires Awesome XP plugin with tokens module active.
 * If Awesome XP is not active, this class does nothing (graceful degradation).
 *
 * LIVES IN: monomyth-fse/inc/class-awx-theme-json-integration.php
 * LOADED BY: monomyth-fse/functions.php
 *
 * @package MonomythFSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class AWX_Theme_JSON_Integration
{

    /** @var AWX_Token_Defaults|null */
    private $defaults = null;

    public function __construct()
    {
        // Priority 20: after theme's own theme.json loads
        add_filter('wp_theme_json_data_theme', [$this, 'inject_tokens'], 20);

        // Suppress WordPress built-in default palette, font sizes, and gradients.
        // wp_theme_json_data_default fires BEFORE theme data, so we strip the
        // defaults here. Our tokens replace them via inject_tokens above.
        add_filter('wp_theme_json_data_default', [$this, 'suppress_wp_defaults'], 99);
    }

    /**
     * Suppress WordPress built-in defaults.
     *
     * Removes the default palette (black, white, vivid-red, etc.),
     * default font sizes (small, medium, large, x-large), and
     * default gradients so only Awesome CSS tokens show in the editor.
     *
     * @param WP_Theme_JSON_Data $theme_json
     * @return WP_Theme_JSON_Data
     */
    public function suppress_wp_defaults($theme_json)
    {
        // Only suppress if Awesome XP is active
        if (!function_exists('awx_get_token_defaults') || !awx_get_token_defaults()) {
            return $theme_json;
        }

        $theme_json->update_with([
            'version' => 3,
            'settings' => [
                'color' => [
                    'defaultPalette' => false,
                    'defaultGradients' => false,
                ],
                'typography' => [
                    'defaultFontSizes' => false,
                ],
                'shadow' => [
                    'defaultPresets' => false,
                ],
            ],
        ]);

        return $theme_json;
    }

    /**
     * Get token defaults from Awesome XP.
     *
     * Returns null if Awesome XP is not active or tokens module is not loaded.
     *
     * @return AWX_Token_Defaults|null
     */
    private function get_defaults()
    {
        if ($this->defaults !== null) {
            return $this->defaults;
        }

        // Check if Awesome XP's token API is available
        if (!function_exists('awx_get_token_defaults')) {
            return null;
        }

        $this->defaults = awx_get_token_defaults();
        return $this->defaults;
    }

    /**
     * Inject token defaults into theme.json data.
     *
     * @param WP_Theme_JSON_Data $theme_json
     * @return WP_Theme_JSON_Data
     */
    public function inject_tokens($theme_json)
    {
        $defaults = $this->get_defaults();

        // Graceful degradation: if Awesome XP is not active, theme.json stays as-is
        if (!$defaults) {
            return $theme_json;
        }

        $tokens = $defaults->get_all();
        if (empty($tokens)) {
            return $theme_json;
        }

        $new_data = [
            'version' => 3,
            'settings' => [],
        ];

        $new_data['settings']['color'] = $this->build_color($tokens, $defaults);
        $new_data['settings']['typography'] = $this->build_typography($tokens);
        $new_data['settings']['spacing'] = $this->build_spacing($tokens);
        $new_data['settings']['shadow'] = $this->build_shadows($tokens);
        $new_data['settings']['custom'] = $this->build_custom($tokens);

        if (!empty($tokens['layout'])) {
            $new_data['settings']['layout'] = [
                'contentSize' => $tokens['layout']['contentSize'] ?? '48rem',
                'wideSize' => $tokens['layout']['wideSize'] ?? '72rem',
            ];
        }

        $theme_json->update_with($new_data);

        return $theme_json;
    }

    /**
     * Build color palette from role tokens.
     *
     * Resolves each role ref (e.g. "brand-a.600") to hex for WordPress.
     */
    private function build_color($tokens, $defaults)
    {
        $palette = [];

        if (!empty($tokens['color']['roles']['light'])) {
            foreach ($tokens['color']['roles']['light'] as $slug => $role) {
                $ref = $role['ref'] ?? '';
                $name = $role['name'] ?? $slug;
                $hex = $defaults->resolve_color_ref_to_hex($ref);

                $palette[] = [
                    'slug' => 'awx-' . $slug,
                    'color' => $hex,
                    'name' => $name,
                ];
            }
        }

        return [
            'palette' => $palette,
            'defaultPalette' => false,
            'defaultGradients' => false,
        ];
    }

    /**
     * Build typography settings.
     *
     * Sanitizes font-family values to prevent WordPress from stripping
     * leading quotes. Uses get_value() to handle Flow Builder value format.
     */
    private function build_typography($tokens)
    {
        $result = [
            'defaultFontSizes' => false,
        ];

        // Font families — sanitize for WordPress
        if (!empty($tokens['fontFamily'])) {
            $families = [];
            foreach ($tokens['fontFamily'] as $slug => $family) {
                $raw_value = self::get_value($family);
                $families[] = [
                    'slug' => 'awx-' . $slug,
                    'name' => is_array($family) ? ($family['name'] ?? $slug) : $slug,
                    'fontFamily' => self::sanitize_font_family($raw_value),
                ];
            }
            $result['fontFamilies'] = $families;
        }

        // Font sizes with fluid support — sanitize values
        if (!empty($tokens['fontSize'])) {
            $sizes = [];
            foreach ($tokens['fontSize'] as $slug => $size) {
                $value = self::get_value($size);

                $entry = [
                    'slug' => 'awx-' . $slug,
                    'size' => $value,
                    'name' => is_array($size) ? ($size['name'] ?? 'Size ' . $slug) : 'Size ' . $slug,
                ];

                // Fluid handling
                if (is_array($size) && !empty($size['fluid'])) {
                    $fluid_min = self::sanitize_css_value($size['fluid']['min'] ?? '');
                    $fluid_max = self::sanitize_css_value($size['fluid']['max'] ?? '');
                    if ($fluid_min && $fluid_max) {
                        $entry['fluid'] = [
                            'min' => $fluid_min,
                            'max' => $fluid_max,
                        ];
                    } else {
                        $entry['fluid'] = false;
                    }
                } else {
                    $entry['fluid'] = false;
                }

                $sizes[] = $entry;
            }
            $result['fontSizes'] = $sizes;
        }

        return $result;
    }

    /**
     * Build spacing sizes — sanitize values.
     */
    private function build_spacing($tokens)
    {
        $sizes = [];

        if (!empty($tokens['space'])) {
            foreach ($tokens['space'] as $slug => $space) {
                $value = self::get_value($space);
                $sizes[] = [
                    'slug' => 'awx-' . $slug,
                    'size' => $value,
                    'name' => is_array($space) ? ($space['name'] ?? 'Space ' . $slug) : 'Space ' . $slug,
                ];
            }
        }

        return [
            'spacingScale' => ['steps' => 0],
            'spacingSizes' => $sizes,
            'units' => ['%', 'px', 'em', 'rem', 'vh', 'vw'],
        ];
    }

    /**
     * Build shadow presets — uses get_value() for consistent value extraction.
     */
    private function build_shadows($tokens)
    {
        $presets = [];
        $names = [
            '1' => 'Subtle',
            '2' => 'Raised',
            '3' => 'Elevated',
            '4' => 'Floating',
            '5' => 'High',
        ];

        if (!empty($tokens['shadow'])) {
            foreach ($tokens['shadow'] as $slug => $value) {
                if ($slug === 'inner' || $slug === 'none')
                    continue;
                $presets[] = [
                    'slug' => 'awx-' . $slug,
                    'name' => $names[$slug] ?? 'Shadow ' . $slug,
                    'shadow' => self::get_value($value),
                ];
            }
        }

        return [
            'defaultPresets' => false,
            'presets' => $presets,
        ];
    }

    /**
     * Build custom properties (--wp--custom--*).
     *
     * Uses self::get_value() for consistent value extraction
     */
    private function build_custom($tokens)
    {
        $custom = [];

        // Helper: extract all values from a token group using get_value()
        $extract = function ($group) {
            $result = [];
            foreach ($group as $slug => $token) {
                $result[$slug] = self::get_value($token);
            }
            return $result;
        };

        // Font weights → integers
        if (!empty($tokens['fontWeight'])) {
            $custom['fontWeight'] = [];
            foreach ($tokens['fontWeight'] as $slug => $token) {
                $custom['fontWeight'][$slug] = (int) self::get_value($token);
            }
        }

        // Line heights → floats
        if (!empty($tokens['leading'])) {
            $custom['lineHeight'] = [];
            foreach ($tokens['leading'] as $slug => $token) {
                $custom['lineHeight'][$slug] = (float) self::get_value($token);
            }
        }

        // Letter spacing → strings (e.g. "-0.02em")
        if (!empty($tokens['tracking'])) {
            $custom['letterSpacing'] = $extract($tokens['tracking']);
        }

        // Radius → strings (e.g. "8px")
        if (!empty($tokens['radius'])) {
            $custom['radius'] = $extract($tokens['radius']);
        }

        // Border widths → strings (e.g. "1px")
        if (!empty($tokens['border'])) {
            $custom['borderWidth'] = $extract($tokens['border']);
        }

        // Duration → strings (e.g. "200ms")
        if (!empty($tokens['duration'])) {
            $custom['duration'] = $extract($tokens['duration']);
        }

        // Easing → strings (e.g. "cubic-bezier(...)")
        if (!empty($tokens['ease'])) {
            $custom['ease'] = $extract($tokens['ease']);
        }

        // Z-index → integers
        if (!empty($tokens['z'])) {
            $custom['zIndex'] = [];
            foreach ($tokens['z'] as $slug => $token) {
                $custom['zIndex'][$slug] = (int) self::get_value($token);
            }
        }

        // Measure → strings (e.g. "65ch")
        if (!empty($tokens['width'])) {
            $custom['measure'] = [];
            foreach (['measure', 'measure-wide', 'measure-narrow'] as $key) {
                if (isset($tokens['width'][$key])) {
                    $custom_key = str_replace('measure-', '', $key);
                    if ($custom_key === 'measure')
                        $custom_key = 'default';
                    $custom['measure'][$custom_key] = self::get_value($tokens['width'][$key]);
                }
            }
        }

        return $custom;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPER METHODS (self-contained — no dependency on plugin method versions)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Extract the raw value from a token entry.
     *
     * Token entries can be:
     * - A simple string: '4px'
     * - An array with 'value' key: ['value' => '4px', 'name' => 'Small']
     * - An array from Awesome Flow Builder with '_value' key
     *
     * @param mixed $token
     * @return string
     */
    private static function get_value($token)
    {
        if (is_string($token)) {
            return self::sanitize_css_value($token);
        }
        if (is_array($token)) {
            $val = $token['_value'] ?? $token['value'] ?? '';
            return self::sanitize_css_value((string) $val);
        }
        return self::sanitize_css_value((string) $token);
    }

    /**
     * Sanitize a CSS value.
     *
     * Fixes "3 rem" → "3rem", "48 px" → "48px" (Flow Builder splitting bug).
     *
     * @param string $value
     * @return string
     */
    private static function sanitize_css_value($value)
    {
        $value = trim($value);
        $value = preg_replace(
            '/^(-?\d*\.?\d+)\s+(rem|em|px|vh|vw|ch|%|ms|s)$/i',
            '$1$2',
            $value
        );
        return $value;
    }

    /**
     * Sanitize a font-family value for WordPress theme.json.
     *
     * WordPress strips leading quotes. This ensures single-word font names
     * are unquoted and multi-word names are properly quoted.
     *
     * @param string $font_family
     * @return string
     */
    private static function sanitize_font_family($font_family)
    {
        $fonts = array_map('trim', explode(',', $font_family));
        $result = [];
        foreach ($fonts as $font) {
            $clean = trim($font, "\"' ");
            if (preg_match('/\s/', $clean) && !preg_match('/^(system-ui|ui-monospace|ui-sans-serif|ui-serif)$/', $clean)) {
                $result[] = "'" . $clean . "'";
            } else {
                $result[] = $clean;
            }
        }
        return implode(', ', $result);
    }
}