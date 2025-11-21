<?php
/**
 * Script di Importazione Categorie Articoli Caniincasa.it
 *
 * Importa categorie e sottocategorie da CSV negli articoli WordPress
 *
 * UTILIZZO:
 * 1. Carica questo file nella root di WordPress
 * 2. Carica il CSV nella root di WordPress
 * 3. Apri browser: http://tuosito.it/import_categories.php
 * 4. Oppure da CLI: php import_categories.php
 *
 * @version 1.0.0
 */

// Carica WordPress
require_once('wp-load.php');

// Configurazione
$csv_file = 'Articoli-Export-2025-November-21-0711-categorizzati.csv';
$dry_run = isset($_GET['dry_run']) ? true : false; // Modalità test (non modifica il DB)

// Check permessi admin
if (!is_admin() && !defined('WP_CLI')) {
    if (!current_user_can('manage_options')) {
        die('❌ Accesso negato. Devi essere amministratore.');
    }
}

// Stile per output HTML
$is_cli = php_sapi_name() === 'cli';
if (!$is_cli) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Importazione Categorie - Caniincasa.it</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                background: #f0f0f1;
                padding: 20px;
                margin: 0;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            h1 {
                color: #1d2327;
                border-bottom: 3px solid #2271b1;
                padding-bottom: 15px;
            }
            .status {
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
                border-left: 4px solid #2271b1;
            }
            .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
            .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
            .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
            .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; }
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .stat-box {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                border-left: 4px solid #2271b1;
            }
            .stat-box h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #666;
                text-transform: uppercase;
            }
            .stat-box .number {
                font-size: 32px;
                font-weight: bold;
                color: #2271b1;
            }
            .log {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 15px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                max-height: 400px;
                overflow-y: auto;
                margin: 20px 0;
            }
            .log .success-line { color: #4ec9b0; }
            .log .error-line { color: #f48771; }
            .log .info-line { color: #9cdcfe; }
            .log .warning-line { color: #dcdcaa; }
            .progress-bar {
                width: 100%;
                height: 30px;
                background: #e0e0e0;
                border-radius: 15px;
                overflow: hidden;
                margin: 20px 0;
            }
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #2271b1, #135e96);
                transition: width 0.3s;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background: #2271b1;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin: 10px 5px;
                border: none;
                cursor: pointer;
                font-size: 14px;
            }
            .button:hover {
                background: #135e96;
            }
            .button.secondary {
                background: #6c757d;
            }
            .button.danger {
                background: #dc3545;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔄 Importazione Categorie Articoli</h1>
    <?php
}

// Funzione di log
function log_message($message, $type = 'info') {
    global $is_cli;

    $icons = [
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️',
        'create' => '✨',
        'update' => '🔄'
    ];

    $icon = $icons[$type] ?? '•';

    if ($is_cli) {
        echo "$icon $message\n";
    } else {
        $class = $type . '-line';
        echo "<div class='$class'>$icon $message</div>";
    }
}

// Check file CSV
if (!file_exists($csv_file)) {
    $msg = "File CSV non trovato: $csv_file";
    if ($is_cli) {
        die("❌ $msg\n");
    } else {
        echo "<div class='status error'>$msg</div></div></body></html>";
        die();
    }
}

log_message("Avvio importazione categorie...", 'info');
log_message("File CSV: $csv_file", 'info');

if ($dry_run) {
    log_message("MODALITÀ DRY RUN - Nessuna modifica verrà effettuata", 'warning');
}

if (!$is_cli) {
    echo "<div class='log'>";
}

// Statistiche
$stats = [
    'total' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0,
    'categories_created' => 0,
    'subcategories_created' => 0
];

$created_categories = [];
$created_subcategories = [];
$category_map = []; // Cache delle categorie

// Leggi CSV
$handle = fopen($csv_file, 'r');

// Skip BOM se presente
$bom = fread($handle, 3);
if ($bom !== "\xef\xbb\xbf") {
    rewind($handle);
}

$header = fgetcsv($handle);
log_message("Header CSV: " . implode(', ', $header), 'info');

// Processa righe
$row_number = 0;

while (($row = fgetcsv($handle)) !== false) {
    $row_number++;
    $stats['total']++;

    // Parsing dati
    $post_id = isset($row[0]) ? intval($row[0]) : 0;
    $title = isset($row[1]) ? $row[1] : '';
    $categoria = isset($row[2]) ? trim($row[2]) : '';
    $sottocategoria = isset($row[3]) ? trim($row[3]) : '';

    // Validazione
    if ($post_id <= 0) {
        log_message("Riga $row_number: ID post invalido", 'error');
        $stats['errors']++;
        continue;
    }

    if (empty($categoria) || $categoria === 'NON_CATEGORIZZATO') {
        log_message("Post $post_id: Nessuna categoria assegnata", 'warning');
        $stats['skipped']++;
        continue;
    }

    // Verifica post esiste
    $post = get_post($post_id);
    if (!$post) {
        log_message("Post $post_id non trovato", 'error');
        $stats['errors']++;
        continue;
    }

    // Get or create categoria principale
    $cat_id = null;
    if (isset($category_map[$categoria])) {
        $cat_id = $category_map[$categoria];
    } else {
        $cat_term = get_term_by('name', $categoria, 'category');

        if (!$cat_term && !$dry_run) {
            $cat_result = wp_insert_term($categoria, 'category', array(
                'slug' => sanitize_title($categoria)
            ));

            if (is_wp_error($cat_result)) {
                log_message("Errore creando categoria '$categoria': " . $cat_result->get_error_message(), 'error');
                $stats['errors']++;
                continue;
            }

            $cat_id = $cat_result['term_id'];
            $category_map[$categoria] = $cat_id;
            $created_categories[] = $categoria;
            $stats['categories_created']++;
            log_message("Categoria creata: $categoria (ID: $cat_id)", 'create');
        } elseif ($cat_term) {
            $cat_id = $cat_term->term_id;
            $category_map[$categoria] = $cat_id;
        }
    }

    // Get or create sottocategoria
    $subcat_id = null;
    $subcat_key = "$categoria|$sottocategoria";

    if (isset($category_map[$subcat_key])) {
        $subcat_id = $category_map[$subcat_key];
    } else {
        $subcat_term = get_term_by('name', $sottocategoria, 'category');

        // Check se la sottocategoria esiste ma con parent diverso
        if ($subcat_term && $subcat_term->parent != $cat_id && !$dry_run) {
            wp_update_term($subcat_term->term_id, 'category', array(
                'parent' => $cat_id
            ));
            log_message("Sottocategoria '$sottocategoria' riassegnata a '$categoria'", 'update');
        }

        if (!$subcat_term && !$dry_run) {
            $subcat_result = wp_insert_term($sottocategoria, 'category', array(
                'parent' => $cat_id,
                'slug' => sanitize_title($sottocategoria)
            ));

            if (is_wp_error($subcat_result)) {
                log_message("Errore creando sottocategoria '$sottocategoria': " . $subcat_result->get_error_message(), 'error');
                $stats['errors']++;
                continue;
            }

            $subcat_id = $subcat_result['term_id'];
            $category_map[$subcat_key] = $subcat_id;
            $created_subcategories[] = "$categoria → $sottocategoria";
            $stats['subcategories_created']++;
            log_message("Sottocategoria creata: $categoria → $sottocategoria (ID: $subcat_id)", 'create');
        } elseif ($subcat_term) {
            $subcat_id = $subcat_term->term_id;
            $category_map[$subcat_key] = $subcat_id;
        }
    }

    // Assegna categorie al post
    if (!$dry_run) {
        // Rimuovi categorie esistenti (tranne Uncategorized che è ID 1)
        $existing_cats = wp_get_post_categories($post_id);
        $uncategorized_id = get_option('default_category');

        // Assegna nuove categorie
        $categories_to_set = array_filter([$cat_id, $subcat_id]);

        $result = wp_set_post_categories($post_id, $categories_to_set, false);

        if (is_wp_error($result)) {
            log_message("Post $post_id: Errore assegnazione categorie", 'error');
            $stats['errors']++;
        } else {
            $stats['updated']++;
            $short_title = mb_substr($title, 0, 50);
            log_message("Post $post_id aggiornato: $categoria → $sottocategoria | $short_title...", 'success');
        }
    } else {
        // Dry run
        $stats['updated']++;
        $short_title = mb_substr($title, 0, 50);
        log_message("[DRY RUN] Post $post_id: $categoria → $sottocategoria | $short_title...", 'info');
    }
}

fclose($handle);

if (!$is_cli) {
    echo "</div>"; // Close log div
}

// Output statistiche finali
if (!$is_cli) {
    echo "<h2>📊 Risultati Importazione</h2>";
    echo "<div class='stats'>";
    echo "<div class='stat-box'><h3>Articoli Totali</h3><div class='number'>{$stats['total']}</div></div>";
    echo "<div class='stat-box'><h3>Aggiornati</h3><div class='number'>{$stats['updated']}</div></div>";
    echo "<div class='stat-box'><h3>Errori</h3><div class='number'>{$stats['errors']}</div></div>";
    echo "<div class='stat-box'><h3>Saltati</h3><div class='number'>{$stats['skipped']}</div></div>";
    echo "<div class='stat-box'><h3>Categorie Create</h3><div class='number'>{$stats['categories_created']}</div></div>";
    echo "<div class='stat-box'><h3>Sottocategorie Create</h3><div class='number'>{$stats['subcategories_created']}</div></div>";
    echo "</div>";

    // Progress bar
    $success_rate = $stats['total'] > 0 ? round(($stats['updated'] / $stats['total']) * 100) : 0;
    echo "<div class='progress-bar'><div class='progress-fill' style='width: {$success_rate}%'>{$success_rate}%</div></div>";

    // Summary
    if ($stats['errors'] === 0 && $stats['updated'] > 0) {
        echo "<div class='status success'><strong>✅ Importazione completata con successo!</strong><br>";
        echo "Tutti i {$stats['updated']} articoli sono stati aggiornati correttamente.</div>";
    } elseif ($stats['errors'] > 0) {
        echo "<div class='status warning'><strong>⚠️ Importazione completata con alcuni errori</strong><br>";
        echo "{$stats['updated']} articoli aggiornati, {$stats['errors']} errori riscontrati.</div>";
    }

    if ($dry_run) {
        echo "<div class='status info'><strong>ℹ️ Modalità Dry Run</strong><br>";
        echo "Nessuna modifica è stata effettuata al database. Rimuovi ?dry_run dall'URL per eseguire l'importazione reale.</div>";
        echo "<a href='import_categories.php' class='button'>Esegui Importazione Reale</a>";
    } else {
        echo "<a href='" . admin_url('edit.php') . "' class='button'>Vedi Articoli</a>";
        echo "<a href='" . admin_url('edit-tags.php?taxonomy=category') . "' class='button secondary'>Vedi Categorie</a>";
    }

    echo "</div></body></html>";
} else {
    // CLI output
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📊 RISULTATI IMPORTAZIONE\n";
    echo str_repeat("=", 60) . "\n\n";
    echo "Articoli totali:        {$stats['total']}\n";
    echo "Articoli aggiornati:    {$stats['updated']}\n";
    echo "Errori:                 {$stats['errors']}\n";
    echo "Saltati:                {$stats['skipped']}\n";
    echo "Categorie create:       {$stats['categories_created']}\n";
    echo "Sottocategorie create:  {$stats['subcategories_created']}\n\n";

    if ($stats['errors'] === 0 && $stats['updated'] > 0) {
        echo "✅ Importazione completata con successo!\n";
    } elseif ($stats['errors'] > 0) {
        echo "⚠️  Importazione completata con {$stats['errors']} errori\n";
    }

    if ($dry_run) {
        echo "\nℹ️  Modalità DRY RUN - Nessuna modifica effettuata\n";
        echo "Esegui senza --dry-run per applicare le modifiche\n";
    }
}
