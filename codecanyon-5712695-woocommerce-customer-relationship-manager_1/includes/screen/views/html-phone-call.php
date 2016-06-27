<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wc-crm-page" class="wrap">
	<div class="panel-wrap woocommerce" id="place_new_call">
		
        <div id="log_data" class="panel">
        <form id="wc_crm_customers_form" method="post" action="<?php echo admin_url('admin.php?page='.WC_CRM_TOKEN); ?>">
			<h2>
				<span  class="h2_new_call"><?php _e('Place New Call', 'wc_crm'); ?></span>
				<div class="clear"></div>
			</h2>
			<div class="error below-h2"></div>
			<p><?php _e('Place a new call with', 'wc_crm'); ?> <?php echo $user_name ?> <?php _e('on', 'wc_crm'); ?> <?php echo $phone; ?> <?php _e('using the fields below.', 'wc_crm'); ?> </p>
			<table class="wp-list-table form-table fixed phone_call">
				
				<?php
					if ( !empty( $phone ) ) {
				?>
				
				<tr class="form-field form-required">
					<th width="200">
						<label for="subject_of_call"><?php _e('Phone Number', 'wc_crm'); ?>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
						<input type="text" disabled="disabled" value="<?php echo $phone; ?>">
						<input type="hidden" name="user_phone" id="user_phone" value="<?php echo $phone; ?>">
						<a href="tel:<?php echo $phone; ?>" id="call_number" class="button tips" data-tip="<?php _e('Place Call', 'wc_crm'); ?>"></a>
						<p class="description"><?php _e('Enter phone number of this phone call.', 'wc_crm'); ?></p>
						<span class="error_message"><?php _e('Phone number of the call is missing.', 'wc_crm'); ?></span>
					</td>
				</tr>
				<?php
				} else{
				?>
				
				<tr class="form-field form-required">
					<th width="200">
						<label for="subject_of_call"><?php _e('Phone Number', 'wc_crm'); ?>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
						<input type="text" name="user_phone" id="user_phone">
						<p class="description"><?php _e('Enter phone number of this phone call.', 'wc_crm'); ?></p>
						<span class="error_message"><?php _e('Phone number of the call is missing.', 'wc_crm'); ?></span>
					</td>
				</tr>
				<?php
				}
				?>
				
				<tr class="form-field form-required">
					<th width="200">
						<label for="subject_of_call"><?php _e('Subject', 'wc_crm'); ?>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
						<input type="text" name="subject_of_call" id="subject_of_call">
						<span class="error_message"><?php _e('Subject of the phone call is missing.', 'wc_crm'); ?></span>
						<p class="description"><?php _e('Enter the subject/topic of this phone call.', 'wc_crm'); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="call_type"><?php _e('Type', 'wc_crm'); ?></label>
					</th>
					<td>
						<select name="call_type" id="call_type">
							<option value="<?php _e('Inbound', 'wc_crm'); ?>"><?php _e('Inbound', 'wc_crm'); ?></option>
							<option value="<?php _e('Outbound', 'wc_crm'); ?>"><?php _e('Outbound', 'wc_crm'); ?></option>
						</select>
						<p class="description"><?php _e('Select the type of the phone call.', 'wc_crm'); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="call_purpose"><?php _e('Purpose', 'wc_crm'); ?></label>
					</th>
					<td>
						<select name="call_purpose" id="call_purpose">
							<option value="<?php _e('None', 'wc_crm'); ?>"><?php _e('None', 'wc_crm'); ?></option>
							<option value="<?php _e('Prospecting', 'wc_crm'); ?>"><?php _e('Prospecting', 'wc_crm'); ?></option>
							<option value="<?php _e('Administrative', 'wc_crm'); ?>"><?php _e('Administrative', 'wc_crm'); ?></option>
							<option value="<?php _e('Negotiation', 'wc_crm'); ?>"><?php _e('Negotiation', 'wc_crm'); ?></option>
							<option value="<?php _e('Demo', 'wc_crm'); ?>"><?php _e('Demo', 'wc_crm'); ?></option>
							<option value="<?php _e('Project', 'wc_crm'); ?>"><?php _e('Project', 'wc_crm'); ?></option>
							<option value="<?php _e('Support', 'wc_crm'); ?>"><?php _e('Support', 'wc_crm'); ?></option>
						</select>
						<p class="description"><?php _e('Select the purpose of the phone call.', 'wc_crm'); ?></p>
					</td>
				</tr>
				<?php
				if( !empty($user_name) ){
				?>
				<tr>
					<th>
						<label for="contact_name"><?php _e('Customer Name', 'wc_crm'); ?></label>
					</th>
					<td>
						<?php
								if( $customer_id > 0 ){
									echo "<input type='text' value='{$user_name}' disabled='disabled'><a class='button tips' href='admin.php?page=".WC_CRM_TOKEN."&c_id={$customer_id}' target='_blank' id='view_customer_info' data-tip='View Customer Profile'>";
								}else{
									echo "<input type='text' value='".$user_name."' disabled='disabled'>";
								}
						?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th>
						<label for="related_to"><?php _e('Related To', 'wc_crm'); ?>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
						<select name="related_to" id="related_to">
							<option value="<?php _e('order', 'wc_crm'); ?>"><?php _e('Order', 'wc_crm'); ?></option>
							<option value="<?php _e('product', 'wc_crm'); ?>"><?php _e('Product', 'wc_crm'); ?></option>
						</select>
						<input type="text" name="number_order_product" id="number_order_product">
						<a href="?page=<?php echo WC_CRM_TOKEN; ?>&screen=order_list&c_id=<?php echo $customer_id; ?>" class="button glass tips" id="view_info" data-tip="<?php _e('Find', 'wc_crm'); ?>"></a>
						<span id="message_view_info"></span>
						<p class="description"><?php _e('Enter the order/product or use the "Find" button to search for it.', 'wc_crm'); ?></p>
						<span class="error_message"><?php _e('Subject of the phone call is missing', 'wc_crm'); ?></span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="call_details"><?php _e('Call Details', 'wc_crm'); ?></label>
					</th>
					<td>
						<label for="current_call" class="call_details">
							<input type="radio" name="call_details" id="current_call" checked="checked" value="current_call">
							<?php _e('Current Call', 'wc_crm'); ?>
						</label>
						<label for="completed_call" class="call_details">
							<input type="radio" name="call_details" id="completed_call"  value="completed_call">
							<?php _e('Completed Call', 'wc_crm'); ?>
						</label>
					</td>
				</tr>
				<tr id="current_call_wrap">
					<th><?php _e('Call Timer', 'wc_crm'); ?></th>
					<td>
						<span class="display_time">00:00:00:00</span>

						<a href="#" class="button tips" id="start_timer" data-tip="<?php _e('Start', 'wc_crm'); ?>"></a>
						<a href="#" class="button tips" id="stop_timer" data-tip="<?php _e('Stop', 'wc_crm'); ?>"><i class="ico_stop"></i></a>
						<a href="#" class="button tips" id="pause_timer" data-tip="<?php _e('Pause/Resume', 'wc_crm'); ?>"><i class="ico_pause"></i></a>
						<a href="#" class="button tips" id="reset_timer" data-tip="<?php _e('Reset', 'wc_crm'); ?>"><i class="ico_reset"></i></a>
					</td>
				</tr>
				<tr class="completed_call_wrap disabled">
					<th>
						<label for="call_date"><strong><?php _e('Call Date', 'wc_crm'); ?></strong></label>
					</th>
					<td>
							<div class="wrap_disabled">
								<input type="text" name="call_date" id="call_date" value="<?php echo current_time('Y-m-d'); ?>">
								<span class="error_message"><?php _e('Phone call date entered is incorrect or invalid.', 'wc_crm'); ?></span>
								<div class="content_disabled"></div>
							</div>
					</td>
				</tr>
				<tr class="completed_call_wrap disabled">
					<th>
						<label for="call_time_h"><strong><?php _e('Call Time', 'wc_crm'); ?></strong>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
							<div class="wrap_disabled">
								<input type="number" name="call_time_h" id="call_time_h" class="call_time"> :
								<input type="number" name="call_time_m" id="call_time_m" class="call_time"> :
								<input type="number" name="call_time_s" id="call_time_s" class="call_time">
								<span class="error_message"><?php _e('Call time is incorrect/invalid.', 'wc_crm'); ?></span>
								<div class="content_disabled"></div>
							</div>
					</td>
				</tr>
				<tr class="completed_call_wrap disabled">
					<th>
						<label for="call_duration_h"><strong><?php _e('Call Duration', 'wc_crm'); ?></strong>
						<span class="description"><?php _e('(required)', 'wc_crm'); ?></span></label>
					</th>
					<td>
							<div class="wrap_disabled">
								<input type="number" name="call_duration_h" id="call_duration_h" class="call_time"> <?php _e('h', 'wc_crm'); ?>
								<input type="number" name="call_duration_m" id="call_duration_m" class="call_time"> <?php _e('m', 'wc_crm'); ?>
								<input type="number" name="call_duration_s" id="call_duration_s" class="call_time"> <?php _e('s', 'wc_crm'); ?>
								<span class="error_message"><?php _e('Call duration is incorrect/invalid.', 'wc_crm'); ?></span>
								<div class="content_disabled"></div>
							</div>
					</td>
				</tr>
				<tr class="completed_call_wrap">
					<th><label for="call_results"><?php _e('Call Results', 'wc_crm'); ?></label></th>
					<td>
						<textarea name="call_results" id="call_results" rows="5"></textarea>
						<span class="error_message"><strong><?php _e('ERROR', 'wc_crm'); ?></strong><?php _e(': This field Call Results is required.', 'wc_crm'); ?></span>
					</td>
				</tr>
			</table>
			<input type="submit" id="save_call" value="Save Call" class="button button-primary button-large">
			<div class="clear"></div>
			<input type="hidden" id="customer_id" name="customer_id" value="<?php echo $customer_id; ?>">
			<input type="hidden" name="wc_crm_customer_action" value="save_phone_call">
			</form>
		</div>
	</div>

	<div id="place_new_call_popup" class="overlay_media_popup">
	    <div class="media-modal wp-core-ui">
	    	<a href="#" class="button-link media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close', 'wc_crm'); ?></span></span></a>
	    	<div class="media-modal-content">
	    		<div class="media-frame mode-select wp-core-ui hide-menu">
	    			<div class="media-frame-title"><h1><?php _e('Select Related Order', 'wc_crm'); ?><span class="dashicons dashicons-arrow-down"></span></h1></div>
	    			<div class="media-frame-content">
	    				<iframe src="" frameborder="0"></iframe>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	    <div class="media-modal-backdrop"></div>
	</div>
</div>