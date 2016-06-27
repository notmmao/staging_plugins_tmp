<?php
/**
 * WCEC_Saved_Cart
 */

class WCEC_Saved_Cart {
	
	public $cart_id;
	public $cart_title;
	
	public $cart_author_id;
	public $cart_author_fullname;
	public $cart_author_username;
	
	public $cart_date;
	public $cart_content;
	public $cart_products;

	public function __construct() {
		global $offset, $cxecrt_options;

		$offset = get_option( 'gmt_offset' );
		$this->cart_id = '';
	}

	public function set_products() {
		
		$cart          = WC()->cart;
		$cart_contents = $cart->cart_contents;
		$keys          = array_keys( $cart_contents );
		
		$products = array();
		foreach ( $keys as $key) {
			$products[] = $cart_contents[$key];
		}

		$this->cart_products = $this->generic_objects( $products );
		
		$this->cart_products = $this->add_titles_to_cart_items( $this->cart_products );
	}

	/*
	* Change all objects  to generic objects for post meta storage
	*
	*/
	public function generic_objects( $object) {
		$serialized = serialize( $object);

		$taboo = array( 'O:17:"WC_Product_Simple"','O:10:"WC_Product"' );
		$generic_replace = 'O:8:"stdClass"';
		$items_arr = str_replace( $taboo, $generic_replace , $serialized );

		return unserialize( $items_arr);
	}

	public function is_guest_order() {
	
		if ( 0 < $this->cart_author_id )
			return false;
		else
			return true;
	}

	public function load_saved_cart( $post_id ) {
		global $offset;
		
		// Precond: post_id > 0 and not ""
		$this->cart_id = $post_id;
		
		$post = get_post( $post_id );
		
		//$products = get_post_meta( $post_id,'_cxecrt_cart_items', true );

		$this->cart_title = $post->post_title;
		$this->cart_date = $post->post_date;
		
		$this->cart_author_id = $post->post_author;
		$this->set_author_details();
	}
	
	public function set_author_details() {
		global $current_user;
		
		/**
		 * Set author_fullname
		 */
		$full_name = array();
		if ( $this->cart_author_id > 0 ) {
			$user_info = get_userdata( $this->cart_author_id );
			
			if ( '' !== $user_info->first_name )
				$full_name[] = $user_info->first_name;
			
			if ( '' !== $user_info->last_name )
				$full_name[] = $user_info->last_name;
		}
		$this->cart_author_fullname = ( ! empty( $full_name ) ) ? implode( ' ', $full_name ) : '' ;
		
		/**
		 * Set author_username
		 */
		$username = '';
		if ( $this->cart_author_id > 0 ) {
			$username = $user_info->user_login;
		}
		$this->cart_author_username = $username;
	}
	
	public function save_cart() {
		global $offset, $cxecrt_options;

		$post = array(
			// 'post_author'  => '', // Leave blank to use logged in user as post author. if guest then will be 0.
			'post_content' => '',
			'post_status'  => 'publish',
			'post_title'   => '',
			'post_type'    => 'stored-carts'
		);
		
		// Create the post
		$post_id = wp_insert_post( $post );
		
		// Bail if error
		if ( is_wp_error( $post_id ) )
			return;
		
		// Set post_id
		$this->cart_id = $post_id;
		
		// Save the cart - most important
		$cart_content = maybe_serialize( WC()->session->cart );
		update_post_meta( $this->cart_id, '_cxecrt_cart_data', $cart_content );
		
		// ---------- Old Stuff ----------
		
		// Save the language that was set at the time of cart creation - may be useful in future
		$currentuser_lang = 'en';
		if ( function_exists( 'icl_register_string' ) ) $currentuser_lang = isset( $_SESSION['wpml_globalcart_language'] ) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
		update_post_meta( $this->cart_id, '_cxecrt_wpml_lang', $currentuser_lang );
		
		// Grab the cart products from WooCommerce then save them.
		// $saved_cart->set_products();
		// update_post_meta( $this->cart_id, '_cxecrt_cart_items', $this->cart_products );
		
		// ---------- Lastly ------------
		
		$this->load_saved_cart( $post_id );
	}

	public function add_titles_to_cart_items( $products ) {

		//Add titles to the attributes in the product array for searching on the index page
		$newProducts = array();
		foreach( $products as $product) {
			 $product['title'] = get_the_title( $product['product_id']);
			 $newProducts[] = $product;
		}
		return $newProducts;
	}
	
	public function get_cart_title() {
		
		$title = $this->cart_title;
		
		if ( "Guest's Cart" == $this->cart_title ) {
			// $title = '';
		}
		
		return $title;
	}
	
	public function get_cart_author_display() {
		
		$return = '';
		
		if ( $this->is_guest_order() ) {
			
			$return = __( 'Guest', 'email-cart' );
		}
		else {
			
			ob_start();
			?>
			<a title="<?php echo esc_attr( $this->cart_author_fullname ); ?>" href="<?php echo esc_url( get_edit_user_link( $this->cart_author_id ) ); ?>">
				<?php echo $this->cart_author_username; ?>
			</a>
			<?php
			$return = ob_get_clean();
		}
		
		return $return;
	}
	
}
?>