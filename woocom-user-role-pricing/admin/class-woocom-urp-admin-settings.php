<?php
/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 7/22/2015
 * Time: 8:20 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Woocom_URP_Admin_Settings' ) ) :

class Woocom_URP_Admin_Settings {

	private static $errors   = array();
	private static $messages = array();

	/**
	 * Save the settings
	 */
	public static function save() {
		global $current_tab, $current_section;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocom-urp-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'woocom-urp' ) );
		}

		switch($current_tab) {
			case 'general':
				self::save_general_settings();
				break;
			case 'role':
				self::save_role_settings();
				break;
			case 'prices':
				self::save_prices_settings();
				break;
			case 'license':
				self::process_license_form();
				break;
		}

		self::add_message( __( 'Your settings have been saved.', 'woocom-urp' ) );


	}

	/**
	 * Add a message
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors
	 * @return string
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main woocommerce settings page in admin.
	 */
	public static function output() {

		global $current_section, $current_tab;

		/*
		wp_enqueue_script( 'woocom_urp_admin', 'js/woocom-urp-admin.js', array( 'jquery' ), 1.0, true );

		wp_localize_script( 'woocom_urp_admin', 'woocom_urp_admin_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'woocommerce' )
		) );
		*/

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			self::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['urp_error'] ) ) {
			self::add_error( stripslashes( $_GET['urp_error'] ) );
		}

		if ( ! empty( $_GET['urp_message'] ) ) {
			self::add_message( stripslashes( $_GET['urp_message'] ) );
		}

		self::show_messages();

		// Get tabs for the settings page
		$tabs = array(
			'general' => __('General', 'woocom-urp'),
			'role' => __('Role Settings', 'woocom-urp'),
			'prices' => __('Prices', 'woocom-urp'),
			'license' => __('Plugin License', 'woocom-urp')
		);

		$options = get_option('woocom_urp_options');

		include 'partials/html-admin-settings.php';
	}

	private static function save_general_settings() {
		$options = get_option('woocom_urp_options');

		$checkboxes = array('uninstall_delete_data', 'use_sale_prices', 'update_mini_cart');

		foreach($checkboxes as $field) {
			$posted = isset($_POST[$field]) ? $_POST[$field] : false;
			if($posted && 'yes' == $posted) {
				$options[$field] = 'yes';
			} else {
				$options[$field] = 'no';
			}
		}

		// no price action
		if(isset($_POST['no_price_action']) && in_array($_POST['no_price_action'], array('revert', 'hide', 'message'))) {
			$options['no_price_action'] = sanitize_key($_POST['no_price_action']);
		} else {
			$options['no_price_action'] = 'revert'; // default
		}

		$text_fields = array('no_price_text', 'billing_account_label');
		foreach($text_fields as $field) {
			$posted = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
			$options[$field] = $posted;
		}

		update_option('woocom_urp_options', $options);
		self::add_message( __('Options updated.', 'woocom-urp') );

	}

	private static function save_role_settings() {
		global $current_section;

		if('add_role' == $current_section) {

			global $wp_roles;

			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			// Get role to copy
			if(empty($_POST['copy_role'])) {
				self::add_error(__('Please select a role to copy!', 'woocom-urp'));
				return;
			}

			$copy_role = $wp_roles->get_role( sanitize_title( $_POST['copy_role'] ) );
			if(!is_object($copy_role)) {
				self::add_error(__('Invalid role to copy', 'woocom-urp'));
				return;
			}

			// sanitize and format name and slug of new role
			$name = sanitize_text_field($_POST['role_name']);
			$temp = str_replace(' ', '_', $name); // replace spaces with underscores
			$slug = sanitize_key($temp);

			// check if the role already exists
			$new_role = $wp_roles->get_role($slug);
			if(null !== $new_role) {
				self::add_error(__('That role already exists.', 'woocom-urp'));
				return;
			}

			// Made it this far, OK to add the role
			$wp_roles->add_role($slug, $name, $copy_role->capabilities);

			self::add_message( sprintf(__('Role %s created.', 'woocom-urp'), $name) );

		} elseif ('remove_role' == $current_section) {

			// Make sure a role is selected
			if(empty($_POST['remove_role'])) {
				self::add_error(__('Please select a role to remove!', 'woocom-urp'));
				return;
			}

			// Make sure they checked the box to confirm role removal
			if(empty($_POST['confirm_remove']) || 'yes' !== $_POST['confirm_remove'] ) {
				self::add_error(__('Please check the box to confirm you wish to remove the role!', 'woocom-urp'));
				return;
			}

			// remove the role
			$role = sanitize_key($_POST['remove_role']);
			remove_role($role);
			self::add_message( __('Role removed.', 'woocom-urp') );

		} elseif ( 'settings' == $current_section || '' == $current_section ) {

			$options = get_option('woocom_urp_options');
			$base_prices = $_POST['base_price'];
			$multipliers = $_POST['multiplier'];

			foreach($base_prices as $key => $value) {
				$old_value = empty($options['base_price'][$key]) ? '' : sanitize_key($options['base_price'][$key]);
				if(!empty($value) && $value !== $old_value) {
					$options['base_price'][$key] = sanitize_key($value);
				} elseif(empty($value)) {
					unset($options['base_price'][$key]);
				}
			}

			foreach($multipliers as $key => $value) {
				// make sure only numeric values
				if(!is_numeric($value) && !empty($value)) {
					self::add_error(__('Numbers only for multipliers!', 'woocom-urp'));
					return;
				}
				$old_value = empty($options['price_multiplier'][$key]) ? '' : floatval($options['price_multiplier'][$key]);
				if( !empty($value) && floatval($value) !== $old_value ) {
					$options['price_multiplier'][$key] = floatval($value);
				} elseif(empty($value)) {
					unset($options['price_multiplier'][$key]);
				}
			}

			$roles = get_editable_roles();

			$selectors = array('tax_exempt', 'disable_shipping');
			foreach($selectors as $field) {
				$posted = isset($_POST[$field]) ? $_POST[$field] : array();
				foreach($roles as $role => $data) {
					if (isset($posted[$role]) && 'yes' == $posted[$role]) {
						$options[$field][$role] = 'yes';
					} elseif (isset($posted[$role]) && 'no' == $posted[$role]) {
						$options[$field][$role] = 'no';
					} elseif (isset($options[$field][$role])) {
						// unset == 'default'
						unset($options[$field][$role]);
					}
				}
			}

			$checkboxes = array('billing_account');
			if( function_exists('woocom_wholesale_is_wholesale_customer') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
				$checkboxes[] = 'is_wholesale';
			}
			foreach($checkboxes as $field) {
				$posted = isset($_POST[$field]) ? $_POST[$field] : array();
				foreach($roles as $role => $data) {
					if(isset($posted[$role]) && 'yes' == $posted[$role]) {
						$options[$field][$role] = 'yes';
					} elseif (isset($options[$field][$role])) {
						unset($options[$field][$role]);
					}
				}
			}

			update_option('woocom_urp_options', $options);
			self::add_message( __('Options updated.', 'woocom-urp') );

		}

	}

	private static function save_prices_settings() {

		$options = get_option('woocom_urp_options');
		$deleted = false;
		$added = false;

		if(isset($_POST['delete_price_field'])) {
			if(!empty($_POST['delete_price'])) {
				foreach($_POST['delete_price'] as $key => $value) {
					if('delete' === $value && isset($options['prices'][$key])) {
						// Cleanup database - removes postmeta fields and no price products option
						$name = $options['prices'][$key];
						if(Woocom_URP_Product_Cache::cleanup($key)) {
							$user_reset = self::reset_user_base_price($key);
							$results = self::reset_role_base_price($key, $options);
							if(false !== $results && is_array($results)) {
								$options = $results;
							}
							unset($options['prices'][$key]);

							self::add_message( sprintf( __('%s deleted along with all associated product prices.', 'woocom-urp'), esc_html( stripslashes($name) ) ) );
							if($user_reset) {
								self::add_message( __('All users with that base price have been reset to "Default" base price.', 'woocom-urp') );
							}
							if(false !== $results) {
								self::add_message( __('All roles with that base price have been reset to "Default" base price.', 'woocom-urp') );
							}
							$deleted = true;
						} else {
							// cleanup error
							$deleted = false;
							self::add_error(sprintf( __('ERROR removing postmeta for %s. Please check your database and try again.', 'woocom-urp'), esc_html( stripslashes($name) ) ));
						}

					}
				}
			}
		}

		if(isset($_POST['add_price_field'])) {

			if(empty($_POST['new_price_name'])) {
				self::add_error(__('Please enter a name for the price field!', 'woocom-urp'));
				return;
			}

			$name = sanitize_text_field($_POST['new_price_name']);
			$key = '_' . sanitize_key(str_replace(' ', '_', $name));

			// Make sure it doesn't already exist
			$current_keys = Woocom_URP_Product_Cache::get_price_keys(); // gets all keys except _wholesale_price
			$current_keys[] = '_sale_price'; // reserved for WooCommerce
			$current_keys[] = '_price'; // reserved for WooCommerce
			if(class_exists('WOO_WholesaleOrdering')) {
				// reserved for Wholesale Ordering plugin
				$current_keys[] = '_wholesale_price';
			}
			if(in_array($key, $current_keys)) {
				self::add_error(__('That price key is in use or is reserved! Please try a different name.', 'woocom-urp'));
				return;
			}
			if(!empty($name) && !empty($key)) {
				$added = true;
				$options['prices'][$key] = $name;
				if(isset($_POST['price_incl_tax']) && 'yes' == $_POST['price_incl_tax']) {
					$options['prices_incl_tax'][$key] = 'yes';
				} else {
					$options['prices_incl_tax'][$key] = 'no';
				}
				// Initialize no_price product cache for new key
				Woocom_URP_Product_Cache::update_product_cache($key);
				self::add_message( sprintf( __('%s Added', 'woocom-urp'), esc_html( stripslashes($name) ) ) );
			}
		}

		if($deleted || $added) {
			update_option('woocom_urp_options', $options);
		}
	}

	private static function process_license_form() {

		// retrieve the license from the posted data
		$license = trim( $_POST['woocom_urp_license_key'] );
		$old = get_option( 'woocom_urp_license_key' );
		if( $old && $old != $license ) {
			delete_option( 'woocom_urp_license_key' ); // new license has been entered, so must reactivate
		}

		$activated = isset($_POST['woocom_urp_activate_mode']) && 'activated' == $_POST['woocom_urp_activate_mode'];
		$deactivated = isset($_POST['woocom_urp_deactivate_mode']) && 'deactivated' == $_POST['woocom_urp_deactivate_mode'];

		// Check for saving a new license key for first time
		if(isset($_POST['woocom_urp_license_save_mode']) && 'submitted' == $_POST['woocom_urp_license_save_mode']) {
			$activated = true; // try to activate after saving a new license code
		}

		if($activated) {
			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $license,
				'item_name' => urlencode( SS_PLUGINS_WOOCOM_URP ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( SS_PLUGINS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				self::add_error( __( 'License Site Communication Error! Please try again in a few minutes.', 'woocom-urp' ) );
			} else {
				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				// $license_data->license will be either "valid" or "invalid"
				update_option( 'woocom_urp_license_status', $license_data->license );
				// update the license key
				update_option( 'woocom_urp_license_key', $license );
				if ( 'valid' == $license_data->license ) {
					self::add_message( __('License Updated!', 'woocom-urp') );
				} else {
					self::add_error( __( 'Not A Valid License!', 'woocom-urp' ) );
				}

			}
		} elseif($deactivated) {
			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $license,
				'item_name' => urlencode( SS_PLUGINS_WOOCOM_URP ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( SS_PLUGINS_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				self::add_error( __( 'License Site Communication Error! Please try again in a few minutes.', 'woocom-urp' ) );
			} else {
				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				// $license_data->license will be either "deactivated" or "failed"
				if( $license_data->license == 'deactivated' ) {
					delete_option( 'woocom_urp_license_status' );
					delete_option( 'woocom_urp_license_key' );
					self::add_message( __('License Deactivated!', 'woocom-urp') );
				} elseif ( $license_data->license == 'failed' ) {
					self::add_error( __( 'Deactivation Failed!  Please try again in a few minutes.', 'woocom-urp' ) );
				}
			}
		}
	}

	private static function reset_role_base_price($price_key = false, $options) {
		if(!$price_key) return false;
		//reset any roles that used the price
		$roles = get_editable_roles();
		foreach($roles as $role => $data) {
			if( isset($options['base_price'][$role]) && $price_key == $options['base_price'][$role] ) {
				unset($options['base_price'][$role]);

			}
		}
		return $options;
	}

	private static function reset_user_base_price($price_key = false) {
		// When deleting a price, this function will reset user base price meta for any user that was using that price
		if(!$price_key) return false;

		// delete any user meta base_price fields set to that price
		global $wpdb;
		$table = $wpdb->usermeta;
		$where = array( 'meta_key' => 'urp_user_base_price', 'meta_value' => $price_key );
		$where_format = array('%s', '%s');
		$result = $wpdb->delete( $table, $where, $where_format );

		return (false !== $result);

	}

}

endif;