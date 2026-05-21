<?php
/**
 * Monomyth FSE Customizer
 *
 * @package Monomyth_FSE
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Customizer settings
 *
 * Note: With FSE themes, most customization is done through the Site Editor.
 * This file is included for backward compatibility and any non-FSE customizations.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function monomyth_customize_register( $wp_customize ) {
    
    // Add Monomyth Panel
    $wp_customize->add_panel( 'monomyth_options', array(
        'title'       => __( 'Monomyth Options', 'monomyth-fse' ),
        'description' => __( 'Additional theme options for Monomyth FSE.', 'monomyth-fse' ),
        'priority'    => 160,
    ) );

    // Add Performance Section
    $wp_customize->add_section( 'monomyth_performance', array(
        'title'       => __( 'Performance', 'monomyth-fse' ),
        'description' => __( 'Performance-related settings.', 'monomyth-fse' ),
        'panel'       => 'monomyth_options',
        'priority'    => 10,
    ) );

    // Preload critical assets
    $wp_customize->add_setting( 'monomyth_preload_assets', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'monomyth_preload_assets', array(
        'label'       => __( 'Preload Critical Assets', 'monomyth-fse' ),
        'description' => __( 'Enable preloading of critical CSS and fonts.', 'monomyth-fse' ),
        'section'     => 'monomyth_performance',
        'type'        => 'checkbox',
    ) );

    // Add Awesome Enterprise Section
    $wp_customize->add_section( 'monomyth_awesome_enterprise', array(
        'title'       => __( 'Awesome Enterprise', 'monomyth-fse' ),
        'description' => __( 'Settings for Awesome Enterprise integration.', 'monomyth-fse' ),
        'panel'       => 'monomyth_options',
        'priority'    => 20,
    ) );

    // Enable SPA mode
    $wp_customize->add_setting( 'monomyth_spa_mode', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'monomyth_spa_mode', array(
        'label'       => __( 'Enable SPA Mode', 'monomyth-fse' ),
        'description' => __( 'Enable Single Page Application mode for Awesome Enterprise.', 'monomyth-fse' ),
        'section'     => 'monomyth_awesome_enterprise',
        'type'        => 'checkbox',
    ) );

}
add_action( 'customize_register', 'monomyth_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function monomyth_customize_partial_blogname() {
    bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function monomyth_customize_partial_blogdescription() {
    bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function monomyth_customize_preview_js() {
    wp_enqueue_script(
        'monomyth-customizer-preview',
        MONOMYTH_URI . '/assets/js/customizer-preview.js',
        array( 'customize-preview' ),
        MONOMYTH_VERSION,
        true
    );
}
// add_action( 'customize_preview_init', 'monomyth_customize_preview_js' );
