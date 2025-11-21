/**
 * Mega Menu System - JavaScript
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initMegaMenu();
    });

    /**
     * Initialize mega menu functionality
     */
    function initMegaMenu() {
        // Mobile accordion toggle
        if (window.innerWidth <= 1024) {
            setupMobileAccordion();
        }

        // Handle window resize
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth <= 1024) {
                    setupMobileAccordion();
                } else {
                    removeMobileAccordion();
                }
            }, 250);
        });

        // Process custom HTML mega menus
        processCustomMegaMenus();

        // Add hover intent for desktop (smoother UX)
        if (window.innerWidth > 1024) {
            setupHoverIntent();
        }
    }

    /**
     * Setup mobile accordion for mega menus
     */
    function setupMobileAccordion() {
        $('.menu-item[class*="mega-menu"]').each(function() {
            const $menuItem = $(this);
            const $link = $menuItem.children('a');
            const $submenu = $menuItem.children('.sub-menu');

            // Remove existing click handler
            $link.off('click.megamenu');

            // Add click handler
            $link.on('click.megamenu', function(e) {
                // Se ha un href valido e non è #, lascia navigare
                const href = $(this).attr('href');
                if (href && href !== '#' && href !== '') {
                    // Se il submenu è già aperto, lascia navigare
                    if ($menuItem.hasClass('active')) {
                        return true;
                    }
                }

                // Altrimenti previene e toggle
                e.preventDefault();
                e.stopPropagation();

                // Close other mega menus
                $('.menu-item[class*="mega-menu"]').not($menuItem).removeClass('active');

                // Toggle this mega menu
                $menuItem.toggleClass('active');

                // Smooth scroll to submenu if opened
                if ($menuItem.hasClass('active')) {
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $submenu.offset().top - 100
                        }, 300);
                    }, 50);
                }
            });
        });
    }

    /**
     * Remove mobile accordion handlers
     */
    function removeMobileAccordion() {
        $('.menu-item[class*="mega-menu"]').each(function() {
            const $menuItem = $(this);
            const $link = $menuItem.children('a');

            // Remove click handler
            $link.off('click.megamenu');

            // Remove active class
            $menuItem.removeClass('active');
        });
    }

    /**
     * Process custom HTML mega menus
     * Wraps content in proper container if needed
     */
    function processCustomMegaMenus() {
        $('.mega-menu-custom > .sub-menu').each(function() {
            const $submenu = $(this);

            // Check if content is already wrapped
            if ($submenu.find('.mega-menu-custom-content').length > 0) {
                return;
            }

            // Get all content
            const content = $submenu.html();

            // Check if it's already a mega-menu-content structure
            if (content.indexOf('mega-menu-content') !== -1) {
                // Just wrap in custom container
                $submenu.html('<div class="mega-menu-custom-content">' + content + '</div>');
            } else {
                // Wrap in both containers
                $submenu.html('<div class="mega-menu-custom-content"><div class="mega-menu-content">' + content + '</div></div>');
            }
        });
    }

    /**
     * Setup hover intent for smoother desktop experience
     * Prevents accidental mega menu triggers
     */
    function setupHoverIntent() {
        let hoverTimer;
        let activeMenuItem = null;

        $('.menu-item[class*="mega-menu"]').each(function() {
            const $menuItem = $(this);

            $menuItem.on('mouseenter', function() {
                clearTimeout(hoverTimer);

                hoverTimer = setTimeout(function() {
                    // Close other mega menus
                    if (activeMenuItem && activeMenuItem[0] !== $menuItem[0]) {
                        activeMenuItem.removeClass('mega-menu-active');
                    }

                    // Open this mega menu
                    $menuItem.addClass('mega-menu-active');
                    activeMenuItem = $menuItem;
                }, 150); // 150ms delay
            });

            $menuItem.on('mouseleave', function() {
                clearTimeout(hoverTimer);

                hoverTimer = setTimeout(function() {
                    $menuItem.removeClass('mega-menu-active');
                    if (activeMenuItem && activeMenuItem[0] === $menuItem[0]) {
                        activeMenuItem = null;
                    }
                }, 200); // 200ms delay before closing
            });
        });

        // Close all mega menus when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.menu-item[class*="mega-menu"]').length) {
                $('.menu-item[class*="mega-menu"]').removeClass('mega-menu-active');
                activeMenuItem = null;
            }
        });
    }

    /**
     * Dynamic counter update (optional feature)
     * Updates count badges with real data
     */
    window.updateMegaMenuCounts = function(counts) {
        if (!counts || typeof counts !== 'object') {
            return;
        }

        Object.keys(counts).forEach(function(selector) {
            $(selector + ' .count').text(counts[selector]);
        });
    };

    /**
     * Add featured breed dynamically (optional feature)
     */
    window.addMegaMenuFeaturedBreed = function(data) {
        const html = `
            <div class="mega-menu-featured">
                <h4>${data.title || 'In Evidenza'}</h4>
                <div class="featured-breed">
                    <img src="${data.image}" alt="${data.name}">
                    <h5>${data.name}</h5>
                    <p>${data.description}</p>
                    <a href="${data.url}" class="btn">${data.buttonText || 'Scopri di più'}</a>
                </div>
            </div>
        `;

        $('.mega-menu-content').append(html);
    };

})(jQuery);
