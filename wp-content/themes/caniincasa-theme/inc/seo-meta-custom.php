<?php
/**
 * Custom SEO Meta Tags System
 * Allows manual meta tag input for archive pages and special pages
 *
 * @package Caniincasa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add SEO Meta Tags settings page
 */
function caniincasa_add_seo_meta_menu() {
    add_options_page(
        'Meta Tag SEO',
        'Meta Tag SEO',
        'manage_options',
        'caniincasa-seo-meta',
        'caniincasa_render_seo_meta_page'
    );
}
add_action( 'admin_menu', 'caniincasa_add_seo_meta_menu' );

/**
 * Render SEO meta tags settings page
 */
function caniincasa_render_seo_meta_page() {
    // Save settings
    if ( isset( $_POST['caniincasa_save_seo_meta'] ) && check_admin_referer( 'caniincasa_seo_meta_nonce' ) ) {
        $meta_tags = array();

        // Get all post types
        $post_types = array(
            'annunci_4zampe',
            'annunci_dogsitter',
            'veterinari',
            'allevamenti',
            'canili',
            'pensioni_per_cani',
            'centri_cinofili',
            'razze_di_cani',
        );

        foreach ( $post_types as $post_type ) {
            $meta_tags[ $post_type ] = array(
                'title'       => sanitize_text_field( $_POST[ $post_type . '_title' ] ?? '' ),
                'description' => sanitize_textarea_field( $_POST[ $post_type . '_description' ] ?? '' ),
            );
        }

        // Homepage
        $meta_tags['homepage'] = array(
            'title'       => sanitize_text_field( $_POST['homepage_title'] ?? '' ),
            'description' => sanitize_textarea_field( $_POST['homepage_description'] ?? '' ),
        );

        update_option( 'caniincasa_custom_meta_tags', $meta_tags );

        echo '<div class="notice notice-success"><p>Impostazioni salvate con successo!</p></div>';
    }

    // Get saved settings
    $meta_tags = get_option( 'caniincasa_custom_meta_tags', array() );

    ?>
    <div class="wrap">
        <h1>Meta Tag SEO Personalizzati</h1>
        <p class="description">
            Inserisci manualmente title e meta description per le pagine archivio e la homepage.
            <br>Se usi Yoast SEO, questi meta tag avranno la priorità su quelli di Yoast.
            <br><strong>Lascia vuoto per usare i meta tag di default di WordPress o Yoast.</strong>
        </p>

        <style>
            .seo-meta-section {
                background: white;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #2271b1;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .seo-meta-section h2 {
                margin-top: 0;
                font-size: 18px;
            }
            .seo-meta-field {
                margin-bottom: 20px;
            }
            .seo-meta-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .seo-meta-field input[type="text"] {
                width: 100%;
                max-width: 800px;
            }
            .seo-meta-field textarea {
                width: 100%;
                max-width: 800px;
                rows: 3;
            }
            .char-count {
                font-size: 12px;
                color: #646970;
                margin-top: 5px;
            }
            .char-count.warning {
                color: #f97316;
            }
            .char-count.error {
                color: #dc3545;
            }
            .seo-meta-example {
                background: #f8f9fa;
                padding: 10px;
                margin-top: 10px;
                border-left: 3px solid #646970;
                font-size: 13px;
            }
            .preview-url {
                color: #006621;
                font-size: 13px;
            }
            .preview-title {
                color: #1a0dab;
                font-size: 18px;
                margin: 5px 0;
            }
            .preview-description {
                color: #545454;
                font-size: 13px;
                line-height: 1.4;
            }
        </style>

        <form method="post">
            <?php wp_nonce_field( 'caniincasa_seo_meta_nonce' ); ?>

            <!-- Homepage -->
            <div class="seo-meta-section">
                <h2>🏠 Homepage</h2>
                <div class="seo-meta-field">
                    <label for="homepage_title">Meta Title</label>
                    <input type="text"
                           name="homepage_title"
                           id="homepage_title"
                           value="<?php echo esc_attr( $meta_tags['homepage']['title'] ?? '' ); ?>"
                           class="char-counter"
                           maxlength="70"
                           placeholder="Cani in Casa - Trova Veterinari, Allevamenti e Razze di Cani">
                    <div class="char-count" data-target="homepage_title">
                        <span class="current">0</span> / 70 caratteri (ottimale: 50-60)
                    </div>
                </div>
                <div class="seo-meta-field">
                    <label for="homepage_description">Meta Description</label>
                    <textarea name="homepage_description"
                              id="homepage_description"
                              rows="3"
                              class="char-counter"
                              maxlength="160"
                              placeholder="Scopri i migliori veterinari, allevamenti e strutture per cani in Italia. Trova la razza perfetta per te e leggi recensioni autentiche."><?php echo esc_textarea( $meta_tags['homepage']['description'] ?? '' ); ?></textarea>
                    <div class="char-count" data-target="homepage_description">
                        <span class="current">0</span> / 160 caratteri (ottimale: 120-155)
                    </div>
                </div>
            </div>

            <!-- Annunci 4Zampe -->
            <div class="seo-meta-section">
                <h2>🐕 Archivio Annunci Cani</h2>
                <p class="description">Pagina: <code>https://www.caniincasa.it/annunci/</code></p>

                <div class="seo-meta-field">
                    <label for="annunci_4zampe_title">Meta Title</label>
                    <input type="text"
                           name="annunci_4zampe_title"
                           id="annunci_4zampe_title"
                           value="<?php echo esc_attr( $meta_tags['annunci_4zampe']['title'] ?? '' ); ?>"
                           class="char-counter"
                           maxlength="70"
                           placeholder="Annunci Cani - Compra, Vendi, Adotta | Cani in Casa">
                    <div class="char-count" data-target="annunci_4zampe_title">
                        <span class="current">0</span> / 70 caratteri
                    </div>
                </div>
                <div class="seo-meta-field">
                    <label for="annunci_4zampe_description">Meta Description</label>
                    <textarea name="annunci_4zampe_description"
                              id="annunci_4zampe_description"
                              rows="3"
                              class="char-counter"
                              maxlength="160"
                              placeholder="Trova cuccioli in vendita, cani da adottare e accessori. Annunci verificati di privati e allevatori professionisti."><?php echo esc_textarea( $meta_tags['annunci_4zampe']['description'] ?? '' ); ?></textarea>
                    <div class="char-count" data-target="annunci_4zampe_description">
                        <span class="current">0</span> / 160 caratteri
                    </div>
                </div>
            </div>

            <!-- Annunci Dogsitter -->
            <div class="seo-meta-section">
                <h2>🦮 Archivio Annunci Dogsitter</h2>
                <p class="description">Pagina archivio annunci dogsitter</p>

                <div class="seo-meta-field">
                    <label for="annunci_dogsitter_title">Meta Title</label>
                    <input type="text"
                           name="annunci_dogsitter_title"
                           id="annunci_dogsitter_title"
                           value="<?php echo esc_attr( $meta_tags['annunci_dogsitter']['title'] ?? '' ); ?>"
                           class="char-counter"
                           maxlength="70"
                           placeholder="Dogsitter e Dog Walking - Annunci | Cani in Casa">
                    <div class="char-count" data-target="annunci_dogsitter_title">
                        <span class="current">0</span> / 70 caratteri
                    </div>
                </div>
                <div class="seo-meta-field">
                    <label for="annunci_dogsitter_description">Meta Description</label>
                    <textarea name="annunci_dogsitter_description"
                              id="annunci_dogsitter_description"
                              rows="3"
                              class="char-counter"
                              maxlength="160"
                              placeholder="Trova dogsitter professionisti e servizi di dog walking nella tua zona. Affida il tuo cane a mani esperte."><?php echo esc_textarea( $meta_tags['annunci_dogsitter']['description'] ?? '' ); ?></textarea>
                    <div class="char-count" data-target="annunci_dogsitter_description">
                        <span class="current">0</span> / 160 caratteri
                    </div>
                </div>
            </div>

            <!-- Veterinari -->
            <div class="seo-meta-section">
                <h2>⚕️ Archivio Veterinari</h2>
                <p class="description">Pagina archivio veterinari</p>

                <div class="seo-meta-field">
                    <label for="veterinari_title">Meta Title</label>
                    <input type="text"
                           name="veterinari_title"
                           id="veterinari_title"
                           value="<?php echo esc_attr( $meta_tags['veterinari']['title'] ?? '' ); ?>"
                           class="char-counter"
                           maxlength="70"
                           placeholder="Veterinari in Italia - Directory Completa | Cani in Casa">
                    <div class="char-count" data-target="veterinari_title">
                        <span class="current">0</span> / 70 caratteri
                    </div>
                </div>
                <div class="seo-meta-field">
                    <label for="veterinari_description">Meta Description</label>
                    <textarea name="veterinari_description"
                              id="veterinari_description"
                              rows="3"
                              class="char-counter"
                              maxlength="160"
                              placeholder="Trova veterinari qualificati vicino a te. Cliniche veterinarie, orari, contatti e servizi per la salute del tuo cane."><?php echo esc_textarea( $meta_tags['veterinari']['description'] ?? '' ); ?></textarea>
                    <div class="char-count" data-target="veterinari_description">
                        <span class="current">0</span> / 160 caratteri
                    </div>
                </div>
            </div>

            <!-- Allevamenti -->
            <div class="seo-meta-section">
                <h2>🏡 Archivio Allevamenti</h2>
                <p class="description">Pagina archivio allevamenti</p>

                <div class="seo-meta-field">
                    <label for="allevamenti_title">Meta Title</label>
                    <input type="text"
                           name="allevamenti_title"
                           id="allevamenti_title"
                           value="<?php echo esc_attr( $meta_tags['allevamenti']['title'] ?? '' ); ?>"
                           class="char-counter"
                           maxlength="70"
                           placeholder="Allevamenti Cani - Directory Riconosciuti ENCI | Cani in Casa">
                    <div class="char-count" data-target="allevamenti_title">
                        <span class="current">0</span> / 70 caratteri
                    </div>
                </div>
                <div class="seo-meta-field">
                    <label for="allevamenti_description">Meta Description</label>
                    <textarea name="allevamenti_description"
                              id="allevamenti_description"
                              rows="3"
                              class="char-counter"
                              maxlength="160"
                              placeholder="Allevamenti certificati ENCI per tutte le razze. Trova cuccioli di razza pura da allevatori professionisti e affidabili."><?php echo esc_textarea( $meta_tags['allevamenti']['description'] ?? '' ); ?></textarea>
                    <div class="char-count" data-target="allevamenti_description">
                        <span class="current">0</span> / 160 caratteri
                    </div>
                </div>
            </div>

            <!-- Altri post types... (canili, pensioni, centri, razze) -->
            <?php
            $other_types = array(
                'canili' => array(
                    'label' => '🏥 Archivio Canili',
                    'icon' => '🏥',
                ),
                'pensioni_per_cani' => array(
                    'label' => '🏨 Archivio Pensioni',
                    'icon' => '🏨',
                ),
                'centri_cinofili' => array(
                    'label' => '🎓 Archivio Centri Cinofili',
                    'icon' => '🎓',
                ),
                'razze_di_cani' => array(
                    'label' => '🐶 Archivio Razze',
                    'icon' => '🐶',
                ),
            );

            foreach ( $other_types as $type => $data ) :
            ?>
                <div class="seo-meta-section">
                    <h2><?php echo esc_html( $data['label'] ); ?></h2>

                    <div class="seo-meta-field">
                        <label for="<?php echo esc_attr( $type ); ?>_title">Meta Title</label>
                        <input type="text"
                               name="<?php echo esc_attr( $type ); ?>_title"
                               id="<?php echo esc_attr( $type ); ?>_title"
                               value="<?php echo esc_attr( $meta_tags[ $type ]['title'] ?? '' ); ?>"
                               class="char-counter"
                               maxlength="70">
                        <div class="char-count" data-target="<?php echo esc_attr( $type ); ?>_title">
                            <span class="current">0</span> / 70 caratteri
                        </div>
                    </div>
                    <div class="seo-meta-field">
                        <label for="<?php echo esc_attr( $type ); ?>_description">Meta Description</label>
                        <textarea name="<?php echo esc_attr( $type ); ?>_description"
                                  id="<?php echo esc_attr( $type ); ?>_description"
                                  rows="3"
                                  class="char-counter"
                                  maxlength="160"><?php echo esc_textarea( $meta_tags[ $type ]['description'] ?? '' ); ?></textarea>
                        <div class="char-count" data-target="<?php echo esc_attr( $type ); ?>_description">
                            <span class="current">0</span> / 160 caratteri
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="submit">
                <button type="submit" name="caniincasa_save_seo_meta" class="button button-primary button-large">
                    Salva Meta Tag
                </button>
            </p>
        </form>

        <div class="seo-meta-section" style="border-left-color: #f97316;">
            <h2>📝 Linee Guida SEO</h2>
            <ul>
                <li><strong>Meta Title:</strong> 50-60 caratteri è l'ottimale (max 70). Include il nome del sito.</li>
                <li><strong>Meta Description:</strong> 120-155 caratteri è l'ottimale (max 160). Deve invogliare al click.</li>
                <li><strong>Parole chiave:</strong> Inserisci le keyword principali nel title e nella description.</li>
                <li><strong>Call to action:</strong> Usa verbi d'azione (Trova, Scopri, Cerca, Confronta).</li>
                <li><strong>Unicità:</strong> Ogni pagina deve avere title e description unici.</li>
            </ul>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Character counter
        $('.char-counter').each(function() {
            updateCharCount($(this));
        }).on('input', function() {
            updateCharCount($(this));
        });

        function updateCharCount($field) {
            var length = $field.val().length;
            var $counter = $('.char-count[data-target="' + $field.attr('id') + '"]');
            var $current = $counter.find('.current');

            $current.text(length);

            // Color coding for title (optimal 50-60)
            if ($field.is('input[type="text"]')) {
                $counter.removeClass('warning error');
                if (length > 70) {
                    $counter.addClass('error');
                } else if (length > 60 || length < 40) {
                    $counter.addClass('warning');
                }
            }

            // Color coding for description (optimal 120-155)
            if ($field.is('textarea')) {
                $counter.removeClass('warning error');
                if (length > 160) {
                    $counter.addClass('error');
                } else if (length > 155 || length < 100) {
                    $counter.addClass('warning');
                }
            }
        }
    });
    </script>
    <?php
}

