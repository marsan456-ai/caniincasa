/**
 * Dashboard Functionality
 * Handle dashboard interactions and AJAX requests
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Remove Preferito from Dashboard
         */
        $(document).on('click', '.remove-preferito', function(e) {
            e.preventDefault();

            var $link = $(this);
            var $card = $link.closest('.preferito-card');
            var postId = $link.data('post-id');
            var type = $link.data('type');

            if (!confirm('Sei sicuro di voler rimuovere questo elemento dai preferiti?')) {
                return;
            }

            // Prevent double-click
            if ($link.hasClass('processing')) {
                return;
            }

            $link.addClass('processing').text('Rimozione...');

            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'remove_preferito',
                    nonce: caniincasaAjax.nonce,
                    post_id: postId,
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        // Fade out and remove card
                        $card.fadeOut(300, function() {
                            $(this).remove();

                            // Check if no more items in this section
                            var $grid = $('.preferiti-grid');
                            if ($grid.find('.preferito-card').length === 0) {
                                // Reload page to show empty state
                                location.reload();
                            }
                        });

                        showNotification(response.data.message, 'success');
                    } else {
                        $link.removeClass('processing').text('Rimuovi');
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    $link.removeClass('processing').text('Rimuovi');
                    showNotification('Si è verificato un errore. Riprova.', 'error');
                }
            });
        });

        /**
         * Password strength indicator
         */
        var $newPassword = $('#new_password');
        var $confirmPassword = $('#confirm_password');

        if ($newPassword.length) {
            $newPassword.on('keyup', function() {
                var password = $(this).val();
                var strength = getPasswordStrength(password);

                // You can add a strength indicator here
                // For now, just validate on submit
            });

            $confirmPassword.on('keyup', function() {
                var password = $newPassword.val();
                var confirm = $(this).val();

                if (confirm.length > 0) {
                    if (password === confirm) {
                        $(this).removeClass('error').addClass('success');
                    } else {
                        $(this).removeClass('success').addClass('error');
                    }
                } else {
                    $(this).removeClass('error success');
                }
            });
        }

        /**
         * Form validation
         */
        $('.dashboard-form').on('submit', function(e) {
            var $form = $(this);
            var action = $form.find('input[name="action"]').val();

            if (action === 'change_password') {
                var newPass = $form.find('#new_password').val();
                var confirmPass = $form.find('#confirm_password').val();

                if (newPass !== confirmPass) {
                    e.preventDefault();
                    showNotification('Le password non corrispondono.', 'error');
                    return false;
                }

                if (newPass.length < 8) {
                    e.preventDefault();
                    showNotification('La password deve contenere almeno 8 caratteri.', 'error');
                    return false;
                }
            }

            if (action === 'update_profile') {
                var email = $form.find('#user_email').val();
                if (!isValidEmail(email)) {
                    e.preventDefault();
                    showNotification('Inserisci un indirizzo email valido.', 'error');
                    return false;
                }
            }
        });

        /**
         * Helper: Get password strength
         */
        function getPasswordStrength(password) {
            var strength = 0;

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            return strength;
        }

        /**
         * Helper: Validate email
         */
        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        /**
         * Show notification message
         */
        function showNotification(message, type) {
            var icon = type === 'success'
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>';

            var $notification = $('<div class="dashboard-notification ' + type + '">' + icon + '<span>' + message + '</span></div>');
            $('body').append($notification);

            setTimeout(function() {
                $notification.addClass('show');
            }, 100);

            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 4000);
        }

        /**
         * Auto-dismiss success/error messages
         */
        if ($('.dashboard-message').length) {
            setTimeout(function() {
                $('.dashboard-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Messages tabs switching (Ricevuti/Inviati)
         */
        $(document).on('click', '.messages-tab', function(e) {
            e.preventDefault();

            var $tab = $(this);
            var targetTab = $tab.data('tab');

            console.log('Dashboard: Switching to messages tab:', targetTab);

            // Update active state
            $('.messages-tab').removeClass('active');
            $tab.addClass('active');

            // Show/hide message lists
            if (targetTab === 'inbox') {
                $('#inbox-messages').show();
                $('#sent-messages').hide();
            } else if (targetTab === 'sent') {
                $('#inbox-messages').hide();
                $('#sent-messages').show();
            }
        });

    });

})(jQuery);
