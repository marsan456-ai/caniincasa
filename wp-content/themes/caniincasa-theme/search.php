<?php
/**
 * Template for search results
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main search-results">

    <!-- Hero Section -->
    <div class="search-hero">
        <div class="container">
            <h1 class="search-title">
                Risultati di ricerca per: <span class="search-query">"<?php echo get_search_query(); ?>"</span>
            </h1>
            <?php if ( have_posts() ) : ?>
                <p class="search-count">
                    Trovati <strong><?php echo $wp_query->found_posts; ?></strong> risultati
                </p>
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
        <div class="search-content-wrapper">

            <!-- Main Content (2/3) -->
            <div class="search-main-content">

                <!-- Search Form -->
                <div class="search-form-section">
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <input
                            type="search"
                            class="search-field"
                            placeholder="Modifica la tua ricerca..."
                            value="<?php echo get_search_query(); ?>"
                            name="s"
                        />
                        <button type="submit" class="search-submit">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                            </svg>
                            Cerca
                        </button>
                    </form>
                </div>

                <?php if ( have_posts() ) : ?>

                    <!-- Results List -->
                    <div class="search-results-list">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <article class="search-result-item">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="result-thumb">
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="result-content">
                                    <div class="result-meta">
                                        <span class="result-type">
                                            <?php
                                            $post_type_obj = get_post_type_object( get_post_type() );
                                            echo esc_html( $post_type_obj->labels->singular_name );
                                            ?>
                                        </span>
                                        <?php if ( 'post' === get_post_type() ) : ?>
                                            <span class="result-date">
                                                <?php echo esc_html( get_the_date() ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h2 class="result-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>

                                    <div class="result-excerpt">
                                        <?php
                                        if ( has_excerpt() ) :
                                            echo wp_trim_words( get_the_excerpt(), 30 );
                                        else :
                                            echo wp_trim_words( get_the_content(), 30 );
                                        endif;
                                        ?>
                                    </div>

                                    <a href="<?php the_permalink(); ?>" class="result-link">
                                        Visualizza
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="search-pagination">
                        <?php
                        the_posts_pagination( array(
                            'mid_size'  => 2,
                            'prev_text' => '← Precedente',
                            'next_text' => 'Successivo →',
                        ) );
                        ?>
                    </div>

                <?php else : ?>

                    <!-- No Results Found -->
                    <div class="no-results-found">
                        <div class="no-results-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </div>
                        <h2>Nessun risultato trovato</h2>
                        <p>Non abbiamo trovato nessun risultato per "<strong><?php echo get_search_query(); ?></strong>".</p>

                        <div class="search-suggestions">
                            <h3>Suggerimenti:</h3>
                            <ul>
                                <li>Controlla che tutte le parole siano scritte correttamente</li>
                                <li>Prova con parole chiave diverse</li>
                                <li>Prova con parole chiave più generali</li>
                                <li>Prova con un minor numero di parole chiave</li>
                            </ul>
                        </div>

                        <div class="search-alternatives">
                            <h3>Potresti essere interessato a:</h3>
                            <div class="alternatives-grid">
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'razze_di_cani' ) ); ?>" class="alt-link">
                                    🐕 Razze di Cani
                                </a>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'allevamenti' ) ); ?>" class="alt-link">
                                    🏠 Allevamenti
                                </a>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'veterinari' ) ); ?>" class="alt-link">
                                    🏥 Veterinari
                                </a>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>" class="alt-link">
                                    📢 Annunci
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>

            <!-- Sidebar (1/3) -->
            <aside class="search-sidebar">

                <!-- Categories Box -->
                <div class="sidebar-box categories-box">
                    <h3 class="box-title">Categorie Blog</h3>
                    <ul class="categories-list">
                        <?php
                        $categories = get_categories( array(
                            'orderby' => 'count',
                            'order'   => 'DESC',
                            'number'  => 8,
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

                <!-- Quick Links Box -->
                <div class="sidebar-box quick-links-box">
                    <h3 class="box-title">Esplora</h3>
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
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'canili' ) ); ?>">
                                🏘️ Canili
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'pensioni_per_cani' ) ); ?>">
                                🏨 Pensioni
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'centri_cinofili' ) ); ?>">
                                🎓 Centri Cinofili
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>">
                                📢 Annunci 4 Zampe
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_dogsitter' ) ); ?>">
                                🐾 Annunci Dogsitter
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

            </aside>

        </div>
    </div>

</main>

<?php
get_footer();
