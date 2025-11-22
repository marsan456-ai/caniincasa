<?php
/**
 * Database Handler
 *
 * Handles all database operations for the plugin.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database Class
 *
 * @since 1.0.0
 */
class Pawstars_Database {

    /**
     * Table names
     *
     * @var array
     */
    private $tables;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;

        $this->tables = array(
            'dogs'         => $wpdb->prefix . 'pawstars_dogs',
            'votes'        => $wpdb->prefix . 'pawstars_votes',
            'achievements' => $wpdb->prefix . 'pawstars_achievements',
            'challenges'   => $wpdb->prefix . 'pawstars_challenges',
            'follows'      => $wpdb->prefix . 'pawstars_follows',
            'stats'        => $wpdb->prefix . 'pawstars_daily_stats',
        );
    }

    /**
     * Get table name
     *
     * @since  1.0.0
     * @param  string $table Table key
     * @return string
     */
    public function get_table( $table ) {
        return isset( $this->tables[ $table ] ) ? $this->tables[ $table ] : '';
    }

    /**
     * Process dog fields (add computed fields)
     *
     * Reusable helper to parse gallery_ids and add image_url/breed_name
     *
     * @since  1.0.0
     * @param  object $dog        Dog object to process (passed by reference)
     * @param  array  $image_urls Optional pre-fetched image URLs map
     * @param  array  $breed_names Optional pre-fetched breed names map
     * @return void
     */
    private function process_dog_fields( &$dog, $image_urls = array(), $breed_names = array() ) {
        // Parse gallery JSON
        if ( ! empty( $dog->gallery_ids ) ) {
            $decoded = json_decode( $dog->gallery_ids, true );
            $dog->gallery_ids = is_array( $decoded ) ? $decoded : array();
        } else {
            $dog->gallery_ids = array();
        }

        // Set image URL
        if ( ! empty( $image_urls ) ) {
            $dog->image_url = isset( $image_urls[ $dog->featured_image_id ] ) ? $image_urls[ $dog->featured_image_id ] : '';
        } else {
            $dog->image_url = $dog->featured_image_id ? wp_get_attachment_url( $dog->featured_image_id ) : '';
        }

        // Set breed name
        if ( ! empty( $breed_names ) ) {
            $dog->breed_name = isset( $breed_names[ $dog->breed_id ] ) ? $breed_names[ $dog->breed_id ] : '';
        } else {
            $dog->breed_name = $dog->breed_id ? get_the_title( $dog->breed_id ) : '';
        }
    }

    // =========================================================================
    // DOGS CRUD
    // =========================================================================

    /**
     * Create dog profile
     *
     * @since  1.0.0
     * @param  array $data Dog data
     * @return int|WP_Error Dog ID or error
     */
    public function create_dog( $data ) {
        global $wpdb;

        $defaults = array(
            'user_id'           => 0,
            'name'              => '',
            'birth_date'        => null,
            'breed_id'          => null,
            'provincia'         => null,
            'bio'               => '',
            'featured_image_id' => null,
            'gallery_ids'       => '',
            'total_points'      => 0,
            'status'            => 'pending',
        );

        $data = wp_parse_args( $data, $defaults );

        // Validate required fields
        if ( empty( $data['user_id'] ) || empty( $data['name'] ) ) {
            return new WP_Error( 'missing_data', __( 'Dati mancanti', 'pawstars' ) );
        }

        // Check user dog limit
        $settings = get_option( 'pawstars_settings', array() );
        $max_dogs = isset( $settings['max_dogs_per_user'] ) ? $settings['max_dogs_per_user'] : 5;
        $user_dogs = $this->count_user_dogs( $data['user_id'] );

        if ( $user_dogs >= $max_dogs ) {
            return new WP_Error( 'limit_reached', sprintf( __( 'Puoi avere massimo %d cani', 'pawstars' ), $max_dogs ) );
        }

        // Sanitize data
        $insert_data = array(
            'user_id'           => absint( $data['user_id'] ),
            'name'              => sanitize_text_field( $data['name'] ),
            'birth_date'        => $data['birth_date'] ? sanitize_text_field( $data['birth_date'] ) : null,
            'breed_id'          => $data['breed_id'] ? absint( $data['breed_id'] ) : null,
            'provincia'         => $data['provincia'] ? strtoupper( sanitize_text_field( $data['provincia'] ) ) : null,
            'bio'               => sanitize_textarea_field( $data['bio'] ),
            'featured_image_id' => $data['featured_image_id'] ? absint( $data['featured_image_id'] ) : null,
            'gallery_ids'       => is_array( $data['gallery_ids'] ) ? wp_json_encode( $data['gallery_ids'] ) : $data['gallery_ids'],
            'total_points'      => 0,
            'status'            => 'pending',
        );

        $result = $wpdb->insert(
            $this->tables['dogs'],
            $insert_data,
            array( '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%d', '%s' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Errore database', 'pawstars' ) );
        }

        $dog_id = $wpdb->insert_id;

        // Trigger action
        do_action( 'pawstars_dog_created', $dog_id, $insert_data );

        // Clear cache after creating new dog
        $this->clear_dogs_cache();

        return $dog_id;
    }

    /**
     * Update dog profile
     *
     * @since  1.0.0
     * @param  int   $dog_id Dog ID
     * @param  array $data   Data to update
     * @return bool|WP_Error
     */
    public function update_dog( $dog_id, $data ) {
        global $wpdb;

        $dog = $this->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        $update_data = array();
        $format = array();

        $allowed_fields = array(
            'name'              => '%s',
            'birth_date'        => '%s',
            'breed_id'          => '%d',
            'provincia'         => '%s',
            'bio'               => '%s',
            'featured_image_id' => '%d',
            'gallery_ids'       => '%s',
            'status'            => '%s',
            'is_featured'       => '%d',
            'total_points'      => '%d',
            'rank_position'     => '%d',
            'rank_category'     => '%s',
        );

        foreach ( $allowed_fields as $field => $field_format ) {
            if ( isset( $data[ $field ] ) ) {
                if ( $field === 'gallery_ids' && is_array( $data[ $field ] ) ) {
                    $update_data[ $field ] = wp_json_encode( $data[ $field ] );
                } elseif ( $field === 'provincia' ) {
                    $update_data[ $field ] = strtoupper( sanitize_text_field( $data[ $field ] ) );
                } elseif ( $field === 'bio' ) {
                    $update_data[ $field ] = sanitize_textarea_field( $data[ $field ] );
                } elseif ( $field === 'name' ) {
                    $update_data[ $field ] = sanitize_text_field( $data[ $field ] );
                } else {
                    $update_data[ $field ] = $data[ $field ];
                }
                $format[] = $field_format;
            }
        }

        if ( empty( $update_data ) ) {
            return new WP_Error( 'no_data', __( 'Nessun dato da aggiornare', 'pawstars' ) );
        }

        $result = $wpdb->update(
            $this->tables['dogs'],
            $update_data,
            array( 'id' => $dog_id ),
            $format,
            array( '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Errore database', 'pawstars' ) );
        }

        // Clear cache
        wp_cache_delete( 'pawstars_dog_' . $dog_id );
        delete_transient( 'pawstars_leaderboard_hot' );
        delete_transient( 'pawstars_leaderboard_alltime' );
        $this->clear_dogs_cache();

        // Trigger action
        do_action( 'pawstars_dog_updated', $dog_id, $update_data );

        return true;
    }

    /**
     * Delete dog profile
     *
     * @since  1.0.0
     * @param  int $dog_id Dog ID
     * @return bool|WP_Error
     */
    public function delete_dog( $dog_id ) {
        global $wpdb;

        $dog = $this->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        // Delete votes
        $wpdb->delete( $this->tables['votes'], array( 'dog_id' => $dog_id ), array( '%d' ) );

        // Delete achievements
        $wpdb->delete(
            $this->tables['achievements'],
            array(
                'entity_type' => 'dog',
                'entity_id'   => $dog_id,
            ),
            array( '%s', '%d' )
        );

        // Delete follows
        $wpdb->delete( $this->tables['follows'], array( 'following_dog_id' => $dog_id ), array( '%d' ) );

        // Delete dog
        $result = $wpdb->delete( $this->tables['dogs'], array( 'id' => $dog_id ), array( '%d' ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Errore database', 'pawstars' ) );
        }

        // Clear caches
        wp_cache_delete( 'pawstars_dog_' . $dog_id );
        delete_transient( 'pawstars_leaderboard_hot' );
        delete_transient( 'pawstars_leaderboard_alltime' );
        $this->clear_dogs_cache();

        // Trigger action
        do_action( 'pawstars_dog_deleted', $dog_id );

        return true;
    }

    /**
     * Get single dog
     *
     * @since  1.0.0
     * @param  int $dog_id Dog ID
     * @return object|null
     */
    public function get_dog( $dog_id ) {
        global $wpdb;

        $cached = wp_cache_get( 'pawstars_dog_' . $dog_id );
        if ( false !== $cached ) {
            return $cached;
        }

        $dog = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->tables['dogs']} WHERE id = %d",
                $dog_id
            )
        );

        if ( $dog ) {
            // Process common fields
            $this->process_dog_fields( $dog );

            // Add author name (specific to single dog)
            $dog->author_name = get_the_author_meta( 'display_name', $dog->user_id );

            wp_cache_set( 'pawstars_dog_' . $dog_id, $dog, '', 300 );
        }

        return $dog;
    }

    /**
     * Get dogs with filters
     *
     * @since  1.0.0
     * @param  array $args Query arguments
     * @return array
     */
    public function get_dogs( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'     => 'active',
            'user_id'    => null,
            'breed_id'   => null,
            'provincia'  => null,
            'search'     => '',
            'orderby'    => 'created_at',
            'order'      => 'DESC',
            'limit'      => 12,
            'offset'     => 0,
            'exclude'    => array(),
            'featured'   => null,
            'no_cache'   => false,
        );

        $args = wp_parse_args( $args, $defaults );

        // Use transient cache for public queries (no user-specific data)
        $use_cache = ! $args['no_cache'] &&
                     $args['status'] === 'active' &&
                     empty( $args['user_id'] ) &&
                     empty( $args['search'] );

        if ( $use_cache ) {
            $cache_key = 'pawstars_dogs_' . md5( serialize( $args ) );
            $cached = get_transient( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $where = array( '1=1' );
        $values = array();

        // Status filter
        if ( $args['status'] ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        // User filter
        if ( $args['user_id'] ) {
            $where[] = 'user_id = %d';
            $values[] = absint( $args['user_id'] );
        }

        // Breed filter
        if ( $args['breed_id'] ) {
            $where[] = 'breed_id = %d';
            $values[] = absint( $args['breed_id'] );
        }

        // Provincia filter
        if ( $args['provincia'] ) {
            $where[] = 'provincia = %s';
            $values[] = strtoupper( sanitize_text_field( $args['provincia'] ) );
        }

        // Featured filter
        if ( $args['featured'] !== null ) {
            $where[] = 'is_featured = %d';
            $values[] = $args['featured'] ? 1 : 0;
        }

        // Search
        if ( ! empty( $args['search'] ) ) {
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where[] = '(name LIKE %s OR bio LIKE %s)';
            $values[] = $search;
            $values[] = $search;
        }

        // Exclude
        if ( ! empty( $args['exclude'] ) ) {
            $exclude_ids = array_map( 'absint', (array) $args['exclude'] );
            $placeholders = implode( ',', array_fill( 0, count( $exclude_ids ), '%d' ) );
            $where[] = "id NOT IN ($placeholders)";
            $values = array_merge( $values, $exclude_ids );
        }

        // Build query
        $where_clause = implode( ' AND ', $where );

        // Orderby
        $allowed_orderby = array( 'created_at', 'total_points', 'name', 'rank_position' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $limit = absint( $args['limit'] );
        $offset = absint( $args['offset'] );

        $sql = "SELECT * FROM {$this->tables['dogs']}
                WHERE $where_clause
                ORDER BY $orderby $order
                LIMIT $limit OFFSET $offset";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        $dogs = $wpdb->get_results( $sql );

        if ( empty( $dogs ) ) {
            return $dogs;
        }

        // Batch collect IDs to avoid N+1 queries
        $image_ids = array();
        $breed_ids = array();

        foreach ( $dogs as $dog ) {
            if ( ! empty( $dog->featured_image_id ) ) {
                $image_ids[] = (int) $dog->featured_image_id;
            }
            if ( ! empty( $dog->breed_id ) ) {
                $breed_ids[] = (int) $dog->breed_id;
            }
        }

        // Batch query for images
        $image_urls = array();
        if ( ! empty( $image_ids ) ) {
            $image_ids_unique = array_unique( $image_ids );
            foreach ( $image_ids_unique as $img_id ) {
                $image_urls[ $img_id ] = wp_get_attachment_url( $img_id );
            }
        }

        // Batch query for breed names
        $breed_names = array();
        if ( ! empty( $breed_ids ) ) {
            $breed_ids_unique = array_unique( $breed_ids );
            $breeds = get_posts( array(
                'post_type'      => 'razze_di_cani',
                'post__in'       => $breed_ids_unique,
                'posts_per_page' => count( $breed_ids_unique ),
                'post_status'    => 'publish',
            ) );
            foreach ( $breeds as $breed ) {
                $breed_names[ $breed->ID ] = $breed->post_title;
            }
        }

        // Process results with cached data
        foreach ( $dogs as &$dog ) {
            $this->process_dog_fields( $dog, $image_urls, $breed_names );
        }

        // Save to cache (5 minutes TTL)
        if ( $use_cache && ! empty( $dogs ) ) {
            set_transient( $cache_key, $dogs, 5 * MINUTE_IN_SECONDS );
        }

        return $dogs;
    }

    /**
     * Clear dogs cache
     *
     * @since 1.0.0
     */
    public function clear_dogs_cache() {
        global $wpdb;

        // Delete all pawstars_dogs_ transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_pawstars_dogs_%'
             OR option_name LIKE '_transient_timeout_pawstars_dogs_%'"
        );
    }

    /**
     * Count dogs with filters
     *
     * @since  1.0.0
     * @param  array $args Query arguments
     * @return int
     */
    public function count_dogs( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'    => 'active',
            'user_id'   => null,
            'breed_id'  => null,
            'provincia' => null,
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( $args['status'] ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ( $args['user_id'] ) {
            $where[] = 'user_id = %d';
            $values[] = absint( $args['user_id'] );
        }

        if ( $args['breed_id'] ) {
            $where[] = 'breed_id = %d';
            $values[] = absint( $args['breed_id'] );
        }

        if ( $args['provincia'] ) {
            $where[] = 'provincia = %s';
            $values[] = strtoupper( $args['provincia'] );
        }

        $where_clause = implode( ' AND ', $where );

        $sql = "SELECT COUNT(*) FROM {$this->tables['dogs']} WHERE $where_clause";

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Count user's dogs
     *
     * @since  1.0.0
     * @param  int $user_id User ID
     * @return int
     */
    public function count_user_dogs( $user_id ) {
        return $this->count_dogs( array(
            'user_id' => $user_id,
            'status'  => null, // All statuses
        ) );
    }

    // =========================================================================
    // VOTES
    // =========================================================================

    /**
     * Add vote
     *
     * @since  1.0.0
     * @param  int    $dog_id        Dog ID
     * @param  int    $voter_user_id Voter user ID
     * @param  string $reaction_type Reaction type
     * @return bool|WP_Error
     */
    public function add_vote( $dog_id, $voter_user_id, $reaction_type ) {
        global $wpdb;

        // Validate reaction type
        $valid_reactions = array( 'love', 'adorable', 'star', 'funny', 'aww' );
        if ( ! in_array( $reaction_type, $valid_reactions ) ) {
            return new WP_Error( 'invalid_reaction', __( 'Reazione non valida', 'pawstars' ) );
        }

        // Get dog
        $dog = $this->get_dog( $dog_id );
        if ( ! $dog || $dog->status !== 'active' ) {
            return new WP_Error( 'invalid_dog', __( 'Cane non trovato o non attivo', 'pawstars' ) );
        }

        // Can't vote own dog
        if ( $dog->user_id == $voter_user_id ) {
            return new WP_Error( 'own_dog', __( 'Non puoi votare il tuo cane', 'pawstars' ) );
        }

        // Check if already voted with this reaction
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->tables['votes']}
                 WHERE dog_id = %d AND voter_user_id = %d AND reaction_type = %s",
                $dog_id,
                $voter_user_id,
                $reaction_type
            )
        );

        if ( $existing ) {
            return new WP_Error( 'already_voted', __( 'Hai già votato con questa reaction', 'pawstars' ) );
        }

        // Check star daily limit
        if ( $reaction_type === 'star' ) {
            $settings = get_option( 'pawstars_settings', array() );
            $star_limit = isset( $settings['star_daily_limit'] ) ? $settings['star_daily_limit'] : 1;

            $today_stars = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->tables['votes']}
                     WHERE voter_user_id = %d
                       AND reaction_type = 'star'
                       AND DATE(voted_at) = CURDATE()",
                    $voter_user_id
                )
            );

            if ( $today_stars >= $star_limit ) {
                return new WP_Error( 'star_limit', sprintf( __( 'Puoi dare solo %d Star al giorno', 'pawstars' ), $star_limit ) );
            }
        }

        // Get points value
        $settings = get_option( 'pawstars_settings', array() );
        $points_map = array(
            'love'     => isset( $settings['points_love'] ) ? (int) $settings['points_love'] : 5,
            'adorable' => isset( $settings['points_adorable'] ) ? (int) $settings['points_adorable'] : 3,
            'star'     => isset( $settings['points_star'] ) ? (int) $settings['points_star'] : 10,
            'funny'    => isset( $settings['points_funny'] ) ? (int) $settings['points_funny'] : 2,
            'aww'      => isset( $settings['points_aww'] ) ? (int) $settings['points_aww'] : 2,
        );
        // Defensive check for reaction type (should be validated above)
        $points = isset( $points_map[ $reaction_type ] ) ? $points_map[ $reaction_type ] : 0;

        // Insert vote
        $result = $wpdb->insert(
            $this->tables['votes'],
            array(
                'dog_id'        => $dog_id,
                'voter_user_id' => $voter_user_id,
                'reaction_type' => $reaction_type,
                'points_value'  => $points,
            ),
            array( '%d', '%d', '%s', '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Errore database', 'pawstars' ) );
        }

        // Update dog total points
        $this->recalculate_dog_points( $dog_id );

        // Clear caches
        wp_cache_delete( 'pawstars_dog_' . $dog_id );
        delete_transient( 'pawstars_leaderboard_hot' );
        delete_transient( 'pawstars_leaderboard_alltime' );

        // Trigger action
        do_action( 'pawstars_vote_added', $dog_id, $voter_user_id, $reaction_type, $points );

        return true;
    }

    /**
     * Recalculate dog total points
     *
     * @since 1.0.0
     * @param int $dog_id Dog ID
     */
    public function recalculate_dog_points( $dog_id ) {
        global $wpdb;

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(points_value), 0) FROM {$this->tables['votes']} WHERE dog_id = %d",
                $dog_id
            )
        );

        $wpdb->update(
            $this->tables['dogs'],
            array( 'total_points' => $total ),
            array( 'id' => $dog_id ),
            array( '%d' ),
            array( '%d' )
        );
    }

    /**
     * Get dog vote stats
     *
     * @since  1.0.0
     * @param  int $dog_id Dog ID
     * @return array
     */
    public function get_dog_vote_stats( $dog_id ) {
        global $wpdb;

        $stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT reaction_type, COUNT(*) as count, SUM(points_value) as points
                 FROM {$this->tables['votes']}
                 WHERE dog_id = %d
                 GROUP BY reaction_type",
                $dog_id
            ),
            OBJECT_K
        );

        $result = array(
            'love'     => array( 'count' => 0, 'points' => 0 ),
            'adorable' => array( 'count' => 0, 'points' => 0 ),
            'star'     => array( 'count' => 0, 'points' => 0 ),
            'funny'    => array( 'count' => 0, 'points' => 0 ),
            'aww'      => array( 'count' => 0, 'points' => 0 ),
            'total'    => array( 'count' => 0, 'points' => 0 ),
        );

        foreach ( $stats as $type => $data ) {
            $result[ $type ] = array(
                'count'  => (int) $data->count,
                'points' => (int) $data->points,
            );
            $result['total']['count'] += (int) $data->count;
            $result['total']['points'] += (int) $data->points;
        }

        return $result;
    }

    /**
     * Check if user voted for dog
     *
     * @since  1.0.0
     * @param  int    $dog_id        Dog ID
     * @param  int    $user_id       User ID
     * @param  string $reaction_type Specific reaction or null for any
     * @return bool|array
     */
    public function user_has_voted( $dog_id, $user_id, $reaction_type = null ) {
        global $wpdb;

        if ( $reaction_type ) {
            return (bool) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$this->tables['votes']}
                     WHERE dog_id = %d AND voter_user_id = %d AND reaction_type = %s",
                    $dog_id,
                    $user_id,
                    $reaction_type
                )
            );
        }

        // Return array of reactions user has given
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT reaction_type FROM {$this->tables['votes']}
                 WHERE dog_id = %d AND voter_user_id = %d",
                $dog_id,
                $user_id
            )
        );
    }

    /**
     * Get user vote stats (votes given)
     *
     * @since  1.0.0
     * @param  int $user_id User ID
     * @return array
     */
    public function get_user_vote_stats( $user_id ) {
        global $wpdb;

        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tables['votes']} WHERE voter_user_id = %d",
                $user_id
            )
        );

        $stars = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->tables['votes']}
                 WHERE voter_user_id = %d AND reaction_type = 'star'",
                $user_id
            )
        );

        return array(
            'total'  => $total,
            'stars'  => $stars,
        );
    }

    // =========================================================================
    // ACHIEVEMENTS
    // =========================================================================

    /**
     * Award achievement
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @param  string $badge_slug  Badge slug
     * @return bool
     */
    public function award_achievement( $entity_type, $entity_id, $badge_slug ) {
        global $wpdb;

        // Check if already has badge
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->tables['achievements']}
                 WHERE entity_type = %s AND entity_id = %d AND badge_slug = %s",
                $entity_type,
                $entity_id,
                $badge_slug
            )
        );

        if ( $existing ) {
            return false;
        }

        $result = $wpdb->insert(
            $this->tables['achievements'],
            array(
                'entity_type' => $entity_type,
                'entity_id'   => $entity_id,
                'badge_slug'  => $badge_slug,
            ),
            array( '%s', '%d', '%s' )
        );

        if ( $result ) {
            do_action( 'pawstars_achievement_awarded', $entity_type, $entity_id, $badge_slug );
        }

        return (bool) $result;
    }

    /**
     * Get entity achievements
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @return array
     */
    public function get_achievements( $entity_type, $entity_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->tables['achievements']}
                 WHERE entity_type = %s AND entity_id = %d
                 ORDER BY earned_at DESC",
                $entity_type,
                $entity_id
            )
        );
    }

    /**
     * Check if entity has achievement
     *
     * @since  1.0.0
     * @param  string $entity_type 'dog' or 'user'
     * @param  int    $entity_id   Entity ID
     * @param  string $badge_slug  Badge slug
     * @return bool
     */
    public function has_achievement( $entity_type, $entity_id, $badge_slug ) {
        global $wpdb;

        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->tables['achievements']}
                 WHERE entity_type = %s AND entity_id = %d AND badge_slug = %s",
                $entity_type,
                $entity_id,
                $badge_slug
            )
        );
    }

    // =========================================================================
    // LEADERBOARDS
    // =========================================================================

    /**
     * Get hot dogs leaderboard (last X days)
     *
     * @since  1.0.0
     * @param  int $limit Number of results
     * @param  int $days  Number of days
     * @return array
     */
    public function get_hot_leaderboard( $limit = 10, $days = 7 ) {
        global $wpdb;

        $cache_key = 'pawstars_leaderboard_hot_' . $limit . '_' . $days;
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        // MySQL 5.7+ strict mode compatible query using subquery
        $dogs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT d.*, COALESCE(hp.hot_points, 0) as hot_points
                 FROM {$this->tables['dogs']} d
                 LEFT JOIN (
                     SELECT dog_id, SUM(points_value) as hot_points
                     FROM {$this->tables['votes']}
                     WHERE voted_at > DATE_SUB(NOW(), INTERVAL %d DAY)
                     GROUP BY dog_id
                 ) hp ON d.id = hp.dog_id
                 WHERE d.status = 'active'
                 ORDER BY hot_points DESC, d.total_points DESC
                 LIMIT %d",
                $days,
                $limit
            )
        );

        // Add rank position and process fields
        $position = 1;
        foreach ( $dogs as &$dog ) {
            $dog->rank = $position++;
            $this->process_dog_fields( $dog );
        }

        $settings = get_option( 'pawstars_settings', array() );
        $cache_time = isset( $settings['leaderboard_cache_time'] ) ? $settings['leaderboard_cache_time'] : 300;
        set_transient( $cache_key, $dogs, $cache_time );

        return $dogs;
    }

    /**
     * Get all-time leaderboard
     *
     * @since  1.0.0
     * @param  int $limit Number of results
     * @return array
     */
    public function get_alltime_leaderboard( $limit = 10 ) {
        global $wpdb;

        $cache_key = 'pawstars_leaderboard_alltime_' . $limit;
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $dogs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->tables['dogs']}
                 WHERE status = 'active'
                 ORDER BY total_points DESC, created_at ASC
                 LIMIT %d",
                $limit
            )
        );

        // Add rank position and process fields
        $position = 1;
        foreach ( $dogs as &$dog ) {
            $dog->rank = $position++;
            $this->process_dog_fields( $dog );
        }

        $settings = get_option( 'pawstars_settings', array() );
        $cache_time = isset( $settings['leaderboard_cache_time'] ) ? $settings['leaderboard_cache_time'] : 300;
        set_transient( $cache_key, $dogs, $cache_time );

        return $dogs;
    }

    /**
     * Get breed leaderboard
     *
     * @since  1.0.0
     * @param  int $breed_id Breed ID
     * @param  int $limit    Number of results
     * @return array
     */
    public function get_breed_leaderboard( $breed_id, $limit = 10 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->tables['dogs']}
                 WHERE status = 'active' AND breed_id = %d
                 ORDER BY total_points DESC
                 LIMIT %d",
                $breed_id,
                $limit
            )
        );
    }

    /**
     * Get provincia leaderboard
     *
     * @since  1.0.0
     * @param  string $provincia Provincia code
     * @param  int    $limit     Number of results
     * @return array
     */
    public function get_provincia_leaderboard( $provincia, $limit = 10 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->tables['dogs']}
                 WHERE status = 'active' AND provincia = %s
                 ORDER BY total_points DESC
                 LIMIT %d",
                strtoupper( $provincia ),
                $limit
            )
        );
    }

    /**
     * Get dog rank position
     *
     * @since  1.0.0
     * @param  int    $dog_id Dog ID
     * @param  string $type   Rank type (hot, alltime)
     * @return int|null
     */
    public function get_dog_rank( $dog_id, $type = 'alltime' ) {
        global $wpdb;

        $dog = $this->get_dog( $dog_id );
        if ( ! $dog || $dog->status !== 'active' ) {
            return null;
        }

        if ( $type === 'hot' ) {
            $settings = get_option( 'pawstars_settings', array() );
            $days = isset( $settings['leaderboard_hot_days'] ) ? $settings['leaderboard_hot_days'] : 7;

            $hot_points = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COALESCE(SUM(points_value), 0)
                     FROM {$this->tables['votes']}
                     WHERE dog_id = %d AND voted_at > DATE_SUB(NOW(), INTERVAL %d DAY)",
                    $dog_id,
                    $days
                )
            );

            $rank = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) + 1
                     FROM {$this->tables['dogs']} d
                     LEFT JOIN (
                         SELECT dog_id, COALESCE(SUM(points_value), 0) as hp
                         FROM {$this->tables['votes']}
                         WHERE voted_at > DATE_SUB(NOW(), INTERVAL %d DAY)
                         GROUP BY dog_id
                     ) v ON d.id = v.dog_id
                     WHERE d.status = 'active' AND COALESCE(v.hp, 0) > %d",
                    $days,
                    $hot_points
                )
            );
        } else {
            $rank = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) + 1 FROM {$this->tables['dogs']}
                     WHERE status = 'active' AND total_points > %d",
                    $dog->total_points
                )
            );
        }

        return (int) $rank;
    }

    // =========================================================================
    // STATS
    // =========================================================================

    /**
     * Get global stats
     *
     * @since  1.0.0
     * @return array
     */
    public function get_global_stats() {
        global $wpdb;

        $cache_key = 'pawstars_global_stats';
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $stats = array(
            'total_dogs'   => $this->count_dogs( array( 'status' => 'active' ) ),
            'pending_dogs' => $this->count_dogs( array( 'status' => 'pending' ) ),
            'total_votes'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->tables['votes']}" ),
            'total_points' => (int) $wpdb->get_var( "SELECT COALESCE(SUM(points_value), 0) FROM {$this->tables['votes']}" ),
            'total_users'  => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$this->tables['dogs']}" ),
            'total_voters' => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT voter_user_id) FROM {$this->tables['votes']}" ),
        );

        set_transient( $cache_key, $stats, 300 );

        return $stats;
    }

    /**
     * Record daily stats
     *
     * @since 1.0.0
     */
    public function record_daily_stats() {
        global $wpdb;

        $today = current_time( 'Y-m-d' );

        $stats = array(
            'total_dogs'   => $this->count_dogs( array( 'status' => 'active' ) ),
            'total_votes'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->tables['votes']}" ),
            'new_dogs'     => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->tables['dogs']} WHERE DATE(created_at) = %s",
                    $today
                )
            ),
            'new_votes'    => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->tables['votes']} WHERE DATE(voted_at) = %s",
                    $today
                )
            ),
            'active_users' => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(DISTINCT voter_user_id) FROM {$this->tables['votes']} WHERE DATE(voted_at) = %s",
                    $today
                )
            ),
        );

        $wpdb->replace(
            $this->tables['stats'],
            array_merge( array( 'stat_date' => $today ), $stats ),
            array( '%s', '%d', '%d', '%d', '%d', '%d' )
        );
    }
}
