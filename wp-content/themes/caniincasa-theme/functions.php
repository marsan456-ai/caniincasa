<?php
/**
 * Caniincasa Theme - Functions and Definitions
 *
 * @package Caniincasa
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Define Constants
 */
define( 'CANIINCASA_VERSION', '1.0.1' );
define( 'CANIINCASA_THEME_DIR', get_template_directory() );
define( 'CANIINCASA_THEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function caniincasa_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );

    // Set post thumbnail sizes
    set_post_thumbnail_size( 1200, 800, true );
    add_image_size( 'caniincasa-small', 400, 300, true );
    add_image_size( 'caniincasa-medium', 800, 600, true );
    add_image_size( 'caniincasa-large', 1200, 800, true );
    add_image_size( 'caniincasa-hero', 1920, 800, true );

    // Register navigation menus
    register_nav_menus( array(
        'primary'   => __( 'Menu Principale', 'caniincasa' ),
        'top-bar'   => __( 'Top Bar Menu', 'caniincasa' ),
        'mobile'    => __( 'Mobile Menu', 'caniincasa' ),
        'footer'    => __( 'Footer Menu', 'caniincasa' ),
    ) );

    // Switch default core markup to output valid HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Add theme support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for custom background
    add_theme_support( 'custom-background', array(
        'default-color' => 'ffffff',
    ) );

    // Add support for responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Add support for editor styles
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );

    // Add support for wide alignment
    add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'caniincasa_setup' );

/**
 * Set the content width in pixels
 */
function caniincasa_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'caniincasa_content_width', 1280 );
}
add_action( 'after_setup_theme', 'caniincasa_content_width', 0 );

/**
 * Register Widget Areas
 */
