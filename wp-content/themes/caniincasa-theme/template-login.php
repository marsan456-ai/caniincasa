<?php
/**
 * Template Name: Login
 */

// Get redirect_to parameter
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/dashboard' );

if ( is_user_logged_in() ) {
    wp_redirect( $redirect_to );
    exit;
}

get_header();
?>

<main class="site-main auth-page">
    <section class="auth-hero">
        <div class="container">
            <h1 class="auth-title">Accedi a Caniincasa</h1>
            <p class="auth-subtitle">Benvenuto! Accedi al tuo account</p>
        </div>
    </section>

    <section class="auth-form-section">
        <div class="container">
            <div class="auth-form-wrapper auth-form-wrapper-narrow">
                <form id="login-form" class="auth-form">
                    <div id="login-messages" class="auth-messages" style="display: none;"></div>

                    <div class="form-group">
                        <label for="username">Nome Utente o Email</label>
                        <input type="text" id="username" name="username" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required autocomplete="current-password">
                            <button type="button" class="toggle-password">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group form-checkbox-row">
                        <div class="form-checkbox">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Ricordami</label>
                        </div>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="forgot-password-link">Password dimenticata?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-login">
                        <span class="btn-text">Accedi</span>
                        <span class="btn-loading" style="display: none;">Accesso...</span>
                    </button>

                    <?php wp_nonce_field( 'caniincasa_login', 'login_nonce' ); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                </form>

                <div class="auth-footer">
                    <?php
                    $register_url = home_url( '/registrazione' );
                    if ( isset( $_GET['redirect_to'] ) ) {
                        $register_url = add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $register_url );
                    }
                    ?>
                    <p>Non hai un account? <a href="<?php echo esc_url( $register_url ); ?>">Registrati qui</a></p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
