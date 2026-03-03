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

    /**
     * Instance
     *
     * Holds the single instance of the class to enforce the Singleton pattern.
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @var ElementorWidgets The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance Access Method
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

    /**
     * Widgets Array
     *
     * @var array List of widget directory names.
     */
    public $widgets = array();

    /**
     * Widgets List
     *
     * Defines the directory names of the widgets to be included.
     *
     * @return array Array of widget directories.
     */
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
            'cars-grid',
            'cars-wishlist' // Added: Prevent fatal error on wishlist instantiation below
        );

        return $this->widgets;
    }

    /**
     * Widget Styles
     *
     * Enqueues frontend styles required by the custom widgets.
     *
     * @since 1.0.0
     * @access public
     */
    public function widget_styles()
    {
        wp_enqueue_style('slick-slider', get_template_directory_uri() . '/assets/libs/slick/slick.css', array(), false);
    }

    /**
     * Widget Scripts
     *
     * Registers frontend scripts required by the custom widgets.
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
     * Include Widgets Files
     *
     * Iterates through the registered widget list and requires their core PHP files
     * and any associated skin files.
     *
     * @since 1.0.0
     * @access private
     */
    private function include_widgets_files()
    {
        foreach ($this->widgets_list() as $widget) {
            $widget_file = get_template_directory() . '/framework/widgets/' . $widget . '/widget.php';
            
            if ( file_exists( $widget_file ) ) {
                require_once $widget_file;
            }

            $skins_pattern = get_template_directory() . '/framework/widgets/' . $widget . '/skins/*.php';
            foreach (glob($skins_pattern) as $filepath) {
                if ( file_exists( $filepath ) ) {
                    include $filepath;
                }
            }
        }
    }

    /**
     * Register Categories
     *
     * Injects a custom category into the Elementor editor panel for these widgets.
     *
     * @since 1.0.0
     * @access public
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager instance.
     */
    public function register_categories($elements_manager)
    {
        $elements_manager->add_category(
            'clwyd',
            [
                'title' => esc_html__('Clwyd', 'clwyd')
            ]
        );
    }

    /**
     * Register Widgets
     *
     * Instantiates and registers the custom widgets with the Elementor API.
     * Updated to use the modern Elementor 3.5.0+ `$widgets_manager->register()` method.
     *
     * @since 1.0.0
     * @access public
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
     */
    public function register_widgets($widgets_manager)
    {
        // Require files before instantiation
        $this->include_widgets_files();

        // Modern Elementor Registration API
        $widgets_manager->register(new Widgets\CarLoopItem\Widget_CarLoopItem());
        $widgets_manager->register(new Widgets\CarLoopItemStyle1\Widget_CarLoopItemStyle1());
        $widgets_manager->register(new Widgets\CarLoopItemStyle2\Widget_CarLoopItemStyle2());
        $widgets_manager->register(new Widgets\CarLoopItemStyle3\Widget_CarLoopItemStyle3());
        $widgets_manager->register(new Widgets\CarLoopItemStyle4\Widget_CarLoopItemStyle4());

        $widgets_manager->register(new Widgets\CarsCompare\Widget_CarsCompare());
        $widgets_manager->register(new Widgets\CarsGrid\Widget_CarsGrid());
        $widgets_manager->register(new Widgets\CarsGridList\Widget_CarsGridList());
        $widgets_manager->register(new Widgets\CarsQuickCompare\Widget_CarsQuickCompare());

        $widgets_manager->register(new Widgets\CarsSearch\Widget_CarsSearch());
        $widgets_manager->register(new Widgets\CarsSearchStyle1\Widget_CarsSearchStyle1());
        $widgets_manager->register(new Widgets\CarsSearchStyle2\Widget_CarsSearchStyle2());
        $widgets_manager->register(new Widgets\CarsWishlist\Widget_CarsWishlist());
    }

    /**
     * ElementorWidgets class constructor
     *
     * Initializes action hooks for integrating with the Elementor lifecycle.
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

        // Modern hook for widget registration (Replaces deprecated 'elementor/widgets/widgets_registered')
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }
}

// Instantiate ElementorWidgets Class
ElementorWidgets::instance();