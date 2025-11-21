<?php
/**
 * Dog Card Partial Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates/partials
 * @since      1.0.0
 *
 * @var object $dog Dog object
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();
$user_votes = $user_id ? pawstars()->voting->get_user_votes( $dog->id, $user_id ) : array();
$vote_stats = pawstars()->database->get_dog_vote_stats( $dog->id );
?>

<div class="pawstars-dog-card" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
    <a href="?dog=<?php echo esc_attr( $dog->id ); ?>" class="dog-card-link">
        <div class="dog-card-image">
            <?php if ( $dog->image_url ) : ?>
                <img src="<?php echo esc_url( $dog->image_url ); ?>" alt="<?php echo esc_attr( $dog->name ); ?>" loading="lazy">
            <?php else : ?>
                <div class="no-image-placeholder">
                    <span>🐕</span>
                </div>
            <?php endif; ?>

            <?php if ( $dog->is_featured ) : ?>
                <span class="featured-badge">⭐ <?php esc_html_e( 'In Evidenza', 'pawstars' ); ?></span>
            <?php endif; ?>
        </div>

        <div class="dog-card-content">
            <h3 class="dog-name"><?php echo esc_html( $dog->name ); ?></h3>

            <div class="dog-meta">
                <?php if ( $dog->breed_name ) : ?>
                    <span class="meta-breed"><?php echo esc_html( $dog->breed_name ); ?></span>
                <?php endif; ?>
                <?php if ( $dog->provincia ) : ?>
                    <span class="meta-location">📍 <?php echo esc_html( $dog->provincia ); ?></span>
                <?php endif; ?>
            </div>

            <div class="dog-stats">
                <span class="stat-points">
                    ⭐ <?php echo esc_html( number_format_i18n( $dog->total_points ) ); ?>
                </span>
                <span class="stat-votes">
                    ❤️ <?php echo esc_html( $vote_stats['total']['count'] ); ?>
                </span>
            </div>
        </div>
    </a>

    <!-- Reactions -->
    <div class="dog-card-actions">
        <div class="pawstars-reactions" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
            <button class="reaction-btn <?php echo in_array( 'love', $user_votes ) ? 'voted' : ''; ?>" data-reaction="love" title="Love">
                <span class="reaction-emoji">❤️</span>
                <span class="reaction-count"><?php echo esc_html( $vote_stats['love']['count'] ); ?></span>
            </button>
            <button class="reaction-btn <?php echo in_array( 'adorable', $user_votes ) ? 'voted' : ''; ?>" data-reaction="adorable" title="Adorable">
                <span class="reaction-emoji">😍</span>
                <span class="reaction-count"><?php echo esc_html( $vote_stats['adorable']['count'] ); ?></span>
            </button>
            <button class="reaction-btn reaction-star <?php echo in_array( 'star', $user_votes ) ? 'voted' : ''; ?>" data-reaction="star" title="Star">
                <span class="reaction-emoji">⭐</span>
                <span class="reaction-count"><?php echo esc_html( $vote_stats['star']['count'] ); ?></span>
            </button>
        </div>
    </div>
</div>
