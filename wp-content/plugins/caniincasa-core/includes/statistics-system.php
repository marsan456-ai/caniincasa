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

    // Get time period filter - whitelist valid values
    $period = isset( $_GET['period'] ) ? sanitize_key( $_GET['period'] ) : '7days';
    $valid_periods = array( '24hours', '7days', '30days', 'all' );
    if ( ! in_array( $period, $valid_periods, true ) ) {
        $period = '7days';
    }

    // Build date filter using prepared statements
    $date_interval = '';
    switch ( $period ) {
        case '24hours':
            $date_interval = '24 HOUR';
            break;
        case '7days':
            $date_interval = '7 DAY';
            break;
        case '30days':
            $date_interval = '30 DAY';
            break;
        case 'all':
        default:
            $date_interval = '';
            break;
    }

    // Get total visits (with prepared statement for consistency)
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $total_visits = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table_name} WHERE visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})"
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $unique_visitors = $wpdb->get_var(
            "SELECT COUNT(DISTINCT ip_address) FROM {$table_name} WHERE visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})"
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total_visits = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $unique_visitors = $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM {$table_name}" );
    }

    // Get visits today
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $visits_today = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$table_name} WHERE DATE(visited_at) = CURDATE()"
    );

    // Get visits by page type
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $visits_by_type = $wpdb->get_results(
            "SELECT page_type, COUNT(*) as count
            FROM {$table_name}
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})
            GROUP BY page_type
            ORDER BY count DESC
            LIMIT 10"
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $visits_by_type = $wpdb->get_results(
            "SELECT page_type, COUNT(*) as count
            FROM {$table_name}
            GROUP BY page_type
            ORDER BY count DESC
            LIMIT 10"
        );
    }

    // Get top pages
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $top_pages = $wpdb->get_results(
            "SELECT page_url, page_title, page_type, COUNT(*) as count
            FROM {$table_name}
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})
            GROUP BY page_url, page_title, page_type
            ORDER BY count DESC
            LIMIT 20"
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $top_pages = $wpdb->get_results(
            "SELECT page_url, page_title, page_type, COUNT(*) as count
            FROM {$table_name}
            GROUP BY page_url, page_title, page_type
            ORDER BY count DESC
            LIMIT 20"
        );
    }

    // Get visits by day (last 30 days) - fixed to use index-friendly comparison
    $thirty_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $visits_by_day = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DATE(visited_at) as date, COUNT(*) as count
            FROM {$table_name}
            WHERE visited_at >= %s
            GROUP BY DATE(visited_at)
            ORDER BY date ASC",
            $thirty_days_ago
        )
    );

    // Get top referrers (external sources only)
    $site_like = '%' . $wpdb->esc_like( wp_parse_url( home_url(), PHP_URL_HOST ) ) . '%';
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $top_referrers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT referer, COUNT(*) as count
                FROM {$table_name}
                WHERE visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})
                AND referer != ''
                AND referer NOT LIKE %s
                GROUP BY referer
                ORDER BY count DESC
                LIMIT 10",
                $site_like
            )
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $top_referrers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT referer, COUNT(*) as count
                FROM {$table_name}
                WHERE referer != ''
                AND referer NOT LIKE %s
                GROUP BY referer
                ORDER BY count DESC
                LIMIT 10",
                $site_like
            )
        );
    }

    // Get 404 errors with details (last 50, grouped by URL)
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $errors_404 = $wpdb->get_results(
            "SELECT page_url, referer, COUNT(*) as count, MAX(visited_at) as last_seen
            FROM {$table_name}
            WHERE page_type = '404'
            AND visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})
            GROUP BY page_url, referer
            ORDER BY count DESC, last_seen DESC
            LIMIT 50"
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $errors_404 = $wpdb->get_results(
            "SELECT page_url, referer, COUNT(*) as count, MAX(visited_at) as last_seen
            FROM {$table_name}
            WHERE page_type = '404'
            GROUP BY page_url, referer
            ORDER BY count DESC, last_seen DESC
            LIMIT 50"
        );
    }

    // Count total 404s
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $total_404 = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$table_name} WHERE page_type = '404'"
    );

    // Get 404s grouped by directory/folder (first path segment)
    if ( $date_interval ) {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_interval is whitelisted
        $errors_404_by_folder = $wpdb->get_results(
            "SELECT
                SUBSTRING_INDEX(SUBSTRING_INDEX(page_url, '/', 2), '/', -1) as folder,
                COUNT(*) as count,
                COUNT(DISTINCT page_url) as unique_urls
            FROM {$table_name}
            WHERE page_type = '404'
            AND visited_at >= DATE_SUB(NOW(), INTERVAL {$date_interval})
            GROUP BY folder
            ORDER BY count DESC
            LIMIT 15"
        );
    } else {
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $errors_404_by_folder = $wpdb->get_results(
            "SELECT
                SUBSTRING_INDEX(SUBSTRING_INDEX(page_url, '/', 2), '/', -1) as folder,
                COUNT(*) as count,
                COUNT(DISTINCT page_url) as unique_urls
            FROM {$table_name}
            WHERE page_type = '404'
            GROUP BY folder
            ORDER BY count DESC
            LIMIT 15"
        );
    }

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
            <div class="stats-card" style="border-left-color: #dc3545;">
                <h3>Errori 404</h3>
                <p class="number"><?php echo number_format_i18n( $total_404 ); ?></p>
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
                            <td><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $day->date ) ) ); ?></td>
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

        <!-- 404 Errors by Folder -->
        <?php if ( ! empty( $errors_404_by_folder ) ) : ?>
        <div class="chart-container" style="border-left: 4px solid #dc3545;">
            <h2 style="color: #dc3545;">Errori 404 per Cartella</h2>
            <p class="description">Raggruppamento per percorso (es. /allevamenti, /razze, /wp-admin). Utile per identificare pattern di attacchi o sezioni problematiche.</p>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Cartella/Percorso</th>
                        <th style="width: 20%;">Totale Hits</th>
                        <th style="width: 20%;">URL Unici</th>
                        <th style="width: 20%;">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Common attack paths to highlight
                    $suspicious_folders = array( 'wp-admin', 'wp-includes', 'wp-content', 'xmlrpc.php', 'wp-login.php', '.env', 'config', 'admin', 'phpmyadmin', '.git' );
                    foreach ( $errors_404_by_folder as $folder_item ) :
                        $folder_name = $folder_item->folder ?: '(root)';
                        $is_suspicious = in_array( strtolower( $folder_name ), $suspicious_folders, true );
                    ?>
                        <tr style="<?php echo $is_suspicious ? 'background: #fff3cd;' : ''; ?>">
                            <td>
                                <code style="background: <?php echo $is_suspicious ? '#ffc107' : '#e9ecef'; ?>; padding: 4px 8px; border-radius: 3px;">
                                    /<?php echo esc_html( $folder_name ); ?>
                                </code>
                            </td>
                            <td><strong style="color: #dc3545;"><?php echo number_format_i18n( $folder_item->count ); ?></strong></td>
                            <td><?php echo number_format_i18n( $folder_item->unique_urls ); ?></td>
                            <td>
                                <?php if ( $is_suspicious ) : ?>
                                    <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">Sospetto</span>
                                <?php else : ?>
                                    <span style="background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">Normale</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 404 Errors Details -->
        <?php if ( ! empty( $errors_404 ) ) : ?>
        <div class="chart-container" style="border-left: 4px solid #dc3545;">
            <h2 style="color: #dc3545;">Dettaglio Errori 404</h2>
            <p class="description">URL specifici che generano errori 404. Utile per identificare link rotti o tentativi di accesso a risorse inesistenti.</p>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">URL Richiesto</th>
                        <th style="width: 30%;">Provenienza (Referer)</th>
                        <th style="width: 10%;">Hits</th>
                        <th style="width: 20%;">Ultimo Accesso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $errors_404 as $error ) : ?>
                        <tr>
                            <td>
                                <code style="background: #fee; padding: 2px 6px; border-radius: 3px; font-size: 12px; word-break: break-all;">
                                    <?php echo esc_html( $error->page_url ); ?>
                                </code>
                            </td>
                            <td>
                                <?php if ( $error->referer ) : ?>
                                    <small style="color: #646970; word-break: break-all;">
                                        <?php echo esc_html( wp_parse_url( $error->referer, PHP_URL_HOST ) ?: $error->referer ); ?>
                                    </small>
                                <?php else : ?>
                                    <small style="color: #999;">Accesso diretto</small>
                                <?php endif; ?>
                            </td>
                            <td><strong style="color: #dc3545;"><?php echo number_format_i18n( $error->count ); ?></strong></td>
                            <td><small><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $error->last_seen ) ) ); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top: 15px;">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=caniincasa-stats&action=clear_404' ), 'clear_404' ) ); ?>"
                   class="button button-secondary"
                   onclick="return confirm('Eliminare tutti i log 404? I dati verranno persi.');">
                    Cancella Solo Errori 404
                </a>
            </p>
        </div>
        <?php endif; ?>

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
            <p class="description" style="margin-top: 10px;">
                <strong>Pulizia automatica attiva:</strong> I dati più vecchi di 90 giorni vengono eliminati quotidianamente.
                I record 404 sono limitati a max 5.000 per evitare crescita eccessiva del DB.
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

