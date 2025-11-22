<?php
/**
 * AI Content Generator - Integrazione ChatGPT per Classic Editor
 *
 * Aggiunge una meta box per generare testi con ChatGPT API
 * su tutti i post type (post, pagine, CPT).
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register settings for API key
 */
function caniincasa_register_ai_settings() {
    register_setting( 'caniincasa_ai_settings', 'caniincasa_openai_api_key', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );

    register_setting( 'caniincasa_ai_settings', 'caniincasa_openai_model', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'gpt-4o-mini',
    ) );

    register_setting( 'caniincasa_ai_settings', 'caniincasa_ai_default_prompt', array(
        'type'              => 'string',
        'sanitize_callback' => 'wp_kses_post',
        'default'           => 'Sei un esperto copywriter italiano specializzato in contenuti per siti web di animali domestici, in particolare cani. Scrivi in modo chiaro, informativo e coinvolgente.',
    ) );
}
add_action( 'admin_init', 'caniincasa_register_ai_settings' );

/**
 * Add AI settings submenu
 */
function caniincasa_add_ai_settings_menu() {
    add_options_page(
        'Impostazioni AI',
        'Generatore AI',
        'manage_options',
        'caniincasa-ai-settings',
        'caniincasa_render_ai_settings_page'
    );
}
add_action( 'admin_menu', 'caniincasa_add_ai_settings_menu' );

/**
 * Render AI settings page
 */
