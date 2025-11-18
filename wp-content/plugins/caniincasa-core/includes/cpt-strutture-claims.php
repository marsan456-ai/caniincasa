<?php
/**
 * Strutture Claims Management
 * Gestisce le richieste di aggiornamento/claim delle strutture
 *
 * @package Caniincasa_Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Custom Post Type for Structure Claims
 */
function caniincasa_register_strutture_claims_cpt() {
    $labels = array(
        'name'               => 'Richieste Strutture',
        'singular_name'      => 'Richiesta Struttura',
        'menu_name'          => 'Richieste Strutture',
        'add_new'            => 'Aggiungi Nuova',
        'add_new_item'       => 'Aggiungi Nuova Richiesta',
        'edit_item'          => 'Modifica Richiesta',
        'view_item'          => 'Visualizza Richiesta',
        'all_items'          => 'Tutte le Richieste',
        'search_items'       => 'Cerca Richieste',
        'not_found'          => 'Nessuna richiesta trovata',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => false, // We'll add custom menu
        'capability_type'     => 'post',
        'capabilities'        => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap'        => true,
        'has_archive'         => false,
        'hierarchical'        => false,
        'supports'            => array( 'title' ),
        'show_in_rest'        => false,
    );

    register_post_type( 'strutture_claims', $args );
}
add_action( 'init', 'caniincasa_register_strutture_claims_cpt' );

/**
 * Add admin menu for claims management
 */
function caniincasa_strutture_claims_menu() {
    add_menu_page(
        'Richieste Strutture',
        'Richieste Strutture',
        'manage_options',
        'strutture-claims',
        'caniincasa_render_claims_page',
        'dashicons-building',
        25
    );
}
add_action( 'admin_menu', 'caniincasa_strutture_claims_menu' );

/**
 * Add meta boxes for claim data display
 */
