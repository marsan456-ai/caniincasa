<?php
/**
 * Template Functions
 *
 * @package Caniincasa
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add body classes based on context
 */
function caniincasa_body_classes( $classes ) {
    // Add class if user is logged in
    if ( is_user_logged_in() ) {
        $classes[] = 'logged-in-user';
    }

    // Add class for layout type
    $layout_type = get_theme_mod( 'caniincasa_layout_type', 'full-width' );
    $classes[] = 'layout-' . $layout_type;

    // Add class for mobile devices
    if ( wp_is_mobile() ) {
        $classes[] = 'is-mobile';
    }

    // Add class for dark mode
    if ( get_theme_mod( 'caniincasa_enable_dark_mode', false ) ) {
        $classes[] = 'dark-mode-enabled';
    }

    // Add class for singular posts
    if ( is_singular() ) {
        $classes[] = 'singular-' . get_post_type();
    }

    return $classes;
}
add_filter( 'body_class', 'caniincasa_body_classes' );

/**
 * Add custom classes to menu items
 */
function caniincasa_nav_menu_css_class( $classes, $item, $args, $depth ) {
    if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
        $classes[] = 'primary-menu-item';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'caniincasa_nav_menu_css_class', 10, 4 );

/**
 * Get post reading time
 */
function caniincasa_get_reading_time( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $content = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( strip_tags( $content ) );
    $reading_time = ceil( $word_count / 200 ); // 200 words per minute

    return $reading_time;
}

/**
 * Display reading time
 */
function caniincasa_reading_time() {
    $time = caniincasa_get_reading_time();
    $text = $time > 1 ? __( 'minuti di lettura', 'caniincasa' ) : __( 'minuto di lettura', 'caniincasa' );

    echo '<span class="reading-time">' . esc_html( $time ) . ' ' . esc_html( $text ) . '</span>';
}

/**
 * Get primary category
 */
function caniincasa_get_primary_category( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $categories = get_the_category( $post_id );

    if ( empty( $categories ) ) {
        return false;
    }

    // If Yoast SEO is active, use primary category
    if ( class_exists( 'WPSEO_Primary_Term' ) ) {
        $primary_term = new WPSEO_Primary_Term( 'category', $post_id );
        $primary_id = $primary_term->get_primary_term();

        if ( $primary_id ) {
            return get_category( $primary_id );
        }
    }

    return $categories[0];
}

/**
 * Display breadcrumbs
 */
function caniincasa_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    $breadcrumbs = array();
    $breadcrumbs[] = array(
        'title' => __( 'Home', 'caniincasa' ),
        'url'   => home_url( '/' ),
    );

    if ( is_singular() ) {
        $post_type = get_post_type();
        $post_type_object = get_post_type_object( $post_type );

        if ( $post_type !== 'post' && $post_type !== 'page' ) {
            $breadcrumbs[] = array(
                'title' => $post_type_object->labels->name,
                'url'   => get_post_type_archive_link( $post_type ),
            );
        } elseif ( $post_type === 'post' ) {
            $category = caniincasa_get_primary_category();
            if ( $category ) {
                $breadcrumbs[] = array(
                    'title' => $category->name,
                    'url'   => get_category_link( $category->term_id ),
                );
            }
        }

        $breadcrumbs[] = array(
            'title' => get_the_title(),
            'url'   => '',
        );
    } elseif ( is_post_type_archive() ) {
        $post_type_object = get_queried_object();
        $breadcrumbs[] = array(
            'title' => $post_type_object->labels->name,
            'url'   => '',
        );
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        $breadcrumbs[] = array(
            'title' => $term->name,
            'url'   => '',
        );
    } elseif ( is_search() ) {
        $breadcrumbs[] = array(
            'title' => sprintf( __( 'Ricerca: %s', 'caniincasa' ), get_search_query() ),
            'url'   => '',
        );
    } elseif ( is_404() ) {
        $breadcrumbs[] = array(
            'title' => __( 'Pagina non trovata', 'caniincasa' ),
            'url'   => '',
        );
    }

    if ( empty( $breadcrumbs ) ) {
        return;
    }

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'caniincasa' ) . '">';
    echo '<ol class="breadcrumb-list">';

    foreach ( $breadcrumbs as $key => $crumb ) {
        $is_last = ( $key === count( $breadcrumbs ) - 1 );

        echo '<li class="breadcrumb-item' . ( $is_last ? ' active' : '' ) . '">';

        if ( ! $is_last && ! empty( $crumb['url'] ) ) {
            echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['title'] ) . '</a>';
        } else {
            echo esc_html( $crumb['title'] );
        }

        echo '</li>';
    }

    echo '</ol>';
    echo '</nav>';
}

