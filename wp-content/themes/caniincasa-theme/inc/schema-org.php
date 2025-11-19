<?php
/**
 * Schema.org Structured Data Implementation
 * Implements JSON-LD markup for local businesses (structures)
 *
 * @package Caniincasa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate Schema.org JSON-LD for veterinari
 *
 * @param int $post_id Post ID
 * @return string JSON-LD markup
 */
function caniincasa_get_veterinari_schema( $post_id ) {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'VeterinaryCare',
        'name'     => get_the_title( $post_id ),
        'url'      => get_permalink( $post_id ),
    );

    // Indirizzo
    $indirizzo = get_field( 'indirizzo', $post_id );
    $citta = get_field( 'citta', $post_id );
    $provincia = get_field( 'provincia', $post_id );
    $cap = get_field( 'cap', $post_id );

    if ( $indirizzo || $citta ) {
        $schema['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $indirizzo,
            'addressLocality' => $citta,
            'addressRegion'   => $provincia,
            'postalCode'      => $cap,
            'addressCountry'  => 'IT',
        );
    }

    // Coordinate geografiche
    $latitudine = get_field( 'latitudine', $post_id );
    $longitudine = get_field( 'longitudine', $post_id );

    if ( $latitudine && $longitudine ) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latitudine,
            'longitude' => $longitudine,
        );
    }

    // Contatti
    $telefono = get_field( 'telefono', $post_id );
    if ( $telefono ) {
        $schema['telephone'] = $telefono;
    }

    $email = get_field( 'email', $post_id );
    if ( $email ) {
        $schema['email'] = $email;
    }

    // Sito web
    $sito_web = get_field( 'sito_web', $post_id );
    if ( $sito_web ) {
        $schema['url'] = $sito_web;
    }

    // Descrizione
    $descrizione = get_field( 'descrizione', $post_id );
    if ( $descrizione ) {
        $schema['description'] = wp_strip_all_tags( $descrizione );
    }

    // Orari di apertura
    $orari = get_field( 'orari_apertura', $post_id );
    if ( $orari ) {
        $schema['openingHours'] = $orari;
    }

    // Immagine
    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        if ( $image_url ) {
            $schema['image'] = $image_url;
        }
    }

    // PriceRange (se disponibile)
    $prezzi = get_field( 'prezzi', $post_id );
    if ( $prezzi ) {
        $schema['priceRange'] = $prezzi;
    }

    return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>';
}

/**
 * Generate Schema.org JSON-LD for allevamenti
 *
 * @param int $post_id Post ID
 * @return string JSON-LD markup
 */
function caniincasa_get_allevamenti_schema( $post_id ) {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'PetStore',
        'name'     => get_the_title( $post_id ),
        'url'      => get_permalink( $post_id ),
    );

    // Indirizzo
    $indirizzo = get_field( 'indirizzo', $post_id );
    $citta = get_field( 'citta', $post_id );
    $provincia = get_field( 'provincia', $post_id );
    $cap = get_field( 'cap', $post_id );

    if ( $indirizzo || $citta ) {
        $schema['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $indirizzo,
            'addressLocality' => $citta,
            'addressRegion'   => $provincia,
            'postalCode'      => $cap,
            'addressCountry'  => 'IT',
        );
    }

    // Coordinate geografiche
    $latitudine = get_field( 'latitudine', $post_id );
    $longitudine = get_field( 'longitudine', $post_id );

    if ( $latitudine && $longitudine ) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latitudine,
            'longitude' => $longitudine,
        );
    }

    // Contatti
    $telefono = get_field( 'telefono', $post_id );
    if ( $telefono ) {
        $schema['telephone'] = $telefono;
    }

    $email = get_field( 'email', $post_id );
    if ( $email ) {
        $schema['email'] = $email;
    }

    // Sito web
    $sito_web = get_field( 'sito_web', $post_id );
    if ( $sito_web ) {
        $schema['url'] = $sito_web;
    }

    // Descrizione
    $descrizione = get_field( 'descrizione', $post_id );
    if ( $descrizione ) {
        $schema['description'] = wp_strip_all_tags( $descrizione );
    }

    // Orari di apertura
    $orari = get_field( 'orari_apertura', $post_id );
    if ( $orari ) {
        $schema['openingHours'] = $orari;
    }

    // Immagine
    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        if ( $image_url ) {
            $schema['image'] = $image_url;
        }
    }

    // Razze allevate (come offerta)
    $razze_allevate = get_field( 'razze_allevate', $post_id );
    if ( $razze_allevate && is_array( $razze_allevate ) ) {
        $offers = array();
        foreach ( $razze_allevate as $razza ) {
            if ( is_object( $razza ) ) {
                $offers[] = array(
                    '@type' => 'Offer',
                    'itemOffered' => array(
                        '@type' => 'Product',
                        'name'  => $razza->post_title,
                    ),
                );
            }
        }
        if ( ! empty( $offers ) ) {
            $schema['hasOfferCatalog'] = array(
                '@type' => 'OfferCatalog',
                'name'  => 'Razze Allevate',
                'itemListElement' => $offers,
            );
        }
    }

    return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>';
}

