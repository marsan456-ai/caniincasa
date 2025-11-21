<?php
/**
 * Template Name: Chi Siamo
 *
 * @package Caniincasa
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main chi-siamo-page">

    <?php while ( have_posts() ) : the_post(); ?>

        <!-- Page Hero -->
        <div class="page-hero">
            <div class="container">
                <h1 class="page-title">
                    <?php echo esc_html( get_theme_mod( 'chi_siamo_title', get_the_title() ) ); ?>
                </h1>
                <?php if ( get_theme_mod( 'chi_siamo_subtitle' ) ) : ?>
                    <p class="page-description">
                        <?php echo esc_html( get_theme_mod( 'chi_siamo_subtitle', '' ) ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="container">
            <div class="breadcrumbs-wrapper">
                <?php caniincasa_breadcrumbs(); ?>
            </div>
        </div>

        <!-- Chi Siamo Content -->
        <div class="container">
            <div class="chi-siamo-content">

                <!-- Intro Section -->
                <section class="chi-siamo-intro section-padding">
                    <div class="intro-grid">
                        <div class="intro-text">
                            <h2><?php echo esc_html( get_theme_mod( 'chi_siamo_intro_title', 'La Nostra Storia' ) ); ?></h2>
                            <div class="intro-description">
                                <?php
                                $intro_text = get_theme_mod( 'chi_siamo_intro_text', get_the_content() );
                                echo wp_kses_post( wpautop( $intro_text ) );
                                ?>
                            </div>
                        </div>
                        <?php if ( get_theme_mod( 'chi_siamo_intro_image' ) ) : ?>
                            <div class="intro-image">
                                <img src="<?php echo esc_url( get_theme_mod( 'chi_siamo_intro_image' ) ); ?>"
                                     alt="<?php echo esc_attr( get_theme_mod( 'chi_siamo_title', 'Chi Siamo' ) ); ?>"
                                     class="img-fluid">
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Missione Section -->
                <?php if ( get_theme_mod( 'chi_siamo_mission_text' ) ) : ?>
                <section class="chi-siamo-mission section-padding">
                    <div class="mission-box">
                        <h2><?php echo esc_html( get_theme_mod( 'chi_siamo_mission_title', 'La Nostra Missione' ) ); ?></h2>
                        <div class="mission-text">
                            <?php echo wp_kses_post( wpautop( get_theme_mod( 'chi_siamo_mission_text', '' ) ) ); ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Valori Section -->
                <?php if ( get_theme_mod( 'chi_siamo_show_values', true ) ) : ?>
                <section class="chi-siamo-values section-padding">
                    <h2 class="section-title text-center">
                        <?php echo esc_html( get_theme_mod( 'chi_siamo_values_title', 'I Nostri Valori' ) ); ?>
                    </h2>

                    <div class="values-grid">
                        <?php for ( $i = 1; $i <= 4; $i++ ) :
                            $value_title = get_theme_mod( "chi_siamo_value_{$i}_title" );
                            $value_text  = get_theme_mod( "chi_siamo_value_{$i}_text" );
                            $value_icon  = get_theme_mod( "chi_siamo_value_{$i}_icon", '⭐' );

                            if ( $value_title ) :
                        ?>
                            <div class="value-box">
                                <div class="value-icon"><?php echo esc_html( $value_icon ); ?></div>
                                <h3 class="value-title"><?php echo esc_html( $value_title ); ?></h3>
                                <?php if ( $value_text ) : ?>
                                    <p class="value-text"><?php echo esc_html( $value_text ); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php
                            endif;
                        endfor;
                        ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Team Section -->
                <?php if ( get_theme_mod( 'chi_siamo_show_team', false ) ) : ?>
                <section class="chi-siamo-team section-padding">
                    <h2 class="section-title text-center">
                        <?php echo esc_html( get_theme_mod( 'chi_siamo_team_title', 'Il Nostro Team' ) ); ?>
                    </h2>

                    <div class="team-grid">
                        <?php for ( $i = 1; $i <= 6; $i++ ) :
                            $member_name  = get_theme_mod( "chi_siamo_member_{$i}_name" );
                            $member_role  = get_theme_mod( "chi_siamo_member_{$i}_role" );
                            $member_image = get_theme_mod( "chi_siamo_member_{$i}_image" );
                            $member_bio   = get_theme_mod( "chi_siamo_member_{$i}_bio" );

                            if ( $member_name ) :
                        ?>
                            <div class="team-member">
                                <?php if ( $member_image ) : ?>
                                    <div class="member-photo">
                                        <img src="<?php echo esc_url( $member_image ); ?>"
                                             alt="<?php echo esc_attr( $member_name ); ?>"
                                             class="img-fluid">
                                    </div>
                                <?php endif; ?>
                                <h3 class="member-name"><?php echo esc_html( $member_name ); ?></h3>
                                <?php if ( $member_role ) : ?>
                                    <p class="member-role"><?php echo esc_html( $member_role ); ?></p>
                                <?php endif; ?>
                                <?php if ( $member_bio ) : ?>
                                    <p class="member-bio"><?php echo esc_html( $member_bio ); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php
                            endif;
                        endfor;
                        ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- CTA Section -->
                <?php if ( get_theme_mod( 'chi_siamo_cta_text' ) ) : ?>
                <section class="chi-siamo-cta section-padding text-center">
                    <div class="cta-box">
                        <h2><?php echo esc_html( get_theme_mod( 'chi_siamo_cta_title', 'Unisciti a Noi' ) ); ?></h2>
                        <p><?php echo esc_html( get_theme_mod( 'chi_siamo_cta_text', '' ) ); ?></p>
                        <?php if ( get_theme_mod( 'chi_siamo_cta_button_text' ) ) : ?>
                            <a href="<?php echo esc_url( get_theme_mod( 'chi_siamo_cta_button_url', '#' ) ); ?>"
                               class="btn btn-primary btn-lg">
                                <?php echo esc_html( get_theme_mod( 'chi_siamo_cta_button_text', '' ) ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>

            </div>
        </div>

    <?php endwhile; ?>

</main>

<?php
get_footer();
