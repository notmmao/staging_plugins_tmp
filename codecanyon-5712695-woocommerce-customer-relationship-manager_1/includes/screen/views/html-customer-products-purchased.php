<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="postbox" id="wcrm-products-purchased">
	<div title="Click to toggle" class="handlediv"><br></div>
	<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Products Purchased', 'wc_crm' ) ?></span></h3>
	<div class="inside" style="margin:0px; padding:0px;">
		<?php 
		$products = $the_customer->get_products_purchased();
		$products_list = new WC_CRM_Table_Customer_Products_Purchased($products);
		$products_list->prepare_items();
		$products_list->display();
		?>
	</div>
</div>