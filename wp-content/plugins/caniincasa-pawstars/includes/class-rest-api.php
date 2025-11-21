<?php
/**
 * REST API
 *
 * Handles all REST API endpoints for the plugin.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST API Class
 *
 * @since 1.0.0
 */
class Pawstars_Rest_API {

    /**
     * Plugin instance
     *
     * @var Caniincasa_Pawstars
     */
    private $plugin;

    /**
     * API namespace
     *
     * @var string
     */
    private $namespace = 'pawstars/v1';

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Caniincasa_Pawstars $plugin Plugin instance
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Register REST routes
     *
     * @since 1.0.0
     */
    public function register_routes() {
        // Dogs endpoints
        register_rest_route( $this->namespace, '/dogs', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dogs' ),
                'permission_callback' => '__return_true',
                'args'                => $this->get_dogs_args(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_dog' ),
                'permission_callback' => array( $this, 'create_dog_permission' ),
                'args'                => $this->create_dog_args(),
            ),
        ) );

        register_rest_route( $this->namespace, '/dogs/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dog' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'validate_callback' => function( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_dog' ),
                'permission_callback' => array( $this, 'update_dog_permission' ),
                'args'                => $this->update_dog_args(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_dog' ),
                'permission_callback' => array( $this, 'delete_dog_permission' ),
            ),
        ) );

