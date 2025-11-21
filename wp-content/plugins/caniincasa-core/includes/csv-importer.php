<?php
/**
 * CSV Importer for Caniincasa Core Plugin
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CSV Importer Class
 */
class Caniincasa_CSV_Importer {

    /**
     * Batch size for processing
     */
    private $batch_size = 10;

    /**
     * Import razze from CSV
     *
     * @param string $file_path Path to CSV file
     * @return array Import results
     */
    public function import_razze( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path,
            );
        }

        $results = array(
            'total'    => 0,
            'imported' => 0,
            'updated'  => 0,
            'skipped'  => 0,
            'errors'   => array(),
        );

        // Open CSV file
        $handle = fopen( $file_path, 'r' );
        if ( ! $handle ) {
            return array(
                'success' => false,
                'message' => 'Could not open file',
            );
        }

        // Read header row
        $headers = fgetcsv( $handle );

        // Remove BOM if present
        if ( isset( $headers[0] ) ) {
            $headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
        }

        // Process rows
        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $results['total']++;

            // Map row to associative array
            $data = array_combine( $headers, $row );

            // Import single razza
            $import_result = $this->import_single_razza( $data );

            if ( $import_result['success'] ) {
                if ( $import_result['action'] === 'updated' ) {
                    $results['updated']++;
                } else {
                    $results['imported']++;
                }
            } else {
                $results['skipped']++;
                $results['errors'][] = array(
                    'title'   => $data['Title'],
                    'message' => $import_result['message'],
                );
            }

            // Free memory
            unset( $data );
            unset( $import_result );

            // Prevent timeout on large files
            if ( $results['total'] % $this->batch_size === 0 ) {
                usleep( 100000 ); // 0.1 seconds
            }
        }

        fclose( $handle );

        $results['success'] = true;
        $results['message'] = sprintf(
            'Importazione completata: %d totali, %d importate, %d aggiornate, %d saltate',
            $results['total'],
            $results['imported'],
            $results['updated'],
            $results['skipped']
        );

        return $results;
    }

    /**
     * Import single razza
     *
     * @param array $data Row data
     * @return array Result
     */
    private function import_single_razza( $data ) {
        // Check if post exists by slug
        $slug = ! empty( $data['Slug'] ) ? sanitize_title( $data['Slug'] ) : sanitize_title( $data['Title'] );

        $existing = get_page_by_path( $slug, OBJECT, 'razze_di_cani' );

        // Prepare post data
        $post_data = array(
            'post_title'   => sanitize_text_field( $data['Title'] ),
            'post_content' => wp_kses_post( $data['Content'] ),
            'post_name'    => $slug,
            'post_type'    => 'razze_di_cani',
            'post_status'  => 'publish',
        );

        // Insert or update post
        if ( $existing ) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post( $post_data );
            $action = 'updated';
        } else {
            $post_id = wp_insert_post( $post_data );
            $action = 'inserted';
        }

        if ( is_wp_error( $post_id ) ) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            );
        }

        // Import featured image if URL provided
        if ( ! empty( $data['Image URL'] ) ) {
            $this->import_image( $post_id, $data['Image URL'], $data['Title'] );
        }

        // Import ACF fields
        $this->import_razza_acf_fields( $post_id, $data );

        return array(
            'success' => true,
            'action'  => $action,
            'post_id' => $post_id,
        );
    }

    /**
     * Import ACF fields for razza
     *
     * @param int   $post_id Post ID
     * @param array $data    Row data
     */
    private function import_razza_acf_fields( $post_id, $data ) {
        error_log( '=== INIZIO IMPORT ACF FIELDS PER POST ID: ' . $post_id . ' ===' );
        error_log( 'Titolo: ' . ( isset( $data['Title'] ) ? $data['Title'] : 'N/A' ) );

        // Debug: mostra TUTTE le chiavi disponibili nel CSV
        error_log( '--- TUTTE LE CHIAVI CSV DISPONIBILI ---' );
        error_log( print_r( array_keys( $data ), true ) );

        // Info fields
        $info_fields = array(
            'nazione_origine'      => 'nazione_origine',
            'colorazioni'          => 'colorazioni',
            'temperamento_breve'   => 'temperamento_breve',
        );

        error_log( '--- INFO FIELDS ---' );
        foreach ( $info_fields as $csv_field => $acf_field ) {
            if ( ! empty( $data[ $csv_field ] ) ) {
                $value = sanitize_text_field( $data[ $csv_field ] );
                error_log( "Campo: $csv_field => $acf_field = $value" );
                $result = update_field( $acf_field, $value, $post_id );
                error_log( "update_field result: " . ( $result ? 'SUCCESS' : 'FAILED' ) );
            } else {
                error_log( "Campo $csv_field: VUOTO o NON TROVATO" );
            }
        }

        // Content fields (WYSIWYG)
        $content_fields = array(
            'descrizione_generale'     => 'descrizione_generale',
            'origini_storia'           => 'origini_storia',
            'aspetto_fisico'           => 'aspetto_fisico',
            'carattere_temperamento'   => 'carattere_temperamento',
            'salute_cura'              => 'salute_cura',
            'attivita_addestramento'   => 'attivita_addestramento',
            'ideale_per'               => 'ideale_per',
        );

        error_log( '--- CONTENT FIELDS ---' );
        foreach ( $content_fields as $csv_field => $acf_field ) {
            if ( ! empty( $data[ $csv_field ] ) ) {
                $value = wp_kses_post( $data[ $csv_field ] );
                error_log( "Campo: $csv_field => $acf_field (lunghezza: " . strlen( $value ) . " caratteri)" );
                $result = update_field( $acf_field, $value, $post_id );
                error_log( "update_field result: " . ( $result ? 'SUCCESS' : 'FAILED' ) );
            } else {
                error_log( "Campo $csv_field: VUOTO o NON TROVATO" );
            }
        }

        // Characteristics (numeric ratings 1-5)
        // IMPORTANTE: i nomi ACF devono corrispondere ESATTAMENTE a quelli definiti in acf-fields.php
        $rating_fields = array(
            'energia_e_livelli_di_attivita'              => 'energia_e_livelli_di_attivita',
            'affettuosita'                               => 'affettuosita',
            'vocalita_e_predisposizione_ad_abbaiare'     => 'vocalita_e_predisposizione_ad_abbaiare',
            'socievolezza_cani'                          => 'socievolezza_cani',
            'adattabilita_appartamento'                  => 'adattabilita_appartamento', // FIX: era 'adattabilita_ad_appartamento'
            'adattabilita_clima_caldo'                   => 'adattabilita_clima_caldo',
            'adattabilita_clima_freddo'                  => 'adattabilita_clima_freddo',
            'tolleranza_alla_solitudine'                 => 'tolleranza_alla_solitudine',
            'compatibilita_con_i_bambini'                => 'compatibilita_con_i_bambini', // FIX: era 'compatibile_con_bambini'
            'tolleranza_estranei'                        => 'tolleranza_estranei', // FIX: era 'tolleranza_verso_estranei'
            'compatibilita_con_altri_animali_domestici'  => 'compatibilita_con_altri_animali_domestici',
            'facilita_di_addestramento'                  => 'facilita_di_addestramento',
            'intelligenza'                               => 'intelligenza',
            'esigenze_di_esercizio'                      => 'esigenze_di_esercizio',
            'facilita_toelettatura'                      => 'facilita_toelettatura',
            'cura_e_perdita_pelo_'                       => 'cura_e_perdita_pelo',
            'predisposizioni_per_la_salute'              => 'predisposizioni_per_la_salute',
            'livello_esperienza_richiesto'               => 'livello_esperienza_richiesto', // FIX: era 'esperienza_richiesta'
            'costo_mantenimento'                         => 'costo_mantenimento',
            'istinti_di_caccia'                          => 'istinti_di_caccia',
        );

        error_log( '--- RATING FIELDS (i più importanti per i filtri!) ---' );
        foreach ( $rating_fields as $csv_field => $acf_field ) {
            // Mostra sempre il debug, anche se il campo è vuoto
            $csv_value = isset( $data[ $csv_field ] ) ? $data[ $csv_field ] : 'NON TROVATO';
            error_log( "CSV Field: '$csv_field' => valore raw: '$csv_value'" );

            if ( ! empty( $data[ $csv_field ] ) && is_numeric( $data[ $csv_field ] ) ) {
                $value = floatval( $data[ $csv_field ] );
                // Ensure value is between 1 and 5
                $value = max( 1, min( 5, $value ) );
                error_log( "  -> Salvo in ACF '$acf_field' = $value" );
                $result = update_field( $acf_field, $value, $post_id );
                error_log( "  -> update_field result: " . ( $result ? 'SUCCESS' : 'FAILED' ) );

                // Verifica immediatamente se il valore è stato salvato
                $saved_value = get_field( $acf_field, $post_id );
                error_log( "  -> VERIFICA: get_field restituisce: " . var_export( $saved_value, true ) );
            } else {
                if ( ! isset( $data[ $csv_field ] ) ) {
                    error_log( "  -> PROBLEMA: Campo '$csv_field' NON ESISTE nel CSV!" );
                } elseif ( empty( $data[ $csv_field ] ) ) {
                    error_log( "  -> Campo vuoto" );
                } elseif ( ! is_numeric( $data[ $csv_field ] ) ) {
                    error_log( "  -> PROBLEMA: Valore non numerico: '" . $data[ $csv_field ] . "'" );
                }
            }
        }

        // SEO Fields (if old_slug provided)
        error_log( '--- SEO FIELDS ---' );
        if ( ! empty( $data['Slug'] ) && $data['Slug'] !== sanitize_title( $data['Title'] ) ) {
            error_log( "Old slug: " . sanitize_title( $data['Slug'] ) );
            update_field( 'old_slug', sanitize_title( $data['Slug'] ), $post_id );
        }

        error_log( '=== FINE IMPORT ACF FIELDS PER POST ID: ' . $post_id . ' ===' );
        error_log( '' );
    }

    /**
     * Import allevamenti from CSV
     *
     * @param string $file_path Path to CSV file
     * @return array Import results
     */
    public function import_allevamenti( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path,
            );
        }

        $results = array(
            'total'    => 0,
            'imported' => 0,
            'updated'  => 0,
            'skipped'  => 0,
            'errors'   => array(),
        );

        $handle = fopen( $file_path, 'r' );
        if ( ! $handle ) {
            return array(
                'success' => false,
                'message' => 'Could not open file',
            );
        }

        // Read header row
        $headers = fgetcsv( $handle );
        if ( isset( $headers[0] ) ) {
            $headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
        }

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $results['total']++;
            $data = array_combine( $headers, $row );

            $import_result = $this->import_single_allevamento( $data );

            if ( $import_result['success'] ) {
                if ( $import_result['action'] === 'updated' ) {
                    $results['updated']++;
                } else {
                    $results['imported']++;
                }
            } else {
                $results['skipped']++;
                $results['errors'][] = array(
                    'title'   => $data['Title'],
                    'message' => $import_result['message'],
                );
            }

            if ( $results['total'] % $this->batch_size === 0 ) {
                usleep( 100000 );
            }
        }

        fclose( $handle );

        $results['success'] = true;
        $results['message'] = sprintf(
            'Importazione completata: %d totali, %d importate, %d aggiornate, %d saltate',
            $results['total'],
            $results['imported'],
            $results['updated'],
            $results['skipped']
        );

        return $results;
    }

    /**
     * Import single allevamento
     *
     * @param array $data Row data
     * @return array Result
     */
    public function import_single_allevamento( $data ) {
        $slug = ! empty( $data['Slug'] ) ? sanitize_title( $data['Slug'] ) : sanitize_title( $data['Title'] );
        $existing = get_page_by_path( $slug, OBJECT, 'allevamenti' );

        $post_data = array(
            'post_title'   => sanitize_text_field( $data['Title'] ),
            'post_content' => wp_kses_post( $data['Content'] ),
            'post_name'    => $slug,
            'post_type'    => 'allevamenti',
            'post_status'  => 'publish',
        );

        if ( $existing ) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post( $post_data );
            $action = 'updated';
        } else {
            $post_id = wp_insert_post( $post_data );
            $action = 'inserted';
        }

        if ( is_wp_error( $post_id ) ) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            );
        }

        // Assign taxonomy (provincia)
        if ( ! empty( $data['provincia_'] ) ) {
            $provincia = sanitize_text_field( $data['provincia_'] );
            wp_set_object_terms( $post_id, $provincia, 'provincia' );
        }

        // Import ACF fields for allevamento
        $acf_fields = array(
            'persona'       => 'persona',
            'desindirizzo'  => 'indirizzo',
            'deslocalita'   => 'localita',
            'provincia_'    => 'provincia',  // Corretto: era 'desprovincia'
            'codcap'        => 'cap',
            'telefono'      => 'telefono',
            'email'         => 'email',
            'sito_web'      => 'sito_web',
            'desaffisso'    => 'affisso',
            'proprietario'  => 'proprietario',
            'idaffisso'     => 'id_affisso',
        );

        foreach ( $acf_fields as $csv_field => $acf_field ) {
            if ( ! empty( $data[ $csv_field ] ) ) {
                update_field( $acf_field, sanitize_text_field( $data[ $csv_field ] ), $post_id );
            }
        }

        // Import Razze Allevate (relationship field)
        if ( ! empty( $data['Razze Allevamenti'] ) ) {
            $razze_names = explode( '|', $data['Razze Allevamenti'] );
            $razze_ids = array();

            foreach ( $razze_names as $razza_name ) {
                $razza_name = trim( $razza_name );
                if ( empty( $razza_name ) ) {
                    continue;
                }

                // Cerca la razza per titolo
                $razza_post = get_page_by_title( $razza_name, OBJECT, 'razze_di_cani' );

                if ( ! $razza_post ) {
                    // Prova ricerca case-insensitive
                    $args = array(
                        'post_type'      => 'razze_di_cani',
                        'posts_per_page' => 1,
                        'post_status'    => 'publish',
                        's'              => $razza_name,
                    );
                    $query = new WP_Query( $args );
                    if ( $query->have_posts() ) {
                        $razza_post = $query->posts[0];
                    }
                    wp_reset_postdata();
                }

                if ( $razza_post ) {
                    $razze_ids[] = $razza_post->ID;
                }
            }

            // Salva il relationship field
            if ( ! empty( $razze_ids ) ) {
                update_field( 'razze_allevate', $razze_ids, $post_id );
            }
        }

        return array(
            'success' => true,
            'action'  => $action,
            'post_id' => $post_id,
        );
    }

    /**
     * Import veterinari from CSV
     *
     * @param string $file_path Path to CSV file
     * @return array Import results
     */
    public function import_veterinari( $file_path ) {
        return $this->import_struttura( $file_path, 'veterinari' );
    }

    /**
     * Import canili from CSV
     */
    public function import_canili( $file_path ) {
        return $this->import_struttura( $file_path, 'canili' );
    }

    /**
     * Import pensioni from CSV
     */
    public function import_pensioni( $file_path ) {
        return $this->import_struttura( $file_path, 'pensioni_per_cani' );
    }

    /**
     * Import centri cinofili from CSV
     */
    public function import_centri_cinofili( $file_path ) {
        return $this->import_struttura( $file_path, 'centri_cinofili' );
    }

    /**
     * Import generic struttura from CSV
     *
     * @param string $file_path Path to CSV file
     * @param string $post_type Post type
     * @return array Import results
     */
    private function import_struttura( $file_path, $post_type ) {
        if ( ! file_exists( $file_path ) ) {
            return array(
                'success' => false,
                'message' => 'File not found: ' . $file_path,
            );
        }

        $results = array(
            'total'    => 0,
            'imported' => 0,
            'updated'  => 0,
            'skipped'  => 0,
            'errors'   => array(),
        );

        $handle = fopen( $file_path, 'r' );
        if ( ! $handle ) {
            return array(
                'success' => false,
                'message' => 'Could not open file',
            );
        }

        $headers = fgetcsv( $handle );
        if ( isset( $headers[0] ) ) {
            $headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
        }

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $results['total']++;
            $data = array_combine( $headers, $row );

            $import_result = $this->import_single_struttura( $data, $post_type );

            if ( $import_result['success'] ) {
                if ( $import_result['action'] === 'updated' ) {
                    $results['updated']++;
                } else {
                    $results['imported']++;
                }
            } else {
                $results['skipped']++;
                $results['errors'][] = array(
                    'title'   => $data['Title'],
                    'message' => $import_result['message'],
                );
            }

            if ( $results['total'] % $this->batch_size === 0 ) {
                usleep( 100000 );
            }
        }

        fclose( $handle );

        $results['success'] = true;
        $results['message'] = sprintf(
            'Importazione completata: %d totali, %d importate, %d aggiornate, %d saltate',
            $results['total'],
            $results['imported'],
            $results['updated'],
            $results['skipped']
        );

        return $results;
    }

    /**
     * Import single struttura
     *
     * @param array  $data      Row data
     * @param string $post_type Post type
     * @return array Result
     */
    private function import_single_struttura( $data, $post_type ) {
        $slug = ! empty( $data['Slug'] ) ? sanitize_title( $data['Slug'] ) : sanitize_title( $data['Title'] );
        $existing = get_page_by_path( $slug, OBJECT, $post_type );

        $post_data = array(
            'post_title'   => sanitize_text_field( $data['Title'] ),
            'post_content' => wp_kses_post( $data['Content'] ?? '' ),
            'post_excerpt' => wp_kses_post( $data['Excerpt'] ?? '' ),
            'post_name'    => $slug,
            'post_type'    => $post_type,
            'post_status'  => 'publish',
        );

        if ( $existing ) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post( $post_data );
            $action = 'updated';
        } else {
            $post_id = wp_insert_post( $post_data );
            $action = 'inserted';
        }

        if ( is_wp_error( $post_id ) ) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            );
        }

        // Assign provincia taxonomy
        if ( ! empty( $data['provincia'] ) ) {
            wp_set_object_terms( $post_id, sanitize_text_field( $data['provincia'] ), 'provincia' );
        }

        // Import ACF fields for struttura
        // Campi comuni a tutte le strutture
        $common_fields = array(
            'nome_struttura' => 'nome_struttura',
            'indirizzo'      => 'indirizzo',
            'localita'       => 'localita',
            'cap'            => 'cap',
            'comune'         => 'comune',
            'provincia'      => 'provincia',
            'provincia_estesa' => 'provincia_estesa',
            'regione'        => 'regione',
            'telefono'       => 'telefono',
            'email'          => 'email',
            'sito_web'       => 'sito_web',
            'referente'      => 'referente',
            'riferimento'    => 'referente', // Alias per canili
        );

        foreach ( $common_fields as $csv_field => $acf_field ) {
            if ( isset( $data[ $csv_field ] ) && $data[ $csv_field ] !== '' ) {
                update_field( $acf_field, sanitize_text_field( $data[ $csv_field ] ), $post_id );
            }
        }

        // Handle 'indirizzo:' field for Canili (con due punti)
        if ( isset( $data['indirizzo:'] ) && $data['indirizzo:'] !== '' ) {
            update_field( 'indirizzo', sanitize_text_field( $data['indirizzo:'] ), $post_id );
        }

        // Handle 'sito' field (alias for sito_web in some CSVs)
        if ( isset( $data['sito'] ) && $data['sito'] !== '' ) {
            update_field( 'sito_web', sanitize_text_field( $data['sito'] ), $post_id );
        }

        // Campi specifici per VETERINARI
        if ( $post_type === 'veterinari' ) {
            $vet_fields = array(
                'tipologia'              => 'tipologia',
                'direttore_sanitario'    => 'direttore_sanitario',
                'pronto_soccorso_h24'    => 'pronto_soccorso',
                'reperibilita_h24'       => 'reperibilita',
                'specie_animali_trattate' => 'specie_trattate',
                'servizi_offerti'        => 'servizi',
            );

            foreach ( $vet_fields as $csv_field => $acf_field ) {
                if ( isset( $data[ $csv_field ] ) && $data[ $csv_field ] !== '' ) {
                    update_field( $acf_field, sanitize_text_field( $data[ $csv_field ] ), $post_id );
                }
            }

            // Campo orari (può contenere HTML)
            if ( isset( $data['orari_di_apertura'] ) && $data['orari_di_apertura'] !== '' ) {
                update_field( 'orari', wp_kses_post( $data['orari_di_apertura'] ), $post_id );
            }
        }

        // Campi specifici per PENSIONI e CENTRI CINOFILI
        if ( $post_type === 'pensioni_per_cani' || $post_type === 'centri_cinofili' ) {
            if ( isset( $data['altre_informazioni'] ) && $data['altre_informazioni'] !== '' ) {
                update_field( 'altre_informazioni', sanitize_text_field( $data['altre_informazioni'] ), $post_id );
            }
        }

        return array(
            'success' => true,
            'action'  => $action,
            'post_id' => $post_id,
        );
    }

    /**
     * Import image from URL and attach to post
     *
     * @param int    $post_id Post ID
     * @param string $image_url Image URL
     * @param string $title Image title
     * @return int|bool Attachment ID or false
     */
    private function import_image( $post_id, $image_url, $title = '' ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Download image
        $tmp = download_url( $image_url );

        if ( is_wp_error( $tmp ) ) {
            return false;
        }

        // Get file extension
        $file_array = array(
            'name'     => basename( $image_url ),
            'tmp_name' => $tmp,
        );

        // Upload to media library
        $attachment_id = media_handle_sideload( $file_array, $post_id, $title );

        // Cleanup temp file
        if ( file_exists( $tmp ) ) {
            @unlink( $tmp );
        }

        if ( is_wp_error( $attachment_id ) ) {
            return false;
        }

        // Set as featured image
        set_post_thumbnail( $post_id, $attachment_id );

        return $attachment_id;
    }
}

/**
 * Get CSV Importer instance
 */
function caniincasa_csv_importer() {
    return new Caniincasa_CSV_Importer();
}
