<?php
/**
 * Theme Customizer
 *
 * @package Caniincasa
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add postMessage support for site title and description
 */
function caniincasa_customize_register( $wp_customize ) {

    $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

    /**
     * Colors Section
     */
    $wp_customize->add_section( 'caniincasa_colors', array(
        'title'    => __( 'Colori Tema', 'caniincasa' ),
        'priority' => 40,
    ) );

    // Primary Color
    $wp_customize->add_setting( 'caniincasa_primary_color', array(
        'default'           => '#FFCC70',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'caniincasa_primary_color', array(
        'label'    => __( 'Colore Primario', 'caniincasa' ),
        'section'  => 'caniincasa_colors',
        'settings' => 'caniincasa_primary_color',
    ) ) );

    // Secondary Color
    $wp_customize->add_setting( 'caniincasa_secondary_color', array(
        'default'           => '#4d3319',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'caniincasa_secondary_color', array(
        'label'    => __( 'Colore Secondario', 'caniincasa' ),
        'section'  => 'caniincasa_colors',
        'settings' => 'caniincasa_secondary_color',
    ) ) );

    // Accent Color
    $wp_customize->add_setting( 'caniincasa_accent_color', array(
        'default'           => '#FF9F40',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'caniincasa_accent_color', array(
        'label'    => __( 'Colore Accent', 'caniincasa' ),
        'section'  => 'caniincasa_colors',
        'settings' => 'caniincasa_accent_color',
    ) ) );

    /**
     * Typography Section
     */
    $wp_customize->add_section( 'caniincasa_typography', array(
        'title'    => __( 'Tipografia', 'caniincasa' ),
        'priority' => 41,
    ) );

    // Primary Font
    $wp_customize->add_setting( 'caniincasa_primary_font', array(
        'default'           => 'Open Sans',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'caniincasa_primary_font', array(
        'label'    => __( 'Font Primario', 'caniincasa' ),
        'section'  => 'caniincasa_typography',
        'type'     => 'select',
        'choices'  => caniincasa_get_google_fonts(),
    ) );

    // Secondary Font (Headings)
    $wp_customize->add_setting( 'caniincasa_secondary_font', array(
        'default'           => 'Baloo 2',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'caniincasa_secondary_font', array(
        'label'    => __( 'Font Secondario (Titoli)', 'caniincasa' ),
        'section'  => 'caniincasa_typography',
        'type'     => 'select',
        'choices'  => caniincasa_get_google_fonts(),
    ) );

    // Base Font Size
    $wp_customize->add_setting( 'caniincasa_base_font_size', array(
        'default'           => '16',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'caniincasa_base_font_size', array(
        'label'       => __( 'Dimensione Font Base (px)', 'caniincasa' ),
        'section'     => 'caniincasa_typography',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 14,
            'max'  => 20,
            'step' => 1,
        ),
    ) );

    /**
     * Layout Section
     */
    $wp_customize->add_section( 'caniincasa_layout', array(
        'title'    => __( 'Layout', 'caniincasa' ),
        'priority' => 42,
    ) );

    // Layout Type
    $wp_customize->add_setting( 'caniincasa_layout_type', array(
        'default'           => 'full-width',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'caniincasa_layout_type', array(
        'label'    => __( 'Tipo Layout', 'caniincasa' ),
        'section'  => 'caniincasa_layout',
        'type'     => 'radio',
        'choices'  => array(
            'full-width' => __( 'Full Width', 'caniincasa' ),
            'boxed'      => __( 'Boxed', 'caniincasa' ),
        ),
    ) );

    // Container Max Width
    $wp_customize->add_setting( 'caniincasa_container_width', array(
        'default'           => '1280',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'caniincasa_container_width', array(
        'label'       => __( 'Larghezza Massima Container (px)', 'caniincasa' ),
        'section'     => 'caniincasa_layout',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 1024,
            'max'  => 1920,
            'step' => 10,
        ),
    ) );

    /**
     * Dark Mode Section
     */
    $wp_customize->add_section( 'caniincasa_dark_mode', array(
        'title'    => __( 'Dark Mode', 'caniincasa' ),
        'priority' => 43,
    ) );

    // Enable Dark Mode
    $wp_customize->add_setting( 'caniincasa_enable_dark_mode', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'caniincasa_enable_dark_mode', array(
        'label'    => __( 'Abilita Dark Mode', 'caniincasa' ),
        'section'  => 'caniincasa_dark_mode',
        'type'     => 'checkbox',
    ) );

    /**
     * Custom Labels Section
     */
    $wp_customize->add_section( 'caniincasa_labels', array(
        'title'    => __( 'Etichette Personalizzate', 'caniincasa' ),
        'priority' => 44,
    ) );

    // CTA Button Text
    $wp_customize->add_setting( 'caniincasa_cta_text', array(
        'default'           => __( 'Scopri di più', 'caniincasa' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'caniincasa_cta_text', array(
        'label'    => __( 'Testo CTA Principale', 'caniincasa' ),
        'section'  => 'caniincasa_labels',
        'type'     => 'text',
    ) );

    // Read More Text
    $wp_customize->add_setting( 'caniincasa_read_more_text', array(
        'default'           => __( 'Leggi tutto', 'caniincasa' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'caniincasa_read_more_text', array(
        'label'    => __( 'Testo "Leggi tutto"', 'caniincasa' ),
        'section'  => 'caniincasa_labels',
        'type'     => 'text',
    ) );

}
add_action( 'customize_register', 'caniincasa_customize_register' );

/**
 * Google Fonts List (30+ fonts)
 */
function caniincasa_get_google_fonts() {
    return array(
        'Open Sans'       => 'Open Sans',
        'Roboto'          => 'Roboto',
        'Lato'            => 'Lato',
        'Montserrat'      => 'Montserrat',
        'Poppins'         => 'Poppins',
        'Raleway'         => 'Raleway',
        'Nunito'          => 'Nunito',
        'Baloo 2'         => 'Baloo 2',
        'Playfair Display'=> 'Playfair Display',
        'Merriweather'    => 'Merriweather',
        'Ubuntu'          => 'Ubuntu',
        'PT Sans'         => 'PT Sans',
        'Source Sans Pro' => 'Source Sans Pro',
        'Oswald'          => 'Oswald',
        'Quicksand'       => 'Quicksand',
        'Work Sans'       => 'Work Sans',
        'Inter'           => 'Inter',
        'Rubik'           => 'Rubik',
        'Mukta'           => 'Mukta',
        'Karla'           => 'Karla',
        'Barlow'          => 'Barlow',
        'Nunito Sans'     => 'Nunito Sans',
        'Josefin Sans'    => 'Josefin Sans',
        'DM Sans'         => 'DM Sans',
        'Archivo'         => 'Archivo',
        'IBM Plex Sans'   => 'IBM Plex Sans',
        'Manrope'         => 'Manrope',
        'Hind'            => 'Hind',
        'Cabin'           => 'Cabin',
        'Bitter'          => 'Bitter',
        'Arimo'           => 'Arimo',
        'Oxygen'          => 'Oxygen',
    );
}

/**
 * Output custom CSS based on Customizer settings
 */
function caniincasa_customizer_css() {
    $primary_color   = get_theme_mod( 'caniincasa_primary_color', '#FFCC70' );
    $secondary_color = get_theme_mod( 'caniincasa_secondary_color', '#4d3319' );
    $accent_color    = get_theme_mod( 'caniincasa_accent_color', '#FF9F40' );
    $container_width = get_theme_mod( 'caniincasa_container_width', '1280' );
    $base_font_size  = get_theme_mod( 'caniincasa_base_font_size', '16' );

    ?>
    <style type="text/css">
        :root {
            --color-primary: <?php echo esc_attr( $primary_color ); ?>;
            --color-secondary: <?php echo esc_attr( $secondary_color ); ?>;
            --color-accent: <?php echo esc_attr( $accent_color ); ?>;
            --container-max-width: <?php echo esc_attr( $container_width ); ?>px;
        }
        body {
            font-size: <?php echo esc_attr( $base_font_size ); ?>px;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'caniincasa_customizer_css' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously
 */
function caniincasa_customize_preview_js() {
    wp_enqueue_script( 'caniincasa-customizer', CANIINCASA_THEME_URI . '/assets/js/customizer.js', array( 'customize-preview' ), CANIINCASA_VERSION, true );
}
add_action( 'customize_preview_init', 'caniincasa_customize_preview_js' );
