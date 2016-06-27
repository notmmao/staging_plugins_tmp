<?php
/*
 * Plugin Name: 	Woocommerce Advanced Shipping
 * Plugin URI: 		http://www.jeroensormani.com/
 * Donate link: 	http://www.jeroensormani.com/donate/
 * Description: 	WooCommerce Advanced Shipping allows you to configure advanced shipping conditions with <strong>conditional logic!</strong>
 * Version: 		1.0.4
 * Author: 			Jeroen Sormani
 * Author URI: 		http://www.jeroensormani.com/
 * Text Domain: 	woocommerce-advanced-shipping
 */


/**
 * Copyright Jeroen Sormani
 * Class Woocommerce_Advanced_Shipping
 *
 * Main WAS class, add filters and handling all other files
 *
 * @class		Woocommerce_Advanced_Shipping
 * @version		1.0.0
 * @author		Jeroen Sormani
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WooCommerce_Advanced_Shipping {


	/**
	 * Version.
	 *
	 * @since 1.0.1
	 * @var string $version Plugin version number.
	 */
	public $version = '1.0.4';


	/**
	 * Instace of WooCommerce_Advanced_Shipping.
	 *
	 * @since 1.0.1
	 * @access private
	 * @var object $instance The instance of WAS.
	 */
	private static $instance;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Check if WooCommerce is active
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				return;
			endif;
		endif;

		// Initialize plugin parts
		$this->init();

		do_action( 'woocommerce_advanced_shipping_init' );

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.1
	 *
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.1
	 */
	public function init() {

		// Add hooks/filters
		$this->was_hooks();

		// Load textdomain
		$this->load_textdomain();

		/**
		 * Require matching conditions hooks.
		 */
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-was-match-conditions.php';
		$this->matcher = new WAS_Match_Conditions();

		/**
		 * Post Type class
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-was-post-type.php';
		$this->post_type = new WAS_Post_Type();

		/**
		 * Load ajax methods
		 */
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-was-ajax.php';
		$this->ajax = new WAS_Ajax();

	}


	/**
	 * Hooks.
	 *
	 * Initialize plugin hooks like actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function was_hooks() {

		// Initialize shipping method class
		add_action( 'woocommerce_shipping_init', array( $this, 'was_shipping_method' ) );

		// Add shipping method
		add_action( 'woocommerce_shipping_methods', array( $this, 'was_add_shipping_method' ) );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'was_admin_enqueue_scripts' ) );

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.1
	 */
	public function load_textdomain() {

		// Load textdomain
		load_plugin_textdomain( 'woocommerce-advanced-shipping', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}


	/**
	 * Add shipping method.
	 *
	 * Configure and add all the shipping methods available.
	 *
	 * @since 1.0.0
	 */
	public function was_shipping_method() {

		/**
		 * Advanced shipping method
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-was-method.php';
		$this->was_method = new WAS_Advanced_Shipping_Method();

	}


	/**
	 * Add shipping method.
	 *
	 * Add configured methods to available shipping methods.
	 *
	 * @since 1.0.0
	 */
	public function was_add_shipping_method( $methods ) {

		if ( class_exists( 'WAS_Advanced_Shipping_Method' ) ) :
			$methods[] = 'WAS_Advanced_Shipping_Method';
		endif;

		return $methods;

	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue style and java scripts.
	 *
	 * @since 1.0.0
	 */
	public function was_admin_enqueue_scripts() {

		// Style script
		wp_enqueue_style( 'was-style', plugins_url( 'assets/css/admin-style.css', __FILE__ ), array(), $this->version );

		// Javascript
		wp_enqueue_script( 'was-js', plugins_url( 'assets/js/was-js.js', __FILE__ ), array( 'jquery' ), $this->version, true );

	}


}


/**
 * The main function responsible for returning the WooCommerce_Advanced_Shipping object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WAS()->method_name(); ?>
 *
 * @since 1.0.1
 *
 * @return object WooCommerce_Advanced_Shipping class object.
 */
if ( ! function_exists( 'WAS' ) ) :

 	function WAS() {
		return WooCommerce_Advanced_Shipping::instance();
	}

endif;

WAS();