function caniincasa_render_ai_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle form submission
    if ( isset( $_POST['submit'] ) && check_admin_referer( 'caniincasa_ai_settings_nonce' ) ) {
        update_option( 'caniincasa_openai_api_key', sanitize_text_field( $_POST['caniincasa_openai_api_key'] ?? '' ) );
        update_option( 'caniincasa_openai_model', sanitize_text_field( $_POST['caniincasa_openai_model'] ?? 'gpt-4o-mini' ) );
        update_option( 'caniincasa_ai_default_prompt', wp_kses_post( $_POST['caniincasa_ai_default_prompt'] ?? '' ) );
        update_option( 'caniincasa_image_model', sanitize_text_field( $_POST['caniincasa_image_model'] ?? 'dall-e-3' ) );
        echo '<div class="notice notice-success"><p>Impostazioni salvate.</p></div>';
    }

    $api_key = get_option( 'caniincasa_openai_api_key', '' );
    $model   = get_option( 'caniincasa_openai_model', 'gpt-4o-mini' );
    $prompt  = get_option( 'caniincasa_ai_default_prompt', '' );
    ?>
    <div class="wrap">
        <h1>Impostazioni Generatore AI</h1>
        <p class="description">Configura l'integrazione con OpenAI per generare contenuti con ChatGPT.</p>

        <form method="post" action="">
            <?php wp_nonce_field( 'caniincasa_ai_settings_nonce' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="caniincasa_openai_api_key">API Key OpenAI</label>
                    </th>
                    <td>
                        <input type="password"
                               id="caniincasa_openai_api_key"
                               name="caniincasa_openai_api_key"
                               value="<?php echo esc_attr( $api_key ); ?>"
                               class="regular-text"
                               autocomplete="off">
                        <p class="description">
                            Ottieni la tua API key da <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>.
                            La chiave viene salvata in modo sicuro nel database.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="caniincasa_openai_model">Modello AI</label>
                    </th>
                    <td>
                        <select id="caniincasa_openai_model" name="caniincasa_openai_model">
                            <optgroup label="GPT-4o (Consigliati)">
                                <option value="gpt-4o-mini" <?php selected( $model, 'gpt-4o-mini' ); ?>>GPT-4o Mini - Veloce ed economico ($0.15/1M input)</option>
                                <option value="gpt-4o" <?php selected( $model, 'gpt-4o' ); ?>>GPT-4o - Flagship, ottima qualita ($2.50/1M input)</option>
                            </optgroup>
                            <optgroup label="GPT-4.1 (Nuovi - Context 1M)">
                                <option value="gpt-4.1" <?php selected( $model, 'gpt-4.1' ); ?>>GPT-4.1 - Ultimo modello, context 1M token</option>
                                <option value="gpt-4.1-mini" <?php selected( $model, 'gpt-4.1-mini' ); ?>>GPT-4.1 Mini - Versione leggera</option>
                                <option value="gpt-4.1-nano" <?php selected( $model, 'gpt-4.1-nano' ); ?>>GPT-4.1 Nano - Ultra veloce</option>
                            </optgroup>
                            <optgroup label="O-Series (Reasoning avanzato)">
                                <option value="o4-mini" <?php selected( $model, 'o4-mini' ); ?>>O4-Mini - Reasoning veloce, ottimo per coding</option>
                                <option value="o3" <?php selected( $model, 'o3' ); ?>>O3 - Reasoning avanzato, meno errori</option>
                                <option value="o3-mini" <?php selected( $model, 'o3-mini' ); ?>>O3-Mini - Reasoning bilanciato</option>
                                <option value="o1" <?php selected( $model, 'o1' ); ?>>O1 - Reasoning complesso</option>
                                <option value="o1-mini" <?php selected( $model, 'o1-mini' ); ?>>O1-Mini - Reasoning economico</option>
                            </optgroup>
                            <optgroup label="Legacy">
                                <option value="gpt-4-turbo" <?php selected( $model, 'gpt-4-turbo' ); ?>>GPT-4 Turbo</option>
                                <option value="gpt-3.5-turbo" <?php selected( $model, 'gpt-3.5-turbo' ); ?>>GPT-3.5 Turbo - Piu economico</option>
                            </optgroup>
                        </select>
                        <p class="description">
                            <strong>Raccomandato:</strong> GPT-4o Mini per uso quotidiano (miglior rapporto qualita/prezzo).<br>
                            I modelli O-series sono ottimizzati per ragionamento complesso ma piu lenti e costosi.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="caniincasa_ai_default_prompt">Prompt di Sistema</label>
                    </th>
                    <td>
                        <textarea id="caniincasa_ai_default_prompt"
                                  name="caniincasa_ai_default_prompt"
                                  rows="4"
                                  class="large-text"><?php echo esc_textarea( $prompt ); ?></textarea>
                        <p class="description">
                            Istruzioni di base per l'AI. Definisce il tono e lo stile dei contenuti generati.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="caniincasa_image_model">Modello Immagini</label>
                    </th>
                    <td>
                        <?php $image_model = get_option( 'caniincasa_image_model', 'dall-e-3' ); ?>
                        <select id="caniincasa_image_model" name="caniincasa_image_model">
                            <optgroup label="DALL-E (OpenAI)">
                                <option value="dall-e-3" <?php selected( $image_model, 'dall-e-3' ); ?>>DALL-E 3 - Alta qualita ($0.04-0.12/img)</option>
                                <option value="dall-e-2" <?php selected( $image_model, 'dall-e-2' ); ?>>DALL-E 2 - Piu economico ($0.016-0.02/img)</option>
                            </optgroup>
                            <optgroup label="GPT Image (Nuovi)">
                                <option value="gpt-image-1" <?php selected( $image_model, 'gpt-image-1' ); ?>>GPT Image 1 - Ultimo modello</option>
                            </optgroup>
                        </select>
                        <p class="description">
                            DALL-E 3 genera immagini di qualita superiore con migliore comprensione del prompt.
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button( 'Salva Impostazioni' ); ?>
        </form>

        <?php if ( $api_key ) : ?>
        <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <h3>Test Connessione</h3>
            <button type="button" id="test-ai-connection" class="button button-secondary">
                Testa Connessione API
            </button>
            <span id="test-result" style="margin-left: 15px;"></span>
        </div>
        <script>
            jQuery('#test-ai-connection').on('click', function() {
                var $btn = jQuery(this);
                var $result = jQuery('#test-result');
                $btn.prop('disabled', true).text('Testing...');
                $result.text('');

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'caniincasa_test_ai_connection',
                        nonce: '<?php echo wp_create_nonce( 'ai_test_nonce' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<span style="color: #00a32a;">&#10004; ' + response.data + '</span>');
                        } else {
                            $result.html('<span style="color: #dc3545;">&#10008; ' + response.data + '</span>');
                        }
                    },
                    error: function() {
                        $result.html('<span style="color: #dc3545;">Errore di connessione</span>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Testa Connessione API');
                    }
                });
            });
        </script>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * AJAX: Test AI connection
 */
