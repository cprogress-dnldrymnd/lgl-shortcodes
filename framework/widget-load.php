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
			'site-information',
			'site-information-style-1',
			'site-social',
			'site-social-style-2',
			'site-copyright',
			'instagram-posts',
			'contact-information',
			'page-breadcrumb',
			'mobile-menu',
			'step-list',
			'post-grid',
			'post-grid-style-1',
			'post-grid-style-2',
			'post-loop-item',
			'post-loop-item-style-1',
			'post-loop-item-style-2',
			'cars-wishlist',
			'mini-wishlist',
			'cars-compare',
			'mini-compare',
			'cars-quick-compare',
			'cars-search',
			'cars-search-style-1',
			'cars-search-style-2',
			'car-loop-item',
			'car-loop-item-style-1',
			'car-loop-item-style-2',
			'car-loop-item-style-3',
			'car-loop-item-style-4',
			'orbit-circle',
			'pattern-background',
			'buble-background',
			'product-loop-item',
			'account-login',
			'highlighted-heading',
			'list-image-text',
			'card-image-text-grid',
			'list-icon-text',
			'list-faq',
			'dealer-loop-item',
			'service-hours',
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
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\SiteInformation\Widget_SiteInformation());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\SiteInformationStyle1\Widget_SiteInformationStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\SiteSocial\Widget_SiteSocial());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\SiteSocialStyle2\Widget_SiteSocialStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\SiteCopyright\Widget_SiteCopyright());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\InstagramPosts\Widget_InstagramPosts());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ContactInformation\Widget_ContactInformation());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PageBreadcrumb\Widget_PageBreadcrumb());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\MobileMenu\Widget_MobileMenu());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\StepList\Widget_StepList());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsWishlist\Widget_CarsWishlist());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\MiniWishlist\Widget_MiniWishlist());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsCompare\Widget_CarsCompare());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\MiniCompare\Widget_MiniCompare());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsQuickCompare\Widget_CarsQuickCompare());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearch\Widget_CarsSearch());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearchStyle1\Widget_CarsSearchStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsSearchStyle2\Widget_CarsSearchStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItem\Widget_CarLoopItem());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle1\Widget_CarLoopItemStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle2\Widget_CarLoopItemStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle3\Widget_CarLoopItemStyle3());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarLoopItemStyle4\Widget_CarLoopItemStyle4());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostGrid\Widget_PostGrid());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostGridStyle1\Widget_PostGridStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostGridStyle2\Widget_PostGridStyle2());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostLoopItem\Widget_PostLoopItem());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostLoopItemStyle1\Widget_PostLoopItemStyle1());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PostLoopItemStyle2\Widget_PostLoopItemStyle2());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\OrbitCircle\Widget_OrbitCircle());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PatternBackground\Widget_PatternBackground());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\BubleBackground\Widget_BubleBackground());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ProductLoopItem\Widget_ProductLoopItem());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\AccountLogin\Widget_AccountLogin());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\HighlightedHeading\Widget_HighlightedHeading());

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ListImageText\Widget_ListImageText());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CardImageTextGrid\Widget_CardImageTextGrid());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ListIconText\Widget_ListIconText());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ListFaq\Widget_ListFaq());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\DealerLoopItem\Widget_DealerLoopItem());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\ServiceHours\Widget_ServiceHours());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsGridList\Widget_CarsGridList());
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\CarsGrid\Widget_CarsGrid());
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