function caniincasa_add_claim_meta_boxes() {
    add_meta_box(
        'claim_data_meta_box',
        'Dati della Richiesta',
        'caniincasa_render_claim_data_meta_box',
        'strutture_claims',
        'normal',
        'high'
    );

    add_meta_box(
        'claim_info_meta_box',
        'Informazioni Richiesta',
        'caniincasa_render_claim_info_meta_box',
        'strutture_claims',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'caniincasa_add_claim_meta_boxes' );

/**
 * Render claim data meta box
 */
function caniincasa_render_claim_data_meta_box( $post ) {
    $claim_data = get_post_meta( $post->ID, '_claim_data', true );
    $struttura_type = get_post_meta( $post->ID, '_struttura_type', true );

    if ( ! $claim_data || ! is_array( $claim_data ) ) {
        echo '<p>Nessun dato disponibile.</p>';
        return;
    }

    // Field labels mapping
    $field_labels = array(
        // Common fields
        'indirizzo' => 'Indirizzo',
        'citta' => 'Città',
        'provincia' => 'Provincia',
        'cap' => 'CAP',
        'telefono' => 'Telefono',
        'email' => 'Email',
        'sito_web' => 'Sito Web',
        'orari_apertura' => 'Orari di Apertura',
        'descrizione' => 'Descrizione',
        'servizi' => 'Servizi',
        'note' => 'Note Aggiuntive',

        // Allevamenti
        'razze_allevate' => 'Razze Allevate',
        'certificazioni' => 'Certificazioni',
        'anno_fondazione' => 'Anno di Fondazione',

        // Veterinari
        'specializzazioni' => 'Specializzazioni',
        'servizi_emergenza' => 'Servizi di Emergenza',
        'attrezzature' => 'Attrezzature',

        // Pensioni
        'capacita' => 'Capacità',
        'dimensioni_box' => 'Dimensioni Box',
        'area_gioco' => 'Area Gioco',

        // Canili
        'tipo_canile' => 'Tipo di Canile',
        'cani_ospitati' => 'Numero Cani Ospitati',
        'adozioni' => 'Adozioni',

        // Centri Cinofili
        'corsi_offerti' => 'Corsi Offerti',
        'istruttori' => 'Istruttori',
        'campo_addestramento' => 'Campo di Addestramento',
    );

    ?>
    <style>
        .claim-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .claim-data-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            width: 30%;
        }
        .claim-data-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .claim-data-table tr:hover {
            background: #f8f9fa;
        }
        .field-value {
            word-break: break-word;
        }
        .field-value-long {
            max-height: 150px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .field-label {
            color: #555;
        }
    </style>

    <table class="claim-data-table">
        <thead>
            <tr>
                <th>Campo</th>
                <th>Valore</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $claim_data as $field_key => $field_value ) : ?>
                <?php
                // Skip empty values
                if ( empty( $field_value ) && $field_value !== '0' ) {
                    continue;
                }

                // Get field label
                $field_label = isset( $field_labels[ $field_key ] ) ? $field_labels[ $field_key ] : ucfirst( str_replace( '_', ' ', $field_key ) );

                // Format value for display
                if ( is_array( $field_value ) ) {
                    // Handle relationship fields (like razze_allevate)
                    if ( isset( $field_value[0] ) && is_numeric( $field_value[0] ) ) {
                        $titles = array();
                        foreach ( $field_value as $post_id ) {
                            $title = get_the_title( $post_id );
                            if ( $title ) {
                                $titles[] = $title;
                            }
                        }
                        $display_value = implode( ', ', $titles );
                    } else {
                        $display_value = implode( ', ', $field_value );
                    }
                } else {
                    $display_value = $field_value;
                }

                // Determine if value is long
                $is_long = strlen( $display_value ) > 200;
                ?>
                <tr>
                    <th class="field-label"><?php echo esc_html( $field_label ); ?></th>
                    <td class="field-value <?php echo $is_long ? 'field-value-long' : ''; ?>">
                        <?php echo nl2br( esc_html( $display_value ) ); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

/**
 * Render claim info meta box
 */
function caniincasa_render_claim_info_meta_box( $post ) {
    $struttura_id = get_post_meta( $post->ID, '_struttura_id', true );
    $struttura_type = get_post_meta( $post->ID, '_struttura_type', true );
    $user_id = get_post_meta( $post->ID, '_user_id', true );

    $struttura = get_post( $struttura_id );
    $user = get_userdata( $user_id );
    $status = get_post_status( $post->ID );

    ?>
    <style>
        .claim-info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .claim-info-item:last-child {
            border-bottom: none;
        }
        .claim-info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        .claim-info-value {
            color: #222;
        }
        .claim-status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .claim-action-buttons {
            margin-top: 15px;
        }
        .claim-action-btn {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }
        .btn-approve-single {
            background: #28a745;
            color: white;
        }
        .btn-approve-single:hover {
            background: #218838;
        }
        .btn-reject-single {
            background: #dc3545;
            color: white;
        }
        .btn-reject-single:hover {
            background: #c82333;
        }
        .btn-view-structure {
            background: #007bff;
            color: white;
        }
        .btn-view-structure:hover {
            background: #0056b3;
        }
    </style>

    <div class="claim-info-item">
        <div class="claim-info-label">Struttura</div>
        <div class="claim-info-value">
            <?php if ( $struttura ) : ?>
                <a href="<?php echo esc_url( get_edit_post_link( $struttura_id ) ); ?>" target="_blank">
                    <?php echo esc_html( $struttura->post_title ); ?>
                </a>
            <?php else : ?>
                N/A
            <?php endif; ?>
        </div>
    </div>

    <div class="claim-info-item">
        <div class="claim-info-label">Tipo Struttura</div>
        <div class="claim-info-value">
            <?php echo esc_html( ucfirst( str_replace( '_', ' ', $struttura_type ) ) ); ?>
        </div>
    </div>

    <div class="claim-info-item">
        <div class="claim-info-label">Utente Richiedente</div>
        <div class="claim-info-value">
            <?php if ( $user ) : ?>
                <a href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" target="_blank">
                    <?php echo esc_html( $user->display_name ); ?>
                </a><br>
                <small><?php echo esc_html( $user->user_email ); ?></small>
            <?php else : ?>
                N/A
            <?php endif; ?>
        </div>
    </div>

    <div class="claim-info-item">
        <div class="claim-info-label">Data Richiesta</div>
        <div class="claim-info-value">
            <?php echo get_the_date( 'd/m/Y H:i', $post->ID ); ?>
        </div>
    </div>

    <div class="claim-info-item">
        <div class="claim-info-label">Stato</div>
        <div class="claim-info-value">
            <?php if ( $status === 'pending' ) : ?>
                <span class="claim-status-badge status-pending">In Attesa</span>
            <?php elseif ( $status === 'publish' ) : ?>
                <span class="claim-status-badge status-approved">Approvato</span>
            <?php else : ?>
                <span class="claim-status-badge status-rejected">Rifiutato</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $status === 'pending' ) : ?>
    <div class="claim-action-buttons">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'approve_claim_' . $post->ID, 'claim_nonce' ); ?>
            <input type="hidden" name="action" value="approve_claim">
            <input type="hidden" name="claim_id" value="<?php echo esc_attr( $post->ID ); ?>">
            <button type="submit" class="claim-action-btn btn-approve-single">Approva Richiesta</button>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'reject_claim_' . $post->ID, 'claim_nonce' ); ?>
            <input type="hidden" name="action" value="reject_claim">
            <input type="hidden" name="claim_id" value="<?php echo esc_attr( $post->ID ); ?>">
            <button type="submit" class="claim-action-btn btn-reject-single">Rifiuta Richiesta</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if ( $struttura ) : ?>
    <a href="<?php echo esc_url( get_permalink( $struttura_id ) ); ?>" target="_blank" class="claim-action-btn btn-view-structure">
        Visualizza Struttura sul Sito
    </a>
    <?php endif; ?>
    <?php
}

