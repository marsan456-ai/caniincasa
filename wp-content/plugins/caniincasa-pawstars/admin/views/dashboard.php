<?php
/**
 * Admin Dashboard View
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
    <h1><?php esc_html_e( 'Paw Stars Dashboard', 'pawstars' ); ?></h1>

    <?php settings_errors( 'pawstars' ); ?>

    <!-- Stats Cards -->
    <div class="pawstars-stats-grid">
        <div class="pawstars-stat-card">
            <div class="stat-icon">🐕</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( $stats['total_dogs'] ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Cani Attivi', 'pawstars' ); ?></div>
            </div>
        </div>

        <div class="pawstars-stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( $stats['pending_dogs'] ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'In Attesa', 'pawstars' ); ?></div>
            </div>
            <?php if ( $stats['pending_dogs'] > 0 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pawstars-moderation' ) ); ?>" class="stat-link">
                    <?php esc_html_e( 'Modera', 'pawstars' ); ?> →
                </a>
            <?php endif; ?>
        </div>

        <div class="pawstars-stat-card">
            <div class="stat-icon">❤️</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( number_format_i18n( $stats['total_votes'] ) ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Voti Totali', 'pawstars' ); ?></div>
            </div>
        </div>

        <div class="pawstars-stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html( $stats['total_users'] ); ?></div>
                <div class="stat-label"><?php esc_html_e( 'Proprietari', 'pawstars' ); ?></div>
            </div>
        </div>
    </div>

    <div class="pawstars-dashboard-grid">
        <!-- Hot Dogs -->
        <div class="pawstars-panel">
            <h2>🔥 <?php esc_html_e( 'Hot Dogs (7 giorni)', 'pawstars' ); ?></h2>
            <?php if ( ! empty( $hot_dogs ) ) : ?>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Rank', 'pawstars' ); ?></th>
                            <th><?php esc_html_e( 'Nome', 'pawstars' ); ?></th>
                            <th><?php esc_html_e( 'Punti Hot', 'pawstars' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $hot_dogs as $index => $dog ) : ?>
                            <tr>
                                <td>
                                    <?php
                                    $medals = array( '🥇', '🥈', '🥉' );
                                    echo isset( $medals[ $index ] ) ? $medals[ $index ] : '#' . ( $index + 1 );
                                    ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $dog->name ); ?></strong>
                                    <?php if ( $dog->breed_name ) : ?>
                                        <br><small><?php echo esc_html( $dog->breed_name ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $dog->hot_points ?? $dog->total_points ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'Nessun cane attivo.', 'pawstars' ); ?></p>
            <?php endif; ?>
        </div>

        <!-- Recent Dogs -->
        <div class="pawstars-panel">
            <h2>🆕 <?php esc_html_e( 'Ultimi Profili', 'pawstars' ); ?></h2>
            <?php if ( ! empty( $recent_dogs ) ) : ?>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Nome', 'pawstars' ); ?></th>
                            <th><?php esc_html_e( 'Stato', 'pawstars' ); ?></th>
                            <th><?php esc_html_e( 'Data', 'pawstars' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_dogs as $dog ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( $dog->name ); ?></strong></td>
                                <td>
                                    <?php
                                    $status_labels = array(
                                        'pending'   => '<span class="status-badge status-pending">' . __( 'In Attesa', 'pawstars' ) . '</span>',
                                        'active'    => '<span class="status-badge status-active">' . __( 'Attivo', 'pawstars' ) . '</span>',
                                        'rejected'  => '<span class="status-badge status-rejected">' . __( 'Rifiutato', 'pawstars' ) . '</span>',
                                        'suspended' => '<span class="status-badge status-suspended">' . __( 'Sospeso', 'pawstars' ) . '</span>',
                                    );
                                    echo isset( $status_labels[ $dog->status ] ) ? wp_kses_post( $status_labels[ $dog->status ] ) : esc_html( $dog->status );
                                    ?>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $dog->created_at ) ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'Nessun profilo creato.', 'pawstars' ); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="pawstars-panel">
        <h2><?php esc_html_e( 'Link Rapidi', 'pawstars' ); ?></h2>
        <div class="pawstars-quick-links">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pawstars-moderation' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Moderazione', 'pawstars' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=pawstars-settings' ) ); ?>" class="button">
                <?php esc_html_e( 'Impostazioni', 'pawstars' ); ?>
            </a>
        </div>
    </div>
</div>
