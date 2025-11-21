/**
 * Comparatore Razze Script
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    // State
    let selectedRazze = {
        1: null,
        2: null,
        3: null
    };

    let razzeData = {};
    let currentMobileBreed = 0;

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
        selectedRazze[slot] = razza;

        const $slot = $(`.selector-slot[data-slot="${slot}"]`);
        const $input = $slot.find('.razza-search');
        const $hiddenInput = $slot.find('.razza-id');
        const $clearBtn = $slot.find('.clear-selection');

        $input.val(razza.name).addClass('has-value');
        $hiddenInput.val(razza.id);
        $clearBtn.show();

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
        const razzeIds = Object.values(selectedRazze)
            .filter(r => r !== null)
            .map(r => r.id);

        if (razzeIds.length < 2) {
            alert('Seleziona almeno 2 razze da confrontare');
            return;
        }

        console.log('Comparing razze:', razzeIds);

        // Show loading
        $('#comparison-table').html('<div style="text-align:center;padding:60px;"><p>Caricamento confronto...</p></div>').show();

        // Fetch razze data
        $.ajax({
            url: caniincasaData.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_razze_comparison',
                razze_ids: razzeIds,
                nonce: caniincasaData.nonce
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    razzeData = response.data;
                    console.log('Razze Data:', razzeData);
                    displayComparison();
                } else {
                    console.error('AJAX Error:', response);
                    alert('Errore nel caricamento dei dati: ' + (response.data || 'Unknown error'));
                    $('#comparison-table').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Connection Error:', xhr, status, error);
                alert('Errore di connessione: ' + error);
                $('#comparison-table').hide();
            }
        });
    }

    /**
     * Display comparison table
     */
    function displayComparison() {
        // Build header
        let headerHTML = '';
        const razzeArray = Object.values(razzeData);

        razzeArray.forEach(function(razza) {
            headerHTML += `
                <div class="breed-header">
                    ${razza.image ? `<img src="${razza.image}" alt="${razza.name}" class="breed-image">` : ''}
                    <h3 class="breed-name">${razza.name}</h3>
                    <a href="${razza.url}" class="breed-link">Vedi scheda completa →</a>
                </div>
            `;
        });

        $('#comparison-breeds-header').html(headerHTML);

        // Build rows
        $('.comparison-row').each(function() {
            const $row = $(this);
            const field = $row.data('field');
            let valuesHTML = '';

            razzeArray.forEach(function(razza) {
                const value = razza.fields[field];
                valuesHTML += `<div class="value-cell">${formatValue(field, value)}</div>`;
            });

            $row.find('.row-values').html(valuesHTML);
        });

        // Update mobile navigation
        $('#total-breeds').text(razzeArray.length);
        currentMobileBreed = 0;
        updateMobileView();

        // Scroll to comparison
        $('html, body').animate({
            scrollTop: $('#comparison-table').offset().top - 100
        }, 500);
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
