<?php
/**
 * Template for Archive Annunci Dogsitter
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main archive-annunci archive-annunci-dogsitter">

	<!-- Archive Header -->
	<div class="archive-header">
		<div class="container">
			<h1 class="archive-title">
				<?php
				if ( is_post_type_archive() ) {
					post_type_archive_title();
				} else {
					esc_html_e( 'Annunci Dogsitter', 'caniincasa' );
				}
				?>
			</h1>
			<p class="archive-description">
				<?php esc_html_e( 'Trova il dogsitter perfetto o offri i tuoi servizi di pet sitting', 'caniincasa' ); ?>
			</p>
		</div>
	</div>

	<div class="container">
		<div class="archive-content">

			<!-- Filters Sidebar -->
			<aside class="archive-filters">
				<h3><?php esc_html_e( 'Filtra Risultati', 'caniincasa' ); ?></h3>

				<form id="dogsitter-filters" class="filters-form" method="get">

					<!-- Tipo -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Tipo', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_tipo = isset( $_GET['tipo'] ) ? sanitize_text_field( $_GET['tipo'] ) : '';
							$tipo_options = array(
								''      => __( 'Tutti', 'caniincasa' ),
								'offro' => __( 'Offro Servizio', 'caniincasa' ),
								'cerco' => __( 'Cerco Dogsitter', 'caniincasa' ),
							);
							foreach ( $tipo_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="radio"
									       id="tipo_<?php echo esc_attr( $value ? $value : 'all' ); ?>"
									       name="tipo"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( $current_tipo, $value ); ?>>
									<label for="tipo_<?php echo esc_attr( $value ? $value : 'all' ); ?>">
										<?php echo esc_html( $label ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Esperienza -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Esperienza', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_esperienza = isset( $_GET['esperienza'] ) ? sanitize_text_field( $_GET['esperienza'] ) : '';
							$esperienza_options = array(
								''               => __( 'Tutte', 'caniincasa' ),
								'principiante'   => __( 'Principiante', 'caniincasa' ),
								'intermedio'     => __( 'Intermedio', 'caniincasa' ),
								'esperto'        => __( 'Esperto', 'caniincasa' ),
								'professionale'  => __( 'Professionale', 'caniincasa' ),
							);
							foreach ( $esperienza_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="radio"
									       id="esperienza_<?php echo esc_attr( $value ? $value : 'all' ); ?>"
									       name="esperienza"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( $current_esperienza, $value ); ?>>
									<label for="esperienza_<?php echo esc_attr( $value ? $value : 'all' ); ?>">
										<?php echo esc_html( $label ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Servizi -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Servizi', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_servizi = isset( $_GET['servizi'] ) ? (array) $_GET['servizi'] : array();
							$servizi_options = array(
								'passeggiate'      => __( 'Passeggiate', 'caniincasa' ),
								'pensione'         => __( 'Pensione', 'caniincasa' ),
								'visita_domicilio' => __( 'Visita a Domicilio', 'caniincasa' ),
								'toelettatura'     => __( 'Toelettatura', 'caniincasa' ),
								'addestramento'    => __( 'Addestramento', 'caniincasa' ),
							);
							foreach ( $servizi_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="checkbox"
									       id="servizio_<?php echo esc_attr( $value ); ?>"
									       name="servizi[]"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( in_array( $value, $current_servizi ) ); ?>>
									<label for="servizio_<?php echo esc_attr( $value ); ?>">
										<?php echo esc_html( $label ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Search -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Cerca', 'caniincasa' ); ?></h4>
						<input type="search"
						       name="s"
						       class="filter-search"
						       placeholder="<?php esc_attr_e( 'Parola chiave...', 'caniincasa' ); ?>"
						       value="<?php echo get_search_query(); ?>">
					</div>

					<!-- Buttons -->
					<div class="filter-actions">
						<button type="submit" class="btn btn-primary btn-block">
							<?php esc_html_e( 'Applica Filtri', 'caniincasa' ); ?>
						</button>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_dogsitter' ) ); ?>" class="btn btn-outline btn-block">
							<?php esc_html_e( 'Reset', 'caniincasa' ); ?>
						</a>
					</div>

				</form>
			</aside>

			<!-- Main Content -->
			<div class="archive-main">

				<!-- Results Info -->
				<div class="archive-results-info">
					<div class="results-count">
						<?php
						global $wp_query;
						printf(
							// translators: %d: number of results
							esc_html( _n( '%d annuncio trovato', '%d annunci trovati', $wp_query->found_posts, 'caniincasa' ) ),
							$wp_query->found_posts
						);
						?>
					</div>
					<div class="results-sort">
						<select id="dogsitter-sort" onchange="this.form.submit()">
							<option value="date_desc" <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'date_desc', 'date_desc' ); ?>>
								<?php esc_html_e( 'Più recenti', 'caniincasa' ); ?>
							</option>
							<option value="date_asc" <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'date_asc' ); ?>>
								<?php esc_html_e( 'Meno recenti', 'caniincasa' ); ?>
							</option>
							<option value="title_asc" <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'title_asc' ); ?>>
								<?php esc_html_e( 'Titolo A-Z', 'caniincasa' ); ?>
							</option>
						</select>
					</div>
				</div>

				<!-- Annunci Grid -->
				<?php if ( have_posts() ) : ?>
					<div class="annunci-grid">
						<?php
						while ( have_posts() ) :
							the_post();

							// Get ACF fields
							$tipo              = get_field( 'tipo' );
							$esperienza        = get_field( 'esperienza' );
							$servizi_offerti   = get_field( 'servizi_offerti' );
							$prezzo_indicativo = get_field( 'prezzo_indicativo' );

							// Check if expired (30 days default for dogsitter)
							$data_pubblicazione = get_the_date( 'U' );
							$giorni_scadenza    = 30;
							$is_expired         = ( current_time( 'timestamp' ) > ( $data_pubblicazione + ( intval( $giorni_scadenza ) * DAY_IN_SECONDS ) ) );

							// Servizi labels
							$servizi_labels = array(
								'passeggiate'      => __( 'Passeggiate', 'caniincasa' ),
								'pensione'         => __( 'Pensione', 'caniincasa' ),
								'visita_domicilio' => __( 'Visita a Domicilio', 'caniincasa' ),
								'toelettatura'     => __( 'Toelettatura', 'caniincasa' ),
								'addestramento'    => __( 'Addestramento', 'caniincasa' ),
							);
							?>

							<article class="annuncio-card <?php echo $is_expired ? 'annuncio-expired' : ''; ?>">

								<!-- Image -->
								<div class="annuncio-card-image">
									<a href="<?php the_permalink(); ?>">
										<?php
										if ( has_post_thumbnail() ) {
											the_post_thumbnail( 'caniincasa-medium' );
										} else {
											echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/placeholder-dogsitter.jpg' ) . '" alt="' . esc_attr( get_the_title() ) . '">';
										}
										?>
									</a>

									<!-- Badges -->
									<?php if ( $tipo ) : ?>
										<span class="annuncio-badge badge-<?php echo esc_attr( $tipo === 'offro' ? 'offro' : 'cerco' ); ?>">
											<?php
											if ( $tipo === 'offro' ) {
												esc_html_e( 'Offro', 'caniincasa' );
											} else {
												esc_html_e( 'Cerco', 'caniincasa' );
											}
											?>
										</span>
									<?php endif; ?>

									<?php if ( $is_expired ) : ?>
										<span class="annuncio-badge badge-error" style="top: 3.5rem;">
											<?php esc_html_e( 'Scaduto', 'caniincasa' ); ?>
										</span>
									<?php endif; ?>
								</div>

								<!-- Content -->
								<div class="annuncio-card-content">
									<h3 class="annuncio-card-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>

									<?php if ( has_excerpt() ) : ?>
										<p class="annuncio-card-excerpt">
											<?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
										</p>
									<?php endif; ?>

									<!-- Servizi Badges -->
									<?php if ( $servizi_offerti && is_array( $servizi_offerti ) && count( $servizi_offerti ) > 0 ) : ?>
										<div class="annuncio-servizi-preview">
											<?php
											$display_count = 0;
											foreach ( $servizi_offerti as $servizio ) :
												if ( $display_count >= 3 ) {
													break;
												}
												?>
												<span class="servizio-tag">
													<?php echo esc_html( $servizi_labels[ $servizio ] ?? ucfirst( $servizio ) ); ?>
												</span>
												<?php
												$display_count++;
											endforeach;
											?>
											<?php if ( count( $servizi_offerti ) > 3 ) : ?>
												<span class="servizi-more">
													+<?php echo count( $servizi_offerti ) - 3; ?>
												</span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<div class="annuncio-card-meta">
										<?php if ( $esperienza ) : ?>
											<span>
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
													<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php echo esc_html( ucfirst( $esperienza ) ); ?>
											</span>
										<?php endif; ?>

										<?php if ( $prezzo_indicativo ) : ?>
											<span>
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
													<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php echo esc_html( $prezzo_indicativo ); ?>
											</span>
										<?php endif; ?>

										<span>
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
												<path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'fa', 'caniincasa' ); ?>
										</span>
									</div>

									<a href="<?php the_permalink(); ?>" class="btn btn-primary btn-block" style="margin-top: 1rem;">
										<?php esc_html_e( 'Vedi Dettagli', 'caniincasa' ); ?>
									</a>
								</div>

							</article>

						<?php endwhile; ?>
					</div>

					<!-- Pagination -->
					<div class="pagination">
						<?php
						echo paginate_links( array(
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'type'      => 'list',
						) );
						?>
					</div>

				<?php else : ?>

					<!-- No Results -->
					<div class="no-results">
						<svg width="80" height="80" viewBox="0 0 24 24" fill="none" style="margin-bottom: 1rem; opacity: 0.3;">
							<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<h2><?php esc_html_e( 'Nessun annuncio trovato', 'caniincasa' ); ?></h2>
						<p><?php esc_html_e( 'Prova a modificare i filtri di ricerca o rimuovi alcuni criteri.', 'caniincasa' ); ?></p>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_dogsitter' ) ); ?>" class="btn btn-primary">
							<?php esc_html_e( 'Visualizza tutti gli annunci', 'caniincasa' ); ?>
						</a>
					</div>

				<?php endif; ?>

			</div>

		</div>
	</div>

	<!-- CTA Section -->
	<?php if ( is_user_logged_in() ) : ?>
		<div class="archive-cta">
			<div class="container">
				<div class="cta-content">
					<h2><?php esc_html_e( 'Offri servizi di dogsitting?', 'caniincasa' ); ?></h2>
					<p><?php esc_html_e( 'Pubblica il tuo annuncio gratuitamente e trova i clienti perfetti per te!', 'caniincasa' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/dashboard/nuovo-annuncio-dogsitter/' ) ); ?>" class="btn btn-large btn-success">
						<?php esc_html_e( 'Pubblica Annuncio', 'caniincasa' ); ?>
					</a>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div class="archive-cta">
			<div class="container">
				<div class="cta-content">
					<h2><?php esc_html_e( 'Vuoi pubblicare un annuncio?', 'caniincasa' ); ?></h2>
					<p><?php esc_html_e( 'Registrati gratuitamente per pubblicare i tuoi annunci!', 'caniincasa' ); ?></p>
					<button type="button" class="btn btn-large btn-success js-open-annuncio-modal">
						<?php esc_html_e( 'Registrati Ora', 'caniincasa' ); ?>
					</button>
				</div>
			</div>
		</div>
	<?php endif; ?>

</main>

<?php
get_footer();
