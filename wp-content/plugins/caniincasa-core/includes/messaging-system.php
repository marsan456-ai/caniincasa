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
 * Get unread message count for user (with caching)
 */
function caniincasa_get_unread_count( $user_id ) {
    // Try to get from cache first
    $cache_key = 'caniincasa_unread_count_' . $user_id;
    $cached_count = get_transient( $cache_key );

    if ( false !== $cached_count ) {
        return (int) $cached_count;
    }

    // FORCE schema update before counting
    caniincasa_ensure_messaging_tables();

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table
        WHERE recipient_id = %d
        AND is_read = 0
        AND COALESCE(recipient_deleted, 0) = 0",
        $user_id
    ) );

    // Cache for 5 minutes
    set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );

    return (int) $count;
}

/**
 * Clear unread count cache for user
 */
function caniincasa_clear_unread_cache( $user_id ) {
    $cache_key = 'caniincasa_unread_count_' . $user_id;
    delete_transient( $cache_key );
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
    // FORCE schema update before every query
    caniincasa_ensure_messaging_tables();

    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';

    if ( $parent_id ) {
        // Get conversation thread
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table
            WHERE (id = %d OR parent_id = %d)
            AND ((sender_id = %d AND COALESCE(sender_deleted, 0) = 0) OR (recipient_id = %d AND COALESCE(recipient_deleted, 0) = 0))
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
            AND COALESCE(sender_deleted, 0) = 0
            AND parent_id IS NULL
            ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A );
    } else {
        // Get inbox messages (only root messages, not replies)
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table
            WHERE recipient_id = %d
            AND COALESCE(recipient_deleted, 0) = 0
            AND parent_id IS NULL
            ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A );
    }

    if ( empty( $messages ) ) {
        return $messages;
    }

    // Batch collect user IDs and message IDs to avoid N+1 queries
    $user_ids = array();
    $message_ids = array();
    foreach ( $messages as $message ) {
        if ( ! empty( $message['sender_id'] ) ) {
            $user_ids[] = (int) $message['sender_id'];
        }
        if ( ! empty( $message['recipient_id'] ) ) {
            $user_ids[] = (int) $message['recipient_id'];
        }
        $message_ids[] = (int) $message['id'];
    }
    $user_ids = array_unique( $user_ids );

    // Batch fetch all users at once
    $users_map = array();
    if ( ! empty( $user_ids ) ) {
        $users = get_users( array(
            'include' => $user_ids,
            'fields'  => array( 'ID', 'display_name', 'user_email' ),
        ) );
        foreach ( $users as $user ) {
            $users_map[ $user->ID ] = $user;
        }
    }

    // Batch fetch reply counts
    $reply_counts = array();
    if ( ! empty( $message_ids ) && ! $parent_id ) {
        // Only count replies for root messages
        $placeholders = implode( ',', array_fill( 0, count( $message_ids ), '%d' ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $reply_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT parent_id, COUNT(*) as count FROM {$table} WHERE parent_id IN ($placeholders) GROUP BY parent_id",
                ...$message_ids
            ),
            OBJECT_K
        );
        foreach ( $reply_results as $parent_id_key => $row ) {
            $reply_counts[ $parent_id_key ] = (int) $row->count;
        }
    }

    // Enrich messages with cached user data
    foreach ( $messages as &$message ) {
        $sender_id = (int) $message['sender_id'];
        $recipient_id = (int) $message['recipient_id'];
        $msg_id = (int) $message['id'];

        $sender = isset( $users_map[ $sender_id ] ) ? $users_map[ $sender_id ] : null;
        $recipient = isset( $users_map[ $recipient_id ] ) ? $users_map[ $recipient_id ] : null;

        $message['sender_name'] = $sender ? $sender->display_name : 'Utente eliminato';
        $message['recipient_name'] = $recipient ? $recipient->display_name : 'Utente eliminato';
        $message['sender_email'] = $sender ? $sender->user_email : '';
        $message['recipient_email'] = $recipient ? $recipient->user_email : '';
        $message['reply_count'] = isset( $reply_counts[ $msg_id ] ) ? $reply_counts[ $msg_id ] : 0;
    }

    return $messages;
}

