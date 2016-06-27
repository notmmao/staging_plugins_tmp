<?php
$loop = 0;
$current_value = isset( $_POST['addon-' . sanitize_title( $addon['field-name'] ) ] ) ? $_POST[ 'addon-' . sanitize_title( $addon['field-name'] ) ] : '';
?>
<p class="form-row form-row-wide addon-wrap-<?php echo sanitize_title( $addon['field-name'] ); ?>">
	<select class="addon addon-select" data-addon-name="<?php echo $addon['name']; ?>" name="addon-<?php echo sanitize_title( $addon['field-name'] ); ?>">
	<?php if ( ! isset( $addon['required'] ) ) : ?>
		<option value=""><?php _e('None', 'woocommerce-product-addons'); ?></option>
	<?php else : ?>
		<option value=""><?php _e('Select an option...', 'woocommerce-product-addons'); ?></option>
	<?php endif; ?>

	<?php foreach ( $addon['options'] as $option ) :
		$loop++;
		$percentage = '';

		if (strpos($option['price'], '%')) {
			$percentage = 'data-price-percentage="true"';
			str_replace('%', '', $option['price']);
			$price = get_product_addon_price_for_display( $option['price'] );
			$price = str_replace('$', ' ', $price);

			if ($price > 0) {
				$price = ' (+' . $price . '%)';
			} else {
				$price = ' (' . $price . '%)';
			}
			
		} else {
			$price = $option['price'] > 0 ? ' (' . woocommerce_price( get_product_addon_price_for_display( $option['price'] ) ) . ')' : '';
		}
		?>
		<option <?php echo $percentage ?> data-price="<?php echo get_product_addon_price_for_display( $option['price'] ); ?>" value="<?php echo sanitize_title( $option['label'] ) . '-' . $loop; ?>" <?php selected( $current_value, sanitize_title( $option['label'] ) . '-' . $loop ); ?>><?php echo wptexturize( $option['label'] ) ?></option>
	<?php endforeach; ?>

	</select>
</p>
