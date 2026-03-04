<?php

/**
 * Plugin Name: LGL Shortcodes
 * Plugin URI: https://digitallydisruptive.co.uk/
 * Description: A robust, OOP-based plugin to output customized data via shortcodes using a dynamic template routing system.
 * Version: 2.0.9
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
define('LGL_SHORTCODES_VERSION', '2.1.7');

if (! class_exists('LGL_Shortcodes')) {

	/**
	 * Main class for the LGL Shortcodes plugin.
	 * Manages the registration, parameter parsing, template routing, and backend settings.
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
		 * Registers shortcodes, assets, AJAX endpoints, template overrides, and admin settings.
		 *
		 * @return void
		 */
		public function __construct()
		{
			// Frontend Hooks
			add_action('init', array($this, 'register_shortcodes'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
			add_action('wp_head', array($this, 'inject_dynamic_css'));

			// AJAX endpoints for dependent dropdowns and search results
			add_action('wp_ajax_lgl_get_models', array($this, 'ajax_get_models'));
			add_action('wp_ajax_nopriv_lgl_get_models', array($this, 'ajax_get_models'));
			add_action('wp_ajax_lgl_fetch_results', array($this, 'ajax_fetch_results'));
			add_action('wp_ajax_nopriv_lgl_fetch_results', array($this, 'ajax_fetch_results'));
			add_action('wp_ajax_lgl_add_to_wishlist', array($this, 'ajax_add_to_wishlist'));

			// New AJAX endpoints for mini wishlist dynamic refresh
			add_action('wp_ajax_lgl_refresh_mini_wishlist', array($this, 'ajax_refresh_mini_wishlist'));
			add_action('wp_ajax_nopriv_lgl_refresh_mini_wishlist', array($this, 'ajax_refresh_mini_wishlist'));

			// Aggressive override: Force plugin template for specific CPTs, bypassing theme hierarchy
			add_filter('single_template', array($this, 'force_plugin_single_template'), 99999);

			// Backend Settings Hooks
			add_action('admin_menu', array($this, 'register_admin_menu'));
			add_action('admin_init', array($this, 'register_plugin_settings'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_assets'));



			// Cache Invalidation Hooks for specific CPTs
			add_action('save_post_caravan', array($this, 'clear_lgl_search_cache'));
			add_action('save_post_motorhome', array($this, 'clear_lgl_search_cache'));
			add_action('save_post_campervan', array($this, 'clear_lgl_search_cache'));

			// Cache Invalidation Hooks for taxonomy modifications
			add_action('saved_term', array($this, 'clear_lgl_taxonomy_cache'), 10, 3);
			add_action('delete_term', array($this, 'clear_lgl_taxonomy_cache'), 10, 3);
		}

		/**
		 * Enqueues administrative scripts and styles strictly on the plugin's settings page.
		 * Loads wp-color-picker for the Design Settings tab.
		 *
		 * @param string $hook The current admin page hook.
		 * @return void
		 */
		public function admin_enqueue_assets($hook)
		{
			if ('toplevel_page_lgl-settings' !== $hook) {
				return;
			}

			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');

			// Inline script to initialize the color picker instances
			wp_add_inline_script('wp-color-picker', '
                jQuery(document).ready(function($){
                    $(".lgl-color-picker").wpColorPicker();
                });
            ');
		}

		/**
		 * Registers the main administrative menu page for the plugin.
		 *
		 * @return void
		 */
		public function register_admin_menu()
		{
			add_menu_page(
				'LGL Settings',
				'LGL Settings',
				'manage_options',
				'lgl-settings',
				array($this, 'render_settings_page'),
				'dashicons-admin-generic',
				80
			);
		}

		/**
		 * Registers settings, sections, and fields via the WordPress Settings API.
		 * Fields are segmented into tab-specific sections for logical rendering.
		 *
		 * @return void
		 */
		public function register_plugin_settings()
		{
			// Register a unified options array to prevent database bloat
			register_setting('lgl_settings_group', 'lgl_settings');

			// --- TAB 1: Design Settings ---
			add_settings_section('lgl_design_section', 'Typography and Color Variables', null, 'lgl-settings-design');

			$design_fields = array(
				'font_primary'     => array('label' => 'Primary Font', 'type' => 'text', 'default' => '"DM Sans", sans-serif'),
				'font_secondary'   => array('label' => 'Secondary Font', 'type' => 'text', 'default' => '"Poppins", sans-serif'),
				'color_accent'     => array('label' => 'Accent Color', 'type' => 'color', 'default' => '#f6d100'),
				'color_primary'    => array('label' => 'Primary Color', 'type' => 'color', 'default' => '#003793'),
				'color_secondary'  => array('label' => 'Secondary Color', 'type' => 'color', 'default' => '#001537'),
				'color_tertiary'   => array('label' => 'Tertiary Color', 'type' => 'color', 'default' => '#00e6f6'),
				'color_quaternary' => array('label' => 'Quaternary Color', 'type' => 'color', 'default' => '#007bff'),
			);

			foreach ($design_fields as $id => $field) {
				add_settings_field(
					$id,
					$field['label'],
					array($this, 'render_field'),
					'lgl-settings-design',
					'lgl_design_section',
					array('id' => $id, 'type' => $field['type'], 'default' => $field['default'])
				);
			}

			// --- TAB 2: Additional Settings ---
			add_settings_section('lgl_additional_section', 'Single Vehicle Additions', null, 'lgl-settings-additional');

			add_settings_field(
				'single_vehicle_content',
				'Single Vehicle Additional Content',
				array($this, 'render_field'),
				'lgl-settings-additional',
				'lgl_additional_section',
				array('id' => 'single_vehicle_content', 'type' => 'textarea', 'default' => '')
			);
		}

		/**
		 * Universal renderer for settings fields, handling multiple input types dynamically.
		 * Extracts current values from the serialized 'lgl_settings' array.
		 *
		 * @param array $args Field configuration arguments (id, type, default).
		 * @return void
		 */
		public function render_field($args)
		{
			$options = get_option('lgl_settings', array());
			$id      = $args['id'];
			$value   = isset($options[$id]) ? $options[$id] : $args['default'];

			switch ($args['type']) {
				case 'color':
					echo sprintf(
						'<input type="text" id="lgl_settings[%1$s]" name="lgl_settings[%1$s]" value="%2$s" class="lgl-color-picker" />',
						esc_attr($id),
						esc_attr($value)
					);
					break;
				case 'textarea':
					echo sprintf(
						'<textarea id="lgl_settings[%1$s]" name="lgl_settings[%1$s]" rows="5" cols="50" class="large-text">%2$s</textarea>',
						esc_attr($id),
						esc_textarea($value)
					);
					break;
				case 'text':
				default:
					echo sprintf(
						'<input type="text" id="lgl_settings[%1$s]" name="lgl_settings[%1$s]" value="%2$s" class="regular-text" />',
						esc_attr($id),
						esc_attr($value)
					);
					break;
			}
		}

		/**
		 * Renders the HTML architecture for the tabbed settings interface.
		 * Utilizes WordPress core CSS classes for native UI compliance.
		 *
		 * @return void
		 */
		public function render_settings_page()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'design';
?>
			<div class="wrap">
				<h1>LGL Shortcodes Settings</h1>
				<h2 class="nav-tab-wrapper">
					<a href="?page=lgl-settings&tab=design" class="nav-tab <?php echo $active_tab == 'design' ? 'nav-tab-active' : ''; ?>">Design Settings</a>
					<a href="?page=lgl-settings&tab=additional" class="nav-tab <?php echo $active_tab == 'additional' ? 'nav-tab-active' : ''; ?>">Additional Settings</a>
				</h2>

				<form method="post" action="options.php">
					<?php
					settings_fields('lgl_settings_group');
					if ($active_tab == 'design') {
						do_settings_sections('lgl-settings-design');
					} else {
						do_settings_sections('lgl-settings-additional');
					}
					submit_button();
					?>
				</form>
			</div>
		<?php
		}

		/**
		 * Injects customized design settings as native CSS variables into the document head.
		 * Values fall back to the defaults established in your provided image if unset.
		 *
		 * @return void
		 */
		public function inject_dynamic_css()
		{
			$options = get_option('lgl_settings', array());

			$vars = array(
				'--lgl-font-primary'     => isset($options['font_primary']) ? $options['font_primary'] : '"DM Sans", sans-serif',
				'--lgl-font-secondary'   => isset($options['font_secondary']) ? $options['font_secondary'] : '"Poppins", sans-serif',
				'--lgl-color-accent'     => isset($options['color_accent']) ? $options['color_accent'] : '#f6d100',
				'--lgl-color-primary'    => isset($options['color_primary']) ? $options['color_primary'] : '#003793',
				'--lgl-color-secondary'  => isset($options['color_secondary']) ? $options['color_secondary'] : '#001537',
				'--lgl-color-tertiary'   => isset($options['color_tertiary']) ? $options['color_tertiary'] : '#00e6f6',
				'--lgl-color-quaternary' => isset($options['color_quaternary']) ? $options['color_quaternary'] : '#007bff',
			);

			echo "<style id='lgl-dynamic-vars'>\n:root {\n";
			foreach ($vars as $key => $val) {
				echo "\t{$key}: " . esc_attr($val) . ";\n";
			}
			echo "}\n</style>\n";
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
			add_shortcode('lgl_related_vehicles', array($this, 'render_shortcode'));
			add_shortcode('lgl_mini_wishlist', array($this, 'render_shortcode'));
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
		 * Generates the internal HTML payload for the mini wishlist items list.
		 * Used for initial shortcode rendering and subsequent AJAX refresh states.
		 *
		 * @return string The compiled HTML string containing the wishlist list items.
		 */
		private function get_mini_wishlist_html()
		{
			if (!is_user_logged_in()) {
				return '<div class="lgl-wishlist-empty">Please log in to view your wishlist.</div>';
			}

			$user_id = get_current_user_id();
			$wishlist = get_user_meta($user_id, 'lgl_wishlists', true);

			if (!is_array($wishlist) || empty($wishlist)) {
				return '<div class="lgl-wishlist-empty">Your wishlist is currently empty.</div>';
			}

			ob_start();
			echo '<ul class="lgl-mini-wishlist-items">';

			foreach ($wishlist as $post_id) {
				$post = get_post($post_id);
				if (!$post || $post->post_status !== 'publish') continue;

				$price = get_post_meta($post_id, 'price', true);
				$formatted_price = $price ? '$' . number_format((float)$price, 0) : 'N/A';

				echo '<li class="lgl-wishlist-item" data-post-id="' . esc_attr($post_id) . '">';
				echo '  <div class="lgl-wishlist-thumb">' . get_the_post_thumbnail($post_id, 'thumbnail') . '</div>';
				echo '  <div class="lgl-wishlist-info">';
				echo '      <h4 class="lgl-wishlist-title"><a href="' . get_permalink($post_id) . '">' . esc_html($post->post_title) . '</a></h4>';
				echo '      <span class="lgl-wishlist-price">' . esc_html($formatted_price) . '</span>';
				echo '  </div>';
				echo '  <div class="lgl-wishlist-remove">';
				echo '      <button class="lgl-remove-btn" data-id="' . esc_attr($post_id) . '" aria-label="Remove from wishlist">';
				echo '          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6"/></svg>';
				echo '      </button>';
				echo '  </div>';
				echo '</li>';
			}

			echo '</ul>';
			return ob_get_clean();
		}

		/**
		 * AJAX handler to refresh the mini wishlist dropdown content.
		 * Returns JSON payload containing the updated HTML and the new item count.
		 *
		 * @return void
		 */
		public function ajax_refresh_mini_wishlist()
		{
			check_ajax_referer('lgl_search_nonce', 'nonce');

			if (!is_user_logged_in()) {
				wp_send_json_error('User not logged in.');
			}

			$html = $this->get_mini_wishlist_html();
			$wishlist = get_user_meta(get_current_user_id(), 'lgl_wishlists', true);
			$count = is_array($wishlist) ? count($wishlist) : 0;

			wp_send_json_success(array(
				'html'  => $html,
				'count' => $count
			));
		}

		/**
		 * AJAX handler to add or remove a post from a user's wishlist.
		 * Stores data in the 'lgl_wishlists' user meta field as an array.
		 * Returns the updated count to immediately sync the frontend UI.
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
				// Ensure count is passed back to immediately update the mini wishlist UI badge
				wp_send_json_success(array(
					'status' => $status,
					'count'  => count($wishlist)
				));
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
			$plugin_path = LGL_SHORTCODES_PATH . 'templates/shortcodes/' . $template_name . '.php';

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
		 * Implements a 12-hour transient cache to mitigate redundant taxonomy queries.
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

			// Define a unique cache key based on the parent ID
			$cache_key = 'lgl_models_' . $parent_id;

			// Attempt to retrieve pre-compiled results from the database
			$results = get_transient($cache_key);

			if (false === $results) {
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

				// Set the transient cache for 12 hours
				set_transient($cache_key, $results, 12 * HOUR_IN_SECONDS);
			}

			wp_send_json_success($results);
		}

		/**
         * AJAX handler to fetch and render the filtered search results and pagination UI.
         * Compiles taxonomy and meta queries based on serialized form data.
         * Implements an MD5-hashed transient cache to store complex query outputs for 1 hour.
         *
         * @return void
         */
        public function ajax_fetch_results()
        {
            check_ajax_referer('lgl_search_nonce', 'nonce');

            // Generate an MD5 hash of the exact POST payload to create a highly specific cache key
            $query_hash = md5(wp_json_encode($_POST));
            $cache_key  = 'lgl_search_' . $query_hash;

            // Intercept execution and return cached payload if available
            $cached_response = get_transient($cache_key);
            if (false !== $cached_response) {
                wp_send_json_success($cached_response);
            }

            $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
            $paged     = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
            $form_data = array();

            // Parse serialized form data
            if (isset($_POST['form_data'])) {
                parse_str($_POST['form_data'], $form_data);
            }

            $args = array(
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => 9,
                'paged'          => $paged,
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
                $args['tax_query'][] = array(
                    'taxonomy' => 'listing-make-model',
                    'field'    => 'term_id',
                    'terms'    => $model_id
                );
            } elseif ($make_id > 0) {
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

            // Package the data array
            $response_data = array(
                'html'       => $html,
                'pagination' => $pagination_html,
                'count'      => $query->found_posts
            );

            // Save payload to a 1-hour transient
            set_transient($cache_key, $response_data, HOUR_IN_SECONDS);

            wp_send_json_success($response_data);
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

		/**
		 * Purges all cached search result transients from the database.
		 * Triggered automatically upon the creation or modification of vehicle CPTs.
		 *
		 * @return void
		 */
		public function clear_lgl_search_cache()
		{
			global $wpdb;
			// Execute direct SQL to delete all transients matching the search prefix
			$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_lgl_search_%' OR option_name LIKE '_transient_timeout_lgl_search_%'");
		}

		/**
		 * Purges taxonomy-specific transients and subsequent search caches when terms are modified.
		 *
		 * @param int    $term_id  The ID of the term being saved or deleted.
		 * @param int    $tt_id    The taxonomy term ID.
		 * @param string $taxonomy The taxonomy slug.
		 * @return void
		 */
		public function clear_lgl_taxonomy_cache($term_id, $tt_id, $taxonomy)
		{
			// Strictly isolate cache clearing to the designated vehicle taxonomy
			if ($taxonomy === 'listing-make-model') {
				global $wpdb;
				$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_lgl_models_%' OR option_name LIKE '_transient_timeout_lgl_models_%'");

				// Taxonomy changes inherently alter search results, so we cascade the purge
				$this->clear_lgl_search_cache();
			}
		}
	}

	// Instantiate the plugin architecture
	new LGL_Shortcodes();
}