function caniincasa_ajax_test_ai_connection() {
    check_ajax_referer( 'ai_test_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $api_key = get_option( 'caniincasa_openai_api_key', '' );

    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API key non configurata' );
    }

    $response = wp_remote_get( 'https://api.openai.com/v1/models', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'Errore: ' . $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );

    if ( $code === 200 ) {
        wp_send_json_success( 'Connessione riuscita! API key valida.' );
    } elseif ( $code === 401 ) {
        wp_send_json_error( 'API key non valida o scaduta' );
    } else {
        wp_send_json_error( 'Errore HTTP: ' . $code );
    }
}
add_action( 'wp_ajax_caniincasa_test_ai_connection', 'caniincasa_ajax_test_ai_connection' );

/**
 * Add AI generator meta box to all post types
 */
function caniincasa_add_ai_meta_box() {
    $api_key = get_option( 'caniincasa_openai_api_key', '' );

    if ( empty( $api_key ) ) {
        return; // Don't show meta box if API not configured
    }

    // Get all public post types
    $post_types = get_post_types( array( 'public' => true ), 'names' );

    foreach ( $post_types as $post_type ) {
        add_meta_box(
            'caniincasa_ai_generator',
            'Generatore Testi AI',
            'caniincasa_render_ai_meta_box',
            $post_type,
            'normal',
            'high'
        );
    }
}
add_action( 'add_meta_boxes', 'caniincasa_add_ai_meta_box' );

/**
 * Render AI generator meta box
 */
