<?php
/**
 * Admin Messages Management
 * View and manage all messages in WordPress admin
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu for messages
 */
function caniincasa_add_messages_admin_menu() {
    add_menu_page(
        __( 'Messaggi Utenti', 'caniincasa-core' ),
        __( 'Messaggi', 'caniincasa-core' ),
        'manage_options',
        'caniincasa-messages',
        'caniincasa_messages_admin_page',
        'dashicons-email',
        26
    );

    add_submenu_page(
        'caniincasa-messages',
        __( 'Tutti i Messaggi', 'caniincasa-core' ),
        __( 'Tutti i Messaggi', 'caniincasa-core' ),
        'manage_options',
        'caniincasa-messages',
        'caniincasa_messages_admin_page'
    );

    add_submenu_page(
        'caniincasa-messages',
        __( 'Statistiche', 'caniincasa-core' ),
        __( 'Statistiche', 'caniincasa-core' ),
        'manage_options',
        'caniincasa-messages-stats',
        'caniincasa_messages_stats_page'
    );
}
add_action( 'admin_menu', 'caniincasa_add_messages_admin_menu' );

/**
 * Main messages admin page
 */
function caniincasa_messages_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Handle bulk actions
    if ( isset( $_POST['action'] ) && $_POST['action'] === 'bulk_delete' && isset( $_POST['message_ids'] ) ) {
        check_admin_referer( 'caniincasa_messages_bulk' );

        $message_ids = array_map( 'absint', $_POST['message_ids'] );
        $placeholders = implode( ',', array_fill( 0, count( $message_ids ), '%d' ) );

        $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table_name WHERE id IN ($placeholders)",
            $message_ids
        ) );

        echo '<div class="notice notice-success"><p>' . sprintf( __( '%d messaggi eliminati.', 'caniincasa-core' ), count( $message_ids ) ) . '</p></div>';
    }

    // Handle single delete
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['message_id'] ) ) {
        check_admin_referer( 'delete_message_' . $_GET['message_id'] );

        $message_id = absint( $_GET['message_id'] );
        $wpdb->delete( $table_name, array( 'id' => $message_id ), array( '%d' ) );

        echo '<div class="notice notice-success"><p>' . __( 'Messaggio eliminato.', 'caniincasa-core' ) . '</p></div>';
    }

    // Get filters
    $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
    $user_filter = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;

    // Pagination
    $per_page = 20;
    $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
    $offset = ( $current_page - 1 ) * $per_page;

    // Build query
    $where = array( '1=1' );
    $query_args = array();

    if ( $search ) {
        $where[] = '(subject LIKE %s OR message LIKE %s)';
        $query_args[] = '%' . $wpdb->esc_like( $search ) . '%';
        $query_args[] = '%' . $wpdb->esc_like( $search ) . '%';
    }

    if ( $status_filter === 'read' ) {
        $where[] = 'is_read = 1';
    } elseif ( $status_filter === 'unread' ) {
        $where[] = 'is_read = 0';
    }

    if ( $user_filter ) {
        $where[] = '(sender_id = %d OR recipient_id = %d)';
        $query_args[] = $user_filter;
        $query_args[] = $user_filter;
    }

    $where_clause = implode( ' AND ', $where );

    // Get total count
    if ( empty( $query_args ) ) {
        $total_messages = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE $where_clause" );
    } else {
        $total_messages = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE $where_clause", $query_args ) );
    }

    // Get messages
    $query_args[] = $per_page;
    $query_args[] = $offset;

    $messages = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name
        WHERE $where_clause
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d",
        $query_args
    ) );

    // Get statistics
    $total_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
    $unread_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE is_read = 0" );
    $today_count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s",
        current_time( 'Y-m-d' )
    ) );

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php echo esc_html( get_admin_page_title() ); ?>
        </h1>

        <hr class="wp-header-end">

        <!-- Statistics Cards -->
        <div class="caniincasa-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: white; padding: 20px; border-left: 4px solid #2271b1; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px; font-size: 14px; color: #646970;">Totale Messaggi</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 600; color: #1d2327;"><?php echo number_format_i18n( $total_count ); ?></p>
            </div>
            <div style="background: white; padding: 20px; border-left: 4px solid #d63638; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px; font-size: 14px; color: #646970;">Non Letti</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 600; color: #1d2327;"><?php echo number_format_i18n( $unread_count ); ?></p>
            </div>
            <div style="background: white; padding: 20px; border-left: 4px solid #00a32a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px; font-size: 14px; color: #646970;">Oggi</h3>
                <p style="margin: 0; font-size: 28px; font-weight: 600; color: #1d2327;"><?php echo number_format_i18n( $today_count ); ?></p>
            </div>
        </div>

        <!-- Filters -->
        <form method="get" style="margin: 20px 0; background: white; padding: 15px; border: 1px solid #c3c4c7;">
            <input type="hidden" name="page" value="caniincasa-messages">

            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label for="s" style="display: block; margin-bottom: 5px; font-weight: 600;">Cerca</label>
                    <input type="search" id="s" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Oggetto o messaggio..." style="width: 250px;">
                </div>

                <div>
                    <label for="status" style="display: block; margin-bottom: 5px; font-weight: 600;">Stato</label>
                    <select name="status" id="status">
                        <option value=""><?php _e( 'Tutti', 'caniincasa-core' ); ?></option>
                        <option value="unread" <?php selected( $status_filter, 'unread' ); ?>><?php _e( 'Non Letti', 'caniincasa-core' ); ?></option>
                        <option value="read" <?php selected( $status_filter, 'read' ); ?>><?php _e( 'Letti', 'caniincasa-core' ); ?></option>
                    </select>
                </div>

                <div>
                    <label for="user_id" style="display: block; margin-bottom: 5px; font-weight: 600;">Utente</label>
                    <input type="number" name="user_id" id="user_id" value="<?php echo esc_attr( $user_filter ); ?>" placeholder="ID Utente" style="width: 120px;">
                </div>

                <div>
                    <button type="submit" class="button button-primary">Filtra</button>
                    <a href="<?php echo admin_url( 'admin.php?page=caniincasa-messages' ); ?>" class="button">Reset</a>
                </div>
            </div>
        </form>

        <!-- Messages Table -->
        <form method="post" id="messages-form">
            <?php wp_nonce_field( 'caniincasa_messages_bulk' ); ?>
            <input type="hidden" name="action" value="bulk_delete">

            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <button type="submit" class="button action" onclick="return confirm('Sei sicuro di voler eliminare i messaggi selezionati?');">
                        Elimina Selezionati
                    </button>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </td>
                        <th class="manage-column">Stato</th>
                        <th class="manage-column">Da</th>
                        <th class="manage-column">A</th>
                        <th class="manage-column">Oggetto</th>
                        <th class="manage-column">Messaggio</th>
                        <th class="manage-column">Data</th>
                        <th class="manage-column">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $messages ) ) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <p><?php _e( 'Nessun messaggio trovato.', 'caniincasa-core' ); ?></p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $messages as $message ) :
                            $sender = get_userdata( $message->sender_id );
                            $recipient = get_userdata( $message->recipient_id );
                            $is_unread = ! $message->is_read;
                        ?>
                            <tr <?php echo $is_unread ? 'style="background: #fff8dc;"' : ''; ?>>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="message_ids[]" value="<?php echo esc_attr( $message->id ); ?>">
                                </th>
                                <td>
                                    <?php if ( $is_unread ) : ?>
                                        <span class="dashicons dashicons-marker" style="color: #d63638;" title="Non letto"></span>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: #00a32a;" title="Letto"></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $sender ) : ?>
                                        <strong><?php echo esc_html( $sender->display_name ); ?></strong><br>
                                        <small style="color: #646970;">ID: <?php echo esc_html( $message->sender_id ); ?></small>
                                    <?php else : ?>
                                        <em style="color: #d63638;">Utente eliminato</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $recipient ) : ?>
                                        <strong><?php echo esc_html( $recipient->display_name ); ?></strong><br>
                                        <small style="color: #646970;">ID: <?php echo esc_html( $message->recipient_id ); ?></small>
                                    <?php else : ?>
                                        <em style="color: #d63638;">Utente eliminato</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $message->subject ); ?></strong>
                                    <?php if ( $message->related_post_id ) : ?>
                                        <br><small style="color: #646970;">
                                            Relativo a: <a href="<?php echo get_edit_post_link( $message->related_post_id ); ?>" target="_blank">
                                                <?php echo esc_html( get_the_title( $message->related_post_id ) ); ?>
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo esc_html( wp_trim_words( $message->message, 15 ) ); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo esc_html( mysql2date( 'd/m/Y H:i', $message->created_at ) ); ?>
                                    <?php if ( $message->read_at ) : ?>
                                        <br><small style="color: #646970;">Letto: <?php echo esc_html( mysql2date( 'd/m/Y H:i', $message->read_at ) ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" class="button button-small view-message-btn" data-message-id="<?php echo esc_attr( $message->id ); ?>">
                                        Visualizza
                                    </a>
                                    <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=caniincasa-messages&action=delete&message_id=' . $message->id ), 'delete_message_' . $message->id ); ?>"
                                       class="button button-small"
                                       onclick="return confirm('Sei sicuro di voler eliminare questo messaggio?');">
                                        Elimina
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php
            $total_pages = ceil( $total_messages / $per_page );
            if ( $total_pages > 1 ) :
                $page_links = paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'prev_text' => __( '&laquo;' ),
                    'next_text' => __( '&raquo;' ),
                    'total' => $total_pages,
                    'current' => $current_page
                ) );

                if ( $page_links ) :
            ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php echo $page_links; ?>
                    </div>
                </div>
            <?php
                endif;
            endif;
            ?>
        </form>
    </div>

    <!-- Message Detail Modal -->
    <div id="message-detail-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7);">
        <div style="background: white; margin: 50px auto; max-width: 700px; padding: 0; border-radius: 4px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">Dettaglio Messaggio</h2>
                <button type="button" class="button" onclick="closeMessageModal();">Chiudi</button>
            </div>
            <div id="message-detail-content" style="padding: 20px;">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Select all checkbox
        $('#cb-select-all').on('click', function() {
            $('input[name="message_ids[]"]').prop('checked', this.checked);
        });

        // View message button
        $('.view-message-btn').on('click', function(e) {
            e.preventDefault();
            var messageId = $(this).data('message-id');
            loadMessageDetail(messageId);
        });

        function loadMessageDetail(messageId) {
            $('#message-detail-modal').show();
            $('#message-detail-content').html('<p>Caricamento...</p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_message_detail',
                    message_id: messageId
                },
                success: function(response) {
                    if (response.success) {
                        $('#message-detail-content').html(response.data.html);
                    } else {
                        $('#message-detail-content').html('<p style="color: red;">Errore nel caricamento del messaggio.</p>');
                    }
                },
                error: function() {
                    $('#message-detail-content').html('<p style="color: red;">Errore di connessione.</p>');
                }
            });
        }

        window.closeMessageModal = function() {
            $('#message-detail-modal').hide();
        };

        // Close modal on outside click
        $('#message-detail-modal').on('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });
    });
    </script>

    <style>
    .caniincasa-stats-cards h3 {
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    </style>
    <?php
}

