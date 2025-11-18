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
