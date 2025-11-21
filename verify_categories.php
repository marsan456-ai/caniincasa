<?php
/**
 * Script di Verifica Categorie
 *
 * Verifica lo stato delle categorie prima/dopo l'importazione
 *
 * UTILIZZO:
 * - Browser: http://tuosito.it/verify_categories.php
 * - CLI: php verify_categories.php
 */

require_once('wp-load.php');

$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Verifica Categorie - Caniincasa.it</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            h2 {
                color: #2271b1;
                margin-top: 30px;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .stat-card h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                opacity: 0.9;
                text-transform: uppercase;
            }
            .stat-card .number {
                font-size: 36px;
                font-weight: bold;
                margin: 10px 0;
            }
            .category-list {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 4px;
                margin: 15px 0;
            }
            .category-item {
                padding: 10px;
                border-left: 4px solid #2271b1;
                background: white;
                margin: 10px 0;
                border-radius: 4px;
            }
            .category-item strong {
                color: #2271b1;
            }
            .subcategory {
                margin-left: 20px;
                padding: 5px 0;
                color: #666;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background: #2271b1;
                color: white;
                font-weight: 600;
            }
            tr:hover {
                background: #f8f9fa;
            }
            .alert {
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
                border-left: 4px solid #2271b1;
            }
            .alert.success { background: #d4edda; border-left-color: #28a745; }
            .alert.warning { background: #fff3cd; border-left-color: #ffc107; }
            .alert.danger { background: #f8d7da; border-left-color: #dc3545; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔍 Verifica Stato Categorie</h1>
    <?php
}

// Carica CSV per confronto
$csv_file = 'Articoli-Export-2025-November-21-0711-categorizzati.csv';
$csv_categories = [];

if (file_exists($csv_file)) {
    $handle = fopen($csv_file, 'r');
    // Skip BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xef\xbb\xbf") {
        rewind($handle);
    }
    fgetcsv($handle); // Skip header

    while (($row = fgetcsv($handle)) !== false) {
        $post_id = isset($row[0]) ? intval($row[0]) : 0;
        $categoria = isset($row[2]) ? trim($row[2]) : '';
        $sottocategoria = isset($row[3]) ? trim($row[3]) : '';

        if ($post_id > 0) {
            $csv_categories[$post_id] = [
                'categoria' => $categoria,
                'sottocategoria' => $sottocategoria
            ];
        }
    }
    fclose($handle);
}

// Statistiche generali
$all_categories = get_categories(array('hide_empty' => false));
$all_posts = get_posts(array(
    'post_type' => 'post',
    'numberposts' => -1,
    'post_status' => 'any'
));

$categorized_posts = 0;
$uncategorized_posts = 0;
$matching_csv = 0;
$not_matching = 0;

foreach ($all_posts as $post) {
    $cats = get_the_category($post->ID);
    if (count($cats) > 0) {
        $categorized_posts++;

        // Check se corrisponde al CSV
        if (isset($csv_categories[$post->ID])) {
            $csv_cat = $csv_categories[$post->ID]['categoria'];
            $csv_subcat = $csv_categories[$post->ID]['sottocategoria'];

            $has_main = false;
            $has_sub = false;

            foreach ($cats as $cat) {
                if ($cat->name === $csv_cat) $has_main = true;
                if ($cat->name === $csv_subcat) $has_sub = true;
            }

            if ($has_main && $has_sub) {
                $matching_csv++;
            } else {
                $not_matching++;
            }
        }
    } else {
        $uncategorized_posts++;
    }
}

// Categorie principali (senza parent)
$main_categories = array_filter($all_categories, function($cat) {
    return $cat->parent == 0;
});

// Conta articoli per categoria
$category_stats = [];
foreach ($main_categories as $cat) {
    $count = $cat->count;
    $subcats = get_categories(array(
        'parent' => $cat->term_id,
        'hide_empty' => false
    ));

    $category_stats[$cat->name] = [
        'count' => $count,
        'subcategories' => count($subcats),
        'subcats' => $subcats
    ];
}

// Output
if (!$is_cli) {
    echo "<div class='stats-grid'>";
    echo "<div class='stat-card'><h3>Totale Articoli</h3><div class='number'>" . count($all_posts) . "</div></div>";
    echo "<div class='stat-card'><h3>Con Categorie</h3><div class='number'>$categorized_posts</div></div>";
    echo "<div class='stat-card'><h3>Senza Categorie</h3><div class='number'>$uncategorized_posts</div></div>";
    echo "<div class='stat-card'><h3>Categorie Totali</h3><div class='number'>" . count($all_categories) . "</div></div>";
    echo "</div>";

    if (count($csv_categories) > 0) {
        $completion = round(($matching_csv / count($csv_categories)) * 100);
        $color = $completion >= 80 ? 'success' : ($completion >= 50 ? 'warning' : 'danger');

        echo "<div class='alert $color'>";
        echo "<strong>📊 Confronto con CSV</strong><br>";
        echo "Articoli che corrispondono al CSV: $matching_csv / " . count($csv_categories) . " ($completion%)";

        if ($not_matching > 0) {
            echo "<br>Articoli da correggere: $not_matching";
        }
        echo "</div>";
    }

    echo "<h2>📁 Categorie Principali</h2>";
    echo "<table>";
    echo "<thead><tr><th>Categoria</th><th>Articoli</th><th>Sottocategorie</th></tr></thead>";
    echo "<tbody>";

    foreach ($category_stats as $name => $data) {
        echo "<tr>";
        echo "<td><strong>$name</strong></td>";
        echo "<td>{$data['count']}</td>";
        echo "<td>{$data['subcategories']}</td>";
        echo "</tr>";

        // Mostra sottocategorie
        foreach ($data['subcats'] as $subcat) {
            echo "<tr style='background:#f8f9fa;'>";
            echo "<td style='padding-left:40px;'>→ {$subcat->name}</td>";
            echo "<td>{$subcat->count}</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";

    if ($uncategorized_posts > 0) {
        echo "<h2>⚠️ Articoli Senza Categoria</h2>";
        echo "<div class='alert warning'>Ci sono $uncategorized_posts articoli senza categoria. Controlla e assegna le categorie mancanti.</div>";
    }

    echo "</div></body></html>";
} else {
    // CLI output
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "VERIFICA STATO CATEGORIE\n";
    echo str_repeat("=", 60) . "\n\n";

    echo "📊 STATISTICHE GENERALI\n\n";
    echo "Totale articoli:          " . count($all_posts) . "\n";
    echo "Articoli con categorie:   $categorized_posts\n";
    echo "Articoli senza categorie: $uncategorized_posts\n";
    echo "Categorie totali:         " . count($all_categories) . "\n";
    echo "Categorie principali:     " . count($main_categories) . "\n\n";

    if (count($csv_categories) > 0) {
        $completion = round(($matching_csv / count($csv_categories)) * 100);
        echo "📄 CONFRONTO CSV\n\n";
        echo "Articoli nel CSV:         " . count($csv_categories) . "\n";
        echo "Corrispondenze:           $matching_csv ($completion%)\n";
        echo "Da correggere:            $not_matching\n\n";
    }

    echo "📁 CATEGORIE PRINCIPALI\n\n";
    foreach ($category_stats as $name => $data) {
        echo "• $name ({$data['count']} articoli, {$data['subcategories']} sottocategorie)\n";
        foreach ($data['subcats'] as $subcat) {
            echo "  → {$subcat->name} ({$subcat->count} articoli)\n";
        }
    }

    echo "\n";
}
