<?php
/**
 * Uninstall Paw Stars
 *
 * Handles cleanup when plugin is uninstalled.
 *
 * @package Pawstars
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if we should delete data
$settings = get_option( 'pawstars_settings', array() );
$delete_data = isset( $settings['delete_data_on_uninstall'] ) ? $settings['delete_data_on_uninstall'] : false;

if ( $delete_data ) {
    global $wpdb;

    // Delete custom tables
    $tables = array(
        $wpdb->prefix . 'pawstars_dogs',
        $wpdb->prefix . 'pawstars_votes',
        $wpdb->prefix . 'pawstars_achievements',
        $wpdb->prefix . 'pawstars_challenges',
        $wpdb->prefix . 'pawstars_follows',
        $wpdb->prefix . 'pawstars_daily_stats',
    );

    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS $table" );
    }

    // Delete options
    delete_option( 'pawstars_settings' );
    delete_option( 'pawstars_enabled' );
    delete_option( 'pawstars_db_version' );
    delete_option( 'pawstars_badges' );

    // Delete transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '%_transient_pawstars_%'
            OR option_name LIKE '%_transient_timeout_pawstars_%'"
    );

    // Clear scheduled events
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

// Always clear caches
wp_cache_flush();
