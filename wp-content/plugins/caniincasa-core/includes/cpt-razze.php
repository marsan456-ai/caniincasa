<?php
/**
 * Custom Post Type: Razze di Cani
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Razze di Cani CPT
 */
function caniincasa_register_cpt_razze() {
    $labels = array(
        'name'                  => _x( 'Razze di Cani', 'Post Type General Name', 'caniincasa-core' ),
        'singular_name'         => _x( 'Razza', 'Post Type Singular Name', 'caniincasa-core' ),
        'menu_name'             => __( 'Razze', 'caniincasa-core' ),
        'name_admin_bar'        => __( 'Razza', 'caniincasa-core' ),
        'archives'              => __( 'Archivio Razze', 'caniincasa-core' ),
        'attributes'            => __( 'Attributi Razza', 'caniincasa-core' ),
        'parent_item_colon'     => __( 'Razza Genitore:', 'caniincasa-core' ),
        'all_items'             => __( 'Tutte le Razze', 'caniincasa-core' ),
        'add_new_item'          => __( 'Aggiungi Nuova Razza', 'caniincasa-core' ),
        'add_new'               => __( 'Aggiungi Nuova', 'caniincasa-core' ),
        'new_item'              => __( 'Nuova Razza', 'caniincasa-core' ),
        'edit_item'             => __( 'Modifica Razza', 'caniincasa-core' ),
        'update_item'           => __( 'Aggiorna Razza', 'caniincasa-core' ),
        'view_item'             => __( 'Visualizza Razza', 'caniincasa-core' ),
        'view_items'            => __( 'Visualizza Razze', 'caniincasa-core' ),
        'search_items'          => __( 'Cerca Razza', 'caniincasa-core' ),
        'not_found'             => __( 'Non trovato', 'caniincasa-core' ),
        'not_found_in_trash'    => __( 'Non trovato nel cestino', 'caniincasa-core' ),
        'featured_image'        => __( 'Immagine Razza', 'caniincasa-core' ),
        'set_featured_image'    => __( 'Imposta immagine razza', 'caniincasa-core' ),
        'remove_featured_image' => __( 'Rimuovi immagine razza', 'caniincasa-core' ),
        'use_featured_image'    => __( 'Usa come immagine razza', 'caniincasa-core' ),
        'insert_into_item'      => __( 'Inserisci nella razza', 'caniincasa-core' ),
        'uploaded_to_this_item' => __( 'Caricato in questa razza', 'caniincasa-core' ),
        'items_list'            => __( 'Lista razze', 'caniincasa-core' ),
        'items_list_navigation' => __( 'Navigazione lista razze', 'caniincasa-core' ),
        'filter_items_list'     => __( 'Filtra lista razze', 'caniincasa-core' ),
    );

    $args = array(
        'label'                 => __( 'Razza', 'caniincasa-core' ),
        'description'           => __( 'Database completo razze di cani', 'caniincasa-core' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
        'taxonomies'            => array( 'razza_taglia', 'razza_gruppo' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-pets',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'razze-di-cani',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'razze',
        'rewrite'               => array(
            'slug'       => 'razze-di-cani',
            'with_front' => false,
        ),
    );

    register_post_type( 'razze_di_cani', $args );
}
add_action( 'init', 'caniincasa_register_cpt_razze', 0 );

/**
 * Register Taxonomies for Razze
 */
function caniincasa_register_razze_taxonomies() {
    // Taglia (Size)
    $taglia_labels = array(
        'name'              => _x( 'Taglie', 'taxonomy general name', 'caniincasa-core' ),
        'singular_name'     => _x( 'Taglia', 'taxonomy singular name', 'caniincasa-core' ),
        'search_items'      => __( 'Cerca Taglie', 'caniincasa-core' ),
        'all_items'         => __( 'Tutte le Taglie', 'caniincasa-core' ),
        'edit_item'         => __( 'Modifica Taglia', 'caniincasa-core' ),
        'update_item'       => __( 'Aggiorna Taglia', 'caniincasa-core' ),
        'add_new_item'      => __( 'Aggiungi Nuova Taglia', 'caniincasa-core' ),
        'new_item_name'     => __( 'Nuova Taglia', 'caniincasa-core' ),
        'menu_name'         => __( 'Taglie', 'caniincasa-core' ),
    );

    register_taxonomy( 'razza_taglia', array( 'razze_di_cani' ), array(
        'hierarchical'      => true,
        'labels'            => $taglia_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'taglia' ),
    ) );

    // Gruppo FCI
    $gruppo_labels = array(
        'name'              => _x( 'Gruppi FCI', 'taxonomy general name', 'caniincasa-core' ),
        'singular_name'     => _x( 'Gruppo FCI', 'taxonomy singular name', 'caniincasa-core' ),
        'search_items'      => __( 'Cerca Gruppi', 'caniincasa-core' ),
        'all_items'         => __( 'Tutti i Gruppi', 'caniincasa-core' ),
        'edit_item'         => __( 'Modifica Gruppo', 'caniincasa-core' ),
        'update_item'       => __( 'Aggiorna Gruppo', 'caniincasa-core' ),
        'add_new_item'      => __( 'Aggiungi Nuovo Gruppo', 'caniincasa-core' ),
        'new_item_name'     => __( 'Nuovo Gruppo', 'caniincasa-core' ),
        'menu_name'         => __( 'Gruppi FCI', 'caniincasa-core' ),
    );

    register_taxonomy( 'razza_gruppo', array( 'razze_di_cani' ), array(
        'hierarchical'      => true,
        'labels'            => $gruppo_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'gruppo-fci' ),
    ) );
}
add_action( 'init', 'caniincasa_register_razze_taxonomies', 0 );

