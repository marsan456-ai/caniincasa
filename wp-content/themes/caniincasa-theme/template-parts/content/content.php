<?php
/**
 * Template part for displaying posts
 *
 * @package Caniincasa
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>

    <?php caniincasa_post_thumbnail( 'caniincasa-medium' ); ?>

    <div class="post-content-wrapper">

        <?php caniincasa_entry_categories(); ?>

        <header class="entry-header">
            <?php
            if ( is_singular() ) :
                the_title( '<h1 class="entry-title">', '</h1>' );
            else :
                the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
            endif;
            ?>
        </header>

        <?php caniincasa_entry_meta(); ?>

        <div class="entry-content">
            <?php
            if ( is_singular() ) {
                the_content();

                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pagine:', 'caniincasa' ),
                    'after'  => '</div>',
                ) );
            } else {
                the_excerpt();
                ?>
                <a href="<?php the_permalink(); ?>" class="read-more">
                    <?php echo esc_html( get_theme_mod( 'caniincasa_read_more_text', __( 'Leggi tutto', 'caniincasa' ) ) ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <?php
            }
            ?>
        </div>

        <?php if ( is_singular() ) : ?>
            <footer class="entry-footer">
                <?php caniincasa_entry_tags(); ?>
            </footer>
        <?php endif; ?>

    </div>

</article>
