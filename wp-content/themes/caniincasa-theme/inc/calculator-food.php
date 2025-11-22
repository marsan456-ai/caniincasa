<?php
/**
 * Dog Food Calculator - Calcolatore Quantità Cibo
 *
 * Calcola la quantità di cibo giornaliera per il cane con 3 modalità:
 * - Crocchette (basato su kcal/kg prodotto)
 * - Dieta BARF (2-3% peso corporeo)
 * - Alimentazione Casalinga (composizione bilanciata)
 *
 * Shortcode: [dog_food_calculator]
 *
 * @package Caniincasa
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register shortcode
 */
add_shortcode( 'dog_food_calculator', 'caniincasa_dog_food_calculator_shortcode' );

/**
 * Get cached breed data for food calculator
 * Uses transients to reduce database queries
 *
 * @return array Breed data for calculator
 */
function caniincasa_get_cached_breed_data_for_calculator() {
    $cache_key = 'caniincasa_food_calc_breeds';
    $breed_data = get_transient( $cache_key );

    if ( false !== $breed_data ) {
        return $breed_data;
    }

    global $wpdb;

    // Get all breed IDs and titles in a single query
    $breeds = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = %s
             ORDER BY post_title ASC",
            'razze_di_cani',
            'publish'
        ),
        ARRAY_A
    );

    if ( empty( $breeds ) ) {
        return array();
    }

    // Get all breed IDs
    $breed_ids = array_column( $breeds, 'ID' );

    // ACF fields we need
    $field_names = array(
        'taglia_standard',
        'peso_ideale_min_maschio',
        'peso_ideale_max_maschio',
        'livello_attivita',
    );

    // Build placeholders for batch query
    $id_placeholders = implode( ',', array_fill( 0, count( $breed_ids ), '%d' ) );
    $field_placeholders = implode( ',', array_fill( 0, count( $field_names ), '%s' ) );
    $args = array_merge( $breed_ids, $field_names );

    // Batch load all meta in single query
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $meta_results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
             WHERE post_id IN ($id_placeholders) AND meta_key IN ($field_placeholders)",
            ...$args
        ),
        ARRAY_A
    );

    // Organize meta by post_id
    $meta_map = array();
    foreach ( $meta_results as $row ) {
        $post_id = (int) $row['post_id'];
        if ( ! isset( $meta_map[ $post_id ] ) ) {
            $meta_map[ $post_id ] = array();
        }
        $meta_map[ $post_id ][ $row['meta_key'] ] = maybe_unserialize( $row['meta_value'] );
    }

    // Build breed data array
    $breed_data = array();
    foreach ( $breeds as $breed ) {
        $id = (int) $breed['ID'];
        $meta = isset( $meta_map[ $id ] ) ? $meta_map[ $id ] : array();

        $breed_data[] = array(
            'id'               => $id,
            'name'             => $breed['post_title'],
            'taglia'           => isset( $meta['taglia_standard'] ) && $meta['taglia_standard'] ? $meta['taglia_standard'] : 'media',
            'peso_min'         => isset( $meta['peso_ideale_min_maschio'] ) ? floatval( $meta['peso_ideale_min_maschio'] ) : 10,
            'peso_max'         => isset( $meta['peso_ideale_max_maschio'] ) ? floatval( $meta['peso_ideale_max_maschio'] ) : 25,
            'livello_attivita' => isset( $meta['livello_attivita'] ) ? intval( $meta['livello_attivita'] ) : 3,
        );
    }

    // Cache for 1 hour (breeds don't change often)
    set_transient( $cache_key, $breed_data, HOUR_IN_SECONDS );

    return $breed_data;
}

/**
 * Clear breed calculator cache when razze_di_cani posts are updated
 */
function caniincasa_clear_breed_calculator_cache( $post_id ) {
    if ( get_post_type( $post_id ) === 'razze_di_cani' ) {
        delete_transient( 'caniincasa_food_calc_breeds' );
    }
}
add_action( 'save_post', 'caniincasa_clear_breed_calculator_cache' );
add_action( 'delete_post', 'caniincasa_clear_breed_calculator_cache' );
add_action( 'acf/save_post', 'caniincasa_clear_breed_calculator_cache' );

/**
 * Render Dog Food Calculator
 */
function caniincasa_dog_food_calculator_shortcode( $atts ) {
    // Enqueue scripts and styles
    wp_enqueue_script( 'dog-food-calculator', get_template_directory_uri() . '/assets/js/calculator-food.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    wp_enqueue_style( 'dog-food-calculator', get_template_directory_uri() . '/assets/css/calculator-food.css', array(), CANIINCASA_VERSION );

    // Get cached breed data (reduces ~800 queries to 2, then 0 on cache hit)
    $breed_data = caniincasa_get_cached_breed_data_for_calculator();

    // Localize script with breed data
    wp_localize_script( 'dog-food-calculator', 'dogFoodCalcData', array(
        'breeds'  => $breed_data,
        'strings' => array(
            'selectBreed'    => __( 'Seleziona una razza', 'caniincasa' ),
            'calculating'    => __( 'Calcolo in corso...', 'caniincasa' ),
            'errorRequired'  => __( 'Compila tutti i campi obbligatori', 'caniincasa' ),
            'gramsDay'       => __( 'grammi/giorno', 'caniincasa' ),
            'kgMonth'        => __( 'kg/mese', 'caniincasa' ),
        ),
    ) );

    ob_start();
    ?>
    <div class="dog-food-calculator calculator-container">
        <div class="calculator-header">
            <h2>Calcolatore Quantit&agrave; Cibo</h2>
            <p class="calculator-intro">Calcola la quantit&agrave; giornaliera di cibo ideale per il tuo cane</p>
        </div>

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
                            <label for="crocc-eta">Et&agrave;</label>
                            <select id="crocc-eta" name="eta">
                                <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                <option value="adulto" selected>Adulto (1-7 anni)</option>
                                <option value="senior">Senior (> 7 anni)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="crocc-attivita">Livello di attivit&agrave;</label>
                            <select id="crocc-attivita" name="attivita">
                                <option value="sedentario">Sedentario (poca attivit&agrave;)</option>
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
                    Calcola Quantit&agrave;
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
                            <label for="barf-eta">Et&agrave;</label>
                            <select id="barf-eta" name="eta">
                                <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                <option value="adulto" selected>Adulto (1-7 anni)</option>
                                <option value="senior">Senior (> 7 anni)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="barf-attivita">Livello di attivit&agrave;</label>
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
                    Calcola Quantit&agrave;
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
                            <label for="casa-eta">Et&agrave;</label>
                            <select id="casa-eta" name="eta">
                                <option value="cucciolo">Cucciolo (< 1 anno)</option>
                                <option value="adulto" selected>Adulto (1-7 anni)</option>
                                <option value="senior">Senior (> 7 anni)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="casa-attivita">Livello di attivit&agrave;</label>
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
                    Calcola Quantit&agrave;
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
    <?php
    return ob_get_clean();
}
