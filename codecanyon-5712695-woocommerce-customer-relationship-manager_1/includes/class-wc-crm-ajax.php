<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 *
 * AJAX Event Handler
 *
 * @class     WC_CRM_AJAX
 * @version   2.2.0
 * @package   WooCommerce_Customer_Relationship_Manager/Classes
 * @category  Class
 * @author    Actuality Extensions
 */

class WC_CRM_AJAX {

    /**
     * Hook into ajax events
     */
    public function __construct() {

        // woocommerce_EVENT => nopriv
        $ajax_events = array(
          'reload_customer'       => false,
          'reload_guest'          => false,
          'get_guest_details'     => false,
          'add_customer_note'     => false,
          'delete_customer_note'  => false,
          'json_search_customers'  => false,
          'json_search_variations' => false,
          'add_account_note'       => false,
        ); 

        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_wc_crm_' . $ajax_event, array($this, $ajax_event));

            if ($nopriv)
                add_action('wp_ajax_nopriv_wc_crm_' . $ajax_event, array($this, $ajax_event));
        }
    }

    public function reload_customer()
    {
      // No timeout limit
      $this->increase_timeout();
    
      // Don't break the JSON result
      error_reporting(0);
      header('Content-type: application/json');
      $id = (int) $_REQUEST['id'];
      try {

        $result = wc_crm_reload_customer($id);
        die( json_encode( $result ) );

      } catch (Exception $e) {
        $this->die_json_failure_msg($id, '<b><span style="color: #DD3D36;">' . $e->getMessage() . '</span></b>');
      }

      die;
    }

    public function reload_guest()
    {
      // No timeout limit
      $this->increase_timeout();
    
      // Don't break the JSON result
      error_reporting(0);
      header('Content-type: application/json');
      $email = $_REQUEST['email'];
      
      try {
        
        $result = wc_crm_reload_guest($email);
        die( json_encode( $result ) );

      } catch (Exception $e) {
        $this->die_json_failure_msg($id, '<b><span style="color: #DD3D36;">' . $e->getMessage() . '</span></b>');
      }

      die;
    }

    /**
     * Helper to make a JSON failure message
     *
     * @param integer $id
     * @param string #message
     * @access public
     * @since 1.8
     */
    function die_json_failure_msg($id, $message) {
      die(json_encode(array('error' => sprintf(__('(ID %s)<br />%s', 'wc_crm'), $id, $message))));
    }

    /**
     * WC REST API can timeout on some servers
     * This is an attempt t o increase the timeout limit
     * TODO: is there a better way?
     */
    public function increase_timeout() { 
      $timeout = 8000;
      if( !ini_get( 'safe_mode' ) )
        @set_time_limit( $timeout );

      @ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
      @ini_set( 'max_execution_time', (int)$timeout );
    }

    /**
     * Output headers for JSON requests
     */
    private function json_headers() {
        header('Content-Type: application/json; charset=utf-8');
    }


    /**
   * Get customer details via ajax
   */
  public static function get_guest_details() {
    ob_start();

    check_ajax_referer( 'get-customer-details', 'security' );

    $order_id     = (int) trim(stripslashes($_POST['order_id']));
    $type_to_load = esc_attr(trim(stripslashes($_POST['type_to_load'])));

    $customer_data = array(
      $type_to_load . '_first_name' => get_user_meta( $order_id, $type_to_load . '_first_name', true ),
      $type_to_load . '_last_name'  => get_user_meta( $order_id, $type_to_load . '_last_name', true ),
      $type_to_load . '_company'    => get_user_meta( $order_id, $type_to_load . '_company', true ),
      $type_to_load . '_address_1'  => get_user_meta( $order_id, $type_to_load . '_address_1', true ),
      $type_to_load . '_address_2'  => get_user_meta( $order_id, $type_to_load . '_address_2', true ),
      $type_to_load . '_city'       => get_user_meta( $order_id, $type_to_load . '_city', true ),
      $type_to_load . '_postcode'   => get_user_meta( $order_id, $type_to_load . '_postcode', true ),
      $type_to_load . '_country'    => get_user_meta( $order_id, $type_to_load . '_country', true ),
      $type_to_load . '_state'      => get_user_meta( $order_id, $type_to_load . '_state', true ),
      $type_to_load . '_email'      => get_user_meta( $order_id, $type_to_load . '_email', true ),
      $type_to_load . '_phone'      => get_user_meta( $order_id, $type_to_load . '_phone', true ),
    );

    $customer_data = apply_filters( 'wc_crm_found_guest_details', $customer_data, $order_id, $type_to_load );

    wp_send_json( $customer_data );
  }

  /**
  * Add customer note via ajax
  */
  function add_customer_note() {

    $customer_id  = (int) $_POST['customer_id'];
    $note         = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );

    if ( $customer_id > 0 ) {
      $customer = new WC_CRM_Customer($customer_id);
      $comment_id = $customer->add_note( $note );

      echo '<li rel="' . esc_attr( $comment_id ) . '" class="note"><div class="note_content">';
      echo wpautop( wptexturize( $note ) );
      echo '</div><p class="meta"><a href="#" class="delete_customer_note">'.__( 'Delete note', 'woocommerce' ).'</a></p>';
      echo '</li>';
    }

    // Quit out
    die();
  }
  /**
  * Delete customer note via ajax
  */
  function delete_customer_note() {
    $note_id  = (int) $_POST['note_id'];

    if ($note_id>0) :
      wp_delete_comment( $note_id );
    endif;

    // Quit out
    die();
  }

    /**
     * AJAX initiated call to obtain list of filtered products and variations
     */
    public function json_search_variations() {

        WC_AJAX::json_search_products( '', array('product_variation') );
    }

    public function json_search_products() {

      WC_AJAX::json_search_products( '', array('product') );
    }

    public function add_account_note()
    {
      check_ajax_referer( 'add-order-note', 'security' );

      if ( ! current_user_can( 'edit_shop_orders' ) ) {
        die(-1);
      }

      $post_id   = absint( $_POST['post_id'] );
      $note      = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );
      $note_type = $_POST['note_type'];

      if ( $post_id > 0 ) {
        /******************************/
        if ( is_user_logged_in() && current_user_can( 'edit_shop_order', $post_id ) ) {
          $user                 = get_user_by( 'id', get_current_user_id() );
          $comment_author       = $user->display_name;
          $comment_author_email = $user->user_email;
        } else {
          $comment_author       = __( 'WC_CRM', 'wc_crm' );
          $comment_author_email = strtolower( __( 'WC_CRM', 'wc_crm' ) ) . '@';
          $comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) : 'noreply.com';
          $comment_author_email = sanitize_email( $comment_author_email );
        }

        $comment_post_ID        = 0;
        $comment_author_url     = '';
        $comment_content        = $note;
        $comment_agent          = 'WC_CRM';
        $comment_type           = 'account_note';
        $comment_parent         = 0;
        $comment_approved       = 1;
        $commentdata            = apply_filters( 'wc_crm_new_account_note_data', compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), array( 'account_id' => $post_id,  ) );

        $comment_id = wp_insert_comment( $commentdata );

        add_comment_meta( $comment_id, 'comment_post_ID', $post_id );

        do_action( 'wc_crm_new_account_note', $comment_id, $post_id );
        /******************************/

        echo '<li rel="' . esc_attr( $comment_id ) . '" class="note ';
        echo '"><div class="note_content">';
        echo wpautop( wptexturize( $note ) );
        echo '</div><p class="meta"><a href="#" class="delete_note">'.__( 'Delete note', 'wc_crm' ).'</a></p>';
        echo '</li>';
      }

      // Quit out
      die();
    }

}

new WC_CRM_AJAX();
