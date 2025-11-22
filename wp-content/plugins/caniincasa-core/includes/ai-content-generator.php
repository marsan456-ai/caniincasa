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
