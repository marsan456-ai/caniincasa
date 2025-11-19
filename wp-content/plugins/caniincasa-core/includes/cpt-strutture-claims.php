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

/**
 * Add metabox to display claim data
 */
function caniincasa_add_claim_metaboxes() {
    add_meta_box(
        'claim_data_metabox',
        'Dati Richiesta Aggiornamento',
        'caniincasa_render_claim_data_metabox',
        'strutture_claims',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'caniincasa_add_claim_metaboxes' );

/**
 * Render claim data metabox
 */
function caniincasa_render_claim_data_metabox( $post ) {
    $claim_id = $post->ID;
    $struttura_id = get_post_meta( $claim_id, '_struttura_id', true );
    $struttura_type = get_post_meta( $claim_id, '_struttura_type', true );
    $user_id = get_post_meta( $claim_id, '_user_id', true );
    $claim_data = get_post_meta( $claim_id, '_claim_data', true );

    $struttura = get_post( $struttura_id );
    $user = get_userdata( $user_id );

    if ( ! $struttura || ! $claim_data ) {
        echo '<p>Dati della richiesta non disponibili.</p>';
        return;
    }
    ?>
    <style>
        .claim-info-box {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .claim-comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .claim-comparison-table th {
            background: #f1f3f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .claim-comparison-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .claim-comparison-table tr:hover {
            background: #f8f9fa;
        }
        .field-label {
            font-weight: 600;
            color: #495057;
        }
        .original-value {
            color: #6c757d;
        }
        .new-value {
            color: #007bff;
            font-weight: 600;
        }
        .value-changed {
            background: #fff3cd;
        }
        .value-empty {
            color: #adb5bd;
            font-style: italic;
        }
        .claim-actions-box {
            background: #fff;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
        }
        .btn-approve-claim {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-approve-claim:hover {
            background: #218838;
        }
        .btn-reject-claim {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-reject-claim:hover {
            background: #c82333;
        }
    </style>

    <!-- Informazioni Generali -->
    <div class="claim-info-box">
        <h3 style="margin-top: 0;">Informazioni Richiesta</h3>
        <p><strong>Struttura:</strong> <?php echo esc_html( $struttura->post_title ); ?>
           <a href="<?php echo get_edit_post_link( $struttura_id ); ?>" target="_blank">(Modifica Struttura)</a>
           <a href="<?php echo get_permalink( $struttura_id ); ?>" target="_blank">(Visualizza)</a>
        </p>
        <p><strong>Tipo:</strong> <?php echo esc_html( ucfirst( str_replace( '_', ' ', $struttura_type ) ) ); ?></p>
        <p><strong>Richiesta da:</strong> <?php echo $user ? esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')' : 'Utente sconosciuto'; ?></p>
        <p><strong>Data Richiesta:</strong> <?php echo get_the_date( 'd/m/Y H:i', $claim_id ); ?></p>
    </div>

    <!-- Tabella Confronto Dati -->
    <h3>Dati Proposti vs Dati Attuali</h3>
    <table class="claim-comparison-table">
        <thead>
            <tr>
                <th style="width: 30%;">Campo</th>
                <th style="width: 35%;">Valore Attuale</th>
                <th style="width: 35%;">Valore Proposto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Get all ACF field labels for better display
            $field_labels = array(
                'telefono' => 'Telefono',
                'email' => 'Email',
                'indirizzo' => 'Indirizzo',
                'citta' => 'Città',
                'provincia' => 'Provincia',
                'cap' => 'CAP',
                'sito_web' => 'Sito Web',
                'orari_apertura' => 'Orari Apertura',
                'descrizione' => 'Descrizione',
                'servizi' => 'Servizi',
                'prezzi' => 'Prezzi',
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'whatsapp' => 'WhatsApp',
                'youtube' => 'YouTube',
                'razze_allevate' => 'Razze Allevate',
                'specializzazioni' => 'Specializzazioni',
                'certificazioni' => 'Certificazioni',
            );

            foreach ( $claim_data as $field_key => $new_value ) :
                $field_label = isset( $field_labels[ $field_key ] ) ? $field_labels[ $field_key ] : ucfirst( str_replace( '_', ' ', $field_key ) );
                $current_value = get_field( $field_key, $struttura_id );

                // Convert arrays to readable format
                if ( is_array( $new_value ) ) {
                    $new_value = implode( ', ', $new_value );
                }
                if ( is_array( $current_value ) ) {
                    $current_value = implode( ', ', $current_value );
                }

                // Check if value changed
                $is_changed = ( $new_value != $current_value );
                $row_class = $is_changed ? 'value-changed' : '';
                ?>
                <tr class="<?php echo esc_attr( $row_class ); ?>">
                    <td class="field-label"><?php echo esc_html( $field_label ); ?></td>
                    <td class="original-value">
                        <?php
                        if ( empty( $current_value ) ) {
                            echo '<span class="value-empty">(vuoto)</span>';
                        } else {
                            echo esc_html( $current_value );
                        }
                        ?>
                    </td>
                    <td class="new-value">
                        <?php
                        if ( empty( $new_value ) ) {
                            echo '<span class="value-empty">(vuoto)</span>';
                        } else {
                            echo esc_html( $new_value );
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Azioni Rapide -->
    <?php if ( get_post_status( $claim_id ) === 'pending' ) : ?>
    <div class="claim-actions-box">
        <form method="post" style="display: inline;">
            <?php wp_nonce_field( 'approve-claim-' . $claim_id, 'approve_nonce' ); ?>
            <input type="hidden" name="claim_id" value="<?php echo esc_attr( $claim_id ); ?>">
            <button type="submit" name="action" value="approve_single" class="btn-approve-claim" onclick="return confirm('Sei sicuro di voler approvare questa richiesta? I dati della struttura verranno aggiornati.');">
                ✓ Approva Richiesta
            </button>
        </form>
        <form method="post" style="display: inline;">
            <?php wp_nonce_field( 'reject-claim-' . $claim_id, 'reject_nonce' ); ?>
            <input type="hidden" name="claim_id" value="<?php echo esc_attr( $claim_id ); ?>">
            <button type="submit" name="action" value="reject_single" class="btn-reject-claim" onclick="return confirm('Sei sicuro di voler rifiutare questa richiesta?');">
                ✗ Rifiuta Richiesta
            </button>
        </form>
    </div>
    <?php else : ?>
    <div class="claim-info-box" style="border-left-color: <?php echo get_post_status( $claim_id ) === 'publish' ? '#28a745' : '#dc3545'; ?>;">
        <p><strong>Stato:</strong> <?php echo get_post_status( $claim_id ) === 'publish' ? 'Approvata' : 'Rifiutata'; ?></p>
        <p><strong>Data Elaborazione:</strong> <?php echo get_the_modified_date( 'd/m/Y H:i', $claim_id ); ?></p>
    </div>
    <?php endif; ?>
    <?php
}

/**
 * Handle single claim approve/reject from metabox
 */
function caniincasa_handle_single_claim_action() {
    if ( ! isset( $_POST['action'] ) || ! isset( $_POST['claim_id'] ) ) {
        return;
    }

    $claim_id = absint( $_POST['claim_id'] );
    $action = sanitize_text_field( $_POST['action'] );

    if ( $action === 'approve_single' && check_admin_referer( 'approve-claim-' . $claim_id, 'approve_nonce' ) ) {
        caniincasa_approve_claim( $claim_id );
        wp_redirect( admin_url( 'admin.php?page=strutture-claims' ) );
        exit;
    } elseif ( $action === 'reject_single' && check_admin_referer( 'reject-claim-' . $claim_id, 'reject_nonce' ) ) {
        caniincasa_reject_claim( $claim_id );
        wp_redirect( admin_url( 'admin.php?page=strutture-claims' ) );
        exit;
    }
}
add_action( 'admin_init', 'caniincasa_handle_single_claim_action' );
