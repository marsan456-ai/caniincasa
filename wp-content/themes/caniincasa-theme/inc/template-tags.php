<?php
/**
 * Custom template tags
 *
 * @package Caniincasa
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display post meta information
 */
if ( ! function_exists( 'caniincasa_entry_meta' ) ) {
    function caniincasa_entry_meta() {
        if ( 'post' === get_post_type() ) {
            echo '<div class="entry-meta">';

            // Author
            printf(
                '<span class="author-meta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <a href="%1$s">%2$s</a>
                </span>',
                esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
                esc_html( get_the_author() )
            );

            // Date
            printf(
                '<span class="date-meta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M8 2V5M16 2V5M3 10V19C3 19.5304 3.21071 20.0391 3.58579 20.4142C3.96086 20.7893 4.46957 21 5 21H19C19.5304 21 20.0391 20.7893 20.4142 20.4142C20.7893 20.0391 21 19.5304 21 19V10M3 10H21M3 10V6C3 5.46957 3.21071 4.96086 3.58579 4.58579C3.96086 4.21071 4.46957 4 5 4H19C19.5304 4 20.0391 4.21071 20.4142 4.58579C20.7893 4.96086 21 5.46957 21 6V10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <time datetime="%1$s">%2$s</time>
                </span>',
                esc_attr( get_the_date( 'c' ) ),
                esc_html( get_the_date() )
            );

            // Comments
            if ( comments_open() ) {
                echo '<span class="comments-meta">';
                echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M21 11.5C21 16.7467 16.9706 21 12 21C10.3126 21 8.74081 20.5701 7.40398 19.8127L3 21L4.56075 17.2385C3.56763 15.9929 3 14.4442 3 12.75C3 7.50329 7.02944 3.25 12 3.25C16.9706 3.25 21 7.50329 21 12.75Z" stroke="currentColor" stroke-width="2"/>
                </svg>';
                comments_number(
                    __( '0 commenti', 'caniincasa' ),
                    __( '1 commento', 'caniincasa' ),
                    __( '% commenti', 'caniincasa' )
                );
                echo '</span>';
            }

            echo '</div>';
        }
    }
}

/**
 * Display post categories
 */
if ( ! function_exists( 'caniincasa_entry_categories' ) ) {
    function caniincasa_entry_categories() {
        if ( 'post' === get_post_type() ) {
            $categories_list = get_the_category_list( ' ' );
            if ( $categories_list ) {
                echo '<div class="entry-categories">' . $categories_list . '</div>';
            }
        }
    }
}

/**
 * Display post tags
 */
if ( ! function_exists( 'caniincasa_entry_tags' ) ) {
    function caniincasa_entry_tags() {
        if ( 'post' === get_post_type() ) {
            $tags_list = get_the_tag_list( '', ' ' );
            if ( $tags_list ) {
                echo '<div class="entry-tags">';
                echo '<span class="tags-label">' . esc_html__( 'Tag:', 'caniincasa' ) . '</span>';
                echo $tags_list;
                echo '</div>';
            }
        }
    }
}

/**
 * Display post thumbnail
 */
if ( ! function_exists( 'caniincasa_post_thumbnail' ) ) {
    function caniincasa_post_thumbnail( $size = 'post-thumbnail' ) {
        if ( ! has_post_thumbnail() ) {
            return;
        }

        if ( is_singular() ) {
            ?>
            <div class="post-thumbnail">
                <?php the_post_thumbnail( $size, array( 'loading' => 'eager' ) ); ?>
            </div>
            <?php
        } else {
            ?>
            <a class="post-thumbnail-link" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php
                the_post_thumbnail( $size, array(
                    'alt'     => the_title_attribute( array( 'echo' => false ) ),
                    'loading' => 'lazy',
                ) );
                ?>
            </a>
            <?php
        }
    }
}

/**
 * Display pagination
 */
if ( ! function_exists( 'caniincasa_pagination' ) ) {
    function caniincasa_pagination( $args = array() ) {
        $defaults = array(
            'mid_size'           => 2,
            'prev_text'          => __( '&laquo; Precedente', 'caniincasa' ),
            'next_text'          => __( 'Successivo &raquo;', 'caniincasa' ),
            'screen_reader_text' => __( 'Navigazione articoli', 'caniincasa' ),
        );

        $args = wp_parse_args( $args, $defaults );

        the_posts_pagination( $args );
    }
}

/**
 * Display comments navigation
 */
if ( ! function_exists( 'caniincasa_comment_navigation' ) ) {
    function caniincasa_comment_navigation() {
        if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
            ?>
            <nav class="comment-navigation">
                <div class="nav-previous"><?php previous_comments_link( __( '&laquo; Commenti precedenti', 'caniincasa' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( 'Commenti successivi &raquo;', 'caniincasa' ) ); ?></div>
            </nav>
            <?php
        }
    }
}

/**
 * Display related posts
 */
if ( ! function_exists( 'caniincasa_related_posts' ) ) {
    function caniincasa_related_posts( $post_id = null, $limit = 3 ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        $categories = wp_get_post_categories( $post_id );

        if ( empty( $categories ) ) {
            return;
        }

        $args = array(
            'category__in'   => $categories,
            'post__not_in'   => array( $post_id ),
            'posts_per_page' => $limit,
            'orderby'        => 'rand',
        );

        $related = new WP_Query( $args );

        if ( ! $related->have_posts() ) {
            return;
        }

        ?>
        <div class="related-posts">
            <h3 class="related-posts-title"><?php esc_html_e( 'Articoli Correlati', 'caniincasa' ); ?></h3>
            <div class="related-posts-grid">
                <?php
                while ( $related->have_posts() ) {
                    $related->the_post();
                    ?>
                    <article class="related-post-item">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="related-post-thumbnail">
                                <?php the_post_thumbnail( 'caniincasa-small' ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="related-post-content">
                            <h4 class="related-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <div class="related-post-meta">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>
                        </div>
                    </article>
                    <?php
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Display estimated reading time
 */
if ( ! function_exists( 'caniincasa_display_reading_time' ) ) {
    function caniincasa_display_reading_time() {
        $time = caniincasa_get_reading_time();
        $label = _n( 'minuto di lettura', 'minuti di lettura', $time, 'caniincasa' );

        printf(
            '<span class="reading-time">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2"/>
                </svg>
                %d %s
            </span>',
            $time,
            esc_html( $label )
        );
    }
}

/**
 * Get rating stars HTML
 *
 * @param float $rating Rating value (1-5)
 * @return string HTML markup for rating stars
 */
if ( ! function_exists( 'caniincasa_get_rating_stars' ) ) {
    function caniincasa_get_rating_stars( $rating ) {
        $rating = floatval( $rating );
        $full_stars = floor( $rating );
        $half_star = ( $rating - $full_stars ) >= 0.5 ? 1 : 0;
        $empty_stars = 5 - $full_stars - $half_star;

        $output = '<span class="rating-stars" aria-label="' . sprintf( esc_attr__( 'Valutazione: %s su 5', 'caniincasa' ), number_format( $rating, 1 ) ) . '">';

        // Full stars
        for ( $i = 0; $i < $full_stars; $i++ ) {
            $output .= '<span class="star star-filled" aria-hidden="true">★</span>';
        }

        // Half star
        if ( $half_star ) {
            $output .= '<span class="star star-half" aria-hidden="true">★</span>';
        }

        // Empty stars
        for ( $i = 0; $i < $empty_stars; $i++ ) {
            $output .= '<span class="star star-empty" aria-hidden="true">☆</span>';
        }

        $output .= '</span>';

        return $output;
    }
}