/**
 * Ensure messaging tables exist and have correct schema
 */
function caniincasa_ensure_messaging_tables() {
    // Cache check - only run once per request
    static $already_checked = false;
    if ( $already_checked ) {
        return;
    }

    global $wpdb;

    $messages_table = $wpdb->prefix . 'caniincasa_messages';
    $blocked_table = $wpdb->prefix . 'caniincasa_blocked_users';

    // Check if tables exist
    $messages_exists = $wpdb->get_var( "SHOW TABLES LIKE '$messages_table'" ) === $messages_table;
    $blocked_exists = $wpdb->get_var( "SHOW TABLES LIKE '$blocked_table'" ) === $blocked_table;

    if ( ! $messages_exists || ! $blocked_exists ) {
        caniincasa_create_messaging_tables();
        $already_checked = true;
        return;
    }

    // Check if messages table has all required columns
    $columns = $wpdb->get_col( "DESCRIBE $messages_table" );

    $columns_added = false;

    // Add parent_id if missing (for threading/replies)
    if ( ! in_array( 'parent_id', $columns ) ) {
        $wpdb->query( "ALTER TABLE $messages_table ADD COLUMN parent_id bigint(20) UNSIGNED DEFAULT NULL AFTER recipient_id" );
        $wpdb->query( "ALTER TABLE $messages_table ADD KEY parent_id (parent_id)" );
        $columns_added = true;
    }

    if ( ! in_array( 'sender_deleted', $columns ) ) {
        $wpdb->query( "ALTER TABLE $messages_table ADD COLUMN sender_deleted tinyint(1) DEFAULT 0 AFTER is_read" );
        $columns_added = true;
    }

    if ( ! in_array( 'recipient_deleted', $columns ) ) {
        $wpdb->query( "ALTER TABLE $messages_table ADD COLUMN recipient_deleted tinyint(1) DEFAULT 0 AFTER sender_deleted" );
        $columns_added = true;
    }

    // If columns were just added, update existing NULL values to 0
    if ( $columns_added ) {
        $wpdb->query( "UPDATE $messages_table SET sender_deleted = 0 WHERE sender_deleted IS NULL" );
        $wpdb->query( "UPDATE $messages_table SET recipient_deleted = 0 WHERE recipient_deleted IS NULL" );
    }

    // Add composite indexes for performance (check if exist first)
    $indexes = $wpdb->get_results( "SHOW INDEX FROM $messages_table", ARRAY_A );
    $index_names = array_column( $indexes, 'Key_name' );

    // Index for inbox query (recipient + not deleted + not read)
    if ( ! in_array( 'idx_recipient_inbox', $index_names ) ) {
        $wpdb->query( "CREATE INDEX idx_recipient_inbox ON $messages_table (recipient_id, recipient_deleted, is_read, created_at)" );
    }

    // Index for sent messages query (sender + not deleted)
    if ( ! in_array( 'idx_sender_sent', $index_names ) ) {
        $wpdb->query( "CREATE INDEX idx_sender_sent ON $messages_table (sender_id, sender_deleted, created_at)" );
    }

    // Index for replies query (parent + users)
    if ( ! in_array( 'idx_parent_replies', $index_names ) ) {
        $wpdb->query( "CREATE INDEX idx_parent_replies ON $messages_table (parent_id, sender_id, recipient_id)" );
    }

    $already_checked = true;
}

/**
 * AJAX: Send Message
 */
