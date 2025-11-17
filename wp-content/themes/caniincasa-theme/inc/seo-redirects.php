<?php
/**
 * SEO & Redirects Handler
 *
 * Handles 301 redirects for old URLs to maintain SEO
 * Redirects are DISABLED by default and must be explicitly enabled
 *
 * @package Caniincasa
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if redirects are enabled
 */
function caniincasa_redirects_enabled() {
    // Redirects are disabled by default
    // Can be enabled via filter or option
    return apply_filters( 'caniincasa_enable_redirects', get_option( 'caniincasa_enable_301_redirects', false ) );
}

/**
 * Handle 301 redirects for old URLs
 */
function caniincasa_handle_301_redirects() {
    // Only run on 404 pages
    if ( ! is_404() ) {
        return;
    }

    // Check if redirects are enabled
    if ( ! caniincasa_redirects_enabled() ) {
        return;
    }

    // Get the requested URL
    $requested_url = $_SERVER['REQUEST_URI'];
    $requested_path = trim( parse_url( $requested_url, PHP_URL_PATH ), '/' );

    // Check for old_slug match
    $redirect_url = caniincasa_find_redirect_by_old_slug( $requested_path );

    if ( $redirect_url ) {
        wp_redirect( $redirect_url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'caniincasa_handle_301_redirects' );

/**
 * Find redirect by old_slug custom field
 */
function caniincasa_find_redirect_by_old_slug( $slug ) {
    global $wpdb;

    // Clean the slug
    $slug = sanitize_title( $slug );

    // Query for posts with matching old_slug
    $query = $wpdb->prepare(
        "SELECT p.ID, p.post_type
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = 'old_slug'
        AND pm.meta_value = %s
        AND p.post_status = 'publish'
        LIMIT 1",
        $slug
    );

    $result = $wpdb->get_row( $query );

    if ( $result ) {
        return get_permalink( $result->ID );
    }

    // Also check if the slug itself matches (without full path)
    $parts = explode( '/', $slug );
    $last_part = end( $parts );

    if ( $last_part !== $slug ) {
        $query = $wpdb->prepare(
            "SELECT p.ID
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = 'old_slug'
            AND pm.meta_value = %s
            AND p.post_status = 'publish'
            LIMIT 1",
            $last_part
        );

        $result = $wpdb->get_row( $query );

        if ( $result ) {
            return get_permalink( $result->ID );
        }
    }

    return false;
}

/**
 * Add Schema.org markup for breadcrumbs
 */
function caniincasa_breadcrumb_schema() {
    if ( is_front_page() ) {
        return;
    }

    $items = array();
    $position = 1;

    // Home
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => __( 'Home', 'caniincasa' ),
        'item'     => home_url( '/' ),
    );

    if ( is_singular() ) {
        $post_type = get_post_type();
        $post_type_object = get_post_type_object( $post_type );

        if ( $post_type !== 'post' && $post_type !== 'page' ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $post_type_object->labels->name,
                'item'     => get_post_type_archive_link( $post_type ),
            );
        }

        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    } elseif ( is_post_type_archive() ) {
        $post_type_object = get_queried_object();
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => $post_type_object->labels->name,
            'item'     => get_post_type_archive_link( $post_type_object->name ),
        );
    }

    if ( empty( $items ) ) {
        return;
    }

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'caniincasa_breadcrumb_schema' );

/**
 * Add Organization schema
 */
