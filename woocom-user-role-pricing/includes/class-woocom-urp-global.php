<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 8/24/2015
 * Time: 9:24 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woocom_URP_Global {
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

	public $wc_prices_include_tax;

	private $calculating_totals = false;

	private $pricing_deals = false;

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

		$this->wc_prices_include_tax = false;

	}

	public function wholesale_ordering_active() {
		if( class_exists('WOO_WholesaleOrdering') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
			$this->wholesale_ordering_active = true;
		} else {
			$this->wholesale_ordering_active = false;
		}
	}

	public function before_woocommerce_init() {
		// actions that need to be setup after init but before woocommerce init
		add_action( 'pre_option_woocommerce_prices_include_tax', array($this, 'custom_prices_include_tax'), 9, 1 );
	}

	public function woocommerce_init() {
		if(!$this->customer)
			$this->set_customer();

		if( $this->is_request( 'frontend' ) && !$this->is_request('order_ajax') ) {
			// Front end hooks
			if( !$this->wholesale_ordering_active || ( !$this->customer->is_wholesale() && wc_tax_enabled() ) ) {
				$this->set_user_vat_status();
			}

			// Price filter slider widget
			add_filter('woocommerce_price_filter_meta_keys', array($this, 'price_filter_meta_keys'), 15, 1);
			add_filter('woocommerce_price_filter_widget_min_amount', array($this, 'modify_min_price'), 15, 1);
			add_filter('woocommerce_price_filter_widget_max_amount', array($this, 'modify_max_price'), 15, 1);

			// Let URP control what to do with no price products - disable hiding of no-price wholesale products in wholesale ordering
			add_filter( 'woocom_wholesale_hide_no_wholesale_price_products', array($this, 'simply_return_false'));

			// Alter the sale status if sale prices are enabled and using regular prices
			add_filter( 'woocommerce_product_is_on_sale', array($this, 'is_product_on_sale'), 5, 2);

			// Hide products?
			if( 'hide' == $this->options['no_price_action'] ) {

				// Product Visibility
				add_action( 'woocommerce_product_query', array($this, 'modify_product_query'), 15, 2 );
				add_filter( 'woocommerce_variation_is_visible', array($this, 'hide_no_price_variation'), 15, 3);
				add_filter( 'woocommerce_product_is_visible', array($this, 'hide_no_price_product'), 15, 2);

				// Modify queries for widgets - non main loop queries
				add_action('pre_get_posts', array($this, 'modify_widget_product_query'));

				//add_filter( 'woocommerce_variation_is_active', array($this, 'active_no_price_variation'), 15, 2);
				//add_action( 'wp', array($this, 'remove_buy_buttons') );
			}
			// Disable Shipping?
			add_filter( 'woocommerce_cart_needs_shipping', array($this, 'needs_shipping'), 15, 1); // called after wholesale ordering plugin filter

			// DON'T ALTER PRICES WHEN CALCULATING TOTALS IN CART SINCE THOSE HAVE ALREADY BEEN SET
			add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'));
			add_action('woocommerce_after_calculate_totals', array($this, 'after_calculate_totals'));
			add_action('woocommerce_before_cart', array($this, 'before_calculate_totals'));
			add_action('woocommerce_after_cart', array($this, 'after_calculate_totals'));
			add_action('woocommerce_review_order_before_cart_contents', array($this, 'before_calculate_totals'));
			add_action('woocommerce_review_order_after_cart_contents', array($this, 'after_calculate_totals'));

		}

		if( $this->wholesale_ordering_active && $this->customer->is_wholesale() ) {
			// Hooks to use when Wholesale Ordering is active & customer is treated as wholesale customer
			if(( '_wholesale_price' !== $this->customer->get_base_price_key() || 1 != (float)$this->customer->get_price_multiplier() ) && $this->is_request( 'frontend' ) ) {
				// filter the wholesale price if they aren't using default wholesale pricing
				add_filter('woocom_wholesale_get_wholesale_price', array($this, 'alter_wholesale_price'), 10, 3);
				add_filter('woocom_wholesale_variation_price', array($this, 'alter_wholesale_variation_price'), 10, 3);
				// Modify prices in cart in case logged out then logged in - called after the Wholesale Ordering hooks so will adjust prices set by it
				add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 15, 1 );
				add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 15, 2 );

				if( '_regular_price' == $this->customer->get_base_price_key() && 1 != (float)$this->customer->get_price_multiplier() ) {
					add_filter('woocommerce_get_regular_price', array($this, 'modify_default_price'), 5, 2);
					add_filter('woocommerce_get_sale_price', array($this, 'modify_default_price'), 5, 2);
				}

			}
			if('_wholesale_price' !== $this->customer->get_base_price_key()) {
				// alter purchasable for customers treated as wholesale but not using wholesale price
				add_filter('woocommerce_wholesale_is_purchasable', array($this, 'can_purchase_product'), 15, 2);
			}

		} elseif ( ( !$this->wholesale_ordering_active || !$this->customer->is_wholesale() ) && $this->is_request( 'frontend' ) ) {
			// Hooks to use when wholesale ordering is not active or customer is not treated as wholesale

			// Alter price only for logged in users - since guests will always use WooCommerce regular price
			if(is_user_logged_in()) {
				add_filter('woocommerce_get_price', array($this, 'get_user_price'), 10, 2);
			}

			add_filter('woocommerce_get_price_html', array($this, 'get_user_html_price'), 15, 2);

			// Purchaseable?
			add_filter( 'woocommerce_variation_is_purchasable', array($this, 'can_purchase_product'), 15, 2);
			add_filter( 'woocommerce_is_purchasable', array($this, 'can_purchase_product'), 15, 2);

			// If using the default price, and anything other than 1 for price multiplier, modify regular and sale prices also
			if( ( 'default' == $this->customer->get_base_price_key() || '_regular_price' == $this->customer->get_base_price_key() ) && 1 != (float)$this->customer->get_price_multiplier() ) {
				add_filter('woocommerce_get_regular_price', array($this, 'modify_default_price'), 5, 2);
				add_filter('woocommerce_get_sale_price', array($this, 'modify_default_price'), 5, 2);
			}


			// ADD TO CART FUNCTIONS - Set the price of products when added to cart, to improve compatibility with other plugins, includes compatibility functions
			// This is done in the Wholesale Ordering plugin for wholesale customer, so put in this section for non-wholesale customers
			add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 15, 1 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 15, 2 );

			// FOR COMPATIBILITY WITH PRODUCT ADDONS - We will calculate wholesale price with addons, so set their filter to false so they don't try to recalculate
			add_filter( 'woocommerce_product_addons_adjust_price', array($this, 'simply_return_false'), 10, 1);

		}


		// ADD ACCOUNT # BILLING FIELD TO WHOLESALE CUSTOMER ACCOUNTS
		add_filter( 'woocommerce_billing_fields', array($this, 'add_billing_fields'), 10, 1);

		// SHOW BILLING ACCOUNT FIELD WHEN DISPLAYING BILLING ADDRESS
		add_filter( 'woocommerce_get_order_address', array($this, 'get_order_address'), 10, 3 );

		// Add Account to admin order edit billing fields
		add_filter('woocommerce_admin_billing_fields', array($this, 'add_order_billing_account_field'));

		// Admin side user profile billing fields
		add_filter('woocommerce_customer_meta_fields', array($this, 'add_billing_account_field'));

		// Add Billing Account # to emails
		add_action( 'woocommerce_email_customer_details', array($this, 'emails_add_billing_account'), 50, 3 );

		// Add custom order meta for pricing info
		add_action( 'woocommerce_new_order', array($this, 'add_pricing_order_meta'), 10, 1);

		// Set unique cache key for base_price variation prices so wrong prices don't get returned
		add_filter('woocommerce_get_variation_prices_hash', array($this, 'modify_variation_prices_hash'), 15, 3);

		if( isset($this->options['update_mini_cart']) && 'yes' === $this->options['update_mini_cart'] ) {
			// fix mini cart ajax update display for pricing deals in Coupon Mode
			add_action('woocommerce_before_mini_cart', array($this, 'before_mini_cart'), 50, 1);
		}

	}

	public function wp_loaded() {

		// DO NOTHING if not logged in and front end
		if(!is_user_logged_in() || !$this->is_request( 'frontend' ) || $this->is_request('order_ajax')) return;

		// DO NOTHING if using regular price & multiplier is 1
		$base = $this->customer->get_base_price_key();
		$multiplier = (float)$this->customer->get_price_multiplier();
		$wholesale = $this->customer->is_wholesale();
		if( (( 'default' == $base && !$wholesale ) || '_regular_price' == $base) && 1 == $multiplier ) return;

		if(is_active_widget( false, false, 'woocommerce_price_filter', true )) {
			// Remove the WC price filter if prices are not default
			remove_filter('loop_shop_post_in', array(WC()->query, 'price_filter'));

			// replace with our own price filter
			add_filter( 'loop_shop_post_in', array( $this, 'price_filter' ) );
		}


	}

	private function set_customer() {
		$user_id = false;
		if($this->is_request('order_ajax')) {
			$user_id = get_option('woocom_urp_admin_ajax_customer_id');
		}
		$this->customer = new Woocom_URP_Customer($user_id);
	}

	public function init() {

		if(!$this->customer)
			$this->set_customer();

		// Update product caches if not updated for current version
		if(!isset($this->options['cache_update_version']) || version_compare($this->options['cache_update_version'], '1.1.5', '<')) {
			Woocom_URP_Product_Cache::update_product_cache(); // update all product caches
			$this->options['cache_update_version'] = '1.1.5';
			update_option('woocom_urp_options', $this->options);
		}

		if(class_exists('VTPRD_Controller')) {
			$this->pricing_deals = true;
			add_filter('vtprd_do_compatability_pricing', array($this, 'simply_return_true'));
		}

	}

	public function before_calculate_totals() {
		$this->calculating_totals = true;
	}

	public function after_calculate_totals() {
		$this->calculating_totals = false;
	}

	public function price_filter_meta_keys($price_keys) {
		if(!is_user_logged_in()) return $price_keys;

		if(!$this->customer)
			$this->set_customer();

		$base = $this->customer->get_base_price_key();
		if('default' == $base) {
			if($this->customer->is_wholesale()) {
				$base = '_wholesale_price';
			} else {
				$base = '_regular_price';
			}
		}

		$new_keys = array($base);

		if( 'yes' === $this->options['use_sale_prices'] && '_regular_price' === $base) {
			$new_keys[] = '_sale_price';
		}

		return $new_keys;
	}

	/**
	 * Price Filter post filter
	 * Modified function from WC to work with URP
	 *
	 * @param array $filtered_posts
	 * @return array
	 */
	public function price_filter( $filtered_posts = array() ) {
		global $wpdb;

		if ( isset( $_GET['max_price'] ) || isset( $_GET['min_price'] ) ) {

			$matched_products = array();
			$min              = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;
			$max              = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 9999999999;

			if(false === $setting = $this->customer->prices_include_tax()) {
				$prices_incl_tax = wc_prices_include_tax();
			} else {
				$prices_incl_tax = 'yes' == $setting ? true : false;
			}

			$multiplier = (float)$this->customer->get_price_multiplier();

			// If displaying prices in the shop including taxes, but prices don't include taxes..
			if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && !$prices_incl_tax ) {
				$tax_classes = array_merge( array( '' ), WC_Tax::get_tax_classes() );

				foreach ( $tax_classes as $tax_class ) {
					$tax_rates = WC_Tax::get_rates( $tax_class );
					$min_class = $min - WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $min, $tax_rates ) );
					$max_class = $max - WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $max, $tax_rates ) );

					$matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
						SELECT DISTINCT ID, post_parent, post_type FROM {$wpdb->posts}
						INNER JOIN {$wpdb->postmeta} pm1 ON ID = pm1.post_id
						INNER JOIN {$wpdb->postmeta} pm2 ON ID = pm2.post_id
						WHERE post_type IN ( 'product', 'product_variation' )
						AND post_status = 'publish'
						AND pm1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', '_price' ) ) ) . "')
						AND (pm1.meta_value * %f) BETWEEN %d AND %d
						AND pm2.meta_key = '_tax_class'
						AND pm2.meta_value = %s
					", $multiplier, $min_class, $max_class, sanitize_title( $tax_class ) ), OBJECT_K ), $min_class, $max_class );

					if ( $matched_products_query ) {
						foreach ( $matched_products_query as $product ) {
							if ( $product->post_type == 'product' ) {
								$matched_products[] = $product->ID;
							}
							if ( $product->post_parent > 0 ) {
								$matched_products[] = $product->post_parent;
							}
						}
					}
				}
			} else {
				$matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
					SELECT DISTINCT ID, post_parent, post_type FROM {$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} pm1 ON ID = pm1.post_id
					WHERE post_type IN ( 'product', 'product_variation' )
					AND post_status = 'publish'
					AND pm1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', '_price' ) ) ) . "')
					AND (pm1.meta_value * %f) BETWEEN %d AND %d
				", $multiplier, $min, $max ), OBJECT_K ), $min, $max );

				if ( $matched_products_query ) {
					foreach ( $matched_products_query as $product ) {
						if ( $product->post_type == 'product' ) {
							$matched_products[] = $product->ID;
						}
						if ( $product->post_parent > 0 ) {
							$matched_products[] = $product->post_parent;
						}
					}
				}
			}

			$matched_products = array_unique( $matched_products );

			// Filter the id's
			if ( 0 === sizeof( $filtered_posts ) ) {
				$filtered_posts = $matched_products;
			} else {
				$filtered_posts = array_intersect( $filtered_posts, $matched_products );
			}
			$filtered_posts[] = 0;
		}

		return (array) $filtered_posts;
	}

	public function alter_wholesale_price($wholesale, $product, $price = '') {
		if( in_array( $product->product_type, array('simple', 'variation', 'external') ) ) {
			return $this->get_user_price($wholesale, $product);
		}
		return $wholesale;
	}

	public function alter_wholesale_variation_price($wholesale, $variation_id, $product) {

		$variation = wc_get_product($variation_id);

		if($variation) {
			$wholesale = $this->get_user_price($wholesale, $variation);
		}

		return $wholesale;

	}

	public function get_user_price($price, $product) {

		// Don't alter price at all for retail customers with default pricing
		if( !$this->customer->is_wholesale() && in_array($this->customer->get_base_price_key(), array('default', '_regular_price')) && 1 == (float)$this->customer->get_price_multiplier() ) {
			return $price;
		}

		if( !in_array( $product->product_type, array('simple', 'variation', 'external') ) || $this->measurement_price_calculator_product($product) || $this->calculating_totals ) {
			return apply_filters('woocom_urp_return_unaltered_price',$price, $product);
		}

		$user_price = apply_filters( 'woocom_urp_public_user_price', $this->customer->get_product_price($product, $price), $price, $product );

		if( $user_price !== false ) {

			if( $this->customer->is_wholesale() && 'yes' !== get_option('woocommerce_wholesale_free_products') && 0 == floatval($user_price) ) {
				$price = ''; // don't count 0 as price for wholesale if not allowing free products
			} else {
				$price = $user_price;
			}
		}

		return $price;
	}

	public function get_user_html_price($price, $product) {

		if(!is_user_logged_in()) {
			$user_price = $price;
		} else {
			$user_price = $this->get_user_price($price, $product);
		}

		if( in_array($product->product_type, array('simple', 'external') ) && '' === $user_price && 'message' == $this->options['no_price_action'] ) {
			return $this->options['no_price_text'];
		}

		if( !in_array( $product->product_type, array('variable', 'grouped') ) ) {
			// correct price for customer should have been retrieved already
			return $price;
		}

		// Variable/Grouped products

		if ( is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART') ) {
			$cart = true;
		} else {
			$cart = false;
		}

		if('variable' === $product->product_type) {
			$prices = $product->get_variation_prices();
			$regular_prices = $prices['regular_price'];
			$variable = true;
		} else {
			$variable = false;
		}

		$children = $product->get_children();

		if ( $children ) {

			$min = $max = 0;
			$min_reg = $max_reg = 0;

			$set = false;

			$multiplier = $this->customer->get_price_multiplier();

			foreach ( $children as $child ) {

				$variation = wc_get_product( $child );
				$child_price = $variation->get_price();
				if(!$cart && '' !== $child_price) {
					$child_price = $variation->get_display_price($child_price);
				}

				if ( $child_price === '' || false === $child_price)
					continue;

				// exclude wholesale only variations from non-wholesale customers
				if( !$this->customer->is_wholesale() && $this->is_product_wholesale_only($child))
					continue;

				$set = true;

				// Actual prices
				if ( $child_price != 0 ) {
					if ( $child_price > $max ) {
						$max = $child_price;
						if( $variable && isset($regular_prices[$child]) ) {
							$max_reg = (float)$regular_prices[$child] * (float)$multiplier;
						}
					}

					if ( $min == 0 || $child_price < $min ) {
						$min = $child_price;
						if( $variable && isset($regular_prices[$child]) ) {

							$min_reg = (float)$regular_prices[$child] * (float)$multiplier;

						}
					}

				}
			}

			$user_price = '';

			if (!$set) {
				if('message' == $this->options['no_price_action']) {
					$user_price = $this->options['no_price_text'];
				}
			} elseif($min == 0 && $max == 0) {
				$user_price = apply_filters('woocom_urp_free_price', __('Free!', 'woocom-urp'));
			} elseif ($min == $max || 0 == $max) {
				$user_price = wc_price( $min ) . $product->get_price_suffix();
			} elseif (0 != $min && 0 != $max) {
				$user_price = wc_price( $min ) . '-' . wc_price( $max ) . $product->get_price_suffix();
			}

			if( $variable && $product->is_on_sale() && $set && '' !== $user_price && ($min > 0 || $max > 0) ) {
				$regular_price     = $min_reg !== $max_reg ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_reg ), wc_price( $max_reg ) ) : wc_price( $min_reg );
				$user_price = $product->get_price_html_from_to( $regular_price, $user_price );
			}

		} else {
			// No children
			$user_price = '';
		}

		return apply_filters('woocom_urp_user_variable_html_price', $user_price, $product, $price);

	}

	public function modify_default_price($price, $product=false) {
		// Called if non-wholesale, and is using default price, and multiplier is not 1
		// Just need to multiply the default (regular or sale) price by the multiplier
		// Also used to modify min and max price for price filter
		$multiplier = $this->customer->get_price_multiplier();
		$new_price = (float)$price * (float)$multiplier;
		return $new_price;
	}

	public function modify_min_price($price) {
		return floor($this->modify_default_price($price, false));
	}

	public function modify_max_price($price) {
		return ceil($this->modify_default_price($price, false));
	}

	public function is_wholesale_customer($is_wholesale, $user) {

		if(!$this->customer)
			$this->set_customer();

		if($this->is_request('ajax') && isset($_POST['user_id'])) {
			return $is_wholesale;
		}

		return $this->customer->is_wholesale();

	}

	public function disable_wholesale_taxes($disable_taxes=false) {
		if( $this->wholesale_ordering_active && $this->customer->is_wholesale() && wc_tax_enabled() ) {
			return $this->customer->is_tax_exempt();
		}
		return $disable_taxes;
	}

	public function set_user_vat_status() {

		if($this->customer->is_tax_exempt()) {
			WC()->customer->set_is_vat_exempt(true);
		} else {
			WC()->customer->set_is_vat_exempt(false);
		}
	}

	public function can_purchase_product($purchasable, $product) {


		$id = 'variation' == $product->product_type ? $product->variation_id : $product->id;
		$purchasable = $this->does_product_have_price($id);

		// Check for wholesale_only
		if($this->wholesale_ordering_active && !$this->customer->is_wholesale() && $this->is_product_wholesale_only($id)) {
			$purchasable = false;
		}

		return apply_filters('woocom_urp_is_purchasable', $purchasable, $product);
	}

	public function hide_no_price_variation($visible, $variation_id, $parent_id) {

		if(!$this->customer->is_wholesale() && $this->is_product_wholesale_only($parent_id)) {
			$visible = false;
		} else {
			$visible = $this->does_product_have_price($variation_id);
		}

		return apply_filters('woocom_urp_hide_no_price_variation', $visible, $variation_id, $parent_id);
	}

	public function hide_no_price_product($visible, $product_id) {

		if( !$this->customer->is_wholesale() && $this->is_product_wholesale_only($product_id)) {
			$visible = false;
		} else {
			$visible = $this->does_product_have_price($product_id);
		}

		return apply_filters('woocom_urp_hide_no_price_product', $visible, $product_id);
	}

	private function is_product_wholesale_only($product_id) {
		// check for wholesale only first - always want to hide wholesale only
		$wholesale_only = false;
		if( $this->wholesale_ordering_active ) {
			$wholesale_only_products = woocom_wholesale_get_wholesale_only_products();
			if(!empty($wholesale_only_products) && in_array($product_id, $wholesale_only_products)) {
				$wholesale_only = true;
			}
		}
		return $wholesale_only;
	}

	private function does_product_have_price($product_id) {

		$product = wc_get_product($product_id);

		if( in_array( $product->product_type, array('variable', 'grouped') ) ) {
			$visible = false;
			$children = $product->get_children();
			foreach($children as $child_id) {
				$child = wc_get_product($child_id);
				$price = $this->customer->get_product_price($child);
				if( false !== $price && '' !== $price ) {
					if( $this->customer->is_wholesale() && 'yes' !== get_option('woocommerce_wholesale_free_products') && 0 == floatval($price) ) {
						continue; // don't count 0 as price for wholesale if not allowing free products
					}
					$visible = true;
					break;
				}
			}
		} else {

			$price = $this->customer->get_product_price($product);

			if( false !== $price && '' !== $price ) {
				if( $this->customer->is_wholesale() && 'yes' !== get_option('woocommerce_wholesale_free_products') && 0 == floatval($price) ) {
					$visible = false; // don't count 0 as price for wholesale if not allowing free products
				} else {
					$visible = true;
				}

			} else {
				$visible = false;
			}
		}

		return $visible;
	}

	private function set_query_exclude($q) {

		$price_key = $this->customer->get_base_price_key();

		if('default' == $price_key) {
			if($this->customer->is_wholesale()) {
				$price_key = '_wholesale_price';
			} else {
				$price_key = '_regular_price';
			}
		}

		$no_prices = Woocom_URP_Product_Cache::get_no_price_products($price_key);

		if( $this->wholesale_ordering_active && !$this->customer->is_wholesale() ) {
			$wholesale_only = woocom_wholesale_get_wholesale_only_products();
			$exclude = array_unique(array_merge($no_prices, $wholesale_only));
		} else {
			$exclude = $no_prices;
		}

		if(!empty($exclude)) {
			// post__in and post__not_in can't both be used
			// if post__in is set we will remove any ids we don't want to show
			$in = $q->get('post__in');
			if(empty($in)) {
				$q->set( 'post__not_in', $exclude );
			} else {
				$new_in = array_diff($in, $exclude);
				$q->set( 'post__in', $new_in );
			}
		}
	}

	/**
	 * Removes products with no prices for customer's base price from the main product query
	 * ONLY Called if the no_price_action option is set to 'hide'
	 *
	 * @param $q
	 * @param $woo_query
	 */
	public function modify_product_query($q, $woo_query) {

		if(!$this->customer) return;

		$this->set_query_exclude($q);

	}

	// called only if no price option set to hide
	// Filter all product queries other than main query - covers all normal widgets
	public function modify_widget_product_query($q) {
		// don't alter main query
		if ( $q->is_main_query() || is_admin() ) {
			return;
		}
		// only want to change product queries
		if ('product' !== $q->get('post_type')) {
			return;
		}

		$this->set_query_exclude($q);
	}

	public function modify_variation_prices_hash($hash, $product, $display) {
		$type = $this->customer->get_base_price_key();
		$type = apply_filters('woocom_urp_variation_prices_hash_type', $type, $hash, $product, $display);
		$hash[] = $type;
		return $hash;
	}

	public function is_product_on_sale($is_on_sale, $product) {
		// If not using the regular woocommerce prices, don't show a sale banner
		if( isset($this->options['use_sale_prices']) && 'yes' === $this->options['use_sale_prices'] &&
		    ( 'default' == $this->customer->get_base_price_key() && !$this->customer->is_wholesale() )
		    || '_regular_price' == $this->customer->get_base_price_key() ) {

			return $is_on_sale;

		} else {
			return false;
		}
	}

	public function needs_shipping($needs_shipping = false) {
		$needs_shipping = $this->customer->disable_shipping() ? false : true;
		return $needs_shipping;
	}

	// BILLING ACCOUNT FIELD FUNCTIONS
	public function add_billing_fields($fields) {
		if( $this->customer->show_billing_account() ) {
			$fields['billing_account'] = array(
				'type'      => 'text',
				'label'     => esc_html($this->options['billing_account_label']),
				'required'  => false,
				'class'     => array('form-row-first'),
				'clear'     => true
			);
		}
		return $fields;
	}

	public function add_billing_account_field($show_fields) {
		$show_fields['billing']['fields']['billing_account'] = array(
			'label' => esc_html($this->options['billing_account_label']),
			'description' => ''
		);
		return $show_fields;
	}

	public function add_order_billing_account_field($billing_fields) {
		$billing_fields['account'] = array('label' => esc_html($this->options['billing_account_label']) );
		return $billing_fields;
	}

	public function get_order_address($address, $type, $order) {
		if( 'billing' == $type && !empty($order->billing_account) ) {
			$address['billing_account'] = $order->billing_account;
		}
		return $address;
	}

	public function emails_add_billing_account($order, $sent_to_admin, $plain_text) {
		if( $this->customer->show_billing_account() && !empty($order->billing_account) ) {
			echo esc_html( $this->options['billing_account_label'] . ' ' . $order->billing_account );
		}
	}

	public function add_pricing_order_meta($order_id) {
		$info = $this->customer->get_pricing_display_info();
		update_post_meta($order_id, 'order_pricing_info', $info);
	}

	public function custom_prices_include_tax($prices_include_tax) {

		if( is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) )  {
			if(function_exists('get_current_screen')) {
				$screen = get_current_screen();
				if('product' == $screen->post_type || 'woocommerce' == $screen->parent_base) {
					return $prices_include_tax;
				}
			}
		}

		// Get original non-cached, unfiltered value of WC option
		if(!$this->wc_prices_include_tax) {
			global $wpdb;
			$this->wc_prices_include_tax = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'woocommerce_prices_include_tax'" );
		}

		if( !$this->customer ) $this->set_customer();

		// returns false or passed-in value to use the option value, or replace with 'yes' or 'no' to override WC option value
		$customer_prices_include_tax = $this->customer->prices_include_tax($this->wc_prices_include_tax);

		return $customer_prices_include_tax;
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

	/************************************
	 * COMPATIBILITY FUNCTIONS *
	 ************************************/

	public function measurement_price_calculator_product($product) {
		if(class_exists('WC_Price_Calculator_Product')) {
			if( $this->is_request('order_ajax') || ! (is_cart() || is_checkout() || is_ajax() ) ) {
				return false;
			} else {
				return WC_Price_Calculator_Product::calculator_enabled($product);
			}

		} else {
			return false;
		}
	}

	/**
	 * add_cart_item function.
	 *
	 * @access public
	 * @param mixed $cart_item
	 * @return mixed $cart_item
	 */
	public function add_cart_item( $cart_item ) {
		// set the wholesale price when added to cart
		if(!empty($cart_item['data'])) {
			// get the current user price from _product
			$_product = $cart_item['data'];
			$item_price = $_product->get_price();
			$price = $this->customer->get_product_price($_product, $item_price); // get the current user price, in case they don't login until checkout and price changes
			$cart_item['data']->set_price( $price  );
		}

		// Measurement Price Calculator adjustment
		if ( ! empty( $cart_item['pricing_item_meta_data'] ) && $price > 0 ) {

			$measurement_amount = $cart_item['pricing_item_meta_data']['_measurement_needed'];
			$measurement_price = (float)$price * (float)$measurement_amount;
			$cart_item['pricing_item_meta_data']['_price'] = $measurement_price; // reset to correct price for display
			$cart_item['data']->set_price( $measurement_price );
		}

		// Product Add-Ons Compatibility
		// Adjust price if addons are set - SHOULD ALWAYS BE LAST, SINCE IT ADDS TO PRICE
		if ( ! empty( $cart_item['addons'] )  ) {

			$extra_cost = 0;

			foreach ( $cart_item['addons'] as $addon ) {
				if ( $addon['price'] > 0 ) {
					$extra_cost += $addon['price'];
				}
			}

			$cart_item['data']->adjust_price( $extra_cost );
		}

		return $cart_item;
	}

	/**
	 * get_cart_item_from_session function.
	 *
	 * @access public
	 * @param mixed $cart_item
	 * @param mixed $values
	 * @return mixed $cart_item
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {

		// Product Add-Ons compatibility function
		if ( ! empty( $values['addons'] ) ) {
			$cart_item['addons'] = $values['addons'];
		}
		$cart_item = $this->add_cart_item( $cart_item );

		return $cart_item;
	}

	public function before_mini_cart() {
		foreach(WC()->cart->get_cart() as $key => $cart_item) {
			$this->add_cart_item($cart_item);
		}
		WC()->cart->calculate_totals();
	}

	public function simply_return_false() {
		return false;
	}

	public function simply_return_true() {
		return true;
	}

	public function return_empty_array($arg1, $arg2) {
		$empty_array = array();
		return $empty_array;
	}

}