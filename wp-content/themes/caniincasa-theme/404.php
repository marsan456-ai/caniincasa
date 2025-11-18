<?php
/**
 * Template for 404 Error Page
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main error-404-page">

    <!-- Error Hero Section -->
    <div class="error-hero">
        <div class="container">
            <div class="error-content">
                <div class="error-code">404</div>
                <h1 class="error-title">Pagina Non Trovata</h1>
                <p class="error-description">
                    Ops! La pagina che stai cercando non esiste o è stata spostata.
                </p>
            </div>
        </div>
    </div>

    <!-- Error Content -->
    <div class="container">
        <div class="error-404-content">

            <!-- Search Section -->
            <div class="error-search-section">
                <h2>Prova a cercare</h2>
                <p>Usa la barra di ricerca per trovare quello che stai cercando:</p>
                <form role="search" method="get" class="error-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input
                        type="search"
                        class="search-field"
                        placeholder="Cerca nel sito..."
                        value="<?php echo get_search_query(); ?>"
                        name="s"
                        autofocus
                    />
                    <button type="submit" class="search-submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                        Cerca
                    </button>
                </form>
            </div>

            <!-- Quick Links Section -->
            <div class="error-quick-links">
                <h2>Link Utili</h2>
                <div class="quick-links-grid">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">🏠</div>
                        <h3>Homepage</h3>
                        <p>Torna alla homepage</p>
                    </a>

                    <a href="<?php echo esc_url( get_post_type_archive_link( 'razze_di_cani' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">🐕</div>
                        <h3>Razze di Cani</h3>
                        <p>Esplora tutte le razze</p>
                    </a>

                    <a href="<?php echo esc_url( get_post_type_archive_link( 'allevamenti' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">🏘️</div>
                        <h3>Allevamenti</h3>
                        <p>Trova allevamenti</p>
                    </a>

                    <a href="<?php echo esc_url( get_post_type_archive_link( 'veterinari' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">🏥</div>
                        <h3>Veterinari</h3>
                        <p>Trova veterinari</p>
                    </a>

                    <a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">📢</div>
                        <h3>Annunci</h3>
                        <p>Cerca annunci</p>
                    </a>

                    <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="quick-link-card">
                        <div class="quick-link-icon">📝</div>
                        <h3>Blog</h3>
                        <p>Leggi gli articoli</p>
                    </a>
                </div>
            </div>

            <!-- Recent Posts Section -->
            <?php
            $recent_posts = new WP_Query( array(
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1,
            ) );

            if ( $recent_posts->have_posts() ) :
                ?>
                <div class="error-recent-posts">
                    <h2>Articoli Recenti</h2>
                    <div class="recent-posts-grid">
                        <?php while ( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                            <article class="recent-post-card">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="recent-post-image">
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                <?php endif; ?>
                                <div class="recent-post-content">
                                    <h3 class="recent-post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    <p class="recent-post-date"><?php echo esc_html( get_the_date() ); ?></p>
                                    <p class="recent-post-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="read-more">Leggi tutto →</a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div class="error-help-section">
                <h2>Hai bisogno di aiuto?</h2>
                <p>Se pensi che ci sia un errore o hai bisogno di assistenza, non esitare a contattarci.</p>
                <a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="btn btn-primary btn-lg">
                    Contattaci
                </a>
            </div>

        </div>
    </div>

</main>

<?php
get_footer();
