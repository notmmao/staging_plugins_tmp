<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://stephensherrardplugins.com
 * @since             1.0.0
 * @package           Woocom_URP
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce User Role Pricing
 * Plugin URI:        http://stephensherrardplugins.com
 * Description:       Create custom price fields, and assign a price and multiplier to use on a role or individual user basis. 
 * Version:           1.1.10
 * Author:            Stephen Sherrard
 * Author URI:        http://stephensherrardplugins.om
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocom-urp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
if ( !defined('SS_PLUGINS_URL') )
	define( 'SS_PLUGINS_URL', 'http://stephensherrardplugins.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
if ( !defined('SS_PLUGINS_WOOCOM_URP') )
	define( 'SS_PLUGINS_WOOCOM_URP', 'Woocommerce User Role Pricing' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

if ( !defined('WOOCOM_URP_PLUGIN_BASENAME') )
	define( 'WOOCOM_URP_PLUGIN_BASENAME', plugin_basename(__FILE__) ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function woocom_urp_updater() {
	// retrieve our license key from the DB
	$license_key = trim( get_option( 'woocom_urp_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( SS_PLUGINS_URL, __FILE__, array(
			'version' 	=> '1.1.10', 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => SS_PLUGINS_WOOCOM_URP, 	// name of this plugin
			'author' 	=> 'Stephen Sherrard'  // author of this plugin
		)
	);
}
add_action( 'admin_init', 'woocom_urp_updater' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocom-urp-activator.php
 */
function activate_woocom_urp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocom-urp-activator.php';
	Woocom_URP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocom-urp-deactivator.php
 */
function deactivate_woocom_urp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocom-urp-deactivator.php';
	Woocom_URP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocom_urp' );
register_deactivation_hook( __FILE__, 'deactivate_woocom_urp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocom-urp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocom_urp() {

	$woocom_urp = new Woocom_URP();
	$woocom_urp->run();

}
run_woocom_urp();
