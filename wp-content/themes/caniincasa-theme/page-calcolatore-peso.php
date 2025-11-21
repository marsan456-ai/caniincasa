<?php
/**
 * Template Name: Calcolatore Peso Cane
 *
 * @package Caniincasa
 */

// Enqueue scripts and styles
wp_enqueue_script( 'dog-weight-calculator', get_template_directory_uri() . '/assets/js/calculator-weight.js', array( 'jquery' ), CANIINCASA_VERSION, true );
wp_enqueue_style( 'dog-weight-calculator', get_template_directory_uri() . '/assets/css/calculator-weight.css', array(), CANIINCASA_VERSION );

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
    $peso_min_maschio = get_field( 'peso_ideale_min_maschio', $breed->ID );
    $peso_max_maschio = get_field( 'peso_ideale_max_maschio', $breed->ID );
    $peso_min_femmina = get_field( 'peso_ideale_min_femmina', $breed->ID );
    $peso_max_femmina = get_field( 'peso_ideale_max_femmina', $breed->ID );
    $livello_attivita = get_field( 'livello_attivita', $breed->ID );
    $taglia           = get_field( 'taglia_standard', $breed->ID );

    if ( $peso_min_maschio || $peso_min_femmina ) {
        $breed_data[] = array(
            'id'              => $breed->ID,
            'name'            => $breed->post_title,
            'taglia'          => $taglia,
            'peso_min_maschio' => floatval( $peso_min_maschio ),
            'peso_max_maschio' => floatval( $peso_max_maschio ),
            'peso_min_femmina' => floatval( $peso_min_femmina ),
            'peso_max_femmina' => floatval( $peso_max_femmina ),
            'livello_attivita' => $livello_attivita ? $livello_attivita : 'moderato',
        );
    }
}

wp_localize_script( 'dog-weight-calculator', 'dogWeightData', array(
    'breeds' => $breed_data,
) );

get_header();
?>

