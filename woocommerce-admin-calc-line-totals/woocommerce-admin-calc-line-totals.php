<?php
/*
Plugin Name: WooCommerce Admin Calc Line Totals
Plugin URI: http://www.foxrunsoftware.net/articles/wordpress/woocommerce-order-admin-calc-line-totals/
Description: Adds the ability to automatically calculate line totals from the Order Admin
Author: Justin Stern
Author URI: http://www.foxrunsoftware.net
Version: 1.0

	Copyright: © 2012 Justin Stern (email : justin@foxrunsoftware.net)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	if ( ! class_exists( 'WC_Admin_Calc_Line_Totals' ) ) {
		

		class WC_Admin_Calc_Line_Totals {
			
			var $plugin_url;
			
			public function __construct() {
				
				if ( is_admin() ) {
					add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ), 1 );
					add_action( 'wp_ajax_woocommerce_calc_line_totals', array( &$this, 'calc_line_totals' ) );
				}
			}
			
			
			/** Admin *********************************************************/
			
			
			/**
			 * Enqueue any needed admin scripts
			 */
			public function admin_scripts() {
				// Get admin screen id
			    $screen = get_current_screen();
    			if ( in_array( $screen->id, array( 'edit-shop_order', 'shop_order' ) ) ) {
					wp_enqueue_script( 'order_writepanel', $this->plugin_url() . '/assets/js/order-writepanel.js' );
					
					$woocommerce_admin_calc_line_totals = array(
						'ajax_url'               => admin_url( 'admin-ajax.php' ),
						'calc_line_totals_label' => __( 'Calc line totals &uarr;' ),
						'calc_line_totals'       => __( "Calculate line totals?" ),
						'calc_totals_nonce'      => wp_create_nonce( 'calc-totals' )
					 );
			
					wp_localize_script( 'order_writepanel', 'woocommerce_admin_calc_line_totals', $woocommerce_admin_calc_line_totals );
				}
			}
			
			
			/** Ajax *********************************************************/
			
			
			/**
			 * Ajax method which calculates a line item total
			 */
			public function calc_line_totals() {
				
				check_ajax_referer( 'calc-totals', 'security' );
				
				$item_id        = isset( $_POST['item_id'] )        ? esc_attr( $_POST['item_id'] )        : null;
				$item_variation = isset( $_POST['item_variation'] ) ? esc_attr( $_POST['item_variation'] ) : null;
				$item_quantity  = isset( $_POST['item_quantity'] )  ? esc_attr( $_POST['item_quantity'] )  : null;
				
				if ( ! $item_id ) return;
				
				// Get product details
				if ( $item_variation )
					$_product = new WC_Product_Variation( $item_variation );
				else
					$_product = new WC_Product( $item_id );
				
				// line subtotal/total
				$line_subtotal = 0;
				$line_total    = 0;
				
				if ( $_product->exists() && $item_quantity > 0 ) {
					
					// use the cart to calculate the line totals
					$cart = new WC_Cart();
					
					$cart->cart_contents[] = array(
						'product_id'   => $item_id,
						'variation_id' => $item_variation,
						'variation'    => '',
						'quantity'     => $item_quantity,
						'data'         => $_product
					);
					
					$cart->calculate_totals();
					
					$line_total    = $cart->cart_contents[0]['line_total'];
					$line_subtotal = $cart->cart_contents[0]['line_subtotal'];
				}
				
				echo json_encode( array(
					'line_subtotal' => $line_subtotal,
					'line_total'    => $line_total
				) );
				
				// Quit out
				die();
			}
			
			
			/** Helpers *********************************************************/
			
			
			/**
			 * Get the plugin url.
			 *
			 * @access public
			 * @return string
			 */
			private function plugin_url() {
				if ( $this->plugin_url ) return $this->plugin_url;
				return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
			}
		}

		// finally instantiate our plugin class and add it to the set of globals
		$GLOBALS['wc_admin_calc_line_totals'] = new WC_Admin_Calc_Line_Totals();
	}
}