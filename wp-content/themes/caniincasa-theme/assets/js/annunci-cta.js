/**
 * Annunci CTA & Auth Modal Handler
 *
 * @package Caniincasa
 * @since 1.0.1
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const modal = $('#annuncio-registration-modal');
        const openBtn = $('#open-annuncio-modal');
        const closeBtn = $('#close-annuncio-modal');
        const overlay = modal.find('.auth-modal-overlay');

        /**
         * Open Modal
         */
        openBtn.on('click', function(e) {
            e.preventDefault();
            modal.fadeIn(300);
            $('body').css('overflow', 'hidden'); // Prevent background scroll
        });

        /**
         * Close Modal
         */
        function closeModal() {
            modal.fadeOut(300);
            $('body').css('overflow', ''); // Restore scroll
        }

        closeBtn.on('click', function(e) {
            e.preventDefault();
            closeModal();
        });

        overlay.on('click', function() {
            closeModal();
        });

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && modal.is(':visible')) {
                closeModal();
            }
        });

        /**
         * Tab Switching
         */
        $('.auth-tab').on('click', function() {
            const tabName = $(this).data('tab');

            // Update active tab button
            $('.auth-tab').removeClass('active');
            $(this).addClass('active');

            // Update active tab content
            $('.auth-tab-content').removeClass('active');
            $('#tab-' + tabName).addClass('active');
        });
    });

})(jQuery);
