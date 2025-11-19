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

    // Handle import
    if ( isset( $_POST['run_import'] ) && check_admin_referer( 'breed_importer_nonce' ) ) {
        $result = caniincasa_import_breed_data();
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
            <p>Questo strumento importa i dati dal file <strong>dog_breed_age_calculator.json</strong> posizionato nella root del sito.</p>

            <h3>Dati che verranno importati:</h3>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Taglia Standard</strong>: toy, piccola, media, grande, gigante</li>
                <li><strong>Aspettativa Vita Min/Max</strong>: in anni</li>
                <li><strong>Coefficiente Cucciolo</strong>: per calcolo età 0-2 anni</li>
                <li><strong>Coefficiente Adulto</strong>: per calcolo età 2-7 anni</li>
                <li><strong>Coefficiente Senior</strong>: per calcolo età 7+ anni</li>
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
                $json_file = get_template_directory() . '/../../dog_breed_age_calculator.json';
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

            <?php if ( $file_exists && function_exists( 'get_field' ) ) : ?>
                <form method="post" action="">
                    <?php wp_nonce_field( 'breed_importer_nonce' ); ?>
                    <p>
                        <button type="submit" name="run_import" class="button button-primary button-large">
                            Avvia Importazione
                        </button>
                    </p>
                </form>
            <?php else : ?>
                <p style="color: #dc3232;"><strong>Impossibile procedere:</strong> Verifica che il file Excel sia presente e che ACF sia attivo.</p>
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
 * Import breed data from Excel file
 */
function caniincasa_import_breed_data() {
    // Check requirements
    if ( ! function_exists( 'get_field' ) ) {
        return array(
            'status' => 'error',
            'message' => 'ACF non è attivo. Installare Advanced Custom Fields.',
            'details' => array(),
        );
    }

    // Locate JSON file
    $json_file = get_template_directory() . '/../../dog_breed_age_calculator.json';

    if ( ! file_exists( $json_file ) ) {
        return array(
            'status' => 'error',
            'message' => 'File JSON non trovato: ' . $json_file,
            'details' => array(),
        );
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
            $taglia_standard = $row['taglia_standard'] ?? '';
            $aspettativa_vita_min = $row['aspettativa_vita_min'] ?? 0;
            $aspettativa_vita_max = $row['aspettativa_vita_max'] ?? 0;
            $coefficiente_cucciolo = $row['coefficiente_cucciolo'] ?? 15;
            $coefficiente_adulto = $row['coefficiente_adulto'] ?? 5;
            $coefficiente_senior = $row['coefficiente_senior'] ?? 5.5;

            // Skip empty rows
            if ( empty( $nome_razza ) ) {
                continue;
            }

            // Find matching breed in WordPress
            $normalized_excel_name = caniincasa_normalize_breed_name( $nome_razza );

            if ( isset( $breed_map[ $normalized_excel_name ] ) ) {
                $post_id = $breed_map[ $normalized_excel_name ];

                // Update ACF fields
                update_field( 'taglia_standard', $taglia_standard, $post_id );
                update_field( 'aspettativa_vita_min', intval( $aspettativa_vita_min ), $post_id );
                update_field( 'aspettativa_vita_max', intval( $aspettativa_vita_max ), $post_id );
                update_field( 'coefficiente_cucciolo', floatval( $coefficiente_cucciolo ), $post_id );
                update_field( 'coefficiente_adulto', floatval( $coefficiente_adulto ), $post_id );
                update_field( 'coefficiente_senior', floatval( $coefficiente_senior ), $post_id );

                $updated++;
                $details[] = "✓ Aggiornata: {$nome_razza}";
            } else {
                $not_found++;
                $not_found_list[] = $nome_razza;
            }
        }

        // Prepare result message
        $message = "Importazione completata! {$updated} razze aggiornate, {$not_found} non trovate nel database.";

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
