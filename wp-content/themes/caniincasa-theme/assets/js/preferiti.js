/**
 * Preferiti Functionality
 * Handle add/remove from preferiti on single pages
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Add/Toggle Preferito
         */
        $(document).on('click', '.btn-preferiti', function(e) {
            e.preventDefault();

            var $button = $(this);
            var postId = $button.data('post-id');
            var postType = $button.data('post-type');
            var isActive = $button.hasClass('active');

            // Prevent double-click
            if ($button.hasClass('processing')) {
                return;
            }

            $button.addClass('processing');

            // Determine action
            var action = isActive ? 'remove_preferito' : 'add_preferito';
            var ajaxData = {
                action: action,
                nonce: caniincasaAjax.nonce,
                post_id: postId
            };

            if (action === 'add_preferito') {
                ajaxData.post_type = postType;
            } else {
                // For remove, determine type from post_type
                if (postType === 'razze_di_cani') {
                    ajaxData.type = 'razze';
                } else {
                    ajaxData.type = 'strutture';
                }
            }

            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    $button.removeClass('processing');

                    if (response.success) {
                        // Toggle button state
                        if (isActive) {
                            $button.removeClass('active');
                            $button.find('span').text('Aggiungi ai Preferiti');
                            $button.find('svg').attr('fill', 'none');
                        } else {
                            $button.addClass('active');
                            $button.find('span').text('Nei Preferiti');
                            $button.find('svg').attr('fill', 'currentColor');
                        }

                        // Show notification
                        showNotification(response.data.message, 'success');
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    $button.removeClass('processing');
                    showNotification('Si è verificato un errore. Riprova.', 'error');
                }
            });
        });

        /**
         * Show notification message
         */
        function showNotification(message, type) {
            var $notification = $('<div class="preferiti-notification ' + type + '">' + message + '</div>');
            $('body').append($notification);

            setTimeout(function() {
                $notification.addClass('show');
            }, 100);

            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        }

    });

})(jQuery);
