<?php
/**
 * Helper Functions
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get all Italian provinces as array
 */
function caniincasa_get_province_array() {
    return array(
        'AG' => 'Agrigento', 'AL' => 'Alessandria', 'AN' => 'Ancona', 'AO' => 'Aosta',
        'AR' => 'Arezzo', 'AP' => 'Ascoli Piceno', 'AT' => 'Asti', 'AV' => 'Avellino',
        'BA' => 'Bari', 'BT' => 'Barletta-Andria-Trani', 'BL' => 'Belluno', 'BN' => 'Benevento',
        'BG' => 'Bergamo', 'BI' => 'Biella', 'BO' => 'Bologna', 'BZ' => 'Bolzano',
        'BS' => 'Brescia', 'BR' => 'Brindisi', 'CA' => 'Cagliari', 'CL' => 'Caltanissetta',
        'CB' => 'Campobasso', 'CE' => 'Caserta', 'CT' => 'Catania', 'CZ' => 'Catanzaro',
        'CH' => 'Chieti', 'CO' => 'Como', 'CS' => 'Cosenza', 'CR' => 'Cremona',
        'KR' => 'Crotone', 'CN' => 'Cuneo', 'EN' => 'Enna', 'FM' => 'Fermo',
        'FE' => 'Ferrara', 'FI' => 'Firenze', 'FG' => 'Foggia', 'FC' => 'Forlì-Cesena',
        'FR' => 'Frosinone', 'GE' => 'Genova', 'GO' => 'Gorizia', 'GR' => 'Grosseto',
        'IM' => 'Imperia', 'IS' => 'Isernia', 'SP' => 'La Spezia', 'AQ' => 'L\'Aquila',
        'LT' => 'Latina', 'LE' => 'Lecce', 'LC' => 'Lecco', 'LI' => 'Livorno',
        'LO' => 'Lodi', 'LU' => 'Lucca', 'MC' => 'Macerata', 'MN' => 'Mantova',
        'MS' => 'Massa-Carrara', 'MT' => 'Matera', 'ME' => 'Messina', 'MI' => 'Milano',
        'MO' => 'Modena', 'MB' => 'Monza e Brianza', 'NA' => 'Napoli', 'NO' => 'Novara',
        'NU' => 'Nuoro', 'OR' => 'Oristano', 'PD' => 'Padova', 'PA' => 'Palermo',
        'PR' => 'Parma', 'PV' => 'Pavia', 'PG' => 'Perugia', 'PU' => 'Pesaro e Urbino',
        'PE' => 'Pescara', 'PC' => 'Piacenza', 'PI' => 'Pisa', 'PT' => 'Pistoia',
        'PN' => 'Pordenone', 'PZ' => 'Potenza', 'PO' => 'Prato', 'RG' => 'Ragusa',
        'RA' => 'Ravenna', 'RC' => 'Reggio Calabria', 'RE' => 'Reggio Emilia', 'RI' => 'Rieti',
        'RN' => 'Rimini', 'RM' => 'Roma', 'RO' => 'Rovigo', 'SA' => 'Salerno',
        'SS' => 'Sassari', 'SV' => 'Savona', 'SI' => 'Siena', 'SR' => 'Siracusa',
        'SO' => 'Sondrio', 'TA' => 'Taranto', 'TE' => 'Teramo', 'TR' => 'Terni',
        'TO' => 'Torino', 'TP' => 'Trapani', 'TN' => 'Trento', 'TV' => 'Treviso',
        'TS' => 'Trieste', 'UD' => 'Udine', 'VA' => 'Varese', 'VE' => 'Venezia',
        'VB' => 'Verbano-Cusio-Ossola', 'VC' => 'Vercelli', 'VR' => 'Verona',
        'VV' => 'Vibo Valentia', 'VI' => 'Vicenza', 'VT' => 'Viterbo',
    );
}

/**
 * Sanitize rating value (1-5)
 */
function caniincasa_sanitize_rating( $value ) {
    $value = intval( $value );
    return max( 1, min( 5, $value ) );
}

/**
 * Get rating stars HTML
 */
function caniincasa_get_rating_stars( $rating, $max = 5 ) {
    $rating = caniincasa_sanitize_rating( $rating );
    $output = '<div class="rating-stars">';

    for ( $i = 1; $i <= $max; $i++ ) {
        $class = ( $i <= $rating ) ? 'star-filled' : 'star-empty';
        $output .= '<span class="star ' . esc_attr( $class ) . '">★</span>';
    }

    $output .= '</div>';
    return $output;
}

/**
 * Check if user can edit annuncio
 */
