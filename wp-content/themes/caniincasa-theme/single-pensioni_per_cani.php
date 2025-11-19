<?php
/**
 * Template for Single Pensione per Cani
 *
 * @package Caniincasa
 */

get_header();

while ( have_posts() ) :
    the_post();

    // Get ACF fields
    $nome_struttura = get_field( 'nome_struttura' );
    $indirizzo      = get_field( 'indirizzo' );
    $comune         = get_field( 'comune' );
    $provincia      = get_field( 'provincia' );
    $regione        = get_field( 'regione' );
    $cap            = get_field( 'cap' );
    $telefono       = get_field( 'telefono' );
    $email          = get_field( 'email' );
    $sito_web       = get_field( 'sito_web' );
    $referente      = get_field( 'referente' );
    $altre_informazioni = get_field( 'altre_informazioni' );
    ?>

    <main id="main-content" class="site-main single-struttura single-pensione">

        <!-- Hero Section -->
        <div class="single-hero">
            <div class="container">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <p class="entry-subtitle">Pensione per Cani</p>
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

                            <?php if ( $referente ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Referente:</span>
                                    <span class="info-value"><?php echo esc_html( $referente ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $indirizzo || $comune || $provincia || $cap ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Indirizzo:</span>
                                    <span class="info-value">
                                        <?php if ( $indirizzo ) : ?>
                                            <?php echo esc_html( $indirizzo ); ?><br>
                                        <?php endif; ?>
                                        <?php
                                        $address_parts = array_filter( array(
                                            $cap,
                                            $comune,
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

                            <!-- Altre Informazioni -->
                            <?php if ( $altre_informazioni ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Altre Informazioni:</span>
                                    <span class="info-value"><?php echo nl2br( esc_html( $altre_informazioni ) ); ?></span>
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

                    <!-- Owner Box -->
                    <?php caniincasa_struttura_claim_buttons( get_the_ID(), 'pensioni_per_cani' ); ?>

                    <!-- Back to Search Box -->
                    <div class="sidebar-box back-search-box">
                        <h3 class="box-title">Cerca Altre Pensioni</h3>
                        <p>Torna all'archivio per cercare altre pensioni per cani.</p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'pensioni_per_cani' ) ); ?>" class="btn btn-secondary btn-block">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"/>
                            </svg>
                            Torna alla Ricerca
                        </a>
                    </div>

                    <!-- Preferiti Box -->
                    <?php if ( is_user_logged_in() ) : ?>
                    <div class="sidebar-box preferiti-action-box">
                        <h3 class="box-title">Ti piace questa pensione?</h3>
                        <?php echo caniincasa_get_preferiti_button( get_the_ID(), 'pensioni_per_cani' ); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Proponi Struttura Box -->
                    <div class="sidebar-box proponi-box">
                        <h3 class="box-title">Hai una pensione per cani?</h3>
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
