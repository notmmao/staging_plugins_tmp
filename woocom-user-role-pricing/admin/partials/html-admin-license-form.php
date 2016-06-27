<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 9/5/2015
 * Time: 11:50 AM
 */

$license 	= get_option( 'woocom_urp_license_key' );
$status 	= get_option( 'woocom_urp_license_status' );
?>
<h3><?php _e('Plugin License', 'woocom-urp'); ?></h3>
<form method="post" action="">

	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" valign="top">
				<?php _e('License Key', 'woocom-urp'); ?>
			</th>
			<td>
				<input id="woocom_urp_license_key" name="woocom_urp_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
				<label class="description" for="woocom_urp_license_key"><?php _e('Enter your license key', 'woocom-urp'); ?></label>
			</td>
		</tr>
		<?php if( false !== $license ) { ?>
			<tr valign="top">
				<th scope="row" valign="top">
					<?php _e('Activate License', 'woocom-urp'); ?>
				</th>
				<td>
					<?php if( $status !== false && $status == 'valid' ) { ?>
						<span style="color:green;"><?php _e('Active', 'woocom-urp'); ?></span>
						<?php wp_nonce_field( 'woocom-urp-settings' ); ?>
						<input type="hidden" name="woocom_urp_deactivate_mode" value="deactivated" />
						<input type="submit" class="button-secondary" name="woocom_urp_license_deactivate" value="<?php _e('Deactivate License', 'woocom-urp'); ?>"/>
					<?php } else {
						wp_nonce_field( 'woocom-urp-settings' ); ?>
						<input type="hidden" name="woocom_urp_activate_mode" value="activated" />
						<input type="submit" class="button-secondary" name="woocom_urp_license_activate" value="<?php _e('Activate License', 'woocom-urp'); ?>"/>
					<?php } ?>
				</td>
			</tr>
		<?php }  ?>

		</tbody>
	</table>
	<?php if( false === $license) {
		wp_nonce_field( 'woocom-urp-settings' ); ?>
		<p class="submit">
			<input type="hidden" name="woocom_urp_license_save_mode" value="submitted" />
			<input type="submit" class="button-secondary" name="woocom_urp_license_save_activate" value="<?php _e('Save License Key', 'woocom-urp'); ?>"/>
		</p>
	<?php } ?>

</form>