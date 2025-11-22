<?php
/**
 * Plugin Name: Caniincasa Core
 * Plugin URI: https://www.caniincasa.it
 * Description: Plugin core per la gestione dei Custom Post Types e funzionalità avanzate del portale Caniincasa.it - Include gestione razze, annunci, strutture e quiz interattivo.
 * Version: 1.0.0
 * Author: Caniincasa Team
 * Author URI: https://www.caniincasa.it
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: caniincasa-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Define Constants
 */
define( 'CANIINCASA_CORE_VERSION', '1.0.0' );
define( 'CANIINCASA_CORE_FILE', __FILE__ );
define( 'CANIINCASA_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CANIINCASA_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'CANIINCASA_CORE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class
 */
class Caniincasa_Core {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Load plugin text domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Include required files
        $this->includes();

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        // CPT Registrations
        require_once CANIINCASA_CORE_PATH . 'includes/cpt-razze.php';
        require_once CANIINCASA_CORE_PATH . 'includes/cpt-strutture.php';
        require_once CANIINCASA_CORE_PATH . 'includes/cpt-annunci.php';

        // Strutture Claims Management
        require_once CANIINCASA_CORE_PATH . 'includes/cpt-strutture-claims.php';

        // Helper functions
        require_once CANIINCASA_CORE_PATH . 'includes/helpers.php';

        // AJAX Handlers
        require_once CANIINCASA_CORE_PATH . 'includes/ajax-handlers.php';

        // Messaging System
        require_once CANIINCASA_CORE_PATH . 'includes/messaging-system.php';

        // Newsletter System
        require_once CANIINCASA_CORE_PATH . 'includes/newsletter-system.php';

        // Statistics System
        require_once CANIINCASA_CORE_PATH . 'includes/statistics-system.php';

        // Shortcode Generator
        require_once CANIINCASA_CORE_PATH . 'includes/shortcode-generator.php';

        // AI Content Generator
        require_once CANIINCASA_CORE_PATH . 'includes/ai-content-generator.php';

        // CSV Importer
        require_once CANIINCASA_CORE_PATH . 'includes/csv-importer.php';

        // Razze CSV Importer
        require_once CANIINCASA_CORE_PATH . 'includes/razze-csv-importer.php';

        // WP-CLI Commands
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once CANIINCASA_CORE_PATH . 'includes/wp-cli-commands.php';
        }

        // Quiz System (TODO: Implement)
        // require_once CANIINCASA_CORE_PATH . 'includes/quiz-system.php';

        // REST API Endpoints (TODO: Implement)
        // require_once CANIINCASA_CORE_PATH . 'includes/rest-api.php';

        // Admin functionality
        if ( is_admin() ) {
            require_once CANIINCASA_CORE_PATH . 'admin/admin-import.php';
            require_once CANIINCASA_CORE_PATH . 'admin/admin-messages.php';
        }

        // Public/Frontend functionality (TODO: Implement)
        // if ( ! is_admin() ) {
        //     require_once CANIINCASA_CORE_PATH . 'public/shortcodes.php';
        // }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load ACF Fields after ACF has fully initialized (priority 20 after default init priority 10)
        add_action( 'acf/init', array( $this, 'load_acf_fields' ) );

        // Activation/Deactivation hooks
        register_activation_hook( CANIINCASA_CORE_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( CANIINCASA_CORE_FILE, array( $this, 'deactivate' ) );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // Customize posts per page for archives
        add_action( 'pre_get_posts', array( $this, 'modify_archive_posts_per_page' ) );
    }

    /**
     * Modify posts_per_page for custom post type archives
     *
     * @param WP_Query $query The WordPress query object
     */
    public function modify_archive_posts_per_page( $query ) {
        // Only affect main query on frontend, not admin
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Set 24 posts per page for razze_di_cani archive
        if ( $query->is_post_type_archive( 'razze_di_cani' ) || $query->is_tax( array( 'razza_taglia', 'razza_gruppo' ) ) ) {
            $query->set( 'posts_per_page', 24 );
        }

        // Set 12 posts per page for other archives (allevamenti, veterinari, etc.)
        if ( $query->is_post_type_archive( array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' ) ) ) {
            $query->set( 'posts_per_page', 12 );
        }
    }

    /**
     * Load ACF Fields
     */
    public function load_acf_fields() {
        if ( class_exists( 'ACF' ) ) {
            require_once CANIINCASA_CORE_PATH . 'includes/acf-fields.php';
        }
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'caniincasa-core', false, dirname( CANIINCASA_CORE_BASENAME ) . '/languages' );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        $default_options = array(
            'annunci_moderation' => true,
            'annunci_expiry_days' => 30,
            'quiz_enabled' => true,
        );

        foreach ( $default_options as $key => $value ) {
            if ( ! get_option( 'caniincasa_' . $key ) ) {
                add_option( 'caniincasa_' . $key, $value );
            }
        }

        // Create custom database tables if needed
        $this->create_tables();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Quiz results table
        $table_name = $wpdb->prefix . 'caniincasa_quiz_results';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            answers longtext NOT NULL,
            results longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY session_id (session_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Newsletter subscribers table
        $newsletter_table = $wpdb->prefix . 'caniincasa_newsletter';

        $newsletter_sql = "CREATE TABLE IF NOT EXISTS $newsletter_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            gdpr_consent tinyint(1) DEFAULT 1,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            source varchar(50) DEFAULT 'form',
            subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at datetime DEFAULT NULL,
            confirm_token varchar(64) DEFAULT NULL,
            confirmed tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY source (source)
        ) $charset_collate;";

        dbDelta( $newsletter_sql );

        // Update version
        update_option( 'caniincasa_core_db_version', CANIINCASA_CORE_VERSION );
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Plugin CSS
        wp_enqueue_style(
            'caniincasa-core',
            CANIINCASA_CORE_URL . 'assets/css/public.css',
            array(),
            CANIINCASA_CORE_VERSION
        );

        // Plugin JS
        wp_enqueue_script(
            'caniincasa-core',
            CANIINCASA_CORE_URL . 'assets/js/public.js',
            array( 'jquery' ),
            CANIINCASA_CORE_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'caniincasa-core', 'caniincasaCore', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'caniincasa_core_nonce' ),
            'strings' => array(
                'loading'       => __( 'Caricamento...', 'caniincasa-core' ),
                'error'         => __( 'Si è verificato un errore', 'caniincasa-core' ),
                'success'       => __( 'Operazione completata', 'caniincasa-core' ),
                'confirm'       => __( 'Sei sicuro?', 'caniincasa-core' ),
            ),
        ) );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts( $hook ) {
        // Admin CSS
        wp_enqueue_style(
            'caniincasa-core-admin',
            CANIINCASA_CORE_URL . 'assets/css/admin.css',
            array(),
            CANIINCASA_CORE_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'caniincasa-core-admin',
            CANIINCASA_CORE_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            CANIINCASA_CORE_VERSION,
            true
        );

        // Localize admin script
        wp_localize_script( 'caniincasa-core-admin', 'caniincasaCoreAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'caniincasa_core_admin_nonce' ),
        ) );
    }
}

/**
 * Initialize plugin
 */
function caniincasa_core() {
    return Caniincasa_Core::instance();
}

// Start the plugin
caniincasa_core();
