<?php
/**
 * Plugin Activator
 *
 * Handles database table creation and initial setup on plugin activation.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activator Class
 *
 * @since 1.0.0
 */
class Pawstars_Activator {

    /**
     * Activate plugin
     *
     * Creates database tables and sets default options.
     *
     * @since 1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::create_default_badges();
        self::schedule_cron_jobs();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        set_transient( 'pawstars_activated', true, 30 );
    }

    /**
     * Create database tables
     *
     * @since 1.0.0
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Dogs table
        $table_dogs = $wpdb->prefix . 'pawstars_dogs';
        $sql_dogs = "CREATE TABLE $table_dogs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            birth_date DATE NULL,
            breed_id BIGINT UNSIGNED NULL,
            provincia VARCHAR(2) NULL,
            bio TEXT NULL,
            featured_image_id BIGINT UNSIGNED NULL,
            gallery_ids TEXT NULL,
            total_points INT DEFAULT 0,
            rank_position INT DEFAULT 0,
            rank_category VARCHAR(50) NULL,
            is_featured BOOLEAN DEFAULT 0,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_breed (breed_id),
            INDEX idx_points (total_points DESC),
            INDEX idx_rank (rank_position ASC),
            INDEX idx_status (status),
            INDEX idx_provincia (provincia),
            INDEX idx_created (created_at DESC)
        ) $charset_collate;";

        // Votes table
        $table_votes = $wpdb->prefix . 'pawstars_votes';
        $sql_votes = "CREATE TABLE $table_votes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            dog_id BIGINT UNSIGNED NOT NULL,
            voter_user_id BIGINT UNSIGNED NOT NULL,
            reaction_type VARCHAR(20) NOT NULL,
            points_value INT NOT NULL,
            voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_vote (dog_id, voter_user_id, reaction_type),
            INDEX idx_dog (dog_id),
            INDEX idx_voter (voter_user_id),
            INDEX idx_reaction (reaction_type),
            INDEX idx_date (voted_at DESC)
        ) $charset_collate;";

        // Achievements table
        $table_achievements = $wpdb->prefix . 'pawstars_achievements';
        $sql_achievements = "CREATE TABLE $table_achievements (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(10) NOT NULL,
            entity_id BIGINT UNSIGNED NOT NULL,
            badge_slug VARCHAR(50) NOT NULL,
            earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_achievement (entity_type, entity_id, badge_slug),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_badge (badge_slug)
        ) $charset_collate;";

        // Challenges table (Phase 2)
        $table_challenges = $wpdb->prefix . 'pawstars_challenges';
        $sql_challenges = "CREATE TABLE $table_challenges (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            dog_id BIGINT UNSIGNED NOT NULL,
            challenge_slug VARCHAR(50) NOT NULL,
            photo_id BIGINT UNSIGNED NOT NULL,
            votes_count INT DEFAULT 0,
            is_winner BOOLEAN DEFAULT 0,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_dog (dog_id),
            INDEX idx_challenge (challenge_slug),
            INDEX idx_winner (is_winner, challenge_slug)
        ) $charset_collate;";

        // Follows table (Phase 2)
        $table_follows = $wpdb->prefix . 'pawstars_follows';
        $sql_follows = "CREATE TABLE $table_follows (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            follower_user_id BIGINT UNSIGNED NOT NULL,
            following_dog_id BIGINT UNSIGNED NOT NULL,
            followed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_follow (follower_user_id, following_dog_id),
            INDEX idx_follower (follower_user_id),
            INDEX idx_following (following_dog_id)
        ) $charset_collate;";

        // Daily stats table for analytics
        $table_stats = $wpdb->prefix . 'pawstars_daily_stats';
        $sql_stats = "CREATE TABLE $table_stats (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            stat_date DATE NOT NULL,
            total_dogs INT DEFAULT 0,
            total_votes INT DEFAULT 0,
            new_dogs INT DEFAULT 0,
            new_votes INT DEFAULT 0,
            active_users INT DEFAULT 0,
            UNIQUE KEY unique_date (stat_date),
            INDEX idx_date (stat_date DESC)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql_dogs );
        dbDelta( $sql_votes );
        dbDelta( $sql_achievements );
        dbDelta( $sql_challenges );
        dbDelta( $sql_follows );
        dbDelta( $sql_stats );

        // Update version
        update_option( 'pawstars_db_version', PAWSTARS_DB_VERSION );
    }

    /**
     * Set default options
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        $default_settings = array(
            'enabled'                 => true,
            'moderation_required'     => true,
            'auto_approve_verified'   => false,
            'max_dogs_per_user'       => 5,
            'max_photos_per_dog'      => 10,
            'max_photo_size_mb'       => 5,
            'star_daily_limit'        => 1,
            'points_love'             => 5,
            'points_adorable'         => 3,
            'points_star'             => 10,
            'points_funny'            => 2,
            'points_aww'              => 2,
            'leaderboard_hot_days'    => 7,
            'leaderboard_cache_time'  => 300, // 5 minutes
            'notification_email'      => get_option( 'admin_email' ),
            'enable_swipe_mobile'     => true,
            'enable_grid_desktop'     => true,
            'dogs_per_page'           => 12,
            'enable_badges'           => true,
            'enable_challenges'       => false, // Phase 2
            'enable_follows'          => false, // Phase 2
        );

        if ( ! get_option( 'pawstars_settings' ) ) {
            add_option( 'pawstars_settings', $default_settings );
        }

        // Individual options for quick access
        if ( ! get_option( 'pawstars_enabled' ) ) {
            add_option( 'pawstars_enabled', true );
        }
    }

    /**
     * Create default badge definitions
     *
     * @since 1.0.0
     */
    private static function create_default_badges() {
        $badges = array(
            'starter' => array(
                'name'        => __( 'Starter', 'pawstars' ),
                'description' => __( 'Primo profilo creato', 'pawstars' ),
                'icon'        => 'star',
                'condition'   => 'dog_created',
                'points'      => 0,
            ),
            'social_pup' => array(
                'name'        => __( 'Social Pup', 'pawstars' ),
                'description' => __( 'Ricevuto 50 voti', 'pawstars' ),
                'icon'        => 'heart',
                'condition'   => 'votes_received_50',
                'points'      => 50,
            ),
            'popular' => array(
                'name'        => __( 'Popular', 'pawstars' ),
                'description' => __( 'Ricevuto 100 voti', 'pawstars' ),
                'icon'        => 'fire',
                'condition'   => 'votes_received_100',
                'points'      => 100,
            ),
            'rising_star' => array(
                'name'        => __( 'Rising Star', 'pawstars' ),
                'description' => __( 'Top 100 Hot Dogs', 'pawstars' ),
                'icon'        => 'trending',
                'condition'   => 'rank_hot_100',
                'points'      => 0,
            ),
            'top_10_hot' => array(
                'name'        => __( 'Hot Dog', 'pawstars' ),
                'description' => __( 'Top 10 Hot Dogs', 'pawstars' ),
                'icon'        => 'flame',
                'condition'   => 'rank_hot_10',
                'points'      => 0,
            ),
            'hall_of_fame' => array(
                'name'        => __( 'Hall of Fame', 'pawstars' ),
                'description' => __( 'Top 10 All Stars', 'pawstars' ),
                'icon'        => 'trophy',
                'condition'   => 'rank_alltime_10',
                'points'      => 0,
            ),
            'photo_lover' => array(
                'name'        => __( 'Photo Lover', 'pawstars' ),
                'description' => __( 'Caricato 5 foto', 'pawstars' ),
                'icon'        => 'camera',
                'condition'   => 'photos_5',
                'points'      => 0,
            ),
            'voter' => array(
                'name'        => __( 'Active Voter', 'pawstars' ),
                'description' => __( 'Dato 50 voti', 'pawstars' ),
                'icon'        => 'thumbs-up',
                'condition'   => 'votes_given_50',
                'points'      => 0,
            ),
            'super_voter' => array(
                'name'        => __( 'Super Voter', 'pawstars' ),
                'description' => __( 'Dato 200 voti', 'pawstars' ),
                'icon'        => 'award',
                'condition'   => 'votes_given_200',
                'points'      => 0,
            ),
            'star_giver' => array(
                'name'        => __( 'Star Giver', 'pawstars' ),
                'description' => __( 'Dato 10 Star', 'pawstars' ),
                'icon'        => 'star-giver',
                'condition'   => 'stars_given_10',
                'points'      => 0,
            ),
        );

        if ( ! get_option( 'pawstars_badges' ) ) {
            add_option( 'pawstars_badges', $badges );
        }
    }

    /**
     * Schedule cron jobs
     *
     * @since 1.0.0
     */
    private static function schedule_cron_jobs() {
        // Daily leaderboard recalculation
        if ( ! wp_next_scheduled( 'pawstars_daily_leaderboard_update' ) ) {
            wp_schedule_event( time(), 'hourly', 'pawstars_daily_leaderboard_update' );
        }

        // Daily stats recording
        if ( ! wp_next_scheduled( 'pawstars_daily_stats' ) ) {
            wp_schedule_event( strtotime( 'midnight' ), 'daily', 'pawstars_daily_stats' );
        }

        // Badge checking
        if ( ! wp_next_scheduled( 'pawstars_check_badges' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'pawstars_check_badges' );
        }
    }
}
