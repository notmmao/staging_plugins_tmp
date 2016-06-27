<?php
/**
 * Plugin Name: WooCommerce Save & Share Cart
 * Description: *** Previously know as WooCommerce Email Cart *** - Empower anyone using your store to Save & Share their Cart.
 * Author: cxThemes
 * Author URI: http://codecanyon.net/user/cxThemes/portfolio
 * Plugin URI: http://codecanyon.net/item/email-cart-for-woocommerce/5568059
 * Version: 2.06
 * Text Domain: email-cart
 * Domain Path: /languages/
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC_Email_Cart
 * @author    cxThemes
 * @category  WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Define Constants
 */
define( 'WC_EMAIL_CART_VERSION', '2.06' );
define( 'WC_EMAIL_CART_REQUIRED_WOOCOMMERCE_VERSION', 2.2 );
define( 'WC_EMAIL_CART_PLUGIN_BASENAME', plugin_basename(__FILE__) );

define( 'WC_EMAIL_CART_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_EMAIL_CART_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Update Check
 */
require 'plugin-updates/plugin-update-checker.php';
$cxecrt_update = new PluginUpdateChecker(
	'http://cxthemes.com/plugins/woocommerce-email-cart/email-cart.json',
	__FILE__,
	'email-cart'
);

/**
 * Includes (before WC active checks)
 */
include( plugin_dir_path( __FILE__ ) . 'includes/class-ec-install.php' );

/**
 * Check if WooCommerce is active, and is required WooCommerce version.
 */
if ( ! WC_Email_Cart::is_woocommerce_active() || version_compare( get_option( 'woocommerce_version' ), WC_EMAIL_CART_REQUIRED_WOOCOMMERCE_VERSION, '<' ) ){
	add_action( 'admin_notices', array( 'WC_Email_Cart', 'woocommerce_inactive_notice' ) );
	return;
}

/**
 * Includes
 */
include( plugin_dir_path( __FILE__ ) . 'includes/class-ec-settings.php' );
include( plugin_dir_path( __FILE__ ) . 'models/Saved_Cart.php' );
include( plugin_dir_path( __FILE__ ) . 'admin/cart_index_interface.php' );
include( plugin_dir_path( __FILE__ ) . 'admin/cart_edit_interface.php' );
include( plugin_dir_path( __FILE__ ) . 'includes/helpers.php' );

/**
 * Instantiate plugin.
 */
$cxecrt = WC_Email_Cart::get_instance();

/**
 * Main Class.
 */
class WC_Email_Cart {
	
	private $id = 'woocommerce_email_cart';
	
	public $saved_cart;
	
	public $backup_cart;
	
	private static $instance;
	
	/**
	* Get Instance creates a singleton class that's cached to stop duplicate instances
	*/
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}
	
	/**
	* Construct empty on purpose
	*/
	private function __construct() {}
	
	/**
	* Init behaves like, and replaces, construct
	*/
	public function init(){
		
		global $wpdb, $cxecrt_options;
		
		// Save settings at startup.
		register_activation_hook( __FILE__, array( $this, 'set_cxecrt_settings' ) );
		
		// Load Translations.
		add_action( 'init', array( $this, 'load_translation' ) );
		
		// Ajax - Send Cart
		add_action( 'wp_ajax_send_cart_email_ajax', array( $this, 'send_cart_email_ajax' ) );
		add_action( 'wp_ajax_nopriv_send_cart_email_ajax', array( $this, 'send_cart_email_ajax' ) );
		
		// Ajax - Save Cart
		add_action( 'wp_ajax_save_cart_and_get_link_ajax', array( $this, 'save_cart_and_get_link_ajax' ) );
		add_action( 'wp_ajax_nopriv_save_cart_and_get_link_ajax', array( $this, 'save_cart_and_get_link_ajax' ) );
		
		// Enqueue Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		
		// Add Save & Share Cart Buttom - if the option is ticked in the settings
		if ( 'yes' == cxecrt_get_option( 'cxecrt_show_cart_page_button' ) ) {
			add_action( 'woocommerce_cart_collaterals', array( $this, 'cart_page_call_to_action' ) );
		}

		// Load the modal on the frontend anyway in anticipation of the user deeplinking
		add_action( 'wp_footer', array( $this, 'cart_page_load_form' ) );
		
		
		// New from CR -----------
		
		// Delete Old Carts
		add_action('woocommerce_cart_reset', array( $this, 'woocommerce_scheduled_cart_delete') );
		
		// Product Index
		$cxecrt_options['show_products_on_index'] = ( 'yes' == cxecrt_get_option( 'cxecrt_show_products_on_index' ) );

		// Cart Expiration Opt-In Checkbox
		$cxecrt_options['cxecrt_cart_expiration_active'] = ( 'yes' == cxecrt_get_option( 'cxecrt_cart_expiration_active' ) );

		// Cart Expiration Day range
		$cart_expiration_opt_in = cxecrt_get_option( 'cxecrt_cart_expiration_active' );
		$cart_expiration = cxecrt_get_option( 'cxecrt_cart_expiration_time' );

		if ( $cart_expiration > 0 && $cart_expiration_opt_in != 'no') {
			if ( ! wp_next_scheduled('woocommerce_cart_reset' ) ) {
				wp_schedule_event( time(), 'hourly', 'woocommerce_cart_reset');
			}
			$cxecrt_options['cxecrt_cart_expiration_time'] = $cart_expiration;
		}
		else {
			if ( wp_next_scheduled('woocommerce_cart_reset' ) ) {
				$timestamp = wp_next_scheduled( 'woocommerce_cart_reset' );
				wp_unschedule_event( $timestamp, 'woocommerce_cart_reset');
			}
			$cxecrt_options['cxecrt_cart_expiration_time'] = false;
		}

		if ( is_admin() ) {
			$Edit_Interface = new WCEC_Edit_Interface();
			$Cart_Index     = new WCEC_Cart_Index_Page();
		}
		
		// Recover the cart from an email. MAIN *********
		add_action( 'wp_loaded', array( $this, 'load_cart_main' ), 100 );
		// add_action( 'wp_loaded', array( $this, 'load_cart_main' ) );
		
		// Recover the cart from an email. MAIN *********
		
		// Debug testing - Email Preview
		add_action( 'wp_loaded', array( $this, 'send_cart_email_test' ) );
		
		// Old legacy method.
		add_action( 'wp_loaded', array( $this, 'add_product_to_cart' ) );
	}
	
	public function list_notice() {
		?>
		<div class="loco-message error loco-warning">
			<p><?php _e( 'All these carts are real live sent carts so rather dont delete them. You can set ', 'email-cart' ) ?></p>
		</div>
		<?php
	}
	
	// Start from Cart Reports
	
	/**
	 * This is the main routine that acts when the visitor makes a change to their cart.
	 * First we save the user id and useragent info ( if the option is set to "on" ) Next we
	 * populate the saved_cart object with the products, owner ( if exists ) and session id.
	 */
	public function save_cart_and_get_link() {
		global $cxecrt_options;
		
		// ---------- Main Check ----------
		
		// Check for when to do this
		// if ( ! isset( $_GET['cxecrt-send-email-cart'] ) ) return;
		
		// Make sure this is only ever run once.
		remove_action( 'woocommerce_set_cart_cookies', array( $this, 'send_cart' ), 100 );
		
		// ---------- Checks ----------
		
		// Don't save if is a search engine
		if ( detect_search_engines( $_SERVER['HTTP_USER_AGENT'] ) ) return;
		
		// Security Check
		/*
		if ( function_exists('wp_verify_nonce') )
			wp_verify_nonce( 'cxecrt-sent-nonce' );
		else
			WC()->verify_nonce( 'cxecrt-sent-nonce' );
		*/
		
		// ---------- Save Cart ----------
		$saved_cart = new WCEC_Saved_Cart();
		$saved_cart->save_cart();
		
		// Save custom Post-Title
		if ( isset( $_POST['saved-cart-name'] ) && cxecrt_test_user_role( 'shop_manager' ) ) {
			
			wp_update_post( array(
				'ID'           => $saved_cart->cart_id,
				'post_title'   => $_POST['saved-cart-name'],
			) );
		}
		
		// Save retrieve cart redirect.
		if ( isset( $_POST['saved-cart-name'] ) && cxecrt_test_user_role( 'shop_manager' ) ) {
			
			update_post_meta(
				$saved_cart->cart_id,
				'_cxecrt_redirect_to',
				sanitize_text_field( $_REQUEST['landing_page'] )
			);
		}
		
		// Check if a landing destination was specified.
		$destination = isset( $_POST['landing_page'] ) ? $_POST['landing_page'] : NULL;
		
		// Return feedback JSON.
		echo json_encode( array(
			'cart_url' => $this->get_retrieve_cart_url( $saved_cart->cart_id, $destination ),
			'cart_id'  => $saved_cart->cart_id,
		) );
		
		die();
	}
	
	public function send_cart_email( $post_id = NULL ) {
		
		// Gather posted fields.
		$to_email_address   = ( isset( $_POST['to_email_address'] ) ) ? $_POST['to_email_address'] : null;
		$from_email_address = ( isset( $_POST['from_email_address'] ) ) ? $_POST['from_email_address'] : null;
		$from_name          = ( isset( $_POST['from_name'] ) ) ? $_POST['from_name'] : null;
		$email_subject      = ( isset( $_POST['email_subject'] ) ) ? $_POST['email_subject'] : null;
		$email_content      = ( isset( $_POST['email_content'] ) ) ? $_POST['email_content'] : null;
		$landing_page       = ( isset( $_POST['landing_page'] ) ) ? $_POST['landing_page'] : null;
		
		$post_id            = ( isset( $_POST['cart_id'] ) ) ? $_POST['cart_id'] : $post_id;
		
		// Prep emails address's - convert comma separated to array, and sanitize.
		$to_email_address  = $this->prep_email_addresses( $to_email_address );
		
		// Prep From Email.
		$from_email_address = get_option( 'woocommerce_email_from_address' );
		
		// Prep From Name (if not set then use the SiteName)
		if ( ! $from_name ) $from_name = trim( get_bloginfo( 'name' ) );
		
		// Prep email checkout URL
		// Check if a landing destination was specified.
		$destination = ( 'checkout' == $landing_page ) ? 'checkout' : 'cart' ;
		$checkout_url = $this->get_retrieve_cart_url( $post_id, $destination );
		
		ob_start();
		?>
		<a href="<?php echo $checkout_url ?>" title="<?php _e( 'Click here to view your cart.', 'email-cart' ) ?>" >
			<?php _e('Click here to view your cart.', 'email-cart') ?>
			<br>
			<br>
			<?php echo $checkout_url; ?>
		</a>
		<?php
		$cart_link = ob_get_clean();
		
		
		$product_items = $this->render_email_order_items( $checkout_url );
		
		// Prep email content.
		// $formatted_email_content = trim( wptexturize( wpautop( nl2br( $email_content ) ) ) );
		
		$formatted_email_content = '';
		$formatted_email_content .= '<div class="cxecrt-email-content">';
		$formatted_email_content .= wpautop( nl2br( $email_content ) );
		$formatted_email_content .= $product_items;
		$formatted_email_content .= '</div>';
		
		
		// Merge data into the message.
		/*
		$formatted_email_content = str_replace(
			array(
				'<p>[product_items]</p>',
				'[product_items]',
			),
			array(
				$product_items,
				$product_items,
			),
			$formatted_email_content
		);
		*/
		
		// $formatted_email_content = $product_items;
		
		// Set the email type as HTML
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
		
		// Prep Admin Copy Email.
		$formatted_email_content = $this->format_email( $formatted_email_content, apply_filters( 'cxecrt_email_heading', '&nbsp;' ) );
		
		// Prep Headers.
		$headers = array();
		$headers[] = "From: $from_name <$from_email_address>";
		
		// Prep Cc Address
		// $headers[] = 'Cc: ' . $cc_email_address;
		
		// Prep Bcc Address
		// $headers[] = 'Bcc: '.$bcc_email_address;
		
		// Allow modification of the our headers.
		$headers = apply_filters( 'cxecrt_headers', $headers );
		
		// Send Main Email.
		$emails_sent = true;
		foreach ( $to_email_address as $address ) {
			$status = wp_mail( $address, $email_subject, $formatted_email_content, $headers );
			
			// Record if any of the emails fail sending.
			if ( ! $status ) $emails_sent = false;
		}
		
		// Debugging
		if ( isset( $_GET['cxecrt-preview-email'] ) ) {
			echo $formatted_email_content;
			exit();
		}

		remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
		
		echo json_encode(array(
			'send_status' => ( $emails_sent ) ? 'sent' : 'not_sent',
		));
		
		die();
	}
	
	public function get_retrieve_cart_url( $post_id, $destination = NULL ) {
		
		// Prep email checkout URL
		$checkout_url = get_home_url();
		
		// Add `cxecrt-retrieve-cart`
		$checkout_url = add_query_arg( 'cxecrt-retrieve-cart', $post_id, $checkout_url );
		
		// Add `cxecrt-redirect`
		if ( isset( $destination ) && 'cart' !== $destination )
			$checkout_url = add_query_arg( 'cxecrt-redirect', $destination, $checkout_url );
		
		return $checkout_url;
	}
	
	public function render_email_order_items( $checkout_url ) {
		
		/**
		 * Generate CSS.
		 */
		ob_start();
		
		$text_color = '#606060';
		$link_color = '#41607B';
		$button_color = '#55BC75';
		?>
		<style>
		
		/* WooCommerce - Email Width (for Dev testing only) */
		#header_wrapper,
		#template_header,
		#template_body,
		.main-body { width: 690px; padding-left: 0; padding-right: 0; }
		
		/* General Styling */
		.cxecrt-email-content { font-size: 20px; font-family: Arial, sans-serif; color: <?php echo $text_color; ?>; text-align: center; line-height: 1.4em; letter-spacing: -0.03em; }
		.cxecrt-email-content table { color: <?php echo $text_color; ?>; }
		.cxecrt-email-content table tr td { padding: 0; }
		.cxecrt-email-content p { margin: 0 0 0.5em; }
		
		/* Button Row */
		.cxecrt-email-content .shop_table_top_spacing_td { padding: 25px 0; text-align: center; }
		.cxecrt-email-content .buttonage { border-radius: 3px; line-height: 1em; background-color: <?php echo $button_color; ?>; box-shadow: inset 0 -5px 0 rgba(0, 0, 0, 0.08); }
		.cxecrt-email-content .buttonage a { font-size: 17px; font-weight: bold; font-family: Arial, sans-serif; text-decoration: none;  color: #ffffff; border-radius: 3px; padding: 17px 25px; border: 1px solid <?php echo $button_color; ?>; display: inline-block; }
		
		/* Cart Products */
		.cxecrt-email-content .shop_table_holder_td { padding: 20px 0; }
		.cxecrt-email-content .shop_table { font-family: Arial, sans-serif; font-size: 15px; line-height: 1em; text-align: left; width: 100%; border-spacing: 0; width: 100%; border-top: 1px solid #EEEEEE; }
		.cxecrt-email-content .shop_table tr th,
		.cxecrt-email-content .shop_table tr td { background-color: #F5F5F5; border-bottom: 1px solid #EEEEEE; }
		.cxecrt-email-content .shop_table tr:nth-child(2n) th,
		.cxecrt-email-content .shop_table tr:nth-child(2n) td { background-color: #FAFAFA; }
		.cxecrt-email-content .shop_table td { padding: 0.85em 1.3em; }
		.cxecrt-email-content .shop_table td.product-thumbnail { padding-left: 0.85em; padding-right: 1.3em; width: 10px; }
		.cxecrt-email-content .shop_table td.product-name a { font-weight: bold; text-decoration: none; color: <?php echo $link_color ?>; font-size: 17px; }
		.cxecrt-email-content .shop_table td.product-name .variations { margin: 3px 0 0; }
		.cxecrt-email-content .shop_table td.product-name .variations .variation { margin: 3px 0 0; }
		.cxecrt-email-content .shop_table td.product-numbers { text-align: center; }
		.cxecrt-email-content .shop_table td.product-numbers .product-numbers-row { margin: 0 0 3px; }
		.cxecrt-email-content .shop_table .product-times { font-size: 11px; font-weight: bold; }
		.cxecrt-email-content .shop_table td .product-subtotal .amount { font-weight: bold; }
		
		/* Cart Totals */
		.cxecrt-email-content .shop_table_total_td { padding: 20px 0; }
		.cxecrt-email-content .cart_totals { text-align: left; }
		.cxecrt-email-content .cart_totals h2 { text-align: center; font-size: 17px; color: <?php echo $text_color; ?>; padding: 0 0 9px 0; margin: 0; }
		.cxecrt-email-content .cart_totals table { text-align: left; width: 100%; font-family: Arial, sans-serif; font-size: 17px; }
		.cxecrt-email-content .cart_totals table th,
		.cxecrt-email-content .cart_totals table td { text-align: left; padding: 0.85em 1.1em; }
		.cxecrt-email-content .cart_totals table td small { font-size: 12px; }
		.cxecrt-email-content .cart_totals table tr:nth-child(2n) td { background: #FAFAFA; }
		.cxecrt-email-content .wc-cart-shipping-notice { margin: 0; padding: 0; text-align: center; }
		.cxecrt-email-content .wc-cart-shipping-notice small { text-align: center; display: block; font-family: Arial; font-size: 15px; line-height: 1.3em; padding: 0; color: #A5A5A5; margin: 0; }
		
		.cxecrt-email-content .buttonage-link-holder { padding: 1.85em 0 1em; text-align: center; }
		.cxecrt-email-content .buttonage-link-holder,
		.cxecrt-email-content .buttonage-link { font-family: Arial; font-size: 15px; line-height: 1.3em; color: #A5A5A5; }
		
		</style>
		<?php
		$css = ob_get_clean();
		
		/**
		 * Generate HTML.
		 */
		ob_start();
		
		
		/**
		 * Clear coupons before we send the cart.
		 */
		
		// We must make WC think we are on the cart so that `calculate_totals()` also calculates the grand totals.
		define( 'WOOCOMMERCE_CART', TRUE ); // Use this alternate method
		// add_filter( 'woocommerce_is_checkout', '__return_true' ); // Filter only introduced in WC2.3
		
		// Store applied_coupons from WC session before we empty it.
		$applied_coupons = WC()->session->get( 'applied_coupons' );
		
		// Empty the applied_coupons WC session.
		WC()->session->set( 'applied_coupons', array() );
		
		// Re-get the cart from the session - so the empty applied_coupons is re-applied.
		WC()->cart->get_cart_from_session();
		
		// Re-calculate totals now that we've emptied coupons.
		WC()->cart->calculate_totals();
		
		?>
		
		<!-- ---------- Cart Button ---------- -->
		<table width="100%" class="shop_table_top_spacing" cellspacing="0" cellpadding="0">
			<tr>
				<td class="shop_table_top_spacing_td">
					
					<table border="0" cellspacing="0" cellpadding="0" style="margin: auto;">
						<tr>
							<td class="buttonage" align="center">
								<a  href="<?php echo $checkout_url; ?>" target="_blank">
									<!--[if mso]>&nbsp;<![endif]-->
									<?php _e( 'GO TO COLLECTION', 'email-cart' ) ?>
									<!--[if mso]>&nbsp;<![endif]-->
								</a>
							</td>
						</tr>
					</table>
					
				</td>
			</tr>
			
		</table>
		<!-- ---------- / Cart Button ---------- -->
		
		
		<!-- ---------- Cart Products ---------- -->
		<table width="100%" class="shop_table_holder" cellspacing="0" cellpadding="0">
			<tr>
				<td class="shop_table_holder_td">
				
					<table class="shop_table cart" cellspacing="0" cellpadding="0">
						<?php if ( FALSE ) : ?>
							<thead>
								<tr>
									<th class="product-thumbnail">&nbsp;</th>
									<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
									<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
									<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
									<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
								</tr>
							</thead>
						<?php endif; ?>
						<tbody>
							
							<!-- START TEMPLATE COMPARE: `woocommerce/templates/cart/cart.php` -->
							<?php
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
								$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
									?>
									<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
										
										<!-- TEMPLATE COMPARE NOTE: Removed by us -->
										<!--
										<td class="product-remove">
											<?php
												echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
													'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
													esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
													__( 'Remove this item', 'woocommerce' ),
													esc_attr( $product_id ),
													esc_attr( $_product->get_sku() )
												), $cart_item_key );
											?>
										</td>
										-->
										
										<td class="product-thumbnail">
											<?php
												$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

												if ( ! $_product->is_visible() ) {
													echo $thumbnail;
												} else {
													printf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink( $cart_item ) ), $thumbnail );
												}
											?>
										</td>

										<td class="product-name" data-title="<?php _e( 'Product', 'woocommerce' ); ?>">
											<?php
												if ( ! $_product->is_visible() ) {
													echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';
												} else {
													echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s </a>', esc_url( $_product->get_permalink( $cart_item ) ), $_product->get_title() ), $cart_item, $cart_item_key );
												}

												// Meta data (TEMPLATE COMPARE NOTE: We customized this to output individual variations)
												$item_data = WC()->cart->get_item_data( $cart_item, FALSE );
												if ( $item_data ) {
													$item_data = array_filter( explode( "\n", $item_data ) );
													echo '<div class="variations"><div class="variation">' . implode( '</div><div class="variation">', $item_data ) . '</div></div>';
												}

												// Backorder notification
												if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
													echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
												}
											?>
										</td>

										<!-- TEMPLATE COMPARE NOTE: Removed by us -->
										<!--
										<td class="product-price" data-title="<?php _e( 'Price', 'woocommerce' ); ?>">
											<?php
												// echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
											?>
										</td>
										-->
										
										<!-- TEMPLATE COMPARE NOTE: we simplified the following into one cell -->
										<td class="product-numbers">
										
											<div class="product-numbers-row">
												
												<span class="product-quantity" data-title="<?php _e( 'Quantity', 'woocommerce' ); ?>">
													<?php
														if ( $_product->is_sold_individually() ) {
															//$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
															$product_quantity = 1;
														} else {
															/*
															$product_quantity = woocommerce_quantity_input( array(
																'input_name'  => "cart[{$cart_item_key}][qty]",
																'input_value' => $cart_item['quantity'],
																'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
																'min_value'   => '0'
															), $_product, false );
															*/
															$product_quantity = $cart_item['quantity'];
														}

														echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
													?>
												</span>
												
												<span class="product-times">
													x
												</span>
												
												<span class="product-price">
													<?php
														echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
													?>
												</span>
												
											</div>

											<div class="product-numbers-row">
												<span class="product-subtotal" data-title="<?php _e( 'Total', 'woocommerce' ); ?>">
													<?php
														echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
													?>
												</span>
											</div>
											
										</td>
									</tr>
									<?php
								}
							}
							?>
							<!-- END TEMPLATE COMPARE -->
							
						</tbody>
					</table>
					
				</td>
			</tr>
		</table>
		<!-- ---------- / Cart Products ---------- -->
		
		
		<!-- ---------- Cart Totals ---------- -->
		<table width="100%" class="shop_table_total" cellspacing="0" cellpadding="0">
			<tr>
				<td class="shop_table_total_td">
					
					<table class="shop_table_bottom_spacing" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="25%"></td>
							<td width="50%">
								
								<?php // $this->render_cart_totals( __( 'Totals', 'email-cart' ) ); ?>
								<?php $this->render_cart_totals(); ?>
								
								<table width="100%" class="shop_table_top_spacing" cellspacing="0" cellpadding="0">
									<tr>
										<td class="buttonage-link-holder">
											<?php _e( "Here's the link to your cart collection if the buttons don't work ", 'email-cart' ) ?> <a href="<?php echo $checkout_url ?>" class="buttonage-link"><?php echo $checkout_url; ?></a>
										</td>
									</tr>
								</table>
								
							</td>
							<td width="25%"></td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
		<!-- ---------- / Cart Totals ---------- -->
		
		<?php
		$content = ob_get_clean();
		
		/**
		 * Return coupons after we send the cart.
		 */
		
		// Re-apply the previously stored applied_coupons to WC session.
		WC()->session->set( 'applied_coupons', $applied_coupons );

		// Re-get the cart from the session - so the restored applied_coupons is re-applied.
		WC()->cart->get_cart_from_session();
		
		// Re-calculate totals now that we've restored the coupons.
		WC()->cart->calculate_totals();
		
		
		// return $this->apply_inline_styles( $content, $css );
		return $content . ' ' . $css;
	}
	
	public function render_cart_totals( $title = FALSE ) {
		
		// Remove Shipping requirement from the Cart.
		add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );
		
		// Remove the Proceed-to-Checkout Button from the Cart.
		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
		
		// Get the Cart Totals from the template.
		ob_start();
		woocommerce_cart_totals();
		$html = ob_get_clean();
		
		// Remove the <h2>Cart Totals</h2>.
		if ( class_exists( 'DomDocument' ) ) {
			
			$dom = new DomDocument();
			$dom->loadHTML( $html );
			
			// Remove <!DOCTYPE
			$dom->removeChild( $dom->doctype );

			// Remove <html><body></body></html>
			$dom->replaceChild( $dom->firstChild->firstChild->firstChild, $dom->firstChild );
			
			// Get the heading and remove if exists.
			$heading = $dom->getElementsByTagName( 'h2' );
			if ( $heading->item(0) ) {
				$heading->item(0)->parentNode->removeChild( $heading->item(0) );
			}
			
			$html = $dom->saveHTML();
		}
		
		// Remove the <h2>Cart Totals</h2>. (Old Method)
		// $html = substr( $html, 0, strrpos( $html, '<h2>' ) ) . $title . substr( $html, ( strrpos( $html, '</h2>' ) + 5 ), strlen( $html ) );
		
		// put a breakline before shipping info.
		$html = str_replace( '<p class="wc-cart-shipping-notice">', '<br/><p class="wc-cart-shipping-notice">', $html );
		
		echo $html;
	}

	public function load_cart_main() {
		
		// only perform recover from member mail
		if ( isset( $_GET['cxecrt-retrieve-cart'] ) ) {
			
			// Get post ID with Cart to be loaded.
			$cart_id = $_GET['cxecrt-retrieve-cart'];
			
			// Main function - to load Cart into session.
			$status = $this->load_cart_from_post( $cart_id );
			
			// `Cart no longer exists` notice.
			if ( ! $status ) {
				wc_add_notice( __( "We're sorry but it seems the cart you're looking for no longer exists. Please feel free to browse our store while you're here.", 'email-cart' ), 'success' );
				return;
			}
			
			/**
			 * Store when the Cart is retrieved, and how many times.
			 */
			update_post_meta( $cart_id, '_cxecrt_cart_retrieved_date', time() );
			
			$retrieve_count = intval( get_post_meta( $cart_id, '_cxecrt_cart_retrieved_count', TRUE ) ) + 1;
			update_post_meta( $cart_id, '_cxecrt_cart_retrieved_count', $retrieve_count );
			
			// (Discarded solution)
			// Remove the hook so doesn't execute twice (this was not the problem).
			// remove_action( 'woocommerce_init', array( $this, 'load_cart_main' ) );
			
			// Display a notice that the cart was successfully retrieved.
			wc_add_notice( __( 'Cart Retrieved', 'email-cart' ), 'success' );
			
			// Get the redirect URL - if `cxecrt-redirect` has been specified.
			
			$redirect_url = cxecrt_get_woocommerce_cart_url();
			if ( isset( $_GET['cxecrt-redirect'] ) ) {
				
				if ( 'checkout' == $_GET['cxecrt-redirect'] )
					$redirect_url = cxecrt_get_woocommerce_checkout_url();
				elseif ( 'home' == $_GET['cxecrt-redirect'] )
					$redirect_url = get_home_url();
				elseif ( 'cart' == $_GET['cxecrt-redirect'] )
					$redirect_url = cxecrt_get_woocommerce_cart_url();
				else
					$redirect_url = $_GET['cxecrt-redirect'];
			}
			
			$redirect_url = remove_query_arg( 'cxecrt-retrieve-cart', $redirect_url );
			
			// (Discarded) WooCommerce stores notices, then outputs them on the next page and discards them - but means don't have to success status $_GET var
			// $redirect_url = add_query_arg( array( 'email-cart-loaded' => 'yes' ), $redirect_url );
			
			// Redirect after successful cart retrieval.
			wp_redirect( $redirect_url );
			// echo "<script>document.location = '{$redirect_url}';</script>"; // javascript redirect for safety.
			exit;
		}
	}
	
	/**
	 * Load a cart by passing it a post_id or a cart array.
	 *
	 * @param  int/array   $post_id   Takes either a post_id of a post with a cart meta, or the cart array.
	 */
	public function load_cart_from_post( $cart ) {
		
		if ( ! is_array( $cart ) ) {
			// Passed $cart is a post_id so first try
			// get the cart data from post if it exists.
			$cart = get_post_meta( $cart, '_cxecrt_cart_data', true );
			
			// Bail - return false as 0 if no cart data exists.
			if ( ! $cart ) return FALSE;
		}
		
		// Prep the Cart.
		$cart = maybe_unserialize( $cart );
		
		// s( $cart );
		
		// Make sure that all required Cart resources are loaded.
		// WooCommerce only loads cart stuff on the front-end.
		cxecrt_maybe_load_required_cart_resources();
		
		// Set the Cart to the New Retrived Cart and load it into the WC session.
		WC()->cart->empty_cart( false ); // Always empty first so that `get_cart_from_session` loads the new cart.
		// WC()->cart->empty_cart( TRUE );
		WC()->session->cart = $cart;
		WC()->cart->get_cart_from_session();
		// WC()->cart->set_cart_cookies();
		
		// Success - feedback $cart.
		return TRUE;
	}
	
	/**
	 * Backup the existiing cart
	 * so it can be restored a bit later.
	 */
	public function overwrite_saved_cart() {
		
		// Make sure we have post_id.
		if ( ! isset( $_GET['post'] ) ) return;
		
		$post_id = $_GET['post'];
		
		// Make sure that all required Cart resources are loaded.
		// WooCommerce only loads cart stuff on the front-end.
		cxecrt_maybe_load_required_cart_resources();
		
		// Check if there is a previous backup.
		// If there is one then don't overwrite it (for safety, to protect it)
		$overwrite_cart = array();
		if ( isset( WC()->session->cart ) & ! empty( WC()->session->cart ) ) {
			$overwrite_cart = WC()->session->cart;
		}
		
		update_post_meta( $post_id, '_cxecrt_cart_data', maybe_serialize( $overwrite_cart ) );
	}
	
	/**
	 * Backup the existiing cart
	 * so it can be restored a bit later.
	 */
	public function backup_current_cart() {
		
		// Make sure that all required Cart resources are loaded.
		// WooCommerce only loads cart stuff on the front-end.
		cxecrt_maybe_load_required_cart_resources();
		
		// Check if there is a previous backup.
		// If there is one then don't overwrite it (for safety, to protect it)
		// if ( ! get_transient( 'cxecrt_current_cart_backup' ) ) {
			
			// Backup the cart to a transient.
			$current_cart_backup = ( ! empty( WC()->session->cart ) ) ? WC()->session->cart : 'empty';
			set_transient( 'cxecrt_current_cart_backup', $current_cart_backup );
		// }
	}
	
	/**
	 * Partner funcion to the previous `Backup`
	 * Restores the backed up current cart.
	 */
	public function restore_current_cart() {
		
		// Check if there is a backed up cart.
		if ( $current_cart_backup = get_transient( 'cxecrt_current_cart_backup' ) ) {
			
			// Make sure that all required Cart resources are loaded.
			// WooCommerce only loads cart stuff on the front-end.
			cxecrt_maybe_load_required_cart_resources();
			
			if ( 'empty' == $current_cart_backup )
				$this->load_cart_from_post( array() );
			else
				$this->load_cart_from_post( $current_cart_backup );
			
			// Delete transient after we've used it.
			delete_transient( 'cxecrt_current_cart_backup' );
		}
	}
	
	/**
	 * Apply CSS to content inline.
	 *
	 * @param string|null $content
	 * @param string|null $css
	 * @return string
	 */
	function apply_inline_styles( $content = '', $css = '' ) {
		
		// load Emogrifier.
		// if ( !class_exists('Emogrifier') ) {
		// 	require_once( WC_EMAIL_CONTROL_DIR . '/includes/emogrifier/Emogrifier.php' );
		// }
		
		try {
			
			// Apply Emogrifier to inline the CSS.
			$emogrifier = new Emogrifier();
			$emogrifier->setHtml( $content );
			$emogrifier->setCss( strip_tags( $css ) );
			$content = $emogrifier->emogrify();
		}
		catch ( Exception $e ) {

			$logger = new WC_Logger();
			$logger->add( 'emogrifier', $e->getMessage() );
		}
		
		return $content;
	}
	
	/**
	 * Function to periodically clear old carts, if this is configured in the settings.
	 */
	public function woocommerce_scheduled_cart_delete() {
		global $cxecrt_options;
		
		$expiration_days = $cxecrt_options['cxecrt_cart_expiration_time'];
		$opt_in_settings = $cxecrt_options['cxecrt_cart_expiration_active'];
		
		if ( $expiration_days && $expiration_days > 0 && $opt_in_settings ) {
			cxecrt_delete( $expiration_days );
		}
	}
	
	// End from Cart Reports

	/**
	 * Save settings at startup.
	 */
	public function set_cxecrt_settings() {
		$cxecrt_settings = WC_Email_Cart_Settings::get_settings();
		foreach ( $cxecrt_settings as $option ) {
			if ( isset( $option['id'] ) && isset( $option['default'] ) ) {
				// TRUE and FALSE when saving options is '1' and '' respectively, so FALSE means nothing is set yet.
				if ( FALSE === get_option( $option['id'] ) ) {
					update_option( $option['id'], $option['default']);
				}
			}
		}
	}

	/**
	 * Localization
	 */
	public function load_translation() {
		
		// Domain ID - used in eg __( 'Text', 'pluginname' )
		$domain = 'email-cart';
		
		// get the languages locale eg 'en_US'
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		
		// Look for languages here: wp-content/languages/pluginname/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/{$domain}/{$domain}-{$locale}.mo" ); // Don't mention this location in the docs - but keep it for legacy.
		
		// Look for languages here: wp-content/languages/plugins/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/plugins/{$domain}-{$locale}.mo" );
		
		// Look for languages here: wp-content/languages/pluginname-en_US.mo
		load_textdomain( $domain, WP_LANG_DIR . "/{$domain}-{$locale}.mo" );
		
		// Look for languages here: wp-content/plugins/pluginname/languages/pluginname-en_US.mo
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . "/languages/" );
	}

	public function save_cart_and_get_link_ajax() {
		
		// check_ajax_referer( 'order-item', 'security' );
		
		$this->save_cart_and_get_link();
		
		die();
	}
	
	public function send_cart_email_ajax() {
		
		// check_ajax_referer( 'order-item', 'security' );

		$this->send_cart_email();
		
		die();
	}
	
	public function send_cart_email_test() {
		
		if ( ! isset( $_GET['cxecrt-preview-email'] ) ) return;
		
		$this->send_cart_email( 1230 );
		
		die();
	}
	
	public function prep_email_addresses( $email_address ) {
		
		$divider = ( FALSE === strpos( $email_address, ';' ) ) ? ',' : ';' ;
		
		$exploded_email_addresses = explode( $divider, $email_address );
		
		$collected_email_addresses = array();
		foreach ( $exploded_email_addresses as $email ) {
			$collected_email_addresses[] = sanitize_email( $email );
		}
		
		return $collected_email_addresses;
	}
	
	/**
	 * Format Email
	 *
	 * Takes the message and wraps the WooCommerce styling
	 * around it so it looks nice like one of their mails.
	 *
	 * @param   string   $message   The content intended to go inside the email.
	 */
	public function format_email( $message = '', $heading = '' ) {
		
		// Must do this to activate the hooks/filters.
		WC()->mailer();
		
		ob_start();
		do_action( 'woocommerce_email_header', $heading );
		echo $message;
		do_action( 'woocommerce_email_footer' );
		$email_body = ob_get_clean();
		$mailer = new WC_Email_Customer_Note(); // Create this just to get to the style-inline() function
		if ( method_exists( $mailer, 'style_inline' ) ) {
			
			// Only came about after WC2.3 - if not exists then inline CSS is already be done.
			$email_body = $mailer->style_inline( $email_body );
		}
		
		return $email_body;
	}

	/**
	 * WP Mail Filter - Set email body as HTML
	 */
	public function set_html_content_type() {
		return 'text/html';
	}
	
	/**
	 * Include admin scripts
	 */
	public function admin_scripts() {
		global $wp_query, $post, $pagenow, $screen;
		$screen = get_current_screen();
		
		if (
				'settings_page_email_cart_settings' == $screen->id || // Save & Share Cart Settings
				'woocommerce_page_woocommerce_email_cart' == $screen->id || // Save & Share Cart Main
				'edit-stored-carts' == $screen->id || // Post-type List
				'stored-carts' == $screen->id // Post Edit
			){
			
			/**
			 * General.
			 */
			wp_enqueue_style(
				'woocommerce_admin_styles',
				WC()->plugin_url() . '/assets/css/admin.css'
			);
			wp_enqueue_style(
				'cxecrt-admin-css',
				WC_EMAIL_CART_URI . '/assets/css/ec-admin-style.css',
				'',
				WC_EMAIL_CART_VERSION,
				'screen'
			);
			wp_enqueue_script(
				'cxecrt-js',
				WC_EMAIL_CART_URI . '/assets/js/email-cart.js',
				array('jquery'),
				WC_EMAIL_CART_VERSION
			);
			wp_localize_script(
				'cxecrt-js',
				'cxecrt_params',
				array(
					'remove_item_notice' => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'email-cart' ),
					'plugin_url'         => WC_EMAIL_CART_URI,
					'ajax_url'           => admin_url('admin-ajax.php'),
					'order_item_nonce'   => wp_create_nonce("order-item"),
					'home_url'           => get_home_url(),
					'version'            => WC()->version,
					'calc_totals_nonce'  => wp_create_nonce("calc-totals"),
					
					// Internationalization.
					'i18n_overwrite_cart_confirm' => __( 'This will overwrite this saved cart. Are you sure you want to proceed?', 'email-cart' ),
					'i18n_delete_cart_confirm' => __( 'Doing this will mean the saved cart is no longer be retrievable by whoever saved it. Are you sure you want to proceed?', 'email-cart' ),
				)
			);
			
			/**
			 * Tip-Tip - tooltip plugin.
			 */
			wp_enqueue_style(
				'cxecrt-tip-tip',
				WC_EMAIL_CART_URI . '/assets/js/tip-tip/tipTip.css',
				array(),
				WC_EMAIL_CART_VERSION
			);
			wp_enqueue_script(
				'cxecrt-tip-tip',
				WC_EMAIL_CART_URI . '/assets/js/tip-tip/jquery.tipTip.minified.js',
				array(
					'jquery',
				),
				WC_EMAIL_CART_VERSION
			);
		}
	}

	public function frontend_scripts() {
		
		/**
		 * Tip-Tip - tooltip plugin.
		 */
		wp_enqueue_style(
			'cxecrt-tip-tip',
			WC_EMAIL_CART_URI . '/assets/js/tip-tip/tipTip.css',
			array(),
			WC_EMAIL_CART_VERSION
		);
		wp_enqueue_script(
			'cxecrt-tip-tip',
			WC_EMAIL_CART_URI . '/assets/js/tip-tip/jquery.tipTip.minified.js',
			array(
				'jquery',
			),
			WC_EMAIL_CART_VERSION,
			TRUE
		);
		
		/**
		 * Fontello.
		 */
		wp_enqueue_style(
			'cxecrt-icon-font',
			WC_EMAIL_CART_URI . '/assets/fontello/css/cxecrt-icon-font.css',
			array(),
			WC_EMAIL_CART_VERSION
		);
		
		wp_enqueue_script(
			'cxecrt-frontend-js',
			WC_EMAIL_CART_URI . '/assets/js/email-cart-frontend.js',
			array('jquery'),
			WC_EMAIL_CART_VERSION,
			TRUE
		);
		
		wp_localize_script(
			'cxecrt-frontend-js',
			'cxecrt_params',
			array(
				'plugin_url'       => WC()->plugin_url(),
				'ajax_url'         => admin_url('admin-ajax.php'),
				'order_item_nonce' => wp_create_nonce("order-item"),
				'home_url'         => get_home_url()."/",
				'cart_url'         => cxecrt_get_woocommerce_cart_url(),
			)
		);

		wp_enqueue_style(
			'cxecrt-css',
			WC_EMAIL_CART_URI . '/assets/css/ec-style.css',
			'',
			WC_EMAIL_CART_VERSION,
			'screen'
		);
		
	}
	
	public function cart_page_call_to_action() {
		?>
		<div class="cxecrt-button-holder">
			<a class="cxecrt-cart-page-button button" href="<?php echo esc_attr( cxecrt_get_woocommerce_cart_url( '#cxecrt-save-cart' ) ); ?>" id="cxecrt_dropdown_btn">
				<?php _e( 'Save & Share Cart', 'email-cart' ); ?>
			</a>
		</div>
		<?php
	}

	public function cart_page_load_form() {
		?>
		
		<?php if ( FALSE ) { // Testing ?>
			<div class="cxecrt-test-buttons">
				<a href="1" class="cxecrt-test-button">1</a>
				<a href="2" class="cxecrt-test-button">2</a>
			</div>
		<?php } ?>
		
		<div id="cxecrt-save-share-cart-modal" class="cxecrt-component-slides cxecrt-component-modal-content-hard-hide">
		
			<div class="cxecrt-component-slide cxecrt-main-modal-slide-1">
			
				<div class="cxecrt-slide-content">
					
					<div class="cxecrt-top-bar">
						<?php _e( 'Save & Share Cart', 'email-cart' ) ?>
						<span class="cxecrt-cross cxecrt-top-bar-cross cxecrt-icon-cancel"></span>
					</div>
					
					<div class="cxecrt-form-description cxecrt-form-description-four">
						<?php _e( "Your Shopping Cart will be saved and you'll be given a link. You, or anyone with the link, can use it to retrieve your Cart at any time.", 'email-cart' ) ?>
					</div>
					
					<form class="cxecrt-cart-form cxecrt-save-and-get-link-form" method="post">
						
						<input type="hidden" name="is_cart_page" value="1" />
						
						<?php
						// Security
						if ( function_exists('wp_verify_nonce') )
							wp_verify_nonce( 'cxecrt-sent-nonce' );
						else
							WC()->nonce_field('cxecrt-sent-nonce');
						?>
						
						<div class="cxecrt-component-slides">
							<div class="cxecrt-component-slide cxecrt-save-get-button-slide-1">
								
								<?php if ( cxecrt_test_user_role( 'shop_manager' ) ) { ?>
                                	<div class="cxecrt-row">
										<div class="cxecrt-row-field cxecrt-row-with-help">
											<input class="cxecrt-input-text" type="text" name="saved-cart-name" id="saved-cart-name" placeholder="<?php _e( '(optional)', 'email-cart' ) ?>" value="" />
											<label><?php _e( 'Cart Name', 'email-cart' ) ?></label>
											<div class="cxecrt-input-help cxecrt-icon-info-circled" data-tip="<?php _e( "Naming your Cart is a way to permanently store the cart - because custom named carts don't get automatically deleted when they get old (if that setting is active). Naming your cart something like 'Summer Hotlist' will mean you can easily find it when browsing your stored carts, then quickly link to it from a Advert or send the link to a customer in an Email. *Only users logged in with Store Manager privileges (and above) will see this option to name their stored carts.", 'email-cart' ) ?>"></div>
										</div>
									</div>
                                    
									<div class="cxecrt-row">
										<div class="cxecrt-row-field cxecrt-row-with-help">
											<select class="cxecrt-input-text" type="text" name="landing_page" id="landing_page_save">
												<option value="cart"><?php _e( 'Cart Page', 'email-cart' ) ?></option>
												<option value="checkout"><?php _e( 'Checkout Page', 'email-cart' ) ?></option>
												<option value="home"><?php _e( 'Home Page', 'email-cart' ) ?></option>
											</select>
											<label><?php _e( 'Redirect To', 'email-cart' ) ?></label>
										</div>
									</div>
								<?php } ?>
								
								<div class="cxecrt-row">
									<a type="submit" class="button alt cxecrt-button" name="cxecrt_submit_get_link" id="cxecrt_submit_get_link">
										<?php _e( 'Save Cart & Generate Link', 'email-cart' ); ?>
										<i class="cxecrt-icon-cart-arrow-down"></i>
									</a>
								</div>
								
							</div>
							<div class="cxecrt-component-slide cxecrt-save-get-button-slide-2">
								
								<div class="cxecrt-row">
									<div class="cxecrt-row-field cxecrt-row-field-full-width cxecrt-row-with-help">
										<input class="cxecrt-input-text" type="text" name="success-get-link-url" id="success-get-link-url" placeholder="<?php _e( 'http://&nbsp; (click below to save cart & generate your link)', 'email-cart' ); ?>" value="" />
										<div class="cxecrt-input-help cxecrt-icon-info-circled" data-tip="<?php _e( 'Copy this cart link and save it, or send it to a friend. Anyone who clicks on the link can retrieve it.', 'email-cart' ) ?>"></div>
									</div>
								</div>
								
								<div class="cxecrt-row cxecrt-double-buttons">
									<a class="button alt cxecrt-button" id="cxecrt_send_email_new">
										<?php _e( 'Send Cart in an Email', 'email-cart' ); ?>
									</a>
									<a class="button alt cxecrt-button" id="cxecrt_finish_new">
										<?php _e( 'Done! close', 'email-cart' ); ?>
									</a>
								</div>
								
							</div>
							<div class="cxecrt-component-slide cxecrt-save-get-button-slide-3">
							
								<div class="cxecrt-sent-notification">
									<?php _e( 'Empty cart. Please add products before saving :)', 'email-cart' ) ?>
								</div>
								<br />
								
							</div>
							
						</div>

					</form>
				
				</div>
				
			</div>
			
			<div class="cxecrt-component-slide cxecrt-main-modal-slide-2">
				
				<div class="cxecrt-slide-content">
				
					<div class="cxecrt-top-bar">
						<span class="cxecrt-top-bar-back">
							<i class="cxecrt-icon-left-open"></i>
							<?php _e( 'Back', 'email-cart' ) ?>
						</span>
						<?php _e( 'Save & Share Cart', 'email-cart' ) ?>
						<span class="cxecrt-cross cxecrt-top-bar-cross cxecrt-icon-cancel"></span>
					</div>
					
					<div class="cxecrt-form-description cxecrt-form-description-two">
						<?php _e( 'Your Shopping Cart will be saved with Product pictures and information, and Cart Totals. Then send it to yourself, or a friend, with a link to retrieve it at any time.', 'email-cart' ) ?>
					</div>
						
					<div class="cxecrt-component-slides">
						
						<div class="cxecrt-component-slide cxecrt-email-button-slide-1">
						
							<form class="cxecrt-cart-form cxecrt-send-cart-email-form" method="post">
								
								<input type="hidden" name="is_cart_page" value="1" />
								<input type="hidden" name="cart_id" id="cart_id" value="" />
								
								<?php
								// Security
								if ( function_exists('wp_verify_nonce') )
									wp_verify_nonce( 'cxecrt-sent-nonce' );
								else
									WC()->nonce_field('cxecrt-sent-nonce');
								?>
								
								<div class="cxecrt-row">
									<div class="cxecrt-row-field cxecrt-row-to-address">
										<input class="cxecrt-input-text" type="text" name="to_email_address" id="to_email_address" placeholder="<?php _e( "To email address(es), comma separated", 'email-cart' ); ?>" value="" />
										<label><?php _e( 'To', 'email-cart' ) ?></label>
									</div>
								</div>
								
								<?php
								global $current_user;
								get_currentuserinfo();
								
								// Prep From Email
								$from_email_address = '';
								if ( cxecrt_test_user_role( 'shop_manager' ) ) {
									if ( ! $from_email_address ) $from_email_address = get_option( 'woocommerce_email_from_address' );
								}
								elseif( is_user_logged_in() ) {
									if ( ! $from_email_address ) $from_email_address = $current_user->user_email;
								}
								
								// Prep From Name
								$from_name = '';
								if ( cxecrt_test_user_role( 'shop_manager' ) ) {
									if ( ! $from_name ) $from_name = trim( get_bloginfo( 'name' ) );
								}
								elseif( is_user_logged_in() ) {
									if ( ! $from_name ) $from_name = trim( $current_user->user_firstname . ' ' . $current_user->user_lastname );
									if ( ! $from_name ) $from_name = trim( $current_user->display_name );
									if ( ! $from_name ) $from_name = trim( get_bloginfo( 'name' ) );
								}
								else {
									if ( ! $from_name ) $from_name = trim( get_bloginfo( 'name' ) );
								}
								?>
								<!--
								<div class="cxecrt-row">
									<div class="cxecrt-row-field cxecrt-row-from-address">
										<input class="cxecrt-input-text" type="text" name="from_email_address" id="from_email_address" placeholder="<?php _e( "Your email address", 'email-cart' ); ?>" value="<?php echo esc_attr( $from_email_address ); ?>" />
										<label><?php _e( 'From', 'email-cart' ) ?></label>
									</div>
								</div>
								-->
								
								<div class="cxecrt-row">
									<div class="cxecrt-row-field cxecrt-row-from-name">
										<input class="cxecrt-input-text" type="text" name="from_name" id="from_name" placeholder="<?php _e( "Your Name", 'email-cart' ); ?>" value="<?php echo esc_attr( $from_name ); ?>" />
										<label><?php _e( 'From Name', 'email-cart' ) ?></label>
									</div>
								</div>
								
								<?php
								$subject = '';
								if ( cxecrt_test_user_role( 'shop_manager' ) ) {
									if ( ! $subject ) $subject = cxecrt_get_option( 'cxecrt_admin_email_subject' );
									if ( ! $subject ) $subject = '';
								}
								else{
									if ( ! $subject ) $subject = cxecrt_get_option( 'cxecrt_customer_email_subject' );
									if ( ! $subject ) $subject = '';
								}
								?>
								<div class="cxecrt-row">
									<div class="cxecrt-row-field">
										<input class="cxecrt-input-text" type="text" name="email_subject" id="email_subject" placeholder="<?php _e( "Your subject", 'email-cart' ); ?>" value="<?php echo esc_attr( $subject ); ?>" />
										<label><?php _e( 'Subject', 'email-cart' ) ?></label>
									</div>
								</div>
								
								<?php
								$email_content = '';
								if ( cxecrt_test_user_role( 'shop_manager' ) ) {
									if ( ! $email_content ) $email_content = cxecrt_get_option( 'cxecrt_admin_email_message' );
									if ( ! $email_content ) $email_content = '';
								}
								else{
									if ( ! $email_content ) $email_content = cxecrt_get_option( 'cxecrt_customer_email_message' );
									if ( ! $email_content ) $email_content = '';
								}
								?>
								<div class="cxecrt-row">
									<div class="cxecrt-row-field">
										<textarea name="email_content" id="email_content" rows="18" cols="20"><?php echo $email_content; ?></textarea>
									</div>
								</div>
								
								<?php if ( cxecrt_test_user_role( 'shop_manager' ) ) { ?>
									<div class="cxecrt-row">
										<div class="cxecrt-row-field cxecrt-row-with-help">
											<select class="cxecrt-input-text" type="text" name="landing_page" id="landing_page_email">
												<option value="cart"><?php _e( 'Cart Page', 'email-cart' ) ?></option>
												<option value="checkout"><?php _e( 'Checkout Page', 'email-cart' ) ?></option>
												<option value="home"><?php _e( 'Home Page', 'email-cart' ) ?></option>
											</select>
											<label><?php _e( 'Redirect To', 'email-cart' ) ?></label>
										</div>
									</div>
								<?php } ?>

								<div class="cxecrt-row">
									<a class="button alt cxecrt-button" id="cxecrt_save_and_send">
										<?php _e( 'Send Cart Email', 'email-cart' ); ?>
										<i class="cxecrt-icon-export"></i>
									</a>
								</div>
								
							</form>
								
						</div>
						
						<div class="cxecrt-component-slide cxecrt-email-button-slide-2">
						
							<div class="cxecrt-sent-notification">
								<?php _e( 'Your cart email sent successfully :)', 'email-cart' ) ?>
							</div>
							<br />
							
						</div>
						
					</div>
				
				</div>
				
			</div>
		
		</div>
		<?php
	}
	
	/**
	 * Add products to the cart.
	 *
	 * When GET parameter email_cart_products is in url add products to the cart.
	 */
	public function add_product_to_cart() {
		if ( ! is_admin() ) {
			if ( ! empty( $_GET['email_cart_products'] ) ) {
				global $woocommerce;

				$landing_page = $_GET['landing_page'];

				$woocommerce->cart->empty_cart();

				$product_ids = $_GET['email_cart_products'];
				if (strpos($product_ids, ',') !== FALSE) {
					$product_ids = explode(',', $product_ids);
				} else {
					$product_ids = array( $product_ids );
				}

				foreach ($product_ids as $product_id) {
					$variations = array();

					if (strpos($product_id, '_') !== FALSE) {
						// Split product parent id from variation data
						$product_variation_ids = preg_split("/_/",$product_id,2);
						$product_id = $product_variation_ids[0];

						// Check if quantity is bigger than 1
						$product_qty = 1;
						if (strpos($product_id, '.') !== FALSE) {
							$product_info = explode(".", $product_id);
							$product_qty = $product_info[0];
							$product_id = $product_info[1];
						}

						$product_variation_info = explode("(", $product_variation_ids[1]);

						$variation_id = $product_variation_info[0];

						// Get variation attributes
						$variation_attributes = substr($product_variation_info[1], 0, -1);
						$variation_attributes = explode(" ", $variation_attributes);
						$request_variation_attributes = array();
						foreach ($variation_attributes as $attr) {
							$attr_key_val = explode("=", $attr);
							$request_variation_attributes[$attr_key_val[0]] = $attr_key_val[1];
						}
						$adding_to_cart = get_product( $product_id );
						$attributes = $adding_to_cart->get_attributes();
						$variation = get_product( $variation_id );
						$all_variations_set = true;

						// Verify all attributes
						foreach ( $attributes as $attribute ) {

							if ( ! $attribute['is_variation'] )
								continue;

							$taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

							if ( ! empty( $request_variation_attributes[ $taxonomy ] ) ) {
								// Don't use woocommerce_clean as it destroys sanitized characters
								$value = sanitize_title( trim( stripslashes( $request_variation_attributes[ $taxonomy ] ) ) );
								// Get valid value from variation
								$valid_value = $variation->variation_data[ $taxonomy ];
								// Allow if valid
								if ( $valid_value == '' || $valid_value == $value ) {
									if ( $attribute['is_taxonomy'] ) {
										$attribute_term = get_term_by( 'slug', $value, $attribute['name'] );
										if ( $attribute_term ) {
											$variations[ esc_html( $attribute['name'] ) ] = $attribute_term->name;
										} else {
											$variations[ esc_html( $attribute['name'] ) ] = $value;
										}
									} else {
										// For custom attributes, get the name from the slug
										$options = array_map( 'trim', explode( '|', $attribute['value'] ) );
										foreach ( $options as $option ) {
											if ( sanitize_title( $option ) == $value ) {
												$value = $option;
												break;
											}
										}
										$attribute_term = get_term_by( 'slug', $value, $attribute['name'] );
										if ( $attribute_term ) {
											$variations[ esc_html( $attribute['name'] ) ] = $attribute_term->name;
										} else {
											$variations[ esc_html( $attribute['name'] ) ] = $value;
										}
									}
									continue;
								}

							}
							$all_variations_set = false;
						}

						if ( $all_variations_set ) {
							$passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $product_qty, $variation_id, $variations );

							if ( $passed_validation ) {
								$woocommerce->cart->add_to_cart( $product_id, $product_qty, $variation_id, $variations );
							}
						}
					} else {
						// Check if quantity is bigger than 1
						$product_qty = 1;
						if (strpos($product_id, '.') !== FALSE) {
							$product_info = explode(".", $product_id);
							$product_qty = $product_info[0];
							$product_id = $product_info[1];
						}

						$woocommerce->cart->add_to_cart( $product_id, $product_qty );
					}
				}

				if (isset($landing_page)) {
					if ($landing_page == "checkout") {
						header('Location: '.$woocommerce->cart->get_checkout_url());
					} else {
						header('Location: '.$woocommerce->cart->get_cart_url());
					}
				} else {
					header('Location: '.$woocommerce->cart->get_cart_url());
				}
				exit;
			}
		}
	}
	
	/**
	 * Is WooCommerce active.
	 */
	public static function is_woocommerce_active() {
		
		$active_plugins = (array) get_option( 'active_plugins', array() );
		
		if ( is_multisite() )
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		
		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}

	/**
	 * Display Notifications on specific criteria.
	 *
	 * @since	2.14
	 */
	public static function woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) :
			if ( !class_exists( 'WooCommerce' ) ) :
				?>
				<div id="message" class="error">
					<p>
						<?php
						printf(
							__( '%sSave & Share Cart for WooCommerce needs WooCommerce%s %sWooCommerce%s must be active for Save & Share Cart to work. Please install & activate WooCommerce.', 'email-cart' ),
							'<strong>',
							'</strong><br>',
							'<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank" >',
							'</a>'
						);
						?>
					</p>
				</div>
				<?php
			elseif ( version_compare( get_option( 'woocommerce_db_version' ), WC_EMAIL_CART_REQUIRED_WOOCOMMERCE_VERSION, '<' ) ) :
				?>
				<div id="message" class="error">
					<!--<p style="float: right; color: #9A9A9A; font-size: 13px; font-style: italic;">For more information <a href="http://cxthemes.com/plugins/update-notice.html" target="_blank" style="color: inheret;">click here</a></p>-->
					<p>
						<?php
						printf(
							__( '%sSave & Share Cart for WooCommerce is inactive%s This version of Save & Share Cart requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', 'email-cart' ),
							'<strong>',
							'</strong><br>',
							WC_EMAIL_CART_REQUIRED_WOOCOMMERCE_VERSION,
							'<a href="https://helpcx.zendesk.com/hc/en-us/articles/202241041/" target="_blank" style="color: inheret;" >',
							'</a>'
						);
						?>
					</p>
					<div style="clear:both;"></div>
				</div>
				<?php
			endif;
		endif;
	}

}

