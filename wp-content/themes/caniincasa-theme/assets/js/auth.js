/**
 * Auth JavaScript - Registration and Login
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    /**
     * Multi-step form navigation
     */
    function initMultiStepForm() {
        let currentStep = 1;
        const totalSteps = 3;

        // Next step
        $('.next-step').on('click', function(e) {
            e.preventDefault();

            if (validateStep(currentStep)) {
                currentStep++;
                updateStep(currentStep);
            }
        });

        // Previous step
        $('.prev-step').on('click', function(e) {
            e.preventDefault();

            if (currentStep > 1) {
                currentStep--;
                updateStep(currentStep);
            }
        });

        function updateStep(step) {
            // Update form steps
            $('.form-step').removeClass('active');
            $('.form-step[data-step="' + step + '"]').addClass('active');

            // Update progress indicator
            $('.progress-step').removeClass('active completed');
            for (let i = 1; i < step; i++) {
                $('.progress-step[data-step="' + i + '"]').addClass('completed');
            }
            $('.progress-step[data-step="' + step + '"]').addClass('active');

            // Scroll to top
            $('html, body').animate({
                scrollTop: $('.auth-form').offset().top - 100
            }, 300);
        }

        function validateStep(step) {
            let isValid = true;
            const $currentStep = $('.form-step[data-step="' + step + '"]');

            // Clear previous errors
            $currentStep.find('.form-group').removeClass('error');

            // Validate required fields
            $currentStep.find('input[required], select[required]').each(function() {
                const $field = $(this);
                const value = $field.val().trim();

                if (!value) {
                    isValid = false;
                    $field.closest('.form-group').addClass('error');
                    showMessage('Compila tutti i campi obbligatori.', 'error');
                    return false;
                }

                // Email validation
                if ($field.attr('type') === 'email' && !isValidEmail(value)) {
                    isValid = false;
                    $field.closest('.form-group').addClass('error');
                    showMessage('Inserisci un\'email valida.', 'error');
                    return false;
                }

                // Password validation
                if ($field.attr('id') === 'password') {
                    if (value.length < 8) {
                        isValid = false;
                        $field.closest('.form-group').addClass('error');
                        showMessage('La password deve contenere almeno 8 caratteri.', 'error');
                        return false;
                    }
                }

                // Confirm password
                if ($field.attr('id') === 'confirm_password') {
                    const password = $('#password').val();
                    if (value !== password) {
                        isValid = false;
                        $field.closest('.form-group').addClass('error');
                        showMessage('Le password non corrispondono.', 'error');
                        return false;
                    }
                }
            });

            return isValid;
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    }

    /**
     * Password toggle visibility
     */
    function initPasswordToggle() {
        $('.toggle-password').on('click', function() {
            const $btn = $(this);
            const $input = $btn.siblings('input');
            const type = $input.attr('type');

            if (type === 'password') {
                $input.attr('type', 'text');
                $btn.addClass('active');
            } else {
                $input.attr('type', 'password');
                $btn.removeClass('active');
            }
        });
    }

    /**
     * Registration form submission
     */
    function initRegistrationForm() {
        $('#registration-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $('#submit-registration');

            // Validate step 3
            if (!validateFinalStep()) {
                return;
            }

            // Disable submit button
            $submitBtn.prop('disabled', true).addClass('loading');

            // Get redirect_to from hidden field or URL
            let redirectTo = $('input[name="redirect_to"]').val();
            if (!redirectTo) {
                const urlParams = new URLSearchParams(window.location.search);
                redirectTo = urlParams.get('redirect_to');
            }

            // Prepare form data
            const formData = {
                action: 'register_user',
                nonce: $('#register_nonce').val(),
                username: $('#username').val().trim(),
                email: $('#email').val().trim(),
                password: $('#password').val(),
                confirm_password: $('#confirm_password').val(),
                first_name: $('#first_name').val().trim(),
                last_name: $('#last_name').val().trim(),
                user_type: $('input[name="user_type"]:checked').val(),
                phone: $('#phone').val().trim(),
                city: $('#city').val().trim(),
                provincia: $('#provincia').val(),
                accept_privacy: $('#accept_privacy').is(':checked')
            };

            if (redirectTo) {
                formData.redirect_to = redirectTo;
            }

            // AJAX request
            $.ajax({
                url: caniincasaAuth.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');

                        // Redirect to dashboard
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 1500);
                    } else {
                        showMessage(response.data.message, 'error');
                        $submitBtn.prop('disabled', false).removeClass('loading');
                    }
                },
                error: function() {
                    showMessage('Errore durante la registrazione. Riprova.', 'error');
                    $submitBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });

        function validateFinalStep() {
            // Check user type
            if (!$('input[name="user_type"]:checked').length) {
                showMessage('Seleziona una tipologia utente.', 'error');
                return false;
            }

            // Check privacy
            if (!$('#accept_privacy').is(':checked')) {
                showMessage('Devi accettare la privacy policy.', 'error');
                return false;
            }

            return true;
        }
    }

    /**
     * Login form submission
     */
    function initLoginForm() {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $('#submit-login');

            // Disable submit button
            $submitBtn.prop('disabled', true).addClass('loading');

            // Get redirect_to from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            const redirectTo = urlParams.get('redirect_to');

            // Prepare form data
            const formData = {
                action: 'login_user',
                nonce: $('#login_nonce').val(),
                username: $('#username').val().trim(),
                password: $('#password').val(),
                remember: $('#remember').is(':checked')
            };

            if (redirectTo) {
                formData.redirect_to = redirectTo;
            }

            // Validation
            if (!formData.username || !formData.password) {
                showMessage('Inserisci username e password.', 'error');
                $submitBtn.prop('disabled', false).removeClass('loading');
                return;
            }

            // AJAX request
            $.ajax({
                url: caniincasaAuth.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');

                        // Redirect to dashboard
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 1000);
                    } else {
                        showMessage(response.data.message, 'error');
                        $submitBtn.prop('disabled', false).removeClass('loading');
                    }
                },
                error: function() {
                    showMessage('Errore durante il login. Riprova.', 'error');
                    $submitBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        const $messagesContainer = $('.auth-messages');

        $messagesContainer
            .removeClass('success error')
            .addClass(type)
            .html(getMessageIcon(type) + '<span>' + message + '</span>')
            .slideDown(300);

        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                $messagesContainer.slideUp(300);
            }, 5000);
        }

        // Scroll to message
        if ($messagesContainer.length && $messagesContainer.offset()) {
            $('html, body').animate({
                scrollTop: $messagesContainer.offset().top - 100
            }, 300);
        }
    }

    function getMessageIcon(type) {
        if (type === 'success') {
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
        } else {
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>';
        }
    }

    /**
     * User type selection animation
     */
    function initUserTypeSelection() {
        $('input[name="user_type"]').on('change', function() {
            // Remove animation classes
            $('.user-type-card').removeClass('selected-animation');

            // Add animation to selected
            $(this).closest('.user-type-card').addClass('selected-animation');
        });
    }

    /**
     * Initialize all functions
     */
    $(document).ready(function() {
        // Check if we're on auth page
        if ($('.auth-page').length) {
            initPasswordToggle();
            initUserTypeSelection();

            // Registration page
            if ($('#registration-form').length) {
                initMultiStepForm();
                initRegistrationForm();
            }

            // Login page
            if ($('#login-form').length) {
                initLoginForm();
            }
        }
    });

})(jQuery);
