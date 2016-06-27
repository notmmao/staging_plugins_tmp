<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocom_URP
 * @subpackage Woocom_URP/includes
 * @author     Your Name <email@example.com>
 */
class Woocom_URP_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Set up options default and save
		$defaults = array(
			'base_price' => array(),
			'price_multiplier' => array(),
			'prices' => array('_regular_price' => __('Regular Price', 'woocom_urp') ),
			'prices_incl_tax' => array(),
			'tax_exempt' => array(),
			'disable_shipping' => array(),
			'billing_account' => array(),
			'is_wholesale' => array(),
			'no_price_action' => 'revert',
			'no_price_text' => __('Not available', 'woocom-urp'),
			'billing_account_label' => __('Account #', 'woocom-urp'),
			'uninstall_delete_data' => 'no',
			'use_sale_prices' => 'no',
			'update_mini_cart' => 'no',
		);

		if(class_exists('WOO_WholesaleOrdering')) {
			$defaults['prices']['_wholesale_price'] = __('Wholesale Price', 'woocom_urp');
			$defaults['base_price']['wholesale_customer'] = '_wholesale_price';
			$defaults['price_multiplier']['wholesale_customer'] = 1;
			$defaults['is_wholesale']['wholesale_customer'] = 'yes';
		}

		$options = get_option('woocom_urp_options', $defaults);
		update_option('woocom_urp_options', $options);

	}

}
