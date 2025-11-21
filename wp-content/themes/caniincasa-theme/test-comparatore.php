<?php
/**
 * Template Name: Test Comparatore
 *
 * File di test per verificare funzionamento AJAX comparatore
 */

get_header();
?>

<style>
.test-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #f5f5f5;
    border-radius: 8px;
}
.test-section {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 4px;
    border-left: 4px solid #3b82f6;
}
.test-result {
    background: #e8f4f8;
    padding: 15px;
    margin: 10px 0;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.test-result.success {
    background: #d4edda;
    border-left: 4px solid #28a745;
}
.test-result.error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}
button {
    background: #3b82f6;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    margin: 5px;
}
button:hover {
    background: #2563eb;
}
</style>

<div class="test-container">
    <h1>Test Comparatore Razze - Diagnostica AJAX</h1>

    <div class="test-section">
        <h2>1. Verifica Configurazione</h2>
        <div id="config-check" class="test-result">
            <strong>AJAX URL:</strong> <?php echo admin_url('admin-ajax.php'); ?><br>
            <strong>Theme Directory:</strong> <?php echo get_template_directory_uri(); ?><br>
            <strong>jQuery disponibile:</strong> <span id="jquery-check">Checking...</span><br>
            <strong>caniincasaData disponibile:</strong> <span id="data-check">Checking...</span>
        </div>
    </div>

    <div class="test-section">
        <h2>2. Test AJAX Semplice</h2>
        <button id="test-simple">Test Endpoint Semplice</button>
        <div id="simple-result" class="test-result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>3. Test Ricerca Razze</h2>
        <input type="text" id="search-input" placeholder="Digita nome razza..." style="width:100%;padding:10px;margin:10px 0;">
        <button id="test-search">Cerca Razze</button>
        <div id="search-result" class="test-result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>4. Test Confronto Razze</h2>
        <p>Inserisci gli ID delle razze da confrontare (separati da virgola):</p>
        <input type="text" id="compare-ids" placeholder="es: 123,456" style="width:100%;padding:10px;margin:10px 0;">
        <button id="test-compare">Confronta Razze</button>
        <div id="compare-result" class="test-result" style="display:none;"></div>
    </div>

    <div class="test-section">
        <h2>5. Lista Razze Disponibili</h2>
        <button id="get-razze-list">Carica Lista Razze</button>
        <div id="razze-list" class="test-result" style="display:none;"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('=== TEST COMPARATORE LOADED ===');

    // Check jQuery
    $('#jquery-check').text(typeof $ !== 'undefined' ? '✓ Disponibile (v' + $.fn.jquery + ')' : '✗ Non disponibile');

    // Check caniincasaData
    if (typeof caniincasaData !== 'undefined') {
        $('#data-check').html('✓ Disponibile<br>' + JSON.stringify(caniincasaData, null, 2));
    } else {
        $('#data-check').text('✗ Non disponibile');
    }

    // Test 1: Simple AJAX
    $('#test-simple').on('click', function() {
        console.log('Testing simple AJAX...');
        $('#simple-result').show().removeClass('success error').html('Testing...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'test_ajax'
            },
            success: function(response) {
                console.log('Simple test success:', response);
                $('#simple-result').addClass('success').html('<strong>SUCCESS</strong>\n' + JSON.stringify(response, null, 2));
            },
            error: function(xhr, status, error) {
                console.error('Simple test error:', xhr, status, error);
                $('#simple-result').addClass('error').html('<strong>ERROR</strong>\nStatus: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText);
            }
        });
    });

    // Test 2: Search razze
    $('#test-search').on('click', function() {
        const query = $('#search-input').val();
        console.log('Testing search with query:', query);
        $('#search-result').show().removeClass('success error').html('Searching...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'search_razze',
                term: query
            },
            success: function(response) {
                console.log('Search success:', response);
                $('#search-result').addClass('success').html('<strong>SUCCESS - Found ' + response.length + ' results</strong>\n' + JSON.stringify(response, null, 2));
            },
            error: function(xhr, status, error) {
                console.error('Search error:', xhr, status, error);
                $('#search-result').addClass('error').html('<strong>ERROR</strong>\nStatus: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText);
            }
        });
    });

    // Test 3: Compare razze
    $('#test-compare').on('click', function() {
        const ids = $('#compare-ids').val().split(',').map(id => parseInt(id.trim()));
        console.log('Testing comparison with IDs:', ids);
        $('#compare-result').show().removeClass('success error').html('Comparing...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'get_razze_comparison',
                razze_ids: ids
            },
            timeout: 30000,
            success: function(response) {
                console.log('Compare success:', response);
                $('#compare-result').addClass('success').html('<strong>SUCCESS</strong>\n' + JSON.stringify(response, null, 2));
            },
            error: function(xhr, status, error) {
                console.error('Compare error:', xhr, status, error);
                $('#compare-result').addClass('error').html('<strong>ERROR</strong>\nStatus: ' + status + '\nError: ' + error + '\nXHR Status: ' + xhr.status + '\nResponse: ' + xhr.responseText);
            }
        });
    });

    // Test 4: Get razze list
    $('#get-razze-list').on('click', function() {
        console.log('Getting razze list...');
        $('#razze-list').show().removeClass('success error').html('Loading...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'search_razze',
                term: ''
            },
            success: function(response) {
                console.log('Razze list success:', response);
                let html = '<strong>SUCCESS - Total razze: ' + response.length + '</strong>\n\n';
                response.forEach(function(razza) {
                    html += 'ID: ' + razza.id + ' - ' + razza.name + '\n';
                });
                $('#razze-list').addClass('success').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Razze list error:', xhr, status, error);
                $('#razze-list').addClass('error').html('<strong>ERROR</strong>\nStatus: ' + status + '\nError: ' + error);
            }
        });
    });
});
</script>

<?php
get_footer();
?>
