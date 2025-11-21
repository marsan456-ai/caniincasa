<?php
/**
 * Plugin Deactivator
 *
 * Handles cleanup on plugin deactivation.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Deactivator Class
 *
 * @since 1.0.0
 */
class Pawstars_Deactivator {

    /**
     * Deactivate plugin
     *
     * Clears scheduled events and transients.
     * Does NOT delete database tables (use uninstall.php for that).
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        self::clear_cron_jobs();
        self::clear_transients();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron jobs
     *
     * @since 1.0.0
     */
    private static function clear_cron_jobs() {
        $cron_hooks = array(
            'pawstars_daily_leaderboard_update',
            'pawstars_daily_stats',
            'pawstars_check_badges',
        );

        foreach ( $cron_hooks as $hook ) {
            $timestamp = wp_next_scheduled( $hook );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $hook );
            }
        }
    }

    /**
     * Clear plugin transients
     *
     * @since 1.0.0
     */
    private static function clear_transients() {
        global $wpdb;

        // Delete all pawstars transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '%_transient_pawstars_%'
                OR option_name LIKE '%_transient_timeout_pawstars_%'"
        );
    }
}
