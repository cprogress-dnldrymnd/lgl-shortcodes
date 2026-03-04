<?php

/**
 * Plugin Name: LGL Shortcodes
 * Plugin URI: https://digitallydisruptive.co.uk/
 * Description: A robust, OOP-based plugin to output customized data via shortcodes using a dynamic template routing system.
 * Version: 1.6.3
 * Author: Digitally Disruptive - Donald Raymundo
 * Author URI: https://digitallydisruptive.co.uk/
 * Text Domain: lgl-shortcodes
 */

if (! defined('ABSPATH')) {
    exit; // Prevent direct access to the file.
}

// Define a constant for the plugin directory path to ensure reliable file inclusion.
define('LGL_SHORTCODES_PATH', plugin_dir_path(__FILE__));
define('LGL_SHORTCODES_URL', plugin_dir_url(__FILE__));
define('LGL_SHORTCODES_VERSION', '2.0.2');

if (! class_exists('LGL_Shortcodes')) {

    /**
     * Main class for the LGL Shortcodes plugin.
     * Manages the registration, parameter parsing, and template routing of all shortcodes.
     */
    class LGL_Shortcodes
    {
        /**
         * Array of custom templates provided by the plugin.
         * Key: filename, Value: Display Name in backend.
         * @var array
         */
        protected $custom_templates;

        /**
         * Initializes the plugin by hooking into the WordPress lifecycle.
         *
         * @return void
         */
        public function __construct()
        {
            add_action('init', array($this, 'register_shortcodes'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

            // AJAX endpoints for dependent dropdowns and search results
            add_action('wp_ajax_lgl_get_models', array($this, 'ajax_get_models'));
            add_action('wp_ajax_nopriv_lgl_get_models', array($this, 'ajax_get_models'));
            add_action('wp_ajax_lgl_fetch_results', array($this, 'ajax_fetch_results'));
            add_action('wp_ajax_nopriv_lgl_fetch_results', array($this, 'ajax_fetch_results'));
            add_action('wp_ajax_lgl_add_to_wishlist', array($this, 'ajax_add_to_wishlist'));

            // Aggressive override: Force plugin template for specific CPTs, bypassing theme hierarchy
            add_filter('single_template', array($this, 'force_plugin_single_template'), 99999);
        }

        /**
         * Forcibly intercepts the single template routing for specific custom post types.
         * Bypasses database meta checks and directly serves the plugin's single-lgl.php template.
         *
         * @param string $template The current path to the template WordPress intends to load.
         * @return string The overridden template path if it matches our target CPTs, otherwise the default.
         */
        public function force_plugin_single_template($template)
        {
            // Define the specific post types that must use the plugin template
            $target_post_types = array('caravan', 'motorhome', 'campervan');

            // Check if the current query is a single view for one of our target post types
            if (is_singular($target_post_types)) {

                // Define the absolute path to the plugin's custom single template
                $plugin_template = LGL_SHORTCODES_PATH . 'templates/single-lgl.php';

                // Prioritize theme override if it exists (e.g., your-theme/lgl-shortcodes/single-lgl.php)
                // Otherwise, strictly enforce the plugin's internal template file
                $theme_override = locate_template('lgl-shortcodes/single-lgl.php');
                $file_to_load = ($theme_override) ? $theme_override : $plugin_template;

                if (file_exists($file_to_load)) {
                    return $file_to_load;
                }
            }

            // Return the default theme template if conditions are not met
            return $template;
        }

        /**
         * Enqueues plugin-specific stylesheets and scripts.
         * Utilizes the wp_enqueue_scripts hook for front-end asset loading.
         *
         * @return void
         */
        public function enqueue_assets()
        {
            // Enqueue Select2 dependencies
            wp_enqueue_style('select2', LGL_SHORTCODES_URL . 'assets/libs/select2/select2.min.css');
            wp_enqueue_style('slick', LGL_SHORTCODES_URL . 'assets/libs/slick/slick.css');

            wp_enqueue_script('slick', LGL_SHORTCODES_URL . 'assets/libs/slick/slick.min.js', array('jquery'), '4.1.0', true);
            wp_enqueue_script('select2', LGL_SHORTCODES_URL . 'assets/libs/select2/select2.min.js', array('jquery'), '4.1.0', true);

            // Enqueue main stylesheet
            wp_enqueue_style(
                'lgl-main-css',
                LGL_SHORTCODES_URL . 'assets/css/main.css',
                array('select2', 'slick'),
                LGL_SHORTCODES_VERSION
            );

            // Enqueue main JavaScript file (footer loaded)
            wp_enqueue_script(
                'lgl-main-js',
                LGL_SHORTCODES_URL . 'assets/js/main.js',
                array('jquery', 'select2', 'slick'),
                LGL_SHORTCODES_VERSION,
                true
            );

            // Localize AJAX URL for frontend operations
            wp_localize_script('lgl-main-js', 'lgl_ajax_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('lgl_search_nonce')
            ));
        }

        /**
         * Registers the defined shortcodes with the WordPress Shortcode API.
         * Routes callbacks to a unified template rendering method or specialized handlers.
         *
         * @return void
         */
        public function register_shortcodes()
        {
            // Registering the original shortcode and the new search shortcode
            add_shortcode('lgl_search_results', array($this, 'render_shortcode'));
            add_shortcode('lgl_search', array($this, 'render_shortcode'));
            
            // Registering the dynamic related vehicles shortcode
            add_shortcode('lgl_related_vehicles', array($this, 'render_related_vehicles_shortcode'));
        }

        /**
         * A unified callback function that processes shortcodes and routes them to external template files.
         * Uses the shortcode tag to determine the required template name dynamically.
         *
         * @param array  $atts          The array of attributes passed by the user.
         * @param string $content       The enclosed content between opening and closing shortcode tags, if any.
         * @param string $shortcode_tag The name of the shortcode tag currently being executed.
         * @return string               The sanitized and formatted HTML string generated by the required template.
         */
        public function render_shortcode($atts, $content = null, $shortcode_tag = '')
        {
            // Set default shortcode attributes. Post type default is 'post'.
            $attributes = shortcode_atts(array(
                'post_type' => 'caravan'
            ), $atts, $shortcode_tag);

            // Hand over execution to the template loader
            return $this->load_template($shortcode_tag, $attributes, $content);
        }

        /**
         * Callback handler specifically designed for the [lgl_related_vehicles] shortcode.
         * Fetches 3 randomized vehicles of the current post type, safely excluding the active global $post.
         *
         * @param array  $atts    The array of attributes passed by the user.
         * @param string $content The enclosed content, if any.
         * @return string         The buffered HTML generated by the associated UI partials.
         */
        public function render_related_vehicles_shortcode($atts, $content = null)
        {
            global $post;

            // Inherit context from the current global post; fallback to 'caravan' if outside the loop
            $current_post_type = (is_a($post, 'WP_Post')) ? $post->post_type : 'caravan';
            $current_post_id   = (is_a($post, 'WP_Post')) ? $post->ID : 0;

            // Standardize parameters, permitting overrides via shortcode attributes
            $attributes = shortcode_atts(array(
                'post_type' => $current_post_type,
                'count'     => 3
            ), $atts, 'lgl_related_vehicles');

            // Compile targeted WP_Query arguments
            $args = array(
                'post_type'      => sanitize_text_field($attributes['post_type']),
                'post_status'    => 'publish',
                'posts_per_page' => intval($attributes['count']),
                'orderby'        => 'rand',           // Trigger MySQL RAND() for randomized output
                'post__not_in'   => array($current_post_id), // Prevent querying the currently active listing
            );

            $query = new WP_Query($args);

            ob_start();

            if ($query->have_posts()) {
                // Implementing a structural wrapper for front-end DOM manipulation (flexbox/CSS grids)
                echo '<div class="lgl-grid-layout lgl-cols--3">';
                while ($query->have_posts()) {
                    $query->the_post();
                    // Invoke existing isolated template logic to adhere to the DRY principle
                    include LGL_SHORTCODES_PATH . 'templates/partials/lgl-grid.php';
                }
                echo '</div>';
            } else {
                echo '<div class="lgl-no-results">No related vehicles available at this time.</div>';
            }

            // Restore global post data object
            wp_reset_postdata();

            return ob_get_clean();
        }

        /**
         * AJAX handler to add or remove a post from a user's wishlist.
         * Stores data in the 'lgl_wishlists' user meta field as an array.
         */
        public function ajax_add_to_wishlist()
        {
            check_ajax_referer('lgl_search_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error('You must be logged in to save to your wishlist.');
            }

            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
            $user_id = get_current_user_id();

            if ($post_id <= 0) {
                wp_send_json_error('Invalid post ID.');
            }

            // Retrieve current wishlist (returns an empty string if it doesn't exist, so cast to array)
            $wishlist = get_user_meta($user_id, 'lgl_wishlists', true);
            if (!is_array($wishlist)) {
                $wishlist = array();
            }

            $status = '';

            // Toggle logic: If it's in the list, remove it. If not, add it.
            if (in_array($post_id, $wishlist)) {
                $wishlist = array_diff($wishlist, array($post_id));
                $status = 'removed';
            } else {
                $wishlist[] = $post_id;
                $status = 'added';
            }

            // Save the updated array back to user meta
            $updated = update_user_meta($user_id, 'lgl_wishlists', array_values($wishlist)); // array_values re-indexes the array

            if ($updated !== false) {
                wp_send_json_success(array('status' => $status));
            } else {
                wp_send_json_error('Failed to update database.');
            }
        }

        /**
         * Locates, isolates variables, and loads the requested template file.
         * Prioritizes theme overrides before falling back to the default plugin template.
         *
         * @param string $template_name The base name of the template file (without extension).
         * @param array  $attributes    The associative array of shortcode attributes.
         * @param string $content       The enclosed shortcode content.
         * @return string               The buffered HTML content rendered by the template.
         */
        private function load_template($template_name, $attributes, $content)
        {
            // 1. Define the default template path inside the plugin's /templates directory
            $plugin_path = LGL_SHORTCODES_PATH . 'templates/' . $template_name . '.php';

            // 2. Check if the active theme contains an override file (e.g., your-theme/lgl-shortcodes/lgl-search.php)
            $theme_override = locate_template('lgl-shortcodes/' . $template_name . '.php');

            // 3. Select the correct file path prioritizing the theme override
            $file_to_load = ($theme_override) ? $theme_override : $plugin_path;

            // 4. Return an HTML comment for debugging if the template does not exist
            if (! file_exists($file_to_load)) {
                return '';
            }

            // 5. Extract attributes into individual variables for cleaner usage within the template file
            // EXTR_SKIP prevents existing variables in this method's scope from being accidentally overwritten
            extract($attributes, EXTR_SKIP);

            // 6. Initialize output buffering to safely capture the included file's output
            ob_start();
            include $file_to_load;
            return ob_get_clean();
        }

        /**
         * Retrieves unique meta values for a specific meta key within a given post type.
         * Executes a direct SQL query for optimized distinct value extraction.
         *
         * @param string $post_type The post type to query.
         * @param string $meta_key  The meta key to target.
         * @return array            An array of distinct meta values.
         */
        public static function get_unique_meta_values($post_type, $meta_key)
        {
            global $wpdb;

            $query = $wpdb->prepare(
                "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                 WHERE p.post_type = %s AND p.post_status = 'publish' 
                 AND pm.meta_key = %s AND pm.meta_value != ''
                 ORDER BY pm.meta_value ASC",
                $post_type,
                $meta_key
            );

            return $wpdb->get_col($query);
        }

        /**
         * Converts a comma-separated string into an array of integer values.
         *
         * This function handles whitespace around the commas and ensures 
         * that the resulting array contains strictly integer types. It uses
         * array_map with a callback to intval after splitting the string.
         *
         * @param string $inputString The comma-separated string to convert.
         * @return int[] Array of integer values.
         */
        public static function convertStringToIntArray(string $inputString): array
        {
            // Return empty array if the string is empty
            if (trim($inputString) === '') {
                return [];
            }

            // Explode the string by comma, trim whitespace, and convert to integer
            return array_map(function ($value) {
                return intval(trim($value));
            }, explode(',', $inputString));
        }

        /**
         * AJAX handler to fetch child taxonomy terms (models) based on a parent term ID (make).
         * Returns a JSON-encoded array structured for Select2 parsing.
         *
         * @return void
         */
        public function ajax_get_models()
        {
            check_ajax_referer('lgl_search_nonce', 'nonce');

            $parent_id = isset($_POST['make_id']) ? intval($_POST['make_id']) : 0;

            if ($parent_id <= 0) {
                wp_send_json_success(array());
            }

            $terms = get_terms(array(
                'taxonomy'   => 'listing-make-model',
                'hide_empty' => false,
                'parent'     => $parent_id,
            ));

            $results = array();
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $results[] = array(
                        'id'   => $term->term_id,
                        'text' => $term->name
                    );
                }
            }

            wp_send_json_success($results);
        }

        /**
         * AJAX handler to fetch and render the filtered search results and pagination UI.
         * Compiles taxonomy and meta queries based on serialized form data.
         *
         * @return void
         */
        public function ajax_fetch_results()
        {
            check_ajax_referer('lgl_search_nonce', 'nonce');

            $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
            $paged     = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1; // Sanitize and set current page
            $form_data = array();

            // Parse serialized form data
            if (isset($_POST['form_data'])) {
                parse_str($_POST['form_data'], $form_data);
            }

            $args = array(
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => 9,
                'paged'          => $paged, // Inject pagination state
                'meta_query'     => array('relation' => 'AND'),
                'tax_query'      => array('relation' => 'AND')
            );

            // Handle Sorting if passed via sort_order dropdown (matching user markup)
            if (!empty($form_data['sort_order'])) {
                switch ($form_data['sort_order']) {
                    case 'date_low':
                        $args['orderby'] = 'date';
                        $args['order']   = 'ASC';
                        break;
                    case 'price_high':
                        $args['orderby']  = 'meta_value_num';
                        $args['meta_key'] = 'price';
                        $args['order']    = 'DESC';
                        break;
                    case 'price_low':
                        $args['orderby']  = 'meta_value_num';
                        $args['meta_key'] = 'price';
                        $args['order']    = 'ASC';
                        break;
                    case 'date_high':
                    default:
                        $args['orderby'] = 'date';
                        $args['order']   = 'DESC';
                        break;
                }
            }

            // Meta Queries
            if (!empty($form_data['condition'])) {
                $args['meta_query'][] = array(
                    'key'     => 'condition',
                    'value'   => sanitize_text_field($form_data['condition']),
                    'compare' => '='
                );
            }

            if (!empty($form_data['berth'])) {
                $args['meta_query'][] = array(
                    'key'     => 'berth',
                    'value'   => sanitize_text_field($form_data['berth']),
                    'compare' => '='
                );
            }

            // Price Range (Min/Max)
            $price_min = !empty($form_data['price_min']) ? floatval($form_data['price_min']) : 0;
            $price_max = !empty($form_data['price_max']) ? floatval($form_data['price_max']) : 0;

            if ($price_min > 0 || $price_max > 0) {
                $price_query = array(
                    'key'  => 'price',
                    'type' => 'NUMERIC'
                );
                if ($price_min > 0 && $price_max > 0) {
                    $price_query['value']   = array($price_min, $price_max);
                    $price_query['compare'] = 'BETWEEN';
                } elseif ($price_min > 0) {
                    $price_query['value']   = $price_min;
                    $price_query['compare'] = '>=';
                } else {
                    $price_query['value']   = $price_max;
                    $price_query['compare'] = '<=';
                }
                $args['meta_query'][] = $price_query;
            }

            // Tax Queries
            $make_id  = !empty($form_data['listing_make']) ? intval($form_data['listing_make']) : 0;
            $model_id = !empty($form_data['listing_model']) ? intval($form_data['listing_model']) : 0;

            if ($model_id > 0) {
                // If model is selected, filter by model (which inherently belongs to the make)
                $args['tax_query'][] = array(
                    'taxonomy' => 'listing-make-model',
                    'field'    => 'term_id',
                    'terms'    => $model_id
                );
            } elseif ($make_id > 0) {
                // If only make is selected
                $args['tax_query'][] = array(
                    'taxonomy' => 'listing-make-model',
                    'field'    => 'term_id',
                    'terms'    => $make_id
                );
            }

            // Execute Query
            $query = new WP_Query($args);

            ob_start();

            // Render specific block logic to maintain the exact DOM structure requested.
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    // Load isolated template component for the iteration to ensure maintainability
                    include LGL_SHORTCODES_PATH . 'templates/partials/lgl-grid.php';
                }
            } else {
                echo '<div class="lgl-no-results">No vehicles found matching your criteria.</div>';
            }

            $html = ob_get_clean();

            // Construct Pagination HTML payload
            $pagination_html = '';
            if ($query->max_num_pages > 1) {
                $pagination_html = paginate_links(array(
                    'base'      => '%_%',
                    'format'    => '?paged=%#%',
                    'current'   => $paged,
                    'total'     => $query->max_num_pages,
                    'prev_text' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'next_text' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'type'      => 'list',
                    'add_args'  => false
                ));
            }
            wp_reset_postdata();

            wp_send_json_success(array(
                'html'       => $html,
                'pagination' => $pagination_html,
                'count'      => $query->found_posts
            ));
        }

        /**
         * Retrieves listing detail fields from the external LGL_Import_Post_Types class.
         * * Uses the Reflection API to bypass the 'private' visibility of the 
         * get_listing_detail_fields method without altering the external plugin's core files.
         *
         * @return array Associative array of listing fields, or an empty array on failure.
         */
        public static function get_external_listing_fields(): array
        {
            if (! class_exists('LGL_Import_Post_Types')) {
                return [];
            }

            try {
                // Initialize reflection on the target class and method
                $reflectionMethod = new ReflectionMethod('LGL_Import_Post_Types', 'get_listing_detail_fields');
                
                // Override the private visibility restriction
                $reflectionMethod->setAccessible(true);
                
                // Invoke the method. Passing null since it is a static method.
                return $reflectionMethod->invoke(null);
                
            } catch (ReflectionException $e) {
                // Handle case where method does not exist or reflection fails
                error_log('LGL Shortcodes: Failed to reflect get_listing_detail_fields - ' . $e->getMessage());
                return [];
            }
        }
    }

    // Instantiate the plugin architecture
    new LGL_Shortcodes();
}