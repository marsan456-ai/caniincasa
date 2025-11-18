<?php
/**
 * Template Name: Dashboard Utente
 * Template for user dashboard with tabs
 *
 * @package Caniincasa
 */

// Redirect to login if not logged in
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

// Get current tab from URL parameter
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'profilo';

// Handle form submissions
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['caniincasa_dashboard_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['caniincasa_dashboard_nonce'], 'caniincasa_dashboard_action' ) ) {

        // Update profile
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'update_profile' ) {
            $user_data = array(
                'ID'           => $user_id,
                'first_name'   => sanitize_text_field( $_POST['first_name'] ),
                'last_name'    => sanitize_text_field( $_POST['last_name'] ),
                'display_name' => sanitize_text_field( $_POST['display_name'] ),
                'user_email'   => sanitize_email( $_POST['user_email'] ),
            );

            $result = wp_update_user( $user_data );

            if ( ! is_wp_error( $result ) ) {
                // Update custom meta fields
                update_user_meta( $user_id, 'phone', sanitize_text_field( $_POST['phone'] ) );
                update_user_meta( $user_id, 'city', sanitize_text_field( $_POST['city'] ) );
                update_user_meta( $user_id, 'provincia', sanitize_text_field( $_POST['provincia'] ) );

                $success_message = 'Profilo aggiornato con successo!';
            } else {
                $error_message = 'Errore durante l\'aggiornamento del profilo.';
            }
        }

        // Change password
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'change_password' ) {
            if ( ! empty( $_POST['new_password'] ) && $_POST['new_password'] === $_POST['confirm_password'] ) {
                wp_set_password( $_POST['new_password'], $user_id );
                wp_logout();
                wp_redirect( wp_login_url() . '?password_changed=true' );
                exit;
            } else {
                $error_message = 'Le password non corrispondono.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'dashboard-page' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site dashboard-site">

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="dashboard-header-wrapper">
                <div class="dashboard-branding">
                    <?php if ( has_custom_logo() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php the_custom_logo(); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="dashboard-logo-text">
                            <h1><?php bloginfo( 'name' ); ?></h1>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="dashboard-header-right">
                    <span class="dashboard-user-name">
                        <?php printf( __( 'Ciao, %s', 'caniincasa' ), esc_html( $current_user->display_name ) ); ?>
                    </span>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                        Torna al Sito
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn btn-primary">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Main -->
    <main class="dashboard-main">
        <div class="container">

            <?php if ( isset( $success_message ) ) : ?>
                <div class="dashboard-message success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <?php echo esc_html( $success_message ); ?>
                </div>
            <?php endif; ?>

            <?php if ( isset( $error_message ) ) : ?>
                <div class="dashboard-message error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                    </svg>
                    <?php echo esc_html( $error_message ); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-content">

                <!-- Dashboard Sidebar -->
                <aside class="dashboard-sidebar">
                    <div class="dashboard-user-card">
                        <div class="user-avatar">
                            <?php echo get_avatar( $user_id, 80 ); ?>
                        </div>
                        <h3 class="user-name"><?php echo esc_html( $current_user->display_name ); ?></h3>
                        <p class="user-email"><?php echo esc_html( $current_user->user_email ); ?></p>
                        <?php
                        $user_type = get_user_meta( $user_id, 'user_type', true );
                        if ( $user_type ) {
                            $user_types = caniincasa_get_user_types();
                            $user_type_label = isset( $user_types[ $user_type ] ) ? $user_types[ $user_type ] : $user_type;
                            ?>
                            <span class="user-type-badge"><?php echo esc_html( $user_type_label ); ?></span>
                        <?php } ?>
                    </div>

                    <nav class="dashboard-nav">
                        <a href="?tab=profilo" class="dashboard-nav-item <?php echo $current_tab === 'profilo' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            Profilo
                        </a>
                        <a href="?tab=annunci" class="dashboard-nav-item <?php echo $current_tab === 'annunci' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                            I Miei Annunci
                        </a>
                        <a href="?tab=quiz" class="dashboard-nav-item <?php echo $current_tab === 'quiz' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                            </svg>
                            Storico Quiz
                        </a>
                        <a href="?tab=preferiti" class="dashboard-nav-item <?php echo $current_tab === 'preferiti' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            Preferiti
                        </a>
                    </nav>
                </aside>

                <!-- Dashboard Content Area -->
                <div class="dashboard-main-content">

                    <?php if ( $current_tab === 'profilo' ) : ?>
                        <!-- PROFILO TAB -->
                        <div class="dashboard-section">
                            <h2 class="section-title">Profilo Personale</h2>

                            <form method="post" class="dashboard-form">
                                <?php wp_nonce_field( 'caniincasa_dashboard_action', 'caniincasa_dashboard_nonce' ); ?>
                                <input type="hidden" name="action" value="update_profile">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">Nome</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Cognome</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="display_name">Nome Visualizzato</label>
                                    <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="user_email">Email</label>
                                    <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Telefono</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo esc_attr( get_user_meta( $user_id, 'phone', true ) ); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="city">Città</label>
                                        <input type="text" id="city" name="city" value="<?php echo esc_attr( get_user_meta( $user_id, 'city', true ) ); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="provincia">Provincia</label>
                                    <select id="provincia" name="provincia">
                                        <option value="">Seleziona provincia</option>
                                        <?php
                                        $province = get_terms( array(
                                            'taxonomy'   => 'provincia',
                                            'hide_empty' => false,
                                            'orderby'    => 'name',
                                            'order'      => 'ASC',
                                        ) );
                                        $current_provincia = get_user_meta( $user_id, 'provincia', true );
                                        foreach ( $province as $prov ) :
                                            ?>
                                            <option value="<?php echo esc_attr( $prov->slug ); ?>" <?php selected( $current_provincia, $prov->slug ); ?>>
                                                <?php echo esc_html( $prov->name ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg">Salva Modifiche</button>
                            </form>

                            <!-- Change Password Section -->
                            <div class="dashboard-subsection">
                                <h3 class="subsection-title">Cambia Password</h3>
                                <form method="post" class="dashboard-form">
                                    <?php wp_nonce_field( 'caniincasa_dashboard_action', 'caniincasa_dashboard_nonce' ); ?>
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="form-group">
                                        <label for="new_password">Nuova Password</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Conferma Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>

                                    <button type="submit" class="btn btn-secondary">Cambia Password</button>
                                </form>
                            </div>
                        </div>

                    <?php elseif ( $current_tab === 'annunci' ) : ?>
                        <!-- ANNUNCI TAB -->
                        <div class="dashboard-section">
                            <h2 class="section-title">I Miei Annunci</h2>

                            <?php
                            // Get user's annunci
                            $annunci_4zampe = new WP_Query( array(
                                'post_type'      => 'annunci_4zampe',
                                'author'         => $user_id,
                                'posts_per_page' => 20,
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            ) );

                            $annunci_dogsitter = new WP_Query( array(
                                'post_type'      => 'annunci_dogsitter',
                                'author'         => $user_id,
                                'posts_per_page' => 20,
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            ) );

                            $has_annunci = $annunci_4zampe->have_posts() || $annunci_dogsitter->have_posts();
                            ?>

                            <?php if ( $has_annunci ) : ?>

                                <?php if ( $annunci_4zampe->have_posts() ) : ?>
                                    <h3 class="subsection-title">Annunci 4 Zampe</h3>
                                    <div class="annunci-list">
                                        <?php while ( $annunci_4zampe->have_posts() ) : $annunci_4zampe->the_post(); ?>
                                            <div class="annuncio-item">
                                                <div class="annuncio-content">
                                                    <h4><?php the_title(); ?></h4>
                                                    <p class="annuncio-meta">
                                                        <span>Pubblicato: <?php echo get_the_date(); ?></span>
                                                        <span>Stato: <?php echo get_post_status() === 'publish' ? 'Pubblicato' : 'In revisione'; ?></span>
                                                    </p>
                                                </div>
                                                <div class="annuncio-actions">
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline">Visualizza</a>
                                                    <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-sm btn-primary">Modifica</a>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php wp_reset_postdata(); ?>
                                <?php endif; ?>

                                <?php if ( $annunci_dogsitter->have_posts() ) : ?>
                                    <h3 class="subsection-title">Annunci Dogsitter</h3>
                                    <div class="annunci-list">
                                        <?php while ( $annunci_dogsitter->have_posts() ) : $annunci_dogsitter->the_post(); ?>
                                            <div class="annuncio-item">
                                                <div class="annuncio-content">
                                                    <h4><?php the_title(); ?></h4>
                                                    <p class="annuncio-meta">
                                                        <span>Pubblicato: <?php echo get_the_date(); ?></span>
                                                        <span>Stato: <?php echo get_post_status() === 'publish' ? 'Pubblicato' : 'In revisione'; ?></span>
                                                    </p>
                                                </div>
                                                <div class="annuncio-actions">
                                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline">Visualizza</a>
                                                    <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-sm btn-primary">Modifica</a>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php wp_reset_postdata(); ?>
                                <?php endif; ?>

                            <?php else : ?>
                                <div class="empty-state">
                                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg>
                                    <h3>Nessun annuncio pubblicato</h3>
                                    <p>Non hai ancora creato nessun annuncio. Inizia ora!</p>
                                    <a href="<?php echo esc_url( home_url( '/pubblica-annuncio' ) ); ?>" class="btn btn-primary">Pubblica Annuncio</a>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ( $current_tab === 'quiz' ) : ?>
                        <!-- QUIZ TAB -->
                        <div class="dashboard-section">
                            <h2 class="section-title">Storico Quiz</h2>

                            <?php
                            $quiz_results = get_user_meta( $user_id, 'quiz_results', true );
                            ?>

                            <?php if ( ! empty( $quiz_results ) && is_array( $quiz_results ) ) : ?>
                                <div class="quiz-results-list">
                                    <?php foreach ( $quiz_results as $quiz ) : ?>
                                        <div class="quiz-result-item">
                                            <div class="quiz-info">
                                                <h4>Quiz del <?php echo esc_html( date( 'd/m/Y', $quiz['date'] ) ); ?></h4>
                                                <p><strong>Razza consigliata:</strong> <?php echo esc_html( $quiz['recommended_breed'] ); ?></p>
                                            </div>
                                            <div class="quiz-actions">
                                                <?php if ( ! empty( $quiz['pdf_url'] ) ) : ?>
                                                    <a href="<?php echo esc_url( $quiz['pdf_url'] ); ?>" class="btn btn-sm btn-outline" download>
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                                        </svg>
                                                        Scarica PDF
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="empty-state">
                                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"></path>
                                    </svg>
                                    <h3>Nessun quiz completato</h3>
                                    <p>Non hai ancora completato nessun quiz. Scopri la razza perfetta per te!</p>
                                    <a href="<?php echo esc_url( home_url( '/quiz-razza' ) ); ?>" class="btn btn-primary">Inizia il Quiz</a>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ( $current_tab === 'preferiti' ) : ?>
                        <!-- PREFERITI TAB -->
                        <div class="dashboard-section">
                            <h2 class="section-title">I Miei Preferiti</h2>

                            <?php
                            $preferiti_razze = get_user_meta( $user_id, 'preferiti_razze', true );
                            $preferiti_strutture = get_user_meta( $user_id, 'preferiti_strutture', true );

                            $has_preferiti = ( ! empty( $preferiti_razze ) && is_array( $preferiti_razze ) ) ||
                                           ( ! empty( $preferiti_strutture ) && is_array( $preferiti_strutture ) );
                            ?>

                            <?php if ( $has_preferiti ) : ?>

                                <?php if ( ! empty( $preferiti_razze ) && is_array( $preferiti_razze ) ) : ?>
                                    <h3 class="subsection-title">Razze Preferite</h3>
                                    <div class="preferiti-grid">
                                        <?php
                                        foreach ( $preferiti_razze as $post_id ) :
                                            $post = get_post( $post_id );
                                            if ( ! $post ) continue;
                                            ?>
                                            <div class="preferito-card">
                                                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                                                    <a href="<?php echo get_permalink( $post_id ); ?>" class="preferito-image">
                                                        <?php echo get_the_post_thumbnail( $post_id, 'medium' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <div class="preferito-content">
                                                    <h4><a href="<?php echo get_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a></h4>
                                                    <a href="#" class="remove-preferito" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-type="razze">Rimuovi</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( ! empty( $preferiti_strutture ) && is_array( $preferiti_strutture ) ) : ?>
                                    <h3 class="subsection-title">Strutture Preferite</h3>
                                    <div class="preferiti-grid">
                                        <?php
                                        foreach ( $preferiti_strutture as $post_id ) :
                                            $post = get_post( $post_id );
                                            if ( ! $post ) continue;
                                            ?>
                                            <div class="preferito-card">
                                                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                                                    <a href="<?php echo get_permalink( $post_id ); ?>" class="preferito-image">
                                                        <?php echo get_the_post_thumbnail( $post_id, 'medium' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <div class="preferito-content">
                                                    <h4><a href="<?php echo get_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a></h4>
                                                    <p class="preferito-type"><?php echo get_post_type_object( get_post_type( $post_id ) )->labels->singular_name; ?></p>
                                                    <a href="#" class="remove-preferito" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-type="strutture">Rimuovi</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php else : ?>
                                <div class="empty-state">
                                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path>
                                    </svg>
                                    <h3>Nessun preferito salvato</h3>
                                    <p>Non hai ancora salvato nessun elemento. Inizia ad esplorare!</p>
                                    <div class="empty-state-actions">
                                        <a href="<?php echo esc_url( get_post_type_archive_link( 'razze_di_cani' ) ); ?>" class="btn btn-outline">Esplora Razze</a>
                                        <a href="<?php echo esc_url( get_post_type_archive_link( 'allevamenti' ) ); ?>" class="btn btn-primary">Esplora Strutture</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </main>

</div>

<?php wp_footer(); ?>
</body>
</html>
