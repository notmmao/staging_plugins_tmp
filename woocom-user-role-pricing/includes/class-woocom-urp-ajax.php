<?php

/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 8/31/2015
 * Time: 3:37 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Woocom_URP_AJAX {

	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax)
	 */
	public static function add_ajax_events() {
		// wcurp_EVENT => nopriv
		$ajax_events = array(
			'set_customer_data'                 => false
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wcurp_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wcurp_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function set_customer_data() {

		check_ajax_referer( 'wcurp_set_customer_data', 'security' );

		if( isset($_POST['user_id'])  ) {
			$user_id = (int) $_POST['user_id'];
		} else {
			$user_id = 0;
		}

		$customer = new Woocom_URP_Customer($user_id);

		update_option('woocom_urp_admin_ajax_customer_id', $user_id);

		$info = $customer->get_pricing_display_info();

		wp_send_json($info);

	}

}

Woocom_URP_AJAX::init();