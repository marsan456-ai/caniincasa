<?php
/**
 * Settings Handler
 *
 * Additional settings functionality.
 *
 * @package    Pawstars
 * @subpackage Pawstars/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Class
 *
 * @since 1.0.0
 */
class Pawstars_Settings {

    /**
     * Default settings
     *
     * @var array
     */
    private static $defaults = array(
        'enabled'                => true,
        'moderation_required'    => true,
        'auto_approve_verified'  => false,
        'max_dogs_per_user'      => 5,
        'max_photos_per_dog'     => 10,
        'max_photo_size_mb'      => 5,
        'star_daily_limit'       => 1,
        'points_love'            => 5,
        'points_adorable'        => 3,
        'points_star'            => 10,
        'points_funny'           => 2,
        'points_aww'             => 2,
        'leaderboard_hot_days'   => 7,
        'leaderboard_cache_time' => 300,
        'notification_email'     => '',
        'enable_swipe_mobile'    => true,
        'enable_grid_desktop'    => true,
        'dogs_per_page'          => 12,
        'enable_badges'          => true,
    );

    /**
     * Get setting value
     *
     * @since  1.0.0
     * @param  string $key     Setting key
     * @param  mixed  $default Default value
     * @return mixed
     */
    public static function get( $key, $default = null ) {
        $settings = get_option( 'pawstars_settings', array() );

        if ( isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        if ( $default !== null ) {
            return $default;
        }

        return isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : null;
    }

    /**
     * Update setting value
     *
     * @since  1.0.0
     * @param  string $key   Setting key
     * @param  mixed  $value Value
     * @return bool
     */
    public static function set( $key, $value ) {
        $settings = get_option( 'pawstars_settings', array() );
        $settings[ $key ] = $value;
        return update_option( 'pawstars_settings', $settings );
    }

    /**
     * Get all settings
     *
     * @since  1.0.0
     * @return array
     */
    public static function get_all() {
        return wp_parse_args( get_option( 'pawstars_settings', array() ), self::$defaults );
    }
}
