<?php
/**
 * Template for Razze di Cani Archive with Filters
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main archive-razze">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Razze di Cani</h1>
            <p class="archive-description">Scopri tutte le razze canine e trova quella perfetta per te</p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="container">
        <div class="breadcrumbs-wrapper">
            <?php caniincasa_breadcrumbs(); ?>
        </div>
    </div>

    <div class="container">
        <div class="archive-content-wrapper">

            <!-- Sidebar Filtri -->
            <aside class="razze-filters-sidebar">

                <div class="filters-box">
                    <div class="filters-header">
                        <h3>🔍 Filtra Razze</h3>
                        <button class="filters-reset" id="reset-filters">
                            Resetta
                        </button>
                    </div>

                    <form id="razze-filters-form" class="filters-form">

                        <!-- Cerca per Nome -->
                        <div class="filter-group">
                            <label for="filter-search">
                                🔎 Cerca per nome
                            </label>
                            <input
                                type="text"
                                id="filter-search"
                                name="search"
                                placeholder="Es. Labrador, Pastore..."
                                class="filter-input"
                            />
                        </div>

                        <!-- Livello di Energia -->
                        <div class="filter-group">
                            <label for="filter-energia">
                                ⚡ Livello di Energia
                            </label>
                            <select id="filter-energia" name="energia" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Molto Basso (1)</option>
                                <option value="2">Basso (2)</option>
                                <option value="3">Medio (3)</option>
                                <option value="4">Alto (4)</option>
                                <option value="5">Molto Alto (5)</option>
                            </select>
                        </div>

                        <!-- Adatta ad Appartamento -->
                        <div class="filter-group">
                            <label for="filter-appartamento">
                                🏠 Adatta ad Appartamento
                            </label>
                            <select id="filter-appartamento" name="appartamento" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Per Niente Adatta (1)</option>
                                <option value="2">Poco Adatta (2)</option>
                                <option value="3">Adatta (3)</option>
                                <option value="4">Molto Adatta (4)</option>
                                <option value="5">Perfetta (5)</option>
                            </select>
                        </div>

                        <!-- Affettuosità -->
                        <div class="filter-group">
                            <label for="filter-affettuosita">
                                ❤️ Affettuosità
                            </label>
                            <select id="filter-affettuosita" name="affettuosita" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Minima (1)</option>
                                <option value="2">Bassa (2)</option>
                                <option value="3">Media (3)</option>
                                <option value="4">Alta (4)</option>
                                <option value="5">Massima (5)</option>
                            </select>
                        </div>

                        <!-- Tolleranza verso Estranei -->
                        <div class="filter-group">
                            <label for="filter-estranei">
                                👥 Tolleranza verso Estranei
                            </label>
                            <select id="filter-estranei" name="estranei" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Molto Bassa (1)</option>
                                <option value="2">Bassa (2)</option>
                                <option value="3">Media (3)</option>
                                <option value="4">Alta (4)</option>
                                <option value="5">Molto Alta (5)</option>
                            </select>
                        </div>

                        <!-- Vocalità -->
                        <div class="filter-group">
                            <label for="filter-vocalita">
                                🔊 Vocalità
                            </label>
                            <select id="filter-vocalita" name="vocalita" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Molto Silenziosa (1)</option>
                                <option value="2">Silenziosa (2)</option>
                                <option value="3">Media (3)</option>
                                <option value="4">Vocale (4)</option>
                                <option value="5">Molto Vocale (5)</option>
                            </select>
                        </div>

                        <!-- Compatibile con Bambini -->
                        <div class="filter-group">
                            <label for="filter-bambini">
                                👨‍👩‍👧 Compatibile con Bambini
                            </label>
                            <select id="filter-bambini" name="bambini" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Poco Compatibile (1)</option>
                                <option value="2">Poco Compatibile (2)</option>
                                <option value="3">Compatibile (3)</option>
                                <option value="4">Molto Compatibile (4)</option>
                                <option value="5">Perfetta (5)</option>
                            </select>
                        </div>

                        <!-- Esperienza Richiesta -->
                        <div class="filter-group">
                            <label for="filter-esperienza">
                                👨‍🎓 Esperienza Richiesta
                            </label>
                            <select id="filter-esperienza" name="esperienza" class="filter-select">
                                <option value="">Tutti</option>
                                <option value="1">Principiante (1)</option>
                                <option value="2">Base (2)</option>
                                <option value="3">Intermedio (3)</option>
                                <option value="4">Avanzato (4)</option>
                                <option value="5">Esperto (5)</option>
                            </select>
                        </div>

                        <!-- Ordina per -->
                        <div class="filter-group">
                            <label for="filter-order">
                                📋 Ordina per
                            </label>
                            <select id="filter-order" name="order" class="filter-select">
                                <option value="name_asc">Nome A-Z</option>
                                <option value="name_desc">Nome Z-A</option>
                                <option value="energia_desc">Energia Alta ➜ Bassa</option>
                                <option value="energia_asc">Energia Bassa ➜ Alta</option>
                                <option value="affettuosita_desc">Affettuosità Alta ➜ Bassa</option>
                                <option value="affettuosita_asc">Affettuosità Bassa ➜ Alta</option>
                            </select>
                        </div>

                        <input type="hidden" name="action" value="filter_razze" />
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'filter_razze_nonce' ); ?>" />

                    </form>
                </div>

            </aside>

            <!-- Grid Razze -->
            <div class="razze-main-content">

                <!-- Results Counter -->
                <div class="results-header">
                    <span class="results-count">
                        <span id="razze-count"><?php echo $wp_query->found_posts; ?></span> razze trovate
                    </span>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                                <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <button class="view-btn" data-view="list">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/>
                                <line x1="3" y1="12" x2="21" y2="12" stroke="currentColor" stroke-width="2"/>
                                <line x1="3" y1="18" x2="21" y2="18" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Loading Overlay -->
                <div id="razze-loading" class="loading-overlay" style="display: none;">
                    <div class="loader"></div>
                    <p>Caricamento razze...</p>
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
                            <p>Prova a modificare i filtri di ricerca</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination Container -->
                <div id="razze-pagination-container">
                    <?php
                    if ( $wp_query->max_num_pages > 1 ) :
                        ?>
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

            </div>

        </div>
    </div>

</main>

<script>
jQuery(document).ready(function($) {
    'use strict';

    const $form = $('#razze-filters-form');
    const $grid = $('#razze-grid');
    const $loading = $('#razze-loading');
    const $count = $('#razze-count');
    const $paginationContainer = $('#razze-pagination-container');

    // Debounce helper
    let filterTimer;

    // Filter changes
    $form.find('input, select').on('change keyup', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            loadRazze(1);
        }, 500);
    });

    // Reset filters
    $('#reset-filters').on('click', function(e) {
        e.preventDefault();
        $form[0].reset();
        loadRazze(1);
    });

    // View toggle
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        $grid.removeClass('view-grid view-list').addClass('view-' + view);
    });

    // Load razze AJAX
    function loadRazze(page = 1) {
        $loading.show();

        const formData = $form.serialize() + '&page=' + page;

        $.ajax({
            url: caniincasaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                    $count.text(response.data.found);

                    // Update pagination
                    if (response.data.pagination) {
                        $paginationContainer.html(response.data.pagination);
                    } else {
                        $paginationContainer.html('');
                    }

                    // Update URL without reload
                    if (history.pushState) {
                        const newUrl = window.location.pathname + '?' + formData;
                        history.pushState(null, '', newUrl);
                    }
                } else {
                    $grid.html('<div class="no-results"><h3>Errore nel caricamento</h3></div>');
                    $paginationContainer.html('');
                }
            },
            error: function() {
                $grid.html('<div class="no-results"><h3>Errore nel caricamento</h3></div>');
                $paginationContainer.html('');
            },
            complete: function() {
                $loading.hide();
            }
        });
    }

    // Handle pagination clicks
    $(document).on('click', '.razze-pagination a.pagination-link', function(e) {
        e.preventDefault();

        // Get page number from data attribute (more reliable than parsing URL)
        let page = $(this).data('page');

        // Fallback: try to extract from href
        if (!page) {
            const url = $(this).attr('href');
            const match = url.match(/[?&]paged=(\d+)/);
            page = match ? parseInt(match[1]) : 1;
        }

        console.log('Pagination clicked - Loading page:', page);
        loadRazze(page);

        // Scroll to top smoothly
        $('html, body').animate({
            scrollTop: $('.archive-razze').offset().top - 100
        }, 500);
    });
});
</script>

<?php
get_footer();
