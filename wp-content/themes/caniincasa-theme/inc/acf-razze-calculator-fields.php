<?php
/**
 * ACF Fields for Razze di Cani - Calculator Data
 *
 * Campi necessari per i calcolatori:
 * - Età Umana
 * - Peso Ideale
 * - Costi Mantenimento
 *
 * @package Caniincasa
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Registra i campi ACF solo se ACF è attivo
if ( function_exists( 'acf_add_local_field_group' ) ) {

    /**
     * Gruppo Campi: Dati Calcolatore Età
     */
    acf_add_local_field_group( array(
        'key' => 'group_razza_calculator_age',
        'title' => 'Dati Calcolatore Età Umana',
        'fields' => array(
            array(
                'key' => 'field_taglia_standard',
                'label' => 'Taglia Standard',
                'name' => 'taglia_standard',
                'type' => 'select',
                'instructions' => 'Classificazione della taglia della razza adulta',
                'required' => 1,
                'choices' => array(
                    'toy' => 'Toy (< 5 kg)',
                    'piccola' => 'Piccola (5-10 kg)',
                    'media' => 'Media (10-25 kg)',
                    'grande' => 'Grande (25-45 kg)',
                    'gigante' => 'Gigante (> 45 kg)',
                ),
                'default_value' => 'media',
                'allow_null' => 0,
                'return_format' => 'value',
            ),
            array(
                'key' => 'field_aspettativa_vita_min',
                'label' => 'Aspettativa Vita Minima (anni)',
                'name' => 'aspettativa_vita_min',
                'type' => 'number',
                'instructions' => 'Aspettativa di vita minima in anni',
                'required' => 1,
                'min' => 5,
                'max' => 20,
                'step' => 1,
            ),
            array(
                'key' => 'field_aspettativa_vita_max',
                'label' => 'Aspettativa Vita Massima (anni)',
                'name' => 'aspettativa_vita_max',
                'type' => 'number',
                'instructions' => 'Aspettativa di vita massima in anni',
                'required' => 1,
                'min' => 5,
                'max' => 20,
                'step' => 1,
            ),
            array(
                'key' => 'field_coefficiente_cucciolo',
                'label' => 'Coefficiente Cucciolo',
                'name' => 'coefficiente_cucciolo',
                'type' => 'number',
                'instructions' => 'Coefficiente di crescita per cuccioli (0-2 anni). Default: 15',
                'required' => 1,
                'default_value' => 15,
                'min' => 10,
                'max' => 20,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_coefficiente_adulto',
                'label' => 'Coefficiente Adulto',
                'name' => 'coefficiente_adulto',
                'type' => 'number',
                'instructions' => 'Coefficiente di invecchiamento per adulti (2-7 anni). Range: 4-7',
                'required' => 1,
                'default_value' => 5,
                'min' => 3,
                'max' => 10,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_coefficiente_senior',
                'label' => 'Coefficiente Senior',
                'name' => 'coefficiente_senior',
                'type' => 'number',
                'instructions' => 'Coefficiente di invecchiamento per anziani (7+ anni). Range: 4-9',
                'required' => 1,
                'default_value' => 5.5,
                'min' => 3,
                'max' => 12,
                'step' => 0.5,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'razze_di_cani',
                ),
            ),
        ),
        'menu_order' => 10,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ) );

    /**
     * Gruppo Campi: Dati Calcolatore Peso Ideale
     */
    acf_add_local_field_group( array(
        'key' => 'group_razza_calculator_weight',
        'title' => 'Dati Calcolatore Peso Ideale',
        'fields' => array(
            array(
                'key' => 'field_peso_ideale_min_maschio',
                'label' => 'Peso Ideale Min Maschio (kg)',
                'name' => 'peso_ideale_min_maschio',
                'type' => 'number',
                'instructions' => 'Peso minimo ideale per maschi adulti',
                'required' => 0,
                'min' => 0.5,
                'max' => 100,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_peso_ideale_max_maschio',
                'label' => 'Peso Ideale Max Maschio (kg)',
                'name' => 'peso_ideale_max_maschio',
                'type' => 'number',
                'instructions' => 'Peso massimo ideale per maschi adulti',
                'required' => 0,
                'min' => 0.5,
                'max' => 100,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_peso_ideale_min_femmina',
                'label' => 'Peso Ideale Min Femmina (kg)',
                'name' => 'peso_ideale_min_femmina',
                'type' => 'number',
                'instructions' => 'Peso minimo ideale per femmine adulte',
                'required' => 0,
                'min' => 0.5,
                'max' => 100,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_peso_ideale_max_femmina',
                'label' => 'Peso Ideale Max Femmina (kg)',
                'name' => 'peso_ideale_max_femmina',
                'type' => 'number',
                'instructions' => 'Peso massimo ideale per femmine adulte',
                'required' => 0,
                'min' => 0.5,
                'max' => 100,
                'step' => 0.5,
            ),
            array(
                'key' => 'field_livello_attivita',
                'label' => 'Livello Attività',
                'name' => 'livello_attivita',
                'type' => 'select',
                'instructions' => 'Livello di attività tipico della razza',
                'required' => 0,
                'choices' => array(
                    'basso' => 'Basso (sedentario)',
                    'moderato' => 'Moderato (passeggiate regolari)',
                    'alto' => 'Alto (molto attivo)',
                    'molto_alto' => 'Molto Alto (sportivo/da lavoro)',
                ),
                'default_value' => 'moderato',
                'allow_null' => 0,
                'return_format' => 'value',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'razze_di_cani',
                ),
            ),
        ),
        'menu_order' => 20,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ) );

    /**
     * Gruppo Campi: Dati Calcolatore Costi Mantenimento
     */
    acf_add_local_field_group( array(
        'key' => 'group_razza_calculator_costs',
        'title' => 'Dati Calcolatore Costi Mantenimento',
        'fields' => array(
            array(
                'key' => 'field_costo_alimentazione_mensile',
                'label' => 'Costo Alimentazione Mensile (€)',
                'name' => 'costo_alimentazione_mensile',
                'type' => 'number',
                'instructions' => 'Costo medio mensile per alimentazione di qualità',
                'required' => 0,
                'min' => 10,
                'max' => 500,
                'step' => 5,
            ),
            array(
                'key' => 'field_costo_veterinario_annuale',
                'label' => 'Costo Veterinario Annuale (€)',
                'name' => 'costo_veterinario_annuale',
                'type' => 'number',
                'instructions' => 'Costo medio annuale per visite veterinarie e prevenzione',
                'required' => 0,
                'min' => 50,
                'max' => 2000,
                'step' => 10,
            ),
            array(
                'key' => 'field_costo_toelettatura_annuale',
                'label' => 'Costo Toelettatura Annuale (€)',
                'name' => 'costo_toelettatura_annuale',
                'type' => 'number',
                'instructions' => 'Costo medio annuale per toelettatura professionale',
                'required' => 0,
                'min' => 0,
                'max' => 2000,
                'step' => 10,
            ),
            array(
                'key' => 'field_predisposizioni_salute',
                'label' => 'Predisposizioni Problemi Salute',
                'name' => 'predisposizioni_salute',
                'type' => 'select',
                'instructions' => 'Livello di predisposizione a problemi di salute comuni nella razza',
                'required' => 0,
                'choices' => array(
                    'bassa' => 'Bassa (razza generalmente sana)',
                    'media' => 'Media (alcuni problemi comuni)',
                    'alta' => 'Alta (diverse predisposizioni note)',
                ),
                'default_value' => 'media',
                'allow_null' => 0,
                'return_format' => 'value',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'razze_di_cani',
                ),
            ),
        ),
        'menu_order' => 30,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ) );

}
