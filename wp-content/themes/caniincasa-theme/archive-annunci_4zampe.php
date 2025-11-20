<?php
/**
 * Template for Archive Annunci 4 Zampe
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main archive-annunci archive-annunci-4zampe">

	<!-- Archive Header -->
	<div class="archive-header">
		<div class="container">
			<h1 class="archive-title">
				<?php
				if ( is_post_type_archive() ) {
					post_type_archive_title();
				} else {
					esc_html_e( 'Annunci 4 Zampe', 'caniincasa' );
				}
				?>
			</h1>
			<p class="archive-description">
				<?php esc_html_e( 'Cerca cani in adozione, cuccioli in vendita o pubblica il tuo annuncio', 'caniincasa' ); ?>
			</p>
		</div>
	</div>

	<div class="container">
		<div class="archive-content">

			<!-- Filters Sidebar -->
			<aside class="archive-filters">
				<h3><?php esc_html_e( 'Filtra Risultati', 'caniincasa' ); ?></h3>

				<form id="annunci-filters" class="filters-form" method="get">

					<!-- Tipo Annuncio -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Tipo Annuncio', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_tipo = isset( $_GET['tipo_annuncio'] ) ? sanitize_text_field( $_GET['tipo_annuncio'] ) : '';
							$tipo_options = array(
								''       => __( 'Tutti', 'caniincasa' ),
								'offro'  => __( 'Offro', 'caniincasa' ),
								'cerco'  => __( 'Cerco', 'caniincasa' ),
							);
							foreach ( $tipo_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="radio"
									       id="tipo_<?php echo esc_attr( $value ? $value : 'all' ); ?>"
									       name="tipo_annuncio"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( $current_tipo, $value ); ?>>
									<label for="tipo_<?php echo esc_attr( $value ? $value : 'all' ); ?>">
										<?php echo esc_html( $label ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Età -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Età', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_eta = isset( $_GET['eta'] ) ? sanitize_text_field( $_GET['eta'] ) : '';
							$eta_options = array(
								''         => __( 'Tutte', 'caniincasa' ),
								'cucciolo' => __( 'Cucciolo', 'caniincasa' ),
								'adulto'   => __( 'Adulto', 'caniincasa' ),
							);
							foreach ( $eta_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="radio"
									       id="eta_<?php echo esc_attr( $value ? $value : 'all' ); ?>"
									       name="eta"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( $current_eta, $value ); ?>>
									<label for="eta_<?php echo esc_attr( $value ? $value : 'all' ); ?>">
										<?php echo esc_html( $label ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Tipo Cane -->
					<div class="filter-group">
						<h4 class="filter-group-title"><?php esc_html_e( 'Tipo Cane', 'caniincasa' ); ?></h4>
						<div class="filter-options">
							<?php
							$current_tipo_cane = isset( $_GET['tipo_cane'] ) ? sanitize_text_field( $_GET['tipo_cane'] ) : '';
							$tipo_cane_options = array(
								''          => __( 'Tutti', 'caniincasa' ),
								'razza'     => __( 'Razza', 'caniincasa' ),
								'meticcio'  => __( 'Meticcio', 'caniincasa' ),
							);
							foreach ( $tipo_cane_options as $value => $label ) :
								?>
								<div class="filter-option">
									<input type="radio"
									       id="tipo_cane_<?php echo esc_attr( $value ? $value : 'all' ); ?>"
									       name="tipo_cane"
									       value="<?php echo esc_attr( $value ); ?>"
										<?php checked( $current_tipo_cane, $value ); ?>>
									<label for="tipo_cane_<?php echo esc_attr( $value ? $value : 'all' ); ?>">
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
						<a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>" class="btn btn-outline btn-block">
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
						<select id="annunci-sort" onchange="this.form.submit()">
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
							$tipo_annuncio = get_field( 'tipo_annuncio' );
							$eta           = get_field( 'eta' );
							$tipo_cane     = get_field( 'tipo_cane' );
							$razza_id      = get_field( 'razza' );

							// Get razza name
							$razza_name = '';
							if ( $razza_id && $tipo_cane === 'razza' ) {
								$razza_name = get_the_title( $razza_id );
							}

							// Check if expired
							$data_pubblicazione = get_the_date( 'U' );
							$giorni_scadenza    = get_field( 'giorni_scadenza' ) ?: 30;
							$is_expired         = ( current_time( 'timestamp' ) > ( $data_pubblicazione + ( intval( $giorni_scadenza ) * DAY_IN_SECONDS ) ) );
							?>

							<article class="annuncio-card <?php echo $is_expired ? 'annuncio-expired' : ''; ?>">

								<!-- Image -->
								<div class="annuncio-card-image">
									<a href="<?php the_permalink(); ?>">
										<?php
										if ( has_post_thumbnail() ) {
											the_post_thumbnail( 'caniincasa-medium' );
										} else {
											echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/placeholder-dog.jpg' ) . '" alt="' . esc_attr( get_the_title() ) . '">';
										}
										?>
									</a>

									<!-- Badges -->
									<?php if ( $tipo_annuncio ) : ?>
										<span class="annuncio-badge badge-<?php echo esc_attr( $tipo_annuncio ); ?>">
											<?php echo esc_html( ucfirst( $tipo_annuncio ) ); ?>
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

									<div class="annuncio-card-meta">
										<?php if ( $eta ) : ?>
											<span>
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
													<path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php echo esc_html( ucfirst( $eta ) ); ?>
											</span>
										<?php endif; ?>

										<?php if ( $razza_name ) : ?>
											<span>
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
													<path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php echo esc_html( $razza_name ); ?>
											</span>
										<?php elseif ( $tipo_cane === 'meticcio' ) : ?>
											<span>
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
													<path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php esc_html_e( 'Meticcio', 'caniincasa' ); ?>
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
						<a href="<?php echo esc_url( get_post_type_archive_link( 'annunci_4zampe' ) ); ?>" class="btn btn-primary">
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
					<h2><?php esc_html_e( 'Hai un cane da offrire o cercare?', 'caniincasa' ); ?></h2>
					<p><?php esc_html_e( 'Pubblica il tuo annuncio gratuitamente e raggiungi migliaia di appassionati!', 'caniincasa' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/dashboard/nuovo-annuncio/' ) ); ?>" class="btn btn-large btn-success">
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