function caniincasa_widgets_init() {
    // Main Sidebar
    register_sidebar( array(
        'name'          => __( 'Sidebar Principale', 'caniincasa' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Sidebar principale del sito', 'caniincasa' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    // Footer Widget Areas (4 columns)
    for ( $i = 1; $i <= 4; $i++ ) {
        register_sidebar( array(
            'name'          => sprintf( __( 'Footer Widget Area %d', 'caniincasa' ), $i ),
            'id'            => 'footer-' . $i,
            'description'   => sprintf( __( 'Area widget footer colonna %d', 'caniincasa' ), $i ),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ) );
    }

    // CPT Specific Sidebars
    register_sidebar( array(
        'name'          => __( 'Sidebar Razze', 'caniincasa' ),
        'id'            => 'sidebar-razze',
        'description'   => __( 'Sidebar per le pagine delle razze', 'caniincasa' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Sidebar Strutture', 'caniincasa' ),
        'id'            => 'sidebar-strutture',
        'description'   => __( 'Sidebar per le pagine delle strutture', 'caniincasa' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'caniincasa_widgets_init' );

/**
 * Enqueue Scripts and Styles
 */
function caniincasa_scripts() {
    // Main stylesheet
    wp_enqueue_style( 'caniincasa-style', get_stylesheet_uri(), array(), CANIINCASA_VERSION );

    // Google Fonts
    wp_enqueue_style( 'caniincasa-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Baloo+2:wght@400;500;600;700&display=swap', array(), null );

    // Main theme styles
    wp_enqueue_style( 'caniincasa-main', CANIINCASA_THEME_URI . '/assets/css/main.css', array(), CANIINCASA_VERSION );

    // Responsive styles
    wp_enqueue_style( 'caniincasa-responsive', CANIINCASA_THEME_URI . '/assets/css/responsive.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );

    // Homepage styles (conditional)
    if ( is_front_page() ) {
        wp_enqueue_style( 'caniincasa-homepage', CANIINCASA_THEME_URI . '/assets/css/homepage.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-newsletter', CANIINCASA_THEME_URI . '/assets/js/newsletter.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }

    // Razze styles (conditional)
    if ( is_singular( 'razze_di_cani' ) || is_post_type_archive( 'razze_di_cani' ) || is_tax( array( 'razza_taglia', 'razza_gruppo' ) ) ) {
        wp_enqueue_style( 'caniincasa-razze', CANIINCASA_THEME_URI . '/assets/css/razze.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Strutture styles (conditional)
    $strutture_types = array( 'allevamenti', 'veterinari', 'canili', 'pensioni_per_cani', 'centri_cinofili' );
    if ( is_singular( $strutture_types ) || is_post_type_archive( $strutture_types ) || is_tax( 'provincia' ) ) {
        wp_enqueue_style( 'caniincasa-strutture', CANIINCASA_THEME_URI . '/assets/css/strutture.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Annunci CTA & Auth Modal (load on all pages except dashboard for topbar button)
    if ( ! is_page_template( 'template-dashboard.php' ) ) {
        wp_enqueue_style( 'caniincasa-annunci-cta', CANIINCASA_THEME_URI . '/assets/css/annunci-cta.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-annunci-cta', CANIINCASA_THEME_URI . '/assets/js/annunci-cta.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }

    // Annunci styles (conditional)
    $annunci_types = array( 'annunci_4zampe', 'annunci_dogsitter' );
    if ( is_singular( $annunci_types ) || is_post_type_archive( $annunci_types ) ) {
        wp_enqueue_style( 'caniincasa-annunci', CANIINCASA_THEME_URI . '/assets/css/annunci.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Annunci form styles (conditional)
    if ( is_page_template( 'template-pubblica-annuncio.php' ) ) {
        wp_enqueue_style( 'caniincasa-annunci-form', CANIINCASA_THEME_URI . '/assets/css/annunci-form.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Blog styles (conditional)
    if ( is_singular( 'post' ) || is_page() || is_archive() || is_search() || is_404() || is_home() ) {
        wp_enqueue_style( 'caniincasa-blog', CANIINCASA_THEME_URI . '/assets/css/blog.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Messaging styles and scripts (conditional - logged in users only)
    if ( is_user_logged_in() ) {
        wp_enqueue_style( 'caniincasa-messaging', CANIINCASA_THEME_URI . '/assets/css/messaging.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
        wp_enqueue_script( 'caniincasa-messaging', CANIINCASA_THEME_URI . '/assets/js/messaging.js', array( 'jquery' ), CANIINCASA_VERSION, true );

        // Localize script for messaging AJAX
        wp_localize_script( 'caniincasa-messaging', 'caniincasaData', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'caniincasa_nonce' ),
        ) );
    }

    // Chi Siamo page styles
    if ( is_page_template( 'template-chi-siamo.php' ) ) {
        wp_enqueue_style( 'caniincasa-chi-siamo', CANIINCASA_THEME_URI . '/assets/css/chi-siamo.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Contatti page styles
    if ( is_page_template( 'template-contatti.php' ) ) {
        wp_enqueue_style( 'caniincasa-contatti', CANIINCASA_THEME_URI . '/assets/css/contatti.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // GDPR Cookie Banner (load on all pages)
    wp_enqueue_style( 'caniincasa-gdpr-cookie', CANIINCASA_THEME_URI . '/assets/css/gdpr-cookie.css', array(), CANIINCASA_VERSION );
    wp_enqueue_script( 'caniincasa-gdpr-cookie', CANIINCASA_THEME_URI . '/assets/js/gdpr-cookie.js', array(), CANIINCASA_VERSION, true );

    // Contact Form 7 optimized styles (load only if CF7 is active)
    if ( class_exists( 'WPCF7' ) ) {
        wp_enqueue_style( 'caniincasa-cf7', CANIINCASA_THEME_URI . '/assets/css/cf7.css', array( 'caniincasa-main' ), CANIINCASA_VERSION );
    }

    // Main JavaScript
    wp_enqueue_script( 'caniincasa-main', CANIINCASA_THEME_URI . '/assets/js/main.js', array( 'jquery' ), CANIINCASA_VERSION, true );

    // Navigation script
    wp_enqueue_script( 'caniincasa-navigation', CANIINCASA_THEME_URI . '/assets/js/navigation.js', array(), CANIINCASA_VERSION, true );

    // Annunci submission script (conditional)
    if ( is_page_template( 'template-pubblica-annuncio.php' ) ) {
        wp_enqueue_script( 'caniincasa-annunci', CANIINCASA_THEME_URI . '/assets/js/annunci.js', array( 'jquery' ), CANIINCASA_VERSION, true );
    }

    // Localize script for AJAX
    wp_localize_script( 'caniincasa-main', 'caniincasaAjax', array(
        'ajaxurl'      => admin_url( 'admin-ajax.php' ),
        'nonce'        => wp_create_nonce( 'caniincasa_nonce' ),
        'dashboardUrl' => home_url( '/dashboard' ),
    ) );

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'caniincasa_scripts' );

/**
 * Block wp-admin access for non-admin users
 */
function caniincasa_block_wp_admin() {
    if ( is_admin() && ! current_user_can( 'administrator' ) && ! wp_doing_ajax() ) {
        wp_redirect( home_url( '/dashboard' ) );
        exit;
    }
}
add_action( 'admin_init', 'caniincasa_block_wp_admin' );

/**
 * Redirect users after login
 */
function caniincasa_login_redirect( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'administrator', $user->roles ) ) {
            return admin_url();
        } else {
            return home_url( '/dashboard' );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'caniincasa_login_redirect', 10, 3 );

/**
 * Custom excerpt length
 */
function caniincasa_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'caniincasa_excerpt_length', 999 );

/**
 * Custom excerpt more
 */
function caniincasa_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'caniincasa_excerpt_more' );

/**
 * Add lazy loading to images
 */
function caniincasa_add_lazy_loading( $attr ) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'caniincasa_add_lazy_loading' );

/**
 * Include required files
 */
require_once CANIINCASA_THEME_DIR . '/inc/customizer.php';
require_once CANIINCASA_THEME_DIR . '/inc/template-functions.php';
require_once CANIINCASA_THEME_DIR . '/inc/template-tags.php';

// Include SEO & Redirects handler
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/seo-redirects.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/seo-redirects.php';
}

// Include Dashboard functions (if exists)
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/dashboard.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/dashboard.php';
}

// Include Schema.org structured data
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/schema-org.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/schema-org.php';
}

// Include Custom SEO Meta Tags system
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/seo-meta-custom.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/seo-meta-custom.php';
}

// Include ACF Calculator Fields for Razze
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/acf-razze-calculator-fields.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/acf-razze-calculator-fields.php';
}

// Include Breed Data Importer
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/breed-data-importer.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/breed-data-importer.php';
}

// Include Dog Age Calculator
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/calculator-age.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/calculator-age.php';
}

// Include Dog Weight Calculator
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/calculator-weight.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/calculator-weight.php';
}

// Include Dog Cost Calculator
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/calculator-cost.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/calculator-cost.php';
}

// Include Dog Food Calculator
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/calculator-food.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/calculator-food.php';
}

// Include Comparatore Razze AJAX
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/comparatore-ajax.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/comparatore-ajax.php';
}

// Include Mega Menu System
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/mega-menu.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/mega-menu.php';
}

// Include Stories System (Storie di Cani)
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/stories-system.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/stories-system.php';
}

// Include Razze Grid Shortcode
if ( file_exists( CANIINCASA_THEME_DIR . '/inc/shortcode-razze-grid.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/shortcode-razze-grid.php';
}

// Include Razze Grid Editor Button (TinyMCE)
if ( is_admin() && file_exists( CANIINCASA_THEME_DIR . '/inc/editor-razze-grid-button.php' ) ) {
    require_once CANIINCASA_THEME_DIR . '/inc/editor-razze-grid-button.php';
}

/**
 * Enqueue Comparatore Razze assets
 */
function caniincasa_enqueue_comparatore_assets() {
    if ( is_page_template( 'page-comparatore-razze.php' ) ) {
        // CSS
        wp_enqueue_style(
            'comparatore-razze',
            get_template_directory_uri() . '/assets/css/comparatore-razze.css',
            array(),
            '1.0.0'
        );

        // JavaScript
        wp_enqueue_script(
            'comparatore-razze',
            get_template_directory_uri() . '/assets/js/comparatore-razze.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        // Localize script
        wp_localize_script(
            'comparatore-razze',
            'caniincasaData',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'caniincasa_nonce' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'caniincasa_enqueue_comparatore_assets' );

/**
 * Security: Remove WordPress version from head
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Security: Disable XML-RPC
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Performance: Remove emoji scripts
 */
function caniincasa_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'caniincasa_disable_emojis' );

/**
 * Add WebP support
 */
function caniincasa_webp_upload_mimes( $existing_mimes ) {
    $existing_mimes['webp'] = 'image/webp';
    return $existing_mimes;
}
add_filter( 'mime_types', 'caniincasa_webp_upload_mimes' );

/**
 * Modify archive queries for custom post types
 */
function caniincasa_modify_archive_query( $query ) {
    // Only for main query on frontend archives
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Allevamenti archive: 24 posts per page
    if ( is_post_type_archive( 'allevamenti' ) ) {
        $query->set( 'posts_per_page', 24 );
    }

    // Razze archive: 24 posts per page, alphabetical order
    if ( is_post_type_archive( 'razze_di_cani' ) ) {
        $query->set( 'posts_per_page', 24 );
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
    }
}
add_action( 'pre_get_posts', 'caniincasa_modify_archive_query' );
