/**
 * Paw Stars - Admin JavaScript
 *
 * @package Pawstars
 * @since 1.0.0
 */

(function($) {
    'use strict';

    window.PawStarsAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Moderation actions
            $(document).on('click', '.pawstars-mod-action', this.handleModeration);

            // Bulk actions
            $('#pawstars-bulk-action').on('submit', this.handleBulkAction);

            // Select all checkbox
            $('#select-all-dogs').on('change', function() {
                $('input[name="dog_ids[]"]').prop('checked', $(this).is(':checked'));
            });
        },

        /**
         * Handle moderation action
         */
        handleModeration: function(e) {
            e.preventDefault();

            const $btn = $(this);
            const action = $btn.data('action');
            const dogId = $btn.data('dog-id');

            if (action === 'delete' && !confirm(pawstarsAdmin.strings.confirmDelete)) {
                return;
            }

            $btn.prop('disabled', true).text(pawstarsAdmin.strings.processing);

            $.ajax({
                url: pawstarsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pawstars_moderate_dog',
                    nonce: pawstarsAdmin.nonce,
                    dog_id: dogId,
                    mod_action: action
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('.pawstars-mod-card').fadeOut(function() {
                            $(this).remove();

                            // Check if no more cards
                            if ($('.pawstars-mod-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert(response.data.message || pawstarsAdmin.strings.error);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(pawstarsAdmin.strings.error);
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Handle bulk action
         */
        handleBulkAction: function(e) {
            e.preventDefault();

            const action = $(this).find('select[name="action"]').val();
            const dogIds = $('input[name="dog_ids[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (!action || action === '-1') {
                alert('Seleziona un\'azione');
                return;
            }

            if (dogIds.length === 0) {
                alert('Seleziona almeno un profilo');
                return;
            }

            if (action === 'delete' && !confirm(pawstarsAdmin.strings.confirmDelete)) {
                return;
            }

            const $submit = $(this).find('[type="submit"]');
            $submit.prop('disabled', true).val(pawstarsAdmin.strings.processing);

            $.ajax({
                url: pawstarsAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pawstars_bulk_moderate',
                    nonce: pawstarsAdmin.nonce,
                    dog_ids: dogIds,
                    mod_action: action
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || pawstarsAdmin.strings.error);
                        $submit.prop('disabled', false).val('Applica');
                    }
                },
                error: function() {
                    alert(pawstarsAdmin.strings.error);
                    $submit.prop('disabled', false).val('Applica');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PawStarsAdmin.init();
    });

})(jQuery);
