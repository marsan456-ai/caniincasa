<?php
/**
 * Admin Menu
 *
 * Handles admin menu and dashboard.
 *
 * @package    Pawstars
 * @subpackage Pawstars/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Menu Class
 *
 * @since 1.0.0
 */
class Pawstars_Admin_Menu {

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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
        add_action( 'admin_notices', array( $this, 'activation_notice' ) );
    }

    /**
     * Add menu pages
     *
     * @since 1.0.0
     */
    public function add_menu_pages() {
        // Main menu
        add_menu_page(
            __( 'Paw Stars', 'pawstars' ),
            __( 'Paw Stars', 'pawstars' ),
            'manage_options',
            'pawstars',
            array( $this, 'render_dashboard' ),
            'dashicons-star-filled',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'pawstars',
            __( 'Dashboard', 'pawstars' ),
            __( 'Dashboard', 'pawstars' ),
            'manage_options',
            'pawstars',
            array( $this, 'render_dashboard' )
        );

        // Moderation submenu
        add_submenu_page(
            'pawstars',
            __( 'Moderazione', 'pawstars' ),
            __( 'Moderazione', 'pawstars' ),
            'manage_options',
            'pawstars-moderation',
            array( $this, 'render_moderation' )
        );

        // Settings submenu
        add_submenu_page(
            'pawstars',
            __( 'Impostazioni', 'pawstars' ),
            __( 'Impostazioni', 'pawstars' ),
            'manage_options',
            'pawstars-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Render dashboard page
     *
     * @since 1.0.0
     */
    public function render_dashboard() {
        $stats = $this->plugin->database->get_global_stats();
        $hot_dogs = $this->plugin->leaderboard->get_hot( 5 );
        $recent_dogs = $this->plugin->database->get_dogs( array(
            'status'  => null,
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 10,
        ) );

        include PAWSTARS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render moderation page
     *
     * @since 1.0.0
     */
    public function render_moderation() {
        // Handle actions
        $this->handle_moderation_actions();

        // Get pending dogs
        $pending_dogs = $this->plugin->database->get_dogs( array(
            'status'  => 'pending',
            'orderby' => 'created_at',
            'order'   => 'ASC',
            'limit'   => 50,
        ) );

        include PAWSTARS_PLUGIN_DIR . 'admin/views/moderation.php';
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     */
    public function render_settings() {
        // Handle save
        if ( isset( $_POST['pawstars_save_settings'] ) && check_admin_referer( 'pawstars_settings_nonce' ) ) {
            $this->save_settings();
        }

        $settings = get_option( 'pawstars_settings', array() );

        include PAWSTARS_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Handle moderation actions
     *
     * @since 1.0.0
     */
    private function handle_moderation_actions() {
        if ( ! isset( $_GET['action'] ) || ! isset( $_GET['dog_id'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'pawstars_moderate' ) ) {
            return;
        }

        $action = sanitize_text_field( $_GET['action'] );
        $dog_id = absint( $_GET['dog_id'] );

        switch ( $action ) {
            case 'approve':
                $this->plugin->database->update_dog( $dog_id, array( 'status' => 'active' ) );
                $this->notify_owner_approved( $dog_id );
                add_settings_error( 'pawstars', 'approved', __( 'Profilo approvato!', 'pawstars' ), 'success' );
                break;

            case 'reject':
                $this->plugin->database->update_dog( $dog_id, array( 'status' => 'rejected' ) );
                $this->notify_owner_rejected( $dog_id );
                add_settings_error( 'pawstars', 'rejected', __( 'Profilo rifiutato', 'pawstars' ), 'warning' );
                break;

            case 'delete':
                $this->plugin->database->delete_dog( $dog_id );
                add_settings_error( 'pawstars', 'deleted', __( 'Profilo eliminato', 'pawstars' ), 'warning' );
                break;
        }

        // Redirect to remove query args
        wp_redirect( admin_url( 'admin.php?page=pawstars-moderation' ) );
        exit;
    }

    /**
     * Save settings
     *
     * @since 1.0.0
     */
    private function save_settings() {
        $settings = array(
            'enabled'                => isset( $_POST['enabled'] ),
            'moderation_required'    => isset( $_POST['moderation_required'] ),
            'auto_approve_verified'  => isset( $_POST['auto_approve_verified'] ),
            'max_dogs_per_user'      => absint( $_POST['max_dogs_per_user'] ?? 5 ),
            'max_photos_per_dog'     => absint( $_POST['max_photos_per_dog'] ?? 10 ),
            'max_photo_size_mb'      => absint( $_POST['max_photo_size_mb'] ?? 5 ),
            'star_daily_limit'       => absint( $_POST['star_daily_limit'] ?? 1 ),
            'points_love'            => absint( $_POST['points_love'] ?? 5 ),
            'points_adorable'        => absint( $_POST['points_adorable'] ?? 3 ),
            'points_star'            => absint( $_POST['points_star'] ?? 10 ),
            'points_funny'           => absint( $_POST['points_funny'] ?? 2 ),
            'points_aww'             => absint( $_POST['points_aww'] ?? 2 ),
            'leaderboard_hot_days'   => absint( $_POST['leaderboard_hot_days'] ?? 7 ),
            'leaderboard_cache_time' => absint( $_POST['leaderboard_cache_time'] ?? 300 ),
            'notification_email'     => sanitize_email( $_POST['notification_email'] ?? get_option( 'admin_email' ) ),
            'enable_swipe_mobile'    => isset( $_POST['enable_swipe_mobile'] ),
            'enable_grid_desktop'    => isset( $_POST['enable_grid_desktop'] ),
            'dogs_per_page'          => absint( $_POST['dogs_per_page'] ?? 12 ),
            'enable_badges'          => isset( $_POST['enable_badges'] ),
        );

        update_option( 'pawstars_settings', $settings );
        update_option( 'pawstars_enabled', $settings['enabled'] );

        add_settings_error( 'pawstars', 'saved', __( 'Impostazioni salvate!', 'pawstars' ), 'success' );
    }

    /**
     * Notify owner of approved profile
     *
     * @since 1.0.0
     * @param int $dog_id Dog ID
     */
    private function notify_owner_approved( $dog_id ) {
        $dog = $this->plugin->database->get_dog( $dog_id );
        if ( ! $dog ) {
            return;
        }

        $user = get_user_by( 'id', $dog->user_id );
        if ( ! $user ) {
            return;
        }

        $subject = sprintf( __( '[Paw Stars] Il profilo di %s è stato approvato!', 'pawstars' ), $dog->name );
        $message = sprintf(
            __( "Ottima notizia! Il profilo di %s è stato approvato e ora è visibile a tutti!\n\nVai a vedere: %s", 'pawstars' ),
            $dog->name,
            home_url( '/paw-stars/?dog=' . $dog_id )
        );

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Notify owner of rejected profile
     *
     * @since 1.0.0
     * @param int $dog_id Dog ID
     */
    private function notify_owner_rejected( $dog_id ) {
        $dog = $this->plugin->database->get_dog( $dog_id );
        if ( ! $dog ) {
            return;
        }

        $user = get_user_by( 'id', $dog->user_id );
        if ( ! $user ) {
            return;
        }

        $subject = sprintf( __( '[Paw Stars] Il profilo di %s non è stato approvato', 'pawstars' ), $dog->name );
        $message = sprintf(
            __( "Ci dispiace, il profilo di %s non ha superato la moderazione.\n\nPuoi modificarlo e riprovare dalla tua dashboard.", 'pawstars' ),
            $dog->name
        );

        wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Show activation notice
     *
     * @since 1.0.0
     */
    public function activation_notice() {
        if ( ! get_transient( 'pawstars_activated' ) ) {
            return;
        }

        delete_transient( 'pawstars_activated' );

        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e( 'Paw Stars attivato!', 'pawstars' ); ?></strong>
                <?php
                printf(
                    wp_kses(
                        __( 'Vai alle <a href="%s">impostazioni</a> per configurare il plugin.', 'pawstars' ),
                        array( 'a' => array( 'href' => array() ) )
                    ),
                    admin_url( 'admin.php?page=pawstars-settings' )
                );
                ?>
            </p>
        </div>
        <?php
    }
}
