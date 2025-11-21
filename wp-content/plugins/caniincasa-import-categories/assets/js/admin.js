/**
 * CaniInCasa Import Categories - Admin JavaScript
 */

(function($) {
    'use strict';

    var selectedFile = null;

    $(document).ready(function() {
        initUploadBox();
        initBrowseLink();
        initFileInput();
        initImportButton();
        initNewImportButton();
    });

    /**
     * Initialize upload box drag & drop
     */
    function initUploadBox() {
        var $uploadBox = $('#upload-box');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            $uploadBox.on(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Highlight on drag
        ['dragenter', 'dragover'].forEach(function(eventName) {
            $uploadBox.on(eventName, function() {
                $(this).addClass('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            $uploadBox.on(eventName, function() {
                $(this).removeClass('dragover');
            });
        });

        // Handle dropped files
        $uploadBox.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        // Click to browse
        $uploadBox.on('click', function() {
            $('#csv-file').click();
        });
    }

    /**
     * Initialize browse link
     */
    function initBrowseLink() {
        $('#browse-link').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#csv-file').click();
        });
    }

    /**
     * Initialize file input
     */
    function initFileInput() {
        $('#csv-file').on('change', function() {
            handleFiles(this.files);
        });
    }

    /**
     * Handle selected files
     */
    function handleFiles(files) {
        if (files.length === 0) return;

        var file = files[0];

        // Validate file type
        if (!file.name.toLowerCase().endsWith('.csv')) {
            alert('Per favore seleziona un file CSV.');
            return;
        }

        selectedFile = file;

        // Update UI
        var $uploadBox = $('#upload-box');
        $uploadBox.addClass('has-file');

        var fileSize = formatFileSize(file.size);
        $('#file-info').text(file.name + ' (' + fileSize + ')');

        // Enable import button
        $('#start-import').prop('disabled', false);
    }

    /**
     * Format file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Initialize import button
     */
    function initImportButton() {
        $('#start-import').on('click', function() {
            if (!selectedFile) {
                alert('Per favore seleziona un file CSV.');
                return;
            }

            startImport();
        });
    }

    /**
     * Start import process
     */
    function startImport() {
        var dryRun = $('#dry-run').is(':checked');

        // Show progress section
        $('#upload-section').hide();
        $('#results-section').hide();
        $('#progress-section').show();

        // Update progress bar
        updateProgress(10, 'Caricamento file...');

        // Create FormData
        var formData = new FormData();
        formData.append('action', 'caniincasa_import_csv');
        formData.append('nonce', caniincasaImport.nonce);
        formData.append('csv_file', selectedFile);
        formData.append('dry_run', dryRun ? 'true' : 'false');

        // Simulate progress
        var progressInterval = simulateProgress();

        // Debug info
        console.log('=== Import Debug ===');
        console.log('AJAX URL:', caniincasaImport.ajaxurl);
        console.log('Nonce:', caniincasaImport.nonce);
        console.log('File:', selectedFile);
        console.log('Dry Run:', dryRun);

        // Send AJAX request
        $.ajax({
            url: caniincasaImport.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minutes timeout
            success: function(response) {
                clearInterval(progressInterval);
                updateProgress(100, 'Completato!');

                setTimeout(function() {
                    if (response.success) {
                        showResults(response.data);
                    } else {
                        showError(response.data.message || 'Errore durante l\'importazione.');
                    }
                }, 500);
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                var errorMsg = 'Errore di connessione';

                if (status === 'timeout') {
                    errorMsg = 'Timeout: l\'operazione ha impiegato troppo tempo. Prova con un file CSV più piccolo.';
                } else if (xhr.status === 0) {
                    errorMsg = 'Impossibile connettersi al server. Verifica la connessione internet.';
                } else if (xhr.status === 413) {
                    errorMsg = 'File troppo grande. Aumenta upload_max_filesize in php.ini.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Errore interno del server (500). Controlla i log PHP per dettagli.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                } else if (error) {
                    errorMsg = 'Errore: ' + error + ' (Status: ' + xhr.status + ')';
                }

                console.error('=== AJAX Error Details ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('XHR Status:', xhr.status);
                console.error('XHR StatusText:', xhr.statusText);
                console.error('XHR Response:', xhr.responseText);
                console.error('XHR ResponseJSON:', xhr.responseJSON);
                showError(errorMsg);
            }
        });
    }

    /**
     * Simulate progress bar
     */
    function simulateProgress() {
        var progress = 10;
        return setInterval(function() {
            if (progress < 90) {
                progress += Math.random() * 10;
                if (progress > 90) progress = 90;
                updateProgress(progress, 'Elaborazione in corso...');
            }
        }, 500);
    }

    /**
     * Update progress bar
     */
    function updateProgress(percent, text) {
        $('#progress-bar').css('width', percent + '%');
        $('#progress-text').text(text);
    }

    /**
     * Show results
     */
    function showResults(data) {
        var stats = data.stats;
        var logs = data.logs;
        var dryRun = data.dry_run;

        // Hide progress, show results
        $('#progress-section').hide();
        $('#results-section').show();

        // Update header
        var $header = $('.results-header');
        if (dryRun) {
            $header.find('h3').text('Simulazione Completata (Dry Run)');
            $header.find('.dashicons').removeClass('dashicons-yes-alt').addClass('dashicons-info-outline');
            $header.find('.dashicons').css('color', '#2271b1');
        } else {
            $header.find('h3').text('Importazione Completata');
            $header.find('.dashicons').removeClass('dashicons-info-outline').addClass('dashicons-yes-alt');
            $header.find('.dashicons').css('color', '#00a32a');
        }

        // Build stats grid
        var statsHtml = '';
        statsHtml += '<div class="stat-card info"><div class="stat-value">' + stats.total + '</div><div class="stat-label">Totale Righe</div></div>';
        statsHtml += '<div class="stat-card success"><div class="stat-value">' + stats.processed + '</div><div class="stat-label">Processati</div></div>';
        statsHtml += '<div class="stat-card warning"><div class="stat-value">' + stats.skipped + '</div><div class="stat-label">Saltati</div></div>';
        statsHtml += '<div class="stat-card error"><div class="stat-value">' + stats.errors + '</div><div class="stat-label">Errori</div></div>';
        statsHtml += '<div class="stat-card info"><div class="stat-value">' + stats.categories_created + '</div><div class="stat-label">Categorie Create</div></div>';
        statsHtml += '<div class="stat-card info"><div class="stat-value">' + stats.subcategories_created + '</div><div class="stat-label">Sottocategorie</div></div>';

        $('#stats-grid').html(statsHtml);

        // Build log
        var logHtml = '';
        logs.forEach(function(entry) {
            logHtml += '<div class="log-entry ' + entry.type + '">' + escapeHtml(entry.message) + '</div>';
        });

        if (logHtml === '') {
            logHtml = '<div class="log-entry info">Nessun log disponibile.</div>';
        }

        $('#log-box').html(logHtml);

        // Scroll log to bottom
        var $logBox = $('#log-box');
        $logBox.scrollTop($logBox[0].scrollHeight);
    }

    /**
     * Show error
     */
    function showError(message) {
        $('#progress-section').hide();
        $('#results-section').show();

        var $header = $('.results-header');
        $header.addClass('error');
        $header.find('h3').text('Errore');
        $header.find('.dashicons').removeClass('dashicons-yes-alt').addClass('dashicons-warning');

        $('#stats-grid').html('<div class="stat-card error" style="grid-column: 1 / -1;"><div class="stat-value" style="font-size: 18px;">' + escapeHtml(message) + '</div></div>');
        $('#log-box').html('');
    }

    /**
     * Initialize new import button
     */
    function initNewImportButton() {
        $('#new-import').on('click', function() {
            resetForm();
        });
    }

    /**
     * Reset form
     */
    function resetForm() {
        selectedFile = null;

        // Reset file input
        $('#csv-file').val('');
        $('#file-info').text('');
        $('#upload-box').removeClass('has-file');
        $('#start-import').prop('disabled', true);

        // Reset progress
        $('#progress-bar').css('width', '0%');
        $('#progress-text').text('');

        // Reset results header
        var $header = $('.results-header');
        $header.removeClass('error');
        $header.find('h3').text('Importazione Completata');
        $header.find('.dashicons').removeClass('dashicons-warning dashicons-info-outline').addClass('dashicons-yes-alt');
        $header.find('.dashicons').css('color', '#00a32a');

        // Show upload section
        $('#results-section').hide();
        $('#progress-section').hide();
        $('#upload-section').show();
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})(jQuery);
