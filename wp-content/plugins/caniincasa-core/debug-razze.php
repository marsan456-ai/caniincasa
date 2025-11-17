<?php
/**
 * Debug script per verificare i dati delle razze
 * Accedi a: /wp-content/plugins/caniincasa-core/debug-razze.php
 */

// Carica WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Query per prendere le prime 5 razze
$args = array(
    'post_type' => 'razze_di_cani',
    'posts_per_page' => 5,
    'post_status' => 'publish',
);

$query = new WP_Query($args);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Razze</title>";
echo "<style>body{font-family:monospace;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .meta{background:#fffbea;}</style>";
echo "</head><body>";
echo "<h1>Debug Dati Razze - Prime 5 razze</h1>";
echo "<p><strong>Totale razze trovate:</strong> " . $query->found_posts . "</p>";

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        echo "<h2>" . get_the_title() . " (ID: $post_id)</h2>";

        // Campi da verificare
        $fields = array(
            'energia_e_livelli_di_attivita',
            'adattabilita_appartamento',
            'affettuosita',
            'tolleranza_estranei',
            'vocalita_e_predisposizione_ad_abbaiare',
            'compatibilita_con_i_bambini',
            'livello_esperienza_richiesto',
        );

        echo "<table>";
        echo "<tr><th>Campo ACF</th><th>Valore get_field()</th><th>Valore get_post_meta()</th><th>Tipo</th></tr>";

        foreach ($fields as $field) {
            $value_acf = get_field($field, $post_id);
            $value_meta = get_post_meta($post_id, $field, true);

            echo "<tr>";
            echo "<td><strong>$field</strong></td>";
            echo "<td>" . (is_null($value_acf) ? '<em>NULL</em>' : var_export($value_acf, true)) . "</td>";
            echo "<td>" . (empty($value_meta) ? '<em>EMPTY</em>' : var_export($value_meta, true)) . "</td>";
            echo "<td>" . gettype($value_meta) . "</td>";
            echo "</tr>";
        }

        echo "</table><br>";

        // Mostra TUTTI i meta per questo post
        echo "<details><summary>Tutti i meta fields (click per espandere)</summary>";
        echo "<table class='meta'>";
        echo "<tr><th>Meta Key</th><th>Meta Value</th></tr>";
        $all_meta = get_post_meta($post_id);
        foreach ($all_meta as $key => $values) {
            if (strpos($key, '_') !== 0) { // Skip internal fields
                echo "<tr><td>$key</td><td>" . var_export($values[0], true) . "</td></tr>";
            }
        }
        echo "</table></details><hr>";
    }
    wp_reset_postdata();
} else {
    echo "<p><strong>NESSUNA RAZZA TROVATA!</strong></p>";
    echo "<p>Verifica che il CPT 'razze_di_cani' esista e abbia post pubblicati.</p>";
}

echo "</body></html>";
