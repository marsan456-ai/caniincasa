<?php
/**
 * Plugin Name: CaniInCasa - Import Categorie
 * Plugin URI: https://caniincasa.it
 * Description: Importa categorie articoli da file CSV con supporto per categorie e sottocategorie.
 * Version: 1.0.0
 * Author: CaniInCasa
 * Author URI: https://caniincasa.it
 * License: GPL v2 or later
 * Text Domain: caniincasa-import
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CaniInCasa_Import_Categories {

    private static $instance = null;
    private $plugin_slug = 'caniincasa-import-categories';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_caniincasa_import_csv', array($this, 'ajax_import_csv'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'Import Categorie Articoli',
            'Import Categorie',
            'manage_options',
            $this->plugin_slug,
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('tools_page_' . $this->plugin_slug !== $hook) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_slug . '-style',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            $this->plugin_slug . '-script',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script($this->plugin_slug . '-script', 'caniincasaImport', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('caniincasa_import_nonce'),
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap caniincasa-import-wrap">
            <h1>
                <span class="dashicons dashicons-upload" style="font-size: 30px; margin-right: 10px;"></span>
                Import Categorie Articoli
            </h1>

            <div class="import-intro">
                <p>Questo strumento permette di importare e assegnare categorie agli articoli da un file CSV.</p>
                <p><strong>Formato CSV richiesto:</strong> ID, Titolo, Categoria, Sottocategoria</p>
            </div>

            <div class="import-container">
                <!-- Upload Form -->
                <div class="upload-section" id="upload-section">
                    <div class="upload-box" id="upload-box">
                        <input type="file" id="csv-file" accept=".csv" style="display: none;">
                        <div class="upload-icon">
                            <span class="dashicons dashicons-cloud-upload"></span>
                        </div>
                        <h3>Carica il file CSV</h3>
                        <p>Trascina il file qui oppure <a href="#" id="browse-link">sfoglia</a></p>
                        <p class="file-info" id="file-info"></p>
                    </div>

                    <div class="options-box">
                        <label class="checkbox-label">
                            <input type="checkbox" id="dry-run" checked>
                            <span class="checkmark"></span>
                            <strong>Modalità Test (Dry Run)</strong>
                            <br><small>Simula l'importazione senza modificare il database</small>
                        </label>
                    </div>

                    <button type="button" id="start-import" class="button button-primary button-hero" disabled>
                        <span class="dashicons dashicons-database-import"></span>
                        Avvia Importazione
                    </button>
                </div>

                <!-- Progress Section -->
                <div class="progress-section" id="progress-section" style="display: none;">
                    <div class="progress-header">
                        <span class="dashicons dashicons-update spin"></span>
                        <h3>Importazione in corso...</h3>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" id="progress-bar"></div>
                    </div>
                    <p class="progress-text" id="progress-text">Preparazione...</p>
                </div>

                <!-- Results Section -->
                <div class="results-section" id="results-section" style="display: none;">
                    <div class="results-header">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <h3>Importazione Completata</h3>
                    </div>

                    <div class="stats-grid" id="stats-grid">
                        <!-- Stats will be inserted here -->
                    </div>

                    <div class="log-container">
                        <h4>
                            <span class="dashicons dashicons-list-view"></span>
                            Log Dettagliato
                        </h4>
                        <div class="log-box" id="log-box">
                            <!-- Log entries will be inserted here -->
                        </div>
                    </div>

                    <button type="button" id="new-import" class="button button-secondary">
                        <span class="dashicons dashicons-upload"></span>
                        Nuova Importazione
                    </button>
                </div>
            </div>

            <!-- Help Section -->
            <div class="help-section">
                <h3><span class="dashicons dashicons-editor-help"></span> Come funziona</h3>
                <ol>
                    <li><strong>Prepara il CSV</strong> - Il file deve avere le colonne: ID, Titolo, Categoria, Sottocategoria</li>
                    <li><strong>Carica il file</strong> - Trascina il file nell'area di upload o clicca per sfogliare</li>
                    <li><strong>Test prima</strong> - Usa la modalità "Dry Run" per verificare senza modificare nulla</li>
                    <li><strong>Importa</strong> - Disattiva "Dry Run" e avvia l'importazione reale</li>
                </ol>

                <h4>Esempio formato CSV:</h4>
                <pre>ID,Title,Categoria,Sottocategoria
123,"Come addestrare il cane",Educazione & Comportamento,Training base
456,"Alimentazione del cucciolo",Alimentazione & Nutrizione,Cuccioli</pre>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for CSV import
     */
    public function ajax_import_csv() {
        // Increase limits for large files
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');

        // Error handling
        try {
            // Verify nonce
            if (!check_ajax_referer('caniincasa_import_nonce', 'nonce', false)) {
                wp_send_json_error(array('message' => 'Errore di sicurezza. Ricarica la pagina.'));
                return;
            }

            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Permessi insufficienti.'));
                return;
            }

            // Check file upload
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $error_messages = array(
                    UPLOAD_ERR_INI_SIZE => 'Il file supera la dimensione massima consentita (max: ' . ini_get('upload_max_filesize') . ').',
                    UPLOAD_ERR_FORM_SIZE => 'Il file supera la dimensione massima del form.',
                    UPLOAD_ERR_PARTIAL => 'Il file è stato caricato solo parzialmente.',
                    UPLOAD_ERR_NO_FILE => 'Nessun file selezionato.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante.',
                    UPLOAD_ERR_CANT_WRITE => 'Impossibile scrivere il file.',
                );
                $error_code = isset($_FILES['csv_file']) ? $_FILES['csv_file']['error'] : UPLOAD_ERR_NO_FILE;
                $error_msg = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Errore upload sconosciuto (codice: ' . $error_code . ').';
                wp_send_json_error(array('message' => $error_msg));
                return;
            }

            $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
            $file_path = $_FILES['csv_file']['tmp_name'];

            // Check if file exists and is readable
            if (!file_exists($file_path) || !is_readable($file_path)) {
                wp_send_json_error(array('message' => 'File temporaneo non accessibile.'));
                return;
            }

            // Process the CSV
            $result = $this->process_csv($file_path, $dry_run);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'Errore PHP: ' . $e->getMessage()));
        }
    }

    /**
     * Process CSV file
     */
    private function process_csv($file_path, $dry_run = true) {
        $stats = array(
            'total' => 0,
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'categories_created' => 0,
            'subcategories_created' => 0,
        );
        $logs = array();
        $category_map = array();
        $subcategory_map = array();

        // Open file
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return array(
                'success' => false,
                'message' => 'Impossibile aprire il file CSV.',
            );
        }

        // Remove BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return array(
                'success' => false,
                'message' => 'File CSV vuoto o formato non valido.',
            );
        }

        // Count total rows
        $total_rows = 0;
        while (fgetcsv($handle) !== false) {
            $total_rows++;
        }
        rewind($handle);
        fgetcsv($handle); // Skip header again

        $stats['total'] = $total_rows;

        // Process rows
        $row_num = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $row_num++;

            // Skip empty rows
            if (empty($row) || count($row) < 4) {
                $logs[] = array(
                    'type' => 'warning',
                    'message' => "Riga $row_num: Dati insufficienti, saltata.",
                );
                $stats['skipped']++;
                continue;
            }

            $post_id = intval(trim($row[0]));
            $title = trim($row[1]);
            $categoria = trim($row[2]);
            $sottocategoria = trim($row[3]);

            // Validate post ID
            if ($post_id <= 0) {
                $logs[] = array(
                    'type' => 'warning',
                    'message' => "Riga $row_num: ID non valido ($post_id), saltata.",
                );
                $stats['skipped']++;
                continue;
            }

            // Check if post exists
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'post') {
                $logs[] = array(
                    'type' => 'error',
                    'message' => "Riga $row_num: Post ID $post_id non trovato o non è un articolo.",
                );
                $stats['errors']++;
                continue;
            }

            // Get or create main category
            $cat_id = null;
            if (isset($category_map[$categoria])) {
                $cat_id = $category_map[$categoria];
            } else {
                $cat_term = get_term_by('name', $categoria, 'category');
                if ($cat_term) {
                    $cat_id = $cat_term->term_id;
                    $category_map[$categoria] = $cat_id;
                } elseif (!$dry_run) {
                    $cat_result = wp_insert_term($categoria, 'category', array(
                        'slug' => sanitize_title($categoria),
                    ));
                    if (!is_wp_error($cat_result)) {
                        $cat_id = $cat_result['term_id'];
                        $category_map[$categoria] = $cat_id;
                        $stats['categories_created']++;
                        $logs[] = array(
                            'type' => 'create',
                            'message' => "Categoria creata: $categoria (ID: $cat_id)",
                        );
                    }
                } else {
                    // Dry run - simulate category creation
                    $cat_id = 'new_' . sanitize_title($categoria);
                    $category_map[$categoria] = $cat_id;
                    $stats['categories_created']++;
                    $logs[] = array(
                        'type' => 'create',
                        'message' => "[DRY RUN] Categoria da creare: $categoria",
                    );
                }
            }

            // Get or create subcategory
            $subcat_id = null;
            $subcat_key = $categoria . '|' . $sottocategoria;
            if (isset($subcategory_map[$subcat_key])) {
                $subcat_id = $subcategory_map[$subcat_key];
            } else {
                $subcat_term = get_term_by('name', $sottocategoria, 'category');
                if ($subcat_term && $subcat_term->parent == $cat_id) {
                    $subcat_id = $subcat_term->term_id;
                    $subcategory_map[$subcat_key] = $subcat_id;
                } elseif (!$dry_run && is_numeric($cat_id)) {
                    $subcat_result = wp_insert_term($sottocategoria, 'category', array(
                        'parent' => $cat_id,
                        'slug' => sanitize_title($sottocategoria),
                    ));
                    if (!is_wp_error($subcat_result)) {
                        $subcat_id = $subcat_result['term_id'];
                        $subcategory_map[$subcat_key] = $subcat_id;
                        $stats['subcategories_created']++;
                        $logs[] = array(
                            'type' => 'create',
                            'message' => "Sottocategoria creata: $categoria → $sottocategoria (ID: $subcat_id)",
                        );
                    }
                } else {
                    // Dry run - simulate subcategory creation
                    $subcat_id = 'new_' . sanitize_title($sottocategoria);
                    $subcategory_map[$subcat_key] = $subcat_id;
                    $stats['subcategories_created']++;
                    $logs[] = array(
                        'type' => 'create',
                        'message' => "[DRY RUN] Sottocategoria da creare: $categoria → $sottocategoria",
                    );
                }
            }

            // Assign categories to post
            if (!$dry_run) {
                $categories_to_set = array();
                if (is_numeric($cat_id)) {
                    $categories_to_set[] = intval($cat_id);
                }
                if (is_numeric($subcat_id)) {
                    $categories_to_set[] = intval($subcat_id);
                }

                if (!empty($categories_to_set)) {
                    $result = wp_set_post_categories($post_id, $categories_to_set, false);
                    if ($result) {
                        $stats['processed']++;
                        $logs[] = array(
                            'type' => 'success',
                            'message' => "Post $post_id: Assegnate categorie $categoria → $sottocategoria",
                        );
                    } else {
                        $stats['errors']++;
                        $logs[] = array(
                            'type' => 'error',
                            'message' => "Post $post_id: Errore nell'assegnazione categorie",
                        );
                    }
                }
            } else {
                // Dry run - simulate assignment
                $stats['processed']++;
                $logs[] = array(
                    'type' => 'info',
                    'message' => "[DRY RUN] Post $post_id ($title): $categoria → $sottocategoria",
                );
            }
        }

        fclose($handle);

        return array(
            'success' => true,
            'dry_run' => $dry_run,
            'stats' => $stats,
            'logs' => $logs,
        );
    }
}

// Initialize plugin
CaniInCasa_Import_Categories::get_instance();
