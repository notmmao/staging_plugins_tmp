<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 7/23/2015
 * Time: 8:15 PM
 */

$no_price_action = isset($options['no_price_action']) ? $options['no_price_action'] : 'revert';
$no_price_action_desc = __('Action to take when there is no base price entered for the current customer. "Revert" will use the WooCommerce Regular Price. "Hide" will hide the products. "Message" will show a message in place of the price.', 'woocom-urp');
$use_sale_prices_desc = __("Check this to allow the use of Sale prices when the user or role price is set to Default or Regular Price.", "woocom-urp");
if(class_exists('WOO_WholesaleOrdering')) {
	$no_price_action_desc = __('Action to take when there is no base price entered for the current customer. "Revert" will use the WooCommerce Regular Price for retail customers, or the Wholesale Price for customers treated as Wholesale. "Hide" will hide the products. "Message" will show a message in place of the price.', 'woocom-urp');
	$use_sale_prices_desc = __("Check this to allow the use of Sale prices when the user or role price is set to Default or Regular Price, and the user or role is not treated as wholesale.", "woocom-urp");
}

$uninstall_delete_data = isset($options['uninstall_delete_data']) ? $options['uninstall_delete_data'] : 'no';
$use_sale_prices = isset($options['use_sale_prices']) ? $options['use_sale_prices'] : 'no';
$update_mini_cart = isset($options['update_mini_cart']) ? $options['update_mini_cart'] : 'no';

?>
<h3><?php _e('General Settings', 'woocom-urp'); ?></h3>

<table class="form-table">
	<tr>
		<th><label for="no_price_action"><?php _e('Action when no price:', 'woocom-urp'); ?></label></th>
		<td>
			<select id="no_price_action" name="no_price_action">
				<option value="revert" <?php selected($no_price_action, 'revert'); ?> ><?php _e('Revert', 'woocom-urp'); ?></option>
				<option value="hide" <?php selected($no_price_action, 'hide'); ?> ><?php _e('Hide', 'woocom-urp'); ?></option>
				<option value="message" <?php selected($no_price_action, 'message'); ?> ><?php _e('Message', 'woocom-urp'); ?></option>
			</select>
			<span class="description"><?php echo $no_price_action_desc; ?></span>
		</td>
	</tr>
	<tr>
		<th><label for="no_price_text"><?php _e('No Price Text', 'woocom-urp'); ?></label></th>
		<td>
			<input type="text" name="no_price_text" value="<?php echo esc_attr($options['no_price_text']); ?>" />
			<span class="description"><?php _e("Text to show in place of the price when there is no product price that matches the current user's base price, and the above option is set to Message.", "woocom-urp"); ?></span>
		</td>
	</tr>
	<tr>
		<th><label for="billing_account_label"><?php _e('Billing Account Label', 'woocom-urp'); ?></label></th>
		<td>
			<input type="text" name="billing_account_label" value="<?php echo esc_attr($options['billing_account_label']); ?>" />
			<span class="description"><?php _e("The label for the billing account field when enabled for a user or role.", "woocom-urp"); ?></span>
		</td>
	</tr>
	<tr>
		<th><label for="use_sale_prices"><?php _e('Use Sale Prices', 'woocom-urp'); ?></label></th>
		<td>
			<input type="checkbox" name="use_sale_prices" value="yes" <?php checked($use_sale_prices, 'yes'); ?> />
			<span class="description"><?php _e("Check this to allow the use of Sale prices when the user or role price is set to Default or Regular Price.", "woocom-urp"); ?></span>
		</td>
	</tr>
	<tr>
		<th><label for="update_mini_cart"><?php _e('Force Update Mini Cart', 'woocom-urp'); ?></label></th>
		<td>
			<input type="checkbox" name="update_mini_cart" value="yes" <?php checked($update_mini_cart, 'yes'); ?> />
			<span class="description"><?php _e("If the mini cart total or product prices are wrong after adding a product from the shop via ajax, check this to force prices to be reset to user/role price for each item in the cart before the mini cart is refreshed", "woocom-urp"); ?></span>
		</td>
	</tr>
	<tr>
		<th><label for="uninstall_delete_data"><?php _e('Delete ALL data on uninstall', 'woocom-urp'); ?></label></th>
		<td>
			<input type="checkbox" name="uninstall_delete_data" value="yes" <?php checked($uninstall_delete_data, 'yes'); ?> />
			<span class="description"><?php _e("Check this to DELETE ALL DATA when you uninstall the plugin. This will delete all plugin options AND ALL CUSTOM PRICES for all products.", "woocom-urp"); ?></span>
		</td>
	</tr>
</table>

<p class="submit">
	<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'woocom-urp' ); ?>" />
	<?php wp_nonce_field( 'woocom-urp-settings' ); ?>
</p>

