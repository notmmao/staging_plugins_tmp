<?php
/**
 * WooCommerce Measurement Price Calculator
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Measurement Price Calculator to newer
 * versions in the future. If you wish to customize WooCommerce Measurement Price Calculator for your
 * needs please refer to http://docs.woothemes.com/document/measurement-price-calculator/ for more information.
 *
 * @package   WC-Measurement-Price-Calculator/Compatibility
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Measurement Price Calculator Compatibility Class
 *
 * @since 3.7.0
 */
class WC_Price_Calculator_Compatibility {


	/**
	 * Construct and initialize the class
	 *
	 * @since 3.7.0
	 */
	public function __construct() {

		// Catalog Visibility Options compatibility
		if ( wc_measurement_price_calculator()->is_plugin_active( 'woocommerce-catalog-visibility-options.php' ) ) {

			// add the pricing calculator and quantity input to products restricted by Catalog Visibility options
			add_action( 'catalog_visibility_after_alternate_add_to_cart_button', array( $this, 'catalog_visibility_options_pricing_calculator_quantity_input' ), 10 );
		}

	}


	/**
	 * Add the pricing calculator and quantity input if the user can view the price
	 *
	 * @since 3.7.0
	 */
	public function catalog_visibility_options_pricing_calculator_quantity_input() {
		global $product;

		// bail if the calculator is not enabled for this product
		if ( ! $product || ! WC_Price_Calculator_Product::calculator_enabled( $product ) ) {
			return;
		}

		// bail if current user can't view the price
		if ( class_exists( 'WC_Catalog_Restrictions_Filters' ) && ! WC_Catalog_Restrictions_Filters::instance()->user_can_view_price( $product ) ) {
			return;
		}

		// render pricing calculator
		wc_measurement_price_calculator()->get_product_page_instance()->render_price_calculator();

		// render quantity input
		if ( ! $product->is_sold_individually() ) {

			woocommerce_quantity_input( array(
				'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
			) );
		}
	}


	/**
	 * Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
	 * Should be safe to remove when we drop WC 2.2 compatibility
	 *
	 * @param  WC_Product $product the product object
	 * @param  string     $price   to calculate, left blank to just use get_price()
	 * @param  integer    $qty     passed on to get_price_including_tax() or get_price_excluding_tax()
	 * @return string
	 */
	public static function get_product_display_price( $product, $price = '', $qty = 1 ) {

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_3() ) {

			return $product->get_display_price( $price, $qty );
		} else {

			if ( $price === '' ) {
				$price = $product->get_price();
			}

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
			$display_price    = $tax_display_mode == 'incl' ? $product->get_price_including_tax( $qty, $price ) : $product->get_price_excluding_tax( $qty, $price );

			return $display_price;
		}
	}


}
