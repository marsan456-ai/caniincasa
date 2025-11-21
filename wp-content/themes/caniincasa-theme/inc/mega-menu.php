<?php
/**
 * Mega Menu System - WordPress Integration
 *
 * @package Caniincasa
 */

/**
 * Enable menu item description field
 * Needed for custom HTML mega menus
 */
function caniincasa_enable_menu_description() {
    add_filter( 'walker_nav_menu_start_el', 'caniincasa_menu_description', 10, 4 );
}
add_action( 'after_setup_theme', 'caniincasa_enable_menu_description' );

/**
 * Add description to menu items
 * Used for custom HTML in mega menus
 */
function caniincasa_menu_description( $item_output, $item, $depth, $args ) {
    // Only for items with mega-menu-custom class
    if ( ! empty( $item->description ) && in_array( 'mega-menu-custom', $item->classes ) ) {
        // Insert description after the link
        $item_output = str_replace(
            '</a>',
            '</a><div class="sub-menu"><div class="mega-menu-custom-content">' . $item->description . '</div></div>',
            $item_output
        );
    }

    return $item_output;
}

/**
 * Add custom fields to menu items in admin
 */
function caniincasa_add_menu_item_custom_fields( $item_id, $item ) {
    $mega_menu_type = get_post_meta( $item_id, '_mega_menu_type', true );
    $mega_menu_columns = get_post_meta( $item_id, '_mega_menu_columns', true );
    $mega_menu_width = get_post_meta( $item_id, '_mega_menu_width', true );
    ?>
    <p class="field-mega-menu description description-wide">
        <label for="edit-menu-item-mega-menu-type-<?php echo $item_id; ?>">
            <?php _e( 'Tipo Mega Menu', 'caniincasa' ); ?><br>
            <select name="menu-item-mega-menu-type[<?php echo $item_id; ?>]" id="edit-menu-item-mega-menu-type-<?php echo $item_id; ?>" class="widefat mega-menu-type-select">
                <option value=""><?php _e( 'Nessuno (menu normale)', 'caniincasa' ); ?></option>
                <option value="columns" <?php selected( $mega_menu_type, 'columns' ); ?>><?php _e( 'Colonne automatiche', 'caniincasa' ); ?></option>
                <option value="custom" <?php selected( $mega_menu_type, 'custom' ); ?>><?php _e( 'HTML personalizzato', 'caniincasa' ); ?></option>
            </select>
        </label>
    </p>

    <p class="field-mega-menu-width description description-wide" style="<?php echo empty( $mega_menu_type ) ? 'display:none;' : ''; ?>">
        <label for="edit-menu-item-mega-menu-width-<?php echo $item_id; ?>">
            <?php _e( 'Larghezza Mega Menu', 'caniincasa' ); ?><br>
            <select name="menu-item-mega-menu-width[<?php echo $item_id; ?>]" id="edit-menu-item-mega-menu-width-<?php echo $item_id; ?>" class="widefat">
                <option value="small" <?php selected( $mega_menu_width, 'small' ); ?>>Piccola (600px)</option>
                <option value="medium" <?php selected( $mega_menu_width, 'medium' ); ?>>Media (900px)</option>
                <option value="large" <?php selected( $mega_menu_width, 'large' ); ?>>Grande (1200px)</option>
                <option value="full" <?php selected( $mega_menu_width, 'full' ); ?>>Full Width (100%)</option>
            </select>
        </label>
    </p>

    <p class="field-mega-menu-columns description description-wide" style="<?php echo $mega_menu_type !== 'columns' ? 'display:none;' : ''; ?>">
        <label for="edit-menu-item-mega-menu-columns-<?php echo $item_id; ?>">
            <?php _e( 'Numero Colonne', 'caniincasa' ); ?><br>
            <select name="menu-item-mega-menu-columns[<?php echo $item_id; ?>]" id="edit-menu-item-mega-menu-columns-<?php echo $item_id; ?>" class="widefat">
                <option value="2" <?php selected( $mega_menu_columns, '2' ); ?>>2 Colonne</option>
                <option value="3" <?php selected( $mega_menu_columns, '3' ); ?>>3 Colonne</option>
                <option value="4" <?php selected( $mega_menu_columns, '4' ); ?>>4 Colonne</option>
            </select>
        </label>
    </p>

    <p class="field-mega-menu-custom description description-wide" style="<?php echo $mega_menu_type !== 'custom' ? 'display:none;' : ''; ?>">
        <label for="edit-menu-item-description-<?php echo $item_id; ?>">
            <?php _e( 'HTML Mega Menu', 'caniincasa' ); ?><br>
            <textarea name="menu-item-description[<?php echo $item_id; ?>]" id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="12" cols="50" style="font-family: monospace; font-size: 13px;"><?php echo esc_textarea( $item->description ); ?></textarea>
            <span class="description"><?php _e( 'Inserisci l\'HTML del mega menu. Vedi documentazione per esempi.', 'caniincasa' ); ?></span>
        </label>
    </p>

    <script>
    jQuery(document).ready(function($) {
        $('#edit-menu-item-mega-menu-type-<?php echo $item_id; ?>').on('change', function() {
            var $columns = $(this).closest('.menu-item-settings').find('.field-mega-menu-columns');
            var $custom = $(this).closest('.menu-item-settings').find('.field-mega-menu-custom');
            var $width = $(this).closest('.menu-item-settings').find('.field-mega-menu-width');

            if ($(this).val() === 'columns') {
                $columns.show();
                $custom.hide();
                $width.show();
            } else if ($(this).val() === 'custom') {
                $columns.hide();
                $custom.show();
                $width.show();
            } else {
                $columns.hide();
                $custom.hide();
                $width.hide();
            }
        });
    });
    </script>
    <?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'caniincasa_add_menu_item_custom_fields', 10, 2 );

