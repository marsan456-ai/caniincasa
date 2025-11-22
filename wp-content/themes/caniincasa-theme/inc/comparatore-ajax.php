<?php
/**
 * Comparatore Razze AJAX Handlers
 *
 * @package Caniincasa
 */

/**
 * Search razze for autocomplete
 */
function caniincasa_search_razze_ajax() {
    // Verify nonce for security
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    $query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';

    if ( strlen( $query ) < 2 ) {
        wp_send_json_error( 'Query too short' );
    }

    // Search razze
    $args = array(
        'post_type'      => 'razze_di_cani',
        'posts_per_page' => 10,
        's'              => $query,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $razze_query = new WP_Query( $args );
    $razze = array();

    if ( $razze_query->have_posts() ) {
        while ( $razze_query->have_posts() ) {
            $razze_query->the_post();

            $taglia_terms = get_the_terms( get_the_ID(), 'razza_taglia' );
            $taglia = $taglia_terms && ! is_wp_error( $taglia_terms ) ? $taglia_terms[0]->name : '';

            $razze[] = array(
                'id'    => get_the_ID(),
                'name'  => get_the_title(),
                'image' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
                'taglia' => $taglia,
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success( $razze );
}
add_action( 'wp_ajax_search_razze', 'caniincasa_search_razze_ajax' );
add_action( 'wp_ajax_nopriv_search_razze', 'caniincasa_search_razze_ajax' );


/**
 * Get razze comparison data
 */
function caniincasa_get_razze_comparison_ajax() {
    // Verify nonce for security
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    $razze_ids = isset( $_POST['razze_ids'] ) ? array_map( 'intval', $_POST['razze_ids'] ) : array();

    if ( empty( $razze_ids ) || count( $razze_ids ) < 2 || count( $razze_ids ) > 3 ) {
        wp_send_json_error( 'Invalid number of razze. Received: ' . count( $razze_ids ) );
        return;
    }

    $razze_data = array();

    foreach ( $razze_ids as $razza_id ) {
        $razza = get_post( $razza_id );

        if ( ! $razza || $razza->post_type !== 'razze_di_cani' ) {
            continue;
        }

        // Get taglia taxonomy
        $taglia_terms = get_the_terms( $razza_id, 'razza_taglia' );
        $taglia = $taglia_terms && ! is_wp_error( $taglia_terms ) ? $taglia_terms[0]->name : 'Non specificata';

        // Format peso (weight) from min/max values
        $peso_min = get_field( 'peso_medio_min', $razza_id );
        $peso_max = get_field( 'peso_medio_max', $razza_id );
        $peso = '';
        if ( $peso_min && $peso_max ) {
            $peso = $peso_min . ' - ' . $peso_max . ' kg';
        } elseif ( $peso_min ) {
            $peso = 'da ' . $peso_min . ' kg';
        } elseif ( $peso_max ) {
            $peso = 'fino a ' . $peso_max . ' kg';
        }

        // Format aspettativa vita (life expectancy) from min/max values
        $vita_min = get_field( 'aspettativa_vita_min', $razza_id );
        $vita_max = get_field( 'aspettativa_vita_max', $razza_id );
        $aspettativa_vita = '';
        if ( $vita_min && $vita_max ) {
            $aspettativa_vita = $vita_min . ' - ' . $vita_max . ' anni';
        } elseif ( $vita_min ) {
            $aspettativa_vita = 'da ' . $vita_min . ' anni';
        } elseif ( $vita_max ) {
            $aspettativa_vita = 'fino a ' . $vita_max . ' anni';
        }

        // Get all ACF fields with CORRECT field names
        $fields = array(
            // Fisici
            'taglia'            => $taglia,
            'peso'              => $peso,
            'altezza'           => '', // This field doesn't exist in ACF
            'aspettativa_vita'  => $aspettativa_vita,
            'tipo_pelo'         => get_field( 'colorazioni', $razza_id ) ?: '', // Using colorazioni as closest match

            // Caratteriali (1-5) - CORRECTED FIELD NAMES
            'affettuosita'          => get_field( 'affettuosita', $razza_id ) ?: 0,
            'energia'               => get_field( 'energia_e_livelli_di_attivita', $razza_id ) ?: 0,
            'socialita'             => get_field( 'tolleranza_estranei', $razza_id ) ?: 0, // FIXED
            'addestrabilita'        => get_field( 'facilita_di_addestramento', $razza_id ) ?: 0, // FIXED
            'territorialita'        => get_field( 'intelligenza', $razza_id ) ?: 0, // Using intelligenza as closest match
            'tendenza_abbaiare'     => get_field( 'vocalita_e_predisposizione_ad_abbaiare', $razza_id ) ?: 0, // FIXED

            // Cure - CORRECTED FIELD NAMES
            'toelettatura'      => get_field( 'facilita_toelettatura', $razza_id ) ?: 0, // FIXED
            'perdita_pelo'      => get_field( 'cura_e_perdita_pelo', $razza_id ) ?: 0, // FIXED
            'esercizio_fisico'  => get_field( 'esigenze_di_esercizio', $razza_id ) ?: 0, // FIXED

            // Ambiente - CORRECTED FIELD NAMES
            'adattabilita_appartamento' => get_field( 'adattabilita_appartamento', $razza_id ) ?: 0, // FIXED (removed extra 'l')
            'tolleranza_solitudine'     => get_field( 'tolleranza_alla_solitudine', $razza_id ) ?: 0,
            'tolleranza_caldo'          => get_field( 'adattabilita_clima_caldo', $razza_id ) ?: 0, // FIXED
            'tolleranza_freddo'         => get_field( 'adattabilita_clima_freddo', $razza_id ) ?: 0, // FIXED

            // Famiglia - CORRECTED FIELD NAMES
            'compatibilita_bambini' => get_field( 'compatibilita_con_i_bambini', $razza_id ) ?: 0,
            'compatibilita_cani'    => get_field( 'socievolezza_cani', $razza_id ) ?: 0, // FIXED
            'compatibilita_gatti'   => get_field( 'compatibilita_con_altri_animali_domestici', $razza_id ) ?: 0, // FIXED
            'adatto_principianti'   => get_field( 'livello_esperienza_richiesto', $razza_id ) ?: 0, // FIXED
        );

        $razze_data[ $razza_id ] = array(
            'id'     => $razza_id,
            'name'   => $razza->post_title,
            'url'    => get_permalink( $razza_id ),
            'image'  => get_the_post_thumbnail_url( $razza_id, 'medium' ),
            'fields' => $fields,
        );
    }

    if ( empty( $razze_data ) ) {
        wp_send_json_error( 'No valid razze found' );
        return;
    }

    wp_send_json_success( $razze_data );
}
add_action( 'wp_ajax_get_razze_comparison', 'caniincasa_get_razze_comparison_ajax' );
add_action( 'wp_ajax_nopriv_get_razze_comparison', 'caniincasa_get_razze_comparison_ajax' );

/**
 * Test AJAX endpoint - simple test to verify AJAX is working
 */
function caniincasa_test_ajax() {
    error_log( 'Test AJAX: Function called successfully' );
    wp_send_json_success( array(
        'message' => 'AJAX is working correctly!',
        'timestamp' => current_time( 'mysql' ),
        'test_data' => array( 1, 2, 3 )
    ) );
}
add_action( 'wp_ajax_test_ajax', 'caniincasa_test_ajax' );
add_action( 'wp_ajax_nopriv_test_ajax', 'caniincasa_test_ajax' );
