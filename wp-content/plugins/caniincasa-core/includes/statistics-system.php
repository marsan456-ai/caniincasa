<?php
/**
 * Simple Statistics System
 * Track page visits and display analytics in WordPress admin
 *
 * @package Caniincasa_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create statistics table
 */
function caniincasa_create_stats_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        page_url varchar(500) NOT NULL,
        page_title varchar(255) DEFAULT NULL,
        page_type varchar(50) DEFAULT NULL,
        post_id bigint(20) unsigned DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        referer varchar(500) DEFAULT NULL,
        visited_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY page_url (page_url(191)),
        KEY page_type (page_type),
        KEY post_id (post_id),
        KEY visited_at (visited_at)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( CANIINCASA_CORE_FILE, 'caniincasa_create_stats_table' );

/**
 * Ensure stats table exists
 */
function caniincasa_ensure_stats_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        caniincasa_create_stats_table();
    }
}
add_action( 'init', 'caniincasa_ensure_stats_table', 5 );

/**
 * Track page visit
 */
function caniincasa_track_visit() {
    // Don't track if:
    // - User is admin
    // - Is admin area
    // - Is AJAX request
    // - Is cron
    // - Is REST API
    if ( current_user_can( 'manage_options' ) ||
         is_admin() ||
         wp_doing_ajax() ||
         wp_doing_cron() ||
         defined( 'REST_REQUEST' ) ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';

    // Get page info
    $page_url = esc_url_raw( $_SERVER['REQUEST_URI'] ?? '' );
    $page_title = wp_get_document_title();
    $page_type = 'unknown';
    $post_id = null;

    if ( is_front_page() ) {
        $page_type = 'homepage';
    } elseif ( is_single() ) {
        $page_type = get_post_type();
        $post_id = get_the_ID();
    } elseif ( is_page() ) {
        $page_type = 'page';
        $post_id = get_the_ID();
    } elseif ( is_archive() ) {
        $page_type = 'archive';
        if ( is_post_type_archive() ) {
            $page_type = 'archive_' . get_post_type();
        }
    } elseif ( is_search() ) {
        $page_type = 'search';
    } elseif ( is_404() ) {
        $page_type = '404';
    }

    // Get visitor info
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    // Anonymize IP (GDPR compliance)
    if ( $ip_address ) {
        $ip_parts = explode( '.', $ip_address );
        if ( count( $ip_parts ) === 4 ) {
            $ip_address = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.0';
        }
    }

    // Insert into database
    $wpdb->insert(
        $table_name,
        array(
            'page_url'    => $page_url,
            'page_title'  => $page_title,
            'page_type'   => $page_type,
            'post_id'     => $post_id,
            'ip_address'  => $ip_address,
            'user_agent'  => substr( $user_agent, 0, 500 ),
            'referer'     => substr( $referer, 0, 500 ),
            'visited_at'  => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
    );
}
add_action( 'wp', 'caniincasa_track_visit' );

/**
 * Add statistics admin menu
 */
function caniincasa_add_stats_menu() {
    add_menu_page(
        'Statistiche Visite',
        'Statistiche',
        'manage_options',
        'caniincasa-stats',
        'caniincasa_render_stats_page',
        'dashicons-chart-line',
        27
    );
}
add_action( 'admin_menu', 'caniincasa_add_stats_menu' );

/**
 * Render statistics page
 */
function caniincasa_render_stats_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';

    // Get time period filter
    $period = isset( $_GET['period'] ) ? sanitize_text_field( $_GET['period'] ) : '7days';

    $date_condition = '';
    switch ( $period ) {
        case '24hours':
            $date_condition = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            break;
        case '7days':
            $date_condition = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case '30days':
            $date_condition = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'all':
        default:
            $date_condition = '';
            break;
    }

    // Get total visits
    $total_visits = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $date_condition" );

    // Get unique visitors (by IP)
    $unique_visitors = $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM $table_name $date_condition" );

    // Get visits today
    $visits_today = $wpdb->get_var(
        "SELECT COUNT(*) FROM $table_name
        WHERE DATE(visited_at) = CURDATE()"
    );

    // Get visits by page type
    $visits_by_type = $wpdb->get_results(
        "SELECT page_type, COUNT(*) as count
        FROM $table_name
        $date_condition
        GROUP BY page_type
        ORDER BY count DESC
        LIMIT 10"
    );

    // Get top pages
    $top_pages = $wpdb->get_results(
        "SELECT page_url, page_title, page_type, COUNT(*) as count
        FROM $table_name
        $date_condition
        GROUP BY page_url, page_title, page_type
        ORDER BY count DESC
        LIMIT 20"
    );

    // Get visits by day (last 30 days)
    $visits_by_day = $wpdb->get_results(
        "SELECT DATE(visited_at) as date, COUNT(*) as count
        FROM $table_name
        WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(visited_at)
        ORDER BY date ASC"
    );

    // Get top referrers
    $top_referrers = $wpdb->get_results(
        "SELECT referer, COUNT(*) as count
        FROM $table_name
        $date_condition
        AND referer != ''
        AND referer NOT LIKE '%caniincasa.it%'
        GROUP BY referer
        ORDER BY count DESC
        LIMIT 10"
    );

    ?>
    <div class="wrap">
        <h1>Statistiche Visite</h1>
        <p class="description">Visite al sito (escludendo amministratori). IP anonimizzati per GDPR.</p>

        <style>
            .stats-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .stats-card {
                background: white;
                padding: 25px;
                border-left: 4px solid #2271b1;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .stats-card h3 {
                margin: 0 0 10px;
                font-size: 14px;
                color: #646970;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .stats-card .number {
                font-size: 36px;
                font-weight: 600;
                color: #1d2327;
                margin: 0;
            }
            .stats-card.green { border-left-color: #00a32a; }
            .stats-card.orange { border-left-color: #f97316; }
            .stats-card.blue { border-left-color: #2271b1; }
            .stats-filters {
                background: white;
                padding: 15px;
                margin: 20px 0;
                border: 1px solid #c3c4c7;
            }
            .stats-table {
                width: 100%;
                background: white;
                border-collapse: collapse;
                margin: 20px 0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .stats-table th {
                background: #f8f9fa;
                padding: 12px;
                text-align: left;
                font-weight: 600;
                border-bottom: 2px solid #dee2e6;
            }
            .stats-table td {
                padding: 12px;
                border-bottom: 1px solid #dee2e6;
            }
            .stats-table tr:hover {
                background: #f8f9fa;
            }
            .chart-container {
                background: white;
                padding: 20px;
                margin: 20px 0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .progress-bar {
                background: #e9ecef;
                height: 20px;
                border-radius: 4px;
                overflow: hidden;
            }
            .progress-fill {
                background: linear-gradient(90deg, #2271b1, #135e96);
                height: 100%;
                display: flex;
                align-items: center;
                padding: 0 10px;
                color: white;
                font-size: 11px;
                font-weight: 600;
            }
        </style>

        <!-- Filters -->
        <div class="stats-filters">
            <form method="get">
                <input type="hidden" name="page" value="caniincasa-stats">
                <label for="period" style="font-weight: 600; margin-right: 10px;">Periodo:</label>
                <select name="period" id="period" onchange="this.form.submit()">
                    <option value="24hours" <?php selected( $period, '24hours' ); ?>>Ultime 24 ore</option>
                    <option value="7days" <?php selected( $period, '7days' ); ?>>Ultimi 7 giorni</option>
                    <option value="30days" <?php selected( $period, '30days' ); ?>>Ultimi 30 giorni</option>
                    <option value="all" <?php selected( $period, 'all' ); ?>>Tutto il periodo</option>
                </select>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="stats-cards">
            <div class="stats-card blue">
                <h3>Visite Totali</h3>
                <p class="number"><?php echo number_format_i18n( $total_visits ); ?></p>
            </div>
            <div class="stats-card green">
                <h3>Visitatori Unici</h3>
                <p class="number"><?php echo number_format_i18n( $unique_visitors ); ?></p>
            </div>
            <div class="stats-card orange">
                <h3>Visite Oggi</h3>
                <p class="number"><?php echo number_format_i18n( $visits_today ); ?></p>
            </div>
        </div>

        <!-- Visits Chart by Day -->
        <?php if ( ! empty( $visits_by_day ) ) : ?>
        <div class="chart-container">
            <h2>Visite per Giorno (Ultimi 30 giorni)</h2>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Visite</th>
                        <th style="width: 50%;">Grafico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $max_visits = max( array_column( $visits_by_day, 'count' ) );
                    foreach ( $visits_by_day as $day ) :
                        $percentage = $max_visits > 0 ? ( $day->count / $max_visits ) * 100 : 0;
                    ?>
                        <tr>
                            <td><?php echo esc_html( mysql2date( 'd/m/Y', $day->date ) ); ?></td>
                            <td><strong><?php echo number_format_i18n( $day->count ); ?></strong></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;">
                                        <?php echo number_format_i18n( $day->count ); ?> visite
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Top Pages -->
        <div class="chart-container">
            <h2>Pagine Più Visitate</h2>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Pagina</th>
                        <th>Tipo</th>
                        <th style="width: 10%;">Visite</th>
                        <th style="width: 30%;">Popolarità</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( ! empty( $top_pages ) ) :
                        $max_count = $top_pages[0]->count;
                        $position = 1;
                        foreach ( $top_pages as $page ) :
                            $percentage = $max_count > 0 ? ( $page->count / $max_count ) * 100 : 0;
                        ?>
                            <tr>
                                <td><strong><?php echo $position++; ?></strong></td>
                                <td>
                                    <strong><?php echo esc_html( $page->page_title ?: 'Senza titolo' ); ?></strong><br>
                                    <small style="color: #646970;"><?php echo esc_html( $page->page_url ); ?></small>
                                </td>
                                <td>
                                    <span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <?php echo esc_html( $page->page_type ); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo number_format_i18n( $page->count ); ?></strong></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;">
                                            <?php echo number_format( $percentage, 1 ); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                Nessun dato disponibile per il periodo selezionato.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Visits by Page Type -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="chart-container">
                <h2>Visite per Tipo Pagina</h2>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Visite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $visits_by_type ) ) : ?>
                            <?php foreach ( $visits_by_type as $type ) : ?>
                                <tr>
                                    <td>
                                        <span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <?php echo esc_html( $type->page_type ); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo number_format_i18n( $type->count ); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2" style="text-align: center; padding: 20px;">Nessun dato</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Referrers -->
            <div class="chart-container">
                <h2>Principali Sorgenti</h2>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Sorgente</th>
                            <th>Visite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $top_referrers ) ) : ?>
                            <?php foreach ( $top_referrers as $referrer ) :
                                $domain = parse_url( $referrer->referer, PHP_URL_HOST );
                            ?>
                                <tr>
                                    <td>
                                        <small style="color: #646970;"><?php echo esc_html( $domain ?: $referrer->referer ); ?></small>
                                    </td>
                                    <td><strong><?php echo number_format_i18n( $referrer->count ); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2" style="text-align: center; padding: 20px;">
                                    Nessuna sorgente esterna rilevata
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Data Management -->
        <div class="chart-container" style="margin-top: 40px; border-top: 2px solid #dee2e6; padding-top: 20px;">
            <h3>Gestione Dati</h3>
            <p>
                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=caniincasa-stats&action=clear_stats' ), 'clear_stats' ); ?>"
                   class="button button-secondary"
                   onclick="return confirm('Sei sicuro di voler eliminare tutte le statistiche? Questa azione non può essere annullata.');">
                    Cancella Tutte le Statistiche
                </a>
            </p>
            <p class="description">
                Le statistiche vengono salvate localmente nel database.
                IP anonimizzati per conformità GDPR (ultimi 2 ottetti rimossi).
            </p>
        </div>
    </div>
    <?php
}

/**
 * Handle clear statistics action
 */
function caniincasa_handle_clear_stats() {
    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'clear_stats' ) {
        return;
    }

    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'caniincasa-stats' ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    check_admin_referer( 'clear_stats' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';
    $wpdb->query( "TRUNCATE TABLE $table_name" );

    wp_redirect( admin_url( 'admin.php?page=caniincasa-stats' ) );
    exit;
}
add_action( 'admin_init', 'caniincasa_handle_clear_stats' );