/**
 * Save custom menu item fields
 */
function caniincasa_save_menu_item_custom_fields( $menu_id, $menu_item_db_id ) {
    // Save mega menu type
    if ( isset( $_POST['menu-item-mega-menu-type'][ $menu_item_db_id ] ) ) {
        $mega_menu_type = sanitize_text_field( $_POST['menu-item-mega-menu-type'][ $menu_item_db_id ] );
        update_post_meta( $menu_item_db_id, '_mega_menu_type', $mega_menu_type );
    }

    // Save width
    if ( isset( $_POST['menu-item-mega-menu-width'][ $menu_item_db_id ] ) ) {
        $width = sanitize_text_field( $_POST['menu-item-mega-menu-width'][ $menu_item_db_id ] );
        update_post_meta( $menu_item_db_id, '_mega_menu_width', $width );
    }

    // Save columns
    if ( isset( $_POST['menu-item-mega-menu-columns'][ $menu_item_db_id ] ) ) {
        $columns = intval( $_POST['menu-item-mega-menu-columns'][ $menu_item_db_id ] );
        update_post_meta( $menu_item_db_id, '_mega_menu_columns', $columns );
    }
}
add_action( 'wp_update_nav_menu_item', 'caniincasa_save_menu_item_custom_fields', 10, 2 );

/**
 * Add mega menu classes to menu items
 */
function caniincasa_add_mega_menu_classes( $classes, $item, $args, $depth ) {
    // Only for top-level items
    if ( $depth !== 0 ) {
        return $classes;
    }

    $mega_menu_type = get_post_meta( $item->ID, '_mega_menu_type', true );

    if ( $mega_menu_type === 'columns' ) {
        $columns = get_post_meta( $item->ID, '_mega_menu_columns', true );
        $columns = $columns ? $columns : '3'; // Default 3 columns
        $classes[] = 'mega-menu-' . $columns . '-cols';

        // Add width class
        $mega_menu_width = get_post_meta( $item->ID, '_mega_menu_width', true );
        $width = $mega_menu_width ? $mega_menu_width : 'large'; // Default large
        $classes[] = 'mega-menu-width-' . $width;
    } elseif ( $mega_menu_type === 'custom' ) {
        $classes[] = 'mega-menu-custom';

        // Add width class
        $mega_menu_width = get_post_meta( $item->ID, '_mega_menu_width', true );
        $width = $mega_menu_width ? $mega_menu_width : 'large'; // Default large
        $classes[] = 'mega-menu-width-' . $width;
    }

    return $classes;
}
add_filter( 'nav_menu_css_class', 'caniincasa_add_mega_menu_classes', 10, 4 );

