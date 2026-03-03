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
                <?php if (is_user_logged_in()) { ?>
                    <?php
                    $user_id = get_current_user_id();
                    $wishlist = is_user_logged_in() ? get_user_meta($user_id, 'lgl_wishlists', true) : array();
                    $is_wishlisted = (is_array($wishlist) && in_array($post_id, $wishlist));
                    $wishlist_class = $is_wishlisted ? 'added' : '';
                    ?>
                    <a class="lgl-icon-btn lgl-wishlist-btn <?php echo esc_attr($wishlist_class); ?>" href="#" data-id="<?php echo esc_attr($post_id); ?>" data-title="<?php echo get_the_title(esc_attr($post_id)); ?>">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M14.9916 18.8679C14.3066 19.4224 13.4266 19.7282 12.5129 19.7282C11.6004 19.7282 10.7179 19.4236 10.0054 18.8512C5.51289 15.2466 2.65289 13.3342 2.50414 9.41186C2.34789 5.26103 7.12289 3.74375 10.0504 7.15438C10.6429 7.84341 11.5329 8.2385 12.4929 8.2385C13.4616 8.2385 14.3579 7.83864 14.9516 7.14128C17.8154 3.78539 22.7179 5.21462 22.4941 9.53324C22.2941 13.3747 19.3241 15.3585 14.9916 18.8679ZM12.9841 5.72634C12.8616 5.87033 12.6766 5.94292 12.4929 5.94292C12.3129 5.94292 12.1341 5.87271 12.0141 5.73348C7.58539 0.574693 -0.234601 3.14396 0.0053982 9.49159C0.196648 14.5433 3.95664 17.0471 8.38414 20.5994C9.56788 21.549 11.0404 22.0238 12.5129 22.0238C13.9891 22.0238 15.4641 21.5466 16.6454 20.5898C21.0241 17.0424 24.7366 14.5552 24.9904 9.64154C25.3279 3.1523 17.4016 0.546134 12.9841 5.72634Z"></path>
                        </svg>
                    </a>
                <?php } ?>
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
            include LGL_SHORTCODES_PATH . 'templates/partials/lgl-meta.php';
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