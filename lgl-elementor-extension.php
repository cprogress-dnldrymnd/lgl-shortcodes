<?php

/**
 * Plugin Name: LGL Elementor Custom Widgets
 * Description: Core framework for registering and loading custom Elementor widgets, scripts, and styles.
 * Plugin URI:  https://digitallydisruptive.co.uk/
 * Version:     1.0.0
 * Author:      Digitally Disruptive - Donald Raymundo
 * Author URI:  https://digitallydisruptive.co.uk/
 * Text Domain: dd-widgets
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Main LGL_Elementor_Extension Class
 *
 * Responsible for initializing custom widgets, scripts, and styles for Elementor.
 */
final class LGL_Elementor_Extension
{

	/**
	 * Instance variable
	 *
	 * Holds the single instance of the class to enforce the Singleton pattern.
	 *
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * Instance access method
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return LGL_Elementor_Extension An instance of the class.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Initializes hooks for Elementor widget registration and script/style enqueueing.
	 */
	public function __construct()
	{
		add_action('elementor/widgets/register', [$this, 'register_widgets']);
		add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
		add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);

		$this->load_theme_framework();
	}

	/**
	 * Load external theme framework
	 *
	 * Executes the theme-level framework load as required. 
	 * Note: Using get_template_directory() creates a hard dependency between this plugin and the active theme.
	 *
	 * @return void
	 */
	private function load_theme_framework()
	{
		$framework_path = get_template_directory() . '/framework/widget-load.php';
		if (file_exists($framework_path)) {
			require_once $framework_path;
		}
	}

	/**
	 * Register Custom Widgets
	 *
	 * Requires and registers individual widget classes with Elementor's widgets manager.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
	 * @return void
	 */
	public function register_widgets($widgets_manager)
	{
		// Example scaffolding for loading a specific widget:
		// require_once plugin_dir_path( __FILE__ ) . 'widgets/class-my-custom-widget.php';
		// $widgets_manager->register( new \My_Custom_Widget() );
	}

	/**
	 * Enqueue Plugin Styles
	 *
	 * Loads the main CSS file for the Elementor widgets on the frontend.
	 *
	 * @return void
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			'dd-elementor-widgets-css',
			plugin_dir_url(__FILE__) . 'assets/css/main.css',
			[],
			'1.0.0'
		);
	}

	/**
	 * Enqueue Plugin Scripts
	 *
	 * Loads the main JavaScript file for the Elementor widgets on the frontend,
	 * utilizing jQuery and Elementor's frontend scripts as dependencies.
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script(
			'dd-elementor-widgets-js',
			plugin_dir_url(__FILE__) . 'assets/js/main.js',
			['jquery', 'elementor-frontend'],
			'1.0.0',
			true // Load in footer
		);
	}
}

// Initialize the plugin.
LGL_Elementor_Extension::instance();
