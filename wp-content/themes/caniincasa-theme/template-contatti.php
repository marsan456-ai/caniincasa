<?php
/**
 * Template Name: Contatti
 *
 * Template per la pagina Contatti
 * Tutti i contenuti sono personalizzabili da Aspetto → Personalizza
 *
 * @package Caniincasa
 */

get_header();

// Handle form submission
$form_success = false;
$form_error = false;

if ( isset( $_POST['contact_form_submit'] ) && wp_verify_nonce( $_POST['contact_form_nonce'], 'contact_form' ) ) {
    $name = sanitize_text_field( $_POST['contact_name'] );
    $email = sanitize_email( $_POST['contact_email'] );
    $phone = sanitize_text_field( $_POST['contact_phone'] ?? '' );
    $subject = sanitize_text_field( $_POST['contact_subject'] );
    $message = sanitize_textarea_field( $_POST['contact_message'] );

    // Validate
    if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
        $form_error = 'Compila tutti i campi obbligatori.';
    } elseif ( ! is_email( $email ) ) {
        $form_error = 'Inserisci un indirizzo email valido.';
    } else {
        // Send email to admin
        $admin_email = get_option( 'admin_email' );
        $email_subject = 'Nuovo messaggio da: ' . $name . ' - ' . $subject;
        $email_body = sprintf(
            "Hai ricevuto un nuovo messaggio dal form di contatto.\n\n" .
            "Nome: %s\n" .
            "Email: %s\n" .
            "Telefono: %s\n" .
            "Oggetto: %s\n\n" .
            "Messaggio:\n%s",
            $name,
            $email,
            $phone,
            $subject,
            $message
        );

        $headers = array(
            'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
            'Reply-To: ' . $email,
        );

        if ( wp_mail( $admin_email, $email_subject, $email_body, $headers ) ) {
            $form_success = true;

            // Send confirmation email to user
            $user_subject = 'Conferma ricezione messaggio - ' . get_bloginfo( 'name' );
            $user_message = sprintf(
                "Ciao %s,\n\n" .
                "Grazie per averci contattato! Abbiamo ricevuto il tuo messaggio e ti risponderemo il prima possibile.\n\n" .
                "Riepilogo del tuo messaggio:\n" .
                "Oggetto: %s\n\n" .
                "Messaggio:\n%s\n\n" .
                "Cordiali saluti,\n" .
                "Il Team di %s",
                $name,
                $subject,
                $message,
                get_bloginfo( 'name' )
            );

            wp_mail( $email, $user_subject, $user_message );
        } else {
            $form_error = 'Si è verificato un errore. Riprova più tardi.';
        }
    }
}
?>

