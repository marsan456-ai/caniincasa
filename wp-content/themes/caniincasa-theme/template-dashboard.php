<?php
/**
 * Template Name: Dashboard Utente
 * Template for user dashboard with tabs
 *
 * @package Caniincasa
 */

// Redirect to login if not logged in
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/login?redirect_to=' . urlencode( get_permalink() ) ) );
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
    <style>
    /* Dashboard Responsive - Inline Critical CSS */
    .dashboard-nav-toggle {
        display: none !important;
        width: 100%;
        padding: 1rem 1.25rem;
        background: #FFFFFF;
        border: 2px solid #E0E0E0;
        border-radius: 12px;
        cursor: pointer;
        align-items: center;
        gap: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        color: #2C3E50;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    .toggle-icon {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 22px;
        height: 22px;
    }
    .hamburger-line {
        display: block;
        width: 100%;
        height: 2.5px;
        background: #2C3E50;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    .dashboard-nav-toggle.is-active .hamburger-line:nth-child(1) {
        transform: translateY(7.5px) rotate(45deg);
    }
    .dashboard-nav-toggle.is-active .hamburger-line:nth-child(2) {
        opacity: 0;
    }
    .dashboard-nav-toggle.is-active .hamburger-line:nth-child(3) {
        transform: translateY(-7.5px) rotate(-45deg);
    }
    .toggle-text { display: none; }
    .toggle-current-tab { flex: 1; text-align: left; }
    .toggle-arrow { transition: transform 0.3s ease; }
    .dashboard-nav-toggle.is-active .toggle-arrow { transform: rotate(180deg); }

    /* Mobile: show hamburger, hide nav */
    @media (max-width: 768px) {
        .dashboard-nav-toggle {
            display: flex !important;
            margin-bottom: 0.5rem;
        }
        .dashboard-nav {
            display: none !important;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .dashboard-nav.is-open {
            display: flex !important;
        }
        .dashboard-sidebar {
            position: relative;
        }
        .dashboard-content {
            grid-template-columns: 1fr;
        }
        .dashboard-user-card {
            display: none;
        }
        .dashboard-nav-item {
            width: 100%;
            padding: 1rem 1.25rem;
            border-radius: 10px;
        }
    }
    </style>
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

                    <!-- Mobile Navigation Toggle -->
                    <button class="dashboard-nav-toggle" id="dashboardNavToggle" aria-expanded="false" aria-controls="dashboardNav">
                        <span class="toggle-icon">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </span>
                        <span class="toggle-text">Menu</span>
                        <span class="toggle-current-tab">
                            <?php
                            $tab_labels = array(
                                'profilo'   => 'Profilo',
                                'annunci'   => 'I Miei Annunci',
                                'quiz'      => 'Storico Quiz',
                                'preferiti' => 'Preferiti',
                                'messaggi'  => 'Messaggi',
                            );
                            echo esc_html( $tab_labels[ $current_tab ] ?? 'Menu' );
                            ?>
                        </span>
                        <svg class="toggle-arrow" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>

                    <nav class="dashboard-nav" id="dashboardNav">
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
                        <a href="?tab=messaggi" class="dashboard-nav-item <?php echo $current_tab === 'messaggi' ? 'active' : ''; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                            </svg>
                            Messaggi
                            <?php
                            $unread_count = caniincasa_get_unread_count( $user_id );
                            if ( $unread_count > 0 ) :
                            ?>
                                <span class="messages-badge"><?php echo esc_html( $unread_count ); ?></span>
                            <?php endif; ?>
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
                                                    <a href="<?php echo add_query_arg( 'edit', get_the_ID(), home_url( '/pubblica-annuncio' ) ); ?>" class="btn btn-sm btn-primary">Modifica</a>
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
                                                    <a href="<?php echo add_query_arg( 'edit', get_the_ID(), home_url( '/pubblica-annuncio' ) ); ?>" class="btn btn-sm btn-primary">Modifica</a>
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

                    <?php elseif ( $current_tab === 'messaggi' ) : ?>
                        <!-- MESSAGGI TAB -->
                        <div class="dashboard-section messages-section">
                            <h2 class="section-title">Messaggi</h2>

                            <!-- Messages Tabs -->
                            <div class="messages-tabs">
                                <button class="messages-tab active" data-tab="inbox">
                                    <?php esc_html_e( 'Ricevuti', 'caniincasa' ); ?>
                                    <?php
                                    $inbox_count = caniincasa_get_unread_count( $user_id );
                                    if ( $inbox_count > 0 ) :
                                    ?>
                                        <span class="messages-badge"><?php echo esc_html( $inbox_count ); ?></span>
                                    <?php endif; ?>
                                </button>
                                <button class="messages-tab" data-tab="sent">
                                    <?php esc_html_e( 'Inviati', 'caniincasa' ); ?>
                                </button>
                            </div>

                            <!-- Inbox Messages -->
                            <div id="inbox-messages" class="messages-list">
                                <?php
                                $inbox_messages = caniincasa_get_messages( $user_id, 'inbox' );

                                if ( ! empty( $inbox_messages ) ) :
                                    foreach ( $inbox_messages as $message ) :
                                        $date = date_i18n( 'j M Y, H:i', strtotime( $message['created_at'] ) );
                                        $unread_class = $message['is_read'] ? '' : 'unread';
                                        $is_blocked = caniincasa_is_user_blocked( $user_id, $message['sender_id'] );
                                        $reply_count_text = $message['reply_count'] > 0 ? ' (' . $message['reply_count'] . ' risposte)' : '';
                                ?>
                                    <div class="message-item <?php echo esc_attr( $unread_class ); ?>" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                        <div class="message-icon">
                                            <?php echo esc_html( strtoupper( substr( $message['sender_name'], 0, 1 ) ) ); ?>
                                        </div>
                                        <div class="message-content-preview">
                                            <div class="message-header-row">
                                                <span class="message-from"><?php echo esc_html( $message['sender_name'] ); ?></span>
                                                <span class="message-date"><?php echo esc_html( $date ); ?></span>
                                            </div>
                                            <div class="message-subject">
                                                <?php echo esc_html( $message['subject'] ); ?>
                                                <?php if ( $reply_count_text ) : ?>
                                                    <span class="reply-count"><?php echo esc_html( $reply_count_text ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-preview-text"><?php echo esc_html( wp_trim_words( strip_tags( $message['message'] ), 15 ) ); ?></div>
                                            <div class="message-full-content" style="display: none;">
                                                <div class="message-full-text"><?php echo wp_kses_post( $message['message'] ); ?></div>
                                                <?php if ( $message['reply_count'] > 0 ) : ?>
                                                    <div class="message-replies-container">
                                                        <div class="replies-loading" style="display: none;">
                                                            <span class="spinner"></span> Caricamento risposte...
                                                        </div>
                                                        <div class="message-replies"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-actions">
                                                <button class="message-action-btn view-message-btn" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                                    <?php esc_html_e( 'Visualizza', 'caniincasa' ); ?>
                                                </button>
                                                <?php if ( ! $message['is_read'] ) : ?>
                                                    <button class="message-action-btn mark-read-btn" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                                        <?php esc_html_e( 'Segna come letto', 'caniincasa' ); ?>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="message-action-btn btn-reply-message"
                                                    data-message-id="<?php echo esc_attr( $message['id'] ); ?>"
                                                    data-recipient-id="<?php echo esc_attr( $message['sender_id'] ); ?>"
                                                    data-recipient-name="<?php echo esc_attr( $message['sender_name'] ); ?>"
                                                    data-subject="<?php echo esc_attr( $message['subject'] ); ?>">
                                                    <?php esc_html_e( 'Rispondi', 'caniincasa' ); ?>
                                                </button>
                                                <button class="message-action-btn delete-message-btn" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                                    <?php esc_html_e( 'Elimina', 'caniincasa' ); ?>
                                                </button>
                                                <?php if ( $is_blocked ) : ?>
                                                    <button class="message-action-btn btn-unblock-user btn-secondary" data-user-id="<?php echo esc_attr( $message['sender_id'] ); ?>">
                                                        <?php esc_html_e( 'Sblocca Utente', 'caniincasa' ); ?>
                                                    </button>
                                                <?php else : ?>
                                                    <button class="message-action-btn btn-block-user btn-danger" data-user-id="<?php echo esc_attr( $message['sender_id'] ); ?>">
                                                        <?php esc_html_e( 'Blocca Utente', 'caniincasa' ); ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    endforeach;
                                else :
                                ?>
                                    <p class="no-messages"><?php esc_html_e( 'Nessun messaggio ricevuto.', 'caniincasa' ); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Sent Messages -->
                            <div id="sent-messages" class="messages-list" style="display: none;">
                                <?php
                                $sent_messages = caniincasa_get_messages( $user_id, 'sent' );

                                if ( ! empty( $sent_messages ) ) :
                                    foreach ( $sent_messages as $message ) :
                                        $date = date_i18n( 'j M Y, H:i', strtotime( $message['created_at'] ) );
                                        $reply_count_text = $message['reply_count'] > 0 ? ' (' . $message['reply_count'] . ' risposte)' : '';
                                ?>
                                    <div class="message-item" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                        <div class="message-icon">
                                            <?php echo esc_html( strtoupper( substr( $message['recipient_name'], 0, 1 ) ) ); ?>
                                        </div>
                                        <div class="message-content-preview">
                                            <div class="message-header-row">
                                                <span class="message-from">A: <?php echo esc_html( $message['recipient_name'] ); ?></span>
                                                <span class="message-date"><?php echo esc_html( $date ); ?></span>
                                            </div>
                                            <div class="message-subject">
                                                <?php echo esc_html( $message['subject'] ); ?>
                                                <?php if ( $reply_count_text ) : ?>
                                                    <span class="reply-count"><?php echo esc_html( $reply_count_text ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-preview-text"><?php echo esc_html( wp_trim_words( strip_tags( $message['message'] ), 15 ) ); ?></div>
                                            <div class="message-full-content" style="display: none;">
                                                <div class="message-full-text"><?php echo wp_kses_post( $message['message'] ); ?></div>
                                                <?php if ( $message['reply_count'] > 0 ) : ?>
                                                    <div class="message-replies-container">
                                                        <div class="replies-loading" style="display: none;">
                                                            <span class="spinner"></span> Caricamento risposte...
                                                        </div>
                                                        <div class="message-replies"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-actions">
                                                <button class="message-action-btn view-message-btn" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                                    <?php esc_html_e( 'Visualizza', 'caniincasa' ); ?>
                                                </button>
                                                <button class="message-action-btn delete-message-btn" data-message-id="<?php echo esc_attr( $message['id'] ); ?>">
                                                    <?php esc_html_e( 'Elimina', 'caniincasa' ); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    endforeach;
                                else :
                                ?>
                                    <p class="no-messages"><?php esc_html_e( 'Nessun messaggio inviato.', 'caniincasa' ); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Tab Switching Script -->
                            <script>
                            jQuery(document).ready(function($) {
                                $('.messages-tab').on('click', function() {
                                    var tab = $(this).data('tab');

                                    $('.messages-tab').removeClass('active');
                                    $(this).addClass('active');

                                    if (tab === 'inbox') {
                                        $('#inbox-messages').show();
                                        $('#sent-messages').hide();
                                    } else {
                                        $('#inbox-messages').hide();
                                        $('#sent-messages').show();
                                    }
                                });
                            });
                            </script>
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </main>

<!-- Dashboard Navigation Toggle Script -->
<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('dashboardNavToggle');
        var nav = document.getElementById('dashboardNav');

        if (!toggle || !nav) return;

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            toggle.setAttribute('aria-expanded', !isExpanded);
            nav.classList.toggle('is-open');
            toggle.classList.toggle('is-active');

            // Animate hamburger
            if (!isExpanded) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking on a nav item (mobile)
        var navItems = nav.querySelectorAll('.dashboard-nav-item');
        navItems.forEach(function(item) {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    nav.classList.remove('is-open');
                    toggle.classList.remove('is-active');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close menu on resize if opened
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && nav.classList.contains('is-open')) {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && nav.classList.contains('is-open')) {
                if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                    nav.classList.remove('is-open');
                    toggle.classList.remove('is-active');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
