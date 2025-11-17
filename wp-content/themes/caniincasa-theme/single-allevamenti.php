<?php
/**
 * Template for Single Allevamento
 *
 * @package Caniincasa
 */

get_header();

while ( have_posts() ) :
    the_post();

    // Get ACF fields
    $persona       = get_field( 'persona' );
    $indirizzo     = get_field( 'indirizzo' );
    $localita      = get_field( 'localita' );
    $provincia     = get_field( 'provincia' );
    $cap           = get_field( 'cap' );
    $telefono      = get_field( 'telefono' );
    $email         = get_field( 'email' );
    $sito_web      = get_field( 'sito_web' );
    $affisso       = get_field( 'affisso' );
    $proprietario  = get_field( 'proprietario' );
    $id_affisso    = get_field( 'id_affisso' );
    ?>

    <main id="main-content" class="site-main single-struttura single-allevamento">

        <!-- Hero Section -->
        <div class="struttura-hero">
            <div class="container">
                <div class="hero-content">
                    <div class="breadcrumbs-wrapper">
                        <?php caniincasa_breadcrumbs(); ?>
                    </div>
                    <h1 class="struttura-title"><?php the_title(); ?></h1>

                    <?php if ( $affisso ) : ?>
                        <p class="struttura-subtitle">Affisso: <?php echo esc_html( $affisso ); ?></p>
                    <?php endif; ?>

                    <div class="struttura-meta">
                        <?php if ( $localita || $provincia ) : ?>
                            <span class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                </svg>
                                <?php
                                $location_parts = array_filter( array( $localita, $provincia ) );
                                echo esc_html( implode( ', ', $location_parts ) );
                                ?>
                            </span>
                        <?php endif; ?>

                        <?php if ( $telefono ) : ?>
                            <span class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                                </svg>
                                <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>">
                                    <?php echo esc_html( $telefono ); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="struttura-content-wrapper">

                <!-- Main Content -->
                <div class="struttura-main-content">

                    <!-- Info Box -->
                    <div class="struttura-info-box">
                        <h2 class="box-title">Informazioni Allevamento</h2>

                        <div class="info-grid">
                            <?php if ( $persona ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Referente:</span>
                                    <span class="info-value"><?php echo esc_html( $persona ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $proprietario ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Proprietario:</span>
                                    <span class="info-value"><?php echo esc_html( $proprietario ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $affisso ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Affisso:</span>
                                    <span class="info-value"><?php echo esc_html( $affisso ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $id_affisso ) : ?>
                                <div class="info-item">
                                    <span class="info-label">ID Affisso:</span>
                                    <span class="info-value"><?php echo esc_html( $id_affisso ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <?php if ( get_the_content() ) : ?>
                        <div class="struttura-description">
                            <h2>Descrizione</h2>
                            <div class="description-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Razze Allevate -->
                    <?php
                    // This would be populated from the CSV "Razze Allevamenti" column
                    // For now, we can show it if it exists as a custom field
                    ?>

                </div>

                <!-- Sidebar -->
                <aside class="struttura-sidebar">

                    <!-- Contact Box -->
                    <div class="sidebar-box contact-box">
                        <h3 class="box-title">Contatti</h3>

                        <?php if ( $indirizzo || $localita || $provincia || $cap ) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div class="contact-details">
                                    <strong>Indirizzo</strong>
                                    <?php if ( $indirizzo ) : ?>
                                        <p><?php echo esc_html( $indirizzo ); ?></p>
                                    <?php endif; ?>
                                    <p>
                                        <?php
                                        $address_parts = array_filter( array(
                                            $cap,
                                            $localita,
                                            $provincia ? '(' . $provincia . ')' : '',
                                        ) );
                                        echo esc_html( implode( ' ', $address_parts ) );
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $telefono ) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div class="contact-details">
                                    <strong>Telefono</strong>
                                    <p>
                                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>">
                                            <?php echo esc_html( $telefono ); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $email ) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div class="contact-details">
                                    <strong>Email</strong>
                                    <p>
                                        <a href="mailto:<?php echo esc_attr( $email ); ?>">
                                            <?php echo esc_html( $email ); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $sito_web ) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div class="contact-details">
                                    <strong>Sito Web</strong>
                                    <p>
                                        <a href="<?php echo esc_url( $sito_web ); ?>" target="_blank" rel="noopener">
                                            Visita il sito
                                        </a>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- CTA Box -->
                    <div class="sidebar-box cta-box">
                        <h3 class="box-title">Richiedi Informazioni</h3>
                        <p>Interessato a questa struttura?</p>

                        <?php if ( $telefono ) : ?>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>" class="btn btn-primary btn-block">
                                Chiama Ora
                            </a>
                        <?php endif; ?>

                        <?php if ( $email ) : ?>
                            <a href="mailto:<?php echo esc_attr( $email ); ?>" class="btn btn-secondary btn-block">
                                Invia Email
                            </a>
                        <?php endif; ?>

                        <?php if ( caniincasa_is_whatsapp_supported() && $telefono ) : ?>
                            <a href="<?php echo esc_url( caniincasa_get_whatsapp_link( $telefono, 'Ciao, ho visto il vostro allevamento su Caniincasa.it' ) ); ?>"
                               target="_blank"
                               rel="noopener"
                               class="btn btn-success btn-block">
                                <svg width="16" height="16" style="margin-right: 5px;" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Share Box -->
                    <div class="sidebar-box share-box">
                        <h3 class="box-title">Condividi</h3>
                        <?php caniincasa_social_share(); ?>
                    </div>

                </aside>

            </div>
        </div>

    </main>

    <?php
endwhile;

get_footer();
