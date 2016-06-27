<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/admin/partials
 */
?>

<h3><?php _e('User Pricing and Tax Options', 'woocom-urp'); ?></h3>
<h4><?php _e("Use these fields to override the user's role based settings and specify pricing and tax status for this particular user.", "woocom-urp"); ?></h4>
<table class="form-table">

	<tr>
		<th><label for="urp_user_base_price"><?php _e('User Base Price', 'woocom-urp'); ?></label></th>
		<td>
			<select name="urp_user_base_price" id="urp_user_base_price">
				<option value="role"><?php _e('Default', 'woocom-urp'); ?></option>
				<?php
				foreach($this->options['prices'] as $key => $name) {
					echo '<option value="'.esc_attr($key).'" '.selected($user_base_price, $key, true).' >'.esc_html(stripslashes($name)).'</option>';
				}
				?>
			</select>
			<span class="description"><?php _e("Leave set to Default to use the user's role default base price.", "woocom-urp"); ?></span>
		</td>
	</tr>

	<tr>
		<th><label for="user_price_multiplier"><?php _e('User Price Multiplier', 'woocom-urp'); ?></label></th>
		<td>
			<input type="text" name="urp_user_price_multiplier" id="urp_user_price_multiplier" value="<?php echo (float)esc_attr($user_price_multiplier); ?>" />
			<span class="description"><?php _e('Leave set to 0 to use the user role default price multiplier.<br/>0.75 would be 75% of the base price, while 1.25 would be 25% above the base price.', 'woocom-urp'); ?></span>
		</td>
	</tr>
	<?php foreach($fields as $field => $values): ?>
		<tr>
			<th><label for="<?php echo $field; ?>"><?php echo $values['label']; ?></label></th>
			<td>
				<select name="<?php echo $field; ?>" id="<?php echo $field; ?>">
					<?php if($is_an_admin && 'urp_user_is_wholesale' == $field) { ?>
						<option value="plugin" <?php selected($field_values[$field], "plugin"); ?> ><?php _e('Wholesale Plugin Setting', 'woocom-urp'); ?></option>
					<?php } else { ?>
						<option value="role" <?php selected($field_values[$field], "role"); ?> ><?php _e('Default', 'woocom-urp'); ?></option>
					<?php } ?>
					<option value="yes" <?php selected($field_values[$field], "yes"); ?> ><?php _e('Yes', 'woocom-urp'); ?></option>
					<option value="no" <?php selected($field_values[$field], "no"); ?> ><?php _e('No', 'woocom-urp'); ?></option>
				</select>
				<span class="description"><?php echo $values['desc']; ?></span>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
