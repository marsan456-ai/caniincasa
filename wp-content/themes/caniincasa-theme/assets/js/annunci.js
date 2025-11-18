/**
 * Annunci Submission
 * Handle frontend annunci submission with AJAX
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Type Selection
         */
        $('.type-card').on('click', function() {
            var type = $(this).data('type');

            // Hide type selection
            $('#type-selection').fadeOut(300, function() {
                // Show corresponding form
                if (type === '4zampe') {
                    $('#form-4zampe').fadeIn(300);
                } else if (type === 'dogsitter') {
                    $('#form-dogsitter').fadeIn(300);
                }
            });
        });

        /**
         * Back Buttons
         */
        $('#back-from-4zampe, #back-from-dogsitter').on('click', function(e) {
            e.preventDefault();

            var $form = $(this).closest('.annuncio-form-container');

            $form.fadeOut(300, function() {
                // Reset form
                $form.find('form')[0].reset();
                // Show type selection
                $('#type-selection').fadeIn(300);
            });
        });

        /**
         * Conditional Logic: Razza field (4 zampe)
         */
        $('#tipo_cane').on('change', function() {
            var value = $(this).val();
            var $razzaGroup = $('#razza-group');

            if (value === 'razza') {
                $razzaGroup.removeClass('hidden');
                $('#razza').attr('required', true);
            } else {
                $razzaGroup.addClass('hidden');
                $('#razza').removeAttr('required');
                $('#razza').val('');
            }
        });

        /**
         * Submit Form 4 Zampe
         */
        $('#annuncio-4zampe-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var $btnText = $submitBtn.find('span');
            var originalText = 'Pubblica Annuncio';

            // Validate description length
            var descrizione = $('#descrizione_4zampe').val();
            if (descrizione.length < 50) {
                showNotification('La descrizione deve contenere almeno 50 caratteri', 'error');
                return;
            }

            // Disable button
            $submitBtn.prop('disabled', true);
            if ($btnText.length) {
                $btnText.text('Pubblicazione in corso...');
            }

            // Serialize form data
            var formData = $form.serializeArray();
            formData.push({ name: 'action', value: 'submit_annuncio_4zampe' });
            formData.push({ name: 'nonce', value: $('#annuncio_4zampe_nonce').val() });

            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    $submitBtn.prop('disabled', false);
                    if ($btnText.length) {
                        $btnText.text(originalText);
                    }

                    if (response.success) {
                        showNotification(response.data.message, 'success');

                        // Reset form
                        $form[0].reset();

                        // Redirect to dashboard after 2 seconds
                        setTimeout(function() {
                            window.location.href = caniincasaAjax.dashboardUrl + '?tab=annunci';
                        }, 2000);
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    $submitBtn.prop('disabled', false);
                    if ($btnText.length) {
                        $btnText.text(originalText);
                    }
                    showNotification('Si è verificato un errore. Riprova.', 'error');
                }
            });
        });

        /**
         * Submit Form Dogsitter
         */
        $('#annuncio-dogsitter-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = 'Pubblica Annuncio';

            // Validate description length
            var descrizione = $('#descrizione_dogsitter').val();
            if (descrizione.length < 50) {
                showNotification('La descrizione deve contenere almeno 50 caratteri', 'error');
                return;
            }

            // Disable button
            $submitBtn.prop('disabled', true).text('Pubblicazione in corso...');

            // Serialize form data
            var formData = $form.serializeArray();
            formData.push({ name: 'action', value: 'submit_annuncio_dogsitter' });
            formData.push({ name: 'nonce', value: $('#annuncio_dogsitter_nonce').val() });

            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    $submitBtn.prop('disabled', false).text(originalText);

                    if (response.success) {
                        showNotification(response.data.message, 'success');

                        // Reset form
                        $form[0].reset();

                        // Redirect to dashboard after 2 seconds
                        setTimeout(function() {
                            window.location.href = caniincasaAjax.dashboardUrl + '?tab=annunci';
                        }, 2000);
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                    showNotification('Si è verificato un errore. Riprova.', 'error');
                }
            });
        });

        /**
         * Character Counter for Descriptions
         */
        $('#descrizione_4zampe, #descrizione_dogsitter').on('input', function() {
            var $textarea = $(this);
            var length = $textarea.val().length;
            var $helpText = $textarea.siblings('.help-text');

            if (length < 50) {
                $helpText.text('Minimo 50 caratteri (' + length + '/50)');
                $helpText.css('color', '#E74C3C');
            } else {
                $helpText.text(length + ' caratteri');
                $helpText.css('color', '#27AE60');
            }
        });

        /**
         * Show notification message
         */
        function showNotification(message, type) {
            var icon = type === 'success'
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>';

            var $notification = $('<div class="annuncio-notification ' + type + '">' + icon + '<span>' + message + '</span></div>');
            $('body').append($notification);

            setTimeout(function() {
                $notification.addClass('show');
            }, 100);

            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 5000);
        }

    });

})(jQuery);
