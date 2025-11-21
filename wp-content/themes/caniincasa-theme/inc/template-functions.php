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
        if ( $post_type_object && isset( $post_type_object->labels->name ) ) {
            $breadcrumbs[] = array(
                'title' => $post_type_object->labels->name,
                'url'   => '',
            );
        }
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( $term && isset( $term->name ) ) {
            $breadcrumbs[] = array(
                'title' => $term->name,
                'url'   => '',
            );
        }
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
 * Get social share buttons with SVG icons
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
        'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode( $args['url'] ),
        'email'     => 'mailto:?subject=' . urlencode( $args['title'] ) . '&body=' . urlencode( $args['url'] ),
    );

    $icons = array(
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
    );

    $labels = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter (X)',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'linkedin' => 'LinkedIn',
        'email' => 'Email',
    );

    ?>
    <div class="social-share-wrapper">
        <h3 class="share-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
            </svg>
            Condividi questa razza
        </h3>
        <div class="share-buttons">
            <?php foreach ( $share_url as $platform => $url ) : ?>
                <a href="<?php echo esc_url( $url ); ?>"
                   class="share-btn share-btn-<?php echo esc_attr( $platform ); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="<?php echo esc_attr( sprintf( 'Condividi su %s', $labels[ $platform ] ) ); ?>"
                   title="<?php echo esc_attr( $labels[ $platform ] ); ?>">
                    <?php echo $icons[ $platform ]; ?>
                    <span class="share-label"><?php echo esc_html( $labels[ $platform ] ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
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

/**
 * Get user display name in format: Nome I. (Nome + iniziale cognome)
 *
 * @param int|WP_User $user User ID or WP_User object
 * @return string Formatted display name
 */
function caniincasa_get_user_display_name( $user = null ) {
    // Get user object
    if ( is_numeric( $user ) ) {
        $user = get_userdata( $user );
    } elseif ( ! $user instanceof WP_User ) {
        $user = wp_get_current_user();
    }

    if ( ! $user || ! $user->exists() ) {
        return '';
    }

    // Get first name and last name
    $first_name = get_user_meta( $user->ID, 'first_name', true );
    $last_name = get_user_meta( $user->ID, 'last_name', true );

    // If we have both first and last name
    if ( $first_name && $last_name ) {
        $last_initial = mb_strtoupper( mb_substr( $last_name, 0, 1 ) );
        return $first_name . ' ' . $last_initial . '.';
    }

    // Fallback to first name only
    if ( $first_name ) {
        return $first_name;
    }

    // Fallback to username
    return $user->user_login;
}

/**
 * Display user name in format: Nome I.
 *
 * @param int|WP_User $user User ID or WP_User object
 */
function caniincasa_display_user_name( $user = null ) {
    echo esc_html( caniincasa_get_user_display_name( $user ) );
}

/**
 * Display structure claim/update buttons
 *
 * @param int $struttura_id Structure post ID
 * @param string $struttura_type Structure post type
 */
function caniincasa_struttura_claim_buttons( $struttura_id, $struttura_type ) {
    $claim_url = add_query_arg(
        array(
            'struttura_id' => $struttura_id,
            'struttura_type' => $struttura_type,
        ),
        home_url( '/claim-struttura' )
    );
    ?>
    <div class="sidebar-box owner-box">
        <h3 class="box-title">Sei il proprietario?</h3>
        <p>Aggiorna direttamente i dati della tua struttura o contattaci per supporto.</p>
        <a href="<?php echo esc_url( $claim_url ); ?>" class="btn btn-primary btn-block">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5 2.50001C18.8978 2.10219 19.4374 1.87869 20 1.87869C20.5626 1.87869 21.1022 2.10219 21.5 2.50001C21.8978 2.89784 22.1213 3.43741 22.1213 4.00001C22.1213 4.56262 21.8978 5.10219 21.5 5.50001L12 15L8 16L9 12L18.5 2.50001Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Inserisci i tuoi dati
        </a>
        <a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>" class="btn btn-outline btn-block" style="margin-top: 10px;">
            Contattaci
        </a>
    </div>
    <?php
}
