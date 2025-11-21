<?php
/**
 * Template Name: Calcolatore Età Cane
 *
 * @package Caniincasa
 */

// Enqueue scripts and styles
wp_enqueue_script( 'dog-age-calculator', get_template_directory_uri() . '/assets/js/calculator-age.js', array( 'jquery' ), CANIINCASA_VERSION, true );
wp_enqueue_style( 'dog-age-calculator', get_template_directory_uri() . '/assets/css/calculator-age.css', array(), CANIINCASA_VERSION );

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
    $taglia       = get_field( 'taglia_standard', $breed->ID );
    $vita_min     = get_field( 'aspettativa_vita_min', $breed->ID );
    $vita_max     = get_field( 'aspettativa_vita_max', $breed->ID );
    $coef_cucciolo = get_field( 'coefficiente_cucciolo', $breed->ID );
    $coef_adulto  = get_field( 'coefficiente_adulto', $breed->ID );
    $coef_senior  = get_field( 'coefficiente_senior', $breed->ID );

    if ( $taglia && $vita_min && $vita_max && $coef_adulto && $coef_senior ) {
        $breed_data[] = array(
            'id'           => $breed->ID,
            'name'         => $breed->post_title,
            'taglia'       => $taglia,
            'vita_min'     => intval( $vita_min ),
            'vita_max'     => intval( $vita_max ),
            'coef_cucciolo' => floatval( $coef_cucciolo ? $coef_cucciolo : 15 ),
            'coef_adulto'  => floatval( $coef_adulto ),
            'coef_senior'  => floatval( $coef_senior ),
        );
    }
}

wp_localize_script( 'dog-age-calculator', 'dogAgeData', array(
    'breeds'  => $breed_data,
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
) );

get_header();
?>

<main id="main-content" class="site-main calcolatore-page">

    <!-- Page Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Calcolatore Età Cane</h1>
            <p class="archive-description">Scopri quanti anni umani ha il tuo cane con metodi scientifici personalizzati per razza</p>
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

            <div class="dog-age-calculator">
                <div class="calculator-container">

                    <div class="calculator-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dog-breed">Razza del Cane *</label>
                                <select id="dog-breed" name="dog_breed" required>
                                    <option value="">Seleziona una razza...</option>
                                    <?php foreach ( $breeds as $breed ) : ?>
                                        <option value="<?php echo esc_attr( $breed->ID ); ?>">
                                            <?php echo esc_html( $breed->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dog-age-years">Età del Cane (anni) *</label>
                                <input type="number" id="dog-age-years" name="dog_age_years" min="0" max="30" step="1" value="1" required>
                            </div>
                            <div class="form-group">
                                <label for="dog-age-months">Mesi aggiuntivi</label>
                                <input type="number" id="dog-age-months" name="dog_age_months" min="0" max="11" step="1" value="0">
                                <small>Per cuccioli sotto 1 anno, inserisci 0 anni + mesi</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="calculate-age-btn" class="btn btn-primary">
                                Calcola Età Umana
                            </button>
                            <button type="button" id="reset-age-calculator" class="btn btn-secondary">
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="calculator-results" id="age-calculator-results" style="display: none;">
                        <div class="results-header">
                            <h3>Risultati del Calcolo</h3>
                        </div>

                        <div class="results-summary">
                            <div class="result-card result-main">
                                <div class="result-icon">🎂</div>
                                <div class="result-content">
                                    <div class="result-label">Età Umana Equivalente</div>
                                    <div class="result-value" id="result-age-breed">-</div>
                                    <div class="result-subtitle">Basato sulla razza specifica</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">📊</div>
                                <div class="result-content">
                                    <div class="result-label">Fase della Vita</div>
                                    <div class="result-value" id="result-life-stage">-</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">💚</div>
                                <div class="result-content">
                                    <div class="result-label">Aspettativa di Vita</div>
                                    <div class="result-value" id="result-life-expectancy">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="results-comparison">
                            <h4>Confronto Metodi di Calcolo</h4>
                            <div class="comparison-grid">
                                <div class="comparison-item">
                                    <div class="comparison-label">Metodo Tradizionale (×7)</div>
                                    <div class="comparison-value" id="result-age-traditional">-</div>
                                </div>
                                <div class="comparison-item">
                                    <div class="comparison-label">Metodo Scientifico UCSD</div>
                                    <div class="comparison-value" id="result-age-scientific">-</div>
                                    <small>Formula logaritmica: 16 × ln(età) + 31</small>
                                </div>
                                <div class="comparison-item">
                                    <div class="comparison-label">Metodo Specifico Razza</div>
                                    <div class="comparison-value" id="result-age-breed-comparison">-</div>
                                    <small>Coefficienti personalizzati per taglia e razza</small>
                                </div>
                            </div>
                        </div>

                        <div class="results-insights">
                            <h4>Insight e Raccomandazioni</h4>
                            <div class="insights-content" id="age-insights-content">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>

                        <div class="results-chart">
                            <h4>Progressione dell'Età</h4>
                            <div class="age-timeline" id="age-timeline">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="calculator-info">
                        <h4>Come Funziona il Calcolo?</h4>
                        <div class="info-grid">
                            <div class="info-card">
                                <h5>Metodo Tradizionale</h5>
                                <p>Il classico "×7" è una semplificazione. Ogni anno canino = 7 anni umani.</p>
                            </div>
                            <div class="info-card">
                                <h5>Metodo Scientifico</h5>
                                <p>Formula UCSD 2020 basata su metilazione del DNA: <code>16 × ln(età) + 31</code></p>
                            </div>
                            <div class="info-card">
                                <h5>Metodo Specifico Razza</h5>
                                <p>Usa coefficienti personalizzati per cucciolo, adulto e senior basati su taglia e razza.</p>
                            </div>
                        </div>
                        <p class="info-note">
                            <strong>Nota:</strong> L'età umana equivalente è una stima. Le razze più grandi invecchiano più velocemente delle razze piccole.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>

<?php
get_footer();
