/**
 * GDPR Cookie Banner Script
 *
 * @package Caniincasa
 */

(function() {
    'use strict';

    const COOKIE_NAME = 'caniincasa_cookie_consent';
    const COOKIE_EXPIRY = 365; // days

    // Cookie preferences object
    let cookiePreferences = {
        necessary: true,      // Always true
        functional: false,
        analytics: false,
        marketing: false
    };

    /**
     * Initialize cookie banner
     */
    function init() {
        // Check if consent already given
        const consent = getCookie(COOKIE_NAME);

        if (!consent) {
            // Show banner after short delay
            setTimeout(showBanner, 1000);
        } else {
            // Load saved preferences
            try {
                cookiePreferences = JSON.parse(consent);
                applyPreferences();
            } catch (e) {
                console.error('Error parsing cookie preferences:', e);
            }
        }

        // Setup event listeners
        setupEventListeners();
    }

    /**
     * Show cookie banner
     */
    function showBanner() {
        const banner = document.querySelector('.cookie-banner');
        if (banner) {
            banner.classList.add('show');
        }
    }

    /**
     * Hide cookie banner
     */
    function hideBanner() {
        const banner = document.querySelector('.cookie-banner');
        if (banner) {
            banner.classList.remove('show');
        }
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Accept all button
        const acceptAllBtn = document.getElementById('accept-all-cookies');
        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', acceptAll);
        }

        // Reject all button
        const rejectAllBtn = document.getElementById('reject-all-cookies');
        if (rejectAllBtn) {
            rejectAllBtn.addEventListener('click', rejectAll);
        }

        // Settings button
        const settingsBtn = document.getElementById('cookie-settings-btn');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', openSettings);
        }

        // Close settings modal
        const closeSettingsBtn = document.querySelector('.cookie-settings-close');
        if (closeSettingsBtn) {
            closeSettingsBtn.addEventListener('click', closeSettings);
        }

        // Save preferences button
        const savePrefsBtn = document.getElementById('save-cookie-preferences');
        if (savePrefsBtn) {
            savePrefsBtn.addEventListener('click', savePreferences);
        }

        // Accept from settings
        const acceptFromSettings = document.getElementById('accept-all-from-settings');
        if (acceptFromSettings) {
            acceptFromSettings.addEventListener('click', acceptAll);
        }

        // Click outside modal to close
        const modal = document.querySelector('.cookie-settings-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeSettings();
                }
            });
        }
    }

    /**
     * Accept all cookies
     */
    function acceptAll() {
        cookiePreferences = {
            necessary: true,
            functional: true,
            analytics: true,
            marketing: true
        };
        saveConsent();
        applyPreferences();
        hideBanner();
        closeSettings();
    }

    /**
     * Reject all (except necessary)
     */
    function rejectAll() {
        cookiePreferences = {
            necessary: true,
            functional: false,
            analytics: false,
            marketing: false
        };
        saveConsent();
        applyPreferences();
        hideBanner();
        closeSettings();
    }

    /**
     * Open settings modal
     */
    function openSettings() {
        const modal = document.querySelector('.cookie-settings-modal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Set current preferences in toggles
            updateToggleStates();
        }
    }

    /**
     * Close settings modal
     */
    function closeSettings() {
        const modal = document.querySelector('.cookie-settings-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    /**
     * Update toggle states from current preferences
     */
    function updateToggleStates() {
        const toggles = {
            'cookie-functional': cookiePreferences.functional,
            'cookie-analytics': cookiePreferences.analytics,
            'cookie-marketing': cookiePreferences.marketing
        };

        for (const [id, value] of Object.entries(toggles)) {
            const toggle = document.getElementById(id);
            if (toggle) {
                toggle.checked = value;
            }
        }
    }

    /**
     * Save custom preferences
     */
    function savePreferences() {
        // Get toggle states
        const functionalToggle = document.getElementById('cookie-functional');
        const analyticsToggle = document.getElementById('cookie-analytics');
        const marketingToggle = document.getElementById('cookie-marketing');

        cookiePreferences = {
            necessary: true,
            functional: functionalToggle ? functionalToggle.checked : false,
            analytics: analyticsToggle ? analyticsToggle.checked : false,
            marketing: marketingToggle ? marketingToggle.checked : false
        };

        saveConsent();
        applyPreferences();
        hideBanner();
        closeSettings();
    }

    /**
     * Save consent to cookie
     */
    function saveConsent() {
        const value = JSON.stringify(cookiePreferences);
        setCookie(COOKIE_NAME, value, COOKIE_EXPIRY);
    }

    /**
     * Apply user preferences
     */
    function applyPreferences() {
        // Load analytics if consented
        if (cookiePreferences.analytics) {
            loadAnalytics();
        }

        // Load marketing scripts if consented
        if (cookiePreferences.marketing) {
            loadMarketing();
        }

        // Dispatch custom event for other scripts
        const event = new CustomEvent('cookieConsentUpdated', {
            detail: cookiePreferences
        });
        document.dispatchEvent(event);
    }

    /**
     * Load analytics scripts (Google Analytics, etc.)
     */
    function loadAnalytics() {
        // Example: Google Analytics
        // Uncomment and add your GA tracking ID
        /*
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');

        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID';
        document.head.appendChild(script);
        */
    }

    /**
     * Load marketing scripts (Facebook Pixel, etc.)
     */
    function loadMarketing() {
        // Example: Facebook Pixel
        // Add your marketing scripts here
    }

    /**
     * Get cookie value
     */
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }

    /**
     * Set cookie
     */
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
    }

    /**
     * Delete cookie
     */
    function deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
    }

    /**
     * Expose public API
     */
    window.CaniincasaCookieConsent = {
        getPreferences: function() {
            return cookiePreferences;
        },
        updatePreferences: function(prefs) {
            cookiePreferences = Object.assign({}, cookiePreferences, prefs);
            saveConsent();
            applyPreferences();
        },
        openSettings: openSettings,
        revokeConsent: function() {
            deleteCookie(COOKIE_NAME);
            window.location.reload();
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