function caniincasa_organization_schema() {
    if ( ! is_front_page() ) {
        return;
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => get_bloginfo( 'name' ),
        'url'      => home_url( '/' ),
        'logo'     => array(
            '@type' => 'ImageObject',
            'url'   => get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '',
        ),
        'sameAs'   => array(
            // Add social media links here
        ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'caniincasa_organization_schema' );

/**
 * Preserve existing permalinks during import
 */
function caniincasa_preserve_permalink( $post_id ) {
    // Check if this is an import and if permalink_esistente field exists
    $existing_permalink = get_post_meta( $post_id, 'permalink_esistente', true );

    if ( ! empty( $existing_permalink ) ) {
        // Extract slug from the existing permalink
        $slug = basename( parse_url( $existing_permalink, PHP_URL_PATH ) );

        if ( $slug ) {
            // Update post slug
            wp_update_post( array(
                'ID'        => $post_id,
                'post_name' => $slug,
            ) );
        }
    }
}
add_action( 'save_post', 'caniincasa_preserve_permalink', 20 );

/**
 * Admin notice about redirect status
 */
function caniincasa_redirect_status_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $enabled = caniincasa_redirects_enabled();
    $class = $enabled ? 'notice-success' : 'notice-warning';
    $status = $enabled ? __( 'ATTIVI', 'caniincasa' ) : __( 'DISATTIVATI', 'caniincasa' );

    ?>
    <div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
        <p>
            <strong><?php esc_html_e( 'Stato Redirect 301:', 'caniincasa' ); ?></strong>
            <?php echo esc_html( $status ); ?>
            <?php if ( ! $enabled ) : ?>
                <br>
                <small><?php esc_html_e( 'I redirect 301 sono attualmente disattivati. Attivali quando sei pronto per il go-live.', 'caniincasa' ); ?></small>
            <?php endif; ?>
        </p>
    </div>
    <?php
}

// Show notice only on specific admin pages
add_action( 'admin_notices', function() {
    $screen = get_current_screen();
    if ( $screen && in_array( $screen->id, array( 'dashboard', 'options-general' ) ) ) {
        caniincasa_redirect_status_notice();
    }
} );

/**
 * Add settings page for redirect management
 */
function caniincasa_redirect_settings() {
    add_options_page(
        __( 'Gestione Redirect 301', 'caniincasa' ),
        __( 'Redirect 301', 'caniincasa' ),
        'manage_options',
        'caniincasa-redirects',
        'caniincasa_redirect_settings_page'
    );
}
add_action( 'admin_menu', 'caniincasa_redirect_settings' );

/**
 * Redirect settings page
 */
function caniincasa_redirect_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle form submission
    if ( isset( $_POST['caniincasa_redirect_nonce'] ) && wp_verify_nonce( $_POST['caniincasa_redirect_nonce'], 'caniincasa_redirect_settings' ) ) {
        $enabled = isset( $_POST['enable_redirects'] ) ? true : false;
        update_option( 'caniincasa_enable_301_redirects', $enabled );

        echo '<div class="notice notice-success"><p>' . esc_html__( 'Impostazioni salvate!', 'caniincasa' ) . '</p></div>';
    }

    $enabled = get_option( 'caniincasa_enable_301_redirects', false );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Gestione Redirect 301', 'caniincasa' ); ?></h1>

        <div class="card">
            <h2><?php esc_html_e( 'Informazioni Importanti', 'caniincasa' ); ?></h2>
            <p>
                <?php esc_html_e( 'Il sistema di redirect 301 permette di reindirizzare automaticamente i vecchi URL ai nuovi, preservando il valore SEO.', 'caniincasa' ); ?>
            </p>
            <p>
                <strong><?php esc_html_e( 'ATTENZIONE:', 'caniincasa' ); ?></strong>
                <?php esc_html_e( 'I redirect devono essere attivati SOLO quando il sito è pronto per andare in produzione.', 'caniincasa' ); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'caniincasa_redirect_settings', 'caniincasa_redirect_nonce' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Abilita Redirect 301', 'caniincasa' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_redirects" value="1" <?php checked( $enabled, true ); ?>>
                            <?php esc_html_e( 'Attiva i redirect 301 automatici', 'caniincasa' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Quando attivo, il sistema cercherà automaticamente corrispondenze nel campo "old_slug" e reindirizzerà gli utenti al nuovo URL.', 'caniincasa' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <div class="card">
            <h2><?php esc_html_e( 'Come Funziona', 'caniincasa' ); ?></h2>
            <ol>
                <li><?php esc_html_e( 'Durante l\'importazione dei contenuti, il campo "permalink_esistente" viene salvato.', 'caniincasa' ); ?></li>
                <li><?php esc_html_e( 'Puoi aggiungere manualmente il campo "old_slug" a qualsiasi post/CPT tramite ACF.', 'caniincasa' ); ?></li>
                <li><?php esc_html_e( 'Quando un utente visita un URL non trovato (404), il sistema cerca una corrispondenza.', 'caniincasa' ); ?></li>
                <li><?php esc_html_e( 'Se trova una corrispondenza, effettua un redirect 301 al nuovo URL.', 'caniincasa' ); ?></li>
            </ol>
        </div>
    </div>
    <?php
}
