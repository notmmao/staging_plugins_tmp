<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Conditions table.
 *
 * Display table with all the user configured shipping conditions.
 *
 * @author		Jeroen Sormani
 * @package 	WooCommerce Advanced Shipping
 * @version		1.0.0
 */

$method_conditions = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'was', 'post_status' => array( 'draft', 'publish' ), 'orderby' => 'menu_order', 'order' => 'ASC' ) );

?><tr valign="top">
	<th scope="row" class="titledesc"><?php
		_e( 'Shipping methods', 'woocommerce-advanced-shipping' ); ?>:<br />
	</th>
	<td class="forminp" id="<?php echo $this->id; ?>_shipping_methods">

		<table class='wp-list-table was-table widefat'>
			<thead>
				<tr>
					<th style='width: 17px;'></th>
					<th style='padding-left: 10px;'><?php _e( 'Title', 'woocommerce-advanced-shipping' ); ?></th>
					<th style='padding-left: 10px;'><?php _e( 'Shipping title', 'woocommerce-advanced-shipping' ); ?></th>
					<th style='padding-left: 10px; width: 100px;'><?php _e( 'Shipping price', 'woocommerce-advanced-shipping' ); ?></th>
					<th style='width: 70px;'><?php _e( '# Groups', 'woocommerce-advanced-shipping' ); ?></th>
				</tr>
			</thead>
			<tbody><?php

				$i = 0;
				foreach ( $method_conditions as $method_condition ) :

					$method_details = get_post_meta( $method_condition->ID, '_was_shipping_method', true );
					$conditions 	= get_post_meta( $method_condition->ID, '_was_shipping_method_conditions', true );

					$alt = ( $i++ ) % 2 == 0 ? 'alternate' : '';
					?><tr class='<?php echo $alt; ?>'>

						<td class='sort'>
							<input type='hidden' name='sort[]' value='<?php echo $method_condition->ID; ?>' />
						</td>
						<td>
							<strong>
								<a href='<?php echo get_edit_post_link( $method_condition->ID ); ?>' class='row-title' title='<?php _e( 'Edit Method', 'woocommerce-advanced-shipping' ); ?>'>
									<?php echo $method_condition->post_title; echo empty( $method_condition->post_title) ? __( 'Untitled', 'woocommerce-advanced-shipping' ) : null; ?>
								</a>
							</strong>
							<div class='row-actions'>
								<span class='edit'>
									<a href='<?php echo get_edit_post_link( $method_condition->ID ); ?>' title='<?php _e( 'Edit Method', 'woocommerce-advanced-shipping' ); ?>'>
										<?php _e( 'Edit', 'woocommerce-advanced-shipping' ); ?>
									</a>
									 |
								</span>
								<span class='trash'>
									<a href='<?php echo get_delete_post_link( $method_condition->ID ); ?>' title='<?php _e( 'Delete Method', 'woocommerce-advanced-shipping' ); ?>'>
										<?php _e( 'Delete', 'woocommerce-advanced-shipping' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td><?php echo empty( $method_details['shipping_title'] ) ? __( 'Shipping', 'woocommerce-advanced-shipping') : $method_details['shipping_title']; ?></td>
						<td><?php echo isset( $method_details['shipping_cost'] ) ? wc_price( $method_details['shipping_cost'] ) : ''; ?></td>
						<td><?php echo count( $conditions ); ?></td>
						</td>
					</tr><?php

				endforeach;

				if ( empty( $method_conditions ) ) :

					?><tr>
						<td colspan='2'><?php _e( 'There are no Advanced Shipping conditions. Yet...', 'woocommerce-advanced-shipping' ); ?></td>
					</tr><?php

				endif;

			?></tbody>
			<tfoot>
				<tr>
					<th colspan='5' style='padding-left: 10px;'>
						<a href='<?php echo admin_url( 'post-new.php?post_type=was' ); ?>' class='add button'>
							<?php _e( 'Add Advanced Shipping Method', 'woocommerce-advanced-shipping' ); ?>
						</a>
					</th>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>