/**
 * Handle single claim approval from edit screen
 */
function caniincasa_handle_single_claim_approval() {
    if ( ! isset( $_POST['claim_id'] ) || ! isset( $_POST['claim_nonce'] ) ) {
        return;
    }

    $claim_id = intval( $_POST['claim_id'] );

    if ( ! wp_verify_nonce( $_POST['claim_nonce'], 'approve_claim_' . $claim_id ) ) {
        wp_die( 'Nonce verification failed' );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Permission denied' );
    }

    caniincasa_approve_claim( $claim_id );

    wp_redirect( admin_url( 'admin.php?page=strutture-claims&approved=1' ) );
    exit;
}
add_action( 'admin_post_approve_claim', 'caniincasa_handle_single_claim_approval' );

/**
 * Handle single claim rejection from edit screen
 */
function caniincasa_handle_single_claim_rejection() {
    if ( ! isset( $_POST['claim_id'] ) || ! isset( $_POST['claim_nonce'] ) ) {
        return;
    }

    $claim_id = intval( $_POST['claim_id'] );

    if ( ! wp_verify_nonce( $_POST['claim_nonce'], 'reject_claim_' . $claim_id ) ) {
        wp_die( 'Nonce verification failed' );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Permission denied' );
    }

    caniincasa_reject_claim( $claim_id );

    wp_redirect( admin_url( 'admin.php?page=strutture-claims&rejected=1' ) );
    exit;
}
add_action( 'admin_post_reject_claim', 'caniincasa_handle_single_claim_rejection' );

/**
 * Render claims management page
 */
