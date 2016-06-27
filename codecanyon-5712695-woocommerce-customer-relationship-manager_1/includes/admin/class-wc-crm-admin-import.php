<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_CRM_Admin_Import{

  public  static function init()
  {
    add_action( 'admin_menu', array( __CLASS__, 'add_crm_import_page'), 9999 );

    if( isset($_GET['page']) && ($_GET['page'] == 'wc_new_customer' || $_GET['page'] == 'wc_crm_import' ) )
        add_action( 'init', array( __CLASS__, 'action_customer_created' ) );
  }

  public static function add_crm_import_page()
  {
  	add_submenu_page( 
  		WC_CRM_TOKEN, 
  		__( "Import", 'wc_crm' ), 
  		__( "Import", 'wc_crm'), 
  		'manage_woocommerce', 
  		'wc_crm_import', 
  		array( __CLASS__, 'render_import_page')
  	);
  }

  public static function render_import_page()
  {
    $importer = new WC_CRM_Importer();
    $importer->dispatch();
  }

  public static function action_customer_created()
  {
    add_filter('woocommerce_email_actions', 'wc_crm_automatic_emails_new_customer', 150, 1);
  }
}
WC_CRM_Admin_Import::init();