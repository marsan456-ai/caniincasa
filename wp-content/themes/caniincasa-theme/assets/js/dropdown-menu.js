/**
 * Dropdown Menu JavaScript
 *
 * Gestisce i menu a discesa su dispositivi touch e mobile
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Seleziona tutti gli elementi del menu con sottomenu
        const menuItems = document.querySelectorAll('.main-navigation .menu-item-has-children');

        if (!menuItems.length) {
            return;
        }

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
