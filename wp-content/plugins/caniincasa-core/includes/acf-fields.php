<?php
/**
 * ACF Fields Configuration
 *
 * Register all ACF field groups via PHP
 * Requires ACF Pro to be installed
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if ACF is active
 */
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
}

/**
 * ACF Fields for Razze di Cani CPT
 */
function caniincasa_register_razze_acf_fields() {

    /**
     * Informazioni Razza (Sidebar)
     */
    acf_add_local_field_group( array(
        'key'      => 'group_razze_info',
        'title'    => 'Informazioni Razza',
        'fields'   => array(
            array(
                'key'   => 'field_nazione_origine',
                'label' => 'Nazione di Origine',
                'name'  => 'nazione_origine',
                'type'  => 'text',
            ),
            array(
                'key'          => 'field_colorazioni',
                'label'        => 'Colorazioni',
                'name'         => 'colorazioni',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Elencare le colorazioni tipiche della razza',
            ),
            array(
                'key'       => 'field_temperamento_breve',
                'label'     => 'Temperamento (Breve)',
                'name'      => 'temperamento_breve',
                'type'      => 'text',
                'maxlength' => 100,
            ),
            array(
                'key'   => 'field_peso_medio_min',
                'label' => 'Peso Medio Min (kg)',
                'name'  => 'peso_medio_min',
                'type'  => 'number',
                'min'   => 0,
                'step'  => 0.5,
            ),
            array(
                'key'   => 'field_peso_medio_max',
                'label' => 'Peso Medio Max (kg)',
                'name'  => 'peso_medio_max',
                'type'  => 'number',
                'min'   => 0,
                'step'  => 0.5,
            ),
            array(
                'key'   => 'field_aspettativa_vita_min',
                'label' => 'Aspettativa di Vita Min (anni)',
                'name'  => 'aspettativa_vita_min',
                'type'  => 'number',
                'min'   => 1,
                'max'   => 30,
            ),
            array(
                'key'   => 'field_aspettativa_vita_max',
                'label' => 'Aspettativa di Vita Max (anni)',
                'name'  => 'aspettativa_vita_max',
                'type'  => 'number',
                'min'   => 1,
                'max'   => 30,
            ),
            array(
                'key'   => 'field_altezza_min',
                'label' => 'Altezza Min (cm)',
                'name'  => 'altezza_min',
                'type'  => 'number',
                'min'   => 0,
            ),
            array(
                'key'   => 'field_altezza_max',
                'label' => 'Altezza Max (cm)',
                'name'  => 'altezza_max',
                'type'  => 'number',
                'min'   => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'razze_di_cani',
                ),
            ),
        ),
        'position' => 'side',
        'style'    => 'default',
    ) );

    /**
     * Caratteristiche Razza (Rating 1-5)
     */
    acf_add_local_field_group( array(
        'key'      => 'group_razze_caratteristiche',
        'title'    => 'Caratteristiche della Razza',
        'fields'   => array(
            // Temperamento & Comportamento
            array(
                'key'          => 'field_affettuosita',
                'label'        => 'Affettuosità',
                'name'         => 'affettuosita',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_socievolezza_cani',
                'label'        => 'Socievolezza con Altri Cani',
                'name'         => 'socievolezza_cani',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_tolleranza_estranei',
                'label'        => 'Tolleranza verso Estranei',
                'name'         => 'tolleranza_estranei',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_compatibilita_bambini',
                'label'        => 'Compatibilità con Bambini',
                'name'         => 'compatibilita_con_i_bambini',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_compatibilita_altri_animali',
                'label'        => 'Compatibilità con Altri Animali',
                'name'         => 'compatibilita_con_altri_animali_domestici',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_vocalita',
                'label'        => 'Vocalità / Predisposizione ad Abbaiare',
                'name'         => 'vocalita_e_predisposizione_ad_abbaiare',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),

            // Adattabilità
            array(
                'key'          => 'field_adattabilita_appartamento',
                'label'        => 'Adattabilità ad Appartamento',
                'name'         => 'adattabilita_appartamento',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_adattabilita_clima_caldo',
                'label'        => 'Adattabilità Clima Caldo',
                'name'         => 'adattabilita_clima_caldo',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_adattabilita_clima_freddo',
                'label'        => 'Adattabilità Clima Freddo',
                'name'         => 'adattabilita_clima_freddo',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_tolleranza_solitudine',
                'label'        => 'Tolleranza alla Solitudine',
                'name'         => 'tolleranza_alla_solitudine',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),

            // Addestramento & Intelligenza
            array(
                'key'          => 'field_intelligenza',
                'label'        => 'Intelligenza',
                'name'         => 'intelligenza',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_facilita_addestramento',
                'label'        => 'Facilità di Addestramento',
                'name'         => 'facilita_di_addestramento',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_livello_esperienza',
                'label'        => 'Livello Esperienza Richiesto',
                'name'         => 'livello_esperienza_richiesto',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
                'instructions' => '1 = Principiante, 5 = Esperto',
            ),

            // Attività & Energia
            array(
                'key'          => 'field_energia_attivita',
                'label'        => 'Energia e Livelli di Attività',
                'name'         => 'energia_e_livelli_di_attivita',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_esigenze_esercizio',
                'label'        => 'Esigenze di Esercizio',
                'name'         => 'esigenze_di_esercizio',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_istinti_caccia',
                'label'        => 'Istinti di Caccia',
                'name'         => 'istinti_di_caccia',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),

            // Cura & Salute
            array(
                'key'          => 'field_facilita_toelettatura',
                'label'        => 'Facilità Toelettatura',
                'name'         => 'facilita_toelettatura',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_cura_perdita_pelo',
                'label'        => 'Cura e Perdita Pelo',
                'name'         => 'cura_e_perdita_pelo',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
            array(
                'key'          => 'field_predisposizioni_salute',
                'label'        => 'Predisposizioni per la Salute',
                'name'         => 'predisposizioni_per_la_salute',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
                'instructions' => '1 = Poche predisposizioni, 5 = Molte predisposizioni',
            ),
            array(
                'key'          => 'field_costo_mantenimento',
                'label'        => 'Costo di Mantenimento',
                'name'         => 'costo_mantenimento',
                'type'         => 'range',
                'min'          => 1,
                'max'          => 5,
                'step'         => 0.5,
                'default_value'=> 3,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'razze_di_cani',
                ),
            ),
        ),
        'position' => 'normal',
        'style'    => 'default',
    ) );

    /**
     * Contenuti Testuali Dettagliati
     */
    acf_add_local_field_group( array(
        'key'      => 'group_razze_contenuti',
        'title'    => 'Contenuti Dettagliati',
        'fields'   => array(
            array(
                'key'   => 'field_descrizione_generale',
                'label' => 'Descrizione Generale',
                'name'  => 'descrizione_generale',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_origini_storia',
                'label' => 'Origini e Storia',
                'name'  => 'origini_storia',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_aspetto_fisico',
                'label' => 'Aspetto Fisico',
                'name'  => 'aspetto_fisico',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_carattere_temperamento',
                'label' => 'Carattere e Temperamento',
                'name'  => 'carattere_temperamento',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_salute_cura',
                'label' => 'Salute e Cura',
                'name'  => 'salute_cura',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_attivita_addestramento',
                'label' => 'Attività e Addestramento',
                'name'  => 'attivita_addestramento',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
            array(
                'key'   => 'field_ideale_per',
                'label' => 'Ideale Per',
                'name'  => 'ideale_per',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar'=> 'full',
                'media_upload' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'razze_di_cani',
                ),
            ),
        ),
        'position' => 'normal',
        'style'    => 'default',
    ) );

    /**
     * SEO & Import Fields
     */
    acf_add_local_field_group( array(
        'key'      => 'group_razze_seo',
        'title'    => 'SEO & Import',
        'fields'   => array(
            array(
                'key'          => 'field_permalink_esistente',
                'label'        => 'Permalink Esistente',
                'name'         => 'permalink_esistente',
                'type'         => 'text',
                'instructions' => 'URL originale per mantenere SEO durante import',
                'readonly'     => 1,
            ),
            array(
                'key'          => 'field_old_slug',
                'label'        => 'Old Slug (per redirect 301)',
                'name'         => 'old_slug',
                'type'         => 'text',
                'instructions' => 'Slug precedente per redirect automatico',
            ),
            array(
                'key'          => 'field_import_id',
                'label'        => 'ID Importazione',
                'name'         => 'import_id',
                'type'         => 'text',
                'instructions' => 'ID originale dal sistema precedente',
                'readonly'     => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'razze_di_cani',
                ),
            ),
        ),
        'position'     => 'normal',
        'style'        => 'default',
        'hide_on_screen' => array( 'the_content' ),
    ) );
}
caniincasa_register_razze_acf_fields();

