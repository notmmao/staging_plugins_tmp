<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Schedule Mailings 20150213
require_once('ic_commerce_premium_golden_schedule_mailing.php');//Created 20150213
$IC_Commerce_Premium_Golden_Schedule_Mailing = new IC_Commerce_Premium_Golden_Schedule_Mailing(__FILE__,'icwoocommercepremiumgold');

if(!function_exists('ic_commerce_premium_golden_page_init')){
	function ic_commerce_premium_golden_page_init($constants = array(), $admin_page = ""){
		
			if(	
				$admin_page == "icwoocommercepremiumgold_details_page"
			|| 	$admin_page == "icwoocommercepremiumgold_variation_page"
			|| 	$admin_page == "icwoocommercepremiumgold_report_page"
			
			){
				
				global $IC_Commerce_Premium_Golden_Advance_Variation;
				$path = WP_PLUGIN_DIR."/ic-woocommerce-advance-sales-report-premium-golden/includes/";
				
				if(file_exists($path.'ic_commerce_premium_golden_customization.php')){
					require_once($path.'ic_commerce_premium_golden_customization.php');
					$IC_Commerce_Premium_Golden_Customization = new IC_Commerce_Premium_Golden_Customization($constants, $admin_page);
				}
				
			}
	}
	
	add_action("ic_commerce_premium_golden_page_init","ic_commerce_premium_golden_page_init", 10, 2);
	
}