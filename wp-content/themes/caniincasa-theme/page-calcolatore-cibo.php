<?php
/**
 * Template Name: Calcolatore Quantità Cibo
 *
 * @package Caniincasa
 */

// Enqueue scripts and styles
wp_enqueue_script( 'dog-food-calculator', get_template_directory_uri() . '/assets/js/calculator-food.js', array( 'jquery' ), CANIINCASA_VERSION, true );
wp_enqueue_style( 'dog-food-calculator', get_template_directory_uri() . '/assets/css/calculator-food.css', array(), CANIINCASA_VERSION );

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
    $taglia           = get_field( 'taglia_standard', $breed->ID );
    $peso_min         = get_field( 'peso_ideale_min_maschio', $breed->ID );
    $peso_max         = get_field( 'peso_ideale_max_maschio', $breed->ID );
    $livello_attivita = get_field( 'livello_attivita', $breed->ID );

    $breed_data[] = array(
        'id'               => $breed->ID,
        'name'             => $breed->post_title,
        'taglia'           => $taglia ?: 'media',
        'peso_min'         => floatval( $peso_min ) ?: 10,
        'peso_max'         => floatval( $peso_max ) ?: 25,
        'livello_attivita' => intval( $livello_attivita ) ?: 3,
    );
}

wp_localize_script( 'dog-food-calculator', 'dogFoodCalcData', array(
    'breeds'  => $breed_data,
    'strings' => array(
        'selectBreed'   => __( 'Seleziona una razza', 'caniincasa' ),
        'calculating'   => __( 'Calcolo in corso...', 'caniincasa' ),
        'errorRequired' => __( 'Compila tutti i campi obbligatori', 'caniincasa' ),
        'gramsDay'      => __( 'grammi/giorno', 'caniincasa' ),
        'kgMonth'       => __( 'kg/mese', 'caniincasa' ),
    ),
) );

get_header();
?>

