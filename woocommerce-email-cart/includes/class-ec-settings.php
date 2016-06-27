<?php
/**
 * WooCommerce Save & Share Cart Settings
 *
 * @author 		cxThemes
 * @category 	Settings
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Cart_Settings' ) ) :

/**
 * WC_Email_Cart_Settings
 */
class WC_Email_Cart_Settings {
	
	protected $id    = '';
	protected $label = '';
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		
		$this->id    = 'email_cart_settings';
		$this->label = __( 'Save & Share Cart Settings', 'email-cart' );
		
		// add the menu itme
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'woocommerce_settings_email_cart_settings', array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_email_cart_settings', array( $this, 'save' ) );
	}
	
	/**
	 * Add a submenu item to the WooCommerce menu
	 */
	public function admin_menu() {

		add_submenu_page(
			'options-general.php',
			__("Save & Share Cart",'email-cart'),
			__("Save & Share Cart",'email-cart'),
			'manage_woocommerce',
			$this->id,
			array( $this, 'admin_page' )
		);
		
	}
	
	public function admin_page() {
		
		// Save settings if data has been posted
		if ( ! empty( $_POST ) )
			$this->save_settings();

		// Add any posted messages
		if ( ! empty( $_GET['wc_error'] ) )
			//self::add_error( stripslashes( $_GET['wc_error'] ) );

		 if ( ! empty( $_GET['wc_message'] ) )
			//self::add_message( stripslashes( $_GET['wc_message'] ) );

		//self::show_messages();
		?>
		<form method="post" id="mainform" action="" enctype="multipart/form-data">
			<div class="cxecrt-wrap cxecrt-wrap-settings woocommerce">
				
				<a class="cxecrt-back" href="<?php echo esc_url( admin_url( 'edit.php?post_type=stored-carts' ) ); ?>"><span class="dashicons dashicons-arrow-left"></span> Back</a>
				
				<h2><?php _e( 'Save & Share Cart', 'email-cart' ); ?><span class="dashicons dashicons-arrow-right"></span><?php _e( 'Settings', 'email-cart' ); ?></h2>
				
				<?php
				$settings = $this->get_settings();
				WC_Admin_Settings::output_fields( $settings );
				?>
				
				<p class="submit">
					<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'email-cart' ); ?>" />
					<?php wp_nonce_field( 'woocommerce-settings' ); ?>
				</p>
				
			</div>
		</form>
		
		<?php
	}
	
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public static function get_settings() {
		global $cxecrt_settings;
		
		if ( isset( $cxecrt_settings ) ) return $cxecrt_settings;
				
		$settings = array(
			
			// --------------------
			
			array(
				'id'   => 'cxecrt_settings',
				'name' => __( 'General Settings', 'email-cart' ),
				'type' => 'title',
				'desc' => '',
			),
			array(
				'id'       => 'cxecrt_show_cart_page_button',
				'name'     => __( "Show Button on Cart Page", 'email-cart' ),
				'desc'     => __( "", 'email-cart' ),
				'desc_tip' => __( "If you don't like the look of our button you can choose not to show it, then create your own button linking to your share cart page like this http://....../cart/#email-cart (#email-cart is essential).", 'email-cart' ),
				'type'     => 'checkbox',
				'default'  => 'yes',
			),
			/*array(
				'id'      => 'cxecrt_show_products_on_index',
				'name'    => __( 'Products on Cart Index Page', 'email-cart' ),
				'desc'    => __( 'Displaying cart products may slow down table listing when showing many carts at once.', 'email-cart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),*/
			array(
				'id'       => 'cxecrt_cart_expiration_active',
				'name'     => __( 'Automatically Delete Old Carts', 'email-cart' ),
				'label'    => '',
				'desc'     => __( '', 'email-cart' ),
				'desc_tip' => __( 'Saving a large number of carts can lead to a large database. Automatically clearing the cart list will keep this under control. Check this on to set number of days.', 'email-cart' ),
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			array(
				'id'      => 'cxecrt_cart_expiration_time',
				'name'    => __( 'Automatically delete carts older than (days)', 'email-cart' ),

				'desc'    => __( 'Any cart that becomes older than the number of days specified will be automatically deleted. In the off chance that a customer attempts to retrieved an old cart a friendly notice will be displayed.', 'email-cart' ),
				'type'    => 'number',
				'default' => '0',
			),
			array(
				'id'   => 'cxecrt_settings',
				'type' => 'sectionend',
			),
			
			// --------------------
			
			array(
				'id'   => 'cxecrt_customer_email_settings_title',
				'name' => __( "Email Settings (for Customers and Guests)", 'email-cart' ),
				'desc' => __( "Customers will be presented with a form where they can personalize the 'To Address', 'From Address', 'From Name', 'Subject' and 'Message' for the email.", 'email-cart' ),
				'type' => 'title',
				
			),
			array(
				'id'          => 'cxecrt_customer_email_subject',
				'name'        => __( 'Subject', 'email-cart' ),
				'desc'        => __( "This is the pre-populated text in the 'Subject' field for Customers emailing their cart. They can then personlize it before sending.", 'email-cart' ),
				'type'        => 'text',
				'default'     => sprintf( __( "Shopping Cart sent to you via %s", 'email-cart' ), get_bloginfo("name") ),
				'placeholder' => sprintf( __( "Shopping Cart sent to you via %s", 'email-cart' ), get_bloginfo("name") ),
				'css'         => 'min-width:500px;',
				'autoload'    => false,
			),
			array(
				'id'          => 'cxecrt_customer_email_message',
				'name'        => __( 'Message', 'email-cart' ),
				'desc'        => __( "This is the pre-populated text in the 'Message' field for Customers emailing their cart. They can then personlize it before sending. The Email message will be styled using the WooCommerce Email Template and the cart added - with it's product pictures and totals.", 'email-cart' ),
				'type'        => 'textarea',
				'default'     => sprintf( __( "Have a look at this Shopping Cart sent by a friend via %s", 'email-cart' ), get_bloginfo("name") ),
				'placeholder' => sprintf( __( "Have a look at this Shopping Cart sent by a friend via %s", 'email-cart' ), get_bloginfo("name") ),
				'css'         => 'height: 100px;',
				'autoload'    => false,
			),
			
			array(
				'id'   => 'cxecrt_customer_email_settings_title',
				'type' => 'sectionend',
			),
			
			// --------------------
			
			array(
				'id'   => 'cxecrt_admin_email_settings_title',
				'name' => __( "Email Settings (for Administrators and Shop Managers)", 'email-cart' ),
				'desc' => __( "Shop Managers will be presented with the same form as above - the difference is the 'From Address' and 'From Name' are pre-populated with the Shop\'s Email and Name, which can aslo be personalized before sending.", 'email-cart' ),
				'type' => 'title',
			),
			array(
				'id'          => 'cxecrt_admin_email_subject',
				'name'        => __( 'Subject', 'email-cart' ),
				'desc'        => __( "This is the pre-populated text in the 'Subject' field for Shop Managers emailing their cart. They can then personlize it before sending.", 'email-cart' ),
				'default'     => sprintf( __( "Shopping Cart sent to you by %s", 'email-cart' ), get_bloginfo("name") ),
				'placeholder' => sprintf( __( "Shopping Cart sent to you by %s", 'email-cart' ), get_bloginfo("name") ),
				'type'        => 'text',
				'css'         => 'min-width:500px;',
				'autoload'    => false,
			),
			array(
				'id'          => 'cxecrt_admin_email_message',
				'name'        => __( 'Message', 'email-cart' ),
				'desc'        => __( "This is the pre-populated text in the 'Message' field for Shop Managers emailing their cart. They can then personlize it before sending. The Email message will be styled using the WooCommerce Email Template and the cart added - with it's product pictures and totals.", 'email-cart'),
				'default'     => sprintf( __( "Have a look at this Shopping Cart from %s", 'email-cart' ), get_bloginfo("name") ),
				'placeholder' => sprintf( __( "Have a look at this Shopping Cart from %s", 'email-cart' ), get_bloginfo("name") ),
				'type'        => 'textarea',
				'css'         => 'height: 100px;',
				'autoload'    => false,
			),
			array(
				'id'   => 'cxecrt_admin_email_settings_title',
				'type' => 'sectionend',
			),
		);

		return $settings;
	}
	
	/**
	 * Save Settings.
	 *
	 * Loops though the woocommerce options array and outputs each field.
	 *
	 * @access public
	 * @return bool
	 */
	public static function save_settings() {
		
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-settings' ) )
			die( __( 'Action failed. Please refresh the page and retry.', 'email-cart' ) );
		
		$settings = self::get_settings();
		
		if ( empty( $_POST ) )
			return false;
		
		// Options to update will be stored here
		$update_options = array();

		// Loop options and get values to save
		foreach ( $settings as $value ) {

			if ( ! isset( $value['id'] ) )
				continue;

			$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

			// Get the option name
			$option_value = null;

			switch ( $type ) {

				// Standard types
				case "checkbox" :

					if ( isset( $_POST[ $value['id'] ] ) ) {
						$option_value = 'yes';
					} else {
						$option_value = 'no';
					}

				break;

				case "textarea" :

					if ( isset( $_POST[$value['id']] ) ) {
						$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
					} else {
						$option_value = '';
					}

				break;

				case "text" :
				case 'email':
				case 'number':
				case "select" :
				case "color" :
				case 'password' :
				case "single_select_page" :
				case "single_select_country" :
				case 'radio' :

					if ( $value['id'] == 'woocommerce_price_thousand_sep' || $value['id'] == 'woocommerce_price_decimal_sep' ) {

						// price separators get a special treatment as they should allow a spaces (don't trim)
						if ( isset( $_POST[ $value['id'] ] )  ) {
							$option_value = wp_kses_post( stripslashes( $_POST[ $value['id'] ] ) );
						} else {
							$option_value = '';
						}

					} elseif ( $value['id'] == 'woocommerce_price_num_decimals' ) {

						// price separators get a special treatment as they should allow a spaces (don't trim)
						if ( isset( $_POST[ $value['id'] ] )  ) {
							$option_value = absint( $_POST[ $value['id'] ] );
						} else {
						   $option_value = 2;
						}

					} elseif ( $value['id'] == 'woocommerce_hold_stock_minutes' ) {

						// Allow > 0 or set to ''
						if ( ! empty( $_POST[ $value['id'] ] )  ) {
							$option_value = absint( $_POST[ $value['id'] ] );
						} else {
							$option_value = '';
						}

						wp_clear_scheduled_hook( 'woocommerce_cancel_unpaid_orders' );

						if ( $option_value != '' )
							wp_schedule_single_event( time() + ( absint( $option_value ) * 60 ), 'woocommerce_cancel_unpaid_orders' );

					} else {

					   if ( isset( $_POST[$value['id']] ) ) {
							$option_value = woocommerce_clean( stripslashes( $_POST[ $value['id'] ] ) );
						} else {
							$option_value = '';
						}

					}

				break;

				// Special types
				case "multiselect" :
				case "multi_select_countries" :

					// Get countries array
					if ( isset( $_POST[ $value['id'] ] ) )
						$selected_countries = array_map( 'wc_clean', array_map( 'stripslashes', (array) $_POST[ $value['id'] ] ) );
					else
						$selected_countries = array();

					$option_value = $selected_countries;

				break;

				case "image_width" :

					if ( isset( $_POST[$value['id'] ]['width'] ) ) {

						$update_options[ $value['id'] ]['width']  = woocommerce_clean( stripslashes( $_POST[ $value['id'] ]['width'] ) );
						$update_options[ $value['id'] ]['height'] = woocommerce_clean( stripslashes( $_POST[ $value['id'] ]['height'] ) );

						if ( isset( $_POST[ $value['id'] ]['crop'] ) )
							$update_options[ $value['id'] ]['crop'] = 1;
						else
							$update_options[ $value['id'] ]['crop'] = 0;

					} else {
						$update_options[ $value['id'] ]['width'] 	= $value['default']['width'];
						$update_options[ $value['id'] ]['height'] 	= $value['default']['height'];
						$update_options[ $value['id'] ]['crop'] 	= $value['default']['crop'];
					}

				break;

				// Custom handling
				default :

					do_action( 'woocommerce_update_option_' . $type, $value );

				break;

			}

			if ( ! is_null( $option_value ) ) {
				// Check if option is an array
				if ( strstr( $value['id'], '[' ) ) {

					parse_str( $value['id'], $option_array );

					// Option name is first key
					$option_name = current( array_keys( $option_array ) );

					// Get old option value
					if ( ! isset( $update_options[ $option_name ] ) )
						 $update_options[ $option_name ] = get_option( $option_name, array() );

					if ( ! is_array( $update_options[ $option_name ] ) )
						$update_options[ $option_name ] = array();

					// Set keys and value
					$key = key( $option_array[ $option_name ] );

					$update_options[ $option_name ][ $key ] = $option_value;

				// Single value
				} else {
					$update_options[ $value['id'] ] = $option_value;
				}
			}

			// Custom handling
			do_action( 'woocommerce_update_option', $value );
		}

		// Now save the options
		foreach( $update_options as $name => $value ) {
			
			$current_option = get_option( $name );
			$current_default = cxecrt_get_default( $name );
			
			if ( $value === $current_default ) {
				delete_option( $name );
			}
			else if ( $value !== $current_option ) {
				update_option( $name, $value );
			}
		}

		return true;
	}

}

endif;

return new WC_Email_Cart_Settings();
