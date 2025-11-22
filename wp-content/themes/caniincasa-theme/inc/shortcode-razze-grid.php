<?php
/**
 * Shortcode: Razze Grid
 *
 * Mostra una griglia di razze specificate per ID.
 * Usa lo stesso stile dell'archivio razze.
 *
 * Utilizzo:
 * [razze_grid ids="123,456,789"]
 * [razze_grid ids="123,456,789" columns="4"]
 * [razze_grid ids="123,456,789" columns="3" title="Razze Consigliate"]
 *
 * @package Caniincasa
 * @since 1.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode callback
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function caniincasa_razze_grid_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'ids'     => '',           // IDs separati da virgola (obbligatorio)
        'columns' => '4',          // Numero colonne: 2, 3, 4, 5, 6
        'title'   => '',           // Titolo opzionale sopra la griglia
        'class'   => '',           // Classe CSS aggiuntiva
        'orderby' => 'post__in',   // Ordine: post__in (mantiene ordine IDs), title, rand
        'order'   => 'ASC',        // ASC o DESC
    ), $atts, 'razze_grid' );

    // Verifica IDs
    if ( empty( $atts['ids'] ) ) {
        return '<!-- razze_grid: nessun ID specificato -->';
    }

    // Parsifica IDs
    $ids = array_map( 'absint', array_filter( explode( ',', $atts['ids'] ) ) );

    if ( empty( $ids ) ) {
        return '<!-- razze_grid: IDs non validi -->';
    }

    // Sanitizza columns
    $columns = absint( $atts['columns'] );
    if ( $columns < 2 ) $columns = 2;
    if ( $columns > 6 ) $columns = 6;

    // Query razze
    $args = array(
        'post_type'      => 'razze_di_cani',
        'post__in'       => $ids,
        'posts_per_page' => count( $ids ),
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
        'post_status'    => 'publish',
    );

    $razze = new WP_Query( $args );

    if ( ! $razze->have_posts() ) {
        return '<!-- razze_grid: nessuna razza trovata -->';
    }

    // Extra classes
    $wrapper_class = 'razze-grid-shortcode';
    $wrapper_class .= ' razze-grid-cols-' . $columns;
    if ( ! empty( $atts['class'] ) ) {
        $wrapper_class .= ' ' . sanitize_html_class( $atts['class'] );
    }

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $wrapper_class ); ?>">

        <?php if ( ! empty( $atts['title'] ) ) : ?>
            <h2 class="razze-grid-title"><?php echo esc_html( $atts['title'] ); ?></h2>
        <?php endif; ?>

        <div class="razze-grid view-grid" style="--razze-columns: <?php echo esc_attr( $columns ); ?>">
            <?php
            while ( $razze->have_posts() ) :
                $razze->the_post();
                ?>
                <article <?php post_class( 'razza-card-item' ); ?> data-id="<?php the_ID(); ?>">
                    <a href="<?php the_permalink(); ?>" class="razza-card-link">

                        <!-- Image -->
                        <div class="razza-card-image">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'caniincasa-medium', array( 'loading' => 'lazy' ) ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( CANIINCASA_THEME_URI . '/assets/images/default-dog.jpg' ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div class="razza-card-content">
                            <h3 class="razza-card-title"><?php the_title(); ?></h3>

                            <!-- Quick Info -->
                            <div class="razza-card-info">
                                <?php
                                $affettuosita = get_field( 'affettuosita' );
                                $energia = get_field( 'energia_e_livelli_di_attivita' );

                                if ( $affettuosita ) :
                                    ?>
                                    <span class="info-badge">
                                        <span class="icon">❤️</span>
                                        <span class="value"><?php echo number_format( $affettuosita, 1 ); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $energia ) : ?>
                                    <span class="info-badge">
                                        <span class="icon">⚡</span>
                                        <span class="value"><?php echo number_format( $energia, 1 ); ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Taglia -->
                            <?php
                            $terms = get_the_terms( get_the_ID(), 'razza_taglia' );
                            if ( $terms && ! is_wp_error( $terms ) ) :
                                ?>
                                <div class="razza-card-tags">
                                    <?php foreach ( $terms as $term ) : ?>
                                        <span class="tag"><?php echo esc_html( $term->name ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </a>
                </article>
            <?php endwhile; ?>
        </div>

    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'razze_grid', 'caniincasa_razze_grid_shortcode' );

/**
 * Carica CSS per lo shortcode
 */
function caniincasa_razze_grid_enqueue_styles() {
    // Carica il CSS razze se non già caricato
    if ( ! wp_style_is( 'caniincasa-razze', 'enqueued' ) ) {
        wp_enqueue_style( 'caniincasa-razze', CANIINCASA_THEME_URI . '/assets/css/razze.css', array(), CANIINCASA_VERSION );
    }

    // CSS aggiuntivo per lo shortcode
    $css = '
    .razze-grid-shortcode .razze-grid {
        display: grid;
        grid-template-columns: repeat(var(--razze-columns, 4), 1fr);
        gap: 1.25em;
    }
    .razze-grid-shortcode .razze-grid-title {
        margin-bottom: 1.5rem;
        font-size: 1.75rem;
        font-weight: 600;
    }
    @media (max-width: 1024px) {
        .razze-grid-shortcode .razze-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .razze-grid-shortcode .razze-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .razze-grid-shortcode .razze-grid {
            grid-template-columns: 1fr;
        }
    }
    ';
    wp_add_inline_style( 'caniincasa-razze', $css );
}

/**
 * Hook per caricare gli stili quando lo shortcode è presente
 */
function caniincasa_razze_grid_maybe_enqueue() {
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'razze_grid' ) ) {
        caniincasa_razze_grid_enqueue_styles();
    }
}
add_action( 'wp_enqueue_scripts', 'caniincasa_razze_grid_maybe_enqueue' );
