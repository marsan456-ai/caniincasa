<?php
/**
 * Dog Profile Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates
 * @since      1.0.0
 *
 * @var object $dog Dog object with full data
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();
$is_owner = $user_id && $dog->user_id == $user_id;
$user_votes = $user_id ? pawstars()->voting->get_user_votes( $dog->id, $user_id ) : array();
$badges = pawstars()->achievements->get_badges();
?>

<div class="pawstars-dog-profile">
    <!-- Profile Header -->
    <div class="profile-header">
        <a href="javascript:history.back()" class="back-link">← <?php esc_html_e( 'Indietro', 'pawstars' ); ?></a>
    </div>

    <div class="profile-main">
        <!-- Photo Section -->
        <div class="profile-photos">
            <div class="main-photo">
                <?php if ( $dog->image_url ) : ?>
                    <img src="<?php echo esc_url( $dog->image_url ); ?>" alt="<?php echo esc_attr( $dog->name ); ?>">
                <?php else : ?>
                    <div class="no-photo-placeholder">🐕</div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $dog->gallery_urls ) ) : ?>
                <div class="photo-gallery">
                    <?php foreach ( $dog->gallery_urls as $image ) : ?>
                        <div class="gallery-thumb">
                            <img src="<?php echo esc_url( $image['thumbnail'] ); ?>" alt="" data-full="<?php echo esc_url( $image['url'] ); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Section -->
        <div class="profile-info">
            <div class="profile-name-row">
                <h1 class="dog-name"><?php echo esc_html( $dog->name ); ?></h1>
                <?php if ( $dog->is_featured ) : ?>
                    <span class="featured-badge">⭐ <?php esc_html_e( 'In Evidenza', 'pawstars' ); ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-meta">
                <?php if ( $dog->breed_name ) : ?>
                    <span class="meta-item">🐕 <?php echo esc_html( $dog->breed_name ); ?></span>
                <?php endif; ?>
                <?php if ( $dog->age_display ) : ?>
                    <span class="meta-item">🎂 <?php echo esc_html( $dog->age_display ); ?></span>
                <?php endif; ?>
                <?php if ( $dog->provincia ) : ?>
                    <span class="meta-item">📍 <?php echo esc_html( $dog->provincia ); ?></span>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="profile-stats">
                <div class="stat-box">
                    <span class="stat-value"><?php echo esc_html( number_format_i18n( $dog->total_points ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Punti', 'pawstars' ); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?php echo esc_html( $dog->vote_stats['total']['count'] ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Voti', 'pawstars' ); ?></span>
                </div>
                <?php if ( $dog->rank_hot ) : ?>
                    <div class="stat-box">
                        <span class="stat-value">#<?php echo esc_html( $dog->rank_hot ); ?></span>
                        <span class="stat-label"><?php esc_html_e( 'Hot Dogs', 'pawstars' ); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ( $dog->rank_alltime ) : ?>
                    <div class="stat-box">
                        <span class="stat-value">#<?php echo esc_html( $dog->rank_alltime ); ?></span>
                        <span class="stat-label"><?php esc_html_e( 'All Stars', 'pawstars' ); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reactions -->
            <div class="profile-reactions">
                <h3><?php esc_html_e( 'Vota questo cane', 'pawstars' ); ?></h3>
                <div class="pawstars-reactions large" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
                    <button class="reaction-btn <?php echo in_array( 'love', $user_votes ) ? 'voted' : ''; ?>" data-reaction="love">
                        <span class="reaction-emoji">❤️</span>
                        <span class="reaction-label">Love</span>
                        <span class="reaction-count"><?php echo esc_html( $dog->vote_stats['love']['count'] ); ?></span>
                    </button>
                    <button class="reaction-btn <?php echo in_array( 'adorable', $user_votes ) ? 'voted' : ''; ?>" data-reaction="adorable">
                        <span class="reaction-emoji">😍</span>
                        <span class="reaction-label">Adorable</span>
                        <span class="reaction-count"><?php echo esc_html( $dog->vote_stats['adorable']['count'] ); ?></span>
                    </button>
                    <button class="reaction-btn reaction-star <?php echo in_array( 'star', $user_votes ) ? 'voted' : ''; ?>" data-reaction="star">
                        <span class="reaction-emoji">⭐</span>
                        <span class="reaction-label">Star</span>
                        <span class="reaction-count"><?php echo esc_html( $dog->vote_stats['star']['count'] ); ?></span>
                    </button>
                    <button class="reaction-btn <?php echo in_array( 'funny', $user_votes ) ? 'voted' : ''; ?>" data-reaction="funny">
                        <span class="reaction-emoji">😄</span>
                        <span class="reaction-label">Funny</span>
                        <span class="reaction-count"><?php echo esc_html( $dog->vote_stats['funny']['count'] ); ?></span>
                    </button>
                    <button class="reaction-btn <?php echo in_array( 'aww', $user_votes ) ? 'voted' : ''; ?>" data-reaction="aww">
                        <span class="reaction-emoji">🥺</span>
                        <span class="reaction-label">Aww</span>
                        <span class="reaction-count"><?php echo esc_html( $dog->vote_stats['aww']['count'] ); ?></span>
                    </button>
                </div>
            </div>

            <!-- Bio -->
            <?php if ( $dog->bio ) : ?>
                <div class="profile-bio">
                    <h3><?php esc_html_e( 'Descrizione', 'pawstars' ); ?></h3>
                    <p><?php echo nl2br( esc_html( $dog->bio ) ); ?></p>
                </div>
            <?php endif; ?>

            <!-- Badges -->
            <?php if ( ! empty( $dog->achievements ) ) : ?>
                <div class="profile-badges">
                    <h3><?php esc_html_e( 'Badge', 'pawstars' ); ?></h3>
                    <div class="badges-list">
                        <?php foreach ( $dog->achievements as $achievement ) : ?>
                            <?php $badge = isset( $badges[ $achievement->badge_slug ] ) ? $badges[ $achievement->badge_slug ] : null; ?>
                            <?php if ( $badge ) : ?>
                                <div class="badge-item" title="<?php echo esc_attr( $badge['description'] ); ?>">
                                    <?php echo pawstars()->achievements->get_badge_icon_html( $achievement->badge_slug ); ?>
                                    <span><?php echo esc_html( $badge['name'] ); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Owner Info -->
            <div class="profile-owner">
                <span class="owner-label"><?php esc_html_e( 'Proprietario:', 'pawstars' ); ?></span>
                <span class="owner-name"><?php echo esc_html( $dog->author_name ); ?></span>
            </div>
        </div>
    </div>

    <!-- Share -->
    <div class="profile-share">
        <span><?php esc_html_e( 'Condividi:', 'pawstars' ); ?></span>
        <div class="share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() . '?dog=' . $dog->id ); ?>" target="_blank" class="share-btn share-facebook">Facebook</a>
            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode( $dog->name . ' su Paw Stars!' ); ?>&url=<?php echo urlencode( get_permalink() . '?dog=' . $dog->id ); ?>" target="_blank" class="share-btn share-twitter">Twitter</a>
            <a href="https://wa.me/?text=<?php echo urlencode( $dog->name . ' su Paw Stars! ' . get_permalink() . '?dog=' . $dog->id ); ?>" target="_blank" class="share-btn share-whatsapp">WhatsApp</a>
        </div>
    </div>
</div>
