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
// Prioritize native query vars from our rewrite rules, fallback to standard $_GET
$active_make      = get_query_var('listing_make') ? sanitize_text_field(get_query_var('listing_make')) : (isset($_GET['listing_make']) ? sanitize_text_field($_GET['listing_make']) : '');
$active_model     = get_query_var('listing_model') ? sanitize_text_field(get_query_var('listing_model')) : (isset($_GET['listing_model']) ? sanitize_text_field($_GET['listing_model']) : '');
$active_condition = isset($_GET['condition'])     ? sanitize_text_field($_GET['condition'])     : '';
$active_berth     = isset($_GET['berth'])         ? sanitize_text_field($_GET['berth'])         : '';
$active_price_min = isset($_GET['price_min'])     ? sanitize_text_field($_GET['price_min'])     : '';
$active_price_max = isset($_GET['price_max'])     ? sanitize_text_field($_GET['price_max'])     : '';

// Calculate the clean Base URL for JavaScript path construction
$base_archive_url = '';
if ($post_type) {
    $options = get_option('lgl_settings', array());
    $page_key = $post_type . '_page';
    if (!empty($options[$page_key])) {
        $base_archive_url = get_permalink($options[$page_key]);
    } else {
        $base_archive_url = get_post_type_archive_link($post_type);
    }
}
?>

<?php if ($post_type) : ?>
    <!-- ① Mobile trigger button (only when a post_type is set) -->
    <div class="lgl-search-mobile-toggle-wrapper">
        <button type="button"
            class="lgl-search-mobile-toggle"
            aria-expanded="false"
            aria-controls="lgl-search-offcanvas"
            aria-label="Start a new search">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
            </svg>
            Start a New Search
        </button>
    </div>

    <!-- ② Backdrop (mobile only, fades in behind panel) -->
    <div class="lgl-search-offcanvas-backdrop" id="lgl-search-backdrop" aria-hidden="true"></div>
<?php endif; ?>

<!-- ③ Offcanvas — static on desktop, fixed sliding panel on mobile.
        The form lives here once; no duplication, no ID conflicts.
        When no post_type is set, the offcanvas chrome is skipped entirely
        and the form renders as a plain inline container. -->
