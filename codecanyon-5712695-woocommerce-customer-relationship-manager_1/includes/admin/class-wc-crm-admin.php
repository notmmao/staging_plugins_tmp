<?php
/**
 * WC_CRM Admin.
 *
 * @class       WC_CRM_Admin
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_CRM/Admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_CRM_Admin class.
 */
class WC_CRM_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'admin_footer', 'wc_print_js', 25 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( 'class-wc-crm-admin-post-types.php' );
		include_once( 'class-wc-crm-admin-post-actions.php' );
		include_once( 'class-wc-crm-admin-import.php' );
		include_once( 'class-wc-crm-admin-menus.php' );
		include_once( 'class-wc-crm-admin-notices.php' );
		include_once( 'class-wc-crm-admin-orders-page.php' );
		/*********** ACF ************/
        if (class_exists('acf_controller_post')){
			include_once( 'class-wc-crm-admin-acf.php' );
        }
        /***********************/

		// Setup/welcome
		if ( ! empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case WC_CRM_TOKEN.'-setup' :
					include_once( 'class-wc-crm-admin-setup-wizard.php' );
					break;
				case WC_CRM_TOKEN.'-about' :
					include_once( 'class-wc-crm-admin-welcome.php' );
					break;
			}
		}

		// Importers
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			include_once( 'class-wc-admin-importers.php' );
		}
	}

	/**
	 * Include admin files conditionally
	 */
	public function conditional_includes() {
		$screen = get_current_screen();

		switch ( $screen->id ) {
			case 'dashboard' :
				include( 'class-wc-admin-dashboard.php' );
			break;
			case 'options-permalink' :
				include( 'class-wc-admin-permalink-settings.php' );
			break;
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				include( 'class-wc-admin-profile.php' );
			break;
		}
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * Transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		if ( ! get_transient( '_wc_crm_activation_redirect' ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		delete_transient( '_wc_crm_activation_redirect' );

		if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( WC_CRM_TOKEN.'-setup', WC_CRM_TOKEN.'-about' ) ) ) {
			return;
		}

		if (  defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
			return;
		}

		// If the user needs to install, send them to the setup wizard
		if ( WC_CRM_Admin_Notices::has_notice( 'crm_install' ) ) {
			wp_safe_redirect( admin_url( 'index.php?page='.WC_CRM_TOKEN.'-setup' ) );
			exit;

		// Otherwise, the welcome page
		} else {
			wp_safe_redirect( admin_url( 'index.php?page='.WC_CRM_TOKEN.'-about' ) );
			exit;
		}
	}

}

return new WC_CRM_Admin();
