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
        'posts_per_page' => 12,
        'paged'          => $paged,
    );

    // Search by name
    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    // Build meta query for characteristics
    $meta_query = array( 'relation' => 'AND' );

    // Energia e Livelli di Attività
    if ( $energia > 0 ) {
        $meta_query[] = array(
            'key'     => 'energia_e_livelli_di_attivita',
            'value'   => $energia,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    // Adattabilità ad Appartamento
    if ( $appartamento > 0 ) {
        $meta_query[] = array(
            'key'     => 'adattabilita_appartamento',
            'value'   => $appartamento,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    // Affettuosità
    if ( $affettuosita > 0 ) {
        $meta_query[] = array(
            'key'     => 'affettuosita',
            'value'   => $affettuosita,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    // Tolleranza verso Estranei
    if ( $estranei > 0 ) {
        $meta_query[] = array(
            'key'     => 'tolleranza_estranei',
            'value'   => $estranei,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    // Vocalità
    if ( $vocalita > 0 ) {
        $meta_query[] = array(
            'key'     => 'vocalita_e_predisposizione_ad_abbaiare',
            'value'   => $vocalita,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
    }

    // Compatibile con Bambini
    if ( $bambini > 0 ) {
        $meta_query[] = array(
            'key'     => 'compatibilita_con_i_bambini',
            'value'   => $bambini,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    // Esperienza Richiesta
    if ( $esperienza > 0 ) {
        $meta_query[] = array(
            'key'     => 'livello_esperienza_richiesto',
            'value'   => $esperienza,
            'compare' => '<=',
            'type'    => 'NUMERIC',
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

    // Start output buffering
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

    // Reset post data
    wp_reset_postdata();

    // Prepare response
    $response = array(
        'html'  => $html,
        'found' => $query->found_posts,
        'pages' => $query->max_num_pages,
    );

    // Send success response
    wp_send_json_success( $response );
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
 *
 * Handles the AJAX request for filtering allevamenti archive
 */
function caniincasa_ajax_filter_allevamenti() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_allevamenti_nonce' ) ) {
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
        'post_type'      => 'allevamenti',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
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

    // Execute query
    $query = new WP_Query( $args );

    // Start output buffering
    ob_start();

    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            get_template_part( 'template-parts/content/content', 'allevamento-card' );
        endwhile;
    else :
        ?>
        <div class="no-results">
            <h3>Nessun allevamento trovato</h3>
            <p>Prova a modificare i filtri di ricerca</p>
        </div>
        <?php
    endif;

    // Get the buffered content
    $html = ob_get_clean();

    // Reset post data
    wp_reset_postdata();

    // Prepare response
    $response = array(
        'html'  => $html,
        'found' => $query->found_posts,
        'pages' => $query->max_num_pages,
    );

    // Send success response
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_filter_allevamenti', 'caniincasa_ajax_filter_allevamenti' );
add_action( 'wp_ajax_nopriv_filter_allevamenti', 'caniincasa_ajax_filter_allevamenti' );

/**
 * AJAX Handler: Filter Veterinari
 */
function caniincasa_ajax_filter_veterinari() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_veterinari_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
		return;
	}

	$search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
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
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'  => $html,
		'found' => $query->found_posts,
		'pages' => $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_filter_veterinari', 'caniincasa_ajax_filter_veterinari' );
add_action( 'wp_ajax_nopriv_filter_veterinari', 'caniincasa_ajax_filter_veterinari' );


/**
 * AJAX Handler: Filter Canili
 */
function caniincasa_ajax_filter_canili() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_canili_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
		return;
	}

	$search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
	$order     = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
	$paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	$args = array(
		'post_type'      => 'canili',
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
			<h3>Nessun canile trovato</h3>
			<p>Prova a modificare i filtri di ricerca</p>
		</div>
		<?php
	endif;

	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'  => $html,
		'found' => $query->found_posts,
		'pages' => $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_filter_canili', 'caniincasa_ajax_filter_canili' );
add_action( 'wp_ajax_nopriv_filter_canili', 'caniincasa_ajax_filter_canili' );


/**
 * AJAX Handler: Filter Pensioni per Cani
 */
function caniincasa_ajax_filter_pensioni() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_pensioni_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
		return;
	}

	$search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
	$order     = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
	$paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	$args = array(
		'post_type'      => 'pensioni_per_cani',
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
			<h3>Nessuna pensione trovata</h3>
			<p>Prova a modificare i filtri di ricerca</p>
		</div>
		<?php
	endif;

	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'  => $html,
		'found' => $query->found_posts,
		'pages' => $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_filter_pensioni', 'caniincasa_ajax_filter_pensioni' );
add_action( 'wp_ajax_nopriv_filter_pensioni', 'caniincasa_ajax_filter_pensioni' );


/**
 * AJAX Handler: Filter Centri Cinofili
 */
function caniincasa_ajax_filter_centri() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'filter_centri_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
		return;
	}

	$search    = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$provincia = isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : '';
	$order     = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'name_asc';
	$paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	$args = array(
		'post_type'      => 'centri_cinofili',
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
			<h3>Nessun centro trovato</h3>
			<p>Prova a modificare i filtri di ricerca</p>
		</div>
		<?php
	endif;

	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'  => $html,
		'found' => $query->found_posts,
		'pages' => $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_filter_centri', 'caniincasa_ajax_filter_centri' );
add_action( 'wp_ajax_nopriv_filter_centri', 'caniincasa_ajax_filter_centri' );