/**
 * Output custom meta tags
 */
function caniincasa_output_custom_meta_tags() {
    // Don't output if Yoast is active (let Yoast handle it)
    if ( defined( 'WPSEO_VERSION' ) ) {
        return;
    }

    $meta_tags = get_option( 'caniincasa_custom_meta_tags', array() );
    $custom_title = '';
    $custom_description = '';

    // Homepage
    if ( is_front_page() && ! empty( $meta_tags['homepage']['title'] ) ) {
        $custom_title = $meta_tags['homepage']['title'];
        $custom_description = $meta_tags['homepage']['description'];
    }

    // Archive pages
    if ( is_post_type_archive() ) {
        $post_type = get_post_type();
        if ( ! empty( $meta_tags[ $post_type ]['title'] ) ) {
            $custom_title = $meta_tags[ $post_type ]['title'];
            $custom_description = $meta_tags[ $post_type ]['description'];
        }
    }

    // Output meta tags
    if ( $custom_description ) {
        echo '<meta name="description" content="' . esc_attr( $custom_description ) . '">' . "\n";
    }

    // Note: title tag is handled by wp_get_document_title filter below
}
add_action( 'wp_head', 'caniincasa_output_custom_meta_tags', 1 );

/**
 * Filter document title
 */
function caniincasa_custom_document_title( $title ) {
    // Don't filter if Yoast is active
    if ( defined( 'WPSEO_VERSION' ) ) {
        return $title;
    }

    $meta_tags = get_option( 'caniincasa_custom_meta_tags', array() );

    // Homepage
    if ( is_front_page() && ! empty( $meta_tags['homepage']['title'] ) ) {
        return $meta_tags['homepage']['title'];
    }

    // Archive pages
    if ( is_post_type_archive() ) {
        $post_type = get_post_type();
        if ( ! empty( $meta_tags[ $post_type ]['title'] ) ) {
            return $meta_tags[ $post_type ]['title'];
        }
    }

    return $title;
}
add_filter( 'pre_get_document_title', 'caniincasa_custom_document_title' );

