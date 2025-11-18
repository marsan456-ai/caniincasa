<?php
/**
 * GDPR Disclaimers and Legal Notices
 *
 * Gestisce tutti i disclaimer e le notice legali GDPR
 *
 * @package Caniincasa
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disclaimer generale per dati strutture
 * Da mostrare nelle pagine di strutture (allevamenti, veterinari, pensioni, canili, centri cinofili)
 */
function caniincasa_structure_data_disclaimer() {
    $disclaimer_enabled = get_theme_mod( 'gdpr_structure_disclaimer_enabled', true );

    if ( ! $disclaimer_enabled ) {
        return;
    }

    $disclaimer_text = get_theme_mod(
        'gdpr_structure_disclaimer_text',
        'I dati presenti in questa scheda sono stati raccolti da fonti pubbliche disponibili su internet, inclusi albi professionali, registri ufficiali e associazioni di categoria. Le informazioni pubblicate sono da considerarsi di carattere informativo e non costituiscono una raccomandazione o garanzia. Decliniamo ogni responsabilità riguardo all\'accuratezza, completezza o aggiornamento dei dati. Per informazioni ufficiali e aggiornate, si prega di contattare direttamente la struttura.'
    );

    ?>
    <div class="gdpr-disclaimer structure-disclaimer">
        <div class="disclaimer-icon">ℹ️</div>
        <div class="disclaimer-content">
            <strong><?php esc_html_e( 'Informativa sui Dati', 'caniincasa' ); ?></strong>
            <p><?php echo esc_html( $disclaimer_text ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Disclaimer responsabilità annunci
 * Da mostrare nelle pagine degli annunci
 */
function caniincasa_annunci_disclaimer() {
    $disclaimer_enabled = get_theme_mod( 'gdpr_annunci_disclaimer_enabled', true );

    if ( ! $disclaimer_enabled ) {
        return;
    }

    $disclaimer_text = get_theme_mod(
        'gdpr_annunci_disclaimer_text',
        'Gli annunci pubblicati sono inseriti direttamente dagli utenti. ' . get_bloginfo( 'name' ) . ' non si assume alcuna responsabilità riguardo al contenuto, all\'accuratezza o alla veridicità delle informazioni fornite negli annunci. Si raccomanda di verificare sempre le informazioni e di incontrare personalmente gli inserzionisti prima di procedere con qualsiasi transazione. Segnalate eventuali annunci sospetti o inappropriati.'
    );

    ?>
    <div class="gdpr-disclaimer annunci-disclaimer">
        <div class="disclaimer-icon">⚠️</div>
        <div class="disclaimer-content">
            <strong><?php esc_html_e( 'Responsabilità Annunci', 'caniincasa' ); ?></strong>
            <p><?php echo esc_html( $disclaimer_text ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Disclaimer razze
 * Da mostrare nelle schede razze
 */
function caniincasa_razze_disclaimer() {
    $disclaimer_enabled = get_theme_mod( 'gdpr_razze_disclaimer_enabled', true );

    if ( ! $disclaimer_enabled ) {
        return;
    }

    $disclaimer_text = get_theme_mod(
        'gdpr_razze_disclaimer_text',
        'Le informazioni fornite sulle razze canine hanno scopo puramente informativo e sono state raccolte da fonti pubbliche e riconosciute a livello cinofilo. Ogni cane è un individuo unico e può differire dalle caratteristiche generali della razza. Prima di adottare un cane, consultate sempre un veterinario e valutate attentamente le esigenze specifiche della razza in relazione al vostro stile di vita.'
    );

    ?>
    <div class="gdpr-disclaimer razze-disclaimer">
        <div class="disclaimer-icon">🐕</div>
        <div class="disclaimer-content">
            <strong><?php esc_html_e( 'Informazioni sulle Razze', 'caniincasa' ); ?></strong>
            <p><?php echo esc_html( $disclaimer_text ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Footer disclaimer generale
 * Da mostrare nel footer di ogni pagina
 */
function caniincasa_footer_disclaimer() {
    $disclaimer_enabled = get_theme_mod( 'gdpr_footer_disclaimer_enabled', true );

    if ( ! $disclaimer_enabled ) {
        return;
    }

    ?>
    <div class="gdpr-footer-disclaimer">
        <div class="container">
            <p class="disclaimer-text">
                <strong><?php esc_html_e( 'Disclaimer', 'caniincasa' ); ?>:</strong>
                <?php
                esc_html_e(
                    'I contenuti presenti su questo sito sono forniti esclusivamente a scopo informativo. I dati relativi a strutture, servizi e professionisti sono raccolti da fonti pubbliche (albi professionali, associazioni di categoria, pubblicazioni ufficiali). Non garantiamo l\'accuratezza, completezza o aggiornamento delle informazioni. L\'utente è invitato a verificare autonomamente i dati prima di prendere decisioni. Non ci assumiamo responsabilità per eventuali danni derivanti dall\'uso delle informazioni fornite.',
                    'caniincasa'
                );
                ?>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Cookie Consent Banner (GDPR Compliant)
 */
function caniincasa_cookie_consent_banner() {
    $cookie_enabled = get_theme_mod( 'gdpr_cookie_banner_enabled', true );

    if ( ! $cookie_enabled || isset( $_COOKIE['caniincasa_cookie_consent'] ) ) {
        return;
    }

    $privacy_policy_url = get_privacy_policy_url();
    ?>
    <div id="cookie-consent-banner" class="cookie-consent-banner">
        <div class="cookie-content">
            <p>
                <?php
                esc_html_e(
                    'Questo sito utilizza cookie tecnici e, previo tuo consenso, cookie di profilazione per migliorare la tua esperienza di navigazione.',
                    'caniincasa'
                );
                ?>
                <?php if ( $privacy_policy_url ) : ?>
                    <a href="<?php echo esc_url( $privacy_policy_url ); ?>" class="privacy-link">
                        <?php esc_html_e( 'Leggi la Privacy Policy', 'caniincasa' ); ?>
                    </a>
                <?php endif; ?>
            </p>
            <div class="cookie-actions">
                <button type="button" id="cookie-accept" class="cookie-btn cookie-accept">
                    <?php esc_html_e( 'Accetta Tutto', 'caniincasa' ); ?>
                </button>
                <button type="button" id="cookie-reject" class="cookie-btn cookie-reject">
                    <?php esc_html_e( 'Solo Necessari', 'caniincasa' ); ?>
                </button>
                <button type="button" id="cookie-settings" class="cookie-btn cookie-settings">
                    <?php esc_html_e( 'Impostazioni', 'caniincasa' ); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'caniincasa_cookie_consent_banner' );

/**
 * GDPR Data Source Notice
 * Mostra l'origine dei dati in modo trasparente
 */
function caniincasa_data_source_notice( $source_type = 'general' ) {
    $sources = array(
        'general'      => 'Dati raccolti da fonti pubbliche e ufficiali',
        'albi'         => 'Dati da Albi Professionali pubblici',
        'associazioni' => 'Dati da Associazioni di Categoria riconosciute',
        'enci'         => 'Dati da ENCI (Ente Nazionale Cinofilia Italiana)',
        'user'         => 'Dati inseriti dall\'utente',
    );

    $source_text = isset( $sources[ $source_type ] ) ? $sources[ $source_type ] : $sources['general'];
    ?>
    <small class="data-source-notice">
        <span class="source-icon">📋</span>
        <?php echo esc_html( $source_text ); ?>
    </small>
    <?php
}

/**
 * Privacy Info for Forms
 * Checkbox obbligatorio per il consenso privacy nei form
 */
function caniincasa_form_privacy_checkbox( $form_id = '' ) {
    $privacy_policy_url = get_privacy_policy_url();

    if ( ! $privacy_policy_url ) {
        $privacy_policy_url = home_url( '/privacy-policy/' );
    }

    ?>
    <div class="form-privacy-consent">
        <label class="privacy-label">
            <input type="checkbox" name="privacy_consent" id="privacy_consent_<?php echo esc_attr( $form_id ); ?>" required>
            <?php
            printf(
                /* translators: %s: URL della privacy policy */
                esc_html__(
                    'Accetto la %s e autorizzo il trattamento dei miei dati personali *',
                    'caniincasa'
                ),
                '<a href="' . esc_url( $privacy_policy_url ) . '" target="_blank">' . esc_html__( 'Privacy Policy', 'caniincasa' ) . '</a>'
            );
            ?>
        </label>
        <p class="privacy-notice">
            <small>
                <?php
                esc_html_e(
                    'I tuoi dati saranno trattati in conformità al Regolamento UE 2016/679 (GDPR) e utilizzati esclusivamente per le finalità indicate nella Privacy Policy.',
                    'caniincasa'
                );
                ?>
            </small>
        </p>
    </div>
    <?php
}

/**
 * Enqueue GDPR styles and scripts
 */
function caniincasa_enqueue_gdpr_assets() {
    wp_enqueue_style(
        'caniincasa-gdpr',
        get_template_directory_uri() . '/assets/css/gdpr.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'caniincasa-gdpr',
        get_template_directory_uri() . '/assets/js/gdpr.js',
        array(),
        '1.0.0',
        true
    );

    wp_localize_script(
        'caniincasa-gdpr',
        'caniin casaGDPR',
        array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'caniincasa_gdpr_nonce' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'caniincasa_enqueue_gdpr_assets' );

/**
 * Set cookie consent via AJAX
 */
function caniincasa_set_cookie_consent() {
    check_ajax_referer( 'caniincasa_gdpr_nonce', 'nonce' );

    $consent_type = isset( $_POST['consent_type'] ) ? sanitize_text_field( $_POST['consent_type'] ) : 'necessary';

    setcookie( 'caniincasa_cookie_consent', $consent_type, time() + ( 365 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

    wp_send_json_success( array( 'message' => 'Consenso salvato' ) );
}
add_action( 'wp_ajax_set_cookie_consent', 'caniincasa_set_cookie_consent' );
add_action( 'wp_ajax_nopriv_set_cookie_consent', 'caniincasa_set_cookie_consent' );
