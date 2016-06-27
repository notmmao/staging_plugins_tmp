<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/public
 * @author     Your Name <email@example.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woocom_URP_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $options;

	private $customer;

	private $wholesale_ordering_active;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->options = get_option('woocom_urp_options');

		$this->customer = false;

	}

	public function woocommerce_public_hooks() {

		if( function_exists('woocom_wholesale_is_wholesale_customer') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
			$this->wholesale_ordering_active = true;
		} else {
			$this->wholesale_ordering_active = false;
		}

		if(!$this->customer) {
			$this->set_customer();
		}

		if( $this->customer->show_billing_account() ) {
			// Add billing account field to the My Address template fields
			add_filter('woocommerce_my_account_my_address_formatted_address', array($this, 'add_billing_account'), 10, 3);
			add_filter('woocommerce_formatted_address_replacements', array($this, 'formatted_address_replacements'), 10,2);
			add_filter('woocommerce_localisation_address_formats', array($this, 'add_billing_account_to_formatted_address'), 10, 1);
		}

	}

	public function set_customer() {
		$user_id = false;
		if($this->is_request('order_ajax')) {
			$user_id = get_option('woocom_urp_admin_ajax_customer_id');
		}
		$this->customer = new Woocom_URP_Customer($user_id);
	}

	public function add_billing_account($address, $customer_id, $type) {
		if('billing' == $type) {
			$address['account'] = get_user_meta( $customer_id, 'billing_account', true );
		} else {
			$address['account'] = '';
		}
		return $address;
	}

	public function formatted_address_replacements($replacements, $args) {

		if( !empty($args['account']) ) {
			$replacements['{account}'] = esc_html($this->options['billing_account_label']).' '.$args['account'];
		} else {
			$replacements['{account}'] = '';
		}

		return $replacements;
	}

	public function add_billing_account_to_formatted_address($address_formats) {
		foreach($address_formats as $key => $address_format) {
			$address_formats[$key] = $address_format .= "\n{account}";
		}
		return $address_formats;
	}

	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			case 'order_ajax':
				return ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ( isset( $_POST['item_to_add'] ) || isset( $_GET['term'] ) || ( isset( $_POST['action'] ) && $_POST['action'] == 'woocommerce_calc_line_taxes' ) ) );
		}
	}

}
