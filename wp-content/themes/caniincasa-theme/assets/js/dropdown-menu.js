/**
 * Dropdown Menu JavaScript
 *
 * Gestisce i menu a discesa su dispositivi touch e mobile
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Seleziona tutti gli elementi del menu con sottomenu - Multiple selectors for compatibility
        const menuSelectors = [
            '.main-navigation .menu-item-has-children',
            '.primary-navigation .menu-item-has-children',
            '.site-navigation .menu-item-has-children',
            'nav.navigation .menu-item-has-children',
            '.header-menu .menu-item-has-children',
            '.navigation-menu .menu-item-has-children',
            '.menu .menu-item-has-children',
            '.nav-menu .menu-item-has-children',
            '[data-header] .menu-item-has-children',
            '.ct-header-nav .menu-item-has-children',
            '.ast-desktop-nav .menu-item-has-children',
            '.main-nav .menu-item-has-children',
            '#site-navigation .menu-item-has-children',
            '.main-navigation .page_item_has_children',
            '.primary-navigation .page_item_has_children'
        ];

        const menuItems = document.querySelectorAll(menuSelectors.join(', '));

        if (!menuItems.length) {
            console.log('Dropdown Menu: No menu items with children found');
            return;
        }

        console.log('Dropdown Menu: Found ' + menuItems.length + ' menu items with children');

        // Funzione per chiudere tutti i sottomenu
        function closeAllSubmenus() {
            menuItems.forEach(function(item) {
                item.classList.remove('active');
            });
        }

        // Gestione click su elementi con sottomenu
        menuItems.forEach(function(item) {
            const link = item.querySelector('a');

            if (!link) {
                return;
            }

            // Previeni il comportamento predefinito solo su dispositivi touch
            link.addEventListener('click', function(e) {
                // Verifica se è un dispositivo touch
                const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

                // Su dispositivi touch o mobile
                if (isTouchDevice || window.innerWidth <= 768) {
                    const isActive = item.classList.contains('active');

                    // Chiudi tutti i sottomenu
                    closeAllSubmenus();

                    // Se non era attivo, aprilo
                    if (!isActive) {
                        e.preventDefault();
                        item.classList.add('active');
                    }
                    // Se era attivo, permettiamo il click sul link
                }
            });
        });

        // Chiudi sottomenu quando si clicca fuori
        document.addEventListener('click', function(e) {
            const isMenuClick = e.target.closest('.main-navigation');
            if (!isMenuClick) {
                closeAllSubmenus();
            }
        });

        // Gestione tastiera per accessibilità
        menuItems.forEach(function(item) {
            const link = item.querySelector('a');

            if (!link) {
                return;
            }

            link.addEventListener('keydown', function(e) {
                // Enter o Space
                if (e.key === 'Enter' || e.key === ' ') {
                    const isActive = item.classList.contains('active');
                    closeAllSubmenus();

                    if (!isActive) {
                        e.preventDefault();
                        item.classList.add('active');
                    }
                }

                // Escape chiude tutti i sottomenu
                if (e.key === 'Escape') {
                    closeAllSubmenus();
                }
            });
        });

        // Gestione responsive - rimuovi classi active quando si ridimensiona
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 768) {
                    closeAllSubmenus();
                }
            }, 250);
        });
    });
})();
