<?php
/**
 * Template for Single Veterinario / Struttura Veterinaria
 *
 * @package Caniincasa
 */

get_header();

while ( have_posts() ) :
    the_post();

    // Get ACF fields
    $nome_struttura     = get_field( 'nome_struttura' );
    $tipologia          = get_field( 'tipologia' );
    $direttore_sanitario = get_field( 'direttore_sanitario' );
    $indirizzo          = get_field( 'indirizzo' );
    $localita           = get_field( 'localita' );
    $comune             = get_field( 'comune' );
    $provincia          = get_field( 'provincia' );
    $regione            = get_field( 'regione' );
    $cap                = get_field( 'cap' );
    $telefono           = get_field( 'telefono' );
    $email              = get_field( 'email' );
    $sito_web           = get_field( 'sito_web' );
    $pronto_soccorso    = get_field( 'pronto_soccorso' );
    $reperibilita       = get_field( 'reperibilita' );
    $specie_trattate    = get_field( 'specie_trattate' );
    $servizi            = get_field( 'servizi' );
    $orari              = get_field( 'orari' );
    ?>

    <main id="main-content" class="site-main single-struttura single-veterinario">

        <!-- Hero Section -->
        <div class="single-hero">
            <div class="container">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <p class="entry-subtitle">
                    <?php echo $tipologia ? esc_html( $tipologia ) : 'Struttura Veterinaria'; ?>
                </p>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="container">
            <div class="breadcrumbs-wrapper">
                <?php caniincasa_breadcrumbs(); ?>
            </div>
        </div>

        <div class="container">
            <div class="struttura-content-wrapper">

                <!-- Main Content -->
                <div class="struttura-main-content">

                    <!-- Info & Contact Unified Box -->
                    <div class="struttura-info-box">
                        <h2 class="box-title">Informazioni e Contatti</h2>

                        <div class="info-grid">
                            <?php if ( $nome_struttura ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Nome Struttura:</span>
                                    <span class="info-value"><?php echo esc_html( $nome_struttura ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $tipologia ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Tipologia:</span>
                                    <span class="info-value"><?php echo esc_html( $tipologia ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $direttore_sanitario ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Direttore Sanitario:</span>
                                    <span class="info-value"><?php echo esc_html( $direttore_sanitario ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $indirizzo || $localita || $comune || $provincia || $cap ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Indirizzo:</span>
                                    <span class="info-value">
                                        <?php if ( $indirizzo ) : ?>
                                            <?php echo esc_html( $indirizzo ); ?><br>
                                        <?php endif; ?>
                                        <?php
                                        $address_parts = array_filter( array(
                                            $cap,
                                            $localita ? $localita : $comune,
                                            $provincia ? '(' . $provincia . ')' : '',
                                        ) );
                                        echo esc_html( implode( ' ', $address_parts ) );
                                        ?>
                                        <?php if ( $regione ) : ?>
                                            <br><?php echo esc_html( $regione ); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $telefono ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Telefono:</span>
                                    <span class="info-value">
                                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>">
                                            <?php echo esc_html( $telefono ); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $email ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value">
                                        <a href="mailto:<?php echo esc_attr( $email ); ?>">
                                            <?php echo esc_html( $email ); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $sito_web ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Sito Web:</span>
                                    <span class="info-value">
                                        <a href="<?php echo esc_url( $sito_web ); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html( $sito_web ); ?>
                                        </a>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Servizi e Disponibilità -->
                            <?php if ( $pronto_soccorso ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Pronto Soccorso H24:</span>
                                    <span class="info-value"><?php echo esc_html( $pronto_soccorso ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $reperibilita ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Reperibilità H24:</span>
                                    <span class="info-value"><?php echo esc_html( $reperibilita ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $specie_trattate ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Specie Animali Trattate:</span>
                                    <span class="info-value"><?php echo nl2br( esc_html( $specie_trattate ) ); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Orari di Apertura -->
                            <?php if ( $orari ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Orari di Apertura:</span>
                                    <span class="info-value"><?php echo wp_kses_post( $orari ); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Servizi Offerti -->
                            <?php if ( $servizi ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Servizi Offerti:</span>
                                    <span class="info-value"><?php echo nl2br( esc_html( $servizi ) ); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Descrizione -->
                            <?php if ( get_the_content() ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Descrizione:</span>
                                    <span class="info-value"><?php the_content(); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="struttura-sidebar">

                    <!-- Urgenze Box (if H24 service available) -->
                    <?php if ( $pronto_soccorso && strtolower( $pronto_soccorso ) !== 'no' ) : ?>
                        <div class="sidebar-box urgenze-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h3 class="box-title" style="color: white;">🚨 Pronto Soccorso H24</h3>
                            <p style="margin: 10px 0;">Questa struttura offre servizio di pronto soccorso 24 ore su 24.</p>
                            <?php if ( $telefono ) : ?>
                                <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $telefono ) ); ?>" class="btn btn-light btn-block" style="background: white; color: #667eea; font-weight: 600;">
                                    📞 Chiama Ora
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Owner Box -->
                    <?php caniincasa_struttura_claim_buttons( get_the_ID(), 'veterinari' ); ?>

                    <!-- Back to Search Box -->
                    <div class="sidebar-box back-search-box">
                        <h3 class="box-title">Cerca Altri Veterinari</h3>
                        <p>Torna all'archivio per cercare altre strutture veterinarie.</p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'veterinari' ) ); ?>" class="btn btn-secondary btn-block">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"/>
                            </svg>
                            Torna alla Ricerca
                        </a>
                    </div>

                    <!-- Preferiti Box -->
                    <?php if ( is_user_logged_in() ) : ?>
                    <div class="sidebar-box preferiti-action-box">
                        <h3 class="box-title">Ti piace questa struttura?</h3>
                        <?php echo caniincasa_get_preferiti_button( get_the_ID(), 'veterinari' ); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Proponi Struttura Box -->
                    <div class="sidebar-box proponi-box">
                        <h3 class="box-title">Hai una struttura veterinaria?</h3>
                        <p>Proponi la tua struttura per essere inserito nel nostro database e raggiungere più clienti.</p>
                        <a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="btn btn-primary btn-block">
                            Proponi la tua struttura
                        </a>
                    </div>

                    <!-- Share Box -->
                    <div class="sidebar-box share-box">
                        <h3 class="box-title">Condividi</h3>
                        <?php caniincasa_social_share_buttons(); ?>
                    </div>

                </aside>

            </div>
        </div>

    </main>

    <?php
endwhile;

get_footer();
