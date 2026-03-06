<?php

/**
 * Template: Compare Duo Shortcode
 * Renders a two-vehicle side-by-side comparison card with a VS badge and Compare Now CTA.
 *
 * Shortcode: [lgl_compare_duo post_id_1="123" post_id_2="456"]
 *
 * @var int $post_id_1  Post ID of the first vehicle.
 * @var int $post_id_2  Post ID of the second vehicle.
 */

if (!defined('ABSPATH')) {
    exit;
}



// Resolve comparison page URL from plugin settings
$options = get_option('lgl_settings', array());
if (!empty($options['disable_compare'])) {
    echo 'Compare is disabled';
    return 'Compare is disabled';
}
$page_id     = isset($options['vehicle_comparison_page_id']) ? intval($options['vehicle_comparison_page_id']) : 0;
$compare_url = ($page_id > 0) ? get_permalink($page_id) : '#';

// Sanitize and validate both post IDs passed via shortcode attributes
$id_1 = isset($post_id_1) ? intval($post_id_1) : 0;
$id_2 = isset($post_id_2) ? intval($post_id_2) : 0;

if (!$id_1 || !$id_2) {
    echo '<p class="lgl-notice">' . esc_html__('Please provide two valid post IDs: [lgl_compare_duo post_id_1="X" post_id_2="Y"]', 'lgl-shortcodes') . '</p>';
    return;
}

$post_1 = get_post($id_1);
$post_2 = get_post($id_2);

if (!$post_1 || !$post_2) {
    echo '<p class="lgl-notice">' . esc_html__('One or both vehicles could not be found.', 'lgl-shortcodes') . '</p>';
    return;
}

/**
 * Helper: extract and format vehicle meta for a given post ID.
 */
$get_vehicle_data = function (int $post_id) {
    return [
        'title'     => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'thumbnail' => get_the_post_thumbnail($post_id, 'large'),
        'condition' => get_post_meta($post_id, 'condition', true),
        'price'     => get_post_meta($post_id, 'price', true),
        'berth'     => get_post_meta($post_id, 'berth', true),
        'year'      => get_post_meta($post_id, 'year', true),
    ];
};

$v1 = $get_vehicle_data($id_1);
$v2 = $get_vehicle_data($id_2);

/**
 * Helper: render a single vehicle column.
 */
$render_vehicle_col = function (array $v) {
    $price_formatted = !empty($v['price']) ? '$' . number_format((float) $v['price'], 0) : '';
?>
    <div class="lgl-compare-duo__vehicle">
        <a href="<?php echo esc_url($v['permalink']); ?>" class="lgl-compare-duo__image-link">
            <div class="lgl-cover-image">
                <?php if ($v['thumbnail']): ?>
                    <?php echo $v['thumbnail']; ?>
                <?php else: ?>
                    <div class="lgl-compare-duo__no-image"></div>
                <?php endif; ?>
            </div>
        </a>

        <div class="lgl-compare-duo__body">
            <?php if (!empty($v['condition'])): ?>
                <span class="lgl-value"><?php echo esc_html($v['condition']); ?></span>
            <?php endif; ?>

            <h3 class="lgl-compare-duo__title">
                <a href="<?php echo esc_url($v['permalink']); ?>">
                    <?php echo esc_html($v['title']); ?>
                </a>
            </h3>

            <?php if ($price_formatted): ?>
                <p class="lgl-compare-duo__price"><?php echo esc_html($price_formatted); ?></p>
            <?php endif; ?>

            <div class="lgl-compare-duo__meta">
                <?php if (!empty($v['berth'])): ?>
                    <div class="lgl-compare-duo__meta-item">
                        <?php
                        echo LGL_Shortcodes::render_inline_svg('berth');
                        ?>
                        <span class="lgl-compare-duo__meta-label-value">
                            <span class="lgl-compare-duo__meta-label"><?php esc_html_e('Berth', 'lgl-shortcodes'); ?></span>
                            <span class="lgl-compare-duo__meta-value"><?php echo esc_html($v['berth']); ?></span>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($v['year'])): ?>
                    <div class="lgl-compare-duo__meta-item">
                        <?php
                        echo LGL_Shortcodes::render_inline_svg('year');
                        ?>
                        <span class="lgl-compare-duo__meta-label-value">
                            <span class="lgl-compare-duo__meta-label"><?php esc_html_e('Year', 'lgl-shortcodes'); ?></span>
                            <span class="lgl-compare-duo__meta-value"><?php echo esc_html($v['year']); ?></span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
};
?>

<div class="lgl-compare-duo">

    <div class="lgl-compare-duo__card">

        <div class="lgl-compare-duo__vehicles">
            <?php $render_vehicle_col($v1); ?>

            <div class="lgl-compare-duo__vs" aria-hidden="true">
                <span>vs</span>
            </div>

            <?php $render_vehicle_col($v2); ?>
        </div>
        <div class=" lgl-post--readmore">
            <a href="<?php echo esc_url(add_query_arg(['compare' => implode(',', [$id_1, $id_2])], $compare_url)); ?>">
                <?php esc_html_e('Compare Now', 'lgl-shortcodes'); ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 12H20M20 12L16 8M20 12L16 16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>


    </div>

</div>