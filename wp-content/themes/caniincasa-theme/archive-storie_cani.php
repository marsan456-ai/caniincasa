<?php
/**
 * Archive Template for Storie di Cani
 *
 * @package Caniincasa
 */

if ( ! caniincasa_stories_enabled() ) {
    wp_redirect( home_url() );
    exit;
}

get_header();

// Get categories
$categories = get_terms( array(
    'taxonomy'   => 'categoria_storia',
    'hide_empty' => true,
) );

// Current filter
$current_category = isset( $_GET['categoria'] ) ? sanitize_text_field( $_GET['categoria'] ) : '';
$current_order = isset( $_GET['ordina'] ) ? sanitize_text_field( $_GET['ordina'] ) : 'recenti';

// Build query args
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type'      => 'storie_cani',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
);

if ( $current_category ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'categoria_storia',
            'field'    => 'slug',
            'terms'    => $current_category,
        ),
    );
}

switch ( $current_order ) {
    case 'popolari':
        $args['meta_key'] = '_storia_views';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'recenti':
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

$stories_query = new WP_Query( $args );

// Get featured story (most recent with featured tag or first one)
$featured_story = null;
$featured_args = array(
    'post_type'      => 'storie_cani',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'meta_key'       => '_storia_featured',
    'meta_value'     => '1',
);
$featured_query = new WP_Query( $featured_args );
if ( $featured_query->have_posts() ) {
    $featured_story = $featured_query->posts[0];
}
wp_reset_postdata();
?>

<main id="main-content" class="site-main archive-storie">

    <!-- Archive Header -->
    <div class="archive-header stories-header">
        <div class="container">
            <h1 class="archive-title">Storie di Cani</h1>
            <p class="archive-description">Storie vere raccontate dalla nostra community</p>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/dashboard/?tab=storie' ) ); ?>" class="btn btn-primary">
                    Condividi la Tua Storia
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( wp_login_url( home_url( '/dashboard/?tab=storie' ) ) ); ?>" class="btn btn-primary">
                    Accedi per Condividere
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="container">
        <div class="breadcrumbs-wrapper">
            <?php caniincasa_breadcrumbs(); ?>
        </div>
    </div>

    <div class="container">

        <!-- Filters -->
        <div class="stories-filters">
            <form method="get" class="filter-form">
                <div class="filter-group">
                    <label for="filter-categoria">Categoria:</label>
                    <select name="categoria" id="filter-categoria" onchange="this.form.submit()">
                        <option value="">Tutte</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $current_category, $cat->slug ); ?>>
                                <?php echo esc_html( $cat->name ); ?> (<?php echo $cat->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-ordina">Ordina per:</label>
                    <select name="ordina" id="filter-ordina" onchange="this.form.submit()">
                        <option value="recenti" <?php selected( $current_order, 'recenti' ); ?>>Più recenti</option>
                        <option value="popolari" <?php selected( $current_order, 'popolari' ); ?>>Più popolari</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if ( $featured_story && $paged === 1 && ! $current_category ) : ?>
        <!-- Featured Story -->
        <div class="featured-story">
            <div class="featured-story-inner">
                <?php if ( has_post_thumbnail( $featured_story->ID ) ) : ?>
                    <div class="featured-image">
                        <a href="<?php echo get_permalink( $featured_story->ID ); ?>">
                            <?php echo get_the_post_thumbnail( $featured_story->ID, 'large' ); ?>
                        </a>
                        <span class="featured-badge">Storia in Evidenza</span>
                    </div>
                <?php endif; ?>
                <div class="featured-content">
                    <div class="featured-meta">
                        <?php
                        $cat_terms = get_the_terms( $featured_story->ID, 'categoria_storia' );
                        if ( $cat_terms && ! is_wp_error( $cat_terms ) ) :
                        ?>
                            <span class="story-category"><?php echo esc_html( $cat_terms[0]->name ); ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="featured-title">
                        <a href="<?php echo get_permalink( $featured_story->ID ); ?>">
                            <?php echo esc_html( $featured_story->post_title ); ?>
                        </a>
                    </h2>
                    <div class="featured-excerpt">
                        <?php echo wp_trim_words( $featured_story->post_content, 40, '...' ); ?>
                    </div>
                    <div class="featured-dog-info">
                        <?php
                        $dog_name = get_post_meta( $featured_story->ID, '_storia_dog_name', true );
                        $dog_breed = get_post_meta( $featured_story->ID, '_storia_dog_breed', true );
                        ?>
                        <?php if ( $dog_name ) : ?>
                            <span class="dog-name"><?php echo esc_html( $dog_name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $dog_breed ) : ?>
                            <span class="dog-breed"><?php echo esc_html( $dog_breed ); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo get_permalink( $featured_story->ID ); ?>" class="btn btn-outline">
                        Leggi la Storia
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stories Grid -->
        <?php if ( $stories_query->have_posts() ) : ?>
            <div class="stories-grid">
                <?php while ( $stories_query->have_posts() ) : $stories_query->the_post(); ?>
                    <?php
                    // Skip featured story if showing
                    if ( $featured_story && get_the_ID() === $featured_story->ID && $paged === 1 && ! $current_category ) {
                        continue;
                    }

                    $dog_name = get_post_meta( get_the_ID(), '_storia_dog_name', true );
                    $dog_breed = get_post_meta( get_the_ID(), '_storia_dog_breed', true );
                    $author_display = get_post_meta( get_the_ID(), '_storia_author_display', true );
                    $cat_terms = get_the_terms( get_the_ID(), 'categoria_storia' );
                    ?>
                    <article class="story-card">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="story-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </a>
                                <?php if ( $cat_terms && ! is_wp_error( $cat_terms ) ) : ?>
                                    <span class="story-category-badge"><?php echo esc_html( $cat_terms[0]->name ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="story-content">
                            <h3 class="story-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="story-excerpt">
                                <?php echo wp_trim_words( get_the_content(), 20, '...' ); ?>
                            </div>
                            <div class="story-meta">
                                <?php if ( $dog_name ) : ?>
                                    <span class="story-dog">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M4.5 12c-1.5 0-3-1.5-3-3.5S3 5 4.5 5s3 1.5 3 3.5S6 12 4.5 12zm15 0c-1.5 0-3-1.5-3-3.5S18 5 19.5 5s3 1.5 3 3.5-1.5 3.5-3 3.5zM12 21c-4.5 0-9-2-9-6 0-2.5 2-4.5 4.5-4.5.5 0 1 .1 1.5.2C10 12.2 11 13 12 13s2-.8 3-2.3c.5-.1 1-.2 1.5-.2C19 10.5 21 12.5 21 15c0 4-4.5 6-9 6z"/></svg>
                                        <?php echo esc_html( $dog_name ); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="story-date"><?php echo get_the_date(); ?></span>
                            </div>
                            <?php if ( $author_display !== 'anonymous' ) : ?>
                                <div class="story-author">
                                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
                                    <span><?php the_author(); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ( $stories_query->max_num_pages > 1 ) : ?>
                <div class="stories-pagination">
                    <?php
                    echo paginate_links( array(
                        'total'     => $stories_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => '&laquo; Precedente',
                        'next_text' => 'Successivo &raquo;',
                    ) );
                    ?>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>
            <div class="no-stories">
                <div class="no-stories-icon">📝</div>
                <h3>Nessuna storia trovata</h3>
                <p>Non ci sono ancora storie in questa categoria.</p>
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( home_url( '/dashboard/?tab=storie' ) ); ?>" class="btn btn-primary">
                        Racconta la prima storia!
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</main>

<?php
get_footer();
