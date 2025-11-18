<?php
/**
 * Debug script per verificare i dati delle razze
 * Accedi a: /wp-content/plugins/caniincasa-core/debug-razze.php
 */

// Carica WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Query per prendere le prime 10 razze
$args = array(
    'post_type' => 'razze_di_cani',
    'posts_per_page' => 10,
    'post_status' => 'publish',
);

$query = new WP_Query($args);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Razze - TUTTI i Campi</title>";
echo "<style>
body{font-family:monospace;padding:20px;background:#f5f5f5;}
table{border-collapse:collapse;width:100%;background:white;margin-bottom:20px;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:12px;}
th{background:#f2f2f2;position:sticky;top:0;}
.meta{background:#fffbea;}
.error{background:#ffebee;color:#c62828;font-weight:bold;}
.success{background:#e8f5e9;color:#2e7d32;}
.warning{background:#fff3e0;color:#f57c00;}
h1{background:white;padding:15px;border-left:5px solid #2196f3;}
h2{background:white;padding:10px;margin-top:20px;border-left:3px solid #4caf50;}
.summary{background:white;padding:15px;margin:10px 0;}
</style>";
echo "</head><body>";
echo "<h1>🔍 Debug Completo Dati Razze - Verifica Mapping ACF</h1>";
echo "<div class='summary'>";
echo "<p><strong>Totale razze nel DB:</strong> " . $query->found_posts . "</p>";
echo "<p><strong>Razze mostrate:</strong> 10</p>";
echo "<p><strong>Campi verificati:</strong> TUTTI i 20 rating fields</p>";
echo "</div>";

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        echo "<h2>" . get_the_title() . " (ID: $post_id)</h2>";

        // TUTTI i campi rating (come definiti in acf-fields.php e csv-importer.php)
        $fields = array(
            'energia_e_livelli_di_attivita',
            'affettuosita',
            'vocalita_e_predisposizione_ad_abbaiare',
            'socievolezza_cani',
            'adattabilita_appartamento',  // ⚠️ Campo FIX
            'adattabilita_clima_caldo',
            'adattabilita_clima_freddo',
            'tolleranza_alla_solitudine',
            'compatibilita_con_i_bambini',  // ⚠️ Campo FIX
            'tolleranza_estranei',  // ⚠️ Campo FIX
            'compatibilita_con_altri_animali_domestici',
            'facilita_di_addestramento',
            'intelligenza',
            'esigenze_di_esercizio',
            'facilita_toelettatura',
            'cura_e_perdita_pelo',
            'predisposizioni_per_la_salute',
            'livello_esperienza_richiesto',  // ⚠️ Campo FIX
            'costo_mantenimento',
            'istinti_di_caccia',
        );

        // Campi problematici (erano sbagliati prima del fix)
        $problematic = array('adattabilita_appartamento', 'compatibilita_con_i_bambini', 'tolleranza_estranei', 'livello_esperienza_richiesto');

        echo "<table>";
        echo "<tr><th>Campo ACF</th><th>get_field()</th><th>get_post_meta()</th><th>Meta Key _field</th><th>Status</th></tr>";

        foreach ($fields as $field) {
            $value_acf = get_field($field, $post_id);
            $value_meta = get_post_meta($post_id, $field, true);
            $field_key = get_post_meta($post_id, '_' . $field, true);

            // Determina lo stato
            $is_problematic = in_array($field, $problematic);
            $has_value = !empty($value_meta) && is_numeric($value_meta);

            if ($has_value) {
                $status_class = 'success';
                $status = '✓ OK';
            } elseif ($is_problematic) {
                $status_class = 'error';
                $status = '⚠️ CAMPO FIX - VUOTO!';
            } else {
                $status_class = 'warning';
                $status = 'Vuoto';
            }

            $row_class = $is_problematic ? 'class="' . $status_class . '"' : '';

            echo "<tr $row_class>";
            echo "<td><strong>" . ($is_problematic ? '⚠️ ' : '') . "$field</strong></td>";
            echo "<td>" . (is_null($value_acf) ? '<em>NULL</em>' : var_export($value_acf, true)) . "</td>";
            echo "<td>" . (empty($value_meta) ? '<em>EMPTY</em>' : var_export($value_meta, true)) . "</td>";
            echo "<td>" . (empty($field_key) ? '<em>NO KEY</em>' : $field_key) . "</td>";
            echo "<td><strong>$status</strong></td>";
            echo "</tr>";
        }

        echo "</table>";

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

    // STATISTICHE AGGREGATE
    echo "<h2>📊 Statistiche Aggregate - Campi Critici</h2>";
    echo "<div class='summary'>";
    echo "<p>Verifica quante razze hanno i 4 campi problematici popolati dopo il fix:</p>";
    echo "</div>";

    // Ricarica tutte le razze per le statistiche
    $all_razze = new WP_Query(array(
        'post_type' => 'razze_di_cani',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    $stats = array(
        'adattabilita_appartamento' => 0,
        'compatibilita_con_i_bambini' => 0,
        'tolleranza_estranei' => 0,
        'livello_esperienza_richiesto' => 0,
    );

    if ($all_razze->have_posts()) {
        while ($all_razze->have_posts()) {
            $all_razze->the_post();
            $pid = get_the_ID();

            foreach ($stats as $field => $count) {
                $val = get_post_meta($pid, $field, true);
                if (!empty($val) && is_numeric($val)) {
                    $stats[$field]++;
                }
            }
        }
        wp_reset_postdata();
    }

    $total = $all_razze->found_posts;

    echo "<table>";
    echo "<tr><th>Campo Critico</th><th>Razze con Valore</th><th>Razze Senza Valore</th><th>% Popolato</th><th>Status</th></tr>";

    foreach ($stats as $field => $count) {
        $empty = $total - $count;
        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;

        if ($percentage > 90) {
            $status = '✓ OTTIMO';
            $class = 'success';
        } elseif ($percentage > 50) {
            $status = '⚠️ PARZIALE';
            $class = 'warning';
        } else {
            $status = '❌ PROBLEMA!';
            $class = 'error';
        }

        echo "<tr class='$class'>";
        echo "<td><strong>$field</strong></td>";
        echo "<td>$count / $total</td>";
        echo "<td>$empty</td>";
        echo "<td>$percentage%</td>";
        echo "<td><strong>$status</strong></td>";
        echo "</tr>";
    }

    echo "</table>";

    echo "<div class='summary'>";
    echo "<h3>💡 Come interpretare i risultati:</h3>";
    echo "<ul>";
    echo "<li><strong class='success'>✓ OTTIMO (>90%)</strong>: Il campo è popolato correttamente per quasi tutte le razze</li>";
    echo "<li><strong class='warning'>⚠️ PARZIALE (50-90%)</strong>: Alcune razze non hanno questo campo, potrebbe essere un problema del CSV originale</li>";
    echo "<li><strong class='error'>❌ PROBLEMA (<50%)</strong>: La maggior parte delle razze non ha questo campo, probabilmente c'è un errore nel mapping!</li>";
    echo "</ul>";
    echo "<p><strong>Azione richiesta:</strong> Se vedi ❌ PROBLEMA sui 4 campi critici, significa che il re-import NON ha funzionato. Controlla i log di WordPress per vedere gli errori durante l'import.</p>";
    echo "</div>";

} else {
    echo "<p><strong>NESSUNA RAZZA TROVATA!</strong></p>";
    echo "<p>Verifica che il CPT 'razze_di_cani' esista e abbia post pubblicati.</p>";
}

echo "</body></html>";