/**
 * ACF Fields for Strutture CPTs (shared fields)
 */
function caniincasa_register_strutture_acf_fields() {

    $strutture_types = array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' );

    acf_add_local_field_group( array(
        'key'      => 'group_strutture_contatti',
        'title'    => 'Informazioni e Contatti',
        'fields'   => array(
            array(
                'key'   => 'field_nome_struttura',
                'label' => 'Nome Struttura',
                'name'  => 'nome_struttura',
                'type'  => 'text',
            ),
            array(
                'key'     => 'field_indirizzo',
                'label'   => 'Indirizzo',
                'name'    => 'indirizzo',
                'type'    => 'text',
                'required'=> 1,
            ),
            array(
                'key'   => 'field_localita',
                'label' => 'Località',
                'name'  => 'localita',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_comune',
                'label' => 'Comune',
                'name'  => 'comune',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_citta',
                'label' => 'Città',
                'name'  => 'citta',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_provincia',
                'label' => 'Provincia',
                'name'  => 'provincia',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_provincia_estesa',
                'label' => 'Provincia Estesa',
                'name'  => 'provincia_estesa',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_regione',
                'label' => 'Regione',
                'name'  => 'regione',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_cap',
                'label' => 'CAP',
                'name'  => 'cap',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_telefono',
                'label' => 'Telefono',
                'name'  => 'telefono',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_cellulare',
                'label' => 'Cellulare / WhatsApp',
                'name'  => 'cellulare',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_email',
                'label' => 'Email',
                'name'  => 'email',
                'type'  => 'email',
            ),
            array(
                'key'   => 'field_sito_web',
                'label' => 'Sito Web',
                'name'  => 'sito_web',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_referente',
                'label' => 'Referente / Riferimento',
                'name'  => 'referente',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_facebook',
                'label' => 'Facebook',
                'name'  => 'facebook',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_instagram',
                'label' => 'Instagram',
                'name'  => 'instagram',
                'type'  => 'url',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[0],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[1],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[2],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[3],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[4],
                ),
            ),
        ),
        'position' => 'normal',
    ) );

    acf_add_local_field_group( array(
        'key'      => 'group_strutture_geo',
        'title'    => 'Geolocalizzazione',
        'fields'   => array(
            array(
                'key'   => 'field_latitudine',
                'label' => 'Latitudine',
                'name'  => 'latitudine',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_longitudine',
                'label' => 'Longitudine',
                'name'  => 'longitudine',
                'type'  => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[0],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[1],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[2],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[3],
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => $strutture_types[4],
                ),
            ),
        ),
        'position' => 'side',
    ) );

    // Additional fields for Veterinari
    acf_add_local_field_group( array(
        'key'      => 'group_veterinari_specifici',
        'title'    => 'Informazioni Specifiche Veterinario',
        'fields'   => array(
            array(
                'key'   => 'field_tipologia',
                'label' => 'Tipologia',
                'name'  => 'tipologia',
                'type'  => 'text',
                'instructions' => 'Es: Clinica veterinaria, Ambulatorio, ecc.',
            ),
            array(
                'key'   => 'field_direttore_sanitario',
                'label' => 'Direttore Sanitario',
                'name'  => 'direttore_sanitario',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_pronto_soccorso',
                'label' => 'Pronto Soccorso H24',
                'name'  => 'pronto_soccorso',
                'type'  => 'text',
                'instructions' => 'Specificare se disponibile servizio H24',
            ),
            array(
                'key'   => 'field_reperibilita',
                'label' => 'Reperibilità H24',
                'name'  => 'reperibilita',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_specie_trattate',
                'label' => 'Specie Animali Trattate',
                'name'  => 'specie_trattate',
                'type'  => 'textarea',
                'rows'  => 3,
            ),
            array(
                'key'   => 'field_servizi',
                'label' => 'Servizi Offerti',
                'name'  => 'servizi',
                'type'  => 'textarea',
                'rows'  => 5,
            ),
            array(
                'key'   => 'field_orari',
                'label' => 'Orari di Apertura',
                'name'  => 'orari',
                'type'  => 'wysiwyg',
                'tabs'  => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'instructions' => 'Può contenere HTML. Usare con attenzione.',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'veterinari',
                ),
            ),
        ),
        'position' => 'normal',
    ) );

    // Additional fields for Pensioni and Centri Cinofili
    acf_add_local_field_group( array(
        'key'      => 'group_strutture_altre_info',
        'title'    => 'Altre Informazioni',
        'fields'   => array(
            array(
                'key'   => 'field_altre_informazioni',
                'label' => 'Altre Informazioni',
                'name'  => 'altre_informazioni',
                'type'  => 'textarea',
                'rows'  => 5,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'pensioni_per_cani',
                ),
            ),
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'centri_cinofili',
                ),
            ),
        ),
        'position' => 'normal',
    ) );
}
caniincasa_register_strutture_acf_fields();

