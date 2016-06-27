<?php
/**
 * Clear Carts Settings
 */

add_filter( 'plugin_action_links_' . WC_EMAIL_CART_PLUGIN_BASENAME, 'cxecrt_settings_link' );

function cxecrt_settings_link( $links ) {
	
	$timestamp = time();
	
	ob_start();
	?>
	<a
		class="clear-link"
		onclick="return confirm('Are you sure you want to delete ALL Cart Reports Data? This includes all carts in the database. Settings will not be affected.')"
		href="?cart-action=clear&timestamp=<?php echo $timestamp ?>&_wpnonce=<?php echo wp_create_nonce('trash-the-carts') ?>"
		>
		<?php _e( 'Clear Carts', 'email-cart' ) ?>
	</a>
	<?php
	$settings_link = ob_get_clean();
	
	$links[] = $settings_link;
	return $links;
}

add_action( 'admin_init', 'cxecrt_clear_all_carts' );

function cxecrt_clear_all_carts() {
	
	if (
			isset( $_GET['cart-action'] ) && 'clear' == $_GET['cart-action'] &&
			isset( $_GET['timestamp'] ) && $_GET['timestamp'] &&
			current_user_can('delete_plugins') &&
			wp_verify_nonce( $_REQUEST['_wpnonce'], 'trash-the-carts' )
		):
		
		$current_time_stamp = time();
		$clicked_time_stamp = $_GET['timestamp'];
		
		if ( $clicked_time_stamp > $current_time_stamp - (60* 60 * 24) ) {
			cxecrt_delete();
			add_action('admin_notices', 'cxecrt_clear_carts_admin_notice');
		}
		else {
			add_action('admin_notices', 'cxecrt_clear_carts_timeout_notice');
		}
		
	endif;
}

function cxecrt_clear_carts_admin_notice() {
	?>
	<div class="updated">
	   <p><?php _e( 'Cart Data Cleared', 'email-cart' ) ?></p>
	</div>
	<?php
}

function cxecrt_clear_carts_timeout_notice() {
	?>
	<div class="updated">
	   <p><?php _e( 'Timeout occured, please try again.', 'email-cart' ) ?></p>
	</div>
	<?php
}
