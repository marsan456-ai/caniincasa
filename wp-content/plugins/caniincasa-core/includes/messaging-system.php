<?php
/**
 * Internal Messaging System
 * Private messaging between users
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create messages table on plugin activation
 */
function caniincasa_create_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) unsigned NOT NULL,
        recipient_id bigint(20) unsigned NOT NULL,
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        related_post_id bigint(20) unsigned DEFAULT NULL,
        related_post_type varchar(50) DEFAULT NULL,
        is_read tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        read_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY sender_id (sender_id),
        KEY recipient_id (recipient_id),
        KEY is_read (is_read),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( CANIINCASA_CORE_FILE, 'caniincasa_create_messages_table' );

/**
 * Ensure messages table exists on init
 * This runs on every page load but only creates table if it doesn't exist
 */
function caniincasa_ensure_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Check if table exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        caniincasa_create_messages_table();
    }
}
add_action( 'init', 'caniincasa_ensure_messages_table' );

/**
 * Send a message
 *
 * @param int    $sender_id Sender user ID
 * @param int    $recipient_id Recipient user ID
 * @param string $subject Message subject
 * @param string $message Message content
 * @param int    $related_post_id Optional related post ID
 * @param string $related_post_type Optional related post type
 * @return int|false Message ID on success, false on failure
 */
function caniincasa_send_message( $sender_id, $recipient_id, $subject, $message, $related_post_id = null, $related_post_type = null ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Validation
    if ( ! $sender_id || ! $recipient_id || ! $subject || ! $message ) {
        return false;
    }

    // Insert message
    $result = $wpdb->insert(
        $table_name,
        array(
            'sender_id'         => $sender_id,
            'recipient_id'      => $recipient_id,
            'subject'           => sanitize_text_field( $subject ),
            'message'           => wp_kses_post( $message ),
            'related_post_id'   => $related_post_id,
            'related_post_type' => $related_post_type,
            'created_at'        => current_time( 'mysql' ),
        ),
        array( '%d', '%d', '%s', '%s', '%d', '%s', '%s' )
    );

    if ( ! $result ) {
        return false;
    }

    $message_id = $wpdb->insert_id;

    // Send email notification
    caniincasa_send_message_notification( $message_id );

    return $message_id;
}

/**
 * Get user messages
 *
 * @param int    $user_id User ID
 * @param string $type 'inbox' or 'sent'
 * @param int    $limit Number of messages to retrieve
 * @param int    $offset Offset for pagination
 * @return array Messages
 */
function caniincasa_get_user_messages( $user_id, $type = 'inbox', $limit = 20, $offset = 0 ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    $field = ( $type === 'inbox' ) ? 'recipient_id' : 'sender_id';

    $messages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name
            WHERE $field = %d
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        )
    );

    // Add user data to each message
    foreach ( $messages as &$message ) {
        $other_user_id = ( $type === 'inbox' ) ? $message->sender_id : $message->recipient_id;
        $message->other_user = get_userdata( $other_user_id );

        if ( $message->related_post_id ) {
            $message->related_post = get_post( $message->related_post_id );
        }
    }

    return $messages;
}

/**
 * Get message by ID
 *
 * @param int $message_id Message ID
 * @return object|null Message object or null
 */
function caniincasa_get_message( $message_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    $message = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $message_id
        )
    );

    if ( $message ) {
        $message->sender = get_userdata( $message->sender_id );
        $message->recipient = get_userdata( $message->recipient_id );

        if ( $message->related_post_id ) {
            $message->related_post = get_post( $message->related_post_id );
        }
    }

    return $message;
}

/**
 * Mark message as read
 *
 * @param int $message_id Message ID
 * @param int $user_id User ID (for security check)
 * @return bool Success
 */
function caniincasa_mark_message_read( $message_id, $user_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Verify user is recipient
    $message = caniincasa_get_message( $message_id );
    if ( ! $message || $message->recipient_id != $user_id ) {
        return false;
    }

    $result = $wpdb->update(
        $table_name,
        array(
            'is_read'  => 1,
            'read_at'  => current_time( 'mysql' ),
        ),
        array( 'id' => $message_id ),
        array( '%d', '%s' ),
        array( '%d' )
    );

    return $result !== false;
}

/**
 * Get unread message count
 *
 * @param int $user_id User ID
 * @return int Number of unread messages
 */
function caniincasa_get_unread_count( $user_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name
            WHERE recipient_id = %d AND is_read = 0",
            $user_id
        )
    );
}

/**
 * Delete message
 *
 * @param int $message_id Message ID
 * @param int $user_id User ID (for security)
 * @return bool Success
 */
function caniincasa_delete_message( $message_id, $user_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Verify user is sender or recipient
    $message = caniincasa_get_message( $message_id );
    if ( ! $message || ( $message->sender_id != $user_id && $message->recipient_id != $user_id ) ) {
        return false;
    }

    $result = $wpdb->delete(
        $table_name,
        array( 'id' => $message_id ),
        array( '%d' )
    );

    return $result !== false;
}

