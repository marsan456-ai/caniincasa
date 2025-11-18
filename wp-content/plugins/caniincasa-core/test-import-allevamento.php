<?php
/**
 * Test Import Single Allevamento
 *
 * Quick test to verify allevamento import with all fields
 * URL: http://cani-in-casa.local/wp-content/plugins/caniincasa-core/test-import-allevamento.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Security check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Accesso negato' );
}

// Load CSV Importer
require_once __DIR__ . '/includes/csv-importer.php';

echo "<h1>Test Import Allevamento</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    table { border-collapse: collapse; margin: 20px 0; }
    table td, table th { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background-color: #4CAF50; color: white; }
</style>";

$csv_file = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/Allevamenti-Export-2025-November-17-1454.csv';

if ( ! file_exists( $csv_file ) ) {
    echo "<p class='error'>❌ CSV file not found: $csv_file</p>";
    exit;
}

echo "<p class='success'>✅ CSV file found</p>";

// Read first data row
$handle = fopen( $csv_file, 'r' );
$headers = fgetcsv( $handle );

// Remove BOM if present
if ( isset( $headers[0] ) ) {
    $headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
}

echo "<h2>CSV Headers:</h2>";
echo "<pre>" . print_r( $headers, true ) . "</pre>";

// Get first allevamento
$row = fgetcsv( $handle );
$data = array_combine( $headers, $row );

echo "<h2>First Row Data:</h2>";
echo "<table>";
foreach ( $data as $key => $value ) {
    $display_value = strlen( $value ) > 100 ? substr( $value, 0, 100 ) . '...' : $value;
    echo "<tr><th>" . esc_html( $key ) . "</th><td>" . esc_html( $display_value ) . "</td></tr>";
}
echo "</table>";

fclose( $handle );

// Test import
$importer = new Caniincasa_CSV_Importer();

echo "<h2>Importing First Allevamento...</h2>";

$result = $importer->import_single_allevamento( $data );

if ( $result['success'] ) {
    $post_id = $result['post_id'];
    echo "<p class='success'>✅ Import successful! Post ID: $post_id</p>";
    echo "<p><a href='" . get_permalink( $post_id ) . "' target='_blank'>View Allevamento</a></p>";

    // Display imported ACF fields
    echo "<h2>Imported ACF Fields:</h2>";
    echo "<table>";

    $acf_fields = array(
        'persona', 'indirizzo', 'localita', 'provincia', 'cap',
        'telefono', 'email', 'sito_web', 'affisso', 'proprietario', 'id_affisso'
    );

    foreach ( $acf_fields as $field ) {
        $value = get_field( $field, $post_id );
        $display = $value ? esc_html( $value ) : '<span class="error">EMPTY</span>';
        echo "<tr><th>$field</th><td>$display</td></tr>";
    }
    echo "</table>";

    // Check razze allevate
    echo "<h2>Razze Allevate:</h2>";
    $razze_allevate = get_field( 'razze_allevate', $post_id );

    if ( $razze_allevate && is_array( $razze_allevate ) ) {
        echo "<p class='success'>✅ Found " . count( $razze_allevate ) . " razze</p>";
        echo "<ul>";
        foreach ( $razze_allevate as $razza_post ) {
            echo "<li>" . esc_html( $razza_post->post_title ) . " (ID: " . $razza_post->ID . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ No razze allevate found</p>";
        echo "<p class='info'>Expected from CSV: " . esc_html( $data['Razze Allevamenti'] ) . "</p>";

        // Check if razze exist in database
        if ( ! empty( $data['Razze Allevamenti'] ) ) {
            $razze_names = explode( '|', $data['Razze Allevamenti'] );
            echo "<h3>Checking Razze in Database:</h3>";
            echo "<ul>";
            foreach ( $razze_names as $razza_name ) {
                $razza_name = trim( $razza_name );
                $razza_post = get_page_by_title( $razza_name, OBJECT, 'razze_di_cani' );

                if ( $razza_post ) {
                    echo "<li class='success'>✅ Found: " . esc_html( $razza_name ) . " (ID: " . $razza_post->ID . ")</li>";
                } else {
                    echo "<li class='error'>❌ NOT Found: " . esc_html( $razza_name ) . "</li>";

                    // Try search
                    $args = array(
                        'post_type' => 'razze_di_cani',
                        's' => $razza_name,
                        'posts_per_page' => 5,
                    );
                    $query = new WP_Query( $args );
                    if ( $query->have_posts() ) {
                        echo "<ul><li class='info'>Similar razze found:</li>";
                        while ( $query->have_posts() ) {
                            $query->the_post();
                            echo "<li>" . get_the_title() . "</li>";
                        }
                        echo "</ul>";
                    }
                    wp_reset_postdata();
                }
            }
            echo "</ul>";
        }
    }

} else {
    echo "<p class='error'>❌ Import failed: " . esc_html( $result['message'] ) . "</p>";
}

echo "<hr><p><a href='?'>Refresh</a></p>";
