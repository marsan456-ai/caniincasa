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
                    <button class="btn btn-secondary" id="open-annuncio-modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        Inserisci Annuncio
                    </button>
                </div>
            </div>
        </div>

    </div>

</main>

<!-- Modal Inserisci Annuncio -->
<div id="annuncio-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="modal-close" id="close-annuncio-modal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <div class="modal-header">
            <h2>Inserisci un Annuncio</h2>
            <p>Scegli che tipo di annuncio vuoi pubblicare</p>
        </div>

        <div class="modal-body">
            <div class="annuncio-types">
                <a href="<?php echo esc_url( home_url( '/inserisci-annuncio-4-zampe/' ) ); ?>" class="annuncio-type-card">
                    <div class="card-icon">🐕</div>
                    <h3>Annuncio 4 Zampe</h3>
                    <p>Cerca o offri un amico a 4 zampe in adozione</p>
                    <span class="card-arrow">→</span>
                </a>

                <a href="<?php echo esc_url( home_url( '/inserisci-annuncio-dogsitter/' ) ); ?>" class="annuncio-type-card">
                    <div class="card-icon">🏠</div>
                    <h3>Servizio Dog Sitter</h3>
                    <p>Offri o cerca servizi di dog sitting</p>
                    <span class="card-arrow">→</span>
                </a>
            </div>

            <?php if ( ! is_user_logged_in() ) : ?>
                <div class="modal-notice">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Devi essere registrato per inserire un annuncio. <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Accedi</a> o <a href="<?php echo esc_url( wp_registration_url() ); ?>">registrati</a>.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #f3f4f6;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    z-index: 10;
}

.modal-close:hover {
    background: #e5e7eb;
    transform: rotate(90deg);
}

.modal-header {
    padding: 32px 32px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0 0 8px;
    font-size: 24px;
    color: #1f2937;
}

.modal-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.modal-body {
    padding: 32px;
}

.annuncio-types {
    display: grid;
    gap: 16px;
    margin-bottom: 24px;
}

.annuncio-type-card {
    display: flex;
    flex-direction: column;
    padding: 24px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    background: #fff;
}

.annuncio-type-card:hover {
    border-color: #f97316;
    box-shadow: 0 8px 24px rgba(249, 115, 22, 0.15);
    transform: translateY(-4px);
}

.annuncio-type-card .card-icon {
    font-size: 48px;
    margin-bottom: 12px;
}

.annuncio-type-card h3 {
    margin: 0 0 8px;
    color: #1f2937;
    font-size: 18px;
}

.annuncio-type-card p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.annuncio-type-card .card-arrow {
    position: absolute;
    top: 50%;
    right: 24px;
    transform: translateY(-50%);
    font-size: 24px;
    color: #f97316;
    opacity: 0;
    transition: all 0.3s ease;
}

.annuncio-type-card:hover .card-arrow {
    opacity: 1;
    right: 20px;
}

.modal-notice {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 8px;
    font-size: 14px;
    color: #92400e;
}

.modal-notice svg {
    flex-shrink: 0;
    color: #f59e0b;
}

.modal-notice a {
    color: #f97316;
    font-weight: 600;
    text-decoration: underline;
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

    .modal-content {
        margin: 20px;
    }

    .modal-header,
    .modal-body {
        padding: 24px 20px;
    }

    .annuncio-type-card .card-arrow {
        display: none;
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

    // Modal open
    $('#open-annuncio-modal').on('click', function() {
        $('#annuncio-modal').fadeIn(300);
    });

    // Modal close
    $('#close-annuncio-modal, .modal-overlay').on('click', function(e) {
        if (e.target === this) {
            $('#annuncio-modal').fadeOut(300);
        }
    });

    // Prevent clicks inside modal content from closing it
    $('.modal-content').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>

<?php
get_footer();