/**
 * Insert default terms for taxonomies
 */
function caniincasa_insert_default_razza_terms() {
    // Check if terms already exist
    if ( get_option( 'caniincasa_razza_terms_inserted' ) ) {
        return;
    }

    // Default sizes
    $taglie = array(
        'toy'      => 'Toy (< 4 kg)',
        'piccola'  => 'Piccola (4-10 kg)',
        'media'    => 'Media (10-25 kg)',
        'grande'   => 'Grande (25-45 kg)',
        'gigante'  => 'Gigante (> 45 kg)',
    );

    foreach ( $taglie as $slug => $name ) {
        if ( ! term_exists( $slug, 'razza_taglia' ) ) {
            wp_insert_term( $name, 'razza_taglia', array( 'slug' => $slug ) );
        }
    }

    // Default FCI groups
    $gruppi = array(
        '1' => 'Gruppo 1 - Cani da pastore e bovari',
        '2' => 'Gruppo 2 - Pinscher, Schnauzer, Molossoidi',
        '3' => 'Gruppo 3 - Terrier',
        '4' => 'Gruppo 4 - Bassotti',
        '5' => 'Gruppo 5 - Spitz e primitivi',
        '6' => 'Gruppo 6 - Segugi e per pista di sangue',
        '7' => 'Gruppo 7 - Cani da ferma',
        '8' => 'Gruppo 8 - Cani da riporto, da cerca, da acqua',
        '9' => 'Gruppo 9 - Cani da compagnia',
        '10' => 'Gruppo 10 - Levrieri',
    );

    foreach ( $gruppi as $slug => $name ) {
        if ( ! term_exists( 'gruppo-' . $slug, 'razza_gruppo' ) ) {
            wp_insert_term( $name, 'razza_gruppo', array( 'slug' => 'gruppo-' . $slug ) );
        }
    }

    // Mark as inserted
    update_option( 'caniincasa_razza_terms_inserted', true );
}
add_action( 'init', 'caniincasa_insert_default_razza_terms' );

/**
 * Custom columns for Razze admin list
 */
function caniincasa_razze_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;

        if ( $key === 'title' ) {
            $new_columns['thumbnail'] = __( 'Immagine', 'caniincasa-core' );
            $new_columns['taglia'] = __( 'Taglia', 'caniincasa-core' );
            $new_columns['nazione'] = __( 'Origine', 'caniincasa-core' );
        }
    }

    return $new_columns;
}
add_filter( 'manage_razze_di_cani_posts_columns', 'caniincasa_razze_columns' );

/**
 * Custom column content
 */
function caniincasa_razze_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'thumbnail':
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
            } else {
                echo '—';
            }
            break;

        case 'taglia':
            $terms = get_the_terms( $post_id, 'razza_taglia' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $taglia_names = wp_list_pluck( $terms, 'name' );
                echo esc_html( implode( ', ', $taglia_names ) );
            } else {
                echo '—';
            }
            break;

        case 'nazione':
            $nazione = get_post_meta( $post_id, 'nazione_origine', true );
            echo $nazione ? esc_html( $nazione ) : '—';
            break;
    }
}
add_action( 'manage_razze_di_cani_posts_custom_column', 'caniincasa_razze_column_content', 10, 2 );

/**
 * Make columns sortable
 */
function caniincasa_razze_sortable_columns( $columns ) {
    $columns['taglia'] = 'taglia';
    $columns['nazione'] = 'nazione';
    return $columns;
}
add_filter( 'manage_edit-razze_di_cani_sortable_columns', 'caniincasa_razze_sortable_columns' );

/**
 * Force update taxonomy terms (admin tool)
 * Add ?caniincasa_update_razza_terms=1 to any admin page to force update
 */
function caniincasa_force_update_razza_terms() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['caniincasa_update_razza_terms'] ) && $_GET['caniincasa_update_razza_terms'] === '1' ) {
        // Delete the flag to allow re-insertion
        delete_option( 'caniincasa_razza_terms_inserted' );

        // Re-run the terms insertion
        caniincasa_insert_default_razza_terms();

        // Show admin notice
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Tassonomie razze aggiornate con successo!</strong></p></div>';
        } );
    }
}
add_action( 'admin_init', 'caniincasa_force_update_razza_terms' );
