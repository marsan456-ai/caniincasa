<?php
/**
 * AJAX Handlers for Caniincasa Core Plugin
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// HELPER FUNCTIONS (DRY)
// =========================================================================

/**
 * Generate pagination HTML for AJAX filter results
 *
 * @param int    $current_page Current page number
 * @param int    $total_pages  Total number of pages
 * @param string $css_class    CSS class for pagination wrapper (default: 'strutture-pagination')
 * @param string $aria_label   ARIA label for navigation (default: 'Navigazione risultati')
 * @return string HTML pagination markup
 */
function caniincasa_generate_pagination_html( $current_page, $total_pages, $css_class = 'strutture-pagination', $aria_label = 'Navigazione risultati' ) {
    if ( $total_pages <= 1 ) {
        return '';
    }

    $current_page = max( 1, $current_page );

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $css_class ); ?>">
        <nav class="pagination-nav" role="navigation" aria-label="<?php echo esc_attr( $aria_label ); ?>">
            <ul class="pagination-list">
                <?php
                // Previous button
                if ( $current_page > 1 ) :
                    ?>
                    <li class="pagination-item pagination-prev">
                        <a href="?paged=<?php echo ( $current_page - 1 ); ?>" data-page="<?php echo ( $current_page - 1 ); ?>" class="pagination-link">
                            <span aria-hidden="true">&laquo;</span> Precedente
                        </a>
                    </li>
                    <?php
                endif;

                // First page
                if ( $current_page > 3 ) :
                    ?>
                    <li class="pagination-item">
                        <a href="?paged=1" data-page="1" class="pagination-link">1</a>
                    </li>
                    <?php if ( $current_page > 4 ) : ?>
                        <li class="pagination-item pagination-dots"><span>...</span></li>
                    <?php endif; ?>
                    <?php
                endif;

                // Pages around current
                for ( $i = max( 1, $current_page - 2 ); $i <= min( $total_pages, $current_page + 2 ); $i++ ) :
                    if ( $i == $current_page ) :
                        ?>
                        <li class="pagination-item pagination-current">
                            <span class="pagination-link current" aria-current="page"><?php echo $i; ?></span>
                        </li>
                        <?php
                    else :
                        ?>
                        <li class="pagination-item">
                            <a href="?paged=<?php echo $i; ?>" data-page="<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                        </li>
                        <?php
                    endif;
                endfor;

                // Last page
                if ( $current_page < $total_pages - 2 ) :
                    if ( $current_page < $total_pages - 3 ) :
                        ?>
                        <li class="pagination-item pagination-dots"><span>...</span></li>
                        <?php
                    endif;
                    ?>
                    <li class="pagination-item">
                        <a href="?paged=<?php echo $total_pages; ?>" data-page="<?php echo $total_pages; ?>" class="pagination-link"><?php echo $total_pages; ?></a>
                    </li>
                    <?php
                endif;

                // Next button
                if ( $current_page < $total_pages ) :
                    ?>
                    <li class="pagination-item pagination-next">
                        <a href="?paged=<?php echo ( $current_page + 1 ); ?>" data-page="<?php echo ( $current_page + 1 ); ?>" class="pagination-link">
                            Successiva <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php
                endif;
                ?>
            </ul>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Apply standard ordering to WP_Query args
 *
 * @param array  $args  WP_Query arguments
 * @param string $order Order parameter from request
 * @return array Modified args with ordering applied
 */
function caniincasa_apply_ordering( $args, $order ) {
    switch ( $order ) {
        case 'name_asc':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'name_desc':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;
        case 'date_desc':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
            break;
        default:
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
    }
    return $args;
}

/**
 * Generic structure filter handler
 * Handles filtering for allevamenti, veterinari, canili, pensioni, centri
 *
 * @param string $post_type     Post type to query
 * @param string $nonce_action  Nonce action name
 * @param string $template_part Template part name (e.g., 'allevamento-card')
 * @param string $no_results    No results message
 * @param string $pagination_class CSS class for pagination
 * @param string $aria_label    ARIA label for pagination
 */
