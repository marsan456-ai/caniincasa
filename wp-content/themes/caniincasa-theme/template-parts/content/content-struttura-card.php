<?php
/**
 * Template part for displaying Generic Struttura Card
 * Works for: veterinari, canili, pensioni_per_cani, centri_cinofili
 *
 * @package Caniincasa
 */

$indirizzo = get_field( 'indirizzo' );
$citta     = get_field( 'citta' );
$provincia = get_field( 'provincia' );
$telefono  = get_field( 'telefono' );
$email     = get_field( 'email' );

// Get post type labels
$post_type = get_post_type();
$post_type_obj = get_post_type_object( $post_type );
$tipo_struttura = $post_type_obj->labels->singular_name;
?>

<article <?php post_class( 'struttura-card-item' ); ?> data-id="<?php the_ID(); ?>">
    <a href="<?php the_permalink(); ?>" class="struttura-card-link">

        <!-- Header -->
        <div class="struttura-card-header">
            <h3 class="struttura-card-title"><?php the_title(); ?></h3>

            <?php if ( $provincia ) : ?>
                <p class="struttura-card-subtitle"><?php echo esc_html( $provincia ); ?></p>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="struttura-card-content">

            <!-- Location -->
            <?php if ( $indirizzo || $citta || $provincia ) : ?>
                <div class="card-info-item">
                    <span class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="info-text">
                        <?php
                        $location_parts = array();
                        if ( $indirizzo ) {
                            $location_parts[] = $indirizzo;
                        }
                        if ( $citta && $citta !== $indirizzo ) {
                            $location_parts[] = $citta;
                        }
                        if ( $provincia ) {
                            $location_parts[] = $provincia;
                        }
                        echo esc_html( implode( ', ', array_filter( $location_parts ) ) );
                        ?>
                    </span>
                </div>
            <?php endif; ?>

        </div>

        <!-- Footer -->
        <div class="struttura-card-footer">
            <span class="view-details">
                Visualizza dettagli
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" fill="currentColor"/>
                </svg>
            </span>
        </div>

    </a>
</article>
