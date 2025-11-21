<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Caniincasa
 */
?>

<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( 'Nessun contenuto trovato', 'caniincasa' ); ?></h1>
    </header>

    <div class="page-content">
        <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

            <p>
                <?php
                printf(
                    wp_kses(
                        __( 'Pronto a pubblicare il tuo primo post? <a href="%1$s">Inizia da qui</a>.', 'caniincasa' ),
                        array(
                            'a' => array(
                                'href' => array(),
                            ),
                        )
                    ),
                    esc_url( admin_url( 'post-new.php' ) )
                );
                ?>
            </p>

        <?php elseif ( is_search() ) : ?>

            <p><?php esc_html_e( 'Spiacenti, nessun risultato trovato. Prova con parole chiave diverse.', 'caniincasa' ); ?></p>
            <?php get_search_form(); ?>

        <?php else : ?>

            <p><?php esc_html_e( 'Sembra che non ci sia nulla qui. Che ne dici di provare una ricerca?', 'caniincasa' ); ?></p>
            <?php get_search_form(); ?>

        <?php endif; ?>
    </div>
</section>
