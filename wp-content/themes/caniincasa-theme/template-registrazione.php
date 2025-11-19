<?php
/**
 * Template Name: Registrazione
 * Template for user registration
 *
 * @package Caniincasa
 */

// Get redirect_to parameter
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/dashboard' );

// Redirect if already logged in
if ( is_user_logged_in() ) {
    wp_redirect( $redirect_to );
    exit;
}

get_header();
?>

<main id="primary" class="site-main auth-page">

    <!-- Hero Section -->
    <section class="auth-hero">
        <div class="container">
            <h1 class="auth-title">Registrati su Caniincasa</h1>
            <p class="auth-subtitle">Crea il tuo account per pubblicare annunci, salvare preferiti e molto altro</p>
        </div>
    </section>

    <!-- Registration Form Section -->
    <section class="auth-form-section">
        <div class="container">
            <div class="auth-form-wrapper">

                <!-- Registration Form -->
                <form id="registration-form" class="auth-form">

                    <!-- Progress Indicator -->
                    <div class="form-progress">
                        <div class="progress-step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label">Account</span>
                        </div>
                        <div class="progress-step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label">Dettagli</span>
                        </div>
                        <div class="progress-step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label">Tipologia</span>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div id="registration-messages" class="auth-messages" style="display: none;"></div>

                    <!-- Step 1: Account Info -->
                    <div class="form-step active" data-step="1">
                        <h2 class="step-title">Informazioni Account</h2>

                        <div class="form-group">
                            <label for="username">Nome Utente *</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                required
                                minlength="4"
                                autocomplete="username"
                                placeholder="Scegli un nome utente (min. 4 caratteri)"
                            >
                            <small class="form-help">Verrà utilizzato per accedere al sito</small>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                autocomplete="email"
                                placeholder="tua@email.com"
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <div class="password-wrapper">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        required
                                        minlength="8"
                                        autocomplete="new-password"
                                        placeholder="Minimo 8 caratteri"
                                    >
                                    <button type="button" class="toggle-password" aria-label="Mostra password">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Conferma Password *</label>
                                <div class="password-wrapper">
                                    <input
                                        type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        required
                                        minlength="8"
                                        autocomplete="new-password"
                                        placeholder="Ripeti la password"
                                    >
                                    <button type="button" class="toggle-password" aria-label="Mostra password">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary btn-lg btn-block next-step">
                            Continua
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: Personal Info -->
                    <div class="form-step" data-step="2">
                        <h2 class="step-title">Informazioni Personali</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Nome *</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    required
                                    autocomplete="given-name"
                                    placeholder="Il tuo nome"
                                >
                            </div>
                            <div class="form-group">
                                <label for="last_name">Cognome *</label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    required
                                    autocomplete="family-name"
                                    placeholder="Il tuo cognome"
                                >
                                <small class="form-help-text" style="display: block; margin-top: 8px; color: #64748b; font-size: 14px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 4px;">
                                        <path d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Per la tua privacy, sul sito sarà visibile solo la tua iniziale (es. Mario R.)
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Telefono</label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                autocomplete="tel"
                                placeholder="+39 123 456 7890"
                            >
                            <small class="form-help">Opzionale - utile per essere contattati riguardo gli annunci</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">Città</label>
                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    autocomplete="address-level2"
                                    placeholder="La tua città"
                                >
                            </div>
                            <div class="form-group">
                                <label for="provincia">Provincia</label>
                                <select id="provincia" name="provincia">
                                    <option value="">Seleziona provincia</option>
                                    <?php
                                    $province = get_terms( array(
                                        'taxonomy'   => 'provincia',
                                        'hide_empty' => false,
                                        'orderby'    => 'name',
                                        'order'      => 'ASC',
                                    ) );
                                    foreach ( $province as $prov ) :
                                        ?>
                                        <option value="<?php echo esc_attr( $prov->slug ); ?>">
                                            <?php echo esc_html( $prov->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-step-actions">
                            <button type="button" class="btn btn-outline prev-step">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                                Indietro
                            </button>
                            <button type="button" class="btn btn-primary btn-lg next-step">
                                Continua
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: User Type -->
                    <div class="form-step" data-step="3">
                        <h2 class="step-title">Seleziona Tipologia Utente</h2>
                        <p class="step-description">Scegli la categoria che ti rappresenta meglio. Potrai accedere a funzionalità specifiche per la tua categoria.</p>

                        <div class="user-type-grid">
                            <?php
                            $user_types = array(
                                'privato' => array(
                                    'label' => 'Privato',
                                    'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
                                    'description' => 'Appassionato di cani o proprietario',
                                ),
                                'veterinario' => array(
                                    'label' => 'Veterinario',
                                    'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>',
                                    'description' => 'Medico veterinario o struttura veterinaria',
                                ),
                                'allevatore' => array(
                                    'label' => 'Allevatore',
                                    'icon' => '<path d="M12 2L2 7l10 5 10-5-10-5z"></path><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline>',
                                    'description' => 'Allevamento riconosciuto',
                                ),
                                'titolare_pensione' => array(
                                    'label' => 'Titolare Pensione',
                                    'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',
                                    'description' => 'Pensione o asilo per cani',
                                ),
                                'dog_sitter' => array(
                                    'label' => 'Dog Sitter',
                                    'icon' => '<circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>',
                                    'description' => 'Professionista dog sitting',
                                ),
                                'educatore_cinofilo' => array(
                                    'label' => 'Educatore Cinofilo',
                                    'icon' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
                                    'description' => 'Istruttore o centro cinofilo',
                                ),
                                'altro' => array(
                                    'label' => 'Altro',
                                    'icon' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
                                    'description' => 'Altra categoria',
                                ),
                            );

                            foreach ( $user_types as $type_slug => $type_data ) :
                                ?>
                                <div class="user-type-card">
                                    <input
                                        type="radio"
                                        id="type_<?php echo esc_attr( $type_slug ); ?>"
                                        name="user_type"
                                        value="<?php echo esc_attr( $type_slug ); ?>"
                                        required
                                    >
                                    <label for="type_<?php echo esc_attr( $type_slug ); ?>">
                                        <div class="card-icon">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <?php echo $type_data['icon']; ?>
                                            </svg>
                                        </div>
                                        <h3 class="card-title"><?php echo esc_html( $type_data['label'] ); ?></h3>
                                        <p class="card-description"><?php echo esc_html( $type_data['description'] ); ?></p>
                                        <div class="card-check">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group form-checkbox">
                            <input type="checkbox" id="accept_privacy" name="accept_privacy" required>
                            <label for="accept_privacy">
                                Accetto la <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>" target="_blank">Privacy Policy</a> e i <a href="<?php echo esc_url( home_url( '/termini-e-condizioni' ) ); ?>" target="_blank">Termini e Condizioni</a> *
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <input type="checkbox" id="newsletter_subscribe" name="newsletter_subscribe" value="1">
                            <label for="newsletter_subscribe">
                                Iscrivimi alla newsletter per ricevere aggiornamenti su razze, annunci e consigli per il tuo amico a quattro zampe
                            </label>
                        </div>

                        <div class="form-step-actions">
                            <button type="button" class="btn btn-outline prev-step">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                                Indietro
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-registration">
                                <span class="btn-text">Completa Registrazione</span>
                                <span class="btn-loading" style="display: none;">
                                    <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                                    </svg>
                                    Registrazione...
                                </span>
                            </button>
                        </div>
                    </div>

                    <?php wp_nonce_field( 'caniincasa_register', 'register_nonce' ); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

                </form>

                <!-- Login Link -->
                <div class="auth-footer">
                    <?php
                    $login_url = home_url( '/login' );
                    if ( isset( $_GET['redirect_to'] ) ) {
                        $login_url = add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $login_url );
                    }
                    ?>
                    <p>Hai già un account? <a href="<?php echo esc_url( $login_url ); ?>">Accedi qui</a></p>
                </div>

            </div>
        </div>
    </section>

</main>

<?php
get_footer();
