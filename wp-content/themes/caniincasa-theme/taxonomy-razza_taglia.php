<?php
/**
 * Template for Razza Taglia Taxonomy Archive
 *
 * @package Caniincasa
 */

get_header();

$term = get_queried_object();
$taglia_name = $term->name;
$taglia_slug = $term->slug;

// Descriptions per taglia
$taglia_descriptions = array(
    'toy'     => 'Razze di taglia Toy, perfette per chi cerca un cane di piccolissime dimensioni (meno di 4 kg)',
    'piccola' => 'Razze di taglia piccola, ideali per appartamenti e spazi ridotti (4-10 kg)',
    'media'   => 'Razze di taglia media, versatili e adatte a diverse situazioni (10-25 kg)',
    'grande'  => 'Razze di taglia grande, per chi ha spazio e cerca un compagno imponente (25-45 kg)',
    'gigante' => 'Razze di taglia gigante, maestose e di grande presenza (oltre 45 kg)',
);

$description = isset( $taglia_descriptions[ $taglia_slug ] ) ? $taglia_descriptions[ $taglia_slug ] : $term->description;
?>

<main id="main-content" class="site-main archive-razze taxonomy-archive">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Razze <?php echo esc_html( $taglia_name ); ?></h1>
            <p class="archive-description"><?php echo esc_html( $description ); ?></p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="container">
        <div class="breadcrumbs-wrapper">
            <?php caniincasa_breadcrumbs(); ?>
        </div>
    </div>

    <div class="container">

        <!-- Results Header -->
        <div class="results-header">
            <span class="results-count">
                <span id="razze-count"><?php echo $wp_query->found_posts; ?></span> razze trovate
            </span>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" title="Vista griglia">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
                <button class="view-btn" data-view="list" title="Vista lista">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/>
                        <line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2"/>
                        <line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Razze Grid -->
        <div id="razze-grid" class="razze-grid view-grid">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content/content', 'razza-card' );
                endwhile;
            else :
                ?>
                <div class="no-results">
                    <h3>Nessuna razza trovata</h3>
                    <p>Non ci sono razze in questa categoria al momento.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div class="razze-pagination">
                <?php
                caniincasa_pagination( array(
                    'prev_text' => '&laquo; Precedente',
                    'next_text' => 'Successiva &raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>

        <!-- CTA Box -->
        <div class="razze-cta-box">
            <div class="cta-content">
                <h3>Cerchi cuccioli o amici 4 zampe di queste razze?</h3>
                <p>Trova allevatori certificati o scopri annunci di adozione nella tua zona</p>
                <div class="cta-buttons">
                    <a href="<?php echo esc_url( home_url( '/allevamenti/' ) ); ?>" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Trova Allevamenti
                    </a>
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/inserisci-annuncio/' ) ); ?>" class="btn btn-secondary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                            Inserisci Annuncio
                        </a>
                    <?php else : ?>
                        <button type="button" class="btn btn-secondary js-open-annuncio-modal">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                            Inserisci Annuncio
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</main>

<style>
/* CTA Box */
.razze-cta-box {
    margin: 60px 0 40px;
    background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(249, 115, 22, 0.2);
}

.razze-cta-box h3 {
    color: #fff;
    font-size: 28px;
    margin: 0 0 12px;
    font-weight: 700;
}

.razze-cta-box p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    margin: 0 0 30px;
}

.cta-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-buttons .btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
}

.cta-buttons .btn svg {
    width: 20px;
    height: 20px;
}

.btn-primary {
    background: #fff;
    color: #f97316;
}

.btn-primary:hover {
    background: #f97316;
    color: #fff;
    border-color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
    background: transparent;
    color: #fff;
    border-color: #fff;
}

.btn-secondary:hover {
    background: #fff;
    color: #f97316;
    transform: translateY(-2px);
}

/* Mobile */
@media (max-width: 768px) {
    .razze-cta-box {
        padding: 30px 20px;
    }

    .razze-cta-box h3 {
        font-size: 22px;
    }

    .cta-buttons {
        flex-direction: column;
    }

    .cta-buttons .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';

    const $grid = $('#razze-grid');

    // View toggle
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        $grid.removeClass('view-grid view-list').addClass('view-' + view);

        // Save preference
        localStorage.setItem('razze_view_mode', view);
    });

    // Restore view preference
    const savedView = localStorage.getItem('razze_view_mode');
    if (savedView && savedView === 'list') {
        $('.view-btn[data-view="list"]').click();
    }
});
</script>

<?php
get_footer();
