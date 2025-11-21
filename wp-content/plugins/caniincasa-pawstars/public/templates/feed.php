<?php
/**
 * Feed Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$plugin = pawstars();
$breeds = Pawstars_Integrations::get_breeds();
$province = Pawstars_Integrations::get_province();

// Get filter values
$filter_breed = isset( $_GET['breed'] ) ? absint( $_GET['breed'] ) : ( $atts['breed'] ?: '' );
$filter_provincia = isset( $_GET['provincia'] ) ? sanitize_text_field( $_GET['provincia'] ) : ( $atts['provincia'] ?: '' );
$filter_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : $atts['orderby'];
$filter_search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

// Get dogs
$dogs = $plugin->database->get_dogs( array(
    'status'    => 'active',
    'breed_id'  => $filter_breed ?: null,
    'provincia' => $filter_provincia ?: null,
    'search'    => $filter_search,
    'orderby'   => $filter_orderby,
    'order'     => $atts['order'],
    'limit'     => absint( $atts['limit'] ),
) );

$total = $plugin->database->count_dogs( array(
    'status'    => 'active',
    'breed_id'  => $filter_breed ?: null,
    'provincia' => $filter_provincia ?: null,
) );

$is_mobile = wp_is_mobile();
?>

<div class="pawstars-feed-wrapper">
    <!-- Header -->
    <div class="pawstars-feed-header">
        <h2 class="feed-title">🐾 <?php esc_html_e( 'Paw Stars', 'pawstars' ); ?></h2>
        <p class="feed-subtitle"><?php esc_html_e( 'Scopri i cani più adorabili della community!', 'pawstars' ); ?></p>

        <?php if ( is_user_logged_in() ) : ?>
            <a href="#pawstars-create" class="btn btn-primary pawstars-create-btn">
                + <?php esc_html_e( 'Aggiungi il Tuo Cane', 'pawstars' ); ?>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-primary">
                <?php esc_html_e( 'Accedi per Partecipare', 'pawstars' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="pawstars-filters">
        <form method="get" class="pawstars-filter-form">
            <div class="filter-group">
                <select name="breed" class="filter-select">
                    <option value=""><?php esc_html_e( 'Tutte le Razze', 'pawstars' ); ?></option>
                    <?php foreach ( $breeds as $breed ) : ?>
                        <option value="<?php echo esc_attr( $breed['id'] ); ?>" <?php selected( $filter_breed, $breed['id'] ); ?>>
                            <?php echo esc_html( $breed['name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="provincia" class="filter-select">
                    <option value=""><?php esc_html_e( 'Tutte le Province', 'pawstars' ); ?></option>
                    <?php foreach ( $province as $code => $name ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $filter_provincia, $code ); ?>>
                            <?php echo esc_html( $name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="orderby" class="filter-select">
                    <option value="created_at" <?php selected( $filter_orderby, 'created_at' ); ?>><?php esc_html_e( 'Più Recenti', 'pawstars' ); ?></option>
                    <option value="total_points" <?php selected( $filter_orderby, 'total_points' ); ?>><?php esc_html_e( 'Più Votati', 'pawstars' ); ?></option>
                    <option value="name" <?php selected( $filter_orderby, 'name' ); ?>><?php esc_html_e( 'Nome A-Z', 'pawstars' ); ?></option>
                </select>
            </div>

            <div class="filter-group filter-search">
                <input type="text" name="search" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="<?php esc_attr_e( 'Cerca...', 'pawstars' ); ?>" class="filter-input">
            </div>

            <button type="submit" class="btn btn-filter"><?php esc_html_e( 'Filtra', 'pawstars' ); ?></button>
        </form>

        <!-- View Toggle -->
        <div class="view-toggle">
            <button class="view-btn <?php echo ! $is_mobile ? 'active' : ''; ?>" data-view="grid" title="<?php esc_attr_e( 'Vista Griglia', 'pawstars' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </button>
            <button class="view-btn <?php echo $is_mobile ? 'active' : ''; ?>" data-view="swipe" title="<?php esc_attr_e( 'Vista Swipe', 'pawstars' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                </svg>
            </button>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="pawstars-stats-bar">
        <span class="stat"><?php printf( esc_html__( '%d cani trovati', 'pawstars' ), $total ); ?></span>
    </div>

    <!-- Feed Content -->
    <?php if ( ! empty( $dogs ) ) : ?>
        <!-- Grid View -->
        <div class="pawstars-feed pawstars-grid <?php echo $is_mobile ? 'hidden' : ''; ?>" data-view="grid">
            <?php foreach ( $dogs as $dog ) : ?>
                <?php include PAWSTARS_PLUGIN_DIR . 'public/templates/partials/dog-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Swipe View -->
        <div class="pawstars-feed pawstars-swipe <?php echo ! $is_mobile ? 'hidden' : ''; ?>" data-view="swipe">
            <div class="swipe-container">
                <div class="swipe-cards" id="swipeCards">
                    <?php foreach ( $dogs as $index => $dog ) : ?>
                        <div class="swipe-card <?php echo $index === 0 ? 'active' : ''; ?>" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
                            <div class="swipe-card-image">
                                <?php if ( $dog->image_url ) : ?>
                                    <img src="<?php echo esc_url( $dog->image_url ); ?>" alt="<?php echo esc_attr( $dog->name ); ?>" loading="lazy">
                                <?php else : ?>
                                    <div class="no-image-placeholder">🐕</div>
                                <?php endif; ?>
                                <div class="swipe-overlay swipe-love">❤️</div>
                                <div class="swipe-overlay swipe-pass">✗</div>
                            </div>
                            <div class="swipe-card-content">
                                <h3 class="dog-name"><?php echo esc_html( $dog->name ); ?></h3>
                                <?php if ( $dog->breed_name ) : ?>
                                    <p class="dog-breed"><?php echo esc_html( $dog->breed_name ); ?></p>
                                <?php endif; ?>
                                <div class="dog-points">⭐ <?php echo esc_html( $dog->total_points ); ?> <?php esc_html_e( 'punti', 'pawstars' ); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Swipe Actions -->
                <div class="swipe-actions">
                    <button class="swipe-action-btn action-pass" data-action="pass">
                        <span>✗</span>
                    </button>
                    <button class="swipe-action-btn action-star" data-action="star">
                        <span>⭐</span>
                    </button>
                    <button class="swipe-action-btn action-love" data-action="love">
                        <span>❤️</span>
                    </button>
                </div>
            </div>

            <div class="swipe-empty hidden">
                <div class="empty-icon">🐾</div>
                <h3><?php esc_html_e( 'Non ci sono altri cani!', 'pawstars' ); ?></h3>
                <p><?php esc_html_e( 'Torna più tardi per scoprirne di nuovi.', 'pawstars' ); ?></p>
                <button class="btn btn-primary" id="reloadSwipe"><?php esc_html_e( 'Ricomincia', 'pawstars' ); ?></button>
            </div>
        </div>

        <!-- Infinite Scroll Trigger -->
        <div class="pawstars-load-more" data-page="1" data-total="<?php echo esc_attr( $total ); ?>">
            <button class="btn btn-secondary" id="loadMoreDogs">
                <?php esc_html_e( 'Carica Altri', 'pawstars' ); ?>
            </button>
            <div class="loading-spinner hidden"></div>
        </div>

    <?php else : ?>
        <div class="pawstars-empty">
            <div class="empty-icon">🐾</div>
            <h3><?php esc_html_e( 'Nessun cane trovato', 'pawstars' ); ?></h3>
            <p><?php esc_html_e( 'Prova a modificare i filtri o aggiungi il tuo cane!', 'pawstars' ); ?></p>
        </div>
    <?php endif; ?>
</div>
