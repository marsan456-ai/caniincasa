<?php
/**
 * Achievements System
 *
 * Handles badge/achievement checking and awarding.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Achievements Class
 *
 * @since 1.0.0
 */
class Pawstars_Achievements {

    /**
     * Database instance
     *
     * @var Pawstars_Database
     */
    private $db;

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
        // Check achievements after actions
        add_action( 'pawstars_dog_created', array( $this, 'check_dog_created' ), 10, 2 );
        add_action( 'pawstars_after_vote', array( $this, 'check_after_vote' ), 10, 3 );
        add_action( 'pawstars_daily_leaderboard_update', array( $this, 'check_rank_badges' ) );
        add_action( 'pawstars_check_badges', array( $this, 'check_all_badges' ) );
    }

    /**
     * Get all badge definitions
     *
     * @since  1.0.0
     * @return array
     */
    public function get_badges() {
        return get_option( 'pawstars_badges', array() );
    }

    /**
     * Get badge by slug
     *
     * @since  1.0.0
     * @param  string $slug Badge slug
     * @return array|null
     */
    public function get_badge( $slug ) {
        $badges = $this->get_badges();
        return isset( $badges[ $slug ] ) ? $badges[ $slug ] : null;
    }

    /**
     * Award badge
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @param  string $badge_slug  Badge slug
     * @return bool
     */
    public function award( $entity_type, $entity_id, $badge_slug ) {
        // Check if badge exists
        if ( ! $this->get_badge( $badge_slug ) ) {
            return false;
        }

        // Award through database
        $result = $this->db->award_achievement( $entity_type, $entity_id, $badge_slug );

        if ( $result ) {
            // Notify user
            $this->notify_badge_earned( $entity_type, $entity_id, $badge_slug );
        }

        return $result;
    }

    /**
     * Check if entity has badge
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @param  string $badge_slug  Badge slug
     * @return bool
     */
    public function has_badge( $entity_type, $entity_id, $badge_slug ) {
        return $this->db->has_achievement( $entity_type, $entity_id, $badge_slug );
    }

    /**
     * Get entity badges
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @return array
     */
    public function get_entity_badges( $entity_type, $entity_id ) {
        $achievements = $this->db->get_achievements( $entity_type, $entity_id );
        $badges = $this->get_badges();
        $result = array();

        foreach ( $achievements as $achievement ) {
            if ( isset( $badges[ $achievement->badge_slug ] ) ) {
                $result[] = array_merge(
                    $badges[ $achievement->badge_slug ],
                    array(
                        'slug'      => $achievement->badge_slug,
                        'earned_at' => $achievement->earned_at,
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Check for starter badge when dog created
     *
     * @since 1.0.0
     * @param int   $dog_id Dog ID
     * @param array $data   Dog data
     */
    public function check_dog_created( $dog_id, $data ) {
        $user_id = isset( $data['user_id'] ) ? $data['user_id'] : get_current_user_id();

        // Award starter badge to dog
        $this->award( 'dog', $dog_id, 'starter' );

        // Check if first dog for user
        $user_dogs = $this->db->count_user_dogs( $user_id );
        if ( $user_dogs === 1 ) {
            $this->award( 'user', $user_id, 'starter' );
        }
    }

    /**
     * Check badges after vote
     *
     * @since 1.0.0
     * @param int    $dog_id   Dog ID
     * @param int    $user_id  Voter user ID
     * @param string $reaction Reaction type
     */
    public function check_after_vote( $dog_id, $user_id, $reaction ) {
        // Check dog badges (votes received)
        $this->check_dog_vote_badges( $dog_id );

        // Check voter badges (votes given)
        $this->check_voter_badges( $user_id, $reaction );
    }

    /**
     * Check dog vote badges
     *
     * @since 1.0.0
     * @param int $dog_id Dog ID
     */
    private function check_dog_vote_badges( $dog_id ) {
        $stats = $this->db->get_dog_vote_stats( $dog_id );
        $total_votes = $stats['total']['count'];

        // Social Pup - 50 votes
        if ( $total_votes >= 50 && ! $this->has_badge( 'dog', $dog_id, 'social_pup' ) ) {
            $this->award( 'dog', $dog_id, 'social_pup' );
        }

        // Popular - 100 votes
        if ( $total_votes >= 100 && ! $this->has_badge( 'dog', $dog_id, 'popular' ) ) {
            $this->award( 'dog', $dog_id, 'popular' );
        }
    }

    /**
     * Check voter badges
     *
     * @since 1.0.0
     * @param int    $user_id  User ID
     * @param string $reaction Reaction type
     */
    private function check_voter_badges( $user_id, $reaction ) {
        $stats = $this->db->get_user_vote_stats( $user_id );

        // Active Voter - 50 votes given
        if ( $stats['total'] >= 50 && ! $this->has_badge( 'user', $user_id, 'voter' ) ) {
            $this->award( 'user', $user_id, 'voter' );
        }

        // Super Voter - 200 votes given
        if ( $stats['total'] >= 200 && ! $this->has_badge( 'user', $user_id, 'super_voter' ) ) {
            $this->award( 'user', $user_id, 'super_voter' );
        }

        // Star Giver - 10 stars given
        if ( $stats['stars'] >= 10 && ! $this->has_badge( 'user', $user_id, 'star_giver' ) ) {
            $this->award( 'user', $user_id, 'star_giver' );
        }
    }

    /**
     * Check rank-based badges
     *
     * @since 1.0.0
     */
    public function check_rank_badges() {
        // Get top 10 hot dogs
        $hot_10 = $this->db->get_hot_leaderboard( 10 );
        foreach ( $hot_10 as $dog ) {
            if ( ! $this->has_badge( 'dog', $dog->id, 'top_10_hot' ) ) {
                $this->award( 'dog', $dog->id, 'top_10_hot' );
            }
        }

        // Get top 100 hot dogs
        $hot_100 = $this->db->get_hot_leaderboard( 100 );
        foreach ( $hot_100 as $dog ) {
            if ( ! $this->has_badge( 'dog', $dog->id, 'rising_star' ) ) {
                $this->award( 'dog', $dog->id, 'rising_star' );
            }
        }

        // Get top 10 all-time
        $alltime_10 = $this->db->get_alltime_leaderboard( 10 );
        foreach ( $alltime_10 as $dog ) {
            if ( ! $this->has_badge( 'dog', $dog->id, 'hall_of_fame' ) ) {
                $this->award( 'dog', $dog->id, 'hall_of_fame' );
            }
        }
    }

    /**
     * Check all badges (scheduled task)
     *
     * @since 1.0.0
     */
    public function check_all_badges() {
        global $wpdb;

        $table = $wpdb->prefix . 'pawstars_dogs';

        // Get all active dogs
        $dogs = $wpdb->get_results( "SELECT id FROM $table WHERE status = 'active'" );

        foreach ( $dogs as $dog ) {
            $this->check_dog_vote_badges( $dog->id );

            // Check photo badges
            $dog_data = $this->db->get_dog( $dog->id );
            if ( $dog_data && ! empty( $dog_data->gallery_ids ) && count( $dog_data->gallery_ids ) >= 5 ) {
                if ( ! $this->has_badge( 'dog', $dog->id, 'photo_lover' ) ) {
                    $this->award( 'dog', $dog->id, 'photo_lover' );
                }
            }
        }

        // Check rank badges
        $this->check_rank_badges();
    }

    /**
     * Notify user of badge earned
     *
     * @since 1.0.0
     * @param string $entity_type Entity type
     * @param int    $entity_id   Entity ID
     * @param string $badge_slug  Badge slug
     */
    private function notify_badge_earned( $entity_type, $entity_id, $badge_slug ) {
        $badge = $this->get_badge( $badge_slug );
        if ( ! $badge ) {
            return;
        }

        // Get user to notify
        if ( $entity_type === 'dog' ) {
            $dog = $this->db->get_dog( $entity_id );
            if ( ! $dog ) {
                return;
            }
            $user_id = $dog->user_id;
            $subject = sprintf( __( '[Paw Stars] %s ha ottenuto un badge!', 'pawstars' ), $dog->name );
            $message = sprintf(
                __( "Complimenti! %s ha ottenuto il badge \"%s\"!\n\n%s", 'pawstars' ),
                $dog->name,
                $badge['name'],
                $badge['description']
            );
        } else {
            $user_id = $entity_id;
            $user = get_user_by( 'id', $user_id );
            if ( ! $user ) {
                return;
            }
            $subject = __( '[Paw Stars] Hai ottenuto un badge!', 'pawstars' );
            $message = sprintf(
                __( "Complimenti! Hai ottenuto il badge \"%s\"!\n\n%s", 'pawstars' ),
                $badge['name'],
                $badge['description']
            );
        }

        $user = get_user_by( 'id', $user_id );
        if ( $user && $user->user_email ) {
            wp_mail( $user->user_email, $subject, $message );
        }
    }

    /**
     * Get badge icon HTML
     *
     * @since  1.0.0
     * @param  string $badge_slug Badge slug
     * @return string
     */
    public function get_badge_icon_html( $badge_slug ) {
        $badge = $this->get_badge( $badge_slug );
        if ( ! $badge ) {
            return '';
        }

        $icons = array(
            'star'       => '⭐',
            'heart'      => '❤️',
            'fire'       => '🔥',
            'trending'   => '📈',
            'flame'      => '🌟',
            'trophy'     => '🏆',
            'camera'     => '📷',
            'thumbs-up'  => '👍',
            'award'      => '🏅',
            'star-giver' => '✨',
        );

        $icon = isset( $icons[ $badge['icon'] ] ) ? $icons[ $badge['icon'] ] : '🎖️';

        return sprintf(
            '<span class="pawstars-badge" title="%s">%s</span>',
            esc_attr( $badge['name'] . ' - ' . $badge['description'] ),
            $icon
        );
    }
}
