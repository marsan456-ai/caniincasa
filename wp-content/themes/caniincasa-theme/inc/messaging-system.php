<?php
/**
 * Messaging System
 * Complete private messaging system with replies and user blocking
 *
 * @package Caniincasa
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create messaging database tables
 */
function caniincasa_create_messaging_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Messages table
    $messages_table = $wpdb->prefix . 'caniincasa_messages';
    $messages_sql = "CREATE TABLE IF NOT EXISTS $messages_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) UNSIGNED NOT NULL,
        recipient_id bigint(20) UNSIGNED NOT NULL,
        parent_id bigint(20) UNSIGNED DEFAULT NULL,
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        related_post_id bigint(20) UNSIGNED DEFAULT NULL,
        related_post_type varchar(50) DEFAULT NULL,
        is_read tinyint(1) DEFAULT 0,
        sender_deleted tinyint(1) DEFAULT 0,
        recipient_deleted tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY sender_id (sender_id),
        KEY recipient_id (recipient_id),
        KEY parent_id (parent_id),
        KEY is_read (is_read),
        KEY created_at (created_at)
    ) $charset_collate;";

    // Blocked users table
    $blocked_table = $wpdb->prefix . 'caniincasa_blocked_users';
    $blocked_sql = "CREATE TABLE IF NOT EXISTS $blocked_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        blocked_user_id bigint(20) UNSIGNED NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_blocked (user_id, blocked_user_id),
        KEY user_id (user_id),
        KEY blocked_user_id (blocked_user_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $messages_sql );
    dbDelta( $blocked_sql );
}
add_action( 'after_setup_theme', 'caniincasa_create_messaging_tables' );

/**
 * Check if user has blocked another user
 */
function caniincasa_is_user_blocked( $user_id, $blocked_user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_blocked_users';

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND blocked_user_id = %d",
        $user_id,
        $blocked_user_id
    ) );

    return $count > 0;
}

/**
 * Check if messaging is allowed between two users
 */
function caniincasa_can_send_message( $sender_id, $recipient_id ) {
    // Check if recipient has blocked sender
    if ( caniincasa_is_user_blocked( $recipient_id, $sender_id ) ) {
        return false;
    }

    // Check if sender has blocked recipient
    if ( caniincasa_is_user_blocked( $sender_id, $recipient_id ) ) {
        return false;
    }

    return true;
}

/**
 * Get unread message count for user
 */
function caniincasa_get_unread_count( $user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table
        WHERE recipient_id = %d
        AND is_read = 0
        AND recipient_deleted = 0",
        $user_id
    ) );

    return (int) $count;
}

/**
 * Get messages for user
 *
 * @param int    $user_id User ID
 * @param string $box     'inbox' or 'sent'
 * @param int    $parent_id Parent message ID (for replies)
 * @return array Messages
 */
function caniincasa_get_messages( $user_id, $box = 'inbox', $parent_id = null ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    if ( $parent_id ) {
        // Get conversation thread
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table
            WHERE (id = %d OR parent_id = %d)
            AND ((sender_id = %d AND sender_deleted = 0) OR (recipient_id = %d AND recipient_deleted = 0))
            ORDER BY created_at ASC",
            $parent_id,
            $parent_id,
            $user_id,
            $user_id
        ), ARRAY_A );
    } elseif ( $box === 'sent' ) {
        // Get sent messages (only root messages, not replies)
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table
            WHERE sender_id = %d
            AND sender_deleted = 0
            AND parent_id IS NULL
            ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A );
    } else {
        // Get inbox messages (only root messages, not replies)
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table
            WHERE recipient_id = %d
            AND recipient_deleted = 0
            AND parent_id IS NULL
            ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A );
    }

    // Enrich with user data
    foreach ( $messages as &$message ) {
        $sender = get_userdata( $message['sender_id'] );
        $recipient = get_userdata( $message['recipient_id'] );

        $message['sender_name'] = $sender ? $sender->display_name : 'Utente eliminato';
        $message['recipient_name'] = $recipient ? $recipient->display_name : 'Utente eliminato';
        $message['sender_email'] = $sender ? $sender->user_email : '';
        $message['recipient_email'] = $recipient ? $recipient->user_email : '';

        // Count replies
        $message['reply_count'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE parent_id = %d",
            $message['id']
        ) );
    }

    return $messages;
}

/**
 * AJAX: Send Message
 */
