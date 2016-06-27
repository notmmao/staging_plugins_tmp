<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 7/23/2015
 * Time: 8:17 PM
 */

$roles = get_editable_roles();

$base_prices = $options['base_price'];
$price_multipliers = $options['price_multiplier'];
$prices = $options['prices'];
$wholesale_active = false;
$colspan = 3;
$defaults_heading = __('If no specific settings for a role (or empty multiplier), the settings will default to Regular Price (or Sale Price if enabled and entered) for the Base Price, and 1 for the Multiplier.', 'woocom-urp');

$checkboxes = array('billing_account');
$selectors = array('tax_exempt', 'disable_shipping');

if( function_exists('woocom_wholesale_is_wholesale_customer') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
	$checkboxes[] = 'is_wholesale';
	$wholesale_active = true;
	$colspan = 4;
	$defaults_heading = __('Setting Base Price to "Default" will use the Regular Price (or Sale Price if enabled and entered) for retail customers and guests, or the Wholesale Price for roles treated as wholesale.<br/>Leaving the Price Multiplier blank defaults to 1 for the multiplier value.', 'woocom-urp');
}

$sections = array('settings' => __('Role Settings', 'woocom-urp'), 'add_role' => __('Add A Role', 'woocom-urp'), 'remove_role' => __('Remove A Role', 'woocom-urp'));
if(empty($current_section)) $current_section = 'settings';

echo '<ul class="subsubsub">';

$array_keys = array_keys( $sections );

