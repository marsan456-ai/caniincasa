<?php
/**
 * Stories System - Storie di Cani
 *
 * Sistema completo per storie utente con:
 * - Toggle attivazione/disattivazione da admin
 * - CPT con moderazione
 * - Form invio da frontend
 * - Notifiche email cambio stato
 *
 * @package Caniincasa
 * @since 1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Caniincasa_Stories_System
 */
class Caniincasa_Stories_System {

    /**
     * Option name for enabling/disabling stories
     */
    const OPTION_ENABLED = 'caniincasa_stories_enabled';

    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Admin settings
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Only load if enabled
        if ( self::is_enabled() ) {
            add_action( 'init', array( $this, 'register_cpt' ) );
            add_action( 'init', array( $this, 'register_taxonomy' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post_storie_cani', array( $this, 'save_meta_boxes' ) );
            add_action( 'transition_post_status', array( $this, 'handle_status_change' ), 10, 3 );

            // Frontend submission
            add_action( 'wp_ajax_submit_story', array( $this, 'ajax_submit_story' ) );

            // Admin columns
            add_filter( 'manage_storie_cani_posts_columns', array( $this, 'admin_columns' ) );
            add_action( 'manage_storie_cani_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );

            // Enqueue assets
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        }
    }

    /**
     * Check if stories system is enabled
     */
    public static function is_enabled() {
        return get_option( self::OPTION_ENABLED, false );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Storie di Cani',
            'Storie di Cani',
            'manage_options',
            'caniincasa-stories',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'caniincasa_stories_settings', self::OPTION_ENABLED );
        register_setting( 'caniincasa_stories_settings', 'caniincasa_stories_moderation_email' );
        register_setting( 'caniincasa_stories_settings', 'caniincasa_stories_auto_approve' );
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        $enabled = self::is_enabled();
        $moderation_email = get_option( 'caniincasa_stories_moderation_email', get_option( 'admin_email' ) );
        $auto_approve = get_option( 'caniincasa_stories_auto_approve', false );

        // Count stats
        $total_stories = wp_count_posts( 'storie_cani' );
        $pending_count = isset( $total_stories->pending ) ? $total_stories->pending : 0;
        $published_count = isset( $total_stories->publish ) ? $total_stories->publish : 0;
        ?>
        <div class="wrap">
            <h1>Storie di Cani - Impostazioni</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'caniincasa_stories_settings' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">Stato Sistema</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo self::OPTION_ENABLED; ?>" value="1" <?php checked( $enabled ); ?>>
                                <strong>Attiva sezione Storie di Cani</strong>
                            </label>
                            <p class="description">
                                Attiva o disattiva l'intera sezione. Se disattivata, le storie esistenti rimangono nel database ma non sono visibili.
                            </p>
                        </td>
                    </tr>

                    <?php if ( $enabled ) : ?>
                    <tr>
                        <th scope="row">Email Moderazione</th>
                        <td>
                            <input type="email" name="caniincasa_stories_moderation_email" value="<?php echo esc_attr( $moderation_email ); ?>" class="regular-text">
                            <p class="description">Email per notifiche nuove storie da moderare.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Approvazione Automatica</th>
                        <td>
                            <label>
                                <input type="checkbox" name="caniincasa_stories_auto_approve" value="1" <?php checked( $auto_approve ); ?>>
                                Approva automaticamente le storie (non consigliato)
                            </label>
                            <p class="description">Se attivo, le storie vengono pubblicate immediatamente senza moderazione.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>

                <?php submit_button( 'Salva Impostazioni' ); ?>
            </form>

            <?php if ( $enabled ) : ?>
            <hr>
            <h2>Statistiche</h2>
            <table class="widefat" style="max-width: 400px;">
                <tr>
                    <td><strong>Storie Pubblicate</strong></td>
                    <td><?php echo $published_count; ?></td>
                </tr>
                <tr>
                    <td><strong>In Attesa di Moderazione</strong></td>
                    <td>
                        <?php echo $pending_count; ?>
                        <?php if ( $pending_count > 0 ) : ?>
                            <a href="<?php echo admin_url( 'edit.php?post_type=storie_cani&post_status=pending' ); ?>" class="button button-small">Modera</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Register Custom Post Type
     */
    public function register_cpt() {
        $labels = array(
            'name'               => 'Storie di Cani',
            'singular_name'      => 'Storia',
            'menu_name'          => 'Storie di Cani',
            'add_new'            => 'Aggiungi Storia',
            'add_new_item'       => 'Aggiungi Nuova Storia',
            'edit_item'          => 'Modifica Storia',
            'new_item'           => 'Nuova Storia',
            'view_item'          => 'Visualizza Storia',
            'search_items'       => 'Cerca Storie',
            'not_found'          => 'Nessuna storia trovata',
            'not_found_in_trash' => 'Nessuna storia nel cestino',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'storie' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-heart',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'storie_cani', $args );
    }

    /**
     * Register Taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => 'Categorie Storie',
            'singular_name'     => 'Categoria Storia',
            'search_items'      => 'Cerca Categorie',
            'all_items'         => 'Tutte le Categorie',
            'edit_item'         => 'Modifica Categoria',
            'update_item'       => 'Aggiorna Categoria',
            'add_new_item'      => 'Aggiungi Categoria',
            'new_item_name'     => 'Nome Nuova Categoria',
            'menu_name'         => 'Categorie',
        );

        register_taxonomy( 'categoria_storia', 'storie_cani', array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'storie/categoria' ),
            'show_in_rest'      => true,
        ) );

        // Add default categories if not exist
        $default_categories = array(
            'adozione'       => 'Adozione',
            'vita-insieme'   => 'Vita Insieme',
            'trasformazione' => 'Trasformazione',
            'in-memoria'     => 'In Memoria',
            'avventure'      => 'Avventure',
        );

        foreach ( $default_categories as $slug => $name ) {
            if ( ! term_exists( $slug, 'categoria_storia' ) ) {
                wp_insert_term( $name, 'categoria_storia', array( 'slug' => $slug ) );
            }
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'storia_details',
            'Dettagli Storia',
            array( $this, 'render_details_meta_box' ),
            'storie_cani',
            'side',
            'high'
        );

        add_meta_box(
            'storia_dog_info',
            'Informazioni Cane',
            array( $this, 'render_dog_info_meta_box' ),
            'storie_cani',
            'normal',
            'high'
        );

        add_meta_box(
            'storia_gallery',
            'Galleria Immagini',
            array( $this, 'render_gallery_meta_box' ),
            'storie_cani',
            'normal',
            'default'
        );

        add_meta_box(
            'storia_moderation',
            'Moderazione',
            array( $this, 'render_moderation_meta_box' ),
            'storie_cani',
            'side',
            'default'
        );
    }

    /**
     * Render details meta box
     */
    public function render_details_meta_box( $post ) {
        wp_nonce_field( 'storia_meta_nonce', 'storia_meta_nonce' );
        $author_display = get_post_meta( $post->ID, '_storia_author_display', true );
        ?>
        <p>
            <label for="storia_author_display"><strong>Visualizzazione Autore:</strong></label><br>
            <select name="storia_author_display" id="storia_author_display" style="width: 100%;">
                <option value="name" <?php selected( $author_display, 'name' ); ?>>Mostra nome utente</option>
                <option value="anonymous" <?php selected( $author_display, 'anonymous' ); ?>>Anonimo</option>
            </select>
        </p>
        <?php
    }

    /**
     * Render dog info meta box
     */
    public function render_dog_info_meta_box( $post ) {
        $dog_name = get_post_meta( $post->ID, '_storia_dog_name', true );
        $dog_breed = get_post_meta( $post->ID, '_storia_dog_breed', true );
        $dog_age = get_post_meta( $post->ID, '_storia_dog_age', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="storia_dog_name">Nome del Cane</label></th>
                <td><input type="text" id="storia_dog_name" name="storia_dog_name" value="<?php echo esc_attr( $dog_name ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="storia_dog_breed">Razza</label></th>
                <td>
                    <select name="storia_dog_breed" id="storia_dog_breed" class="regular-text">
                        <option value="">Seleziona...</option>
                        <option value="meticcio" <?php selected( $dog_breed, 'meticcio' ); ?>>Meticcio</option>
                        <?php
                        $breeds = get_posts( array(
                            'post_type'      => 'razze_di_cani',
                            'posts_per_page' => -1,
                            'orderby'        => 'title',
                            'order'          => 'ASC',
                        ) );
                        foreach ( $breeds as $breed ) :
                        ?>
                            <option value="<?php echo esc_attr( $breed->post_title ); ?>" <?php selected( $dog_breed, $breed->post_title ); ?>>
                                <?php echo esc_html( $breed->post_title ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="storia_dog_age">Età</label></th>
                <td><input type="text" id="storia_dog_age" name="storia_dog_age" value="<?php echo esc_attr( $dog_age ); ?>" class="regular-text" placeholder="Es. 3 anni"></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render gallery meta box
     */
    public function render_gallery_meta_box( $post ) {
        $gallery = get_post_meta( $post->ID, '_storia_gallery', true );
        ?>
        <p>
            <label><strong>Immagini aggiuntive (oltre all'immagine in evidenza):</strong></label>
        </p>
        <div id="storia-gallery-container">
            <?php if ( $gallery && is_array( $gallery ) ) : ?>
                <?php foreach ( $gallery as $image_id ) : ?>
                    <div class="gallery-item" style="display: inline-block; margin: 5px;">
                        <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
                        <input type="hidden" name="storia_gallery[]" value="<?php echo esc_attr( $image_id ); ?>">
                        <br><a href="#" class="remove-gallery-image">Rimuovi</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p>
            <button type="button" class="button" id="add-gallery-images">Aggiungi Immagini</button>
        </p>
        <script>
        jQuery(document).ready(function($) {
            $('#add-gallery-images').on('click', function(e) {
                e.preventDefault();
                var frame = wp.media({
                    title: 'Seleziona Immagini',
                    multiple: true,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var attachments = frame.state().get('selection').toJSON();
                    attachments.forEach(function(attachment) {
                        $('#storia-gallery-container').append(
                            '<div class="gallery-item" style="display: inline-block; margin: 5px;">' +
                            '<img src="' + attachment.sizes.thumbnail.url + '" />' +
                            '<input type="hidden" name="storia_gallery[]" value="' + attachment.id + '">' +
                            '<br><a href="#" class="remove-gallery-image">Rimuovi</a></div>'
                        );
                    });
                });
                frame.open();
            });
            $(document).on('click', '.remove-gallery-image', function(e) {
                e.preventDefault();
                $(this).closest('.gallery-item').remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Render moderation meta box
     */
    public function render_moderation_meta_box( $post ) {
        $rejection_reason = get_post_meta( $post->ID, '_storia_rejection_reason', true );
        $submitted_date = get_post_meta( $post->ID, '_storia_submitted_date', true );
        ?>
        <?php if ( $submitted_date ) : ?>
            <p><strong>Data invio:</strong> <?php echo date_i18n( 'd/m/Y H:i', strtotime( $submitted_date ) ); ?></p>
        <?php endif; ?>

        <?php if ( $post->post_status === 'pending' ) : ?>
            <p style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;">
                <strong>In attesa di moderazione</strong>
            </p>
        <?php endif; ?>

        <p>
            <label for="storia_rejection_reason"><strong>Motivo rifiuto (se applicabile):</strong></label>
            <textarea name="storia_rejection_reason" id="storia_rejection_reason" rows="3" style="width: 100%;"><?php echo esc_textarea( $rejection_reason ); ?></textarea>
            <small>Verrà inviato all'utente se la storia viene rifiutata.</small>
        </p>

        <?php if ( $post->post_status === 'pending' ) : ?>
            <p>
                <button type="submit" name="approve_story" value="1" class="button button-primary">Approva e Pubblica</button>
                <button type="submit" name="reject_story" value="1" class="button" style="color: #d63638;">Rifiuta</button>
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['storia_meta_nonce'] ) || ! wp_verify_nonce( $_POST['storia_meta_nonce'], 'storia_meta_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save fields
        $fields = array(
            'storia_author_display' => '_storia_author_display',
            'storia_dog_name'       => '_storia_dog_name',
            'storia_dog_breed'      => '_storia_dog_breed',
            'storia_dog_age'        => '_storia_dog_age',
            'storia_rejection_reason' => '_storia_rejection_reason',
        );

        foreach ( $fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $post_key ] ) );
            }
        }

        // Save gallery
        if ( isset( $_POST['storia_gallery'] ) ) {
            $gallery = array_map( 'absint', $_POST['storia_gallery'] );
            update_post_meta( $post_id, '_storia_gallery', $gallery );
        } else {
            delete_post_meta( $post_id, '_storia_gallery' );
        }

        // Handle approve/reject buttons
        if ( isset( $_POST['approve_story'] ) ) {
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'publish',
            ) );
        }

        if ( isset( $_POST['reject_story'] ) ) {
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'draft',
            ) );
            update_post_meta( $post_id, '_storia_rejected', true );
            $this->send_rejection_email( $post_id );
        }
    }

    /**
     * Handle status change - send notifications
     */
    public function handle_status_change( $new_status, $old_status, $post ) {
        if ( $post->post_type !== 'storie_cani' ) {
            return;
        }

        // New submission (pending)
        if ( $new_status === 'pending' && $old_status !== 'pending' ) {
            $this->send_moderation_notification( $post->ID );
        }

        // Approved (published)
        if ( $new_status === 'publish' && $old_status === 'pending' ) {
            $this->send_approval_email( $post->ID );
        }
    }

    /**
     * Send moderation notification to admin
     */
    private function send_moderation_notification( $post_id ) {
        $post = get_post( $post_id );
        $admin_email = get_option( 'caniincasa_stories_moderation_email', get_option( 'admin_email' ) );
        $author = get_userdata( $post->post_author );

        $subject = '[Caniincasa] Nuova storia da moderare: ' . $post->post_title;
        $message = "È stata inviata una nuova storia da moderare.\n\n";
        $message .= "Titolo: " . $post->post_title . "\n";
        $message .= "Autore: " . $author->display_name . " (" . $author->user_email . ")\n\n";
        $message .= "Modera qui: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";

        wp_mail( $admin_email, $subject, $message );
    }

    /**
     * Send approval email to user
     */
    private function send_approval_email( $post_id ) {
        $post = get_post( $post_id );
        $author = get_userdata( $post->post_author );

        if ( ! $author ) {
            return;
        }

        $subject = 'La tua storia è stata pubblicata! - Caniincasa';
        $message = "Ciao " . $author->display_name . ",\n\n";
        $message .= "La tua storia \"" . $post->post_title . "\" è stata approvata e pubblicata!\n\n";
        $message .= "Puoi visualizzarla qui: " . get_permalink( $post_id ) . "\n\n";
        $message .= "Grazie per aver condiviso la tua storia con la community!\n\n";
        $message .= "Il team di Caniincasa";

        wp_mail( $author->user_email, $subject, $message );
    }

    /**
     * Send rejection email to user
     */
    private function send_rejection_email( $post_id ) {
        $post = get_post( $post_id );
        $author = get_userdata( $post->post_author );
        $reason = get_post_meta( $post_id, '_storia_rejection_reason', true );

        if ( ! $author ) {
            return;
        }

        $subject = 'Informazioni sulla tua storia - Caniincasa';
        $message = "Ciao " . $author->display_name . ",\n\n";
        $message .= "Purtroppo la tua storia \"" . $post->post_title . "\" non può essere pubblicata.\n\n";
        if ( $reason ) {
            $message .= "Motivo: " . $reason . "\n\n";
        }
        $message .= "Puoi modificarla e inviarla nuovamente dalla tua dashboard.\n\n";
        $message .= "Il team di Caniincasa";

        wp_mail( $author->user_email, $subject, $message );
    }

    /**
     * Admin columns
     */
    public function admin_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( $key === 'title' ) {
                $new_columns['dog_name'] = 'Nome Cane';
                $new_columns['dog_breed'] = 'Razza';
            }
        }
        return $new_columns;
    }

    /**
     * Admin column content
     */
    public function admin_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'dog_name':
                echo esc_html( get_post_meta( $post_id, '_storia_dog_name', true ) );
                break;
            case 'dog_breed':
                echo esc_html( get_post_meta( $post_id, '_storia_dog_breed', true ) );
                break;
        }
    }

