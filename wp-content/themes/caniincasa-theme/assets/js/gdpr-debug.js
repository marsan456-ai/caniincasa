/**
 * GDPR Cookie Banner Debug Helper
 *
 * Aggiungi questo file temporaneamente a functions.php per debug:
 * wp_enqueue_script( 'gdpr-debug', CANIINCASA_THEME_URI . '/assets/js/gdpr-debug.js', array('caniincasa-gdpr-cookie'), CANIINCASA_VERSION, true );
 */

(function() {
    'use strict';

    // Wait for DOM and GDPR script to load
    window.addEventListener('load', function() {
        setTimeout(function() {
            console.group('🍪 GDPR Cookie Banner Debug');

            // Check if API is available
            if (typeof CaniincasaCookieConsent !== 'undefined') {
                console.log('✅ API GDPR disponibile');
                console.log('Preferenze correnti:', CaniincasaCookieConsent.getPreferences());
            } else {
                console.error('❌ API GDPR NON disponibile - gdpr-cookie.js non caricato?');
            }

            // Check banner HTML
            const banner = document.querySelector('.cookie-banner');
            if (banner) {
                console.log('✅ Banner HTML presente nel DOM');
                console.log('Banner ha classe "show"?', banner.classList.contains('show'));

                // Check computed styles
                const styles = window.getComputedStyle(banner);
                console.log('Transform del banner:', styles.transform);
                console.log('Opacity del banner:', styles.opacity);
                console.log('Visibility del banner:', styles.visibility);
            } else {
                console.error('❌ Banner HTML NON trovato nel DOM');
            }

            // Check modal
            const modal = document.querySelector('.cookie-settings-modal');
            if (modal) {
                console.log('✅ Modal impostazioni presente');
            } else {
                console.warn('⚠️  Modal impostazioni NON trovato');
            }

            // Check cookie
            const cookieValue = document.cookie
                .split('; ')
                .find(row => row.startsWith('caniincasa_cookie_consent='));

            if (cookieValue) {
                try {
                    const consent = JSON.parse(decodeURIComponent(cookieValue.split('=')[1]));
                    console.log('🍪 Cookie consenso trovato:', consent);
                    console.log('ℹ️  Il banner è nascosto perché il consenso è già stato dato');
                } catch (e) {
                    console.error('❌ Errore parsing cookie:', e);
                }
            } else {
                console.log('ℹ️  Nessun cookie consenso trovato - il banner dovrebbe apparire');
            }

            // Check CSS
            const cssLoaded = [...document.styleSheets].some(sheet => {
                try {
                    return sheet.href && sheet.href.includes('gdpr-cookie.css');
                } catch (e) {
                    return false;
                }
            });

            if (cssLoaded) {
                console.log('✅ CSS gdpr-cookie.css caricato');
            } else {
                console.error('❌ CSS gdpr-cookie.css NON caricato');
            }

            // Check buttons
            const buttons = {
                acceptAll: document.getElementById('accept-all-cookies'),
                rejectAll: document.getElementById('reject-all-cookies'),
                settings: document.getElementById('cookie-settings-btn'),
            };

            console.log('Pulsanti banner:', {
                'Accetta tutti': !!buttons.acceptAll,
                'Rifiuta': !!buttons.rejectAll,
                'Impostazioni': !!buttons.settings,
            });

            console.groupEnd();

            // Add helper buttons to page
            addDebugButtons();

        }, 2000); // Wait 2 seconds for everything to load
    });

    function addDebugButtons() {
        const container = document.createElement('div');
        container.id = 'gdpr-debug-controls';
        container.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: #fff;
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 15px;
            z-index: 9999999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            font-family: Arial, sans-serif;
        `;

        container.innerHTML = `
            <div style="font-size: 12px; font-weight: bold; margin-bottom: 10px; color: #e74c3c;">
                🔧 GDPR Debug Controls
            </div>
            <button onclick="document.querySelector('.cookie-banner').classList.add('show')"
                    style="display:block; width:100%; margin:5px 0; padding:8px; cursor:pointer; border:1px solid #3498db; background:#3498db; color:white; border-radius:4px;">
                Mostra Banner
            </button>
            <button onclick="CaniincasaCookieConsent.openSettings()"
                    style="display:block; width:100%; margin:5px 0; padding:8px; cursor:pointer; border:1px solid #2ecc71; background:#2ecc71; color:white; border-radius:4px;">
                Apri Impostazioni
            </button>
            <button onclick="CaniincasaCookieConsent.revokeConsent()"
                    style="display:block; width:100%; margin:5px 0; padding:8px; cursor:pointer; border:1px solid #e74c3c; background:#e74c3c; color:white; border-radius:4px;">
                Reset & Reload
            </button>
            <button onclick="document.getElementById('gdpr-debug-controls').remove()"
                    style="display:block; width:100%; margin:5px 0; padding:8px; cursor:pointer; border:1px solid #95a5a6; background:#95a5a6; color:white; border-radius:4px;">
                Chiudi Debug
            </button>
        `;

        document.body.appendChild(container);
    }

})();
