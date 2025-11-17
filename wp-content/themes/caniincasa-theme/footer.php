<?php
/**
 * The footer template
 *
 * @package Caniincasa
 * @since 1.0.0
 */
?>

    <?php if ( ! is_page_template( 'template-dashboard.php' ) ) : ?>

    <!-- Footer -->
    <footer id="colophon" class="site-footer">

        <!-- Footer Widgets -->
        <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
        <div class="footer-widgets">
            <div class="container">
                <div class="footer-widgets-grid">
                    <?php for ( $i = 1; $i <= 4; $i++ ) : ?>
                        <?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
                            <div class="footer-widget-column">
                                <?php dynamic_sidebar( 'footer-' . $i ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <div class="footer-info">
                        <p class="copyright">
                            &copy; <?php echo date( 'Y' ); ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
                            - <?php esc_html_e( 'Tutti i diritti riservati', 'caniincasa' ); ?>
                        </p>
                    </div>

                    <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <nav class="footer-navigation">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'menu_class'     => 'footer-menu',
                            'container'      => false,
                            'depth'          => 1,
                        ) );
                        ?>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <?php endif; // End footer check ?>

</div><!-- #page -->

<!-- Mobile Bottom Navigation -->
<?php if ( ! is_page_template( 'template-dashboard.php' ) ) : ?>
<nav class="mobile-bottom-nav mobile-show">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Home', 'caniincasa' ); ?></span>
    </a>

    <a href="<?php echo esc_url( home_url( '/annunci' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M19 11H5M19 11C20.1046 11 21 11.8954 21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V13C3 11.8954 3.89543 11 5 11M19 11V9C19 7.89543 18.1046 7 17 7M5 11V9C5 7.89543 5.89543 7 7 7M7 7V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V7M7 7H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Annunci', 'caniincasa' ); ?></span>
    </a>

    <a href="<?php echo esc_url( home_url( '/razze-di-cani' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Razze', 'caniincasa' ); ?></span>
    </a>

    <?php if ( is_user_logged_in() ) : ?>
    <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Profilo', 'caniincasa' ); ?></span>
    </a>
    <?php else : ?>
    <a href="<?php echo esc_url( wp_login_url() ); ?>" class="mobile-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15M10 17L15 12M15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?php esc_html_e( 'Login', 'caniincasa' ); ?></span>
    </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
