<?php 
/**
Plugin Name: WooCommerce Advance Sales Report Premium Gold 
Plugin URI: http://plugins.infosofttech.com/
Author: Infosoft Consultants
Description: The latest release of our WooCommerce Report Plug-in has all features of Gold version plus new features like Projected Vs Actual Sales, Comprehensive Tax based Reporting, Improvised Dashboard, Filters by Variation Attributes, Sales summary by Map View, Graphs and much more.
Version: 1.4
Author URI: http://www.infosofttech.com

Copyright: © 2015 - www.infosofttech.com - All Rights Reserved

Tested WooCommerce Version: 2.4.12
Tested Wordpress Version: 4.4

Last Update Date:December 21, 2015 
**/   

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('init_icwoocommercepremiumgold')){
	
	function init_icwoocommercepremiumgold() {
		global $ic_woocommerce_advance_sales_report_premium_golden, $ic_woocommerce_advance_sales_report_premium_golden_constant;
		
		$constants = array(
				"version" 				=> "1.4"
				,"product_id" 			=> "1583"
				,"plugin_key" 			=> "icwoocommercepremiumgold"
				,"plugin_api_url" 		=> "http://plugins.infosofttech.com/api-woo-prem-golden.php"
				,"plugin_main_class" 	=> "IC_Woocommerce_Advance_Sales_Report_Premium_Golden"
				,"plugin_instance" 		=> "ic_woocommerce_advance_sales_report_premium_golden"
				,"plugin_dir" 			=> 'ic-woocommerce-advance-sales-report-premium-golden'
				,"plugin_file" 			=> __FILE__
				,"plugin_role" 			=> apply_filters('ic_commerce_premium_gold_plugin_role','manage_woocommerce')//'read'
				,"per_page_default"		=> 5
				,"plugin_parent_active" => false
				,"color_code" 			=> '#77aedb'
				,"plugin_parent" 		=> array(
					"plugin_name"		=>"WooCommerce"
					,"plugin_slug"		=>"woocommerce/woocommerce.php"
					,"plugin_file_name"	=>"woocommerce.php"
					,"plugin_folder"	=>"woocommerce"
					,"order_detail_url"	=>"post.php?&action=edit&post="
				)			
		);
		
		//echo 'pm';
		require_once('includes/ic_commerce_premium_golden_fuctions.php');
		
		load_plugin_textdomain('icwoocommerce_textdomains', WP_PLUGIN_DIR.'/'.$constants['plugin_dir'].'/languages',$constants['plugin_dir'].'/languages');
		$constants['plugin_name'] 		= __('WooCommerce Advance Sales Report Premium Gold', 	'icwoocommerce_textdomains');
		$constants['plugin_menu_name'] 	= __('WooCommerce Report Premium',						'icwoocommerce_textdomains');
		$constants['admin_page'] 		= isset($_REQUEST['page']) ? $_REQUEST['page'] : "";
		$constants['is_admin'] 			= is_admin();
		
		$constants = apply_filters('ic_commerce_premium_golden_init_constants', $constants, $constants['plugin_key']);
		do_action('ic_commerce_premium_golden_textdomain_loaded',$constants, $constants['plugin_key']);
		
		$ic_woocommerce_advance_sales_report_premium_golden_constant = $constants;
		
		require_once('includes/ic_commerce_premium_golden_add_actions.php');
		
		
		
		if ($constants['is_admin']) {
				
				require_once('includes/ic_commerce_premium_golden_init.php');
								
				if(!class_exists('IC_Woocommerce_Advance_Sales_Report_Premium_Golden')){class IC_Woocommerce_Advance_Sales_Report_Premium_Golden extends IC_Commerce_Premium_Golden_Init{}}
				
				$ic_woocommerce_advance_sales_report_premium_golden 			= new IC_Woocommerce_Advance_Sales_Report_Premium_Golden( __FILE__, $ic_woocommerce_advance_sales_report_premium_golden_constant);
		}
	}
}

add_action('init','init_icwoocommercepremiumgold', 100);