<main class="contatti-page">
    <!-- Hero Section -->
    <section class="contatti-hero">
        <?php
        $hero_image = get_theme_mod( 'contatti_hero_image', '' );
        if ( $hero_image ) :
        ?>
            <div class="hero-image" style="background-image: url('<?php echo esc_url( $hero_image ); ?>');">
                <div class="hero-overlay"></div>
            </div>
        <?php endif; ?>

        <div class="container">
            <div class="hero-content">
                <h1 class="page-title">
                    <?php
                    $title = get_theme_mod( 'contatti_title', 'Contatti' );
                    echo esc_html( $title );
                    ?>
                </h1>
                <?php
                $subtitle = get_theme_mod( 'contatti_subtitle', 'Siamo qui per aiutarti' );
                if ( $subtitle ) :
                ?>
                    <p class="page-subtitle">
                        <?php echo esc_html( $subtitle ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Info and Form -->
    <section class="contact-section section-padding">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2 class="section-title">
                        <?php
                        $info_title = get_theme_mod( 'contatti_info_title', 'Informazioni di Contatto' );
                        echo esc_html( $info_title );
                        ?>
                    </h2>

                    <?php
                    $intro_text = get_theme_mod( 'contatti_intro_text', 'Hai domande o suggerimenti? Contattaci!' );
                    if ( $intro_text ) :
                    ?>
                        <p class="contact-intro"><?php echo esc_html( $intro_text ); ?></p>
                    <?php endif; ?>

                    <div class="contact-details">
                        <?php
                        $email = get_theme_mod( 'contatti_email', get_option( 'admin_email' ) );
                        if ( $email ) :
                        ?>
                            <div class="contact-detail-item">
                                <span class="detail-icon">✉️</span>
                                <div class="detail-content">
                                    <strong>Email</strong>
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>">
                                        <?php echo esc_html( $email ); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $phone = get_theme_mod( 'contatti_phone', '' );
                        if ( $phone ) :
                        ?>
                            <div class="contact-detail-item">
                                <span class="detail-icon">📞</span>
                                <div class="detail-content">
                                    <strong>Telefono</strong>
                                    <a href="tel:<?php echo esc_attr( str_replace( ' ', '', $phone ) ); ?>">
                                        <?php echo esc_html( $phone ); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $address = get_theme_mod( 'contatti_address', '' );
                        if ( $address ) :
                        ?>
                            <div class="contact-detail-item">
                                <span class="detail-icon">📍</span>
                                <div class="detail-content">
                                    <strong>Indirizzo</strong>
                                    <p><?php echo nl2br( esc_html( $address ) ); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $hours = get_theme_mod( 'contatti_hours', '' );
                        if ( $hours ) :
                        ?>
                            <div class="contact-detail-item">
                                <span class="detail-icon">🕐</span>
                                <div class="detail-content">
                                    <strong>Orari</strong>
                                    <p><?php echo nl2br( esc_html( $hours ) ); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    // Social Media Links
                    $show_social = get_theme_mod( 'contatti_show_social', true );
                    if ( $show_social ) :
                        $facebook = get_theme_mod( 'social_facebook', '' );
                        $instagram = get_theme_mod( 'social_instagram', '' );
                        $twitter = get_theme_mod( 'social_twitter', '' );

                        if ( $facebook || $instagram || $twitter ) :
                    ?>
                        <div class="contact-social">
                            <h3>Seguici sui Social</h3>
                            <div class="social-links">
                                <?php if ( $facebook ) : ?>
                                    <a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener" class="social-link facebook">
                                        <span class="icon">📘</span>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $instagram ) : ?>
                                    <a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener" class="social-link instagram">
                                        <span class="icon">📷</span>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $twitter ) : ?>
                                    <a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener" class="social-link twitter">
                                        <span class="icon">🐦</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                        endif;
                    endif;
                    ?>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-wrapper">
                    <h2 class="section-title">
                        <?php
                        $form_title = get_theme_mod( 'contatti_form_title', 'Inviaci un Messaggio' );
                        echo esc_html( $form_title );
                        ?>
                    </h2>

                    <?php if ( $form_success ) : ?>
                        <div class="form-message success">
                            <strong>✓ Messaggio inviato con successo!</strong>
                            <p>Ti risponderemo il prima possibile.</p>
                        </div>
                    <?php elseif ( $form_error ) : ?>
                        <div class="form-message error">
                            <strong>✗ Errore</strong>
                            <p><?php echo esc_html( $form_error ); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="contact-form" id="contact-form">
                        <?php wp_nonce_field( 'contact_form', 'contact_form_nonce' ); ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_name">Nome e Cognome *</label>
                                <input type="text" id="contact_name" name="contact_name" required
                                       value="<?php echo isset( $_POST['contact_name'] ) ? esc_attr( $_POST['contact_name'] ) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="contact_email">Email *</label>
                                <input type="email" id="contact_email" name="contact_email" required
                                       value="<?php echo isset( $_POST['contact_email'] ) ? esc_attr( $_POST['contact_email'] ) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_phone">Telefono</label>
                                <input type="tel" id="contact_phone" name="contact_phone"
                                       value="<?php echo isset( $_POST['contact_phone'] ) ? esc_attr( $_POST['contact_phone'] ) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="contact_subject">Oggetto *</label>
                                <input type="text" id="contact_subject" name="contact_subject" required
                                       value="<?php echo isset( $_POST['contact_subject'] ) ? esc_attr( $_POST['contact_subject'] ) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_message">Messaggio *</label>
                            <textarea id="contact_message" name="contact_message" rows="6" required><?php echo isset( $_POST['contact_message'] ) ? esc_textarea( $_POST['contact_message'] ) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="contact_form_submit" class="btn btn-primary btn-large">
                                Invia Messaggio
                            </button>
                        </div>

                        <p class="form-note">* Campi obbligatori</p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section (optional) -->
    <?php
    $map_enabled = get_theme_mod( 'contatti_map_enabled', false );
    $map_embed = get_theme_mod( 'contatti_map_embed', '' );

    if ( $map_enabled && $map_embed ) :
    ?>
        <section class="contact-map">
            <div class="map-container">
                <?php echo wp_kses_post( $map_embed ); ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
