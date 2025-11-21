<?php
/**
 * Admin Moderation View
 *
 * @package    Pawstars
 * @subpackage Pawstars/admin/views
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap pawstars-admin">
    <h1><?php esc_html_e( 'Moderazione Profili', 'pawstars' ); ?></h1>

    <?php settings_errors( 'pawstars' ); ?>

    <?php if ( ! empty( $pending_dogs ) ) : ?>
        <div class="pawstars-moderation-queue">
            <p class="description">
                <?php printf( esc_html__( '%d profili in attesa di approvazione', 'pawstars' ), count( $pending_dogs ) ); ?>
            </p>

            <div class="pawstars-moderation-grid">
                <?php foreach ( $pending_dogs as $dog ) : ?>
                    <div class="pawstars-mod-card" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
                        <div class="mod-card-image">
                            <?php if ( $dog->featured_image_id ) : ?>
                                <?php echo wp_get_attachment_image( $dog->featured_image_id, 'medium' ); ?>
                            <?php else : ?>
                                <div class="no-image"><?php esc_html_e( 'Nessuna foto', 'pawstars' ); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mod-card-content">
                            <h3><?php echo esc_html( $dog->name ); ?></h3>

                            <div class="mod-card-meta">
                                <?php if ( $dog->breed_name ) : ?>
                                    <span class="meta-item">🐕 <?php echo esc_html( $dog->breed_name ); ?></span>
                                <?php endif; ?>
                                <?php if ( $dog->provincia ) : ?>
                                    <span class="meta-item">📍 <?php echo esc_html( $dog->provincia ); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ( $dog->bio ) : ?>
                                <p class="mod-card-bio"><?php echo esc_html( wp_trim_words( $dog->bio, 20 ) ); ?></p>
                            <?php endif; ?>

                            <div class="mod-card-info">
                                <span class="info-item">
                                    <strong><?php esc_html_e( 'Proprietario:', 'pawstars' ); ?></strong>
                                    <?php echo esc_html( get_the_author_meta( 'display_name', $dog->user_id ) ); ?>
                                </span>
                                <span class="info-item">
                                    <strong><?php esc_html_e( 'Inviato:', 'pawstars' ); ?></strong>
                                    <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $dog->created_at ) ) ); ?>
                                </span>
                            </div>
                        </div>

                        <div class="mod-card-actions">
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=pawstars-moderation&action=approve&dog_id=' . $dog->id ), 'pawstars_moderate' ) ); ?>" class="button button-primary">
                                ✓ <?php esc_html_e( 'Approva', 'pawstars' ); ?>
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=pawstars-moderation&action=reject&dog_id=' . $dog->id ), 'pawstars_moderate' ) ); ?>" class="button">
                                ✗ <?php esc_html_e( 'Rifiuta', 'pawstars' ); ?>
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=pawstars-moderation&action=delete&dog_id=' . $dog->id ), 'pawstars_moderate' ) ); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Eliminare definitivamente?', 'pawstars' ); ?>')">
                                🗑 <?php esc_html_e( 'Elimina', 'pawstars' ); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="pawstars-empty-state">
            <div class="empty-icon">✓</div>
            <h2><?php esc_html_e( 'Tutto in ordine!', 'pawstars' ); ?></h2>
            <p><?php esc_html_e( 'Non ci sono profili in attesa di approvazione.', 'pawstars' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pawstars' ) ); ?>" class="button">
                <?php esc_html_e( 'Torna alla Dashboard', 'pawstars' ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
