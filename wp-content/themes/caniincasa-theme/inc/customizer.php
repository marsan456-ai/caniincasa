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

    /**
     * Homepage Settings Panel
     */
    $wp_customize->add_panel( 'homepage_settings', array(
        'title'    => __( 'Impostazioni Homepage', 'caniincasa' ),
        'priority' => 30,
    ) );

    /**
     * Hero Section
     */
    $wp_customize->add_section( 'hero_section', array(
        'title'    => __( 'Hero Section', 'caniincasa' ),
        'panel'    => 'homepage_settings',
        'priority' => 10,
    ) );

    // Hero Background Image
    $wp_customize->add_setting( 'hero_background_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image', array(
        'label'    => __( 'Immagine di Sfondo Hero', 'caniincasa' ),
        'section'  => 'hero_section',
        'settings' => 'hero_background_image',
    ) ) );

    // Hero Overlay Color
    $wp_customize->add_setting( 'hero_overlay_color', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hero_overlay_color', array(
        'label'    => __( 'Colore Overlay Hero', 'caniincasa' ),
        'section'  => 'hero_section',
        'settings' => 'hero_overlay_color',
    ) ) );

    // Hero Overlay Opacity
    $wp_customize->add_setting( 'hero_overlay_opacity', array(
        'default'           => '0.6',
        'sanitize_callback' => 'caniincasa_sanitize_float',
    ) );
    $wp_customize->add_control( 'hero_overlay_opacity', array(
        'label'       => __( 'Opacità Overlay (0-1)', 'caniincasa' ),
        'description' => __( 'Inserisci un valore tra 0 (trasparente) e 1 (opaco)', 'caniincasa' ),
        'section'     => 'hero_section',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 0,
            'max'  => 1,
            'step' => 0.1,
        ),
    ) );

    // Hero Title
    $wp_customize->add_setting( 'hero_title', array(
        'default'           => 'Il tuo portale cinofilo di riferimento',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_title', array(
        'label'    => __( 'Titolo Hero', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'text',
    ) );

    // Hero Subtitle
    $wp_customize->add_setting( 'hero_subtitle', array(
        'default'           => 'Scopri razze, trova allevamenti, adotta un amico a quattro zampe',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_subtitle', array(
        'label'    => __( 'Sottotitolo Hero', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'textarea',
    ) );

    // Hero Button 1 Text
    $wp_customize->add_setting( 'hero_button1_text', array(
        'default'           => 'Esplora le Razze',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_button1_text', array(
        'label'    => __( 'Testo Pulsante 1', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'text',
    ) );

    // Hero Button 1 URL
    $wp_customize->add_setting( 'hero_button1_url', array(
        'default'           => '/razze-di-cani/',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'hero_button1_url', array(
        'label'    => __( 'Link Pulsante 1', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'url',
    ) );

    // Hero Button 2 Text
    $wp_customize->add_setting( 'hero_button2_text', array(
        'default'           => 'Vedi Annunci',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_button2_text', array(
        'label'    => __( 'Testo Pulsante 2', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'text',
    ) );

    // Hero Button 2 URL
    $wp_customize->add_setting( 'hero_button2_url', array(
        'default'           => '/annunci/',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'hero_button2_url', array(
        'label'    => __( 'Link Pulsante 2', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'url',
    ) );

    // Hero Button 3 Text
    $wp_customize->add_setting( 'hero_button3_text', array(
        'default'           => 'Fai il Quiz',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'hero_button3_text', array(
        'label'    => __( 'Testo Pulsante 3', 'caniincasa' ),
        'section'  => 'hero_section',
        'type'     => 'text',
    ) );

    // Hero Button 3 URL
    $wp_customize->add_setting( 'hero_button3_url', array(
        'default'           => '#quiz-section',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'hero_button3_url', array(
        'label'       => __( 'Link Pulsante 3', 'caniincasa' ),
        'description' => __( 'Usa #quiz-section per scroll smooth alla sezione quiz', 'caniincasa' ),
        'section'     => 'hero_section',
        'type'        => 'text',
    ) );

    // Hero Background Carousel - Additional Images
    $wp_customize->add_setting( 'hero_background_image_2', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image_2', array(
        'label'       => __( 'Immagine Sfondo 2 (Carosello)', 'caniincasa' ),
        'description' => __( 'Aggiungi immagini per creare un carosello di sfondo', 'caniincasa' ),
        'section'     => 'hero_section',
        'settings'    => 'hero_background_image_2',
    ) ) );

    $wp_customize->add_setting( 'hero_background_image_3', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image_3', array(
        'label'    => __( 'Immagine Sfondo 3 (Carosello)', 'caniincasa' ),
        'section'  => 'hero_section',
        'settings' => 'hero_background_image_3',
    ) ) );

    $wp_customize->add_setting( 'hero_background_image_4', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image_4', array(
        'label'    => __( 'Immagine Sfondo 4 (Carosello)', 'caniincasa' ),
        'section'  => 'hero_section',
        'settings' => 'hero_background_image_4',
    ) ) );

    $wp_customize->add_setting( 'hero_background_image_5', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image_5', array(
        'label'    => __( 'Immagine Sfondo 5 (Carosello)', 'caniincasa' ),
        'section'  => 'hero_section',
        'settings' => 'hero_background_image_5',
    ) ) );

    // Hero Carousel Speed
    $wp_customize->add_setting( 'hero_carousel_speed', array(
        'default'           => '5',
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'hero_carousel_speed', array(
        'label'       => __( 'Velocità Carosello (secondi)', 'caniincasa' ),
        'description' => __( 'Tempo di visualizzazione di ogni immagine (3-15 secondi)', 'caniincasa' ),
        'section'     => 'hero_section',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 3,
            'max'  => 15,
            'step' => 1,
        ),
    ) );

    /**
     * Annunci Section
     */
    $wp_customize->add_section( 'annunci_section', array(
        'title'    => __( 'Sezione Annunci', 'caniincasa' ),
        'panel'    => 'homepage_settings',
        'priority' => 20,
    ) );

    // Annunci Section Title
    $wp_customize->add_setting( 'annunci_title', array(
        'default'           => 'Annunci Amici 4 Zampe',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'annunci_title', array(
        'label'    => __( 'Titolo Sezione', 'caniincasa' ),
        'section'  => 'annunci_section',
        'type'     => 'text',
    ) );

    // Annunci Section Subtitle
    $wp_customize->add_setting( 'annunci_subtitle', array(
        'default'           => 'Trova il tuo prossimo compagno di avventure',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'annunci_subtitle', array(
        'label'    => __( 'Sottotitolo Sezione', 'caniincasa' ),
        'section'  => 'annunci_section',
        'type'     => 'text',
    ) );

    /**
     * Razze Section
     */
    $wp_customize->add_section( 'razze_section', array(
        'title'    => __( 'Sezione Razze', 'caniincasa' ),
        'panel'    => 'homepage_settings',
        'priority' => 30,
    ) );

    // Razze Section Title
    $wp_customize->add_setting( 'razze_title', array(
        'default'           => 'Esplora le Razze di Cani',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'razze_title', array(
        'label'    => __( 'Titolo Sezione', 'caniincasa' ),
        'section'  => 'razze_section',
        'type'     => 'text',
    ) );

    // Razze Section Subtitle
    $wp_customize->add_setting( 'razze_subtitle', array(
        'default'           => 'Scopri caratteristiche, temperamento e curiosità di oltre 400 razze',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'razze_subtitle', array(
        'label'    => __( 'Sottotitolo Sezione', 'caniincasa' ),
        'section'  => 'razze_section',
        'type'     => 'textarea',
    ) );

    /**
     * Quiz Section
     */
    $wp_customize->add_section( 'quiz_section', array(
        'title'    => __( 'Sezione Quiz', 'caniincasa' ),
        'panel'    => 'homepage_settings',
        'priority' => 40,
    ) );

    // Quiz Title
    $wp_customize->add_setting( 'quiz_title', array(
        'default'           => 'Trova la Razza Perfetta per Te',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'quiz_title', array(
        'label'    => __( 'Titolo Quiz', 'caniincasa' ),
        'section'  => 'quiz_section',
        'type'     => 'text',
    ) );

    // Quiz Description
    $wp_customize->add_setting( 'quiz_description', array(
        'default'           => 'Rispondi a 9 semplici domande e scopri quali razze sono più compatibili con il tuo stile di vita. Il nostro algoritmo analizzerà le tue risposte e ti suggerirà le razze ideali.',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'quiz_description', array(
        'label'    => __( 'Descrizione Quiz', 'caniincasa' ),
        'section'  => 'quiz_section',
        'type'     => 'textarea',
    ) );

    // Quiz Button Text
    $wp_customize->add_setting( 'quiz_button_text', array(
        'default'           => 'Inizia il Quiz',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'quiz_button_text', array(
        'label'    => __( 'Testo Pulsante Quiz', 'caniincasa' ),
        'section'  => 'quiz_section',
        'type'     => 'text',
    ) );

    // Quiz Button URL
    $wp_customize->add_setting( 'quiz_button_url', array(
        'default'           => '/quiz-razza/',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'quiz_button_url', array(
        'label'    => __( 'Link Pulsante Quiz', 'caniincasa' ),
        'section'  => 'quiz_section',
        'type'     => 'url',
    ) );

    // Quiz Illustration Image
    $wp_customize->add_setting( 'quiz_illustration', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'quiz_illustration', array(
        'label'       => __( 'Immagine Quiz', 'caniincasa' ),
        'description' => __( 'Immagine affiancata alla descrizione del quiz', 'caniincasa' ),
        'section'     => 'quiz_section',
        'settings'    => 'quiz_illustration',
    ) ) );

    /**
     * CHI SIAMO PAGE PANEL
     */
    $wp_customize->add_panel( 'chi_siamo_panel', array(
        'title'    => __( 'Pagina Chi Siamo', 'caniincasa' ),
        'priority' => 50,
    ) );

    /**
     * Chi Siamo - Hero Section
     */
    $wp_customize->add_section( 'chi_siamo_hero', array(
        'title' => __( 'Hero Chi Siamo', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_title', array(
        'default'           => 'Chi Siamo',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'chi_siamo_title', array(
        'label'   => __( 'Titolo Pagina', 'caniincasa' ),
        'section' => 'chi_siamo_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_subtitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_subtitle', array(
        'label'   => __( 'Sottotitolo', 'caniincasa' ),
        'section' => 'chi_siamo_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'chi_siamo_hero_image', array(
        'label'   => __( 'Immagine Hero', 'caniincasa' ),
        'section' => 'chi_siamo_hero',
    ) ) );

    /**
     * Chi Siamo - Intro Section
     */
    $wp_customize->add_section( 'chi_siamo_intro', array(
        'title' => __( 'Sezione Intro', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_intro_title', array(
        'default'           => 'La Nostra Storia',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_intro_title', array(
        'label'   => __( 'Titolo Intro', 'caniincasa' ),
        'section' => 'chi_siamo_intro',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_intro_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'chi_siamo_intro_text', array(
        'label'   => __( 'Testo Intro', 'caniincasa' ),
        'section' => 'chi_siamo_intro',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'chi_siamo_intro_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'chi_siamo_intro_image', array(
        'label'   => __( 'Immagine Intro', 'caniincasa' ),
        'section' => 'chi_siamo_intro',
    ) ) );

    /**
     * Chi Siamo - Mission Section
     */
    $wp_customize->add_section( 'chi_siamo_mission', array(
        'title' => __( 'Sezione Missione', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_mission_title', array(
        'default'           => 'La Nostra Missione',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_mission_title', array(
        'label'   => __( 'Titolo Missione', 'caniincasa' ),
        'section' => 'chi_siamo_mission',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_mission_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'chi_siamo_mission_text', array(
        'label'   => __( 'Testo Missione', 'caniincasa' ),
        'section' => 'chi_siamo_mission',
        'type'    => 'textarea',
    ) );

    /**
     * Chi Siamo - Values Section
     */
    $wp_customize->add_section( 'chi_siamo_values', array(
        'title' => __( 'Sezione Valori', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_show_values', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'chi_siamo_show_values', array(
        'label'   => __( 'Mostra Sezione Valori', 'caniincasa' ),
        'section' => 'chi_siamo_values',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'chi_siamo_values_title', array(
        'default'           => 'I Nostri Valori',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_values_title', array(
        'label'   => __( 'Titolo Sezione', 'caniincasa' ),
        'section' => 'chi_siamo_values',
        'type'    => 'text',
    ) );

    // 4 Valori
    for ( $i = 1; $i <= 4; $i++ ) {
        $wp_customize->add_setting( "chi_siamo_value_{$i}_icon", array(
            'default'           => '⭐',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "chi_siamo_value_{$i}_icon", array(
            'label'   => sprintf( __( 'Valore %d - Icona (Emoji)', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_values',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "chi_siamo_value_{$i}_title", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "chi_siamo_value_{$i}_title", array(
            'label'   => sprintf( __( 'Valore %d - Titolo', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_values',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "chi_siamo_value_{$i}_text", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "chi_siamo_value_{$i}_text", array(
            'label'   => sprintf( __( 'Valore %d - Testo', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_values',
            'type'    => 'textarea',
        ) );
    }

    /**
     * Chi Siamo - Team Section
     */
    $wp_customize->add_section( 'chi_siamo_team', array(
        'title' => __( 'Sezione Team', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_show_team', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'chi_siamo_show_team', array(
        'label'   => __( 'Mostra Sezione Team', 'caniincasa' ),
        'section' => 'chi_siamo_team',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'chi_siamo_team_title', array(
        'default'           => 'Il Nostro Team',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_team_title', array(
        'label'   => __( 'Titolo Sezione', 'caniincasa' ),
        'section' => 'chi_siamo_team',
        'type'    => 'text',
    ) );

    // 6 Team Members
    for ( $i = 1; $i <= 6; $i++ ) {
        $wp_customize->add_setting( "chi_siamo_member_{$i}_name", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "chi_siamo_member_{$i}_name", array(
            'label'   => sprintf( __( 'Membro %d - Nome', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_team',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "chi_siamo_member_{$i}_role", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "chi_siamo_member_{$i}_role", array(
            'label'   => sprintf( __( 'Membro %d - Ruolo', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_team',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( "chi_siamo_member_{$i}_image", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "chi_siamo_member_{$i}_image", array(
            'label'   => sprintf( __( 'Membro %d - Foto', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_team',
        ) ) );

        $wp_customize->add_setting( "chi_siamo_member_{$i}_bio", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_textarea_field',
        ) );
        $wp_customize->add_control( "chi_siamo_member_{$i}_bio", array(
            'label'   => sprintf( __( 'Membro %d - Bio', 'caniincasa' ), $i ),
            'section' => 'chi_siamo_team',
            'type'    => 'textarea',
        ) );
    }

    /**
     * Chi Siamo - CTA Section
     */
    $wp_customize->add_section( 'chi_siamo_cta', array(
        'title' => __( 'Sezione CTA', 'caniincasa' ),
        'panel' => 'chi_siamo_panel',
    ) );

    $wp_customize->add_setting( 'chi_siamo_cta_title', array(
        'default'           => 'Unisciti a Noi',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_cta_title', array(
        'label'   => __( 'Titolo CTA', 'caniincasa' ),
        'section' => 'chi_siamo_cta',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_cta_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_cta_text', array(
        'label'   => __( 'Testo CTA', 'caniincasa' ),
        'section' => 'chi_siamo_cta',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'chi_siamo_cta_button_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'chi_siamo_cta_button_text', array(
        'label'   => __( 'Testo Pulsante', 'caniincasa' ),
        'section' => 'chi_siamo_cta',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'chi_siamo_cta_button_url', array(
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'chi_siamo_cta_button_url', array(
        'label'   => __( 'URL Pulsante', 'caniincasa' ),
        'section' => 'chi_siamo_cta',
        'type'    => 'url',
    ) );

    /**
     * CONTATTI PAGE PANEL
     */
    $wp_customize->add_panel( 'contatti_panel', array(
        'title'    => __( 'Pagina Contatti', 'caniincasa' ),
        'priority' => 51,
    ) );

    /**
     * Contatti - Hero Section
     */
    $wp_customize->add_section( 'contatti_hero', array(
        'title' => __( 'Hero Contatti', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_title', array(
        'default'           => 'Contatti',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_title', array(
        'label'   => __( 'Titolo Pagina', 'caniincasa' ),
        'section' => 'contatti_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_subtitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_subtitle', array(
        'label'   => __( 'Sottotitolo', 'caniincasa' ),
        'section' => 'contatti_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'contatti_hero_image', array(
        'label'   => __( 'Immagine Hero', 'caniincasa' ),
        'section' => 'contatti_hero',
    ) ) );

    /**
     * Contatti - Form Section
     */
    $wp_customize->add_section( 'contatti_form', array(
        'title' => __( 'Form Contatti', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_form_title', array(
        'default'           => 'Inviaci un Messaggio',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_form_title', array(
        'label'   => __( 'Titolo Form', 'caniincasa' ),
        'section' => 'contatti_form',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_form_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_form_text', array(
        'label'   => __( 'Testo Introduttivo Form', 'caniincasa' ),
        'section' => 'contatti_form',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'contatti_form_button_text', array(
        'default'           => 'Invia Messaggio',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_form_button_text', array(
        'label'   => __( 'Testo Pulsante', 'caniincasa' ),
        'section' => 'contatti_form',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_form_shortcode', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_form_shortcode', array(
        'label'       => __( 'Shortcode Contact Form 7 *', 'caniincasa' ),
        'description' => __( 'Inserisci lo shortcode di Contact Form 7 (es: [contact-form-7 id="123" title="Contatti"]). Crea prima un form in Contact Form 7, poi copia qui lo shortcode.', 'caniincasa' ),
        'section'     => 'contatti_form',
        'type'        => 'text',
        'input_attrs' => array(
            'placeholder' => '[contact-form-7 id="123"]',
        ),
    ) );

    /**
     * Contatti - Info Section
     */
    $wp_customize->add_section( 'contatti_info', array(
        'title' => __( 'Informazioni Contatto', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_show_info', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'contatti_show_info', array(
        'label'   => __( 'Mostra Informazioni', 'caniincasa' ),
        'section' => 'contatti_info',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'contatti_info_title', array(
        'default'           => 'Informazioni di Contatto',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_info_title', array(
        'label'   => __( 'Titolo Sezione', 'caniincasa' ),
        'section' => 'contatti_info',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_address', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );
    $wp_customize->add_control( 'contatti_address', array(
        'label'   => __( 'Indirizzo', 'caniincasa' ),
        'section' => 'contatti_info',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'contatti_phone', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_phone', array(
        'label'   => __( 'Telefono', 'caniincasa' ),
        'section' => 'contatti_info',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_email', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_control( 'contatti_email', array(
        'label'   => __( 'Email', 'caniincasa' ),
        'section' => 'contatti_info',
        'type'    => 'email',
    ) );

    $wp_customize->add_setting( 'contatti_whatsapp', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_whatsapp', array(
        'label'       => __( 'WhatsApp', 'caniincasa' ),
        'description' => __( 'Numero con prefisso internazionale (es: +39123456789)', 'caniincasa' ),
        'section'     => 'contatti_info',
        'type'        => 'text',
    ) );

    /**
     * Contatti - Hours Section
     */
    $wp_customize->add_section( 'contatti_hours', array(
        'title' => __( 'Orari di Apertura', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_show_hours', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'contatti_show_hours', array(
        'label'   => __( 'Mostra Orari', 'caniincasa' ),
        'section' => 'contatti_hours',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'contatti_hours_title', array(
        'default'           => 'Orari di Apertura',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_hours_title', array(
        'label'   => __( 'Titolo Orari', 'caniincasa' ),
        'section' => 'contatti_hours',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_hours_text', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'contatti_hours_text', array(
        'label'   => __( 'Orari (HTML permesso)', 'caniincasa' ),
        'section' => 'contatti_hours',
        'type'    => 'textarea',
    ) );

    /**
     * Contatti - Social Section
     */
    $wp_customize->add_section( 'contatti_social', array(
        'title' => __( 'Social Media', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_show_social', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'contatti_show_social', array(
        'label'   => __( 'Mostra Social Media', 'caniincasa' ),
        'section' => 'contatti_social',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'contatti_social_title', array(
        'default'           => 'Seguici sui Social',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_social_title', array(
        'label'   => __( 'Titolo Sezione', 'caniincasa' ),
        'section' => 'contatti_social',
        'type'    => 'text',
    ) );

    $social_networks = array( 'facebook', 'instagram', 'twitter', 'youtube' );
    foreach ( $social_networks as $network ) {
        $wp_customize->add_setting( "contatti_social_{$network}", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( "contatti_social_{$network}", array(
            'label'   => sprintf( __( 'URL %s', 'caniincasa' ), ucfirst( $network ) ),
            'section' => 'contatti_social',
            'type'    => 'url',
        ) );
    }

    /**
     * Contatti - Map Section
     */
    $wp_customize->add_section( 'contatti_map', array(
        'title' => __( 'Mappa', 'caniincasa' ),
        'panel' => 'contatti_panel',
    ) );

    $wp_customize->add_setting( 'contatti_show_map', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );
    $wp_customize->add_control( 'contatti_show_map', array(
        'label'   => __( 'Mostra Mappa', 'caniincasa' ),
        'section' => 'contatti_map',
        'type'    => 'checkbox',
    ) );

    $wp_customize->add_setting( 'contatti_map_title', array(
        'default'           => 'Dove Siamo',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'contatti_map_title', array(
        'label'   => __( 'Titolo Mappa', 'caniincasa' ),
        'section' => 'contatti_map',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'contatti_map_embed', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'contatti_map_embed', array(
        'label'       => __( 'Codice Embed Mappa', 'caniincasa' ),
        'description' => __( 'Incolla il codice iframe di Google Maps', 'caniincasa' ),
        'section'     => 'contatti_map',
        'type'        => 'textarea',
    ) );

}
add_action( 'customize_register', 'caniincasa_customize_register' );

/**
 * Sanitize float value
 */
function caniincasa_sanitize_float( $value ) {
    return floatval( $value );
}

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

    // Hero section customizer values
    $hero_bg_image     = get_theme_mod( 'hero_background_image' );
    $hero_overlay_color = get_theme_mod( 'hero_overlay_color', '#000000' );
    $hero_overlay_opacity = get_theme_mod( 'hero_overlay_opacity', '0.6' );

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
        <?php if ( $hero_bg_image ) : ?>
        .hero-section {
            background-image: url(<?php echo esc_url( $hero_bg_image ); ?>);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        <?php endif; ?>
        <?php if ( $hero_overlay_color && $hero_overlay_opacity ) : ?>
        .hero-overlay {
            background-color: <?php echo esc_attr( $hero_overlay_color ); ?>;
            opacity: <?php echo esc_attr( $hero_overlay_opacity ); ?>;
        }
        <?php endif; ?>
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