/**
 * Get responsive image attributes
 */
function caniincasa_get_responsive_image( $attachment_id, $size = 'full', $args = array() ) {
    $defaults = array(
        'class' => '',
        'alt'   => '',
        'lazy'  => true,
    );

    $args = wp_parse_args( $args, $defaults );

    $image = wp_get_attachment_image_src( $attachment_id, $size );

    if ( ! $image ) {
        return '';
    }

    $alt = $args['alt'] ?: get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
    $class = $args['class'];

    if ( $args['lazy'] ) {
        $class .= ' lazy';
    }

    $srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
    $sizes = wp_get_attachment_image_sizes( $attachment_id, $size );

    $output = sprintf(
        '<img src="%s" alt="%s" class="%s" %s %s %s />',
        esc_url( $image[0] ),
        esc_attr( $alt ),
        esc_attr( trim( $class ) ),
        $srcset ? 'srcset="' . esc_attr( $srcset ) . '"' : '',
        $sizes ? 'sizes="' . esc_attr( $sizes ) . '"' : '',
        $args['lazy'] ? 'loading="lazy"' : ''
    );

    return $output;
}

/**
 * Sanitize checkbox
 */
function caniincasa_sanitize_checkbox( $checked ) {
    return ( isset( $checked ) && true === $checked ) ? true : false;
}

/**
 * Sanitize select
 */
function caniincasa_sanitize_select( $input, $setting ) {
    $input = sanitize_key( $input );
    $choices = $setting->manager->get_control( $setting->id )->choices;

    return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Get social share buttons
 */
function caniincasa_social_share_buttons( $args = array() ) {
    $defaults = array(
        'title' => get_the_title(),
        'url'   => get_permalink(),
    );

    $args = wp_parse_args( $args, $defaults );

    $share_url = array(
        'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $args['url'] ),
        'twitter'   => 'https://twitter.com/intent/tweet?url=' . urlencode( $args['url'] ) . '&text=' . urlencode( $args['title'] ),
        'whatsapp'  => 'https://wa.me/?text=' . urlencode( $args['title'] . ' ' . $args['url'] ),
        'telegram'  => 'https://t.me/share/url?url=' . urlencode( $args['url'] ) . '&text=' . urlencode( $args['title'] ),
    );

    echo '<div class="social-share">';
    echo '<span class="share-label">' . esc_html__( 'Condividi:', 'caniincasa' ) . '</span>';
    echo '<div class="share-buttons">';

    foreach ( $share_url as $platform => $url ) {
        printf(
            '<a href="%s" class="share-btn share-%s" target="_blank" rel="noopener noreferrer" aria-label="%s">
                <i class="icon-' . esc_attr( $platform ) . '"></i>
            </a>',
            esc_url( $url ),
            esc_attr( $platform ),
            sprintf( esc_attr__( 'Condividi su %s', 'caniincasa' ), ucfirst( $platform ) )
        );
    }

    echo '</div>';
    echo '</div>';
}

/**
 * Format phone number for tel: link
 */
function caniincasa_format_phone_link( $phone ) {
    return preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Check if WhatsApp number is valid (mobile)
 */
function caniincasa_is_whatsapp_number( $phone ) {
    $phone = caniincasa_format_phone_link( $phone );

    // Italian mobile numbers start with 3
    if ( preg_match( '/^\+39\s*3/', $phone ) || preg_match( '/^3/', $phone ) ) {
        return true;
    }

    return false;
}
