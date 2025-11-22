<?php
/**
 * Plugin Name:       Paw Stars - Caniincasa
 * Plugin URI:        https://www.caniincasa.it/paw-stars
 * Description:       Sistema social/gamificato per profili cani con voti, classifiche e badge.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Creattivo Communication
 * Author URI:        https://www.creattivo.it
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pawstars
 * Domain Path:       /languages
 *
 * @package Pawstars
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Constants
 */
define( 'PAWSTARS_VERSION', '1.0.0' );
define( 'PAWSTARS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAWSTARS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PAWSTARS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PAWSTARS_DB_VERSION', '1.0.0' );

/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */
final class Caniincasa_Pawstars {

    /**
     * Single instance
     *
     * @var Caniincasa_Pawstars
     */
    private static $instance = null;

    /**
     * Plugin components
     */
    public $database;
    public $dog_profile;
    public $voting;
    public $leaderboard;
    public $achievements;
    public $rest_api;
    public $public;
    public $shortcodes;
    public $admin;

    /**
     * Get instance
     *
     * @since  1.0.0
     * @return Caniincasa_Pawstars
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Load required files
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Core classes
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-activator.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-deactivator.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-database.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-dog-profile.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-voting-system.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-leaderboard.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-achievements.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once PAWSTARS_PLUGIN_DIR . 'includes/class-integrations.php';

        // Public classes
        require_once PAWSTARS_PLUGIN_DIR . 'public/class-public.php';
        require_once PAWSTARS_PLUGIN_DIR . 'public/class-shortcodes.php';

        // Admin classes
        if ( is_admin() ) {
            require_once PAWSTARS_PLUGIN_DIR . 'admin/class-admin-menu.php';
            require_once PAWSTARS_PLUGIN_DIR . 'admin/class-settings.php';
            require_once PAWSTARS_PLUGIN_DIR . 'admin/class-moderation.php';
        }
    }

    /**
     * Initialize components
     *
     * @since 1.0.0
     */
    private function init_components() {
        $this->database     = new Pawstars_Database();
        $this->dog_profile  = new Pawstars_Dog_Profile( $this->database );
        $this->voting       = new Pawstars_Voting_System( $this->database );
        $this->leaderboard  = new Pawstars_Leaderboard( $this->database );
        $this->achievements = new Pawstars_Achievements( $this->database );
        $this->rest_api     = new Pawstars_Rest_API( $this );
        $this->public       = new Pawstars_Public( $this );
        $this->shortcodes   = new Pawstars_Shortcodes( $this );

        if ( is_admin() ) {
            $this->admin = new Pawstars_Admin_Menu( $this );
        }
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook( __FILE__, array( 'Pawstars_Activator', 'activate' ) );
        register_deactivation_hook( __FILE__, array( 'Pawstars_Deactivator', 'deactivate' ) );

        // Init
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'check_db_version' ) );

        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // REST API
        add_action( 'rest_api_init', array( $this->rest_api, 'register_routes' ) );
    }

    /**
     * Load plugin textdomain
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'pawstars',
            false,
            dirname( PAWSTARS_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Check and update database version
     *
     * @since 1.0.0
     */
    public function check_db_version() {
        $installed_version = get_option( 'pawstars_db_version' );

        if ( $installed_version !== PAWSTARS_DB_VERSION ) {
            Pawstars_Activator::activate();
        }
    }

    /**
     * Enqueue public assets
     *
     * @since 1.0.0
     */
    public function enqueue_public_assets() {
        // Only load on relevant pages
        if ( ! $this->should_load_assets() ) {
            return;
        }

        // Main CSS
        wp_enqueue_style(
            'pawstars-main',
            PAWSTARS_PLUGIN_URL . 'public/css/pawstars.css',
            array(),
            PAWSTARS_VERSION
        );

        // Swipe Cards CSS
        wp_enqueue_style(
            'pawstars-swipe',
            PAWSTARS_PLUGIN_URL . 'public/css/swipe-cards.css',
            array( 'pawstars-main' ),
            PAWSTARS_VERSION
        );

        // Main JS
        wp_enqueue_script(
            'pawstars-main',
            PAWSTARS_PLUGIN_URL . 'public/js/pawstars.js',
            array( 'jquery' ),
            PAWSTARS_VERSION,
            true
        );

        // Swipe Cards JS
        wp_enqueue_script(
            'pawstars-swipe',
            PAWSTARS_PLUGIN_URL . 'public/js/swipe-cards.js',
            array( 'pawstars-main' ),
            PAWSTARS_VERSION,
            true
        );

        // Voting JS
        wp_enqueue_script(
            'pawstars-voting',
            PAWSTARS_PLUGIN_URL . 'public/js/voting.js',
            array( 'pawstars-main' ),
            PAWSTARS_VERSION,
            true
        );

        // Infinite Scroll JS
        wp_enqueue_script(
            'pawstars-infinite-scroll',
            PAWSTARS_PLUGIN_URL . 'public/js/infinite-scroll.js',
            array( 'pawstars-main' ),
            PAWSTARS_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'pawstars-main', 'pawstarsData', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'restUrl'     => rest_url( 'pawstars/v1/' ),
            'nonce'       => wp_create_nonce( 'pawstars_nonce' ),
            'restNonce'   => wp_create_nonce( 'wp_rest' ),
            'isLoggedIn'  => is_user_logged_in(),
            'userId'      => get_current_user_id(),
            'pluginUrl'   => PAWSTARS_PLUGIN_URL,
            'strings'     => array(
                'loginRequired'   => __( 'Devi essere loggato per votare', 'pawstars' ),
                'voteSuccess'     => __( 'Voto registrato!', 'pawstars' ),
                'voteError'       => __( 'Errore durante il voto', 'pawstars' ),
                'starLimit'       => __( 'Puoi dare solo 1 Star al giorno', 'pawstars' ),
                'alreadyVoted'    => __( 'Hai già votato con questa reaction', 'pawstars' ),
                'loading'         => __( 'Caricamento...', 'pawstars' ),
                'noMoreDogs'      => __( 'Non ci sono altri cani da mostrare', 'pawstars' ),
                'error'           => __( 'Si è verificato un errore', 'pawstars' ),
                'confirmDelete'   => __( 'Sei sicuro di voler eliminare questo profilo?', 'pawstars' ),
                'uploadError'     => __( 'Errore durante il caricamento', 'pawstars' ),
                'fileTooLarge'    => __( 'File troppo grande (max 5MB)', 'pawstars' ),
                'invalidFormat'   => __( 'Formato non supportato (usa JPG, PNG, WebP)', 'pawstars' ),
            ),
        ) );
    }

    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook Current admin page
     */
    public function enqueue_admin_assets( $hook ) {
        // Only on plugin pages
        if ( strpos( $hook, 'pawstars' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'pawstars-admin',
            PAWSTARS_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            PAWSTARS_VERSION
        );

        wp_enqueue_script(
            'pawstars-admin',
            PAWSTARS_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            PAWSTARS_VERSION,
            true
        );

        wp_localize_script( 'pawstars-admin', 'pawstarsAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'pawstars_admin_nonce' ),
            'strings' => array(
                'confirmApprove' => __( 'Approvare questo profilo?', 'pawstars' ),
                'confirmReject'  => __( 'Rifiutare questo profilo?', 'pawstars' ),
                'confirmDelete'  => __( 'Eliminare definitivamente?', 'pawstars' ),
                'processing'     => __( 'Elaborazione...', 'pawstars' ),
                'success'        => __( 'Operazione completata', 'pawstars' ),
                'error'          => __( 'Errore durante l\'operazione', 'pawstars' ),
            ),
        ) );

        // Media uploader
        wp_enqueue_media();
    }

    /**
     * Check if assets should be loaded
     *
     * @since  1.0.0
     * @return bool
     */
    private function should_load_assets() {
        global $post;

        // Always load if shortcode is present
        if ( $post && (
            has_shortcode( $post->post_content, 'pawstars_feed' ) ||
            has_shortcode( $post->post_content, 'pawstars_leaderboard' ) ||
            has_shortcode( $post->post_content, 'pawstars_user_dashboard' ) ||
            has_shortcode( $post->post_content, 'pawstars_dog_profile' ) ||
            has_shortcode( $post->post_content, 'pawstars_create' )
        ) ) {
            return true;
        }

        // Check page template
        if ( $post && get_page_template_slug( $post->ID ) === 'page-pawstars.php' ) {
            return true;
        }

        // Allow filtering
        return apply_filters( 'pawstars_load_assets', false );
    }

    /**
     * Check if plugin is enabled
     *
     * @since  1.0.0
     * @return bool
     */
    public function is_enabled() {
        return get_option( 'pawstars_enabled', true );
    }

    /**
     * Get plugin setting
     *
     * @since  1.0.0
     * @param  string $key     Setting key
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function get_setting( $key, $default = null ) {
        $settings = get_option( 'pawstars_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}

/**
 * Get plugin instance
 *
 * @since  1.0.0
 * @return Caniincasa_Pawstars
 */
function pawstars() {
    return Caniincasa_Pawstars::instance();
}

/**
 * Helper function to check if plugin is active
 *
 * @since  1.0.0
 * @return bool
 */
function pawstars_is_active() {
    return pawstars()->is_enabled();
}

/**
 * Initialize plugin on plugins_loaded hook
 *
 * @since 1.0.0
 */
function pawstars_init() {
    pawstars();
}
add_action( 'plugins_loaded', 'pawstars_init' );
