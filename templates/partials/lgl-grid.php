<?php

/**
 * Standardized partial for a single vehicle result card.
 * Context: Runs within the standard WordPress loop.
 */

$post_id = get_the_ID();
$condition = get_post_meta($post_id, 'condition', true);
$price = get_post_meta($post_id, 'price', true);
$berth = get_post_meta($post_id, 'berth', true);
$mileage = get_post_meta($post_id, 'mileage', true);
$year = get_post_meta($post_id, 'year', true);
$link = get_the_permalink();
$title = get_the_title();


?>

<article <?php post_class('lgl-post car type-car status-publish has-post-thumbnail hentry'); ?>>
    <div class="lgl-post--inner">
        <div class="lgl-post--thumbnail">
            <div class="lgl-post--featured">
                <a href="<?php echo esc_url($link); ?>">
                    <div class="lgl-cover-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else: ?>
                            <img src="" alt="Placeholder">
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <div class="lgl-post--icon-btn">
                <?php
                include LGL_SHORTCODES_PATH . 'templates/partials/lgl-button-wishlist.php';
                ?>
                <a class="lgl-icon-btn lgl-compare-btn" href="#" data-id="<?php echo esc_attr($post_id); ?>">
                    <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.75 10.9375L17.3125 12.5L22.6875 7.10938L17.1875 1.5625L15.625 3.125L18.4375 5.9375H3.125V8.125H18.4688L15.75 10.9375ZM9.15625 14.0625L7.59375 12.5L2.21875 17.9688L7.67187 23.4375L9.23437 21.875L6.40625 19.0625H21.875V16.875H6.40625L9.15625 14.0625Z"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="lgl-post--infor">
            <div class="lgl-post--body">
                <span class="lgl-value"><?php echo esc_html($condition); ?></span>
            </div>

            <div class="lgl-post--info-inner">
                <div class="lgl-post--price">
                    $<?php echo esc_html(number_format((float)$price, 2)); ?>
                </div>
                <h3 class="lgl-post--title">
                    <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
                </h3>
            </div>

            <?php
            include LGL_SHORTCODES_PATH . 'templates/partials/lgl-meta-short.php';
            ?>

            <div class="lgl-post--readmore">
                <a href="<?php echo esc_url($link); ?>">
                    View Details
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 12H20M20 12L16 8M20 12L16 16" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</article>