<?php
/**
 * Custom template tags for Monomyth FSE
 *
 * @package Monomyth_FSE
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Prints HTML with meta information for the current post-date/time.
 */
function monomyth_posted_on() {
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    
    if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr( get_the_date( DATE_W3C ) ),
        esc_html( get_the_date() ),
        esc_attr( get_the_modified_date( DATE_W3C ) ),
        esc_html( get_the_modified_date() )
    );

    printf(
        '<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
        esc_html_x( 'Posted on', 'post date', 'monomyth-fse' ),
        esc_url( get_permalink() ),
        $time_string
    );
}

/**
 * Prints HTML with meta information for the current author.
 */
function monomyth_posted_by() {
    printf(
        '<span class="byline">%1$s <span class="author vcard"><a class="url fn n" href="%2$s">%3$s</a></span></span>',
        esc_html_x( 'by', 'post author', 'monomyth-fse' ),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        esc_html( get_the_author() )
    );
}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function monomyth_entry_footer() {
    // Hide category and tag text for pages.
    if ( 'post' === get_post_type() ) {
        $categories_list = get_the_category_list( esc_html__( ', ', 'monomyth-fse' ) );
        if ( $categories_list ) {
            printf(
                '<span class="cat-links">%1$s %2$s</span>',
                esc_html_x( 'Posted in', 'Used before category names.', 'monomyth-fse' ),
                $categories_list
            );
        }

        $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'monomyth-fse' ) );
        if ( $tags_list ) {
            printf(
                '<span class="tags-links">%1$s %2$s</span>',
                esc_html_x( 'Tagged', 'Used before tag names.', 'monomyth-fse' ),
                $tags_list
            );
        }
    }

    if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
        echo '<span class="comments-link">';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'monomyth-fse' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post( get_the_title() )
            )
        );
        echo '</span>';
    }

    edit_post_link(
        sprintf(
            wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                __( 'Edit <span class="screen-reader-text">%s</span>', 'monomyth-fse' ),
                array(
                    'span' => array(
                        'class' => array(),
                    ),
                )
            ),
            wp_kses_post( get_the_title() )
        ),
        '<span class="edit-link">',
        '</span>'
    );
}

/**
 * Displays an optional post thumbnail.
 */
function monomyth_post_thumbnail( $size = 'post-thumbnail' ) {
    if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
        return;
    }

    if ( is_singular() ) {
        ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail( $size ); ?>
        </div>
        <?php
    } else {
        ?>
        <a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
            <?php
            the_post_thumbnail(
                $size,
                array(
                    'alt' => the_title_attribute(
                        array(
                            'echo' => false,
                        )
                    ),
                )
            );
            ?>
        </a>
        <?php
    }
}

/**
 * Check if the current page is using a specific template.
 *
 * @param string $template Template name to check.
 * @return bool
 */
function monomyth_is_template( $template ) {
    return is_page_template( "templates/{$template}.html" );
}

/**
 * Get the reading time for a post.
 *
 * @param int $post_id Optional. Post ID. Default is current post.
 * @return string Reading time in minutes.
 */
function monomyth_reading_time( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    $content = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $reading_time = ceil( $word_count / 200 ); // Average reading speed: 200 words per minute

    return sprintf(
        /* translators: %d: number of minutes */
        _n( '%d min read', '%d min read', $reading_time, 'monomyth-fse' ),
        $reading_time
    );
}

/**
 * Output breadcrumbs.
 */
function monomyth_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    $separator = ' <span class="breadcrumb-separator">/</span> ';
    
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'monomyth-fse' ) . '">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'monomyth-fse' ) . '</a>';
    
    if ( is_category() || is_single() ) {
        echo $separator;
        the_category( ', ' );
        if ( is_single() ) {
            echo $separator;
            the_title();
        }
    } elseif ( is_page() ) {
        echo $separator;
        the_title();
    } elseif ( is_search() ) {
        echo $separator;
        printf( esc_html__( 'Search Results for: %s', 'monomyth-fse' ), get_search_query() );
    } elseif ( is_404() ) {
        echo $separator;
        esc_html_e( 'Page Not Found', 'monomyth-fse' );
    } elseif ( is_archive() ) {
        echo $separator;
        if ( is_day() ) {
            echo get_the_date();
        } elseif ( is_month() ) {
            echo get_the_date( 'F Y' );
        } elseif ( is_year() ) {
            echo get_the_date( 'Y' );
        } else {
            esc_html_e( 'Archives', 'monomyth-fse' );
        }
    }
    
    echo '</nav>';
}

/**
 * Output social sharing links.
 *
 * @param int $post_id Optional. Post ID. Default is current post.
 */
function monomyth_social_share( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    $url = urlencode( get_permalink( $post_id ) );
    $title = urlencode( get_the_title( $post_id ) );

    $networks = array(
        'twitter'  => array(
            'url'   => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
            'label' => __( 'Share on Twitter', 'monomyth-fse' ),
        ),
        'facebook' => array(
            'url'   => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'label' => __( 'Share on Facebook', 'monomyth-fse' ),
        ),
        'linkedin' => array(
            'url'   => "https://www.linkedin.com/shareArticle?mini=true&url={$url}&title={$title}",
            'label' => __( 'Share on LinkedIn', 'monomyth-fse' ),
        ),
    );

    echo '<div class="social-share">';
    echo '<span class="social-share-label">' . esc_html__( 'Share:', 'monomyth-fse' ) . '</span>';
    
    foreach ( $networks as $network => $data ) {
        printf(
            '<a href="%1$s" class="social-share-link social-share-%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%2$s</a>',
            esc_url( $data['url'] ),
            esc_attr( $network ),
            esc_attr( $data['label'] )
        );
    }
    
    echo '</div>';
}
