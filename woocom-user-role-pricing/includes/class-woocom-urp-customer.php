<?php

/**
 * The customer class.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP_Customer
 */

/**
 * The customer class.
 * Create a URP Customer from given user_id, or from current user if no user_id provided
 *
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP_Customer
 * @author     Your Name <email@example.com>
 */
class Woocom_URP_Customer {

	public $user = false;

	public $role = false;

	public $options;

	public $id;

	public $wholesale_ordering_active;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      int    $user_id       The WordPress user id.
	 */
	public function __construct( $user_id = false ) {

		if( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		if($user_id > 0) {
			$this->user = get_user_by( 'id', $user_id);
			if($this->user) {
				$user_roles = $this->user->roles;
				$this->role = array_shift($user_roles);
				$this->id = (int)$user_id;
			}

		}

		$this->options = get_option('woocom_urp_options');

		if( class_exists('WOO_WholesaleOrdering') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
			$this->wholesale_ordering_active = true;
		} else {
			$this->wholesale_ordering_active = false;
		}

	}

	public function get_user_meta( $key=false ) {

		$user_meta = false;

		if(!$this->user) return false;

		// First see if the user has a custom value
		if ( $this->id > 0 && $key ) {

			$user_meta = get_user_meta( $this->id, 'urp_user_'.$key, true );

			if ( '' == $user_meta || 'role' == $user_meta ) {

				// if not set to unique user value, get the role value
				if( $this->role && isset($this->options[$key][$this->role]) ) {

					$user_meta = $this->options[$key][$this->role];

				} else {

					$user_meta = false;

				}
			}
		}

		return $user_meta;

	}

	public function get_base_price_key() {

		$base_price_key = $this->get_user_meta('base_price');

		if( !$base_price_key || !in_array( $base_price_key, array_keys($this->options['prices']) ) ) {
			$base_price_key = 'default'; // if nothing returned, or not in array of prices, set to "default"
		}

		return apply_filters('woocom_urp_user_base_price_key', $base_price_key, $this->role, $this->user);

	}

	public function get_price_multiplier() {

		$multiplier = $this->get_user_meta('price_multiplier');

		if( !$multiplier ) {
			$multiplier = 1; // default to 1
		}

		return apply_filters('woocom_urp_user_price_multiplier', $multiplier, $this->role, $this->user);
	}

	public function get_product_price($product=false, $price = false) {
		if( !$product ) {
			return false;
		}

		// first get the base price key
		$base_price_key = $this->get_base_price_key();

		if($this->wholesale_ordering_active && $this->is_wholesale() && 'default' == $base_price_key) {
			$base_price_key = '_wholesale_price';
		}

		if( 'default' == $base_price_key || '_regular_price' == $base_price_key ) {
			if(isset($this->options['use_sale_prices']) && 'yes' === $this->options['use_sale_prices'] ) {
				// using _price should get regular or sale price according to WooCommerce settings
				$base_price_key = '_price';
			} else {
				// otherwise get the specific regular price
				$base_price_key = '_regular_price';
			}

		}

		$id = $product->id;
		if ('variation' == $product->product_type) {
			$id = $product->variation_id;
		}
		if ( in_array( $product->product_type, array('simple', 'variation', 'external') ) ) {
			$base_price = get_post_meta($id, $base_price_key, true);
		} else {
			$base_price = $product->get_price();
		}

		// get the price multiplier for the user
		$multiplier = $this->get_price_multiplier();

		if('' === $base_price) {
			if('revert' == $this->options['no_price_action']) {
				if( $this->wholesale_ordering_active && $this->is_wholesale() && '_wholesale_price' !== $base_price_key ) {
					$new_price = get_post_meta($id, '_wholesale_price', true);
				} else {
					$new_price = $price;
				}
			} else {
				$new_price = '';
			}

		} else {
			$new_price = (float)$base_price * (float)$multiplier;
		}

		return apply_filters('woocom_urp_user_product_price', $new_price, $product, $price);
	}

	private function check_yes_no_field($field = false) {
		$result = false;
		if($field && 'yes' == $this->get_user_meta($field)) {
			$result = true;
		}
		return $result;
	}

	public function is_wholesale() {

		// return false if wholesale ordering is not active
		if(!class_exists('WOO_WholesaleOrdering')) return false;

		// Special case for Wholesale Customer role
		if( 'wholesale_customer' == $this->role ) {
			if( 'no' == get_user_meta( $this->id, 'urp_user_is_wholesale', true ) ) {
				// Don't want to check role settings for wholesale customer, only check if user_meta value overrides wholesale customer
				return false;
			} else {
				return true;
			}
		}

		// Special case for admin
		if ('administrator' == $this->role) {
			if('plugin' == get_user_meta( $this->id, 'urp_user_is_wholesale', true ) || '' == get_user_meta( $this->id, 'urp_user_is_wholesale', true ) ) {
				return 'yes' == get_option('woocommerce_wholesale_admin') ? true : false;
			} else {
				return 'yes' == get_user_meta( $this->id, 'urp_user_is_wholesale', true ) ? true : false;
			}
		}

		return $this->check_yes_no_field('is_wholesale');
	}

	public function is_tax_exempt() {

		$tax_exempt = $this->get_user_meta('tax_exempt');

		if(!$tax_exempt) {
			if($this->is_wholesale()) {
				$tax_exempt = get_option('woocommerce_disable_wholesale_taxes');
			} else {
				$tax_exempt = wc_tax_enabled() ? 'no' : 'yes';
			}
		}

		$is_tax_exempt = 'yes' == $tax_exempt ? true : false;

		return $is_tax_exempt;

	}

	public function prices_include_tax($prices_include_tax = false) {
		// return false or passed-in value to use the option value, or replace with 'yes' or 'no' to override WC option value
		if($this->user) {
			$base_price_key = $this->get_base_price_key();
			if( '_regular_price' === $base_price_key || ( 'default' === $base_price_key && !$this->is_wholesale()) ) {
				return $prices_include_tax;
			} elseif( '_wholesale_price' === $base_price_key || ( 'default' === $base_price_key && $this->is_wholesale()) ) {
				return get_option('woocommerce_wholesale_prices_include_tax', 'no' );
			} else {

				if(isset($this->options['prices_incl_tax'][$base_price_key]) && 'yes' == $this->options['prices_incl_tax'][$base_price_key]) {
					$prices_include_tax = 'yes';
				} else {
					$prices_include_tax = 'no';
				}

			}

		}
		return $prices_include_tax;
	}

	public function disable_shipping() {

		$disable_shipping = $this->get_user_meta('disable_shipping');

		if(!$disable_shipping) {
			if($this->is_wholesale()) {
				$disable_shipping = get_option('woocommerce_disable_wholesale_shipping');
			} else {
				$disable_shipping = 'yes' == get_option('woocommerce_calc_shipping') ? 'no' : 'yes';
			}
		}

		$is_shipping_disabled = 'yes' == $disable_shipping ? true : false;

		return $is_shipping_disabled;

	}

	public function show_billing_account() {
		return $this->check_yes_no_field('billing_account');
	}

	public function get_pricing_display_info() {
		$info = array();
		$prices = $this->options['prices'];
		if($this->user) {
			$base_price_key = $this->get_base_price_key();
			$base_price_name = esc_html($prices[$base_price_key]);
			$info['base_price'] = $base_price_name;
			$info['multiplier'] = $this->get_price_multiplier();
			$info['tax_exempt'] = $this->is_tax_exempt() ? __('Yes', 'woocom-urp') : __('No', 'woocom-urp');
			$info['disable_shipping'] = $this->disable_shipping() ? __('Yes', 'woocom-urp') : __('No', 'woocom-urp');
			$info['is_wholesale'] = $this->is_wholesale() ? __('Yes', 'woocom-urp') : __('No', 'woocom-urp');
		} else {
			// Guest/Default
			$info['base_price'] = $prices['_regular_price'];
			$info['multiplier'] = '1';
			$info['tax_exempt'] = __('No', 'woocom-urp');
			$info['disable_shipping'] = __('No', 'woocom-urp');
			$info['is_wholesale'] = __('No', 'woocom-urp');
		}
		return $info;

	}

}
