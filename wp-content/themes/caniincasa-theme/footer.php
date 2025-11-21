<?php
/**
 * The footer template
 *
 * @package Caniincasa
 * @since 1.0.0
 */
?>

    <?php if ( ! is_page_template( 'template-dashboard.php' ) ) : ?>

    <!-- Footer -->
    <footer id="colophon" class="site-footer">

        <!-- Footer Widgets -->
        <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
        <div class="footer-widgets">
            <div class="container">
                <div class="footer-widgets-grid">
                    <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                        <?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
                            <div class="footer-widget-column">
                                <?php dynamic_sidebar( 'footer-' . $i ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <div class="footer-info">
                        <p class="copyright">
                            &copy; <?php echo date( 'Y' ); ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
                            - <?php esc_html_e( 'Tutti i diritti riservati', 'caniincasa' ); ?>
                        </p>
                    </div>

                    <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <nav class="footer-navigation">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'menu_class'     => 'footer-menu',
                            'container'      => false,
                            'depth'          => 1,
                        ) );
                        ?>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <?php endif; // End footer check ?>

</div><!-- #page -->

<!-- Mobile Bottom Navigation -->
<?php if ( ! is_page_template( 'template-dashboard.php' ) ) : ?>
<nav class="mobile-bottom-nav mobile-show">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Home', 'caniincasa' ); ?></span>
    </a>

    <a href="<?php echo esc_url( home_url( '/annunci' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M19 11H5M19 11C20.1046 11 21 11.8954 21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V13C3 11.8954 3.89543 11 5 11M19 11V9C19 7.89543 18.1046 7 17 7M5 11V9C5 7.89543 5.89543 7 7 7M7 7V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V7M7 7H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Annunci', 'caniincasa' ); ?></span>
    </a>

    <a href="<?php echo esc_url( home_url( '/razze-di-cani' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Razze', 'caniincasa' ); ?></span>
    </a>

    <?php if ( is_user_logged_in() ) : ?>
    <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Profilo', 'caniincasa' ); ?></span>
    </a>
    <?php else : ?>
    <a href="<?php echo esc_url( wp_login_url() ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15M10 17L15 12M15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Login', 'caniincasa' ); ?></span>
    </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Message Modal (only for logged in users) -->
