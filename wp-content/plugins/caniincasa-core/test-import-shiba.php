<?php
/**
 * Test import di UNA singola razza (Shiba) per debug
 * Accedi a: /wp-content/plugins/caniincasa-core/test-import-shiba.php
 */

// Carica WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Assicurati di essere admin
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Import Shiba</title>";
echo "<style>
body{font-family:monospace;padding:20px;background:#f5f5f5;}
pre{background:white;padding:15px;border-left:3px solid #2196f3;overflow-x:auto;}
.success{color:#2e7d32;font-weight:bold;}
.error{color:#c62828;font-weight:bold;}
h1{background:white;padding:15px;border-left:5px solid #4caf50;}
</style>";
echo "</head><body>";
echo "<h1>🧪 Test Import Singola Razza: Shiba Inu</h1>";

// Trova lo Shiba nel CSV
$csv_path = ABSPATH . 'Razze-di-Cani-Export-2025-November-17-1521.csv';
if (!file_exists($csv_path)) {
    die("<p class='error'>❌ CSV non trovato: $csv_path</p></body></html>");
}

echo "<p>📁 CSV trovato: <code>$csv_path</code></p>";

$handle = fopen($csv_path, 'r');
$headers = fgetcsv($handle);
$headers[0] = str_replace("\xEF\xBB\xBF", '', $headers[0]);

echo "<p>📊 Colonne nel CSV: " . count($headers) . "</p>";
echo "<details><summary>Mostra header CSV</summary><pre>" . print_r($headers, true) . "</pre></details>";

// Cerca lo Shiba
$shiba_data = null;
while (($row = fgetcsv($handle)) !== false) {
    if (stripos($row[1], 'Shiba') !== false) {
        $shiba_data = array_combine($headers, $row);
        break;
    }
}
fclose($handle);

if (!$shiba_data) {
    die("<p class='error'>❌ Shiba non trovato nel CSV!</p></body></html>");
}

echo "<p class='success'>✓ Shiba trovato nel CSV: <strong>" . $shiba_data['Title'] . "</strong></p>";

// Mostra i 4 campi critici dal CSV
echo "<h2>📋 Dati CSV per i 4 campi critici:</h2>";
echo "<pre>";
echo "adattabilita_appartamento: " . $shiba_data['adattabilita_appartamento'] . "\n";
echo "compatibilita_con_i_bambini: " . $shiba_data['compatibilita_con_i_bambini'] . "\n";
echo "tolleranza_estranei: " . $shiba_data['tolleranza_estranei'] . "\n";
echo "livello_esperienza_richiesto: " . $shiba_data['livello_esperienza_richiesto'] . "\n";
echo "</pre>";

// Trova il post esistente
$slug = !empty($shiba_data['Slug']) ? sanitize_title($shiba_data['Slug']) : sanitize_title($shiba_data['Title']);
$existing = get_page_by_path($slug, OBJECT, 'razze_di_cani');

if ($existing) {
    echo "<p>📝 Post esistente trovato: ID <strong>" . $existing->ID . "</strong> - " . $existing->post_title . "</p>";
    $post_id = $existing->ID;
} else {
    echo "<p class='error'>⚠️ Post Shiba NON trovato nel DB! Slug cercato: <code>$slug</code></p>";
    echo "<p>Creo un nuovo post...</p>";

    $post_data = array(
        'post_title'   => sanitize_text_field($shiba_data['Title']),
        'post_content' => wp_kses_post($shiba_data['Content']),
        'post_name'    => $slug,
        'post_type'    => 'razze_di_cani',
        'post_status'  => 'publish',
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        die("<p class='error'>❌ Errore creazione post: " . $post_id->get_error_message() . "</p></body></html>");
    }

    echo "<p class='success'>✓ Nuovo post creato: ID $post_id</p>";
}

// Importa i campi ACF
echo "<h2>💾 Import campi ACF...</h2>";
echo "<p><em>Controlla i log di WordPress per vedere il debug dettagliato!</em></p>";

// Carica l'importer
require_once plugin_dir_path(__FILE__) . 'includes/csv-importer.php';
$importer = new Caniincasa_CSV_Importer();

// Usa reflection per chiamare il metodo privato
$reflection = new ReflectionClass($importer);
$method = $reflection->getMethod('import_razza_acf_fields');
$method->setAccessible(true);
$method->invoke($importer, $post_id, $shiba_data);

echo "<p class='success'>✓ Metodo import_razza_acf_fields chiamato!</p>";

// Verifica i campi
echo "<h2>🔍 Verifica campi nel DB dopo l'import:</h2>";
echo "<table border='1' cellpadding='8' style='background:white;border-collapse:collapse;'>";
echo "<tr><th>Campo ACF</th><th>Valore CSV</th><th>Valore DB (get_field)</th><th>Valore Meta</th><th>Status</th></tr>";

$critical_fields = array(
    'adattabilita_appartamento',
    'compatibilita_con_i_bambini',
    'tolleranza_estranei',
    'livello_esperienza_richiesto',
);

foreach ($critical_fields as $field) {
    $csv_value = $shiba_data[$field];
    $db_value = get_field($field, $post_id);
    $meta_value = get_post_meta($post_id, $field, true);

    $status = (!empty($meta_value) && is_numeric($meta_value)) ? "✓ OK" : "❌ VUOTO";
    $status_class = $status == "✓ OK" ? "success" : "error";

    echo "<tr>";
    echo "<td><strong>$field</strong></td>";
    echo "<td>$csv_value</td>";
    echo "<td>" . var_export($db_value, true) . "</td>";
    echo "<td>" . var_export($meta_value, true) . "</td>";
    echo "<td class='$status_class'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>📄 Log Debug</h2>";
echo "<p><strong>IMPORTANTE:</strong> Controlla il file di log di WordPress per vedere il debug completo:</p>";
echo "<ul>";
echo "<li><code>/wp-content/debug.log</code> (se WP_DEBUG_LOG è attivo)</li>";
echo "<li>Oppure controlla i log del server PHP</li>";
echo "</ul>";
echo "<p>Cerca le righe che iniziano con:<br><code>=== INIZIO IMPORT ACF FIELDS PER POST ID: $post_id ===</code></p>";

echo "</body></html>";
