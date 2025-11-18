<?php
/**
 * Dashboard Functions
 * Helper functions and AJAX handlers for user dashboard
 *
 * @package Caniincasa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX: Add to Preferiti
 */
function caniincasa_ajax_add_preferito() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per aggiungere ai preferiti.' ) );
    }

    $post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
    $user_id   = get_current_user_id();

    if ( ! $post_id || ! $post_type ) {
        wp_send_json_error( array( 'message' => 'Dati non validi.' ) );
    }

    // Determine meta key based on post type
    if ( $post_type === 'razze_di_cani' ) {
        $meta_key = 'preferiti_razze';
    } elseif ( in_array( $post_type, array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' ) ) ) {
        $meta_key = 'preferiti_strutture';
    } else {
        wp_send_json_error( array( 'message' => 'Tipo di post non supportato.' ) );
    }

    // Get current preferiti
    $preferiti = get_user_meta( $user_id, $meta_key, true );
    if ( ! is_array( $preferiti ) ) {
        $preferiti = array();
    }

    // Add if not already in array
    if ( ! in_array( $post_id, $preferiti ) ) {
        $preferiti[] = $post_id;
        update_user_meta( $user_id, $meta_key, $preferiti );
        wp_send_json_success( array( 'message' => 'Aggiunto ai preferiti!' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Già nei preferiti.' ) );
    }
}
add_action( 'wp_ajax_add_preferito', 'caniincasa_ajax_add_preferito' );

/**
 * AJAX: Remove from Preferiti
 */
function caniincasa_ajax_remove_preferito() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $type    = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
    $user_id = get_current_user_id();

    if ( ! $post_id || ! $type ) {
        wp_send_json_error( array( 'message' => 'Dati non validi.' ) );
    }

    $meta_key = 'preferiti_' . $type;

    // Get current preferiti
    $preferiti = get_user_meta( $user_id, $meta_key, true );
    if ( ! is_array( $preferiti ) ) {
        $preferiti = array();
    }

    // Remove from array
    $key = array_search( $post_id, $preferiti );
    if ( $key !== false ) {
        unset( $preferiti[ $key ] );
        $preferiti = array_values( $preferiti ); // Re-index array
        update_user_meta( $user_id, $meta_key, $preferiti );
        wp_send_json_success( array( 'message' => 'Rimosso dai preferiti.' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Non trovato nei preferiti.' ) );
    }
}
add_action( 'wp_ajax_remove_preferito', 'caniincasa_ajax_remove_preferito' );

/**
 * Check if post is in user's preferiti
 *
 * @param int    $post_id   Post ID
 * @param string $post_type Post type
 * @param int    $user_id   User ID (optional, defaults to current user)
 * @return bool
 */
function caniincasa_is_preferito( $post_id, $post_type, $user_id = null ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return false;
    }

    // Determine meta key based on post type
    if ( $post_type === 'razze_di_cani' ) {
        $meta_key = 'preferiti_razze';
    } elseif ( in_array( $post_type, array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' ) ) ) {
        $meta_key = 'preferiti_strutture';
    } else {
        return false;
    }

    $preferiti = get_user_meta( $user_id, $meta_key, true );
    if ( ! is_array( $preferiti ) ) {
        return false;
    }

    return in_array( $post_id, $preferiti );
}

/**
 * Get preferiti button HTML
 *
 * @param int    $post_id   Post ID
 * @param string $post_type Post type
 * @return string
 */
function caniincasa_get_preferiti_button( $post_id, $post_type ) {
    if ( ! is_user_logged_in() ) {
        return '<a href="' . esc_url( wp_login_url( get_permalink( $post_id ) ) ) . '" class="btn-preferiti">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <span>Aggiungi ai Preferiti</span>
        </a>';
    }

    $is_preferito = caniincasa_is_preferito( $post_id, $post_type );
    $class        = $is_preferito ? 'btn-preferiti active' : 'btn-preferiti';
    $text         = $is_preferito ? 'Nei Preferiti' : 'Aggiungi ai Preferiti';
    $icon_fill    = $is_preferito ? 'currentColor' : 'none';

    return '<button class="' . esc_attr( $class ) . '" data-post-id="' . esc_attr( $post_id ) . '" data-post-type="' . esc_attr( $post_type ) . '">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="' . esc_attr( $icon_fill ) . '" stroke="currentColor">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        <span>' . esc_html( $text ) . '</span>
    </button>';
}

/**
 * Save quiz results for user
 *
 * @param int   $user_id           User ID
 * @param array $quiz_data         Quiz data (answers, results, etc.)
 * @param string $recommended_breed Recommended breed name
 * @param string $pdf_url          URL to generated PDF (optional)
 * @return bool
 */
function caniincasa_save_quiz_result( $user_id, $quiz_data, $recommended_breed, $pdf_url = '' ) {
    if ( ! $user_id ) {
        return false;
    }

    $quiz_results = get_user_meta( $user_id, 'quiz_results', true );
    if ( ! is_array( $quiz_results ) ) {
        $quiz_results = array();
    }

    $new_result = array(
        'date'              => time(),
        'recommended_breed' => $recommended_breed,
        'quiz_data'         => $quiz_data,
        'pdf_url'           => $pdf_url,
    );

    array_unshift( $quiz_results, $new_result ); // Add to beginning

    // Keep only last 10 results
    if ( count( $quiz_results ) > 10 ) {
        $quiz_results = array_slice( $quiz_results, 0, 10 );
    }

    return update_user_meta( $user_id, 'quiz_results', $quiz_results );
}

/**
 * Get user's quiz results
 *
 * @param int $user_id User ID
 * @param int $limit   Number of results to return (default: all)
 * @return array
 */
function caniincasa_get_quiz_results( $user_id, $limit = -1 ) {
    if ( ! $user_id ) {
        return array();
    }

    $quiz_results = get_user_meta( $user_id, 'quiz_results', true );
    if ( ! is_array( $quiz_results ) ) {
        return array();
    }

    if ( $limit > 0 ) {
        return array_slice( $quiz_results, 0, $limit );
    }

    return $quiz_results;
}

/**
 * Enqueue dashboard scripts and styles
 */
function caniincasa_dashboard_scripts() {
    if ( is_page_template( 'template-dashboard.php' ) ) {
        wp_enqueue_style( 'caniincasa-dashboard', CANIINCASA_THEME_URI . '/assets/css/dashboard.css', array(), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-dashboard', CANIINCASA_THEME_URI . '/assets/js/dashboard.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }

    // Preferiti functionality on all pages (for single pages with add button)
    if ( is_singular( array( 'razze_di_cani', 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' ) ) ) {
        wp_enqueue_script( 'caniincasa-preferiti', CANIINCASA_THEME_URI . '/assets/js/preferiti.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }
}
add_action( 'wp_enqueue_scripts', 'caniincasa_dashboard_scripts' );

/**
 * Create dashboard page programmatically if it doesn't exist
 */
function caniincasa_create_dashboard_page() {
    // Check if dashboard page already exists
    $dashboard_page = get_page_by_path( 'dashboard' );

    if ( ! $dashboard_page ) {
        $page_id = wp_insert_post( array(
            'post_title'     => 'Dashboard',
            'post_name'      => 'dashboard',
            'post_content'   => '',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', 'template-dashboard.php' );
        }
    }
}
// Uncomment to auto-create dashboard page on theme activation
// add_action( 'after_setup_theme', 'caniincasa_create_dashboard_page' );

/**
 * Enable user registration programmatically
 * This allows users to register without admin intervention
 */
function caniincasa_enable_user_registration() {
    // Check if registration is disabled
    if ( ! get_option( 'users_can_register' ) ) {
        // Note: This should be done via WordPress admin or manually
        // Uncomment the line below to enable programmatically (not recommended for security)
        // update_option( 'users_can_register', 1 );
    }
}
add_action( 'init', 'caniincasa_enable_user_registration' );

/**
 * Customize default user role for new registrations
 */
function caniincasa_set_default_user_role( $user_id ) {
    $user = new WP_User( $user_id );
    $user->set_role( 'subscriber' ); // Set default role to subscriber
}
add_action( 'user_register', 'caniincasa_set_default_user_role' );

/**
 * AJAX: Submit Annuncio 4 Zampe
 */
function caniincasa_ajax_submit_annuncio_4zampe() {
    check_ajax_referer( 'submit_annuncio_4zampe', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per pubblicare un annuncio.' ) );
    }

    // Sanitize and validate inputs
    $titolo = isset( $_POST['titolo'] ) ? sanitize_text_field( $_POST['titolo'] ) : '';
    $descrizione = isset( $_POST['descrizione'] ) ? wp_kses_post( $_POST['descrizione'] ) : '';
    $tipo_annuncio = isset( $_POST['tipo_annuncio'] ) ? sanitize_text_field( $_POST['tipo_annuncio'] ) : '';
    $eta = isset( $_POST['eta'] ) ? sanitize_text_field( $_POST['eta'] ) : '';
    $tipo_cane = isset( $_POST['tipo_cane'] ) ? sanitize_text_field( $_POST['tipo_cane'] ) : '';
    $razza = isset( $_POST['razza'] ) ? absint( $_POST['razza'] ) : 0;
    $contatto_preferito = isset( $_POST['contatto_preferito'] ) ? sanitize_text_field( $_POST['contatto_preferito'] ) : '';
    $telefono = isset( $_POST['telefono'] ) ? sanitize_text_field( $_POST['telefono'] ) : '';
    $citta = isset( $_POST['citta'] ) ? sanitize_text_field( $_POST['citta'] ) : '';
    $provincia = isset( $_POST['provincia'] ) ? absint( $_POST['provincia'] ) : 0;
    $giorni_scadenza = isset( $_POST['giorni_scadenza'] ) ? absint( $_POST['giorni_scadenza'] ) : 30;

    // Validation
    if ( empty( $titolo ) || empty( $descrizione ) || empty( $tipo_annuncio ) || empty( $eta ) || empty( $tipo_cane ) ) {
        wp_send_json_error( array( 'message' => 'Compila tutti i campi obbligatori.' ) );
    }

    if ( strlen( $descrizione ) < 50 ) {
        wp_send_json_error( array( 'message' => 'La descrizione deve contenere almeno 50 caratteri.' ) );
    }

    if ( $tipo_cane === 'razza' && empty( $razza ) ) {
        wp_send_json_error( array( 'message' => 'Seleziona una razza.' ) );
    }

    // Create post
    $post_data = array(
        'post_title'   => $titolo,
        'post_content' => $descrizione,
        'post_type'    => 'annunci_4zampe',
        'post_status'  => 'pending', // Will be set by hook
        'post_author'  => get_current_user_id(),
    );

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Errore durante la creazione dell\'annuncio.' ) );
    }

    // Save ACF fields
    update_field( 'tipo_annuncio', $tipo_annuncio, $post_id );
    update_field( 'eta', $eta, $post_id );
    update_field( 'tipo_cane', $tipo_cane, $post_id );
    if ( $razza ) {
        update_field( 'razza', $razza, $post_id );
    }
    if ( $contatto_preferito ) {
        update_field( 'contatto_preferito', $contatto_preferito, $post_id );
    }
    if ( $giorni_scadenza ) {
        update_field( 'giorni_scadenza', $giorni_scadenza, $post_id );
    }

    // Save custom meta for location and contact
    if ( $telefono ) {
        update_post_meta( $post_id, 'telefono', $telefono );
    }
    if ( $citta ) {
        update_post_meta( $post_id, 'citta', $citta );
    }

    // Set provincia taxonomy
    if ( $provincia ) {
        wp_set_object_terms( $post_id, $provincia, 'provincia' );
    }

    wp_send_json_success( array(
        'message' => 'Annuncio pubblicato con successo! Sarà visibile dopo l\'approvazione.',
        'post_id' => $post_id,
    ) );
}
add_action( 'wp_ajax_submit_annuncio_4zampe', 'caniincasa_ajax_submit_annuncio_4zampe' );

/**
 * AJAX: Submit Annuncio Dogsitter
 */
function caniincasa_ajax_submit_annuncio_dogsitter() {
    check_ajax_referer( 'submit_annuncio_dogsitter', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per pubblicare un annuncio.' ) );
    }

    // Sanitize and validate inputs
    $titolo = isset( $_POST['titolo'] ) ? sanitize_text_field( $_POST['titolo'] ) : '';
    $descrizione = isset( $_POST['descrizione'] ) ? wp_kses_post( $_POST['descrizione'] ) : '';
    $tipo = isset( $_POST['tipo'] ) ? sanitize_text_field( $_POST['tipo'] ) : '';
    $esperienza = isset( $_POST['esperienza'] ) ? sanitize_text_field( $_POST['esperienza'] ) : '';
    $servizi_offerti = isset( $_POST['servizi_offerti'] ) ? array_map( 'sanitize_text_field', $_POST['servizi_offerti'] ) : array();
    $disponibilita = isset( $_POST['disponibilita'] ) ? sanitize_textarea_field( $_POST['disponibilita'] ) : '';
    $prezzo_indicativo = isset( $_POST['prezzo_indicativo'] ) ? sanitize_text_field( $_POST['prezzo_indicativo'] ) : '';
    $telefono = isset( $_POST['telefono'] ) ? sanitize_text_field( $_POST['telefono'] ) : '';
    $citta = isset( $_POST['citta'] ) ? sanitize_text_field( $_POST['citta'] ) : '';
    $provincia = isset( $_POST['provincia'] ) ? absint( $_POST['provincia'] ) : 0;

    // Validation
    if ( empty( $titolo ) || empty( $descrizione ) || empty( $tipo ) ) {
        wp_send_json_error( array( 'message' => 'Compila tutti i campi obbligatori.' ) );
    }

    if ( strlen( $descrizione ) < 50 ) {
        wp_send_json_error( array( 'message' => 'La descrizione deve contenere almeno 50 caratteri.' ) );
    }

    // Create post
    $post_data = array(
        'post_title'   => $titolo,
        'post_content' => $descrizione,
        'post_type'    => 'annunci_dogsitter',
        'post_status'  => 'pending',
        'post_author'  => get_current_user_id(),
    );

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Errore durante la creazione dell\'annuncio.' ) );
    }

    // Save ACF fields
    update_field( 'tipo', $tipo, $post_id );
    if ( $esperienza ) {
        update_field( 'esperienza', $esperienza, $post_id );
    }
    if ( ! empty( $servizi_offerti ) ) {
        update_field( 'servizi_offerti', $servizi_offerti, $post_id );
    }
    if ( $disponibilita ) {
        update_field( 'disponibilita', $disponibilita, $post_id );
    }
    if ( $prezzo_indicativo ) {
        update_field( 'prezzo_indicativo', $prezzo_indicativo, $post_id );
    }

    // Save custom meta
    if ( $telefono ) {
        update_post_meta( $post_id, 'telefono', $telefono );
    }
    if ( $citta ) {
        update_post_meta( $post_id, 'citta', $citta );
    }

    // Set provincia taxonomy
    if ( $provincia ) {
        wp_set_object_terms( $post_id, $provincia, 'provincia' );
    }

    wp_send_json_success( array(
        'message' => 'Annuncio pubblicato con successo! Sarà visibile dopo l\'approvazione.',
        'post_id' => $post_id,
    ) );
}
add_action( 'wp_ajax_submit_annuncio_dogsitter', 'caniincasa_ajax_submit_annuncio_dogsitter' );
