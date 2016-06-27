<?php
/*
* interfaces.php - cart list page modifications
*
*
*/

class WCEC_Cart_Index_Page {

	public function __construct() {
		global $start_date, $end_date;

		$current_month = date("j/n/Y", mktime(0, 0, 0,  1, date("m"), date("Y") ) );

		$start_date = ( isset( $_GET['start_date'] ) ) ? $_GET['start_date'] : '';
		$end_date	= ( isset( $_GET['end_date'] ) ) ? $_GET['end_date'] : '';
		
		if ( ! $start_date )
			$start_date = $current_month;
		if ( ! $end_date )
			$end_date = date( 'Ymd', current_time( 'timestamp' ) );
		
		$start_date = strtotime( $start_date );
		$end_date = strtotime( $end_date );
		
		
		// Add custom filter to post list.
		/*
		add_filter( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 1000 );
		add_filter( 'posts_where', array( $this, 'filter_where' ) );
		*/
		
		add_action( 'admin_menu',array( $this, 'hide_add_new_carts' ) );
		// add_action( 'views_edit-stored-carts', array( $this,'cxecrt_remove_cart_views' ) ); // Remove the All / Published / Trash view.
		add_action( 'manage_stored-carts_posts_custom_column', array( $this,'cxecrt_manage_cart_columns' ), 1, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_index' ) );
		
		add_filter( 'manage_edit-stored-carts_columns', array( $this,'cxecrt_carts_columns' ) ) ;
		add_filter( 'manage_edit-stored-carts_sortable_columns', array( $this,'cxecrt_carts_sort' ) );
		add_filter( 'request', array( $this,'cart_column_orderby' ) );
		// add_filter( 'bulk_actions-edit-' . 'stored-carts', '__return_empty_array' ); // Remove bulk edit
		add_filter( 'parse_query', array( $this, 'woocommerce_carts_search_custom_fields' ) );
		add_filter( 'get_search_query', array( $this, 'woocommerce_carts_search_label' ) );
		
		// Top interface
		add_action( 'admin_notices', array( $this, 'render_top_interface' ) );
	}

	/*
	* Include require init scripts for the index page.
	*/

	function woocommerce_carts_search_label( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' != $pagenow ) return $query;
		if ( $typenow!='stored-carts' ) return $query;
		if ( ! get_query_var( 'cart_search' ) ) return $query;

		return $_GET['s'];
	}

	function woocommerce_carts_search_custom_fields( $wp ) {
		global $pagenow, $wpdb;

		if ( 'edit.php' != $pagenow ) return $wp;
		if ( ! isset( $wp->query_vars['s'] ) || !$wp->query_vars['s'] ) return $wp;
		if ( $wp->query_vars['post_type'] != 'stored-carts' ) return $wp;

		$search_fields = array(
			'_cxecrt_cart_items'
		);

		// Query matching custom fields - this seems faster than meta_query
		$post_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key IN ( '.'"'.implode( '","', $search_fields ).'"'.' ) AND meta_value LIKE "%%%s%%"', esc_attr( $_GET['s'] ) ) );
		// Query matching excerpts and titles
		$post_ids = array_merge( $post_ids, $wpdb->get_col( $wpdb->prepare( '
			SELECT '.$wpdb->posts.'.ID
			FROM '.$wpdb->posts.'
			LEFT JOIN '.$wpdb->postmeta.' ON '.$wpdb->posts.'.ID = '.$wpdb->postmeta.'.post_id
			LEFT JOIN '.$wpdb->users.' ON '.$wpdb->postmeta.'.meta_value = '.$wpdb->users.'.ID
			WHERE
				post_excerpt 	LIKE "%%%1$s%%" OR
				post_title 		LIKE "%%%1$s%%" OR
				user_login		LIKE "%%%1$s%%" OR
				user_nicename	LIKE "%%%1$s%%" OR
				user_email		LIKE "%%%1$s%%" OR
				display_name	LIKE "%%%1$s%%"
			',
			esc_attr( $_GET['s'] )
		) ) );

		// Add ID
		$search_order_id = str_replace( 'Order #', '', $_GET['s'] );
		if ( is_numeric( $search_order_id) ) $post_ids[] = $search_order_id;

		// Add blank ID so not all results are returned if the search finds nothing
		$post_ids[] = 0;

		// Remove s - we don't want to search order name
		unset( $wp->query_vars['s'] );

		// so we know we're doing this
		$wp->query_vars['cart_search'] = true;

		// Search by found posts
		$wp->query_vars['post__in'] = $post_ids;
	}

	public function enqueue_index() {
		global $pagenow;
		
		if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'stored-carts' ) {
			
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'jquery' );
			
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );
			wp_enqueue_style( 'email_cart_admin_index_css', plugins_url() . '/woocommerce-email-cart/assets/css/email_cart_admin_index.css' );
		}
	}

	/**
	* hide_add_new_carts()
	*
	* Hide the "New Carts" link
	*/
	public function hide_add_new_carts() {
		global $submenu;
		// replace my_type with the name of your post type
		unset( $submenu['edit.php?post_type=stored-carts'][10] );
	}

	/*
	*
	* cxecrt_carts_columns( $columns )
	*
	* Rename Columns for the new "Cart" post type
	*/
	public function cxecrt_carts_columns( $columns ) {
		global $cxecrt_options;
		
		$columns = array(
			'cb'             => 'checky',
			// 'title'       => $columns['title'],
			'cart_sent'      => __( 'Cart Created', 'email-cart' ),
			'cart_retrieved' => __( 'Cart Retrieved', 'email-cart' ),
			'cart_author'    => __( 'Author', 'email-cart' ),
			'cart_name'      => __( 'Name <em>(optional)</em>', 'email-cart' ),
			'actions'        => __( '&nbsp;', 'email-cart' ),
		);
		
		if ( $cxecrt_options['show_products_on_index'] ) {
			// $columns['products'] = __( 'Products', 'email-cart' );
		}
		
		return $columns;
	}

	/**
	* my_edit_carts_columns( $columns )
	*
	* Declare our new columns as sortable columns ( except the action column, for obvious reasons )
	*/
	public function cxecrt_carts_sort( $columns ) {
		
		$custom = array(
			'cart_sent' => 'cart_sent',
		);
		
		return wp_parse_args( $custom, $columns );
	}

	/*
	*
	* cart_column_orderby( $vars )
	*
	* Hook for the actual sorting on the custom columns (when the post request comes back)
	*
	*/
	public function cart_column_orderby( $vars ) {

		return $vars;
	}

	/*
	*
	* cxecrt_remove_cart_views( $views )
	*
	* Remove drag-over action items on carts page
	*
	*/
	public function cxecrt_remove_cart_views( $views ) {
		
		unset( $views['all'] );
		unset( $views['publish'] );
		unset( $views['trash'] );
		return $views;
	}

	/*
	*
	* cxecrt_manage_cart_columns( $column, $post_id )
	*
	* Add cases for our custom columns (status, updated, actions )
	*
	*/
	public function cxecrt_manage_cart_columns( $column, $post_id ='' ) {
		global $post;
		
		$cart = new WCEC_Saved_Cart();
		$cart->load_saved_cart( $post->ID );
		
		$post_url = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
		
		switch( $column ) {

			case 'cart_author':
				
				echo $cart->get_cart_author_display();
				
				break;
				
			case 'cart_name':
				
				echo $cart->get_cart_title();
				
				break;

			case 'cart_sent' :
			
				$full_date = '';
				$full_date .= get_the_time( 'M j Y' );
				$full_date .= ' ';
				$full_date .= __( '@', 'email-cart' );
				$full_date .= ' ';
				$full_date .= get_the_time( 'g:ia' );
				?>
				<a href="<?php echo esc_url( $post_url ) ?>" title="<?php echo esc_attr( $full_date ); ?>">
					<span class="dashicons dashicons-calendar"></span>
					<?php echo get_the_time( 'M j Y' ); ?>
				</a>
				<?php
				
				break;
			
			case 'cart_retrieved' :
				
				if ( $cart_retrieved = get_post_meta( $post->ID, '_cxecrt_cart_retrieved_date', true ) ) {
					
					if ( is_array( $cart_retrieved ) && ! empty( $cart_retrieved ) ){
						$cart_retrieved = end( $cart_retrieved );
						update_post_meta( $post->ID, '_cxecrt_cart_retrieved_date', $cart_retrieved );
					}
					
					$full_date = '';
					$full_date .= date( 'M j Y', $cart_retrieved );
					$full_date .= ' ';
					$full_date .= __( '@', 'email-cart' );
					$full_date .= ' ';
					$full_date .= date( 'g:ia', $cart_retrieved );
					
					$retrieve_count = intval( get_post_meta( $post->ID, '_cxecrt_cart_retrieved_count', TRUE ) );
					?>
					<span title="<?php echo esc_attr( $full_date ); ?>">
						<span class="cxecrt_index_retrieval_count"><?php echo $retrieve_count; ?></span> <?php _e( 'retrievals, last time', 'email-cart' ) ?> <span class="dashicons dashicons-calendar"></span> <?php echo date( 'M j Y', $cart_retrieved ); ?>
					</span>
					<?php
				}
				
				break;
				
			case 'products' :
				
				$cartitems = get_post_meta( $post->ID, '_cxecrt_cart_items', true );
				$items_arr = str_replace( array( 'O:17:"WC_Product_Simple"','O:10:"WC_Product"' ), 'O:8:"stdClass"',$cartitems );

				if ( isset( $cartitems ) && $cartitems != false ) {
					$order_items = ( array ) maybe_unserialize( $items_arr );
				}
				else {
					break;
				}
			
				$loop = 0;

				if (sizeof( $order_items )>0 && $order_items != false ) {
					foreach ( $order_items as $item) :
						
						
						if (function_exists( 'get_product' ) ) {
						if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0) :
								$_product = get_product( $item['variation_id'] );
							else :
								$_product = get_product( $item['product_id'] );
							endif;
						}
						else {
							if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0) :
								$_product = new WC_Product_Variation( $item['variation_id'] );
							else :
								$_product = new WC_Product( $item['product_id'] );
							endif;
						}
						if ( isset( $_product ) && $_product != false ) {
							echo "<a href='" . get_admin_url( '','post.php?post='.$_product->id.'&action=edit' ) . "'>" . $_product->get_title() . "</a>";
							if ( isset( $_product->variation_data) ) {
								echo ' ( ' . woocommerce_get_formatted_variation( $_product->variation_data, true ) . ' )';
							}
							if ( $item['quantity'] > 1)
								echo " x".$item['quantity'];
						}
						if ( $loop < sizeof( $order_items ) -1) echo ", ";
						$loop++;
					endforeach;
				}
				else {
					echo "<span style='color:lightgray;'>" . __("No Products", "email-cart") . "</span>";
				}
				
				break;
			
			case 'actions':
				
				?>
				<a class="button" href="<?php echo esc_url( $post_url ) ?>">
					<!-- <span class="dashicons dashicons-visibility"></span> -->
					<span class="cxecrt-elipses" title="<?php _e( 'View', 'email-cart' ); ?>"></span>
				</a>
				<?php
				
				break;

			default :
				
				break;
		}
	}

	/*
	*
	* Print Available cart actions
	*
	*/
	public function restrict_manage_posts() {
		global $pagenow;
		
		if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && 'stored-carts' == $_GET['post_type'] ) {
			?>
			<label for="from"><?php _e( 'From:', 'email-cart' ); ?></label> <input type="text" name="start_date" id="from" readonly value="<?php echo esc_attr( date( 'Y-m-d', $start_date ) ); ?>" /> <label for="to"><?php _e( 'To:', 'email-cart' ); ?></label> <input type="text" name="end_date" id="to" readonly value="<?php echo esc_attr( date( 'Y-m-d', $end_date ) ); ?>" /> 
			<script type="text/javascript">
			jQuery(function() {
				<?php $this->woocommerce_datepicker_js_carts(); ?>
			});
			</script>
			<?php
		}
	}
		
	/**
	 * Adds a date range to the WHERE portion of our query
	 *
	 * @param string $where The current WHERE portion of the query
	 * @return string $where The updated WHERE portion of the query
	 */
	public function filter_where( $where = '' ) {
		global $pagenow;

		if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'stored-carts' ) {
			
			global $cxecrt_options, $start_date, $end_date, $offset;
			
			if ( isset( $_GET['lifetime'] ) || ! isset( $_GET['mv'] ) ) {
			
				$args = array(
					'numberposts'     => 1,
					'offset'          => 0,
					'orderby'         => 'post_date',
					'order'           => 'ASC',
					'post_type'       => 'stored-carts',
					'post_status'     => 'publish',
						
				 );
				
				$post = get_posts( $args );
				if ( isset( $post[0] ) )
					$post = $post[0];
				if ( isset( $post ) && sizeof( $post ) > 0)
					$start_date = strtotime( $post->post_date ) - (86400); // Add on a day for good measure.
			}
		
			$start = date( 'Y-m-d G:i:s', $start_date );
			$end = date( 'Y-m-d G:i:s', $end_date + 86400);
			
			if ( ! isset( $_GET['mv'] ) ) {
				//If not isset -> set with dumy value
				$_GET['action'] = "empty";
			}

			$where .= " AND post_date > '" . $start . "' AND post_date < '" . $end . "'";
			
			if ( isset( $_GET['author'] ) ) {
				if ( $_GET['author'] == "-1" )
					$where .= " AND post_author = ''";
			}
		}
		
		return $where;
	}

	/**
	 * JS for the datepicker on the table (changes from woocommerce stock include removing the minimum date )
	 */
	public function woocommerce_datepicker_js_carts() {
		?>
		var dates = jQuery( "#posts-filter #from, #posts-filter #to" ).datepicker({
			defaultDate: "",
			dateFormat: "yy-mm-dd",
			//changeMonth: true,
			//changeYear: true,
			numberOfMonths: 1,
			maxDate: "+0D",
			showButtonPanel: true,
			showOn: "button",
			buttonImage: "<?php echo WC()->plugin_url(); ?>/assets/images/calendar.png",
			buttonImageOnly: true,
			onSelect: function( selectedDate ) {
				var option = this.id == "from" ? "minDate" : "maxDate",
					instance = jQuery( this ).data( "datepicker" ),
					date = jQuery.datepicker.parseDate(
						instance.settings.dateFormat ||
						jQuery.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
		<?php
	}
	
	
	/**
	* Index top interface.
	*/
	public function render_top_interface() {
		global $screen;
		
		$cart_url = cxecrt_get_woocommerce_cart_url( '#cxecrt-save-cart' );
		
		// Bail if not Cart Index page.
		if ( ! isset( $screen->id ) || 'edit-stored-carts' !== $screen->id ) return;
		?>
		<div class="updated cxecrt-admin-notice">
			<p><?php _e( '<strong>Save & Share Cart</strong> allows you and your customers to send a link to a pre-stocked cart to any email address. Admins can send the email from here, and your customers send using the Save & Share Cart button on your shop\'s cart page. The list below shows Sent Cart activity.', 'email-cart' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $cart_url ); ?>" class="button button-primary cxecrt-button">
					<?php _e( 'Save & Share Cart', 'email-cart' ); ?> <span class="dashicons dashicons-cart"></span>
				</a>
				&nbsp;
				<a href="<?php echo admin_url( 'options-general.php?page=email_cart_settings' ); ?>" class="button cxecrt-button">
					<?php _e( 'Settings', 'email-cart' ); ?> <span class="dashicons dashicons-admin-generic"></span>
				</a>
			</p>
	    </div>
		<?php
	}
	
}

?>