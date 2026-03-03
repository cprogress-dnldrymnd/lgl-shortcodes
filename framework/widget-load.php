<?php

namespace AutoArtElementorWidgets;

/**
 * Class ElementorWidgets
 *
 * Main ElementorWidgets class
 * @since 1.0.0
 */
class ElementorWidgets
{

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var ElementorWidgets The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ElementorWidgets An instance of the class.
	 */
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

		$this->widgets = array(
			'Widget_CarLoopItem',
			'cars-compare',
			'cars-quick-compare',
			'cars-search',
			'cars-search-style-1',
			'cars-search-style-2',
			'car-loop-item',
			'car-loop-item-style-1',
			'car-loop-item-style-2',
			'car-loop-item-style-3',
			'car-loop-item-style-4',
			'cars-grid-list',
			'cars-grid'
		);

		return $this->widgets;
	}

	/**
	 * widget_styles
	 *
	 * Load required core files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_styles()
	{
		wp_enqueue_style('slick-slider', get_template_directory_uri() . '/assets/libs/slick/slick.css', array(), false);
	}

	/**
	 * widget_scripts
	 *
	 * Load required core files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function widget_scripts()
	{
		wp_register_script('slick-slider', get_template_directory_uri() . '/assets/libs/slick/slick.min.js', array('jquery'), '', true);
		wp_register_script('select2-min', get_template_directory_uri() . '/assets/libs/select2/select2.min.js', array('jquery'), '', true);
		wp_register_script('elementor-widgets', get_template_directory_uri() . '/framework/widgets/frontend.js', ['jquery'], '', true);
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function include_widgets_files()
	{

		foreach ($this->widgets_list() as $widget) {
			require_once(get_template_directory() . '/framework/widgets/' . $widget . '/widget.php');

			foreach (glob(get_template_directory() . '/framework/widgets/' . $widget . '/skins/*.php') as $filepath) {
				include $filepath;
			}
		}
	}

	/**
	 * Register categories
	 *
	 * Register new Elementor category.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_categories($elements_manager)
	{

		$elements_manager->add_category(
			'autoart',
			[
				'title' => esc_html__('AutoArt', 'autoart')
			]
		);
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_widgets()
	{
		// Its is now safe to include Widgets files
		$this->include_widgets_files();

		// Register Widgets

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItem\Widget_CarLoopItem());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle1\Widget_CarLoopItemStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle2\Widget_CarLoopItemStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle3\Widget_CarLoopItemStyle3());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle4\Widget_CarLoopItemStyle4());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsCompare\Widget_CarsCompare());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsGrid\Widget_CarsGrid());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsGridList\Widget_CarsGridList());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsQuickCompare\Widget_CarsQuickCompare());



		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearch\Widget_CarsSearch());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearchStyle1\Widget_CarsSearchStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearchStyle2\Widget_CarsSearchStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsWishlist\Widget_CarsWishlist());
	}

	/**
	 *  ElementorWidgets class constructor
	 *
	 * Register action hooks and filters
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct()
	{

		// Register widget styles
		add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);

		// Register widget scripts
		add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);

		// Register categories
		add_action('elementor/elements/categories_registered', [$this, 'register_categories']);

		// Register widgets
		add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
	}
}

// Instantiate ElementorWidgets Class
ElementorWidgets::instance();
