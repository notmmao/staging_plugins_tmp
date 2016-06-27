<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="postbox" id="woocommerce-customer-orders">
	<div title="Click to toggle" class="handlediv"><br></div>
	<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Customer Orders', 'wc_crm' ) ?></span></h3>
	<div class="inside" style="margin:0px; padding:0px;">
		<?php 
		$orders = $the_customer->get_orders();
		$order_list = new WC_CRM_Table_Customer_Orders($orders);
		$order_list->prepare_items();
		$order_list->display();
		?>
	</div>
</div>