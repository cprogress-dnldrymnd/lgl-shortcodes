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

<article <?php post_class('bt-post car type-car status-publish has-post-thumbnail hentry'); ?>>
    <div class="bt-post--inner">
        <div class="bt-post--thumbnail">
            <div class="bt-post--featured">
                <a href="<?php echo esc_url($link); ?>">
                    <div class="bt-cover-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else: ?>
                            <img src="" alt="Placeholder">
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <div class="bt-post--icon-btn">
                <a class="bt-icon-btn bt-car-wishlist-btn" href="#" data-id="<?php echo esc_attr($post_id); ?>">
                    </a>
                <a class="bt-icon-btn bt-car-compare-btn" href="#" data-id="<?php echo esc_attr($post_id); ?>">
                    </a>
            </div>
        </div>

        <div class="bt-post--infor">
            <div class="bt-post--body">
                <span class="bt-value"><?php echo esc_html($condition); ?></span>
            </div>
    
            <div class="bt-post--info-inner">
                <div class="bt-post--price">
                    $<?php echo esc_html(number_format((float)$price, 2)); ?>
                </div>
                <h3 class="bt-post--title">
                    <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a>
                </h3>
            </div>

            <div class="bt-post--meta">
                <div class="bt-post--meta-row">
                    <div class="bt-post--meta-col">
                        <div class="bt-post--meta-item bt-post--fuel-type">
                            <span class="bt-label">Berth</span><span class="bt-value"><?php echo esc_html($berth ? $berth : 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="bt-post--meta-col">
                        <div class="bt-post--meta-item bt-post--mileage">
                            <span class="bt-label">Year</span><span class="bt-value"><?php echo esc_html($year ? $year : 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="bt-post--meta-col">
                        <div class="bt-post--meta-item bt-post--transmission">
                            <span class="bt-label">Mileage</span><span class="bt-value"><?php echo esc_html($mileage ? $mileage : 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bt-post--readmore">
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