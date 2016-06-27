<?php
/**
 * My Orders
 *
 * Shows recent orders on the account page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$plugin_id = 'wpfortune-upload-my-file';

if ( version_compare( WOOCOMMERCE_VERSION, "2.2" ) < 0 ) {

    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => get_current_user_id(),
        'post_type'   => 'shop_order',
        'post_status' => 'publish'
    ) );

} else {

    $customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
    	'numberposts' => -1,
    	'meta_key'    => '_customer_user',
    	'meta_value'  => get_current_user_id(),
    	'post_type'   => wc_get_order_types( 'view-orders' ),
    	'post_status' => array_keys( wc_get_order_statuses() )
    ) ) );

}

if ( $customer_orders ) : ?>

	<h2><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', __( 'Recent Orders', 'woocommerce' ) ); ?></h2>

	<table class="shop_table my_account_orders">

		<thead>
			<tr>
				<th class="order-number"><span class="nobr"><?php _e( 'Order', 'woocommerce' ); ?></span></th>
				<th class="order-date"><span class="nobr"><?php _e( 'Date', 'woocommerce' ); ?></span></th>
				<th class="order-status"><span class="nobr"><?php _e( 'Status', 'woocommerce' ); ?></span></th>
				<th class="order-total"><span class="nobr"><?php _e( 'Total', 'woocommerce' ); ?></span></th>
				<th class="order-files"><span class="nobr"><?php _e( 'Files', $plugin_id ); ?></span></th>
				<th class="order-actions">&nbsp;</th>
			</tr>
		</thead>

		<tbody><?php
			foreach ( $customer_orders as $customer_order ) {

                $has_uploads = false;

				if ( version_compare( WOOCOMMERCE_VERSION, "2.2" ) < 0 ) {
				    $order = new WC_Order();
                } else {
                    $order = wc_get_order();
                }

				$order->populate( $customer_order );

				$item_count = $order->get_item_count();

                $uploads = get_post_meta($order->id, '_wpf_umf_uploads');

                if (isset($uploads[0]))
                    $uploads = $uploads[0];

                if (count($uploads))
                    $uploads_approved = true;
                else
                    $uploads_approved = false;

                // Check if there are products that need uploading
                foreach ($order->get_items() AS $key => $product) {

                    $product_id = (!empty($product['variation_id']))?$product['variation_id']:$product['product_id'];

                    $unique_product_key = WPF_Uploads::get_unique_product_key($product_id, $key);

                    if ($has_uploads == false && get_post_meta($product['product_id'], '_wpf_umf_upload_enable', true) == 1) {
                        $has_uploads = true;
                    }

                    if (isset($uploads[$unique_product_key]) && is_array($uploads[$unique_product_key])) {

                        foreach ($uploads[$unique_product_key] AS $item_number => $upload_types) {

                            foreach ($upload_types AS $upload_type => $file_numbers) {

                                foreach ($file_numbers AS $file_number => $data) {

                                    if ($data['status'] != 'approved') {
                                      $uploads_approved = false;
                                    }
                                }

                            }

                        }

                    }

                }

				?><tr class="order">
					<td class="order-number">
						<a href="<?php echo $order->get_view_order_url(); ?>">
							<?php echo $order->get_order_number(); ?>
						</a>
					</td>
					<td class="order-date">
						<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->order_date ) ); ?>" title="<?php echo esc_attr( strtotime( $order->order_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></time>
					</td>
					<td class="order-status" style="text-align:left; white-space:nowrap;">
						<span class="dashicons dashicons-<?php echo ($order->status == 'completed')?'yes':'no-alt'; ?>" style="margin-right: 5px;"></span><?php echo ucfirst( __( $order->status, 'woocommerce' ) ); ?>
					</td>

                    <?php if(in_array($order->status, array('completed'))):  ?>

                        <td class="order-total paid">
  						    <?php echo $order->get_formatted_order_total(); ?>
  					    </td>

                    <?php else: ?>

    					<td class="order-total unpaid">
    						<?php echo $order->get_formatted_order_total(); ?>
    					</td>

                    <?php endif; ?>

                    <td class="order-files">
                        <?php if ($has_uploads): ?>

                            <?php if ($uploads_approved): ?>
                                <span class="dashicons dashicons-yes"></span> <?php _e('Approved', $plugin_id); ?>
                            <?php else: ?>
                                <a href="<?php echo $order->get_view_order_url(); ?>" class="button"><?php _e( 'Upload', $plugin_id ); ?></a>
                            <?php endif; ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
					<td class="order-actions">
						<?php
							$actions = array();

							if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'failed' ), $order ) ) ) {
								$actions['pay'] = array(
									'url'  => $order->get_checkout_payment_url(),
									'name' => __( 'Pay', 'woocommerce' )
								);
							}

							if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) ) {
								$actions['cancel'] = array(
									'url'  => $order->get_cancel_order_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ),
									'name' => __( 'Cancel', 'woocommerce' )
								);
							}

							$actions['view'] = array(
								'url'  => $order->get_view_order_url(),
								'name' => __( 'View', 'woocommerce' )
							);

							$actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

							if ($actions) {
								foreach ( $actions as $key => $action ) {
									echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
								}
							}
						?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table>

<?php endif; ?>