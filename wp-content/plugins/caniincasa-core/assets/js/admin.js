/**
 * Caniincasa Core - Admin JavaScript
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * CSV Import Handler
	 */
	const CSVImport = {
		form: null,
		progressCard: null,
		currentImport: null,
		isImporting: false,

		/**
		 * Initialize
		 */
		init: function() {
			this.form = $('#caniincasa-import-form');
			this.progressCard = $('#import-progress-card');

			if (!this.form.length) {
				return;
			}

			this.bindEvents();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			const self = this;

			// Form submit
			this.form.on('submit', function(e) {
				e.preventDefault();
				self.startImport();
			});

			// Use file button clicks
			$('.use-file-btn').on('click', function(e) {
				e.preventDefault();
				const filePath = $(this).data('file');
				const fileType = $(this).data('type');

				// Set hidden input for file path
				if (!$('#import-file-path').length) {
					self.form.append('<input type="hidden" id="import-file-path" name="file_path" />');
				}
				$('#import-file-path').val(filePath);

				// Set import type
				$('#import-type').val(fileType);

				// Visual feedback
				$('.use-file-btn').removeClass('button-primary');
				$(this).addClass('button-primary');

				// Disable file input
				$('#csv-file').prop('disabled', true).val('');

				alert('File selezionato: ' + $(this).text().trim());
			});

			// File input change
			$('#csv-file').on('change', function() {
				if ($(this).val()) {
					$('.use-file-btn').removeClass('button-primary');
					$('#import-file-path').remove();
				}
			});
		},

		/**
		 * Start import process
		 */
		startImport: function() {
			if (this.isImporting) {
				alert('Importazione già in corso!');
				return;
			}

			const importType = $('#import-type').val();
			const filePath = $('#import-file-path').val();
			const fileInput = $('#csv-file')[0];
			const batchSize = parseInt($('#batch-size').val()) || 10;

			// Validation
			if (!importType) {
				alert('Seleziona il tipo di dati da importare');
				return;
			}

			if (!filePath && (!fileInput.files || !fileInput.files.length)) {
				alert('Seleziona un file CSV o usa uno dei file disponibili');
				return;
			}

			// Show progress card
			this.progressCard.fadeIn();
			$('#start-import-btn').prop('disabled', true).text('Importazione in corso...');

			this.isImporting = true;
			this.currentImport = {
				type: importType,
				filePath: filePath,
				batchSize: batchSize,
				offset: 0,
				totalProcessed: 0,
				totalImported: 0,
				totalUpdated: 0,
				totalSkipped: 0,
				errors: []
			};

			// If uploaded file, handle upload first
			if (fileInput.files && fileInput.files.length) {
				this.uploadAndImport(fileInput.files[0]);
			} else {
				this.processBatch();
			}
		},

		/**
		 * Upload CSV file and start import
		 */
		uploadAndImport: function(file) {
			const self = this;
			const formData = new FormData();
			formData.append('action', 'caniincasa_upload_csv');
			formData.append('nonce', caniincasaCoreAdmin.nonce);
			formData.append('csv_file', file);

			$.ajax({
				url: caniincasaCoreAdmin.ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						self.currentImport.filePath = response.data.file_path;
						self.processBatch();
					} else {
						self.importError(response.data.message || 'Errore durante l\'upload');
					}
				},
				error: function() {
					self.importError('Errore di connessione durante l\'upload');
				}
			});
		},

		/**
		 * Process single batch
		 */
		processBatch: function() {
			const self = this;

			$('#import-status').text('Importazione in corso...');

			$.ajax({
				url: caniincasaCoreAdmin.ajaxurl,
				type: 'POST',
				data: {
					action: 'caniincasa_import_csv',
					nonce: $('#caniincasa_import_nonce').val(),
					import_type: this.currentImport.type,
					file_path: this.currentImport.filePath,
					batch_size: this.currentImport.batchSize,
					offset: this.currentImport.offset
				},
				success: function(response) {
					if (response.success) {
						self.updateProgress(response.data);

						if (response.data.completed) {
							self.importComplete();
						} else {
							// Continue with next batch
							self.currentImport.offset = response.data.next_offset;
							setTimeout(function() {
								self.processBatch();
							}, 500); // Small delay to prevent server overload
						}
					} else {
						self.importError(response.data.message || 'Errore durante l\'importazione');
					}
				},
				error: function(xhr, status, error) {
					self.importError('Errore di connessione: ' + error);
				}
			});
		},

		/**
		 * Update progress UI
		 */
		updateProgress: function(data) {
			// Update totals
			this.currentImport.totalProcessed += data.processed;
			this.currentImport.totalImported += data.imported;
			this.currentImport.totalUpdated += data.updated;
			this.currentImport.totalSkipped += data.skipped;

			// Merge errors
			if (data.errors && data.errors.length) {
				this.currentImport.errors = this.currentImport.errors.concat(data.errors);
			}

			// Calculate percentage
			const percentage = Math.round((this.currentImport.totalProcessed / data.total) * 100);

			// Update UI
			$('#import-progress-bar').css('width', percentage + '%');
			$('#progress-text').text(percentage + '%');
			$('#import-total').text(data.total);
			$('#import-imported').text(this.currentImport.totalImported);
			$('#import-updated').text(this.currentImport.totalUpdated);
			$('#import-skipped').text(this.currentImport.totalSkipped);

			// Show errors if any
			if (this.currentImport.errors.length > 0) {
				$('#import-log').show();
				let errorText = '';
				this.currentImport.errors.forEach(function(error) {
					errorText += error.title + ': ' + error.message + '\n';
				});
				$('#error-log').text(errorText);
			}
		},

		/**
		 * Import completed
		 */
		importComplete: function() {
			this.isImporting = false;
			$('#import-status').html('<span style="color: green;">✓ Completato</span>');
			$('#start-import-btn').prop('disabled', false).text('Avvia Nuova Importazione');

			// Show summary
			const summary = 'Importazione completata!\n\n' +
				'Totali: ' + this.currentImport.totalProcessed + '\n' +
				'Importati: ' + this.currentImport.totalImported + '\n' +
				'Aggiornati: ' + this.currentImport.totalUpdated + '\n' +
				'Saltati: ' + this.currentImport.totalSkipped;

			alert(summary);

			// Reset form
			this.form[0].reset();
			$('#import-file-path').remove();
			$('.use-file-btn').removeClass('button-primary');
			$('#csv-file').prop('disabled', false);
		},

		/**
		 * Handle import error
		 */
		importError: function(message) {
			this.isImporting = false;
			$('#import-status').html('<span style="color: red;">✗ Errore</span>');
			$('#start-import-btn').prop('disabled', false).text('Riprova');

			alert('Errore durante l\'importazione:\n' + message);
		}
	};

	/**
	 * Document ready
	 */
	$(document).ready(function() {
		CSVImport.init();
	});

})(jQuery);