<?php if ( is_user_logged_in() ) : ?>
<div id="message-modal" class="message-modal">
    <div class="message-modal-overlay"></div>
    <div class="message-modal-content">
        <div class="message-modal-header">
            <h2><?php esc_html_e( 'Invia Messaggio', 'caniincasa' ); ?></h2>
            <button class="message-modal-close" aria-label="<?php esc_attr_e( 'Chiudi', 'caniincasa' ); ?>">&times;</button>
        </div>

        <div class="message-modal-body">
            <div class="message-recipient-info">
                <strong><?php esc_html_e( 'Destinatario:', 'caniincasa' ); ?></strong>
                <span id="message-recipient-name"></span>
            </div>

            <div class="message-response"></div>

            <form id="message-form">
                <input type="hidden" id="message-recipient-id" name="recipient_id">
                <input type="hidden" id="message-parent-id" name="parent_id">
                <input type="hidden" id="message-related-post-id" name="related_post_id">
                <input type="hidden" id="message-related-post-type" name="related_post_type">

                <div class="message-form-group">
                    <label for="message-subject"><?php esc_html_e( 'Oggetto', 'caniincasa' ); ?> *</label>
                    <input type="text" id="message-subject" name="subject" required placeholder="<?php esc_attr_e( 'Oggetto del messaggio', 'caniincasa' ); ?>">
                </div>

                <div class="message-form-group">
                    <label for="message-content"><?php esc_html_e( 'Messaggio', 'caniincasa' ); ?> *</label>
                    <textarea id="message-content" name="message" required placeholder="<?php esc_attr_e( 'Scrivi il tuo messaggio qui...', 'caniincasa' ); ?>"></textarea>
                </div>

                <button type="submit" class="message-submit-btn">
                    <?php esc_html_e( 'Invia Messaggio', 'caniincasa' ); ?>
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Cookie Banner GDPR -->
<div class="cookie-banner">
    <div class="container">
        <div class="cookie-banner-content">
            <div class="cookie-info">
                <h3><?php esc_html_e( 'Utilizziamo i Cookie', 'caniincasa' ); ?></h3>
                <p>
                    <?php esc_html_e( 'Utilizziamo cookie tecnici e, previo tuo consenso, cookie analitici e di profilazione di terze parti per offrirti una migliore esperienza di navigazione. Puoi acconsentire a tutti i cookie cliccando su "Accetta tutti", negare il consenso cliccando su "Rifiuta" o gestire le tue preferenze attraverso le impostazioni.', 'caniincasa' ); ?>
                    <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'caniincasa' ); ?></a>
                </p>
            </div>
            <div class="cookie-actions">
                <button id="cookie-settings-btn" class="cookie-btn cookie-btn-settings">
                    <?php esc_html_e( 'Impostazioni', 'caniincasa' ); ?>
                </button>
                <button id="reject-all-cookies" class="cookie-btn cookie-btn-secondary">
                    <?php esc_html_e( 'Rifiuta', 'caniincasa' ); ?>
                </button>
                <button id="accept-all-cookies" class="cookie-btn cookie-btn-primary">
                    <?php esc_html_e( 'Accetta tutti', 'caniincasa' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Settings Modal -->
<div class="cookie-settings-modal">
    <div class="cookie-settings-content">
        <div class="cookie-settings-header">
            <h2><?php esc_html_e( 'Gestione Cookie', 'caniincasa' ); ?></h2>
            <button class="cookie-settings-close" aria-label="<?php esc_attr_e( 'Chiudi', 'caniincasa' ); ?>">&times;</button>
        </div>

        <div class="cookie-settings-body">
            <p><?php esc_html_e( 'Puoi gestire le tue preferenze sui cookie selezionando le categorie qui sotto. I cookie necessari sono sempre attivi in quanto indispensabili per il funzionamento del sito.', 'caniincasa' ); ?></p>

            <!-- Necessary Cookies -->
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h3 class="cookie-category-title"><?php esc_html_e( 'Cookie Necessari', 'caniincasa' ); ?></h3>
                    <label class="cookie-toggle">
                        <input type="checkbox" checked disabled>
                        <span class="cookie-toggle-slider"></span>
                    </label>
                </div>
                <p class="cookie-category-description">
                    <?php esc_html_e( 'Questi cookie sono essenziali per il funzionamento del sito web e non possono essere disabilitati. Vengono utilizzati per gestire la navigazione e permettere le funzionalità di base.', 'caniincasa' ); ?>
                </p>
            </div>

            <!-- Functional Cookies -->
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h3 class="cookie-category-title"><?php esc_html_e( 'Cookie Funzionali', 'caniincasa' ); ?></h3>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="cookie-functional">
                        <span class="cookie-toggle-slider"></span>
                    </label>
                </div>
                <p class="cookie-category-description">
                    <?php esc_html_e( 'Questi cookie permettono al sito di ricordare le tue scelte (come username, lingua o regione) e fornire funzionalità migliorate e più personalizzate.', 'caniincasa' ); ?>
                </p>
            </div>

            <!-- Analytics Cookies -->
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h3 class="cookie-category-title"><?php esc_html_e( 'Cookie Analitici', 'caniincasa' ); ?></h3>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="cookie-analytics">
                        <span class="cookie-toggle-slider"></span>
                    </label>
                </div>
                <p class="cookie-category-description">
                    <?php esc_html_e( 'Questi cookie ci aiutano a capire come i visitatori interagiscono con il sito raccogliendo e segnalando informazioni in forma anonima. Ci permettono di migliorare il sito.', 'caniincasa' ); ?>
                </p>
            </div>

            <!-- Marketing Cookies -->
            <div class="cookie-category">
                <div class="cookie-category-header">
                    <h3 class="cookie-category-title"><?php esc_html_e( 'Cookie di Marketing', 'caniincasa' ); ?></h3>
                    <label class="cookie-toggle">
                        <input type="checkbox" id="cookie-marketing">
                        <span class="cookie-toggle-slider"></span>
                    </label>
                </div>
                <p class="cookie-category-description">
                    <?php esc_html_e( 'Questi cookie vengono utilizzati per mostrarti annunci pubblicitari pertinenti ai tuoi interessi. Possono essere utilizzati anche per limitare il numero di volte che vedi un annuncio.', 'caniincasa' ); ?>
                </p>
            </div>
        </div>

        <div class="cookie-settings-footer">
            <button id="save-cookie-preferences" class="cookie-btn cookie-btn-secondary">
                <?php esc_html_e( 'Salva Preferenze', 'caniincasa' ); ?>
            </button>
            <button id="accept-all-from-settings" class="cookie-btn cookie-btn-primary">
                <?php esc_html_e( 'Accetta Tutti', 'caniincasa' ); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal Registrazione per Annunci -->
<?php if ( ! is_user_logged_in() ) : ?>
<div id="annuncio-registration-modal" class="auth-modal" style="display: none;">
    <div class="auth-modal-overlay"></div>
    <div class="auth-modal-content">
        <button type="button" class="auth-modal-close" id="close-annuncio-modal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="auth-modal-header">
            <div class="auth-modal-icon">🐾</div>
            <h2 class="auth-modal-title">Pubblica il Tuo Annuncio</h2>
            <p class="auth-modal-subtitle">Registrati o accedi per inserire annunci di cuccioli</p>
        </div>

        <div class="auth-modal-body">
            <div class="auth-tabs">
                <button type="button" class="auth-tab active" data-tab="register">
                    <strong>Registrati</strong> <span class="tab-badge">Consigliato</span>
                </button>
                <button type="button" class="auth-tab" data-tab="login">
                    Ho già un account
                </button>
            </div>

            <!-- Tab Registrazione -->
            <div class="auth-tab-content active" id="tab-register">
                <p class="tab-intro">
                    Crea un account gratuito in pochi secondi e inizia subito a pubblicare i tuoi annunci!
                </p>

                <div class="auth-benefits">
                    <h3>Perché registrarsi?</h3>
                    <ul class="benefits-list">
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#4CAF50">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span><strong>Annunci verificati:</strong> Il tuo profilo garantisce qualità e affidabilità agli acquirenti</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#4CAF50">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span><strong>Gestione centralizzata:</strong> Modifica, aggiorna ed elimina i tuoi annunci quando vuoi</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#4CAF50">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span><strong>Messaggistica diretta:</strong> Ricevi e rispondi ai messaggi degli interessati</span>
                        </li>
                        <li>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#4CAF50">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span><strong>Statistiche:</strong> Visualizza quante persone hanno visto i tuoi annunci</span>
                        </li>
                    </ul>
                </div>

                <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="btn btn-auth-primary btn-block">
                    Crea Account Gratuito
                </a>

                <p class="auth-note">
                    Hai già un account? <button type="button" class="auth-switch-tab" data-tab="login">Accedi qui</button>
                </p>
            </div>

            <!-- Tab Login -->
            <div class="auth-tab-content" id="tab-login">
                <p class="tab-intro">
                    Accedi con il tuo account per continuare a pubblicare annunci.
                </p>

                <a href="<?php echo esc_url( wp_login_url( home_url( '/inserisci-annuncio/' ) ) ); ?>" class="btn btn-auth-secondary btn-block">
                    Accedi
                </a>

                <p class="auth-note">
                    Non hai un account? <button type="button" class="auth-switch-tab" data-tab="register">Registrati qui</button>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
