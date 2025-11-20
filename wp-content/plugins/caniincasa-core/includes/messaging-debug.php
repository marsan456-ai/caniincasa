<?php
/**
 * Messaging System Debug
 * Temporary debug file to diagnose messaging issues
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Debug messaging system - show all data
 */
function caniincasa_messaging_debug() {
    if ( ! current_user_can( 'manage_options' ) && ! isset( $_GET['debug_messages'] ) ) {
        return;
    }

    global $wpdb;
    $messages_table = $wpdb->prefix . 'caniincasa_messages';
    $blocked_table = $wpdb->prefix . 'caniincasa_blocked_users';

    echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid #333;">';
    echo '<h2>🔍 MESSAGING SYSTEM DEBUG</h2>';

    // Check if tables exist
    echo '<h3>1. TABELLE DATABASE</h3>';
    $messages_exists = $wpdb->get_var( "SHOW TABLES LIKE '$messages_table'" );
    $blocked_exists = $wpdb->get_var( "SHOW TABLES LIKE '$blocked_table'" );

    echo '<p><strong>Tabella messaggi:</strong> ' . ( $messages_exists ? '✅ ESISTE' : '❌ NON ESISTE' ) . '</p>';
    echo '<p><strong>Tabella blocchi:</strong> ' . ( $blocked_exists ? '✅ ESISTE' : '❌ NON ESISTE' ) . '</p>';

    if ( ! $messages_exists ) {
        echo '<p style="color: red;"><strong>PROBLEMA: La tabella messaggi non esiste!</strong></p>';
        echo '<p>Eseguo creazione tabella...</p>';
        caniincasa_create_messaging_tables();
        echo '<p>✅ Tabella creata. Ricarica la pagina.</p>';
        echo '</div>';
        return;
    }

    // Show table structure
    echo '<h3>2. STRUTTURA TABELLA MESSAGGI</h3>';
    $columns = $wpdb->get_results( "DESCRIBE $messages_table" );
    echo '<table border="1" cellpadding="5" style="background: white;">';
    echo '<tr><th>Colonna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>';
    foreach ( $columns as $col ) {
        echo '<tr>';
        echo '<td>' . $col->Field . '</td>';
        echo '<td>' . $col->Type . '</td>';
        echo '<td>' . $col->Null . '</td>';
        echo '<td>' . ( $col->Default !== null ? $col->Default : 'NULL' ) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // Count all messages
    echo '<h3>3. CONTEGGIO MESSAGGI</h3>';
    $total = $wpdb->get_var( "SELECT COUNT(*) FROM $messages_table" );
    echo '<p><strong>Totale messaggi nel database:</strong> ' . $total . '</p>';

    if ( $total == 0 ) {
        echo '<p style="color: orange;"><strong>⚠️ Nessun messaggio nel database!</strong></p>';
        echo '<p>Questo è normale se non hai ancora inviato messaggi.</p>';
    } else {
        // Show all messages
        echo '<h3>4. TUTTI I MESSAGGI (primi 10)</h3>';
        $all_messages = $wpdb->get_results( "SELECT * FROM $messages_table ORDER BY created_at DESC LIMIT 10", ARRAY_A );

        echo '<table border="1" cellpadding="5" style="background: white; font-size: 12px;">';
        echo '<tr>';
        echo '<th>ID</th><th>Sender</th><th>Recipient</th><th>Subject</th>';
        echo '<th>is_read</th><th>sender_deleted</th><th>recipient_deleted</th>';
        echo '<th>parent_id</th><th>Date</th>';
        echo '</tr>';

        foreach ( $all_messages as $msg ) {
            echo '<tr>';
            echo '<td>' . $msg['id'] . '</td>';
            echo '<td>' . $msg['sender_id'] . '</td>';
            echo '<td>' . $msg['recipient_id'] . '</td>';
            echo '<td>' . substr( $msg['subject'], 0, 30 ) . '</td>';
            echo '<td>' . ( $msg['is_read'] ?? 'NULL' ) . '</td>';
            echo '<td style="background: ' . ( isset( $msg['sender_deleted'] ) ? ( $msg['sender_deleted'] ? 'red' : 'green' ) : 'yellow' ) . '">';
            echo ( $msg['sender_deleted'] ?? 'NULL' );
            echo '</td>';
            echo '<td style="background: ' . ( isset( $msg['recipient_deleted'] ) ? ( $msg['recipient_deleted'] ? 'red' : 'green' ) : 'yellow' ) . '">';
            echo ( $msg['recipient_deleted'] ?? 'NULL' );
            echo '</td>';
            echo '<td>' . ( $msg['parent_id'] ?? 'NULL' ) . '</td>';
            echo '<td>' . $msg['created_at'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Test query for current user
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            echo '<h3>5. QUERY PER UTENTE CORRENTE (ID: ' . $user_id . ')</h3>';

            // Query inbox
            $query = $wpdb->prepare(
                "SELECT * FROM $messages_table
                WHERE recipient_id = %d
                AND COALESCE(recipient_deleted, 0) = 0
                AND parent_id IS NULL
                ORDER BY created_at DESC",
                $user_id
            );

            echo '<p><strong>Query eseguita:</strong></p>';
            echo '<pre>' . $query . '</pre>';

            $inbox = $wpdb->get_results( $query, ARRAY_A );
            echo '<p><strong>Messaggi ricevuti trovati:</strong> ' . count( $inbox ) . '</p>';

            if ( count( $inbox ) > 0 ) {
                echo '<table border="1" cellpadding="5" style="background: white;">';
                echo '<tr><th>ID</th><th>Da</th><th>Oggetto</th><th>Letto</th></tr>';
                foreach ( $inbox as $msg ) {
                    $sender = get_userdata( $msg['sender_id'] );
                    echo '<tr>';
                    echo '<td>' . $msg['id'] . '</td>';
                    echo '<td>' . ( $sender ? $sender->display_name : 'Sconosciuto' ) . '</td>';
                    echo '<td>' . $msg['subject'] . '</td>';
                    echo '<td>' . ( $msg['is_read'] ? 'Sì' : 'No' ) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            // Show what caniincasa_get_messages returns
            echo '<h3>6. RISULTATO caniincasa_get_messages()</h3>';
            $messages = caniincasa_get_messages( $user_id, 'inbox' );
            echo '<p><strong>Messaggi restituiti dalla funzione:</strong> ' . count( $messages ) . '</p>';
            echo '<pre>' . print_r( $messages, true ) . '</pre>';
        }
    }

    echo '<hr>';
    echo '<p><strong>💡 AZIONI DISPONIBILI:</strong></p>';
    echo '<p><a href="?fix_null_values=1" style="padding: 10px; background: blue; color: white; text-decoration: none;">🔧 Converti tutti NULL in 0</a></p>';
    echo '<p><a href="?recreate_tables=1" style="padding: 10px; background: orange; color: white; text-decoration: none;">🔄 Ricrea tabelle da zero</a></p>';

    echo '</div>';
}

// Fix NULL values
if ( isset( $_GET['fix_null_values'] ) && current_user_can( 'manage_options' ) ) {
    global $wpdb;
    $table = $wpdb->prefix . 'caniincasa_messages';
    $wpdb->query( "UPDATE $table SET sender_deleted = 0 WHERE sender_deleted IS NULL" );
    $wpdb->query( "UPDATE $table SET recipient_deleted = 0 WHERE recipient_deleted IS NULL" );
    wp_redirect( remove_query_arg( 'fix_null_values' ) );
    exit;
}

// Recreate tables
if ( isset( $_GET['recreate_tables'] ) && current_user_can( 'manage_options' ) ) {
    global $wpdb;
    $messages_table = $wpdb->prefix . 'caniincasa_messages';
    $blocked_table = $wpdb->prefix . 'caniincasa_blocked_users';

    // Backup first
    $wpdb->query( "CREATE TABLE IF NOT EXISTS {$messages_table}_backup AS SELECT * FROM $messages_table" );

    // Drop and recreate
    $wpdb->query( "DROP TABLE IF EXISTS $messages_table" );
    $wpdb->query( "DROP TABLE IF EXISTS $blocked_table" );

    caniincasa_create_messaging_tables();

    // Restore data
    $wpdb->query( "INSERT INTO $messages_table SELECT * FROM {$messages_table}_backup" );
    $wpdb->query( "DROP TABLE {$messages_table}_backup" );

    wp_redirect( remove_query_arg( 'recreate_tables' ) );
    exit;
}

// Add to dashboard page
add_action( 'wp_footer', 'caniincasa_messaging_debug' );
