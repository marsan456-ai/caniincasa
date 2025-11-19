<?php
/**
 * Template for single Razza di Cani
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main single-razza">

    <?php while ( have_posts() ) : the_post(); ?>

        <!-- Hero Section -->
        <div class="single-hero">
            <div class="container">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php
                $nazione = get_field( 'nazione_origine' );
                if ( $nazione ) :
                    ?>
                    <p class="entry-subtitle"><?php echo esc_html( $nazione ); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="container">
            <div class="breadcrumbs-wrapper">
                <?php caniincasa_breadcrumbs(); ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="container">
            <div class="razza-content-wrapper">

                <!-- Main Content (2/3) -->
                <article class="razza-main-content">

                    <!-- Descrizione Generale -->
                    <?php
                    $descrizione = get_field( 'descrizione_generale' );
                    if ( $descrizione ) :
                        ?>
                        <div class="razza-section razza-descrizione">
                            <?php echo wp_kses_post( $descrizione ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Origini e Storia -->
                    <?php
                    $origini = get_field( 'origini_storia' );
                    if ( $origini ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">📜</span>
                                Origini e Storia
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $origini ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Aspetto Fisico -->
                    <?php
                    $aspetto = get_field( 'aspetto_fisico' );
                    if ( $aspetto ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">🐕</span>
                                Aspetto Fisico
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $aspetto ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Carattere e Temperamento -->
                    <?php
                    $carattere = get_field( 'carattere_temperamento' );
                    if ( $carattere ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">❤️</span>
                                Carattere e Temperamento
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $carattere ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Salute e Cura -->
                    <?php
                    $salute = get_field( 'salute_cura' );
                    if ( $salute ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">🏥</span>
                                Salute e Cura
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $salute ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Attività e Addestramento -->
                    <?php
                    $attivita = get_field( 'attivita_addestramento' );
                    if ( $attivita ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">🎾</span>
                                Attività e Addestramento
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $attivita ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ideale Per -->
                    <?php
                    $ideale = get_field( 'ideale_per' );
                    if ( $ideale ) :
                        ?>
                        <div class="razza-section">
                            <h2 class="section-title">
                                <span class="icon">👨‍👩‍👧‍👦</span>
                                Ideale Per
                            </h2>
                            <div class="section-content">
                                <?php echo wp_kses_post( $ideale ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Caratteristiche della Razza (Box Arancione con Rating) -->
                    <div class="razza-caratteristiche-box">
                        <h2 class="box-title">Caratteristiche della Razza</h2>

                        <div class="caratteristiche-grid">

                            <!-- Temperamento & Comportamento -->
                            <div class="caratteristica-categoria">
                                <h3 class="categoria-title">
                                    <span class="icon">😊</span>
                                    Temperamento & Comportamento
                                </h3>
                                <div class="caratteristiche-list">
                                    <?php
                                    $caratteristiche_temp = array(
                                        'affettuosita'                                   => 'Affettuosità',
                                        'socievolezza_cani'                              => 'Socievolezza',
                                        'tolleranza_estranei'                            => 'Tolleranza Estranei',
                                        'compatibilita_con_i_bambini'                    => 'Compatibile con Bambini',
                                        'compatibilita_con_altri_animali_domestici'      => 'Compatibile con Altri Animali',
                                        'vocalita_e_predisposizione_ad_abbaiare'         => 'Vocalità',
                                    );
                                    foreach ( $caratteristiche_temp as $key => $label ) :
                                        $value = get_field( $key );
                                        if ( $value ) :
                                            ?>
                                            <div class="caratteristica-item">
                                                <span class="caratteristica-label"><?php echo esc_html( $label ); ?></span>
                                                <span class="caratteristica-value">
                                                    <?php echo caniincasa_get_rating_stars( $value ); ?>
                                                    <span class="rating-number"><?php echo number_format( $value, 1 ); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Adattabilità -->
                            <div class="caratteristica-categoria">
                                <h3 class="categoria-title">
                                    <span class="icon">🏠</span>
                                    Adattabilità
                                </h3>
                                <div class="caratteristiche-list">
                                    <?php
                                    $caratteristiche_adapt = array(
                                        'adattabilita_appartamento'     => 'Adatto ad Appartamento',
                                        'adattabilita_clima_caldo'      => 'Tollera Clima Caldo',
                                        'adattabilita_clima_freddo'     => 'Tollera Clima Freddo',
                                        'tolleranza_alla_solitudine'    => 'Tolleranza Solitudine',
                                    );
                                    foreach ( $caratteristiche_adapt as $key => $label ) :
                                        $value = get_field( $key );
                                        if ( $value ) :
                                            ?>
                                            <div class="caratteristica-item">
                                                <span class="caratteristica-label"><?php echo esc_html( $label ); ?></span>
                                                <span class="caratteristica-value">
                                                    <?php echo caniincasa_get_rating_stars( $value ); ?>
                                                    <span class="rating-number"><?php echo number_format( $value, 1 ); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Famiglia & Socialità -->
                            <div class="caratteristica-categoria">
                                <h3 class="categoria-title">
                                    <span class="icon">👨‍👩‍👧</span>
                                    Famiglia & Socialità
                                </h3>
                                <div class="caratteristiche-list">
                                    <?php
                                    $caratteristiche_fam = array(
                                        'affettuosita'                                   => 'Affettuosità',
                                        'socievolezza_cani'                              => 'Socievolezza con Altri Cani',
                                        'compatibilita_con_i_bambini'                    => 'Compatibilità con Bambini',
                                        'compatibilita_con_altri_animali_domestici'      => 'Compatibilità con Altri Animali',
                                        'tolleranza_estranei'                            => 'Tolleranza verso Estranei',
                                    );
                                    foreach ( $caratteristiche_fam as $key => $label ) :
                                        $value = get_field( $key );
                                        if ( $value ) :
                                            ?>
                                            <div class="caratteristica-item">
                                                <span class="caratteristica-label"><?php echo esc_html( $label ); ?></span>
                                                <span class="caratteristica-value">
                                                    <?php echo caniincasa_get_rating_stars( $value ); ?>
                                                    <span class="rating-number"><?php echo number_format( $value, 1 ); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Addestramento & Cura -->
                            <div class="caratteristica-categoria">
                                <h3 class="categoria-title">
                                    <span class="icon">🎓</span>
                                    Addestramento & Cura
                                </h3>
                                <div class="caratteristiche-list">
                                    <?php
                                    $caratteristiche_train = array(
                                        'intelligenza'                  => 'Intelligenza',
                                        'facilita_di_addestramento'     => 'Facilità Addestramento',
                                        'facilita_toelettatura'         => 'Facilità Toelettatura',
                                        'cura_e_perdita_pelo'           => 'Cura e Perdita Pelo',
                                        'livello_esperienza_richiesto'  => 'Livello Esperienza',
                                        'costo_mantenimento'            => 'Costo Mantenimento',
                                    );
                                    foreach ( $caratteristiche_train as $key => $label ) :
                                        $value = get_field( $key );
                                        if ( $value ) :
                                            ?>
                                            <div class="caratteristica-item">
                                                <span class="caratteristica-label"><?php echo esc_html( $label ); ?></span>
                                                <span class="caratteristica-value">
                                                    <?php echo caniincasa_get_rating_stars( $value ); ?>
                                                    <span class="rating-number"><?php echo number_format( $value, 1 ); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Esigenze & Attività -->
                            <div class="caratteristica-categoria">
                                <h3 class="categoria-title">
                                    <span class="icon">⚡</span>
                                    Esigenze & Attività
                                </h3>
                                <div class="caratteristiche-list">
                                    <?php
                                    $caratteristiche_energy = array(
                                        'energia_e_livelli_di_attivita' => 'Livello di Energia',
                                        'esigenze_di_esercizio'         => 'Esigenze di Esercizio',
                                        'istinti_di_caccia'             => 'Istinti di Caccia',
                                    );
                                    foreach ( $caratteristiche_energy as $key => $label ) :
                                        $value = get_field( $key );
                                        if ( $value ) :
                                            ?>
                                            <div class="caratteristica-item">
                                                <span class="caratteristica-label"><?php echo esc_html( $label ); ?></span>
                                                <span class="caratteristica-value">
                                                    <?php echo caniincasa_get_rating_stars( $value ); ?>
                                                    <span class="rating-number"><?php echo number_format( $value, 1 ); ?></span>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Social Share -->
                    <div class="razza-share">
                        <?php caniincasa_social_share_buttons(); ?>
                    </div>

                    <!-- Related Razze -->
                    <?php
                    $terms = get_the_terms( get_the_ID(), 'razza_taglia' );
                    if ( $terms && ! is_wp_error( $terms ) ) {
                        $term_ids = wp_list_pluck( $terms, 'term_id' );

                        $related_args = array(
                            'post_type'      => 'razze_di_cani',
                            'posts_per_page' => 3,
                            'post__not_in'   => array( get_the_ID() ),
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'razza_taglia',
                                    'field'    => 'term_id',
                                    'terms'    => $term_ids,
                                ),
                            ),
                        );

                        $related_query = new WP_Query( $related_args );

                        if ( $related_query->have_posts() ) :
                            ?>
                            <div class="razze-correlate">
                                <h2>Razze Simili</h2>
                                <div class="razze-grid">
                                    <?php
                                    while ( $related_query->have_posts() ) :
                                        $related_query->the_post();
                                        ?>
                                        <div class="razza-card">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php if ( has_post_thumbnail() ) : ?>
                                                    <?php the_post_thumbnail( 'caniincasa-small' ); ?>
                                                <?php endif; ?>
                                                <h3><?php the_title(); ?></h3>
                                                <?php
                                                $affettuosita = get_field( 'affettuosita' );
                                                $energia = get_field( 'energia_e_livelli_di_attivita' );
                                                if ( $affettuosita || $energia ) :
                                                    ?>
                                                    <div class="razza-quick-info">
                                                        <?php if ( $affettuosita ) : ?>
                                                            <span>❤️ <?php echo number_format( $affettuosita, 1 ); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ( $energia ) : ?>
                                                            <span>⚡ <?php echo number_format( $energia, 1 ); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <?php
                            wp_reset_postdata();
                        endif;
                    }
                    ?>

                </article>

                <!-- Sidebar (1/3) -->
                <aside class="razza-sidebar">

                    <!-- Immagine Razza -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="razza-image-box">
                            <?php the_post_thumbnail( 'caniincasa-medium' ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Informazioni Razza -->
                    <div class="razza-info-box">
                        <h3 class="box-title">Informazioni Razza</h3>

                        <?php
                        $nazione = get_field( 'nazione_origine' );
                        if ( $nazione ) :
                            ?>
                            <div class="info-item">
                                <span class="info-label">🌍 Nazione d'Origine</span>
                                <span class="info-value"><?php echo esc_html( $nazione ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $colorazioni = get_field( 'colorazioni' );
                        if ( $colorazioni ) :
                            ?>
                            <div class="info-item">
                                <span class="info-label">🎨 Colorazioni</span>
                                <span class="info-value"><?php echo esc_html( $colorazioni ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $temperamento = get_field( 'temperamento_breve' );
                        if ( $temperamento ) :
                            ?>
                            <div class="info-item">
                                <span class="info-label">💭 Temperamento</span>
                                <span class="info-value"><?php echo esc_html( $temperamento ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $terms = get_the_terms( get_the_ID(), 'razza_taglia' );
                        if ( $terms && ! is_wp_error( $terms ) ) :
                            $taglia_names = wp_list_pluck( $terms, 'name' );
                            ?>
                            <div class="info-item">
                                <span class="info-label">📏 Taglia</span>
                                <span class="info-value"><?php echo esc_html( implode( ', ', $taglia_names ) ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $peso_min = get_field( 'peso_medio_min' );
                        $peso_max = get_field( 'peso_medio_max' );
                        if ( $peso_min || $peso_max ) :
                            ?>
                            <div class="info-item">
                                <span class="info-label">⚖️ Peso</span>
                                <span class="info-value">
                                    <?php
                                    if ( $peso_min && $peso_max ) {
                                        echo esc_html( $peso_min . ' - ' . $peso_max . ' kg' );
                                    } elseif ( $peso_min ) {
                                        echo esc_html( 'da ' . $peso_min . ' kg' );
                                    } else {
                                        echo esc_html( 'fino a ' . $peso_max . ' kg' );
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $vita_min = get_field( 'aspettativa_vita_min' );
                        $vita_max = get_field( 'aspettativa_vita_max' );
                        if ( $vita_min || $vita_max ) :
                            ?>
                            <div class="info-item">
                                <span class="info-label">⏳ Aspettativa di Vita</span>
                                <span class="info-value">
                                    <?php
                                    if ( $vita_min && $vita_max ) {
                                        echo esc_html( $vita_min . ' - ' . $vita_max . ' anni' );
                                    } elseif ( $vita_min ) {
                                        echo esc_html( 'da ' . $vita_min . ' anni' );
                                    } else {
                                        echo esc_html( 'fino a ' . $vita_max . ' anni' );
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Preferiti Box -->
                    <?php if ( is_user_logged_in() ) : ?>
                    <div class="sidebar-box preferiti-action-box">
                        <h3 class="box-title">Ti piace questa razza?</h3>
                        <?php echo caniincasa_get_preferiti_button( get_the_ID(), 'razze_di_cani' ); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Allevamenti Consigliati -->
                    <?php
                    // Filter allevamenti by current razza using meta_query
                    $allevamenti_args = array(
                        'post_type'      => 'allevamenti',
                        'posts_per_page' => 3,
                        'post_status'    => 'publish',
                        'orderby'        => 'rand',
                        'meta_query'     => array(
                            array(
                                'key'     => 'razze_allevate', // ACF relationship field
                                'value'   => '"' . get_the_ID() . '"', // Match serialized value
                                'compare' => 'LIKE',
                            ),
                        ),
                    );

                    $allevamenti_query = new WP_Query( $allevamenti_args );

                    if ( $allevamenti_query->have_posts() ) :
                        ?>
                        <div class="razza-allevamenti-box">
                            <h3 class="box-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                Allevamenti Consigliati
                            </h3>
                            <p class="box-subtitle">Trova cuccioli di <?php the_title(); ?> presso allevatori certificati</p>

                            <div class="allevamenti-list">
                                <?php
                                while ( $allevamenti_query->have_posts() ) :
                                    $allevamenti_query->the_post();
                                    $localita = get_field( 'localita' );
                                    $provincia = get_field( 'provincia' );
                                    $telefono = get_field( 'telefono' );
                                    $provincia_term = get_the_terms( get_the_ID(), 'provincia' );
                                    $provincia_name = $provincia_term && ! is_wp_error( $provincia_term ) ? $provincia_term[0]->name : $provincia;
                                    ?>
                                    <div class="allevamento-item">
                                        <h4 class="allevamento-nome">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                                <polyline points="9 22 9 12 15 12 15 22"/>
                                            </svg>
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                        <?php if ( $localita || $provincia_name ) : ?>
                                            <p class="allevamento-location">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                    <circle cx="12" cy="10" r="3"/>
                                                </svg>
                                                <?php
                                                $location_parts = array_filter( array( $localita, $provincia_name ) );
                                                echo esc_html( implode( ', ', $location_parts ) );
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ( $telefono ) : ?>
                                            <p class="allevamento-phone">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                </svg>
                                                <a href="tel:<?php echo esc_attr( caniincasa_format_phone_link( $telefono ) ); ?>">
                                                    <?php echo esc_html( $telefono ); ?>
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        <a href="<?php the_permalink(); ?>" class="allevamento-link">
                                            Vedi dettagli →
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <a href="<?php echo esc_url( home_url( '/allevamenti/' ) ); ?>" class="view-all-link">
                                Vedi tutti gli allevamenti →
                            </a>
                        </div>
                        <?php
                        wp_reset_postdata();
                    endif;
                    ?>

                    <!-- CTA Box -->
                    <div class="razza-cta-box">
                        <h3>Ti piace questa razza?</h3>
                        <p>Scopri gli allevamenti certificati e i canili dove potresti trovare il tuo futuro compagno.</p>
                        <a href="<?php echo esc_url( home_url( '/allevamenti/' ) ); ?>" class="btn btn-primary">
                            Trova Allevamenti
                        </a>
                        <a href="<?php echo esc_url( home_url( '/canili/' ) ); ?>" class="btn btn-secondary">
                            Scopri Canili
                        </a>
                    </div>

                    <!-- Widget Area -->
                    <?php if ( is_active_sidebar( 'sidebar-razze' ) ) : ?>
                        <?php dynamic_sidebar( 'sidebar-razze' ); ?>
                    <?php endif; ?>

                </aside>

            </div>
        </div>

    <?php endwhile; ?>

</main>

<?php
get_footer();
