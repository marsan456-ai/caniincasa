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
.gruppo-icon {
    font-size: 1.5em;
    margin-right: 0.3em;
    display: inline-block;
    vertical-align: middle;
}

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
