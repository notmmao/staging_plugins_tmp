jQuery(document).ready(function($) {
	var GlobalHasSizeOption = false;
	var GlobalSizeCost = 0;
	var global_has_role_discount = false;
	var global_role_discount = 0;

	function isNumeric(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}

	function init_addon_totals() {
      	$('.cart').on( 'keyup change', '.product-addon input, .product-addon textarea', function() {
			if ( $(this).attr('maxlength') > 0 ) {
				var value = $(this).val();
				var remaining = $(this).attr('maxlength') - value.length;
				$(this).next('.chars_remaining').find('span').text( remaining );
			}
		} );

		$('.cart').find('.addon-custom, .addon-custom-textarea').each(function(){

			if ( $(this).attr('maxlength') > 0 ) {
				$(this).after('<small class="chars_remaining"><span>' + $(this).attr('maxlength') + '</span> ' + woocommerce_addons_params.i18n_remaining + '</small>' );
			}

		} );

		$('.cart').on( 'change', '.product-addon input, .product-addon textarea, .product-addon select, input.qty, .mod-quantity-input', function() {
			var $cart = $(this).closest('.cart');
			$cart.trigger('woocommerce-product-addons-update');
		} );

		$('body').on('found_variation', '.variations_form', function( event, variation ) {
			var $variation_form = $(this);
			var $totals         = $variation_form.find('#product-addons-total');

			if ( $( variation.price_html ).find('.amount:last').size() ) {
		 		product_price = $( variation.price_html ).find('.amount:last').text();
				product_price = product_price.replace( woocommerce_addons_params.currency_format_thousand_sep, '' );
				product_price = product_price.replace( woocommerce_addons_params.currency_format_decimal_sep, '.' );
				product_price = product_price.replace(/[^0-9\.]/g, '');
				product_price = parseFloat( product_price );

				$totals.data( 'price', product_price );
			}
			$variation_form.trigger('woocommerce-product-addons-update');
		});

		$('.cart').bind( 'woocommerce-product-addons-update', function() {

			var addons_total  = 0;
			var global_addons = 0;

			var isPercentage  = false;
			var $cart         = $(this);
			var $totals       = $cart.find('#product-addons-total');
			var product_price = parseFloat($totals.data( 'price' ));
			if($('#role-discount').length && $('#role-discount').data('value') > 0) {
				global_role_discount = parseInt($('#role-discount').data('value'));
				global_has_role_discount = true;
			}
			var product_type  = $totals.data( 'type' );
                 // alert(product_price);
			// Move totals
			if ( product_type == 'variable' || product_type == 'variable-subscription' ) {
				$cart.find('.single_variation').after( $totals );
			}

			var qty = parseFloat( $cart.find('.mod-quantity-input').val() );
			if (isNaN(qty)) {
				qty = parseFloat( $cart.find('.qty').val() );
			}
			qty = parseFloat(qty);

			$('.addon').each(function() {
				//  $(this).data('addon-name') == 'standard sizes'
				if ($(this).data('addon-name')) {
					var addonName = $(this).data('addon-name');
					if(addonName.toLowerCase() == 'size'){
						console.log('Size found!');

						GlobalHasSizeOption = true;
						if ($(this).find('option:selected').data('price-percentage') == true) {
							GlobalSizeCost = $(this).find('option:selected').data('price');
							console.log("Size addon: " + GlobalSizeCost + "%");
							GlobalSizeCost = parseFloat(GlobalSizeCost / 100 * (product_price));
							console.log("New product price is: " + GlobalSizeCost);
						};
					}
				}
			});

			$cart.find('.addon').each(function() {
				var unit_addon_cost = 0;

				if ( $(this).is('.addon-custom-price') ) {
					unit_addon_cost = $(this).val();
					
				} else if ( $(this).is('.addon-input_multiplier') ) {
					if( isNaN( $(this).val() ) || $(this).val() == "" ) { // Number inputs return blank when invalid
						$(this).val('');
						$(this).closest('p').find('.addon-alert').show();
					} else {
						if( $(this).val() != "" ){
							$(this).val( Math.ceil( $(this).val() ) );
						}
						$(this).closest('p').find('.addon-alert').hide();
					}
					unit_addon_cost = $(this).data('price') * $(this).val();
					//alert(unit_addon_cost);
					
				} else if ( $(this).is('.addon-checkbox, .addon-radio') ) {
					if ( $(this).is(':checked') )
						unit_addon_cost = $(this).data('price');

					if ($(this).data('price-percentage')) {
						isPercentage = true;
					}
				} else if ( $(this).is('.addon-select') ) {
					if ( $(this).val() )
						unit_addon_cost = $(this).find('option:selected').data('price');
					if ($(this).find('option:selected').data('price-percentage')) {
						isPercentage = true;
					}
				} else {
					if ( $(this).val() )
						unit_addon_cost = $(this).data('price');
				}

				unit_addon_cost = parseFloat(unit_addon_cost);

				var addonName = '';
 				if ($(this).data('addon-name')) {
 					addonName = $(this).data('addon-name');
 				}

				if (addonName == 'MAILING SERVICES / SORTING FEES') {
					global_addons += unit_addon_cost;
					unit_addon_cost = 0;
					// console.log(global_addons);
					addons_total = addons_total + unit_addon_cost;
					return;
				}

     			if (isPercentage) {
     				var additionalCost = GlobalSizeCost;
     				if(addonName.toLowerCase() == 'size' || addonName.toLowerCase() == 'standard sizes'){
						additionalCost = 0;
					}
					if (unit_addon_cost != 0) {
						unit_addon_cost = parseFloat(unit_addon_cost / 100 * (product_price + additionalCost));
						console.log('Option: ' + $(this).data('addon-name') + ",  " + unit_addon_cost);
					}
				}

				if (isNaN(unit_addon_cost)) {
					unit_addon_cost = 0;
				}

				addons_total = addons_total + unit_addon_cost;
			});

				var formatted_addons_total = accounting.formatMoney( addons_total, {
					symbol 		: woocommerce_addons_params.currency_format_symbol,
					decimal 	: woocommerce_addons_params.currency_format_decimal_sep,
					thousand	: woocommerce_addons_params.currency_format_thousand_sep,
					precision 	: woocommerce_addons_params.currency_format_num_decimals,
					format		: woocommerce_addons_params.currency_format
				} );

				// Note(stas):
				// Apply quantity discounts 
				// discountRules is declared in: woocommerce\templates\single-product\add-to-cart\simple.php

				var discountAmount = 0;
				var discountType = false;
				var calculated_discount = 0;

				product_unit_price = parseFloat( product_price );
				product_unit_price_to_format = product_unit_price + addons_total;

				product_total_price = parseFloat(((product_price + addons_total) * qty));


				if (!isNumeric(product_unit_price_to_format) || product_unit_price_to_format < 0) {
					product_unit_price_to_format = 0;
				}
				console.log('Unit price before round: ' + product_unit_price_to_format);
				product_unit_price_to_format = Math.round(product_unit_price_to_format * 100) / 100;
				product_total_price_to_format = product_unit_price_to_format * qty;


				// Apply discounts
				if(discountRules.length) {
					for (var key in discountRules[0]['rules']) {
						var ruleOptions = discountRules[0]['rules'][key];

						if (qty >= ruleOptions.from && qty <= ruleOptions.to) {
							discountAmount = ruleOptions.amount;
							discountType = ruleOptions.type;
							break;
						};
					}

					if (discountAmount) {
						console.log('Discount found:');
						console.log('	' + discountAmount + ', ' + discountType);
						console.log('	Price before: ' + product_total_price_to_format);
						if (discountType == 'percentage_discount') {
							product_total_price_to_format = Math.round(((product_unit_price_to_format/100) * (100 - discountAmount)) * 100) / 100 * qty; 
							var discount_text = 'Including ' + discountAmount + '%' + ' quantity discount';
						} else if (discountType == 'price_discount') {
							product_total_price_to_format = (product_unit_price_to_format - discountAmount) * qty;
							var discount_text = 'Including $' + discountAmount + ' quantity discount';
						} else {
							product_total_price_to_format = discountAmount;
							var discount_text = 'Including quantity discount';
						}
						console.log('	Price after: ' + product_total_price_to_format);

					}
				}

				// NOTE(stas): apply global discounts (eg fro mailing fees)
				product_unit_price_to_format += (global_addons / qty);
				product_total_price_to_format += global_addons;

				var formatted_unit_total = accounting.formatMoney( product_unit_price_to_format, {
					symbol 		: woocommerce_addons_params.currency_format_symbol,
					decimal 	: woocommerce_addons_params.currency_format_decimal_sep,
					thousand	: woocommerce_addons_params.currency_format_thousand_sep,
					precision 	: woocommerce_addons_params.currency_format_num_decimals,
					format		: woocommerce_addons_params.currency_format
				} );

				var formatted_role_discount_price = 0;

				if (global_has_role_discount) {
					var new_price = product_unit_price_to_format * (1 - (global_role_discount/100));
					new_price = Math.round(new_price * 100) / 100;

					new_price *= qty;
					var formatted_role_discount_price = accounting.formatMoney(new_price, {
						symbol 		: woocommerce_addons_params.currency_format_symbol,
						decimal 	: woocommerce_addons_params.currency_format_decimal_sep,
						thousand	: woocommerce_addons_params.currency_format_thousand_sep,
						precision 	: woocommerce_addons_params.currency_format_num_decimals,
						format		: woocommerce_addons_params.currency_format
					} );
				}

				var formatted_grand_total = accounting.formatMoney( product_total_price_to_format, {
					symbol 		: woocommerce_addons_params.currency_format_symbol,
					decimal 	: woocommerce_addons_params.currency_format_decimal_sep,
					thousand	: woocommerce_addons_params.currency_format_thousand_sep,
					precision 	: woocommerce_addons_params.currency_format_num_decimals,
					format		: woocommerce_addons_params.currency_format
				} );

				if ( $('.single_variation_wrap .subscription-details').length ) {
					var subscription_details = $('.single_variation_wrap .subscription-details').clone().wrap('<p>').parent().html();
					formatted_addons_total += subscription_details;
					if ( formatted_unit_total ){
						formatted_unit_total += subscription_details;
					}
				}

				html = '<dl class="product-addon-totals"><dt>' + woocommerce_addons_params.i18n_addon_total + '</dt><dd><strong><span class="amount">' + formatted_addons_total + '</span></strong></dd>';

				html = html + '<dt>Unit price: </dt><dd><strong><span class="amount">' + formatted_unit_total + '</span></strong></dd>';
				
				if (global_has_role_discount) {
					html = html + '<dt>Grand total: </dt><dd><del><strong><span class="amount">' + formatted_grand_total + '</span></strong></del></dd>';
					html = html + '<dt>&nbsp;</dt><dd><strong><span class="amount" style="font-size: 1.1em;color: rgb(234, 109, 60);margin-left: -86px;">Your price: ' + formatted_role_discount_price + '</span></strong></dd>';
				} else {
					html = html + '<dt>Grand total: </dt><dd><strong><span class="amount">' + formatted_grand_total + '</span></strong></dd>';
				}
				if (discountAmount > 0) {
					html = html + '<dt style="font-size: 0.8em; color:#888; -moz-hyphens: none; margin-top: -18px;">' + discount_text + '</dt><dd></dd>';
				}
				html = html + '</dl>';

				$totals.html( html );

			$('body').trigger('updated_addons');

			console.log('');
			console.log('-----------------------------------------------');

		} );

		$('.cart').find('.addon-custom, .addon-custom-textarea, .product-addon input, .product-addon textarea, .product-addon select, .mod-quantity-input').change();
	}

	init_addon_totals();

	$( '.variations_form .product-addon' ).closest( '.cart' ).find( '.variations select' ).change();

	// Quick view
	$('body').on('quick-view-displayed', function() {
		init_addon_totals();
	});
	

	
	
	
});