function caniincasa_filter_structure_handler( $post_type, $nonce_action, $template_part, $no_results, $pagination_class = 'strutture-pagination', $aria_label = 'Navigazione risultati' ) {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $nonce_action ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        return;
    }

    // Get filter parameters
    $search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    $provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
    $order     = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
    $paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

    // Build WP_Query args
    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'paged'          => $paged,
    );

    // Search by name
    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    // Filter by provincia taxonomy
    if ( ! empty( $provincia ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'provincia',
                'field'    => 'slug',
                'terms'    => $provincia,
            ),
        );
    }

    // Apply ordering
    $args = caniincasa_apply_ordering( $args, $order );

    // Execute query
    $query = new WP_Query( $args );

    // Render results
    ob_start();
    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', $template_part );
        endwhile;
    else :
        ?>
        <div class="no-results">
            <h3><?php echo esc_html( $no_results ); ?></h3>
            <p>Prova a modificare i filtri di ricerca</p>
        </div>
        <?php
    endif;
    $html = ob_get_clean();

    // Generate pagination
    $pagination = caniincasa_generate_pagination_html( $paged, $query->max_num_pages, $pagination_class, $aria_label );

    wp_reset_postdata();

    // Send response
    wp_send_json_success( array(
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'pages'      => $query->max_num_pages,
    ) );
}

// =========================================================================
// RAZZE FILTER (custom logic, not using generic handler)
// =========================================================================

/**
 * AJAX Handler: Filter Razze
 *
 * Handles the AJAX request for filtering razze di cani archive
 * Returns JSON with HTML and count
 */
function caniincasa_ajax_filter_razze() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_razze_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        return;
    }

    // Get filter parameters
    $search      = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    $energia     = isset( $_POST['energia'] ) ? absint( $_POST['energia'] ) : 0;
    $appartamento = isset( $_POST['appartamento'] ) ? absint( $_POST['appartamento'] ) : 0;
    $affettuosita = isset( $_POST['affettuosita'] ) ? absint( $_POST['affettuosita'] ) : 0;
    $estranei    = isset( $_POST['estranei'] ) ? absint( $_POST['estranei'] ) : 0;
    $vocalita    = isset( $_POST['vocalita'] ) ? absint( $_POST['vocalita'] ) : 0;
    $bambini     = isset( $_POST['bambini'] ) ? absint( $_POST['bambini'] ) : 0;
    $esperienza  = isset( $_POST['esperienza'] ) ? absint( $_POST['esperienza'] ) : 0;
    $order       = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
    $paged       = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

    // Build WP_Query args
    $args = array(
        'post_type'      => 'razze_di_cani',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'paged'          => $paged,
    );

    // Search by name
    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    // Build meta query for characteristics - TUTTI I FILTRI ATTIVI
    $meta_query = array( 'relation' => 'AND' );

    // DEBUG: Log dei parametri ricevuti
    error_log('FILTRI RAZZE - Parametri: energia=' . $energia . ', appartamento=' . $appartamento .
              ', affettuosita=' . $affettuosita . ', estranei=' . $estranei .
              ', vocalita=' . $vocalita . ', bambini=' . $bambini . ', esperienza=' . $esperienza);

    // Energia e Livelli di Attività
    // Logica: range ±0.7 per trovare razze simili
    if ( $energia > 0 ) {
        $meta_query[] = array(
            'key'     => 'energia_e_livelli_di_attivita',
            'value'   => array( max(1, $energia - 0.7), min(5, $energia + 0.7) ),
            'compare' => 'BETWEEN',
            'type'    => 'DECIMAL',
        );
    }

    // Adattabilità ad Appartamento
    // Logica: razze ALMENO adatte quanto richiesto
    if ( $appartamento > 0 ) {
        $meta_query[] = array(
            'key'     => 'adattabilita_appartamento',
            'value'   => max(1, $appartamento - 0.5),
            'compare' => '>=',
            'type'    => 'DECIMAL',
        );
    }

    // Affettuosità
    // Logica: razze ALMENO affettuose quanto richiesto
    if ( $affettuosita > 0 ) {
        $meta_query[] = array(
            'key'     => 'affettuosita',
            'value'   => max(1, $affettuosita - 0.5),
            'compare' => '>=',
            'type'    => 'DECIMAL',
        );
    }

    // Tolleranza verso Estranei
    // Logica: razze ALMENO tolleranti quanto richiesto
    if ( $estranei > 0 ) {
        $meta_query[] = array(
            'key'     => 'tolleranza_estranei',
            'value'   => max(1, $estranei - 0.5),
            'compare' => '>=',
            'type'    => 'DECIMAL',
        );
    }

    // Vocalità
    // Logica: razze AL MASSIMO vocali quanto indicato
    if ( $vocalita > 0 ) {
        $meta_query[] = array(
            'key'     => 'vocalita_e_predisposizione_ad_abbaiare',
            'value'   => min(5, $vocalita + 0.5),
            'compare' => '<=',
            'type'    => 'DECIMAL',
        );
    }

    // Compatibilità con Bambini
    // Logica: razze ALMENO compatibili quanto richiesto
    if ( $bambini > 0 ) {
        $meta_query[] = array(
            'key'     => 'compatibilita_con_i_bambini',
            'value'   => max(1, $bambini - 0.5),
            'compare' => '>=',
            'type'    => 'DECIMAL',
        );
    }

    // Esperienza Richiesta
    // Logica: razze che richiedono AL MASSIMO l'esperienza indicata
    if ( $esperienza > 0 ) {
        $meta_query[] = array(
            'key'     => 'livello_esperienza_richiesto',
            'value'   => min(5, $esperienza + 0.5),
            'compare' => '<=',
            'type'    => 'DECIMAL',
        );
    }

    // Add meta query to args if not empty
    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    // Handle ordering
    switch ( $order ) {
        case 'name_asc':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;

        case 'name_desc':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;

        case 'energia_desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'energia_e_livelli_di_attivita';
            $args['order']    = 'DESC';
            break;

        case 'energia_asc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'energia_e_livelli_di_attivita';
            $args['order']    = 'ASC';
            break;

        case 'affettuosita_desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'affettuosita';
            $args['order']    = 'DESC';
            break;

        case 'affettuosita_asc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'affettuosita';
            $args['order']    = 'ASC';
            break;

        default:
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
    }

    // Execute query
    $query = new WP_Query( $args );

    // Start output buffering for razze cards
    ob_start();

    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', 'razza-card' );
        endwhile;
    else :
        ?>
        <div class="no-results">
            <h3>Nessuna razza trovata</h3>
            <p>Prova a modificare i filtri di ricerca</p>
        </div>
        <?php
    endif;

    // Get the buffered content
    $html = ob_get_clean();

    // Generate pagination using helper function
    $pagination = caniincasa_generate_pagination_html( $paged, $query->max_num_pages, 'razze-pagination', 'Navigazione razze' );

    wp_reset_postdata();

    // Send response
    wp_send_json_success( array(
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'pages'      => $query->max_num_pages,
    ) );
}
add_action( 'wp_ajax_filter_razze', 'caniincasa_ajax_filter_razze' );
add_action( 'wp_ajax_nopriv_filter_razze', 'caniincasa_ajax_filter_razze' );


