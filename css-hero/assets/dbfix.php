<?php

if (get_option('wpcss_wefixed_autoload')!=1):
 
	global $wpdb;
	$update_table=$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->options." SET autoload='no' WHERE (option_name LIKE 'wpcss_%%' and autoload='yes' )  ",NULL));
    wpcss_update_option('wpcss_wefixed_autoload',1);
	 
endif;