jQuery(function(){
    jQuery('#the-list').on('click', '.editinline', function(){

		var post_id = jQuery(this).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $wc_inline_data = jQuery('#woocom_urp_inline_' + post_id );

		var $prices = ajax_object.prices;

		jQuery.each($prices, function(p){

			var price 		= $wc_inline_data.find('.'+p).text();

			jQuery('input[name="'+p+'"]', '.inline-edit-row').val(price);

		});


    });

});
