<?php
/**
 * Template for generic pages
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main single-page">

    <?php while ( have_posts() ) : the_post(); ?>

        <!-- Hero Section -->
        <div class="page-hero">
            <div class="container">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="container">
            <div class="breadcrumbs-wrapper">
                <?php caniincasa_breadcrumbs(); ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="container">
            <div class="page-content-wrapper">

                <!-- Main Content (2/3) -->
                <article class="page-main-content">

                    <!-- Featured Image -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="page-featured-image">
                            <?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid' ) ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Page Content -->
                    <div class="page-content-body">
                        <?php the_content(); ?>
                    </div>

                    <!-- Page Links for Multi-page Content -->
                    <?php
                    wp_link_pages( array(
                        'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pagine:', 'caniincasa' ) . '</span>',
                        'after'       => '</div>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                    ) );
                    ?>

                </article>

                <!-- Sidebar (1/3) -->
                <aside class="page-sidebar">

                    <!-- Search Box -->
                    <div class="sidebar-box search-box">
                        <h3 class="box-title">Cerca</h3>
                        <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <input type="search" class="search-field" placeholder="Cerca..." value="<?php echo get_search_query(); ?>" name="s" />
                            <button type="submit" class="search-submit">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Quick Links Box -->
                    <div class="sidebar-box quick-links-box">
                        <h3 class="box-title">Link Rapidi</h3>
                        <ul class="quick-links-list">
                            <li>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'razze_di_cani' ) ); ?>">
                                    🐕 Razze di Cani
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'allevamenti' ) ); ?>">
                                    🏠 Allevamenti
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'veterinari' ) ); ?>">
                                    🏥 Veterinari
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>">
                                    📢 Annunci 4 Zampe
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">
                                    📝 Blog
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Recent Posts Box -->
                    <div class="sidebar-box recent-posts-box">
                        <h3 class="box-title">Articoli Recenti</h3>
                        <?php
                        $recent_posts = new WP_Query( array(
                            'posts_per_page'      => 5,
                            'ignore_sticky_posts' => 1,
                        ) );

                        if ( $recent_posts->have_posts() ) :
                            ?>
                            <ul class="recent-posts-list">
                                <?php while ( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                                    <li class="recent-post-item">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <a href="<?php the_permalink(); ?>" class="recent-post-thumb">
                                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                                            </a>
                                        <?php endif; ?>
                                        <div class="recent-post-content">
                                            <h4 class="recent-post-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h4>
                                            <span class="recent-post-date"><?php echo esc_html( get_the_date() ); ?></span>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                                <?php wp_reset_postdata(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Share Box -->
                    <div class="sidebar-box share-box">
                        <h3 class="box-title">Condividi</h3>
                        <?php caniincasa_social_share_buttons(); ?>
                    </div>

                </aside>

            </div>
        </div>

        <!-- Comments (if enabled for pages) -->
        <?php
        if ( comments_open() || get_comments_number() ) :
            ?>
            <div class="container">
                <div class="page-comments">
                    <?php comments_template(); ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endwhile; ?>

</main>

<?php
get_footer();
