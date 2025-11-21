<?php
/**
 * Admin CSV Import Interface
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Import Page Class
 */
class Caniincasa_Admin_Import {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'wp_ajax_caniincasa_import_csv', array( $this, 'ajax_import_csv' ) );
		add_action( 'wp_ajax_caniincasa_upload_csv', array( $this, 'ajax_upload_csv' ) );
		add_action( 'wp_ajax_caniincasa_get_import_status', array( $this, 'ajax_get_import_status' ) );
	}

	/**
	 * Add admin menu page
	 */
	public function add_menu_page() {
		add_menu_page(
			__( 'Importa CSV', 'caniincasa-core' ),
			__( 'Importa CSV', 'caniincasa-core' ),
			'manage_options',
			'caniincasa-import',
			array( $this, 'render_import_page' ),
			'dashicons-upload',
			30
		);
	}

	/**
	 * Render import page
	 */
	public function render_import_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Non hai i permessi per accedere a questa pagina.', 'caniincasa-core' ) );
		}

		// Get available CSV files
		$csv_files = $this->get_available_csv_files();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Importa Dati CSV - Caniincasa', 'caniincasa-core' ); ?></h1>

			<!-- AVVISO IMPORTANTE: Fix Bug Mapping ACF -->
			<div class="notice notice-warning is-dismissible">
				<h2 style="margin-top: 10px;">⚠️ <?php esc_html_e( 'IMPORTANTE: Bug Mapping ACF Corretto', 'caniincasa-core' ); ?></h2>
				<p><strong><?php esc_html_e( 'È stato risolto un bug critico nel mapping dei campi ACF delle razze.', 'caniincasa-core' ); ?></strong></p>
				<p><?php esc_html_e( 'I seguenti campi erano mappati con nomi SBAGLIATI nelle importazioni precedenti:', 'caniincasa-core' ); ?></p>
				<ul style="list-style: disc; margin-left: 30px; line-height: 1.8;">
					<li><code>adattabilita_appartamento</code> <?php esc_html_e( '(prima era "adattabilita_ad_appartamento")', 'caniincasa-core' ); ?></li>
					<li><code>compatibilita_con_i_bambini</code> <?php esc_html_e( '(prima era "compatibile_con_bambini")', 'caniincasa-core' ); ?></li>
					<li><code>tolleranza_estranei</code> <?php esc_html_e( '(prima era "tolleranza_verso_estranei")', 'caniincasa-core' ); ?></li>
					<li><code>livello_esperienza_richiesto</code> <?php esc_html_e( '(prima era "esperienza_richiesta")', 'caniincasa-core' ); ?></li>
				</ul>
				<p><strong style="color: #d63638;">
					<?php esc_html_e( '📌 AZIONE RICHIESTA: Devi RE-IMPORTARE il file "Razze-di-Cani" per applicare la correzione e far funzionare i filtri sul frontend.', 'caniincasa-core' ); ?>
				</strong></p>
				<p><?php esc_html_e( 'Le importazioni precedenti hanno salvato i dati con nomi di campo errati che non corrispondono alle definizioni ACF.', 'caniincasa-core' ); ?></p>
			</div>

			<div class="caniincasa-import-container">

				<!-- Import Options -->
				<div class="card">
					<h2><?php esc_html_e( 'Opzioni di Importazione', 'caniincasa-core' ); ?></h2>

					<form id="caniincasa-import-form">
						<?php wp_nonce_field( 'caniincasa_import_csv', 'caniincasa_import_nonce' ); ?>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="import-type"><?php esc_html_e( 'Tipo di Dati', 'caniincasa-core' ); ?></label>
								</th>
								<td>
									<select name="import_type" id="import-type" class="regular-text">
										<option value=""><?php esc_html_e( '-- Seleziona --', 'caniincasa-core' ); ?></option>
										<option value="razze"><?php esc_html_e( 'Razze di Cani', 'caniincasa-core' ); ?></option>
										<option value="allevamenti"><?php esc_html_e( 'Allevamenti', 'caniincasa-core' ); ?></option>
										<option value="veterinari"><?php esc_html_e( 'Strutture Veterinarie', 'caniincasa-core' ); ?></option>
										<option value="canili"><?php esc_html_e( 'Canili', 'caniincasa-core' ); ?></option>
										<option value="pensioni"><?php esc_html_e( 'Pensioni per Cani', 'caniincasa-core' ); ?></option>
										<option value="centri-cinofili"><?php esc_html_e( 'Centri Cinofili', 'caniincasa-core' ); ?></option>
									</select>
									<p class="description">
										<?php esc_html_e( 'Seleziona il tipo di dati da importare', 'caniincasa-core' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="csv-file"><?php esc_html_e( 'File CSV', 'caniincasa-core' ); ?></label>
								</th>
								<td>
									<input type="file" name="csv_file" id="csv-file" accept=".csv" />
									<p class="description">
										<?php esc_html_e( 'Carica un file CSV da importare', 'caniincasa-core' ); ?>
									</p>

									<?php if ( ! empty( $csv_files ) ) : ?>
										<p><strong><?php esc_html_e( 'File CSV disponibili nella root:', 'caniincasa-core' ); ?></strong></p>
										<ul style="list-style: disc; margin-left: 20px;">
											<?php foreach ( $csv_files as $file ) : ?>
												<li>
													<code><?php echo esc_html( $file['name'] ); ?></code>
													(<?php echo esc_html( size_format( $file['size'] ) ); ?>)
													<button type="button" class="button button-small use-file-btn" data-file="<?php echo esc_attr( $file['path'] ); ?>" data-type="<?php echo esc_attr( $file['type'] ); ?>">
														<?php esc_html_e( 'Usa questo file', 'caniincasa-core' ); ?>
													</button>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="batch-size"><?php esc_html_e( 'Batch Size', 'caniincasa-core' ); ?></label>
								</th>
								<td>
									<input type="number" name="batch_size" id="batch-size" value="10" min="1" max="100" class="small-text" />
									<p class="description">
										<?php esc_html_e( 'Numero di record da processare per batch (ridurre se si verificano timeout)', 'caniincasa-core' ); ?>
									</p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<button type="submit" class="button button-primary" id="start-import-btn">
								<?php esc_html_e( 'Avvia Importazione', 'caniincasa-core' ); ?>
							</button>
						</p>
					</form>
				</div>

				<!-- Import Progress -->
				<div class="card" id="import-progress-card" style="display: none;">
					<h2><?php esc_html_e( 'Progresso Importazione', 'caniincasa-core' ); ?></h2>

					<div class="import-progress-bar">
						<div class="progress-bar-container">
							<div class="progress-bar" id="import-progress-bar" style="width: 0%;"></div>
						</div>
						<p class="progress-text" id="progress-text">0%</p>
					</div>

					<div class="import-stats">
						<table class="widefat">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Stato', 'caniincasa-core' ); ?></th>
									<th><?php esc_html_e( 'Totali', 'caniincasa-core' ); ?></th>
									<th><?php esc_html_e( 'Importati', 'caniincasa-core' ); ?></th>
									<th><?php esc_html_e( 'Aggiornati', 'caniincasa-core' ); ?></th>
									<th><?php esc_html_e( 'Saltati', 'caniincasa-core' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td id="import-status">-</td>
									<td id="import-total">0</td>
									<td id="import-imported">0</td>
									<td id="import-updated">0</td>
									<td id="import-skipped">0</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="import-log" id="import-log" style="display: none;">
						<h3><?php esc_html_e( 'Log Errori', 'caniincasa-core' ); ?></h3>
						<div class="log-content" style="max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">
							<pre id="error-log"></pre>
						</div>
					</div>
				</div>

				<!-- Import Instructions -->
				<div class="card">
					<h2><?php esc_html_e( 'Istruzioni', 'caniincasa-core' ); ?></h2>

					<ol>
						<li><?php esc_html_e( 'Seleziona il tipo di dati che vuoi importare dal menu a tendina', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'Carica un file CSV oppure usa uno dei file disponibili nella root del progetto', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'Clicca su "Avvia Importazione" e attendi il completamento', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'L\'importazione avviene in batch per evitare timeout del server', 'caniincasa-core' ); ?></li>
					</ol>

					<p><strong><?php esc_html_e( 'IMPORTANTE:', 'caniincasa-core' ); ?></strong></p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li><?php esc_html_e( 'Assicurati che ACF Pro sia attivato prima di importare', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'L\'importazione può richiedere diversi minuti per file grandi', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'Non chiudere la pagina durante l\'importazione', 'caniincasa-core' ); ?></li>
						<li><?php esc_html_e( 'Dopo l\'importazione, vai in Impostazioni → Permalink e salva per aggiornare le URL', 'caniincasa-core' ); ?></li>
					</ul>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Get available CSV files from root
	 */
	private function get_available_csv_files() {
		$root_path = ABSPATH;
		$csv_files = array();

		$files = array(
			'Razze-di-Cani-Export-2025-November-17-1521.csv' => 'razze',
			'Allevamenti-Export-2025-November-17-1454.csv' => 'allevamenti',
			'Strutture-Veterinarie-Export-2025-November-17-1522.csv' => 'veterinari',
			'Canili-Export-2025-November-17-1510.csv' => 'canili',
			'Pensioni-per-Cani-Export-2025-November-17-1518.csv' => 'pensioni',
			'Centri-Cinofili-Export-2025-November-17-1516.csv' => 'centri-cinofili',
		);

		foreach ( $files as $filename => $type ) {
			$filepath = $root_path . $filename;
			if ( file_exists( $filepath ) ) {
				$csv_files[] = array(
					'name' => $filename,
					'path' => $filepath,
					'size' => filesize( $filepath ),
					'type' => $type,
				);
			}
		}

		return $csv_files;
	}

	/**
	 * AJAX handler for CSV import
	 */
	public function ajax_import_csv() {
		check_ajax_referer( 'caniincasa_import_csv', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permessi insufficienti', 'caniincasa-core' ) ) );
		}

		$import_type = isset( $_POST['import_type'] ) ? sanitize_text_field( $_POST['import_type'] ) : '';
		$file_path = isset( $_POST['file_path'] ) ? sanitize_text_field( $_POST['file_path'] ) : '';
		$batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;

		if ( empty( $import_type ) || empty( $file_path ) ) {
			wp_send_json_error( array( 'message' => __( 'Parametri mancanti', 'caniincasa-core' ) ) );
		}

		if ( ! file_exists( $file_path ) ) {
			wp_send_json_error( array( 'message' => __( 'File non trovato', 'caniincasa-core' ) ) );
		}

		// Get importer instance
		$importer = caniincasa_csv_importer();

		// Process batch
		$result = $this->process_batch( $importer, $file_path, $import_type, $batch_size, $offset );

		wp_send_json_success( $result );
	}

	/**
	 * Process single batch of CSV data
	 */
	private function process_batch( $importer, $file_path, $import_type, $batch_size, $offset ) {
		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			return array(
				'success' => false,
				'message' => __( 'Impossibile aprire il file', 'caniincasa-core' ),
			);
		}

		// Read header
		$headers = fgetcsv( $handle );
		if ( isset( $headers[0] ) ) {
			$headers[0] = str_replace( "\xEF\xBB\xBF", '', $headers[0] );
		}

		// Count total rows
		$total_rows = 0;
		while ( fgetcsv( $handle ) !== false ) {
			$total_rows++;
		}

		// Reset pointer
		rewind( $handle );
		fgetcsv( $handle ); // Skip header

		// Skip to offset
		for ( $i = 0; $i < $offset; $i++ ) {
			if ( fgetcsv( $handle ) === false ) {
				break;
			}
		}

		// Process batch
		$results = array(
			'total' => $total_rows,
			'processed' => 0,
			'imported' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors' => array(),
			'completed' => false,
		);

		$count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false && $count < $batch_size ) {
			$data = array_combine( $headers, $row );

			// Import based on type
			$import_result = $this->import_single_row( $importer, $data, $import_type );

			if ( $import_result['success'] ) {
				if ( $import_result['action'] === 'updated' ) {
					$results['updated']++;
				} else {
					$results['imported']++;
				}
			} else {
				$results['skipped']++;
				$results['errors'][] = array(
					'title' => isset( $data['Title'] ) ? $data['Title'] : 'N/A',
					'message' => $import_result['message'],
				);
			}

			$results['processed']++;
			$count++;
		}

		fclose( $handle );

		// Check if completed
		$results['completed'] = ( $offset + $count ) >= $total_rows;
		$results['next_offset'] = $offset + $count;

		return $results;
	}

	/**
	 * Import single row based on type
	 */
	private function import_single_row( $importer, $data, $type ) {
		$reflection = new ReflectionClass( $importer );

		switch ( $type ) {
			case 'razze':
				$method = $reflection->getMethod( 'import_single_razza' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data );

			case 'allevamenti':
				$method = $reflection->getMethod( 'import_single_allevamento' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data );

			case 'veterinari':
				$method = $reflection->getMethod( 'import_single_struttura' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data, 'veterinari' );

			case 'canili':
				$method = $reflection->getMethod( 'import_single_struttura' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data, 'canili' );

			case 'pensioni':
				$method = $reflection->getMethod( 'import_single_struttura' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data, 'pensioni_per_cani' );

			case 'centri-cinofili':
				$method = $reflection->getMethod( 'import_single_struttura' );
				$method->setAccessible( true );
				return $method->invoke( $importer, $data, 'centri_cinofili' );

			default:
				return array(
					'success' => false,
					'message' => __( 'Tipo di importazione non riconosciuto', 'caniincasa-core' ),
				);
		}
	}

	/**
	 * AJAX handler for CSV upload
	 */
	public function ajax_upload_csv() {
		check_ajax_referer( 'caniincasa_core_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permessi insufficienti', 'caniincasa-core' ) ) );
		}

		if ( empty( $_FILES['csv_file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Nessun file caricato', 'caniincasa-core' ) ) );
		}

		$file = $_FILES['csv_file'];

		// Validate file type
		$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( $file_ext !== 'csv' ) {
			wp_send_json_error( array( 'message' => __( 'Il file deve essere in formato CSV', 'caniincasa-core' ) ) );
		}

		// Create uploads directory if not exists
		$upload_dir = wp_upload_dir();
		$csv_dir = $upload_dir['basedir'] . '/caniincasa-imports';

		if ( ! file_exists( $csv_dir ) ) {
			wp_mkdir_p( $csv_dir );
		}

		// Generate unique filename
		$filename = 'import_' . time() . '_' . sanitize_file_name( $file['name'] );
		$filepath = $csv_dir . '/' . $filename;

		// Move uploaded file
		if ( move_uploaded_file( $file['tmp_name'], $filepath ) ) {
			wp_send_json_success( array(
				'message'   => __( 'File caricato con successo', 'caniincasa-core' ),
				'file_path' => $filepath,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Errore durante il caricamento del file', 'caniincasa-core' ) ) );
		}
	}
}

// Initialize
new Caniincasa_Admin_Import();
