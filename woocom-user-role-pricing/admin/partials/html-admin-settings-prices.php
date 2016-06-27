<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 7/23/2015
 * Time: 8:19 PM
 */

$prices = $options['prices'];
$prices_incl_tax = $options['prices_incl_tax'];

?>
<h3><?php _e('Base Prices and Settings', 'woocom-urp'); ?></h3>

<h4><?php _e('Current Price fields', 'woocom-urp'); ?></h4>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Price Name', 'woocom-urp'); ?></th>
			<th><?php _e('Price Meta Key', 'woocom-urp'); ?></th>
			<th><?php _e('Price Includes Tax?', 'woocom-urp'); ?></th>
			<th><?php _e('DELETE', 'woocom-urp'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($prices as $slug => $name):
			$disabled = '';
			$incl_tax = ( isset($prices_incl_tax[$slug]) && 'yes' == $prices_incl_tax[$slug] ) ? __('Yes', 'woocom-urp') :  __('No', 'woocom-urp');
			if('_regular_price' == $slug || '_wholesale_price' == $slug) {
				$disabled = "disabled";
				if('_regular_price' == $slug ) {
					$incl_tax = __('WooCommerce Settings', 'woocom-urp');
				} elseif ('_wholesale_price' == $slug ) {
					$incl_tax = __('Wholesale Settings', 'woocom-urp');
				}
			}
		?>
		<tr>
			<td><?php echo esc_html(stripslashes($name)); ?></td>
			<td><?php echo esc_html($slug); ?></td>
			<td><?php echo esc_html($incl_tax); ?></td>
			<td><input type="checkbox" name="delete_price[<?php echo esc_attr($slug); ?>]" value="delete" <?php echo $disabled; ?> /></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<th><?php _e('Price Name', 'woocom-urp'); ?></th>
			<th><?php _e('Price Meta Key', 'woocom-urp'); ?></th>
			<th><?php _e('Price Includes Tax?', 'woocom-urp'); ?></th>
			<th><?php _e('DELETE', 'woocom-urp'); ?></th>
		</tr>
	</tfoot>
</table>

<input type="hidden" name="subtab" id="last_tab" />
<?php wp_nonce_field( 'woocom-urp-settings' ); ?>

<p class="submit" style="text-align: right;">
	<input name="delete_price_field" class="button-primary" type="submit" value="<?php _e( 'Delete Selected Prices', 'woocom-urp' ); ?>" />
</p>

<h3><?php _e('Add a new Price field', 'woocom-urp'); ?></h3>
<p><?php _e('This will create a new price field for all products and variations, which you can then choose to use as a base price for roles or users.', 'woocom-urp'); ?></p>

<table class="form-table">
	<tr>
		<th><label for="new_price_name"><?php _e('New Price Name', 'woocom-urp'); ?></label></th>
		<td><input type="text" name="new_price_name" value="" /></td>
	</tr>
	<tr>
		<th><label for="price_incl_tax"><?php _e('Prices Include Tax?', 'woocom-urp'); ?></label></th>
		<td>
			<select name="price_incl_tax">
				<option value="no"><?php _e('No', 'woocom-urp'); ?></option>
				<option value="yes"><?php _e('Yes', 'woocom-urp'); ?></option>
			</select>
		</td>
	</tr>
</table>

<p class="submit">
	<input name="add_price_field" class="button-primary" type="submit" value="<?php _e( 'Add Price Field', 'woocom-urp' ); ?>" />
</p>