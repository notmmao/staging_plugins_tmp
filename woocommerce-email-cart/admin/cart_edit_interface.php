<?php
/*
*
* cart_edit_interfaces.php - Interface elements / modifications for the "Cart Edit" page.
*
*/

/*
*
* Add Metaboxes for "Cart Edit" page ( status / last updated date )
*
*/


class WCEC_Edit_Interface {

	public $saved_cart;

	public function __construct() {
		global $post;
		
		$this->saved_cart = new WCEC_Saved_Cart();
		
		if ( isset( $post ) ){
			$this->saved_cart->load_saved_cart( $post->ID );
		}
		
		add_action( 'admin_enqueue_scripts', array( &$this, 'tooltip_scripts' ) );
	
		// These metaboxes default to the left.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		// Remove post-type support elements.
		add_action( 'admin_menu', array( $this, 'remove_post_type_support' ) );
		
		// Handle overwriting of the saved cart.
		add_action( 'wp_loaded', array( $this, 'handle_overwrite' ), 99 );
		
		// Save custom post meta.
		add_action( 'save_post', array( $this, 'save_custom_meta' ), 10, 3 );
	}
	
	public function handle_overwrite( $title) {
		global $cxecrt, $post;
		
		if ( isset( $_GET['cxecrt-overwrite-cart'] ) ) {
			
			$cxecrt->overwrite_saved_cart();
			wp_safe_redirect( remove_query_arg( 'cxecrt-overwrite-cart' ) );
			exit;
		}
	}
	
	public function custom_edit_title( $title) {
		$newtitle = 'View Cart ' . $title;
		return $newtitle;
	}

