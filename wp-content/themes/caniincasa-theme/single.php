<?php
/**
 * Template for single blog post
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main single-post">

    <?php while ( have_posts() ) : the_post(); ?>

        <!-- Hero Section -->
        <div class="single-hero">
            <div class="container">
                <?php
                $categories = get_the_category();
                if ( ! empty( $categories ) ) :
                    ?>
                    <div class="post-categories">
                        <?php foreach ( $categories as $category ) : ?>
                            <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="category-badge">
                                <?php echo esc_html( $category->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 class="entry-title"><?php the_title(); ?></h1>

                <div class="entry-meta">
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <?php echo esc_html( get_the_author() ); ?>
                    </span>
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                        <?php echo esc_html( get_the_date() ); ?>
                    </span>
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <?php echo caniincasa_get_reading_time() . ' min di lettura'; ?>
                    </span>
                </div>
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
            <div class="post-content-wrapper">

                <!-- Main Content (2/3) -->
                <article class="post-main-content">

                    <!-- Featured Image -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="post-featured-image">
                            <?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid' ) ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Post Content -->
                    <div class="post-content-body">
                        <?php the_content(); ?>
                    </div>

                    <!-- Tags -->
                    <?php
                    $tags = get_the_tags();
                    if ( $tags ) :
                        ?>
                        <div class="post-tags">
                            <span class="tags-label">Tag:</span>
                            <?php foreach ( $tags as $tag ) : ?>
                                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag-link">
                                    <?php echo esc_html( $tag->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Share Buttons -->
                    <div class="post-share">
                        <?php caniincasa_social_share_buttons(); ?>
                    </div>

                    <!-- Author Bio -->
                    <div class="author-bio">
                        <div class="author-avatar">
                            <?php echo get_avatar( get_the_author_meta( 'ID' ), 80 ); ?>
                        </div>
                        <div class="author-info">
                            <h3 class="author-name"><?php echo esc_html( get_the_author() ); ?></h3>
                            <?php if ( get_the_author_meta( 'description' ) ) : ?>
                                <p class="author-description"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <?php
                    $categories = get_the_category();
                    if ( $categories ) :
                        $category_ids = array();
                        foreach ( $categories as $category ) {
                            $category_ids[] = $category->term_id;
                        }

                        $related_args = array(
                            'category__in'        => $category_ids,
                            'post__not_in'        => array( get_the_ID() ),
                            'posts_per_page'      => 3,
                            'ignore_sticky_posts' => 1,
                        );

                        $related_query = new WP_Query( $related_args );

                        if ( $related_query->have_posts() ) :
                            ?>
                            <div class="related-posts">
                                <h2 class="related-title">Articoli Correlati</h2>
                                <div class="related-posts-grid">
                                    <?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                                        <article class="related-post-card">
                                            <?php if ( has_post_thumbnail() ) : ?>
                                                <a href="<?php the_permalink(); ?>" class="related-post-image">
                                                    <?php the_post_thumbnail( 'medium' ); ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="related-post-content">
                                                <h3 class="related-post-title">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h3>
                                                <p class="related-post-date"><?php echo esc_html( get_the_date() ); ?></p>
                                            </div>
                                        </article>
                                    <?php endwhile; ?>
                                    <?php wp_reset_postdata(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Comments -->
                    <?php
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
                    ?>

                </article>

                <!-- Sidebar (1/3) -->
                <aside class="post-sidebar">

                    <!-- Search Box -->
                    <div class="sidebar-box search-box">
                        <h3 class="box-title">Cerca nel Blog</h3>
                        <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <input type="search" class="search-field" placeholder="Cerca articoli..." value="<?php echo get_search_query(); ?>" name="s" />
                            <button type="submit" class="search-submit">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Categories Box -->
                    <div class="sidebar-box categories-box">
                        <h3 class="box-title">Categorie</h3>
                        <ul class="categories-list">
                            <?php
                            $categories = get_categories( array(
                                'orderby' => 'count',
                                'order'   => 'DESC',
                                'number'  => 10,
                            ) );
                            foreach ( $categories as $category ) :
                                ?>
                                <li>
                                    <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
                                        <?php echo esc_html( $category->name ); ?>
                                        <span class="count">(<?php echo esc_html( $category->count ); ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Box -->
                    <div class="sidebar-box recent-posts-box">
                        <h3 class="box-title">Articoli Recenti</h3>
                        <?php
                        $recent_posts = new WP_Query( array(
                            'posts_per_page'      => 5,
                            'post__not_in'        => array( get_the_ID() ),
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

    <?php endwhile; ?>

</main>

<?php
get_footer();