function caniincasa_render_ai_meta_box( $post ) {
    wp_nonce_field( 'caniincasa_ai_generate', 'ai_generate_nonce' );
    ?>
    <div class="ai-generator-wrap" style="padding: 15px 0;">
        <style>
            .ai-generator-wrap .ai-field { margin-bottom: 15px; }
            .ai-generator-wrap label { display: block; font-weight: 600; margin-bottom: 5px; }
            .ai-generator-wrap textarea { width: 100%; }
            .ai-generator-wrap .ai-actions { display: flex; gap: 10px; align-items: center; margin-top: 15px; }
            .ai-generator-wrap .ai-output {
                margin-top: 20px;
                padding: 20px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
                display: none;
            }
            .ai-generator-wrap .ai-output-content {
                white-space: pre-wrap;
                line-height: 1.6;
                max-height: 400px;
                overflow-y: auto;
            }
            .ai-generator-wrap .ai-output-actions {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }
            .ai-generator-wrap .ai-loading {
                display: none;
                align-items: center;
                gap: 10px;
                color: #666;
            }
            .ai-generator-wrap .spinner-ai {
                width: 20px;
                height: 20px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #2271b1;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .ai-quick-prompts { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
            .ai-quick-prompt {
                background: #e9ecef;
                border: none;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 12px;
                cursor: pointer;
                transition: background 0.2s;
            }
            .ai-quick-prompt:hover { background: #2271b1; color: white; }
        </style>

        <div class="ai-field">
            <label>Prompt Rapidi</label>
            <div class="ai-quick-prompts">
                <button type="button" class="ai-quick-prompt" data-prompt="Scrivi una descrizione SEO-friendly di circa 150 parole per questo contenuto">Descrizione SEO</button>
                <button type="button" class="ai-quick-prompt" data-prompt="Scrivi un'introduzione accattivante di 2-3 paragrafi per questo articolo">Introduzione</button>
                <button type="button" class="ai-quick-prompt" data-prompt="Genera 5 FAQ pertinenti con risposte dettagliate relative a questo argomento">FAQ</button>
                <button type="button" class="ai-quick-prompt" data-prompt="Scrivi una conclusione convincente che inviti all'azione">Conclusione</button>
                <button type="button" class="ai-quick-prompt" data-prompt="Riscrivi e migliora il testo esistente mantenendo le informazioni chiave">Riscrivi</button>
                <button type="button" class="ai-quick-prompt" data-prompt="Genera 10 titoli alternativi accattivanti per questo contenuto">Titoli</button>
            </div>
        </div>

        <div class="ai-field">
            <label for="ai-prompt">Il tuo Prompt</label>
            <textarea id="ai-prompt" rows="4" placeholder="Descrivi cosa vuoi generare... Es: Scrivi un articolo informativo di 500 parole sulle razze di cani adatte ai bambini"></textarea>
            <p class="description">Sii specifico: indica lunghezza, tono, formato e argomento desiderato.</p>
        </div>

        <div class="ai-field">
            <label>
                <input type="checkbox" id="ai-use-title" checked>
                Usa il titolo del post come contesto
            </label>
            <label style="margin-left: 20px;">
                <input type="checkbox" id="ai-use-content">
                Usa il contenuto esistente come contesto
            </label>
        </div>

        <div class="ai-actions">
            <button type="button" id="ai-generate-btn" class="button button-primary button-large">
                Genera con AI
            </button>
            <div class="ai-loading">
                <div class="spinner-ai"></div>
                <span>Generazione in corso...</span>
            </div>
            <span id="ai-error" style="color: #dc3545; display: none;"></span>
        </div>

        <div class="ai-output" id="ai-output">
            <h4 style="margin-top: 0;">Testo Generato</h4>
            <div class="ai-output-content" id="ai-output-content"></div>
            <div class="ai-output-actions">
                <button type="button" class="button button-primary" id="ai-insert-content">
                    Inserisci nell'Editor
                </button>
                <button type="button" class="button" id="ai-copy-content">
                    Copia negli Appunti
                </button>
                <button type="button" class="button" id="ai-regenerate">
                    Rigenera
                </button>
            </div>
        </div>

        <!-- Image Generation Section -->
        <div class="ai-image-section" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #2271b1;">
            <h4 style="margin-top: 0; color: #2271b1;">Genera Immagine in Evidenza</h4>

            <div class="ai-field">
                <label for="ai-image-prompt">Descrizione Immagine</label>
                <textarea id="ai-image-prompt" rows="2" placeholder="Descrivi l'immagine da generare... Es: Un golden retriever che gioca in un prato verde, luce naturale, stile fotografico"></textarea>
                <p class="description">Sii specifico: descrivi soggetto, ambiente, stile, colori e atmosfera.</p>
            </div>

            <div class="ai-field" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div>
                    <label for="ai-image-style">Stile</label>
                    <select id="ai-image-style">
                        <option value="">Naturale</option>
                        <option value="photographic">Fotografico</option>
                        <option value="illustration">Illustrazione</option>
                        <option value="digital-art">Digital Art</option>
                        <option value="watercolor">Acquerello</option>
                        <option value="cartoon">Cartoon</option>
                    </select>
                </div>
                <div>
                    <label for="ai-image-size">Dimensione</label>
                    <select id="ai-image-size">
                        <option value="1024x1024">1024x1024 (Quadrato)</option>
                        <option value="1792x1024" selected>1792x1024 (Landscape)</option>
                        <option value="1024x1792">1024x1792 (Portrait)</option>
                    </select>
                </div>
            </div>

            <div class="ai-actions" style="margin-top: 15px;">
                <button type="button" id="ai-generate-image-btn" class="button button-secondary button-large">
                    Genera Immagine
                </button>
                <div class="ai-loading ai-image-loading">
                    <div class="spinner-ai"></div>
                    <span>Generazione immagine in corso (30-60 sec)...</span>
                </div>
                <span id="ai-image-error" style="color: #dc3545; display: none;"></span>
            </div>

            <div class="ai-image-output" id="ai-image-output" style="display: none; margin-top: 20px;">
                <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                    <div>
                        <img id="ai-generated-image" src="" alt="Immagine generata" style="max-width: 400px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    </div>
                    <div class="ai-image-actions" style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="button" class="button button-primary" id="ai-set-featured-image">
                            Imposta come Immagine in Evidenza
                        </button>
                        <button type="button" class="button" id="ai-download-image">
                            Scarica Immagine
                        </button>
                        <button type="button" class="button" id="ai-regenerate-image">
                            Rigenera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var $prompt = $('#ai-prompt');
        var $generateBtn = $('#ai-generate-btn');
        var $loading = $('.ai-loading');
        var $output = $('#ai-output');
        var $outputContent = $('#ai-output-content');
        var $error = $('#ai-error');

        // Quick prompts
        $('.ai-quick-prompt').on('click', function() {
            var quickPrompt = $(this).data('prompt');
            $prompt.val(quickPrompt);
        });

        // Generate content
        $generateBtn.on('click', generateContent);
        $('#ai-regenerate').on('click', generateContent);

        function generateContent() {
            var prompt = $prompt.val().trim();

            if (!prompt) {
                alert('Inserisci un prompt');
                return;
            }

            var postTitle = $('#title').val() || '';
            var postContent = '';

            // Get content from editor
            if ($('#ai-use-content').is(':checked')) {
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    postContent = tinymce.get('content').getContent({ format: 'text' });
                } else {
                    postContent = $('#content').val() || '';
                }
            }

            $generateBtn.prop('disabled', true);
            $loading.css('display', 'flex');
            $error.hide();
            $output.hide();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'caniincasa_generate_ai_content',
                    nonce: $('#ai_generate_nonce').val(),
                    prompt: prompt,
                    post_title: $('#ai-use-title').is(':checked') ? postTitle : '',
                    post_content: postContent,
                    post_id: <?php echo $post->ID; ?>
                },
                success: function(response) {
                    if (response.success) {
                        $outputContent.text(response.data.content);
                        $output.show();
                    } else {
                        $error.text(response.data).show();
                    }
                },
                error: function(xhr) {
                    $error.text('Errore di connessione: ' + xhr.statusText).show();
                },
                complete: function() {
                    $generateBtn.prop('disabled', false);
                    $loading.hide();
                }
            });
        }

        // Insert into editor
        $('#ai-insert-content').on('click', function() {
            var content = $outputContent.text();

            // Convert newlines to paragraphs
            var htmlContent = content.split('\n\n').map(function(p) {
                return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
            }).join('');

            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                tinymce.get('content').execCommand('mceInsertContent', false, htmlContent);
            } else {
                var $textarea = $('#content');
                var currentContent = $textarea.val();
                $textarea.val(currentContent + '\n\n' + content);
            }

            // Scroll to editor
            $('html, body').animate({
                scrollTop: $('#postdivrich').offset().top - 50
            }, 500);
        });

        // Copy to clipboard
        $('#ai-copy-content').on('click', function() {
            var content = $outputContent.text();
            navigator.clipboard.writeText(content).then(function() {
                var $btn = $('#ai-copy-content');
                var originalText = $btn.text();
                $btn.text('Copiato!');
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            });
        });

        // ============================================
        // IMAGE GENERATION
        // ============================================
        var $imagePrompt = $('#ai-image-prompt');
        var $generateImageBtn = $('#ai-generate-image-btn');
        var $imageLoading = $('.ai-image-loading');
        var $imageOutput = $('#ai-image-output');
        var $imageError = $('#ai-image-error');
        var $generatedImage = $('#ai-generated-image');
        var currentImageUrl = '';

        // Generate image
        $generateImageBtn.on('click', generateImage);
        $('#ai-regenerate-image').on('click', generateImage);

        function generateImage() {
            var prompt = $imagePrompt.val().trim();
            var postTitle = $('#title').val() || '';

            // If no prompt, use post title
            if (!prompt && postTitle) {
                prompt = 'Immagine per articolo: ' + postTitle;
            }

            if (!prompt) {
                alert('Inserisci una descrizione per l\'immagine o un titolo per il post');
                return;
            }

            // Add style to prompt if selected
            var style = $('#ai-image-style').val();
            if (style) {
                prompt += ', stile ' + style;
            }

            var size = $('#ai-image-size').val();

            $generateImageBtn.prop('disabled', true);
            $imageLoading.css('display', 'flex');
            $imageError.hide();
            $imageOutput.hide();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'caniincasa_generate_ai_image',
                    nonce: $('#ai_generate_nonce').val(),
                    prompt: prompt,
                    size: size,
                    post_id: <?php echo $post->ID; ?>
                },
                success: function(response) {
                    if (response.success) {
                        currentImageUrl = response.data.url;
                        $generatedImage.attr('src', currentImageUrl);
                        $imageOutput.show();
                    } else {
                        $imageError.text(response.data).show();
                    }
                },
                error: function(xhr) {
                    $imageError.text('Errore di connessione: ' + xhr.statusText).show();
                },
                complete: function() {
                    $generateImageBtn.prop('disabled', false);
                    $imageLoading.hide();
                }
            });
        }

        // Set as featured image
        $('#ai-set-featured-image').on('click', function() {
            if (!currentImageUrl) {
                alert('Nessuna immagine generata');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Salvataggio...');

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'caniincasa_set_ai_featured_image',
                    nonce: $('#ai_generate_nonce').val(),
                    image_url: currentImageUrl,
                    post_id: <?php echo $post->ID; ?>
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text('Impostata!').css('background', '#00a32a');
                        // Refresh featured image metabox if exists
                        if (typeof wp !== 'undefined' && wp.media && wp.media.featuredImage) {
                            wp.media.featuredImage.set(response.data.attachment_id);
                        }
                        setTimeout(function() {
                            $btn.text('Imposta come Immagine in Evidenza').css('background', '').prop('disabled', false);
                        }, 3000);
                    } else {
                        alert('Errore: ' + response.data);
                        $btn.text('Imposta come Immagine in Evidenza').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Errore di connessione');
                    $btn.text('Imposta come Immagine in Evidenza').prop('disabled', false);
                }
            });
        });

        // Download image
        $('#ai-download-image').on('click', function() {
            if (currentImageUrl) {
                var link = document.createElement('a');
                link.href = currentImageUrl;
                link.download = 'ai-generated-image.png';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
    </script>
    <?php
}

/**
 * AJAX: Generate AI content
 */
function caniincasa_ajax_generate_ai_content() {
    check_ajax_referer( 'caniincasa_ai_generate', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $api_key = get_option( 'caniincasa_openai_api_key', '' );

    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API key non configurata. Vai in Impostazioni > Generatore AI.' );
    }

    $prompt       = isset( $_POST['prompt'] ) ? sanitize_textarea_field( $_POST['prompt'] ) : '';
    $post_title   = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : '';
    $post_content = isset( $_POST['post_content'] ) ? sanitize_textarea_field( $_POST['post_content'] ) : '';

    if ( empty( $prompt ) ) {
        wp_send_json_error( 'Prompt vuoto' );
    }

    // Build messages
    $system_prompt = get_option( 'caniincasa_ai_default_prompt', 'Sei un esperto copywriter italiano.' );
    $model         = get_option( 'caniincasa_openai_model', 'gpt-4o-mini' );

    // Build user message with context
    $user_message = $prompt;

    if ( ! empty( $post_title ) ) {
        $user_message .= "\n\nTitolo del contenuto: " . $post_title;
    }

    if ( ! empty( $post_content ) ) {
        // Limit content to avoid token limits
        $post_content = wp_trim_words( $post_content, 500, '...' );
        $user_message .= "\n\nContenuto esistente:\n" . $post_content;
    }

    $messages = array(
        array(
            'role'    => 'system',
            'content' => $system_prompt,
        ),
        array(
            'role'    => 'user',
            'content' => $user_message,
        ),
    );

    // Check if this is an O-series reasoning model
    $is_o_series = preg_match( '/^o[0-9]/', $model );

    // Build request body based on model type
    $request_body = array(
        'model'    => $model,
        'messages' => $messages,
    );

    if ( $is_o_series ) {
        // O-series models use max_completion_tokens and don't support temperature
        $request_body['max_completion_tokens'] = 4000;
    } else {
        // Standard GPT models
        $request_body['max_tokens']  = 2000;
        $request_body['temperature'] = 0.7;
    }

    // Call OpenAI API
    $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
        'timeout' => 120, // O-series models can take longer
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $request_body ),
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'Errore API: ' . $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code !== 200 ) {
        $error_msg = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Errore sconosciuto';
        wp_send_json_error( 'Errore OpenAI: ' . $error_msg );
    }

    if ( empty( $body['choices'][0]['message']['content'] ) ) {
        wp_send_json_error( 'Risposta vuota da OpenAI' );
    }

    $generated_content = $body['choices'][0]['message']['content'];

    // Log usage for monitoring (optional)
    $usage = isset( $body['usage'] ) ? $body['usage'] : array();

    wp_send_json_success( array(
        'content' => $generated_content,
        'usage'   => $usage,
    ) );
}
add_action( 'wp_ajax_caniincasa_generate_ai_content', 'caniincasa_ajax_generate_ai_content' );

