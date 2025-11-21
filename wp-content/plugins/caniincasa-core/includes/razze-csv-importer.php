<?php
/**
 * Razze CSV Importer
 *
 * Sistema di importazione CSV per aggiornare tassonomie razze (taglia e gruppo FCI)
 * senza sovrascrivere altri dati esistenti.
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu for CSV import
 */
function caniincasa_razze_importer_menu() {
    add_submenu_page(
        'edit.php?post_type=razze_di_cani',
        'Importa Classificazioni CSV',
        'Importa CSV',
        'manage_options',
        'razze-csv-importer',
        'caniincasa_razze_importer_page'
    );
}
add_action( 'admin_menu', 'caniincasa_razze_importer_menu' );

/**
 * Render importer page
 */
function caniincasa_razze_importer_page() {
    // Check user permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Non hai i permessi per accedere a questa pagina.', 'caniincasa-core' ) );
    }

    // Handle form submission
    $import_result = null;
    if ( isset( $_POST['caniincasa_import_csv'] ) && check_admin_referer( 'caniincasa_razze_csv_import', 'caniincasa_csv_nonce' ) ) {
        $import_result = caniincasa_process_razze_csv_import();
    }

    ?>
    <div class="wrap">
        <h1><?php _e( 'Importazione Classificazioni Razze da CSV', 'caniincasa-core' ); ?></h1>

        <div class="notice notice-info">
            <p><strong><?php _e( 'Formato CSV richiesto:', 'caniincasa-core' ); ?></strong></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>ID</strong>: ID del post WordPress della razza</li>
                <li><strong>Title</strong>: Nome della razza (per riferimento)</li>
                <li><strong>Taglia</strong>: Toy, Piccola, Media, Grande, o Gigante (può contenere multiple taglie separate da virgola)</li>
                <li><strong>Gruppo FCI</strong>: Numero da 1 a 10</li>
            </ul>
            <p><?php _e( 'Esempio:', 'caniincasa-core' ); ?> <code>14790,Chihuahua,Toy,9</code></p>
            <p><em><?php _e( 'Nota: Questa importazione aggiorna SOLO le tassonomie (taglia e gruppo FCI). Tutti gli altri dati della razza rimangono invariati.', 'caniincasa-core' ); ?></em></p>
        </div>

        <?php if ( $import_result ) : ?>
            <?php if ( $import_result['success'] ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php _e( 'Importazione completata con successo!', 'caniincasa-core' ); ?></strong></p>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li><?php printf( __( 'Razze aggiornate: %d', 'caniincasa-core' ), $import_result['updated'] ); ?></li>
                        <li><?php printf( __( 'Razze non trovate: %d', 'caniincasa-core' ), $import_result['not_found'] ); ?></li>
                        <li><?php printf( __( 'Errori: %d', 'caniincasa-core' ), $import_result['errors'] ); ?></li>
                    </ul>
                    <?php if ( ! empty( $import_result['log'] ) ) : ?>
                        <details style="margin-top: 10px;">
                            <summary style="cursor: pointer; font-weight: 600;">
                                <?php _e( 'Dettagli importazione', 'caniincasa-core' ); ?>
                            </summary>
                            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto; max-height: 400px; font-size: 12px;"><?php echo esc_html( implode( "\n", $import_result['log'] ) ); ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong><?php _e( 'Errore durante l\'importazione:', 'caniincasa-core' ); ?></strong></p>
                    <p><?php echo esc_html( $import_result['message'] ); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
            <?php wp_nonce_field( 'caniincasa_razze_csv_import', 'caniincasa_csv_nonce' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="razze_csv_file">
                            <?php _e( 'File CSV', 'caniincasa-core' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="file"
                            name="razze_csv_file"
                            id="razze_csv_file"
                            accept=".csv"
                            required
                        />
                        <p class="description">
                            <?php _e( 'Seleziona il file CSV con le classificazioni delle razze.', 'caniincasa-core' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="dry_run">
                            <?php _e( 'Modalità Test (Dry Run)', 'caniincasa-core' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="dry_run" id="dry_run" value="1" />
                        <label for="dry_run">
                            <?php _e( 'Simula l\'importazione senza salvare i dati (consigliato per il primo test)', 'caniincasa-core' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input
                    type="submit"
                    name="caniincasa_import_csv"
                    id="submit"
                    class="button button-primary"
                    value="<?php esc_attr_e( 'Importa CSV', 'caniincasa-core' ); ?>"
                />
            </p>
        </form>

        <hr style="margin: 40px 0;">

        <h2><?php _e( 'Stato Tassonomie', 'caniincasa-core' ); ?></h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3><?php _e( 'Taglie Disponibili', 'caniincasa-core' ); ?></h3>
                <?php
                $taglie = get_terms( array(
                    'taxonomy'   => 'razza_taglia',
                    'hide_empty' => false,
                ) );

                if ( $taglie && ! is_wp_error( $taglie ) ) {
                    echo '<ul>';
                    foreach ( $taglie as $taglia ) {
                        $count = $taglia->count;
                        echo '<li><strong>' . esc_html( $taglia->name ) . '</strong> <code>' . esc_html( $taglia->slug ) . '</code> <span class="count">(' . $count . ' razze)</span></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p><em>' . __( 'Nessuna taglia trovata.', 'caniincasa-core' ) . '</em></p>';
                }
                ?>
            </div>

            <div class="card">
                <h3><?php _e( 'Gruppi FCI Disponibili', 'caniincasa-core' ); ?></h3>
                <?php
                $gruppi = get_terms( array(
                    'taxonomy'   => 'razza_gruppo',
                    'hide_empty' => false,
                ) );

                if ( $gruppi && ! is_wp_error( $gruppi ) ) {
                    echo '<ul>';
                    foreach ( $gruppi as $gruppo ) {
                        $count = $gruppo->count;
                        echo '<li><strong>' . esc_html( $gruppo->name ) . '</strong> <code>' . esc_html( $gruppo->slug ) . '</code> <span class="count">(' . $count . ' razze)</span></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p><em>' . __( 'Nessun gruppo trovato.', 'caniincasa-core' ) . '</em></p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Process CSV import
 *
 * @return array Result with success status and statistics
 */
function caniincasa_process_razze_csv_import() {
    $result = array(
        'success'   => false,
        'updated'   => 0,
        'not_found' => 0,
        'errors'    => 0,
        'log'       => array(),
        'message'   => '',
    );

    // Check if file was uploaded
    if ( ! isset( $_FILES['razze_csv_file'] ) || $_FILES['razze_csv_file']['error'] !== UPLOAD_ERR_OK ) {
        $result['message'] = __( 'Errore nel caricamento del file.', 'caniincasa-core' );
        return $result;
    }

    $file = $_FILES['razze_csv_file'];

    // Validate file type
    $file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    if ( $file_extension !== 'csv' ) {
        $result['message'] = __( 'Il file deve essere in formato CSV.', 'caniincasa-core' );
        return $result;
    }

    // Check dry run mode
    $dry_run = isset( $_POST['dry_run'] ) && $_POST['dry_run'] === '1';
    if ( $dry_run ) {
        $result['log'][] = '=== MODALITÀ TEST (DRY RUN) - NESSUN DATO VERRÀ SALVATO ===';
    }

    // Open file
    $file_handle = fopen( $file['tmp_name'], 'r' );
    if ( ! $file_handle ) {
        $result['message'] = __( 'Impossibile aprire il file CSV.', 'caniincasa-core' );
        return $result;
    }

    // Read header (skip empty lines at the beginning)
    $header = false;
    while ( ( $line = fgetcsv( $file_handle ) ) !== false ) {
        // Skip completely empty lines
        if ( empty( array_filter( $line ) ) ) {
            continue;
        }
        $header = $line;
        break;
    }

    // Validate header
    if ( ! $header || count( $header ) < 4 ) {
        fclose( $file_handle );
        $result['message'] = __( 'Il file CSV non ha il formato corretto. Colonne richieste: ID, Title, Taglia, Gruppo FCI', 'caniincasa-core' );
        return $result;
    }

    // Additional validation: check if header contains expected columns
    $header_lower = array_map( 'strtolower', array_map( 'trim', $header ) );
    $required = array( 'id', 'title', 'taglia', 'gruppo fci' );
    $has_all = true;
    foreach ( $required as $col ) {
        if ( ! in_array( $col, $header_lower ) ) {
            $has_all = false;
            break;
        }
    }

    if ( ! $has_all ) {
        fclose( $file_handle );
        $result['message'] = sprintf(
            __( 'Header CSV non valido. Trovato: %s. Richiesto: ID, Title, Taglia, Gruppo FCI', 'caniincasa-core' ),
            implode( ', ', $header )
        );
        return $result;
    }

    $result['log'][] = 'Inizio importazione: ' . date( 'Y-m-d H:i:s' );
    $result['log'][] = 'File: ' . $file['name'];
    $result['log'][] = '';

    $line_number = 1; // Start from 1 (header is line 0)

    // Process each row
    while ( ( $row = fgetcsv( $file_handle ) ) !== false ) {
        $line_number++;

        // Skip empty rows
        if ( empty( $row[0] ) ) {
            continue;
        }

        $post_id = intval( $row[0] );
        $title   = isset( $row[1] ) ? $row[1] : '';
        $taglia  = isset( $row[2] ) ? $row[2] : '';
        $gruppo  = isset( $row[3] ) ? intval( $row[3] ) : 0;

        // Validate post ID
        if ( $post_id <= 0 ) {
            $result['errors']++;
            $result['log'][] = "Riga {$line_number}: ID non valido ({$post_id})";
            continue;
        }

        // Check if post exists
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'razze_di_cani' ) {
            $result['not_found']++;
            $result['log'][] = "Riga {$line_number}: Razza ID {$post_id} non trovata o non è una razza";
            continue;
        }

        $updates = array();

        // Process Taglia (can be multiple, comma-separated)
        if ( ! empty( $taglia ) ) {
            $taglie_array = array_map( 'trim', explode( ',', $taglia ) );
            $taglia_slugs = array();

            foreach ( $taglie_array as $taglia_name ) {
                $taglia_slug = caniincasa_get_taglia_slug_from_name( $taglia_name );
                if ( $taglia_slug ) {
                    $taglia_slugs[] = $taglia_slug;
                } else {
                    $result['log'][] = "Riga {$line_number}: Taglia '{$taglia_name}' non riconosciuta per razza '{$title}' (ID {$post_id})";
                }
            }

            if ( ! empty( $taglia_slugs ) ) {
                if ( ! $dry_run ) {
                    wp_set_object_terms( $post_id, $taglia_slugs, 'razza_taglia', false );
                }
                $updates[] = 'Taglia: ' . implode( ', ', $taglia_slugs );
            }
        }

        // Process Gruppo FCI
        if ( $gruppo > 0 && $gruppo <= 10 ) {
            $gruppo_slug = 'gruppo-' . $gruppo;

            if ( ! $dry_run ) {
                wp_set_object_terms( $post_id, $gruppo_slug, 'razza_gruppo', false );
            }
            $updates[] = 'Gruppo FCI: ' . $gruppo;
        } else {
            $result['log'][] = "Riga {$line_number}: Gruppo FCI '{$gruppo}' non valido per razza '{$title}' (ID {$post_id})";
        }

        if ( ! empty( $updates ) ) {
            $result['updated']++;
            $result['log'][] = "✓ Riga {$line_number}: {$title} (ID {$post_id}) - " . implode( ', ', $updates );
        }
    }

    fclose( $file_handle );

    $result['log'][] = '';
    $result['log'][] = '=== RIEPILOGO ===';
    $result['log'][] = "Razze aggiornate: {$result['updated']}";
    $result['log'][] = "Razze non trovate: {$result['not_found']}";
    $result['log'][] = "Errori: {$result['errors']}";

    if ( $dry_run ) {
        $result['log'][] = '';
        $result['log'][] = '⚠️ MODALITÀ TEST: Nessun dato è stato salvato nel database.';
    }

    $result['success'] = true;
    return $result;
}

/**
 * Get taxonomy slug from taglia name
 *
 * @param string $name Taglia name
 * @return string|false Slug or false if not found
 */
function caniincasa_get_taglia_slug_from_name( $name ) {
    $name_lower = strtolower( trim( $name ) );

    $mapping = array(
        'toy'     => 'toy',
        'piccola' => 'piccola',
        'media'   => 'media',
        'grande'  => 'grande',
        'gigante' => 'gigante',
    );

    return isset( $mapping[ $name_lower ] ) ? $mapping[ $name_lower ] : false;
}

/**
 * Add custom CSS for importer page
 */
function caniincasa_razze_importer_admin_css() {
    $screen = get_current_screen();
    if ( $screen && $screen->id === 'razze_di_cani_page_razze-csv-importer' ) {
        ?>
        <style>
            .card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .card h3 {
                margin-top: 0;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .card ul {
                list-style: none;
                padding-left: 0;
            }
            .card ul li {
                padding: 8px 0;
                border-bottom: 1px solid #f5f5f5;
            }
            .card ul li:last-child {
                border-bottom: none;
            }
            .card code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
                margin-left: 5px;
            }
            .card .count {
                color: #646970;
                font-size: 13px;
                float: right;
            }
        </style>
        <?php
    }
}
add_action( 'admin_head', 'caniincasa_razze_importer_admin_css' );
