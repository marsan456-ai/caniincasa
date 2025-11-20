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

            // Debug log
            if (this.modal.length === 0) {
                console.error('Messaging: Modal #message-modal not found!');
                return;
            }

            if (this.form.length === 0) {
                console.error('Messaging: Form #message-form not found!');
                return;
            }

            console.log('Messaging: Initialized successfully');
            this.bindEvents();
            this.updateUnreadCount();
        },

        bindEvents: function() {
            // TEST: Verifica immediata se l'evento si attacca
            console.log('Messaging: Binding events to buttons');
            console.log('Messaging: Reply buttons found:', $('.btn-reply-message').length);

            // Open modal button
            $(document).on('click', '.btn-send-message', this.openModal.bind(this));

            // Reply to message
            $(document).on('click', '.btn-reply-message', function(e) {
                alert('CLICK RILEVATO! Il pulsante funziona. Ora apro il modal...');
                Messaging.openReplyModal(e);
            });

            // Close modal
            $(document).on('click', '.message-modal-close, .message-modal-overlay', this.closeModal.bind(this));

            // Submit form
            this.form.on('submit', this.sendMessage.bind(this));

            // View full message
            $(document).on('click', '.view-message-btn', this.viewMessage.bind(this));

            // Mark as read
            $(document).on('click', '.mark-read-btn', this.markAsRead.bind(this));

            // Delete message
            $(document).on('click', '.delete-message-btn', this.deleteMessage.bind(this));

            // Block user
            $(document).on('click', '.btn-block-user', this.blockUser.bind(this));

            // Unblock user
            $(document).on('click', '.btn-unblock-user', this.unblockUser.bind(this));

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
            $('#message-parent-id').val(''); // Clear parent ID for new messages
            $('#message-related-post-id').val(relatedPostId);
            $('#message-related-post-type').val(relatedPostType);
            $('#message-subject').val(subject);
            $('#message-recipient-name').text(recipientName);
            $('.message-modal-header h2').text('Invia Messaggio');

            // Show modal
            this.modal.addClass('active');
            $('body').addClass('modal-open');

            // Focus message textarea
            setTimeout(() => {
                $('#message-content').focus();
            }, 300);
        },

        openReplyModal: function(e) {
            e.preventDefault();

            console.log('Messaging: Reply button clicked');

            const $btn = $(e.currentTarget);
            const parentId = $btn.data('message-id');
            const recipientId = $btn.data('recipient-id');
            const recipientName = $btn.data('recipient-name');
            const subject = $btn.data('subject') || '';

            console.log('Reply data:', { parentId, recipientId, recipientName, subject });

            // Populate form for reply
            $('#message-recipient-id').val(recipientId);
            $('#message-parent-id').val(parentId);
            $('#message-related-post-id').val('');
            $('#message-related-post-type').val('');

            // Add Re: to subject if not already there
            const replySubject = subject.startsWith('Re:') ? subject : 'Re: ' + subject;
            $('#message-subject').val(replySubject);
            $('#message-recipient-name').text(recipientName);
            $('.message-modal-header h2').text('Rispondi al Messaggio');

            console.log('Opening modal...');

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
                parent_id: $('#message-parent-id').val() || null,
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

        viewMessage: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const $messageItem = $btn.closest('.message-item');
            const $preview = $messageItem.find('.message-preview-text');
            const $fullContent = $messageItem.find('.message-full-content');
            const messageId = $btn.data('message-id');

            // Toggle visibility
            if ($fullContent.is(':visible')) {
                $fullContent.slideUp(300);
                $preview.show();
                $btn.text('Visualizza');
            } else {
                $preview.hide();
                $fullContent.slideDown(300);
                $btn.text('Nascondi');

                // Auto mark as read when viewing
                if ($messageItem.hasClass('unread')) {
                    this.markAsRead({
                        currentTarget: $messageItem.find('.mark-read-btn')[0] || $btn[0],
                        preventDefault: () => {}
                    });
                }
            }
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
        },

        blockUser: function(e) {
            e.preventDefault();

            if (!confirm('Sei sicuro di voler bloccare questo utente? Non potrete più inviarvi messaggi.')) {
                return;
            }

            const $btn = $(e.currentTarget);
            const blockedUserId = $btn.data('user-id');

            $btn.prop('disabled', true).text('Blocco...');

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'block_user',
                    nonce: caniincasaData.nonce,
                    blocked_user_id: blockedUserId
                },
                success: (response) => {
                    if (response.success) {
                        alert(response.data.message);

                        // Replace block button with unblock button
                        $btn.removeClass('btn-block-user btn-danger')
                            .addClass('btn-unblock-user btn-secondary')
                            .data('user-id', blockedUserId)
                            .text('Sblocca Utente')
                            .prop('disabled', false);
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false).text('Blocca Utente');
                    }
                },
                error: () => {
                    alert('Errore di connessione. Riprova.');
                    $btn.prop('disabled', false).text('Blocca Utente');
                }
            });
        },

        unblockUser: function(e) {
            e.preventDefault();

            if (!confirm('Vuoi sbloccare questo utente?')) {
                return;
            }

            const $btn = $(e.currentTarget);
            const blockedUserId = $btn.data('user-id');

            $btn.prop('disabled', true).text('Sblocco...');

            $.ajax({
                url: caniincasaData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'unblock_user',
                    nonce: caniincasaData.nonce,
                    blocked_user_id: blockedUserId
                },
                success: (response) => {
                    if (response.success) {
                        alert(response.data.message);

                        // Replace unblock button with block button
                        $btn.removeClass('btn-unblock-user btn-secondary')
                            .addClass('btn-block-user btn-danger')
                            .data('user-id', blockedUserId)
                            .text('Blocca Utente')
                            .prop('disabled', false);
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false).text('Sblocca Utente');
                    }
                },
                error: () => {
                    alert('Errore di connessione. Riprova.');
                    $btn.prop('disabled', false).text('Sblocca Utente');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        Messaging.init();
    });

})(jQuery);
