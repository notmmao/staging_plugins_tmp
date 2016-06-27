jQuery( function($){

	$( document ).ready( function() {
		
		// Attach Open event to our link, and any user created links
		// #cxecrt_dropdown_btn, .cxecrt-open
		$( document ).on( 'click', '[href *= "#email-cart"], [href *= "#cxecrt-save-cart"]', function(){
			open_cart();
			return false;
		});
		
		// Init the Slides - move them to the right frames to start.
		go_to_slide( 1, $save_get_button_slides );
		go_to_slide( 1, $email_button_slides );
		go_to_slide( 1, $main_modal_slides, { resize_modal: true } );
		
		// Check if user has initiated a deep link to our Save & Share Cart
		if ( '#email-cart' == window.location.hash || '#cxecrt-save-cart' == window.location.hash ) {
			setTimeout( function(){
				open_cart();
			}, 1000 );
		}
		
		/**
		 * Init Tip Tip's (renamed to cxecrtTip)
		 */
		$('[data-tip]').each( function( index, element ) {

			var $element = $(element);
			
			// Tooltips
			$element.cxecrtTip({
				'attribute' : 'data-tip',
				'fadeIn' : 300,
				'fadeOut' : 300,
				'delay' : 200,
				'defaultPosition' : 'top',
				'edgeOffset' : 3,
				'maxWidth' : '300px',
				//'enter' : function() {
				//	//jQuery("#cxecrtTip_holder").addClass("cx_tip_tip");
				//	jQuery("#cxecrtTip_holder #cxecrtTip_content").addClass('cx_tip_tip');
				//}
				//'keepAlive' : true,
				//'activation' : 'click'
			});
			
		});
		
	});
	
	
	// Handle Slide.
	$( document ).on( 'click', '#cxecrt_send_email_new', function() {
		go_to_slide( 2, $main_modal_slides, { resize_modal: true } );
		return false;
	});

	// Handle back button.
	$( document ).on( 'click', '.cxecrt-top-bar-back', function() {
		
		go_to_slide( 1, $main_modal_slides, { resize_modal: true } );
		
		setTimeout( function(){
			go_to_slide( 1, $email_button_slides );
		}, 300 );
		return false;
	});

	// Handle close button.
	$( document ).on( 'click', '.cxecrt-cross, .cxecrt-button-done, #cxecrt_finish_new', function() {
		close_cart();
		setTimeout( function(){
			go_to_slide( 1, $main_modal_slides, { resize_modal: true } );
		}, 500 );
		return false;
	});
	
	// Redirect to Cart/Checkout select
	$( document ).on( 'change', '#landing_page_save', function(){
		
		$select = $(this);
		$related_select = $('#landing_page_email');

		$related_select.val( $select.val() );
	});
	
	
	// Save Cart and Get Link Ajax
	$( document ).on( 'click', '#cxecrt_submit_get_link', function() {

		var $button = $(this);
		var $form = $button.closest('form');
		
		var error = false;
		
		if ( error ) {
			return false;
		}
		else {
			
			$button.addClass('cxecrt-button-loading');
			
			// var form_data = $form.serialize() + '&action=send_cart_email_ajax&security=' + cxecrt_params.order_item_nonce;
			var form_data = $form.serialize() + '&action=save_cart_and_get_link_ajax';
			
			jQuery.ajax({
				type     : 'post',
				dataType : 'json',
				url      : cxecrt_params.ajax_url,
				data: form_data,
				success: function( data ) {
					
					setTimeout( function(){

						$('#success-get-link-url').val( data.cart_url );
						
						$('form.cxecrt-send-cart-email-form #cart_id').val( data.cart_id );
					
						go_to_slide( 2, $save_get_button_slides );
						
					}, 1000 );
					
					setTimeout( function(){
						$button.removeClass('cxecrt-button-loading');
					}, 2000 );
				},
				error: function(xhr, status, error) {
					
				}
			});
		}
		
		return false;
	});

	
	function show_error_message( element_to_append_to, message ) {
		
		// There's already an error out there so bail!
		if ( $( element_to_append_to ).prev('.cxecrt-form-error').length ) return;
		
		var show_time = 3000;
		
		var element_to_append_to = $( element_to_append_to );
		var new_error = $('<div class="cxecrt-form-error cxecrt-form-error-to-email cxecrt-form-error-hide">' + message + '</div>');
		
		new_error.insertBefore(element_to_append_to);
		
		setTimeout(function() {
			new_error.removeClass('cxecrt-form-error-hide');
		}, 50 );
		
		setTimeout(function() {
			new_error.addClass('cxecrt-form-error-hide');
		}, 250 + show_time );
		
		setTimeout(function() {
			new_error.remove();
		}, 200 + show_time + 300 );
	}
	
	
	// Send Cart Link Ajax
	$( document ).on( 'click', '#cxecrt_save_and_send', function() {
		
		var $button = $(this);
		var $form   = $(this).closest('form');
		
		$button.addClass('cxecrt-button-loading');
		
		var error = false;
		
		var to_email_address = $.trim( $form.find('#to_email_address').val() );
		if ( to_email_address == "" ) {
			
			show_error_message(
				'.cxecrt-row-to-address',
				'<strong>Oops</strong>: Please enter at least one email address to send to.'
			);
			error = true;
		}
		
		/*
		var from_email_address = $.trim( $('#from_email_address').val() );
		if ( from_email_address == "" ) {
			
			show_error_message(
				'.cxecrt-row-from-address',
				'<strong>Oops</strong>: Please enter your email address - who is the email from.'
			);
			error = true;
		}
		*/
		
		if ( error ) {
			
			$button.removeClass('cxecrt-button-loading');
			return false;
		}
		else {
			
			// var form_data = $form.serialize() + '&action=send_cart_email_ajax&security=' + cxecrt_params.order_item_nonce;
			var form_data = $form.serialize() + '&action=send_cart_email_ajax';
			
			jQuery.ajax({
				type     : 'post',
				dataType : 'json',
				url      : cxecrt_params.ajax_url,
				data: form_data,
				success: function( data ) {
					
					if ( 'sent' == data.send_status ) {
						
						setTimeout( function(){
							$button.removeClass('cxecrt-button-loading');
						}, 800 );
						
						// Go to a success slide.
						go_to_slide( 2, $email_button_slides );
					}
					else {
						
						$button.removeClass('cxecrt-button-loading');
						
						show_error_message(
							'#cxecrt_save_and_send',
							'<strong>Sorry</strong>: Your email can not be sent at this time.'
						);
					}
				},
				error: function(xhr, status, error) {
					
					$button.removeClass('cxecrt-button-loading');
					
					show_error_message(
						'#cxecrt_save_and_send',
						'<strong>Sorry</strong>: Your email can not be sent at this time.'
					);
				}
			});
		}
		
		return false;
	});
	
	
	/**
	 * Show notification when cart is empty.
	 */
	
	$( document.body ).on( 'added_to_cart', display_empty_cart_notification );
	setTimeout( display_empty_cart_notification, 1 );
	
	function display_empty_cart_notification() {
		
		if ( ! $.cookie( 'woocommerce_items_in_cart' ) ) {
			// There's no items in cart so display notification.
			go_to_slide( 3, $save_get_button_slides );
		}
		else {
			go_to_slide( 1, $save_get_button_slides );
		}
	}
	
	
	/**
	 * Test Buttons.
	 */
	if ( false ) {
		
		$('body').prepend( '<div class="cxecrt-test-buttons">' ); // Prepend the buttons holder.
		
		// Modal Popups.
		
		$button = $('<a>POPUP CENTER</a>');
		$('.cxecrt-test-buttons').append( $button );
		$button.click( function() {
			open_modal( '#cxecrt-save-share-cart-modal', { position: 'center', close_button: false } );
			return false;
		});
		
		$button = $('<a>POPUP TOP-RIGHT <i>no-cover</i></a>');
		$('.cxecrt-test-buttons').append( $button );
		$button.click( function() {
			open_modal( '#cxecrt-save-share-cart-modal', { position: 'top-right', cover: false, close_button: false } );
			return false;
		});
		
		$button = $('<a>POPUP TOP-CENTER <i>don\'t close_click_outside</i></a>');
		$('.cxecrt-test-buttons').append( $button );
		$button.click( function() {
			open_modal( '#woocommerce_product_categories-3, #woocommerce_widget_cart-2', { position: 'top-center', close_button: true, close_click_outside: false } );
			return false;
		});
		
		// Break
		$('.cxecrt-test-buttons').append('<br/>');
		
		// Slide Changing buttons
		
		$button = $('<a class="alt">SLIDE 1</a>');
		$('.cxecrt-test-buttons').append( $button );
		$button.click( function() {
			go_to_slide( 1, $main_modal_slides, { resize_modal: true } );
			return false;
		});
		
		$button = $('<a class="alt">SLIDE 2</a>');
		$('.cxecrt-test-buttons').append( $button );
		$button.click( function() {
			go_to_slide( 2, $main_modal_slides, { resize_modal: true } );
			return false;
		});
	}
	
	
	
	
	// Open Save & Share Cart popup
	function open_cart() {
		open_modal( '#cxecrt-save-share-cart-modal', { close_button: false } )
		// Set hash.
		window.location.hash = 'cxecrt-save-cart';
		return false;
	}
	
	// Close Save & Share Cart block
	function close_cart() {
		close_modal()
		// Set hash.
		if ( '#email-cart' == window.location.hash || '#cxecrt-save-cart' == window.location.hash ) window.location.hash = "";
		return false;
	}
	
	
	
	/**
	 * Modal Popups.
	 */
	
	function init_modal( $close_button ) {
		
		// Add the required elements if they not in the page yet.
		if ( ! $('.cxecrt-component-modal-popup').length ) {
			
			// Add the required elements to the dom.
			$('body').append( '<div class="cxecrt-component-modal-temp cxecrt-component-modal-hard-hide"></div>' );
			$('body').append( '<div class="cxecrt-component-modal-cover cxecrt-component-modal-hard-hide"></div>' );
			$('body').append( '<div class="cxecrt-component-modal-popup cxecrt-component-modal-hard-hide"></div>' );
			
			// Enable the close_click_outside
			$('html').click(function(event) {
				if ( 0 === $('.cxecrt-component-modal-popup.cxecrt-component-modal-hard-hide').length && 0 !== $('.cxecrt-close-click-outside').length && 0 === $(event.target).closest('.cxecrt-close-click-outside').length ) {
					close_modal();
					return false;
				}
			});
		}
	}
	
	function open_modal( $selector, $settings ) {
		
		// Set defaults
		$defaults = {
			position            : 'center',
			cover               : true,
			close_button        : true,
			close_click_outside : true,
		};
		$settings = $.extend( true, $defaults, $settings );
		
		// Init modal - incase this is first run.
		init_modal( $settings.close_button );
		
		// Move any elements that may already be in the modal out, to the temp holder.
		$('.cxecrt-component-modal-temp').append( $('.cxecrt-component-modal-popup' ).children() );
		
		// Get content to load in modal.
		$content = $( $selector );
		
		// If content to load doesn't exist then rather close the whole modal and bail.
		if ( ! $content.length ) {
			close_modal();
			console.log('Content to load into modal does not exists.');
			return;
		}
		
		// Enable whether to close when clicked outside the modal.
		if ( $settings.close_click_outside )
			$('.cxecrt-component-modal-popup' ).addClass('cxecrt-close-click-outside');
		else
			$('.cxecrt-component-modal-popup' ).removeClass('cxecrt-close-click-outside');
		
		
		// Show the close button, or remove it if not.
		if ( $settings.close_button )
			$('.cxecrt-component-modal-popup').append('<span class="cxecrt-cross cxecrt-top-bar-cross cxecrt-icon-cancel"></span>');
		else
			$('.cxecrt-component-modal-popup').children('.cxecrt-cross').remove();
		
		// Add the intended content into the modal.
		$('.cxecrt-component-modal-popup' ).prepend( $content );
		
		// Remove the class that's hiding the modal.
		$content.removeClass( 'cxecrt-component-modal-content-hard-hide' );
		
		// Apply positioning.
		$( '.cxecrt-component-modal-popup' )
			.removeClass( 'cxecrt-modal-position-center cxecrt-modal-position-top-right cxecrt-modal-position-top-center' )
			.addClass( 'cxecrt-modal-position-' + $settings.position );
		
		// Move elements into the viewport by removing hard-hide.
		$('.cxecrt-component-modal-popup').removeClass( 'cxecrt-component-modal-hard-hide' );
		
		// Fade in the elements.
		$('.cxecrt-component-modal-popup').addClass( 'cxecrt-modal-play-in' );
		
		// Optionally show the back cover.
		if ( $settings.cover ) {
			$('.cxecrt-component-modal-cover').removeClass( 'cxecrt-component-modal-hard-hide' );
			$('.cxecrt-component-modal-cover').addClass( 'cxecrt-modal-play-in' );
		}
		else {
			// If not showing then make sure to fade it out.
			$('.cxecrt-component-modal-cover').removeClass( 'cxecrt-modal-play-in' );
			setTimeout(function() {
				$('.cxecrt-component-modal-cover').addClass( 'cxecrt-component-modal-hard-hide' );
			}, 200 );
		}
	}
	
	function close_modal() {
		
		// Fade out the elements.
		$('.cxecrt-component-modal-cover, .cxecrt-component-modal-popup').removeClass( 'cxecrt-modal-play-in' );
		
		// Move elements out the viewport by adding hard-hide.
		setTimeout(function() {
			$('.cxecrt-component-modal-cover, .cxecrt-component-modal-popup').addClass( 'cxecrt-component-modal-hard-hide' );
			
			// Remove specific positioning.
			$('.cxecrt-component-modal-popup')
				.removeClass( 'cxecrt-modal-position-center cxecrt-modal-position-top-right cxecrt-modal-position-top-center' );
			
		}, 200 );
	}
	
	function resize_modal( $to_height ) {
		
		// Init modal - incase this is first run.
		init_modal();
		
		// Cache elements.
		$modal_popup = $('.cxecrt-component-modal-popup');
		
		// Get the intended heights.
		var $to_height = ( $to_height ) ? $to_height : $modal_popup.outerHeight();
		var $margin_top = ( $to_height / 2 );
		
		// Temporarily enable margin-top transition, do the height-ing/margin-ing, then remove the transtion.
		$modal_popup.css({ height: $to_height, marginTop: -$margin_top, transitionDelay: '0s', transition: 'margin .3s' });
		setTimeout( function(){
			$modal_popup.css({ height: '', transitionDelay: '', transition: '' });
		}, 1000 );
	}
	
	
	/**
	 * Slides.
	 */
	
	var $main_modal_slides = [
		'.cxecrt-main-modal-slide-1',
		'.cxecrt-main-modal-slide-2',
		'.cxecrt-main-modal-slide-3',
		'.cxecrt-main-modal-slide-4',
	];
	
	var $save_get_button_slides = [
		'.cxecrt-save-get-button-slide-1',
		'.cxecrt-save-get-button-slide-2',
		'.cxecrt-save-get-button-slide-3',
	];
	
	var $email_button_slides = [
		'.cxecrt-email-button-slide-1',
		'.cxecrt-email-button-slide-2',
		'.cxecrt-email-button-slide-3',
	];

	function go_to_slide( $to_slide, $slides_array, $settings ){
		
		// Set defaults
		$defaults = {
			resize_modal: false,
		};
		$settings = $.extend( true, $defaults, $settings );
		
		var $slide_index = $to_slide - 1;
		var $slides_array = $slides_array.slice();
		var $slide_selector = $slides_array.splice( $slide_index, 1 ).join(', ');
		var $other_slides_selectors = $slides_array.join(', ');
		var $to_slide = $( $slide_selector );
		var $container = $to_slide.parent();
		var $from_slide = $( $container.find('.cxecrt-component-slide-current') );
		var $other_slides = $( $other_slides_selectors );
		var $first_load = ( 0 == $container.find('.cxecrt-component-slide-current').length );

		// Bail is already the current slide
		if ( $to_slide.hasClass('cxecrt-component-slide-current') ) return false;
		
		var $from_height = $container.height();
		$container.height( $from_height );
		
		// Fade Out all except current slide
		$other_slides
			.removeClass('cxecrt-component-slide-current')
			.addClass('cxecrt-component-slide-inactive');

		// Move destination slide to the front of the container and fade in
		$container.append( $to_slide );
		$to_slide
			.removeClass('cxecrt-component-slide-inactive')
			.addClass('cxecrt-component-slide-current');
		
		// Transition to the height of the new slide.
		var $to_height = $to_slide.outerHeight();
		$container.css({ height: $to_height });
		setTimeout( function(){
			$container.css({ height: '' });
		}, 1000 );
		
		// Resize the modal.
		if ( $settings.resize_modal ) {
			resize_modal( $to_height );
		}
		
		// Return the height.
		return $to_height;
	}
	
});