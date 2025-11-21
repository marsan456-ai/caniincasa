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

    </div>

</main>

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
