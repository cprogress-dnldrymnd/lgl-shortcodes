<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Template for rendering the search filter form.
 * Available variables:
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
    'parent'     => 0,
    'hide_empty' => false
));

// -------------------------------------------------------------------
// Read active URL parameters to pre-populate the filter fields
// -------------------------------------------------------------------
$active_make      = isset($_GET['listing_make'])  ? intval($_GET['listing_make'])              : 0;
$active_model     = isset($_GET['listing_model']) ? intval($_GET['listing_model'])              : 0;
$active_condition = isset($_GET['condition'])     ? sanitize_text_field($_GET['condition'])     : '';
$active_berth     = isset($_GET['berth'])         ? sanitize_text_field($_GET['berth'])         : '';
$active_price_min = isset($_GET['price_min'])     ? sanitize_text_field($_GET['price_min'])     : '';
$active_price_max = isset($_GET['price_max'])     ? sanitize_text_field($_GET['price_max'])     : '';

// If a make is active, fetch its child models so the Model dropdown can be pre-populated
$active_make_models = array();
if ($active_make) {
    $active_make_models = get_terms(array(
        'taxonomy'   => 'listing-make-model',
        'parent'     => $active_make,
        'hide_empty' => false,
    ));
}
?>

<div class="lgl-search-container lgl-holder <?= $post_type == false ? 'lgl-search-container-bg-secondary' :  '' ?>">
    <form id="lgl-search-form" class="lgl-filter-form <?= $post_type == false ? 'lgl-filter-form-no-ajax' :  'lgl-filter-form-ajax' ?>">
        <input type="hidden" name="post_type" id="lgl_target_post_type" value="<?php echo esc_attr($post_type); ?>">

        <?php if ($post_type == false) { ?>
            <?php
            //post type select option
            $options = get_option('lgl_settings', array());

            $caravan_page   = $options['caravan_page']   ?? false;
            $motorhome_page = $options['motorhome_page'] ?? false;
            $campervan_page = $options['campervan_page'] ?? false;

            // Build vehicle type options from configured pages
            $vehicle_types = array();
            if ($caravan_page)   $vehicle_types[get_the_permalink($caravan_page)]   = 'Caravan';
            if ($motorhome_page) $vehicle_types[get_the_permalink($motorhome_page)] = 'Motorhome';
            if ($campervan_page) $vehicle_types[get_the_permalink($campervan_page)] = 'Campervan';
            ?>

            <!-- Leisure Vehicle Type -->
            <?php if (!empty($vehicle_types)) : ?>
                <div class="lgl-filter-group">
                    <label for="lgl_vehicle_type">Leisure Vehicle Type</label>
                    <select name="post_type" id="lgl_post_type" class="lgl-select2" data-placeholder="Leisure Vehicle Type" required>
                        <option value="">Leisure Vehicle Type</option>
                        <?php foreach ($vehicle_types as $type_key => $type_label) : ?>
                            <option value="<?php echo esc_attr($type_key); ?>"
                                <?php selected($active_post_type, $type_key); ?>>
                                <?php echo esc_html($type_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        <?php } ?>

        <!-- Make -->
        <div class="lgl-filter-group">
            <label for="lgl_make">Make</label>
            <select name="listing_make" id="lgl_make" class="lgl-select2" data-placeholder="Select Make">
                <option value="">All Makes</option>
                <?php foreach ($makes as $make) : ?>
                    <option value="<?php echo esc_attr($make->term_id); ?>"
                        <?php selected($active_make, $make->term_id); ?>>
                        <?php echo esc_html($make->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Model (pre-populated when a make URL param is active) -->
        <div class="lgl-filter-group">
            <label for="lgl_model">Model</label>
            <select name="listing_model" id="lgl_model" class="lgl-select2" data-placeholder="Select Model"
                <?php echo empty($active_make_models) ? 'disabled' : ''; ?>>
                <?php if (empty($active_make_models)) : ?>
                    <option value="">Select Make First</option>
                <?php else : ?>
                    <option value="">All Models</option>
                    <?php foreach ($active_make_models as $model) : ?>
                        <option value="<?php echo esc_attr($model->term_id); ?>"
                            <?php selected($active_model, $model->term_id); ?>>
                            <?php echo esc_html($model->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <?php if ($post_type != false) { ?>
            <!-- Condition -->
            <div class="lgl-filter-group">
                <label for="lgl_condition">Condition</label>
                <select name="condition" id="lgl_condition" class="lgl-select2" data-placeholder="Any Condition">
                    <option value="">Any Condition</option>
                    <?php foreach ($conditions as $cond) : ?>
                        <option value="<?php echo esc_attr($cond); ?>"
                            <?php selected($active_condition, $cond); ?>>
                            <?php echo esc_html($cond); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Berth -->
            <div class="lgl-filter-group">
                <label for="lgl_berth">Berth</label>
                <select name="berth" id="lgl_berth" class="lgl-select2" data-placeholder="Any Berth">
                    <option value="">Any Berth</option>
                    <?php foreach ($berths as $berth) : ?>
                        <option value="<?php echo esc_attr($berth); ?>"
                            <?php selected($active_berth, $berth); ?>>
                            <?php echo esc_html($berth); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Min Price -->
            <div class="lgl-filter-group">
                <select name="price_min" id="lgl_price_min" class="lgl-select2" data-placeholder="Min Price">
                    <option value="">Min Price</option>
                    <?php foreach ($prices as $price) : ?>
                        <option value="<?php echo esc_attr($price); ?>"
                            <?php selected((float) $active_price_min, $price); ?>>
                            <?php echo esc_html('$' . number_format($price, 0)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Max Price -->
            <div class="lgl-filter-group">
                <select name="price_max" id="lgl_price_max" class="lgl-select2" data-placeholder="Max Price">
                    <option value="">Max Price</option>
                    <?php foreach ($prices as $price) : ?>
                        <option value="<?php echo esc_attr($price); ?>"
                            <?php selected((float) $active_price_max, $price); ?>>
                            <?php echo esc_html('$' . number_format($price, 0)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        <?php } ?>

        <div class="lgl-filter-group lgl-submit-group">
            <button type="submit" class="lgl-search-submit">
                <?php if ($post_type != false) { ?>
                    SEARCH NOW
                <?php } else { ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                <?php } ?>
            </button>
        </div>
    </form>
</div>