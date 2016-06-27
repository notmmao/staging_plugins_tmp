<?php 
/**
 * Plugin Name: WooCommerce Empty Cart Button
 * Plugin URI: http://www.technorigins.com/woocommerce-empty-cart-button/
 * Description: Empty cart button for woocommerce
 * Version: 1.1
 * Author: Technorigins
 * Author URI: http://technorigins.com
 * Tested up to: 3.8
 *
 *
 */
 
 /***** Empty cart button  starts****/
add_action('woocommerce_after_cart_contents', 'woocommerce_empty_cart_button'); 

function woocommerce_empty_cart_button( $cart ) {global $woocommerce;$cart_url = $woocommerce->cart->get_cart_url();?>

<tr>
			<td colspan="6" class="actions">
			<?php 
			
			if(empty($_GET)) {?>
			<a class="button emptycart" href="<?php echo $cart_url;?>?clear-cart=empty-cart"><?php _e('Empty Cart','wc-emptycart'); ?></a>
			<?php } else {?>
			<a class="button emptycart" href="<?php echo $cart_url;?>&clear-cart=empty-cart"><?php _e('Empty Cart','wc-emptycart'); ?></a>
			<?php } ?>
			
			
</td></tr>


<?php }

add_action('init', 'woocommerce_clear_cart_url');
function woocommerce_clear_cart_url() {
	// Add text domain for plugin. 
	load_plugin_textdomain( 'wc-emptycart', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	global $woocommerce;
	if( isset($_REQUEST['clear-cart']) ) {
		$woocommerce->cart->empty_cart();
	}
	load_plugin_textdomain('wc-emptycart');
}

/***** Empty cart button ends****/
?>