/**
 * Generate Schema.org JSON-LD for canili
 *
 * @param int $post_id Post ID
 * @return string JSON-LD markup
 */
function caniincasa_get_canili_schema( $post_id ) {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'AnimalShelter',
        'name'     => get_the_title( $post_id ),
        'url'      => get_permalink( $post_id ),
    );

    // Indirizzo
    $indirizzo = get_field( 'indirizzo', $post_id );
    $citta = get_field( 'citta', $post_id );
    $provincia = get_field( 'provincia', $post_id );
    $cap = get_field( 'cap', $post_id );

    if ( $indirizzo || $citta ) {
        $schema['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $indirizzo,
            'addressLocality' => $citta,
            'addressRegion'   => $provincia,
            'postalCode'      => $cap,
            'addressCountry'  => 'IT',
        );
    }

    // Coordinate geografiche
    $latitudine = get_field( 'latitudine', $post_id );
    $longitudine = get_field( 'longitudine', $post_id );

    if ( $latitudine && $longitudine ) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latitudine,
            'longitude' => $longitudine,
        );
    }

    // Contatti
    $telefono = get_field( 'telefono', $post_id );
    if ( $telefono ) {
        $schema['telephone'] = $telefono;
    }

    $email = get_field( 'email', $post_id );
    if ( $email ) {
        $schema['email'] = $email;
    }

    // Sito web
    $sito_web = get_field( 'sito_web', $post_id );
    if ( $sito_web ) {
        $schema['url'] = $sito_web;
    }

    // Descrizione
    $descrizione = get_field( 'descrizione', $post_id );
    if ( $descrizione ) {
        $schema['description'] = wp_strip_all_tags( $descrizione );
    }

    // Orari di apertura
    $orari = get_field( 'orari_apertura', $post_id );
    if ( $orari ) {
        $schema['openingHours'] = $orari;
    }

    // Immagine
    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        if ( $image_url ) {
            $schema['image'] = $image_url;
        }
    }

    return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>';
}

/**
 * Generate Schema.org JSON-LD for pensioni_per_cani
 *
 * @param int $post_id Post ID
 * @return string JSON-LD markup
 */
function caniincasa_get_pensioni_schema( $post_id ) {
    $schema = array(
        '@context'       => 'https://schema.org',
        '@type'          => 'LocalBusiness',
        'additionalType' => 'https://schema.org/LodgingBusiness',
        'name'           => get_the_title( $post_id ),
        'url'            => get_permalink( $post_id ),
        'description'    => 'Pensione per cani',
    );

    // Indirizzo
    $indirizzo = get_field( 'indirizzo', $post_id );
    $citta = get_field( 'citta', $post_id );
    $provincia = get_field( 'provincia', $post_id );
    $cap = get_field( 'cap', $post_id );

    if ( $indirizzo || $citta ) {
        $schema['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $indirizzo,
            'addressLocality' => $citta,
            'addressRegion'   => $provincia,
            'postalCode'      => $cap,
            'addressCountry'  => 'IT',
        );
    }

    // Coordinate geografiche
    $latitudine = get_field( 'latitudine', $post_id );
    $longitudine = get_field( 'longitudine', $post_id );

    if ( $latitudine && $longitudine ) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latitudine,
            'longitude' => $longitudine,
        );
    }

    // Contatti
    $telefono = get_field( 'telefono', $post_id );
    if ( $telefono ) {
        $schema['telephone'] = $telefono;
    }

    $email = get_field( 'email', $post_id );
    if ( $email ) {
        $schema['email'] = $email;
    }

    // Sito web
    $sito_web = get_field( 'sito_web', $post_id );
    if ( $sito_web ) {
        $schema['url'] = $sito_web;
    }

    // Descrizione personalizzata
    $descrizione_custom = get_field( 'descrizione', $post_id );
    if ( $descrizione_custom ) {
        $schema['description'] = wp_strip_all_tags( $descrizione_custom );
    }

    // Orari di apertura
    $orari = get_field( 'orari_apertura', $post_id );
    if ( $orari ) {
        $schema['openingHours'] = $orari;
    }

    // Immagine
    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        if ( $image_url ) {
            $schema['image'] = $image_url;
        }
    }

    // Servizi
    $servizi = get_field( 'servizi', $post_id );
    if ( $servizi ) {
        $schema['amenityFeature'] = array(
            '@type' => 'LocationFeatureSpecification',
            'value' => $servizi,
        );
    }

    // PriceRange
    $prezzi = get_field( 'prezzi', $post_id );
    if ( $prezzi ) {
        $schema['priceRange'] = $prezzi;
    }

    return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>';
}