/**
 * Yoast SEO filters (if Yoast is active)
 */
if ( defined( 'WPSEO_VERSION' ) ) {
    /**
     * Override Yoast title with custom meta tags
     */
    add_filter( 'wpseo_title', function( $title ) {
        $meta_tags = get_option( 'caniincasa_custom_meta_tags', array() );

        if ( is_front_page() && ! empty( $meta_tags['homepage']['title'] ) ) {
            return $meta_tags['homepage']['title'];
        }

        if ( is_post_type_archive() ) {
            $post_type = get_post_type();
            if ( ! empty( $meta_tags[ $post_type ]['title'] ) ) {
                return $meta_tags[ $post_type ]['title'];
            }
        }

        return $title;
    }, 100 );

    /**
     * Override Yoast meta description with custom meta tags
     */
    add_filter( 'wpseo_metadesc', function( $description ) {
        $meta_tags = get_option( 'caniincasa_custom_meta_tags', array() );

        if ( is_front_page() && ! empty( $meta_tags['homepage']['description'] ) ) {
            return $meta_tags['homepage']['description'];
        }

        if ( is_post_type_archive() ) {
            $post_type = get_post_type();
            if ( ! empty( $meta_tags[ $post_type ]['description'] ) ) {
                return $meta_tags[ $post_type ]['description'];
            }
        }

        return $description;
    }, 100 );
}