<?php if ($post_type) : ?>
    <div class="lgl-search-offcanvas"
        id="lgl-search-offcanvas"
        role="dialog"
        aria-modal="true"
        aria-label="Search filters">

        <!-- Header bar (CSS hides this on desktop) -->
        <div class="lgl-offcanvas-header">
            <h3>Search Filters</h3>
            <button type="button" class="lgl-offcanvas-close" aria-label="Close search filters">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                </svg>
            </button>
        </div>

        <div class="lgl-offcanvas-body">
        <?php endif; ?>

        <div class="lgl-search-container lgl-holder <?= $post_type == false ? 'lgl-search-container-bg-secondary' : '' ?>">
            <form id="lgl-search-form" class="lgl-filter-form <?= $post_type == false ? 'lgl-filter-form-no-ajax' : 'lgl-filter-form-ajax' ?>">
                <input type="hidden" name="post_type" id="lgl_target_post_type" value="<?php echo esc_attr($post_type); ?>">
                <input type="hidden" id="lgl_base_archive_url" value="<?php echo esc_url($base_archive_url); ?>">

                <?php if ($post_type == false) { ?>
                    <?php
                    $options = get_option('lgl_settings', array());

                    $caravan_page   = $options['caravan_page']   ?? false;
                    $motorhome_page = $options['motorhome_page'] ?? false;
                    $campervan_page = $options['campervan_page'] ?? false;

                    $vehicle_types = array();
                    if ($caravan_page)   $vehicle_types[] = array('url' => get_the_permalink($caravan_page),   'label' => 'Caravan',   'slug' => 'caravan');
                    if ($motorhome_page) $vehicle_types[] = array('url' => get_the_permalink($motorhome_page), 'label' => 'Motorhome', 'slug' => 'motorhome');
                    if ($campervan_page) $vehicle_types[] = array('url' => get_the_permalink($campervan_page), 'label' => 'Campervan', 'slug' => 'campervan');
                    ?>

                    <?php if (!empty($vehicle_types)) : ?>
                        <div class="lgl-filter-group">
                            <label for="lgl_vehicle_type">Leisure Vehicle Type</label>
                            <select name="post_type" id="lgl_post_type" class="lgl-select2" data-placeholder="Leisure Vehicle Type" required>
                                <option value="">Leisure Vehicle Type</option>
                                <?php foreach ($vehicle_types as $type) : ?>
                                    <option value="<?php echo esc_attr($type['url']); ?>"
                                        data-post-type="<?php echo esc_attr($type['slug']); ?>"
                                        <?php selected($active_post_type, $type['url']); ?>>
                                        <?php echo esc_html($type['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php } ?>

                <!-- Make -->
                <div class="lgl-filter-group">
                    <label for="lgl_make">Make</label>
                    <select name="listing_make" id="lgl_make" class="lgl-select2" data-placeholder="Select Make" <?php echo ($post_type == false) ? 'disabled' : ''; ?>>
                        <option value=""><?php echo ($post_type == false) ? 'Select Vehicle Type First' : 'All Makes'; ?></option>
                        <?php if ($post_type != false) : ?>
                            <?php foreach ($makes as $make) : ?>
                                <option value="<?php echo esc_attr($make->slug); ?>" <?php selected($active_make, $make->slug); ?>>
                                    <?php echo esc_html($make->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Model -->
                <div class="lgl-filter-group">
                    <label for="lgl_model">Model</label>
                    <select name="listing_model" id="lgl_model" class="lgl-select2" data-placeholder="Select Model" <?php echo empty($active_make_models) ? 'disabled' : ''; ?>>
                        <?php if (empty($active_make_models)) : ?>
                            <option value="">Select Make First</option>
                        <?php else : ?>
                            <option value="">All Models</option>
                            <?php foreach ($active_make_models as $model) : ?>
                                <option value="<?php echo esc_attr($model->slug); ?>" <?php selected($active_model, $model->slug); ?>>
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
                                    <?php echo esc_html(LGL_Shortcodes::format_price($price)); ?>
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
                                    <?php echo esc_html(LGL_Shortcodes::format_price($price)); ?>
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

        <?php if ($post_type) : ?>
        </div><!-- /.lgl-offcanvas-body -->
    </div><!-- /.lgl-search-offcanvas -->

    <script>
        (function() {
            var toggleBtn = document.querySelector('.lgl-search-mobile-toggle');
            var panel = document.getElementById('lgl-search-offcanvas');
            var backdrop = document.getElementById('lgl-search-backdrop');
            var closeBtn = panel ? panel.querySelector('.lgl-offcanvas-close') : null;
            var searchForm = document.getElementById('lgl-search-form');

            if (!toggleBtn || !panel || !backdrop) return;

            function openPanel() {
                panel.classList.add('is-open');
                backdrop.classList.add('is-visible');
                document.body.classList.add('lgl-offcanvas-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
                // Shift focus to the close button for keyboard accessibility
                if (closeBtn) closeBtn.focus();
            }

            function closePanel() {
                panel.classList.remove('is-open');
                backdrop.classList.remove('is-visible');
                document.body.classList.remove('lgl-offcanvas-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.focus();
            }

            toggleBtn.addEventListener('click', openPanel);
            if (closeBtn) closeBtn.addEventListener('click', closePanel);
            backdrop.addEventListener('click', closePanel);

            // Intercept form submission to automatically collapse the mobile offcanvas
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    if (panel.classList.contains('is-open')) {
                        closePanel();
                    }
                });
            }

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && panel.classList.contains('is-open')) {
                    closePanel();
                }
            });
        })();
    </script>
<?php endif; ?>