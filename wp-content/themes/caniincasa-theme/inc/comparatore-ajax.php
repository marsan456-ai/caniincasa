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
    // Temporarily skip nonce check for debugging
    // check_ajax_referer( 'caniincasa_nonce', 'nonce' );

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
    // Log start of function
    error_log( 'Comparatore AJAX: Function started' );

    // Temporarily skip nonce check for debugging
    // check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    $razze_ids = isset( $_POST['razze_ids'] ) ? array_map( 'intval', $_POST['razze_ids'] ) : array();
    error_log( 'Comparatore AJAX: Razze IDs received: ' . print_r( $razze_ids, true ) );

    if ( empty( $razze_ids ) || count( $razze_ids ) < 2 || count( $razze_ids ) > 3 ) {
        error_log( 'Comparatore AJAX: Invalid number of razze - count: ' . count( $razze_ids ) );
        wp_send_json_error( 'Invalid number of razze. Received: ' . count( $razze_ids ) );
        return;
    }

    $razze_data = array();

    foreach ( $razze_ids as $razza_id ) {
        error_log( 'Comparatore AJAX: Processing razza ID: ' . $razza_id );

        $razza = get_post( $razza_id );

        if ( ! $razza || $razza->post_type !== 'razze_di_cani' ) {
            error_log( 'Comparatore AJAX: Invalid post or wrong post type for ID: ' . $razza_id );
            continue;
        }

        error_log( 'Comparatore AJAX: Valid razza found: ' . $razza->post_title );

        // Get taglia taxonomy
        $taglia_terms = get_the_terms( $razza_id, 'razza_taglia' );
        $taglia = $taglia_terms && ! is_wp_error( $taglia_terms ) ? $taglia_terms[0]->name : 'Non specificata';

        // Get all ACF fields with fallback values
        $fields = array(
            // Fisici
            'taglia'            => $taglia,
            'peso'              => get_field( 'peso', $razza_id ) ?: get_post_meta( $razza_id, 'peso', true ) ?: '',
            'altezza'           => get_field( 'altezza', $razza_id ) ?: get_post_meta( $razza_id, 'altezza', true ) ?: '',
            'aspettativa_vita'  => get_field( 'aspettativa_di_vita', $razza_id ) ?: get_post_meta( $razza_id, 'aspettativa_di_vita', true ) ?: '',
            'tipo_pelo'         => get_field( 'tipo_di_pelo', $razza_id ) ?: get_post_meta( $razza_id, 'tipo_di_pelo', true ) ?: '',

            // Caratteriali (1-5)
            'affettuosita'          => get_field( 'affettuosita', $razza_id ) ?: get_post_meta( $razza_id, 'affettuosita', true ) ?: 0,
            'energia'               => get_field( 'energia_e_livelli_di_attivita', $razza_id ) ?: get_post_meta( $razza_id, 'energia_e_livelli_di_attivita', true ) ?: 0,
            'socialita'             => get_field( 'socialita_con_estranei', $razza_id ) ?: get_post_meta( $razza_id, 'socialita_con_estranei', true ) ?: 0,
            'addestrabilita'        => get_field( 'addestrabilita', $razza_id ) ?: get_post_meta( $razza_id, 'addestrabilita', true ) ?: 0,
            'territorialita'        => get_field( 'territorialita', $razza_id ) ?: get_post_meta( $razza_id, 'territorialita', true ) ?: 0,
            'tendenza_abbaiare'     => get_field( 'tendenza_ad_abbaiare', $razza_id ) ?: get_post_meta( $razza_id, 'tendenza_ad_abbaiare', true ) ?: 0,

            // Cure
            'toelettatura'      => get_field( 'necessita_di_toelettatura', $razza_id ) ?: get_post_meta( $razza_id, 'necessita_di_toelettatura', true ) ?: 0,
            'perdita_pelo'      => get_field( 'perdita_di_pelo', $razza_id ) ?: get_post_meta( $razza_id, 'perdita_di_pelo', true ) ?: 0,
            'esercizio_fisico'  => get_field( 'necessita_di_esercizio', $razza_id ) ?: get_post_meta( $razza_id, 'necessita_di_esercizio', true ) ?: 0,

            // Ambiente
            'adattabilita_appartamento' => get_field( 'adattabilita_allappartamento', $razza_id ) ?: get_post_meta( $razza_id, 'adattabilita_allappartamento', true ) ?: 0,
            'tolleranza_solitudine'     => get_field( 'tolleranza_alla_solitudine', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_alla_solitudine', true ) ?: 0,
            'tolleranza_caldo'          => get_field( 'tolleranza_al_caldo', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_al_caldo', true ) ?: 0,
            'tolleranza_freddo'         => get_field( 'tolleranza_al_freddo', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_al_freddo', true ) ?: 0,

            // Famiglia
            'compatibilita_bambini' => get_field( 'compatibilita_con_i_bambini', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_i_bambini', true ) ?: 0,
            'compatibilita_cani'    => get_field( 'compatibilita_con_altri_cani', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_altri_cani', true ) ?: 0,
            'compatibilita_gatti'   => get_field( 'compatibilita_con_i_gatti', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_i_gatti', true ) ?: 0,
            'adatto_principianti'   => get_field( 'adatto_ai_principianti', $razza_id ) ?: get_post_meta( $razza_id, 'adatto_ai_principianti', true ) ?: 0,
        );

        $razze_data[ $razza_id ] = array(
            'id'     => $razza_id,
            'name'   => $razza->post_title,
            'url'    => get_permalink( $razza_id ),
            'image'  => get_the_post_thumbnail_url( $razza_id, 'medium' ),
            'fields' => $fields,
        );

        error_log( 'Comparatore AJAX: Successfully added razza data for: ' . $razza->post_title );
    }

    if ( empty( $razze_data ) ) {
        error_log( 'Comparatore AJAX: No razze data collected' );
        wp_send_json_error( 'No valid razze found' );
        return;
    }

    error_log( 'Comparatore AJAX: Sending success response with ' . count( $razze_data ) . ' razze' );
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