function caniincasa_ajax_send_message() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per inviare messaggi.' ) );
    }

    $sender_id = get_current_user_id();
    $recipient_id = isset( $_POST['recipient_id'] ) ? absint( $_POST['recipient_id'] ) : 0;
    $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : null;
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
    $message = isset( $_POST['message'] ) ? wp_kses_post( $_POST['message'] ) : '';
    $related_post_id = isset( $_POST['related_post_id'] ) ? absint( $_POST['related_post_id'] ) : null;
    $related_post_type = isset( $_POST['related_post_type'] ) ? sanitize_text_field( $_POST['related_post_type'] ) : null;

    // Validation
    if ( ! $recipient_id || ! $message ) {
        wp_send_json_error( array( 'message' => 'Compila tutti i campi obbligatori.' ) );
    }

    if ( $sender_id === $recipient_id ) {
        wp_send_json_error( array( 'message' => 'Non puoi inviare messaggi a te stesso.' ) );
    }

    // Check if recipient exists
    $recipient = get_userdata( $recipient_id );
    if ( ! $recipient ) {
        wp_send_json_error( array( 'message' => 'Destinatario non valido.' ) );
    }

    // Check if messaging is allowed
    if ( ! caniincasa_can_send_message( $sender_id, $recipient_id ) ) {
        wp_send_json_error( array( 'message' => 'Non puoi inviare messaggi a questo utente.' ) );
    }

    // If reply, use parent's subject if empty
    if ( $parent_id && empty( $subject ) ) {
        global $wpdb;
        $table = $wpdb->prefix . 'caniincasa_messages';
        $parent_subject = $wpdb->get_var( $wpdb->prepare(
            "SELECT subject FROM $table WHERE id = %d",
            $parent_id
        ) );

        if ( $parent_subject ) {
            $subject = 'Re: ' . $parent_subject;
        }
    }

    // Default subject if still empty
    if ( empty( $subject ) ) {
        $subject = 'Nuovo messaggio';
    }

    // Insert message
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    $result = $wpdb->insert(
        $table,
        array(
            'sender_id'         => $sender_id,
            'recipient_id'      => $recipient_id,
            'parent_id'         => $parent_id,
            'subject'           => $subject,
            'message'           => $message,
            'related_post_id'   => $related_post_id,
            'related_post_type' => $related_post_type,
            'is_read'           => 0,
            'created_at'        => current_time( 'mysql' ),
        ),
        array( '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s' )
    );

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Errore durante l\'invio del messaggio.' ) );
    }

    $message_id = $wpdb->insert_id;

    // Send email notification to recipient
    caniincasa_send_message_notification( $message_id );

    wp_send_json_success( array(
        'message'    => 'Messaggio inviato con successo!',
        'message_id' => $message_id,
    ) );
}
add_action( 'wp_ajax_send_message', 'caniincasa_ajax_send_message' );

/**
 * Send email notification for new message
 */
function caniincasa_send_message_notification( $message_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    $message = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $message_id
    ), ARRAY_A );

    if ( ! $message ) {
        return false;
    }

    $sender = get_userdata( $message['sender_id'] );
    $recipient = get_userdata( $message['recipient_id'] );

    if ( ! $sender || ! $recipient ) {
        return false;
    }

    $to = $recipient->user_email;
    $subject = sprintf( '[%s] Nuovo messaggio da %s', get_bloginfo( 'name' ), $sender->display_name );

    $message_url = home_url( '/dashboard/?tab=messages&message_id=' . $message_id );

    $body = sprintf(
        "Ciao %s,\n\n" .
        "Hai ricevuto un nuovo messaggio da %s.\n\n" .
        "Oggetto: %s\n\n" .
        "%s\n\n" .
        "Rispondi al messaggio: %s\n\n" .
        "Puoi anche gestire le tue impostazioni di notifica dalla tua dashboard.\n\n" .
        "Il team di %s",
        $recipient->display_name,
        $sender->display_name,
        $message['subject'],
        wp_trim_words( strip_tags( $message['message'] ), 50, '...' ),
        $message_url,
        get_bloginfo( 'name' )
    );

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    return wp_mail( $to, $subject, $body, $headers );
}

/**
 * AJAX: Mark Message as Read
 */
function caniincasa_ajax_mark_message_read() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $user_id = get_current_user_id();
    $message_id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

    if ( ! $message_id ) {
        wp_send_json_error( array( 'message' => 'ID messaggio non valido.' ) );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    // Verify message belongs to user
    $message = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND recipient_id = %d",
        $message_id,
        $user_id
    ), ARRAY_A );

    if ( ! $message ) {
        wp_send_json_error( array( 'message' => 'Messaggio non trovato.' ) );
    }

    // Update
    $result = $wpdb->update(
        $table,
        array( 'is_read' => 1 ),
        array( 'id' => $message_id ),
        array( '%d' ),
        array( '%d' )
    );

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Errore durante l\'aggiornamento.' ) );
    }

    wp_send_json_success( array( 'message' => 'Messaggio segnato come letto.' ) );
}
add_action( 'wp_ajax_mark_message_read', 'caniincasa_ajax_mark_message_read' );

