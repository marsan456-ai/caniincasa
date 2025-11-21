/**
 * Paw Stars - Infinite Scroll / Load More
 *
 * @package Pawstars
 * @since 1.0.0
 */

(function($) {
    'use strict';

    window.PawStarsInfiniteScroll = {

        page: 1,
        loading: false,
        hasMore: true,
        perPage: 12,

        /**
         * Initialize
         */
        init: function() {
            this.$container = $('.pawstars-grid');
            this.$loadMore = $('.pawstars-load-more');
            this.$button = $('#loadMoreDogs');
            this.$spinner = this.$loadMore.find('.loading-spinner');

            if (!this.$container.length) return;

            // Get initial state
            this.page = parseInt(this.$loadMore.data('page')) || 1;
            this.total = parseInt(this.$loadMore.data('total')) || 0;
            this.perPage = parseInt(new URLSearchParams(window.location.search).get('per_page')) || 12;

            this.checkHasMore();
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Load more button
            this.$button.on('click', function() {
                self.loadMore();
            });

            // Optional: Intersection Observer for auto-load
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    if (entries[0].isIntersecting && !self.loading && self.hasMore) {
                        // Uncomment to enable auto-load on scroll
                        // self.loadMore();
                    }
                }, {
                    rootMargin: '100px'
                });

                if (this.$loadMore.length) {
                    observer.observe(this.$loadMore[0]);
                }
            }
        },

        /**
         * Load more dogs
         */
        loadMore: function() {
            if (this.loading || !this.hasMore) return;

            this.loading = true;
            this.$button.addClass('hidden');
            this.$spinner.removeClass('hidden');

            const self = this;
            const params = new URLSearchParams(window.location.search);

            // Build query params
            const queryParams = {
                page: this.page + 1,
                per_page: this.perPage,
                breed: params.get('breed') || '',
                provincia: params.get('provincia') || '',
                orderby: params.get('orderby') || 'created_at',
                search: params.get('search') || ''
            };

            // Collect existing dog IDs to exclude
            const existingIds = [];
            this.$container.find('.pawstars-dog-card').each(function() {
                existingIds.push($(this).data('dog-id'));
            });
            queryParams.exclude = existingIds.join(',');

            // Build query string
            const queryString = Object.keys(queryParams)
                .filter(key => queryParams[key])
                .map(key => `${key}=${encodeURIComponent(queryParams[key])}`)
                .join('&');

            // Fetch from REST API
            fetch(`${pawstarsData.restUrl}dogs?${queryString}`, {
                headers: {
                    'X-WP-Nonce': pawstarsData.restNonce
                }
            })
            .then(response => {
                // Get total from headers
                const total = parseInt(response.headers.get('X-WP-Total')) || 0;
                self.total = total;
                return response.json();
            })
            .then(dogs => {
                if (dogs.length > 0) {
                    self.appendDogs(dogs);
                    self.page++;
                }

                self.checkHasMore();
                self.loading = false;
                self.$spinner.addClass('hidden');

                if (self.hasMore) {
                    self.$button.removeClass('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading dogs:', error);
                self.loading = false;
                self.$spinner.addClass('hidden');
                self.$button.removeClass('hidden');
                PawStars.toast(pawstarsData.strings.error, 'error');
            });
        },

        /**
         * Append dogs to grid
         */
        appendDogs: function(dogs) {
            const self = this;

            dogs.forEach(function(dog) {
                const $card = self.createDogCard(dog);
                self.$container.append($card);
            });

            // Re-initialize any needed functionality
            $(document).trigger('pawstars:dogs:appended', { dogs: dogs });
        },

        /**
         * Create dog card HTML
         */
        createDogCard: function(dog) {
            const imageHtml = dog.image_url
                ? `<img src="${dog.image_url}" alt="${this.escapeHtml(dog.name)}" loading="lazy">`
                : '<div class="no-image-placeholder"><span>🐕</span></div>';

            const breedHtml = dog.breed_name
                ? `<span class="meta-breed">${this.escapeHtml(dog.breed_name)}</span>`
                : '';

            const provinciaHtml = dog.provincia
                ? `<span class="meta-location">📍 ${this.escapeHtml(dog.provincia)}</span>`
                : '';

            return $(`
                <div class="pawstars-dog-card" data-dog-id="${dog.id}">
                    <a href="?dog=${dog.id}" class="dog-card-link">
                        <div class="dog-card-image">
                            ${imageHtml}
                        </div>
                        <div class="dog-card-content">
                            <h3 class="dog-name">${this.escapeHtml(dog.name)}</h3>
                            <div class="dog-meta">
                                ${breedHtml}
                                ${provinciaHtml}
                            </div>
                            <div class="dog-stats">
                                <span class="stat-points">⭐ ${dog.total_points.toLocaleString()}</span>
                            </div>
                        </div>
                    </a>
                    <div class="dog-card-actions">
                        <div class="pawstars-reactions" data-dog-id="${dog.id}">
                            <button class="reaction-btn" data-reaction="love" title="Love">
                                <span class="reaction-emoji">❤️</span>
                                <span class="reaction-count">0</span>
                            </button>
                            <button class="reaction-btn" data-reaction="adorable" title="Adorable">
                                <span class="reaction-emoji">😍</span>
                                <span class="reaction-count">0</span>
                            </button>
                            <button class="reaction-btn reaction-star" data-reaction="star" title="Star">
                                <span class="reaction-emoji">⭐</span>
                                <span class="reaction-count">0</span>
                            </button>
                        </div>
                    </div>
                </div>
            `);
        },

        /**
         * Check if there are more dogs to load
         */
        checkHasMore: function() {
            const loadedCount = this.$container.find('.pawstars-dog-card').length;
            this.hasMore = loadedCount < this.total;

            if (!this.hasMore) {
                this.$loadMore.addClass('hidden');
            } else {
                this.$loadMore.removeClass('hidden');
            }
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PawStarsInfiniteScroll.init();
    });

})(jQuery);
