<?php
/**
 * Custom Post Types: Strutture (5 tipologie)
 *
 * - Allevamenti
 * - Veterinari
 * - Canili
 * - Pensioni per Cani
 * - Centri Cinofili
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register all structure CPTs
 */
function caniincasa_register_strutture_cpts() {
    $strutture = array(
        'allevamenti' => array(
            'singular' => 'Allevamento',
            'plural'   => 'Allevamenti',
            'slug'     => 'allevamenti',
            'icon'     => 'dashicons-admin-home',
            'desc'     => 'Directory allevamenti di cani',
        ),
        'veterinari' => array(
            'singular' => 'Veterinario',
            'plural'   => 'Veterinari',
            'slug'     => 'veterinari',
            'icon'     => 'dashicons-heart',
            'desc'     => 'Directory veterinari e cliniche veterinarie',
        ),
        'canili' => array(
            'singular' => 'Canile',
            'plural'   => 'Canili',
            'slug'     => 'canili',
            'icon'     => 'dashicons-groups',
            'desc'     => 'Directory canili e rifugi',
        ),
        'pensioni_per_cani' => array(
            'singular' => 'Pensione',
            'plural'   => 'Pensioni per Cani',
            'slug'     => 'pensioni-per-cani',
            'icon'     => 'dashicons-building',
            'desc'     => 'Directory pensioni per cani',
        ),
        'centri_cinofili' => array(
            'singular' => 'Centro Cinofilo',
            'plural'   => 'Centri Cinofili',
            'slug'     => 'centri-cinofili',
            'icon'     => 'dashicons-awards',
            'desc'     => 'Directory centri cinofili e scuole addestramento',
        ),
    );

    foreach ( $strutture as $post_type => $config ) {
        caniincasa_register_struttura_cpt( $post_type, $config );
    }
}
add_action( 'init', 'caniincasa_register_strutture_cpts', 0 );

/**
 * Register single structure CPT
 */
function caniincasa_register_struttura_cpt( $post_type, $config ) {
    $singular = $config['singular'];
    $plural   = $config['plural'];
    $slug     = $config['slug'];
    $icon     = $config['icon'];
    $desc     = $config['desc'];

    $labels = array(
        'name'                  => $plural,
        'singular_name'         => $singular,
        'menu_name'             => $plural,
        'name_admin_bar'        => $singular,
        'archives'              => sprintf( __( 'Archivio %s', 'caniincasa-core' ), $plural ),
        'attributes'            => sprintf( __( 'Attributi %s', 'caniincasa-core' ), $singular ),
        'all_items'             => sprintf( __( 'Tutti i %s', 'caniincasa-core' ), $plural ),
        'add_new_item'          => sprintf( __( 'Aggiungi Nuovo %s', 'caniincasa-core' ), $singular ),
        'add_new'               => __( 'Aggiungi Nuovo', 'caniincasa-core' ),
        'new_item'              => sprintf( __( 'Nuovo %s', 'caniincasa-core' ), $singular ),
        'edit_item'             => sprintf( __( 'Modifica %s', 'caniincasa-core' ), $singular ),
        'update_item'           => sprintf( __( 'Aggiorna %s', 'caniincasa-core' ), $singular ),
        'view_item'             => sprintf( __( 'Visualizza %s', 'caniincasa-core' ), $singular ),
        'view_items'            => sprintf( __( 'Visualizza %s', 'caniincasa-core' ), $plural ),
        'search_items'          => sprintf( __( 'Cerca %s', 'caniincasa-core' ), $plural ),
        'not_found'             => __( 'Non trovato', 'caniincasa-core' ),
        'not_found_in_trash'    => __( 'Non trovato nel cestino', 'caniincasa-core' ),
        'featured_image'        => __( 'Immagine Principale', 'caniincasa-core' ),
        'set_featured_image'    => __( 'Imposta immagine', 'caniincasa-core' ),
        'items_list'            => sprintf( __( 'Lista %s', 'caniincasa-core' ), $plural ),
    );

    $args = array(
        'label'                 => $singular,
        'description'           => $desc,
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => 'caniincasa-strutture',
        'menu_position'         => 21,
        'menu_icon'             => $icon,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => $slug,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => str_replace( '_', '-', $post_type ),
        'rewrite'               => array(
            'slug'       => $slug,
            'with_front' => false,
        ),
    );

    register_post_type( $post_type, $args );
}

