/**
 * Caniincasa Core - Public JavaScript
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * AJAX Filters Handler
	 */
	const AJAXFilters = {
		form: null,
		container: null,

		init: function() {
			this.form = $('.caniincasa-filters form');
			this.container = $('.caniincasa-results');

			if (!this.form.length || !this.container.length) {
				return;
			}

			this.bindEvents();
		},

		bindEvents: function() {
			const self = this;

			// Filter change
			this.form.on('change', 'select, input[type="checkbox"], input[type="radio"]', function() {
				self.applyFilters();
			});

			// Search submit
			this.form.on('submit', function(e) {
				e.preventDefault();
				self.applyFilters();
			});

			// Reset button
			this.form.on('click', '.reset-filters', function(e) {
				e.preventDefault();
				self.resetFilters();
			});
		},

		applyFilters: function() {
			const self = this;
			const formData = this.form.serialize();

			// Show loading
			this.container.css('opacity', '0.5');

			$.ajax({
				url: caniincasaCore.ajaxurl,
				type: 'POST',
				data: formData + '&action=caniincasa_filter_posts&nonce=' + caniincasaCore.nonce,
				success: function(response) {
					if (response.success) {
						self.container.html(response.data.html);
						self.updateResultsCount(response.data.count);
					} else {
						alert(caniincasaCore.strings.error);
					}
					self.container.css('opacity', '1');
				},
				error: function() {
					alert(caniincasaCore.strings.error);
					self.container.css('opacity', '1');
				}
			});
		},

		resetFilters: function() {
			this.form[0].reset();
			this.applyFilters();
		},

		updateResultsCount: function(count) {
			$('.caniincasa-results-count').text(count + ' risultati trovati');
		}
	};

	/**
	 * Compare Razze Handler
	 */
	const CompareRazze = {
		selected: [],
		maxCompare: 3,

		init: function() {
			this.bindEvents();
			this.loadSaved();
		},

		bindEvents: function() {
			const self = this;

			// Add to compare
			$('.add-to-compare').on('click', function(e) {
				e.preventDefault();
				const razzaId = $(this).data('razza-id');
				self.toggleCompare(razzaId, $(this));
			});

			// View comparison
			$('.view-comparison').on('click', function(e) {
				e.preventDefault();
				self.viewComparison();
			});

			// Clear comparison
			$('.clear-comparison').on('click', function(e) {
				e.preventDefault();
				self.clearComparison();
			});
		},

		toggleCompare: function(razzaId, button) {
			const index = this.selected.indexOf(razzaId);

			if (index > -1) {
				// Remove
				this.selected.splice(index, 1);
				button.removeClass('selected').text('Aggiungi al confronto');
			} else {
				// Add
				if (this.selected.length >= this.maxCompare) {
					alert('Puoi confrontare massimo ' + this.maxCompare + ' razze');
					return;
				}
				this.selected.push(razzaId);
				button.addClass('selected').text('Rimuovi dal confronto');
			}

			this.saveToStorage();
			this.updateCounter();
		},

		viewComparison: function() {
			if (this.selected.length < 2) {
				alert('Seleziona almeno 2 razze da confrontare');
				return;
			}

			// Redirect to comparison page
			window.location.href = '/confronta-razze?ids=' + this.selected.join(',');
		},

		clearComparison: function() {
			this.selected = [];
			$('.add-to-compare').removeClass('selected').text('Aggiungi al confronto');
			this.saveToStorage();
			this.updateCounter();
		},

		updateCounter: function() {
			$('.compare-counter').text(this.selected.length);
			if (this.selected.length > 0) {
				$('.compare-bar').fadeIn();
			} else {
				$('.compare-bar').fadeOut();
			}
		},

		saveToStorage: function() {
			localStorage.setItem('caniincasa_compare', JSON.stringify(this.selected));
		},

		loadSaved: function() {
			const saved = localStorage.getItem('caniincasa_compare');
			if (saved) {
				this.selected = JSON.parse(saved);
				this.updateCounter();

				// Update button states
				const self = this;
				this.selected.forEach(function(id) {
					$('.add-to-compare[data-razza-id="' + id + '"]')
						.addClass('selected')
						.text('Rimuovi dal confronto');
				});
			}
		}
	};

	/**
	 * Favorites Handler
	 */
	const Favorites = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			const self = this;

			$('.add-to-favorites').on('click', function(e) {
				e.preventDefault();
				const postId = $(this).data('post-id');
				const button = $(this);

				self.toggleFavorite(postId, button);
			});
		},

		toggleFavorite: function(postId, button) {
			$.ajax({
				url: caniincasaCore.ajaxurl,
				type: 'POST',
				data: {
					action: 'caniincasa_toggle_favorite',
					nonce: caniincasaCore.nonce,
					post_id: postId
				},
				success: function(response) {
					if (response.success) {
						if (response.data.added) {
							button.addClass('favorited').html('★ Nei preferiti');
						} else {
							button.removeClass('favorited').html('☆ Aggiungi ai preferiti');
						}
					} else {
						alert(response.data.message || caniincasaCore.strings.error);
					}
				},
				error: function() {
					alert(caniincasaCore.strings.error);
				}
			});
		}
	};

	/**
	 * Copy to Clipboard
	 */
	const CopyToClipboard = {
		init: function() {
			$('.copy-to-clipboard').on('click', function(e) {
				e.preventDefault();
				const text = $(this).data('text');

				if (navigator.clipboard) {
					navigator.clipboard.writeText(text).then(function() {
						alert('Copiato negli appunti!');
					});
				} else {
					// Fallback
					const input = $('<input>');
					$('body').append(input);
					input.val(text).select();
					document.execCommand('copy');
					input.remove();
					alert('Copiato negli appunti!');
				}
			});
		}
	};

	/**
	 * Smooth Scroll
	 */
	const SmoothScroll = {
		init: function() {
			$('a[href^="#"]').on('click', function(e) {
				const target = $(this.getAttribute('href'));
				if (target.length) {
					e.preventDefault();
					$('html, body').animate({
						scrollTop: target.offset().top - 100
					}, 800);
				}
			});
		}
	};

	/**
	 * Document Ready
	 */
	$(document).ready(function() {
		AJAXFilters.init();
		CompareRazze.init();
		Favorites.init();
		CopyToClipboard.init();
		SmoothScroll.init();
	});

})(jQuery);
