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
    $razze_allevate = get_field( 'razze_allevate' ); // Relationship field con razze_di_cani
    ?>

    <main id="main-content" class="site-main single-struttura single-allevamento">

        <!-- Hero Section -->
        <div class="single-hero">
            <div class="container">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <p class="entry-subtitle">Allevamento</p>
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

                            <?php if ( $proprietario ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Proprietario:</span>
                                    <span class="info-value"><?php echo esc_html( $proprietario ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $persona ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Referente:</span>
                                    <span class="info-value"><?php echo esc_html( $persona ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $indirizzo || $localita || $provincia || $cap ) : ?>
                                <div class="info-item full-width">
                                    <span class="info-label">Indirizzo:</span>
                                    <span class="info-value">
                                        <?php if ( $indirizzo ) : ?>
                                            <?php echo esc_html( $indirizzo ); ?><br>
                                        <?php endif; ?>
                                        <?php
                                        $address_parts = array_filter( array(
                                            $cap,
                                            $localita,
                                            $provincia ? '(' . $provincia . ')' : '',
                                        ) );
                                        echo esc_html( implode( ' ', $address_parts ) );
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $telefono ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Telefono:</span>
                                    <span class="info-value"><?php echo esc_html( $telefono ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $email ) : ?>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value"><?php echo esc_html( $email ); ?></span>
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
                    <?php if ( $razze_allevate && is_array( $razze_allevate ) && count( $razze_allevate ) > 0 ) : ?>
                        <div class="razze-allevate-section">
                            <h2 class="section-title">Razze Allevate</h2>
                            <div class="razze-grid razze-grid-compact">
                                <?php
                                foreach ( $razze_allevate as $razza_post ) :
                                    // Set up post data for the razza
                                    setup_postdata( $GLOBALS['post'] =& $razza_post );

                                    // Include the razza card template
                                    get_template_part( 'template-parts/content/content', 'razza-card' );
                                endforeach;
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CTA Box Annunci Cuccioli -->
                    <div class="annunci-cuccioli-cta-box">
                        <div class="cta-content">
                            <div class="cta-icon">🐾</div>
                            <h3 class="cta-title">Cerchi o Offri Cuccioli?</h3>
                            <p class="cta-description">Pubblica il tuo annuncio gratuitamente e raggiungi migliaia di appassionati!</p>

                            <div class="cta-features">
                                <div class="cta-feature">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#4CAF50">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                    <span>Annunci verificati</span>
                                </div>
                                <div class="cta-feature">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#4CAF50">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                    <span>Visibilità garantita</span>
                                </div>
                                <div class="cta-feature">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#4CAF50">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                    <span>Contatti diretti</span>
                                </div>
                            </div>

                            <?php if ( is_user_logged_in() ) : ?>
                                <a href="<?php echo esc_url( home_url( '/inserisci-annuncio/' ) ); ?>" class="btn btn-cta-primary">
                                    Inserisci Annuncio
                                </a>
                            <?php else : ?>
                                <button type="button" class="btn btn-cta-primary js-open-annuncio-modal">
                                    Inserisci Annuncio
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="struttura-sidebar">

                    <!-- Owner Box -->
                    <?php caniincasa_struttura_claim_buttons( get_the_ID(), 'allevamenti' ); ?>

                    <!-- Back to Search Box -->
                    <div class="sidebar-box back-search-box">
                        <h3 class="box-title">Cerca Altri Allevamenti</h3>
                        <p>Torna all'archivio per cercare altri allevamenti.</p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'allevamenti' ) ); ?>" class="btn btn-secondary btn-block">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"/>
                            </svg>
                            Torna alla Ricerca
                        </a>
                    </div>

                    <!-- Preferiti Box -->
                    <?php if ( is_user_logged_in() ) : ?>
                    <div class="sidebar-box preferiti-action-box">
                        <h3 class="box-title">Ti piace questo allevamento?</h3>
                        <?php echo caniincasa_get_preferiti_button( get_the_ID(), 'allevamenti' ); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Proponi Struttura Box -->
                    <div class="sidebar-box proponi-box">
                        <h3 class="box-title">Hai un allevamento?</h3>
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
