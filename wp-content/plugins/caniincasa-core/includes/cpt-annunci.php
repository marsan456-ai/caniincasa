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

    // Add submenu for pending annunci (admin only)
    if ( current_user_can( 'administrator' ) ) {
        add_submenu_page(
            'caniincasa-annunci',
            __( 'Approvazione Annunci', 'caniincasa-core' ),
            __( 'Approvazione Annunci', 'caniincasa-core' ),
            'administrator',
            'caniincasa-annunci-approval',
            'caniincasa_render_annunci_approval_page'
        );
    }
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

/**
 * Render Annunci Approval Page
 */
function caniincasa_render_annunci_approval_page() {
    // Handle bulk actions
    if ( isset( $_POST['caniincasa_bulk_action'] ) && check_admin_referer( 'caniincasa_annunci_approval', 'caniincasa_approval_nonce' ) ) {
        $action = sanitize_text_field( $_POST['bulk_action'] );
        $post_ids = isset( $_POST['annunci_ids'] ) ? array_map( 'absint', $_POST['annunci_ids'] ) : array();

        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $post_id ) {
                if ( $action === 'approve' ) {
                    wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
                } elseif ( $action === 'reject' ) {
                    wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );
                }
            }

            $message = ( $action === 'approve' )
                ? sprintf( __( '%d annunci approvati.', 'caniincasa-core' ), count( $post_ids ) )
                : sprintf( __( '%d annunci rifiutati.', 'caniincasa-core' ), count( $post_ids ) );

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        }
    }

    // Get pending annunci
    $args_4zampe = array(
        'post_type'      => 'annunci_4zampe',
        'post_status'    => 'pending',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
    );

    $args_dogsitter = array(
        'post_type'      => 'annunci_dogsitter',
        'post_status'    => 'pending',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
    );

    $pending_4zampe = new WP_Query( $args_4zampe );
    $pending_dogsitter = new WP_Query( $args_dogsitter );

    $total_pending = $pending_4zampe->found_posts + $pending_dogsitter->found_posts;

    ?>
    <div class="wrap">
        <h1><?php _e( 'Approvazione Annunci', 'caniincasa-core' ); ?></h1>

        <div class="notice notice-info">
            <p>
                <strong><?php _e( 'Annunci in attesa di approvazione:', 'caniincasa-core' ); ?></strong>
                <?php echo esc_html( $total_pending ); ?>
            </p>
        </div>

        <?php if ( $total_pending === 0 ) : ?>
            <p><?php _e( 'Nessun annuncio in attesa di approvazione.', 'caniincasa-core' ); ?></p>
        <?php else : ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'caniincasa_annunci_approval', 'caniincasa_approval_nonce' ); ?>

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php _e( 'Azioni multiple', 'caniincasa-core' ); ?></option>
                            <option value="approve"><?php _e( 'Approva', 'caniincasa-core' ); ?></option>
                            <option value="reject"><?php _e( 'Rifiuta', 'caniincasa-core' ); ?></option>
                        </select>
                        <input type="submit" name="caniincasa_bulk_action" class="button action" value="<?php esc_attr_e( 'Applica', 'caniincasa-core' ); ?>">
                    </div>
                </div>

                <!-- Annunci 4 Zampe -->
                <?php if ( $pending_4zampe->have_posts() ) : ?>
                    <h2><?php _e( 'Annunci 4 Zampe', 'caniincasa-core' ); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" class="select-all" data-group="4zampe">
                                </th>
                                <th><?php _e( 'Titolo', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Autore', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Tipo', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Data', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Azioni', 'caniincasa-core' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ( $pending_4zampe->have_posts() ) : $pending_4zampe->the_post(); ?>
                                <?php
                                $post_id = get_the_ID();
                                $tipo = get_post_meta( $post_id, 'tipo_annuncio', true );
                                $author_id = get_the_author_meta( 'ID' );
                                $author_name = get_the_author();
                                ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="annunci_ids[]" value="<?php echo esc_attr( $post_id ); ?>" class="annuncio-checkbox-4zampe">
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
                                                <?php the_title(); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small><a href="<?php the_permalink(); ?>" target="_blank"><?php _e( 'Anteprima', 'caniincasa-core' ); ?></a></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $author_id ) ); ?>">
                                            <?php echo esc_html( $author_name ); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html( ucfirst( $tipo ) ); ?></td>
                                    <td><?php echo get_the_date(); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=approve_annuncio&post_id=' . $post_id ), 'approve_annuncio_' . $post_id ) ); ?>" class="button button-primary button-small">
                                            <?php _e( 'Approva', 'caniincasa-core' ); ?>
                                        </a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=reject_annuncio&post_id=' . $post_id ), 'reject_annuncio_' . $post_id ) ); ?>" class="button button-small">
                                            <?php _e( 'Rifiuta', 'caniincasa-core' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <br>
                <?php endif; ?>

                <!-- Annunci Dogsitter -->
                <?php if ( $pending_dogsitter->have_posts() ) : ?>
                    <h2><?php _e( 'Annunci Dogsitter', 'caniincasa-core' ); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" class="select-all" data-group="dogsitter">
                                </th>
                                <th><?php _e( 'Titolo', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Autore', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Data', 'caniincasa-core' ); ?></th>
                                <th><?php _e( 'Azioni', 'caniincasa-core' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ( $pending_dogsitter->have_posts() ) : $pending_dogsitter->the_post(); ?>
                                <?php
                                $post_id = get_the_ID();
                                $author_id = get_the_author_meta( 'ID' );
                                $author_name = get_the_author();
                                ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="annunci_ids[]" value="<?php echo esc_attr( $post_id ); ?>" class="annuncio-checkbox-dogsitter">
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
                                                <?php the_title(); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small><a href="<?php the_permalink(); ?>" target="_blank"><?php _e( 'Anteprima', 'caniincasa-core' ); ?></a></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $author_id ) ); ?>">
                                            <?php echo esc_html( $author_name ); ?>
                                        </a>
                                    </td>
                                    <td><?php echo get_the_date(); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=approve_annuncio&post_id=' . $post_id ), 'approve_annuncio_' . $post_id ) ); ?>" class="button button-primary button-small">
                                            <?php _e( 'Approva', 'caniincasa-core' ); ?>
                                        </a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=reject_annuncio&post_id=' . $post_id ), 'reject_annuncio_' . $post_id ) ); ?>" class="button button-small">
                                            <?php _e( 'Rifiuta', 'caniincasa-core' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php wp_reset_postdata(); ?>
            </form>

            <script>
            jQuery(document).ready(function($) {
                $('.select-all').on('change', function() {
                    var group = $(this).data('group');
                    $('.annuncio-checkbox-' + group).prop('checked', $(this).prop('checked'));
                });
            });
            </script>

        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle single annuncio approval
 */
function caniincasa_handle_approve_annuncio() {
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

    if ( ! $post_id || ! check_admin_referer( 'approve_annuncio_' . $post_id ) ) {
        wp_die( __( 'Operazione non valida.', 'caniincasa-core' ) );
    }

    wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );

    wp_redirect( admin_url( 'admin.php?page=caniincasa-annunci-approval&approved=1' ) );
    exit;
}
add_action( 'admin_post_approve_annuncio', 'caniincasa_handle_approve_annuncio' );

