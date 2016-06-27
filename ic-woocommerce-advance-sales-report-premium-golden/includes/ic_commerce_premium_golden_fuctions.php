<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'IC_Commerce_Premium_Golden_Fuctions' ) ) {
	class IC_Commerce_Premium_Golden_Fuctions{
		
		public $firstorderdate = NULL;
		
		public $constants 	=	array();
		
		public function __construct($constants) {
			global $options;						$this->constants		= $constants;
			$options				= $this->constants['plugin_options'];
			//$this->constants['price_format_count'] = 0;
		}
		
		function get_number_only($value, $default = 0){
			global $options;
			$per_page = (isset($options[$value]) and strlen($options[$value]) > 0)? $options[$value] : $default;
			$per_page = is_numeric($per_page) ? $per_page : $default;
			return $per_page;
		}
		function ic_cr_get_country_name($country_code){			
			$country      = $this->get_wc_countries();//Added 20150225					
			return $country->countries[$country_code];
		}
		
		function first_order_date(){
			if(!isset($this->constants['first_order_date'])){
				
				if(!defined("IC_WOOCOMMERCE_FIRST_ORDER_DATE")){
					
					if(!isset($_REQUEST['first_order_date'])){
						global $wpdb;					
						$sql = "SELECT DATE_FORMAT(posts.post_date, '%Y-%m-%d') AS 'OrderDate' FROM {$wpdb->prefix}posts  AS posts	WHERE posts.post_type='shop_order' Order By posts.post_date ASC LIMIT 1";
						
						$this->constants['first_order_date'] 	= $wpdb->get_var($sql);
						
						$_REQUEST['first_order_date']			= $this->constants['first_order_date'];
						
					}else{
						$this->constants['first_order_date'] = $_REQUEST['first_order_date'];
					}
					
					define("IC_WOOCOMMERCE_FIRST_ORDER_DATE", $this->constants['first_order_date']);
					
				}else{
					
					$this->constants['first_order_date'] = IC_WOOCOMMERCE_FIRST_ORDER_DATE;
					
				}
			}
			
			return $this->constants['first_order_date'];
		}
		
		
		function get_total_shop_day($key = NULL){
			 $now = time(); // or your date as well
			//$this->first_order_date();
			$first_date = strtotime(($this->first_order_date($key)));
			$datediff = $now - $first_date;
			$total_shop_day = floor($datediff/(60*60*24));
			return $total_shop_day;
		}
		
		function get_date_diffrence($start_date, $end_date){
			$now = time(); // or your date as well			
			$start_date 	= strtotime($start_date);
			$end_date 		= strtotime($end_date);
			$datediff 		= $end_date - $start_date;
			$days 			= floor($datediff/(60*60*24));
			return $days;
		}
		
		function price($vlaue, $args = array()){
			
			$currency        = isset( $args['currency'] ) ? $args['currency'] : '';
			
			if (!$currency ) {
				if(!isset($this->constants['woocommerce_currency'])){
					$this->constants['woocommerce_currency'] =  $currency = (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : "USD");
				}else{
					$currency  = $this->constants['woocommerce_currency'];
				}
			}
			
			$args['currency'] 	= $currency;
			$vlaue 				= trim($vlaue);
			$withoutdecimal 	= str_replace(".","d",$vlaue);
						
			if(!isset($this->constants['price_format'][$currency][$withoutdecimal])){
				if(!function_exists('woocommerce_price')){
					if(!isset($this->constants['currency_symbol'])){
						$this->constants['currency_symbol'] =  $currency_symbol 	= apply_filters( 'ic_commerce_currency_symbol', '&#36;', 'USD');
					}else{
						$currency_symbol  = $this->constants['currency_symbol'];
					}					
					$vlaue				= strlen(trim($vlaue)) > 0 ? $vlaue : 0;
					$v 					= $currency_symbol."".number_format($vlaue, 2, '.', ' ');
					$v					= "<span class=\"amount\">{$v}</span>";
					
				}else{
					$v = woocommerce_price($vlaue, $args);					
				}
				$this->constants['price_format'][$currency][$withoutdecimal] = $v;
			}else{
				$v = $this->constants['price_format'][$currency][$withoutdecimal];				
			}
			
			
			return $v;
		}
		
		function woocommerce_currency(){
			if(!isset($this->constants['woocommerce_currency'])){
				$this->constants['woocommerce_currency'] =  $currency = (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : "USD");
			}else{
				$currency  = $this->constants['woocommerce_currency'];
			}			
			return $currency;
		}
		
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest2 = array();
					foreach($newRequest as $akey => $avalue):
						$newRequest2[] = is_array($avalue) ? implode(",", $avalue) : $avalue;
					endforeach;
					$newRequest = implode(",", $newRequest2);					
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		function create_dropdown($data = NULL, $name = "",$id='', $show_option_none="Select One", $class='', $default ="-1", $type = "array", $multiple = false, $size = 0, $d = "-1", $display = true){
			$count 				= count($data);
			$dropdown_multiple 	= '';
			$dropdown_size 		= '';
			
			$selected =  explode(",",$default);
			
			if($count<=0) return '';
			
			if($multiple == true and $size >= 0){
				//$this->print_array($data);
				
				if($count < $size) $size = $count + 1;
				$dropdown_multiple 	= ' multiple="multiple"';
				//echo $count;
				$dropdown_size 		= ' size="'.$size.'"  data-size="'.$size.'"';
			}
			$output = "";
			$output .= '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$dropdown_multiple.$dropdown_size.'>';
			
			//if(!$dropdown_multiple)
			
			//$output .= '<option value="-1">'.$show_option_none.'</option>';
			
			if($show_option_none){
				if($default == "all"){
					$output .= '<option value="'.$d.'" selected="selected">'.$show_option_none.'</option>';
				}else{
					$output .= '<option value="'.$d.'">'.$show_option_none.'</option>';
				}
			}
			
			if($type == "object"){
				foreach($data as $key => $value):
					$s = '';
					
					if(in_array($value->id,$selected)) $s = ' selected="selected"';					
					//if($value->id == $default ) $s = ' selected="selected"';
					
					$c = (isset($value->counts) and $value->counts > 0) ? " (".$value->counts.")" : '';
					
					$output .= "\n<option value=\"".$value->id."\"{$s}>".$value->label.$c."</option>";
				endforeach;
			}else if($type == "array"){
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}else{
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}
						
			$output .= '</select>';
			if($display){
				echo $output;
			}else{
				return  $output;
			}
		
		}
		
		function get_product_data($product_type = 'all'){
				
				global $wpdb;
				$category_id			= $this->get_request('category_id','-1');				
				$taxonomy				= $this->get_request_default('taxonomy','product_cat');				
				$purchased_product_id	= $this->get_request_default('purchased_product_id','-1');						
				$publish_order			= 'no';
				$transaction_products 	= $this->get_setting('transaction_products',$this->constants['plugin_options'], "yes");
				$product_status 		= $this->get_setting('product_status',$this->constants['plugin_options'], array());
				
				if($transaction_products == "yes"){
					
					if(count($product_status)>0){
						$sql = "SELECT woocommerce_order_itemmeta.meta_value AS id, products.post_title AS label ";
					}else{
						$sql = "SELECT woocommerce_order_itemmeta.meta_value AS id, woocommerce_order_items.order_item_name AS label ";
					}
					
					
				
					$sql .= "
					FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items				
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
					
					if($category_id != "-1" && $category_id >= 0){
						$sql .= " 
								LEFT JOIN {$wpdb->prefix}term_relationships		AS term_relationships		ON term_relationships.object_id				= woocommerce_order_itemmeta.meta_value
								LEFT JOIN {$wpdb->prefix}term_taxonomy			AS term_taxonomy			ON term_taxonomy.term_taxonomy_id			= term_relationships.term_taxonomy_id
								LEFT JOIN {$wpdb->prefix}terms					AS terms					ON terms.term_id							= term_taxonomy.term_id";
					}
					
					if($product_type == 1)
						$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id_order_itemmeta ON variation_id_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
					
					if($product_type == 2 || ($product_type == 'grouped' || $product_type == 'external' || $product_type == 'simple' || $product_type == 'variable_')){
						$sql .= " 	
								LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships_product_type 	ON term_relationships_product_type.object_id		=	woocommerce_order_itemmeta.meta_value 
								LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy_product_type 		ON term_taxonomy_product_type.term_taxonomy_id		=	term_relationships_product_type.term_taxonomy_id
								LEFT JOIN  {$wpdb->prefix}terms 				as terms_product_type 				ON terms_product_type.term_id						=	term_taxonomy_product_type.term_id";
					}
					
					if($publish_order == "yes")	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";
					
					if(count($product_status)>0){
						$sql .= " LEFT JOIN {$wpdb->prefix}posts AS products ON products.ID = woocommerce_order_itemmeta.meta_value";
					}
					
					$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id'";
					
					if($category_id != "-1" && $category_id >= 0){
						$sql .= " AND term_taxonomy.taxonomy = 'product_cat'";
					}
					
					if($product_type == 1)
						$sql .= " AND variation_id_order_itemmeta.meta_key = '_variation_id' AND (variation_id_order_itemmeta.meta_value IS NOT NULL AND variation_id_order_itemmeta.meta_value > 0)";
					
					if($category_id != "-1" && $category_id >= 0)
						$sql .= " AND terms .term_id IN(".$category_id.")";
					
					if($publish_order == 'yes')	$sql .= " AND posts.post_status = 'publish'";
					
					if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
					
					if($product_type == 'grouped' || $product_type == 'external' || $product_type == 'simple' || $product_type == 'variable_'){
						$sql .= " AND terms_product_type.name IN ('{$product_type}')";
					}
					
					if(count($product_status)>0){
						$in_product_status = implode("','",$product_status);
						$sql .= " AND products.post_type IN ('product')";
						$sql .= " AND products.post_status IN ('{$in_product_status}')";
					}
					
					$sql .= " GROUP BY woocommerce_order_itemmeta.meta_value ";
					
					$sql .= " ORDER BY label ASC, woocommerce_order_items.order_item_id DESC";
					
					//echo $sql;
					
					$products = $wpdb->get_results($sql);
				}else{
					
					$sql = "SELECT posts.ID AS id, posts.post_title AS label 
				
					FROM `{$wpdb->prefix}posts` AS posts";
					$sql .= " WHERE 1*1";
					
					$sql .= " AND posts.post_type IN ('product')";
					
					if(count($product_status)>0){
						$in_product_status = implode("','",$product_status);
						$sql .= " AND products.post_status IN ('{$in_product_status}')";
					}
					
					$sql .= " ORDER BY posts.post_title ASC";
					
					$products = $wpdb->get_results($sql);
				}
				
				
				//echo mysql_error();
			
				return $products;
		}
		
		function get_product_data2($post_type = 'product', $post_status = 'no'){
				global $wpdb;
			$category_id			= $this->get_request('category_id','-1');
			
			if($post_status == "yes") $post_status == 'publish';
			if($post_status == "publish") $post_status == 'publish';
			$publish_order			= $this->get_request_default('publish_order',$post_status,true);//if publish display publish order only, no or null display all order
			
			$sql = "SELECT *, posts.ID AS id, posts.post_title AS label FROM `{$wpdb->prefix}posts` AS posts";
			
			if($category_id != "-1" && $category_id >= 0){
				$sql .= " LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = posts.ID
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
			}
			$sql .= " WHERE posts.post_type = '{$post_type}'";
			
			if($category_id != "-1" && $category_id >= 0) $sql .= " AND terms .term_id 		IN(".$category_id.")";
			
			if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
			
			$sql .= " GROUP BY posts.ID ORDER BY posts.post_title";
			
			$products = $wpdb->get_results($sql);
			
			//$this->print_array($products);
			
			return $products;
		}
		
		function get_category_data($taxonomy = 'product_cat', $post_status = 'no'){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				
				$hide_order_status = $this->get_request_default('hide_order_status','-1',true);
				
				if($post_status == "yes") $post_status == 'publish';
				
				$sql = "SELECT terms.term_id AS id, terms.name AS label
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
				
				LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = woocommerce_order_itemmeta.meta_value
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
				
				if($post_status == 'publish' || $post_status == 'trash' || ($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'"))	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id' 
				AND term_taxonomy.taxonomy = '{$taxonomy}'";
				
				
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN ('".$hide_order_status."')";
				
				$sql .= " GROUP BY terms.term_id
				ORDER BY terms.name ASC";
			
				
				$products_category = $wpdb->get_results($sql);
				
				return $products_category; 
		}
		
		
		function get_category_data2($taxonomy = 'product_cat',$post_status = 'no', $count = true){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				if($post_status == "yes") $post_status == 'publish';
				
				$sql = "SELECT 
				terms.term_id AS id, terms.name AS label";
				
				if($count)
					$sql .= ", count(posts.ID) AS counts";
				
				$sql .= " FROM `{$wpdb->prefix}posts` AS posts				
				LEFT JOIN {$wpdb->prefix}term_relationships AS term_relationships ON term_relationships.object_id = posts.ID
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}terms AS terms ON terms.term_id = term_taxonomy.term_id";
				
				$sql .= " WHERE term_taxonomy.taxonomy = '{$taxonomy}'";				
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				$sql .= " 
				GROUP BY terms.term_id
				ORDER BY terms.name ASC";
				
				$products_category = $wpdb->get_results($sql);
				return $products_category; 
		}
		
		
		
		function get_order_customer($post_type = 'shop_order',$post_status = 'no'){
				global $wpdb;
				
				$post_status = $this->get_request_default('post_status',$post_status,true);
				if($post_status == "yes") $post_status == 'publish';
				
				
				$sql = "SELECT billing_email.meta_value AS id, concat(billing_first_name.meta_value, ' ',billing_last_name.meta_value) AS label, COUNT(billing_email.meta_value) AS counts FROM `{$wpdb->prefix}posts` AS posts
					LEFT JOIN  {$wpdb->prefix}postmeta as customer_user ON customer_user.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_first_name ON billing_first_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_last_name ON billing_last_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}postmeta as billing_email ON billing_email.post_id=posts.ID
				";
				$sql .= " WHERE 
					post_type='{$post_type}' 
				AND customer_user.meta_key = '_customer_user'
				AND billing_first_name.meta_key = '_billing_first_name'
				AND billing_last_name.meta_key = '_billing_last_name'
				AND billing_email.meta_key = '_billing_email'
				";
				if($post_status == 'publish' || $post_status == 'trash')	$sql .= " AND posts.post_status = '".$post_status."'";
				
				$sql .= " 
				GROUP BY billing_email.meta_value
				ORDER BY label  ASC";
				
				$products_category = $wpdb->get_results($sql);
				return $products_category; 
		}
		
		
		
		function get_order_username_list()
		{
			global $wpdb,$sql;
			$sql="SELECT users.user_email AS label
					,customer_user.post_author AS id 
					FROM `{$wpdb->prefix}posts` AS  customer_user
					LEFT JOIN  `{$wpdb->prefix}users` AS  users ON users.ID = customer_user.post_author
					LEFT JOIN  {$wpdb->prefix}usermeta as usermeta ON usermeta.user_id=users.ID
					WHERE customer_user.post_type ='shop_order' AND post_status='publish'
						AND  usermeta.meta_value =9		
				";
				$sql .= " 
				GROUP BY id
				ORDER BY label  ASC";
			$products_category = $wpdb->get_results($sql);
			return $products_category; 		
		
		}
		
		function get_paying_country($code = "_billing_country"){
			global $wpdb;
			
			$country      	= $this->get_wc_countries();//Added 20150225
			
			$sql = "SELECT 
			postmeta.meta_value AS 'id'
			,postmeta.meta_value AS 'label'
			
			FROM {$wpdb->prefix}postmeta as postmeta
			WHERE postmeta.meta_key='{$code}'
			GROUP BY postmeta.meta_value
			ORDER BY postmeta.meta_value ASC";
			$results = $wpdb->get_results($sql);
			
			foreach($results as $key => $value):
					$results[$key]->label = isset($country->countries[$value->label]) ? $country->countries[$value->label]: $value->label;
			endforeach;
			
			return $results;
		}
		
		function get_payment_method_name($payment_method = NULL){
			return $payment_method;
		}
		
		function get_custom_field_data($order_item = NULL, $meta_key = NULL, $default = NULL ){
			global $ic_commerce_premium_golden_custom_fields;
			return $ic_commerce_premium_golden_custom_fields->get_custom_field_data($order_item,$meta_key,$default);
		}
		
		function get_all_post_meta($order_id,$is_product = false){
			$order_meta	= get_post_meta($order_id);
			
			$order_meta_new = array();
			if($is_product){
				foreach($order_meta as $omkey => $omvalue){
					$order_meta_new[$omkey] = $omvalue[0];
				}
			}else{
				foreach($order_meta as $omkey => $omvalue){
					$omkey = ltrim($omkey, "_");
					$order_meta_new[$omkey] = $omvalue[0];
				}
			}
			return $order_meta_new;
		}
		
		function emailLlink($e, $display = true){
			$return = '<a href="mailto:'.$e.'">'.$e.'</a>';
			if($display)
				echo $return;
			else
				return $return;
		}
		
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
		//New Change ID 20140918
		function print_sql($string){			
			
			$string = str_replace("\t", "",$string);
			$string = str_replace("\r\n", "",$string);
			$string = str_replace("\n", "",$string);
			
			$string = str_replace("SELECT ", "\n\tSELECT \n\t",$string);
			//$string = str_replace(",", "\n\t,",$string);
			
			$string = str_replace("FROM", "\n\nFROM",$string);
			$string = str_replace("LEFT", "\n\tLEFT",$string);
			
			$string = str_replace("AND", "\r\n\tAND",$string);			
			$string = str_replace("WHERE", "\n\nWHERE",$string);
			
			$string = str_replace("LIMIT", "\nLIMIT",$string);
			$string = str_replace("ORDER", "\nORDER",$string);
			$string = str_replace("GROUP", "\nGROUP",$string);
			
			$new_str = "<pre>";
				$new_str .= $string;
			$new_str .= "</pre>";
			
			echo $new_str;
		}
		
		function get_request_default($name, $default='', $set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = trim($_REQUEST[$name]);
				return $newRequest;
			}else{
				if($set) $_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		
		function get_post_meta($post_id, $key, $key_prefix = "", $single = true ){
			return get_post_meta($post_id, $key_prefix.$key, $single);
		}
		
		function export_to_pdf($export_rows = array(),$output){
			if(count($export_rows)>0){
				
				$export_file_name 		= $this->get_request('export_file_name',"no");
				
				$today 					= date_i18n("Y-m-d-H-i-s");
				
				$export_file_format 	= 'pdf';
				
				$report_name 			= $this->get_request('report_name','');	
							
				if(strlen($report_name)> 0){
					$report_name 			= str_replace("_page","_list",$report_name);
					$report_name 			= $report_name."-";
				}
				
				$file_name 				= $export_file_name."-".$report_name.$today.".".$export_file_format;
				
				$file_name 				= str_replace("_","-",$file_name);
				
				$orientation_pdf 		= $this->get_request('orientation_pdf',"portrait");
				
				$paper_size 			= $this->get_request('paper_size',"letter");
				
				$this->unset_class_variables();
				
				$GLOBALS 				= NULL;unset($GLOBALS);unset($this);
				
				
				require_once("ic_commerce_premium_golden_dompdf_config.inc.php");
				$dompdf = new DOMPDF();	
				$dompdf->set_paper($paper_size,$orientation_pdf);
				$dompdf->load_html($output,"utf-8");
				$dompdf->render();
				$dompdf->stream($file_name);				
			}
		}
		
		function get_order_item_variation_sku($order_item_id = 0){
			global $wpdb;
			$sql = "
			SELECT 
			postmeta_sku.meta_value AS variation_sku				
			FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value
			WHERE woocommerce_order_items.order_item_id={$order_item_id}
			
			AND woocommerce_order_items.order_item_type = 'line_item'
			AND woocommerce_order_itemmeta.meta_key = '_variation_id'
			AND postmeta_sku.meta_key = '_sku'
			";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		function get_order_product_sku($product_id = 0){
			global $wpdb;
			$sql = "SELECT postmeta_sku.meta_value AS product_sku
			FROM {$wpdb->prefix}postmeta as postmeta_sku			
			WHERE postmeta_sku.meta_key = '_sku'";
			
			//Added Start 20150209
			if(strlen($product_id) >= 0 and  $product_id > 0)
				$sql .= " and postmeta_sku.post_id = {$product_id}";
				
			if(strlen($product_id) >= 0 and  $product_id > 0){
				$orderitems = $wpdb->get_var($sql);
				if(strlen($wpdb->last_error) > 0){
					echo $wpdb->last_error;
				}
			}else
				$orderitems = '';
			//Added Start 20150209
			return $orderitems;
		}
		
		function get_sku($order_item_id, $product_id){
			$td_value = $this->get_order_item_variation_sku($order_item_id);
			$td_value = strlen($td_value) > 0 ? $td_value : $this->get_order_product_sku($product_id);
			$td_value = strlen($td_value) > 0 ? $td_value : 'Not Set';
			return $td_value;
		}
		
		function get_order_item_variation_stock($order_item_id = 0){
			global $wpdb;
			$sql = "
			SELECT 
			postmeta_sku.meta_value AS variation_sku				
			FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
			LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value
			WHERE woocommerce_order_items.order_item_id={$order_item_id}
			
			AND woocommerce_order_items.order_item_type = 'line_item'
			AND woocommerce_order_itemmeta.meta_key = '_variation_id'
			AND postmeta_sku.meta_key = '_stock'
			";
			return $orderitems = $wpdb->get_var($sql);
		}
		
		function get_order_product_stock($product_id = 0){
			global $wpdb;
			$sql = "SELECT postmeta_stock.meta_value AS product_sku
			FROM {$wpdb->prefix}postmeta as postmeta_stock			
			WHERE postmeta_stock.meta_key = '_stock'";
			
			//Added Start 20150209
			if(strlen($product_id) >= 0 and  $product_id > 0)
				$sql .= " and postmeta_stock.post_id = {$product_id}";
				
			if(strlen($product_id) >= 0 and  $product_id > 0){
				$orderitems = $wpdb->get_var($sql);
				if(strlen($wpdb->last_error) > 0){
					echo $wpdb->last_error;
				}
			}else
				$orderitems = '';
			//Added Start 20150209
			
			return $orderitems;
			
			//return $orderitems = $wpdb->get_var($sql);
		}
		
		
		function get_stock_($order_item_id, $product_id){
			$td_value = $this->get_order_item_variation_stock($order_item_id);
			$td_value = strlen($td_value) > 0 ? $td_value : $this->get_order_product_stock($product_id);
			$td_value = strlen($td_value) > 0 ? $td_value : 'Not Set';
			return $td_value;
		}
		
		function get_product_category(){
				
				global $wpdb;
				$product_status 		= $this->get_setting('product_status',$this->constants['plugin_options'], array());
				$sql = "
				SELECT 
				woocommerce_order_itemmeta.meta_value 		AS id, 				
				term_taxonomy.term_id 						AS parent_id,
				CONCAT(term_taxonomy.term_id,'-',woocommerce_order_itemmeta.meta_value) AS category_product_id,
				terms.name 						AS name";
				
				if(count($product_status)>0){
					$sql = " ,products.post_title 	AS label";					
				}else{
					$sql .= " ,woocommerce_order_items.order_item_name 	AS label";
				}
				
				$sql .= " 
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items				
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
				
				$sql .= " 
						LEFT JOIN {$wpdb->prefix}term_relationships		AS term_relationships		ON term_relationships.object_id				= woocommerce_order_itemmeta.meta_value
						LEFT JOIN {$wpdb->prefix}term_taxonomy			AS term_taxonomy			ON term_taxonomy.term_taxonomy_id			= term_relationships.term_taxonomy_id
						LEFT JOIN {$wpdb->prefix}terms					AS terms					ON terms.term_id							= term_taxonomy.term_id";
				
				//if($publish_order == "yes")	$sql .= " LEFT JOIN {$wpdb->prefix}posts AS posts ON posts.ID = woocommerce_order_items.order_id";				
				
				if(count($product_status)>0){
					$sql .= " LEFT JOIN {$wpdb->prefix}posts AS products ON products.ID = woocommerce_order_itemmeta.meta_value";
				}
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id'";
				$sql .= " AND term_taxonomy.taxonomy = 'product_cat'";
				
				//if($publish_order == 'yes')	$sql .= " AND posts.post_status = 'publish'";				
				//if($publish_order == 'publish' || $publish_order == 'trash')	$sql .= " AND posts.post_status = '".$publish_order."'";
				
				if(count($product_status)>0){
					$in_product_status = implode("','",$product_status);
					$sql .= " AND products.post_type IN ('product')";
					$sql .= " AND products.post_status IN ('{$in_product_status}')";
				}
				
				$sql .= " GROUP BY category_product_id ORDER BY woocommerce_order_items.order_item_name ASC";
			
				$products = $wpdb->get_results($sql);
			
				return $products;
		}
		
		//New Change ID 20140918
		function get_set_status_ids(){
				if(isset($this->constants['shop_order_status'])){
					$stauts_ids = $this->constants['shop_order_status'];
				}else{
					
					if($this->constants['post_order_status_found'] == 0 ){
					
						$stauts_ids = $this->get_setting('shop_order_status',$this->constants['plugin_options'],false);
						//$this->print_array($stauts_ids);
						//echo "test";
						if(!$stauts_ids){
							
							$detault_stauts_slug	= (isset($this->constants['detault_stauts_slug']) and count($this->constants['detault_stauts_slug'])>0) ? $this->constants['detault_stauts_slug'] : array();
							
							if(count($detault_stauts_slug)>0){
								$detault_stauts_id		= array();
								//$detault_stauts_slug 	= array_merge(array('completed'), (array)$detault_stauts_slug);
								
								$new_shop_order_status 	= array();
								$shop_order_status 		= $this->shop_order_status($detault_stauts_slug);
								foreach($shop_order_status as $key => $value){
									$new_shop_order_status[$value->id] = ucfirst($value->label);				
									if(in_array($value->label,$detault_stauts_slug)){
										$detault_stauts_id[]= $value->id;
									}
								}
								
								$stauts_ids = $detault_stauts_id;
							}else{
								$stauts_ids = $detault_stauts_slug;
							}
						}else{
							$stauts_ids = $stauts_ids;
						
						}
					}else if($this->constants['post_order_status_found'] == 1 ){
						$stauts_ids = $this->get_setting('post_order_status',$this->constants['plugin_options'],false);
						if(!$stauts_ids){
							$detault_order_status	= (isset($this->constants['detault_order_status']) and count($this->constants['detault_order_status'])>0) ? $this->constants['detault_order_status'] : array();
							$stauts_ids = $detault_order_status;	
						}
					}
					
					if(isset($stauts_ids[0]) and $stauts_ids[0] == 'all') unset($stauts_ids[0]);
				}
				$this->constants['shop_order_status']	=	$stauts_ids;				
				return $stauts_ids;
			}
			
			//New Change ID 20140918
			function shop_order_status($shop_order_status = array()){
				global $wpdb;
				
				$sql = "SELECT terms.term_id AS id, terms.name AS label, terms.slug AS slug
				FROM {$wpdb->prefix}terms as terms				
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_taxonomy.term_id = terms.term_id
				WHERE term_taxonomy.taxonomy = 'shop_order_status'";
				
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode("', '",$shop_order_status);
					$sql .= "	AND terms.slug IN ('{$in_shop_order_status}')";
				}
				
				$sql .= "
				GROUP BY terms.term_id
				ORDER BY terms.name ASC";
				
				$shop_order_status = $wpdb->get_results($sql);
				
				return $shop_order_status;
			}//END shop_order_status
			
			//New Change ID 20140918
			function ic_get_order_statuses_slug_id(){
				return $this->shop_order_status();
			}
			
			//New Change ID 20140918
			function get_value($data = NULL, $id, $default = ''){
				if($data){
					if($data->$id)
						return $data->$id;
				}
				return $default;
			}
			
			//New Change ID 20140918
			function get_setting($id, $data, $defalut = NULL){
				if(isset($data[$id]))
					return $data[$id];
				else
					return $defalut;
			}
			
			//New Change ID 20140918
			function get_setting2($id, $data, $defalut = NULL){
				if(isset($data[$id]))
					return array($data[$id]);
				else
					return $defalut;
			}
			
			//New Change ID 20140918
			function get_post_order_status($key = NULL){
				$sql = "SELECT DATE_FORMAT(posts.post_date, '%Y-%m-%d') AS 'OrderDate' FROM {$wpdb->prefix}posts  AS posts	WHERE posts.post_type='shop_order' Order By posts.post_date ASC LIMIT 1";
				return $this->firstorderdate = $wpdb->get_var($sql);
				
				global $wpdb;
			}
			
			//New Change ID 20140918
			function ic_get_order_statuses(){
				if(!isset($this->constants['wc_order_statuses'])){
					if(function_exists('wc_get_order_statuses')){
						$order_statuses = wc_get_order_statuses();						
					}else{
						$order_statuses = array();
					}
					
					$order_statuses['trash']	=	"Trash";
										
					$this->constants['wc_order_statuses'] = $order_statuses;
				}else{
					$order_statuses = $this->constants['wc_order_statuses'];
				}
				return $order_statuses;
			}
			
			//New Change ID 20140918
			function ic_get_order_status($order_item){
				if(!isset($this->constants['wc_order_statuses'])){
					$order_statuses = $this->ic_get_order_statuses();
				}else{
					$order_statuses = $this->constants['wc_order_statuses'];
				}
				
				$order_status = isset($order_item->order_status) ? $order_item->order_status : '';
				$order_status = isset($order_statuses[$order_status]) ? $order_statuses[$order_status] : $order_status;
				return $order_status;
			}
			
			//New Change ID 20140918
			function get_post_order_status2(){
				global $wpdb;
				
				$sql = " SELECT post_status as id, post_status as label, post_status as order_status  FROM {$wpdb->prefix}posts WHERE  post_type IN ('shop_order') AND post_status NOT IN ('auto-draft','inherit','publish') GROUP BY post_status ORDER BY post_status";				
				$order_items = $wpdb->get_results($sql);
				
				$order_statuses = $this->ic_get_order_statuses();
				$trash_label = "";
				$trash_id 	= "";
				$order_statuses_found = array();
				if(count($order_statuses)>0){
					foreach ( $order_items as $key => $order_item ) {
						if($order_item->order_status == "trash"){
							$trash_label 	= isset($order_statuses[$order_item->order_status]) ? $order_statuses[$order_item->order_status] : '';						
						}else{
							$order_statuses_found[$order_item->id] 	= isset($order_statuses[$order_item->order_status]) ? $order_statuses[$order_item->order_status] : '';
						}
					}
										
					if($trash_label){
						if(!in_array('trash',$this->constants['hide_order_status'])){
							$order_statuses_found['trash'] 	= $trash_label;
						}
					}
				}
				
				
				return $order_statuses_found;
			}
			
			function humanTiming ($time, $current_time = NULL, $suffix = ''){
				if($time){
					if($current_time == NULL)
						$time = time() - $time; // to get the time since that moment
					else
						$time = $current_time - $time; // to get the time since that moment
				
					$tokens = array (
						31536000 => 'year',
						2592000 => 'month',
						604800 => 'week',
						86400 => 'day',
						3600 => 'hour',
						60 => 'minute',
						1 => 'second'
					);
				
					foreach ($tokens as $unit => $text) {
						if ($time < $unit) continue;
						$numberOfUnits = floor($time / $unit);
						return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'') .$suffix;
					}
				}else{
					return 0;
				}		
			}
			
			function get_woocommerce_currency_symbol_pdf( $currency = '' ) {				
				add_filter('woocommerce_currency_symbol', array($this, 'get_woocommerce_currency_symbol'),10,2);
			}
			
			function get_woocommerce_currency_symbol( $currency_symbol = '', $currency = '' ) {				
				$new_currency_symbol = "";				
				switch ( $currency ) {
					case 'AED' : $currency_symbol = $new_currency_symbol; break;
					case 'BDT' : $currency_symbol = $new_currency_symbol; break;
					case 'BRL' : $currency_symbol = $new_currency_symbol; break;
					case 'BGN' : $currency_symbol = $new_currency_symbol; break;						
					case 'RUB' : $currency_symbol = $new_currency_symbol; break;
					case 'KRW' : $currency_symbol = $new_currency_symbol; break;
					case 'TRY' : $currency_symbol = $new_currency_symbol; break;
					case 'NOK' : $currency_symbol = $new_currency_symbol; break;
					case 'ZAR' : $currency_symbol = $new_currency_symbol; break;
					case 'CZK' : $currency_symbol = $new_currency_symbol; break;
					case 'MYR' : $currency_symbol = $new_currency_symbol; break;
					case 'HUF' : $currency_symbol = $new_currency_symbol; break;
					case 'ILS' : $currency_symbol = $new_currency_symbol; break;
					case 'PHP' : $currency_symbol = $new_currency_symbol; break;
					case 'PLN' : $currency_symbol = $new_currency_symbol; break;
					case 'SEK' : $currency_symbol = $new_currency_symbol; break;
					case 'CHF' : $currency_symbol = $new_currency_symbol; break;
					case 'TWD' : $currency_symbol = $new_currency_symbol; break;
					case 'THB' : $currency_symbol = $new_currency_symbol; break;
					case 'VND' : $currency_symbol = $new_currency_symbol; break;
					case 'NGN' : $currency_symbol = $new_currency_symbol; break;
					default    : $currency_symbol = $currency_symbol; break;
				}
				return $currency_symbol;
			}
			
			//New Change ID 20141010
			function get_variation_values($variation_attributes = NULL, $all_attributes = NULL){
				global $wpdb;
				//
					$sql = "
					SELECT
					postmeta_variation.meta_value AS variation 
					,postmeta_variation.meta_key AS attribute
					FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
					LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
					LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_variation ON postmeta_variation.post_id = woocommerce_order_itemmeta.meta_value";
					
					$var = array();
					if($variation_attributes != NULL and $variation_attributes != '-1' and strlen($variation_attributes) > 0){
						$variations = explode(",",$variation_attributes);
						foreach($variations as $key => $value):
							$var[] .=  "attribute_pa_".$value;
							$var[] .=  "attribute_".$value;
						endforeach;
						$variation_attributes =  implode("', '",$var);
					}
					$sql .= "
					
					WHERE 
					
					woocommerce_order_items.order_item_type = 'line_item'
					AND woocommerce_order_itemmeta.meta_key = '_variation_id'
					AND postmeta_variation.meta_key like 'attribute_%'";
					
					if($variation_attributes != NULL and $variation_attributes != "-1" and strlen($variation_attributes)>1)
						$sql .= " AND postmeta_variation.meta_key IN ('{$variation_attributes}')";
					else				
						$sql .= " AND postmeta_variation.meta_key like 'attribute_%'";
					
					
					/*if($variation_attributes != NULL and $variation_attributes != "-1" and strlen($variation_attributes)>1)
						$sql .= " AND postmeta_variation.meta_key IN ('{$variation_attributes}')";
					else				
						$sql .= " AND postmeta_variation.meta_key like 'attribute_%'";*/
					
					/*	
					
					*/
					$items = $wpdb->get_results($sql);
					//echo mysql_error();
					
					//$this->print_array($items);
					
					$variations = array();
					$variations2 = array();
					foreach($items as $key => $value):
						if(!isset($variations2[$value->variation])){
							$var = $value->attribute;
							$var = str_replace("attribute_pa_","",$var);
							$var = str_replace("attribute_","",$var);
							
							
							$var2 = $value->variation;
							if(strlen($var2)>0){
								$var2 = str_replace("-"," ",$var2);
							}else{
								$var2 = $var;
							}
							//$variations[$var] = ucfirst($var2);						
							$variations2[$value->variation] = ucfirst($var2);
						}
							
						
					endforeach;	
					
					return $variations2;
			}
			
			//New Change ID 20141016
			function create_summary($request = array()){
				$report_name 		= $this->get_request('report_name');
				$total_columns 		= $this->result_columns($report_name);
				$summary 			= array();
				$summary['total_row_amount'] 		= isset($request['total_row_amount']) 		? $request['total_row_amount'] : '';
				$summary['total_row_count'] 		= isset($request['total_row_count']) 		? $request['total_row_count'] : '';
				
				if(count($total_columns) > 0){
					foreach($total_columns as $key => $label):
						$summary[$key] 	= isset($request[$key]) 	? $request[$key] : 0;
					endforeach;
				}
				return $summary;						
			}
			
			//New Change ID 20141016
			function result_grid($report_name = '', $summary = array(),$zero='', $price_columns = array()){			
				 $output		= "";
				// $output .= $this->print_array($summary,false);
				 if(count($summary) > 0){
						$total_columns = $this->result_columns($report_name);
						if(count($total_columns) <= 0) return '';
						$summary = apply_filters("ic_commerce_result_summary_data_grid",$summary, $total_columns, $zero, $report_name);
						//$output .= $this->print_array($total_columns,false);
						$output .= '<table class="widefat summary_table sTable3">';
						$output .= '<thead>';
						$output .=	'<tr class="first">';				
						foreach($total_columns as $key => $label):
							$td_class = $key;
							$td_style = '';
							$td_value = "";
							switch($key):									
									case "total_row_amount":
									case "ic_commerce_order_item_count":
									case "cost_of_good_amount":
									case "total_cost_good_amount":
									case "sales_rate_amount":
									case "total_amount":
									case "margin_profit_amount":
									
									case "coupon_amount":
									
									case "order_shipping":
									case "order_shipping_tax":
									case "order_tax":
									case "total_tax":
									case "gross_amount":
									case "order_discount":
									case "cart_discount":
									case "total_discount":
									case "order_total":
									case "total_amount":
									
									case "product_rate":
									case "total_price":
									case "amount":
									case "_order_shipping_amount":
									case "_order_amount":
									case "order_total_amount":
									case "_shipping_tax_amount":
									case "_order_tax":
									case "_total_tax":
									
									case "order_shipping_tax":
									case "order_shipping":
									case "order_tax":
									
									case "order_discount":
									case "cart_discount":
									case "total_discount":
									case "total_tax":
									case "order_total_tax":
									case "refund_amount":
									case "order_refund_amount":
									case "part_order_refund_amount":
									case "sold_rate":
									case "difference_rate":
									case "item_amount":
									case "item_discount":
									case "profit_percentage":
									$td_value = $label;
									$td_class .= " amount";
									break;
								default:
									$td_value = $label;
									break;
							endswitch;
							$td_content = "<th class=\"{$td_class}\"{$td_style}>{$td_value}</th>\n";
							$output .= $td_content;
						endforeach;									
						$output .=	'</tr>';
						$output .=	'</thead>';
						$output .=	'<tbody>';
						$output .= "<tr>";	
						foreach($total_columns as $key => $label):
							$td_class = $key;
							$td_style = '';
							$td_value = "";
							switch($key):									
								case "total_row_amount":
								case "ic_commerce_order_item_count":
								case "cost_of_good_amount":
								case "total_cost_good_amount":
								case "sales_rate_amount":
								case "total_amount":
								case "margin_profit_amount":
								
								case "coupon_amount":
								
								case "order_shipping":
								case "order_shipping_tax":
								case "order_tax":
								case "total_tax":
								case "gross_amount":
								case "order_discount":
								case "cart_discount":
								case "total_discount":
								case "order_total":
								case "total_amount":
								
								case "product_rate":
								case "total_price":
								case "amount":
								case "_order_shipping_amount":
								case "_order_amount":
								case "order_total_amount":
								case "_shipping_tax_amount":
								case "_order_tax":
								case "_total_tax":
								
								case "order_shipping_tax":
								case "order_shipping":
								case "order_tax":
								
								case "order_discount":
								case "cart_discount":
								case "total_discount":
								case "total_tax":
								case "order_total_tax":
								case "refund_amount":
								case "order_refund_amount":
								case "part_order_refund_amount":
								case "sold_rate":
								case "difference_rate":
								case "item_amount":
								case "item_discount":
								
								case "product_sold_rate":
								case "product_total":
								case "product_subtotal":
								case "product_discount":
								
									$td_value = isset($summary[$key]) ? $summary[$key] : 0;
									$td_value = $td_value != 0 ? $this->price($td_value) : $zero;
									$td_class .= " amount";
									break;
								case "order_tax_rate":
									$td_value = isset($order_item->$key) ? $order_item->$key : 0;
									$td_value = sprintf("%.2f%%",$td_value);
									$td_class .= " amount";
									break;
								case "profit_percentage":
									$td_class .= " amount";
									$total_cost_good_amount 	= isset($summary['total_cost_good_amount']) 	? $summary['total_cost_good_amount'] 	: 0;
									$margin_profit_amount 		= isset($summary['margin_profit_amount']) 		? $summary['margin_profit_amount'] 		: 0;
									$profit_percentage 			= isset($summary['profit_percentage']) 			? $summary['profit_percentage'] 		: 0;
									
									if($total_cost_good_amount != 0 and $margin_profit_amount != 0){
										$profit_percentage = ($margin_profit_amount/$total_cost_good_amount)*100;
									}
									
									$td_value = sprintf("%.2f%%",$profit_percentage);
									break;
								case "ic_commerce_order_item_count":
								case "total_row_count":
								case "quantity":
								case "product_quantity":
								default:
									if(in_array($key, $price_columns)){
										$td_value = isset($summary[$key]) ? $summary[$key] : '';
										$td_value = $td_value == 0 ? $zero : $this->price($td_value);
										$td_class .= " amount";
									}else{
										$td_value = isset($summary[$key]) ? $summary[$key] : '';
									}
									
									$td_class .= " amount";
									break;
							endswitch;
							$td_content = "<td class=\"{$td_class}\"{$td_style}>{$td_value}</td>\n";
							$output .= $td_content;
						endforeach;
						$output .=	'</tr>';
						$output .=	'</tbody>';
						$output .=	'</table>';
				}
				return $output;
			}
			
			var $grid_column = NULL;
			function get_grid_columns(){
				if($this->grid_column){
					$grid_column = $this->grid_column;
				}else{
					include_once('ic_commerce_premium_golden_columns.php');
					$grid_column 		= new IC_Commerce_Premium_Golden_Columns($this->constants);
					$this->grid_column	= $grid_column;
				}
				
				return $grid_column;
			}
			
			// New Change ID 20141106
			function get_user_details($user_id){
				global $wpdb,$options;
				$sql = "SELECT user_login as user_name, first_name.meta_value as first_name";
				$sql .= " FROM {$wpdb->prefix}users as users ";
				
				$sql .= " LEFT JOIN  {$wpdb->prefix}usermeta as first_name ON first_name.user_id = users.ID";
				$sql .= " WHERE 1*1 ";
				
				$sql .= " AND users.ID={$user_id}";
				$sql .= " AND first_name.meta_key='billing_first_name'";
				
				return $wpdb->get_row($sql);
				
			}	
			
			// New Change ID 20141107
			var $states_name = array();
			var $country_states = array();
			
			// New Change ID 20141107
			function get_billling_state_name($cc = NULL,$st = NULL){
				global $woocommerce;
				$state_code = $st;
				
				if(!$cc) return $state_code;
				
				if(isset($this->states_name[$cc][$st])){
					$state_code = $this->states_name[$cc][$st];				
				}else{
					
					if(isset($this->country_states[$cc])){
						$states = $this->country_states[$cc];
					}else{
						$states = $this->get_wc_states($cc);//Added 20150225
						$this->country_states[$cc] = $states;						
					}				
					
					if(is_array($states)){					
						$state_code = isset($states[$state_code]) ? $states[$state_code] : $state_code;
					}
					
					$this->states_name[$cc][$st] = $state_code;				
				}
				return $state_code;
			}
						
			// New Change ID 20141119
			function get_quick_dates($start_date,$end_date,$current_date){
				
				$quick_date_change = array();
				
				if(!isset($this->constants['quick_date_change'])){
				
					$current_date_strtotime = strtotime($current_date);
				
					$tomorrow							= date("Y-m-d",strtotime("-1 day", $current_date_strtotime));
					$yesterday							= date("Y-m-d",strtotime("-2 day", $current_date_strtotime));	
							
					$last_strtotime 					= strtotime("last sunday", $current_date_strtotime);
					
					$quick_date_change['Tomorrow']		= array("start_date" => $tomorrow,															"end_date" => $tomorrow);
					$quick_date_change['Yesterday'] 	= array("start_date" => $yesterday,															"end_date" => $yesterday);
					
					$quick_date_change['This Week'] 	= array("start_date" => date("Y-m-d",$last_strtotime),										"end_date" => date('Y-m-d',$current_date_strtotime));			
					$quick_date_change['Last Week'] 	= array("start_date" => date("Y-m-d",strtotime("-7 day", $last_strtotime)),					"end_date" => date('Y-m-d',strtotime("-1 day", $last_strtotime)));			
					
					$quick_date_change['This Month'] 	= array("start_date" => date("Y-m-01",strtotime("this month", $current_date_strtotime)),	"end_date" => date('Y-m-t',$current_date_strtotime));
					$quick_date_change['Last Month'] 	= array("start_date" => date("Y-m-01",strtotime("last month", $current_date_strtotime)),	"end_date" => date("Y-m-t",strtotime("last month", $current_date_strtotime)));			
					
					$quick_date_change['This Year'] 	= array("start_date" => date("Y-01-01",strtotime("this year", $current_date_strtotime)),	"end_date" => date('Y-12-31',$current_date_strtotime));
					$quick_date_change['Last Year'] 	= array("start_date" => date("Y-01-01",strtotime("last year", $current_date_strtotime)),	"end_date" => date("Y-12-31",strtotime("last year", $current_date_strtotime)));
					
					$this->constants['quick_date_change']		= $quick_date_change;
					echo "1";
				}else{
					$quick_date_change = $this->constants['quick_date_change'];
					echo "2";
				}
				return $quick_date_change;
			}
			
			function update_option($option_key = '', $option_value = array()){				
				$option_value_old = get_option($option_key,NULL);				
				if($option_value_old){
					update_option($option_key,$option_value);
				}else{delete_option($option_key);
					add_option($option_key,$option_value);
				}
			}
			
			
			//Added 20150209
			function get_percentage($first_value = 0, $second_value = 0, $default = 0){
				$return = $default;
				$first_value = trim($first_value);
				$second_value = trim($second_value);
				
				if($first_value > 0  and $second_value > 0){
					$return = ($first_value/$second_value)*100;
				}
				
				return $return;		
			}
			
			//Added 20150209
			function get_start_of_week(){				
				$start_of_week = get_option( 'start_of_week',0);
				$week_days = array("sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday");
				$day_name = isset($week_days[$start_of_week]) ? $week_days[$start_of_week] : "sunday";
				return $day_name;		
			}
			
			//Added 20150210
			function get_average($first_value = 0, $second_value = 0, $default = 0){
				$return = $default;
				$first_value = trim($first_value);
				$second_value = trim($second_value);
				
				if($first_value > 0  and $second_value > 0){
					$return = ($first_value/$second_value);
				}
				
				return $return;		
			}
			
			function set_error_log($str){
				$this->set_error_on();
				error_log("[".date("Y-m-d H:i:s")."] PHP Notice: \t".$str."\n",3,$this->log_destination);			
			}
			
			var $error_on = NULL;
			
			var $log_destination = NULL;
			
			function set_error_on(){
				
				if($this->error_on) return '';
						
				//$plugin_path	= isset($this->constants['plugin_dir']) ? $this->constants['plugin_dir'] : dirname(__FILE__);
				
				//$plugin_path = str_replace("\includes","",$plugin_path);
				//$plugin_path = str_replace("/includes","",$plugin_path);
				
				$error_folder = ABSPATH . '/ic-logerror/';
		
				if (!file_exists($error_folder)) {
					@mkdir($error_folder, 0777, true);
				}
				
				$this->log_destination = $error_folder.'ic_error_'.date("Ymd").'.log';
				
				@ini_set('error_reporting', E_ALL);
				
				@ini_set('log_errors','On');
				
				@ini_set('error_log',$this->log_destination);
				
				$this->error_on = true;
			}
			
			function set_error_off(){
				@ini_set('log_errors','off');
			}
			
			//Added 20150214
			function get_labels(){
				global $ic_commerce_golden_labels;
				$c				= $this->constants;
				include_once('ic_commerce_golden_label.php');
				$ic_commerce_golden_labels = new IC_Commerce_Golden_Label($c);
				
				return $ic_commerce_golden_labels;
			}
			
			//Added 20150216
			var $order_item_name = array();
			function order_item_name($order_id = 0,$order_item_type = "tax"){
				if(!isset($this->order_item_name[$order_item_type][$order_id])){
					global $wpdb;
				
					$sql = "SELECT woocommerce_order_items.order_item_name	AS	item_name
					FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items	
					WHERE order_item_type ='$order_item_type' AND order_id = '{$order_id}'";
					$order_item_name = $wpdb->get_var($sql);
					
					$this->order_item_name[$order_item_type][$order_id] = $order_item_name;
					
				}else{				
					$order_item_name = $this->order_item_name[$order_item_type][$order_id];
				}
				
				return $order_item_name;
			}
			
			//Added 20150219
			function get_products_list_in_category($categories = array(), $products = array(), $return_default = '-1' , $return_formate = 'string'){
				global $wpdb;
				
				$category_product_id_string = $return_default;
				
				if(is_array($categories)){
					$categories = implode(",",$categories);
				}
				
				if(is_array($products)){
					$products = implode(",",$products);
				}
				
				if($categories  && $categories != "-1") {
				
					$sql  = " SELECT ";					
					$sql .= " woocommerce_order_itemmeta.meta_value		AS product_id";					
					
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id";
					$sql .= " LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	woocommerce_order_itemmeta.meta_value ";
					$sql .= " LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";								
					$sql .= " WHERE 1*1 AND woocommerce_order_itemmeta.meta_key 	= '_product_id'";					
					$sql .= " AND term_taxonomy.term_id IN (".$categories .")";
										
					if($products  && $products != "-1") $sql .= " AND woocommerce_order_itemmeta.meta_value IN (".$products .")";
					
					$sql .= " GROUP BY  woocommerce_order_itemmeta.meta_value";
					
					$sql .= " ORDER BY product_id ASC";
					
					$order_items = $wpdb->get_results($sql);					
					$product_id_list = array();
					if(count($order_items) > 0){
						foreach($order_items as $key => $order_item) $product_id_list[] = $order_item->product_id;
						if($return_formate == 'string'){
							$category_product_id_string = implode(",", $product_id_list);
						}else{
							$category_product_id_string = $product_id_list;
						}
					}
				}
				
				return $category_product_id_string;
				
			}
			
			
			function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
				$list 	= array();
				$string = $return_default;
				if(count($order_items) > 0){
					foreach ($order_items as $key => $order_item) {
						if(isset($order_item->$field_key))
							$list[] = $order_item->$field_key;
					}
					
					$list = array_unique($list);
					
					if($return_formate == "string"){
						$string = implode(",",$list);
					}else{
						$string = $list;
					}
				}
				return $string;
			}
			
			//Added 20150221
			function order_item_name_list($order_id_string = array(),$order_item_type = "tax"){
					global $wpdb;
					$item_name = array();
					if(is_array($order_id_string)){
						$order_id_string = implode(",",$order_id_string);
					}
					
					if(strlen($order_id_string) > 0){
						$sql = "SELECT
						woocommerce_order_items.order_id as order_id,
						woocommerce_order_items.order_item_name AS item_name
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
						WHERE order_item_type ='{$order_item_type}' AND order_id IN ({$order_id_string})";
						$order_items = $wpdb->get_results($sql);
						
						
						if(count($order_items) > 0){
							foreach($order_items as $key => $value){
								if(isset($item_name[$value->order_id]))
									$item_name[$value->order_id] = $item_name[$value->order_id].", " . $value->item_name;
								else
									$item_name[$value->order_id] = $value->item_name;
							}
						}
					}
				
					return $item_name;
			}
			
			//Added 20150221
			function get_orders_items_count($order_id_string = array(),$order_item_type = 'line_item'){
					global $wpdb;
					$item_name = array();
					if(is_array($order_id_string)){
						$order_id_string = implode(",",$order_id_string);
					}
					
					if(strlen($order_id_string) > 0){
						$sql = "SELECT woocommerce_order_items.order_id as order_id, COUNT(*) AS item_count FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
						$sql .= " WHERE order_item_type='{$order_item_type}'";
						$sql .= " AND order_id IN ({$order_id_string})";
						$sql .= " GROUP BY woocommerce_order_items.order_id";
						$sql .= " ORDER BY woocommerce_order_items.order_id DESC";
						
						$order_items = $wpdb->get_results($sql);
						
						if(count($order_items) > 0){
							foreach($order_items as $key => $value){								
								$item_name[$value->order_id] = $value->item_count;
							}
						}
					}
					
					return $item_name;					
					//return $order_items_counts;
			}
			
			//Added 20150221
			
			function get_variation_list($order_id_string = array(), $order_item_id = 0){
					global $wpdb;
					
					$variations		= array();
					
					if(is_array($order_id_string)){
						$order_id_string = implode(",",$order_id_string);
					}
					
					if(strlen($order_id_string) > 0){					
						$sql = "
						SELECT
						woocommerce_order_items.order_item_id AS order_item_id,
						woocommerce_order_items.order_id AS order_id,
						postmeta_variation.meta_value AS product_variation,
						woocommerce_order_itemmeta.meta_value as variation_id
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
						LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_variation ON postmeta_variation.post_id = woocommerce_order_itemmeta.meta_value
						WHERE 1*1";
						
						if($order_item_id > 0){
							$sql .= "AND woocommerce_order_items.order_item_id={$order_item_id}";
						}
						
						$sql .= " AND order_id IN ({$order_id_string})";
						
						$sql .= "
						AND woocommerce_order_items.order_item_type = 'line_item'
						AND woocommerce_order_itemmeta.meta_key = '_variation_id'
						AND postmeta_variation.meta_key like 'attribute_%'";
						
						$order_items	= $wpdb->get_results($sql);
						$variation 		= array();
												
						if(count($order_items) > 0){
							
							foreach($order_items as $key=>$vlaue){
								$variation[$vlaue->order_item_id][] = $vlaue->product_variation;
							}
							
							if(count($variation) > 0)
							foreach($variation as $key => $vlaue){
								$variations[$key] = ucwords (implode(", ", $vlaue));
							}
						}
					}					
					return $variations;
			}
			
			function get_order_item_sku($order_id_string = array(), $order_item_id = 0){
				global $wpdb;
				
				$order_item_sku = array();
				
				$sql = "
				SELECT 
				woocommerce_order_items.order_item_id AS order_item_id,
				woocommerce_order_items.order_id AS order_id,
				woocommerce_order_itemmeta.meta_value AS variation_id,
				postmeta_sku.meta_value AS variation_sku
				
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value				
				WHERE  1*1";
						
				if($order_item_id > 0){
					$sql .= "AND woocommerce_order_items.order_item_id={$order_item_id}";
				}
				
				$sql .= " AND order_id IN ({$order_id_string})";
				$sql .= "
				AND woocommerce_order_items.order_item_type = 'line_item'
				AND woocommerce_order_itemmeta.meta_key = '_variation_id'
				AND postmeta_sku.meta_key = '_sku'
				AND LENGTH(postmeta_sku.meta_value) > 0
				";
				
				$order_items	= $wpdb->get_results($sql);
				$order_item_ids = array();
				if(count($order_items) > 0){
					foreach($order_items as $key => $value){
						$order_item_sku[$value->order_item_id] = trim($value->variation_sku);
						$order_item_ids[] = $value->order_item_id;
					}
				}
				
				$order_product_sku = $this->get_order_product_sku_list($order_id_string, 0);
				foreach($order_product_sku as $key => $value){
					$order_item_ids[] = $key;
				}
				
				$final_sku = '';
				foreach($order_item_ids as $key => $order_item_id){
					$final_sku[$order_item_id] = isset($order_item_sku[$order_item_id]) ? $order_item_sku[$order_item_id] : (isset($order_product_sku[$order_item_id]) ? $order_product_sku[$order_item_id] : '');
				}
				
				return $final_sku;
			}
			
			function get_order_product_sku_list($order_id_string = array(), $order_item_id = 0){
				global $wpdb;
				
				$order_product_sku = array();
				
				$sql = "
				SELECT 
				woocommerce_order_items.order_item_id AS order_item_id,
				woocommerce_order_items.order_id AS order_id,				
				woocommerce_order_itemmeta_products.meta_value AS product_id,
				postmeta_product_sku.meta_value AS product_sku
				
				
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
				
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_products ON woocommerce_order_itemmeta_products.order_item_id = woocommerce_order_items.order_item_id
				
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta_product_sku ON postmeta_product_sku.post_id = woocommerce_order_itemmeta_products.meta_value
				WHERE  1*1";
						
				if($order_item_id > 0){
					$sql .= "AND woocommerce_order_items.order_item_id={$order_item_id}";
				}
				
				$sql .= " AND order_id IN ({$order_id_string})";
				$sql .= "
				AND woocommerce_order_items.order_item_type = 'line_item'
				AND woocommerce_order_itemmeta_products.meta_key = '_product_id'				
				AND postmeta_product_sku.meta_key = '_sku'
				AND LENGTH(postmeta_product_sku.meta_value)> 0
				";
				
				$order_items	= $wpdb->get_results($sql);
				
				if(count($order_items) > 0){
					foreach($order_items as $key => $value){														
						$order_product_sku[$value->order_item_id] = trim($value->product_sku);
					}
				}
				
				return $order_product_sku;				
			}
			
			function get_order_product_categories(){
				return array();
			}
			
			function get_grid_object(){
				include_once("ic_commerce_premium_golden_grid_data.php");				
				return $object = new IC_Commerce_Premium_Golden_Grid_Data($this->constants);
			}
			
			//Added 20150225
			function get_wc_countries(){
				return class_exists('WC_Countries') ? (new WC_Countries) : (object) array();
			}
			
			//Added 20150225
			function get_wc_states($country_code){
				global $woocommerce;
				return isset($woocommerce) ? $woocommerce->countries->get_states($country_code) : array();
			}
			
			//Added 20150225
			function get_old_order_status($old = array('cancelled'),$new = array('cancelled')){
				if($this->constants['post_order_status_found'] == 0 ){
					$shop_order_status 		= $this->shop_order_status();			
					$detault_stauts_slug	= $old;
					$detault_stauts_id		= array();
					
					foreach($shop_order_status as $key => $value){
						$new_shop_order_status[$value->id] = ucfirst($value->label);
						if(in_array($value->label,$detault_stauts_slug)){
							$detault_stauts_id[]= $value->id;
						}
					}				
					$cancelled_id = $detault_stauts_id;
				}else{
					$cancelled_id = $new;
				}			
				return $cancelled_id;
			}
			
			//Added 20150310
			function unset_class_variables(){
				$this->constants		= $this->request = $this->request_string = $this->normal_sql_query = $this->all_variation = $this->terms_by = $this->product_link = NULL;
				$this->states_name		= $this->country_states = $this->order_item_name = $this->per_page = $this->per_page_default = NULL;
				unset($this->constants, $this->request, $this->product_link, $this->request_string, $this->normal_sql_query, $this->all_variation, $this->terms_by, $this->per_page, $this->states_name, $this->states_name, $this->error_on);
			}
			
			//Added 20150310
			function unset_global_variables(){
				$this->unset_class_variables();
				$GLOBALS = NULL;$_SERVER = NULL;
				unset($GLOBALS);unset($this);unset($_SERVER);
			}
			
			//Added 20150311
			function plugins_loaded_icwoocommerce_textdomains() {
				$this->create_directory('languages',WP_PLUGIN_DIR.'/'.$this->constants['plugin_folder']);
				load_plugin_textdomain('icwoocommerce_textdomains', WP_PLUGIN_DIR.'/'.$this->constants['plugin_folder'].'/languages',$this->constants['plugin_folder'].'/languages');
			}
			
			//Added 20150413
			function create_directory($directory_name = '',$path = '') {
				if (!file_exists($path.'/'.$directory_name)) {
					mkdir($path.'/'.$directory_name, 0777, true);
				}
			}
			
			//Added 201500312
			function get_pdf_paper_size(){
				$paper_sizes = array(
					"letter"	=>__("Letter",'icwoocommerce_textdomains'),
					"legal"		=>__("Legal",'icwoocommerce_textdomains'),
					"a0"		=>__("A0",'icwoocommerce_textdomains'),
					"a1"		=>__("A1",'icwoocommerce_textdomains'),
					"a2"		=>__("A2",'icwoocommerce_textdomains'),
					"a3"		=>__("A3",'icwoocommerce_textdomains'),
					"a4"		=>__("A4",'icwoocommerce_textdomains'),
					"a5"		=>__("A5",'icwoocommerce_textdomains'),
					"a6"		=>__("A6",'icwoocommerce_textdomains')
				);
				
				$paper_sizes = apply_filters('icwoocommerce_paper_sizes', $paper_sizes);
				
				return $paper_sizes;
			}
			
			function get_pdf_style_align($columns=array(),$alight='right',$output = '',$prefix = "", $report_name = NULL){
				$output_array 	= array();
				$report_name	= $report_name == NULL ? $this->get_request('report_name','') : $report_name;
				$custom_columns = apply_filters("ic_commerce_pdf_custom_column_right_alignment",array(), $columns,$report_name);
				foreach($columns as $key => $value):
					switch ($key) {
						case "sale_price":
						case "regular_price":
						case "otal_sales":
						case "total_sales":
						case "stock":
						case "variation_sold":
						case "refund_id":
						case "refund_count":
						
						//Details Page
						case "order_shipping":
						case "order_shipping_tax":
						case "order_tax":
						case "gross_amount":
						case "order_discount":
						case "cart_discount":
						case "total_discount":
						case "total_tax":
						case "order_total":
						case 'product_rate':
						case 'total_price':	
						case "order_total_tax":
						case "refund_amount":
						case "order_refund_amount":
						case "part_order_refund_amount":
						case "sold_rate":
						case "difference_rate":
						case "item_amount":
						case "item_discount":
						
						case "order_shipping":
						case "order_shipping_tax":
						case "order_tax":
						case "gross_amount":
						case "order_discount":
						case "cart_discount":
						case "total_discount":
						case "total_tax":
						case "order_total":
						case "item_count":
						case "transaction_id":
						case "order_item_count":
						case "customer_id"://New Change ID 20150227
						case "quantity":
						case "product_stock":
						case "total_amount":
						case "order_count":
						case "coupon_amount":
						case "Count":
						case "coupon_count":
						case "refund_amount":
						case "refund_count":
						case "order_refund_amount":
						case "part_order_refund_amount":
						
						case "quantity":
						case "cost_of_good_amount":
						case "total_cost_good_amount":
						case "sales_rate_amount":
						case "margin_profit_amount":
						case "product_rate":
						case "profit_percentage":
							$output_array[] = "{$prefix} th.{$key}";
							$output_array[] = "{$prefix} td.{$key}";
							break;
						default:
							if(isset($custom_columns[$key])){
								$output_array[] = "{$prefix} th.{$key}";
								$output_array[] = "{$prefix} td.{$key}";
							}
							break;
					}
				endforeach;
				/*
				foreach($custom_columns as $key => $value){
					$output_array[] = "{$prefix} th.{$key}";
					$output_array[] = "{$prefix} td.{$key}";
				}
				*/
				//$this->print_array($columns);
				//$this->print_array($custom_columns);
				//$this->print_array($output_array);
				if(count($output_array)>0){
					$output .= implode(",",$output_array);
					$output .= "{text-align:{$alight};}";
				}
				
				return $output;
			}
			
			//20150312
			function get_pagination($total_pages = 50,$limit = 10,$adjacents = 3,$targetpage = "admin.php?page=RegisterDetail",$request = array()){		
				
				if(count($request)>0){
					unset($request['p']);
					unset($request['new_variations_value']);
					$new_request = array_map(create_function('$key, $value', 'return $key."=".$value;'), array_keys($request), array_values($request));
					$new_request = implode("&",$new_request);
					$targetpage = $targetpage."&".$new_request;
				}
				
				
				/* Setup vars for query. */
				//$targetpage = "admin.php?page=RegisterDetail"; 	//your file name  (the name of this file)										
				/* Setup page vars for display. */
				if(isset($_REQUEST['p'])){
					$page = $_REQUEST['p'];
					$_GET['p'] = $page;
					$start = ($page - 1) * $limit; 			//first item to display on this page
				}else{
					$page = false;
					$start = 0;	
					$page = 1;
				}
				
				if ($page == 0) $page = 1;					//if no page var is given, default to 1.
				$prev = $page - 1;							//previous page is page - 1
				$next = $page + 1;							//next page is page + 1
				$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
				$lpm1 = $lastpage - 1;						//last page minus 1
				
				
				
				$label_previous = __('previous', 'icwoocommerce_textdomains');
				$label_next = __('next', 'icwoocommerce_textdomains');
				
				/* 
					Now we apply our rules and draw the pagination object. 
					We're actually saving the code to a variable in case we want to draw it more than once.
				*/
				$pagination = "";
				if($lastpage > 1)
				{	
					$pagination .= "<div class=\"pagination\">";
					//previous button
					if ($page > 1) 
						$pagination.= "<a href=\"$targetpage&p=$prev\" data-p=\"$prev\">{$label_previous}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_previous}</span>\n";	
					
					//pages	
					if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
					{	
						for ($counter = 1; $counter <= $lastpage; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>\n";
							else
								$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
						}
					}
					elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
					{
						//close to beginning; only hide later pages
						if($page < 1 + ($adjacents * 2))		
						{
							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//in middle; hide some front and some back
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//close to end; only hide early pages
						else
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
						}
					}
					
					//next button
					if ($page < $counter - 1) 
						$pagination.= "<a href=\"$targetpage&p=$next\" data-p=\"$next\">{$label_next}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_next}</span>\n";
					$pagination.= "</div>\n";		
				}
				return $pagination;
			
		}
		
		function get_export_pdf_content($rows=array(),$columns=array(),$summary=array()){
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			$th_open = '<th class="#class#">';
			$th_close = '</th>';
			
			$td_open = '<td class="#class#">';
			$td_close = '</td>';
			
			$tr_open = '<tr>';
			$tr_close = '</tr>';
			
			
			
			foreach($columns as $key => $value):
				//$l = $th_open . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $th_close;
				$l = str_replace("#class#",$key,$th_open) . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $th_close;
				$schema_insert .= $l;				
			endforeach;// end for
			
			//New Change ID 20140918
			$company_name	= $this->get_request('company_name','');
			$report_title	= $this->get_request('report_title','');
			$display_logo	= $this->get_request('display_logo','');
			$display_date	= $this->get_request('display_date','');
			$display_center	= $this->get_request('display_center','');
			
			$keywords		= $this->get_request('pdf_keywords','');
			$description	= $this->get_request('pdf_description','');
			
			$column_align_style = $this->get_pdf_style_align($columns,'right');
			$date_format 	= get_option( 'date_format' );
			
			//New Change ID 20140918
			$out ='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head>
					<title>'.$report_title.'</title>
						<meta name="description" content="'.$description.'" />
						<meta name="keywords" content="'.$keywords.'" />
						<meta name="author" content="'.$company_name.'" /><style type="text/css"><!-- 
						.header {position: fixed; top: -40px; text-align:center;}
						.footer { position: fixed; bottom: 0px; text-align:center;}
						.pagenum:before { content: counter(page); }
						body{font-family: "Source Sans Pro", sans-serif; font-size:10px;}
						span{font-weight:bold;}
						.Clear{clear:both; margin-bottom:10px;}
						label{width:100px; float:left; }
						.sTable3{border:1px solid #DFDFDF; width:100%;}
						.sTable3 th{padding:10px 10px 7px 10px;background:#eee url(../images/thead.png) repeat-x top left;text-align:left;}
						.Form{padding:1% 1% 11% 1%; margin:5px 5px 5px 5px;}
						.myclass{border:1px solid black;}
						
						.sTable3 tbody tr td{padding:8px 10px; background:#fff; border-top:1px solid #DFDFDF; border-right:1px solid #DFDFDF;}
						.sTable3 tbody tr.AltRow td{background:#FBFBFB;}
						.print_header_logo.center_header, .header.center_header{margin:auto;  text-align:center;}
						'.$column_align_style.'--></style>
					</head>
					<body>';
			$logo_html		=	"";
			
			if(strlen($display_logo) > 0){
				$company_logo	=	$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');
				$upload_dir 	= wp_upload_dir(); // Array of key => value pairs
				$company_logo	= str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$company_logo);
				//$logo_html 		= "<div class='Clear'><img src='".$company_logo."' alt='' /><span>".$company_name."</span></div>";
				$logo_html 		= "<div class='Clear  print_header_logo ".$display_center."'><img src='".$company_logo."' alt='' /></div>";
			}else{
				//$logo_html 		= "<div class='Clear'><span>".$company_name."</span></div>";
			}
			if(strlen($company_name) > 0)	$out .="<div class='header ".$display_center."'><h2>".stripslashes($company_name)."</h2></div>";			
			$out .="<div class='footer'>Page: <span class='pagenum'></span></div>";
			$out .= "<div class='Container1'>";
			$out .= "<div class='Form1'>";
			$out .= $logo_html;
			
			if(strlen($company_name) > 0 || strlen($display_logo) > 0)
			$out .= "<hr class='myclass1'>";
			
			if(strlen($report_title) > 0)	$out .= "<div class='Clear'><label>".__( 'Report Title:', 'icwoocommerce_textdomains' )." </label><label>".stripslashes($report_title)."</label></div>";
			
			$out .= "<div class='Clear'></div>";
			
			if($display_date) $out .= "<div class='Clear'><label>".__( 'Date:', 'icwoocommerce_textdomains' )." </label><label>".date_i18n($date_format)."</label></div>";
			
			$out .= "<div class='Clear'></div>";
			$out .= "<div class='Clear'>";			
			$out .= "<table class='sTable3' cellpadding='0' cellspacing='0' width='100%'>";
			$out .= "<thead>";
			$out .= $tr_open;			
			$out .= trim(substr($schema_insert, 0, -1));
			$out .= $tr_close;
			$out .= "</thead>";			
			$out .= "<tbody>";			
			$out .= $csv_terminated;
			
				
			
			$last_order_id = 0;
			$alt_order_id = 0; 
			for($i =0;$i<count($rows);$i++){			
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								//$schema_insert .= $td_open . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $td_close;
								$schema_insert .= str_replace("#class#",$key,$td_open).str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $td_close;
								
							}
							
						 }else{
							$schema_insert .= $td_open.''.$td_close;;
						 }
						$j++;
				}				
				$out .= $tr_open;
				$out .= $schema_insert;
				$out .= $tr_close;	
			}

			$out .= "</tbody>";
			$out .= "</table>";	
			$out .= "</div></div>";
			
			if(count($summary)>0){
				$out .= "<div class=\"print_summary_bottom\">";
				$out .= __("Summary Total:",'icwoocommerce_textdomains');
				$out .= "</div>";
				
				$out .= "<div class=\"print_summary_bottom2\">";
				$out .= 		"<br />";				
				$detail_view	= $this->get_request('detail_view',"no");
				$zero			= $this->price(0);
				$out .= 		$this->result_grid($detail_view,$summary,$zero);
				$out .= "</div>";
			}else{
				$out .= "<div class=\"print_summary_bottom\">";
				$out .= sprintf(__("Total product variations: %s",'icwoocommerce_textdomains'),count($rows));
				$out .= "</div>";
			}
			
			
			
			$out .= "</div></body>";			
			$out .="</html>";			
			return $out;
		 
		}
		
		function check_cog_exits(){
			global $wpdb;
			$cog_metakey = $this->get_request('cog_metakey');
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta as postmeta WHERE meta_key = '{$cog_metakey}' LIMIT 1";
			$count = $wpdb->get_var($sql);			
			echo $count;
			die;
		}
		
		function common_request_form(){
			$_REQUEST['date_format']			= isset($_REQUEST['date_format']) 			? trim($_REQUEST['date_format']) 			: get_option('date_format',"jS F Y");
			$_REQUEST['formatted_start_date']	= isset($_REQUEST['formatted_start_date']) 	? trim($_REQUEST['formatted_start_date']) 	: (isset($_REQUEST['start_date']) 	? date($_REQUEST['date_format'],strtotime($_REQUEST['start_date'])) : '');
			$_REQUEST['formatted_end_date']		= isset($_REQUEST['formatted_end_date']) 	? trim($_REQUEST['formatted_end_date']) 	: (isset($_REQUEST['end_date']) 	? date($_REQUEST['date_format'],strtotime($_REQUEST['end_date'])) 	: '');
		}
		
		function get_product_sku($product_type = "simple"){				
				global $wpdb;				
				
				$sql = "SELECT postmeta_sku.meta_value AS id, postmeta_sku.meta_value AS label
				
				FROM `{$wpdb->prefix}woocommerce_order_items` AS woocommerce_order_items
				
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id
				
				LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value
				
				";
				if($product_type == "variation")
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta2 ON woocommerce_order_itemmeta2.order_item_id = woocommerce_order_items.order_item_id";
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_product_id'";
				
				$sql .= " AND postmeta_sku.meta_key = '_sku' AND LENGTH(postmeta_sku.meta_value) > 0";
				
				if($product_type == "variation")
					$sql .= " AND woocommerce_order_itemmeta2.meta_key = '_variation_id' AND woocommerce_order_itemmeta2.meta_value > 0";
				
				$sql .= " GROUP BY postmeta_sku.meta_value ORDER BY postmeta_sku.meta_value ASC";
			
				$products = $wpdb->get_results($sql);
				
				//$this->print_array($products);
				
				//$this->print_sql($sql);
			
				return $products;
		}
		//new change id 20150228
		function get_variation_sku($product_type = "simple"){				
				global $wpdb;				
				
				$sql = "  SELECT postmeta_sku.meta_value AS id, postmeta_sku.meta_value AS label FROM `{$wpdb->prefix}woocommerce_order_itemmeta` AS woocommerce_order_itemmeta";				
				$sql .= " LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_sku ON postmeta_sku.post_id = woocommerce_order_itemmeta.meta_value";
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key = '_variation_id' AND woocommerce_order_itemmeta.meta_value > 0";
				$sql .= " AND postmeta_sku.meta_key = '_sku' AND LENGTH(postmeta_sku.meta_value) > 0";
				$sql .= " GROUP BY postmeta_sku.meta_value ORDER BY postmeta_sku.meta_value ASC";
				$products = $wpdb->get_results($sql);
				return $products;
		}
		
		//Added 20150424
		function get_coupon_codes(){
			global $wpdb;
			$sql = " SELECT ";
			$sql .= "
			woocommerce_order_items.order_item_name				AS		'label', 
			woocommerce_order_items.order_item_name				AS		'id'
			
			FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
			LEFT JOIN	{$wpdb->prefix}posts as posts 	ON posts.ID = woocommerce_order_items.order_id";				
			$sql .= "
			WHERE 
			posts.post_type 								=	'shop_order'
			AND woocommerce_order_items.order_item_type		=	'coupon'";						
			
			$sql .= "
			Group BY woocommerce_order_items.order_item_name
			ORDER BY woocommerce_order_items.order_item_name ASC";
			
			$coupon_codes = $wpdb->get_results($sql);
			
			return $coupon_codes;
		}
		
		function get_coupon_types(){
			if(function_exists('wc_get_coupon_types')){
				$wc_coupon_types = wc_get_coupon_types();						
			}else{
				$wc_coupon_types = array(
					'fixed_cart'      => __( 'Cart Discount', 			'icwoocommerce_textdomains' ),
					'percent'         => __( 'Cart % Discount', 		'icwoocommerce_textdomains' ),
					'fixed_product'   => __( 'Product Discount', 		'icwoocommerce_textdomains' ),
					'percent_product' => __( 'Product % Discount', 		'icwoocommerce_textdomains' )
				);
			}
			
			return $wc_coupon_types;
		}
		
		function load_class_file($file_path){
			$return = false;
			$complete_path = $this->constants['plugin_dir'].$file_path;
			if(file_exists($complete_path)){
				include_once($complete_path);
				$return = true;
			}else{
				//echo "file not found{$file_path}";
			}
			
			return $return;
		}
		
		function get_variaiton_attributes($variation_by = 'variation_id', $variation_ids = '', $order_item_ids = ''){			
				global $wpdb;
				
				$sql = "SELECT TRIM(LEADING 'attribute_' FROM meta_key)  AS attribute_key  ";
				$sql .= " FROM {$wpdb->prefix}postmeta ";
				$sql .= " WHERE meta_key LIKE 'attribute%'";
				if($variation_ids){
					$sql .= " AND post_id IN ({$variation_ids})";
				}
				
				$sql .= " GROUP BY attribute_key ORDER BY attribute_key ASC";
				
				$attributes =  $wpdb->get_results($sql);
				
				//$this->print_array($attributes);
				
				$new_attr 			= array();
				$attribute_keys 	= array();
				$attribute_labels 	= array();
				$return 			= array();
				$variations 		= array();
				
				$new_item_attr_variation_id		= array();
				$new_item_attr_order_item_id	= array();
				$order_item_variations			= array();
				
				//return $new_attr;
				if($attributes){
					foreach($attributes as $key => $value){						
						$attribute_keys[]	= $value->attribute_key;
					}
				}
								
				//$this->print_array($attribute_keys);
				
				$attribute_keys = array_unique($attribute_keys);
				sort($attribute_keys);
				
				
				$attribute_meta_key = implode("', '",$attribute_keys);
				
				$sql = "SELECT TRIM(LEADING 'pa_' FROM woocommerce_order_itemmeta.meta_key) AS attribute_key, woocommerce_order_itemmeta.meta_value AS attribute_value, woocommerce_order_itemmeta.order_item_id, woocommerce_order_itemmeta.meta_key AS meta_key";
				if($variation_by == 'variation_id'){
					$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value AS variation_id";
				}else{
					$sql .= ", 0 AS variation_id";
				}				
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta";
				if($variation_by == 'variation_id'){
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id 			ON woocommerce_order_itemmeta_variation_id.order_item_id			=	woocommerce_order_itemmeta.order_item_id";
				}
				
				$sql .= " WHERE woocommerce_order_itemmeta.meta_key IN ('{$attribute_meta_key}')";
				
				if($variation_by == 'variation_id'){
					$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 			= '_variation_id'";
					$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value > 0";
					//$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value = 4859";
					
					if($variation_ids){
						$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value IN ({$variation_ids})";
					}
				}
				
				if($order_item_ids){
					$sql .= " AND woocommerce_order_itemmeta.order_item_id IN ({$order_item_ids})";
				}
				
				
				
				$item_attributes =  $wpdb->get_results($sql);
				//$this->print_array($item_attributes);
				
				if($item_attributes){
					foreach($item_attributes as $key => $value){
						$attribute_key 		= $value->attribute_key;
						$attribute_key 		= ucwords(str_replace("-"," ",$attribute_key));
						
						$attribute_value	= $value->attribute_value;
						$attribute_value 	= ucwords(str_replace("-"," ",$attribute_value));
											
						$new_item_attr_variation_id[$value->variation_id][$attribute_key] = $attribute_value;
						$new_item_attr_order_item_id[$value->order_item_id][$attribute_key] = $attribute_value;
						
						$attribute_labels[] = $attribute_key;
					}
				}
				
				$attribute_labels = array_unique($attribute_labels);
				sort($attribute_labels);
				
				//$this->print_array($new_item_attr_order_item_id);
				
				//By Variation ID
				if($variation_by == 'variation_id'){
					foreach($new_item_attr_variation_id as $id => $attribute_values){
						foreach($attribute_labels as $key2 => $value2){
							//$this->print_array($attribute_values);
							if(isset($attribute_values[$value2]))
								$new_item_attr_variation_id[$id]['varations'][] = $attribute_values[$value2];
						}
					}
					
					foreach($new_item_attr_variation_id as $id => $attribute_values){
						$new_item_attr_variation_id[$id]['varation_string'] 	= implode(", ",$attribute_values['varations']);					
						$variations[$id]['varation_string'] 					= implode(", ",$attribute_values['varations']);
					}
				}
				
				//$this->print_array($new_item_attr_variation_id);
				
				//By Order Item ID
				foreach($new_item_attr_order_item_id as $id => $attribute_values){
					foreach($attribute_labels as $key2 => $value2){
						//$this->print_array($attribute_values);
						if(isset($attribute_values[$value2]))
							$new_item_attr_order_item_id[$id]['varations'][] = $attribute_values[$value2];
					}
				}
				
				foreach($new_item_attr_order_item_id as $id => $attribute_values){
					$new_item_attr_order_item_id[$id]['varation_string'] 	= implode(", ",$attribute_values['varations']);					
					$order_item_variations[$id]['varation_string'] 		= implode(", ",$attribute_values['varations']);
				}
				
				//$this->print_array($order_item_variations);
				
				$return['attribute_keys']		= $attribute_keys;
				$return['variation_labels']		= $attribute_labels;
				$return['varation_string']		= $variations;
				$return['item_varation_string']	= $order_item_variations;
				$return['varation']				= $new_item_attr_variation_id;
				
				//$this->print_array($return);
				
				return $return;
		}
		
		////////////////Variation////////////////////
		function get_variaiton_attributes_columner_separated($variation_by = 'variation_id', $variation_ids = '', $order_item_ids = ''){			
					global $wpdb;
					$variation_order_item_ids 		= array();
					$new_item_attr_order_item_ids 	= array();
					
					$variations_by_variation_ids 	= array();
					$variations_by_order_item_ids 	= array();
					
					if(!isset($this->constants['variations_by_order_item_ids'])){
						
						$new_attr 			= array();
						$attribute_keys 	= array();
						$attribute_labels 	= array();
						$return 			= array();
						$variations 		= array();
						
						$new_item_attr_variation_id		= array();
						$new_item_attr_order_item_id	= array();
						$order_item_variations			= array();
						
						
						$sql = "SELECT postmeta_product_addons.meta_value product_attributes FROM {$wpdb->prefix}posts AS posts";
						$sql .= " LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_product_addons ON postmeta_product_addons.post_id = posts.ID";
						$sql .= " WHERE post_type in ('product')";
						$sql .= " AND postmeta_product_addons.meta_key IN ('_product_attributes') ";
						
						$product_addon_objects = $wpdb->get_results($sql);
						//$this->print_array($attributes);
						$product_addon_master = array();
						if(count($product_addon_objects)>0){					
							foreach($product_addon_objects as $key => $value){
								$product_addon_lists = unserialize($value->product_attributes);
								foreach($product_addon_lists as $key2 => $value2){
									$product_addon_master[] = $key2;
									//$attribute_keys2[]	= "wcv_".str_replace("pa_","",$key2);
								}
								//$this->print_array($product_addon_lists);
							}
						}
						
						$product_addon_master_key = "";
						if(count($product_addon_master)>0){
							$product_addon_master = array_unique($product_addon_master);
							sort($product_addon_master);
							
							$product_addon_master_key = implode("','", $product_addon_master);
						}
						
						$attribute_meta_key = $product_addon_master_key;
						foreach($product_addon_master as $key => $value){
							//$attribute_new_key[] = strtolower("wcv_".str_replace("pa_","",$value));
							$attribute_new_key[] = $value;//20150825
						}
						$this->constants['variation_attribute_keys'] = $attribute_new_key;
						
						//$this->print_array($attribute_new_key);
						
						//$this->print_array($attribute_new_key);
						
						/*
						$sql = "SELECT TRIM(LEADING 'attribute_' FROM meta_key)  AS attribute_key  ";
						$sql .= " FROM {$wpdb->prefix}postmeta ";
						$sql .= " WHERE meta_key LIKE 'attribute%'";
						if($variation_ids){
							$sql .= " AND post_id IN ({$variation_ids})";
						}
						
						$sql .= " GROUP BY attribute_key ORDER BY attribute_key ASC";
						
						$attributes =  $wpdb->get_results($sql);
						
						$this->print_array($attributes);
						
						
						
						//return $new_attr;
						if($attributes){
							foreach($attributes as $key => $value){						
								$attribute_keys[]	= $value->attribute_key;
								$attribute_keys2[]	= "wcv_".str_replace("pa_","",$value->attribute_key);
							}
						}
										
						$this->constants['variation_attribute_keys'] = $attribute_keys2;
						
						$attribute_keys = array_unique($attribute_keys);
						sort($attribute_keys);
						
						
						$attribute_meta_key = implode("', '",$attribute_keys);
						*/
						$sql = "SELECT 
						TRIM(LEADING 'pa_' FROM woocommerce_order_itemmeta.meta_key) AS attribute_key, 
						woocommerce_order_itemmeta.meta_value AS attribute_value, 
						woocommerce_order_itemmeta.order_item_id, 
						woocommerce_order_itemmeta.meta_key AS meta_key";
						
						if($variation_by == 'variation_id'){
							$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value AS variation_id";
						}else{
							$sql .= ", 0 AS variation_id";
						}				
						
						$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta";
						if($variation_by == 'variation_id'){
							$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id 			ON woocommerce_order_itemmeta_variation_id.order_item_id			=	woocommerce_order_itemmeta.order_item_id";
						}
						
						if(isset($_REQUEST['new_variations_value']) and count($_REQUEST['new_variations_value'])>0){
							foreach($_REQUEST['new_variations_value'] as $key => $value){
								$new_v_key = "wcvf_".$this->remove_special_characters($key);
								$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_{$new_v_key} ON woocommerce_order_itemmeta_{$new_v_key}.order_item_id = woocommerce_order_itemmeta.order_item_id";
							}
						}
						
						$sql .= " WHERE woocommerce_order_itemmeta.meta_key IN ('{$attribute_meta_key}')";
						
						if($variation_by == 'variation_id'){
							$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 			= '_variation_id'";
							$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value > 0";
							//$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value = 4859";
							
							if($variation_ids){
								$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value IN ({$variation_ids})";
							}
						}
						
						if($order_item_ids){
							$sql .= " AND woocommerce_order_itemmeta.order_item_id IN ({$order_item_ids})";
						}
						
						
						if(isset($_REQUEST['new_variations_value']) and count($_REQUEST['new_variations_value'])>0){
							foreach($_REQUEST['new_variations_value'] as $key => $value){
								$new_v_key = "wcvf_".$this->remove_special_characters($key);
								$key = str_replace("'","",$key);
								$sql .= " AND woocommerce_order_itemmeta_{$new_v_key}.meta_key = '{$key}'";
								$vv = is_array($value) ? implode(",",$value) : $value;
								//$vv = str_replace("','",",",$vv);
								$vv = str_replace(",","','",$vv);
								$sql .= " AND woocommerce_order_itemmeta_{$new_v_key}.meta_value IN ('{$vv}') ";
							}
						}
						
						
						$item_attributes =  $wpdb->get_results($sql);
						//$this->print_array($item_attributes);
						
						if($item_attributes){
							foreach($item_attributes as $key => $value){
								$attribute_key 		= strtolower($value->meta_key);
								//$attribute_key 		= (str_replace(" "," ",$attribute_key));
								
								$attribute_value	= $value->attribute_value;
								$attribute_value 	= ucwords(str_replace("-"," ",$attribute_value));
													
								//$variations_by_variation_ids[$value->variation_id]["wcv_".$attribute_key] = $attribute_value;
								//$variations_by_order_item_ids[$value->order_item_id]["wcv_".$attribute_key] = $attribute_value;
								
								$variations_by_variation_ids[$value->variation_id][$attribute_key] = $attribute_value;
								$variations_by_order_item_ids[$value->order_item_id][$attribute_key] = $attribute_value;
								
								$attribute_labels[] = $attribute_key;
							}
						}
						
						$this->constants['variations_by_variation_ids'] = $variations_by_variation_ids;
						$this->constants['variations_by_order_item_ids'] = $variations_by_order_item_ids;
						
						//$this->print_array($variations_by_variation_ids);
						
					}else{
						//echo "test";
						$variations_by_order_item_ids = $this->constants['variations_by_order_item_ids'];
					}
					
					return $variations_by_order_item_ids;
			}
			
			function get_order_item_id_variation($order_item_id = 0){
				if(!isset($this->constants['variations_by_order_item_ids'])){
					$this->get_variaiton_attributes_columner_separated('variation_id','',$order_item_id);
				}
				
				$variations_by_order_item_ids 	= $this->constants['variations_by_order_item_ids'];				
				$variation_order_item_id 		= isset($variations_by_order_item_ids[$order_item_id]) ? $variations_by_order_item_ids[$order_item_id] : array();
				
				return $variation_order_item_id;
			}
			
			function get_variation_id_variation($variation_id = 0){
				if(!isset($this->constants['variations_by_order_item_ids'])){
					$this->get_variaiton_attributes_columner_separated('variation_id','',$variation_id);
				}
				
				$variations_by_variation_ids 	= $this->constants['variations_by_variation_ids'];				
				$variations_by_variation_id 		= isset($variations_by_variation_ids[$variation_id]) ? $variations_by_variation_ids[$variation_id] : array();
				
				return $variations_by_variation_id;
			}
			
			
			//For Details page
			function get_grid_items($columns = array(),$order_items = array()){
				
				$order_item_ids  = $this->get_items_id_list($order_items,'order_item_id');
				
				$this->get_variaiton_attributes_columner_separated('variation_id','',$order_item_ids);
				
				$variation_attribute_keys = $this->constants['variation_attribute_keys'];
				
				foreach($order_items as $rkey => $order_item ):				
					$variation 				= $this->get_order_item_id_variation($order_item->order_item_id);							
					foreach($columns as $key => $value):
						$td_value = "";
						switch ($key) {
							default:
								if(in_array($key, $variation_attribute_keys)){
									$td_value = isset($variation[$key]) ? $variation[$key] : '-';
								}else{
									$td_value = isset($order_item->$key) ? $order_item->$key : '';
								}
								break;
							}						
							$order_items[$rkey]->$key 				= $td_value;						
						endforeach;
					endforeach;
				
					return $order_items;
			}
			
			
			
			//Comma Separated Variations
			function get_variaiton_attributes_comma_separated($variation_by = 'variation_id', $variation_ids = '', $order_item_ids = ''){
			
					global $wpdb;
					
					$new_attr 			= array();
					$attribute_keys 	= array();
					$attribute_labels 	= array();
					$return 			= array();
					$variations 		= array();
					
					$new_item_attr_variation_id		= array();
					$new_item_attr_order_item_id	= array();
					$order_item_variations			= array();
					
					
					$sql = "SELECT postmeta_product_addons.meta_value product_attributes FROM {$wpdb->prefix}posts AS posts";
					$sql .= " LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_product_addons ON postmeta_product_addons.post_id = posts.ID";
					$sql .= " WHERE post_type in ('product')";
					$sql .= " AND postmeta_product_addons.meta_key IN ('_product_attributes') ";
					
					$product_addon_objects = $wpdb->get_results($sql);
					//$this->print_array($attributes);
					$product_addon_master = array();
					if(count($product_addon_objects)>0){					
						foreach($product_addon_objects as $key => $value){
							$product_addon_lists = unserialize($value->product_attributes);
							foreach($product_addon_lists as $key2 => $value2){
								$product_addon_master[] = $key2;
								//$attribute_keys2[]	= "wcv_".str_replace("pa_","",$key2);
							}
							//$this->print_array($product_addon_lists);
						}
					}
					
					$product_addon_master_key = "";
					if(count($product_addon_master)>0){
						$product_addon_master = array_unique($product_addon_master);
						sort($product_addon_master);
						
						$product_addon_master_key = implode("','", $product_addon_master);
					}
					
					$attribute_meta_key = $product_addon_master_key;
					foreach($product_addon_master as $key => $value){
						$attribute_new_key[] = strtolower("wcv_".str_replace("pa_","",$value));
					}
					$this->constants['variation_attribute_keys'] = $attribute_new_key;
					
					$sql = "SELECT TRIM(LEADING 'pa_' FROM woocommerce_order_itemmeta.meta_key) AS attribute_key, woocommerce_order_itemmeta.meta_value AS attribute_value, woocommerce_order_itemmeta.order_item_id, woocommerce_order_itemmeta.meta_key AS meta_key";
					if($variation_by == 'variation_id'){
						$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value AS variation_id";
					}else{
						$sql .= ", 0 AS variation_id";
					}				
					
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta";
					if($variation_by == 'variation_id'){
						$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id 			ON woocommerce_order_itemmeta_variation_id.order_item_id			=	woocommerce_order_itemmeta.order_item_id";
					}
					
					$sql .= " WHERE woocommerce_order_itemmeta.meta_key IN ('{$attribute_meta_key}')";
					
					if($variation_by == 'variation_id'){
						$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 			= '_variation_id'";
						$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value > 0";
						//$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value = 4859";
						
						if($variation_ids){
							$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value IN ({$variation_ids})";
						}
					}
					
					if($order_item_ids){
						$sql .= " AND woocommerce_order_itemmeta.order_item_id IN ({$order_item_ids})";
					}
					
					
					
					$item_attributes =  $wpdb->get_results($sql);
					//$this->print_array($item_attributes);
					
					if($item_attributes){
						foreach($item_attributes as $key => $value){
							$attribute_key 		= strtolower($value->attribute_key);
							//$attribute_key 		= ucwords(str_replace("-"," ",$attribute_key));
							
							$attribute_value	= $value->attribute_value;
							$attribute_value 	= ucwords(str_replace("-"," ",$attribute_value));
												
							$new_item_attr_variation_id[$value->variation_id][$attribute_key] = $attribute_value;
							$new_item_attr_order_item_id[$value->order_item_id][$attribute_key] = $attribute_value;
							
							$attribute_labels[] = $attribute_key;
						}
					}
					
					$attribute_labels = array_unique($attribute_labels);
					sort($attribute_labels);
					
					//$this->print_array($new_item_attr_order_item_id);
					
					//By Variation ID
					if($variation_by == 'variation_id'){
						foreach($new_item_attr_variation_id as $id => $attribute_values){
							foreach($attribute_labels as $key2 => $value2){
								//$this->print_array($attribute_values);
								if(isset($attribute_values[$value2]))
									$new_item_attr_variation_id[$id]['varations'][] = $attribute_values[$value2];
							}
						}
						
						foreach($new_item_attr_variation_id as $id => $attribute_values){
							$new_item_attr_variation_id[$id]['varation_string'] 	= implode(", ",$attribute_values['varations']);					
							$variations[$id]['varation_string'] 					= implode(", ",$attribute_values['varations']);
						}
					}
					
					//$this->print_array($new_item_attr_variation_id);
					
					//By Order Item ID
					foreach($new_item_attr_order_item_id as $id => $attribute_values){
						foreach($attribute_labels as $key2 => $value2){
							//$this->print_array($attribute_values);
							if(isset($attribute_values[$value2]))
								$new_item_attr_order_item_id[$id]['varations'][] = $attribute_values[$value2];
						}
					}
					
					foreach($new_item_attr_order_item_id as $id => $attribute_values){
						$new_item_attr_order_item_id[$id]['varation_string'] 	= implode(", ",$attribute_values['varations']);					
						$order_item_variations[$id]['varation_string'] 		= implode(", ",$attribute_values['varations']);
					}
					
					//$this->print_array($order_item_variations);
					
					$return['attribute_keys']		= $attribute_keys;
					$return['variation_labels']		= $attribute_labels;
					$return['varation_string']		= $variations;
					$return['item_varation_string']	= $order_item_variations;
					$return['varation']				= $new_item_attr_variation_id;
					
					//$this->print_array($return);
					
					return $return;
			}
			
			function get_grid_items_variation_by_comma_separated($columns = array(),$order_items = array(),$group_by = 'order_item_id'){
				
				$group_by 			= $this->get_request('group_by',$group_by);
				if($group_by == 'variation_id'){
					$ids  					= $this->get_items_id_list($order_items,$group_by);
					$product_variation 		= $this->get_variaiton_attributes_comma_separated($group_by,$ids);
					$varation_string		= "varation_string";
				}else if($group_by == 'order_item_id'){
					$ids  					= $this->get_items_id_list($order_items,$group_by);
					$product_variation 		=  $this->get_variaiton_attributes_comma_separated($group_by,'',$ids);
					$varation_string		= "item_varation_string";
				}else{
					return $order_items;
				}
				
				//$this->print_array($product_variation);
				
				foreach($order_items as $rkey => $order_item ):
					foreach($columns as $key => $value):
						$td_value = "";
						switch ($key) {
							case "product_variation":
								$td_value = isset($product_variation[$varation_string][$order_item->$group_by]['varation_string']) ? $product_variation[$varation_string][$order_item->$group_by]['varation_string'] : '';
								break;
							default:
								$td_value = isset($order_item->$key) ? $order_item->$key : '';
								break;
							}						
							$order_items[$rkey]->$key 				= $td_value;						
						endforeach;
					endforeach;
				
					return $order_items;
			}
			
			function get_grid_items_variation_by_columner_separated($columns = array(),$order_items = array(),$group_by = 'order_item_id'){
				
				$group_by 			= $this->get_request('group_by',$group_by);
				if($group_by == 'variation_id'){
					$ids  					= $this->get_items_id_list($order_items,$group_by);
					$product_variation 		= $this->get_variaiton_attributes_columner_separated($group_by,$ids);
					$varation_string		= "varation_string";
					$product_variation 		= $this->constants['variations_by_variation_ids'];
				}else if($group_by == 'order_item_id'){
					$ids  					= $this->get_items_id_list($order_items,$group_by);
					$product_variation 		= $this->get_variaiton_attributes_columner_separated($group_by,'',$ids);
					$varation_string		= "item_varation_string";
					$product_variation 		= $this->constants['variations_by_order_item_ids'];
				}else{
					return $order_items;
				}
				
				$variation_attribute_keys 		= $this->constants['variation_attribute_keys'];
				//$this->print_array($variation_attribute_keys);
				
				$dash_label 					= __("-",'icwoocommerce_textdomains');
				foreach($order_items as $rkey => $order_item ):				
					$variation 				= isset($product_variation[$order_item->$group_by]) ? $product_variation[$order_item->$group_by] : array();//$this->get_variation_id_variation($order_item->variation_id);
					foreach($columns as $key => $value):
						$td_value = "";
						switch ($key) {
							default:
								if(in_array($key, $variation_attribute_keys)){
									$td_value = isset($variation[$key]) ? $variation[$key] : $dash_label;
								}else{
									$td_value = isset($order_item->$key) ? $order_item->$key : $dash_label;
								}
							break;
						}						
						$order_items[$rkey]->$key 				= $td_value;						
					endforeach;
				endforeach;					
				return $order_items;
			}
			
			function get_grid_items_variation($columns = array(),$order_items = array(),$group_by = 'order_item_id'){
				$show_variation		= $this->get_request('show_variation','');
				if($show_variation == 'variable'){
					$variation_column		= $this->get_request('variation_column','1');
					if($variation_column == 1){
						$order_items = $this->get_grid_items_variation_by_columner_separated($columns,$order_items,$group_by);
					}else if($variation_column == 0){
						$order_items = $this->get_grid_items_variation_by_comma_separated($columns,$order_items,$group_by);
					}
				}
				
				return $order_items;
			}
			
			function get_product_variation_attributes($all_columns = "no"){
				global $wpdb;	
				
				$product_addon_master 		= array();
				$product_addon_master_key 	= "";
				$product_attirbute_columns 	= array();
						
				$sql = "SELECT postmeta_product_addons.meta_value AS product_attributes FROM {$wpdb->prefix}posts AS posts";
				$sql .= " LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_product_addons ON postmeta_product_addons.post_id = posts.ID";
				$sql .= " WHERE post_type in ('product')";
				$sql .= " AND postmeta_product_addons.meta_key IN ('_product_attributes') ";
				$sql .= " AND postmeta_product_addons.meta_value NOT IN ('a:0:{}','') ";
				
				
				
				$product_addon_objects = $wpdb->get_results($sql);
				//$this->print_array($product_addon_objects);
				
				if(count($product_addon_objects)>0){					
					foreach($product_addon_objects as $key => $value){
						$product_attributes = isset($value->product_attributes) ? $value->product_attributes : '';
						if(!empty($product_attributes)){
							$product_addon_lists = unserialize($product_attributes);
							foreach($product_addon_lists as $key2 => $value2){
								$product_addon_master[] = $key2;
							}
						}
						//$this->print_array($product_addon_lists);
					}
				}
				
				
				//$product_addon_master 		= array();
				
				if(count($product_addon_master)>0){
					$product_addon_master = array_unique($product_addon_master);
					sort($product_addon_master);
					
					$product_addon_master_key = implode("','", $product_addon_master);
				}
				
				if($product_addon_master_key){
					$sql = "SELECT ";
				
					$sql .= " woocommerce_order_itemmeta.meta_key 							as attribute_key_label ";
					$sql .= " ,woocommerce_order_itemmeta.meta_value						as attribute_key_value ";
					
					$sql .= " , REPLACE(woocommerce_order_itemmeta.meta_key,'pa_','') 		as attribute_key ";
					//$sql .= " , woocommerce_order_itemmeta.meta_key 						as attribute_key ";				
					
					$sql .= " ,woocommerce_order_itemmeta.order_item_id 					as order_item_id ";
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_items 					AS woocommerce_order_items";
					$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id = woocommerce_order_items.order_item_id";
					$sql .= " WHERE 1*1";
					
					if($product_addon_master_key){
						$sql .= " AND woocommerce_order_itemmeta.meta_key IN ('{$product_addon_master_key}') ";
					}
					
					if($all_columns == "no"){
						$variation_itemmetakey = $this->get_request('variation_itemmetakey','-1');
						if($variation_itemmetakey and $variation_itemmetakey != '-1'){
							$sql .= " AND woocommerce_order_itemmeta.meta_key IN ('{$variation_itemmetakey}') ";
						}
					}
					
					$sql .= " GROUP BY woocommerce_order_itemmeta.meta_key";
					$sql .= " ORDER BY attribute_key";
					
					$items_objects = $wpdb->get_results($sql);
					
					//$this->print_sql($sql);
					//$this->print_array($items_objects);
					
					
					if(count($items_objects)>0){					
						foreach($items_objects as $key => $value){
							$product_addon_master[] = $key2;
							$product_attirbute_columns[strtolower($value->attribute_key_label)] = ucwords($value->attribute_key);
						}
					}
				}
				
				return $product_attirbute_columns;
				
				
			}
		////////////////Variation////////////////////
		
		////////////////Variation Fields Start//////
		function get_variation_dropdown_item(){
			global $wpdb;					
			$new_attr 			= array();
			$attribute_keys 	= array();
			$attribute_labels 	= array();
			$return 			= array();
			$variations 		= array();
			
			$new_item_attr_variation_id		= array();
			$new_item_attr_order_item_id	= array();
			$order_item_variations			= array();
			/*
			$sql = "SELECT TRIM(LEADING 'attribute_' FROM meta_key)  AS attribute_key  ";
			$sql .= " FROM {$wpdb->prefix}postmeta ";
			$sql .= " WHERE meta_key LIKE 'attribute%'";
			
			$sql .= " GROUP BY attribute_key ORDER BY attribute_key ASC";
			
			$attributes =  $wpdb->get_results($sql);
			
			if($attributes){
				foreach($attributes as $key => $value){						
					$attribute_keys[]	= $value->attribute_key;
				}
			}
			
			//$this->print_data($attribute_keys);					
			//$attribute_meta_key = implode("', '",$attribute_keys);
			//$attribute_meta_key = "order-period";
			*/
			$sql = "SELECT postmeta_product_addons.meta_value product_attributes FROM {$wpdb->prefix}posts AS posts";
			$sql .= " LEFT JOIN {$wpdb->prefix}postmeta AS postmeta_product_addons ON postmeta_product_addons.post_id = posts.ID";
			$sql .= " WHERE post_type in ('product')";
			$sql .= " AND postmeta_product_addons.meta_key IN ('_product_attributes') ";
			
			$product_addon_objects = $wpdb->get_results($sql);
			//$this->print_array($attributes);
			$product_addon_master = array();
			if(count($product_addon_objects)>0){					
				foreach($product_addon_objects as $key => $value){
					$product_addon_lists = unserialize($value->product_attributes);
					foreach($product_addon_lists as $key2 => $value2){
						$product_addon_master[] = $key2;
						//$attribute_keys2[]	= "wcv_".str_replace("pa_","",$key2);
					}
					//$this->print_array($product_addon_lists);
				}
			}
			
			$product_addon_master_key = "";
			if(count($product_addon_master)>0){
				$product_addon_master = array_unique($product_addon_master);
				sort($product_addon_master);
				
				$product_addon_master_key = implode("','", $product_addon_master);
			}
						
			$attribute_meta_key = $product_addon_master_key;
			
			$sql = "SELECT TRIM(LEADING 'pa_' FROM woocommerce_order_itemmeta.meta_key) AS attribute_key, 
					woocommerce_order_itemmeta.meta_value AS attribute_value, woocommerce_order_itemmeta.order_item_id, woocommerce_order_itemmeta.meta_key AS meta_key";		
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta";					
			
			$sql .= " WHERE woocommerce_order_itemmeta.meta_key IN ('{$attribute_meta_key}')";
			
			$sql .= " GROUP BY attribute_value ORDER BY attribute_key ASC";
			
			$item_attributes =  $wpdb->get_results($sql);
			if($item_attributes){
				foreach($item_attributes as $key => $value){
					$attribute_key 		= $value->attribute_key;
					$attribute_value	= $value->attribute_value;
					$attribute_value 	= ucwords(str_replace("-"," ",$attribute_value));												
					$new_item_attr_order_item_id[$value->meta_key][$value->attribute_value] = $attribute_value;
				}
			}		
			return $new_item_attr_order_item_id;
		}
		function remove_special_characters($string) {
		   $string = str_replace(' ', '_', $string);
		   $string = str_replace('-', '_', $string);
		   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		}
		
		function get_variation_dropdown(){
			$new_item_attr_order_item_id = $this->get_variation_dropdown_item();
			//$this->print_array($new_item_attr_order_item_id);
			$output = "";
			if(count($new_item_attr_order_item_id)>0){
				$vi = 0;
				$output .= '<div class="form-group dynamic_fields">';
				foreach($new_item_attr_order_item_id as $key => $values):
					$vl 	= str_replace("attribute_pa_","",$key);
					$vl 	= str_replace("pa_","",$vl);					
					$vl 	= str_replace("-"," ",$vl);
					$label 	= ucwords($vl);
					
					
					$id = str_replace(" ","_",$vl);
					
					$attr = array();
					foreach($values as $k => $v){
						$attr[] = $k;
					}						
					$detault =  $input = implode(",",$attr);
					$output .= '<div class="FormRow'.($vi%2 ? ' SecondRow' : ' FirstRow').' var_attr_'.$key.'">';
					$output .= '<div class="label-text"><label for="new_variations_value_'. $key.'">'.$label.':</label></div>';
					$output .= '<div class="input-text">';
						$attribute_values = $values;
						$output .= $this->create_dropdown($attribute_values,"new_variations_value[$key][]","new_variations_value_{$key}",'Select All',"variation_dropdowns",'-1', 'array', true, 5, $detault, false);
					$output .= '</div>';
					$output .= '</div>';
					$vi++;
				endforeach;
				$output .= '</div>';
			}
			return $output;
		}
		
		function get_cron_schedule($cron_schedule = array()){
			
			$cron_schedule = array(
				/*'minute' 		=> __("Once Minute",	'icwoocommerce_textdomains')
				,'five_minute' 	=> __("Once 5 Minutes",	'icwoocommerce_textdomains')
				,'ten_minute' 	=> __("Once 10 Minutes",'icwoocommerce_textdomains')
				,'hourly'		=> __("Once Hourly",	'icwoocommerce_textdomains')*/
				'daily'		=> __("Once Daily",		'icwoocommerce_textdomains')
				,'weekly'		=> __("Once Weekly",	'icwoocommerce_textdomains')
				
				/*,'twicehourly'	=> __("Twice Hourly",	'icwoocommerce_textdomains')
				,'twicedaily'	=> __("Twice Daily",	'icwoocommerce_textdomains')
				,'twiceweekly'	=> __("Twice Weekly",	'icwoocommerce_textdomains')*/				
			);
			
			return $cron_schedule;
		}
		
		////////////////Variation Fields End//////
		
		function create_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			//$this->print_array($request);
			foreach($request as $key => $value):
				if(is_array($value)){
					foreach($value as $akey => $avalue):
						if(is_array($avalue)){
							$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"".implode(",",$avalue)."\" />";
						}else{
							$output_fields .=  "<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"{$avalue}\" />";
						}
					endforeach;
				}else{
					$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" value=\"{$value}\" />";
				}
			endforeach;
			return $output_fields;
		}
		
		function create_search_form_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			foreach($request as $key => $value):
				$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />";
			endforeach;
			return $output_fields;
		}
		
		function get_limit_data($strat_limit = 5, $end_limit = 20, $page = "", $report_name = ""){
			$data = array();
			while($strat_limit <= $end_limit){
				$data[$strat_limit] = $strat_limit;
				if($strat_limit<20){
					$strat_limit = $strat_limit + 1;
				//else if($strat_limit<20)
					//$strat_limit = $strat_limit + 5;
				}else if($strat_limit<100)
					$strat_limit = $strat_limit + 10;
				else if($strat_limit<1000)
					$strat_limit = $strat_limit + 100;
				else if($strat_limit<3000)
					$strat_limit = $strat_limit + 500;
				else if($strat_limit<10000)
					$strat_limit = $strat_limit + 1000;
			}			
			return $data;
		}
		
	}//End Class
}