<?php
/**
 * Shortcodes Handler
 *
 * Registers and handles all plugin shortcodes.
 *
 * @package    Pawstars
 * @subpackage Pawstars/public
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcodes Class
 *
 * @since 1.0.0
 */
class Pawstars_Shortcodes {

    /**
     * Plugin instance
     *
     * @var Caniincasa_Pawstars
     */
    private $plugin;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Caniincasa_Pawstars $plugin Plugin instance
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->register_shortcodes();
    }

    /**
     * Register shortcodes
     *
     * @since 1.0.0
     */
    private function register_shortcodes() {
        add_shortcode( 'pawstars_feed', array( $this, 'feed_shortcode' ) );
        add_shortcode( 'pawstars_leaderboard', array( $this, 'leaderboard_shortcode' ) );
        add_shortcode( 'pawstars_user_dashboard', array( $this, 'user_dashboard_shortcode' ) );
        add_shortcode( 'pawstars_dog_profile', array( $this, 'dog_profile_shortcode' ) );
        add_shortcode( 'pawstars_create', array( $this, 'create_profile_shortcode' ) );
    }

    /**
     * Feed shortcode
     *
     * @since  1.0.0
     * @param  array $atts Shortcode attributes
     * @return string
     */
    public function feed_shortcode( $atts ) {
        if ( ! $this->plugin->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts( array(
            'breed'     => '',
            'provincia' => '',
            'limit'     => 12,
            'orderby'   => 'created_at',
            'order'     => 'DESC',
        ), $atts, 'pawstars_feed' );

        // Force load assets
        add_filter( 'pawstars_load_assets', '__return_true' );

        ob_start();
        include PAWSTARS_PLUGIN_DIR . 'public/templates/feed.php';
        return ob_get_clean();
    }

    /**
     * Leaderboard shortcode
     *
     * @since  1.0.0
     * @param  array $atts Shortcode attributes
     * @return string
     */
    public function leaderboard_shortcode( $atts ) {
        if ( ! $this->plugin->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts( array(
            'type'   => 'hot',
            'limit'  => 10,
            'filter' => '',
        ), $atts, 'pawstars_leaderboard' );

        // Force load assets
        add_filter( 'pawstars_load_assets', '__return_true' );

        ob_start();
        include PAWSTARS_PLUGIN_DIR . 'public/templates/leaderboard.php';
        return ob_get_clean();
    }

    /**
     * User dashboard shortcode
     *
     * @since  1.0.0
     * @param  array $atts Shortcode attributes
     * @return string
     */
    public function user_dashboard_shortcode( $atts ) {
        if ( ! $this->plugin->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts( array(), $atts, 'pawstars_user_dashboard' );

        // Force load assets
        add_filter( 'pawstars_load_assets', '__return_true' );

        ob_start();
        include PAWSTARS_PLUGIN_DIR . 'public/templates/user-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Dog profile shortcode
     *
     * @since  1.0.0
     * @param  array $atts Shortcode attributes
     * @return string
     */
    public function dog_profile_shortcode( $atts ) {
        if ( ! $this->plugin->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'pawstars_dog_profile' );

        // Check for ID in URL
        if ( empty( $atts['id'] ) && isset( $_GET['dog'] ) ) {
            $atts['id'] = absint( $_GET['dog'] );
        }

        if ( empty( $atts['id'] ) ) {
            return '<p>' . esc_html__( 'Profilo non specificato.', 'pawstars' ) . '</p>';
        }

        $dog = $this->plugin->dog_profile->get( $atts['id'] );

        if ( ! $dog || ( $dog->status !== 'active' && $dog->user_id != get_current_user_id() ) ) {
            return '<p>' . esc_html__( 'Profilo non trovato.', 'pawstars' ) . '</p>';
        }

        // Force load assets
        add_filter( 'pawstars_load_assets', '__return_true' );

        ob_start();
        include PAWSTARS_PLUGIN_DIR . 'public/templates/dog-profile.php';
        return ob_get_clean();
    }

    /**
     * Create profile shortcode
     *
     * @since  1.0.0
     * @param  array $atts Shortcode attributes
     * @return string
     */
    public function create_profile_shortcode( $atts ) {
        if ( ! $this->plugin->is_enabled() ) {
            return '';
        }

        $atts = shortcode_atts( array(), $atts, 'pawstars_create' );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="pawstars-notice pawstars-notice-warning">
                <p>' . esc_html__( 'Devi effettuare il login per creare un profilo.', 'pawstars' ) . '</p>
                <p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="btn btn-primary">' . esc_html__( 'Accedi', 'pawstars' ) . '</a></p>
            </div>';
        }

        // Check dog limit
        $user_dogs = $this->plugin->database->count_user_dogs( get_current_user_id() );
        $settings = get_option( 'pawstars_settings', array() );
        $max_dogs = isset( $settings['max_dogs_per_user'] ) ? (int) $settings['max_dogs_per_user'] : 5;

        if ( $user_dogs >= $max_dogs ) {
            return '<div class="pawstars-notice pawstars-notice-warning">
                <p>' . sprintf( esc_html__( 'Hai raggiunto il limite massimo di %d profili.', 'pawstars' ), $max_dogs ) . '</p>
            </div>';
        }

        // Force load assets
        add_filter( 'pawstars_load_assets', '__return_true' );

        ob_start();
        include PAWSTARS_PLUGIN_DIR . 'public/templates/create-profile.php';
        return ob_get_clean();
    }
}
