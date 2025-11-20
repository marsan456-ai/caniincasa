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

            // Check if modal exists
            if (this.modal.length === 0) {
                return;
            }

            if (this.form.length === 0) {
                return;
            }

            this.bindEvents();
            this.updateUnreadCount();
        },

        bindEvents: function() {
            var self = this; // Save reference to avoid context issues

            // Open modal button
            $(document).on('click', '.btn-send-message', function(e) {
                self.openModal(e);
            });

            // Reply to message - FIXED
            $(document).on('click', '.btn-reply-message', function(e) {
                self.openReplyModal(e);
            });

            // Close modal
            $(document).on('click', '.message-modal-close, .message-modal-overlay', function(e) {
                self.closeModal(e);
            });

            // Submit form
            this.form.on('submit', function(e) {
                self.sendMessage(e);
            });

            // View full message
            $(document).on('click', '.view-message-btn', function(e) {
                self.viewMessage(e);
            });

            // Mark as read
            $(document).on('click', '.mark-read-btn', function(e) {
                self.markAsRead(e);
            });

            // Delete message
            $(document).on('click', '.delete-message-btn', function(e) {
                self.deleteMessage(e);
            });

            // Block user
            $(document).on('click', '.btn-block-user', function(e) {
                self.blockUser(e);
            });

            // Unblock user
            $(document).on('click', '.btn-unblock-user', function(e) {
                self.unblockUser(e);
            });

            // Refresh count periodically
            setInterval(function() {
                self.updateUnreadCount();
            }, 60000);
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

            var $btn = $(e.currentTarget);
            var parentId = $btn.data('message-id');
            var recipientId = $btn.data('recipient-id');
            var recipientName = $btn.data('recipient-name');
            var subject = $btn.data('subject') || '';

            // Populate form for reply
            $('#message-recipient-id').val(recipientId);
            $('#message-parent-id').val(parentId);
            $('#message-related-post-id').val('');
            $('#message-related-post-type').val('');

            // Add Re: to subject if not already there
            var replySubject = subject.indexOf('Re:') === 0 ? subject : 'Re: ' + subject;
            $('#message-subject').val(replySubject);
            $('#message-recipient-name').text(recipientName);
            $('.message-modal-header h2').text('Rispondi al Messaggio');

            // Show modal
            this.modal.addClass('active');
            $('body').addClass('modal-open');

            // Focus message textarea
            var self = this;
            setTimeout(function() {
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

                // Load replies if they exist and haven't been loaded yet
                const $repliesContainer = $fullContent.find('.message-replies');
                const $repliesLoading = $fullContent.find('.replies-loading');

                if ($repliesContainer.length > 0 && $repliesContainer.is(':empty')) {
                    // Load replies via AJAX
                    $repliesLoading.show();

                    $.ajax({
                        url: caniincasaData.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_message_replies',
                            nonce: caniincasaData.nonce,
                            parent_id: messageId
                        },
                        success: (response) => {
                            $repliesLoading.hide();

                            if (response.success && response.data.replies.length > 0) {
                                this.renderReplies($repliesContainer, response.data);
                            }
                        },
                        error: () => {
                            $repliesLoading.hide();
                            $repliesContainer.html('<p class="error-text">Errore nel caricamento delle risposte.</p>');
                        }
                    });
                }

                // Auto mark as read when viewing
                if ($messageItem.hasClass('unread')) {
                    this.markAsRead({
                        currentTarget: $messageItem.find('.mark-read-btn')[0] || $btn[0],
                        preventDefault: () => {}
                    });
                }
            }
        },

        renderReplies: function($container, data) {
            const replies = data.replies;
            const hasMore = data.has_more || false;
            const total = data.total || replies.length;

            let html = '<div class="message-thread-header">';

            if (hasMore) {
                html += '<strong>Mostrando ' + replies.length + ' di ' + total + ' risposte (ultime risposte):</strong>';
                html += '<p class="replies-limit-notice">Per performance, vengono mostrate le prime 50 risposte.</p>';
            } else {
                html += '<strong>' + replies.length + ' ' + (replies.length === 1 ? 'Risposta' : 'Risposte') + ':</strong>';
            }

            html += '</div>';

            replies.forEach((reply) => {
                const date = new Date(reply.created_at);
                const dateStr = date.toLocaleDateString('it-IT') + ' ' + date.toLocaleTimeString('it-IT', {hour: '2-digit', minute: '2-digit'});
                const isMineCls = reply.is_mine ? 'reply-mine' : 'reply-theirs';

                html += '<div class="message-reply ' + isMineCls + '">';
                html += '<div class="reply-header">';
                html += '<strong>' + this.escapeHtml(reply.sender_name) + '</strong>';
                html += '<span class="reply-date">' + dateStr + '</span>';
                html += '</div>';
                html += '<div class="reply-content">' + this.escapeHtml(reply.message) + '</div>';
                html += '</div>';
            });

            $container.html(html);
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, (m) => map[m]);
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
