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
			'Widget_CarLoopItem',
			'Widget_CarLoopItemStyle1',
			'Widget_CarLoopItemStyle2',
			'Widget_CarLoopItemStyle3',
			'Widget_CarLoopItemStyle4',
			'Widget_CarsCompare',
			'Widget_CarsGrid',
			'Widget_CarsGridList',
			'Widget_CarsQuickCompare',
			'Widget_CarsSearch',
			'Widget_CarsSearchStyle1',
			'Widget_CarsSearchStyle2',
			'Widget_CarsWishlist',
		);

		return $this->widgets;
	}

	public function widget_styles()
	{
		wp_enqueue_style('slick-slider', get_template_directory_uri() . '/assets/libs/slick/slick.css', array(), false);
	}

	public function widget_scripts()
	{
		wp_register_script('slick-slider', get_template_directory_uri() . '/assets/libs/slick/slick.min.js', array('jquery'), '', true);
		wp_register_script('select2-min', get_template_directory_uri() . '/assets/libs/select2/select2.min.js', array('jquery'), '', true);
		wp_register_script('elementor-widgets', get_template_directory_uri() . '/framework/widgets/frontend.js', ['jquery'], '', true);
	}

	private function include_widgets_files()
	{
		foreach ($this->widgets_list() as $widget) {
			$widget_file = get_template_directory() . '/framework/widgets/' . $widget . '/widget.php';

			if (file_exists($widget_file)) {
				require_once $widget_file;
			}

			$skins_pattern = get_template_directory() . '/framework/widgets/' . $widget . '/skins/*.php';
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

		if (class_exists('\LGLArtElementorWidgets\Widgets\CarLoopItem\Widget_CarLoopItem')) {
			$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarLoopItem\Widget_CarLoopItem());
		}


		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarLoopItemStyle1\Widget_CarLoopItemStyle1());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarLoopItemStyle2\Widget_CarLoopItemStyle2());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarLoopItemStyle3\Widget_CarLoopItemStyle3());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarLoopItemStyle4\Widget_CarLoopItemStyle4());

		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsCompare\Widget_CarsCompare());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsGrid\Widget_CarsGrid());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsGridList\Widget_CarsGridList());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsQuickCompare\Widget_CarsQuickCompare());

		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsSearch\Widget_CarsSearch());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsSearchStyle1\Widget_CarsSearchStyle1());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsSearchStyle2\Widget_CarsSearchStyle2());
		$widgets_manager->register(new \LGLArtElementorWidgets\Widgets\CarsWishlist\Widget_CarsWishlist());
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