foreach ( $sections as $id => $label ) {
	echo '<li><a href="' . admin_url( 'admin.php?page=user_role_pricing&tab=role&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
}

echo '</ul><br class="clear" />';

if(empty($current_section) || 'settings' == $current_section ): ?>
	<?php
	$button_label = __( 'Save changes', 'woocom-urp' );
	// Remove default customer role and wholesale customer role -- don't alter their settings on this page
	if(isset($roles['customer'])) unset($roles['customer']);
	if(isset($roles['wholesale_customer'])) unset($roles['wholesale_customer']);
	?>
	<h3><?php _e('Set base prices and multiplier for roles', 'woocom-urp'); ?></h3>
	<h4><?php echo $defaults_heading; ?></h4>
	<table class="widefat">
		<thead>
			<tr valign="top">
				<th><?php _e('Role', 'woocom-urp'); ?></th>
				<th><?php _e('Base Price', 'woocom-urp'); ?></th>
				<th><?php _e('Multiplier', 'woocom-urp'); ?></th>
				<th><?php _e('Tax Exempt', 'woocom-urp'); ?></th>
				<th><?php _e('Disable Shipping', 'woocom-urp'); ?></th>
				<th><?php _e('Billing Account', 'woocom-urp'); ?></th>
				<?php if( $wholesale_active ): ?>
					<th><?php _e('Treat As Wholesale', 'woocom-urp'); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<tr valign="top">
				<td><?php _e('Customer / Guest', 'woocom-urp'); ?></td>
				<td><?php _e('Regular Price', 'woocom-urp'); ?></td>
				<td><?php echo '1.00'; ?></td>
				<td colspan="<?php echo (int)$colspan; ?>"><?php _e('Set by regular WooCommerce settings.', 'woocom-urp'); ?></td>
			</tr>
			<?php if( $wholesale_active ): ?>
				<tr valign="top">
					<td><?php _e('Wholesale Customer', 'woocom-urp'); ?></td>
					<td><?php _e('Wholesale Price', 'woocom-urp'); ?></td>
					<td><?php echo '1.00'; ?></td>
					<td colspan="<?php echo (int)$colspan; ?>"><?php _e('Set by WooCommerce Wholesale Ordering plugin settings.', 'woocom-urp'); ?></td>
				</tr>
			<?php endif; ?>
			<?php


			foreach($roles as $key => $data):
				$checkbox_values = array();
				$base = isset($base_prices[$key]) ? $base_prices[$key] : "";
				$multiplier = isset($price_multipliers[$key]) ? floatval($price_multipliers[$key]) : "";
				foreach($checkboxes as $field) {
					if(isset($options[$field][$key]) && 'yes' == $options[$field][$key]) {
						$checkbox_values[$field] = 'yes';
					} else {
						$checkbox_values[$field] = 'no';
					}
				}
				$selector_values = array();
				foreach($selectors as $field) {
					if(isset($options[$field][$key]) && 'yes' == $options[$field][$key]) {
						$selector_values[$field] = 'yes';
					} elseif(isset($options[$field][$key]) && 'no' == $options[$field][$key]) {
						$selector_values[$field] = 'no';
					} else {
						$selector_values[$field] = '';
					}
				}
			?>
				<tr valign="top">
					<td><?php echo esc_html($data['name']); ?></td>
					<td>
						<select name="base_price[<?php echo esc_attr($key); ?>]">
							<option value="" <?php selected("", $base); ?>><?php _e('Default', 'woocom-urp'); ?></option>
							<?php foreach($prices as $metakey => $name): ?>
							<option value="<?php echo esc_attr($metakey); ?>" <?php selected($metakey, $base); ?>><?php echo esc_html(stripslashes($name)); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="multiplier[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($multiplier); ?>" /></td>
					<?php foreach($selectors as $field): ?>
						<td>
							<select name="<?php echo esc_attr($field); ?>[<?php echo esc_attr($key); ?>]">
								<option value="" <?php selected("", $selector_values[$field]); ?>><?php _e('Default', 'woocom-urp'); ?></option>
								<option value="no" <?php selected("no", $selector_values[$field]); ?>><?php _e('No', 'woocom-urp');  ?></option>
								<option value="yes" <?php selected("yes", $selector_values[$field]); ?>><?php _e('Yes', 'woocom-urp'); ?></option>
							</select>
						</td>
					<?php endforeach; ?>
					<?php foreach($checkboxes as $field): ?>
						<?php
						$disabled = '';
						if('is_wholesale' == $field && 'administrator' == $key) {
							$disabled = 'disabled';
							$checkbox_values['is_wholesale'] = get_option('woocommerce_wholesale_admin', 'no');
						}
						?>
						<td><input type="checkbox" name="<?php echo esc_attr($field); ?>[<?php echo esc_attr($key); ?>]" value="yes" <?php checked($checkbox_values[$field], 'yes'); ?> <?php echo $disabled; ?> /></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr valign="top">
			<th><?php _e('Role', 'woocom-urp'); ?></th>
			<th><?php _e('Base Price', 'woocom-urp'); ?></th>
			<th><?php _e('Multiplier', 'woocom-urp'); ?></th>
			<th><?php _e('Tax Exempt', 'woocom-urp'); ?></th>
			<th><?php _e('Disable Shipping', 'woocom-urp'); ?></th>
			<th><?php _e('Billing Account', 'woocom-urp'); ?></th>
			<?php if( function_exists('woocom_wholesale_is_wholesale_customer') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ): ?>
				<th><?php _e('Treat As Wholesale', 'woocom-urp'); ?></th>
			<?php endif; ?>
		</tr>
		</tfoot>
	</table>

<?php elseif('add_role' == $current_section): ?>
	<?php $button_label = __( 'Add Role', 'woocom-urp' ); ?>
	<h3><?php _e('Add a new Role', 'woocom-urp'); ?></h3>
	<h4><?php _e('The new role will have the same capabilities as the role you select to copy.', 'woocom-urp'); ?></h4>
	<table class="formtable">
		<tr>
			<th><label for="copy_role"><?php _e('Role to Copy', 'woocom-urp'); ?></label></th>
			<td>
				<select name="copy_role">
					<option value=""><?php _e('Select a Role to Copy', 'woocom-urp'); ?></option>
					<?php foreach($roles as $key => $data):	?>

						<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($data['name']); ?></option>

					<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><label for="role_name"><?php _e('New Role Name', 'woocom-urp'); ?></label></th>
			<td>
				<input type="text" name="role_name" />
			</td>
		</tr>
	</table>
<?php elseif('remove_role' == $current_section): ?>
	<?php
	$button_label = __( 'REMOVE Role', 'woocom-urp' );
	// Remove default customer role and wholesale customer role -- don't allow to remove those
	if(isset($roles['customer'])) unset($roles['customer']);
	if(isset($roles['wholesale_customer'])) unset($roles['wholesale_customer']);
	?>
	<h3><?php _e('Remove a Role', 'woocom-urp'); ?></h3>
	<h4 style="color:red"><?php _e('WARNING! Once you remove a role, any users who were assigned that role will lose ALL site privileges and you will have to manually assign new roles to those users!', 'woocom-urp'); ?></h4>
	<table class="formtable">
		<tr>
			<th><label for="remove_role"><?php _e('Role to REMOVE', 'woocom-urp'); ?></label></th>
			<td>
				<select name="remove_role">
					<option value=""><?php _e('Select a Role to REMOVE', 'woocom-urp'); ?></option>
					<?php foreach($roles as $key => $data):	?>

						<?php if('administrator' === $key) continue; ?>

						<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($data['name']).' ('.esc_html($key).')'; ?></option>

					<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><label for="confirm_remove"><?php _e('Are you sure?', 'woocom-urp'); ?></label></th>
			<td>
				<input type="checkbox" name="confirm_remove" value="yes" /> YES! Remove the role!
			</td>
		</tr>
	</table>
<?php endif; ?>

<p class="submit">
	<input name="save" class="button-primary" type="submit" value="<?php echo esc_attr($button_label); ?>" />
	<?php wp_nonce_field( 'woocom-urp-settings' ); ?>
</p>
