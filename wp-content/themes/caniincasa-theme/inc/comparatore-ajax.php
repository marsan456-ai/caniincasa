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
    // Temporarily skip nonce check for debugging
    // check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    $razze_ids = isset( $_POST['razze_ids'] ) ? array_map( 'intval', $_POST['razze_ids'] ) : array();

    if ( empty( $razze_ids ) || count( $razze_ids ) < 2 || count( $razze_ids ) > 3 ) {
        wp_send_json_error( 'Invalid number of razze' );
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

        // Get all ACF fields with fallback values
        $fields = array(
            // Fisici
            'taglia'            => $taglia,
            'peso'              => get_field( 'peso', $razza_id ) ?: get_post_meta( $razza_id, 'peso', true ),
            'altezza'           => get_field( 'altezza', $razza_id ) ?: get_post_meta( $razza_id, 'altezza', true ),
            'aspettativa_vita'  => get_field( 'aspettativa_di_vita', $razza_id ) ?: get_post_meta( $razza_id, 'aspettativa_di_vita', true ),
            'tipo_pelo'         => get_field( 'tipo_di_pelo', $razza_id ) ?: get_post_meta( $razza_id, 'tipo_di_pelo', true ),

            // Caratteriali (1-5)
            'affettuosita'          => get_field( 'affettuosita', $razza_id ) ?: get_post_meta( $razza_id, 'affettuosita', true ),
            'energia'               => get_field( 'energia_e_livelli_di_attivita', $razza_id ) ?: get_post_meta( $razza_id, 'energia_e_livelli_di_attivita', true ),
            'socialita'             => get_field( 'socialita_con_estranei', $razza_id ) ?: get_post_meta( $razza_id, 'socialita_con_estranei', true ),
            'addestrabilita'        => get_field( 'addestrabilita', $razza_id ) ?: get_post_meta( $razza_id, 'addestrabilita', true ),
            'territorialita'        => get_field( 'territorialita', $razza_id ) ?: get_post_meta( $razza_id, 'territorialita', true ),
            'tendenza_abbaiare'     => get_field( 'tendenza_ad_abbaiare', $razza_id ) ?: get_post_meta( $razza_id, 'tendenza_ad_abbaiare', true ),

            // Cure
            'toelettatura'      => get_field( 'necessita_di_toelettatura', $razza_id ) ?: get_post_meta( $razza_id, 'necessita_di_toelettatura', true ),
            'perdita_pelo'      => get_field( 'perdita_di_pelo', $razza_id ) ?: get_post_meta( $razza_id, 'perdita_di_pelo', true ),
            'esercizio_fisico'  => get_field( 'necessita_di_esercizio', $razza_id ) ?: get_post_meta( $razza_id, 'necessita_di_esercizio', true ),

            // Ambiente
            'adattabilita_appartamento' => get_field( 'adattabilita_allappartamento', $razza_id ) ?: get_post_meta( $razza_id, 'adattabilita_allappartamento', true ),
            'tolleranza_solitudine'     => get_field( 'tolleranza_alla_solitudine', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_alla_solitudine', true ),
            'tolleranza_caldo'          => get_field( 'tolleranza_al_caldo', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_al_caldo', true ),
            'tolleranza_freddo'         => get_field( 'tolleranza_al_freddo', $razza_id ) ?: get_post_meta( $razza_id, 'tolleranza_al_freddo', true ),

            // Famiglia
            'compatibilita_bambini' => get_field( 'compatibilita_con_i_bambini', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_i_bambini', true ),
            'compatibilita_cani'    => get_field( 'compatibilita_con_altri_cani', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_altri_cani', true ),
            'compatibilita_gatti'   => get_field( 'compatibilita_con_i_gatti', $razza_id ) ?: get_post_meta( $razza_id, 'compatibilita_con_i_gatti', true ),
            'adatto_principianti'   => get_field( 'adatto_ai_principianti', $razza_id ) ?: get_post_meta( $razza_id, 'adatto_ai_principianti', true ),
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
        wp_send_json_error( 'No razze found' );
    }

    wp_send_json_success( $razze_data );
}
add_action( 'wp_ajax_get_razze_comparison', 'caniincasa_get_razze_comparison_ajax' );
add_action( 'wp_ajax_nopriv_get_razze_comparison', 'caniincasa_get_razze_comparison_ajax' );
