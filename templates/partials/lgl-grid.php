<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Standardized partial for a single vehicle result card.
 * Context: Runs within the standard WordPress loop.
 */

$post_id = get_the_ID();
$post_type = get_post_type();
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
                include LGL_SHORTCODES_PATH . 'templates/partials/lgl-button-compare.php';
                ?>
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