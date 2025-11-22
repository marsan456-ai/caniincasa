<?php
/**
 * TinyMCE Button: Razze Grid Generator
 *
 * Aggiunge un pulsante all'editor classico per generare
 * shortcode [razze_grid] con ricerca razze.
 *
 * @package Caniincasa
 * @since 1.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Caniincasa_Razze_Grid_Editor
 */
class Caniincasa_Razze_Grid_Editor {

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
        // Solo per utenti che possono editare
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        // Solo se l'editor visuale è abilitato
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }

        add_action( 'admin_head', array( $this, 'add_editor_styles' ) );
        add_action( 'admin_footer', array( $this, 'add_modal_html' ) );
        add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
        add_filter( 'mce_buttons', array( $this, 'register_tinymce_button' ) );
        add_action( 'wp_ajax_search_razze_for_grid', array( $this, 'ajax_search_razze' ) );
    }

    /**
     * Register TinyMCE button
     */
    public function register_tinymce_button( $buttons ) {
        array_push( $buttons, 'razze_grid_button' );
        return $buttons;
    }

    /**
     * Add TinyMCE plugin
     */
    public function add_tinymce_plugin( $plugins ) {
        $plugins['razze_grid_button'] = CANIINCASA_THEME_URI . '/assets/js/tinymce-razze-grid.js';
        return $plugins;
    }

    /**
     * Add editor styles
     */
    public function add_editor_styles() {
        ?>
        <style>
            /* Modal Overlay */
            .razze-grid-modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                z-index: 100100;
                justify-content: center;
                align-items: center;
            }
            .razze-grid-modal-overlay.active {
                display: flex;
            }

            /* Modal */
            .razze-grid-modal {
                background: #fff;
                border-radius: 8px;
                width: 90%;
                max-width: 700px;
                max-height: 85vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            }

            /* Header */
            .razze-grid-modal-header {
                padding: 20px;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .razze-grid-modal-header h2 {
                margin: 0;
                font-size: 18px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .razze-grid-modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #666;
                padding: 0;
                line-height: 1;
            }
            .razze-grid-modal-close:hover {
                color: #d63638;
            }

            /* Body */
            .razze-grid-modal-body {
                padding: 20px;
                overflow-y: auto;
                flex: 1;
            }

            /* Search */
            .razze-search-box {
                margin-bottom: 15px;
            }
            .razze-search-box input {
                width: 100%;
                padding: 10px 15px;
                font-size: 14px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .razze-search-box input:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 0 1px #2271b1;
            }

            /* Results */
            .razze-search-results {
                max-height: 250px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .razze-search-item {
                padding: 10px 15px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                transition: background 0.2s;
            }
            .razze-search-item:last-child {
                border-bottom: none;
            }
            .razze-search-item:hover {
                background: #f0f6fc;
            }
            .razze-search-item img {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border-radius: 4px;
            }
            .razze-search-item-info {
                flex: 1;
            }
            .razze-search-item-name {
                font-weight: 600;
                color: #1d2327;
            }
            .razze-search-item-meta {
                font-size: 12px;
                color: #666;
            }
            .razze-search-item-add {
                background: #2271b1;
                color: #fff;
                border: none;
                padding: 5px 12px;
                border-radius: 3px;
                font-size: 12px;
                cursor: pointer;
            }
            .razze-search-item-add:hover {
                background: #135e96;
            }
            .razze-search-loading,
            .razze-search-empty {
                padding: 20px;
                text-align: center;
                color: #666;
            }

            /* Selected */
            .razze-selected-section h4 {
                margin: 0 0 10px;
                font-size: 14px;
                color: #1d2327;
            }
            .razze-selected-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                min-height: 40px;
                padding: 10px;
                background: #f6f7f7;
                border-radius: 4px;
                margin-bottom: 15px;
            }
            .razze-selected-item {
                display: flex;
                align-items: center;
                gap: 5px;
                background: #2271b1;
                color: #fff;
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 13px;
            }
            .razze-selected-item .remove {
                cursor: pointer;
                font-weight: bold;
                margin-left: 5px;
            }
            .razze-selected-item .remove:hover {
                color: #ffcccc;
            }
            .razze-selected-empty {
                color: #666;
                font-style: italic;
                font-size: 13px;
            }

            /* Options */
            .razze-grid-options {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                margin-bottom: 15px;
            }
            .razze-grid-option label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
                font-size: 13px;
            }
            .razze-grid-option input,
            .razze-grid-option select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            /* Preview */
            .razze-shortcode-preview {
                background: #1d2327;
                color: #50c878;
                padding: 12px 15px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 13px;
                word-break: break-all;
            }

            /* Footer */
            .razze-grid-modal-footer {
                padding: 15px 20px;
                border-top: 1px solid #ddd;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            .razze-grid-modal-footer button {
                padding: 8px 20px;
                border-radius: 4px;
                font-size: 14px;
                cursor: pointer;
            }
            .btn-cancel {
                background: #f6f7f7;
                border: 1px solid #ddd;
                color: #50575e;
            }
            .btn-cancel:hover {
                background: #ddd;
            }
            .btn-insert {
                background: #2271b1;
                border: 1px solid #2271b1;
                color: #fff;
            }
            .btn-insert:hover {
                background: #135e96;
            }
            .btn-insert:disabled {
                background: #a7aaad;
                border-color: #a7aaad;
                cursor: not-allowed;
            }
        </style>
        <?php
    }

    /**
     * Add modal HTML
     */
    public function add_modal_html() {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->base, array( 'post', 'page' ) ) ) {
            return;
        }
        ?>
        <div class="razze-grid-modal-overlay" id="razzeGridModal">
            <div class="razze-grid-modal">
                <div class="razze-grid-modal-header">
                    <h2><span class="dashicons dashicons-grid-view"></span> Genera Griglia Razze</h2>
                    <button type="button" class="razze-grid-modal-close" id="razzeGridModalClose">&times;</button>
                </div>

                <div class="razze-grid-modal-body">
                    <!-- Search -->
                    <div class="razze-search-box">
                        <input type="text" id="razzeSearchInput" placeholder="Cerca razze per nome..." autocomplete="off">
                    </div>

                    <!-- Results -->
                    <div class="razze-search-results" id="razzeSearchResults">
                        <div class="razze-search-empty">Digita per cercare razze...</div>
                    </div>

                    <!-- Selected -->
                    <div class="razze-selected-section">
                        <h4>Razze Selezionate (<span id="razzeSelectedCount">0</span>)</h4>
                        <div class="razze-selected-list" id="razzeSelectedList">
                            <span class="razze-selected-empty">Nessuna razza selezionata</span>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="razze-grid-options">
                        <div class="razze-grid-option">
                            <label for="razzeGridColumns">Colonne</label>
                            <select id="razzeGridColumns">
                                <option value="2">2 colonne</option>
                                <option value="3">3 colonne</option>
                                <option value="4" selected>4 colonne</option>
                                <option value="5">5 colonne</option>
                                <option value="6">6 colonne</option>
                            </select>
                        </div>
                        <div class="razze-grid-option">
                            <label for="razzeGridTitle">Titolo (opzionale)</label>
                            <input type="text" id="razzeGridTitle" placeholder="Es. Razze Popolari">
                        </div>
                        <div class="razze-grid-option">
                            <label for="razzeGridOrder">Ordinamento</label>
                            <select id="razzeGridOrder">
                                <option value="post__in">Ordine selezione</option>
                                <option value="title">Alfabetico</option>
                                <option value="rand">Casuale</option>
                            </select>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="razze-shortcode-preview" id="razzeShortcodePreview">
                        [razze_grid ids=""]
                    </div>
                </div>

                <div class="razze-grid-modal-footer">
                    <button type="button" class="btn-cancel" id="razzeGridCancel">Annulla</button>
                    <button type="button" class="btn-insert" id="razzeGridInsert" disabled>Inserisci Shortcode</button>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var selectedRazze = [];
            var searchTimer;

            // Open modal
            window.openRazzeGridModal = function() {
                $('#razzeGridModal').addClass('active');
                $('#razzeSearchInput').focus();
            };

            // Close modal
            function closeModal() {
                $('#razzeGridModal').removeClass('active');
                resetModal();
            }

            function resetModal() {
                selectedRazze = [];
                $('#razzeSearchInput').val('');
                $('#razzeSearchResults').html('<div class="razze-search-empty">Digita per cercare razze...</div>');
                $('#razzeSelectedList').html('<span class="razze-selected-empty">Nessuna razza selezionata</span>');
                $('#razzeSelectedCount').text('0');
                $('#razzeGridTitle').val('');
                $('#razzeGridColumns').val('4');
                $('#razzeGridOrder').val('post__in');
                updatePreview();
            }

            $('#razzeGridModalClose, #razzeGridCancel').on('click', closeModal);

            $('#razzeGridModal').on('click', function(e) {
                if (e.target === this) closeModal();
            });

            // Search
            $('#razzeSearchInput').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimer);

                if (query.length < 2) {
                    $('#razzeSearchResults').html('<div class="razze-search-empty">Digita almeno 2 caratteri...</div>');
                    return;
                }

                $('#razzeSearchResults').html('<div class="razze-search-loading">Ricerca in corso...</div>');

                searchTimer = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'search_razze_for_grid',
                            query: query,
                            nonce: '<?php echo wp_create_nonce( 'search_razze_nonce' ); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.length > 0) {
                                var html = '';
                                response.data.forEach(function(razza) {
                                    var isSelected = selectedRazze.find(function(r) { return r.id === razza.id; });
                                    html += '<div class="razze-search-item" data-id="' + razza.id + '" data-name="' + razza.name + '">';
                                    html += '<img src="' + razza.image + '" alt="">';
                                    html += '<div class="razze-search-item-info">';
                                    html += '<div class="razze-search-item-name">' + razza.name + '</div>';
                                    html += '<div class="razze-search-item-meta">ID: ' + razza.id + (razza.taglia ? ' | ' + razza.taglia : '') + '</div>';
                                    html += '</div>';
                                    if (!isSelected) {
                                        html += '<button type="button" class="razze-search-item-add">+ Aggiungi</button>';
                                    } else {
                                        html += '<span style="color:#50c878;font-size:12px;">Aggiunto</span>';
                                    }
                                    html += '</div>';
                                });
                                $('#razzeSearchResults').html(html);
                            } else {
                                $('#razzeSearchResults').html('<div class="razze-search-empty">Nessuna razza trovata</div>');
                            }
                        }
                    });
                }, 300);
            });

            // Add razza
            $(document).on('click', '.razze-search-item-add', function(e) {
                e.stopPropagation();
                var $item = $(this).closest('.razze-search-item');
                var id = $item.data('id');
                var name = $item.data('name');

                if (!selectedRazze.find(function(r) { return r.id === id; })) {
                    selectedRazze.push({ id: id, name: name });
                    updateSelectedList();
                    $(this).replaceWith('<span style="color:#50c878;font-size:12px;">Aggiunto</span>');
                }
            });

            // Remove razza
            $(document).on('click', '.razze-selected-item .remove', function() {
                var id = $(this).closest('.razze-selected-item').data('id');
                selectedRazze = selectedRazze.filter(function(r) { return r.id !== id; });
                updateSelectedList();
                // Update search results if visible
                $('#razzeSearchInput').trigger('input');
            });

            function updateSelectedList() {
                var $list = $('#razzeSelectedList');
                $('#razzeSelectedCount').text(selectedRazze.length);

                if (selectedRazze.length === 0) {
                    $list.html('<span class="razze-selected-empty">Nessuna razza selezionata</span>');
                    $('#razzeGridInsert').prop('disabled', true);
                } else {
                    var html = '';
                    selectedRazze.forEach(function(razza) {
                        html += '<span class="razze-selected-item" data-id="' + razza.id + '">';
                        html += razza.name;
                        html += '<span class="remove">&times;</span>';
                        html += '</span>';
                    });
                    $list.html(html);
                    $('#razzeGridInsert').prop('disabled', false);
                }

                updatePreview();
            }

            function updatePreview() {
                var ids = selectedRazze.map(function(r) { return r.id; }).join(',');
                var columns = $('#razzeGridColumns').val();
                var title = $('#razzeGridTitle').val();
                var order = $('#razzeGridOrder').val();

                var shortcode = '[razze_grid ids="' + ids + '"';
                if (columns !== '4') shortcode += ' columns="' + columns + '"';
                if (title) shortcode += ' title="' + title + '"';
                if (order !== 'post__in') shortcode += ' orderby="' + order + '"';
                shortcode += ']';

                $('#razzeShortcodePreview').text(shortcode);
            }

            $('#razzeGridColumns, #razzeGridTitle, #razzeGridOrder').on('change input', updatePreview);

            // Insert shortcode
            $('#razzeGridInsert').on('click', function() {
                var shortcode = $('#razzeShortcodePreview').text();
                if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                    tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
                }
                closeModal();
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX: Search razze
     */
    public function ajax_search_razze() {
        check_ajax_referer( 'search_razze_nonce', 'nonce' );

        $query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';

        if ( strlen( $query ) < 2 ) {
            wp_send_json_success( array() );
        }

        $args = array(
            'post_type'      => 'razze_di_cani',
            'posts_per_page' => 20,
            's'              => $query,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $razze = get_posts( $args );
        $results = array();

        foreach ( $razze as $razza ) {
            $image = get_the_post_thumbnail_url( $razza->ID, 'thumbnail' );
            if ( ! $image ) {
                $image = CANIINCASA_THEME_URI . '/assets/images/default-dog.jpg';
            }

            $terms = get_the_terms( $razza->ID, 'razza_taglia' );
            $taglia = '';
            if ( $terms && ! is_wp_error( $terms ) ) {
                $taglia = $terms[0]->name;
            }

            $results[] = array(
                'id'     => $razza->ID,
                'name'   => $razza->post_title,
                'image'  => $image,
                'taglia' => $taglia,
            );
        }

        wp_send_json_success( $results );
    }
}

// Initialize
Caniincasa_Razze_Grid_Editor::get_instance();
