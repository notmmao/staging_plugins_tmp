<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocom_URP
 * @subpackage Woocom_URP/includes
 * @author     Your Name <email@example.com>
 */
class Woocom_URP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocom_URP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'woocom-urp';
		$this->version = '1.1.10';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_global_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocom_URP_Loader. Orchestrates the hooks of the plugin.
	 * - Woocom_URP_i18n. Defines internationalization functionality.
	 * - Woocom_URP_Admin. Defines all hooks for the admin area.
	 * - Woocom_URP_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-customer.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-global.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-product-cache.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocom-urp-ajax.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocom-urp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocom-urp-public.php';

		$this->loader = new Woocom_URP_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocom_URP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocom_URP_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related needed for global functionality
	 * of the plugin.  Mostly public functions also needed by AJAX calls
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_global_hooks() {
		$plugin_global = new Woocom_URP_Global( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_global, 'init' );
		$this->loader->add_action( 'woocommerce_init', $plugin_global, 'woocommerce_init', 60 );

		// Filter the Wholesale Ordering check for wholesale customer
		$this->loader->add_filter('woocom_wholesale_is_wholesale_customer', $plugin_global, 'is_wholesale_customer', 10, 2);
		// Filter taxes disabled for wholesale customers
		$this->loader->add_filter('woocommerce_wholesale_disable_taxes', $plugin_global, 'disable_wholesale_taxes', 10, 1);

		$this->loader->add_action('plugins_loaded', $plugin_global, 'wholesale_ordering_active');

		// Override prices include taxes option for custom prices
		$this->loader->add_action( 'before_woocommerce_init', $plugin_global, 'before_woocommerce_init');

		// Hooks for after WooCommerce is fully loaded
		$this->loader->add_action( 'wp_loaded', $plugin_global, 'wp_loaded');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocom_URP_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// WooCommerce Submenu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_user_role_pricing_page', 60 );

		// Custom user profile fields
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'add_user_meta_fields' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'add_user_meta_fields' );
		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_user_meta_fields' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'save_user_meta_fields' );

		// WooCommerce Admin Hooks
		$this->loader->add_action( 'admin_init', $plugin_admin, 'woocommerce_admin_hooks' );

		// Plugin row links
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2 );

		// Plugin action links
		$this->loader->add_filter( 'plugin_action_links_' . WOOCOM_URP_PLUGIN_BASENAME, $plugin_admin, 'plugin_action_links' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocom_URP_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'set_customer' );
		$this->loader->add_action( 'woocommerce_init', $plugin_public, 'woocommerce_public_hooks' );


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocom_URP_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
