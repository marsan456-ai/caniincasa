<?php
/**
 * Caniincasa Theme Functions
 *
 * @package Caniincasa
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Include Customizer
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Include GDPR Disclaimers
 */
require get_template_directory() . '/inc/gdpr-disclaimers.php';

/**
 * Enqueue theme styles and scripts
 */
function caniincasa_enqueue_assets() {
    // Header center menu styles (always load)
    wp_enqueue_style(
        'caniincasa-header-center',
        get_template_directory_uri() . '/assets/css/header-center-menu.css',
        array(),
        '1.0.0'
    );

    // Dropdown menu styles (always load)
    wp_enqueue_style(
        'caniincasa-dropdown-menu',
        get_template_directory_uri() . '/assets/css/dropdown-menu.css',
        array(),
        '1.0.0'
    );

    // Dropdown menu script (always load)
    wp_enqueue_script(
        'caniincasa-dropdown-menu',
        get_template_directory_uri() . '/assets/js/dropdown-menu.js',
        array(),
        '1.0.0',
        true
    );

    // Chi Siamo styles
    if ( is_page_template( 'template-chi-siamo.php' ) ) {
        wp_enqueue_style(
            'caniincasa-chi-siamo',
            get_template_directory_uri() . '/assets/css/chi-siamo.css',
            array(),
            '1.0.0'
        );
    }

    // Contatti styles
    if ( is_page_template( 'template-contatti.php' ) ) {
        wp_enqueue_style(
            'caniincasa-contatti',
            get_template_directory_uri() . '/assets/css/contatti.css',
            array(),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'caniincasa_enqueue_assets' );