function caniincasa_ajax_send_message() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per inviare messaggi.' ) );
    }

    // Ensure tables exist
    caniincasa_ensure_messaging_tables();

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

    // Build insert data dynamically to handle NULL values
    $insert_data = array(
        'sender_id'         => $sender_id,
        'recipient_id'      => $recipient_id,
        'subject'           => $subject,
        'message'           => $message,
        'is_read'           => 0,
        'created_at'        => current_time( 'mysql' ),
    );

    $insert_format = array( '%d', '%d', '%s', '%s', '%d', '%s' );

    if ( $parent_id ) {
        $insert_data['parent_id'] = $parent_id;
        $insert_format[] = '%d';
    }

    if ( $related_post_id ) {
        $insert_data['related_post_id'] = $related_post_id;
        $insert_format[] = '%d';
    }

    if ( $related_post_type ) {
        $insert_data['related_post_type'] = $related_post_type;
        $insert_format[] = '%s';
    }

    $result = $wpdb->insert( $table, $insert_data, $insert_format );

    if ( $result === false ) {
        $error_msg = 'Errore durante l\'invio del messaggio.';
        if ( $wpdb->last_error ) {
            error_log( 'Messaging DB Error: ' . $wpdb->last_error );
            $error_msg .= ' (Dettagli: ' . $wpdb->last_error . ')';
        }
        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    $message_id = $wpdb->insert_id;

    // Clear unread count cache for recipient
    caniincasa_clear_unread_cache( $recipient_id );

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

    // Clear unread count cache for user
    caniincasa_clear_unread_cache( $user_id );

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

    // Clear unread count cache for user (in case they deleted an unread message)
    caniincasa_clear_unread_cache( $user_id );

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

/**
 * AJAX: Get message replies
 */
function caniincasa_ajax_get_message_replies() {
    check_ajax_referer( 'caniincasa_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Devi essere loggato per visualizzare i messaggi.' ) );
    }

    $user_id = get_current_user_id();
    $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0;

    if ( ! $parent_id ) {
        wp_send_json_error( array( 'message' => 'ID messaggio non valido.' ) );
    }

    // Get the parent message to verify user is involved
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';
    $parent_message = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $parent_id
    ), ARRAY_A );

    if ( ! $parent_message ) {
        wp_send_json_error( array( 'message' => 'Messaggio non trovato.' ) );
    }

    // Verify user is sender or recipient of parent message
    if ( $parent_message['sender_id'] != $user_id && $parent_message['recipient_id'] != $user_id ) {
        wp_send_json_error( array( 'message' => 'Non hai i permessi per visualizzare questo messaggio.' ) );
    }

    // Get count of all replies first
    $total_replies = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table
        WHERE parent_id = %d
        AND ((sender_id = %d AND COALESCE(sender_deleted, 0) = 0) OR (recipient_id = %d AND COALESCE(recipient_deleted, 0) = 0))",
        $parent_id,
        $user_id,
        $user_id
    ) );

    // Limit replies to prevent performance issues (max 50)
    $limit = 50;
    $has_more = $total_replies > $limit;

    // Get replies with limit
    $replies = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table
        WHERE parent_id = %d
        AND ((sender_id = %d AND COALESCE(sender_deleted, 0) = 0) OR (recipient_id = %d AND COALESCE(recipient_deleted, 0) = 0))
        ORDER BY created_at ASC
        LIMIT %d",
        $parent_id,
        $user_id,
        $user_id,
        $limit
    ), ARRAY_A );

    // Enrich with user data
    foreach ( $replies as &$reply ) {
        $sender = get_userdata( $reply['sender_id'] );
        $recipient = get_userdata( $reply['recipient_id'] );

        $reply['sender_name'] = $sender ? $sender->display_name : 'Utente eliminato';
        $reply['recipient_name'] = $recipient ? $recipient->display_name : 'Utente eliminato';
        $reply['is_mine'] = ( $reply['sender_id'] == $user_id );
    }

    wp_send_json_success( array(
        'replies'      => $replies,
        'count'        => count( $replies ),
        'total'        => (int) $total_replies,
        'has_more'     => $has_more,
        'showing'      => count( $replies )
    ) );
}
add_action( 'wp_ajax_get_message_replies', 'caniincasa_ajax_get_message_replies' );
