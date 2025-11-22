<?php
/**
 * Dog Profile Handler
 *
 * Manages dog profile operations and media handling.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dog Profile Class
 *
 * @since 1.0.0
 */
class Pawstars_Dog_Profile {

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
        // AJAX handlers
        add_action( 'wp_ajax_pawstars_create_dog', array( $this, 'ajax_create_dog' ) );
        add_action( 'wp_ajax_pawstars_update_dog', array( $this, 'ajax_update_dog' ) );
        add_action( 'wp_ajax_pawstars_delete_dog', array( $this, 'ajax_delete_dog' ) );
        add_action( 'wp_ajax_pawstars_upload_photo', array( $this, 'ajax_upload_photo' ) );
    }

    /**
     * Create dog profile
     *
     * @since  1.0.0
     * @param  array $data Dog data
     * @return int|WP_Error
     */
    public function create( $data ) {
        // Validate user
        if ( empty( $data['user_id'] ) ) {
            $data['user_id'] = get_current_user_id();
        }

        if ( ! $data['user_id'] ) {
            return new WP_Error( 'not_logged_in', __( 'Devi essere loggato', 'pawstars' ) );
        }

        // Create in database
        $result = $this->db->create_dog( $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Send notification to admin
        $this->notify_admin_new_dog( $result );

        return $result;
    }

    /**
     * Update dog profile
     *
     * @since  1.0.0
     * @param  int   $dog_id Dog ID
     * @param  array $data   Update data
     * @return bool|WP_Error
     */
    public function update( $dog_id, $data ) {
        // Check ownership
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        $current_user = get_current_user_id();
        if ( $dog->user_id != $current_user && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'not_owner', __( 'Non sei il proprietario', 'pawstars' ) );
        }

        return $this->db->update_dog( $dog_id, $data );
    }

    /**
     * Delete dog profile
     *
     * @since  1.0.0
     * @param  int $dog_id Dog ID
     * @return bool|WP_Error
     */
    public function delete( $dog_id ) {
        // Check ownership
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        $current_user = get_current_user_id();
        if ( $dog->user_id != $current_user && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'not_owner', __( 'Non sei il proprietario', 'pawstars' ) );
        }

        return $this->db->delete_dog( $dog_id );
    }

    /**
     * Get dog with full data
     *
     * @since  1.0.0
     * @param  int $dog_id Dog ID
     * @return object|null
     */
    public function get( $dog_id ) {
        $dog = $this->db->get_dog( $dog_id );

        if ( ! $dog ) {
            return null;
        }

        // Add vote stats
        $dog->vote_stats = $this->db->get_dog_vote_stats( $dog_id );

        // Add achievements
        $dog->achievements = $this->db->get_achievements( 'dog', $dog_id );

        // Add ranks
        $dog->rank_hot = $this->db->get_dog_rank( $dog_id, 'hot' );
        $dog->rank_alltime = $this->db->get_dog_rank( $dog_id, 'alltime' );

        // Add age
        if ( $dog->birth_date ) {
            $birth = new DateTime( $dog->birth_date );
            $now = new DateTime();
            $age = $now->diff( $birth );
            $dog->age_years = $age->y;
            $dog->age_months = $age->m;
            $dog->age_display = $this->format_age( $age->y, $age->m );
        } else {
            $dog->age_years = null;
            $dog->age_months = null;
            $dog->age_display = '';
        }

        // Add gallery URLs
        $dog->gallery_urls = array();
        if ( ! empty( $dog->gallery_ids ) ) {
            foreach ( $dog->gallery_ids as $image_id ) {
                $url = wp_get_attachment_url( $image_id );
                if ( $url ) {
                    $dog->gallery_urls[] = array(
                        'id'        => $image_id,
                        'url'       => $url,
                        'thumbnail' => wp_get_attachment_image_url( $image_id, 'thumbnail' ),
                        'medium'    => wp_get_attachment_image_url( $image_id, 'medium' ),
                        'large'     => wp_get_attachment_image_url( $image_id, 'large' ),
                    );
                }
            }
        }

        return $dog;
    }

    /**
     * Format age display
     *
     * @since  1.0.0
     * @param  int $years  Years
     * @param  int $months Months
     * @return string
     */
    private function format_age( $years, $months ) {
        $parts = array();

        if ( $years > 0 ) {
            $parts[] = sprintf(
                _n( '%d anno', '%d anni', $years, 'pawstars' ),
                $years
            );
        }

        if ( $months > 0 && $years < 3 ) {
            $parts[] = sprintf(
                _n( '%d mese', '%d mesi', $months, 'pawstars' ),
                $months
            );
        }

        return implode( ' e ', $parts );
    }

    /**
     * Get user's dogs
     *
     * @since  1.0.0
     * @param  int $user_id User ID
     * @return array
     */
    public function get_user_dogs( $user_id = null ) {
        if ( empty( $user_id ) || $user_id <= 0 ) {
            $user_id = get_current_user_id();
        }

        // Prevent information disclosure - require valid user
        if ( empty( $user_id ) || $user_id <= 0 ) {
            return array();
        }

        return $this->db->get_dogs( array(
            'user_id' => absint( $user_id ),
            'status'  => null, // All statuses
            'orderby' => 'created_at',
            'order'   => 'DESC',
        ) );
    }

    /**
     * Handle photo upload
     *
     * @since  1.0.0
     * @param  array $file    $_FILES array item
     * @param  int   $dog_id  Dog ID (optional, for association)
     * @return int|WP_Error Attachment ID or error
     */
    public function upload_photo( $file, $dog_id = null ) {
        // Validate user
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'not_logged_in', __( 'Devi essere loggato', 'pawstars' ) );
        }

        // Check file type
        $allowed_types = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );
        if ( ! in_array( $file['type'], $allowed_types ) ) {
            return new WP_Error( 'invalid_type', __( 'Formato non supportato', 'pawstars' ) );
        }

        // Check file size
        $settings = get_option( 'pawstars_settings', array() );
        $max_size = isset( $settings['max_photo_size_mb'] ) ? $settings['max_photo_size_mb'] : 5;
        $max_bytes = $max_size * 1024 * 1024;

        if ( $file['size'] > $max_bytes ) {
            return new WP_Error( 'too_large', sprintf( __( 'File troppo grande (max %dMB)', 'pawstars' ), $max_size ) );
        }

        // Handle upload
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Set up file array for wp_handle_upload
        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'] );
        }

        // Create attachment
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name( $file['name'] ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $attach_id ) ) {
            return $attach_id;
        }

        // Generate metadata
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }

    /**
     * Add photo to dog gallery
     *
     * @since  1.0.0
     * @param  int $dog_id   Dog ID
     * @param  int $image_id Image attachment ID
     * @return bool|WP_Error
     */
    public function add_gallery_photo( $dog_id, $image_id ) {
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        // Check ownership
        if ( $dog->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'not_owner', __( 'Non sei il proprietario', 'pawstars' ) );
        }

        // Check gallery limit
        $settings = get_option( 'pawstars_settings', array() );
        $max_photos = isset( $settings['max_photos_per_dog'] ) ? $settings['max_photos_per_dog'] : 10;

        $gallery = $dog->gallery_ids ?: array();
        if ( count( $gallery ) >= $max_photos ) {
            return new WP_Error( 'limit_reached', sprintf( __( 'Massimo %d foto per cane', 'pawstars' ), $max_photos ) );
        }

        // Add to gallery
        $gallery[] = $image_id;

        return $this->db->update_dog( $dog_id, array( 'gallery_ids' => $gallery ) );
    }

    /**
     * Remove photo from gallery
     *
     * @since  1.0.0
     * @param  int $dog_id   Dog ID
     * @param  int $image_id Image attachment ID
     * @return bool|WP_Error
     */
    public function remove_gallery_photo( $dog_id, $image_id ) {
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return new WP_Error( 'not_found', __( 'Cane non trovato', 'pawstars' ) );
        }

        // Check ownership
        if ( $dog->user_id != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'not_owner', __( 'Non sei il proprietario', 'pawstars' ) );
        }

        $gallery = $dog->gallery_ids ?: array();
        $gallery = array_filter( $gallery, function( $id ) use ( $image_id ) {
            return $id != $image_id;
        } );

        return $this->db->update_dog( $dog_id, array( 'gallery_ids' => array_values( $gallery ) ) );
    }

    /**
     * Notify admin of new dog
     *
     * @since 1.0.0
     * @param int $dog_id Dog ID
     */
    private function notify_admin_new_dog( $dog_id ) {
        $dog = $this->db->get_dog( $dog_id );
        if ( ! $dog ) {
            return;
        }

        $settings = get_option( 'pawstars_settings', array() );
        $admin_email = isset( $settings['notification_email'] ) ? $settings['notification_email'] : get_option( 'admin_email' );

        $subject = sprintf( __( '[Paw Stars] Nuovo cane in attesa: %s', 'pawstars' ), $dog->name );

        $message = sprintf(
            __( "Un nuovo profilo cane è stato creato e richiede approvazione.\n\nNome: %s\nProprietario: %s\n\nVai alla moderazione: %s", 'pawstars' ),
            $dog->name,
            get_the_author_meta( 'display_name', $dog->user_id ),
            admin_url( 'admin.php?page=pawstars-moderation' )
        );

        wp_mail( $admin_email, $subject, $message );
    }

    // =========================================================================
    // AJAX HANDLERS
    // =========================================================================

    /**
     * AJAX: Create dog
     *
     * @since 1.0.0
     */
    public function ajax_create_dog() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Devi essere loggato', 'pawstars' ) ) );
        }

        $data = array(
            'user_id'           => get_current_user_id(),
            'name'              => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
            'birth_date'        => isset( $_POST['birth_date'] ) ? sanitize_text_field( $_POST['birth_date'] ) : null,
            'breed_id'          => isset( $_POST['breed_id'] ) ? absint( $_POST['breed_id'] ) : null,
            'provincia'         => isset( $_POST['provincia'] ) ? sanitize_text_field( $_POST['provincia'] ) : null,
            'bio'               => isset( $_POST['bio'] ) ? sanitize_textarea_field( $_POST['bio'] ) : '',
            'featured_image_id' => isset( $_POST['featured_image_id'] ) ? absint( $_POST['featured_image_id'] ) : null,
        );

        $result = $this->create( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Profilo creato! In attesa di approvazione.', 'pawstars' ),
            'dog_id'  => $result,
        ) );
    }

    /**
     * AJAX: Update dog
     *
     * @since 1.0.0
     */
    public function ajax_update_dog() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Devi essere loggato', 'pawstars' ) ) );
        }

        $dog_id = isset( $_POST['dog_id'] ) ? absint( $_POST['dog_id'] ) : 0;
        if ( ! $dog_id ) {
            wp_send_json_error( array( 'message' => __( 'ID cane mancante', 'pawstars' ) ) );
        }

        $data = array();
        $allowed = array( 'name', 'birth_date', 'breed_id', 'provincia', 'bio', 'featured_image_id' );

        foreach ( $allowed as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                if ( $field === 'bio' ) {
                    $data[ $field ] = sanitize_textarea_field( $_POST[ $field ] );
                } elseif ( in_array( $field, array( 'breed_id', 'featured_image_id' ) ) ) {
                    $data[ $field ] = absint( $_POST[ $field ] );
                } else {
                    $data[ $field ] = sanitize_text_field( $_POST[ $field ] );
                }
            }
        }

        $result = $this->update( $dog_id, $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Profilo aggiornato!', 'pawstars' ),
        ) );
    }

    /**
     * AJAX: Delete dog
     *
     * @since 1.0.0
     */
    public function ajax_delete_dog() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Devi essere loggato', 'pawstars' ) ) );
        }

        $dog_id = isset( $_POST['dog_id'] ) ? absint( $_POST['dog_id'] ) : 0;
        if ( ! $dog_id ) {
            wp_send_json_error( array( 'message' => __( 'ID cane mancante', 'pawstars' ) ) );
        }

        $result = $this->delete( $dog_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Profilo eliminato', 'pawstars' ),
        ) );
    }

    /**
     * AJAX: Upload photo
     *
     * @since 1.0.0
     */
    public function ajax_upload_photo() {
        check_ajax_referer( 'pawstars_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Devi essere loggato', 'pawstars' ) ) );
        }

        if ( empty( $_FILES['photo'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Nessun file caricato', 'pawstars' ) ) );
        }

        $result = $this->upload_photo( $_FILES['photo'] );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Add to gallery if dog_id provided
        $dog_id = isset( $_POST['dog_id'] ) ? absint( $_POST['dog_id'] ) : 0;
        if ( $dog_id ) {
            $gallery_result = $this->add_gallery_photo( $dog_id, $result );
            if ( is_wp_error( $gallery_result ) ) {
                wp_send_json_error( array( 'message' => $gallery_result->get_error_message() ) );
            }
        }

        wp_send_json_success( array(
            'message'   => __( 'Foto caricata!', 'pawstars' ),
            'image_id'  => $result,
            'url'       => wp_get_attachment_url( $result ),
            'thumbnail' => wp_get_attachment_image_url( $result, 'thumbnail' ),
        ) );
    }
}