function caniincasa_render_claims_page() {
    // Handle bulk actions
    if ( isset( $_POST['action'] ) && isset( $_POST['claims'] ) && check_admin_referer( 'bulk-claims-action', 'bulk_claims_nonce' ) ) {
        $action = sanitize_text_field( $_POST['action'] );
        $claims = array_map( 'intval', $_POST['claims'] );

        foreach ( $claims as $claim_id ) {
            if ( $action === 'approve' ) {
                caniincasa_approve_claim( $claim_id );
            } elseif ( $action === 'reject' ) {
                caniincasa_reject_claim( $claim_id );
            }
        }

        $message = $action === 'approve' ? 'Richieste approvate con successo.' : 'Richieste rifiutate.';
        echo '<div class="notice notice-success"><p>' . esc_html( $message ) . '</p></div>';
    }

    // Get all pending claims
    $pending_claims = new WP_Query( array(
        'post_type'      => 'strutture_claims',
        'post_status'    => 'pending',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    // Get approved/rejected claims
    $processed_claims = new WP_Query( array(
        'post_type'      => 'strutture_claims',
        'post_status'    => array( 'publish', 'trash' ),
        'posts_per_page' => 20,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );

    ?>
    <div class="wrap">
        <h1>Gestione Richieste Aggiornamento Strutture</h1>

        <style>
            .claims-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                background: white;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .claims-table th {
                background: #f8f9fa;
                padding: 12px;
                text-align: left;
                font-weight: 600;
                border-bottom: 2px solid #dee2e6;
            }
            .claims-table td {
                padding: 12px;
                border-bottom: 1px solid #dee2e6;
            }
            .claims-table tr:hover {
                background: #f8f9fa;
            }
            .claim-actions {
                display: flex;
                gap: 8px;
            }
            .btn-approve {
                background: #28a745;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
            }
            .btn-approve:hover {
                background: #218838;
            }
            .btn-reject {
                background: #dc3545;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
            }
            .btn-reject:hover {
                background: #c82333;
            }
            .btn-view {
                background: #007bff;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                text-decoration: none;
                display: inline-block;
            }
            .btn-view:hover {
                background: #0056b3;
                color: white;
            }
            .status-badge {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
            }
            .status-pending {
                background: #fff3cd;
                color: #856404;
            }
            .status-approved {
                background: #d4edda;
                color: #155724;
            }
            .status-rejected {
                background: #f8d7da;
                color: #721c24;
            }
            .bulk-actions-bar {
                margin: 20px 0;
                padding: 15px;
                background: white;
                border: 1px solid #ddd;
                display: flex;
                gap: 10px;
                align-items: center;
            }
        </style>

        <!-- Pending Claims Section -->
        <h2>Richieste in Attesa (<?php echo $pending_claims->found_posts; ?>)</h2>

        <?php if ( $pending_claims->have_posts() ) : ?>
            <form method="post">
                <?php wp_nonce_field( 'bulk-claims-action', 'bulk_claims_nonce' ); ?>

                <div class="bulk-actions-bar">
                    <input type="checkbox" id="select-all" onclick="
                        var checkboxes = document.querySelectorAll('.claim-checkbox');
                        checkboxes.forEach(cb => cb.checked = this.checked);
                    ">
                    <label for="select-all">Seleziona tutti</label>

                    <button type="submit" name="action" value="approve" class="btn-approve">
                        Approva Selezionati
                    </button>
                    <button type="submit" name="action" value="reject" class="btn-reject">
                        Rifiuta Selezionati
                    </button>
                </div>

                <table class="claims-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="select-all-header"></th>
                            <th>Struttura</th>
                            <th>Tipo</th>
                            <th>Utente</th>
                            <th>Data Richiesta</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ( $pending_claims->have_posts() ) :
                            $pending_claims->the_post();
                            $claim_id = get_the_ID();
                            $struttura_id = get_post_meta( $claim_id, '_struttura_id', true );
                            $struttura_type = get_post_meta( $claim_id, '_struttura_type', true );
                            $user_id = get_post_meta( $claim_id, '_user_id', true );
                            $user = get_userdata( $user_id );
                            $struttura = get_post( $struttura_id );
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="claims[]" value="<?php echo esc_attr( $claim_id ); ?>" class="claim-checkbox">
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $struttura ? $struttura->post_title : 'N/A' ); ?></strong>
                                </td>
                                <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $struttura_type ) ) ); ?></td>
                                <td><?php echo esc_html( $user ? $user->display_name : 'N/A' ); ?></td>
                                <td><?php echo get_the_date( 'd/m/Y H:i' ); ?></td>
                                <td>
                                    <span class="status-badge status-pending">In Attesa</span>
                                </td>
                                <td>
                                    <div class="claim-actions">
                                        <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $claim_id . '&action=edit' ) ); ?>" class="btn-view">
                                            Visualizza/Modifica
                                        </a>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'bulk-claims-action', 'bulk_claims_nonce' ); ?>
                                            <input type="hidden" name="claims[]" value="<?php echo esc_attr( $claim_id ); ?>">
                                            <button type="submit" name="action" value="approve" class="btn-approve">Approva</button>
                                            <button type="submit" name="action" value="reject" class="btn-reject">Rifiuta</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        <?php else : ?>
            <p>Nessuna richiesta in attesa.</p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

        <!-- Processed Claims Section -->
        <h2 style="margin-top: 40px;">Richieste Elaborate</h2>

        <?php if ( $processed_claims->have_posts() ) : ?>
            <table class="claims-table">
                <thead>
                    <tr>
                        <th>Struttura</th>
                        <th>Tipo</th>
                        <th>Utente</th>
                        <th>Data Elaborazione</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ( $processed_claims->have_posts() ) :
                        $processed_claims->the_post();
                        $claim_id = get_the_ID();
                        $struttura_id = get_post_meta( $claim_id, '_struttura_id', true );
                        $struttura_type = get_post_meta( $claim_id, '_struttura_type', true );
                        $user_id = get_post_meta( $claim_id, '_user_id', true );
                        $user = get_userdata( $user_id );
                        $struttura = get_post( $struttura_id );
                        $status = get_post_status();
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $struttura ? $struttura->post_title : 'N/A' ); ?></strong>
                            </td>
                            <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $struttura_type ) ) ); ?></td>
                            <td><?php echo esc_html( $user ? $user->display_name : 'N/A' ); ?></td>
                            <td><?php echo get_the_modified_date( 'd/m/Y H:i' ); ?></td>
                            <td>
                                <?php if ( $status === 'publish' ) : ?>
                                    <span class="status-badge status-approved">Approvato</span>
                                <?php else : ?>
                                    <span class="status-badge status-rejected">Rifiutato</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $claim_id . '&action=edit' ) ); ?>" class="btn-view">
                                    Visualizza Dati
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Nessuna richiesta elaborata.</p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>

    <script>
    document.getElementById('select-all-header').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.claim-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
    </script>
    <?php
}

