<?php
/**
 * Template Name: LGL Custom Single Template
 * Template Post Type: caravan, post, page
 * Description: A customized single post view controlled strictly by the LGL Shortcodes plugin.
 * Author: Digitally Disruptive - Donald Raymundo
 */

if (! defined('ABSPATH')) {
    exit; // Prevent direct access to the file.
}

// Load standard WordPress header
get_header(); 
?>

<main id="lgl-primary" class="lgl-site-main">
    <div class="lgl-holder">
        <?php
        // Standard WordPress Loop
        while (have_posts()) :
            the_post();

            // Fetch dynamic meta data example
            $price = get_post_meta(get_the_ID(), 'price', true);
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('lgl-custom-single-article'); ?>>
                <header class="lgl-entry-header">
                    <?php the_title('<h1 class="lgl-entry-title">', '</h1>'); ?>
                    
                    <?php if (!empty($price)) : ?>
                        <div class="lgl-single-price">
                            <strong>Price:</strong> $<?php echo esc_html(number_format((float)$price, 2)); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="lgl-post-thumbnail">
                    <?php 
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('full');
                    } 
                    ?>
                </div>

                <div class="lgl-entry-content">
                    <?php
                    the_content();
                    
                    // Handle paginated posts (tags)
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'lgl-shortcodes'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>
            </article>

        <?php endwhile; // End of the loop. ?>
    </div>
</main>

<?php
// Load standard WordPress footer
get_footer();