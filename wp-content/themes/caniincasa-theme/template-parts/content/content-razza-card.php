<?php
/**
 * Template part for displaying Razza Card
 *
 * @package Caniincasa
 */
?>

<article <?php post_class( 'razza-card-item' ); ?> data-id="<?php the_ID(); ?>">
    <a href="<?php the_permalink(); ?>" class="razza-card-link">

        <!-- Image -->
        <div class="razza-card-image">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'caniincasa-medium', array( 'loading' => 'lazy' ) ); ?>
            <?php else : ?>
                <img src="<?php echo esc_url( CANIINCASA_THEME_URI . '/assets/images/default-dog.jpg' ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="razza-card-content">
            <h3 class="razza-card-title"><?php the_title(); ?></h3>

            <!-- Quick Info -->
            <div class="razza-card-info">
                <?php
                $affettuosita = get_field( 'affettuosita' );
                $energia = get_field( 'energia_e_livelli_di_attivita' );

                if ( $affettuosita ) :
                    ?>
                    <span class="info-badge">
                        <span class="icon">❤️</span>
                        <span class="value"><?php echo number_format( $affettuosita, 1 ); ?></span>
                    </span>
                <?php endif; ?>

                <?php if ( $energia ) : ?>
                    <span class="info-badge">
                        <span class="icon">⚡</span>
                        <span class="value"><?php echo number_format( $energia, 1 ); ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Taglia -->
            <?php
            $terms = get_the_terms( get_the_ID(), 'razza_taglia' );
            if ( $terms && ! is_wp_error( $terms ) ) :
                ?>
                <div class="razza-card-tags">
                    <?php foreach ( $terms as $term ) : ?>
                        <span class="tag"><?php echo esc_html( $term->name ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </a>
</article>