<main id="main-content" class="site-main calcolatore-page">

    <!-- Page Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Calcolatore Peso Ideale</h1>
            <p class="archive-description">Valuta il peso del tuo cane con il Body Condition Score e ricevi un piano personalizzato</p>
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

            <div class="dog-weight-calculator">
                <div class="calculator-container">

                    <div class="calculator-form">
                        <div class="form-section">
                            <h3>Informazioni di Base</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="weight-breed">Razza del Cane *</label>
                                    <select id="weight-breed" name="weight_breed" required>
                                        <option value="">Seleziona una razza...</option>
                                        <?php foreach ( $breeds as $breed ) : ?>
                                            <option value="<?php echo esc_attr( $breed->ID ); ?>">
                                                <?php echo esc_html( $breed->post_title ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dog-gender">Sesso *</label>
                                    <select id="dog-gender" name="dog_gender" required>
                                        <option value="">Seleziona...</option>
                                        <option value="maschio">Maschio</option>
                                        <option value="femmina">Femmina</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="current-weight">Peso Attuale (kg) *</label>
                                    <input type="number" id="current-weight" name="current_weight" min="0.5" max="100" step="0.1" required>
                                </div>
                                <div class="form-group">
                                    <label for="dog-age-weight">Età (anni)</label>
                                    <input type="number" id="dog-age-weight" name="dog_age" min="0" max="30" step="0.5" value="1">
                                    <small>Aiuta a determinare se è ancora in crescita</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Body Condition Score (BCS)</h3>
                            <p class="section-description">Rispondi alle seguenti domande per valutare la condizione fisica del tuo cane</p>

                            <div class="bcs-questions">
                                <div class="bcs-question">
                                    <label>1. Guardando il cane dall'alto, la vita è visibile?</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="bcs_q1" value="1"> Molto evidente (troppo magro)</label>
                                        <label><input type="radio" name="bcs_q1" value="5" checked> Ben definita (ideale)</label>
                                        <label><input type="radio" name="bcs_q1" value="9"> Non visibile (sovrappeso)</label>
                                    </div>
                                </div>

                                <div class="bcs-question">
                                    <label>2. Guardando di lato, l'addome è retratto?</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="bcs_q2" value="1"> Molto retratto</label>
                                        <label><input type="radio" name="bcs_q2" value="5" checked> Moderatamente retratto</label>
                                        <label><input type="radio" name="bcs_q2" value="9"> Non retratto/pendente</label>
                                    </div>
                                </div>

                                <div class="bcs-question">
                                    <label>3. Palpando, le costole sono facilmente palpabili?</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="bcs_q3" value="1"> Molto facilmente (sporgenti)</label>
                                        <label><input type="radio" name="bcs_q3" value="5" checked> Facilmente, con leggero strato di grasso</label>
                                        <label><input type="radio" name="bcs_q3" value="9"> Difficilmente (strato grasso spesso)</label>
                                    </div>
                                </div>

                                <div class="bcs-question">
                                    <label>4. C'è grasso visibile su dorso e base della coda?</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="bcs_q4" value="1"> No, ossa molto evidenti</label>
                                        <label><input type="radio" name="bcs_q4" value="5" checked> Minimo, forma naturale</label>
                                        <label><input type="radio" name="bcs_q4" value="9"> Sì, depositi adiposi evidenti</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Livello di Attività</h3>
                            <div class="form-group">
                                <label for="activity-level">Quanto è attivo il tuo cane?</label>
                                <select id="activity-level" name="activity_level">
                                    <option value="sedentario">Sedentario (< 30 min/giorno)</option>
                                    <option value="leggero">Leggero (30-60 min/giorno)</option>
                                    <option value="moderato" selected>Moderato (1-2 ore/giorno)</option>
                                    <option value="attivo">Attivo (2-3 ore/giorno)</option>
                                    <option value="molto_attivo">Molto Attivo (> 3 ore/giorno o sport)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="calculate-weight-btn" class="btn btn-primary">
                                Calcola Peso Ideale
                            </button>
                            <button type="button" id="reset-weight-calculator" class="btn btn-secondary">
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="calculator-results" id="weight-calculator-results" style="display: none;">
                        <div class="results-header">
                            <h3>Risultati della Valutazione</h3>
                        </div>

                        <div class="results-summary">
                            <div class="result-card result-main">
                                <div class="result-icon">⚖️</div>
                                <div class="result-content">
                                    <div class="result-label">Peso Ideale Target</div>
                                    <div class="result-value" id="result-ideal-weight">-</div>
                                    <div class="result-subtitle" id="result-weight-delta">-</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">📊</div>
                                <div class="result-content">
                                    <div class="result-label">Body Condition Score</div>
                                    <div class="result-value" id="result-bcs-score">-</div>
                                    <div class="result-subtitle" id="result-bcs-label">-</div>
                                </div>
                            </div>

                            <div class="result-card">
                                <div class="result-icon">🎯</div>
                                <div class="result-content">
                                    <div class="result-label">Stato Attuale</div>
                                    <div class="result-value" id="result-weight-status">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="results-plan">
                            <h4>Piano Alimentare Personalizzato</h4>
                            <div class="plan-content" id="diet-plan-content">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>

                        <div class="results-plan">
                            <h4>Piano di Esercizio</h4>
                            <div class="plan-content" id="exercise-plan-content">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>

                        <div class="results-insights">
                            <h4>Raccomandazioni e Consigli</h4>
                            <div class="insights-content" id="weight-insights-content">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="calculator-info">
                        <h4>Cos'è il Body Condition Score (BCS)?</h4>
                        <p>Il Body Condition Score è un sistema di valutazione su scala 1-9 che aiuta a determinare se un cane è sottopeso, normopeso o sovrappeso.</p>

                        <div class="bcs-scale">
                            <div class="bcs-item">
                                <strong>BCS 1-3:</strong> Sottopeso<br>
                                <small>Costole, vertebre e ossa pelviche visibili. Mancanza di grasso corporeo.</small>
                            </div>
                            <div class="bcs-item bcs-ideal">
                                <strong>BCS 4-5:</strong> Peso Ideale<br>
                                <small>Costole palpabili con leggero strato di grasso. Vita ben definita.</small>
                            </div>
                            <div class="bcs-item">
                                <strong>BCS 6-9:</strong> Sovrappeso/Obeso<br>
                                <small>Costole difficili da palpare. Depositi di grasso visibili. Vita poco definita.</small>
                            </div>
                        </div>

                        <p class="info-note">
                            <strong>Importante:</strong> Consulta sempre il veterinario prima di iniziare un programma di perdita o aumento di peso.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>

<?php
get_footer();
