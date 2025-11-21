/**
 * Paw Stars - Swipe Cards
 *
 * @package Pawstars
 * @since 1.0.0
 */

(function($) {
    'use strict';

    window.PawStarsSwipe = {

        cards: [],
        currentIndex: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        currentX: 0,
        currentY: 0,
        threshold: 100,

        /**
         * Initialize swipe cards
         */
        init: function() {
            this.$container = $('.swipe-cards');
            if (!this.$container.length) return;

            this.cards = this.$container.find('.swipe-card').toArray();
            this.bindEvents();
            this.updateCardStates();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Touch events
            this.$container.on('touchstart', '.swipe-card.active', function(e) {
                self.onDragStart(e.originalEvent.touches[0]);
            });

            $(document).on('touchmove', function(e) {
                if (self.isDragging) {
                    e.preventDefault();
                    self.onDragMove(e.originalEvent.touches[0]);
                }
            });

            $(document).on('touchend', function(e) {
                if (self.isDragging) {
                    self.onDragEnd();
                }
            });

            // Mouse events (for desktop testing)
            this.$container.on('mousedown', '.swipe-card.active', function(e) {
                e.preventDefault();
                self.onDragStart(e);
            });

            $(document).on('mousemove', function(e) {
                if (self.isDragging) {
                    self.onDragMove(e);
                }
            });

            $(document).on('mouseup', function() {
                if (self.isDragging) {
                    self.onDragEnd();
                }
            });

            // Action buttons
            $('.swipe-actions').on('click', '.swipe-action-btn', function() {
                const action = $(this).data('action');
                self.performAction(action);
            });

            // Reload button
            $('#reloadSwipe').on('click', function() {
                location.reload();
            });
        },

        /**
         * Handle drag start
         */
        onDragStart: function(e) {
            this.isDragging = true;
            this.startX = e.clientX || e.pageX;
            this.startY = e.clientY || e.pageY;
            this.$activeCard = $(this.cards[this.currentIndex]);
            this.$activeCard.css('transition', 'none');
        },

        /**
         * Handle drag move
         */
        onDragMove: function(e) {
            if (!this.isDragging) return;

            this.currentX = (e.clientX || e.pageX) - this.startX;
            this.currentY = (e.clientY || e.pageY) - this.startY;

            const rotation = this.currentX * 0.1;
            const scale = 1 - Math.abs(this.currentX) / 2000;

            this.$activeCard.css('transform', `translateX(${this.currentX}px) translateY(${this.currentY}px) rotate(${rotation}deg) scale(${scale})`);

            // Show overlay hints
            if (this.currentX > 50) {
                this.$activeCard.addClass('swiping-right').removeClass('swiping-left');
            } else if (this.currentX < -50) {
                this.$activeCard.addClass('swiping-left').removeClass('swiping-right');
            } else {
                this.$activeCard.removeClass('swiping-left swiping-right');
            }
        },

        /**
         * Handle drag end
         */
        onDragEnd: function() {
            this.isDragging = false;

            this.$activeCard.css('transition', '');
            this.$activeCard.removeClass('swiping-left swiping-right');

            if (Math.abs(this.currentX) > this.threshold) {
                // Swipe completed
                const direction = this.currentX > 0 ? 'right' : 'left';
                this.swipeCard(direction);
            } else {
                // Return to center
                this.$activeCard.css('transform', '');
            }

            this.currentX = 0;
            this.currentY = 0;
        },

        /**
         * Swipe card in direction
         */
        swipeCard: function(direction) {
            const $card = $(this.cards[this.currentIndex]);
            const dogId = $card.data('dog-id');
            const reaction = direction === 'right' ? 'love' : 'pass';

            // Add exit animation
            $card.addClass(direction === 'right' ? 'swipe-exit-right' : 'swipe-exit-left');

            // Vote
            if (reaction !== 'pass') {
                PawStarsVoting.swipeVote(dogId, reaction);
            }

            // Move to next card
            setTimeout(() => {
                this.currentIndex++;
                this.updateCardStates();
            }, 400);
        },

        /**
         * Perform action from button
         */
        performAction: function(action) {
            if (this.currentIndex >= this.cards.length) return;

            const $card = $(this.cards[this.currentIndex]);
            const dogId = $card.data('dog-id');

            let animationClass = '';
            let reaction = '';

            switch (action) {
                case 'love':
                    animationClass = 'swipe-exit-right';
                    reaction = 'love';
                    break;
                case 'star':
                    animationClass = 'swipe-exit-up';
                    reaction = 'star';
                    break;
                case 'pass':
                    animationClass = 'swipe-exit-left';
                    reaction = 'pass';
                    break;
            }

            $card.addClass(animationClass);

            if (reaction !== 'pass') {
                PawStarsVoting.swipeVote(dogId, reaction);
            }

            setTimeout(() => {
                this.currentIndex++;
                this.updateCardStates();
            }, 400);
        },

        /**
         * Update card states
         */
        updateCardStates: function() {
            const cards = this.cards;
            const current = this.currentIndex;

            // Check if no more cards
            if (current >= cards.length) {
                this.$container.addClass('hidden');
                $('.swipe-actions').addClass('hidden');
                $('.swipe-empty').removeClass('hidden');
                return;
            }

            // Update classes
            cards.forEach((card, index) => {
                const $card = $(card);
                $card.removeClass('active next third swipe-exit-left swipe-exit-right swipe-exit-up');
                $card.css('transform', '');

                if (index === current) {
                    $card.addClass('active');
                } else if (index === current + 1) {
                    $card.addClass('next');
                } else if (index === current + 2) {
                    $card.addClass('third');
                }
            });
        },

        /**
         * Reset cards
         */
        reset: function() {
            this.currentIndex = 0;
            this.cards.forEach(card => {
                $(card).removeClass('swipe-exit-left swipe-exit-right swipe-exit-up');
            });
            this.$container.removeClass('hidden');
            $('.swipe-actions').removeClass('hidden');
            $('.swipe-empty').addClass('hidden');
            this.updateCardStates();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PawStarsSwipe.init();
    });

})(jQuery);
