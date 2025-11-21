<?php
/**
 * Voting System
 *
 * Handles all voting operations and reactions.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Voting System Class
 *
 * @since 1.0.0
 */
class Pawstars_Voting_System {

    /**
     * Database instance
     *
     * @var Pawstars_Database
     */
    private $db;

    /**
     * Reaction types with emoji and points
     *
     * @var array
     */
    private $reactions = array(
        'love'     => array( 'emoji' => '❤️', 'label' => 'Love', 'default_points' => 5 ),
        'adorable' => array( 'emoji' => '😍', 'label' => 'Adorable', 'default_points' => 3 ),
        'star'     => array( 'emoji' => '⭐', 'label' => 'Star', 'default_points' => 10 ),
        'funny'    => array( 'emoji' => '😄', 'label' => 'Funny', 'default_points' => 2 ),
        'aww'      => array( 'emoji' => '🥺', 'label' => 'Aww', 'default_points' => 2 ),
    );

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Pawstars_Database $database Database instance
     */
    public function __construct( $database ) {
        $this->db = $database;
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'wp_ajax_pawstars_vote', array( $this, 'ajax_vote' ) );
        add_action( 'wp_ajax_pawstars_remove_vote', array( $this, 'ajax_remove_vote' ) );
        add_action( 'wp_ajax_nopriv_pawstars_vote', array( $this, 'ajax_vote_guest' ) );
    }

    /**
     * Get reaction types
     *
     * @since  1.0.0
     * @return array
     */
    public function get_reactions() {
        return $this->reactions;
    }

    /**
     * Get reaction by type
     *
     * @since  1.0.0
     * @param  string $type Reaction type
     * @return array|null
     */
    public function get_reaction( $type ) {
        return isset( $this->reactions[ $type ] ) ? $this->reactions[ $type ] : null;
    }

    /**
     * Get points for reaction
     *
     * @since  1.0.0
     * @param  string $type Reaction type
     * @return int
     */
    public function get_points( $type ) {
        $settings = get_option( 'pawstars_settings', array() );
        $key = 'points_' . $type;

        if ( isset( $settings[ $key ] ) ) {
            return (int) $settings[ $key ];
        }

        return isset( $this->reactions[ $type ] ) ? $this->reactions[ $type ]['default_points'] : 0;
    }

    /**
     * Add vote
     *
     * @since  1.0.0
     * @param  int    $dog_id   Dog ID
     * @param  int    $user_id  User ID
     * @param  string $reaction Reaction type
     * @return bool|WP_Error
     */
    public function vote( $dog_id, $user_id, $reaction ) {
        // Validate reaction
        if ( ! isset( $this->reactions[ $reaction ] ) ) {
            return new WP_Error( 'invalid_reaction', __( 'Reazione non valida', 'pawstars' ) );
        }

        // Add vote through database
        $result = $this->db->add_vote( $dog_id, $user_id, $reaction );

        if ( ! is_wp_error( $result ) ) {
            // Check for achievements
            do_action( 'pawstars_after_vote', $dog_id, $user_id, $reaction );
        }

        return $result;
    }

    /**
     * Check if user can vote
     *
     * @since  1.0.0
     * @param  int    $dog_id   Dog ID
     * @param  int    $user_id  User ID
     * @param  string $reaction Reaction type
     * @return bool|WP_Error
     */
    public function can_vote( $dog_id, $user_id, $reaction ) {
        // Check if logged in
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'Devi essere loggato per votare', 'pawstars' ) );
        }

        // Get dog
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'invalid_dog', __( 'Cane non trovato', 'pawstars' ) );
        }

        // Can't vote own dog
        if ( $dog->user_id == $user_id ) {
            return new WP_Error( 'own_dog', __( 'Non puoi votare il tuo cane', 'pawstars' ) );
        }

        // Check if already voted
        if ( $this->db->user_has_voted( $dog_id, $user_id, $reaction ) ) {
            return new WP_Error( 'already_voted', __( 'Hai già votato con questa reaction', 'pawstars' ) );
        }

        // Check star limit
        if ( $reaction === 'star' ) {
            $settings = get_option( 'pawstars_settings', array() );
            $limit = isset( $settings['star_daily_limit'] ) ? $settings['star_daily_limit'] : 1;

            global $wpdb;
            $table = $wpdb->prefix . 'pawstars_votes';
            $today_stars = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table
                     WHERE voter_user_id = %d AND reaction_type = 'star' AND DATE(voted_at) = CURDATE()",
                    $user_id
                )
            );

            if ( $today_stars >= $limit ) {
                return new WP_Error( 'star_limit', sprintf( __( 'Puoi dare solo %d Star al giorno', 'pawstars' ), $limit ) );
            }
        }

        return true;
    }

    /**
     * Get user's votes for a dog
     *
     * @since  1.0.0
     * @param  int $dog_id  Dog ID
     * @param  int $user_id User ID
     * @return array
     */
    public function get_user_votes( $dog_id, $user_id ) {
        return $this->db->user_has_voted( $dog_id, $user_id );
    }

    /**
     * Check remaining stars for today
     *
     * @since  1.0.0
     * @param  int $user_id User ID
     * @return int
     */
    public function get_remaining_stars( $user_id ) {
        global $wpdb;

        $settings = get_option( 'pawstars_settings', array() );
        $limit = isset( $settings['star_daily_limit'] ) ? $settings['star_daily_limit'] : 1;

        $table = $wpdb->prefix . 'pawstars_votes';
        $used = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table
                 WHERE voter_user_id = %d AND reaction_type = 'star' AND DATE(voted_at) = CURDATE()",
                $user_id
            )
        );

        return max( 0, $limit - $used );
    }

    // =========================================================================
    // AJAX HANDLERS
    // =========================================================================

    /**
     * AJAX: Vote (logged in)
     *
     * @since 1.0.0
     */
    public function ajax_vote() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );

        $dog_id = isset( $_POST['dog_id'] ) ? absint( $_POST['dog_id'] ) : 0;
        $reaction = isset( $_POST['reaction'] ) ? sanitize_text_field( $_POST['reaction'] ) : '';
        $user_id = get_current_user_id();

        if ( ! $dog_id || ! $reaction ) {
            wp_send_json_error( array( 'message' => __( 'Dati mancanti', 'pawstars' ) ) );
        }

        // Check if can vote
        $can_vote = $this->can_vote( $dog_id, $user_id, $reaction );
        if ( is_wp_error( $can_vote ) ) {
            wp_send_json_error( array( 'message' => $can_vote->get_error_message() ) );
        }

        // Add vote
        $result = $this->vote( $dog_id, $user_id, $reaction );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Get updated stats
        $dog = $this->db->get_dog( $dog_id );
        $vote_stats = $this->db->get_dog_vote_stats( $dog_id );

        wp_send_json_success( array(
            'message'      => __( 'Voto registrato!', 'pawstars' ),
            'total_points' => $dog->total_points,
            'vote_stats'   => $vote_stats,
            'user_votes'   => $this->get_user_votes( $dog_id, $user_id ),
            'stars_left'   => $this->get_remaining_stars( $user_id ),
        ) );
    }

    /**
     * AJAX: Vote (guest - redirect to login)
     *
     * @since 1.0.0
     */
    public function ajax_vote_guest() {
        wp_send_json_error( array(
            'message'      => __( 'Devi essere loggato per votare', 'pawstars' ),
            'login_url'    => wp_login_url( wp_get_referer() ),
            'require_login' => true,
        ) );
    }

    /**
     * AJAX: Remove vote (not implemented for MVP)
     *
     * @since 1.0.0
     */
    public function ajax_remove_vote() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );
        wp_send_json_error( array( 'message' => __( 'Funzione non disponibile', 'pawstars' ) ) );
    }
}
