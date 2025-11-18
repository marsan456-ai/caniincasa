<?php
/**
 * Test Verify Strutture Import
 *
 * Verifica minuziosa CSV vs dati importati in ACF
 * URL: http://cani-in-casa.local/wp-content/plugins/caniincasa-core/test-verify-strutture-import.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Security check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Accesso negato' );
}

echo "<h1>Verifica Import Strutture</h1>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: #16a34a; font-weight: 600; }
    .error { color: #dc2626; font-weight: 600; }
    .warning { color: #ea580c; font-weight: 600; }
    .info { color: #2563eb; }
    pre { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; overflow-x: auto; }
    table { border-collapse: collapse; margin: 20px 0; background: white; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    table td, table th { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
    table th { background-color: #3b82f6; color: white; font-weight: 600; }
    table tr:nth-child(even) { background-color: #f9fafb; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    h2 { color: #1f2937; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; }
    .stat-number { font-size: 2em; font-weight: bold; }
    .stat-label { opacity: 0.9; margin-top: 5px; }
</style>";

$strutture_types = array(
    'canili' => array(
        'name' => 'Canili',
        'csv' => dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/Canili-Export-2025-November-17-1510.csv',
    ),
    'pensioni_per_cani' => array(
        'name' => 'Pensioni per Cani',
        'csv' => dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/Pensioni-per-Cani-Export-2025-November-17-1518.csv',
    ),
    'centri_cinofili' => array(
        'name' => 'Centri Cinofili',
        'csv' => dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/Centri-Cinofili-Export-2025-November-17-1516.csv',
    ),
    'veterinari' => array(
        'name' => 'Veterinari',
        'csv' => dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/Strutture-Veterinarie-Export-2025-November-17-1522.csv',
    ),
);

foreach ( $strutture_types as $post_type => $config ) {
    echo "<div class='section'>";
    echo "<h2>" . esc_html( $config['name'] ) . "</h2>";

    // Check CSV file
    if ( ! file_exists( $config['csv'] ) ) {
        echo "<p class='error'>❌ CSV file not found: " . esc_html( $config['csv'] ) . "</p>";
        echo "</div>";
        continue;
    }

    // Count records in CSV
    $csv_count = count( file( $config['csv'] ) ) - 1; // -1 for header

    // Count posts in DB
    $db_count = wp_count_posts( $post_type )->publish;

    echo "<div class='stats'>";
    echo "<div class='stat-card'>";
    echo "<div class='stat-number'>" . number_format( $csv_count ) . "</div>";
    echo "<div class='stat-label'>Record in CSV</div>";
    echo "</div>";
    echo "<div class='stat-card' style='background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);'>";
    echo "<div class='stat-number'>" . number_format( $db_count ) . "</div>";
    echo "<div class='stat-label'>Post Importati</div>";
    echo "</div>";
    echo "<div class='stat-card' style='background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);'>";
    $percentage = $csv_count > 0 ? round( ( $db_count / $csv_count ) * 100, 1 ) : 0;
    echo "<div class='stat-number'>" . $percentage . "%</div>";
    echo "<div class='stat-label'>Completamento</div>";
    echo "</div>";
    echo "</div>";

    if ( $db_count === 0 ) {
        echo "<p class='warning'>⚠️ Nessun post importato per questa tipologia!</p>";
        echo "</div>";
        continue;
    }

    // Read CSV headers
    $handle = fopen( $config['csv'], 'r' );
    $headers = fgetcsv( $handle );
    if ( isset( $headers[0] ) ) {
        $headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
    }

    echo "<h3>📋 Campi disponibili nel CSV:</h3>";
    echo "<pre>" . implode( ', ', array_map( 'esc_html', $headers ) ) . "</pre>";

    // Get first 3 posts
    $posts = get_posts( array(
        'post_type' => $post_type,
        'posts_per_page' => 3,
        'post_status' => 'publish',
    ) );

    if ( empty( $posts ) ) {
        echo "<p class='warning'>⚠️ Nessun post trovato</p>";
        fclose( $handle );
        echo "</div>";
        continue;
    }

    echo "<h3>🔍 Verifica Primi 3 Record Importati:</h3>";

    foreach ( $posts as $post ) {
        echo "<h4>" . esc_html( $post->post_title ) . " (ID: " . $post->ID . ")</h4>";
        echo "<table>";
        echo "<tr><th style='width: 30%;'>Campo ACF</th><th>Valore</th><th>Stato</th></tr>";

        // Check common fields
        $common_fields = array(
            'indirizzo',
            'citta',
            'comune',
            'localita',
            'provincia',
            'cap',
            'regione',
            'telefono',
            'email',
            'sito_web',
            'cellulare',
        );

        foreach ( $common_fields as $field ) {
            $value = get_field( $field, $post->ID );
            $status = $value ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
            $display = $value ? esc_html( mb_substr( $value, 0, 100 ) ) . ( mb_strlen( $value ) > 100 ? '...' : '' ) : '<em>vuoto</em>';
            echo "<tr><td><code>$field</code></td><td>$display</td><td>$status</td></tr>";
        }

        // Type-specific fields
        if ( $post_type === 'veterinari' ) {
            $vet_fields = array(
                'nome_struttura',
                'tipologia',
                'direttore_sanitario',
                'pronto_soccorso',
                'reperibilita',
                'specie_trattate',
                'servizi',
                'orari',
            );

            echo "<tr><th colspan='3' style='background: #6366f1;'>Campi Specifici Veterinari</th></tr>";
            foreach ( $vet_fields as $field ) {
                $value = get_field( $field, $post->ID );
                $status = $value ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
                $display = $value ? esc_html( mb_substr( $value, 0, 100 ) ) . ( mb_strlen( $value ) > 100 ? '...' : '' ) : '<em>vuoto</em>';
                echo "<tr><td><code>$field</code></td><td>$display</td><td>$status</td></tr>";
            }
        }

        if ( $post_type === 'pensioni_per_cani' || $post_type === 'centri_cinofili' ) {
            $extra_fields = array(
                'nome_struttura',
                'referente',
                'altre_informazioni',
            );

            echo "<tr><th colspan='3' style='background: #8b5cf6;'>Campi Aggiuntivi</th></tr>";
            foreach ( $extra_fields as $field ) {
                $value = get_field( $field, $post->ID );
                $status = $value ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
                $display = $value ? esc_html( mb_substr( $value, 0, 100 ) ) . ( mb_strlen( $value ) > 100 ? '...' : '' ) : '<em>vuoto</em>';
                echo "<tr><td><code>$field</code></td><td>$display</td><td>$status</td></tr>";
            }
        }

        if ( $post_type === 'canili' ) {
            $canili_fields = array(
                'riferimento',
                'provincia_estesa',
            );

            echo "<tr><th colspan='3' style='background: #ec4899;'>Campi Specifici Canili</th></tr>";
            foreach ( $canili_fields as $field ) {
                $value = get_field( $field, $post->ID );
                $status = $value ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
                $display = $value ? esc_html( mb_substr( $value, 0, 100 ) ) . ( mb_strlen( $value ) > 100 ? '...' : '' ) : '<em>vuoto</em>';
                echo "<tr><td><code>$field</code></td><td>$display</td><td>$status</td></tr>";
            }
        }

        echo "</table>";

        // Check all post_meta
        echo "<details style='margin: 10px 0;'>";
        echo "<summary style='cursor: pointer; color: #2563eb; font-weight: 600;'>📦 Visualizza tutti i post_meta</summary>";
        echo "<pre style='margin-top: 10px;'>";
        $all_meta = get_post_meta( $post->ID );
        foreach ( $all_meta as $key => $values ) {
            if ( strpos( $key, '_' ) === 0 ) continue; // Skip ACF internal fields
            echo esc_html( $key ) . ": " . esc_html( print_r( $values, true ) ) . "\n";
        }
        echo "</pre>";
        echo "</details>";
    }

    fclose( $handle );
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>📊 Riepilogo Generale</h2>";
echo "<table>";
echo "<tr><th>Tipologia</th><th>CSV</th><th>Importati</th><th>%</th><th>Template Single</th></tr>";

foreach ( $strutture_types as $post_type => $config ) {
    $csv_count = file_exists( $config['csv'] ) ? count( file( $config['csv'] ) ) - 1 : 0;
    $db_count = wp_count_posts( $post_type )->publish;
    $percentage = $csv_count > 0 ? round( ( $db_count / $csv_count ) * 100, 1 ) : 0;

    $template_file = get_template_directory() . '/single-' . $post_type . '.php';
    $template_exists = file_exists( $template_file );
    $template_status = $template_exists ? '<span class="success">✓ Esiste</span>' : '<span class="error">✗ Mancante</span>';

    echo "<tr>";
    echo "<td><strong>" . esc_html( $config['name'] ) . "</strong></td>";
    echo "<td>" . number_format( $csv_count ) . "</td>";
    echo "<td>" . number_format( $db_count ) . "</td>";
    echo "<td>" . $percentage . "%</td>";
    echo "<td>$template_status</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

echo "<hr><p><a href='?' style='color: #3b82f6; text-decoration: none; font-weight: 600;'>🔄 Refresh</a></p>";
