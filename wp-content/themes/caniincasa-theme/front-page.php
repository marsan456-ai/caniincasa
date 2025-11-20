<?php
/**
 * Template for Homepage
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main homepage">

	<!-- Hero Section -->
	<?php
	// Get all hero background images for carousel
	$hero_images = array();
	$hero_bg_1 = get_theme_mod( 'hero_background_image', '' );
	if ( $hero_bg_1 ) {
		$hero_images[] = $hero_bg_1;
	}
	for ( $i = 2; $i <= 5; $i++ ) {
		$hero_bg = get_theme_mod( 'hero_background_image_' . $i, '' );
		if ( $hero_bg ) {
			$hero_images[] = $hero_bg;
		}
	}
	$carousel_speed = get_theme_mod( 'hero_carousel_speed', 5 );
	$has_carousel = count( $hero_images ) > 1;
	?>
	<section class="hero-section" data-carousel-speed="<?php echo esc_attr( $carousel_speed ); ?>" data-has-carousel="<?php echo $has_carousel ? 'true' : 'false'; ?>">
		<!-- Background Carousel Images -->
		<?php if ( ! empty( $hero_images ) ) : ?>
			<div class="hero-backgrounds">
				<?php foreach ( $hero_images as $index => $image_url ) : ?>
					<div class="hero-bg" data-index="<?php echo esc_attr( $index ); ?>" style="background-image: url('<?php echo esc_url( $image_url ); ?>');" <?php echo $index === 0 ? 'data-active="true"' : ''; ?>></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="hero-overlay"></div>
		<div class="hero-content">
			<div class="container">
				<h1 class="hero-title">
					<?php echo get_theme_mod( 'hero_title', 'Il tuo portale cinofilo di riferimento' ); ?>
				</h1>
				<p class="hero-subtitle">
					<?php echo get_theme_mod( 'hero_subtitle', 'Scopri razze, trova allevamenti, adotta un amico a quattro zampe' ); ?>
				</p>
				<div class="hero-cta-buttons">
					<a href="<?php echo esc_url( get_theme_mod( 'hero_button1_url', home_url( '/razze-di-cani/' ) ) ); ?>" class="btn btn-primary btn-large">
						<?php echo esc_html( get_theme_mod( 'hero_button1_text', 'Esplora le Razze' ) ); ?>
					</a>
					<a href="<?php echo esc_url( get_theme_mod( 'hero_button2_url', home_url( '/annunci/' ) ) ); ?>" class="btn btn-secondary btn-large">
						<?php echo esc_html( get_theme_mod( 'hero_button2_text', 'Vedi Annunci' ) ); ?>
					</a>
					<a href="<?php echo esc_url( get_theme_mod( 'hero_button3_url', '#quiz-section' ) ); ?>" class="btn btn-accent btn-large smooth-scroll">
						<?php echo esc_html( get_theme_mod( 'hero_button3_text', 'Fai il Quiz' ) ); ?>
					</a>
				</div>

				<!-- Quick Search -->
				<div class="hero-search">
					<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="search-wrapper">
							<input type="search"
							       class="search-field"
							       placeholder="<?php esc_attr_e( 'Cerca una razza, un allevamento o un annuncio...', 'caniincasa' ); ?>"
							       value="<?php echo get_search_query(); ?>"
							       name="s" />
							<button type="submit" class="search-submit">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span class="screen-reader-text"><?php esc_html_e( 'Cerca', 'caniincasa' ); ?></span>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Scroll Indicator -->
		<div class="scroll-indicator">
			<a href="#annunci-section" class="smooth-scroll">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
					<path d="M19 14l-7 7m0 0l-7-7m7 7V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
		</div>
	</section>

	<!-- Annunci 4 Zampe Section -->
	<section id="annunci-section" class="annunci-section">
		<div class="container">
			<div class="section-header">
				<h2 class="section-title">
					<span class="icon">🐾</span>
					<?php echo esc_html( get_theme_mod( 'annunci_title', 'Annunci Amici 4 Zampe' ) ); ?>
				</h2>
				<p class="section-subtitle">
					<?php echo esc_html( get_theme_mod( 'annunci_subtitle', 'Trova il tuo prossimo compagno di avventure' ) ); ?>
				</p>
			</div>

			<!-- Quick Filters -->
			<div class="quick-filters">
				<button class="filter-btn active" data-filter="all">
					<?php esc_html_e( 'Tutti', 'caniincasa' ); ?>
				</button>
				<button class="filter-btn" data-filter="offro">
					<?php esc_html_e( 'In Adozione', 'caniincasa' ); ?>
				</button>
				<button class="filter-btn" data-filter="cerco">
					<?php esc_html_e( 'Cercano Casa', 'caniincasa' ); ?>
				</button>
				<button class="filter-btn" data-filter="cucciolo">
					<?php esc_html_e( 'Cuccioli', 'caniincasa' ); ?>
				</button>
			</div>

			<!-- Annunci Grid -->
			<div class="annunci-grid">
				<?php
				$annunci_args = array(
					'post_type'      => 'annunci_4zampe',
					'posts_per_page' => 6,
					'post_status'    => 'publish',
					'orderby'        => 'date',
					'order'          => 'DESC',
				);
				$annunci_query = new WP_Query( $annunci_args );

				if ( $annunci_query->have_posts() ) :
					while ( $annunci_query->have_posts() ) :
						$annunci_query->the_post();
						$tipo_annuncio = get_field( 'tipo_annuncio' );
						$eta = get_field( 'eta' );
						$tipo_cane = get_field( 'tipo_cane' );
						?>
						<article class="annuncio-card" data-tipo="<?php echo esc_attr( $tipo_annuncio ); ?>" data-eta="<?php echo esc_attr( $eta ); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="annuncio-thumbnail">
									<?php the_post_thumbnail( 'medium' ); ?>
									<?php if ( $tipo_annuncio ) : ?>
										<span class="annuncio-badge <?php echo esc_attr( $tipo_annuncio ); ?>">
											<?php echo esc_html( ucfirst( $tipo_annuncio ) ); ?>
										</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="annuncio-content">
								<h3 class="annuncio-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<div class="annuncio-meta">
									<?php if ( $eta ) : ?>
										<span class="meta-item">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
												<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
												<path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
											</svg>
											<?php echo esc_html( ucfirst( $eta ) ); ?>
										</span>
									<?php endif; ?>
									<?php if ( $tipo_cane ) : ?>
										<span class="meta-item">
											<?php echo esc_html( ucfirst( $tipo_cane ) ); ?>
										</span>
									<?php endif; ?>
								</div>
								<?php if ( has_excerpt() ) : ?>
									<p class="annuncio-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>" class="btn btn-small btn-primary">
									<?php esc_html_e( 'Vedi Dettagli', 'caniincasa' ); ?>
								</a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="no-annunci"><?php esc_html_e( 'Nessun annuncio disponibile al momento.', 'caniincasa' ); ?></p>
				<?php endif; ?>
			</div>

			<!-- CTA -->
			<div class="section-cta">
				<a href="<?php echo esc_url( home_url( '/annunci/' ) ); ?>" class="btn btn-primary btn-large">
					<?php esc_html_e( 'Vedi Tutti gli Annunci', 'caniincasa' ); ?>
				</a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( home_url( '/inserisci-annuncio/' ) ); ?>" class="btn btn-secondary btn-large">
						<?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
					</a>
				<?php else : ?>
					<button type="button" class="btn btn-secondary btn-large js-open-annuncio-modal">
						<?php esc_html_e( 'Inserisci Annuncio', 'caniincasa' ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Database Razze Section -->
	<section class="razze-section">
		<div class="container">
			<div class="section-header">
				<h2 class="section-title">
					<span class="icon">🐕</span>
					<?php echo esc_html( get_theme_mod( 'razze_title', 'Esplora il Database Razze' ) ); ?>
				</h2>
				<p class="section-subtitle">
					<?php echo esc_html( get_theme_mod( 'razze_subtitle', 'Oltre 400 razze di cani con schede complete' ) ); ?>
				</p>
			</div>

			<!-- Razze del Giorno -->
			<div class="razze-featured">
				<h3 class="subsection-title"><?php esc_html_e( 'Razze Popolari', 'caniincasa' ); ?></h3>
				<div class="razze-carousel">
					<?php
					$razze_args = array(
						'post_type'      => 'razze_di_cani',
						'posts_per_page' => 8,
						'orderby'        => 'rand',
					);
					$razze_query = new WP_Query( $razze_args );

					if ( $razze_query->have_posts() ) :
						while ( $razze_query->have_posts() ) :
							$razze_query->the_post();
							$affettuosita = get_field( 'affettuosita' );
							$energia = get_field( 'energia_e_livelli_di_attivita' );
							$taglia_terms = get_the_terms( get_the_ID(), 'razza_taglia' );
							?>
							<article class="razza-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="razza-thumbnail">
										<a href="<?php the_permalink(); ?>">
											<?php the_post_thumbnail( 'medium' ); ?>
										</a>
									</div>
								<?php endif; ?>
								<div class="razza-content">
									<h4 class="razza-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h4>
									<?php if ( $taglia_terms && ! is_wp_error( $taglia_terms ) ) : ?>
										<span class="razza-taglia">
											<?php echo esc_html( $taglia_terms[0]->name ); ?>
										</span>
									<?php endif; ?>
									<div class="razza-quick-stats">
										<?php if ( $affettuosita ) : ?>
											<div class="stat-item">
												<span class="stat-label">❤️</span>
												<span class="stat-value"><?php echo number_format( $affettuosita, 1 ); ?></span>
											</div>
										<?php endif; ?>
										<?php if ( $energia ) : ?>
											<div class="stat-item">
												<span class="stat-label">⚡</span>
												<span class="stat-value"><?php echo number_format( $energia, 1 ); ?></span>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					<?php endif; ?>
				</div>
			</div>

			<!-- Quick Search by Taglia -->
			<div class="razze-by-taglia">
				<div class="taglia-grid">
					<?php
					$taglie = get_terms( array(
						'taxonomy' => 'razza_taglia',
						'hide_empty' => true,
					) );
					if ( ! empty( $taglie ) && ! is_wp_error( $taglie ) ) :
						foreach ( $taglie as $taglia ) :
							$count = $taglia->count;
							?>
							<a href="<?php echo esc_url( get_term_link( $taglia ) ); ?>" class="taglia-card">
								<span class="taglia-name"><?php echo esc_html( $taglia->name ); ?></span>
								<span class="taglia-count"><?php echo esc_html( $count ); ?> <?php esc_html_e( 'razze', 'caniincasa' ); ?></span>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<!-- CTA -->
			<div class="section-cta">
				<a href="<?php echo esc_url( home_url( '/razze-di-cani/' ) ); ?>" class="btn btn-primary btn-large">
					<?php esc_html_e( 'Esplora Tutte le Razze', 'caniincasa' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Quiz Section -->
	<section id="quiz-section" class="quiz-section">
		<div class="container">
			<div class="quiz-content-wrapper">
				<div class="quiz-info">
					<h2 class="section-title">
						<span class="icon">🎯</span>
						<?php echo esc_html( get_theme_mod( 'quiz_title', 'Trova la Razza Perfetta per Te' ) ); ?>
					</h2>
					<p class="quiz-description">
						<?php echo esc_html( get_theme_mod( 'quiz_description', 'Rispondi a 9 semplici domande e scopri quali razze sono più compatibili con il tuo stile di vita. Il nostro algoritmo analizzerà le tue risposte e ti suggerirà le razze ideali.' ) ); ?>
					</p>

					<!-- Quiz Stats -->
					<div class="quiz-stats">
						<div class="stat-box">
							<span class="stat-number">1200+</span>
							<span class="stat-label"><?php esc_html_e( 'Utenti hanno trovato la loro razza', 'caniincasa' ); ?></span>
						</div>
						<div class="stat-box">
							<span class="stat-number">9</span>
							<span class="stat-label"><?php esc_html_e( 'Domande veloci', 'caniincasa' ); ?></span>
						</div>
						<div class="stat-box">
							<span class="stat-number">400+</span>
							<span class="stat-label"><?php esc_html_e( 'Razze analizzate', 'caniincasa' ); ?></span>
						</div>
					</div>

					<!-- Recent Results Preview -->
					<div class="recent-results">
						<h4><?php esc_html_e( 'Razze più visitate nelle ultime 24h:', 'caniincasa' ); ?></h4>
						<div class="results-preview">
							<?php
							// Top 3 razze più viste (simulato per ora)
							$top_razze = get_posts( array(
								'post_type' => 'razze_di_cani',
								'posts_per_page' => 3,
								'orderby' => 'rand',
							) );
							foreach ( $top_razze as $razza ) :
								?>
								<a href="<?php echo esc_url( get_permalink( $razza->ID ) ); ?>" class="result-tag">
									<?php echo esc_html( $razza->post_title ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- CTA Button -->
					<a href="<?php echo esc_url( get_theme_mod( 'quiz_button_url', home_url( '/quiz-razza/' ) ) ); ?>" class="btn btn-accent btn-large btn-quiz">
						<?php echo esc_html( get_theme_mod( 'quiz_button_text', 'Inizia il Quiz' ) ); ?>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
							<path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				</div>

				<!-- Quiz Visual -->
				<div class="quiz-visual">
					<div class="quiz-illustration">
						<?php
						$quiz_illustration = get_theme_mod( 'quiz_illustration' );
						if ( $quiz_illustration ) :
							?>
							<img src="<?php echo esc_url( $quiz_illustration ); ?>"
							     alt="<?php esc_attr_e( 'Quiz Illustrazione', 'caniincasa' ); ?>">
						<?php else : ?>
							<!-- Placeholder per illustrazione/immagine quiz -->
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/quiz-illustration.svg' ); ?>"
							     alt="<?php esc_attr_e( 'Quiz Illustrazione', 'caniincasa' ); ?>"
							     onerror="this.style.display='none'">
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Blog/Articles Section -->
	<section class="blog-section">
		<div class="container">
			<div class="section-header">
				<h2 class="section-title">
					<span class="icon">📰</span>
					<?php esc_html_e( 'Guide e Consigli', 'caniincasa' ); ?>
				</h2>
				<p class="section-subtitle">
					<?php esc_html_e( 'I nostri articoli più recenti per prenderti cura del tuo cane', 'caniincasa' ); ?>
				</p>
			</div>

			<div class="blog-grid">
				<?php
				$blog_args = array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'orderby'        => 'date',
					'order'          => 'DESC',
				);
				$blog_query = new WP_Query( $blog_args );

				if ( $blog_query->have_posts() ) :
					while ( $blog_query->have_posts() ) :
						$blog_query->the_post();
						?>
						<article class="blog-card">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="blog-thumbnail">
									<a href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'medium' ); ?>
									</a>
								</div>
							<?php endif; ?>
							<div class="blog-content">
								<div class="blog-meta">
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
										<?php echo esc_html( get_the_date() ); ?>
									</time>
									<?php
									$categories = get_the_category();
									if ( ! empty( $categories ) ) :
										?>
										<span class="category"><?php echo esc_html( $categories[0]->name ); ?></span>
									<?php endif; ?>
								</div>
								<h3 class="blog-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<?php if ( has_excerpt() ) : ?>
									<p class="blog-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>" class="read-more">
									<?php esc_html_e( 'Leggi di più', 'caniincasa' ); ?>
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
										<path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'Nessun articolo disponibile al momento.', 'caniincasa' ); ?></p>
				<?php endif; ?>
			</div>

			<!-- Categories Links -->
			<div class="blog-categories">
				<h4><?php esc_html_e( 'Esplora per Categoria:', 'caniincasa' ); ?></h4>
				<div class="categories-list">
					<?php
					$categories = get_categories( array(
						'number' => 6,
						'orderby' => 'count',
						'order' => 'DESC',
					) );
					foreach ( $categories as $category ) :
						?>
						<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="category-tag">
							<?php echo esc_html( $category->name ); ?>
							<span class="count">(<?php echo esc_html( $category->count ); ?>)</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- CTA -->
			<div class="section-cta">
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn-primary btn-large">
					<?php esc_html_e( 'Leggi Tutti gli Articoli', 'caniincasa' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Call to Action Final -->
	<?php if ( ! is_user_logged_in() ) : ?>
	<section class="cta-final-section">
		<div class="container">
			<div class="cta-content">
				<h2><?php esc_html_e( 'Pronto a trovare il tuo amico a quattro zampe?', 'caniincasa' ); ?></h2>
				<p><?php esc_html_e( 'Registrati gratuitamente e accedi a tutte le funzionalità del portale', 'caniincasa' ); ?></p>
				<div class="cta-buttons">
					<button type="button" class="btn btn-primary btn-large js-open-annuncio-modal">
						<?php esc_html_e( 'Registrati Gratis', 'caniincasa' ); ?>
					</button>
					<a href="<?php echo esc_url( wp_login_url( home_url() ) ); ?>" class="btn btn-secondary btn-large">
						<?php esc_html_e( 'Accedi', 'caniincasa' ); ?>
					</a>
					<button type="button" class="btn btn-accent btn-large" id="open-newsletter-modal">
						<?php esc_html_e( 'Iscriviti alla Newsletter', 'caniincasa' ); ?>
					</button>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Newsletter Modal -->
	<div id="newsletter-modal" class="modal newsletter-modal" style="display: none;">
		<div class="modal-overlay"></div>
		<div class="modal-content">
			<button type="button" class="modal-close" aria-label="Chiudi">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
					<path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<div class="modal-header">
				<h2><?php esc_html_e( 'Iscriviti alla Newsletter', 'caniincasa' ); ?></h2>
				<p><?php esc_html_e( 'Ricevi aggiornamenti su razze, annunci e consigli per il tuo amico a quattro zampe', 'caniincasa' ); ?></p>
			</div>

			<div class="modal-body">
				<form id="newsletter-form" class="newsletter-form">
					<div id="newsletter-messages" class="form-messages" style="display: none;"></div>

					<div class="form-group">
						<label for="newsletter-name"><?php esc_html_e( 'Nome', 'caniincasa' ); ?></label>
						<input type="text" id="newsletter-name" name="newsletter_name" class="form-control" placeholder="Il tuo nome">
					</div>

					<div class="form-group">
						<label for="newsletter-email"><?php esc_html_e( 'Email *', 'caniincasa' ); ?></label>
						<input type="email" id="newsletter-email" name="newsletter_email" class="form-control" required placeholder="tua@email.com">
					</div>

					<div class="form-group form-checkbox">
						<input type="checkbox" id="newsletter-gdpr" name="newsletter_gdpr" required>
						<label for="newsletter-gdpr">
							<?php esc_html_e( 'Acconsento al trattamento dei miei dati personali secondo la ', 'caniincasa' ); ?>
							<a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>" target="_blank"><?php esc_html_e( 'Privacy Policy', 'caniincasa' ); ?></a> *
						</label>
					</div>

					<div class="form-group form-checkbox">
						<input type="checkbox" id="newsletter-marketing" name="newsletter_marketing" required>
						<label for="newsletter-marketing">
							<?php esc_html_e( 'Acconsento a ricevere comunicazioni commerciali e newsletter', 'caniincasa' ); ?> *
						</label>
					</div>

					<?php wp_nonce_field( 'newsletter_subscribe', 'newsletter_nonce' ); ?>

					<button type="submit" class="btn btn-primary btn-lg btn-block">
						<span class="btn-text"><?php esc_html_e( 'Iscriviti', 'caniincasa' ); ?></span>
						<span class="btn-loading" style="display: none;">
							<svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
							</svg>
							<?php esc_html_e( 'Iscrizione...', 'caniincasa' ); ?>
						</span>
					</button>
				</form>
			</div>
		</div>
	</div>

</main>

<?php
get_footer();
