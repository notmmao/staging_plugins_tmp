<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function cxecrt__uninstall() {
	global $wpdb;
	$sql = "SELECT * FROM ".$wpdb->prefix. "posts WHERE post_type = 'stored-carts'";
	$result = $wpdb->get_results($sql);

	foreach( $result as $cart ) {
		$delete_meta_sql = "DELETE FROM ".$wpdb->prefix. "postmeta WHERE post_id = '" . $cart->ID . "'";
		$wpdb->query( $delete_meta_sql );
		$delete_sql = "DELETE FROM ".$wpdb->prefix. "posts WHERE ID = '" . $cart->ID . "'";
		$wpdb->query( $delete_sql );
	}
}

cxecrt__uninstall();

?>