/**
 * Handle single annuncio rejection
 */
function caniincasa_handle_reject_annuncio() {
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

    if ( ! $post_id || ! check_admin_referer( 'reject_annuncio_' . $post_id ) ) {
        wp_die( __( 'Operazione non valida.', 'caniincasa-core' ) );
    }

    wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );

    wp_redirect( admin_url( 'admin.php?page=caniincasa-annunci-approval&rejected=1' ) );
    exit;
}
add_action( 'admin_post_reject_annuncio', 'caniincasa_handle_reject_annuncio' );

/**
 * Add Author column to Annunci admin list
 */
function caniincasa_annunci_add_author_column( $columns ) {
    // Insert author column after title
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( $key === 'title' ) {
            $new_columns['author'] = __( 'Autore', 'caniincasa-core' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_annunci_4zampe_posts_columns', 'caniincasa_annunci_add_author_column' );
add_filter( 'manage_annunci_dogsitter_posts_columns', 'caniincasa_annunci_add_author_column' );

/**
 * Display Author column content
 */
function caniincasa_annunci_author_column_content( $column, $post_id ) {
    if ( $column === 'author' ) {
        $author_id = get_post_field( 'post_author', $post_id );
        $author = get_userdata( $author_id );

        if ( $author ) {
            $edit_link = add_query_arg(
                array(
                    'user_id' => $author_id,
                ),
                admin_url( 'user-edit.php' )
            );

            echo '<a href="' . esc_url( $edit_link ) . '">';
            echo esc_html( $author->display_name );
            echo '</a>';
            echo '<br><small>' . esc_html( $author->user_email ) . '</small>';
        }
    }
}
add_action( 'manage_annunci_4zampe_posts_custom_column', 'caniincasa_annunci_author_column_content', 10, 2 );
add_action( 'manage_annunci_dogsitter_posts_custom_column', 'caniincasa_annunci_author_column_content', 10, 2 );

/**
 * Make Author column sortable
 */
function caniincasa_annunci_sortable_columns( $columns ) {
    $columns['author'] = 'author';
    return $columns;
}
add_filter( 'manage_edit-annunci_4zampe_sortable_columns', 'caniincasa_annunci_sortable_columns' );
add_filter( 'manage_edit-annunci_dogsitter_sortable_columns', 'caniincasa_annunci_sortable_columns' );

/**
 * Add filter dropdown for Author in admin list
 */
function caniincasa_annunci_author_filter() {
    global $typenow;

    if ( in_array( $typenow, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        $selected = isset( $_GET['author_filter'] ) ? intval( $_GET['author_filter'] ) : 0;

        $users = get_users( array(
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ) );

        if ( ! empty( $users ) ) {
            echo '<select name="author_filter">';
            echo '<option value="0">' . __( 'Tutti gli autori', 'caniincasa-core' ) . '</option>';

            foreach ( $users as $user ) {
                // Count posts for this author
                $count = count_user_posts( $user->ID, $typenow );

                if ( $count > 0 ) {
                    printf(
                        '<option value="%s"%s>%s (%d)</option>',
                        $user->ID,
                        selected( $selected, $user->ID, false ),
                        esc_html( $user->display_name ),
                        $count
                    );
                }
            }

            echo '</select>';
        }
    }
}
add_action( 'restrict_manage_posts', 'caniincasa_annunci_author_filter' );

/**
 * Filter posts by selected author
 */
function caniincasa_annunci_filter_by_author( $query ) {
    global $pagenow, $typenow;

    if ( is_admin()
        && $pagenow === 'edit.php'
        && in_array( $typenow, array( 'annunci_4zampe', 'annunci_dogsitter' ) )
        && isset( $_GET['author_filter'] )
        && ! empty( $_GET['author_filter'] )
    ) {
        $query->set( 'author', intval( $_GET['author_filter'] ) );
    }
}
add_filter( 'parse_query', 'caniincasa_annunci_filter_by_author' );

/**
 * Add custom meta boxes
 */
function caniincasa_annunci_add_author_meta_box() {
    add_meta_box(
        'caniincasa_annuncio_author',
        __( 'Cambia Autore Annuncio', 'caniincasa-core' ),
        'caniincasa_annunci_author_meta_box_callback',
        array( 'annunci_4zampe', 'annunci_dogsitter' ),
        'side',
        'high'
    );

    add_meta_box(
        'caniincasa_annuncio_anonymous',
        __( 'Utente Anonimo', 'caniincasa-core' ),
        'caniincasa_annunci_anonymous_meta_box_callback',
        array( 'annunci_4zampe', 'annunci_dogsitter' ),
        'side',
        'high'
    );

    add_meta_box(
        'caniincasa_annuncio_contact',
        __( 'Dati di Contatto Annuncio', 'caniincasa-core' ),
        'caniincasa_annunci_contact_meta_box_callback',
        array( 'annunci_4zampe', 'annunci_dogsitter' ),
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'caniincasa_annunci_add_author_meta_box' );

/**
 * Meta box callback for author selection
 */
function caniincasa_annunci_author_meta_box_callback( $post ) {
    $current_author_id = $post->post_author;
    $current_author = get_userdata( $current_author_id );

    // Get all users
    $users = get_users( array(
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ) );

    wp_nonce_field( 'caniincasa_change_annuncio_author', 'caniincasa_author_nonce' );
    ?>

    <div class="caniincasa-author-select">
        <p>
            <strong><?php _e( 'Autore Corrente:', 'caniincasa-core' ); ?></strong><br>
            <?php echo esc_html( $current_author->display_name ); ?><br>
            <small><?php echo esc_html( $current_author->user_email ); ?></small>
        </p>

        <p>
            <label for="post_author_override">
                <strong><?php _e( 'Cambia in:', 'caniincasa-core' ); ?></strong>
            </label>
            <select name="post_author_override" id="post_author_override" style="width: 100%; margin-top: 5px;">
                <?php foreach ( $users as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $current_author_id, $user->ID ); ?>>
                        <?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="description">
            <?php _e( 'Seleziona un nuovo autore per questo annuncio. Il cambio sarà effettivo dopo aver salvato.', 'caniincasa-core' ); ?>
        </p>
    </div>

    <style>
        .caniincasa-author-select {
            padding: 10px 0;
        }
        .caniincasa-author-select p {
            margin-bottom: 15px;
        }
        .caniincasa-author-select small {
            color: #666;
        }
    </style>
    <?php
}

/**
 * Save the author change
 */
function caniincasa_save_annuncio_author( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['caniincasa_author_nonce'] ) ||
         ! wp_verify_nonce( $_POST['caniincasa_author_nonce'], 'caniincasa_change_annuncio_author' ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if author field is set
    if ( ! isset( $_POST['post_author_override'] ) ) {
        return;
    }

    $new_author_id = intval( $_POST['post_author_override'] );

    // Verify user exists
    if ( ! get_userdata( $new_author_id ) ) {
        return;
    }

    // Update post author
    remove_action( 'save_post', 'caniincasa_save_annuncio_author' );

    wp_update_post( array(
        'ID'          => $post_id,
        'post_author' => $new_author_id,
    ) );

    add_action( 'save_post', 'caniincasa_save_annuncio_author' );
}
add_action( 'save_post', 'caniincasa_save_annuncio_author' );

/**
 * Add author to quick edit
 */
function caniincasa_annunci_quick_edit_author( $column_name, $post_type ) {
    if ( ! in_array( $post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    if ( $column_name !== 'author' ) {
        return;
    }

    $users = get_users( array(
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ) );

    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title"><?php _e( 'Autore', 'caniincasa-core' ); ?></span>
                <select name="post_author">
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->ID ); ?>">
                            <?php echo esc_html( $user->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </fieldset>
    <?php
}
add_action( 'quick_edit_custom_box', 'caniincasa_annunci_quick_edit_author', 10, 2 );

/**
 * Meta box callback for anonymous user data
 */
function caniincasa_annunci_anonymous_meta_box_callback( $post ) {
    $is_anonymous = get_post_meta( $post->ID, '_is_anonymous_user', true );
    $anon_name = get_post_meta( $post->ID, '_anonymous_name', true );
    $anon_email = get_post_meta( $post->ID, '_anonymous_email', true );
    $anon_phone = get_post_meta( $post->ID, '_anonymous_phone', true );

    wp_nonce_field( 'caniincasa_save_anonymous_data', 'caniincasa_anonymous_nonce' );
    ?>

    <div class="caniincasa-anonymous-user">
        <p>
            <label>
                <input type="checkbox" name="is_anonymous_user" id="is_anonymous_user" value="1" <?php checked( $is_anonymous, '1' ); ?>>
                <strong><?php _e( 'Questo è un annuncio per utente anonimo (non registrato)', 'caniincasa-core' ); ?></strong>
            </label>
        </p>

        <div id="anonymous-fields" style="<?php echo $is_anonymous === '1' ? '' : 'display: none;'; ?>">
            <hr style="margin: 15px 0;">

            <p>
                <label for="anonymous_name">
                    <strong><?php _e( 'Nome e Cognome *', 'caniincasa-core' ); ?></strong>
                </label>
                <input type="text"
                       name="anonymous_name"
                       id="anonymous_name"
                       value="<?php echo esc_attr( $anon_name ); ?>"
                       style="width: 100%; margin-top: 5px;"
                       placeholder="<?php esc_attr_e( 'Es: Mario Rossi', 'caniincasa-core' ); ?>">
            </p>

            <p>
                <label for="anonymous_email">
                    <strong><?php _e( 'Email *', 'caniincasa-core' ); ?></strong>
                </label>
                <input type="email"
                       name="anonymous_email"
                       id="anonymous_email"
                       value="<?php echo esc_attr( $anon_email ); ?>"
                       style="width: 100%; margin-top: 5px;"
                       placeholder="<?php esc_attr_e( 'email@esempio.it', 'caniincasa-core' ); ?>">
            </p>

            <p>
                <label for="anonymous_phone">
                    <strong><?php _e( 'Telefono *', 'caniincasa-core' ); ?></strong>
                </label>
                <input type="tel"
                       name="anonymous_phone"
                       id="anonymous_phone"
                       value="<?php echo esc_attr( $anon_phone ); ?>"
                       style="width: 100%; margin-top: 5px;"
                       placeholder="<?php esc_attr_e( '+39 123 456 7890', 'caniincasa-core' ); ?>">
            </p>

            <p class="description">
                <?php _e( 'Questi dati di contatto saranno visualizzati pubblicamente al posto dei dati dell\'autore.', 'caniincasa-core' ); ?>
            </p>
        </div>
    </div>

    <style>
        .caniincasa-anonymous-user {
            padding: 10px 0;
        }
        .caniincasa-anonymous-user p {
            margin-bottom: 15px;
        }
        .caniincasa-anonymous-user input[type="text"],
        .caniincasa-anonymous-user input[type="email"],
        .caniincasa-anonymous-user input[type="tel"] {
            padding: 6px 8px;
        }
        .caniincasa-anonymous-user .description {
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('#is_anonymous_user').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#anonymous-fields').slideDown(200);
                } else {
                    $('#anonymous-fields').slideUp(200);
                }
            });
        });
    </script>
    <?php
}

/**
 * Save anonymous user data
 */
function caniincasa_save_anonymous_data( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['caniincasa_anonymous_nonce'] ) ||
         ! wp_verify_nonce( $_POST['caniincasa_anonymous_nonce'], 'caniincasa_save_anonymous_data' ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check post type
    $post_type = get_post_type( $post_id );
    if ( ! in_array( $post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    // Save is_anonymous flag
    $is_anonymous = isset( $_POST['is_anonymous_user'] ) && $_POST['is_anonymous_user'] === '1';
    update_post_meta( $post_id, '_is_anonymous_user', $is_anonymous ? '1' : '0' );

    if ( $is_anonymous ) {
        // Validate and save anonymous data
        $anon_name = isset( $_POST['anonymous_name'] ) ? sanitize_text_field( $_POST['anonymous_name'] ) : '';
        $anon_email = isset( $_POST['anonymous_email'] ) ? sanitize_email( $_POST['anonymous_email'] ) : '';
        $anon_phone = isset( $_POST['anonymous_phone'] ) ? sanitize_text_field( $_POST['anonymous_phone'] ) : '';

        // Validation
        if ( empty( $anon_name ) || empty( $anon_email ) || empty( $anon_phone ) ) {
            // Set admin notice for missing required fields
            set_transient( 'caniincasa_anonymous_error_' . $post_id, __( 'Per gli annunci anonimi sono obbligatori Nome, Email e Telefono.', 'caniincasa-core' ), 45 );
            return;
        }

        if ( ! is_email( $anon_email ) ) {
            set_transient( 'caniincasa_anonymous_error_' . $post_id, __( 'L\'indirizzo email inserito non è valido.', 'caniincasa-core' ), 45 );
            return;
        }

        // Save data
        update_post_meta( $post_id, '_anonymous_name', $anon_name );
        update_post_meta( $post_id, '_anonymous_email', $anon_email );
        update_post_meta( $post_id, '_anonymous_phone', $anon_phone );
    } else {
        // Remove anonymous data if checkbox is unchecked
        delete_post_meta( $post_id, '_anonymous_name' );
        delete_post_meta( $post_id, '_anonymous_email' );
        delete_post_meta( $post_id, '_anonymous_phone' );
    }
}
add_action( 'save_post', 'caniincasa_save_anonymous_data' );

/**
 * Show admin notices for anonymous user validation errors
 */
function caniincasa_anonymous_admin_notices() {
    global $post;

    if ( ! $post || ! isset( $post->ID ) ) {
        return;
    }

    $error = get_transient( 'caniincasa_anonymous_error_' . $post->ID );

    if ( $error ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php _e( 'Errore Utente Anonimo:', 'caniincasa-core' ); ?></strong> <?php echo esc_html( $error ); ?></p>
        </div>
        <?php
        delete_transient( 'caniincasa_anonymous_error_' . $post->ID );
    }
}
add_action( 'admin_notices', 'caniincasa_anonymous_admin_notices' );

/**
 * Update author column to show anonymous status
 */
function caniincasa_annunci_author_column_content_with_anonymous( $column, $post_id ) {
    if ( $column === 'author' ) {
        $is_anonymous = get_post_meta( $post_id, '_is_anonymous_user', true );

        if ( $is_anonymous === '1' ) {
            $anon_name = get_post_meta( $post_id, '_anonymous_name', true );
            $anon_email = get_post_meta( $post_id, '_anonymous_email', true );

            echo '<span style="color: #d63638; font-weight: 600;">🔒 ' . esc_html__( 'ANONIMO', 'caniincasa-core' ) . '</span><br>';
            echo '<strong>' . esc_html( $anon_name ) . '</strong><br>';
            echo '<small>' . esc_html( $anon_email ) . '</small>';
        } else {
            $author_id = get_post_field( 'post_author', $post_id );
            $author = get_userdata( $author_id );

            if ( $author ) {
                $edit_link = add_query_arg(
                    array(
                        'user_id' => $author_id,
                    ),
                    admin_url( 'user-edit.php' )
                );

                echo '<a href="' . esc_url( $edit_link ) . '">';
                echo esc_html( $author->display_name );
                echo '</a>';
                echo '<br><small>' . esc_html( $author->user_email ) . '</small>';
            }
        }
    }
}
// Remove old action and add new one
remove_action( 'manage_annunci_4zampe_posts_custom_column', 'caniincasa_annunci_author_column_content', 10 );
remove_action( 'manage_annunci_dogsitter_posts_custom_column', 'caniincasa_annunci_author_column_content', 10 );
add_action( 'manage_annunci_4zampe_posts_custom_column', 'caniincasa_annunci_author_column_content_with_anonymous', 10, 2 );
add_action( 'manage_annunci_dogsitter_posts_custom_column', 'caniincasa_annunci_author_column_content_with_anonymous', 10, 2 );

/**
 * Add filter for anonymous users
 */
function caniincasa_annunci_anonymous_filter() {
    global $typenow;

    if ( in_array( $typenow, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        $selected = isset( $_GET['anonymous_filter'] ) ? $_GET['anonymous_filter'] : '';

        echo '<select name="anonymous_filter">';
        echo '<option value="">' . __( 'Tutti i tipi', 'caniincasa-core' ) . '</option>';
        echo '<option value="registered"' . selected( $selected, 'registered', false ) . '>' . __( 'Solo Utenti Registrati', 'caniincasa-core' ) . '</option>';
        echo '<option value="anonymous"' . selected( $selected, 'anonymous', false ) . '>' . __( 'Solo Anonimi', 'caniincasa-core' ) . '</option>';
        echo '</select>';
    }
}
add_action( 'restrict_manage_posts', 'caniincasa_annunci_anonymous_filter', 11 );

/**
 * Filter posts by anonymous status
 */
function caniincasa_annunci_filter_by_anonymous( $query ) {
    global $pagenow, $typenow;

    if ( is_admin()
        && $pagenow === 'edit.php'
        && in_array( $typenow, array( 'annunci_4zampe', 'annunci_dogsitter' ) )
        && isset( $_GET['anonymous_filter'] )
        && ! empty( $_GET['anonymous_filter'] )
    ) {
        $meta_query = array();

        if ( $_GET['anonymous_filter'] === 'anonymous' ) {
            $meta_query[] = array(
                'key'     => '_is_anonymous_user',
                'value'   => '1',
                'compare' => '=',
            );
        } elseif ( $_GET['anonymous_filter'] === 'registered' ) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_is_anonymous_user',
                    'value'   => '0',
                    'compare' => '=',
                ),
                array(
                    'key'     => '_is_anonymous_user',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        if ( ! empty( $meta_query ) ) {
            $query->set( 'meta_query', $meta_query );
        }
    }
}
add_filter( 'parse_query', 'caniincasa_annunci_filter_by_anonymous', 11 );

/**
 * Meta box callback for annuncio contact info
 */
function caniincasa_annunci_contact_meta_box_callback( $post ) {
    $author_id = $post->post_author;
    $author = get_userdata( $author_id );

    // Get saved contact info or defaults from author profile
    $annuncio_email = get_post_meta( $post->ID, '_annuncio_email', true );
    $annuncio_phone = get_post_meta( $post->ID, '_annuncio_phone', true );

    // If empty and post is new, populate from author profile
    if ( empty( $annuncio_email ) && $author ) {
        $annuncio_email = $author->user_email;
    }
    if ( empty( $annuncio_phone ) && $author ) {
        $annuncio_phone = get_user_meta( $author_id, 'phone', true );
    }

    wp_nonce_field( 'caniincasa_save_annuncio_contact', 'caniincasa_contact_nonce' );
    ?>

    <div class="caniincasa-contact-info">
        <p class="description" style="margin-bottom: 15px;">
            <?php _e( 'Questi dati di contatto saranno mostrati pubblicamente per questo annuncio. Vengono inizialmente popolati dal tuo profilo ma puoi modificarli liberamente.', 'caniincasa-core' ); ?>
        </p>

        <p>
            <label for="annuncio_email">
                <strong><?php _e( 'Email di Contatto *', 'caniincasa-core' ); ?></strong>
            </label>
            <input type="email"
                   name="annuncio_email"
                   id="annuncio_email"
                   value="<?php echo esc_attr( $annuncio_email ); ?>"
                   style="width: 100%; margin-top: 5px;"
                   placeholder="<?php esc_attr_e( 'email@esempio.it', 'caniincasa-core' ); ?>"
                   required>
        </p>

        <p>
            <label for="annuncio_phone">
                <strong><?php _e( 'Telefono di Contatto *', 'caniincasa-core' ); ?></strong>
            </label>
            <input type="tel"
                   name="annuncio_phone"
                   id="annuncio_phone"
                   value="<?php echo esc_attr( $annuncio_phone ); ?>"
                   style="width: 100%; margin-top: 5px;"
                   placeholder="<?php esc_attr_e( '+39 123 456 7890', 'caniincasa-core' ); ?>"
                   required>
        </p>

        <?php if ( $author ) : ?>
            <p class="description" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
                <strong><?php _e( 'Dati dal tuo profilo:', 'caniincasa-core' ); ?></strong><br>
                Email: <?php echo esc_html( $author->user_email ); ?><br>
                <?php
                $profile_phone = get_user_meta( $author_id, 'phone', true );
                if ( $profile_phone ) :
                    ?>
                    Tel: <?php echo esc_html( $profile_phone ); ?>
                <?php else : ?>
                    <em><?php _e( 'Telefono non impostato nel profilo', 'caniincasa-core' ); ?></em>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <style>
        .caniincasa-contact-info {
            padding: 10px 0;
        }
        .caniincasa-contact-info p {
            margin-bottom: 15px;
        }
        .caniincasa-contact-info input[type="email"],
        .caniincasa-contact-info input[type="tel"] {
            padding: 6px 8px;
        }
        .caniincasa-contact-info .description {
            font-style: italic;
            color: #666;
            font-size: 13px;
        }
    </style>
    <?php
}

/**
 * Save annuncio contact info
 */
function caniincasa_save_annuncio_contact( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['caniincasa_contact_nonce'] ) ||
         ! wp_verify_nonce( $_POST['caniincasa_contact_nonce'], 'caniincasa_save_annuncio_contact' ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check post type
    $post_type = get_post_type( $post_id );
    if ( ! in_array( $post_type, array( 'annunci_4zampe', 'annunci_dogsitter' ) ) ) {
        return;
    }

    // Validate and save email
    if ( isset( $_POST['annuncio_email'] ) ) {
        $email = sanitize_email( $_POST['annuncio_email'] );

        if ( ! is_email( $email ) ) {
            set_transient( 'caniincasa_contact_error_' . $post_id, __( 'L\'indirizzo email inserito non è valido.', 'caniincasa-core' ), 45 );
            return;
        }

        update_post_meta( $post_id, '_annuncio_email', $email );
    }

    // Validate and save phone
    if ( isset( $_POST['annuncio_phone'] ) ) {
        $phone = sanitize_text_field( $_POST['annuncio_phone'] );

        if ( empty( $phone ) ) {
            set_transient( 'caniincasa_contact_error_' . $post_id, __( 'Il numero di telefono è obbligatorio.', 'caniincasa-core' ), 45 );
            return;
        }

        update_post_meta( $post_id, '_annuncio_phone', $phone );
    }
}
add_action( 'save_post', 'caniincasa_save_annuncio_contact' );

/**
 * Show admin notices for contact info validation errors
 */
function caniincasa_contact_admin_notices() {
    global $post;

    if ( ! $post || ! isset( $post->ID ) ) {
        return;
    }

    $error = get_transient( 'caniincasa_contact_error_' . $post->ID );

    if ( $error ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php _e( 'Errore Dati Contatto:', 'caniincasa-core' ); ?></strong> <?php echo esc_html( $error ); ?></p>
        </div>
        <?php
        delete_transient( 'caniincasa_contact_error_' . $post->ID );
    }
}
add_action( 'admin_notices', 'caniincasa_contact_admin_notices' );

/**
 * Helper function to get annuncio contact info (for frontend use)
 *
 * @param int $post_id Post ID
 * @return array Contact information
 */
function caniincasa_get_annuncio_contact_info( $post_id ) {
    $is_anonymous = get_post_meta( $post_id, '_is_anonymous_user', true );

    // For anonymous users, use anonymous data
    if ( $is_anonymous === '1' ) {
        return array(
            'is_anonymous' => true,
            'name'         => get_post_meta( $post_id, '_anonymous_name', true ),
            'email'        => get_post_meta( $post_id, '_anonymous_email', true ),
            'phone'        => get_post_meta( $post_id, '_anonymous_phone', true ),
        );
    }

    // For registered users, get contact info from annuncio meta or author profile
    $author_id = get_post_field( 'post_author', $post_id );
    $author = get_userdata( $author_id );

    if ( ! $author ) {
        return array(
            'is_anonymous' => false,
            'name'         => '',
            'email'        => '',
            'phone'        => '',
        );
    }

    // Get annuncio-specific email and phone (saved in meta box)
    $annuncio_email = get_post_meta( $post_id, '_annuncio_email', true );
    $annuncio_phone = get_post_meta( $post_id, '_annuncio_phone', true );

    // Fallback to author profile if annuncio fields are empty
    if ( empty( $annuncio_email ) ) {
        $annuncio_email = $author->user_email;
    }
    if ( empty( $annuncio_phone ) ) {
        $annuncio_phone = get_user_meta( $author_id, 'phone', true );
    }

    return array(
        'is_anonymous' => false,
        'name'         => $author->display_name,
        'email'        => $annuncio_email,
        'phone'        => $annuncio_phone,
        'user_id'      => $author_id,
    );
}

