<?php
/**
 * Template Name: Login
 * Template for user login
 *
 * @package Caniincasa
 */

// Redirect to dashboard if already logged in
if ( is_user_logged_in() ) {
    wp_redirect( home_url( '/dashboard' ) );
    exit;
}

get_header();
?>

<main id="primary" class="site-main auth-page">

    <!-- Hero Section -->
    <section class="auth-hero">
        <div class="container">
            <h1 class="auth-title">Accedi a Caniincasa</h1>
            <p class="auth-subtitle">Benvenuto! Accedi al tuo account per continuare</p>
        </div>
    </section>

    <!-- Login Form Section -->
    <section class="auth-form-section">
        <div class="container">
            <div class="auth-form-wrapper auth-form-wrapper-narrow">

                <!-- Login Form -->
                <form id="login-form" class="auth-form">

                    <!-- Messages -->
                    <div id="login-messages" class="auth-messages" style="display: none;"></div>

                    <?php if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) : ?>
                        <div class="auth-messages error">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                            </svg>
                            <span>Credenziali non valide. Riprova.</span>
                        </div>
                    <?php endif; ?>

                    <?php if ( isset( $_GET['password_changed'] ) && $_GET['password_changed'] === 'true' ) : ?>
                        <div class="auth-messages success">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>Password modificata con successo! Effettua il login.</span>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="username">Nome Utente o Email</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                            placeholder="Il tuo nome utente o email"
                            value="<?php echo isset( $_GET['username'] ) ? esc_attr( $_GET['username'] ) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="La tua password"
                            >
                            <button type="button" class="toggle-password" aria-label="Mostra password">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group form-checkbox-row">
                        <div class="form-checkbox">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">Ricordami</label>
                        </div>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="forgot-password-link">
                            Password dimenticata?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-login">
                        <span class="btn-text">Accedi</span>
                        <span class="btn-loading" style="display: none;">
                            <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                            </svg>
                            Accesso...
                        </span>
                    </button>

                    <?php wp_nonce_field( 'caniincasa_login', 'login_nonce' ); ?>

                </form>

                <!-- Social Login (Optional - for future implementation) -->
                <!--
                <div class="auth-divider">
                    <span>Oppure accedi con</span>
                </div>

                <div class="social-login-buttons">
                    <button type="button" class="btn btn-social btn-google">
                        <svg width="20" height="20" viewBox="0 0 24 24">...</svg>
                        Google
                    </button>
                    <button type="button" class="btn btn-social btn-facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24">...</svg>
                        Facebook
                    </button>
                </div>
                -->

                <!-- Registration Link -->
                <div class="auth-footer">
                    <p>Non hai un account? <a href="<?php echo esc_url( home_url( '/registrazione' ) ); ?>">Registrati qui</a></p>
                </div>

            </div>
        </div>
    </section>

</main>

<?php
get_footer();
