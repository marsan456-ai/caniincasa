<?php
/**
 * Single Template for Storie di Cani
 *
 * @package Caniincasa
 */

if ( ! caniincasa_stories_enabled() ) {
    wp_redirect( home_url() );
    exit;
}

get_header();

while ( have_posts() ) :
    the_post();

    // Increment view counter
    $views = get_post_meta( get_the_ID(), '_storia_views', true );
    update_post_meta( get_the_ID(), '_storia_views', intval( $views ) + 1 );

    // Get story meta
    $dog_name = get_post_meta( get_the_ID(), '_storia_dog_name', true );
    $dog_breed = get_post_meta( get_the_ID(), '_storia_dog_breed', true );
    $dog_age = get_post_meta( get_the_ID(), '_storia_dog_age', true );
    $author_display = get_post_meta( get_the_ID(), '_storia_author_display', true );
    $gallery = get_post_meta( get_the_ID(), '_storia_gallery', true );
    $cat_terms = get_the_terms( get_the_ID(), 'categoria_storia' );
    ?>

    <main id="main-content" class="site-main single-storia">

        <!-- Story Header -->
        <div class="storia-header">
            <div class="container">
                <!-- Breadcrumbs -->
                <div class="breadcrumbs-wrapper">
                    <?php caniincasa_breadcrumbs(); ?>
                </div>

                <?php if ( $cat_terms && ! is_wp_error( $cat_terms ) ) : ?>
                    <div class="storia-categories">
                        <?php foreach ( $cat_terms as $term ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'categoria', $term->slug, get_post_type_archive_link( 'storie_cani' ) ) ); ?>" class="storia-category-tag">
                                <?php echo esc_html( $term->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 class="storia-title"><?php the_title(); ?></h1>

                <div class="storia-meta-header">
                    <?php if ( $author_display !== 'anonymous' ) : ?>
                        <div class="storia-author">
                            <?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
                            <div class="author-info">
                                <span class="author-name"><?php the_author(); ?></span>
                                <span class="publish-date"><?php echo get_the_date(); ?></span>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="storia-author anonymous">
                            <div class="author-info">
                                <span class="author-name">Autore Anonimo</span>
                                <span class="publish-date"><?php echo get_the_date(); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="storia-stats">
                        <span class="stat-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <?php echo intval( $views ) + 1; ?> visualizzazioni
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="storia-content-wrapper">

                <!-- Main Content -->
                <article class="storia-content">

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="storia-featured-image">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Dog Info Card -->
                    <?php if ( $dog_name || $dog_breed || $dog_age ) : ?>
                        <div class="dog-info-card">
                            <div class="dog-info-header">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M4.5 12c-1.5 0-3-1.5-3-3.5S3 5 4.5 5s3 1.5 3 3.5S6 12 4.5 12zm15 0c-1.5 0-3-1.5-3-3.5S18 5 19.5 5s3 1.5 3 3.5-1.5 3.5-3 3.5zM12 21c-4.5 0-9-2-9-6 0-2.5 2-4.5 4.5-4.5.5 0 1 .1 1.5.2C10 12.2 11 13 12 13s2-.8 3-2.3c.5-.1 1-.2 1.5-.2C19 10.5 21 12.5 21 15c0 4-4.5 6-9 6z"/>
                                </svg>
                                <h3>Il Protagonista</h3>
                            </div>
                            <div class="dog-info-details">
                                <?php if ( $dog_name ) : ?>
                                    <div class="dog-detail">
                                        <span class="detail-label">Nome</span>
                                        <span class="detail-value"><?php echo esc_html( $dog_name ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $dog_breed ) : ?>
                                    <div class="dog-detail">
                                        <span class="detail-label">Razza</span>
                                        <span class="detail-value"><?php echo esc_html( $dog_breed ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $dog_age ) : ?>
                                    <div class="dog-detail">
                                        <span class="detail-label">Età</span>
                                        <span class="detail-value"><?php echo esc_html( $dog_age ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Story Content -->
                    <div class="storia-text">
                        <?php the_content(); ?>
                    </div>

                    <!-- Photo Gallery -->
                    <?php if ( $gallery && is_array( $gallery ) ) : ?>
                        <div class="storia-gallery">
                            <h3>Galleria Fotografica</h3>
                            <div class="gallery-grid">
                                <?php foreach ( $gallery as $image_id ) : ?>
                                    <div class="gallery-item">
                                        <a href="<?php echo esc_url( wp_get_attachment_url( $image_id ) ); ?>" data-lightbox="storia-gallery">
                                            <?php echo wp_get_attachment_image( $image_id, 'medium' ); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Share Section -->
                    <div class="storia-share">
                        <h4>Condividi questa storia</h4>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" target="_blank" rel="noopener" class="share-btn share-facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                                </svg>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" rel="noopener" class="share-btn share-twitter">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                                </svg>
                                Twitter
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener" class="share-btn share-whatsapp">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                WhatsApp
                            </a>
                        </div>
                    </div>

                </article>

                <!-- Sidebar -->
                <aside class="storia-sidebar">

                    <!-- CTA Box -->
                    <div class="sidebar-box cta-box">
                        <h3>Hai una storia da raccontare?</h3>
                        <p>Condividi la tua esperienza con il tuo amico a quattro zampe!</p>
                        <?php if ( is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( home_url( '/dashboard/?tab=storie' ) ); ?>" class="btn btn-primary">
                                Racconta la Tua Storia
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( wp_login_url( home_url( '/dashboard/?tab=storie' ) ) ); ?>" class="btn btn-primary">
                                Accedi per Condividere
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Related Stories -->
                    <?php
                    $related_args = array(
                        'post_type'      => 'storie_cani',
                        'post_status'    => 'publish',
                        'posts_per_page' => 3,
                        'post__not_in'   => array( get_the_ID() ),
                    );

                    if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
                        $term_ids = wp_list_pluck( $cat_terms, 'term_id' );
                        $related_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'categoria_storia',
                                'field'    => 'term_id',
                                'terms'    => $term_ids,
                            ),
                        );
                    }

                    $related_query = new WP_Query( $related_args );

                    if ( $related_query->have_posts() ) :
                    ?>
                        <div class="sidebar-box related-stories">
                            <h3>Storie Correlate</h3>
                            <div class="related-list">
                                <?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                                    <a href="<?php the_permalink(); ?>" class="related-item">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <div class="related-thumb">
                                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="related-info">
                                            <h4><?php the_title(); ?></h4>
                                            <span class="related-date"><?php echo get_the_date(); ?></span>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php
                    wp_reset_postdata();
                    endif;
                    ?>

                    <!-- Categories -->
                    <?php
                    $all_categories = get_terms( array(
                        'taxonomy'   => 'categoria_storia',
                        'hide_empty' => true,
                    ) );

                    if ( $all_categories && ! is_wp_error( $all_categories ) ) :
                    ?>
                        <div class="sidebar-box categories-box">
                            <h3>Categorie</h3>
                            <ul class="category-list">
                                <?php foreach ( $all_categories as $cat ) : ?>
                                    <li>
                                        <a href="<?php echo esc_url( add_query_arg( 'categoria', $cat->slug, get_post_type_archive_link( 'storie_cani' ) ) ); ?>">
                                            <?php echo esc_html( $cat->name ); ?>
                                            <span class="count">(<?php echo $cat->count; ?>)</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </aside>

            </div>
        </div>

    </main>

    <?php
endwhile;

get_footer();
