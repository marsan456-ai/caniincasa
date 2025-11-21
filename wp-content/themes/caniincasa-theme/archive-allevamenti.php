<?php
/**
 * Template for Allevamenti Archive
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main archive-strutture archive-allevamenti">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Allevamenti</h1>
            <p class="archive-description">Trova allevamenti riconosciuti ENCI in tutta Italia</p>
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
            <aside class="strutture-filters-sidebar">

                <div class="filters-box">
                    <div class="filters-header">
                        <h3>🔍 Filtra Allevamenti</h3>
                        <button class="filters-reset" id="reset-filters">
                            Resetta
                        </button>
                    </div>

                    <form id="allevamenti-filters-form" class="filters-form">

                        <!-- Cerca per Nome -->
                        <div class="filter-group">
                            <label for="filter-search">
                                🔎 Cerca per nome
                            </label>
                            <input
                                type="text"
                                id="filter-search"
                                name="search"
                                placeholder="Nome allevamento..."
                                class="filter-input"
                            />
                        </div>

                        <!-- Provincia -->
                        <div class="filter-group">
                            <label for="filter-provincia">
                                📍 Provincia
                            </label>
                            <select id="filter-provincia" name="provincia" class="filter-select">
                                <option value="">Tutte le province</option>
                                <?php
                                $province = caniincasa_get_province_array();
                                foreach ( $province as $sigla => $nome ) :
                                    ?>
                                    <option value="<?php echo esc_attr( $sigla ); ?>">
                                        <?php echo esc_html( $sigla . ' - ' . $nome ); ?>
                                    </option>
                                <?php endforeach; ?>
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
                                <option value="date_desc">Più Recenti</option>
                                <option value="date_asc">Meno Recenti</option>
                            </select>
                        </div>

                        <input type="hidden" name="action" value="filter_allevamenti" />
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'filter_allevamenti_nonce' ); ?>" />

                    </form>
                </div>

            </aside>

            <!-- Grid Allevamenti -->
            <div class="strutture-main-content">

                <!-- Results Counter -->
                <div class="results-header">
                    <span class="results-count">
                        <span id="allevamenti-count"><?php echo $wp_query->found_posts; ?></span> allevamenti trovati
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
                <div id="allevamenti-loading" class="loading-overlay" style="display: none;">
                    <div class="loader"></div>
                    <p>Caricamento allevamenti...</p>
                </div>

                <!-- Allevamenti Grid -->
                <div id="allevamenti-grid" class="strutture-grid view-grid">
                    <?php
                    if ( have_posts() ) :
                        while ( have_posts() ) :
                            the_post();
                            get_template_part( 'template-parts/content/content', 'allevamento-card' );
                        endwhile;
                    else :
                        ?>
                        <div class="no-results">
                            <h3>Nessun allevamento trovato</h3>
                            <p>Prova a modificare i filtri di ricerca</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination Container -->
                <div id="allevamenti-pagination-container">
                    <?php
                    if ( $wp_query->max_num_pages > 1 ) :
                        ?>
                        <div class="strutture-pagination">
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

    const $form = $('#allevamenti-filters-form');
    const $grid = $('#allevamenti-grid');
    const $loading = $('#allevamenti-loading');
    const $count = $('#allevamenti-count');
    const $paginationContainer = $('#allevamenti-pagination-container');

    // Debounce helper
    let filterTimer;

    // Filter changes
    $form.find('input, select').on('change keyup', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            loadAllevamenti(1);
        }, 500);
    });

    // Reset filters
    $('#reset-filters').on('click', function(e) {
        e.preventDefault();
        $form[0].reset();
        loadAllevamenti(1);
    });

    // View toggle
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        $grid.removeClass('view-grid view-list').addClass('view-' + view);
    });

    // Load allevamenti AJAX
    function loadAllevamenti(page = 1) {
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
    $(document).on('click', '.strutture-pagination a.pagination-link', function(e) {
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
        loadAllevamenti(page);

        // Scroll to top smoothly
        $('html, body').animate({
            scrollTop: $('.archive-allevamenti').offset().top - 100
        }, 500);
    });
});
</script>

<?php
get_footer();