/**
 * Register Provincia Taxonomy (shared among all structure CPTs)
 */
function caniincasa_register_provincia_taxonomy() {
    $labels = array(
        'name'              => _x( 'Province', 'taxonomy general name', 'caniincasa-core' ),
        'singular_name'     => _x( 'Provincia', 'taxonomy singular name', 'caniincasa-core' ),
        'search_items'      => __( 'Cerca Province', 'caniincasa-core' ),
        'all_items'         => __( 'Tutte le Province', 'caniincasa-core' ),
        'edit_item'         => __( 'Modifica Provincia', 'caniincasa-core' ),
        'update_item'       => __( 'Aggiorna Provincia', 'caniincasa-core' ),
        'add_new_item'      => __( 'Aggiungi Nuova Provincia', 'caniincasa-core' ),
        'new_item_name'     => __( 'Nuova Provincia', 'caniincasa-core' ),
        'menu_name'         => __( 'Province', 'caniincasa-core' ),
    );

    $post_types = array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' );

    register_taxonomy( 'provincia', $post_types, array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'provincia' ),
    ) );
}
add_action( 'init', 'caniincasa_register_provincia_taxonomy', 0 );

/**
 * Insert default Italian provinces
 */
function caniincasa_insert_default_province() {
    if ( get_option( 'caniincasa_province_inserted' ) ) {
        return;
    }

    $province = array(
        'AG' => 'Agrigento', 'AL' => 'Alessandria', 'AN' => 'Ancona', 'AO' => 'Aosta',
        'AR' => 'Arezzo', 'AP' => 'Ascoli Piceno', 'AT' => 'Asti', 'AV' => 'Avellino',
        'BA' => 'Bari', 'BT' => 'Barletta-Andria-Trani', 'BL' => 'Belluno', 'BN' => 'Benevento',
        'BG' => 'Bergamo', 'BI' => 'Biella', 'BO' => 'Bologna', 'BZ' => 'Bolzano',
        'BS' => 'Brescia', 'BR' => 'Brindisi', 'CA' => 'Cagliari', 'CL' => 'Caltanissetta',
        'CB' => 'Campobasso', 'CI' => 'Carbonia-Iglesias', 'CE' => 'Caserta', 'CT' => 'Catania',
        'CZ' => 'Catanzaro', 'CH' => 'Chieti', 'CO' => 'Como', 'CS' => 'Cosenza',
        'CR' => 'Cremona', 'KR' => 'Crotone', 'CN' => 'Cuneo', 'EN' => 'Enna',
        'FM' => 'Fermo', 'FE' => 'Ferrara', 'FI' => 'Firenze', 'FG' => 'Foggia',
        'FC' => 'Forlì-Cesena', 'FR' => 'Frosinone', 'GE' => 'Genova', 'GO' => 'Gorizia',
        'GR' => 'Grosseto', 'IM' => 'Imperia', 'IS' => 'Isernia', 'SP' => 'La Spezia',
        'AQ' => 'L\'Aquila', 'LT' => 'Latina', 'LE' => 'Lecce', 'LC' => 'Lecco',
        'LI' => 'Livorno', 'LO' => 'Lodi', 'LU' => 'Lucca', 'MC' => 'Macerata',
        'MN' => 'Mantova', 'MS' => 'Massa-Carrara', 'MT' => 'Matera', 'ME' => 'Messina',
        'MI' => 'Milano', 'MO' => 'Modena', 'MB' => 'Monza e Brianza', 'NA' => 'Napoli',
        'NO' => 'Novara', 'NU' => 'Nuoro', 'OT' => 'Olbia-Tempio', 'OR' => 'Oristano',
        'PD' => 'Padova', 'PA' => 'Palermo', 'PR' => 'Parma', 'PV' => 'Pavia',
        'PG' => 'Perugia', 'PU' => 'Pesaro e Urbino', 'PE' => 'Pescara', 'PC' => 'Piacenza',
        'PI' => 'Pisa', 'PT' => 'Pistoia', 'PN' => 'Pordenone', 'PZ' => 'Potenza',
        'PO' => 'Prato', 'RG' => 'Ragusa', 'RA' => 'Ravenna', 'RC' => 'Reggio Calabria',
        'RE' => 'Reggio Emilia', 'RI' => 'Rieti', 'RN' => 'Rimini', 'RM' => 'Roma',
        'RO' => 'Rovigo', 'SA' => 'Salerno', 'VS' => 'Medio Campidano', 'SS' => 'Sassari',
        'SV' => 'Savona', 'SI' => 'Siena', 'SR' => 'Siracusa', 'SO' => 'Sondrio',
        'TA' => 'Taranto', 'TE' => 'Teramo', 'TR' => 'Terni', 'TO' => 'Torino',
        'OG' => 'Ogliastra', 'TP' => 'Trapani', 'TN' => 'Trento', 'TV' => 'Treviso',
        'TS' => 'Trieste', 'UD' => 'Udine', 'VA' => 'Varese', 'VE' => 'Venezia',
        'VB' => 'Verbano-Cusio-Ossola', 'VC' => 'Vercelli', 'VR' => 'Verona',
        'VV' => 'Vibo Valentia', 'VI' => 'Vicenza', 'VT' => 'Viterbo',
    );

    foreach ( $province as $sigla => $nome ) {
        if ( ! term_exists( strtolower( $sigla ), 'provincia' ) ) {
            wp_insert_term( $nome, 'provincia', array(
                'slug'        => strtolower( $sigla ),
                'description' => $sigla,
            ) );
        }
    }

    update_option( 'caniincasa_province_inserted', true );
}
add_action( 'init', 'caniincasa_insert_default_province' );

