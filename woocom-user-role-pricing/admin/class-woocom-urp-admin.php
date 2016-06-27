<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocom_URP
 * @subpackage Woocom_URP/admin
 * @author     Your Name <email@example.com>
 */
class Woocom_URP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $options;

	public $wholesale_ordering_active;

	public $custom_prices;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->options = get_option('woocom_urp_options');

		$this->custom_prices = $this->options['prices'];
		if(isset($this->custom_prices['_regular_price'])) unset($this->custom_prices['_regular_price']);
		if(isset($this->custom_prices['_wholesale_price'])) unset($this->custom_prices['_wholesale_price']);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocom_URP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocom_URP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocom-urp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocom_URP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocom_URP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( 'post.php' == $hook && 'product' == $screen->post_type ) {
			wp_register_script( 'woocom-urp-admin', plugins_url('js/woocom-urp-admin.js', __FILE__ ), array('jquery'), $this->version, true);
			wp_enqueue_script( 'woocom-urp-admin' );
			wp_localize_script( 'woocom-urp-admin', 'ajax_object',
				array( 'prices' => $this->custom_prices)
			);
		}

		if ('edit.php' == $hook && 'product' == $screen->post_type) {
			wp_register_script( 'woocom-urp-quick-edit', plugins_url('js/woocom-urp-quick-edit.js', __FILE__ ), array('jquery'), $this->version, true);
			wp_enqueue_script( 'woocom-urp-quick-edit' );
			wp_localize_script( 'woocom-urp-quick-edit', 'ajax_object',
				array( 'prices' => $this->custom_prices)
			);
		}

		if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && 'shop_order' == $screen->post_type ) {
			wp_register_script( 'woocom-urp-admin-orders', plugins_url('js/woocom-urp-admin-orders.js', __FILE__ ), array('jquery'), $this->version, true);
			wp_enqueue_script( 'woocom-urp-admin-orders' );
			$nonce = wp_create_nonce('wcurp_set_customer_data');
			// in JavaScript, object properties are accessed as ajax_object.ajax_url
			$prices = $this->options['prices'];
			wp_localize_script( 'woocom-urp-admin-orders', 'urp_ajax_object',
				array( 'security' => $nonce,
				       'prices' => $prices
				)
			);
		}

	}

	public function woocommerce_admin_hooks() {

		// Add the Price Fields
		add_action( 'woocommerce_product_options_pricing', array($this, 'add_price_fields'), 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', array($this, 'add_variable_price_fields'), 10, 3);

		// Save Prices
		add_action( 'woocommerce_process_product_meta', array($this, 'save_price_fields'), 10, 1);
		add_action( 'woocommerce_create_product_variation', array($this, 'save_variable_price_fields'), 10, 1 ); // Hook into these two actions to add/update variations with wholesale price
		add_action( 'woocommerce_update_product_variation', array($this, 'save_variable_price_fields'), 10, 1 ); // variation_id is post_id, variations saved as new post

		if( function_exists('woocom_wholesale_is_wholesale_customer') && 'yes' == get_option('woocommerce_wholesale_storefront_enable') ) {
			$this->wholesale_ordering_active = true;
		} else {
			$this->wholesale_ordering_active = false;
		}

		// Bulk & Quick Edit actions
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array($this, 'variable_product_bulk_edit_price_fields') );
		add_action( 'woocommerce_product_quick_edit_start', array($this, 'quick_edit_price_fields') );
		add_action( 'woocommerce_product_quick_edit_save', array( $this, 'quick_edit_save_post' ), 10, 1 );
		add_action( 'woocommerce_product_bulk_edit_start', array($this, 'add_bulk_edit_fields'), 10, 1);
		add_action( 'woocommerce_product_bulk_edit_save', array($this, 'save_bulk_edit_fields'), 10, 1);
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_columns' ), 10 );

		// WooCommerce 2.4 Variations bulk edit save action
		//add_action( 'woocommerce_bulk_edit_variations_default', array ($this, 'ajax_bulk_edit_variations'), 10, 4 );

		// Customer Pricing data
		add_action( 'woocommerce_admin_order_data_after_order_details', array($this, 'admin_order_show_customer_data'), 15, 1);

	}

	public function add_user_role_pricing_page() {
		global $urp_settings_page;
		$urp_settings_page = add_submenu_page( 'woocommerce', 'User Role Pricing', 'User Role Pricing', 'manage_woocommerce', 'user_role_pricing', array($this, 'user_role_pricing_page') );
	}

	public function user_role_pricing_page() {
		if(!class_exists('Woocom_URP_Admin_Settings')) {
			include_once('class-woocom-urp-admin-settings.php');
		}
		Woocom_URP_Admin_Settings::output();
	}

	/*
	 * USER META FIELDS
	 */

	public function add_user_meta_fields($user) {
		if( ! current_user_can('edit_users')) {
			return;
		}

		$is_an_admin = false;
		if(user_can($user,'manage_options')) {
			$is_an_admin = true;
		}

		$user_base_price = get_user_meta($user->ID,'urp_user_base_price', true);
		if ('' == $user_base_price) {
			$user_base_price = 'role';
		}
		$user_price_multiplier = get_user_meta($user->ID,'urp_user_price_multiplier', true);
		if ('' == $user_price_multiplier) {
			$user_price_multiplier = '0';
		}
		// Yes, No, Role fields
		$tax_desc = __('Leave set to "Default" to use the user role tax exemption status.','woocom-urp');
		if($this->wholesale_ordering_active) {
			$tax_desc .= '<br/>'.__('If the user is set to be treated as a Wholesale Customer, and you leave this set to "Default", then the tax status will be based on the "Disable Wholesale Taxes" setting in the Wholesale Ordering plugin.', 'woocom-urp');
		}
		$shipping_desc = __('Leave set to "Default" to use the user role shipping calculation status.', 'woocom-urp');
		if($this->wholesale_ordering_active) {
			$shipping_desc .= '<br/>'.__('If the user is set to be treated as a Wholesale Customer, and you leave this set to "Default", then the shipping calculation status will be based on the "Disable Wholesale Shipping" setting in the Wholesale Ordering plugin.', 'woocom-urp');
		}
		$fields = array(
			'urp_user_tax_exempt' => array('label' => __('Is User Tax Exempt?', 'woocom-urp'), 'desc' => $tax_desc ),
			'urp_user_disable_shipping' => array('label' => __('Disable Shipping for user?', 'woocom-urp'), 'desc' =>  $shipping_desc ),
			'urp_user_billing_account' => array('label' => __('Show Billing Account Field?', 'woocom-urp'), 'desc' => __('Leave set to "Default" to use the user role billing account field setting', 'woocom-urp') )
		);
		if($this->wholesale_ordering_active) {
			if($is_an_admin) {
				$description = __('For admin users, leave set to "Wholesale Plugin Setting" to use the "Treat Admin as Wholesale" setting from the Wholesale Ordering plugin, otherwise if you set to "Yes" or "No", these personal settings will override the Wholesale Ordering plugin setting for this user.', 'woocom-urp');
			} else {
				$description = __('If you have the Wholesale Ordering plugin installed and active, set to "Yes" to treat this user like a Wholesale Customer, "No" to treat as Retail Customer, or leave set to "Default" to use the role settings.', 'woocom-urp');
			}
			$fields['urp_user_is_wholesale'] = array('label' => 'Treat as Wholesale Customer?', 'desc' => $description );
		}

		$field_values = array();
		foreach ($fields as $field => $values) {
			$field_values[$field] = get_user_meta($user->ID, $field, true);
			if ( '' == $field_values[$field] ) {
				$field_values[$field] = 'role';
			}
		}

		include('partials/woocom-urp-admin-user-fields.php');

	}

	public function save_user_meta_fields($user_id) {
		if( isset($_POST['urp_user_base_price']) && ( 'role' == $_POST['urp_user_base_price'] || in_array($_POST['urp_user_base_price'], array_keys($this->options['prices'] ) ) ) ) {
			update_user_meta( $user_id, 'urp_user_base_price', $_POST[ 'urp_user_base_price' ]  );
		}
		if( isset($_POST['urp_user_price_multiplier']) && is_numeric($_POST['urp_user_price_multiplier']) ) {
			if( '0' == $_POST['urp_user_price_multiplier'] ) {
				delete_user_meta($user_id, 'urp_user_price_multiplier');
			} else {
				update_user_meta( $user_id, 'urp_user_price_multiplier', (float)$_POST[ 'urp_user_price_multiplier' ]  );
			}

		}

		// Yes, No, Role fields
		$fields = array('urp_user_tax_exempt', 'urp_user_billing_account', 'urp_user_disable_shipping');
		if($this->wholesale_ordering_active) {
			$fields[] = 'urp_user_is_wholesale';
		}
		foreach($fields as $field) {
			if(isset($_POST[$field]) && in_array($_POST[$field], array('role','yes','no','plugin'))) {
				update_user_meta( $user_id, $field, $_POST[ $field ]  );
			}
		}

	}

	/*
	 * PRODUCT PRICE FIELDS
	 */

	public function add_price_fields() {

		foreach($this->custom_prices as $key => $name) {

			woocommerce_wp_text_input( array(
				'id' => sanitize_key($key),
				'label' => esc_html(stripslashes($name)) . ' ('.get_woocommerce_currency_symbol().')',
				'data_type' => 'price',
				'desc_tip' => 'true',
				'description' => sprintf(__( 'Enter the %s for this product.', 'woocom-urp' ), esc_html(stripslashes($name)) ),
				 ) );
		}

	}

	public function add_variable_price_fields($loop, $variation_data, $variation) {

		$id = $variation->ID;
		?>
		<div class="clear"></div>
		<div class="options_group">
			<h3><?php _e('Custom Variation Prices:', 'woocom-urp'); ?></h3>
		<?php
		$count = 0;

		foreach($this->custom_prices as $key => $name) {
			$class = ++$count%2 ? "form-row-first":"form-row-last";
			$_field_price = wc_format_localized_price( get_post_meta($id, sanitize_key($key), true) );
			$field_name = 'variable'.sanitize_key($key).'['.$loop.']';
			?>
				<p class="form-row <?php echo $class; ?>">
					<label><?php echo esc_html(stripslashes($name)) . ' (' . get_woocommerce_currency_symbol() . ')'; ?> <a class="tips" data-tip="<?php echo sprintf(__( 'Enter the %s for this variation.', 'woocom-urp' ), esc_html(stripslashes($name)) ); ?>" href="#"> [?]</a></label>
					<input type="text" size="5" name="<?php echo $field_name; ?>" value="<?php echo esc_attr( $_field_price ); ?>" class="wc_input_price" placeholder="<?php echo esc_html(stripslashes($name)); ?>" />
				</p>
			<?php
		}
		echo '</div>';
	}

	public function save_price_fields( $post_id ) {

		foreach($this->custom_prices as $key => $name) {

			if( isset($_REQUEST[$key]) ) {
				$field_price = wc_format_decimal($_REQUEST[$key]);
			} else {
				$field_price = 0;
			}
			update_post_meta( $post_id, sanitize_key($key), $field_price );
		}

		Woocom_URP_Product_Cache::update_product_cache();
	}

	public function save_variable_price_fields( $variation_id ) {

		foreach($this->custom_prices as $key => $name) {

			$field_name = 'variable'.sanitize_key($key);
			if (isset($_REQUEST[$field_name])) {
				$field_price = '';
				foreach($_REQUEST[$field_name] as $i => $price) {
					if($_REQUEST['variable_post_id'][$i] == $variation_id) {
						$field_price = wc_format_decimal($price);
					}
				}

			} else {
				$field_price = 0;
			}

			update_post_meta( $variation_id, sanitize_key($key), $field_price );
		}

		Woocom_URP_Product_Cache::update_product_cache();

	}

	public function variable_product_bulk_edit_price_fields() {
		foreach($this->custom_prices as $key => $name) {
			?>
			<optgroup label="<?php echo esc_attr( stripslashes($name) ); ?>">
				<option value="variable<?php echo esc_attr($key); ?>"><?php echo esc_html(stripslashes($name)). ' : ' .__( 'Set Prices', 'woocom-urp' ); ?></option>
				<option value="variable<?php echo esc_attr($key); ?>_increase"><?php echo esc_html(stripslashes($name)). ' : ' .__( 'Increase Prices by (fixed amount or %)', 'woocom-urp' ); ?></option>
				<option value="variable<?php echo esc_attr($key); ?>_decrease"><?php echo esc_html(stripslashes($name)). ' : ' .__( 'Decrease Prices by (fixed amount or %)', 'woocom-urp' ); ?></option>
			</optgroup>

			<?php
		}

	}

	public function quick_edit_price_fields() {
		foreach($this->custom_prices as $key => $name) {
			?>
			<div class="price_fields">
				<label>
					<span class="title"><?php echo esc_html(stripslashes($name)); ?></span>
				    <span class="input-text-wrap">
						<input type="text" name="<?php echo esc_attr($key); ?>" class="text <?php echo esc_attr($key); ?>"
						       placeholder="<?php echo esc_attr( stripslashes($name) ); ?>" value="">
					</span>
				</label>
				<br class="clear"/>
			</div>

			<?php
		}
	}

	public function quick_edit_save_post($product) {

		if ( $product->is_type('simple') || $product->is_type('external') ) {
			foreach($this->custom_prices as $key => $name) {
				if ( isset( $_REQUEST[$key] ) ) {
					$new_price = $_REQUEST[$key] === '' ? '' : wc_format_decimal( $_REQUEST[$key] );
					update_post_meta( $product->id, $key, $new_price );
				}
			}
		}

		Woocom_URP_Product_Cache::update_product_cache();

	}

	public function add_bulk_edit_fields() {
		foreach($this->custom_prices as $key => $name) {
			?>
			<div class="inline-edit-group">
				<label class="alignleft">
					<span class="title"><?php echo esc_html(stripslashes($name)); ?></span>
				    <span class="input-text-wrap">
				    	<select class="change<?php echo esc_attr($key); ?> change_to" name="change<?php echo esc_attr($key); ?>">
						    <?php
						    $options = array(
							    ''  => __( '&mdash; No Change &mdash;', 'woocom-urp' ),
							    '1' => __( 'Change to:', 'woocom-urp' ),
							    '2' => __( 'Increase by (fixed amount or %):', 'woocom-urp' ),
							    '3' => __( 'Decrease by (fixed amount or %):', 'woocom-urp' )
						    );
						    foreach ( $options as $num => $value ) {
							    echo '<option value="' . esc_attr( $num ) . '">' . $value . '</option>';
						    }
						    ?>
					    </select>
					</span>
				</label>
				<label class="change-input">
					<input type="text" name="<?php echo esc_attr($key); ?>" class="text <?php echo esc_attr($key); ?>"
					       placeholder="<?php _e( 'Enter price', 'woocom_wholesale' ); ?>" value=""/>
				</label>
			</div>

			<?php
		}
	}

	public function save_bulk_edit_fields( $product ) {

		if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {

			foreach($this->custom_prices as $key => $name) {

				$old_price = esc_attr(get_post_meta( $product->id, $key, true ));

				if ( ! empty( $_REQUEST['change'.$key] ) ) {

					$change_price = absint( $_REQUEST['change'.$key] );
					$price = esc_attr( stripslashes( $_REQUEST[$key] ) );

					switch ( $change_price ) {
						case 1 :
							$new_price = $price;
							break;
						case 2 :
							if ( strstr( $price, '%' ) ) {
								$percent = str_replace( '%', '', $price ) / 100;
								$new_price = $old_price + ( round( $old_price * $percent, absint( get_option( 'woocommerce_price_num_decimals' ) ) ) );
							} else {
								$new_price = $old_price + $price;
							}
							break;
						case 3 :
							if ( strstr( $price, '%' ) ) {
								$percent = str_replace( '%', '', $price ) / 100;
								$new_price = $old_price - ( round ( $old_price * $percent, absint( get_option( 'woocommerce_price_num_decimals' ) ) ) );
							} else {
								$new_price = $old_price - $price;
							}
							break;

						default :
							break;
					}

					if ( isset( $new_price ) && $new_price != $old_price ) {
						$new_price = wc_format_decimal( $new_price );
						update_post_meta( $product->id, $key, $new_price );
					}
				}
			}
		}

		Woocom_URP_Product_Cache::update_product_cache();
	}

	public function render_product_columns($column) {
		global $post;

		if ( empty( $the_product ) || $the_product->id != $post->ID ) {
			$the_product = wc_get_product( $post );
		}
		if('bundle' === $the_product->product_type) return;
		switch ($column) {
			// Add our hidden inline price data for quick edit javascript
			case 'name' :
				/* Custom inline data */
				echo '<div class="hidden" id="woocom_urp_inline_' . $post->ID . '">';
				foreach($this->custom_prices as $key => $name) {
					$price = esc_attr(get_post_meta( $post->ID, $key, true ));
					echo '<div class="'.esc_attr($key).'">' . $price . '</div>';
				}
				echo '</div>';
				break;

		}
	}

	private function set_bulk_variable_prices($key, $data, $product_id, $variations) {
		if ( empty( $data['value'] ) ) {
			return;
		}

		$price = wc_format_decimail( wc_clean( $data['value'] ) );

		foreach ( $variations as $variation_id ) {
			// Price field
			$id  = absint( $variation_id );
			update_post_meta($id, $key, $price);
		}

	}

	private function adjust_bulk_variable_prices($key, $data, $product_id, $variations, $bulk_action) {
		if ( empty( $data['value'] ) ) {
			return;
		}

		$operator = substr($bulk_action, -8);
		if('increase' == $operator) {
			$operator = '+';
		} elseif ('increase' == $operator) {
			$operator = '-';
		} else {
			return;
		}

		$value = wc_clean( $data['value'] );
		foreach ( $variations as $variation_id ) {
			// Get existing data
			$id  = absint( $variation_id );
			$price = get_post_meta( $id, $key, true );

			if ( '%' === substr( $value, -1 ) ) {
				$percent = wc_format_decimal( substr( $value, 0, -1 ) );
				$price  += ( ( $price / 100 ) * $percent ) * "{$operator}1";
			} else {
				$price += $value * "{$operator}1";
			}
			update_post_meta($id, $key, $price);
		}
	}

	public function ajax_bulk_edit_variations($bulk_action, $data, $product_id, $variations) {

		foreach($this->custom_prices as $key => $name) {
			if( 'variable'.$key == $bulk_action ) {
				$this->set_bulk_variable_prices($key, $data, $product_id, $variations);
				break;
			} elseif ( 'variable'.$key.'_increase' == $bulk_action || 'variable'.$key.'_decrease' == $bulk_action ) {
				$this->adjust_bulk_variable_prices($key, $data, $product_id, $variations, $bulk_action);
				break;
			}
		}

	}

	public function admin_order_show_customer_data($order) {
		$info = get_post_meta($order->id, 'order_pricing_info', true);
		if('' !== $info && is_array($info)) {
			$base_price_name = $info['base_price'];
			$multiplier = $info['multiplier'];
			$tax_exempt = $info['tax_exempt'];
			$disable_shipping = $info['disable_shipping'];
			$is_wholesale = $info['is_wholesale'];
		} else {
			$base_price_name = $multiplier = $tax_exempt = $disable_shipping = $is_wholesale = __('Not set in Order meta', 'woocom-urp');
		}

		?>

		<p class="form-field form-field-wide"><strong><?php _e('Base Price', 'woocom-urp'); ?>: </strong><span id="base_price"><?php echo esc_html($base_price_name); ?></span></p>
		<p class="form-field form-field-wide"><strong><?php _e('Price Multiplier', 'woocom-urp'); ?>: </strong><span id="multiplier"><?php echo esc_html($multiplier); ?></span></p>
		<p class="form-field form-field-wide"><strong><?php _e('Tax Exempt', 'woocom-urp'); ?>: </strong><span id="tax_exempt"><?php echo esc_html($tax_exempt); ?></span></p>
		<p class="form-field form-field-wide"><strong><?php _e('Disable Shipping', 'woocom-urp'); ?>: </strong><span id="disable_shipping"><?php echo esc_html($disable_shipping); ?></span></p>
		<p class="form-field form-field-wide"><strong><?php _e('Is Wholesale', 'woocom-urp'); ?>: </strong><span id="is_wholesale"><?php echo esc_html($is_wholesale); ?></span></p>

		<?php
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file == WOOCOM_URP_PLUGIN_BASENAME ) {
			$row_meta = array(
				'docs'    => '<a href="http://stephensherrardplugins.com/docs/woocommerce-user-role-pricing/" title="' . esc_attr( __( 'View WooCommerce User Role Pricing Documentation', 'woocom-urp' ) ) . '">' . __( 'Docs', 'woocom-urp' ) . '</a>',
				'support' => '<a href="http://stephensherrardplugins.com/support/forum/woocommerce-user-role-pricing/" title="' . esc_attr( __( 'Support Forum', 'woocom-urp' ) ) . '">' . __( 'Plugin Support', 'woocom-urp' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=user_role_pricing' ) . '" title="' . esc_attr( __( 'View WooCommerce User Role Pricing Settings', 'woocom-urp' ) ) . '">' . __( 'Settings', 'woocom-urp' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

}
