/**
 * GDPR and Cookie Consent JavaScript
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const cookieBanner = document.getElementById('cookie-consent-banner');

        if (!cookieBanner) {
            return;
        }

        const acceptBtn = document.getElementById('cookie-accept');
        const rejectBtn = document.getElementById('cookie-reject');
        const settingsBtn = document.getElementById('cookie-settings');

        // Accept all cookies
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                setCookieConsent('all');
            });
        }

        // Reject optional cookies (only necessary)
        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                setCookieConsent('necessary');
            });
        }

        // Open settings modal
        if (settingsBtn) {
            settingsBtn.addEventListener('click', function() {
                showCookieSettings();
            });
        }

        /**
         * Set cookie consent
         */
        function setCookieConsent(type) {
            // Send AJAX request to save consent
            if (typeof caniincasaGDPR !== 'undefined') {
                const formData = new FormData();
                formData.append('action', 'set_cookie_consent');
                formData.append('nonce', caniincasaGDPR.nonce);
                formData.append('consent_type', type);

                fetch(caniincasaGDPR.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideCookieBanner();

                        // Enable/disable tracking based on consent
                        if (type === 'all') {
                            enableAnalytics();
                        } else {
                            disableAnalytics();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving cookie consent:', error);
                    // Hide banner anyway
                    hideCookieBanner();
                });
            } else {
                // Fallback: just hide the banner
                hideCookieBanner();
            }
        }

        /**
         * Hide cookie banner
         */
        function hideCookieBanner() {
            cookieBanner.classList.add('hidden');
            setTimeout(function() {
                cookieBanner.style.display = 'none';
            }, 300);
        }

        /**
         * Show cookie settings modal
         */
        function showCookieSettings() {
            // Create modal if it doesn't exist
            let modal = document.getElementById('cookie-settings-modal');

            if (!modal) {
                modal = createSettingsModal();
                document.body.appendChild(modal);
            }

            modal.classList.add('active');
        }

        /**
         * Create settings modal HTML
         */
        function createSettingsModal() {
            const modal = document.createElement('div');
            modal.id = 'cookie-settings-modal';
            modal.className = 'cookie-settings-modal';

            modal.innerHTML = `
                <div class="cookie-settings-content">
                    <h2>Impostazioni Cookie</h2>

                    <div class="cookie-category">
                        <h3>Cookie Necessari</h3>
                        <p>Questi cookie sono essenziali per il funzionamento del sito e non possono essere disabilitati.</p>
                        <div class="cookie-toggle">
                            <input type="checkbox" id="necessary-cookies" checked disabled>
                            <label for="necessary-cookies">Sempre attivi</label>
                        </div>
                    </div>

                    <div class="cookie-category">
                        <h3>Cookie Analitici</h3>
                        <p>Questi cookie ci aiutano a capire come i visitatori interagiscono con il sito raccogliendo informazioni in forma anonima.</p>
                        <div class="cookie-toggle">
                            <input type="checkbox" id="analytics-cookies">
                            <label for="analytics-cookies">Abilita cookie analitici</label>
                        </div>
                    </div>

                    <div class="cookie-category">
                        <h3>Cookie di Marketing</h3>
                        <p>Questi cookie vengono utilizzati per mostrare pubblicità rilevanti per te e per i tuoi interessi.</p>
                        <div class="cookie-toggle">
                            <input type="checkbox" id="marketing-cookies">
                            <label for="marketing-cookies">Abilita cookie di marketing</label>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" id="save-preferences" class="cookie-btn cookie-accept">Salva Preferenze</button>
                        <button type="button" id="close-settings" class="cookie-btn cookie-settings">Chiudi</button>
                    </div>
                </div>
            `;

            // Event listeners
            modal.querySelector('#save-preferences').addEventListener('click', function() {
                const analyticsEnabled = modal.querySelector('#analytics-cookies').checked;
                const marketingEnabled = modal.querySelector('#marketing-cookies').checked;

                let consentType = 'necessary';
                if (analyticsEnabled && marketingEnabled) {
                    consentType = 'all';
                } else if (analyticsEnabled) {
                    consentType = 'analytics';
                } else if (marketingEnabled) {
                    consentType = 'marketing';
                }

                setCookieConsent(consentType);
                modal.classList.remove('active');
            });

            modal.querySelector('#close-settings').addEventListener('click', function() {
                modal.classList.remove('active');
            });

            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });

            return modal;
        }

        /**
         * Enable analytics tracking
         */
        function enableAnalytics() {
            // Google Analytics
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'granted'
                });
            }

            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('consent', 'grant');
            }

            console.log('Analytics enabled');
        }

        /**
         * Disable analytics tracking
         */
        function disableAnalytics() {
            // Google Analytics
            if (typeof gtag !== 'undefined') {
                gtag('consent', 'update', {
                    'analytics_storage': 'denied'
                });
            }

            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('consent', 'revoke');
            }

            console.log('Analytics disabled');
        }
    });
})();