/**
 * Statistics page
 */
function caniincasa_messages_stats_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_messages';

    // Get statistics
    $total_messages = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
    $total_users = $wpdb->get_var( "SELECT COUNT(DISTINCT sender_id) FROM $table_name" );
    $avg_per_user = $total_users > 0 ? round( $total_messages / $total_users, 2 ) : 0;

    // Top senders
    $top_senders = $wpdb->get_results( "
        SELECT sender_id, COUNT(*) as count
        FROM $table_name
        GROUP BY sender_id
        ORDER BY count DESC
        LIMIT 10
    " );

    // Messages per day (last 30 days)
    $daily_stats = $wpdb->get_results( "
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM $table_name
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    " );

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: white; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px;">Messaggi Totali</h3>
                <p style="font-size: 36px; font-weight: 600; margin: 0; color: #2271b1;"><?php echo number_format_i18n( $total_messages ); ?></p>
            </div>
            <div style="background: white; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px;">Utenti Attivi</h3>
                <p style="font-size: 36px; font-weight: 600; margin: 0; color: #00a32a;"><?php echo number_format_i18n( $total_users ); ?></p>
            </div>
            <div style="background: white; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px;">Media per Utente</h3>
                <p style="font-size: 36px; font-weight: 600; margin: 0; color: #f97316;"><?php echo $avg_per_user; ?></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin: 20px 0;">
            <!-- Top Senders -->
            <div style="background: white; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Top 10 Mittenti</h2>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>Utente</th>
                            <th>Messaggi Inviati</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $top_senders as $sender ) :
                            $user = get_userdata( $sender->sender_id );
                        ?>
                            <tr>
                                <td>
                                    <?php if ( $user ) : ?>
                                        <?php echo esc_html( $user->display_name ); ?>
                                        <br><small style="color: #646970;">ID: <?php echo esc_html( $sender->sender_id ); ?></small>
                                    <?php else : ?>
                                        <em>Utente eliminato (ID: <?php echo esc_html( $sender->sender_id ); ?>)</em>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo number_format_i18n( $sender->count ); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Daily Stats -->
            <div style="background: white; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Messaggi per Giorno (Ultimi 30 giorni)</h2>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Messaggi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $daily_stats as $stat ) : ?>
                            <tr>
                                <td><?php echo esc_html( mysql2date( 'd/m/Y', $stat->date ) ); ?></td>
                                <td><strong><?php echo number_format_i18n( $stat->count ); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

/**
 * AJAX: Get message detail
 */
function caniincasa_ajax_get_message_detail() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Non autorizzato' ) );
    }

    $message_id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

    if ( ! $message_id ) {
        wp_send_json_error( array( 'message' => 'ID messaggio non valido' ) );
    }

    $message = caniincasa_get_message( $message_id );

    if ( ! $message ) {
        wp_send_json_error( array( 'message' => 'Messaggio non trovato' ) );
    }

    $sender = get_userdata( $message->sender_id );
    $recipient = get_userdata( $message->recipient_id );

    ob_start();
    ?>
    <table class="form-table">
        <tr>
            <th>Da:</th>
            <td>
                <?php if ( $sender ) : ?>
                    <strong><?php echo esc_html( $sender->display_name ); ?></strong> (<?php echo esc_html( $sender->user_email ); ?>)<br>
                    <small>ID: <?php echo esc_html( $message->sender_id ); ?></small>
                <?php else : ?>
                    <em style="color: #d63638;">Utente eliminato (ID: <?php echo esc_html( $message->sender_id ); ?>)</em>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>A:</th>
            <td>
                <?php if ( $recipient ) : ?>
                    <strong><?php echo esc_html( $recipient->display_name ); ?></strong> (<?php echo esc_html( $recipient->user_email ); ?>)<br>
                    <small>ID: <?php echo esc_html( $message->recipient_id ); ?></small>
                <?php else : ?>
                    <em style="color: #d63638;">Utente eliminato (ID: <?php echo esc_html( $message->recipient_id ); ?>)</em>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Oggetto:</th>
            <td><strong><?php echo esc_html( $message->subject ); ?></strong></td>
        </tr>
        <tr>
            <th>Messaggio:</th>
            <td>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #ddd;">
                    <?php echo nl2br( esc_html( $message->message ) ); ?>
                </div>
            </td>
        </tr>
        <?php if ( $message->related_post_id ) : ?>
        <tr>
            <th>Post Correlato:</th>
            <td>
                <a href="<?php echo get_edit_post_link( $message->related_post_id ); ?>" target="_blank">
                    <?php echo esc_html( get_the_title( $message->related_post_id ) ); ?>
                </a>
                <small>(ID: <?php echo esc_html( $message->related_post_id ); ?>)</small>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <th>Inviato:</th>
            <td><?php echo esc_html( mysql2date( 'd/m/Y H:i:s', $message->created_at ) ); ?></td>
        </tr>
        <tr>
            <th>Stato:</th>
            <td>
                <?php if ( $message->is_read ) : ?>
                    <span style="color: #00a32a;">✓ Letto</span> - <?php echo esc_html( mysql2date( 'd/m/Y H:i:s', $message->read_at ) ); ?>
                <?php else : ?>
                    <span style="color: #d63638;">● Non letto</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_get_message_detail', 'caniincasa_ajax_get_message_detail' );
