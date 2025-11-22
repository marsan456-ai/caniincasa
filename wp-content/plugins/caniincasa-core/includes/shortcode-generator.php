<?php
/**
 * Shortcode Generator - Generatore di Griglie Contenuti
 *
 * Permette di creare shortcode per griglie di contenuti selezionati:
 * - Razze di cani
 * - Articoli/Post
 * - Stories
 * - Strutture (allevamenti, canili, pensioni, etc.)
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu page for shortcode generator
 */
function caniincasa_add_shortcode_generator_menu() {
    add_submenu_page(
        'tools.php',
        'Generatore Shortcode',
        'Generatore Shortcode',
        'edit_posts',
        'caniincasa-shortcode-generator',
        'caniincasa_render_shortcode_generator_page'
    );
}
add_action( 'admin_menu', 'caniincasa_add_shortcode_generator_menu' );

/**
 * Enqueue admin scripts for shortcode generator
 */
function caniincasa_shortcode_generator_admin_scripts( $hook ) {
    if ( 'tools_page_caniincasa-shortcode-generator' !== $hook ) {
        return;
    }

    wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

    wp_enqueue_script(
        'caniincasa-shortcode-generator',
        plugin_dir_url( CANIINCASA_CORE_FILE ) . 'assets/js/shortcode-generator.js',
        array( 'jquery', 'select2' ),
        CANIINCASA_CORE_VERSION,
        true
    );

    wp_localize_script( 'caniincasa-shortcode-generator', 'shortcodeGeneratorData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'shortcode_generator_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'caniincasa_shortcode_generator_admin_scripts' );

/**
 * AJAX: Search posts for shortcode generator
 */
function caniincasa_ajax_search_posts_for_grid() {
    check_ajax_referer( 'shortcode_generator_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'post';
    $search    = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

    // Validate post type
    $allowed_types = array( 'post', 'razze_di_cani', 'stories', 'allevamenti', 'canili', 'pensioni', 'centri_cinofili', 'veterinari' );
    if ( ! in_array( $post_type, $allowed_types, true ) ) {
        wp_send_json_error( 'Tipo di contenuto non valido' );
    }

    $args = array(
        'post_type'      => $post_type,
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    $posts   = get_posts( $args );
    $results = array();

    foreach ( $posts as $post ) {
        $thumbnail = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
        $results[] = array(
            'id'        => $post->ID,
            'title'     => $post->post_title,
            'thumbnail' => $thumbnail ?: '',
            'edit_url'  => get_edit_post_link( $post->ID, 'raw' ),
        );
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_caniincasa_search_posts_for_grid', 'caniincasa_ajax_search_posts_for_grid' );

/**
 * Render shortcode generator admin page
 */
function caniincasa_render_shortcode_generator_page() {
    ?>
    <div class="wrap">
        <h1>Generatore Shortcode Griglia</h1>
        <p class="description">Crea shortcode per visualizzare griglie di contenuti selezionati.</p>

        <style>
            .shortcode-generator-wrap {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-top: 20px;
            }
            .generator-panel {
                background: white;
                padding: 25px;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
            }
            .generator-panel h2 {
                margin-top: 0;
                padding-bottom: 15px;
                border-bottom: 1px solid #e9ecef;
            }
            .form-field {
                margin-bottom: 20px;
            }
            .form-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .form-field select,
            .form-field input[type="number"] {
                width: 100%;
                max-width: 300px;
            }
            .selected-items-list {
                margin-top: 15px;
                min-height: 100px;
                border: 1px dashed #c3c4c7;
                padding: 15px;
                border-radius: 4px;
                background: #f9f9f9;
            }
            .selected-item {
                display: inline-flex;
                align-items: center;
                background: #2271b1;
                color: white;
                padding: 5px 10px;
                border-radius: 3px;
                margin: 3px;
                font-size: 13px;
            }
            .selected-item img {
                width: 24px;
                height: 24px;
                border-radius: 3px;
                margin-right: 8px;
                object-fit: cover;
            }
            .selected-item .remove-item {
                margin-left: 8px;
                cursor: pointer;
                opacity: 0.8;
            }
            .selected-item .remove-item:hover {
                opacity: 1;
            }
            .shortcode-output {
                background: #1d2327;
                color: #50c878;
                padding: 20px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 14px;
                word-break: break-all;
                min-height: 60px;
            }
            .copy-btn {
                margin-top: 15px;
            }
            .preview-section {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e9ecef;
            }
            .grid-style-options {
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
            }
            .grid-style-option {
                border: 2px solid #c3c4c7;
                padding: 15px;
                border-radius: 4px;
                cursor: pointer;
                text-align: center;
                transition: all 0.2s;
            }
            .grid-style-option:hover,
            .grid-style-option.selected {
                border-color: #2271b1;
                background: #f0f6fc;
            }
            .grid-style-option .style-preview {
                display: grid;
                gap: 3px;
                margin-bottom: 8px;
            }
            .grid-style-option .style-preview span {
                background: #2271b1;
                height: 20px;
                border-radius: 2px;
            }
            .style-2col .style-preview { grid-template-columns: 1fr 1fr; }
            .style-3col .style-preview { grid-template-columns: 1fr 1fr 1fr; }
            .style-4col .style-preview { grid-template-columns: 1fr 1fr 1fr 1fr; }
            .style-list .style-preview { grid-template-columns: 1fr; }
            .select2-container { width: 100% !important; max-width: 400px; }
        </style>

        <div class="shortcode-generator-wrap">
            <!-- Left Panel: Configuration -->
            <div class="generator-panel">
                <h2>Configurazione</h2>

                <div class="form-field">
                    <label for="content-type">Tipo di Contenuto</label>
                    <select id="content-type" class="regular-text">
                        <option value="">-- Seleziona --</option>
                        <option value="razze_di_cani">Razze di Cani</option>
                        <option value="post">Articoli</option>
                        <option value="stories">Stories</option>
                        <optgroup label="Strutture">
                            <option value="allevamenti">Allevamenti</option>
                            <option value="canili">Canili</option>
                            <option value="pensioni">Pensioni</option>
                            <option value="centri_cinofili">Centri Cinofili</option>
                            <option value="veterinari">Veterinari</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-field" id="search-field" style="display: none;">
                    <label for="content-search">Cerca e Seleziona Contenuti</label>
                    <select id="content-search" class="content-search-select" multiple="multiple">
                    </select>
                    <p class="description">Cerca per titolo. Lascia vuoto per mostrare i pi&ugrave; recenti.</p>
                </div>

                <div class="form-field" id="selected-field" style="display: none;">
                    <label>Contenuti Selezionati <span id="selected-count">(0)</span></label>
                    <div class="selected-items-list" id="selected-items">
                        <p class="placeholder-text" style="color: #999; text-align: center; margin: 0;">
                            Nessun contenuto selezionato.<br>Cerca e aggiungi contenuti sopra.
                        </p>
                    </div>
                </div>

                <div class="form-field">
                    <label>Stile Griglia</label>
                    <div class="grid-style-options">
                        <div class="grid-style-option style-2col selected" data-columns="2">
                            <div class="style-preview"><span></span><span></span></div>
                            <small>2 Colonne</small>
                        </div>
                        <div class="grid-style-option style-3col" data-columns="3">
                            <div class="style-preview"><span></span><span></span><span></span></div>
                            <small>3 Colonne</small>
                        </div>
                        <div class="grid-style-option style-4col" data-columns="4">
                            <div class="style-preview"><span></span><span></span><span></span><span></span></div>
                            <small>4 Colonne</small>
                        </div>
                        <div class="grid-style-option style-list" data-columns="1">
                            <div class="style-preview"><span></span><span></span></div>
                            <small>Lista</small>
                        </div>
                    </div>
                </div>

                <div class="form-field">
                    <label for="items-limit">Numero Massimo Elementi</label>
                    <input type="number" id="items-limit" value="6" min="1" max="24" step="1">
                    <p class="description">Usato solo se non selezioni contenuti specifici.</p>
                </div>

                <div class="form-field">
                    <label>
                        <input type="checkbox" id="show-excerpt" checked> Mostra estratto
                    </label>
                </div>

                <div class="form-field">
                    <label>
                        <input type="checkbox" id="show-image" checked> Mostra immagine
                    </label>
                </div>
            </div>

            <!-- Right Panel: Output -->
            <div class="generator-panel">
                <h2>Shortcode Generato</h2>

                <div class="shortcode-output" id="shortcode-output">
                    [caniincasa_grid type="razze_di_cani" columns="2" limit="6"]
                </div>

                <button type="button" class="button button-primary copy-btn" id="copy-shortcode">
                    Copia Shortcode
                </button>
                <span id="copy-feedback" style="margin-left: 10px; color: #00a32a; display: none;">Copiato!</span>

                <div class="preview-section">
                    <h3>Anteprima</h3>
                    <p class="description">L'anteprima mostra come apparir&agrave; la griglia nel frontend.</p>
                    <div id="shortcode-preview" style="border: 1px solid #ddd; padding: 20px; background: #f9f9f9; min-height: 200px;">
                        <p style="text-align: center; color: #666;">Seleziona un tipo di contenuto per vedere l'anteprima</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register the grid shortcode
 */
function caniincasa_register_grid_shortcode() {
    add_shortcode( 'caniincasa_grid', 'caniincasa_render_grid_shortcode' );
}
add_action( 'init', 'caniincasa_register_grid_shortcode' );

/**
 * Render grid shortcode
 *
 * Usage:
 * [caniincasa_grid type="razze_di_cani" columns="3" limit="6"]
 * [caniincasa_grid type="post" ids="1,2,3,4" columns="2"]
 * [caniincasa_grid type="allevamenti" columns="3" excerpt="yes" image="yes"]
 */
function caniincasa_render_grid_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'type'    => 'post',
            'ids'     => '',
            'columns' => '3',
            'limit'   => '6',
            'excerpt' => 'yes',
            'image'   => 'yes',
            'orderby' => 'date',
            'order'   => 'DESC',
        ),
        $atts,
        'caniincasa_grid'
    );

    // Validate post type
    $allowed_types = array( 'post', 'razze_di_cani', 'stories', 'allevamenti', 'canili', 'pensioni', 'centri_cinofili', 'veterinari' );
    if ( ! in_array( $atts['type'], $allowed_types, true ) ) {
        return '<p class="error">Tipo di contenuto non valido.</p>';
    }

    // Build query args
    $args = array(
        'post_type'      => $atts['type'],
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => sanitize_key( $atts['orderby'] ),
        'order'          => in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : 'DESC',
    );

    // If specific IDs provided
    if ( ! empty( $atts['ids'] ) ) {
        $ids = array_map( 'intval', explode( ',', $atts['ids'] ) );
        $ids = array_filter( $ids );
        if ( ! empty( $ids ) ) {
            $args['post__in'] = $ids;
            $args['orderby']  = 'post__in';
            $args['posts_per_page'] = count( $ids );
        }
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p class="no-results">Nessun contenuto trovato.</p>';
    }

    $columns     = intval( $atts['columns'] );
    $show_excerpt = $atts['excerpt'] === 'yes';
    $show_image   = $atts['image'] === 'yes';

    // Get post type label for aria
    $post_type_obj = get_post_type_object( $atts['type'] );
    $type_label    = $post_type_obj ? $post_type_obj->labels->name : 'Contenuti';

    ob_start();
    ?>
    <div class="caniincasa-grid caniincasa-grid-<?php echo esc_attr( $columns ); ?>col"
         role="list"
         aria-label="<?php echo esc_attr( sprintf( 'Griglia %s', $type_label ) ); ?>">
        <?php
        while ( $query->have_posts() ) :
            $query->the_post();
            $post_id = get_the_ID();
            ?>
            <article class="caniincasa-grid-item" role="listitem">
                <?php if ( $show_image && has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="grid-item-image">
                        <?php the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); ?>
                    </a>
                <?php endif; ?>

                <div class="grid-item-content">
                    <h3 class="grid-item-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <?php if ( $show_excerpt ) : ?>
                        <p class="grid-item-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                        </p>
                    <?php endif; ?>

                    <?php
                    // Type-specific meta
                    if ( $atts['type'] === 'razze_di_cani' ) {
                        $taglia = get_field( 'taglia_standard', $post_id );
                        if ( $taglia ) {
                            echo '<span class="grid-item-meta">Taglia: ' . esc_html( ucfirst( $taglia ) ) . '</span>';
                        }
                    } elseif ( in_array( $atts['type'], array( 'allevamenti', 'canili', 'pensioni', 'centri_cinofili', 'veterinari' ), true ) ) {
                        $citta = get_field( 'citta', $post_id );
                        $regione = get_field( 'regione', $post_id );
                        if ( $citta || $regione ) {
                            $location = array_filter( array( $citta, $regione ) );
                            echo '<span class="grid-item-meta grid-item-location">' . esc_html( implode( ', ', $location ) ) . '</span>';
                        }
                    }
                    ?>

                    <a href="<?php the_permalink(); ?>" class="grid-item-link">
                        Scopri di pi&ugrave; &rarr;
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <style>
        .caniincasa-grid {
            display: grid;
            gap: 25px;
            margin: 30px 0;
        }
        .caniincasa-grid-1col { grid-template-columns: 1fr; }
        .caniincasa-grid-2col { grid-template-columns: repeat(2, 1fr); }
        .caniincasa-grid-3col { grid-template-columns: repeat(3, 1fr); }
        .caniincasa-grid-4col { grid-template-columns: repeat(4, 1fr); }

        @media (max-width: 768px) {
            .caniincasa-grid-3col,
            .caniincasa-grid-4col { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .caniincasa-grid-2col,
            .caniincasa-grid-3col,
            .caniincasa-grid-4col { grid-template-columns: 1fr; }
        }

        .caniincasa-grid-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .caniincasa-grid-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        .caniincasa-grid-item .grid-item-image {
            display: block;
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        .caniincasa-grid-item .grid-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .caniincasa-grid-item:hover .grid-item-image img {
            transform: scale(1.05);
        }
        .caniincasa-grid-item .grid-item-content {
            padding: 20px;
        }
        .caniincasa-grid-item .grid-item-title {
            margin: 0 0 10px;
            font-size: 1.1em;
            line-height: 1.3;
        }
        .caniincasa-grid-item .grid-item-title a {
            color: inherit;
            text-decoration: none;
        }
        .caniincasa-grid-item .grid-item-title a:hover {
            color: #2271b1;
        }
        .caniincasa-grid-item .grid-item-excerpt {
            color: #666;
            font-size: 0.9em;
            line-height: 1.5;
            margin: 0 0 15px;
        }
        .caniincasa-grid-item .grid-item-meta {
            display: inline-block;
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            color: #555;
            margin-bottom: 10px;
        }
        .caniincasa-grid-item .grid-item-link {
            display: inline-block;
            color: #2271b1;
            font-size: 0.9em;
            text-decoration: none;
            font-weight: 500;
        }
        .caniincasa-grid-item .grid-item-link:hover {
            text-decoration: underline;
        }
    </style>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * AJAX: Get shortcode preview
 */
function caniincasa_ajax_preview_grid_shortcode() {
    check_ajax_referer( 'shortcode_generator_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $shortcode = isset( $_POST['shortcode'] ) ? wp_kses_post( $_POST['shortcode'] ) : '';

    if ( empty( $shortcode ) ) {
        wp_send_json_error( 'Shortcode vuoto' );
    }

    // Execute shortcode and return HTML
    $html = do_shortcode( $shortcode );

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_caniincasa_preview_grid_shortcode', 'caniincasa_ajax_preview_grid_shortcode' );
