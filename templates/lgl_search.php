<?php

/**
 * Template for rendering the search filter form.
 * * Available variables:
 * @var string $post_type Extracted from shortcode attributes.
 */

$conditions = LGL_Shortcodes::get_unique_meta_values($post_type, 'condition');
$berths     = LGL_Shortcodes::get_unique_meta_values($post_type, 'berth');
$raw_prices = LGL_Shortcodes::get_unique_meta_values($post_type, 'price');

// Sanitize and sort prices numerically to ensure correct sequential display
$prices = array();
if (!empty($raw_prices)) {
    foreach ($raw_prices as $price) {
        if (is_numeric($price)) {
            $prices[] = (float) $price;
        }
    }
}
$prices = array_unique($prices);
sort($prices, SORT_NUMERIC);

// Fetch top-level terms for the 'Make'
$makes = get_terms(array(
    'taxonomy'   => 'listing-make-model',
    'parent'     => 0, // Parent terms only
    'hide_empty' => false
));
?>

<div class="lgl-search-container lgl-holder">
    <form id="lgl-search-form" class="lgl-filter-form">
        <input type="hidden" name="post_type" id="lgl_target_post_type" value="<?php echo esc_attr($post_type); ?>">

        <div class="lgl-filter-group">
            <label for="lgl_make">Make</label>
            <select name="listing_make" id="lgl_make" class="lgl-select2" data-placeholder="Select Make">
                <option value="">All Makes</option>
                <?php foreach ($makes as $make) : ?>
                    <option value="<?php echo esc_attr($make->term_id); ?>"><?php echo esc_html($make->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lgl-filter-group">
            <label for="lgl_model">Model</label>
            <select name="listing_model" id="lgl_model" class="lgl-select2" data-placeholder="Select Model" disabled>
                <option value="">Select Make First</option>
            </select>
        </div>

        <div class="lgl-filter-group">
            <label for="lgl_condition">Condition</label>
            <select name="condition" id="lgl_condition" class="lgl-select2" data-placeholder="Any Condition">
                <option value="">Any Condition</option>
                <?php foreach ($conditions as $cond) : ?>
                    <option value="<?php echo esc_attr($cond); ?>"><?php echo esc_html($cond); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lgl-filter-group">
            <label for="lgl_berth">Berth</label>
            <select name="berth" id="lgl_berth" class="lgl-select2" data-placeholder="Any Berth">
                <option value="">Any Berth</option>
                <?php foreach ($berths as $berth) : ?>
                    <option value="<?php echo esc_attr($berth); ?>"><?php echo esc_html($berth); ?></option>
                <?php endforeach; ?>
            </select>
        </div>



        <div class="lgl-filter-group">
            <select name="price_min" id="lgl_price_min" class="lgl-select2" data-placeholder="Min Price">
                <option value="">Min Price</option>
                <?php foreach ($prices as $price) : ?>
                    <option value="<?php echo esc_attr($price); ?>"><?php echo esc_html('$' . number_format($price, 0)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lgl-filter-group">

            <select name="price_max" id="lgl_price_max" class="lgl-select2" data-placeholder="Max Price">
                <option value="">Max Price</option>
                <?php foreach ($prices as $price) : ?>
                    <option value="<?php echo esc_attr($price); ?>"><?php echo esc_html('$' . number_format($price, 0)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lgl-filter-group lgl-submit-group">
            <button type="submit" class="lgl-search-submit">Search Now</button>
        </div>
    </form>
</div>