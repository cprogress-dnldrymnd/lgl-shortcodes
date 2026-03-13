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

// Fetch makes filtered to only those with published posts of the current post_type.
// When post_type is false (global search form), all top-level makes are returned as
// the vehicle type hasn't been chosen yet — JS will reload them on type selection.
if ($post_type) {
    $type_post_ids = get_posts(array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    $makes = array();

    if (!empty($type_post_ids)) {
        $assigned_terms = wp_get_object_terms($type_post_ids, 'listing-make-model', array('fields' => 'all'));

        if (!is_wp_error($assigned_terms) && !empty($assigned_terms)) {
            $make_ids = array();
            foreach ($assigned_terms as $term) {
                // Models point to their parent make; top-level terms are already makes
                $make_ids[] = ($term->parent > 0) ? (int) $term->parent : (int) $term->term_id;
            }
            $make_ids = array_unique($make_ids);

            $makes = get_terms(array(
                'taxonomy'   => 'listing-make-model',
                'include'    => $make_ids,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ));

            if (is_wp_error($makes)) {
                $makes = array();
            }
        }
    }
} else {
    // Global search form — no post_type yet, return all top-level makes
    $makes = get_terms(array(
        'taxonomy'   => 'listing-make-model',
        'parent'     => 0,
        'hide_empty' => false,
    ));
}

// Filter active make models to only those belonging to the current post_type as well
if ($post_type && $active_make) {
    $active_make_models = get_terms(array(
        'taxonomy'   => 'listing-make-model',
        'parent'     => $active_make,
        'hide_empty' => false,
    ));

    if (!is_wp_error($active_make_models) && !empty($active_make_models) && !empty($type_post_ids)) {
        $assigned_model_ids = array();
        $assigned_all = wp_get_object_terms($type_post_ids, 'listing-make-model', array('fields' => 'ids'));
        if (!is_wp_error($assigned_all)) {
            $assigned_model_ids = array_map('intval', $assigned_all);
        }

        $active_make_models = array_filter($active_make_models, function ($model) use ($assigned_model_ids) {
            return in_array((int) $model->term_id, $assigned_model_ids, true);
        });
    }
}

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

<style>
/* --- Mobile search toggle (< 1025px) ---------------------------------- */
.lgl-search-mobile-toggle {
    display: none;
    width: 100%;
    padding: 12px 20px;
    background: var(--lgl-color-primary, #003793);
    color: #fff;
    font-family: var(--lgl-font-primary, sans-serif);
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 0;
}

.lgl-search-mobile-toggle svg {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.lgl-search-mobile-toggle.is-open svg {
    transform: rotate(180deg);
}

@media (max-width: 1024px) {
    .lgl-search-mobile-toggle {
        display: flex;
    }

    /* Collapsed state — zero-height with overflow hidden for smooth transition */
    .lgl-search-collapsible {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease;
    }

    /* Expanded state driven by JS toggling the class */
    .lgl-search-collapsible.is-open {
        max-height: 2000px; /* large enough to never clip even long filter lists */
    }
}
</style>

<div class="lgl-search-container lgl-holder <?= $post_type == false ? 'lgl-search-container-bg-secondary' : '' ?>">

    <!-- Mobile toggle button — hidden above 1024px via CSS -->
    <button type="button" class="lgl-search-mobile-toggle" aria-expanded="false" aria-controls="lgl-search-collapsible">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
        <span class="lgl-toggle-label">Start a New Search</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-left:auto;">
            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
        </svg>
    </button>

    <!-- Collapsible wrapper — always visible on desktop, toggled on mobile -->
    <div class="lgl-search-collapsible" id="lgl-search-collapsible">
        <form id="lgl-search-form" class="lgl-filter-form <?= $post_type == false ? 'lgl-filter-form-no-ajax' : 'lgl-filter-form-ajax' ?>">
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
            <select name="listing_make" id="lgl_make" class="lgl-select2" data-placeholder="Select Make"
                <?php echo ($post_type == false) ? 'disabled' : ''; ?>>
                <option value=""><?php echo ($post_type == false) ? 'Select Vehicle Type First' : 'All Makes'; ?></option>
                <?php if ($post_type != false) : ?>
                    <?php foreach ($makes as $make) : ?>
                        <option value="<?php echo esc_attr($make->term_id); ?>"
                            <?php selected($active_make, $make->term_id); ?>>
                            <?php echo esc_html($make->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                            <?php echo esc_html( LGL_Shortcodes::format_price( $price ) ); ?>
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
                            <?php echo esc_html( LGL_Shortcodes::format_price( $price ) ); ?>
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
    </div><!-- /.lgl-search-collapsible -->

</div><!-- /.lgl-search-container -->

<script>
(function () {
    var btn        = document.querySelector('.lgl-search-mobile-toggle');
    var panel      = document.getElementById('lgl-search-collapsible');
    var label      = btn ? btn.querySelector('.lgl-toggle-label') : null;

    if (!btn || !panel) return;

    btn.addEventListener('click', function () {
        var isOpen = panel.classList.toggle('is-open');
        btn.classList.toggle('is-open', isOpen);
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (label) {
            label.textContent = isOpen ? 'Close Search' : 'Start a New Search';
        }
    });
})();
</script>