<?php
/**
 * Template Name: Calcolatore Costi Cane
 *
 * @package Caniincasa
 */

// Enqueue scripts and styles
wp_enqueue_script( 'dog-cost-calculator', get_template_directory_uri() . '/assets/js/calculator-cost.js', array( 'jquery' ), CANIINCASA_VERSION, true );
wp_enqueue_style( 'dog-cost-calculator', get_template_directory_uri() . '/assets/css/calculator-cost.css', array(), CANIINCASA_VERSION );

// Get all breeds for dropdown
$breeds = get_posts( array(
    'post_type'      => 'razze_di_cani',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post_status'    => 'publish',
) );

// Prepare breed data for JavaScript
$breed_data = array();
foreach ( $breeds as $breed ) {
    $taglia             = get_field( 'taglia_standard', $breed->ID );
    $vita_min           = get_field( 'aspettativa_vita_min', $breed->ID );
    $vita_max           = get_field( 'aspettativa_vita_max', $breed->ID );
    $costo_alimentazione = get_field( 'costo_alimentazione_mensile', $breed->ID );
    $costo_veterinario  = get_field( 'costo_veterinario_annuale', $breed->ID );
    $costo_toelettatura = get_field( 'costo_toelettatura_annuale', $breed->ID );
    $predisposizioni    = get_field( 'predisposizioni_salute', $breed->ID );

    if ( $taglia && $vita_min && $vita_max ) {
        $breed_data[] = array(
            'id'                    => $breed->ID,
            'name'                  => $breed->post_title,
            'taglia'                => $taglia,
            'vita_min'              => intval( $vita_min ),
            'vita_max'              => intval( $vita_max ),
            'costo_alimentazione'   => floatval( $costo_alimentazione ? $costo_alimentazione : 0 ),
            'costo_veterinario'     => floatval( $costo_veterinario ? $costo_veterinario : 0 ),
            'costo_toelettatura'    => floatval( $costo_toelettatura ? $costo_toelettatura : 0 ),
            'predisposizioni_salute' => $predisposizioni ? $predisposizioni : 'media',
        );
    }
}

wp_localize_script( 'dog-cost-calculator', 'dogCostData', array(
    'breeds' => $breed_data,
) );

get_header();
?>

<main id="main-content" class="site-main calcolatore-page">

    <!-- Page Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Calcolatore Costi Mantenimento</h1>
            <p class="archive-description">Scopri i costi completi di mantenimento del tuo cane: iniziali, mensili, annuali e lifetime</p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="container">
        <div class="breadcrumbs-wrapper">
            <?php caniincasa_breadcrumbs(); ?>
        </div>
    </div>

    <div class="container">
        <div class="calculator-wrapper">

            <div class="dog-cost-calculator">
                <div class="calculator-container">

                    <div class="calculator-form">
                        <div class="form-section">
                            <h3>Informazioni del Cane</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cost-breed">Razza del Cane *</label>
                                    <select id="cost-breed" name="cost_breed" required>
                                        <option value="">Seleziona una razza...</option>
                                        <?php foreach ( $breeds as $breed ) : ?>
                                            <option value="<?php echo esc_attr( $breed->ID ); ?>">
                                                <?php echo esc_html( $breed->post_title ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dog-age-cost">Età Attuale (anni)</label>
                                    <input type="number" id="dog-age-cost" name="dog_age" min="0" max="20" step="0.5" value="0">
                                    <small>0 = cucciolo appena nato/adottato</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Località e Preferenze</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="region">Regione di Residenza</label>
                                    <select id="region" name="region">
                                        <option value="nord">Nord Italia</option>
                                        <option value="centro" selected>Centro Italia</option>
                                        <option value="sud">Sud Italia</option>
                                    </select>
                                    <small>I costi variano per regione</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="food-quality">Qualità Alimentazione</label>
                                    <select id="food-quality" name="food_quality">
                                        <option value="economica">Economica (supermercato)</option>
                                        <option value="media" selected>Media (premium)</option>
                                        <option value="alta">Alta (super premium/BARF)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="vet-plan">Piano Veterinario</label>
                                    <select id="vet-plan" name="vet_plan">
                                        <option value="base">Base (vaccini essenziali)</option>
                                        <option value="completo" selected>Completo (prevenzione)</option>
                                        <option value="assicurazione">Con Assicurazione</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Servizi Aggiuntivi</h3>

                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="include-grooming" name="include_grooming" checked>
                                    <span>Toelettatura Professionale</span>
                                </label>
                                <label>
                                    <input type="checkbox" id="include-training" name="include_training">
                                    <span>Addestramento/Educazione</span>
                                </label>
                                <label>
                                    <input type="checkbox" id="include-boarding" name="include_boarding">
                                    <span>Pensione durante Vacanze</span>
                                </label>
                                <label>
                                    <input type="checkbox" id="include-walker" name="include_walker">
                                    <span>Dog Walker Regolare</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="calculate-cost-btn" class="btn btn-primary">
                                Calcola Costi
                            </button>
                            <button type="button" id="reset-cost-calculator" class="btn btn-secondary">
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="calculator-results" id="cost-calculator-results" style="display: none;">
                        <div class="results-header">
                            <h3>Analisi Completa dei Costi</h3>
                        </div>

                        <div class="results-summary">
                            <div class="result-card result-main">
                                <div class="result-icon">💰</div>
                                <div class="result-content">
                                    <div class="result-label">Costo Lifetime Totale</div>
                                    <div class="result-value" id="result-lifetime-cost">-</div>
                                    <div class="result-subtitle" id="result-lifetime-years">-</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">📅</div>
                                <div class="result-content">
                                    <div class="result-label">Costo Annuale</div>
                                    <div class="result-value" id="result-annual-cost">-</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">📆</div>
                                <div class="result-content">
                                    <div class="result-label">Costo Mensile</div>
                                    <div class="result-value" id="result-monthly-cost">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="cost-breakdown">
                            <h4>Dettaglio Costi</h4>
                            <div class="breakdown-tabs">
                                <button class="tab-btn active" data-tab="initial">Costi Iniziali</button>
                                <button class="tab-btn" data-tab="recurring">Costi Ricorrenti</button>
                                <button class="tab-btn" data-tab="optional">Servizi Extra</button>
                            </div>

                            <div class="tab-content active" id="tab-initial">
                                <div class="breakdown-list" id="initial-costs-list">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <div class="tab-content" id="tab-recurring">
                                <div class="breakdown-list" id="recurring-costs-list">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <div class="tab-content" id="tab-optional">
                                <div class="breakdown-list" id="optional-costs-list">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <div class="results-chart">
                            <h4>Distribuzione Costi Annuali</h4>
                            <div class="cost-chart" id="cost-distribution-chart">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>

                        <div class="results-insights">
                            <h4>Consigli per Risparmiare</h4>
                            <div class="insights-content" id="cost-insights-content">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="calculator-info">
                        <h4>Informazioni sui Costi</h4>
                        <p>I costi sono stimati basandosi su:</p>
                        <ul>
                            <li><strong>Dati 2024</strong> per l'Italia</li>
                            <li><strong>Taglia della razza</strong> (cibo, accessori, farmaci)</li>
                            <li><strong>Aspettativa di vita</strong> della razza</li>
                            <li><strong>Predisposizioni salute</strong> comuni nella razza</li>
                            <li><strong>Variazioni regionali</strong> dei prezzi</li>
                        </ul>
                        <p class="info-note">
                            <strong>Nota:</strong> Le stime sono indicative. I costi effettivi possono variare in base a condizioni di salute specifiche, stile di vita e scelte personali.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>

<?php
get_footer();
