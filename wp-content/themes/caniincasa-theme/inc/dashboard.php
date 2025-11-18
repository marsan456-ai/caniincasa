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
    // Dashboard page
    if ( is_page_template( 'template-dashboard.php' ) ) {
        wp_enqueue_style( 'caniincasa-dashboard', CANIINCASA_THEME_URI . '/assets/css/dashboard.css', array(), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-dashboard', CANIINCASA_THEME_URI . '/assets/js/dashboard.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }

    // Auth pages (Registration and Login)
    if ( is_page_template( 'template-registrazione.php' ) || is_page_template( 'template-login.php' ) ) {
        wp_enqueue_style( 'caniincasa-auth', CANIINCASA_THEME_URI . '/assets/css/auth.css', array(), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-auth', CANIINCASA_THEME_URI . '/assets/js/auth.js', array( 'jquery' ), CANIINCASA_VERSION, true );

        // Localize script with AJAX URL
        wp_localize_script( 'caniincasa-auth', 'caniincasaAuth', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        ) );
    }

    // Quiz page
    if ( is_page_template( 'template-quiz-razza.php' ) ) {
        wp_enqueue_style( 'caniincasa-quiz', CANIINCASA_THEME_URI . '/assets/css/quiz.css', array(), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-quiz', CANIINCASA_THEME_URI . '/assets/js/quiz.js', array( 'jquery' ), CANIINCASA_VERSION, true );

        // Localize script with AJAX URL
        wp_localize_script( 'caniincasa-quiz', 'caniincasaQuiz', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        ) );
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

/**
 * Register custom user roles
 */
function caniincasa_register_custom_roles() {
    // Define custom roles with same capabilities as subscriber for now
    $subscriber_caps = get_role( 'subscriber' )->capabilities;

    $custom_roles = array(
        'privato' => array(
            'display_name' => 'Privato',
            'capabilities' => $subscriber_caps,
        ),
        'veterinario' => array(
            'display_name' => 'Veterinario',
            'capabilities' => $subscriber_caps,
        ),
        'allevatore' => array(
            'display_name' => 'Allevatore',
            'capabilities' => $subscriber_caps,
        ),
        'titolare_pensione' => array(
            'display_name' => 'Titolare Pensione',
            'capabilities' => $subscriber_caps,
        ),
        'dog_sitter' => array(
            'display_name' => 'Dog Sitter',
            'capabilities' => $subscriber_caps,
        ),
        'educatore_cinofilo' => array(
            'display_name' => 'Educatore Cinofilo',
            'capabilities' => $subscriber_caps,
        ),
        'altro' => array(
            'display_name' => 'Altro',
            'capabilities' => $subscriber_caps,
        ),
    );

    foreach ( $custom_roles as $role_slug => $role_data ) {
        if ( ! get_role( $role_slug ) ) {
            add_role( $role_slug, $role_data['display_name'], $role_data['capabilities'] );
        }
    }
}
add_action( 'init', 'caniincasa_register_custom_roles' );

/**
 * Get available user types for registration
 */
function caniincasa_get_user_types() {
    return array(
        'privato'            => 'Privato',
        'veterinario'        => 'Veterinario',
        'allevatore'         => 'Allevatore',
        'titolare_pensione'  => 'Titolare Pensione',
        'dog_sitter'         => 'Dog Sitter',
        'educatore_cinofilo' => 'Educatore Cinofilo',
        'altro'              => 'Altro',
    );
}

/**
 * Block access to wp-admin for non-admin users
 */
function caniincasa_block_wp_admin_access() {
    if ( is_admin() && ! current_user_can( 'administrator' ) && ! wp_doing_ajax() ) {
        wp_redirect( home_url( '/dashboard' ) );
        exit;
    }
}
add_action( 'admin_init', 'caniincasa_block_wp_admin_access' );

/**
 * Redirect to custom login page
 */
function caniincasa_redirect_login_page() {
    $login_page = home_url( '/login' );
    $page_viewed = basename( $_SERVER['REQUEST_URI'] );

    if ( $page_viewed == 'wp-login.php' && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
        wp_redirect( $login_page );
        exit;
    }
}
add_action( 'init', 'caniincasa_redirect_login_page' );

/**
 * Redirect failed login to custom login page
 */
function caniincasa_redirect_login_fail( $username ) {
    $referrer = $_SERVER['HTTP_REFERER'];

    if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
        if ( ! empty( $username ) ) {
            wp_redirect( home_url( '/login' ) . '?login=failed&username=' . urlencode( $username ) );
        } else {
            wp_redirect( home_url( '/login' ) . '?login=failed' );
        }
        exit;
    }
}
add_action( 'wp_login_failed', 'caniincasa_redirect_login_fail' );

/**
 * Redirect after successful login
 */
function caniincasa_redirect_after_login( $redirect_to, $request, $user ) {
    // Don't redirect if there's an error
    if ( isset( $user->errors ) && ! empty( $user->errors ) ) {
        return $redirect_to;
    }

    // Redirect to dashboard for non-admin users
    if ( ! is_wp_error( $user ) && ! current_user_can( 'administrator', $user ) ) {
        return home_url( '/dashboard' );
    }

    return $redirect_to;
}
add_filter( 'login_redirect', 'caniincasa_redirect_after_login', 10, 3 );

/**
 * AJAX: Handle user registration
 */
function caniincasa_ajax_register_user() {
    check_ajax_referer( 'caniincasa_register', 'nonce' );

    // Sanitize inputs
    $username       = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
    $email          = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $password       = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $confirm_pass   = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';
    $first_name     = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
    $last_name      = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
    $user_type      = isset( $_POST['user_type'] ) ? sanitize_text_field( $_POST['user_type'] ) : '';
    $phone          = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
    $city           = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
    $provincia      = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
    $accept_privacy = isset( $_POST['accept_privacy'] ) ? $_POST['accept_privacy'] === 'true' : false;

    // Validation
    if ( empty( $username ) || empty( $email ) || empty( $password ) || empty( $first_name ) || empty( $last_name ) || empty( $user_type ) ) {
        wp_send_json_error( array( 'message' => 'Compila tutti i campi obbligatori.' ) );
    }

    if ( ! $accept_privacy ) {
        wp_send_json_error( array( 'message' => 'Devi accettare la privacy policy.' ) );
    }

    if ( strlen( $username ) < 4 ) {
        wp_send_json_error( array( 'message' => 'Il nome utente deve contenere almeno 4 caratteri.' ) );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Email non valida.' ) );
    }

    if ( strlen( $password ) < 8 ) {
        wp_send_json_error( array( 'message' => 'La password deve contenere almeno 8 caratteri.' ) );
    }

    if ( $password !== $confirm_pass ) {
        wp_send_json_error( array( 'message' => 'Le password non corrispondono.' ) );
    }

    if ( username_exists( $username ) ) {
        wp_send_json_error( array( 'message' => 'Nome utente già in uso.' ) );
    }

    if ( email_exists( $email ) ) {
        wp_send_json_error( array( 'message' => 'Email già registrata.' ) );
    }

    // Validate user type
    $available_types = array_keys( caniincasa_get_user_types() );
    if ( ! in_array( $user_type, $available_types ) ) {
        wp_send_json_error( array( 'message' => 'Tipologia utente non valida.' ) );
    }

    // Create user
    $user_data = array(
        'user_login'   => $username,
        'user_email'   => $email,
        'user_pass'    => $password,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => $first_name . ' ' . $last_name,
        'role'         => $user_type, // Set custom role directly
    );

    $user_id = wp_insert_user( $user_data );

    if ( is_wp_error( $user_id ) ) {
        wp_send_json_error( array( 'message' => 'Errore durante la registrazione: ' . $user_id->get_error_message() ) );
    }

    // Save additional meta
    update_user_meta( $user_id, 'user_type', $user_type );
    if ( $phone ) {
        update_user_meta( $user_id, 'phone', $phone );
    }
    if ( $city ) {
        update_user_meta( $user_id, 'city', $city );
    }
    if ( $provincia ) {
        update_user_meta( $user_id, 'provincia', $provincia );
    }

    // Auto-login after registration
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );

    wp_send_json_success( array(
        'message'      => 'Registrazione completata con successo!',
        'redirect_url' => home_url( '/dashboard' ),
    ) );
}
add_action( 'wp_ajax_nopriv_register_user', 'caniincasa_ajax_register_user' );