/**
 * Register our shiny new post type for "Carts"
 */
add_action( 'init', 'cxecrt_add_cart_post_type' );

function cxecrt_add_cart_post_type() {
	
	register_post_type(
		'stored-carts',
		array(
			'label' => 'Emailed Carts',
			'description' => '',
			'show_ui' => TRUE,
			'show_in_menu' => 'woocommerce',
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => ''),
			'query_var' => true,
			// 'supports' => array( 'title', 'author' ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_nav_menus' => 'true',
			'labels' => array (
				'name' => 'Save & Share Cart',
				'singular_name' => 'Cart',
				'menu_name' => 'Save & Share Cart',
				'add_new' => 'Add Cart',
				'add_new_item' => '',
				'edit' => 'Edit',
				'edit_item' => 'Saved Cart',
				'new_item' => 'New Cart',
				'view' => 'View Cart',
				'view_item' => 'View Cart',
				'search_items' => 'Search Saved Carts',
				'not_found' => 'No Saved Carts Found',
				'not_found_in_trash' => 'No Saved Carts Found in Trash',
				'parent' => 'Parent Cart',
			),
		)
	);
}

/**
 * Activate the function with some default options
 */
register_activation_hook( __FILE__, 'cxecrt_activate' );

function cxecrt_activate() {
	global $wpdb;
	
	// Check first to see if we need to upgrade

	$check_sql = "SELECT meta_value FROM ".$wpdb->prefix. "postmeta WHERE meta_key = '_cxecrt_cart_items'";
	$upgrade_needed = false;
	$check_meta_vals = $wpdb->get_results( $check_sql);
	foreach( $check_meta_vals as $check_meta_val):
		if ( strpos( $check_meta_val->meta_value, 'WC_Product') ):
			$upgrade_needed = true;
		endif;
	endforeach;

	if ( $upgrade_needed) {
		//Upgrade needed.
		$check_sql = "SELECT * from ".$wpdb->prefix. "postmeta WHERE meta_key = '_cxecrt_cart_items'";

		$meta_vals = $wpdb->get_results( $check_sql);
		$counter = 0;
		foreach( $meta_vals as $meta_key):
			$new_meta_value = str_replace('O:10:"WC_Product"','O:8:"stdclass"', $meta_key->meta_value);
			$upgrade_sql = "UPDATE ".$wpdb->prefix. "postmeta SET meta_value = '" . $new_meta_value ."'WHERE meta_id = '" . $meta_key->meta_id . "' AND meta_key = '" . $meta_key->meta_key . "'";
			$wpdb->query( $upgrade_sql);
			$counter ++;
		endforeach;
	}
}

/**
 * Deactivate the plugin - cleanup the options
 */
register_deactivation_hook( __FILE__, 'cxecrt_deactivate' );

function cxecrt_deactivate() {}

/**
 * Delete Cart Data
 */
function cxecrt_delete( $older_than_in_days = false) {
	global $wpdb;
	
	// Select all stored carts that have NO TITLE.
	$sql = "SELECT * FROM ".$wpdb->prefix. "posts WHERE post_type = 'stored-carts' AND post_title = ''";

	if ( $older_than_in_days ) {
		$sql .= " AND post_date < DATE_SUB(CURDATE(),INTERVAL $older_than_in_days DAY)";
	}

	$results = $wpdb->get_results( $sql);

	foreach( $results as $result ) {
		$delete_meta_sql = "DELETE FROM ".$wpdb->prefix. "postmeta WHERE post_id = '" . $result->ID . "'";
		$wpdb->query( $delete_meta_sql);
		$delete_sql = "DELETE FROM ".$wpdb->prefix. "posts WHERE ID = '" . $result->ID . "'";
		$wpdb->query( $delete_sql);
	}
}
