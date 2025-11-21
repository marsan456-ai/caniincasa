<?php
/**
 * Moderation Handler
 *
 * Additional moderation functionality.
 *
 * @package    Pawstars
 * @subpackage Pawstars/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Moderation Class
 *
 * @since 1.0.0
 */
class Pawstars_Moderation {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_pawstars_moderate_dog', array( $this, 'ajax_moderate' ) );
        add_action( 'wp_ajax_pawstars_bulk_moderate', array( $this, 'ajax_bulk_moderate' ) );
    }

    /**
     * AJAX: Moderate single dog
     *
     * @since 1.0.0
     */
    public function ajax_moderate() {
        check_ajax_referer( 'pawstars_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Non autorizzato', 'pawstars' ) ) );
        }

        $dog_id = isset( $_POST['dog_id'] ) ? absint( $_POST['dog_id'] ) : 0;
        $action = isset( $_POST['mod_action'] ) ? sanitize_text_field( $_POST['mod_action'] ) : '';

        if ( ! $dog_id || ! $action ) {
            wp_send_json_error( array( 'message' => __( 'Dati mancanti', 'pawstars' ) ) );
        }

        $db = pawstars()->database;

        switch ( $action ) {
            case 'approve':
                $result = $db->update_dog( $dog_id, array( 'status' => 'active' ) );
                $message = __( 'Profilo approvato', 'pawstars' );
                break;

            case 'reject':
                $result = $db->update_dog( $dog_id, array( 'status' => 'rejected' ) );
                $message = __( 'Profilo rifiutato', 'pawstars' );
                break;

            case 'suspend':
                $result = $db->update_dog( $dog_id, array( 'status' => 'suspended' ) );
                $message = __( 'Profilo sospeso', 'pawstars' );
                break;

            case 'delete':
                $result = $db->delete_dog( $dog_id );
                $message = __( 'Profilo eliminato', 'pawstars' );
                break;

            default:
                wp_send_json_error( array( 'message' => __( 'Azione non valida', 'pawstars' ) ) );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => $message ) );
    }

    /**
     * AJAX: Bulk moderate
     *
     * @since 1.0.0
     */
    public function ajax_bulk_moderate() {
        check_ajax_referer( 'pawstars_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Non autorizzato', 'pawstars' ) ) );
        }

        $dog_ids = isset( $_POST['dog_ids'] ) ? array_map( 'absint', (array) $_POST['dog_ids'] ) : array();
        $action = isset( $_POST['mod_action'] ) ? sanitize_text_field( $_POST['mod_action'] ) : '';

        if ( empty( $dog_ids ) || ! $action ) {
            wp_send_json_error( array( 'message' => __( 'Dati mancanti', 'pawstars' ) ) );
        }

        $db = pawstars()->database;
        $count = 0;

        foreach ( $dog_ids as $dog_id ) {
            switch ( $action ) {
                case 'approve':
                    $result = $db->update_dog( $dog_id, array( 'status' => 'active' ) );
                    break;
                case 'reject':
                    $result = $db->update_dog( $dog_id, array( 'status' => 'rejected' ) );
                    break;
                case 'delete':
                    $result = $db->delete_dog( $dog_id );
                    break;
            }

            if ( ! is_wp_error( $result ) ) {
                $count++;
            }
        }

        wp_send_json_success( array(
            'message' => sprintf( __( '%d profili aggiornati', 'pawstars' ), $count ),
            'count'   => $count,
        ) );
    }
}

// Initialize
new Pawstars_Moderation();