/**
 * ACF Fields for Annunci 4 Zampe
 */
function caniincasa_register_annunci_4zampe_acf_fields() {
    acf_add_local_field_group( array(
        'key'      => 'group_annunci_4zampe',
        'title'    => 'Dettagli Annuncio',
        'fields'   => array(
            array(
                'key'      => 'field_tipo_annuncio',
                'label'    => 'Tipo Annuncio',
                'name'     => 'tipo_annuncio',
                'type'     => 'select',
                'required' => 1,
                'choices'  => array(
                    'cerco' => 'Cerco',
                    'offro' => 'Offro',
                ),
            ),
            array(
                'key'      => 'field_eta_cane',
                'label'    => 'Età',
                'name'     => 'eta',
                'type'     => 'select',
                'required' => 1,
                'choices'  => array(
                    'cucciolo' => 'Cucciolo',
                    'adulto'   => 'Adulto',
                ),
            ),
            array(
                'key'      => 'field_tipo_cane',
                'label'    => 'Tipo Cane',
                'name'     => 'tipo_cane',
                'type'     => 'select',
                'required' => 1,
                'choices'  => array(
                    'meticcio' => 'Meticcio',
                    'razza'    => 'Razza',
                ),
            ),
            array(
                'key'               => 'field_razza',
                'label'             => 'Razza',
                'name'              => 'razza',
                'type'              => 'post_object',
                'post_type'         => array( 'razze_di_cani' ),
                'return_format'     => 'id',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_tipo_cane',
                            'operator' => '==',
                            'value'    => 'razza',
                        ),
                    ),
                ),
            ),
            array(
                'key'   => 'field_contatto_preferito',
                'label' => 'Contatto Preferito',
                'name'  => 'contatto_preferito',
                'type'  => 'select',
                'choices' => array(
                    'email'    => 'Email',
                    'telefono' => 'Telefono',
                    'whatsapp' => 'WhatsApp',
                ),
            ),
            array(
                'key'   => 'field_giorni_scadenza',
                'label' => 'Giorni Validità Annuncio',
                'name'  => 'giorni_scadenza',
                'type'  => 'select',
                'choices' => array(
                    '30' => '30 giorni',
                    '60' => '60 giorni',
                    '90' => '90 giorni',
                ),
                'default_value' => '30',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'annunci_4zampe',
                ),
            ),
        ),
        'position' => 'normal',
    ) );
}
caniincasa_register_annunci_4zampe_acf_fields();

