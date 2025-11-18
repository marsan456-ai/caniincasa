<?php
/**
 * Theme Customizer
 *
 * @package Caniincasa
 */

/**
 * Add postMessage support for site title and description.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function caniincasa_customize_register( $wp_customize ) {
    // Chi Siamo Page Section
    $wp_customize->add_section(
        'chi_siamo_section',
        array(
            'title'    => __( 'Pagina Chi Siamo', 'caniincasa' ),
            'priority' => 130,
        )
    );

    // Chi Siamo - Hero
    $wp_customize->add_setting(
        'chi_siamo_hero_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'chi_siamo_hero_image',
            array(
                'label'    => __( 'Immagine Hero', 'caniincasa' ),
                'section'  => 'chi_siamo_section',
                'settings' => 'chi_siamo_hero_image',
            )
        )
    );

    $wp_customize->add_setting(
        'chi_siamo_title',
        array(
            'default'           => 'Chi Siamo',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_title',
        array(
            'label'    => __( 'Titolo Pagina', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'text',
        )
    );

    $wp_customize->add_setting(
        'chi_siamo_subtitle',
        array(
            'default'           => 'La nostra storia, la nostra passione per i cani',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_subtitle',
        array(
            'label'    => __( 'Sottotitolo', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'text',
        )
    );

    // Chi Siamo - Intro
    $wp_customize->add_setting(
        'chi_siamo_intro_text',
        array(
            'default'           => 'Benvenuti su Caniincasa, il punto di riferimento per tutti gli amanti dei cani in Italia.',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_intro_text',
        array(
            'label'    => __( 'Testo Introduttivo', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'textarea',
        )
    );

    // Chi Siamo - Mission
    $wp_customize->add_setting(
        'chi_siamo_mission_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'chi_siamo_mission_image',
            array(
                'label'    => __( 'Immagine Missione', 'caniincasa' ),
                'section'  => 'chi_siamo_section',
                'settings' => 'chi_siamo_mission_image',
            )
        )
    );

    $wp_customize->add_setting(
        'chi_siamo_mission_title',
        array(
            'default'           => 'La Nostra Missione',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_mission_title',
        array(
            'label'    => __( 'Titolo Missione', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'text',
        )
    );

    $wp_customize->add_setting(
        'chi_siamo_mission_text',
        array(
            'default'           => 'La nostra missione è quella di creare una community unita dalla passione per i cani.',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_mission_text',
        array(
            'label'    => __( 'Testo Missione', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'textarea',
        )
    );

    // Chi Siamo - Values
    $wp_customize->add_setting(
        'chi_siamo_values_title',
        array(
            'default'           => 'I Nostri Valori',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'chi_siamo_values_title',
        array(
            'label'    => __( 'Titolo Valori', 'caniincasa' ),
            'section'  => 'chi_siamo_section',
            'type'     => 'text',
        )
    );

    // Valore 1
    $wp_customize->add_setting( 'chi_siamo_value_1_icon', array( 'default' => '❤️', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_1_icon', array( 'label' => __( 'Valore 1 - Icona (emoji)', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_1_title', array( 'default' => 'Passione', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_1_title', array( 'label' => __( 'Valore 1 - Titolo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_1_text', array( 'default' => 'Amiamo i cani e lavoriamo ogni giorno per il loro benessere.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_1_text', array( 'label' => __( 'Valore 1 - Testo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'textarea' ) );

    // Valore 2
    $wp_customize->add_setting( 'chi_siamo_value_2_icon', array( 'default' => '🤝', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_2_icon', array( 'label' => __( 'Valore 2 - Icona (emoji)', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_2_title', array( 'default' => 'Comunità', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_2_title', array( 'label' => __( 'Valore 2 - Titolo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_2_text', array( 'default' => 'Crediamo nella forza della community e nella condivisione.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_2_text', array( 'label' => __( 'Valore 2 - Testo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'textarea' ) );

    // Valore 3
    $wp_customize->add_setting( 'chi_siamo_value_3_icon', array( 'default' => '🎯', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_3_icon', array( 'label' => __( 'Valore 3 - Icona (emoji)', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_3_title', array( 'default' => 'Qualità', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_3_title', array( 'label' => __( 'Valore 3 - Titolo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_3_text', array( 'default' => 'Offriamo solo informazioni verificate e servizi di qualità.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_3_text', array( 'label' => __( 'Valore 3 - Testo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'textarea' ) );

    // Valore 4
    $wp_customize->add_setting( 'chi_siamo_value_4_icon', array( 'default' => '🌟', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_4_icon', array( 'label' => __( 'Valore 4 - Icona (emoji)', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_4_title', array( 'default' => 'Innovazione', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_4_title', array( 'label' => __( 'Valore 4 - Titolo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_value_4_text', array( 'default' => 'Utilizziamo la tecnologia per migliorare la vita dei cani e dei loro proprietari.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_value_4_text', array( 'label' => __( 'Valore 4 - Testo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'textarea' ) );

    // Chi Siamo - CTA
    $wp_customize->add_setting( 'chi_siamo_cta_enabled', array( 'default' => true, 'sanitize_callback' => 'absint' ) );
    $wp_customize->add_control( 'chi_siamo_cta_enabled', array( 'label' => __( 'Abilita CTA', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'checkbox' ) );

    $wp_customize->add_setting( 'chi_siamo_cta_title', array( 'default' => 'Unisciti alla Nostra Community', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_cta_title', array( 'label' => __( 'CTA - Titolo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_cta_text', array( 'default' => 'Registrati oggi e scopri tutti i servizi dedicati a te e al tuo cane.', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_cta_text', array( 'label' => __( 'CTA - Testo', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'textarea' ) );

    $wp_customize->add_setting( 'chi_siamo_cta_button_text', array( 'default' => 'Registrati Ora', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'chi_siamo_cta_button_text', array( 'label' => __( 'CTA - Testo Pulsante', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'chi_siamo_cta_button_url', array( 'default' => home_url( '/registrazione/' ), 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'chi_siamo_cta_button_url', array( 'label' => __( 'CTA - URL Pulsante', 'caniincasa' ), 'section' => 'chi_siamo_section', 'type' => 'url' ) );

    // ===============================================
    // CONTATTI PAGE SECTION
    // ===============================================

    $wp_customize->add_section(
        'contatti_section',
        array(
            'title'    => __( 'Pagina Contatti', 'caniincasa' ),
            'priority' => 131,
        )
    );

    // Contatti - Hero
    $wp_customize->add_setting( 'contatti_hero_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'contatti_hero_image', array( 'label' => __( 'Immagine Hero', 'caniincasa' ), 'section' => 'contatti_section', 'settings' => 'contatti_hero_image' ) ) );

    $wp_customize->add_setting( 'contatti_title', array( 'default' => 'Contatti', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_title', array( 'label' => __( 'Titolo Pagina', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'contatti_subtitle', array( 'default' => 'Siamo qui per aiutarti', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_subtitle', array( 'label' => __( 'Sottotitolo', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'text' ) );

    // Contatti - Info
    $wp_customize->add_setting( 'contatti_info_title', array( 'default' => 'Informazioni di Contatto', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_info_title', array( 'label' => __( 'Titolo Info', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'contatti_intro_text', array( 'default' => 'Hai domande o suggerimenti? Contattaci!', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_intro_text', array( 'label' => __( 'Testo Introduttivo', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'textarea' ) );

    $wp_customize->add_setting( 'contatti_email', array( 'default' => get_option( 'admin_email' ), 'sanitize_callback' => 'sanitize_email' ) );
    $wp_customize->add_control( 'contatti_email', array( 'label' => __( 'Email', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'email' ) );

    $wp_customize->add_setting( 'contatti_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_phone', array( 'label' => __( 'Telefono', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'text' ) );

    $wp_customize->add_setting( 'contatti_address', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
    $wp_customize->add_control( 'contatti_address', array( 'label' => __( 'Indirizzo', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'textarea' ) );

    $wp_customize->add_setting( 'contatti_hours', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
    $wp_customize->add_control( 'contatti_hours', array( 'label' => __( 'Orari', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'textarea' ) );

    // Contatti - Social
    $wp_customize->add_setting( 'contatti_show_social', array( 'default' => true, 'sanitize_callback' => 'absint' ) );
    $wp_customize->add_control( 'contatti_show_social', array( 'label' => __( 'Mostra Social Media', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'checkbox' ) );

    // Contatti - Form
    $wp_customize->add_setting( 'contatti_form_title', array( 'default' => 'Inviaci un Messaggio', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'contatti_form_title', array( 'label' => __( 'Titolo Form', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'text' ) );

    // Contatti - Map
    $wp_customize->add_setting( 'contatti_map_enabled', array( 'default' => false, 'sanitize_callback' => 'absint' ) );
    $wp_customize->add_control( 'contatti_map_enabled', array( 'label' => __( 'Abilita Mappa', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'checkbox' ) );

    $wp_customize->add_setting( 'contatti_map_embed', array( 'default' => '', 'sanitize_callback' => 'wp_kses_post' ) );
    $wp_customize->add_control( 'contatti_map_embed', array( 'label' => __( 'Codice Embed Mappa (iframe Google Maps)', 'caniincasa' ), 'section' => 'contatti_section', 'type' => 'textarea', 'description' => __( 'Incolla qui il codice iframe di Google Maps', 'caniincasa' ) ) );

    // ===============================================
    // SOCIAL MEDIA SECTION
    // ===============================================

    $wp_customize->add_section(
        'social_media_section',
        array(
            'title'    => __( 'Social Media', 'caniincasa' ),
            'priority' => 132,
        )
    );

    $wp_customize->add_setting( 'social_facebook', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'social_facebook', array( 'label' => __( 'Facebook URL', 'caniincasa' ), 'section' => 'social_media_section', 'type' => 'url' ) );

    $wp_customize->add_setting( 'social_instagram', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'social_instagram', array( 'label' => __( 'Instagram URL', 'caniincasa' ), 'section' => 'social_media_section', 'type' => 'url' ) );

    $wp_customize->add_setting( 'social_twitter', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'social_twitter', array( 'label' => __( 'Twitter URL', 'caniincasa' ), 'section' => 'social_media_section', 'type' => 'url' ) );

    $wp_customize->add_setting( 'social_youtube', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'social_youtube', array( 'label' => __( 'YouTube URL', 'caniincasa' ), 'section' => 'social_media_section', 'type' => 'url' ) );
}
add_action( 'customize_register', 'caniincasa_customize_register' );
