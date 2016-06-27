<?php
/*
Author: Feroz Jaffer
Author URI: http://www.bilogicit.com
Plugin Name: WooCommerce Role Price Manager
Plugin URI: http://www.bilogicit.com/woo-role-price-manager/
Description: WooCommerce Role Price Manager provides an easy way to establish different prices for each roles.
Version: 1.2
*/
if ( ! defined( 'ABSPATH' ) ) exit; 

// load the text domain
$plugin_dir = plugin_dir_url( __FILE__ );
load_plugin_textdomain('rpmanager', false, $plugin_dir.'languages');
add_action('admin_menu', 'rpmanager_menu',99);
add_action('admin_init', 'rpmanager_settings');
add_action('admin_init', 'rpmanager_admin_init' );

function rpmanager_admin_init() {
	    wp_register_script( 'rp-bootstrap-min', plugins_url('assets/bootstrap.min.js', __FILE__));	   
		wp_register_script( 'rp-chosen-js', plugins_url('assets/chosen.jquery.js', __FILE__));
		wp_enqueue_script( 'rp-bootstrap-min' );
		wp_enqueue_script( 'rp-chosen-js' );
    }

function load_custom_wp_admin_style() {
		wp_register_style( 'rp-bootstrap-css', plugins_url('assets/bootstrap.min.css', __FILE__));
		wp_register_style( 'rp-chosen-css', plugins_url('assets/chosen.css', __FILE__));
        wp_enqueue_style( 'rp-bootstrap-css' );
		wp_enqueue_style( 'rp-chosen-css' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );


function rpmanager_menu() {
	add_submenu_page( 'woocommerce', 'Role Price Manager', 'Role Price Manager', 'manage_options', 'manage-rpmanager', 'rpmanager_option' ); 
}

function rpmanager_settings() {	
	register_setting( 'rp_form_fields', 'rp_discount_price' );
	register_setting( 'rp_form_fields', 'rp_new_role' );
	register_setting( 'rp_form_fields', 'rp_roles' );
	register_setting( 'rp_form_fields', 'rp_new_role_del' );	
	register_setting( 'rp_form_fields', 'rp_new_role_submit' );
	register_setting( 'rp_round_field', 'rp_round_price' );
	
	add_option('myCategories', '');
	add_option('taxCountries', '');
	add_option('taxRoles', '');
	
	    global $wp_roles;
        $roles = $wp_roles->get_names();
        foreach($roles as $role) { 
        $rolev = str_replace(' ', '_', $role);
		register_setting( 'rp_form_fields', $rolev );
		}
}
function rpmanager_option() {
	include('rpmanager-options.php');
}

//////

function remove_tax_for_exempt( $cart ) {
    global $woocommerce, $current_user;
	$current_user = new WP_User(wp_get_current_user()->ID);
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	$user_role = str_replace('_',' ',$user_role);
	$user_role = ucwords($user_role);
	$myTaxesRole = get_option('taxRoles');

	$my_counties = get_option('taxCountries');
	$rp_country = $woocommerce->customer->get_country( );
	
	if (!empty($my_counties) && !empty($myTaxesRole))
	{
		if ( in_array ( $rp_country, $my_counties ) && in_array ( $user_role, $myTaxesRole ) )
		{
				$cart->remove_taxes();
		}
	}
    return $cart;
} 
add_action( 'woocommerce_calculate_totals', 'remove_tax_for_exempt' );

add_action( 'woocommerce_get_price_html' , 'rp_get_price',10,2);
function rp_get_price($price,$product){
	$current_user = new WP_User(wp_get_current_user()->ID);
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	$rp_percentage = get_option( $user_role );
	$rp_percentage = (empty($rp_percentage) ? 0 : $rp_percentage);
	$target_product_types = array('variable');
	$rp_apply_round = get_option( 'rp_round_price' );
	$reg_price = $product->price;
	
	global $post;
	$my_terms = get_option('myCategories');
	$terms = get_the_terms( $post->ID, 'product_cat');
	if ($terms) {
		if (!empty($my_terms)){
		foreach ($terms as $term) {
			$product_cat_name = $term->name;
	
					if ( in_array ( $product_cat_name, $my_terms ) )
					{
					$rp_percentage = 0;
					}
			}
		}
	}

	
	$rp_discount = $reg_price*$rp_percentage/100;
	$price = $reg_price - $rp_discount;
	$price = ($rp_apply_round==1 ? round($price) : $price);
	
if ( in_array ( $product->product_type, $target_product_types ) ) {
$cmin = $product->min_variation_price*$rp_percentage/100;
$cmax = $product->max_variation_price*$rp_percentage/100;
$ccmin = $product->min_variation_price-$cmin;
$ccmax = $product->max_variation_price-$cmax;

	if ($rp_apply_round==1)
	{
		return $ccmin==$ccmax ? woocommerce_price(round($ccmin)) : woocommerce_price(round($ccmin)) .' - '. woocommerce_price(round($ccmax));
				
	}
	else
	{
		return $ccmin==$ccmax ? woocommerce_price($ccmin) : woocommerce_price($ccmin) .' - '. woocommerce_price($ccmax);

	}
}        
return woocommerce_price($price);		
}	

add_action( 'woocommerce_before_calculate_totals', 'rp_add_cart_price' );
function rp_add_cart_price( $cart_object ) {
	$current_user = new WP_User(wp_get_current_user()->ID);
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	$rp_percentage = get_option( $user_role );
	$rp_percentage = (empty($rp_percentage) ? 0 : $rp_percentage);
	$rp_apply_round = get_option( 'rp_round_price' );

 		foreach ( $cart_object->cart_contents as $key => $value ) {
			// $rp_cart_price = get_post_meta( $value['data']->id, '_price', true );
			// NOTE(stas): We want the price with addons applied, not the base price so:
			$rp_cart_price = $value['data']->price;
			$rp_cart_pricev = get_post_meta( $value['data']->variation_id, '_price', true );

			// NOTE (stas): added rounding to corrected price
			if ($rp_cart_price){
				$rp_discount = $rp_cart_price*$rp_percentage/100;
				$price = $rp_cart_price - $rp_discount;	
				$price = ($rp_apply_round==1 ? round($price) : $price);
				$price = round($price, 2);
				$value['data']->price = $price;
			}
	
			if ($rp_cart_pricev){
				$rp_discount = $rp_cart_pricev*$rp_percentage/100;
				$price = $rp_cart_pricev - $rp_discount;
				$price = ($rp_apply_round==1 ? round($price) : $price);
				$price = round($price, 2);
				$value['data']->price = $price;
			}
} 
}

add_filter( 'woocommerce_cart_item_price', 'rp_mini_cart_prices', 10, 3);
function rp_mini_cart_prices( $product_price, $values, $cart_item) {
	
	global $woocommerce;	
	$current_user = new WP_User(wp_get_current_user()->ID);
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	$rp_percentage = get_option( $user_role );
	$rp_percentage = (empty($rp_percentage) ? 0 : $rp_percentage);
	$rp_apply_round = get_option( 'rp_round_price' );
	
	$rp_var_p = get_post_meta( $values['variation_id'], '_price', true );
	// $rp_simple_p = get_post_meta( $values['product_id'], '_price', true );
	$rp_simple_p = $value['data']->price;

			if ($rp_var_p == '')
			{
				$rp_discount = $rp_simple_p*$rp_percentage/100;
				$price = $rp_simple_p - $rp_discount;	
				$price = ($rp_apply_round==1 ? round($price) : $price);	
				$price = round($price, 2);
			return woocommerce_price($price);
			}
			 else 
			{
				$rp_discount = $rp_var_p*$rp_percentage/100;
				$price = $rp_var_p - $rp_discount;	
				$price = ($rp_apply_round==1 ? round($price) : $price);
				$price = round($price, 2);
			return woocommerce_price($price);	
			}
	
return $product_price;	
}

add_filter( 'woocommerce_available_variation', 'rp_dropdown_variation_price', 10, 3);
function rp_dropdown_variation_price( $data, $product, $variation ) {
	
	$current_user = new WP_User(wp_get_current_user()->ID);
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	$rp_percentage = get_option( $user_role );
	$rp_percentage = (empty($rp_percentage) ? 0 : $rp_percentage);
	$regularp = get_post_meta( $data['variation_id'], '_price', true );
	$rp_apply_round = get_option( 'rp_round_price' );
	
				$rp_discount = $regularp*$rp_percentage/100;
				$price = $regularp - $rp_discount;	
				$price = ($rp_apply_round==1 ? round($price) : $price);		
	$data['price_html'] = '<span class="price">'.woocommerce_price($price).'</span>';
	return $data;	
}
function rp_add_roles() {
	check_ajax_referer( "addroles" );
			$new_rp_role = $_POST['wrp_role'];
			$rp_role_val = str_replace(' ', '_', $new_rp_role);
			$rp_role_val = strtolower($rp_role_val);

		add_role($rp_role_val, $new_rp_role, array(
		'read' => true, 
		'edit_posts' => false,
		'delete_posts' => false, 
		));
		register_setting( 'rp_form_fields', $new_rp_role );
	?>

 
<table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">ROLE</th>
            <th style="color:#FFF;">DISCOUNT</th>
          </tr>
        </thead>
        <tbody>
        
        <?php
		global $wp_roles;
        $roles = $wp_roles->get_names();
        foreach($roles as $role) { 
        $rolev = str_replace(' ', '_', $role);
		?>        
        
          <tr>
            <td><?php echo $role; ?></td>
            <td><input type="text" name="<?php echo $rolev; ?>" value="<?php echo get_option( $rolev ); ?>"/></td>           
          </tr>  
          
          <?php
		}
		?> 
                        
<tr>
<td>
</td>
<td style="font-style:italic;">* Leave empty if no role discount should be applied.</td>
</tr>                 
        </tbody>
 </table> 
 <?php

	die();
}

function rp_del_roles() {
	check_ajax_referer( "delroles" );
			$rp_current_role = $_POST['wrp_role'];
			$rp_current_role = str_replace(' ', '_', $rp_current_role);
			$rp_current_role= strtolower($rp_current_role);
			remove_role( $rp_current_role );
	?>

 
<table class="table table-bordered rp_box_bg" style="width:90%;">
        <thead>
          <tr class="rp_box_head">
            <th style="color:#FFF;">ROLE</th>
            <th style="color:#FFF;">DISCOUNT</th>
          </tr>
        </thead>
        <tbody>
        
        <?php
		global $wp_roles;
        $roles = $wp_roles->get_names();
        foreach($roles as $role) { 
        $rolev = str_replace(' ', '_', $role);
		?>        
        
          <tr>
            <td><?php echo $role; ?></td>
            <td><input type="text" name="<?php echo $rolev; ?>" value="<?php echo get_option( $rolev ); ?>"/></td>           
          </tr>  
          
          <?php
		}
		?> 
                        
<tr>
<td>
</td>
<td style="font-style:italic;">* Leave empty if no role discount should be applied.</td>
</tr>                 
        </tbody>
 </table> 
 <?php

	die();
}
add_action( 'wp_ajax_addrolesrp', 'rp_add_roles' );
add_action( 'wp_ajax_delrolesrp', 'rp_del_roles' );
?>
