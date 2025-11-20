<?php
/**
 * The main template file
 *
 * @package Caniincasa
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main">
    <div class="container">
        <div class="content-wrapper">

            <?php if ( have_posts() ) : ?>

                <div class="posts-grid">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        get_template_part( 'template-parts/content/content', get_post_type() );
                    endwhile;
                    ?>
                </div>

                <?php
                // Pagination
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => __( '&laquo; Precedente', 'caniincasa' ),
                    'next_text' => __( 'Successivo &raquo;', 'caniincasa' ),
                ) );
                ?>

            <?php else : ?>

                <?php get_template_part( 'template-parts/content/content', 'none' ); ?>

            <?php endif; ?>

        </div>
    </div>
</main>

<?php
get_footer();