/**
 * AJAX: Delete Message
 */
function caniincasa_ajax_delete_message() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $user_id = get_current_user_id();
    $message_id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

    if ( ! $message_id ) {
        wp_send_json_error( array( 'message' => 'ID messaggio non valido.' ) );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    // Verify message belongs to user
    $message = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND (sender_id = %d OR recipient_id = %d)",
        $message_id,
        $user_id,
        $user_id
    ), ARRAY_A );

    if ( ! $message ) {
        wp_send_json_error( array( 'message' => 'Messaggio non trovato.' ) );
    }

    // Soft delete based on user role
    $field = ( $message['sender_id'] == $user_id ) ? 'sender_deleted' : 'recipient_deleted';

    $result = $wpdb->update(
        $table,
        array( $field => 1 ),
        array( 'id' => $message_id ),
        array( '%d' ),
        array( '%d' )
    );

    // If both users have deleted, permanently delete
    $updated_message = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $message_id
    ), ARRAY_A );

    if ( $updated_message && $updated_message['sender_deleted'] && $updated_message['recipient_deleted'] ) {
        $wpdb->delete( $table, array( 'id' => $message_id ), array( '%d' ) );
    }

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Errore durante l\'eliminazione.' ) );
    }

    wp_send_json_success( array( 'message' => 'Messaggio eliminato.' ) );
}
add_action( 'wp_ajax_delete_message', 'caniincasa_ajax_delete_message' );

/**
 * AJAX: Get Unread Count
 */
function caniincasa_ajax_get_unread_count() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $count = caniincasa_get_unread_count( get_current_user_id() );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_get_unread_count', 'caniincasa_ajax_get_unread_count' );

/**
 * AJAX: Block User
 */
function caniincasa_ajax_block_user() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $user_id = get_current_user_id();
    $blocked_user_id = isset( $_POST['blocked_user_id'] ) ? absint( $_POST['blocked_user_id'] ) : 0;

    if ( ! $blocked_user_id ) {
        wp_send_json_error( array( 'message' => 'ID utente non valido.' ) );
    }

    if ( $user_id === $blocked_user_id ) {
        wp_send_json_error( array( 'message' => 'Non puoi bloccare te stesso.' ) );
    }

    // Check if user exists
    if ( ! get_userdata( $blocked_user_id ) ) {
        wp_send_json_error( array( 'message' => 'Utente non trovato.' ) );
    }

    // Check if already blocked
    if ( caniincasa_is_user_blocked( $user_id, $blocked_user_id ) ) {
        wp_send_json_error( array( 'message' => 'Utente già bloccato.' ) );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_blocked_users';

    $result = $wpdb->insert(
        $table,
        array(
            'user_id'         => $user_id,
            'blocked_user_id' => $blocked_user_id,
            'created_at'      => current_time( 'mysql' ),
        ),
        array( '%d', '%d', '%s' )
    );

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Errore durante il blocco dell\'utente.' ) );
    }

    wp_send_json_success( array( 'message' => 'Utente bloccato con successo.' ) );
}
add_action( 'wp_ajax_block_user', 'caniincasa_ajax_block_user' );

/**
 * AJAX: Unblock User
 */
function caniincasa_ajax_unblock_user() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato.' ) );
    }

    $user_id = get_current_user_id();
    $blocked_user_id = isset( $_POST['blocked_user_id'] ) ? absint( $_POST['blocked_user_id'] ) : 0;

    if ( ! $blocked_user_id ) {
        wp_send_json_error( array( 'message' => 'ID utente non valido.' ) );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_blocked_users';

    $result = $wpdb->delete(
        $table,
        array(
            'user_id'         => $user_id,
            'blocked_user_id' => $blocked_user_id,
        ),
        array( '%d', '%d' )
    );

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Errore durante lo sblocco dell\'utente.' ) );
    }

    wp_send_json_success( array( 'message' => 'Utente sbloccato con successo.' ) );
}
add_action( 'wp_ajax_unblock_user', 'caniincasa_ajax_unblock_user' );

/**
 * Get blocked users for a user
 */
function caniincasa_get_blocked_users( $user_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_blocked_users';

    $blocked_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT blocked_user_id FROM $table WHERE user_id = %d",
        $user_id
    ) );

    if ( empty( $blocked_ids ) ) {
        return array();
    }

    $blocked_users = array();
    foreach ( $blocked_ids as $blocked_id ) {
        $user = get_userdata( $blocked_id );
        if ( $user ) {
            $blocked_users[] = array(
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
            );
        }
    }

    return $blocked_users;
}