/**
 * Add top-level menu for Strutture
 */
function caniincasa_strutture_menu() {
    add_menu_page(
        __( 'Strutture', 'caniincasa-core' ),
        __( 'Strutture', 'caniincasa-core' ),
        'edit_posts',
        'caniincasa-strutture',
        '',
        'dashicons-location-alt',
        21
    );
}
add_action( 'admin_menu', 'caniincasa_strutture_menu' );

/**
 * Custom columns for strutture
 */
function caniincasa_strutture_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;

        if ( $key === 'title' ) {
            $new_columns['indirizzo'] = __( 'Indirizzo', 'caniincasa-core' );
            $new_columns['telefono'] = __( 'Telefono', 'caniincasa-core' );
        }
    }

    return $new_columns;
}

// Apply to all structure CPTs
$struttura_types = array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' );
foreach ( $struttura_types as $type ) {
    add_filter( "manage_{$type}_posts_columns", 'caniincasa_strutture_columns' );
}

/**
 * Custom column content for strutture
 */
function caniincasa_strutture_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'indirizzo':
            $indirizzo = get_post_meta( $post_id, 'indirizzo', true );
            $citta = get_post_meta( $post_id, 'citta', true );
            if ( $indirizzo || $citta ) {
                echo esc_html( $indirizzo );
                if ( $citta ) {
                    echo $indirizzo ? ', ' : '';
                    echo esc_html( $citta );
                }
            } else {
                echo '—';
            }
            break;

        case 'telefono':
            $telefono = get_post_meta( $post_id, 'telefono', true );
            echo $telefono ? esc_html( $telefono ) : '—';
            break;
    }
}

// Apply to all structure CPTs
foreach ( $struttura_types as $type ) {
    add_action( "manage_{$type}_posts_custom_column", 'caniincasa_strutture_column_content', 10, 2 );
}