/**
 * AJAX: Generate AI image with DALL-E
 */
function caniincasa_ajax_generate_ai_image() {
    check_ajax_referer( 'caniincasa_ai_generate', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $api_key = get_option( 'caniincasa_openai_api_key', '' );

    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API key non configurata' );
    }

    $prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( $_POST['prompt'] ) : '';
    $size   = isset( $_POST['size'] ) ? sanitize_text_field( $_POST['size'] ) : '1792x1024';

    if ( empty( $prompt ) ) {
        wp_send_json_error( 'Prompt vuoto' );
    }

    // Validate size
    $valid_sizes = array( '1024x1024', '1792x1024', '1024x1792' );
    if ( ! in_array( $size, $valid_sizes, true ) ) {
        $size = '1792x1024';
    }

    $model = get_option( 'caniincasa_image_model', 'dall-e-3' );

    // Build request based on model
    $request_body = array(
        'model'  => $model,
        'prompt' => $prompt,
        'n'      => 1,
        'size'   => $size,
    );

    // DALL-E 3 specific options
    if ( $model === 'dall-e-3' ) {
        $request_body['quality'] = 'standard'; // or 'hd'
        $request_body['style']   = 'vivid'; // or 'natural'
    }

    // DALL-E 2 only supports specific sizes
    if ( $model === 'dall-e-2' ) {
        $request_body['size'] = '1024x1024'; // DALL-E 2 max size
    }

    // Call OpenAI Images API
    $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', array(
        'timeout' => 120,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body'    => wp_json_encode( $request_body ),
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'Errore API: ' . $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code !== 200 ) {
        $error_msg = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Errore sconosciuto';
        wp_send_json_error( 'Errore OpenAI: ' . $error_msg );
    }

    if ( empty( $body['data'][0]['url'] ) ) {
        wp_send_json_error( 'Nessuna immagine generata' );
    }

    wp_send_json_success( array(
        'url'            => $body['data'][0]['url'],
        'revised_prompt' => isset( $body['data'][0]['revised_prompt'] ) ? $body['data'][0]['revised_prompt'] : '',
    ) );
}
add_action( 'wp_ajax_caniincasa_generate_ai_image', 'caniincasa_ajax_generate_ai_image' );