    /**
     * AJAX: Submit story from frontend
     */
    public function ajax_submit_story() {
        check_ajax_referer( 'caniincasa_stories', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Devi essere loggato per inviare una storia.' ) );
        }

        $user_id = get_current_user_id();

        // Validate required fields
        $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
        $content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
        $dog_name = isset( $_POST['dog_name'] ) ? sanitize_text_field( $_POST['dog_name'] ) : '';
        $category = isset( $_POST['category'] ) ? absint( $_POST['category'] ) : 0;

        if ( empty( $title ) || empty( $content ) || empty( $dog_name ) ) {
            wp_send_json_error( array( 'message' => 'Compila tutti i campi obbligatori.' ) );
        }

        // Determine post status
        $auto_approve = get_option( 'caniincasa_stories_auto_approve', false );
        $post_status = $auto_approve ? 'publish' : 'pending';

        // Create post
        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => 'storie_cani',
            'post_author'  => $user_id,
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Errore durante il salvataggio.' ) );
        }

        // Save meta
        update_post_meta( $post_id, '_storia_dog_name', $dog_name );
        update_post_meta( $post_id, '_storia_dog_breed', sanitize_text_field( $_POST['dog_breed'] ?? '' ) );
        update_post_meta( $post_id, '_storia_dog_age', sanitize_text_field( $_POST['dog_age'] ?? '' ) );
        update_post_meta( $post_id, '_storia_author_display', sanitize_text_field( $_POST['author_display'] ?? 'name' ) );
        update_post_meta( $post_id, '_storia_submitted_date', current_time( 'mysql' ) );

