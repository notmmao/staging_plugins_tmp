<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = get_option('woocom_urp_options');
if('yes' === $options['uninstall_delete_data']) {

	// delete all custom prices from postmeta
	$prices = $options['prices'];
	// remove  _wholesale_price keys as that is handled by that plugin
	if(isset($prices['_wholesale_price'])) unset($prices['_wholesale_price']);
	if(isset($prices['_regular_price'])) unset($prices['_regular_price']);
	$keys = array_keys($prices); // keys of any added prices
	global $wpdb;
	$table = $wpdb->postmeta;
	foreach($keys as $key) {
		$wpdb->delete($table, array('meta_key' => $key), '%s');
	}
	$plugin_options = array('woocom_urp_options', 'woocom_urp_admin_ajax_customer_id', 'woocom_urp_license_key', 'woocom_urp_license_status' );
	foreach($plugin_options as $option) {
		delete_option($option);
		delete_site_option($option);
	}

}
