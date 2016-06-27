jQuery( function($){
	
	// add the 'calc line totals' button
	$('#woocommerce-order-items .buttons-alt').prepend('<button type="button" class="button calc_line_totals">' + woocommerce_admin_calc_line_totals.calc_line_totals_label + '</button>');
	
	// calculate line totals
	$('button.calc_line_totals').on('click', function() {
		
		// Block write panel
		$('.woocommerce_order_items_wrapper').block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_writepanel_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
		
		var answer = confirm(woocommerce_admin_calc_line_totals.calc_line_totals);
		
		if (answer) {
			
			var $items = $('#order_items_list tr.item');
			
			$items.each(function( idx ){
	
				var $row = $(this);
				
				var item_variation = $.grep( $row.find('input[type="hidden"]'), function(item) {
					return $(item).attr('name').substr(0, 14) == 'item_variation';
				});
	
				var data = {
					action:         'woocommerce_calc_line_totals',
					item_id:        $row.find('input.item_id').val(),
					item_variation: item_variation.length ? $(item_variation[0]).val() : 0,
					item_quantity:  $row.find('input.quantity').val(),
					security:       woocommerce_admin_calc_line_totals.calc_totals_nonce
				};
	
				$.post( woocommerce_admin_calc_line_totals.ajax_url, data, function(response) {
					
					var result = jQuery.parseJSON( response );
					
					$row.find('input.line_subtotal').val( result.line_subtotal );
					$row.find('input.line_total').val( result.line_total );

					if (idx == ($items.size() - 1)) {
						$('.woocommerce_order_items_wrapper').unblock();
					}
					
				});
				
			});
		} else {
			$('.woocommerce_order_items_wrapper').unblock();
		}
		
		return false;
	}).hover(function() {
		$('#order_items_list input.line_subtotal, #order_items_list input.line_total').css('background-color', '#d8c8d2');
	}, function() {
		$('#order_items_list input.line_subtotal, #order_items_list input.line_total').css('background-color', '');
	});
});