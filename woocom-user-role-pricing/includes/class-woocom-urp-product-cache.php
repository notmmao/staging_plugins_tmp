<?php

/**
 * Created by PhpStorm.
 * User: Stephen
 * Date: 8/27/2015
 * Time: 11:16 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woocom_URP_Product_Cache {

	private static function get_all_product_ids() {
		// gets all product ids, excluding children (variations), that are currently published
		global $wpdb;
		return $wpdb->get_col("SELECT ID FROM `$wpdb->posts` WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'");
	}

	public static function get_price_keys() {
		// Get all price keys except _wholesale_price - _regular_price is also included
		$options = get_option('woocom_urp_options');
		$prices = $options['prices'];
		// remove  _wholesale_price keys as that is handled by that plugin
		if(isset($prices['_wholesale_price'])) unset($prices['_wholesale_price']);
		return array_keys($prices); // return keys of any added prices
	}

	public static function product_has_price($product_id, $price_key) {
		$has_price = false;

		$product = wc_get_product($product_id);

		if(!$product) return false;
		if('bundle' === $product->product_type) return false;

		if( in_array($product->product_type, array( 'simple', 'variation', 'external' ) ) ) {
			$price = get_post_meta( $product_id, $price_key, true );
			if( '' !== $price ) {
				$has_price = true;
			}
		} elseif ('variable' == $product->product_type || 'grouped' == $product->product_type )	{
			// Only show if at least one variation has a wholesale price
			$children = $product->get_children($visible=false);
			if(empty($children)) return false;

			foreach ($children as $id) {
				$price = get_post_meta( $id, $price_key, true );
				if( '' !== $price ) {
					$has_price = true;
					break;
				}
			}
		}
		return apply_filters('woocom_urp_has_price', $has_price, $product_id, $price_key);
	}

	private static function update_no_price_products($price_key = false) {
		if(!$price_key) {
			$price_keys = self::get_price_keys();
		} else {
			$price_keys = array($price_key);
		}
		$product_ids = self::get_all_product_ids();

		foreach($price_keys as $key) {

			$exclude = array();

			foreach ($product_ids as $product_id) {
				if( !self::product_has_price($product_id, $key) ) {
					$exclude[] = $product_id;
				}
			}

			$option_name = 'woocom_urp_no' . sanitize_key($key) .'_products';

			update_option($option_name, $exclude);
		}

	}

	public static function get_no_price_products($price_key) {
		if('_wholesale_price' == $price_key) {
			$products = get_option('wcws_no_wholesale_price_products');
			if(false === $products && function_exists('woocom_wholesale_update_no_wholesale_price_products')) {
				woocom_wholesale_update_no_wholesale_price_products();
				$products = get_option('wcws_no_wholesale_price_products');
			}
		} elseif ( in_array($price_key, self::get_price_keys() )) {
			$option_name = 'woocom_urp_no' . sanitize_key($price_key) .'_products';
			$products = get_option($option_name);
			if(false === $products) {
				self::update_no_price_products($price_key);
				$products = get_option($option_name);
			}
		} else {
			$products = false;
		}

		return $products;
	}

	public static function update_product_cache($price_key = false) {
		self::update_no_price_products($price_key);
	}

	public static function cleanup($price_key = false) {

		if(!$price_key) return false;

		$custom_keys = self::get_price_keys();
		if(isset($custom_keys['_regular_price'])) unset($custom_keys['_regular_price']);

		if(!in_array($price_key, $custom_keys)) return false;

		// Delete the no price option
		$option_name = 'woocom_urp_no' . sanitize_key($price_key) .'_products';
		delete_option($option_name);

		// delete prices from postmeta table
		global $wpdb;
		$table = $wpdb->postmeta;
		$where = array('meta_key' => $price_key);
		$where_format = '%s';
		$result = $wpdb->delete( $table, $where, $where_format );

		return (false !== $result);
	}

}