/**
 * ACF Fields for Annunci Dogsitter
 */
function caniincasa_register_annunci_dogsitter_acf_fields() {
    acf_add_local_field_group( array(
        'key'      => 'group_annunci_dogsitter',
        'title'    => 'Dettagli Servizio',
        'fields'   => array(
            array(
                'key'      => 'field_tipo_servizio',
                'label'    => 'Tipo',
                'name'     => 'tipo',
                'type'     => 'select',
                'required' => 1,
                'choices'  => array(
                    'cerco'   => 'Cerco Dogsitter',
                    'offro'   => 'Offro Servizio Dogsitter',
                ),
            ),
            array(
                'key'   => 'field_disponibilita',
                'label' => 'Disponibilità',
                'name'  => 'disponibilita',
                'type'  => 'textarea',
                'rows'  => 3,
            ),
            array(
                'key'      => 'field_servizi_offerti',
                'label'    => 'Servizi Offerti',
                'name'     => 'servizi_offerti',
                'type'     => 'checkbox',
                'choices'  => array(
                    'passeggiate'       => 'Passeggiate',
                    'pensione'          => 'Pensione',
                    'visita_domicilio'  => 'Visita a Domicilio',
                    'toelettatura'      => 'Toelettatura',
                    'addestramento'     => 'Addestramento Base',
                ),
            ),
            array(
                'key'   => 'field_esperienza',
                'label' => 'Esperienza',
                'name'  => 'esperienza',
                'type'  => 'select',
                'choices' => array(
                    'principiante' => 'Principiante',
                    'intermedio'   => 'Intermedio',
                    'esperto'      => 'Esperto',
                    'professionale'=> 'Professionale',
                ),
            ),
            array(
                'key'   => 'field_prezzo_indicativo',
                'label' => 'Prezzo Indicativo',
                'name'  => 'prezzo_indicativo',
                'type'  => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'annunci_dogsitter',
                ),
            ),
        ),
        'position' => 'normal',
    ) );
}
caniincasa_register_annunci_dogsitter_acf_fields();

/**
 * ACF Fields for Allevamenti
 */
function caniincasa_register_allevamenti_acf_fields() {
    acf_add_local_field_group( array(
        'key'      => 'group_allevamenti',
        'title'    => 'Informazioni Allevamento',
        'fields'   => array(
            array(
                'key'   => 'field_razze_allevate',
                'label' => 'Razze Allevate',
                'name'  => 'razze_allevate',
                'type'  => 'relationship',
                'instructions' => 'Seleziona le razze allevate in questa struttura',
                'post_type' => array(
                    0 => 'razze_di_cani',
                ),
                'filters' => array(
                    0 => 'search',
                ),
                'return_format' => 'object',
                'min' => 0,
                'max' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'allevamenti',
                ),
            ),
        ),
        'position' => 'normal',
    ) );
}
caniincasa_register_allevamenti_acf_fields();