/**
 * AJAX Handler: Load More Razze
 *
 * For infinite scroll or load more button functionality
 */
function caniincasa_ajax_load_more_razze() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'caniincasa_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        return;
    }

    $paged = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

    $args = array(
        'post_type'      => 'razze_di_cani',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', 'razza-card' );
        endwhile;
    endif;

    $html = ob_get_clean();
    wp_reset_postdata();

    $response = array(
        'html'     => $html,
        'has_more' => $paged < $query->max_num_pages,
    );

    wp_send_json_success( $response );
}
add_action( 'wp_ajax_load_more_razze', 'caniincasa_ajax_load_more_razze' );
add_action( 'wp_ajax_nopriv_load_more_razze', 'caniincasa_ajax_load_more_razze' );


/**
 * AJAX Handler: Get Related Razze
 *
 * Fetches razze related to the current one based on taglia or gruppo
 */
function caniincasa_ajax_get_related_razze() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'caniincasa_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        return;
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

    if ( ! $post_id ) {
        wp_send_json_error( array( 'message' => 'Invalid post ID' ) );
        return;
    }

    // Get current post taxonomies
    $taglia_terms = get_the_terms( $post_id, 'razza_taglia' );
    $gruppo_terms = get_the_terms( $post_id, 'razza_gruppo' );

    $tax_query = array( 'relation' => 'OR' );

    if ( $taglia_terms && ! is_wp_error( $taglia_terms ) ) {
        $taglia_ids = wp_list_pluck( $taglia_terms, 'term_id' );
        $tax_query[] = array(
            'taxonomy' => 'razza_taglia',
            'field'    => 'term_id',
            'terms'    => $taglia_ids,
        );
    }

    if ( $gruppo_terms && ! is_wp_error( $gruppo_terms ) ) {
        $gruppo_ids = wp_list_pluck( $gruppo_terms, 'term_id' );
        $tax_query[] = array(
            'taxonomy' => 'razza_gruppo',
            'field'    => 'term_id',
            'terms'    => $gruppo_ids,
        );
    }

    $args = array(
        'post_type'      => 'razze_di_cani',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'post__not_in'   => array( $post_id ),
        'orderby'        => 'rand',
    );

    if ( count( $tax_query ) > 1 ) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', 'razza-card' );
        endwhile;
    endif;

    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_get_related_razze', 'caniincasa_ajax_get_related_razze' );