function caniincasa_user_can_edit_annuncio( $post_id ) {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $post = get_post( $post_id );

    if ( ! $post ) {
        return false;
    }

    $user_id = get_current_user_id();

    // Admin can edit all
    if ( current_user_can( 'administrator' ) ) {
        return true;
    }

    // Author can edit own posts
    return ( $post->post_author == $user_id );
}

/**
 * Get annuncio status badge HTML
 */
function caniincasa_get_annuncio_status_badge( $post_id ) {
    $status = get_post_status( $post_id );
    $status_obj = get_post_status_object( $status );

    $badges = array(
        'publish' => 'success',
        'pending' => 'warning',
        'draft'   => 'info',
        'trash'   => 'error',
    );

    $badge_class = isset( $badges[ $status ] ) ? $badges[ $status ] : 'default';
    $label = $status_obj ? $status_obj->label : $status;

    return '<span class="badge badge-' . esc_attr( $badge_class ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * Check if annuncio is expired
 */
function caniincasa_is_annuncio_expired( $post_id ) {
    $scadenza = get_post_meta( $post_id, 'scadenza_annuncio', true );

    if ( ! $scadenza ) {
        return false;
    }

    return ( $scadenza < date( 'Y-m-d' ) );
}

/**
 * Get days until expiration
 */
function caniincasa_days_until_expiration( $post_id ) {
    $scadenza = get_post_meta( $post_id, 'scadenza_annuncio', true );

    if ( ! $scadenza ) {
        return null;
    }

    $today = new DateTime( date( 'Y-m-d' ) );
    $expiry = new DateTime( $scadenza );
    $diff = $today->diff( $expiry );

    return $diff->invert ? -$diff->days : $diff->days;
}

/**
 * Format phone number for display
 */
function caniincasa_format_phone_display( $phone ) {
    // Remove all non-numeric characters except +
    $phone = preg_replace( '/[^0-9+]/', '', $phone );

    // Format Italian mobile numbers
    if ( preg_match( '/^(\+39)?(\d{3})(\d{3})(\d{4})$/', $phone, $matches ) ) {
        return ( $matches[1] ?: '+39' ) . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4];
    }

    return $phone;
}

/**
 * Get WhatsApp link
 */
function caniincasa_get_whatsapp_link( $phone, $message = '' ) {
    $phone = preg_replace( '/[^0-9]/', '', $phone );

    // Ensure it starts with country code
    if ( ! str_starts_with( $phone, '39' ) && ! str_starts_with( $phone, '+39' ) ) {
        $phone = '39' . $phone;
    }

    $url = 'https://wa.me/' . $phone;

    if ( $message ) {
        $url .= '?text=' . urlencode( $message );
    }

    return $url;
}

/**
 * Generate breadcrumb data for Schema.org
 */
function caniincasa_get_breadcrumb_data() {
    if ( is_front_page() ) {
        return array();
    }

    $items = array();
    $position = 1;

    // Home
    $items[] = array(
        'position' => $position++,
        'name'     => __( 'Home', 'caniincasa-core' ),
        'url'      => home_url( '/' ),
    );

    if ( is_singular() ) {
        $post_type = get_post_type();

        if ( $post_type !== 'post' && $post_type !== 'page' ) {
            $post_type_obj = get_post_type_object( $post_type );
            $items[] = array(
                'position' => $position++,
                'name'     => $post_type_obj->labels->name,
                'url'      => get_post_type_archive_link( $post_type ),
            );
        }

        $items[] = array(
            'position' => $position++,
            'name'     => get_the_title(),
            'url'      => get_permalink(),
        );
    }

    return $items;
}

/**
 * Verify nonce with automatic error handling
 */
function caniincasa_verify_nonce( $nonce, $action ) {
    if ( ! wp_verify_nonce( $nonce, $action ) ) {
        wp_send_json_error( array(
            'message' => __( 'Verifica di sicurezza fallita', 'caniincasa-core' ),
        ) );
    }
}

/**
 * Check if user is logged in (AJAX)
 */
function caniincasa_require_login() {
    if ( ! is_user_logged_in() ) {
        // Sanitize REQUEST_URI to prevent XSS and open redirect
        $redirect_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( home_url( $_SERVER['REQUEST_URI'] ) ) : home_url();
        // Validate redirect is internal
        if ( wp_validate_redirect( $redirect_url, home_url() ) !== $redirect_url ) {
            $redirect_url = home_url();
        }
        wp_send_json_error( array(
            'message'  => __( 'Devi effettuare il login per continuare', 'caniincasa-core' ),
            'redirect' => wp_login_url( $redirect_url ),
        ) );
    }
}
