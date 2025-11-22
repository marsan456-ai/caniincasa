<?php
/**
 * Newsletter System - GDPR Compliant
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter System Class
 */
class Caniincasa_Newsletter {

	/**
	 * Table name
	 */
	private $table_name;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'caniincasa_newsletter';

		// Hooks
		add_action( 'init', array( $this, 'create_table' ) );
		add_action( 'wp_ajax_subscribe_newsletter', array( $this, 'subscribe_ajax' ) );
		add_action( 'wp_ajax_nopriv_subscribe_newsletter', array( $this, 'subscribe_ajax' ) );
		add_action( 'wp_ajax_unsubscribe_newsletter', array( $this, 'unsubscribe_ajax' ) );
		add_action( 'wp_ajax_nopriv_unsubscribe_newsletter', array( $this, 'unsubscribe_ajax' ) );

		// Admin hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_post_export_newsletter_csv', array( $this, 'export_csv' ) );
		add_action( 'admin_post_delete_newsletter_subscriber', array( $this, 'delete_subscriber' ) );
	}

	/**
	 * Create newsletter table
	 */
	public function create_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			name varchar(255) DEFAULT NULL,
			status varchar(20) DEFAULT 'active',
			gdpr_consent tinyint(1) DEFAULT 1,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			source varchar(50) DEFAULT 'form',
			subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
			unsubscribed_at datetime DEFAULT NULL,
			confirm_token varchar(64) DEFAULT NULL,
			confirmed tinyint(1) DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			KEY status (status),
			KEY subscribed_at (subscribed_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Subscribe to newsletter (AJAX)
	 */
	public function subscribe_ajax() {
		// Check nonce
		check_ajax_referer( 'caniincasa_nonce', 'nonce' );

		// Get and sanitize data
		$email = sanitize_email( $_POST['email'] ?? '' );
		$name  = sanitize_text_field( $_POST['name'] ?? '' );
		$gdpr  = isset( $_POST['gdpr_consent'] ) ? 1 : 0;
		$source = sanitize_text_field( $_POST['source'] ?? 'form' );

		// Validate
		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Inserisci un indirizzo email valido.', 'caniincasa-core' ),
			) );
		}

		if ( ! $gdpr ) {
			wp_send_json_error( array(
				'message' => __( 'Devi accettare la privacy policy per iscriverti alla newsletter.', 'caniincasa-core' ),
			) );
		}

		// Subscribe
		$result = $this->subscribe( $email, $name, $gdpr, $source );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => $result->get_error_message(),
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Iscrizione completata con successo! Controlla la tua email per confermare.', 'caniincasa-core' ),
		) );
	}

	/**
	 * Subscribe to newsletter
	 */
	public function subscribe( $email, $name = '', $gdpr_consent = 1, $source = 'form' ) {
		global $wpdb;

		// Check if already subscribed
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE email = %s",
			$email
		) );

		if ( $existing ) {
			if ( $existing->status === 'active' ) {
				return new WP_Error( 'already_subscribed', __( 'Questo indirizzo email è già iscritto alla newsletter.', 'caniincasa-core' ) );
			}

			// Reactivate subscription
			$wpdb->update(
				$this->table_name,
				array(
					'status'          => 'active',
					'gdpr_consent'    => $gdpr_consent,
					'subscribed_at'   => current_time( 'mysql' ),
					'unsubscribed_at' => null,
					'name'            => $name,
				),
				array( 'id' => $existing->id ),
				array( '%s', '%d', '%s', '%s', '%s' ),
				array( '%d' )
			);

			return $existing->id;
		}

		// Create new subscription
		$token = bin2hex( random_bytes( 32 ) );

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'email'        => $email,
				'name'         => $name,
				'status'       => 'active',
				'gdpr_consent' => $gdpr_consent,
				'ip_address'   => $this->get_client_ip(),
				'user_agent'   => substr( $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255 ),
				'source'       => $source,
				'confirm_token'=> $token,
				'confirmed'    => 0,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d' )
		);

		if ( ! $result ) {
			return new WP_Error( 'insert_failed', __( 'Errore durante l\'iscrizione. Riprova.', 'caniincasa-core' ) );
		}

		$subscriber_id = $wpdb->insert_id;

		// Send confirmation email
		$this->send_confirmation_email( $email, $name, $token );

		return $subscriber_id;
	}

	/**
	 * Send confirmation email
	 */
	private function send_confirmation_email( $email, $name, $token ) {
		$confirm_url = add_query_arg(
			array(
				'action' => 'confirm_newsletter',
				'token'  => $token,
			),
			home_url()
		);

		$subject = sprintf( __( 'Conferma iscrizione alla newsletter di %s', 'caniincasa-core' ), get_bloginfo( 'name' ) );

		$message = sprintf(
			__( "Ciao %s,\n\nGrazie per esserti iscritto alla newsletter di %s!\n\nPer completare l'iscrizione, clicca sul seguente link:\n\n%s\n\nSe non hai richiesto questa iscrizione, ignora questa email.\n\nGrazie,\nIl team di %s", 'caniincasa-core' ),
			$name ?: __( 'amico', 'caniincasa-core' ),
			get_bloginfo( 'name' ),
			$confirm_url,
			get_bloginfo( 'name' )
		);

		wp_mail( $email, $subject, $message );
	}

	/**
	 * Unsubscribe from newsletter (AJAX)
	 */
	public function unsubscribe_ajax() {
		check_ajax_referer( 'caniincasa_nonce', 'nonce' );

		$email = sanitize_email( $_POST['email'] ?? '' );

		if ( empty( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Email non valida.', 'caniincasa-core' ),
			) );
		}

		$result = $this->unsubscribe( $email );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => $result->get_error_message(),
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Disiscrizione completata con successo.', 'caniincasa-core' ),
		) );
	}

	/**
	 * Unsubscribe from newsletter
	 */
	public function unsubscribe( $email ) {
		global $wpdb;

		$result = $wpdb->update(
			$this->table_name,
			array(
				'status'          => 'unsubscribed',
				'unsubscribed_at' => current_time( 'mysql' ),
			),
			array( 'email' => $email ),
			array( '%s', '%s' ),
			array( '%s' )
		);

		if ( ! $result ) {
			return new WP_Error( 'not_found', __( 'Email non trovata.', 'caniincasa-core' ) );
		}

		return true;
	}

	/**
	 * Get client IP address
	 *
	 * Note: Only uses REMOTE_ADDR as it's the only reliable server-set variable.
	 * HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR are user-controlled headers that can be spoofed.
	 * If behind a trusted proxy (CloudFlare, load balancer), configure WordPress properly.
	 */
	private function get_client_ip() {
		// Only trust REMOTE_ADDR - it's set by the server, not HTTP headers
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		// Validate IP format (supports both IPv4 and IPv6)
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$ip = '';
		}

		// Optionally anonymize for GDPR (zero last octet for IPv4)
		if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$ip_parts = explode( '.', $ip );
			if ( count( $ip_parts ) === 4 ) {
				$ip = $ip_parts[0] . '.' . $ip_parts[1] . '.' . $ip_parts[2] . '.0';
			}
		} elseif ( $ip && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			// For IPv6, anonymize by removing last 64 bits
			$ip = inet_ntop( substr( inet_pton( $ip ), 0, 8 ) . str_repeat( "\0", 8 ) );
		}

		return $ip;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Newsletter', 'caniincasa-core' ),
			__( 'Newsletter', 'caniincasa-core' ),
			'manage_options',
			'caniincasa-newsletter',
			array( $this, 'admin_page' ),
			'dashicons-email-alt',
			30
		);
	}

	/**
	 * Admin page
	 */
	public function admin_page() {
		global $wpdb;

		// Get stats
		$total_subscribers = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'active'" );
		$total_unsubscribed = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'unsubscribed'" );
		$total_pending = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE confirmed = 0" );

		// Get subscribers
		$per_page = 50;
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$offset = ( $page - 1 ) * $per_page;

		$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'active';

		$subscribers = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE status = %s ORDER BY subscribed_at DESC LIMIT %d OFFSET %d",
			$status,
			$per_page,
			$offset
		) );

		$total_items = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
			$status
		) );

		$total_pages = ceil( $total_items / $per_page );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Gestione Newsletter', 'caniincasa-core' ); ?></h1>

			<!-- Stats -->
			<div class="newsletter-stats" style="display: flex; gap: 20px; margin: 20px 0;">
				<div class="stat-box" style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1;">
					<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Iscritti Attivi', 'caniincasa-core' ); ?></h3>
					<p style="margin: 0; font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo number_format_i18n( $total_subscribers ); ?></p>
				</div>
				<div class="stat-box" style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1;">
					<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'In Attesa Conferma', 'caniincasa-core' ); ?></h3>
					<p style="margin: 0; font-size: 32px; font-weight: bold; color: #dba617;"><?php echo number_format_i18n( $total_pending ); ?></p>
				</div>
				<div class="stat-box" style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1;">
					<h3 style="margin: 0 0 10px; font-size: 14px; color: #666;"><?php esc_html_e( 'Disiscritti', 'caniincasa-core' ); ?></h3>
					<p style="margin: 0; font-size: 32px; font-weight: bold; color: #d63638;"><?php echo number_format_i18n( $total_unsubscribed ); ?></p>
				</div>
			</div>

			<!-- Actions -->
			<div class="tablenav top">
				<div class="alignleft actions">
					<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display: inline-block;">
						<input type="hidden" name="action" value="export_newsletter_csv">
						<?php wp_nonce_field( 'export_newsletter_csv' ); ?>
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Esporta CSV', 'caniincasa-core' ); ?>
						</button>
					</form>
				</div>

				<!-- Status Filter -->
				<div class="alignleft actions">
					<select name="status" id="status-filter" onchange="window.location.href='?page=caniincasa-newsletter&status='+this.value">
						<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Attivi', 'caniincasa-core' ); ?></option>
						<option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php esc_html_e( 'Disiscritti', 'caniincasa-core' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Subscribers Table -->
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email', 'caniincasa-core' ); ?></th>
						<th><?php esc_html_e( 'Nome', 'caniincasa-core' ); ?></th>
						<th><?php esc_html_e( 'Fonte', 'caniincasa-core' ); ?></th>
						<th><?php esc_html_e( 'Data Iscrizione', 'caniincasa-core' ); ?></th>
						<th><?php esc_html_e( 'Confermato', 'caniincasa-core' ); ?></th>
						<th><?php esc_html_e( 'Azioni', 'caniincasa-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $subscribers ) ) : ?>
						<tr>
							<td colspan="6" style="text-align: center; padding: 40px;">
								<?php esc_html_e( 'Nessun iscritto trovato.', 'caniincasa-core' ); ?>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $subscribers as $subscriber ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $subscriber->email ); ?></strong></td>
								<td><?php echo esc_html( $subscriber->name ?: '-' ); ?></td>
								<td><?php echo esc_html( $subscriber->source ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $subscriber->subscribed_at ) ) ); ?></td>
								<td>
									<?php if ( $subscriber->confirmed ) : ?>
										<span style="color: green;">✓ <?php esc_html_e( 'Sì', 'caniincasa-core' ); ?></span>
									<?php else : ?>
										<span style="color: orange;">⏳ <?php esc_html_e( 'In attesa', 'caniincasa-core' ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display: inline-block;">
										<input type="hidden" name="action" value="delete_newsletter_subscriber">
										<input type="hidden" name="subscriber_id" value="<?php echo esc_attr( $subscriber->id ); ?>">
										<?php wp_nonce_field( 'delete_newsletter_subscriber' ); ?>
										<button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Sei sicuro di voler eliminare questo iscritto?', 'caniincasa-core' ); ?>');">
											<?php esc_html_e( 'Elimina', 'caniincasa-core' ); ?>
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<?php
						echo paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;', 'caniincasa-core' ),
							'next_text' => __( '&raquo;', 'caniincasa-core' ),
							'total'     => $total_pages,
							'current'   => $page,
						) );
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Export CSV
	 */
	public function export_csv() {
		check_admin_referer( 'export_newsletter_csv' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Non hai i permessi per eseguire questa azione.', 'caniincasa-core' ) );
		}

		global $wpdb;

		$subscribers = $wpdb->get_results(
			"SELECT email, name, source, subscribed_at, confirmed, ip_address FROM {$this->table_name} WHERE status = 'active' ORDER BY subscribed_at DESC"
		);

		// Set headers
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=newsletter-subscribers-' . date( 'Y-m-d' ) . '.csv' );

		// Create output stream
		$output = fopen( 'php://output', 'w' );

		// BOM for UTF-8
		fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );

		// Header row
		fputcsv( $output, array( 'Email', 'Nome', 'Fonte', 'Data Iscrizione', 'Confermato', 'IP' ) );

		// Data rows
		foreach ( $subscribers as $subscriber ) {
			fputcsv( $output, array(
				$subscriber->email,
				$subscriber->name,
				$subscriber->source,
				$subscriber->subscribed_at,
				$subscriber->confirmed ? 'Sì' : 'No',
				$subscriber->ip_address,
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Delete subscriber
	 */
	public function delete_subscriber() {
		check_admin_referer( 'delete_newsletter_subscriber' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Non hai i permessi per eseguire questa azione.', 'caniincasa-core' ) );
		}

		global $wpdb;

		$subscriber_id = intval( $_POST['subscriber_id'] ?? 0 );

		if ( $subscriber_id ) {
			$wpdb->delete(
				$this->table_name,
				array( 'id' => $subscriber_id ),
				array( '%d' )
			);
		}

		wp_redirect( admin_url( 'admin.php?page=caniincasa-newsletter' ) );
		exit;
	}
}

// Initialize
new Caniincasa_Newsletter();
