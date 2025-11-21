<?php
/**
 * Template for Single Annuncio Dogsitter
 *
 * @package Caniincasa
 */

get_header();

while ( have_posts() ) :
	the_post();

	// Get ACF fields
	$tipo             = get_field( 'tipo' );
	$disponibilita    = get_field( 'disponibilita' );
	$servizi_offerti  = get_field( 'servizi_offerti' );
	$esperienza       = get_field( 'esperienza' );
	$prezzo_indicativo = get_field( 'prezzo_indicativo' );

	// Get contact info (handles both regular and anonymous users)
	$contact_info = caniincasa_get_annuncio_contact_info( get_the_ID() );
	$is_anonymous = isset( $contact_info['is_anonymous'] ) && $contact_info['is_anonymous'];

	// For display purposes
	if ( $is_anonymous ) {
		// Anonymous user: show anonymous name and contacts
		$author_name  = $contact_info['name'];
		$author_email = $contact_info['email'];
		$author_phone = $contact_info['phone'];
		$author_id    = 0; // No real author ID for anonymous
	} else {
		// Regular user: show author info
		$author_id    = get_the_author_meta( 'ID' );
		$author_name  = caniincasa_get_user_display_name( $author_id ); // Privacy-safe name (Nome I.)
		$author_email = $contact_info['email']; // Use contact info (handles annuncio-specific email)
		$author_phone = $contact_info['phone']; // Use contact info (handles annuncio-specific phone)
	}

	// Calculate expiration date (default 30 days for dogsitter)
	$data_pubblicazione = get_the_date( 'U' );
	$giorni_validita    = 30;
	$data_scadenza      = date_i18n( get_option( 'date_format' ), $data_pubblicazione + ( $giorni_validita * DAY_IN_SECONDS ) );

	// Check if expired
	$is_expired = ( current_time( 'timestamp' ) > ( $data_pubblicazione + ( $giorni_validita * DAY_IN_SECONDS ) ) );

	// Servizi labels
	$servizi_labels = array(
		'passeggiate'      => __( 'Passeggiate', 'caniincasa' ),
		'pensione'         => __( 'Pensione', 'caniincasa' ),
		'visita_domicilio' => __( 'Visita a Domicilio', 'caniincasa' ),
		'toelettatura'     => __( 'Toelettatura', 'caniincasa' ),
		'addestramento'    => __( 'Addestramento Base', 'caniincasa' ),
	);
	?>

	<main id="main-content" class="site-main single-annuncio single-annuncio-dogsitter">

		<!-- Hero Section -->
		<div class="annuncio-hero <?php echo $is_expired ? 'annuncio-expired' : ''; ?>">
			<div class="container">
				<div class="hero-content">
					<div class="breadcrumbs-wrapper">
						<?php caniincasa_breadcrumbs(); ?>
					</div>

					<?php if ( $is_expired ) : ?>
						<div class="expiration-notice">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
								<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							<?php esc_html_e( 'Questo annuncio è scaduto', 'caniincasa' ); ?>
						</div>
					<?php endif; ?>

					<h1 class="annuncio-title"><?php the_title(); ?></h1>

					<div class="annuncio-meta">
						<?php if ( $tipo ) : ?>
							<span class="meta-item badge badge-<?php echo esc_attr( $tipo === 'offro' ? 'offro' : 'cerco' ); ?>">
								<?php
								if ( $tipo === 'offro' ) {
									esc_html_e( 'Offro Servizio', 'caniincasa' );
								} else {
									esc_html_e( 'Cerco Dogsitter', 'caniincasa' );
								}
								?>
							</span>
						<?php endif; ?>

						<?php if ( $esperienza ) : ?>
							<span class="meta-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
									<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php echo esc_html( ucfirst( $esperienza ) ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $prezzo_indicativo ) : ?>
							<span class="meta-item">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
									<path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php echo esc_html( $prezzo_indicativo ); ?>
							</span>
						<?php endif; ?>

						<span class="meta-item meta-date">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
								<path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php echo get_the_date(); ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="annuncio-content-wrapper">

				<!-- Main Content -->
				<div class="annuncio-main-content">

					<!-- Featured Image -->
					<div class="annuncio-featured-image">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'caniincasa-large' ); ?>
						<?php else : ?>
							<img src="https://www.caniincasa.it/wp-content/uploads/2025/11/image_placeholder.webp" alt="<?php echo esc_attr( get_the_title() ); ?>" class="placeholder-image">
						<?php endif; ?>
					</div>

					<!-- Info Box -->
					<div class="annuncio-info-box">
						<h2 class="box-title">
							<?php
							if ( $tipo === 'offro' ) {
								esc_html_e( 'Servizi Offerti', 'caniincasa' );
							} else {
								esc_html_e( 'Cosa Cerco', 'caniincasa' );
							}
							?>
						</h2>

						<div class="info-grid">
							<?php if ( $tipo ) : ?>
								<div class="info-item">
									<span class="info-label"><?php esc_html_e( 'Tipo:', 'caniincasa' ); ?></span>
									<span class="info-value badge-large badge-<?php echo esc_attr( $tipo === 'offro' ? 'offro' : 'cerco' ); ?>">
										<?php
										if ( $tipo === 'offro' ) {
											esc_html_e( 'Offro Servizio', 'caniincasa' );
										} else {
											esc_html_e( 'Cerco Dogsitter', 'caniincasa' );
										}
										?>
									</span>
								</div>
							<?php endif; ?>

							<?php if ( $esperienza ) : ?>
								<div class="info-item">
									<span class="info-label"><?php esc_html_e( 'Esperienza:', 'caniincasa' ); ?></span>
									<span class="info-value">
										<?php echo esc_html( ucfirst( $esperienza ) ); ?>
									</span>
								</div>
							<?php endif; ?>

							<?php if ( $prezzo_indicativo ) : ?>
								<div class="info-item">
									<span class="info-label"><?php esc_html_e( 'Prezzo Indicativo:', 'caniincasa' ); ?></span>
									<span class="info-value">
										<?php echo esc_html( $prezzo_indicativo ); ?>
									</span>
								</div>
							<?php endif; ?>

							<div class="info-item">
								<span class="info-label"><?php esc_html_e( 'Pubblicato:', 'caniincasa' ); ?></span>
								<span class="info-value"><?php echo get_the_date(); ?></span>
							</div>

							<div class="info-item">
								<span class="info-label"><?php esc_html_e( 'Scadenza:', 'caniincasa' ); ?></span>
								<span class="info-value <?php echo $is_expired ? 'text-error' : ''; ?>">
									<?php echo esc_html( $data_scadenza ); ?>
									<?php if ( $is_expired ) : ?>
										<span class="badge badge-error"><?php esc_html_e( 'Scaduto', 'caniincasa' ); ?></span>
									<?php endif; ?>
								</span>
							</div>
						</div>

						<!-- Servizi Offerti -->
						<?php if ( $servizi_offerti && is_array( $servizi_offerti ) && count( $servizi_offerti ) > 0 ) : ?>
							<div class="servizi-section">
								<h3 class="servizi-title">
									<?php esc_html_e( 'Servizi Disponibili', 'caniincasa' ); ?>
								</h3>
								<div class="servizi-list">
									<?php foreach ( $servizi_offerti as $servizio ) : ?>
										<span class="servizio-badge">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
												<path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<?php echo esc_html( $servizi_labels[ $servizio ] ?? ucfirst( $servizio ) ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- Description -->
					<?php if ( get_the_content() ) : ?>
						<div class="annuncio-description">
							<h2><?php esc_html_e( 'Descrizione', 'caniincasa' ); ?></h2>
							<div class="description-content">
								<?php the_content(); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Disponibilità -->
					<?php if ( $disponibilita ) : ?>
						<div class="annuncio-description">
							<h2><?php esc_html_e( 'Disponibilità', 'caniincasa' ); ?></h2>
							<div class="description-content">
								<p><?php echo nl2br( esc_html( $disponibilita ) ); ?></p>
							</div>
						</div>
					<?php endif; ?>

				</div>

				<!-- Sidebar -->
				<aside class="annuncio-sidebar">

					<!-- Author/Contact Box -->
					<div class="sidebar-box author-box">
						<h3 class="box-title"><?php esc_html_e( 'Pubblicato da', 'caniincasa' ); ?></h3>

						<?php if ( $is_anonymous ) : ?>
							<!-- Anonymous User -->
							<div class="author-info">
								<div class="author-avatar">
									<?php
									// Generic avatar for anonymous users
									echo '<div class="anonymous-avatar">' .
										'<svg width="60" height="60" viewBox="0 0 24 24" fill="none">' .
										'<path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' .
										'<path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' .
										'</svg>' .
										'</div>';
									?>
								</div>
								<div class="author-details">
									<h4 class="author-name"><?php echo esc_html( $author_name ); ?></h4>
									<p class="author-meta">
										<span class="badge badge-anonymous" style="background-color: #6c757d; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 3px;">
											<?php esc_html_e( 'Utente Privato', 'caniincasa' ); ?>
										</span>
									</p>
								</div>
							</div>

							<!-- Contact Info for Anonymous -->
							<div class="anonymous-contact-info" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--color-border);">
								<?php if ( $author_email ) : ?>
									<div class="contact-item" style="margin-bottom: 12px; font-size: 0.9rem;">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 6px; color: var(--color-primary);">
											<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<a href="mailto:<?php echo esc_attr( $author_email ); ?>" style="color: var(--color-text); text-decoration: none;">
											<?php echo esc_html( $author_email ); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if ( $author_phone ) : ?>
									<div class="contact-item" style="font-size: 0.9rem;">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 6px; color: var(--color-primary);">
											<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $author_phone ) ); ?>" style="color: var(--color-text); text-decoration: none;">
											<?php echo esc_html( $author_phone ); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>

						<?php else : ?>
							<!-- Registered User -->
							<div class="author-info">
								<div class="author-avatar">
									<?php echo get_avatar( $author_id, 80 ); ?>
								</div>
								<div class="author-details">
									<h4 class="author-name"><?php echo esc_html( $author_name ); ?></h4>
									<p class="author-meta">
										<?php
										printf(
											// translators: %s: registration date
											esc_html__( 'Membro da %s', 'caniincasa' ),
											get_the_author_meta( 'user_registered' ) ? date_i18n( 'F Y', strtotime( get_the_author_meta( 'user_registered' ) ) ) : ''
										);
										?>
									</p>
								</div>
							</div>

							<?php
							// Count author's posts
							$author_posts_count = count_user_posts( $author_id, 'annunci_dogsitter' );
							if ( $author_posts_count > 1 ) :
								?>
								<div class="author-stats">
									<span class="stat-item">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
											<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php
										printf(
											// translators: %d: number of posts
											esc_html( _n( '%d annuncio pubblicato', '%d annunci pubblicati', $author_posts_count, 'caniincasa' ) ),
											$author_posts_count
										);
										?>
									</span>
								</div>
							<?php endif; ?>

							<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="btn btn-secondary btn-block">
								<?php esc_html_e( 'Vedi tutti gli annunci', 'caniincasa' ); ?>
							</a>
						<?php endif; ?>
					</div>

					<!-- Contact Box -->
					<?php if ( ! $is_expired ) : ?>
						<div class="sidebar-box contact-box">
							<h3 class="box-title"><?php esc_html_e( 'Contatta', 'caniincasa' ); ?></h3>

							<p class="contact-description">
								<?php esc_html_e( 'Interessato? Contatta il proprietario dell\'annuncio:', 'caniincasa' ); ?>
							</p>

							<?php if ( $author_phone && caniincasa_is_whatsapp_supported() ) : ?>
								<a href="<?php echo esc_url( caniincasa_get_whatsapp_link( $author_phone, 'Ciao, ho visto il tuo annuncio su Caniincasa.it: ' . get_the_title() ) ); ?>"
								   target="_blank"
								   rel="noopener"
								   class="btn btn-success btn-block">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 5px;">
										<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
									</svg>
									<?php esc_html_e( 'WhatsApp', 'caniincasa' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $author_phone ) : ?>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $author_phone ) ); ?>" class="btn btn-primary btn-block">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
										<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php esc_html_e( 'Chiama Ora', 'caniincasa' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $author_email ) : ?>
								<a href="mailto:<?php echo esc_attr( $author_email ); ?>?subject=<?php echo rawurlencode( 'Re: ' . get_the_title() ); ?>" class="btn btn-primary btn-block">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
										<path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php esc_html_e( 'Invia Email', 'caniincasa' ); ?>
								</a>
							<?php endif; ?>

							<?php
							// Message button only for registered users (not anonymous)
							if ( ! $is_anonymous ) :
								if ( is_user_logged_in() && get_current_user_id() != $author_id ) :
									?>
									<button class="btn btn-send-message btn-secondary btn-block"
										data-recipient-id="<?php echo esc_attr( $author_id ); ?>"
										data-recipient-name="<?php echo esc_attr( caniincasa_get_user_display_name( $author_id ) ); ?>"
										data-post-id="<?php the_ID(); ?>"
										data-post-type="annunci_dogsitter"
										data-subject="<?php echo esc_attr( 'Re: ' . get_the_title() ); ?>">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
											<path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Messaggio Interno', 'caniincasa' ); ?>
									</button>
								<?php elseif ( ! is_user_logged_in() ) : ?>
									<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-secondary btn-block">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 5px;">
											<path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15M10 17L15 12M15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Accedi per Messaggiare', 'caniincasa' ); ?>
									</a>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="sidebar-box notice-box">
							<p class="text-muted">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;">
									<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								</svg>
								<?php esc_html_e( 'Questo annuncio è scaduto e non è più possibile contattare il proprietario.', 'caniincasa' ); ?>
							</p>
						</div>
					<?php endif; ?>

					<!-- Share Box -->
					<div class="sidebar-box share-box">
						<h3 class="box-title"><?php esc_html_e( 'Condividi', 'caniincasa' ); ?></h3>
						<?php caniincasa_social_share(); ?>
					</div>

				</aside>

			</div>
		</div>

		<!-- Related Posts -->
		<?php
		$related_args = array(
			'post_type'      => 'annunci_dogsitter',
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => 'rand',
		);

		// Same tipo
		if ( $tipo ) {
			$related_args['meta_query'] = array(
				array(
					'key'   => 'tipo',
					'value' => $tipo,
				),
			);
		}

		$related_query = new WP_Query( $related_args );

		if ( $related_query->have_posts() ) :
			?>
			<div class="related-annunci">
				<div class="container">
					<h2 class="section-title"><?php esc_html_e( 'Annunci Simili', 'caniincasa' ); ?></h2>
					<div class="annunci-grid">
						<?php
						while ( $related_query->have_posts() ) :
							$related_query->the_post();
							$related_tipo = get_field( 'tipo' );
							?>
							<article class="annuncio-card">
								<div class="annuncio-card-image">
									<a href="<?php the_permalink(); ?>">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'caniincasa-medium' ); ?>
										<?php else : ?>
											<img src="https://www.caniincasa.it/wp-content/uploads/2025/11/image_placeholder.webp" alt="<?php echo esc_attr( get_the_title() ); ?>" class="placeholder-image">
										<?php endif; ?>
									</a>
									<?php if ( $related_tipo ) : ?>
										<span class="annuncio-badge badge-<?php echo esc_attr( $related_tipo === 'offro' ? 'offro' : 'cerco' ); ?>">
											<?php
											if ( $related_tipo === 'offro' ) {
												esc_html_e( 'Offro', 'caniincasa' );
											} else {
													esc_html_e( 'Cerco', 'caniincasa' );
												}
												?>
											</span>
										<?php endif; ?>
								</div>
								<div class="annuncio-card-content">
									<h3 class="annuncio-card-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
									<div class="annuncio-card-meta">
										<?php
										$related_esperienza = get_field( 'esperienza' );
										if ( $related_esperienza ) :
											?>
											<span><?php echo esc_html( ucfirst( $related_esperienza ) ); ?></span>
										<?php endif; ?>
										<span><?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'fa', 'caniincasa' ); ?></span>
									</div>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
				</div>
			</div>
			<?php
			wp_reset_postdata();
		endif;
		?>

	</main>

	<?php
endwhile;

get_footer();