/**
 * Approve claim and update structure data
 */
function caniincasa_approve_claim( $claim_id ) {
    $struttura_id = get_post_meta( $claim_id, '_struttura_id', true );

    if ( ! $struttura_id ) {
        return false;
    }

    // Get all claim data
    $claim_data = get_post_meta( $claim_id, '_claim_data', true );

    if ( ! $claim_data || ! is_array( $claim_data ) ) {
        return false;
    }

    // Update structure with new data
    foreach ( $claim_data as $field_key => $field_value ) {
        update_field( $field_key, $field_value, $struttura_id );
    }

    // Mark claim as approved
    wp_update_post( array(
        'ID'          => $claim_id,
        'post_status' => 'publish',
    ) );

    // Send notification email to user
    $user_id = get_post_meta( $claim_id, '_user_id', true );
    $user = get_userdata( $user_id );
    $struttura = get_post( $struttura_id );

    if ( $user && $struttura ) {
        $subject = 'Richiesta Approvata - ' . $struttura->post_title;
        $message = sprintf(
            "Ciao %s,\n\nLa tua richiesta di aggiornamento per la struttura '%s' è stata approvata!\n\nI nuovi dati sono ora visibili sul sito.\n\nGrazie,\nIl Team di %s",
            $user->display_name,
            $struttura->post_title,
            get_bloginfo( 'name' )
        );

        wp_mail( $user->user_email, $subject, $message );
    }

    return true;
}

/**
 * Reject claim
 */
function caniincasa_reject_claim( $claim_id ) {
    // Mark claim as rejected (trash)
    wp_trash_post( $claim_id );

    // Send notification email to user
    $user_id = get_post_meta( $claim_id, '_user_id', true );
    $struttura_id = get_post_meta( $claim_id, '_struttura_id', true );
    $user = get_userdata( $user_id );
    $struttura = get_post( $struttura_id );

    if ( $user && $struttura ) {
        $subject = 'Richiesta Non Approvata - ' . $struttura->post_title;
        $message = sprintf(
            "Ciao %s,\n\nLa tua richiesta di aggiornamento per la struttura '%s' non è stata approvata.\n\nSe hai domande, contattaci.\n\nGrazie,\nIl Team di %s",
            $user->display_name,
            $struttura->post_title,
            get_bloginfo( 'name' )
        );

        wp_mail( $user->user_email, $subject, $message );
    }

    return true;
}

/**
 * Submit a new claim (called from frontend)
 */
function caniincasa_submit_structure_claim( $struttura_id, $struttura_type, $claim_data ) {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $user_id = get_current_user_id();

    // Create claim post
    $claim_id = wp_insert_post( array(
        'post_type'   => 'strutture_claims',
        'post_title'  => 'Richiesta aggiornamento - ' . get_the_title( $struttura_id ),
        'post_status' => 'pending',
        'post_author' => $user_id,
    ) );

    if ( is_wp_error( $claim_id ) ) {
        return false;
    }

    // Save metadata
    update_post_meta( $claim_id, '_struttura_id', $struttura_id );
    update_post_meta( $claim_id, '_struttura_type', $struttura_type );
    update_post_meta( $claim_id, '_user_id', $user_id );
    update_post_meta( $claim_id, '_claim_data', $claim_data );

    // Send notification to admin
    $admin_email = get_option( 'admin_email' );
    $struttura = get_post( $struttura_id );
    $user = wp_get_current_user();

    $subject = 'Nuova Richiesta Aggiornamento Struttura';
    $message = sprintf(
        "Una nuova richiesta di aggiornamento è stata inviata.\n\nStruttura: %s\nTipo: %s\nUtente: %s (%s)\n\nVisualizza e gestisci la richiesta: %s",
        $struttura->post_title,
        $struttura_type,
        $user->display_name,
        $user->user_email,
        admin_url( 'admin.php?page=strutture-claims' )
    );

    wp_mail( $admin_email, $subject, $message );

    return $claim_id;
}