	/**
	 *
	 */
	public function tooltip_scripts() {
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'post.php' && 'stored-carts' == get_post_type( get_post( $_GET['post'] ) ) ) {
			
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'ajax-chosen' );
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );
			wp_enqueue_style( 'email_cart_admin_edit_css', plugins_url() . '/woocommerce-email-cart/assets/css/email_cart_admin_edit.css' );
		}
	}

	/**
	 * Add metabox to show items in the cart, complete with a bunch of info about the items
	 * Layout was taken from the order details page( thanks woo!)
	 */
	public function add_meta_boxes(){

		add_meta_box(
			'woocommerce-cart-items',
			__( 'Cart', 'email-cart' ),
			array( $this,'woocommerce_cart_items_meta_box' ),
			'stored-carts',
			'normal',
			'default'
		);
		
		add_meta_box(
			'woocommerce-cart-details',
			__( 'Activity', 'email-cart' ),
			array( $this,'cart_action_customer_metabox' ),
			'stored-carts',
			'side',
			'default'
		);
	}

	/**
	 * Cart Actions Implementation
	 *
	 */
	public function cart_action_customer_metabox(){
		global $post;
		
		// Load the saved_cart.
		$this->saved_cart->load_saved_cart( $post->ID );
		
		// Generate full date.
		$full_created_date = '';
		$full_created_date .= get_the_time( 'M j Y' );
		$full_created_date .= ' ';
		$full_created_date .= __( '@', 'email-cart' );
		$full_created_date .= ' ';
		$full_created_date .= get_the_time( 'g:ia' );
		
		$retrieve_count = intval( get_post_meta( $post->ID, '_cxecrt_cart_retrieved_count', TRUE ) );
		
		$full_retrieved_date = '';
		if ( $cart_retrieved = get_post_meta( $post->ID, '_cxecrt_cart_retrieved_date', true ) ) {
			$full_retrieved_date = '';
			$full_retrieved_date .= date( 'M j Y', $cart_retrieved );
			$full_retrieved_date .= ' ';
			$full_retrieved_date .= __( '@', 'email-cart' );
			$full_retrieved_date .= ' ';
			$full_retrieved_date .= date( 'g:ia', $cart_retrieved );
		}
		?>
		<div class="cxecrt-meta-box-details">
			
			<div class="misc-pub-section misc-pub-visibility">
				<span class="dashicons dashicons-admin-users"></span> 
				<?php _e( 'Created by:', 'email-cart' ); ?> 
				<span class="cxecrt-value"><?php echo $this->saved_cart->get_cart_author_display() ?></span>
			</div>
			
			<div class="misc-pub-section misc-pub-visibility">
				<span class="dashicons dashicons-calendar"></span> 
				<?php _e( 'Created:', 'email-cart' ); ?> 
				<span class="cxecrt-value"><?php echo $full_created_date ?></span>
			</div>
			
			<div class="misc-pub-section misc-pub-visibility">
				<span class="dashicons dashicons-calendar"></span> 
				<?php _e( 'Retrieve Count:', 'email-cart' ); ?> 
				<?php if ( ! $retrieve_count ) { ?>
					<span class="cxecrt-value cxecrt-value-not-retrieved">
						<?php _e( 'Not Yet Retrieved...', 'email-cart' ); ?>
					</span>
				<?php } else { ?>
					<span class="cxecrt-value">
						<?php echo $retrieve_count; ?>
					</span>
				<?php } ?>
			</div>
			
			<?php if ( $retrieve_count ) { ?>
				<div class="misc-pub-section misc-pub-visibility">
					<span class="dashicons dashicons-calendar"></span> 
					<?php _e( 'Last Retrieved:', 'email-cart' ); ?> 
					<span class="cxecrt-value">
						<?php echo $full_retrieved_date; ?>
					</span>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Remove post-type support.
	 *
	 */
	public function remove_post_type_support() {
			
		// Remove Title meta Box from "Cart Edit" page
		// remove_post_type_support( 'stored-carts', 'title' );

		// Remove Title meta Box from "Cart Edit" page
		remove_post_type_support( 'stored-carts', 'editor' );

		// Remove Publish meta Box from "Cart Edit" page
		// remove_meta_box( 'submitdiv', 'stored-carts', 'side' );

		// Remove Author meta Box from "Cart Edit" page
		remove_meta_box( 'authordiv', 'stored-carts', 'side' );

		// Remove the WooThemes' custom configuration box for posts and pages - not needed!
		remove_meta_box( 'woothemes-settings', 'stored-carts', 'normal' );

		// Remove Slug Edit box.
		remove_meta_box( 'slugdiv', 'stored-carts', 'normal' );
	}

	/**
	 * Cart Meta Box
	 */
	public function woocommerce_cart_items_meta_box( $post) {

		global $cxecrt, $post;
		
		// Get the saved info;
		$to_email_address  = get_post_meta( $post->ID, '_cxecrt_to_email_address', true );
		$email_content     = get_post_meta( $post->ID, '_cxecrt_email_content', true );
		$email_subject     = get_post_meta( $post->ID, '_cxecrt_email_subject', true );
		$landing_page      = get_post_meta( $post->ID, '_cxecrt_redirect_to', true );
		if ( ! $landing_page ) $landing_page = 'cart';
		?>
		
		<div class="cxecrt-wrap cxecrt-wrap-edit-page">
			
			<table class="cxecrt-admin-table">
				<tbody>
					
					<tr>
						<td class="cxecrt-label">
							<label><?php _e( 'Retrieve Cart URL', 'email-cart' ); ?></label>
						</td>
						<td>
							<div class="cxecrt-form-row">
								<input type="text" name="landing_page_display" id="landing_page_display" value="<?php echo $cxecrt->get_retrieve_cart_url( $post->ID, $landing_page ); ?>">
							</div>
							<div class="cxecrt-form-row">
								<label>
									<input type="radio" name="landing_page" value="cart" <?php checked( 'cart', $landing_page, TRUE ); ?> checked="checked" data-url="<?php echo esc_attr( $cxecrt->get_retrieve_cart_url( $post->ID, 'cart' ) ); ?>" > <?php _e( 'Redirect to Cart', 'email-cart' ) ?>
								</label>
								<label>
									<input type="radio" name="landing_page" value="checkout" <?php checked( 'checkout', $landing_page, TRUE ); ?> data-url="<?php echo esc_attr( $cxecrt->get_retrieve_cart_url( $post->ID, 'checkout' ) ); ?>" > <?php _e( 'Redirect to Checkout', 'email-cart' ) ?>
								</label>
								<label>
									<input type="radio" name="landing_page" value="home" <?php checked( 'home', $landing_page, TRUE ); ?> data-url="<?php echo esc_attr( $cxecrt->get_retrieve_cart_url( $post->ID, 'home' ) ); ?>" > <?php _e( 'Redirect to Home', 'email-cart' ) ?>
								</label>
							</div>
							<p class="cxecrt-description">
								<?php _e( 'You can copy this cart and send it to anyone. Anyone who clicks on it can retrieve it.', 'email-cart' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<td class="cxecrt-label">
							<label for="post_type">
								<?php _e( 'Cart', 'email-cart' ); ?>
							</label>
						</td>
						<td>
							
							<div class="cxecrt-form-row">
								<div class="cxecrt-mini-cart-holder">
									
									<?php
									// Return the current users cart - if it's by any chance needed.
									$cxecrt->backup_current_cart();
																		
									// Load the cart from this post.
									$cxecrt->load_cart_from_post( $post->ID );
									?>
									
									<?php if ( 0 !== sizeof( WC()->cart->get_cart() ) ) { ?>
									
										<div class="cxecrt-mini-cart">
											<?php woocommerce_mini_cart(); ?>
											
											<a href="" class="button cxecrt-button cart-edit-button">
												<?php _e( 'Edit Cart', 'email-cart' ); ?>
											</a>
										</div>
										
										<!--
										<div class="compiler_actions">
											<a href="<?php echo get_permalink( woocommerce_get_page_id( 'cart' ) ) ?>" class="button cxecrt-button edit-cart"><?php _e( 'Edit Cart', 'email-cart' ) ?></a>
											<a href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>" class="button cxecrt-button add-cart"><?php _e( 'Add Products', 'email-cart' ) ?></a>
										</div>
										-->
									
									<?php } else { ?>
									
										<div class="cxecrt-mini-cart cxecrt-mini-cart-empty">
											<p><?php _e( 'Your cart is empty.', 'email-cart' ) ?></p>
											<!--<a href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>" class="button cxecrt-button add-cart"><?php _e( 'Add Products', 'email-cart' ) ?></a>-->
										</div>
										
									<?php } ?>
									
									<?php
									// Return the current users cart - if it's by any chance needed.
									$cxecrt->restore_current_cart();
									?>
								</div>
							</div>
							<!--
							<p class="cxecrt-description">
								<?php
								echo sprintf(
									__( 'This is what\'s currently in your cart. Add products to it - the standard way through your <a href="%s">shop</a> - then return here to send it.', 'email-cart' ),
									get_permalink( woocommerce_get_page_id( 'shop' ) )
								);
								?>
							</p>
							-->
							
							<div class="cxecrt-overwrite-cart-holder">
								<div class="cxecrt-overwrite-cart-holder-inner">
									
									<p class="cxecrt-description">
										<?php _e( 'The way you change the above saved cart is by overwriting it with your existing WooCommerce cart seen below. Modify your existing cart and when you\'re ready return here and click to overwrite the Saved Cart above.', 'email-cart' ); ?>
									</p>
									
									<div class="cxecrt-overwrite-actions">
										<a href="<?php echo add_query_arg( 'cxecrt-overwrite-cart', 'yes' ); ?>" class="button cxecrt-button overwrite-button button-primary">
											<span class="dashicons dashicons-arrow-up-alt"></span> <?php _e( 'Overwrite Cart above with your existing Cart Below', 'email-cart' ); ?>
										</a>
										&nbsp;
										<a href="" class="button cxecrt-button cancel-button">
											<?php _e( 'Cancel', 'email-cart' ); ?>
										</a>
									</div>
								
									<div class="cxecrt-mini-cart-holder">
										
										<?php if ( 0 !== sizeof( WC()->cart->get_cart() ) ) { ?>
										
											<div class="cxecrt-mini-cart">
												<?php woocommerce_mini_cart(); ?>
											</div>
											
											<!--
											<div class="compiler_actions">
												<a href="<?php echo get_permalink( woocommerce_get_page_id( 'cart' ) ) ?>" class="button cxecrt-button edit-cart"><?php _e( 'Edit Cart', 'email-cart' ) ?></a>
												<a href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>" class="button cxecrt-button add-cart"><?php _e( 'Add Products', 'email-cart' ) ?></a>
											</div>
											-->
										
										<?php } else { ?>
										
											<div class="cxecrt-mini-cart cxecrt-mini-cart-empty">
												<p><?php _e( 'Your cart is empty.', 'email-cart' ) ?></p>
												<!--<a href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>" class="button cxecrt-button add-cart"><?php _e( 'Add Products', 'email-cart' ) ?></a>-->
											</div>
											
										<?php } ?>
									</div>
									
								</div>
							</div>
							
						</td>
					</tr>
					
					<!--
					<tr>
						<td class="cxecrt-label">
							<label><?php _e( 'Redirect To', 'email-cart' ); ?></label>
						</td>
						<td>
							<div class="cxecrt-form-row">
								<select>
									<option>--</option>
								</select>
							</div>
						</td>
					</tr>
					-->
					
				</tbody>
			</table>
			
		</div>
		<?php
	}
	
	/**
	 * Save custom post meta.
	 */
	public function save_custom_meta( $post_id, $post, $update ) {

		// Check post type.
		if ( 'stored-carts' != $post->post_type ) return;

		if ( isset( $_REQUEST['landing_page'] ) ) {
			
			update_post_meta(
				$post_id,
				'_cxecrt_redirect_to',
				sanitize_text_field( $_REQUEST['landing_page'] )
			);
		}
	}
	
}

?>