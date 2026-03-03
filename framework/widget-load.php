<?php

namespace LGLArtElementorWidgets;

/**
 * Class ElementorWidgets
 *
 * Main ElementorWidgets class responsible for managing Elementor dependencies,
 * categories, and custom widgets registration.
 *
 * @since 1.0.0
 */
class ElementorWidgets
{

	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public $widgets = array();

	public function widgets_list()
	{
		// FIX: Ensure these match the EXACT folder names in /framework/widgets/
		$this->widgets = array(
			'lgl-search',
		);

		return $this->widgets;
	}

	public function widget_styles()
	{
		// Use plugins_url to get the URL relative to this specific file's directory
		wp_enqueue_style('slick-slider', plugins_url('../assets/libs/slick/slick.css', __FILE__), array(), false);
	}

	public function widget_scripts()
	{
		wp_register_script('slick-slider', plugins_url('../assets/libs/slick/slick.min.js', __FILE__), array('jquery'), '', true);
		wp_register_script('select2-min', plugins_url('../assets/libs/select2/select2.min.js', __FILE__), array('jquery'), '', true);
		wp_register_script('elementor-widgets', plugins_url('widgets/frontend.js', __FILE__), ['jquery'], '', true);
	}

	private function include_widgets_files()
	{
		// plugin_dir_path(__FILE__) gets the path to the /framework/ folder where widget-load.php lives
		$framework_dir = plugin_dir_path(__FILE__);

		foreach ($this->widgets_list() as $widget) {
			$widget_file = $framework_dir . 'widgets/' . $widget . '/widget.php';

			if (file_exists($widget_file)) {
				require_once $widget_file;
			}

			$skins_pattern = $framework_dir . 'widgets/' . $widget . '/skins/*.php';
			foreach (glob($skins_pattern) as $filepath) {
				if (file_exists($filepath)) {
					include $filepath;
				}
			}
		}
	}

	public function register_categories($elements_manager)
	{
		$elements_manager->add_category(
			'clwyd',
			[
				'title' => esc_html__('Clwyd', 'clwyd')
			]
		);
	}

	public function register_widgets($widgets_manager)
	{
		$this->include_widgets_files();

		// FIX: Using fully qualified namespaces to guarantee the classes are found.
		// Ensure that the namespace defined at the top of each widget.php matches these exactly.



		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\LGLSearch\Widget_LGLSearch());
	}

	public function __construct()
	{
		add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);
		add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
		add_action('elementor/elements/categories_registered', [$this, 'register_categories']);
		add_action('elementor/widgets/register', [$this, 'register_widgets']);
	}
}

// Instantiate ElementorWidgets Class
ElementorWidgets::instance();