/**
 * AJAX: Handle user login
 */
function caniincasa_ajax_login_user() {
    check_ajax_referer( 'caniincasa_login', 'nonce' );

    $username = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
    $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
    $remember = isset( $_POST['remember'] ) ? $_POST['remember'] === 'true' : false;

    if ( empty( $username ) || empty( $password ) ) {
        wp_send_json_error( array( 'message' => 'Inserisci username e password.' ) );
    }

    $creds = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    );

    $user = wp_signon( $creds, false );

    if ( is_wp_error( $user ) ) {
        wp_send_json_error( array( 'message' => 'Credenziali non valide.' ) );
    }

    wp_send_json_success( array(
        'message'      => 'Login effettuato con successo!',
        'redirect_url' => home_url( '/dashboard' ),
    ) );
}
add_action( 'wp_ajax_nopriv_login_user', 'caniincasa_ajax_login_user' );

/**
 * AJAX: Submit Quiz and Calculate Breed Matches
 */
function caniincasa_ajax_submit_quiz() {
    check_ajax_referer( 'caniincasa_quiz', 'nonce' );

    // Sanitize quiz answers
    $quiz_answers = array(
        'esperienza'    => isset( $_POST['esperienza'] ) ? sanitize_text_field( $_POST['esperienza'] ) : '',
        'abitazione'    => isset( $_POST['abitazione'] ) ? sanitize_text_field( $_POST['abitazione'] ) : '',
        'tempo'         => isset( $_POST['tempo'] ) ? sanitize_text_field( $_POST['tempo'] ) : '',
        'attivita'      => isset( $_POST['attivita'] ) ? sanitize_text_field( $_POST['attivita'] ) : '',
        'bambini'       => isset( $_POST['bambini'] ) ? sanitize_text_field( $_POST['bambini'] ) : '',
        'animali'       => isset( $_POST['animali'] ) ? sanitize_text_field( $_POST['animali'] ) : '',
        'clima'         => isset( $_POST['clima'] ) ? sanitize_text_field( $_POST['clima'] ) : '',
        'manutenzione'  => isset( $_POST['manutenzione'] ) ? sanitize_text_field( $_POST['manutenzione'] ) : '',
        'scopo'         => isset( $_POST['scopo'] ) ? sanitize_text_field( $_POST['scopo'] ) : '',
    );

    // Validate all answers are present
    foreach ( $quiz_answers as $key => $value ) {
        if ( empty( $value ) ) {
            wp_send_json_error( array( 'message' => 'Rispondi a tutte le domande.' ) );
        }
    }

    // Get all razze_di_cani
    $razze_query = new WP_Query( array(
        'post_type'      => 'razze_di_cani',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    $breed_matches = array();

    if ( $razze_query->have_posts() ) {
        while ( $razze_query->have_posts() ) {
            $razze_query->the_post();
            $post_id = get_the_ID();

            // Calculate compatibility percentage
            $compatibility = caniincasa_calculate_breed_compatibility( $post_id, $quiz_answers );

            // Get breed data
            $breed_data = array(
                'id'               => $post_id,
                'name'             => get_the_title(),
                'description'      => wp_trim_words( get_the_excerpt(), 20, '...' ),
                'url'              => get_permalink(),
                'image'            => get_the_post_thumbnail_url( $post_id, 'medium' ),
                'match_percentage' => $compatibility,
            );

            $breed_matches[] = $breed_data;
        }
        wp_reset_postdata();
    }

    // Sort by compatibility (highest first)
    usort( $breed_matches, function( $a, $b ) {
        return $b['match_percentage'] - $a['match_percentage'];
    });

    // Get top 10
    $top_breeds = array_slice( $breed_matches, 0, 10 );

    // Save quiz result for logged-in users
    if ( is_user_logged_in() && ! empty( $top_breeds ) ) {
        $user_id = get_current_user_id();
        $top_breed_name = $top_breeds[0]['name'];
        caniincasa_save_quiz_result( $user_id, $quiz_answers, $top_breed_name );
    }

    wp_send_json_success( array(
        'breeds'       => $top_breeds,
        'quiz_answers' => $quiz_answers,
        'message'      => 'Quiz completato con successo!',
    ) );
}
add_action( 'wp_ajax_submit_quiz', 'caniincasa_ajax_submit_quiz' );
add_action( 'wp_ajax_nopriv_submit_quiz', 'caniincasa_ajax_submit_quiz' );

/**
 * Calculate breed compatibility based on quiz answers
 *
 * @param int   $breed_post_id Breed post ID
 * @param array $quiz_answers  User's quiz answers
 * @return int Compatibility percentage (0-100)
 */
function caniincasa_calculate_breed_compatibility( $breed_post_id, $quiz_answers ) {
    $total_points = 0;
    $max_points = 0;

    // Experience level (livello_esperienza_richiesto)
    $required_exp = get_field( 'livello_esperienza_richiesto', $breed_post_id );
    if ( $required_exp ) {
        $max_points += 10;
        $exp_map = array(
            'principiante' => array( 'principiante' => 10, 'intermedia' => 5, 'esperto' => 3 ),
            'intermedia'   => array( 'principiante' => 3, 'intermedia' => 10, 'esperto' => 8 ),
            'esperto'      => array( 'principiante' => 0, 'intermedia' => 5, 'esperto' => 10 ),
        );
        if ( isset( $exp_map[ $quiz_answers['esperienza'] ][ $required_exp ] ) ) {
            $total_points += $exp_map[ $quiz_answers['esperienza'] ][ $required_exp ];
        }
    }

    // Housing (adattabilita_appartamento)
    $apartment_adapt = get_field( 'adattabilita_appartamento', $breed_post_id );
    if ( $apartment_adapt ) {
        $max_points += 10;
        if ( $quiz_answers['abitazione'] === 'appartamento' ) {
            // Higher apartment adaptability = better match for apartments
            $total_points += ( $apartment_adapt / 5 ) * 10;
        } elseif ( $quiz_answers['abitazione'] === 'casa_giardino' ) {
            // Any adaptability works for house with garden
            $total_points += 8;
        } else { // fattoria
            // Lower apartment adaptability might mean larger, more active dogs (good for farm)
            $total_points += ( ( 5 - $apartment_adapt ) / 5 ) * 10;
        }
    }

    // Time/Activity (livello_energia + bisogno_esercizio)
    $energy_level = get_field( 'livello_energia', $breed_post_id );
    $exercise_need = get_field( 'bisogno_esercizio', $breed_post_id );
    if ( $energy_level || $exercise_need ) {
        $max_points += 15;
        $avg_activity = ( $energy_level + $exercise_need ) / 2;

        $time_activity_map = array(
            'poco'        => array( 'threshold' => 2, 'ideal' => 1 ),
            'medio'       => array( 'threshold' => 3.5, 'ideal' => 3 ),
            'molto'       => array( 'threshold' => 5, 'ideal' => 4.5 ),
        );

        $user_time = $quiz_answers['tempo'];
        $user_activity = $quiz_answers['attivita'];

        // Match based on time and activity
        if ( $user_time === 'poco' && $user_activity === 'sedentario' ) {
            // Low energy breeds
            $total_points += max( 0, 15 - ( abs( $avg_activity - 1.5 ) * 3 ) );
        } elseif ( $user_time === 'molto' && $user_activity === 'molto_attivo' ) {
            // High energy breeds
            $total_points += max( 0, 15 - ( abs( $avg_activity - 4.5 ) * 3 ) );
        } else {
            // Medium energy breeds
            $total_points += max( 0, 15 - ( abs( $avg_activity - 3 ) * 3 ) );
        }
    }

    // Children compatibility (tolleranza_bambini)
    $child_tolerance = get_field( 'tolleranza_bambini', $breed_post_id );
    if ( $child_tolerance ) {
        $max_points += 10;
        if ( $quiz_answers['bambini'] === 'piccoli' ) {
            // Need high tolerance for young children
            $total_points += ( $child_tolerance >= 4 ) ? 10 : ( $child_tolerance * 2 );
        } elseif ( $quiz_answers['bambini'] === 'grandi' ) {
            // Medium to high tolerance for older children
            $total_points += ( $child_tolerance >= 3 ) ? 10 : ( $child_tolerance * 2.5 );
        } else {
            // No children - tolerance doesn't matter much
            $total_points += 5;
        }
    }

    // Other animals (socievolezza_cani + socievolezza_altri_animali)
    $dog_sociability = get_field( 'socievolezza_cani', $breed_post_id );
    $other_animals = get_field( 'socievolezza_altri_animali', $breed_post_id );
    if ( $dog_sociability || $other_animals ) {
        $max_points += 10;
        if ( $quiz_answers['animali'] === 'gatti' ) {
            // Need good sociability with other animals
            $total_points += ( $other_animals >= 3 ) ? 10 : ( $other_animals * 2.5 );
        } elseif ( $quiz_answers['animali'] === 'cani' ) {
            // Need good sociability with dogs
            $total_points += ( $dog_sociability >= 3 ) ? 10 : ( $dog_sociability * 2.5 );
        } else {
            // No other animals
            $total_points += 5;
        }
    }

    // Climate tolerance (tolleranza_freddo + tolleranza_caldo)
    $cold_tolerance = get_field( 'tolleranza_freddo', $breed_post_id );
    $heat_tolerance = get_field( 'tolleranza_caldo', $breed_post_id );
    if ( $cold_tolerance || $heat_tolerance ) {
        $max_points += 10;
        if ( $quiz_answers['clima'] === 'freddo' ) {
            $total_points += ( $cold_tolerance >= 4 ) ? 10 : ( $cold_tolerance * 2 );
        } elseif ( $quiz_answers['clima'] === 'caldo' ) {
            $total_points += ( $heat_tolerance >= 4 ) ? 10 : ( $heat_tolerance * 2 );
        } else { // temperato
            $avg_climate = ( $cold_tolerance + $heat_tolerance ) / 2;
            $total_points += ( $avg_climate >= 3 ) ? 10 : ( $avg_climate * 2.5 );
        }
    }

    // Grooming needs (necessita_toelettatura)
    $grooming_needs = get_field( 'necessita_toelettatura', $breed_post_id );
    if ( $grooming_needs ) {
        $max_points += 10;
        $grooming_map = array(
            'bassa' => array( 1 => 10, 2 => 8, 3 => 5, 4 => 3, 5 => 1 ),
            'media' => array( 1 => 5, 2 => 8, 3 => 10, 4 => 8, 5 => 5 ),
            'alta'  => array( 1 => 1, 2 => 3, 3 => 5, 4 => 8, 5 => 10 ),
        );
        if ( isset( $grooming_map[ $quiz_answers['manutenzione'] ][ $grooming_needs ] ) ) {
            $total_points += $grooming_map[ $quiz_answers['manutenzione'] ][ $grooming_needs ];
        }
    }

    // Purpose (tendenza_abbaio for guardia, affettuosita for compagnia, etc.)
    $affection = get_field( 'affettuosita', $breed_post_id );
    $barking = get_field( 'tendenza_abbaio', $breed_post_id );
    $trainability = get_field( 'addestrabilita', $breed_post_id );
    if ( $affection || $barking || $trainability ) {
        $max_points += 15;
        if ( $quiz_answers['scopo'] === 'compagnia' ) {
            // High affection, moderate barking
            $total_points += ( $affection >= 4 ) ? 10 : ( $affection * 2 );
            $total_points += ( $barking <= 2 ) ? 5 : max( 0, 5 - $barking );
        } elseif ( $quiz_answers['scopo'] === 'guardia' ) {
            // High barking, protective
            $total_points += ( $barking >= 3 ) ? 10 : ( $barking * 2.5 );
            $total_points += 5;
        } elseif ( $quiz_answers['scopo'] === 'sport' ) {
            // High trainability, high energy
            $total_points += ( $trainability >= 4 ) ? 15 : ( $trainability * 3 );
        } else { // famiglia
            // Balanced affection, trainability, moderate barking
            $total_points += ( $affection >= 3 ) ? 5 : ( $affection * 1.5 );
            $total_points += ( $trainability >= 3 ) ? 5 : ( $trainability * 1.5 );
            $total_points += ( $barking <= 3 ) ? 5 : max( 0, 5 - ( $barking - 3 ) );
        }
    }

    // Calculate percentage
    if ( $max_points > 0 ) {
        $percentage = round( ( $total_points / $max_points ) * 100 );
        return max( 0, min( 100, $percentage ) ); // Clamp between 0-100
    }

    return 0;
}

/**
 * AJAX: Email Quiz Results
 */
function caniincasa_ajax_email_quiz_results() {
    check_ajax_referer( 'caniincasa_quiz', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per ricevere i risultati via email.' ) );
    }

    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );
    $user_email = $user->user_email;

    // Get results from POST
    $results = isset( $_POST['results'] ) ? json_decode( stripslashes( $_POST['results'] ), true ) : array();

    if ( empty( $results ) || ! isset( $results['breeds'] ) ) {
        wp_send_json_error( array( 'message' => 'Nessun risultato disponibile.' ) );
    }

    // Prepare email
    $to = $user_email;
    $subject = 'I tuoi risultati del Quiz Selezione Razza - Caniincasa';

    // Build email body
    $body = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px;">';
    $body .= '<h1 style="color: #f97316; border-bottom: 3px solid #f97316; padding-bottom: 10px;">Quiz Selezione Razza - I Tuoi Risultati</h1>';
    $body .= '<p>Ciao ' . esc_html( $user->display_name ) . ',</p>';
    $body .= '<p>Ecco le razze di cani più adatte a te in base alle tue risposte:</p>';

    // Top 10 breeds
    $body .= '<h2 style="color: #f97316; margin-top: 30px;">Top 10 Razze per Te:</h2>';
    foreach ( $results['breeds'] as $index => $breed ) {
        $rank = $index + 1;
        $body .= '<div style="margin: 20px 0; padding: 15px; border: 2px solid #e2e8f0; border-radius: 8px;">';
        $body .= '<h3 style="margin: 0 0 10px 0; color: #1e293b;">' . $rank . '. ' . esc_html( $breed['name'] ) . '</h3>';
        $body .= '<p style="margin: 5px 0;"><strong style="color: #f97316;">Compatibilità: ' . $breed['match_percentage'] . '%</strong></p>';
        if ( ! empty( $breed['description'] ) ) {
            $body .= '<p style="margin: 10px 0 0 0; color: #64748b;">' . esc_html( $breed['description'] ) . '</p>';
        }
        $body .= '<p style="margin: 10px 0 0 0;"><a href="' . esc_url( $breed['url'] ) . '" style="color: #f97316; text-decoration: none; font-weight: 600;">Scopri di più &rarr;</a></p>';
        $body .= '</div>';
    }

    $body .= '<div style="margin: 30px 0; padding: 20px; background: #f8fafc; border-radius: 8px;">';
    $body .= '<p style="margin: 0;"><strong>💡 Consiglio:</strong> Considera anche l\'adozione di un meticcio! I meticci sono cani unici e spesso più sani.</p>';
    $body .= '</div>';

    $body .= '<p style="margin-top: 30px;">Visita <a href="' . home_url() . '" style="color: #f97316;">Caniincasa.it</a> per trovare il tuo compagno perfetto!</p>';
    $body .= '</div></body></html>';

    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    // Send email
    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        wp_send_json_success( array( 'message' => 'Email inviata con successo!' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Errore durante l\'invio dell\'email.' ) );
    }
}
add_action( 'wp_ajax_email_quiz_results', 'caniincasa_ajax_email_quiz_results' );

