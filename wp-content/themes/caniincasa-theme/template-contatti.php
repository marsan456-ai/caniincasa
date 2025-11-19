<?php
/**
 * Template Name: Contatti
 *
 * @package Caniincasa
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main contatti-page">

    <?php while ( have_posts() ) : the_post(); ?>

        <!-- Page Hero -->
        <div class="page-hero">
            <div class="container">
                <h1 class="page-title">
                    <?php echo esc_html( get_theme_mod( 'contatti_title', get_the_title() ) ); ?>
                </h1>
                <?php if ( get_theme_mod( 'contatti_subtitle' ) ) : ?>
                    <p class="page-description">
                        <?php echo esc_html( get_theme_mod( 'contatti_subtitle', '' ) ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="container">
            <div class="breadcrumbs-wrapper">
                <?php caniincasa_breadcrumbs(); ?>
            </div>
        </div>

        <!-- Contatti Content -->
        <div class="container">
            <div class="contatti-wrapper">

                <!-- Contact Info Section -->
                <section class="contatti-info-section section-padding">
                    <div class="contatti-grid">

                        <!-- Contact Form -->
                        <div class="contact-form-wrapper">
                            <h2><?php echo esc_html( get_theme_mod( 'contatti_form_title', 'Inviaci un Messaggio' ) ); ?></h2>
                            <?php if ( get_theme_mod( 'contatti_form_text' ) ) : ?>
                                <p class="form-intro"><?php echo esc_html( get_theme_mod( 'contatti_form_text', '' ) ); ?></p>
                            <?php endif; ?>

                            <!-- Contact Form 7 Shortcode -->
                            <?php
                            $contact_form_shortcode = get_theme_mod( 'contatti_form_shortcode', '' );
                            if ( $contact_form_shortcode ) {
                                echo do_shortcode( $contact_form_shortcode );
                            } else {
                                // Messaggio per amministratori se shortcode mancante
                                if ( current_user_can( 'edit_theme_options' ) ) {
                                    ?>
                                    <div class="admin-notice" style="background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px;">
                                        <p style="margin: 0; color: #856404;">
                                            <strong>⚠️ Attenzione Amministratore:</strong><br>
                                            Nessun form di contatto configurato.
                                            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=contatti_form' ) ); ?>" style="color: #0066cc; text-decoration: underline;">
                                                Vai al Customizer per inserire lo shortcode di Contact Form 7
                                            </a>
                                        </p>
                                    </div>
                                    <?php
                                } else {
                                    // Per utenti normali, mostra messaggio generico
                                    ?>
                                    <div class="contact-form-placeholder" style="background: #f8f9fa; padding: 40px; text-align: center; border-radius: 8px;">
                                        <p style="margin: 0; color: #6c757d;">
                                            <?php esc_html_e( 'Il modulo di contatto sarà disponibile a breve.', 'caniincasa' ); ?>
                                        </p>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <!-- Contact Info Sidebar -->
                        <div class="contact-info-sidebar">

                            <!-- Informazioni di Contatto -->
                            <?php if ( get_theme_mod( 'contatti_show_info', true ) ) : ?>
                            <div class="contact-info-box">
                                <h3><?php echo esc_html( get_theme_mod( 'contatti_info_title', 'Informazioni di Contatto' ) ); ?></h3>

                                <?php if ( get_theme_mod( 'contatti_address' ) ) : ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <div class="info-text">
                                        <strong><?php esc_html_e( 'Indirizzo', 'caniincasa' ); ?></strong>
                                        <p><?php echo esc_html( get_theme_mod( 'contatti_address', '' ) ); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ( get_theme_mod( 'contatti_phone' ) ) : ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <div class="info-text">
                                        <strong><?php esc_html_e( 'Telefono', 'caniincasa' ); ?></strong>
                                        <p><a href="tel:<?php echo esc_attr( str_replace( ' ', '', get_theme_mod( 'contatti_phone', '' ) ) ); ?>">
                                            <?php echo esc_html( get_theme_mod( 'contatti_phone', '' ) ); ?>
                                        </a></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ( get_theme_mod( 'contatti_email' ) ) : ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <div class="info-text">
                                        <strong><?php esc_html_e( 'Email', 'caniincasa' ); ?></strong>
                                        <p><a href="mailto:<?php echo esc_attr( get_theme_mod( 'contatti_email', '' ) ); ?>">
                                            <?php echo esc_html( get_theme_mod( 'contatti_email', '' ) ); ?>
                                        </a></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ( get_theme_mod( 'contatti_whatsapp' ) ) : ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <div class="info-text">
                                        <strong><?php esc_html_e( 'WhatsApp', 'caniincasa' ); ?></strong>
                                        <p><a href="https://wa.me/<?php echo esc_attr( str_replace( ' ', '', get_theme_mod( 'contatti_whatsapp', '' ) ) ); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html( get_theme_mod( 'contatti_whatsapp', '' ) ); ?>
                                        </a></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Orari di Apertura -->
                            <?php if ( get_theme_mod( 'contatti_show_hours', false ) ) : ?>
                            <div class="contact-hours-box">
                                <h3><?php echo esc_html( get_theme_mod( 'contatti_hours_title', 'Orari di Apertura' ) ); ?></h3>
                                <div class="hours-content">
                                    <?php echo wp_kses_post( wpautop( get_theme_mod( 'contatti_hours_text', '' ) ) ); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Social Links -->
                            <?php if ( get_theme_mod( 'contatti_show_social', true ) ) : ?>
                            <div class="contact-social-box">
                                <h3><?php echo esc_html( get_theme_mod( 'contatti_social_title', 'Seguici sui Social' ) ); ?></h3>
                                <div class="social-links">
                                    <?php
                                    $social_links = array(
                                        'facebook'  => array( 'label' => 'Facebook', 'icon' => 'facebook' ),
                                        'instagram' => array( 'label' => 'Instagram', 'icon' => 'instagram' ),
                                        'twitter'   => array( 'label' => 'Twitter', 'icon' => 'twitter' ),
                                        'youtube'   => array( 'label' => 'YouTube', 'icon' => 'youtube' ),
                                    );

                                    foreach ( $social_links as $key => $data ) :
                                        $url = get_theme_mod( "contatti_social_{$key}" );
                                        if ( $url ) :
                                    ?>
                                        <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="social-link" aria-label="<?php echo esc_attr( $data['label'] ); ?>">
                                            <span class="social-icon"><?php echo esc_html( $data['label'] ); ?></span>
                                        </a>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>

                    </div>
                </section>

                <!-- Mappa Section -->
                <?php if ( get_theme_mod( 'contatti_show_map', true ) && get_theme_mod( 'contatti_map_embed' ) ) : ?>
                <section class="contatti-map-section section-padding">
                    <h2 class="section-title text-center"><?php echo esc_html( get_theme_mod( 'contatti_map_title', 'Dove Siamo' ) ); ?></h2>
                    <div class="map-container">
                        <?php echo wp_kses_post( get_theme_mod( 'contatti_map_embed', '' ) ); ?>
                    </div>
                </section>
                <?php endif; ?>

            </div>
        </div>

    <?php endwhile; ?>

</main>

<?php
get_footer();