/**
 * Generate Schema.org JSON-LD for centri_cinofili
 *
 * @param int $post_id Post ID
 * @return string JSON-LD markup
 */
function caniincasa_get_centri_cinofili_schema( $post_id ) {
    $schema = array(
        '@context'       => 'https://schema.org',
        '@type'          => 'LocalBusiness',
        'additionalType' => 'https://schema.org/EducationalOrganization',
        'name'           => get_the_title( $post_id ),
        'url'            => get_permalink( $post_id ),
        'description'    => 'Centro cinofilo e addestramento cani',
    );

    // Indirizzo
    $indirizzo = get_field( 'indirizzo', $post_id );
    $citta = get_field( 'citta', $post_id );
    $provincia = get_field( 'provincia', $post_id );
    $cap = get_field( 'cap', $post_id );

    if ( $indirizzo || $citta ) {
        $schema['address'] = array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $indirizzo,
            'addressLocality' => $citta,
            'addressRegion'   => $provincia,
            'postalCode'      => $cap,
            'addressCountry'  => 'IT',
        );
    }

    // Coordinate geografiche
    $latitudine = get_field( 'latitudine', $post_id );
    $longitudine = get_field( 'longitudine', $post_id );

    if ( $latitudine && $longitudine ) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => $latitudine,
            'longitude' => $longitudine,
        );
    }

    // Contatti
    $telefono = get_field( 'telefono', $post_id );
    if ( $telefono ) {
        $schema['telephone'] = $telefono;
    }

    $email = get_field( 'email', $post_id );
    if ( $email ) {
        $schema['email'] = $email;
    }

    // Sito web
    $sito_web = get_field( 'sito_web', $post_id );
    if ( $sito_web ) {
        $schema['url'] = $sito_web;
    }

    // Descrizione
    $descrizione = get_field( 'descrizione', $post_id );
    if ( $descrizione ) {
        $schema['description'] = wp_strip_all_tags( $descrizione );
    }

    // Orari di apertura
    $orari = get_field( 'orari_apertura', $post_id );
    if ( $orari ) {
        $schema['openingHours'] = $orari;
    }

    // Immagine
    $thumbnail_id = get_post_thumbnail_id( $post_id );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
        if ( $image_url ) {
            $schema['image'] = $image_url;
        }
    }

    // Servizi/Specializzazioni
    $specializzazioni = get_field( 'specializzazioni', $post_id );
    if ( $specializzazioni ) {
        $schema['knowsAbout'] = $specializzazioni;
    }

    // PriceRange
    $prezzi = get_field( 'prezzi', $post_id );
    if ( $prezzi ) {
        $schema['priceRange'] = $prezzi;
    }

    return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>';
}

/**
 * Output Schema.org markup in head for single structure pages
 */
function caniincasa_output_structure_schema() {
    if ( ! is_singular() ) {
        return;
    }

    $post_type = get_post_type();
    $post_id = get_the_ID();

    switch ( $post_type ) {
        case 'veterinari':
            echo caniincasa_get_veterinari_schema( $post_id );
            break;

        case 'allevamenti':
            echo caniincasa_get_allevamenti_schema( $post_id );
            break;

        case 'canili':
            echo caniincasa_get_canili_schema( $post_id );
            break;

        case 'pensioni_per_cani':
            echo caniincasa_get_pensioni_schema( $post_id );
            break;

        case 'centri_cinofili':
            echo caniincasa_get_centri_cinofili_schema( $post_id );
            break;
    }
}
add_action( 'wp_head', 'caniincasa_output_structure_schema' );
