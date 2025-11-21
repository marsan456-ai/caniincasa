<?php
/**
 * Template for Razza Gruppo (FCI) Taxonomy Archive
 *
 * @package Caniincasa
 */

get_header();

$term = get_queried_object();
$gruppo_name = $term->name;
$gruppo_slug = $term->slug;

// Extract number from slug (gruppo-1, gruppo-2, etc.)
preg_match( '/gruppo-(\d+)/', $gruppo_slug, $matches );
$gruppo_number = isset( $matches[1] ) ? $matches[1] : '';

// Descriptions per gruppo FCI
$gruppo_descriptions = array(
    '1'  => 'Razze selezionate per la conduzione e la protezione del bestiame, caratterizzate da intelligenza, fedeltà e capacità di lavoro.',
    '2'  => 'Razze di tipo molossoide e cani da montagna, noti per forza, coraggio e temperamento protettivo.',
    '3'  => 'Razze Terrier, originariamente selezionate per la caccia in tana, caratterizzate da tenacia e vivacità.',
    '4'  => 'Razze Bassotti, specializzate nella caccia in tana grazie alla loro particolare conformazione.',
    '5'  => 'Razze di tipo primitivo e Spitz, tra le più antiche, caratterizzate da indipendenza e istinti conservati.',
    '6'  => 'Razze segugio, selezionate per seguire tracce olfattive con grande resistenza e determinazione.',
    '7'  => 'Razze da ferma, specializzate nell\'individuazione e segnalazione della selvaggina.',
    '8'  => 'Razze da riporto e acqua, eccellenti nuotatori e collaboratori nella caccia acquatica.',
    '9'  => 'Razze da compagnia, selezionate principalmente per essere compagni dell\'uomo.',
    '10' => 'Razze Levriero, tra i cani più veloci, originariamente usati per la caccia a vista.',
);

$description = isset( $gruppo_descriptions[ $gruppo_number ] ) ? $gruppo_descriptions[ $gruppo_number ] : $term->description;

// Icons per gruppo (emoji o icone)
$gruppo_icons = array(
    '1'  => '🐑', // Pastore
    '2'  => '🦁', // Molossoidi
    '3'  => '🦊', // Terrier
    '4'  => '🦡', // Bassotti
    '5'  => '🐺', // Spitz e primitivi
    '6'  => '🐾', // Segugi
    '7'  => '🦅', // Ferma
    '8'  => '🦆', // Riporto/acqua
    '9'  => '💕', // Compagnia
    '10' => '⚡', // Levrieri
);

$icon = isset( $gruppo_icons[ $gruppo_number ] ) ? $gruppo_icons[ $gruppo_number ] : '🐕';
?>

<main id="main-content" class="site-main archive-razze taxonomy-archive">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">
                <span class="gruppo-icon"><?php echo $icon; ?></span>
                <?php echo esc_html( $gruppo_name ); ?>
            </h1>
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
                    <p>Non ci sono razze in questo gruppo al momento.</p>
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

<style>
.gruppo-icon {
    font-size: 1.5em;
    margin-right: 0.3em;
    display: inline-block;
    vertical-align: middle;
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