/**
 * AJAX: Set AI generated image as featured image
 */
function caniincasa_ajax_set_ai_featured_image() {
    check_ajax_referer( 'caniincasa_ai_generate', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Permesso negato' );
    }

    $image_url = isset( $_POST['image_url'] ) ? esc_url_raw( $_POST['image_url'] ) : '';
    $post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

    if ( empty( $image_url ) || empty( $post_id ) ) {
        wp_send_json_error( 'Parametri mancanti' );
    }

    // Download image
    $response = wp_remote_get( $image_url, array( 'timeout' => 60 ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'Errore download: ' . $response->get_error_message() );
    }

    $image_data = wp_remote_retrieve_body( $response );

    if ( empty( $image_data ) ) {
        wp_send_json_error( 'Immagine vuota' );
    }

    // Generate filename
    $post_title = get_the_title( $post_id );
    $filename   = sanitize_file_name( 'ai-' . sanitize_title( $post_title ) . '-' . time() . '.png' );

    // Upload to WordPress media library
    $upload = wp_upload_bits( $filename, null, $image_data );

    if ( $upload['error'] ) {
        wp_send_json_error( 'Errore upload: ' . $upload['error'] );
    }

    // Create attachment
    $file_path = $upload['file'];
    $file_type = wp_check_filetype( $filename, null );

    $attachment = array(
        'post_mime_type' => $file_type['type'],
        'post_title'     => 'AI Generated - ' . $post_title,
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $file_path, $post_id );

    if ( is_wp_error( $attach_id ) ) {
        wp_send_json_error( 'Errore creazione attachment' );
    }

    // Generate metadata
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    // Set as featured image
    set_post_thumbnail( $post_id, $attach_id );

    wp_send_json_success( array(
        'attachment_id' => $attach_id,
        'url'           => wp_get_attachment_url( $attach_id ),
    ) );
}
add_action( 'wp_ajax_caniincasa_set_ai_featured_image', 'caniincasa_ajax_set_ai_featured_image' );
