<?php

if ( ! function_exists( 'detect_search_engines' ) ) {
	function detect_search_engines( $useragent ) {
		$searchengines = array(
			'Googlebot',
			'Slurp',
			'search.msn.com',
			'nutch',
			'simpy',
			'bot',
			'ASPSeek',
			'crawler',
			'msnbot',
			'Libwww-perl',
			'FAST',
			'Baidu',
		);
		
		$is_se = false;
		
		foreach ($searchengines as $searchengine){
			if ( ! empty($_SERVER['HTTP_USER_AGENT'] ) and false !== strpos( strtolower($useragent), strtolower($searchengine) ) ) {
				$is_se = true;
				break;
			}
		}
		if ($is_se) {
			return true;
		}
		else {
			return false;
		}

	}
}

/**
 * Test a users capability
 *
 * Checks if user has `manage_woocommerce` abilities by default.
 */
function cxecrt_test_user_role( $role = NULL ) {

	switch ( $role ) {
		
		case 'administrator':
			$capability = 'manage_options';
			break;
			
		case 'shop_manager':
		default:
			$capability = 'manage_woocommerce';
			break;
	}

	$user_id = get_current_user_id();
	
	return user_can( $user_id, $capability );
}

/**
 * Loads Resources required to render the cart templates,
 * if we try to render it while not on the frontend.
 */
function cxecrt_maybe_load_required_cart_resources() {
	
	$files = get_included_files();
	
	// All of the following are omitted during WC()->init(),
	// by being wrapped in `if ( is_request( 'frontend' ) )...`
	// and we may not be on the frontend.
	
	WC()->frontend_includes();
	
	$file_name = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, plugin_dir_path( WC_PLUGIN_FILE ) . 'includes\abstracts\abstract-wc-session.php' );
	if ( file_exists( $file_name ) && ! in_array( $file_name, $files ) ) { // ! class_exists( 'WC_Session' )
		// s( 'Had to include: abstract-wc-session.php' ); // Debug
		include_once( $file_name );
	}
	
	$file_name = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, plugin_dir_path( WC_PLUGIN_FILE ) . 'includes\class-wc-session-handler.php' );
	if ( file_exists( $file_name ) && ! in_array( $file_name, $files ) ) { // ! class_exists( 'WC_Session_Handler' )
		// s( 'Had to include: class-wc-session-handler.php' ); // Debug
		include_once( $file_name );
	}
	
	$file_name = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, plugin_dir_path( WC_PLUGIN_FILE ) . 'includes\wc-template-functions.php' );
	if ( file_exists( $file_name ) && ! in_array( $file_name, $files ) ) { // ! function_exists( 'wc_template_redirect' )
		// s( 'Had to include: wc-template-functions.php' ); // Debug
		include_once( $file_name );
	}
	
	if ( ! isset( WC()->session ) ) {
		// s( 'Had to do: session' ); // Debug
		$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
		WC()->session = new $session_class();
	}
	
	if ( ! isset( WC()->cart ) ) {
		// s( 'Had to do: cart' ); // Debug
		WC()->cart = new WC_Cart();
		remove_action( 'shutdown', array( WC()->cart, 'maybe_set_cart_cookies' ), 0 ); // Set cookies before shutdown and ob flushing.
	}
	
	if ( ! isset( WC()->customer ) ) {
		// s( 'Had to do: customer' ); // Debug
		WC()->customer = new WC_Customer();
	}
}

/**
 * Get the WC cart url.
 */

function cxecrt_get_woocommerce_cart_url( $suffix = '' ) {
	
	// Since WC2.5.0
	if ( function_exists( 'wc_get_cart_url' ) )
		return untrailingslashit( wc_get_cart_url() ) . "/{$suffix}";
	
	// If we on the front-end and the WC cart is already loaded.
	if ( isset( WC()->cart ) && method_exists( WC()->cart, 'get_cart_url' ) )
		return untrailingslashit( WC()->cart->get_cart_url() ) . "/{$suffix}";
	
	// If we on the backend and the WC cart is not loaded.
	if ( $cart_page_id = get_option( 'woocommerce_cart_page_id' ) )
		return untrailingslashit( get_permalink( $cart_page_id ) ) . "/{$suffix}";
	else
		return untrailingslashit( get_permalink( 'cart' ) ) . "/{$suffix}";
}

/**
 * Get the WC checkout url.
 */

function cxecrt_get_woocommerce_checkout_url( $suffix = '' ) {
	
	// Since WC2.5.0
	if ( function_exists( 'wc_get_checkout_url' ) )
		return untrailingslashit( wc_get_checkout_url() ) . "/{$suffix}";
	
	// If we on the front-end and the WC cart is already loaded.
	if ( isset( WC()->cart ) && method_exists( WC()->cart, 'get_checkout_url' ) )
		return untrailingslashit( WC()->cart->get_checkout_url() ) . "/{$suffix}";
	
	// If we on the backend and the WC cart is not loaded.
	if ( $cart_page_id = get_option( 'woocommerce_checkout_page_id' ) )
		return untrailingslashit( get_permalink( $cart_page_id ) ) . "/{$suffix}";
	else
		return untrailingslashit( get_permalink( 'cart' ) ) . "/{$suffix}";
}

/**
 * Get one of our options.
 *
 * Automatically mixes in our defaults if nothing is saved yet.
 *
 * @param  string $key key name of the option.
 * @return mixed       the value stored with the option, or the default if nothing stored yet.
 */
function cxecrt_get_option( $key ) {
	return get_option( $key, cxecrt_get_default( $key ) );
}

/**
 * Get one of defaults options.
 *
 * @param  string $key key name of the option.
 * @return mixed       the default set for that option, or FALSE if none has been set.
 */
function cxecrt_get_default( $key ) {
	
	$settings = WC_Email_Cart_Settings::get_settings();
	
	$default = FALSE;
	
	foreach ( $settings as $setting ) {
		if ( isset( $setting['id'] ) && $key == $setting['id'] && isset( $setting['default'] ) ) {
			$default = $setting['default'];
		}
	}
	
	return $default;
}



?>