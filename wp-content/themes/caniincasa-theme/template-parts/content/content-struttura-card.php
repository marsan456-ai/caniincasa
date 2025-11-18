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

            <!-- Phone -->
            <?php if ( $telefono ) : ?>
                <div class="card-info-item">
                    <span class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="info-text"><?php echo esc_html( $telefono ); ?></span>
                </div>
            <?php endif; ?>

            <!-- Email -->
            <?php if ( $email ) : ?>
                <div class="card-info-item">
                    <span class="info-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="info-text"><?php echo esc_html( $email ); ?></span>
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
