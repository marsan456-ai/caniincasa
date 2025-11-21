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

            // Load saved preference
            const savedView = localStorage.getItem('pawstars_view') || (this.isMobile() ? 'swipe' : 'grid');
            this.setView(savedView);

            $toggle.on('click', '.view-btn', function() {
                const view = $(this).data('view');
                PawStars.setView(view);
                localStorage.setItem('pawstars_view', view);
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

                $this.on('drop', function(e) {
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length) {
                        $input[0].files = files;
                        $input.trigger('change');
                    }
                });

                // File selected
                $input.on('change', function() {
                    const file = this.files[0];
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

                    // Upload to server
                    PawStars.uploadPhoto(file, function(response) {
                        if (response.success) {
                            $this.find('input[name="featured_image_id"]').val(response.data.image_id);
                        }
                    });
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
         * Upload photo to server
         */
        uploadPhoto: function(file, callback) {
            const formData = new FormData();
            formData.append('action', 'pawstars_upload_photo');
            formData.append('nonce', pawstarsData.nonce);
            formData.append('photo', file);

            $.ajax({
                url: pawstarsData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        callback(response);
                    } else {
                        PawStars.toast(response.data.message || pawstarsData.strings.uploadError, 'error');
                    }
                },
                error: function() {
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

            // Delete dog
            $('.btn-delete').on('click', function() {
                if (!confirm(pawstarsData.strings.confirmDelete)) return;

                const dogId = $(this).data('dog-id');
                const $item = $(this).closest('.my-dog-item');

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
            });
        },

        /**
         * Show toast notification
         */
        toast: function(message, type = 'info') {
            const $toast = $(`<div class="pawstars-toast ${type}">${message}</div>`);
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
