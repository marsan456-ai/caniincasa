<?php
/**
 * Leaderboard Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$plugin = pawstars();
$type = $atts['type'];
$limit = absint( $atts['limit'] );
$filter = $atts['filter'];

$dogs = $plugin->leaderboard->get( $type, $limit, $filter );

$titles = array(
    'hot'       => __( '🔥 Hot Dogs', 'pawstars' ),
    'alltime'   => __( '🏆 All Stars', 'pawstars' ),
    'breed'     => __( '🐕 Top per Razza', 'pawstars' ),
    'provincia' => __( '📍 Top per Provincia', 'pawstars' ),
);

$descriptions = array(
    'hot'       => __( 'I cani più votati negli ultimi 7 giorni', 'pawstars' ),
    'alltime'   => __( 'I cani con più punti di sempre', 'pawstars' ),
    'breed'     => __( 'I migliori della razza', 'pawstars' ),
    'provincia' => __( 'I migliori della provincia', 'pawstars' ),
);
?>

<div class="pawstars-leaderboard-wrapper">
    <div class="leaderboard-header">
        <h2 class="leaderboard-title"><?php echo esc_html( $titles[ $type ] ?? $titles['hot'] ); ?></h2>
        <p class="leaderboard-description"><?php echo esc_html( $descriptions[ $type ] ?? '' ); ?></p>
    </div>

    <?php if ( ! empty( $dogs ) ) : ?>
        <!-- Podium (Top 3) -->
        <?php if ( count( $dogs ) >= 3 ) : ?>
            <div class="leaderboard-podium">
                <!-- Second Place -->
                <div class="podium-item podium-second">
                    <div class="podium-position">🥈</div>
                    <div class="podium-image">
                        <?php if ( $dogs[1]->image_url ) : ?>
                            <img src="<?php echo esc_url( $dogs[1]->image_url ); ?>" alt="<?php echo esc_attr( $dogs[1]->name ); ?>">
                        <?php else : ?>
                            <div class="no-image">🐕</div>
                        <?php endif; ?>
                    </div>
                    <h4 class="podium-name"><?php echo esc_html( $dogs[1]->name ); ?></h4>
                    <div class="podium-points"><?php echo esc_html( $dogs[1]->hot_points ?? $dogs[1]->total_points ); ?> <?php esc_html_e( 'pt', 'pawstars' ); ?></div>
                </div>

                <!-- First Place -->
                <div class="podium-item podium-first">
                    <div class="podium-crown">👑</div>
                    <div class="podium-position">🥇</div>
                    <div class="podium-image">
                        <?php if ( $dogs[0]->image_url ) : ?>
                            <img src="<?php echo esc_url( $dogs[0]->image_url ); ?>" alt="<?php echo esc_attr( $dogs[0]->name ); ?>">
                        <?php else : ?>
                            <div class="no-image">🐕</div>
                        <?php endif; ?>
                    </div>
                    <h4 class="podium-name"><?php echo esc_html( $dogs[0]->name ); ?></h4>
                    <div class="podium-points"><?php echo esc_html( $dogs[0]->hot_points ?? $dogs[0]->total_points ); ?> <?php esc_html_e( 'pt', 'pawstars' ); ?></div>
                </div>

                <!-- Third Place -->
                <div class="podium-item podium-third">
                    <div class="podium-position">🥉</div>
                    <div class="podium-image">
                        <?php if ( $dogs[2]->image_url ) : ?>
                            <img src="<?php echo esc_url( $dogs[2]->image_url ); ?>" alt="<?php echo esc_attr( $dogs[2]->name ); ?>">
                        <?php else : ?>
                            <div class="no-image">🐕</div>
                        <?php endif; ?>
                    </div>
                    <h4 class="podium-name"><?php echo esc_html( $dogs[2]->name ); ?></h4>
                    <div class="podium-points"><?php echo esc_html( $dogs[2]->hot_points ?? $dogs[2]->total_points ); ?> <?php esc_html_e( 'pt', 'pawstars' ); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Full List -->
        <div class="leaderboard-list">
            <?php foreach ( $dogs as $index => $dog ) : ?>
                <div class="leaderboard-item <?php echo $index < 3 ? 'top-three' : ''; ?>">
                    <div class="rank-position">
                        <?php
                        $medals = array( '🥇', '🥈', '🥉' );
                        echo isset( $medals[ $index ] ) ? $medals[ $index ] : '#' . ( $index + 1 );
                        ?>
                    </div>

                    <div class="rank-image">
                        <?php if ( $dog->image_url ) : ?>
                            <img src="<?php echo esc_url( $dog->image_url ); ?>" alt="<?php echo esc_attr( $dog->name ); ?>">
                        <?php else : ?>
                            <div class="no-image">🐕</div>
                        <?php endif; ?>
                    </div>

                    <div class="rank-info">
                        <a href="?dog=<?php echo esc_attr( $dog->id ); ?>" class="rank-name">
                            <?php echo esc_html( $dog->name ); ?>
                        </a>
                        <?php if ( $dog->breed_name ) : ?>
                            <span class="rank-breed"><?php echo esc_html( $dog->breed_name ); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="rank-points">
                        <span class="points-value"><?php echo esc_html( number_format_i18n( $dog->hot_points ?? $dog->total_points ) ); ?></span>
                        <span class="points-label"><?php esc_html_e( 'punti', 'pawstars' ); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else : ?>
        <div class="leaderboard-empty">
            <div class="empty-icon">🏆</div>
            <p><?php esc_html_e( 'Nessun cane in classifica. Sii il primo!', 'pawstars' ); ?></p>
        </div>
    <?php endif; ?>
</div>
