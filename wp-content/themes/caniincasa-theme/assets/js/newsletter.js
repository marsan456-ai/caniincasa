/**
 * Newsletter Modal and Subscription Handler
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    // Newsletter Modal Handler
    const NewsletterModal = {
        modal: null,
        form: null,
        messages: null,

        init: function() {
            this.modal = $('#newsletter-modal');
            this.form = $('#newsletter-form');
            this.messages = $('#newsletter-messages');

            if (!this.modal.length || !this.form.length) {
                return;
            }

            this.bindEvents();
        },

        bindEvents: function() {
            // Open modal button
            $('#open-newsletter-modal').on('click', this.openModal.bind(this));

            // Close modal buttons
            this.modal.find('.modal-close, .modal-overlay').on('click', this.closeModal.bind(this));

            // Prevent closing when clicking inside modal content
            this.modal.find('.modal-content').on('click', function(e) {
                e.stopPropagation();
            });

            // Handle form submission
            this.form.on('submit', this.handleSubmit.bind(this));

            // Close on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && this.modal.is(':visible')) {
                    this.closeModal();
                }
            }.bind(this));
        },

        openModal: function(e) {
            e.preventDefault();
            this.modal.fadeIn(300);
            $('body').css('overflow', 'hidden');

            // Focus on first input
            setTimeout(() => {
                this.form.find('input[type="email"]').focus();
            }, 350);
        },

        closeModal: function(e) {
            if (e) {
                e.preventDefault();
            }
            this.modal.fadeOut(300);
            $('body').css('overflow', '');

            // Reset form if submission was successful
            if (this.messages.hasClass('success')) {
                setTimeout(() => {
                    this.resetForm();
                }, 300);
            }
        },

        showMessage: function(message, type) {
            this.messages
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .slideDown(200);

            // Scroll to message
            this.modal.find('.modal-content').animate({
                scrollTop: 0
            }, 300);
        },

        hideMessage: function() {
            this.messages.slideUp(200, () => {
                this.messages.html('').removeClass('success error');
            });
        },

        resetForm: function() {
            this.form[0].reset();
            this.hideMessage();
        },

        handleSubmit: function(e) {
            e.preventDefault();

            // Hide previous messages
            this.hideMessage();

            // Get form data
            const formData = {
                action: 'caniincasa_newsletter_subscribe',
                newsletter_name: $('#newsletter-name').val().trim(),
                newsletter_email: $('#newsletter-email').val().trim(),
                newsletter_gdpr: $('#newsletter-gdpr').is(':checked') ? 1 : 0,
                newsletter_marketing: $('#newsletter-marketing').is(':checked') ? 1 : 0,
                newsletter_nonce: this.form.find('[name="newsletter_nonce"]').val()
            };

            // Validate email
            if (!this.validateEmail(formData.newsletter_email)) {
                this.showMessage('Inserisci un indirizzo email valido.', 'error');
                return;
            }

            // Validate GDPR consent
            if (!formData.newsletter_gdpr) {
                this.showMessage('Devi accettare la Privacy Policy per iscriverti.', 'error');
                return;
            }

            // Validate marketing consent
            if (!formData.newsletter_marketing) {
                this.showMessage('Devi acconsentire a ricevere comunicazioni per iscriverti alla newsletter.', 'error');
                return;
            }

            // Show loading state
            const submitBtn = this.form.find('button[type="submit"]');
            const btnText = submitBtn.find('.btn-text');
            const btnLoading = submitBtn.find('.btn-loading');

            submitBtn.prop('disabled', true);
            btnText.hide();
            btnLoading.css('display', 'flex');

            // Send AJAX request
            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        this.showMessage(
                            response.data.message || 'Iscrizione completata con successo! Controlla la tua email per confermare.',
                            'success'
                        );

                        // Close modal after 3 seconds
                        setTimeout(() => {
                            this.closeModal();
                        }, 3000);
                    } else {
                        this.showMessage(
                            response.data.message || 'Si è verificato un errore. Riprova.',
                            'error'
                        );
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Newsletter subscription error:', error);
                    this.showMessage(
                        'Si è verificato un errore di connessione. Riprova più tardi.',
                        'error'
                    );
                },
                complete: () => {
                    // Reset loading state
                    submitBtn.prop('disabled', false);
                    btnText.show();
                    btnLoading.hide();
                }
            });
        },

        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        NewsletterModal.init();
    });

})(jQuery);
