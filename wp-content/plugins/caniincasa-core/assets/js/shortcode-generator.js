/**
 * Shortcode Generator Admin Script
 */
(function($) {
    'use strict';

    let selectedItems = [];
    let currentColumns = 2;
    let currentType = '';

    $(document).ready(function() {
        initSelect2();
        initEventHandlers();
        updateShortcode();
    });

    /**
     * Initialize Select2 for content search
     */
    function initSelect2() {
        $('#content-search').select2({
            placeholder: 'Cerca contenuti...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: shortcodeGeneratorData.ajaxUrl,
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        action: 'caniincasa_search_posts_for_grid',
                        nonce: shortcodeGeneratorData.nonce,
                        post_type: currentType,
                        search: params.term || ''
                    };
                },
                processResults: function(response) {
                    if (!response.success) {
                        return { results: [] };
                    }
                    return {
                        results: response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.title,
                                thumbnail: item.thumbnail
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: formatSearchResult,
            templateSelection: formatSelection
        });
    }

    /**
     * Format search result with thumbnail
     */
    function formatSearchResult(item) {
        if (item.loading) {
            return $('<span>Caricamento...</span>');
        }

        let $result = $('<span class="select2-result">');
        if (item.thumbnail) {
            $result.append('<img src="' + item.thumbnail + '" style="width:30px;height:30px;object-fit:cover;border-radius:3px;margin-right:10px;vertical-align:middle;">');
        }
        $result.append('<span>' + item.text + '</span>');
        return $result;
    }

    /**
     * Format selected item
     */
    function formatSelection(item) {
        return item.text || item.id;
    }

    /**
     * Initialize event handlers
     */
    function initEventHandlers() {
        // Content type change
        $('#content-type').on('change', function() {
            currentType = $(this).val();

            if (currentType) {
                $('#search-field, #selected-field').show();
                // Clear and reinitialize select2 with new type
                $('#content-search').val(null).trigger('change');
            } else {
                $('#search-field, #selected-field').hide();
            }

            selectedItems = [];
            renderSelectedItems();
            updateShortcode();
        });

        // Add item from search
        $('#content-search').on('select2:select', function(e) {
            const item = e.params.data;

            // Check if already selected
            if (selectedItems.find(i => i.id === item.id)) {
                return;
            }

            selectedItems.push({
                id: item.id,
                title: item.text,
                thumbnail: item.thumbnail
            });

            renderSelectedItems();
            updateShortcode();

            // Clear selection
            $(this).val(null).trigger('change');
        });

        // Grid style selection
        $('.grid-style-option').on('click', function() {
            $('.grid-style-option').removeClass('selected');
            $(this).addClass('selected');
            currentColumns = parseInt($(this).data('columns'));
            updateShortcode();
        });

        // Other options
        $('#items-limit, #show-excerpt, #show-image').on('change', function() {
            updateShortcode();
        });

        // Copy shortcode
        $('#copy-shortcode').on('click', function() {
            const shortcode = $('#shortcode-output').text();
            navigator.clipboard.writeText(shortcode).then(function() {
                $('#copy-feedback').fadeIn().delay(2000).fadeOut();
            });
        });

        // Remove selected item (delegated)
        $('#selected-items').on('click', '.remove-item', function() {
            const id = $(this).data('id');
            selectedItems = selectedItems.filter(item => item.id !== id);
            renderSelectedItems();
            updateShortcode();
        });
    }

    /**
     * Render selected items list
     */
    function renderSelectedItems() {
        const $container = $('#selected-items');
        $container.empty();

        if (selectedItems.length === 0) {
            $container.html('<p class="placeholder-text" style="color: #999; text-align: center; margin: 0;">Nessun contenuto selezionato.<br>Cerca e aggiungi contenuti sopra.</p>');
        } else {
            selectedItems.forEach(function(item) {
                const $item = $('<span class="selected-item">');
                if (item.thumbnail) {
                    $item.append('<img src="' + item.thumbnail + '" alt="">');
                }
                $item.append('<span>' + escapeHtml(item.title) + '</span>');
                $item.append('<span class="remove-item" data-id="' + item.id + '" title="Rimuovi">&times;</span>');
                $container.append($item);
            });
        }

        $('#selected-count').text('(' + selectedItems.length + ')');
    }

    /**
     * Update generated shortcode
     */
    function updateShortcode() {
        const type = $('#content-type').val() || 'post';
        const columns = currentColumns;
        const limit = $('#items-limit').val() || 6;
        const excerpt = $('#show-excerpt').is(':checked') ? 'yes' : 'no';
        const image = $('#show-image').is(':checked') ? 'yes' : 'no';

        let shortcode = '[caniincasa_grid';
        shortcode += ' type="' + type + '"';
        shortcode += ' columns="' + columns + '"';

        if (selectedItems.length > 0) {
            const ids = selectedItems.map(item => item.id).join(',');
            shortcode += ' ids="' + ids + '"';
        } else {
            shortcode += ' limit="' + limit + '"';
        }

        shortcode += ' excerpt="' + excerpt + '"';
        shortcode += ' image="' + image + '"';
        shortcode += ']';

        $('#shortcode-output').text(shortcode);

        // Update preview
        updatePreview(shortcode);
    }

    /**
     * Update shortcode preview
     */
    function updatePreview(shortcode) {
        const $preview = $('#shortcode-preview');

        if (!currentType) {
            $preview.html('<p style="text-align: center; color: #666;">Seleziona un tipo di contenuto per vedere l\'anteprima</p>');
            return;
        }

        $preview.html('<p style="text-align: center; color: #666;">Caricamento anteprima...</p>');

        $.ajax({
            url: shortcodeGeneratorData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'caniincasa_preview_grid_shortcode',
                nonce: shortcodeGeneratorData.nonce,
                shortcode: shortcode
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    $preview.html(response.data.html);
                } else {
                    $preview.html('<p style="text-align: center; color: #999;">Nessun contenuto da visualizzare</p>');
                }
            },
            error: function() {
                $preview.html('<p style="text-align: center; color: #dc3545;">Errore nel caricamento anteprima</p>');
            }
        });
    }

    /**
     * Escape HTML
     */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

})(jQuery);
