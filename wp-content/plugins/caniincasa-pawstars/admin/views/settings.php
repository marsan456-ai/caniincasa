<?php
/**
 * Admin Settings View
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
    <h1><?php esc_html_e( 'Impostazioni Paw Stars', 'pawstars' ); ?></h1>

    <?php settings_errors( 'pawstars' ); ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'pawstars_settings_nonce' ); ?>

        <div class="pawstars-settings-grid">
            <!-- General Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Generale', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Abilita Paw Stars', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked( $settings['enabled'] ?? true ); ?>>
                                <?php esc_html_e( 'Attiva il sistema Paw Stars', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Email Notifiche', 'pawstars' ); ?></th>
                        <td>
                            <input type="email" name="notification_email" value="<?php echo esc_attr( $settings['notification_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Email per ricevere notifiche su nuovi profili', 'pawstars' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Moderation Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Moderazione', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Richiedi Approvazione', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="moderation_required" value="1" <?php checked( $settings['moderation_required'] ?? true ); ?>>
                                <?php esc_html_e( 'I nuovi profili richiedono approvazione', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Auto-Approva Verificati', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_approve_verified" value="1" <?php checked( $settings['auto_approve_verified'] ?? false ); ?>>
                                <?php esc_html_e( 'Approva automaticamente utenti verificati', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Limits Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Limiti', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Max Cani per Utente', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="max_dogs_per_user" value="<?php echo esc_attr( $settings['max_dogs_per_user'] ?? 5 ); ?>" min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Max Foto per Cane', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="max_photos_per_dog" value="<?php echo esc_attr( $settings['max_photos_per_dog'] ?? 10 ); ?>" min="1" max="30" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Max Dimensione Foto (MB)', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="max_photo_size_mb" value="<?php echo esc_attr( $settings['max_photo_size_mb'] ?? 5 ); ?>" min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Limite Star Giornaliero', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="star_daily_limit" value="<?php echo esc_attr( $settings['star_daily_limit'] ?? 1 ); ?>" min="1" max="10" class="small-text">
                            <p class="description"><?php esc_html_e( 'Quante Star un utente può dare al giorno', 'pawstars' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Points Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Punti Reazioni', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">❤️ <?php esc_html_e( 'Love', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="points_love" value="<?php echo esc_attr( $settings['points_love'] ?? 5 ); ?>" min="1" max="20" class="small-text">
                            <?php esc_html_e( 'punti', 'pawstars' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">😍 <?php esc_html_e( 'Adorable', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="points_adorable" value="<?php echo esc_attr( $settings['points_adorable'] ?? 3 ); ?>" min="1" max="20" class="small-text">
                            <?php esc_html_e( 'punti', 'pawstars' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">⭐ <?php esc_html_e( 'Star', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="points_star" value="<?php echo esc_attr( $settings['points_star'] ?? 10 ); ?>" min="1" max="50" class="small-text">
                            <?php esc_html_e( 'punti', 'pawstars' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">😄 <?php esc_html_e( 'Funny', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="points_funny" value="<?php echo esc_attr( $settings['points_funny'] ?? 2 ); ?>" min="1" max="20" class="small-text">
                            <?php esc_html_e( 'punti', 'pawstars' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">🥺 <?php esc_html_e( 'Aww', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="points_aww" value="<?php echo esc_attr( $settings['points_aww'] ?? 2 ); ?>" min="1" max="20" class="small-text">
                            <?php esc_html_e( 'punti', 'pawstars' ); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Display Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Visualizzazione', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Swipe su Mobile', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_swipe_mobile" value="1" <?php checked( $settings['enable_swipe_mobile'] ?? true ); ?>>
                                <?php esc_html_e( 'Abilita swipe cards su mobile', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Grid su Desktop', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_grid_desktop" value="1" <?php checked( $settings['enable_grid_desktop'] ?? true ); ?>>
                                <?php esc_html_e( 'Mostra griglia su desktop', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Cani per Pagina', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="dogs_per_page" value="<?php echo esc_attr( $settings['dogs_per_page'] ?? 12 ); ?>" min="6" max="48" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Hot Dogs Periodo', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="leaderboard_hot_days" value="<?php echo esc_attr( $settings['leaderboard_hot_days'] ?? 7 ); ?>" min="1" max="30" class="small-text">
                            <?php esc_html_e( 'giorni', 'pawstars' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Cache Classifica', 'pawstars' ); ?></th>
                        <td>
                            <input type="number" name="leaderboard_cache_time" value="<?php echo esc_attr( $settings['leaderboard_cache_time'] ?? 300 ); ?>" min="60" max="3600" class="small-text">
                            <?php esc_html_e( 'secondi', 'pawstars' ); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Gamification Settings -->
            <div class="pawstars-settings-section">
                <h2><?php esc_html_e( 'Gamification', 'pawstars' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Abilita Badge', 'pawstars' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_badges" value="1" <?php checked( $settings['enable_badges'] ?? true ); ?>>
                                <?php esc_html_e( 'Assegna badge per achievements', 'pawstars' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="submit">
            <button type="submit" name="pawstars_save_settings" class="button button-primary">
                <?php esc_html_e( 'Salva Impostazioni', 'pawstars' ); ?>
            </button>
        </p>
    </form>

    <!-- Shortcodes Reference -->
    <div class="pawstars-settings-section">
        <h2><?php esc_html_e( 'Shortcodes Disponibili', 'pawstars' ); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Shortcode', 'pawstars' ); ?></th>
                    <th><?php esc_html_e( 'Descrizione', 'pawstars' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[pawstars_feed]</code></td>
                    <td><?php esc_html_e( 'Feed principale con tutti i cani', 'pawstars' ); ?></td>
                </tr>
                <tr>
                    <td><code>[pawstars_leaderboard type="hot"]</code></td>
                    <td><?php esc_html_e( 'Classifica Hot Dogs (type: hot, alltime, breed, provincia)', 'pawstars' ); ?></td>
                </tr>
                <tr>
                    <td><code>[pawstars_user_dashboard]</code></td>
                    <td><?php esc_html_e( 'Dashboard utente per gestire i propri cani', 'pawstars' ); ?></td>
                </tr>
                <tr>
                    <td><code>[pawstars_dog_profile id="123"]</code></td>
                    <td><?php esc_html_e( 'Profilo singolo cane', 'pawstars' ); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
