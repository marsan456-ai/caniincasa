<?php
/**
 * Template Name: Larghezza Piena (Senza Sidebar)
 * Template Post Type: page
 *
 * Template per pagine a larghezza piena senza barra laterale.
 * Il contenuto occupa l'intera larghezza del container.
 *
 * @package Caniincasa
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main single-page full-width-page">

	<?php while ( have_posts() ) : the_post(); ?>

		<!-- Hero Section -->
		<div class="page-hero">
			<div class="container">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</div>
		</div>

		<!-- Breadcrumbs -->
		<div class="container">
			<div class="breadcrumbs-wrapper">
				<?php caniincasa_breadcrumbs(); ?>
			</div>
		</div>

		<!-- Content Area (Full Width) -->
		<div class="container">

			<!-- Main Content (Full Width) -->
			<article class="page-full-width-content">

				<!-- Featured Image -->
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="page-featured-image">
						<?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid' ) ); ?>
					</div>
				<?php endif; ?>

				<!-- Page Content -->
				<div class="page-content-body">
					<?php the_content(); ?>
				</div>

				<!-- Page Links for Multi-page Content -->
				<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pagine:', 'caniincasa' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				) );
				?>

			</article>

		</div>

		<!-- Comments (if enabled for pages) -->
		<?php
		if ( comments_open() || get_comments_number() ) :
			?>
			<div class="container">
				<div class="page-comments">
					<?php comments_template(); ?>
				</div>
			</div>
		<?php endif; ?>

	<?php endwhile; ?>

</main>

<?php
get_footer();
