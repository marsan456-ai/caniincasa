/**
 * Main JavaScript
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Lazy Loading Images (fallback if not supported natively)
         */
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading is supported
            const images = document.querySelectorAll('img.lazy');
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const lazyImages = document.querySelectorAll('img.lazy');

            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const image = entry.target;
                            image.src = image.dataset.src || image.src;
                            image.classList.remove('lazy');
                            imageObserver.unobserve(image);
                        }
                    });
                });

                lazyImages.forEach(function(image) {
                    imageObserver.observe(image);
                });
            } else {
                // Very basic fallback
                lazyImages.forEach(function(image) {
                    image.src = image.dataset.src || image.src;
                });
            }
        }

        /**
         * AJAX Form Handlers
         */
        $('.ajax-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            const formData = new FormData(this);

            // Add action
            formData.append('action', $form.data('action'));
            formData.append('nonce', caniincasaAjax.nonce);

            // Disable submit button
            $submitBtn.prop('disabled', true).addClass('loading');

            $.ajax({
                url: caniincasaAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showMessage($form, response.data.message, 'success');
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1000);
                        } else {
                            $form[0].reset();
                        }
                    } else {
                        showMessage($form, response.data.message || 'Si è verificato un errore.', 'error');
                    }
                },
                error: function() {
                    showMessage($form, 'Si è verificato un errore. Riprova.', 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).removeClass('loading');
                }
            });
        });

        /**
         * Show Message Helper
         */
        function showMessage($context, message, type) {
            const $existingMessage = $context.find('.form-message');
            if ($existingMessage.length) {
                $existingMessage.remove();
            }

            const $message = $('<div class="form-message message-' + type + '">' + message + '</div>');
            $context.prepend($message);

            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Back to Top Button
         */
        const $backToTop = $('<button class="back-to-top" aria-label="Torna su"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 19V5M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>');
        $('body').append($backToTop);

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                $backToTop.addClass('visible');
            } else {
                $backToTop.removeClass('visible');
            }
        });

        $backToTop.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 600);
        });

        /**
         * Accordion (FAQ, etc.)
         */
        $('.accordion-item').each(function() {
            const $item = $(this);
            const $header = $item.find('.accordion-header');
            const $content = $item.find('.accordion-content');

            $header.on('click', function() {
                const isActive = $item.hasClass('active');

                // Close all other accordions (optional)
                if ($item.closest('.accordion').hasClass('single-open')) {
                    $item.siblings().removeClass('active').find('.accordion-content').slideUp();
                }

                // Toggle current
                if (isActive) {
                    $item.removeClass('active');
                    $content.slideUp();
                } else {
                    $item.addClass('active');
                    $content.slideDown();
                }
            });
        });

        /**
         * Tabs
         */
        $('.tabs-nav button').on('click', function() {
            const $tab = $(this);
            const target = $tab.data('tab');
            const $tabsContainer = $tab.closest('.tabs');

            // Update nav
            $tab.addClass('active').siblings().removeClass('active');

            // Update content
            $tabsContainer.find('.tab-pane').removeClass('active');
            $tabsContainer.find('[data-tab-content="' + target + '"]').addClass('active');
        });

        /**
         * Modal/Popup
         */
        $('[data-modal]').on('click', function(e) {
            e.preventDefault();
            const modalId = $(this).data('modal');
            $('#' + modalId).addClass('active');
            $('body').addClass('modal-open');
        });

        $('.modal-close, .modal-overlay').on('click', function() {
            $(this).closest('.modal').removeClass('active');
            $('body').removeClass('modal-open');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal.active').removeClass('active');
                $('body').removeClass('modal-open');
            }
        });

        /**
         * Copy to Clipboard
         */
        $('[data-copy]').on('click', function() {
            const text = $(this).data('copy');
            const $btn = $(this);

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopyFeedback($btn);
                });
            } else {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback($btn);
                } catch (err) {
                    console.error('Could not copy text: ', err);
                }
                document.body.removeChild(textArea);
            }
        });

        function showCopyFeedback($btn) {
            const originalText = $btn.html();
            $btn.html('Copiato!').addClass('copied');
            setTimeout(function() {
                $btn.html(originalText).removeClass('copied');
            }, 2000);
        }

        /**
         * External Links - Open in New Tab
         */
        $('a[href^="http"]').not('[href*="' + window.location.hostname + '"]').attr('target', '_blank').attr('rel', 'noopener noreferrer');

        /**
         * Print Page
         */
        $('[data-print]').on('click', function(e) {
            e.preventDefault();
            window.print();
        });

        /**
         * Initialize tooltips (if using a tooltip library)
         */
        if (typeof tippy !== 'undefined') {
            tippy('[data-tooltip]', {
                content: function(reference) {
                    return reference.getAttribute('data-tooltip');
                },
            });
        }

        /**
         * Hero Background Carousel
         */
        const $heroSection = $('.hero-section');
        if ($heroSection.length && $heroSection.data('has-carousel') === true) {
            const $backgrounds = $('.hero-bg');
            const speed = parseInt($heroSection.data('carousel-speed'), 10) || 5;
            const totalImages = $backgrounds.length;
            let currentIndex = 0;

            if (totalImages > 1) {
                // Auto-rotate background images
                setInterval(function() {
                    // Remove active state from current
                    $backgrounds.eq(currentIndex).attr('data-active', 'false');

                    // Move to next image
                    currentIndex = (currentIndex + 1) % totalImages;

                    // Set active state to next
                    $backgrounds.eq(currentIndex).attr('data-active', 'true');
                }, speed * 1000);
            }
        }

    });

    /**
     * Window Load Events
     */
    $(window).on('load', function() {
        // Remove loading class from body
        $('body').removeClass('loading');

        // Trigger resize to fix any layout issues
        $(window).trigger('resize');
    });

})(jQuery);
