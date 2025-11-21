/**
 * Paw Stars - Voting System
 *
 * @package Pawstars
 * @since 1.0.0
 */

(function($) {
    'use strict';

    window.PawStarsVoting = {

        /**
         * Initialize voting system
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind voting events
         */
        bindEvents: function() {
            $(document).on('click', '.pawstars-reactions .reaction-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if ($(this).hasClass('voted') || $(this).prop('disabled')) {
                    return;
                }

                const $btn = $(this);
                const $reactions = $btn.closest('.pawstars-reactions');
                const dogId = $reactions.data('dog-id');
                const reaction = $btn.data('reaction');

                PawStarsVoting.vote(dogId, reaction, $btn);
            });
        },

        /**
         * Submit vote
         */
        vote: function(dogId, reaction, $btn) {
            // Check if logged in
            if (!pawstarsData.isLoggedIn) {
                this.showLoginPrompt();
                return;
            }

            const $reactions = $btn.closest('.pawstars-reactions');
            const originalHtml = $btn.html();

            // Show loading state
            $btn.prop('disabled', true).html('<span class="loading">...</span>');

            $.ajax({
                url: pawstarsData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pawstars_vote',
                    nonce: pawstarsData.nonce,
                    dog_id: dogId,
                    reaction: reaction
                },
                success: function(response) {
                    if (response.success) {
                        // Update button state
                        $btn.addClass('voted').html(originalHtml);

                        // Update counts
                        if (response.data.vote_stats) {
                            PawStarsVoting.updateCounts($reactions, response.data.vote_stats);
                        }

                        // Update points display
                        if (response.data.total_points !== undefined) {
                            PawStarsVoting.updatePoints(dogId, response.data.total_points);
                        }

                        // Show success message
                        PawStars.toast(response.data.message, 'success');

                        // Trigger custom event
                        $(document).trigger('pawstars:vote:success', {
                            dogId: dogId,
                            reaction: reaction,
                            data: response.data
                        });

                    } else {
                        $btn.prop('disabled', false).html(originalHtml);
                        PawStars.toast(response.data.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    PawStars.toast(pawstarsData.strings.voteError, 'error');
                }
            });
        },

        /**
         * Update vote counts in UI
         */
        updateCounts: function($reactions, stats) {
            Object.keys(stats).forEach(function(type) {
                if (type === 'total') return;

                const $btn = $reactions.find(`[data-reaction="${type}"]`);
                const $count = $btn.find('.reaction-count');

                if ($count.length) {
                    $count.text(stats[type].count);
                }
            });
        },

        /**
         * Update points display
         */
        updatePoints: function(dogId, points) {
            const $card = $(`.pawstars-dog-card[data-dog-id="${dogId}"]`);
            $card.find('.stat-points').html(`⭐ ${points.toLocaleString()}`);

            // Also update profile if on profile page
            $('.profile-stats .stat-box').first().find('.stat-value').text(points.toLocaleString());
        },

        /**
         * Show login prompt
         */
        showLoginPrompt: function() {
            const loginUrl = pawstarsData.loginUrl || '/wp-login.php?redirect_to=' + encodeURIComponent(window.location.href);

            const $modal = $(`
                <div class="pawstars-modal">
                    <div class="modal-content">
                        <button class="modal-close">&times;</button>
                        <div class="modal-icon">🐾</div>
                        <h3>${pawstarsData.strings.loginRequired}</h3>
                        <p>Accedi per votare i tuoi cani preferiti!</p>
                        <a href="${loginUrl}" class="btn btn-primary">Accedi</a>
                    </div>
                </div>
            `);

            $('body').append($modal);

            $modal.on('click', function(e) {
                if ($(e.target).hasClass('pawstars-modal') || $(e.target).hasClass('modal-close')) {
                    $modal.remove();
                }
            });
        },

        /**
         * Vote via swipe
         */
        swipeVote: function(dogId, reaction) {
            if (!pawstarsData.isLoggedIn && reaction !== 'pass') {
                return;
            }

            if (reaction === 'pass') {
                return; // Just skip, no vote
            }

            // Silent vote (no UI feedback)
            $.ajax({
                url: pawstarsData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pawstars_vote',
                    nonce: pawstarsData.nonce,
                    dog_id: dogId,
                    reaction: reaction
                },
                success: function(response) {
                    if (response.success) {
                        $(document).trigger('pawstars:swipe:voted', {
                            dogId: dogId,
                            reaction: reaction
                        });
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PawStarsVoting.init();
    });

})(jQuery);