/**
 * Handle clear 404 errors action
 */
function caniincasa_handle_clear_404() {
    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'clear_404' ) {
        return;
    }

    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'caniincasa-stats' ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    check_admin_referer( 'clear_404' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE page_type = %s",
            '404'
        )
    );

    wp_redirect( admin_url( 'admin.php?page=caniincasa-stats' ) );
    exit;
}
add_action( 'admin_init', 'caniincasa_handle_clear_404' );

/**
 * Schedule automatic cleanup of old statistics
 * Keeps DB size manageable by removing data older than 90 days
 */
function caniincasa_schedule_stats_cleanup() {
    if ( ! wp_next_scheduled( 'caniincasa_stats_cleanup_cron' ) ) {
        wp_schedule_event( time(), 'daily', 'caniincasa_stats_cleanup_cron' );
    }
}
add_action( 'init', 'caniincasa_schedule_stats_cleanup' );

/**
 * Perform automatic cleanup
 * - Delete stats older than 90 days
 * - Keep max 10,000 404 records (delete oldest if exceeded)
 */
function caniincasa_stats_cleanup() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caniincasa_stats';

    // Delete records older than 90 days
    $ninety_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) );
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE visited_at < %s",
            $ninety_days_ago
        )
    );

    // Limit 404 records to max 5000 (keep most recent)
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $count_404 = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE page_type = %s",
            '404'
        )
    );

    if ( $count_404 > 5000 ) {
        $to_delete = $count_404 - 5000;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name}
                WHERE page_type = %s
                ORDER BY visited_at ASC
                LIMIT %d",
                '404',
                $to_delete
            )
        );
    }

    // Optimize table after cleanup
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query( "OPTIMIZE TABLE {$table_name}" );
}
add_action( 'caniincasa_stats_cleanup_cron', 'caniincasa_stats_cleanup' );

/**
 * Unschedule cleanup on plugin deactivation
 */
function caniincasa_unschedule_stats_cleanup() {
    $timestamp = wp_next_scheduled( 'caniincasa_stats_cleanup_cron' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'caniincasa_stats_cleanup_cron' );
    }
}
register_deactivation_hook( CANIINCASA_CORE_FILE, 'caniincasa_unschedule_stats_cleanup' );
