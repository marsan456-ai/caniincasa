<?php
/**
 * Template Name: Chi Siamo
 *
 * Template per la pagina Chi Siamo
 * Tutti i contenuti sono personalizzabili da Aspetto → Personalizza
 *
 * @package Caniincasa
 */

get_header();
?>

<main class="chi-siamo-page">
    <!-- Hero Section -->
    <section class="chi-siamo-hero">
        <?php
        $hero_image = get_theme_mod( 'chi_siamo_hero_image', '' );
        if ( $hero_image ) :
        ?>
            <div class="hero-image" style="background-image: url('<?php echo esc_url( $hero_image ); ?>');">
                <div class="hero-overlay"></div>
            </div>
        <?php endif; ?>

        <div class="container">
            <div class="hero-content">
                <h1 class="page-title">
                    <?php
                    $title = get_theme_mod( 'chi_siamo_title', 'Chi Siamo' );
                    echo esc_html( $title );
                    ?>
                </h1>
                <?php
                $subtitle = get_theme_mod( 'chi_siamo_subtitle', 'La nostra storia, la nostra passione per i cani' );
                if ( $subtitle ) :
                ?>
                    <p class="page-subtitle">
                        <?php echo esc_html( $subtitle ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Introduzione -->
    <section class="chi-siamo-intro section-padding">
        <div class="container">
            <div class="intro-content">
                <?php
                $intro_text = get_theme_mod( 'chi_siamo_intro_text', 'Benvenuti su Caniincasa, il punto di riferimento per tutti gli amanti dei cani in Italia.' );
                if ( $intro_text ) :
                ?>
                    <div class="intro-text">
                        <?php echo wpautop( wp_kses_post( $intro_text ) ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Missione -->
    <section class="chi-siamo-mission section-padding bg-light">
        <div class="container">
            <div class="mission-grid">
                <?php
                $mission_image = get_theme_mod( 'chi_siamo_mission_image', '' );
                if ( $mission_image ) :
                ?>
                    <div class="mission-image">
                        <img src="<?php echo esc_url( $mission_image ); ?>" alt="<?php esc_attr_e( 'La nostra missione', 'caniincasa' ); ?>">
                    </div>
                <?php endif; ?>

                <div class="mission-content">
                    <h2 class="section-title">
                        <?php
                        $mission_title = get_theme_mod( 'chi_siamo_mission_title', 'La Nostra Missione' );
                        echo esc_html( $mission_title );
                        ?>
                    </h2>
                    <?php
                    $mission_text = get_theme_mod( 'chi_siamo_mission_text', 'La nostra missione è quella di creare una community unita dalla passione per i cani.' );
                    if ( $mission_text ) :
                    ?>
                        <div class="mission-text">
                            <?php echo wpautop( wp_kses_post( $mission_text ) ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Valori -->
    <section class="chi-siamo-values section-padding">
        <div class="container">
            <h2 class="section-title text-center">
                <?php
                $values_title = get_theme_mod( 'chi_siamo_values_title', 'I Nostri Valori' );
                echo esc_html( $values_title );
                ?>
            </h2>

            <div class="values-grid">
                <?php
                // Valore 1
                $value_1_icon = get_theme_mod( 'chi_siamo_value_1_icon', '❤️' );
                $value_1_title = get_theme_mod( 'chi_siamo_value_1_title', 'Passione' );
                $value_1_text = get_theme_mod( 'chi_siamo_value_1_text', 'Amiamo i cani e lavoriamo ogni giorno per il loro benessere.' );

                if ( $value_1_title ) :
                ?>
                    <div class="value-card">
                        <?php if ( $value_1_icon ) : ?>
                            <div class="value-icon"><?php echo esc_html( $value_1_icon ); ?></div>
                        <?php endif; ?>
                        <h3 class="value-title"><?php echo esc_html( $value_1_title ); ?></h3>
                        <p class="value-text"><?php echo esc_html( $value_1_text ); ?></p>
                    </div>
                <?php endif; ?>

                <?php
                // Valore 2
                $value_2_icon = get_theme_mod( 'chi_siamo_value_2_icon', '🤝' );
                $value_2_title = get_theme_mod( 'chi_siamo_value_2_title', 'Comunità' );
                $value_2_text = get_theme_mod( 'chi_siamo_value_2_text', 'Crediamo nella forza della community e nella condivisione.' );

                if ( $value_2_title ) :
                ?>
                    <div class="value-card">
                        <?php if ( $value_2_icon ) : ?>
                            <div class="value-icon"><?php echo esc_html( $value_2_icon ); ?></div>
                        <?php endif; ?>
                        <h3 class="value-title"><?php echo esc_html( $value_2_title ); ?></h3>
                        <p class="value-text"><?php echo esc_html( $value_2_text ); ?></p>
                    </div>
                <?php endif; ?>

                <?php
                // Valore 3
                $value_3_icon = get_theme_mod( 'chi_siamo_value_3_icon', '🎯' );
                $value_3_title = get_theme_mod( 'chi_siamo_value_3_title', 'Qualità' );
                $value_3_text = get_theme_mod( 'chi_siamo_value_3_text', 'Offriamo solo informazioni verificate e servizi di qualità.' );

                if ( $value_3_title ) :
                ?>
                    <div class="value-card">
                        <?php if ( $value_3_icon ) : ?>
                            <div class="value-icon"><?php echo esc_html( $value_3_icon ); ?></div>
                        <?php endif; ?>
                        <h3 class="value-title"><?php echo esc_html( $value_3_title ); ?></h3>
                        <p class="value-text"><?php echo esc_html( $value_3_text ); ?></p>
                    </div>
                <?php endif; ?>

                <?php
                // Valore 4
                $value_4_icon = get_theme_mod( 'chi_siamo_value_4_icon', '🌟' );
                $value_4_title = get_theme_mod( 'chi_siamo_value_4_title', 'Innovazione' );
                $value_4_text = get_theme_mod( 'chi_siamo_value_4_text', 'Utilizziamo la tecnologia per migliorare la vita dei cani e dei loro proprietari.' );

                if ( $value_4_title ) :
                ?>
                    <div class="value-card">
                        <?php if ( $value_4_icon ) : ?>
                            <div class="value-icon"><?php echo esc_html( $value_4_icon ); ?></div>
                        <?php endif; ?>
                        <h3 class="value-title"><?php echo esc_html( $value_4_title ); ?></h3>
                        <p class="value-text"><?php echo esc_html( $value_4_text ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <?php
    $cta_enabled = get_theme_mod( 'chi_siamo_cta_enabled', true );
    if ( $cta_enabled ) :
    ?>
        <section class="chi-siamo-cta section-padding bg-primary">
            <div class="container">
                <div class="cta-content text-center">
                    <h2 class="cta-title">
                        <?php
                        $cta_title = get_theme_mod( 'chi_siamo_cta_title', 'Unisciti alla Nostra Community' );
                        echo esc_html( $cta_title );
                        ?>
                    </h2>
                    <?php
                    $cta_text = get_theme_mod( 'chi_siamo_cta_text', 'Registrati oggi e scopri tutti i servizi dedicati a te e al tuo cane.' );
                    if ( $cta_text ) :
                    ?>
                        <p class="cta-text"><?php echo esc_html( $cta_text ); ?></p>
                    <?php endif; ?>

                    <?php
                    $cta_button_text = get_theme_mod( 'chi_siamo_cta_button_text', 'Registrati Ora' );
                    $cta_button_url = get_theme_mod( 'chi_siamo_cta_button_url', home_url( '/registrazione/' ) );
                    if ( $cta_button_text && $cta_button_url ) :
                    ?>
                        <a href="<?php echo esc_url( $cta_button_url ); ?>" class="btn btn-white btn-large">
                            <?php echo esc_html( $cta_button_text ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
