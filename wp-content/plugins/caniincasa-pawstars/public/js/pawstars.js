/**
 * Paw Stars - Main JavaScript
 *
 * @package Pawstars
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Main Paw Stars object
    window.PawStars = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initViewToggle();
            this.initPhotoUpload();
            this.initCreateForm();
            this.initDashboard();
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            // Filter form auto-submit on select change
            $('.pawstars-filter-form select').on('change', function() {
                $(this).closest('form').submit();
            });

            // Bio character counter
            $('#dog_bio, #create_bio').on('input', function() {
                const count = $(this).val().length;
                $('#bioCharCount').text(count);
            });
        },

        /**
         * Initialize view toggle (grid/swipe)
         */
        initViewToggle: function() {
            const $toggle = $('.view-toggle');
            if (!$toggle.length) return;

            // Load saved preference (with try-catch for incognito/private mode)
            let savedView = this.isMobile() ? 'swipe' : 'grid';
            try {
                const stored = localStorage.getItem('pawstars_view');
                if (stored) savedView = stored;
            } catch (e) {
                // localStorage not available (incognito mode)
            }
            this.setView(savedView);

            $toggle.on('click', '.view-btn', function() {
                const view = $(this).data('view');
                PawStars.setView(view);
                try {
                    localStorage.setItem('pawstars_view', view);
                } catch (e) {
                    // localStorage not available
                }
            });
        },

        /**
         * Set active view
         */
        setView: function(view) {
            $('.view-btn').removeClass('active');
            $(`.view-btn[data-view="${view}"]`).addClass('active');

            $('.pawstars-feed').addClass('hidden');
            $(`.pawstars-feed[data-view="${view}"]`).removeClass('hidden');
        },

        /**
         * Check if mobile device
         */
        isMobile: function() {
            return window.innerWidth < 768;
        },

        /**
         * Initialize photo upload
         */
        initPhotoUpload: function() {
            const $zone = $('.photo-upload-zone, .photo-upload-area');
            if (!$zone.length) return;

            $zone.each(function() {
                const $this = $(this);
                const $input = $this.find('input[type="file"]');
                const $content = $this.find('.upload-content, .upload-placeholder');
                const $preview = $this.find('.upload-preview');

                // Click to upload
                $this.on('click', function(e) {
                    if (!$(e.target).hasClass('remove-preview') && !$(e.target).closest('.remove-preview').length) {
                        $input.trigger('click');
                    }
                });

                // Drag and drop
                $this.on('dragover dragenter', function(e) {
                    e.preventDefault();
                    $this.addClass('dragover');
                });

                $this.on('dragleave drop', function(e) {
                    e.preventDefault();
                    $this.removeClass('dragover');
                });

                // Handle file processing (shared between input change and drop)
                function processFile(file, $zone) {
                    if (!file) return;

                    // Validate
                    if (!PawStars.validateFile(file)) return;

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $preview.find('img').attr('src', e.target.result);
                        $content.addClass('hidden');
                        $preview.addClass('active').removeClass('hidden');
                    };
                    reader.readAsDataURL(file);

                    // Upload to server with progress indicator
                    PawStars.uploadPhoto(file, function(response) {
                        if (response.success) {
                            $zone.find('input[name="featured_image_id"]').val(response.data.image_id);
                            PawStars.toast('Foto caricata!', 'success');
                        }
                    }, $zone);
                }

                $this.on('drop', function(e) {
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length) {
                        // Process file directly instead of assigning to input (files is read-only)
                        processFile(files[0], $this);
                    }
                });

                // File selected via input
                $input.on('change', function() {
                    processFile(this.files[0], $this);
                });

                // Remove preview
                $this.find('.remove-preview, .remove-photo').on('click', function(e) {
                    e.stopPropagation();
                    $input.val('');
                    $preview.removeClass('active').addClass('hidden');
                    $content.removeClass('hidden');
                    $this.find('input[name="featured_image_id"]').val('');
                });
            });
        },

        /**
         * Validate file
         */
        validateFile: function(file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (file.size > maxSize) {
                this.toast(pawstarsData.strings.fileTooLarge, 'error');
                return false;
            }

            if (!allowedTypes.includes(file.type)) {
                this.toast(pawstarsData.strings.invalidFormat, 'error');
                return false;
            }

            return true;
        },

        /**
         * Upload photo to server with progress
         */
        uploadPhoto: function(file, callback, $progressContainer) {
            const formData = new FormData();
            formData.append('action', 'pawstars_upload_photo');
            formData.append('nonce', pawstarsData.nonce);
            formData.append('photo', file);

            // Show progress bar if container provided
            let $progressBar = null;
            if ($progressContainer && $progressContainer.length) {
                $progressBar = $('<div class="upload-progress"><div class="upload-progress-bar"></div></div>');
                $progressContainer.append($progressBar);
            }

            $.ajax({
                url: pawstarsData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable && $progressBar) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            $progressBar.find('.upload-progress-bar').css('width', percent + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if ($progressBar) $progressBar.remove();
                    if (response.success) {
                        callback(response);
                    } else {
                        PawStars.toast(response.data.message || pawstarsData.strings.uploadError, 'error');
                    }
                },
                error: function() {
                    if ($progressBar) $progressBar.remove();
                    PawStars.toast(pawstarsData.strings.uploadError, 'error');
                }
            });
        },

        /**
         * Initialize create form
         */
        initCreateForm: function() {
            const $form = $('#pawstarsCreateProfile, #pawstarsCreateDogForm');
            if (!$form.length) return;

            $form.on('submit', function(e) {
                e.preventDefault();
                PawStars.submitCreateForm($(this));
            });
        },

        /**
         * Submit create form
         */
        submitCreateForm: function($form) {
            const $submit = $form.find('[type="submit"]');
            const originalText = $submit.text();

            $submit.prop('disabled', true).text(pawstarsData.strings.loading);

            const formData = new FormData($form[0]);
            formData.append('action', 'pawstars_create_dog');
            formData.append('nonce', pawstarsData.nonce);

            $.ajax({
                url: pawstarsData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        PawStars.toast(response.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        PawStars.toast(response.data.message, 'error');
                        $submit.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    PawStars.toast(pawstarsData.strings.error, 'error');
                    $submit.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Initialize dashboard
         */
        initDashboard: function() {
            // Show/hide create form
            $('#showCreateForm').on('click', function() {
                $('#createDogForm').removeClass('hidden');
                $(this).addClass('hidden');
            });

            $('#cancelCreate').on('click', function() {
                $('#createDogForm').addClass('hidden');
                $('#showCreateForm').removeClass('hidden');
            });

            // Delete dog with modal confirmation
            $('.btn-delete').on('click', function() {
                const dogId = $(this).data('dog-id');
                const dogName = $(this).data('dog-name') || 'questo profilo';
                const $item = $(this).closest('.my-dog-item');

                PawStars.confirmModal({
                    title: 'Conferma eliminazione',
                    message: 'Sei sicuro di voler eliminare <strong>' + $('<div>').text(dogName).html() + '</strong>? Questa azione non può essere annullata.',
                    confirmText: 'Elimina',
                    cancelText: 'Annulla',
                    confirmClass: 'btn-danger',
                    onConfirm: function() {
                        $.ajax({
                            url: pawstarsData.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'pawstars_delete_dog',
                                nonce: pawstarsData.nonce,
                                dog_id: dogId
                            },
                            success: function(response) {
                                if (response.success) {
                                    $item.fadeOut(function() {
                                        $(this).remove();
                                    });
                                    PawStars.toast(response.data.message, 'success');
                                } else {
                                    PawStars.toast(response.data.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
        },

        /**
         * Show confirmation modal
         */
        confirmModal: function(options) {
            const defaults = {
                title: 'Conferma',
                message: 'Sei sicuro?',
                confirmText: 'Conferma',
                cancelText: 'Annulla',
                confirmClass: 'btn-primary',
                onConfirm: function() {},
                onCancel: function() {}
            };

            const settings = $.extend({}, defaults, options);

            // Remove existing modal
            $('#pawstarsConfirmModal').remove();

            // Create modal HTML
            const modalHtml = `
                <div class="pawstars-modal-overlay" id="pawstarsConfirmModal">
                    <div class="pawstars-modal">
                        <div class="pawstars-modal-header">
                            <h3>${$('<div>').text(settings.title).html()}</h3>
                            <button type="button" class="pawstars-modal-close">&times;</button>
                        </div>
                        <div class="pawstars-modal-body">
                            <p>${settings.message}</p>
                        </div>
                        <div class="pawstars-modal-footer">
                            <button type="button" class="btn btn-secondary pawstars-modal-cancel">${$('<div>').text(settings.cancelText).html()}</button>
                            <button type="button" class="btn ${settings.confirmClass} pawstars-modal-confirm">${$('<div>').text(settings.confirmText).html()}</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            const $modal = $('#pawstarsConfirmModal');

            // Show with animation
            setTimeout(function() {
                $modal.addClass('active');
            }, 10);

            // Close handlers
            function closeModal() {
                $modal.removeClass('active');
                setTimeout(function() {
                    $modal.remove();
                }, 300);
            }

            $modal.find('.pawstars-modal-close, .pawstars-modal-cancel').on('click', function() {
                closeModal();
                settings.onCancel();
            });

            $modal.find('.pawstars-modal-confirm').on('click', function() {
                closeModal();
                settings.onConfirm();
            });

            // Close on overlay click
            $modal.on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                    settings.onCancel();
                }
            });

            // Close on escape
            $(document).on('keydown.pawstarsModal', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    settings.onCancel();
                    $(document).off('keydown.pawstarsModal');
                }
            });
        },

        /**
         * Show toast notification
         */
        toast: function(message, type = 'info') {
            // Sanitize type to prevent XSS
            const safeType = ['info', 'success', 'error', 'warning'].includes(type) ? type : 'info';
            const $toast = $('<div>')
                .addClass('pawstars-toast')
                .addClass(safeType)
                .text(message);
            $('body').append($toast);

            setTimeout(function() {
                $toast.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        },

        /**
         * API request helper
         */
        api: function(endpoint, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': pawstarsData.restNonce
                }
            };

            const settings = Object.assign({}, defaults, options);

            return fetch(pawstarsData.restUrl + endpoint, settings)
                .then(response => response.json());
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PawStars.init();
    });

})(jQuery);
