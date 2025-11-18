/**
 * Navigation Scripts
 *
 * @package Caniincasa
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        /**
         * Mobile Menu Toggle
         */
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
        const mobileNavClose = document.querySelector('.mobile-nav-close');
        const body = document.body;

        if (mobileMenuToggle && mobileNavOverlay) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileNavOverlay.classList.add('active');
                body.style.overflow = 'hidden';
                this.setAttribute('aria-expanded', 'true');
            });

            if (mobileNavClose) {
                mobileNavClose.addEventListener('click', closeMobileNav);
            }

            // Close on overlay click (outside menu)
            mobileNavOverlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMobileNav();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileNavOverlay.classList.contains('active')) {
                    closeMobileNav();
                }
            });

            function closeMobileNav() {
                mobileNavOverlay.classList.remove('active');
                body.style.overflow = '';
                if (mobileMenuToggle) {
                    mobileMenuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        }

        /**
         * Search Toggle
         */
        const searchToggle = document.querySelector('.search-toggle');
        const searchOverlay = document.querySelector('.search-overlay');
        const searchClose = document.querySelector('.search-close');
        const searchInput = document.querySelector('.search-overlay input[type="search"]');

        if (searchToggle && searchOverlay) {
            searchToggle.addEventListener('click', function() {
                searchOverlay.classList.add('active');
                body.style.overflow = 'hidden';

                // Focus on search input
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            });

            if (searchClose) {
                searchClose.addEventListener('click', closeSearch);
            }

            // Close on overlay click
            searchOverlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSearch();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                    closeSearch();
                }
            });

            function closeSearch() {
                searchOverlay.classList.remove('active');
                body.style.overflow = '';
            }
        }

        /**
         * Sticky Header on Scroll
         */
        const header = document.querySelector('.site-header');
        let lastScroll = 0;

        if (header && header.classList.contains('sticky-header')) {
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;

                // Add shadow when scrolled
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                lastScroll = currentScroll;
            });
        }

        /**
         * Mobile Bottom Nav - Active State
         */
        const mobileNavItems = document.querySelectorAll('.mobile-bottom-nav .mobile-nav-item');
        const currentURL = window.location.pathname;

        mobileNavItems.forEach(function(item) {
            const itemURL = new URL(item.href).pathname;

            if (currentURL === itemURL || (itemURL !== '/' && currentURL.startsWith(itemURL))) {
                item.classList.add('active');
            }
        });

        /**
         * Dropdown Menu Support (if needed)
         */
        const menuItemsWithChildren = document.querySelectorAll('.menu-item-has-children');

        menuItemsWithChildren.forEach(function(item) {
            const link = item.querySelector('a');
            const submenu = item.querySelector('.sub-menu');

            if (link && submenu) {
                // Desktop: hover behavior is handled by CSS
                // Mobile: click to toggle
                if (window.innerWidth <= 768) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        item.classList.toggle('open');
                    });
                }
            }
        });

        /**
         * Handle window resize
         */
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Close mobile nav if window is resized to desktop
                if (window.innerWidth > 768 && mobileNavOverlay.classList.contains('active')) {
                    closeMobileNav();
                }
            }, 250);
        });

        /**
         * Smooth Scroll for Anchor Links
         */
        const anchorLinks = document.querySelectorAll('a[href^="#"]');

        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');

                if (targetId === '#' || targetId === '#top') {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    return;
                }

                const target = document.querySelector(targetId);

                if (target) {
                    e.preventDefault();

                    const headerHeight = header ? header.offsetHeight : 0;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

    });

})();
