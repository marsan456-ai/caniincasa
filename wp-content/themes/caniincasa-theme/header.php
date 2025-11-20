<?php
/**
 * The header template
 *
 * @package Caniincasa
 * @since 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Vai al contenuto', 'caniincasa' ); ?></a>

    <?php if ( ! is_page_template( 'template-dashboard.php' ) ) : ?>

    <!-- Top Bar (Desktop Only) -->
    <div class="top-bar desktop-hide-mobile">
        <div class="container">
            <div class="top-bar-content">
                <nav class="top-bar-nav">
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/inserisci-annuncio/' ) ); ?>" class="top-bar-cta">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 4px;">
                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
                        </a>
                    <?php else : ?>
                        <button type="button" class="top-bar-cta js-open-annuncio-modal">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 4px;">
                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
                        </button>
                    <?php endif; ?>
                    <?php
                    if ( is_user_logged_in() ) {
                        $current_user = wp_get_current_user();
                        ?>
                        <span class="top-bar-welcome">
                            <?php printf( __( 'Ciao, %s', 'caniincasa' ), esc_html( $current_user->display_name ) ); ?>
                        </span>
                        <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" style="position: relative; display: inline-flex; align-items: center; gap: 6px;">
                            <?php esc_html_e( 'Dashboard', 'caniincasa' ); ?>
                            <?php
                            $unread_count = caniincasa_get_unread_count( get_current_user_id() );
                            if ( $unread_count > 0 ) :
                            ?>
                                <span class="messages-badge"><?php echo esc_html( $unread_count ); ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Logout', 'caniincasa' ); ?></a>
                    <?php } else { ?>
                        <a href="<?php echo esc_url( home_url( '/login' ) ); ?>"><?php esc_html_e( 'Login', 'caniincasa' ); ?></a>
                        <a href="<?php echo esc_url( home_url( '/registrazione' ) ); ?>"><?php esc_html_e( 'Registrazione', 'caniincasa' ); ?></a>
                    <?php } ?>
                    <a href="<?php echo esc_url( home_url( '/contatti' ) ); ?>"><?php esc_html_e( 'Contatti', 'caniincasa' ); ?></a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header id="masthead" class="site-header sticky-header">
        <div class="container">
            <div class="header-wrapper">

                <!-- Logo -->
                <div class="site-branding">
                    <?php
                    if ( has_custom_logo() ) {
                        the_custom_logo();
                    } else {
                        ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="site-logo-text">
                            <h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
                        </a>
                        <?php
                    }
                    ?>
                </div>

                <!-- Desktop Navigation -->
                <nav id="site-navigation" class="main-navigation desktop-hide-mobile">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'menu_class'     => 'primary-menu',
                        'container'      => false,
                        'fallback_cb'    => false,
                    ) );
                    ?>
                </nav>

                <!-- Header Right (Search + Mobile Menu Toggle) -->
                <div class="header-right">
                    <!-- Search Icon -->
                    <button class="search-toggle" aria-label="<?php esc_attr_e( 'Apri ricerca', 'caniincasa' ); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle mobile-show" aria-label="<?php esc_attr_e( 'Apri menu', 'caniincasa' ); ?>" aria-expanded="false">
                        <span class="hamburger">
                            <span class="line"></span>
                            <span class="line"></span>
                            <span class="line"></span>
                        </span>
                    </button>
                </div>

            </div>
        </div>

        <!-- Search Overlay -->
        <div class="search-overlay">
            <div class="search-overlay-content">
                <button class="search-close" aria-label="<?php esc_attr_e( 'Chiudi ricerca', 'caniincasa' ); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php get_search_form(); ?>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay mobile-show">
        <div class="mobile-nav-content">
            <div class="mobile-nav-header">
                <div class="site-branding-mobile">
                    <?php
                    if ( has_custom_logo() ) {
                        the_custom_logo();
                    } else {
                        ?>
                        <span class="site-title"><?php bloginfo( 'name' ); ?></span>
                        <?php
                    }
                    ?>
                </div>
                <button class="mobile-nav-close" aria-label="<?php esc_attr_e( 'Chiudi menu', 'caniincasa' ); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <nav class="mobile-navigation">
                <?php
                // Use mobile menu if set, otherwise fallback to primary menu
                $mobile_menu_location = 'mobile';
                if ( ! has_nav_menu( 'mobile' ) && has_nav_menu( 'primary' ) ) {
                    $mobile_menu_location = 'primary';
                }

                wp_nav_menu( array(
                    'theme_location' => $mobile_menu_location,
                    'menu_class'     => 'mobile-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                ) );
                ?>
            </nav>

            <!-- Mobile User Menu -->
            <div class="mobile-user-menu">
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( home_url( '/inserisci-annuncio/' ) ); ?>" class="mobile-user-link mobile-cta-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="mobile-user-link" style="position: relative;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e( 'Dashboard', 'caniincasa' ); ?>
                        <?php
                        $unread_count = caniincasa_get_unread_count( get_current_user_id() );
                        if ( $unread_count > 0 ) :
                        ?>
                            <span class="messages-badge" style="position: absolute; top: -5px; right: -5px;"><?php echo esc_html( $unread_count ); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="mobile-user-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9M16 17L21 12M21 12L16 7M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e( 'Logout', 'caniincasa' ); ?>
                    </a>
                <?php else : ?>
                    <button type="button" class="mobile-user-link mobile-cta-link js-open-annuncio-modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
                    </button>
                    <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="mobile-user-link">
                        <?php esc_html_e( 'Login', 'caniincasa' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/registrazione' ) ); ?>" class="mobile-user-link">
                        <?php esc_html_e( 'Registrazione', 'caniincasa' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php endif; // End header check ?>
