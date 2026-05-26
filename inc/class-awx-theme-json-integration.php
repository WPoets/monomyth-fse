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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AWX_Theme_JSON_Integration {

    /** @var AWX_Token_Defaults|null */
    private $defaults = null;

    public function __construct() {
        // after_setup_theme is too early — Awesome XP loads on plugins_loaded (priority 5)
        // wp_theme_json_data_theme fires during template loading, which is after plugins_loaded
        // Priority 20: after theme's own theme.json loads, before user overrides
        add_filter( 'wp_theme_json_data_theme', [ $this, 'inject_tokens' ], 20 );
    }

    /**
     * Get token defaults from Awesome XP.
     *
     * Returns null if Awesome XP is not active or tokens module is not loaded.
     *
     * @return AWX_Token_Defaults|null
     */
    private function get_defaults() {
        if ( $this->defaults !== null ) {
            return $this->defaults;
        }

        // Check if Awesome XP's token API is available
        if ( ! function_exists( 'awx_get_token_defaults' ) ) {
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
    public function inject_tokens( $theme_json ) {
        $defaults = $this->get_defaults();

        // Graceful degradation: if Awesome XP is not active, theme.json stays as-is
        if ( ! $defaults ) {
            return $theme_json;
        }

        $tokens = $defaults->get_all();
        if ( empty( $tokens ) ) {
            return $theme_json;
        }

        $new_data = [
            'version'  => 3,
            'settings' => [],
        ];

        $new_data['settings']['color']      = $this->build_color( $tokens, $defaults );
        $new_data['settings']['typography']  = $this->build_typography( $tokens );
        $new_data['settings']['spacing']     = $this->build_spacing( $tokens );
        $new_data['settings']['shadow']      = $this->build_shadows( $tokens );
        $new_data['settings']['custom']      = $this->build_custom( $tokens );

        if ( ! empty( $tokens['layout'] ) ) {
            $new_data['settings']['layout'] = [
                'contentSize' => $tokens['layout']['contentSize'] ?? '48rem',
                'wideSize'    => $tokens['layout']['wideSize'] ?? '72rem',
            ];
        }

        $theme_json->update_with( $new_data );

        return $theme_json;
    }

    /**
     * Build color palette from role tokens.
     *
     * Resolves each role ref (e.g. "brand-a.600") to hex for WordPress.
     */
    private function build_color( $tokens, $defaults ) {
        $palette = [];

        if ( ! empty( $tokens['color']['roles']['light'] ) ) {
            foreach ( $tokens['color']['roles']['light'] as $slug => $role ) {
                $ref  = $role['ref'] ?? '';
                $name = $role['name'] ?? $slug;
                $hex  = $defaults->resolve_color_ref_to_hex( $ref );

                $palette[] = [
                    'slug'  => 'awx-' . $slug,
                    'color' => $hex,
                    'name'  => $name,
                ];
            }
        }

        return [
            'palette'          => $palette,
            'defaultPalette'   => false,
            'defaultGradients' => false,
        ];
    }

    /**
     * Build typography settings.
     */
    private function build_typography( $tokens ) {
        $result = [
            'defaultFontSizes' => false,
        ];

        // Font families
        if ( ! empty( $tokens['fontFamily'] ) ) {
            $families = [];
            foreach ( $tokens['fontFamily'] as $slug => $family ) {
                $families[] = [
                    'slug'       => 'awx-' . $slug,
                    'name'       => $family['name'] ?? $slug,
                    'fontFamily' => $family['value'],
                ];
            }
            $result['fontFamilies'] = $families;
        }

        // Font sizes with fluid support
        if ( ! empty( $tokens['fontSize'] ) ) {
            $sizes = [];
            foreach ( $tokens['fontSize'] as $slug => $size ) {
                $entry = [
                    'slug' => 'awx-' . $slug,
                    'size' => $size['value'],
                    'name' => $size['name'] ?? 'Size ' . $slug,
                ];

                if ( ! empty( $size['fluid'] ) ) {
                    $entry['fluid'] = [
                        'min' => $size['fluid']['min'],
                        'max' => $size['fluid']['max'],
                    ];
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
     * Build spacing sizes.
     */
    private function build_spacing( $tokens ) {
        $sizes = [];

        if ( ! empty( $tokens['space'] ) ) {
            foreach ( $tokens['space'] as $slug => $space ) {
                $sizes[] = [
                    'slug' => 'awx-' . $slug,
                    'size' => $space['value'],
                    'name' => $space['name'] ?? 'Space ' . $slug,
                ];
            }
        }

        return [
            'spacingScale' => [ 'steps' => 0 ],
            'spacingSizes' => $sizes,
            'units'        => [ '%', 'px', 'em', 'rem', 'vh', 'vw' ],
        ];
    }

    /**
     * Build shadow presets.
     */
    private function build_shadows( $tokens ) {
        $presets = [];
        $names   = [
            '1' => 'Subtle', '2' => 'Raised', '3' => 'Elevated',
            '4' => 'Floating', '5' => 'High',
        ];

        if ( ! empty( $tokens['shadow'] ) ) {
            foreach ( $tokens['shadow'] as $slug => $value ) {
                if ( $slug === 'inner' || $slug === 'none' ) continue;
                $val = is_array( $value ) ? ( $value['value'] ?? '' ) : $value;
                $presets[] = [
                    'slug'   => 'awx-' . $slug,
                    'name'   => $names[ $slug ] ?? 'Shadow ' . $slug,
                    'shadow' => $val,
                ];
            }
        }

        return [
            'defaultPresets' => false,
            'presets'        => $presets,
        ];
    }

    /**
     * Build custom properties (--wp--custom--*).
     *
     * These are module-managed tokens exposed via theme.json's custom section
     * so they're available as var(--wp--custom--*) in theme.json styles.
     */
    private function build_custom( $tokens ) {
        $custom = [];

        // Font weights
        if ( ! empty( $tokens['fontWeight'] ) ) {
            $custom['fontWeight'] = [];
            foreach ( $tokens['fontWeight'] as $slug => $value ) {
                $val = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
                $custom['fontWeight'][ $slug ] = (int) $val;
            }
        }

        // Line heights
        if ( ! empty( $tokens['leading'] ) ) {
            $custom['lineHeight'] = [];
            foreach ( $tokens['leading'] as $slug => $value ) {
                $val = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
                $custom['lineHeight'][ $slug ] = (float) $val;
            }
        }

        // Letter spacing
        if ( ! empty( $tokens['tracking'] ) ) {
            $custom['letterSpacing'] = [];
            foreach ( $tokens['tracking'] as $slug => $value ) {
                $val = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
                $custom['letterSpacing'][ $slug ] = $val;
            }
        }

        // Radius
        if ( ! empty( $tokens['radius'] ) ) {
            $custom['radius'] = [];
            foreach ( $tokens['radius'] as $slug => $value ) {
                $custom['radius'][ $slug ] = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
            }
        }

        // Border widths
        if ( ! empty( $tokens['border'] ) ) {
            $custom['borderWidth'] = [];
            foreach ( $tokens['border'] as $slug => $value ) {
                $custom['borderWidth'][ $slug ] = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
            }
        }

        // Duration
        if ( ! empty( $tokens['duration'] ) ) {
            $custom['duration'] = [];
            foreach ( $tokens['duration'] as $slug => $value ) {
                $custom['duration'][ $slug ] = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
            }
        }

        // Easing
        if ( ! empty( $tokens['ease'] ) ) {
            $custom['ease'] = [];
            foreach ( $tokens['ease'] as $slug => $value ) {
                $custom['ease'][ $slug ] = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
            }
        }

        // Z-index
        if ( ! empty( $tokens['z'] ) ) {
            $custom['zIndex'] = [];
            foreach ( $tokens['z'] as $slug => $value ) {
                $val = is_array( $value ) ? ( $value['value'] ?? $value ) : $value;
                $custom['zIndex'][ $slug ] = (int) $val;
            }
        }

        // Measure
        if ( ! empty( $tokens['width'] ) ) {
            $custom['measure'] = [];
            foreach ( [ 'measure', 'measure-wide', 'measure-narrow' ] as $key ) {
                if ( isset( $tokens['width'][ $key ] ) ) {
                    $val = is_array( $tokens['width'][ $key ] ) ? $tokens['width'][ $key ]['value'] : $tokens['width'][ $key ];
                    // Convert "measure-wide" to "wide" for cleaner --wp--custom--measure--wide
                    $custom_key = str_replace( 'measure-', '', $key );
                    if ( $custom_key === 'measure' ) $custom_key = 'default';
                    $custom['measure'][ $custom_key ] = $val;
                }
            }
        }

        return $custom;
    }
}