        // My dogs
        register_rest_route( $this->namespace, '/my-dogs', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_my_dogs' ),
            'permission_callback' => array( $this, 'logged_in_permission' ),
        ) );

        // Vote endpoint
        register_rest_route( $this->namespace, '/vote', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'add_vote' ),
            'permission_callback' => array( $this, 'logged_in_permission' ),
            'args'                => array(
                'dog_id' => array(
                    'required'          => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ),
                'reaction' => array(
                    'required'          => true,
                    'validate_callback' => function( $param ) {
                        return in_array( $param, array( 'love', 'adorable', 'star', 'funny', 'aww' ) );
                    },
                ),
            ),
        ) );

        // Leaderboard endpoints
        register_rest_route( $this->namespace, '/leaderboard/(?P<type>[a-z]+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_leaderboard' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'type' => array(
                    'required'          => true,
                    'validate_callback' => function( $param ) {
                        return in_array( $param, array( 'hot', 'alltime', 'breed', 'provincia' ) );
                    },
                ),
                'filter' => array(
                    'required' => false,
                ),
                'limit' => array(
                    'default'           => 10,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param > 0 && $param <= 100;
                    },
                ),
            ),
        ) );

        // Breeds list
        register_rest_route( $this->namespace, '/breeds', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_breeds' ),
            'permission_callback' => '__return_true',
        ) );

        // Province list
        register_rest_route( $this->namespace, '/province', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_province' ),
            'permission_callback' => '__return_true',
        ) );

        // Stats
        register_rest_route( $this->namespace, '/stats', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_stats' ),
            'permission_callback' => '__return_true',
        ) );
    }

    // =========================================================================
    // PERMISSION CALLBACKS
    // =========================================================================

    /**
     * Check if user is logged in
     *
     * @since  1.0.0
     * @return bool
     */
    public function logged_in_permission() {
        return is_user_logged_in();
    }

    /**
     * Check create dog permission
     *
     * @since  1.0.0
     * @return bool
     */
    public function create_dog_permission() {
        return is_user_logged_in();
    }

    /**
     * Check update dog permission
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return bool
     */
    public function update_dog_permission( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $dog_id = $request->get_param( 'id' );
        $dog = $this->plugin->database->get_dog( $dog_id );

        if ( ! $dog ) {
            return false;
        }

        return $dog->user_id == get_current_user_id() || current_user_can( 'manage_options' );
    }

    /**
     * Check delete dog permission
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return bool
     */
    public function delete_dog_permission( $request ) {
        return $this->update_dog_permission( $request );
    }

    // =========================================================================
    // ENDPOINT CALLBACKS
    // =========================================================================

    /**
     * Get dogs
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_dogs( $request ) {
        $args = array(
            'status'    => 'active',
            'breed_id'  => $request->get_param( 'breed' ),
            'provincia' => $request->get_param( 'provincia' ),
            'search'    => $request->get_param( 'search' ),
            'orderby'   => $request->get_param( 'orderby' ) ?: 'created_at',
            'order'     => $request->get_param( 'order' ) ?: 'DESC',
            'limit'     => $request->get_param( 'per_page' ) ?: 12,
            'offset'    => ( ( $request->get_param( 'page' ) ?: 1 ) - 1 ) * ( $request->get_param( 'per_page' ) ?: 12 ),
            'exclude'   => $request->get_param( 'exclude' ) ? explode( ',', $request->get_param( 'exclude' ) ) : array(),
        );

        $dogs = $this->plugin->database->get_dogs( $args );
        $total = $this->plugin->database->count_dogs( array(
            'status'    => 'active',
            'breed_id'  => $args['breed_id'],
            'provincia' => $args['provincia'],
        ) );

        $formatted = array_map( array( $this, 'format_dog' ), $dogs );

        $response = rest_ensure_response( $formatted );
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', ceil( $total / $args['limit'] ) );

        return $response;
    }

    /**
     * Get single dog
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function get_dog( $request ) {
        $dog_id = $request->get_param( 'id' );
        $dog = $this->plugin->dog_profile->get( $dog_id );

        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ), array( 'status' => 404 ) );
        }

        // Check if visible (active or own dog)
        if ( $dog->status !== 'active' ) {
            if ( ! is_user_logged_in() || $dog->user_id != get_current_user_id() ) {
                return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ), array( 'status' => 404 ) );
            }
        }

        return rest_ensure_response( $this->format_dog_full( $dog ) );
    }

    /**
     * Create dog
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function create_dog( $request ) {
        $data = array(
            'user_id'           => get_current_user_id(),
            'name'              => $request->get_param( 'name' ),
            'birth_date'        => $request->get_param( 'birth_date' ),
            'breed_id'          => $request->get_param( 'breed_id' ),
            'provincia'         => $request->get_param( 'provincia' ),
            'bio'               => $request->get_param( 'bio' ),
            'featured_image_id' => $request->get_param( 'featured_image_id' ),
        );

        $result = $this->plugin->dog_profile->create( $data );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
        }

        $dog = $this->plugin->dog_profile->get( $result );

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Profilo creato! In attesa di approvazione.', 'pawstars' ),
            'dog'     => $this->format_dog_full( $dog ),
        ) );
    }

    /**
     * Update dog
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function update_dog( $request ) {
        $dog_id = $request->get_param( 'id' );

        $data = array();
        $fields = array( 'name', 'birth_date', 'breed_id', 'provincia', 'bio', 'featured_image_id' );

        foreach ( $fields as $field ) {
            $value = $request->get_param( $field );
            if ( $value !== null ) {
                $data[ $field ] = $value;
            }
        }

        $result = $this->plugin->dog_profile->update( $dog_id, $data );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
        }

        $dog = $this->plugin->dog_profile->get( $dog_id );

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Profilo aggiornato!', 'pawstars' ),
            'dog'     => $this->format_dog_full( $dog ),
        ) );
    }

    /**
     * Delete dog
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function delete_dog( $request ) {
        $dog_id = $request->get_param( 'id' );

        $result = $this->plugin->dog_profile->delete( $dog_id );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Profilo eliminato', 'pawstars' ),
        ) );
    }

    /**
     * Get current user's dogs
     *
     * @since  1.0.0
     * @return WP_REST_Response
     */
    public function get_my_dogs() {
        $dogs = $this->plugin->dog_profile->get_user_dogs();
        $formatted = array_map( array( $this, 'format_dog_full' ), $dogs );

        return rest_ensure_response( $formatted );
    }

    /**
     * Add vote
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function add_vote( $request ) {
        $dog_id = $request->get_param( 'dog_id' );
        $reaction = $request->get_param( 'reaction' );
        $user_id = get_current_user_id();

        // Check if can vote
        $can_vote = $this->plugin->voting->can_vote( $dog_id, $user_id, $reaction );
        if ( is_wp_error( $can_vote ) ) {
            return new WP_Error( $can_vote->get_error_code(), $can_vote->get_error_message(), array( 'status' => 400 ) );
        }

        // Add vote
        $result = $this->plugin->voting->vote( $dog_id, $user_id, $reaction );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
        }

        // Get updated data
        $dog = $this->plugin->database->get_dog( $dog_id );
        $vote_stats = $this->plugin->database->get_dog_vote_stats( $dog_id );

        return rest_ensure_response( array(
            'success'      => true,
            'message'      => __( 'Voto registrato!', 'pawstars' ),
            'total_points' => $dog->total_points,
            'vote_stats'   => $vote_stats,
            'user_votes'   => $this->plugin->voting->get_user_votes( $dog_id, $user_id ),
            'stars_left'   => $this->plugin->voting->get_remaining_stars( $user_id ),
        ) );
    }

    /**
     * Get leaderboard
     *
     * @since  1.0.0
     * @param  WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_leaderboard( $request ) {
        $type = $request->get_param( 'type' );
        $filter = $request->get_param( 'filter' );
        $limit = $request->get_param( 'limit' ) ?: 10;

        $dogs = $this->plugin->leaderboard->get( $type, $limit, $filter );
        $formatted = array_map( array( $this, 'format_dog' ), $dogs );

        return rest_ensure_response( array(
            'type'  => $type,
            'dogs'  => $formatted,
            'total' => count( $formatted ),
        ) );
    }

    /**
     * Get breeds
     *
     * @since  1.0.0
     * @return WP_REST_Response
     */
    public function get_breeds() {
        $breeds = Pawstars_Integrations::get_breeds();
        return rest_ensure_response( $breeds );
    }

    /**
     * Get province
     *
     * @since  1.0.0
     * @return WP_REST_Response
     */
    public function get_province() {
        $province = Pawstars_Integrations::get_province();
        $result = array();

        foreach ( $province as $code => $name ) {
            $result[] = array(
                'code' => $code,
                'name' => $name,
            );
        }

        return rest_ensure_response( $result );
    }

    /**
     * Get stats
     *
     * @since  1.0.0
     * @return WP_REST_Response
     */
    public function get_stats() {
        $stats = $this->plugin->database->get_global_stats();
        return rest_ensure_response( $stats );
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Format dog for API response
     *
     * @since  1.0.0
     * @param  object $dog Dog object
     * @return array
     */
    private function format_dog( $dog ) {
        return array(
            'id'           => (int) $dog->id,
            'name'         => $dog->name,
            'breed_id'     => $dog->breed_id ? (int) $dog->breed_id : null,
            'breed_name'   => $dog->breed_name ?: '',
            'provincia'    => $dog->provincia,
            'bio'          => $dog->bio,
            'image_url'    => $dog->image_url,
            'total_points' => (int) $dog->total_points,
            'rank'         => isset( $dog->rank ) ? (int) $dog->rank : null,
            'hot_points'   => isset( $dog->hot_points ) ? (int) $dog->hot_points : null,
            'created_at'   => $dog->created_at,
        );
    }

    /**
     * Format dog with full data for API response
     *
     * @since  1.0.0
     * @param  object $dog Dog object
     * @return array
     */
    private function format_dog_full( $dog ) {
        $base = $this->format_dog( $dog );

        return array_merge( $base, array(
            'user_id'       => (int) $dog->user_id,
            'author_name'   => isset( $dog->author_name ) ? $dog->author_name : get_the_author_meta( 'display_name', $dog->user_id ),
            'birth_date'    => $dog->birth_date,
            'age_display'   => isset( $dog->age_display ) ? $dog->age_display : '',
            'gallery_urls'  => isset( $dog->gallery_urls ) ? $dog->gallery_urls : array(),
            'vote_stats'    => isset( $dog->vote_stats ) ? $dog->vote_stats : array(),
            'achievements'  => isset( $dog->achievements ) ? $dog->achievements : array(),
            'rank_hot'      => isset( $dog->rank_hot ) ? $dog->rank_hot : null,
            'rank_alltime'  => isset( $dog->rank_alltime ) ? $dog->rank_alltime : null,
            'status'        => $dog->status,
            'is_featured'   => (bool) $dog->is_featured,
        ) );
    }

    /**
     * Get dogs endpoint args
     *
     * @since  1.0.0
     * @return array
     */
    private function get_dogs_args() {
        return array(
            'breed' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return empty( $param ) || is_numeric( $param );
                },
            ),
            'provincia' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'search' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'orderby' => array(
                'default'           => 'created_at',
                'validate_callback' => function( $param ) {
                    return in_array( $param, array( 'created_at', 'total_points', 'name' ) );
                },
            ),
            'order' => array(
                'default'           => 'DESC',
                'validate_callback' => function( $param ) {
                    return in_array( strtoupper( $param ), array( 'ASC', 'DESC' ) );
                },
            ),
            'page' => array(
                'default'           => 1,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
            ),
            'per_page' => array(
                'default'           => 12,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0 && $param <= 50;
                },
            ),
            'exclude' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Create dog endpoint args
     *
     * @since  1.0.0
     * @return array
     */
    private function create_dog_args() {
        return array(
            'name' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function( $param ) {
                    return ! empty( $param ) && strlen( $param ) <= 100;
                },
            ),
            'birth_date' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'breed_id' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return empty( $param ) || is_numeric( $param );
                },
            ),
            'provincia' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'bio' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
            'featured_image_id' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return empty( $param ) || is_numeric( $param );
                },
            ),
        );
    }

    /**
     * Update dog endpoint args
     *
     * @since  1.0.0
     * @return array
     */
    private function update_dog_args() {
        $args = $this->create_dog_args();
        $args['name']['required'] = false;
        return $args;
    }
}
