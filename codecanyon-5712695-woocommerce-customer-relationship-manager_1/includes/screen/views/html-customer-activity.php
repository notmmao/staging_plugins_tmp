<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="postbox" id="woocommerce-customer-activity">
	<div title="Click to toggle" class="handlediv"><br></div>
	<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Activity', 'wc_crm' ) ?></span></h3>
	<div class="inside" style="margin:0px; padding:0px;">
		<?php 
		$activity = $the_customer->get_activity();
		$activity_list = new WC_CRM_Table_Customer_Activity($activity);
		$activity_list->prepare_items();
		$activity_list->display();
		?>
	</div>
</div>