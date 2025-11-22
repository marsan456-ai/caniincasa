/**
 * Comparatore Razze Script
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    console.log('=== COMPARATORE RAZZE SCRIPT LOADED ===');
    console.log('jQuery version:', $.fn.jquery);
    console.log('caniincasaData:', typeof caniincasaData !== 'undefined' ? caniincasaData : 'NOT DEFINED');

    // Check if required data is available
    if (typeof caniincasaData === 'undefined') {
        alert('ERRORE: caniincasaData non è definito! Gli script non sono stati caricati correttamente.');
        console.error('CRITICAL: caniincasaData is not defined');
        return;
    }

    if (!caniincasaData.ajaxurl) {
        alert('ERRORE: ajaxurl non è definito in caniincasaData!');
        console.error('CRITICAL: ajaxurl is not defined');
        return;
    }

    console.log('AJAX URL:', caniincasaData.ajaxurl);

    // State
    let selectedRazze = {
        1: null,
        2: null,
        3: null
    };

    let razzeData = {};
    let currentMobileBreed = 0;

    /**
     * Sanitize URL to prevent XSS via javascript: protocol
     * @param {string} url - URL to validate
     * @returns {string} - Safe URL or '#' if invalid
     */
    function sanitizeUrl(url) {
        if (!url || typeof url !== 'string') {
            return '#';
        }
        // Trim and lowercase for protocol check
        const trimmed = url.trim().toLowerCase();
        // Block javascript:, data:, vbscript: protocols
        if (trimmed.startsWith('javascript:') ||
            trimmed.startsWith('data:') ||
            trimmed.startsWith('vbscript:')) {
            return '#';
        }
        // Only allow http://, https://, or relative URLs (starting with /)
        if (trimmed.startsWith('http://') ||
            trimmed.startsWith('https://') ||
            url.trim().startsWith('/')) {
            return url;
        }
        // Fallback: if it looks like a relative path without protocol, allow it
        if (!trimmed.includes(':')) {
            return url;
        }
        return '#';
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} str - String to escape
     * @returns {string} - Escaped string
     */
    function escapeHtml(str) {
        if (!str || typeof str !== 'string') return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Field mappings for ACF fields
    const fieldMappings = {
        // Fisici
        'taglia': 'Taglia',
        'peso': 'Peso (kg)',
        'altezza': 'Altezza (cm)',
        'aspettativa_vita': 'Aspettativa di vita (anni)',
        'tipo_pelo': 'Tipo di pelo',

        // Caratteriali (rating 1-5)
        'affettuosita': 'Affettuosità',
        'energia': 'Energia e livelli di attività',
        'socialita': 'Socialità con estranei',
        'addestrabilita': 'Addestrabilità',
        'territorialita': 'Territorialità',
        'tendenza_abbaiare': 'Tendenza ad abbaiare',

        // Cure
        'toelettatura': 'Necessità di toelettatura',
        'perdita_pelo': 'Perdita di pelo',
        'esercizio_fisico': 'Necessità di esercizio',

        // Ambiente
        'adattabilita_appartamento': 'Adattabilità all\'appartamento',
        'tolleranza_solitudine': 'Tolleranza alla solitudine',
        'tolleranza_caldo': 'Tolleranza al caldo',
        'tolleranza_freddo': 'Tolleranza al freddo',

        // Famiglia
        'compatibilita_bambini': 'Compatibilità con i bambini',
        'compatibilita_cani': 'Compatibilità con altri cani',
        'compatibilita_gatti': 'Compatibilità con i gatti',
        'adatto_principianti': 'Adatto ai principianti'
    };

    /**
     * Initialize
     */
    $(document).ready(function() {
        // Test AJAX connection on page load
        testAjaxConnection();

        initAutocomplete();
        initEvents();
    });

    /**
     * Test AJAX connection
     */
    function testAjaxConnection() {
        console.log('Testing AJAX connection...');
        $.ajax({
            url: caniincasaData.ajaxurl,
            type: 'POST',
            data: {
                action: 'test_ajax'
            },
            success: function(response) {
                console.log('AJAX Test Success:', response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Test Failed:', xhr, status, error);
            }
        });
    }

    /**
     * Initialize autocomplete for all search inputs
     */
    function initAutocomplete() {
        $('.razza-search').each(function() {
            const $input = $(this);
            const slot = $input.closest('.selector-slot').data('slot');
            let currentFocus = -1;

            // Input event
            $input.on('input', function() {
                const query = $(this).val().trim();

                // Remove existing suggestions
                $input.siblings('.autocomplete-suggestions').remove();

                if (query.length < 2) {
                    return;
                }

                // Fetch suggestions
                fetchSuggestions(query, slot);
            });

            // Keyboard navigation
            $input.on('keydown', function(e) {
                const $suggestions = $(this).siblings('.autocomplete-suggestions');
                const $items = $suggestions.find('.autocomplete-suggestion');

                if ($items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    if (currentFocus >= $items.length) currentFocus = 0;
                    addActive($items, currentFocus);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    if (currentFocus < 0) currentFocus = $items.length - 1;
                    addActive($items, currentFocus);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentFocus > -1 && $items.length > 0) {
                        $items.eq(currentFocus).click();
                    }
                } else if (e.key === 'Escape') {
                    $suggestions.remove();
                    currentFocus = -1;
                }
            });

            // Close suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.selector-slot').length) {
                    $('.autocomplete-suggestions').remove();
                }
            });
        });
    }

    /**
     * Fetch autocomplete suggestions
     */
    function fetchSuggestions(query, slot) {
        $.ajax({
            url: caniincasaData.ajaxurl,
            type: 'POST',
            data: {
                action: 'search_razze',
                query: query,
                nonce: caniincasaData.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    displaySuggestions(response.data, slot);
                } else {
                    displayNoResults(slot);
                }
            },
            error: function() {
                displayNoResults(slot);
            }
        });
    }

    /**
     * Display autocomplete suggestions
     */
    function displaySuggestions(razze, slot) {
        const $slot = $(`.selector-slot[data-slot="${slot}"]`);
        const $input = $slot.find('.razza-search');

        // Remove existing suggestions
        $slot.find('.autocomplete-suggestions').remove();

        // Create suggestions container
        const $suggestions = $('<div class="autocomplete-suggestions"></div>');

        // Add suggestions
        razze.forEach(function(razza) {
            // Skip if already selected
            if (Object.values(selectedRazze).some(r => r && r.id === razza.id)) {
                return;
            }

            const $suggestion = $(`
                <div class="autocomplete-suggestion" data-id="${razza.id}" data-name="${razza.name}">
                    ${razza.image ? `<img src="${razza.image}" alt="${razza.name}">` : ''}
                    <div class="suggestion-info">
                        <span class="suggestion-name">${razza.name}</span>
                        ${razza.taglia ? `<span class="suggestion-meta">Taglia: ${razza.taglia}</span>` : ''}
                    </div>
                </div>
            `);

            $suggestion.on('click', function() {
                selectRazza(slot, {
                    id: razza.id,
                    name: razza.name,
                    image: razza.image
                });
                $suggestions.remove();
            });

            $suggestions.append($suggestion);
        });

        $slot.append($suggestions);
    }

    /**
     * Display no results message
     */
    function displayNoResults(slot) {
        const $slot = $(`.selector-slot[data-slot="${slot}"]`);
        $slot.find('.autocomplete-suggestions').remove();

        const $noResults = $(`
            <div class="autocomplete-suggestions">
                <div class="autocomplete-no-results">Nessuna razza trovata</div>
            </div>
        `);

        $slot.append($noResults);

        setTimeout(() => $noResults.remove(), 2000);
    }

    /**
     * Add active class to suggestion
     */
    function addActive($items, index) {
        $items.removeClass('active');
        if (index >= 0 && index < $items.length) {
            $items.eq(index).addClass('active');
        }
    }

    /**
     * Select a razza for comparison
     */
    function selectRazza(slot, razza) {
        console.log('selectRazza called - Slot:', slot, 'Razza:', razza);
        selectedRazze[slot] = razza;
        console.log('Updated selectedRazze:', selectedRazze);

        const $slot = $(`.selector-slot[data-slot="${slot}"]`);
        const $input = $slot.find('.razza-search');
        const $hiddenInput = $slot.find('.razza-id');
        const $clearBtn = $slot.find('.clear-selection');

        $input.val(razza.name).addClass('has-value');
        $hiddenInput.val(razza.id);
        $clearBtn.show();

        console.log('Input value set to:', razza.name);
        console.log('Hidden input value set to:', razza.id);

        updateCompareButton();
    }

    /**
     * Initialize events
     */
    function initEvents() {
        // Clear selection
        $('.clear-selection').on('click', function() {
            const slot = $(this).data('slot');
            clearSelection(slot);
        });

        // Compare button
        $('#compare-btn').on('click', function() {
            compareRazze();
        });

        // Reset button
        $('#reset-btn').on('click', function() {
            resetComparison();
        });

        // New comparison button
        $('#new-comparison').on('click', function() {
            resetComparison();
            $('html, body').animate({
                scrollTop: $('.razze-selector').offset().top - 100
            }, 500);
        });

        // Share button
        $('#share-comparison').on('click', function() {
            shareComparison();
        });

        // Mobile navigation
        $('#prev-breed').on('click', function() {
            navigateMobileBreed(-1);
        });

        $('#next-breed').on('click', function() {
            navigateMobileBreed(1);
        });
    }

    /**
     * Clear a selection
     */
    function clearSelection(slot) {
        selectedRazze[slot] = null;

        const $slot = $(`.selector-slot[data-slot="${slot}"]`);
        $slot.find('.razza-search').val('').removeClass('has-value');
        $slot.find('.razza-id').val('');
        $slot.find('.clear-selection').hide();

        updateCompareButton();
    }

    /**
     * Update compare button state
     */
    function updateCompareButton() {
        const selectedCount = Object.values(selectedRazze).filter(r => r !== null).length;
        $('#compare-btn').prop('disabled', selectedCount < 2);
    }

    /**
     * Compare razze
     */
    function compareRazze() {
        console.log('=== compareRazze() CALLED ===');

        const razzeIds = Object.values(selectedRazze)
            .filter(r => r !== null)
            .map(r => r.id);

        console.log('Selected razze before filter:', selectedRazze);
        console.log('Razze IDs after filter:', razzeIds);

        if (razzeIds.length < 2) {
            alert('Seleziona almeno 2 razze da confrontare');
            return;
        }

        console.log('Comparing razze:', razzeIds);
        console.log('AJAX URL:', caniincasaData.ajaxurl);
        console.log('AJAX Data:', {
            action: 'get_razze_comparison',
            razze_ids: razzeIds,
            nonce: caniincasaData.nonce
        });

        // Show loading - DON'T destroy the table structure!
        const $table = $('#comparison-table');
        $table.show();

        // Add loading overlay instead of replacing content
        if ($table.find('.loading-overlay').length === 0) {
            $table.prepend('<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.95);display:flex;align-items:center;justify-content:center;z-index:100;"><div style="text-align:center;"><div style="font-size:18px;margin-bottom:10px;">Caricamento confronto...</div><div style="font-size:14px;color:#666;">Attendere prego</div></div></div>');
        } else {
            $table.find('.loading-overlay').show();
        }

        console.log('Starting AJAX request...');
        const startTime = Date.now();

        // Fetch razze data
        $.ajax({
            url: caniincasaData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_razze_comparison',
                razze_ids: razzeIds,
                nonce: caniincasaData.nonce
            },
            timeout: 30000, // 30 second timeout
            beforeSend: function(xhr) {
                console.log('AJAX beforeSend - Request is being sent');
            },
            success: function(response) {
                const duration = Date.now() - startTime;
                console.log('AJAX Response received in ' + duration + 'ms');
                console.log('AJAX Response:', response);
                console.log('Response type:', typeof response);
                console.log('Response.success:', response.success);

                if (response.success) {
                    razzeData = response.data;
                    console.log('Razze Data:', razzeData);
                    console.log('Calling displayComparison()...');
                    displayComparison();
                } else {
                    console.error('AJAX Error:', response);
                    $('.loading-overlay').remove();
                    alert('Errore nel caricamento dei dati: ' + (response.data || 'Unknown error'));
                    $('#comparison-table').hide();
                }
            },
            error: function(xhr, status, error) {
                const duration = Date.now() - startTime;
                console.error('=== AJAX ERROR ===');
                console.error('Duration:', duration + 'ms');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('XHR:', xhr);
                console.error('XHR Status:', xhr.status);
                console.error('XHR Response Text:', xhr.responseText);

                // Remove loading overlay
                $('.loading-overlay').remove();

                let errorMsg = 'Errore di connessione: ' + error;
                if (status === 'timeout') {
                    errorMsg = 'Timeout: la richiesta ha impiegato troppo tempo (>30s)';
                } else if (xhr.status === 0) {
                    errorMsg = 'Nessuna connessione: verifica che il server sia raggiungibile';
                } else if (xhr.status === 404) {
                    errorMsg = 'Endpoint non trovato (404): ' + caniincasaData.ajaxurl;
                } else if (xhr.status === 500) {
                    errorMsg = 'Errore del server (500): controlla i log PHP';
                }

                alert(errorMsg);
                $('#comparison-table').hide();
            },
            complete: function() {
                console.log('AJAX request complete');
            }
        });
    }

    /**
     * Display comparison table
     */
    function displayComparison() {
        console.log('=== displayComparison() CALLED ===');
        console.log('razzeData:', razzeData);

        try {
            // Build header
            let headerHTML = '';
            const razzeArray = Object.values(razzeData);
            console.log('razzeArray:', razzeArray);
            console.log('Number of razze:', razzeArray.length);

            // Check if comparison-breeds-header exists
            const $header = $('#comparison-breeds-header');
            console.log('Header element found:', $header.length > 0);
            if ($header.length === 0) {
                console.error('ERROR: #comparison-breeds-header element not found!');
                alert('Errore: elemento #comparison-breeds-header non trovato nel DOM');
                return;
            }

            razzeArray.forEach(function(razza, index) {
                console.log(`Building header for razza ${index}:`, razza.name);
                // Sanitize URL and escape HTML to prevent XSS
                const safeUrl = sanitizeUrl(razza.url);
                const safeName = escapeHtml(razza.name);
                const safeImage = razza.image ? sanitizeUrl(razza.image) : '';
                headerHTML += `
                    <div class="breed-header">
                        ${safeImage ? `<img src="${safeImage}" alt="${safeName}" class="breed-image">` : ''}
                        <h3 class="breed-name">${safeName}</h3>
                        <a href="${safeUrl}" class="breed-link">Vedi scheda completa →</a>
                    </div>
                `;
            });

            console.log('Setting header HTML...');
            $header.html(headerHTML);
            console.log('Header HTML set successfully');

            // Build rows
            const $rows = $('.comparison-row');
            console.log('Comparison rows found:', $rows.length);

            if ($rows.length === 0) {
                console.error('ERROR: No .comparison-row elements found!');
                alert('Errore: nessun elemento .comparison-row trovato nel DOM');
                return;
            }

            $rows.each(function(index) {
                const $row = $(this);
                const field = $row.data('field');
                console.log(`Processing row ${index}, field: ${field}`);

                if (!field) {
                    console.warn(`Row ${index} has no data-field attribute`);
                    return;
                }

                let valuesHTML = '';

                razzeArray.forEach(function(razza) {
                    const value = razza.fields[field];
                    console.log(`  - Razza ${razza.name}, field ${field}:`, value);
                    try {
                        const formattedValue = formatValue(field, value);
                        valuesHTML += `<div class="value-cell">${formattedValue}</div>`;
                    } catch (e) {
                        console.error(`Error formatting value for ${field}:`, e);
                        valuesHTML += `<div class="value-cell">Errore</div>`;
                    }
                });

                $row.find('.row-values').html(valuesHTML);
            });

            console.log('All rows processed successfully');

            // Update mobile navigation
            $('#total-breeds').text(razzeArray.length);
            currentMobileBreed = 0;
            updateMobileView();

            console.log('Mobile view updated');

            // Remove loading overlay
            $('.loading-overlay').fadeOut(300, function() {
                $(this).remove();
            });

            // Scroll to comparison
            const $table = $('#comparison-table');
            console.log('Comparison table element found:', $table.length > 0);

            if ($table.length > 0) {
                console.log('Scrolling to comparison table...');
                $('html, body').animate({
                    scrollTop: $table.offset().top - 100
                }, 500);
            } else {
                console.error('ERROR: #comparison-table element not found!');
            }

            console.log('=== displayComparison() COMPLETED ===');
        } catch (error) {
            console.error('=== CRITICAL ERROR in displayComparison() ===');
            console.error('Error:', error);
            console.error('Stack:', error.stack);
            $('.loading-overlay').remove();
            alert('Errore critico nella visualizzazione: ' + error.message);
        }
    }

    /**
     * Format field value for display
     */
    function formatValue(field, value) {
        if (!value && value !== 0) {
            return '<span class="value-text" style="color:#9ca3af;">Non disponibile</span>';
        }

        // Rating fields (1-5)
        const ratingFields = ['affettuosita', 'energia', 'socialita', 'addestrabilita',
                              'territorialita', 'tendenza_abbaiare', 'toelettatura',
                              'perdita_pelo', 'esercizio_fisico', 'adattabilita_appartamento',
                              'tolleranza_solitudine', 'tolleranza_caldo', 'tolleranza_freddo',
                              'compatibilita_bambini', 'compatibilita_cani', 'compatibilita_gatti',
                              'adatto_principianti'];

        if (ratingFields.includes(field)) {
            return formatRating(parseInt(value));
        }

        // Text fields
        return `<span class="value-text">${value}</span>`;
    }

    /**
     * Format rating (stars)
     */
    function formatRating(rating) {
        let html = '<div class="value-rating">';

        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                html += '<svg class="rating-star filled" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            } else {
                html += '<svg class="rating-star empty" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            }
        }

        html += '</div>';
        return html;
    }

    /**
     * Navigate mobile breed view
     */
    function navigateMobileBreed(direction) {
        const totalBreeds = Object.keys(razzeData).length;
        currentMobileBreed += direction;

        if (currentMobileBreed < 0) currentMobileBreed = 0;
        if (currentMobileBreed >= totalBreeds) currentMobileBreed = totalBreeds - 1;

        updateMobileView();
    }

    /**
     * Update mobile view
     */
    function updateMobileView() {
        $('#current-breed').text(currentMobileBreed + 1);

        // Hide/show breeds
        $('.breed-header, .value-cell').removeClass('hidden-mobile');
        $('.breed-header').eq(currentMobileBreed).removeClass('hidden-mobile');

        $('.value-cell').each(function(index) {
            const totalBreeds = Object.keys(razzeData).length;
            const breedIndex = index % totalBreeds;
            if (breedIndex !== currentMobileBreed) {
                $(this).addClass('hidden-mobile');
            }
        });

        // Update navigation buttons
        $('#prev-breed').prop('disabled', currentMobileBreed === 0);
        $('#next-breed').prop('disabled', currentMobileBreed === Object.keys(razzeData).length - 1);
    }

    /**
     * Share comparison
     */
    function shareComparison() {
        const razzeNames = Object.values(razzeData).map(r => r.name).join(', ');
        const text = `Confronto razze: ${razzeNames}`;
        const url = window.location.href;

        if (navigator.share) {
            navigator.share({
                title: text,
                url: url
            }).catch(() => {});
        } else {
            // Fallback: copy to clipboard
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(url).select();
            document.execCommand('copy');
            tempInput.remove();
            alert('Link copiato negli appunti!');
        }
    }

    /**
     * Reset comparison
     */
    function resetComparison() {
        for (let i = 1; i <= 3; i++) {
            clearSelection(i);
        }
        $('#comparison-table').hide();
        razzeData = {};
    }

})(jQuery);
