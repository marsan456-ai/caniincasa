/**
 * Navigation Scripts
 *
 * @package Caniincasa
 */

(function() {
    'use strict';

    /**
     * Throttle function - limits how often a function can fire
     * Essential for scroll/resize events to prevent performance issues
     *
     * @param {Function} func - Function to throttle
     * @param {number} limit - Time in ms between calls
     * @returns {Function} Throttled function
     */
    function throttle(func, limit) {
        let inThrottle;
        let lastFunc;
        let lastRan;

        return function() {
            const context = this;
            const args = arguments;

            if (!inThrottle) {
                func.apply(context, args);
                lastRan = Date.now();
                inThrottle = true;

                setTimeout(function() {
                    inThrottle = false;
                    // If function was called during throttle, execute once more
                    if (lastFunc) {
                        lastFunc();
                        lastFunc = null;
                    }
                }, limit);
            } else {
                // Store the last call to execute after throttle period
                lastFunc = function() {
                    func.apply(context, args);
                    lastRan = Date.now();
                };
            }
        };
    }

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
         * Uses throttling to improve performance (scroll fires 60+ times/sec)
         */
        const header = document.querySelector('.site-header');
        let lastScroll = 0;

        if (header && header.classList.contains('sticky-header')) {
            // Throttled scroll handler - fires max once per 16ms (~60fps)
            const handleScroll = throttle(function() {
                const currentScroll = window.pageYOffset;

                // Add shadow when scrolled
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                lastScroll = currentScroll;
            }, 16);

            window.addEventListener('scroll', handleScroll, { passive: true });
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
         * Dropdown Menu Support - Desktop & Mobile
         */
        const menuItemsWithChildren = document.querySelectorAll('.primary-menu .menu-item-has-children');

        menuItemsWithChildren.forEach(function(item) {
            const link = item.querySelector(':scope > a');
            const submenu = item.querySelector(':scope > .sub-menu');

            if (link && submenu) {
                // Click handler for touch devices and keyboard accessibility
                link.addEventListener('click', function(e) {
                    // On mobile, always prevent default and toggle
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        closeAllDropdowns(item);
                        item.classList.toggle('open');
                        return;
                    }

                    // On desktop, allow click to toggle for touch devices
                    const isOpen = item.classList.contains('open');

                    // Check if this is a touch device
                    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
                        e.preventDefault();

                        if (!isOpen) {
                            closeAllDropdowns(item);
                            item.classList.add('open');
                        } else {
                            item.classList.remove('open');
                        }
                    } else {
                        // On non-touch desktop, if the link has a valid URL, allow navigation
                        // Otherwise prevent default
                        if (link.getAttribute('href') === '#' || link.getAttribute('href') === '') {
                            e.preventDefault();
                        }
                    }
                });

                // Desktop hover support
                if (window.innerWidth > 768) {
                    item.addEventListener('mouseenter', function() {
                        closeAllDropdowns(item);
                        item.classList.add('open');
                    });

                    item.addEventListener('mouseleave', function() {
                        item.classList.remove('open');
                    });
                }

                // Keyboard accessibility
                link.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        closeAllDropdowns(item);
                        item.classList.toggle('open');
                    }
                });
            }
        });

        /**
         * Close all dropdowns except the current one
         */
        function closeAllDropdowns(currentItem) {
            const allDropdowns = document.querySelectorAll('.primary-menu .menu-item-has-children');
            allDropdowns.forEach(function(dropdown) {
                if (dropdown !== currentItem && !dropdown.contains(currentItem)) {
                    dropdown.classList.remove('open');
                }
            });
        }

        /**
         * Close dropdowns when clicking outside
         */
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.primary-menu')) {
                closeAllDropdowns(null);
            }
        });

        /**
         * Close dropdowns on ESC key
         */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns(null);
            }
        });

        /**
         * Mobile Menu Dropdown Support
         * Supports nested submenus (multi-level)
         */
        function initMobileMenuDropdowns() {
            const mobileMenuItemsWithChildren = document.querySelectorAll('.mobile-menu .menu-item-has-children');

            console.log('Mobile menu items with children found:', mobileMenuItemsWithChildren.length);

            if (mobileMenuItemsWithChildren.length === 0) {
                console.warn('No mobile menu items with children found. Checking after delay...');

                // Try again after mobile menu is loaded
                setTimeout(function() {
                    const retryItems = document.querySelectorAll('.mobile-menu .menu-item-has-children');
                    console.log('Retry - Mobile menu items found:', retryItems.length);
                    if (retryItems.length > 0) {
                        attachMobileMenuListeners(retryItems);
                    }
                }, 500);
                return;
            }

            attachMobileMenuListeners(mobileMenuItemsWithChildren);
        }

        function attachMobileMenuListeners(items) {
            items.forEach(function(item) {
                // Use :scope to select direct children
                const link = item.querySelector(':scope > a');
                const submenu = item.querySelector(':scope > .sub-menu');

                if (link && submenu) {
                    // Remove existing listeners to avoid duplicates
                    const newLink = link.cloneNode(true);
                    link.parentNode.replaceChild(newLink, link);

                    newLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        console.log('Mobile menu item clicked:', newLink.textContent.trim());

                        // Toggle current item
                        const isOpen = item.classList.contains('open');

                        // Close all sibling dropdowns (same level only)
                        const parent = item.parentElement;
                        const siblings = Array.from(parent.children).filter(child =>
                            child !== item && child.classList.contains('menu-item-has-children')
                        );
                        siblings.forEach(sibling => {
                            sibling.classList.remove('open');
                            // Also close nested items
                            const nestedOpen = sibling.querySelectorAll('.menu-item-has-children.open');
                            nestedOpen.forEach(nested => nested.classList.remove('open'));
                        });

                        // Toggle current
                        if (isOpen) {
                            item.classList.remove('open');
                            console.log('Closed submenu');
                            // Close all nested open items
                            const nestedOpen = item.querySelectorAll('.menu-item-has-children.open');
                            nestedOpen.forEach(nested => nested.classList.remove('open'));
                        } else {
                            item.classList.add('open');
                            console.log('Opened submenu');
                        }

                        // Update aria-expanded
                        newLink.setAttribute('aria-expanded', !isOpen);
                    });

                    // Initialize aria-expanded
                    newLink.setAttribute('aria-expanded', 'false');

                    console.log('Attached listener to:', newLink.textContent.trim());
                }
            });
        }

        // Initialize mobile menu dropdowns
        initMobileMenuDropdowns();

        // Re-initialize when mobile nav opens (in case menu is dynamically loaded)
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                setTimeout(initMobileMenuDropdowns, 100);
            });
        }

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