<main id="main-content" class="site-main calcolatore-page">

    <!-- Page Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Calcolatore Quantità Cibo</h1>
            <p class="archive-description">Calcola la quantità giornaliera di cibo ideale per il tuo cane</p>
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

            <div class="dog-food-calculator calculator-container">

                <!-- Tabs for calculation modes -->
                <div class="calculator-tabs">
                    <button type="button" class="tab-btn active" data-tab="crocchette">
                        <span class="tab-icon">🥣</span>
                        <span class="tab-label">Crocchette</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="barf">
                        <span class="tab-icon">🥩</span>
                        <span class="tab-label">Dieta BARF</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="casalinga">
                        <span class="tab-icon">🍲</span>
                        <span class="tab-label">Casalinga</span>
                    </button>
                </div>

                <!-- Tab Content: Crocchette -->
                <div class="tab-content active" id="tab-crocchette">
                    <form class="calculator-form" id="form-crocchette">
                        <div class="form-section">
                            <h3>Dati del Cane</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="crocc-peso">Peso del cane (kg) *</label>
                                    <input type="number" id="crocc-peso" name="peso" min="1" max="100" step="0.1" required>
                                </div>
                                <div class="form-group">
                                    <label for="crocc-eta">Età</label>
                                    <select id="crocc-eta" name="eta">
                                        <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                        <option value="adulto" selected>Adulto (1-7 anni)</option>
                                        <option value="senior">Senior (> 7 anni)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="crocc-attivita">Livello di attività</label>
                                    <select id="crocc-attivita" name="attivita">
                                        <option value="sedentario">Sedentario (poca attività)</option>
                                        <option value="moderato" selected>Moderato (1-2 ore/giorno)</option>
                                        <option value="attivo">Attivo (> 2 ore/giorno)</option>
                                        <option value="sportivo">Sportivo/Lavoro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="crocc-stato">Stato fisico</label>
                                    <select id="crocc-stato" name="stato">
                                        <option value="sottopeso">Sottopeso</option>
                                        <option value="normale" selected>Peso forma</option>
                                        <option value="sovrappeso">Sovrappeso</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Dati Crocchette</h3>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="crocc-kcal">Calorie per 100g di crocchette (kcal) *</label>
                                    <input type="number" id="crocc-kcal" name="kcal" min="200" max="600" step="1" placeholder="Es. 350" required>
                                    <span class="form-hint">Trovi questo dato sulla confezione del prodotto</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="crocc-pasti">Numero pasti al giorno</label>
                                    <select id="crocc-pasti" name="pasti">
                                        <option value="1">1 pasto</option>
                                        <option value="2" selected>2 pasti</option>
                                        <option value="3">3 pasti</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-calculate">
                            <span class="btn-icon">📊</span>
                            Calcola Quantità
                        </button>
                    </form>

                    <div class="calculator-results" id="results-crocchette" style="display: none;">
                        <!-- Results will be inserted here -->
                    </div>
                </div>

                <!-- Tab Content: BARF -->
                <div class="tab-content" id="tab-barf">
                    <form class="calculator-form" id="form-barf">
                        <div class="form-section">
                            <h3>Dati del Cane</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="barf-peso">Peso del cane (kg) *</label>
                                    <input type="number" id="barf-peso" name="peso" min="1" max="100" step="0.1" required>
                                </div>
                                <div class="form-group">
                                    <label for="barf-eta">Età</label>
                                    <select id="barf-eta" name="eta">
                                        <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                        <option value="adulto" selected>Adulto (1-7 anni)</option>
                                        <option value="senior">Senior (> 7 anni)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="barf-attivita">Livello di attività</label>
                                    <select id="barf-attivita" name="attivita">
                                        <option value="sedentario">Sedentario</option>
                                        <option value="moderato" selected>Moderato</option>
                                        <option value="attivo">Attivo</option>
                                        <option value="sportivo">Sportivo/Lavoro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="barf-percentuale">Percentuale peso corporeo</label>
                                    <select id="barf-percentuale" name="percentuale">
                                        <option value="2">2% (mantenimento/dieta)</option>
                                        <option value="2.5" selected>2.5% (standard)</option>
                                        <option value="3">3% (attivo/crescita)</option>
                                        <option value="4">4% (cuccioli)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="barf-info-box">
                            <h4>Composizione BARF Standard</h4>
                            <ul>
                                <li><strong>70%</strong> Carne muscolare + Ossa polpose</li>
                                <li><strong>10%</strong> Frattaglie (fegato, cuore, reni)</li>
                                <li><strong>15%</strong> Verdure e frutta</li>
                                <li><strong>5%</strong> Integratori (olio, uova, alghe)</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn-calculate">
                            <span class="btn-icon">📊</span>
                            Calcola Quantità
                        </button>
                    </form>

                    <div class="calculator-results" id="results-barf" style="display: none;">
                        <!-- Results will be inserted here -->
                    </div>
                </div>

                <!-- Tab Content: Casalinga -->
                <div class="tab-content" id="tab-casalinga">
                    <form class="calculator-form" id="form-casalinga">
                        <div class="form-section">
                            <h3>Dati del Cane</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="casa-peso">Peso del cane (kg) *</label>
                                    <input type="number" id="casa-peso" name="peso" min="1" max="100" step="0.1" required>
                                </div>
                                <div class="form-group">
                                    <label for="casa-eta">Età</label>
                                    <select id="casa-eta" name="eta">
                                        <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                        <option value="adulto" selected>Adulto (1-7 anni)</option>
                                        <option value="senior">Senior (> 7 anni)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="casa-attivita">Livello di attività</label>
                                    <select id="casa-attivita" name="attivita">
                                        <option value="sedentario">Sedentario</option>
                                        <option value="moderato" selected>Moderato</option>
                                        <option value="attivo">Attivo</option>
                                        <option value="sportivo">Sportivo/Lavoro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="casa-stato">Stato fisico</label>
                                    <select id="casa-stato" name="stato">
                                        <option value="sottopeso">Sottopeso</option>
                                        <option value="normale" selected>Peso forma</option>
                                        <option value="sovrappeso">Sovrappeso</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="casalinga-info-box">
                            <h4>Composizione Alimentazione Casalinga</h4>
                            <ul>
                                <li><strong>40%</strong> Proteine (carne, pesce, uova)</li>
                                <li><strong>30%</strong> Carboidrati (riso, pasta, patate)</li>
                                <li><strong>25%</strong> Verdure (zucchine, carote, spinaci)</li>
                                <li><strong>5%</strong> Grassi (olio EVO, olio di pesce)</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn-calculate">
                            <span class="btn-icon">📊</span>
                            Calcola Quantità
                        </button>
                    </form>

                    <div class="calculator-results" id="results-casalinga" style="display: none;">
                        <!-- Results will be inserted here -->
                    </div>
                </div>

                <!-- Warning Box -->
                <div class="calculator-warning">
                    <span class="warning-icon">⚠️</span>
                    <p><strong>Nota importante:</strong> Questi calcoli sono indicativi. Consulta sempre il tuo veterinario per un piano alimentare personalizzato, specialmente per cuccioli, cani anziani o con condizioni di salute particolari.</p>
                </div>

            </div>

        </div>
    </div>

</main>

<?php
get_footer();
