<?php
/**
 * Class for E-mail handling.
 *
 * @author   Actuality Extensions
 * @package  WC_CRM
 * @since    1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_CRM_Screen_Activity {

	public static function output()
	{
		add_action( 'wc_crm_restrict_list_logs', 'WC_CRM_Screen_Activity_Filters::restrict_list_logs' );
		?>
		<div class="wrap wc-crm-page-logs-table"  id="wc-crm-page">
			<h2><?php _e( 'Activity', 'wc_crm' ); ?></h2>
			<?php wc_crm_print_notices(); ?>
			<form method="post">
				<input type="hidden" name="page" value="<?php echo WC_CRM_TOKEN; ?>">
				<?php
					$customers_table = WC_CRM()->tables['activity'];
					$customers_table->views();					
					$customers_table->prepare_items();
					$customers_table->display();
				?>
			</form>
		</div>
		<?php
	}

	public static function display_activity_data()
	{
		$the_activity = new WC_CRM_Activity($_REQUEST['log_id']);
		$date = date("d F Y", strtotime($the_activity->created));
        $time = date("H:i:s", strtotime($the_activity->created));
		?>
		<div class="wrap wc-crm-page-logs-view"  id="wc-crm-page">
			<?php if ( $the_activity->activity_type == 'email' ) {
				$file = 'views/html-activity-email.php';
				$title = __( 'Email Details', 'wc_crm' );
			}else if($the_activity->activity_type == 'phone call'){
				$file = 'views/html-activity-phone-call.php';
				$title = __( 'Call Details', 'wc_crm' );
			}
			?>
			<h2><?php echo $title; ?></h2>
			<?php wc_crm_print_notices(); ?>
			<form method="post">
				<input type="hidden" name="page" value="<?php echo WC_CRM_TOKEN; ?>">
				<?php
				include_once $file;
				?>
			</form>
		</div>
		<?php
	}


	/**
	 * Displays form with e-mail editor.
	 */
	public static function display_email_form() {

		$ids        = array();
		$recipients = array();


		if ( isset( $_REQUEST['c_id'] ) ) {
			if( !is_array( $_REQUEST['c_id'] ))
				$ids[] = $_REQUEST['c_id'];
			else
				$ids = $_REQUEST['c_id'];
		}
		if( !empty($ids) ){
			$ids = implode(', ', $ids);
			global $wpdb;
			$result = $wpdb->get_results("SELECT email FROM {$wpdb->prefix}wc_crm_customer_list WHERE c_id IN({$ids})");
			if($result){
				foreach ($result as $customer) {
					$recipients[] = $customer->email;
				}
			}
		}
		$mailer = WC()->mailer();
		include_once 'views/html-email-form.php';
	}
	/**
	 * Displays phone call form.
	 */
	public static function display_phone_call_form() {
		$phone       = '';
		$user_name   = '';
		$user_email  = '';
		$customer_id = 0;
		if ( isset( $_REQUEST['c_id'] ) ) {
			$the_customer = new WC_CRM_Customer($_REQUEST['c_id']);
			$phone       = $the_customer->phone;
			$$user_email = $the_customer->user_email;
			$user_name   = trim($the_customer->first_name.' '.$the_customer->last_name);
			$customer_id = $the_customer->customer_id;
		}
		include_once 'views/html-phone-call.php';
	}

	/**
	 * Processes the form data.
	 */
	public static function process_phone_call_form() {
		global $wpdb;
		
		wc_crm_clear_notices();

		extract($_POST);

		$s = $call_date.' '.$call_time_h.':'.$call_time_m.':'.$call_time_s;
		$unix_timestamp  = strtotime($s);
		$mysql_timestamp = date('Y-m-d H:i:s',$unix_timestamp);
		$created_gmt     = get_gmt_from_date( $mysql_timestamp );

		$data = array(
		    'subject'               => $subject_of_call,
			'message'               => wpautop($call_results),
			'phone'                 => $user_phone,
			'activity_type'         => 'phone call',
			'user_id'               => get_current_user_id(),
			'created'               => $mysql_timestamp,
			'created_gmt'           => $created_gmt,
			'call_type'             => $call_type,
			'call_purpose'          => $call_purpose,
			'related_to'            => $related_to,
			'number_order_product'  => $number_order_product,
			'call_duration'         => $call_duration_h.':'.$call_duration_m.':'.$call_duration_s
		);
		$table_name = $wpdb->prefix . "wc_crm_log";
		$wpdb->insert( $table_name, $data );
		$log_id = $wpdb->insert_id;

		$c_id    = $customer_id;
		$user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}wc_crm_customer_list WHERE c_id = {$c_id} LIMIT 1");			
		if($user_id){
			if($user_id > 0){
				add_user_meta($user_id, 'wc_crm_log_id', $log_id);
			}else{
				wc_crm_add_cmeta($c_id, 'wc_crm_log_id', $log_id);
			}
		}
		wc_crm_add_notice( __("Phone call saved.", 'wc_crm'), 'success' );
	}

	/**
	 * Processes the form data.
	 */
	public static function process_email_form() {
		global $wpdb;
		
		wc_crm_clear_notices();
		$recipients = explode( ',', $_POST['recipients'] );

		$text = wpautop($_POST['emaileditor']);
		$subject = $_POST['subject'];
		if( !empty( $_POST['from_email'] ) && filter_var($_POST['from_email'], FILTER_VALIDATE_EMAIL) ) {
			add_filter( 'wp_mail_from', __CLASS__.'::change_from_email', 9999 );
		}
		if( !empty( $_POST['from_name'] ) ) {
			add_filter( 'wp_mail_from_name', __CLASS__ . '::change_from_name', 9999 );
		}
		$mailer = WC()->mailer();
		ob_start();
		wc_crm_custom_woocommerce_get_template( 'emails/customer-send-email.php', array(
			'email_heading' => $subject,
			'email_message' => $text
		) );
		$message = ob_get_clean();
		$order_ID = '';
		if(isset($_GET['order_id']) && $_GET['order_id'] != ''){
			$order_ID = $_GET['order_id'];
		}
		//save log
		$emails_     = $_POST['recipients'];
		$type        = "email";
		$table_name  = $wpdb->prefix . "wc_crm_log";
		$created     = current_time('mysql');
		$created_gmt = get_gmt_from_date( $created );

		$insert = $wpdb->prepare( "(%s, %s, %s, %s, %s, %d)", $created, $created_gmt, $subject, $text, $type, get_current_user_id() );

		$wpdb->query("INSERT INTO $table_name (created, created_gmt, subject, message, activity_type, user_id) VALUES " . $insert);
		$log_id = $wpdb->insert_id;
		
		foreach ( $recipients as $r ) {
			
			$mailer->send( $r, stripslashes($subject), stripslashes($message) );
			$result = $wpdb->get_results("SELECT c_id, user_id FROM {$wpdb->prefix}wc_crm_customer_list WHERE email = '{$r}' LIMIT 1");			
			if($result){
				$customer = $result[0];
				if($customer->user_id > 0){
					add_user_meta($customer->user_id, 'wc_crm_log_id', $log_id);
				}else{
					wc_crm_add_cmeta($customer->c_id, 'wc_crm_log_id', $log_id);
				}
			}
		}
		wc_crm_add_notice( __("Email sent.", 'wc_crm'), 'success' );

	}

	public static function change_from_email( $email ) {
		return $_POST['from_email'];
	}
	public static function change_from_name( $name ) {
		return $_POST['from_name'];
	}


	public static function get_activity()
	{
		global $wpdb;
        $filter  = '';

        if( !empty( $_REQUEST['activity_types'] ) ){
          if($filter == '') $filter .= 'WHERE ';
          else  $filter .= ' AND ';
            $filter .= 'activity_type = "'.$_REQUEST['activity_types'].'"';
        }
        if( isset( $_REQUEST['log_status'] ) && $_REQUEST['log_status'] == 'trash' ){
          if($filter == '') $filter .= 'WHERE ';
          else  $filter .= ' AND ';
            $filter .= 'log_status = \'trash\' ';
        }else{
          if($filter == '') $filter .= 'WHERE ';
          else  $filter .= ' AND ';
            $filter .= 'log_status <> \'trash\' ';
        }
        if( !empty( $_REQUEST['log_users'] ) ){
          if($filter == '') $filter .= 'WHERE ';
          else  $filter .= ' AND ';
            $filter .= 'user_id = '.$_REQUEST['log_users'];
        }
        $filter_m = '';
        if( !empty( $_REQUEST['created_date'] ) ){
          $month = substr($_REQUEST['created_date'], -2);
          if( $month{0} == 0 ) $month = substr($month, -1);
          $year = substr($_REQUEST['created_date'], 0, 4);
          if($filter == '') $filter_m .= 'WHERE ';
          else  $filter_m .= ' AND ';
            $filter_m .= 'YEAR( created ) = ' . $year . ' AND MONTH( created ) = ' . $month;
        }
        $orderby = ( ! empty( $_GET['orderby'] )  ) ? 'ORDER BY '.$_GET['orderby'] : 'ORDER BY created';
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'ASC';

        $table_name = $wpdb->prefix . "wc_crm_log";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter $filter_m $orderby $order");
        $data = array();

        foreach ($db_data as $value) {
            $data[] = get_object_vars($value);
        }

        $logs_data = $data;

        
        return $data;
	}

}
