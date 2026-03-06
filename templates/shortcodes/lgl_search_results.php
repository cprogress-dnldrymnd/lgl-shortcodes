<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Template for rendering the search results grid wrapper.
 * The loop content relies on AJAX injection mapping to partials/result-item.php.
 *
 * @var bool $search Whether the search/topbar UI is enabled. Default true.
 */

// Normalise the attribute — shortcode_atts stores strings, so cast properly
$show_search = ! (strtolower((string) $search) === 'false' || $search === false || $search === '0');
$grid_limit  = $show_search ? 9 : 6;
?>
<div class="lgl-elwg-cars-grid--default lgl-results-wrapper lgl-holder">
    <input type="hidden" name="search" value="<?= esc_attr($search) ?>">
    <?php if (!$show_search) { ?>
        <input type="hidden" name="post_type" id="lgl_target_post_type" value="<?php echo esc_attr($post_type); ?>">
    <?php } ?>
    <div class="lgls-grid-list-template">
        <div class="lgl-filter-scroll-pos"></div>
        <?php if ($show_search) : ?>
            <div class="lgl-topbar">
                <div class="lgl-col-left">
                    <div class="lgl-results-block" id="lgl-results-count">
                        Awaiting Search...
                    </div>
                </div>

                <div class="lgl-col-right">
                    <form class="lgl-filter-form-sortview" id="lgl-sort-form" action="" method="get">
                        <input type="hidden" name="orderby" value="date">
                        <input type="hidden" name="order" value="desc">
                        <div class="lgl-sort-block">
                            <span class="lgl-sort-title">Sort by:</span>
                            <div class="lgl-sort-field">
                                <select name="sort_order" id="lgl-sort-order" class="lgl-select2" style="width: 220px;">
                                    <option value="date_high" selected="selected">Date: newest first</option>
                                    <option value="date_low">Date: oldest first</option>
                                    <option value="mileage_high">Mileage: highest first</option>
                                    <option value="mileage_low">Mileage: lowest first</option>
                                    <option value="price_high">Price: highest first</option>
                                    <option value="price_low">Price: lower first</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="lgl-filter-results">
            <span class="lgl-loading-wave" id="lgl-loader" style="display: none;">Loading...</span>
            <div class="lgl-grid-layout lgl-cols--3 lgl-layout-default" id="lgl-results-grid" data-limit="<?= $grid_limit ?>">
            </div>
            <div class="lgl-pagination-wrap"></div>
        </div>
    </div>
</div>