/**
 * Send email notification for new message
 *
 * @param int $message_id Message ID
 */
function caniincasa_send_message_notification( $message_id ) {
    $message = caniincasa_get_message( $message_id );

    if ( ! $message ) {
        return;
    }

    $recipient_email = $message->recipient->user_email;
    $sender_name = caniincasa_get_user_display_name( $message->sender_id );

    $subject = sprintf(
        __( '[%s] Nuovo messaggio da %s', 'caniincasa-core' ),
        get_bloginfo( 'name' ),
        $sender_name
    );

    $dashboard_url = home_url( '/dashboard/?section=messaggi' );

    $email_message = sprintf(
        __( "Ciao,\n\nHai ricevuto un nuovo messaggio da %s.\n\nOggetto: %s\n\nPer leggere il messaggio e rispondere, accedi alla tua dashboard:\n%s\n\nGrazie!", 'caniincasa-core' ),
        $sender_name,
        $message->subject,
        $dashboard_url
    );

    wp_mail( $recipient_email, $subject, $email_message );
}

/**
 * AJAX: Send message
 */
function caniincasa_ajax_send_message() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Devi essere loggato per inviare messaggi.', 'caniincasa-core' ) ) );
    }

    $recipient_id = isset( $_POST['recipient_id'] ) ? absint( $_POST['recipient_id'] ) : 0;
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
    $related_post_id = isset( $_POST['related_post_id'] ) ? absint( $_POST['related_post_id'] ) : null;
    $related_post_type = isset( $_POST['related_post_type'] ) ? sanitize_text_field( $_POST['related_post_type'] ) : null;

    if ( ! $recipient_id || ! $subject || ! $message ) {
        wp_send_json_error( array( 'message' => __( 'Tutti i campi sono obbligatori.', 'caniincasa-core' ) ) );
    }

    $sender_id = get_current_user_id();

    // Prevent sending to yourself
    if ( $sender_id === $recipient_id ) {
        wp_send_json_error( array( 'message' => __( 'Non puoi inviare messaggi a te stesso.', 'caniincasa-core' ) ) );
    }

    $message_id = caniincasa_send_message( $sender_id, $recipient_id, $subject, $message, $related_post_id, $related_post_type );

    if ( $message_id ) {
        wp_send_json_success( array(
            'message'    => __( 'Messaggio inviato con successo!', 'caniincasa-core' ),
            'message_id' => $message_id,
        ) );
    } else {
        global $wpdb;
        $db_error = $wpdb->last_error ? $wpdb->last_error : __( 'Errore sconosciuto', 'caniincasa-core' );

        wp_send_json_error( array(
            'message' => __( 'Errore durante l\'invio del messaggio.', 'caniincasa-core' ),
            'debug' => $db_error
        ) );
    }
}
add_action( 'wp_ajax_send_message', 'caniincasa_ajax_send_message' );

/**
 * AJAX: Mark message as read
 */
function caniincasa_ajax_mark_read() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Non autorizzato.', 'caniincasa-core' ) ) );
    }

    $message_id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

    if ( ! $message_id ) {
        wp_send_json_error( array( 'message' => __( 'ID messaggio non valido.', 'caniincasa-core' ) ) );
    }

    $result = caniincasa_mark_message_read( $message_id, get_current_user_id() );

    if ( $result ) {
        wp_send_json_success( array( 'message' => __( 'Messaggio segnato come letto.', 'caniincasa-core' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Errore durante l\'aggiornamento.', 'caniincasa-core' ) ) );
    }
}
add_action( 'wp_ajax_mark_message_read', 'caniincasa_ajax_mark_read' );

/**
 * AJAX: Delete message
 */
function caniincasa_ajax_delete_message() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Non autorizzato.', 'caniincasa-core' ) ) );
    }

    $message_id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

    if ( ! $message_id ) {
        wp_send_json_error( array( 'message' => __( 'ID messaggio non valido.', 'caniincasa-core' ) ) );
    }

    $result = caniincasa_delete_message( $message_id, get_current_user_id() );

    if ( $result ) {
        wp_send_json_success( array( 'message' => __( 'Messaggio eliminato.', 'caniincasa-core' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Errore durante l\'eliminazione.', 'caniincasa-core' ) ) );
    }
}
add_action( 'wp_ajax_delete_message', 'caniincasa_ajax_delete_message' );

/**
 * AJAX: Get unread count
 */
function caniincasa_ajax_get_unread_count() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Non autorizzato.', 'caniincasa-core' ) ) );
    }

    $count = caniincasa_get_unread_count( get_current_user_id() );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_get_unread_count', 'caniincasa_ajax_get_unread_count' );
