<?php
/**
 * Template for blog archives (categories, tags, dates, author)
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main archive-blog">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">
                <?php
                if ( is_category() ) :
                    single_cat_title();
                elseif ( is_tag() ) :
                    single_tag_title();
                elseif ( is_author() ) :
                    echo 'Articoli di ' . get_the_author();
                elseif ( is_day() ) :
                    echo 'Archivio del ' . get_the_date();
                elseif ( is_month() ) :
                    echo 'Archivio ' . get_the_date( 'F Y' );
                elseif ( is_year() ) :
                    echo 'Archivio ' . get_the_date( 'Y' );
                else :
                    echo 'Archivio Blog';
                endif;
                ?>
            </h1>
            <?php
            if ( is_category() || is_tag() ) :
                $description = term_description();
                if ( $description ) :
                    ?>
                    <p class="archive-description"><?php echo wp_kses_post( $description ); ?></p>
                <?php endif; ?>
            <?php endif; ?>
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
        <div class="archive-content-wrapper-blog">

            <!-- Main Content (2/3) -->
            <div class="archive-main-content">

                <?php if ( have_posts() ) : ?>

                    <!-- Posts Grid -->
                    <div class="blog-posts-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <article class="blog-post-card">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="post-card-image">
                                        <?php the_post_thumbnail( 'medium_large' ); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="post-card-content">
                                    <?php
                                    $categories = get_the_category();
                                    if ( ! empty( $categories ) ) :
                                        ?>
                                        <div class="post-card-categories">
                                            <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="category-badge">
                                                <?php echo esc_html( $categories[0]->name ); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <h2 class="post-card-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>

                                    <div class="post-card-meta">
                                        <span class="meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
                                            </svg>
                                            <?php echo esc_html( get_the_date() ); ?>
                                        </span>
                                        <span class="meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                            <?php echo caniincasa_get_reading_time() . ' min'; ?>
                                        </span>
                                    </div>

                                    <div class="post-card-excerpt">
                                        <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                    </div>

                                    <a href="<?php the_permalink(); ?>" class="post-card-link">
                                        Leggi tutto
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="blog-pagination">
                        <?php
                        the_posts_pagination( array(
                            'mid_size'  => 2,
                            'prev_text' => '← Precedente',
                            'next_text' => 'Successivo →',
                        ) );
                        ?>
                    </div>

                <?php else : ?>

                    <!-- No Posts Found -->
                    <div class="no-posts-found">
                        <h2>Nessun articolo trovato</h2>
                        <p>Non ci sono articoli in questo archivio.</p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                            Torna alla Home
                        </a>
                    </div>

                <?php endif; ?>

            </div>

            <!-- Sidebar (1/3) -->
            <aside class="archive-sidebar">

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
                            $current_class = ( is_category( $category->term_id ) ) ? ' current-category' : '';
                            ?>
                            <li class="<?php echo esc_attr( $current_class ); ?>">
                                <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
                                    <?php echo esc_html( $category->name ); ?>
                                    <span class="count">(<?php echo esc_html( $category->count ); ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Popular Tags Box -->
                <?php
                $tags = get_tags( array(
                    'orderby' => 'count',
                    'order'   => 'DESC',
                    'number'  => 15,
                ) );
                if ( $tags ) :
                    ?>
                    <div class="sidebar-box tags-box">
                        <h3 class="box-title">Tag Popolari</h3>
                        <div class="tags-cloud">
                            <?php foreach ( $tags as $tag ) : ?>
                                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag-link">
                                    <?php echo esc_html( $tag->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

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

            </aside>

        </div>
    </div>

</main>

<?php
get_footer();
