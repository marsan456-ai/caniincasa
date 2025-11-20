<?php
/**
 * Breed Data Importer - Import calculator data from Excel
 *
 * Importa i dati dal file dog_breed_age_calculator.xlsx
 * nei campi ACF delle razze esistenti
 *
 * @package Caniincasa
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu for importer
 */
function caniincasa_breed_importer_menu() {
    add_management_page(
        'Importa Dati Razze',
        'Importa Dati Razze',
        'manage_options',
        'breed-data-importer',
        'caniincasa_breed_importer_page'
    );
}
add_action( 'admin_menu', 'caniincasa_breed_importer_menu' );

/**
 * Render importer admin page
 */
function caniincasa_breed_importer_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Non hai i permessi per accedere a questa pagina.' );
    }

    // Handle file upload
    $uploaded_file = null;
    if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK ) {
        $uploaded_file = $_FILES['json_file']['tmp_name'];
    }

    // Handle import
    if ( isset( $_POST['run_import'] ) && check_admin_referer( 'breed_importer_nonce' ) ) {
        $result = caniincasa_import_breed_data( $uploaded_file );
        echo '<div class="notice notice-' . esc_attr( $result['status'] ) . '"><p>' . esc_html( $result['message'] ) . '</p></div>';

        if ( ! empty( $result['details'] ) ) {
            echo '<div class="breed-import-details">';
            echo '<h3>Dettagli Importazione:</h3>';
            echo '<ul style="list-style: disc; margin-left: 20px;">';
            foreach ( $result['details'] as $detail ) {
                echo '<li>' . esc_html( $detail ) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Importa Dati Razze per Calcolatori</h1>

        <div class="card" style="max-width: 800px;">
            <h2>Informazioni Importazione</h2>
            <p>Questo strumento importa i dati dal file <strong>dog_breed_calculators_complete.json</strong> posizionato nella root del sito.</p>

            <h3>Dati che verranno importati (16 campi):</h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Calcolatore Età:</strong> taglia_standard, aspettativa_vita_min/max, coefficienti cucciolo/adulto/senior</li>
                <li><strong>Calcolatore Peso:</strong> peso_ideale_min/max maschio/femmina, livello_attivita</li>
                <li><strong>Calcolatore Costi:</strong> costo_alimentazione_mensile, costo_veterinario_annuale, costo_toelettatura_annuale, predisposizioni_salute</li>
            </ul>

            <h3>Come funziona:</h3>
            <ol style="list-style: decimal; margin-left: 20px;">
                <li>Il sistema cerca le razze esistenti nel database WordPress</li>
                <li>Fa il match per nome razza (case-insensitive)</li>
                <li>Aggiorna i campi ACF con i dati dell'Excel</li>
                <li>Mostra un report delle razze aggiornate e di quelle non trovate</li>
            </ol>

            <div class="breed-import-status" style="margin: 20px 0; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <?php
                $json_file = get_template_directory() . '/../../dog_breed_calculators_complete.json';
                $file_exists = file_exists( $json_file );

                if ( $file_exists ) {
                    echo '<p style="color: #46b450;"><strong>✓ File JSON trovato:</strong> ' . esc_html( $json_file ) . '</p>';
                    echo '<p><strong>Dimensione:</strong> ' . size_format( filesize( $json_file ) ) . '</p>';

                    // Count breeds in JSON
                    $json_content = file_get_contents( $json_file );
                    $breed_data = json_decode( $json_content, true );
                    if ( $breed_data ) {
                        echo '<p><strong>Razze nel JSON:</strong> ' . count( $breed_data ) . '</p>';
                    }
                } else {
                    echo '<p style="color: #dc3232;"><strong>✗ File JSON non trovato</strong></p>';
                    echo '<p>Percorso atteso: ' . esc_html( $json_file ) . '</p>';
                }

                // Count existing breeds
                $breeds = get_posts( array(
                    'post_type' => 'razze_di_cani',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                ) );
                echo '<p><strong>Razze nel database:</strong> ' . count( $breeds ) . '</p>';

                // Check if ACF is active
                if ( function_exists( 'get_field' ) ) {
                    echo '<p style="color: #46b450;"><strong>✓ ACF attivo</strong></p>';
                } else {
                    echo '<p style="color: #dc3232;"><strong>✗ ACF non attivo</strong> - Installare Advanced Custom Fields</p>';
                }
                ?>
            </div>

            <?php if ( function_exists( 'get_field' ) ) : ?>
                <h3 style="margin-top: 2rem;">Opzione 1: Importa da File Server</h3>
                <?php if ( $file_exists ) : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'breed_importer_nonce' ); ?>
                        <p>
                            <button type="submit" name="run_import" class="button button-primary button-large">
                                Avvia Importazione da Server
                            </button>
                        </p>
                    </form>
                <?php else : ?>
                    <p style="color: #dc3232;">File JSON non trovato sul server.</p>
                <?php endif; ?>

                <hr style="margin: 2rem 0;">

                <h3>Opzione 2: Carica File JSON Manualmente</h3>
                <form method="post" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'breed_importer_nonce' ); ?>
                    <p>
                        <label for="json_file" style="display: block; margin-bottom: 0.5rem;">
                            <strong>Seleziona file dog_breed_calculators_complete.json:</strong>
                        </label>
                        <input type="file" name="json_file" id="json_file" accept=".json" required>
                    </p>
                    <p>
                        <button type="submit" name="run_import" class="button button-primary button-large">
                            Carica e Importa JSON
                        </button>
                    </p>
                </form>
            <?php else : ?>
                <p style="color: #dc3232;"><strong>Impossibile procedere:</strong> ACF non è attivo. Installare Advanced Custom Fields.</p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .breed-import-details {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
        }
        .breed-import-details ul {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
    <?php
}

/**
 * Import breed data from JSON file
 *
 * @param string|null $uploaded_file Path to uploaded temp file
 */
function caniincasa_import_breed_data( $uploaded_file = null ) {
    // Check requirements
    if ( ! function_exists( 'get_field' ) ) {
        return array(
            'status' => 'error',
            'message' => 'ACF non è attivo. Installare Advanced Custom Fields.',
            'details' => array(),
        );
    }

    // Determine JSON file source
    if ( $uploaded_file && file_exists( $uploaded_file ) ) {
        $json_file = $uploaded_file;
        $source = 'upload';
    } else {
        $json_file = get_template_directory() . '/../../dog_breed_calculators_complete.json';
        $source = 'server';

        if ( ! file_exists( $json_file ) ) {
            return array(
                'status' => 'error',
                'message' => 'File JSON non trovato: ' . $json_file,
                'details' => array(),
            );
        }
    }

    try {
        // Load JSON file
        $json_content = file_get_contents( $json_file );
        $breed_data = json_decode( $json_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new Exception( 'Errore nel parsing del JSON: ' . json_last_error_msg() );
        }

        // Get all breeds from WordPress
        $breeds = get_posts( array(
            'post_type' => 'razze_di_cani',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ) );

        // Create breed name => ID mapping (normalized)
        $breed_map = array();
        foreach ( $breeds as $breed ) {
            $normalized_name = caniincasa_normalize_breed_name( $breed->post_title );
            $breed_map[ $normalized_name ] = $breed->ID;
        }

        // Import counters
        $updated = 0;
        $not_found = 0;
        $details = array();
        $not_found_list = array();

        // Process each breed from JSON
        foreach ( $breed_data as $row ) {
            $nome_razza = $row['nome_razza'] ?? '';

            // Calcolatore Età
            $taglia_standard = $row['taglia_standard'] ?? '';
            $aspettativa_vita_min = $row['aspettativa_vita_min'] ?? 0;
            $aspettativa_vita_max = $row['aspettativa_vita_max'] ?? 0;
            $coefficiente_cucciolo = $row['coefficiente_cucciolo'] ?? 15;
            $coefficiente_adulto = $row['coefficiente_adulto'] ?? 5;
            $coefficiente_senior = $row['coefficiente_senior'] ?? 5.5;

            // Calcolatore Peso
            $peso_ideale_min_maschio = $row['peso_ideale_min_maschio'] ?? null;
            $peso_ideale_max_maschio = $row['peso_ideale_max_maschio'] ?? null;
            $peso_ideale_min_femmina = $row['peso_ideale_min_femmina'] ?? null;
            $peso_ideale_max_femmina = $row['peso_ideale_max_femmina'] ?? null;
            $livello_attivita = $row['livello_attivita'] ?? '';

            // Calcolatore Costi
            $costo_alimentazione_mensile = $row['costo_alimentazione_mensile'] ?? null;
            $costo_veterinario_annuale = $row['costo_veterinario_annuale'] ?? null;
            $costo_toelettatura_annuale = $row['costo_toelettatura_annuale'] ?? null;
            $predisposizioni_salute = $row['predisposizioni_salute'] ?? '';

            // Skip empty rows
            if ( empty( $nome_razza ) ) {
                continue;
            }

            // Find matching breed in WordPress
            $normalized_excel_name = caniincasa_normalize_breed_name( $nome_razza );

            if ( isset( $breed_map[ $normalized_excel_name ] ) ) {
                $post_id = $breed_map[ $normalized_excel_name ];

                // Update ACF fields - Calcolatore Età
                update_field( 'taglia_standard', $taglia_standard, $post_id );
                update_field( 'aspettativa_vita_min', intval( $aspettativa_vita_min ), $post_id );
                update_field( 'aspettativa_vita_max', intval( $aspettativa_vita_max ), $post_id );
                update_field( 'coefficiente_cucciolo', floatval( $coefficiente_cucciolo ), $post_id );
                update_field( 'coefficiente_adulto', floatval( $coefficiente_adulto ), $post_id );
                update_field( 'coefficiente_senior', floatval( $coefficiente_senior ), $post_id );

                // Update ACF fields - Calcolatore Peso
                if ( $peso_ideale_min_maschio !== null ) {
                    update_field( 'peso_ideale_min_maschio', floatval( $peso_ideale_min_maschio ), $post_id );
                }
                if ( $peso_ideale_max_maschio !== null ) {
                    update_field( 'peso_ideale_max_maschio', floatval( $peso_ideale_max_maschio ), $post_id );
                }
                if ( $peso_ideale_min_femmina !== null ) {
                    update_field( 'peso_ideale_min_femmina', floatval( $peso_ideale_min_femmina ), $post_id );
                }
                if ( $peso_ideale_max_femmina !== null ) {
                    update_field( 'peso_ideale_max_femmina', floatval( $peso_ideale_max_femmina ), $post_id );
                }
                if ( ! empty( $livello_attivita ) ) {
                    update_field( 'livello_attivita', $livello_attivita, $post_id );
                }

                // Update ACF fields - Calcolatore Costi
                if ( $costo_alimentazione_mensile !== null ) {
                    update_field( 'costo_alimentazione_mensile', floatval( $costo_alimentazione_mensile ), $post_id );
                }
                if ( $costo_veterinario_annuale !== null ) {
                    update_field( 'costo_veterinario_annuale', floatval( $costo_veterinario_annuale ), $post_id );
                }
                if ( $costo_toelettatura_annuale !== null ) {
                    update_field( 'costo_toelettatura_annuale', floatval( $costo_toelettatura_annuale ), $post_id );
                }
                if ( ! empty( $predisposizioni_salute ) ) {
                    update_field( 'predisposizioni_salute', $predisposizioni_salute, $post_id );
                }

                $updated++;
                $details[] = "✓ Aggiornata: {$nome_razza}";
            } else {
                $not_found++;
                $not_found_list[] = $nome_razza;
            }
        }

        // Prepare result message
        $source_text = $source === 'upload' ? 'da file caricato' : 'da file server';
        $message = "Importazione completata {$source_text}! {$updated} razze aggiornate, {$not_found} non trovate nel database.";

        if ( ! empty( $not_found_list ) ) {
            $details[] = '';
            $details[] = '--- Razze non trovate nel database WordPress ---';
            foreach ( $not_found_list as $breed_name ) {
                $details[] = "✗ {$breed_name}";
            }
        }

        return array(
            'status' => 'success',
            'message' => $message,
            'details' => $details,
        );

    } catch ( Exception $e ) {
        return array(
            'status' => 'error',
            'message' => 'Errore durante l\'importazione: ' . $e->getMessage(),
            'details' => array(),
        );
    }
}

/**
 * Normalize breed name for comparison
 *
 * @param string $name Breed name
 * @return string Normalized name
 */
function caniincasa_normalize_breed_name( $name ) {
    // Convert to lowercase
    $name = mb_strtolower( $name, 'UTF-8' );

    // Remove accents
    $name = remove_accents( $name );

    // Remove extra spaces and special characters
    $name = preg_replace( '/[^a-z0-9\s]/', '', $name );
    $name = preg_replace( '/\s+/', ' ', $name );
    $name = trim( $name );

    return $name;
}