/**
 * Enqueue mega menu assets
 */
function caniincasa_enqueue_mega_menu_assets() {
    // CSS
    wp_enqueue_style(
        'mega-menu',
        get_template_directory_uri() . '/assets/css/mega-menu.css',
        array(),
        '1.0.0'
    );

    // JavaScript
    wp_enqueue_script(
        'mega-menu',
        get_template_directory_uri() . '/assets/js/mega-menu.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'caniincasa_enqueue_mega_menu_assets' );

/**
 * Helper function to generate mega menu HTML for razze
 * Can be called programmatically or via shortcode
 */
function caniincasa_generate_razze_mega_menu() {
    // Get razze counts by taglia
    $taglie = get_terms( array(
        'taxonomy' => 'razza_taglia',
        'hide_empty' => false,
    ) );

    // Get FCI groups
    $gruppi = get_terms( array(
        'taxonomy' => 'razza_gruppo',
        'hide_empty' => false,
        'number' => 5,
    ) );

    // Get featured breed (random)
    $featured_razze = get_posts( array(
        'post_type' => 'razze_di_cani',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'meta_query' => array(
            array(
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
            ),
        ),
    ) );

    ob_start();
    ?>
    <div class="mega-menu-content">
        <div class="mega-menu-section">
            <h3><i class="icon">📏</i> Per Taglia</h3>
            <ul>
                <?php if ( ! empty( $taglie ) && ! is_wp_error( $taglie ) ) : ?>
                    <?php foreach ( $taglie as $taglia ) : ?>
                        <li>
                            <a href="<?php echo esc_url( get_term_link( $taglia ) ); ?>">
                                <?php echo esc_html( $taglia->name ); ?>
                                <span class="count"><?php echo $taglia->count; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="mega-menu-section">
            <h3><i class="icon">🎯</i> Gruppi FCI</h3>
            <ul>
                <?php if ( ! empty( $gruppi ) && ! is_wp_error( $gruppi ) ) : ?>
                    <?php foreach ( $gruppi as $gruppo ) : ?>
                        <li>
                            <a href="<?php echo esc_url( get_term_link( $gruppo ) ); ?>">
                                <?php echo esc_html( $gruppo->name ); ?>
                                <span class="count"><?php echo $gruppo->count; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="mega-menu-section">
            <h3><i class="icon">🔧</i> Strumenti</h3>
            <ul>
                <li><a href="<?php echo home_url( '/comparatore-razze/' ); ?>">Comparatore Razze <span class="mega-menu-badge new">Nuovo</span></a></li>
                <li><a href="<?php echo home_url( '/quiz-razza/' ); ?>">Quiz Compatibilità</a></li>
                <li><a href="<?php echo home_url( '/razze-di-cani/' ); ?>">Tutte le Razze A-Z</a></li>
            </ul>
        </div>

        <?php if ( ! empty( $featured_razze ) ) : ?>
            <?php
            $featured = $featured_razze[0];
            $featured_image = get_the_post_thumbnail_url( $featured->ID, 'medium' );
            $featured_excerpt = wp_trim_words( get_the_excerpt( $featured->ID ), 15 );
            ?>
            <div class="mega-menu-featured">
                <h4>Razza in Evidenza</h4>
                <div class="featured-breed">
                    <?php if ( $featured_image ) : ?>
                        <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $featured->post_title ); ?>">
                    <?php endif; ?>
                    <h5><?php echo esc_html( $featured->post_title ); ?></h5>
                    <p><?php echo esc_html( $featured_excerpt ); ?></p>
                    <a href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>" class="btn">Scopri di più</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}

/**
 * Shortcode to display razze mega menu
 * Usage: [razze_mega_menu]
 */
function caniincasa_razze_mega_menu_shortcode() {
    return caniincasa_generate_razze_mega_menu();
}
add_shortcode( 'razze_mega_menu', 'caniincasa_razze_mega_menu_shortcode' );
