<?php
/**
 * Custom Post Types: Annunci
 *
 * - Annunci 4 Zampe (adozione, ricerca cani)
 * - Annunci Dogsitter
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register CPT Annunci 4 Zampe
 */
function caniincasa_register_cpt_annunci_4zampe() {
    $labels = array(
        'name'                  => _x( 'Annunci 4 Zampe', 'Post Type General Name', 'caniincasa-core' ),
        'singular_name'         => _x( 'Annuncio', 'Post Type Singular Name', 'caniincasa-core' ),
        'menu_name'             => __( 'Annunci 4 Zampe', 'caniincasa-core' ),
        'name_admin_bar'        => __( 'Annuncio 4 Zampe', 'caniincasa-core' ),
        'all_items'             => __( 'Tutti gli Annunci', 'caniincasa-core' ),
        'add_new_item'          => __( 'Aggiungi Nuovo Annuncio', 'caniincasa-core' ),
        'add_new'               => __( 'Aggiungi Nuovo', 'caniincasa-core' ),
        'new_item'              => __( 'Nuovo Annuncio', 'caniincasa-core' ),
        'edit_item'             => __( 'Modifica Annuncio', 'caniincasa-core' ),
        'update_item'           => __( 'Aggiorna Annuncio', 'caniincasa-core' ),
        'view_item'             => __( 'Visualizza Annuncio', 'caniincasa-core' ),
        'search_items'          => __( 'Cerca Annuncio', 'caniincasa-core' ),
        'not_found'             => __( 'Nessun annuncio trovato', 'caniincasa-core' ),
    );

    $args = array(
        'label'                 => __( 'Annunci 4 Zampe', 'caniincasa-core' ),
        'description'           => __( 'Annunci per adozione e ricerca cani', 'caniincasa-core' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'author', 'custom-fields' ),
        'taxonomies'            => array( 'provincia' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => 'caniincasa-annunci',
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-megaphone',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'annunci',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'annunci-4zampe',
        'rewrite'               => array(
            'slug'       => 'annunci',
            'with_front' => false,
        ),
    );

    register_post_type( 'annunci_4zampe', $args );
}
add_action( 'init', 'caniincasa_register_cpt_annunci_4zampe', 0 );

/**
 * Register CPT Annunci Dogsitter
 */
function caniincasa_register_cpt_annunci_dogsitter() {
    $labels = array(
        'name'                  => _x( 'Annunci Dogsitter', 'Post Type General Name', 'caniincasa-core' ),
        'singular_name'         => _x( 'Annuncio Dogsitter', 'Post Type Singular Name', 'caniincasa-core' ),
        'menu_name'             => __( 'Annunci Dogsitter', 'caniincasa-core' ),
        'name_admin_bar'        => __( 'Annuncio Dogsitter', 'caniincasa-core' ),
        'all_items'             => __( 'Tutti gli Annunci', 'caniincasa-core' ),
        'add_new_item'          => __( 'Aggiungi Nuovo Annuncio', 'caniincasa-core' ),
        'add_new'               => __( 'Aggiungi Nuovo', 'caniincasa-core' ),
        'new_item'              => __( 'Nuovo Annuncio', 'caniincasa-core' ),
        'edit_item'             => __( 'Modifica Annuncio', 'caniincasa-core' ),
        'update_item'           => __( 'Aggiorna Annuncio', 'caniincasa-core' ),
        'view_item'             => __( 'Visualizza Annuncio', 'caniincasa-core' ),
        'search_items'          => __( 'Cerca Annuncio', 'caniincasa-core' ),
        'not_found'             => __( 'Nessun annuncio trovato', 'caniincasa-core' ),
    );

    $args = array(
        'label'                 => __( 'Annunci Dogsitter', 'caniincasa-core' ),
        'description'           => __( 'Annunci per servizi di dogsitting', 'caniincasa-core' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'author', 'custom-fields' ),
        'taxonomies'            => array( 'provincia' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => 'caniincasa-annunci',
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-businesswoman',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'annunci-dogsitter',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'annunci-dogsitter',
        'rewrite'               => array(
            'slug'       => 'annunci-dogsitter',
            'with_front' => false,
        ),
    );

    register_post_type( 'annunci_dogsitter', $args );
}
add_action( 'init', 'caniincasa_register_cpt_annunci_dogsitter', 0 );

/**
 * Add top-level menu for Annunci
 */
function caniincasa_annunci_menu() {
    add_menu_page(
        __( 'Annunci', 'caniincasa-core' ),
        __( 'Annunci', 'caniincasa-core' ),
        'edit_posts',
        'caniincasa-annunci',
        '',
        'dashicons-format-status',
        22
    );
}
add_action( 'admin_menu', 'caniincasa_annunci_menu' );

/**
 * Set annuncio status to pending on submission
 */
function caniincasa_set_annuncio_pending( $post_id, $post, $update ) {
    // Only for new posts (not updates)
    if ( $update ) {
        return;
    }

    // Check if this is an annuncio CPT
    if ( ! in_array( $post->post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    // Check if moderation is enabled
    $moderation = get_option( 'caniincasa_annunci_moderation', true );
    if ( ! $moderation ) {
        return;
    }

    // If submitted by non-admin, set to pending
    if ( ! current_user_can( 'administrator' ) && $post->post_status !== 'pending' ) {
        remove_action( 'save_post', 'caniincasa_set_annuncio_pending', 10 );

        wp_update_post( array(
            'ID'          => $post_id,
            'post_status' => 'pending',
        ) );

        add_action( 'save_post', 'caniincasa_set_annuncio_pending', 10, 3 );
    }
}
add_action( 'save_post', 'caniincasa_set_annuncio_pending', 10, 3 );

/**
 * Handle annuncio expiration
 */
function caniincasa_check_annuncio_expiration() {
    $expiry_days = get_option( 'caniincasa_annunci_expiry_days', 30 );

    $args = array(
        'post_type'      => array( 'annunci_4zampe', 'annunci_dogsitter' ),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'scadenza_annuncio',
                'value'   => current_time( 'Y-m-d' ),
                'compare' => '<=',
                'type'    => 'DATE',
            ),
        ),
    );

    $expired_posts = new WP_Query( $args );

    if ( $expired_posts->have_posts() ) {
        while ( $expired_posts->have_posts() ) {
            $expired_posts->the_post();

            // Mark as expired (draft status)
            wp_update_post( array(
                'ID'          => get_the_ID(),
                'post_status' => 'draft',
            ) );

            // Send notification to author
            $author_id = get_post_field( 'post_author', get_the_ID() );
            $author_email = get_the_author_meta( 'user_email', $author_id );

            if ( $author_email ) {
                $subject = sprintf( __( 'Il tuo annuncio "%s" è scaduto', 'caniincasa-core' ), get_the_title() );
                $message = sprintf(
                    __( "Ciao,\n\nIl tuo annuncio \"%s\" è scaduto e non è più visibile.\n\nPuoi rinnovarlo dalla tua dashboard: %s\n\nGrazie!", 'caniincasa-core' ),
                    get_the_title(),
                    home_url( '/dashboard' )
                );

                wp_mail( $author_email, $subject, $message );
            }
        }

        wp_reset_postdata();
    }
}

// Check expiration daily
if ( ! wp_next_scheduled( 'caniincasa_check_expiration' ) ) {
    wp_schedule_event( time(), 'daily', 'caniincasa_check_expiration' );
}
add_action( 'caniincasa_check_expiration', 'caniincasa_check_annuncio_expiration' );

/**
 * Calculate and set expiration date on publish
 */
function caniincasa_set_expiration_date( $post_id, $post, $update ) {
    // Only for annunci
    if ( ! in_array( $post->post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    // Only on publish
    if ( $post->post_status !== 'publish' ) {
        return;
    }

    // Check if expiration date already set
    $existing_expiry = get_post_meta( $post_id, 'scadenza_annuncio', true );
    if ( $existing_expiry ) {
        return;
    }

    // Get custom expiry or default
    $custom_expiry_days = get_post_meta( $post_id, 'giorni_scadenza', true );
    $expiry_days = $custom_expiry_days ? intval( $custom_expiry_days ) : get_option( 'caniincasa_annunci_expiry_days', 30 );

    // Calculate expiration date
    $expiry_date = date( 'Y-m-d', strtotime( "+{$expiry_days} days" ) );

    // Save expiration date
    update_post_meta( $post_id, 'scadenza_annuncio', $expiry_date );
}
add_action( 'save_post', 'caniincasa_set_expiration_date', 10, 3 );

/**
 * Send notification email on status change
 */
function caniincasa_annuncio_status_notification( $new_status, $old_status, $post ) {
    // Only for annunci
    if ( ! in_array( $post->post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    // Don't send on auto-draft or if status hasn't changed
    if ( $new_status === $old_status || $old_status === 'auto-draft' ) {
        return;
    }

    $author_email = get_the_author_meta( 'user_email', $post->post_author );

    if ( ! $author_email ) {
        return;
    }

    $subject = '';
    $message = '';
    $site_name = get_bloginfo( 'name' );

    switch ( $new_status ) {
        case 'pending':
            $subject = sprintf( __( '[%s] Annuncio in attesa di approvazione', 'caniincasa-core' ), $site_name );
            $message = sprintf(
                __( "Ciao,\n\nIl tuo annuncio \"%s\" è stato ricevuto ed è in attesa di approvazione.\n\nRiceverai una notifica quando sarà pubblicato.\n\nGrazie!", 'caniincasa-core' ),
                $post->post_title
            );
            break;

        case 'publish':
            if ( $old_status === 'pending' ) {
                $subject = sprintf( __( '[%s] Annuncio approvato e pubblicato', 'caniincasa-core' ), $site_name );
                $message = sprintf(
                    __( "Ciao,\n\nBuone notizie! Il tuo annuncio \"%s\" è stato approvato ed è ora visibile.\n\nVedi l'annuncio: %s\n\nGrazie!", 'caniincasa-core' ),
                    $post->post_title,
                    get_permalink( $post->ID )
                );
            }
            break;

        case 'trash':
            $subject = sprintf( __( '[%s] Annuncio rimosso', 'caniincasa-core' ), $site_name );
            $message = sprintf(
                __( "Ciao,\n\nIl tuo annuncio \"%s\" è stato rimosso.\n\nSe pensi che sia un errore, contattaci.\n\nGrazie!", 'caniincasa-core' ),
                $post->post_title
            );
            break;
    }

    if ( $subject && $message ) {
        wp_mail( $author_email, $subject, $message );
    }
}
add_action( 'transition_post_status', 'caniincasa_annuncio_status_notification', 10, 3 );

/**
 * Custom columns for annunci
 */
function caniincasa_annunci_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        if ( $key === 'date' ) {
            $new_columns['tipo'] = __( 'Tipo', 'caniincasa-core' );
            $new_columns['stato'] = __( 'Stato', 'caniincasa-core' );
            $new_columns['scadenza'] = __( 'Scadenza', 'caniincasa-core' );
        }
        $new_columns[ $key ] = $value;
    }

    return $new_columns;
}
add_filter( 'manage_annunci_4zampe_posts_columns', 'caniincasa_annunci_columns' );
add_filter( 'manage_annunci_dogsitter_posts_columns', 'caniincasa_annunci_columns' );

/**
 * Custom column content for annunci
 */
function caniincasa_annunci_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'tipo':
            $tipo = get_post_meta( $post_id, 'tipo_annuncio', true );
            if ( $tipo ) {
                echo '<span class="annuncio-tipo tipo-' . esc_attr( $tipo ) . '">';
                echo esc_html( ucfirst( $tipo ) );
                echo '</span>';
            } else {
                echo '—';
            }
            break;

        case 'stato':
            $post_status = get_post_status( $post_id );
            $status_obj = get_post_status_object( $post_status );
            if ( $status_obj ) {
                echo '<span class="annuncio-status status-' . esc_attr( $post_status ) . '">';
                echo esc_html( $status_obj->label );
                echo '</span>';
            }
            break;

        case 'scadenza':
            $scadenza = get_post_meta( $post_id, 'scadenza_annuncio', true );
            if ( $scadenza ) {
                $today = date( 'Y-m-d' );
                $class = ( $scadenza < $today ) ? 'scaduto' : 'attivo';

                echo '<span class="annuncio-scadenza ' . esc_attr( $class ) . '">';
                echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $scadenza ) ) );
                echo '</span>';
            } else {
                echo '—';
            }
            break;
    }
}
add_action( 'manage_annunci_4zampe_posts_custom_column', 'caniincasa_annunci_column_content', 10, 2 );
add_action( 'manage_annunci_dogsitter_posts_custom_column', 'caniincasa_annunci_column_content', 10, 2 );
