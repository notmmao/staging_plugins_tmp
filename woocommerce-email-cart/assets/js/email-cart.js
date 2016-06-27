jQuery( function($){

	$( document ).ready( function() {

		/**
		 * From Cart Reports
		 */
		var expiration_row = jQuery('#cxecrt_cart_expiration_time').closest('tr');
		var expiration_checked = jQuery("#cxecrt_cart_expiration_active:checked").length;

		var expiration_opt_in = jQuery("#cxecrt_cart_expiration_active");

		if (expiration_checked == 0) {
			expiration_row.hide();
		}

		jQuery('#cxecrt_cart_expiration_active').click(function() {
			expiration_row.toggle();
		});
		
		/**
		 * Notification on attempted 'trash' of Cart.
		 */
		
		// On admin cart list.
		$( document ).on( 'click', '.post-type-stored-carts .bulkactions #doaction, .post-type-stored-carts .bulkactions #doaction2', function() {
			if ( 'trash' == $( this ).parent().children('[name="action2"]').val() || 'trash' == $( this ).parent().children('[name="action"]').val() ) {
				if ( ! confirm( cxecrt_params.i18n_delete_cart_confirm ) ) {
					return false;
				}
			}
		} );
		
		// On admin cart edit.
		$( '.submitdelete.deletion' ).cxecrtTip({
			// 'attribute': 'data-tip',
			'content': cxecrt_params.i18n_delete_cart_confirm,
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		});
		
		/**
		 * Overwrite Cart Panel
		 */
		
		// Init the panel
		$('.cxecrt-overwrite-cart-holder')
			.slideUp(0)
			.css({ position: 'relative', visibility: 'visible' });
		
		// Toggle slide to open panel.
		$( document ).on( 'click', '.cart-edit-button, .cancel-button', function(){
			$('.cxecrt-overwrite-cart-holder').slideToggle(300);
			return false;
		});

		// Add confirm dialog to the overwrite button.
		$( document ).on( 'click', '.overwrite-button', function(){
			
			if ( ! confirm( cxecrt_params.i18n_overwrite_cart_confirm ) ) {
				return false;
			}
		});

		/**
		 * Toggle Redirect to Cart/Checkout
		 */
		 $( document ).on( 'change', '[name="landing_page"]', function(){
			
			// Get elements.
			$radio_input = $(this);
			$related_text_input = $('#landing_page_display');
			
			// Update value.
			$related_text_input.val( $radio_input.data('url') );
		});

	});

});