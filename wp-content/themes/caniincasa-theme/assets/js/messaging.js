/**
 * Messaging System JavaScript
 * Handles private messaging between users
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    const Messaging = {
        modal: null,
        form: null,

        init: function() {
            this.modal = $('#message-modal');
            this.form = $('#message-form');

            this.bindEvents();
            this.updateUnreadCount();
        },

        bindEvents: function() {
            // Open modal button
            $(document).on('click', '.btn-send-message', this.openModal.bind(this));

            // Close modal
            $(document).on('click', '.message-modal-close, .message-modal-overlay', this.closeModal.bind(this));

            // Submit form
            this.form.on('submit', this.sendMessage.bind(this));

            // Mark as read
            $(document).on('click', '.mark-read-btn', this.markAsRead.bind(this));

            // Delete message
            $(document).on('click', '.delete-message-btn', this.deleteMessage.bind(this));

            // Refresh count periodically
            setInterval(this.updateUnreadCount.bind(this), 60000); // Every minute
        },

        openModal: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const recipientId = $btn.data('recipient-id');
            const recipientName = $btn.data('recipient-name');
            const relatedPostId = $btn.data('post-id') || '';
            const relatedPostType = $btn.data('post-type') || '';
            const subject = $btn.data('subject') || '';

            // Populate form
            $('#message-recipient-id').val(recipientId);
            $('#message-related-post-id').val(relatedPostId);
            $('#message-related-post-type').val(relatedPostType);
            $('#message-subject').val(subject);
            $('#message-recipient-name').text(recipientName);

            // Show modal
            this.modal.addClass('active');
            $('body').addClass('modal-open');

            // Focus message textarea
            setTimeout(() => {
                $('#message-content').focus();
            }, 300);
        },

        closeModal: function(e) {
            if (e) {
                e.preventDefault();
            }

            this.modal.removeClass('active');
            $('body').removeClass('modal-open');

            // Reset form
            this.form[0].reset();
            $('.message-response').empty().hide();
        },

        sendMessage: function(e) {
            e.preventDefault();

            const $form = $(e.target);
            const $submitBtn = $form.find('button[type="submit"]');
            const $response = $('.message-response');

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Invio in corso...');
            $response.empty().hide();

            const formData = {
                action: 'send_message',
                nonce: caniincasaData.nonce,
                recipient_id: $('#message-recipient-id').val(),
                subject: $('#message-subject').val(),
                message: $('#message-content').val(),
                related_post_id: $('#message-related-post-id').val(),
                related_post_type: $('#message-related-post-type').val()
            };

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    if (response.success) {
                        $response
                            .removeClass('error')
                            .addClass('success')
                            .html('<p>' + response.data.message + '</p>')
                            .show();

                        // Close modal after 2 seconds
                        setTimeout(() => {
                            this.closeModal();
                        }, 2000);
                    } else {
                        // Log debug info to console if available
                        if (response.data.debug) {
                            console.error('Messaging error:', response.data.debug);
                        }

                        $response
                            .removeClass('success')
                            .addClass('error')
                            .html('<p>' + response.data.message + '</p>')
                            .show();

                        $submitBtn.prop('disabled', false).text('Invia Messaggio');
                    }
                },
                error: () => {
                    $response
                        .removeClass('success')
                        .addClass('error')
                        .html('<p>Errore di connessione. Riprova.</p>')
                        .show();

                    $submitBtn.prop('disabled', false).text('Invia Messaggio');
                }
            });
        },

        markAsRead: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const messageId = $btn.data('message-id');

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'mark_message_read',
                    nonce: caniincasaData.nonce,
                    message_id: messageId
                },
                success: (response) => {
                    if (response.success) {
                        $btn.closest('.message-item').removeClass('unread');
                        $btn.remove();
                        this.updateUnreadCount();
                    }
                }
            });
        },

        deleteMessage: function(e) {
            e.preventDefault();

            if (!confirm('Sei sicuro di voler eliminare questo messaggio?')) {
                return;
            }

            const $btn = $(e.currentTarget);
            const messageId = $btn.data('message-id');

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_message',
                    nonce: caniincasaData.nonce,
                    message_id: messageId
                },
                success: (response) => {
                    if (response.success) {
                        $btn.closest('.message-item').fadeOut(300, function() {
                            $(this).remove();

                            // Check if empty
                            if ($('.message-item').length === 0) {
                                $('.messages-list').html('<p class="no-messages">Nessun messaggio.</p>');
                            }
                        });

                        this.updateUnreadCount();
                    }
                }
            });
        },

        updateUnreadCount: function() {
            const $badge = $('.messages-badge');

            if ($badge.length === 0) {
                return;
            }

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_unread_count',
                    nonce: caniincasaData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        const count = response.data.count;

                        if (count > 0) {
                            $badge.text(count).show();
                        } else {
                            $badge.hide();
                        }
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        Messaging.init();
    });

})(jQuery);