add_action( 'wp_ajax_nopriv_get_related_razze', 'caniincasa_ajax_get_related_razze' );


/**
 * AJAX Handler: Filter Allevamenti
 * Uses generic handler
 */
function caniincasa_ajax_filter_allevamenti() {
    caniincasa_filter_structure_handler(
        'allevamenti',
        'filter_allevamenti_nonce',
        'allevamento-card',
        'Nessun allevamento trovato',
        'strutture-pagination',
        'Navigazione allevamenti'
    );
}
add_action( 'wp_ajax_filter_allevamenti', 'caniincasa_ajax_filter_allevamenti' );
add_action( 'wp_ajax_nopriv_filter_allevamenti', 'caniincasa_ajax_filter_allevamenti' );

/**
 * AJAX Handler: Filter Veterinari
 * Custom handler due to servizi meta filter
 */
function caniincasa_ajax_filter_veterinari() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'filter_veterinari_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        return;
    }

    $search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    $provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
    $servizi   = isset( $_POST['servizi'] ) ? array_map( 'sanitize_text_field', $_POST['servizi'] ) : array();
    $order     = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
    $paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

    $args = array(
        'post_type'      => 'veterinari',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
    );

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    if ( ! empty( $provincia ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'provincia',
                'field'    => 'slug',
                'terms'    => $provincia,
            ),
        );
    }

    // Filter by servizi (OR relation - at least one service)
    if ( ! empty( $servizi ) ) {
        $meta_query = array( 'relation' => 'OR' );
        foreach ( $servizi as $servizio ) {
            $meta_query[] = array(
                'key'     => 'servizi',
                'value'   => $servizio,
                'compare' => 'LIKE',
            );
        }
        $args['meta_query'] = $meta_query;
    }

    // Apply ordering using helper
    $args = caniincasa_apply_ordering( $args, $order );

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', 'struttura-card' );
        endwhile;
    else :
        ?>
        <div class="no-results">
            <h3>Nessun veterinario trovato</h3>
            <p>Prova a modificare i filtri di ricerca</p>
        </div>
        <?php
    endif;
    $html = ob_get_clean();

    // Generate pagination using helper
    $pagination = caniincasa_generate_pagination_html( $paged, $query->max_num_pages, 'strutture-pagination', 'Navigazione veterinari' );

    wp_reset_postdata();

    wp_send_json_success( array(
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'pages'      => $query->max_num_pages,
    ) );
}
add_action( 'wp_ajax_filter_veterinari', 'caniincasa_ajax_filter_veterinari' );
add_action( 'wp_ajax_nopriv_filter_veterinari', 'caniincasa_ajax_filter_veterinari' );


/**
 * AJAX Handler: Filter Canili
 * Uses generic handler
 */
function caniincasa_ajax_filter_canili() {
    caniincasa_filter_structure_handler(
        'canili',
        'filter_canili_nonce',
        'struttura-card',
        'Nessun canile trovato',
        'strutture-pagination',
        'Navigazione canili'
    );
}
add_action( 'wp_ajax_filter_canili', 'caniincasa_ajax_filter_canili' );
add_action( 'wp_ajax_nopriv_filter_canili', 'caniincasa_ajax_filter_canili' );


/**
 * AJAX Handler: Filter Pensioni per Cani
 * Uses generic handler
 */
function caniincasa_ajax_filter_pensioni() {
    caniincasa_filter_structure_handler(
        'pensioni_per_cani',
        'filter_pensioni_nonce',
        'struttura-card',
        'Nessuna pensione trovata',
        'strutture-pagination',
        'Navigazione pensioni'
    );
}
add_action( 'wp_ajax_filter_pensioni', 'caniincasa_ajax_filter_pensioni' );
add_action( 'wp_ajax_nopriv_filter_pensioni', 'caniincasa_ajax_filter_pensioni' );


/**
 * AJAX Handler: Filter Centri Cinofili
 * Uses generic handler
 */
function caniincasa_ajax_filter_centri() {
    caniincasa_filter_structure_handler(
        'centri_cinofili',
        'filter_centri_nonce',
        'struttura-card',
        'Nessun centro trovato',
        'strutture-pagination',
        'Navigazione centri'
    );
}
add_action( 'wp_ajax_filter_centri', 'caniincasa_ajax_filter_centri' );
add_action( 'wp_ajax_nopriv_filter_centri', 'caniincasa_ajax_filter_centri' );