        // Set category
        if ( $category ) {
            wp_set_object_terms( $post_id, $category, 'categoria_storia' );
        }

        // Handle featured image
        if ( ! empty( $_FILES['featured_image']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_id = media_handle_upload( 'featured_image', $post_id );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        // Handle gallery images
        if ( ! empty( $_FILES['gallery'] ) ) {
            $gallery_ids = array();
            $files = $_FILES['gallery'];

            for ( $i = 0; $i < count( $files['name'] ); $i++ ) {
                if ( $files['error'][ $i ] === UPLOAD_ERR_OK ) {
                    $_FILES['upload_file'] = array(
                        'name'     => $files['name'][ $i ],
                        'type'     => $files['type'][ $i ],
                        'tmp_name' => $files['tmp_name'][ $i ],
                        'error'    => $files['error'][ $i ],
                        'size'     => $files['size'][ $i ],
                    );
                    $attachment_id = media_handle_upload( 'upload_file', $post_id );
                    if ( ! is_wp_error( $attachment_id ) ) {
                        $gallery_ids[] = $attachment_id;
                    }
                }
            }

            if ( ! empty( $gallery_ids ) ) {
                update_post_meta( $post_id, '_storia_gallery', $gallery_ids );
            }
        }

        $message = $auto_approve
            ? 'Storia pubblicata con successo!'
            : 'Storia inviata con successo! Sarà visibile dopo l\'approvazione.';

        wp_send_json_success( array(
            'message' => $message,
            'post_id' => $post_id,
        ) );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if ( is_post_type_archive( 'storie_cani' ) || is_singular( 'storie_cani' ) ) {
            wp_enqueue_style( 'caniincasa-stories', get_template_directory_uri() . '/assets/css/stories.css', array(), CANIINCASA_VERSION );
        }
    }

    /**
     * Get submission form HTML (for dashboard)
     */
    public static function get_submission_form() {
        if ( ! self::is_enabled() ) {
            return '<p>La sezione Storie non è attualmente disponibile.</p>';
        }

        if ( ! is_user_logged_in() ) {
            return '<p>Devi essere loggato per inviare una storia.</p>';
        }

        $categories = get_terms( array(
            'taxonomy'   => 'categoria_storia',
            'hide_empty' => false,
        ) );

        $breeds = get_posts( array(
            'post_type'      => 'razze_di_cani',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        ob_start();
        ?>
        <div class="story-submission-form">
            <h3>Condividi la Tua Storia</h3>
            <p class="form-intro">Racconta la storia del tuo cane e condividila con la community!</p>

            <form id="submit-story-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'caniincasa_stories', 'stories_nonce' ); ?>

                <div class="form-group">
                    <label for="story-title">Titolo della Storia *</label>
                    <input type="text" id="story-title" name="title" required placeholder="Es. Come Fido ha cambiato la mia vita">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="story-dog-name">Nome del Cane *</label>
                        <input type="text" id="story-dog-name" name="dog_name" required>
                    </div>
                    <div class="form-group">
                        <label for="story-dog-breed">Razza</label>
                        <select id="story-dog-breed" name="dog_breed">
                            <option value="">Seleziona...</option>
                            <option value="meticcio">Meticcio</option>
                            <?php foreach ( $breeds as $breed ) : ?>
                                <option value="<?php echo esc_attr( $breed->post_title ); ?>">
                                    <?php echo esc_html( $breed->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="story-dog-age">Età</label>
                        <input type="text" id="story-dog-age" name="dog_age" placeholder="Es. 3 anni">
                    </div>
                </div>

                <div class="form-group">
                    <label for="story-category">Categoria *</label>
                    <select id="story-category" name="category" required>
                        <option value="">Seleziona una categoria...</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->term_id ); ?>">
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="story-content">La Tua Storia *</label>
                    <textarea id="story-content" name="content" rows="10" required placeholder="Racconta la storia del tuo cane..."></textarea>
                </div>

                <div class="form-group">
                    <label for="story-featured-image">Foto Principale *</label>
                    <input type="file" id="story-featured-image" name="featured_image" accept="image/*" required>
                    <small>Formato: JPG, PNG. Max 5MB.</small>
                </div>

                <div class="form-group">
                    <label for="story-gallery">Altre Foto (max 4)</label>
                    <input type="file" id="story-gallery" name="gallery[]" accept="image/*" multiple>
                    <small>Puoi selezionare fino a 4 foto aggiuntive.</small>
                </div>

                <div class="form-group">
                    <label for="story-author-display">Visualizzazione Autore</label>
                    <select id="story-author-display" name="author_display">
                        <option value="name">Mostra il mio nome</option>
                        <option value="anonymous">Pubblica in modo anonimo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="accept_terms" required>
                        Accetto che la storia venga pubblicata dopo moderazione
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submit-story-btn">
                        Invia Storia
                    </button>
                </div>

                <div class="form-message" id="story-form-message" style="display: none;"></div>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#submit-story-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $('#submit-story-btn');
                var $message = $('#story-form-message');

                $btn.prop('disabled', true).text('Invio in corso...');
                $message.hide();

                var formData = new FormData(this);
                formData.append('action', 'submit_story');
                formData.append('nonce', $('input[name="stories_nonce"]').val());

                $.ajax({
                    url: caniincasaAjax.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $message.removeClass('error').addClass('success').html(response.data.message).show();
                            $form[0].reset();
                        } else {
                            $message.removeClass('success').addClass('error').html(response.data.message).show();
                        }
                    },
                    error: function() {
                        $message.removeClass('success').addClass('error').html('Errore di connessione. Riprova.').show();
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Invia Storia');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Get user's stories for dashboard
     */
    public static function get_user_stories( $user_id = null ) {
        if ( ! self::is_enabled() ) {
            return array();
        }

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        return get_posts( array(
            'post_type'      => 'storie_cani',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'pending', 'draft' ),
        ) );
    }
}

// Initialize
Caniincasa_Stories_System::get_instance();

/**
 * Helper function to check if stories are enabled
 */
function caniincasa_stories_enabled() {
    return Caniincasa_Stories_System::is_enabled();
}

/**
 * Helper function to get submission form
 */
function caniincasa_get_story_submission_form() {
    return Caniincasa_Stories_System::get_submission_form();
}

/**
 * Helper function to get user stories
 */
function caniincasa_get_user_stories( $user_id = null ) {
    return Caniincasa_Stories_System::get_user_stories( $user_id );
}