/**
 * AJAX: Download Quiz Results PDF
 */
function caniincasa_ajax_download_quiz_pdf() {
    check_ajax_referer( 'caniincasa_quiz', 'nonce' );

    // Get results from POST
    $results = isset( $_POST['results'] ) ? json_decode( stripslashes( $_POST['results'] ), true ) : array();

    if ( empty( $results ) || ! isset( $results['breeds'] ) ) {
        wp_send_json_error( array( 'message' => 'Nessun risultato disponibile.' ) );
    }

    // Check if TCPDF or similar library is available
    // For now, we'll create a simple HTML version that can be printed as PDF
    // In production, you would use a PDF library like TCPDF or Dompdf

    // Set headers for PDF download (browser print to PDF)
    header( 'Content-Type: text/html; charset=utf-8' );
    header( 'Content-Disposition: inline; filename="quiz-risultati-caniincasa.html"' );

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Quiz Selezione Razza - Risultati</title>
        <style>
            @media print {
                @page {
                    margin: 2cm;
                }
                .no-print {
                    display: none;
                }
            }
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1 {
                color: #f97316;
                border-bottom: 3px solid #f97316;
                padding-bottom: 10px;
            }
            h2 {
                color: #f97316;
                margin-top: 30px;
            }
            .breed-item {
                margin: 20px 0;
                padding: 15px;
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                page-break-inside: avoid;
            }
            .breed-item h3 {
                margin: 0 0 10px 0;
                color: #1e293b;
            }
            .match-percentage {
                color: #f97316;
                font-weight: bold;
            }
            .description {
                margin: 10px 0 0 0;
                color: #64748b;
            }
            .tip-box {
                margin: 30px 0;
                padding: 20px;
                background: #f8fafc;
                border-radius: 8px;
                border-left: 4px solid #f97316;
            }
            .print-btn {
                margin: 20px 0;
                padding: 12px 24px;
                background: #f97316;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
            }
            .print-btn:hover {
                background: #ea580c;
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">🖨️ Stampa / Salva come PDF</button>
        </div>

        <h1>Quiz Selezione Razza - I Tuoi Risultati</h1>
        <p><strong>Data:</strong> <?php echo date( 'd/m/Y' ); ?></p>

        <h2>Top 10 Razze per Te:</h2>

        <?php foreach ( $results['breeds'] as $index => $breed ) : ?>
            <div class="breed-item">
                <h3><?php echo ( $index + 1 ) . '. ' . esc_html( $breed['name'] ); ?></h3>
                <p class="match-percentage">Compatibilità: <?php echo $breed['match_percentage']; ?>%</p>
                <?php if ( ! empty( $breed['description'] ) ) : ?>
                    <p class="description"><?php echo esc_html( $breed['description'] ); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="tip-box">
            <p><strong>💡 Consiglio:</strong> Considera anche l'adozione di un meticcio! I meticci sono cani unici, spesso più sani e con caratteristiche imprevedibili che li rendono compagni speciali.</p>
        </div>

        <p style="margin-top: 30px; text-align: center; color: #64748b;">
            Visita <strong>Caniincasa.it</strong> per trovare il tuo compagno perfetto!
        </p>

        <div class="no-print">
            <button class="print-btn" onclick="window.print()">🖨️ Stampa / Salva come PDF</button>
        </div>
    </body>
    </html>
    <?php

    exit;
}
add_action( 'wp_ajax_download_quiz_pdf', 'caniincasa_ajax_download_quiz_pdf' );
add_action( 'wp_ajax_nopriv_download_quiz_pdf', 'caniincasa_ajax_download_quiz_pdf' );
