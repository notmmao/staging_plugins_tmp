<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once('class-wpfortune-base.php');

class WPF_Uploads_Before extends WPFortune_Base {

    /*
     * @var $wpf_uploads_instance WPF_Uploads instance
     */

    public $wpf_uploads_instance;

    public $cart_product_data;

    /*
     * When class is called, perform base actions
     *
     * @params string $settings_plugin_name The plugin name
     * @params string $settings_plugin_version The plugin version
     * @params string $settings_plugin_slug The plugin slug
     * @params string $settings_plugin_file The plugin file
     * @params string $settings_plugin_dir The plugin directory
     * @params string $settings_upgrade_url The plugin upgrade url
     * @params string $settings_renew_url The plugin renew subscription url
     * @params string $settings_docs_url The plugin docs url
     * @params string $settings_support_url The plugin support url
     */

    public function __construct($settings_plugin_name, $settings_plugin_version, $settings_plugin_id, $settings_plugin_slug, $settings_plugin_dir, $settings_plugin_file, $settings_upgrade_url, $settings_renew_url, $settings_docs_url, $settings_support_url)
    {

        parent::__construct($settings_plugin_name, $settings_plugin_version, $settings_plugin_id, $settings_plugin_slug, $settings_plugin_dir, $settings_plugin_file, $settings_upgrade_url, $settings_renew_url, $settings_docs_url, $settings_support_url);

        add_filter('query_vars', array($this, 'add_query_vars_filter'));

        add_action('plugins_loaded', array($this, 'translation_load_textdomain'));

        if (!in_array('woocommerce-uploads/woocommerce-uploads.php',get_option('active_plugins')) || !in_array('woocommerce/woocommerce.php',get_option('active_plugins'))) {

            add_action('admin_notices', array($this, 'main_plugin_not_active'));

        } else {
            add_action('init', array($this, 'init'));
            add_action('wp', array($this, 'wp_init'));

            add_action('wp_enqueue_scripts', array($this, 'wp_scripts'));
            add_filter('add_to_cart_redirect', array($this, 'add_to_cart_redirect'));

            add_action('woocommerce_add_to_cart', array($this, 'save_latest_added_cart_key'), 10, 1);  // Only cart key is needed

            add_action('woocommerce_checkout_order_processed', array($this, 'process_temp_uploads'));
            add_action('woocommerce_before_single_product', array($this, 'check_for_uploads'));
            add_action('woocommerce_cart_contents', array($this, 'cart_contents_uploads'));
            add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'delete_temp_upload_data'));

            add_action('woocommerce_before_cart', array($this, 'render_before_cart'));
            add_action('woocommerce_after_cart', array($this, 'render_after_cart'));

            add_action('woocommerce_add_order_item_meta', array($this, 'prepare_session_data'), 10, 3);

            add_filter( 'woocommerce_available_variation', array($this, 'variation_uploads_check'));


            if (is_admin()) {

                add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

            }

            if (get_option('wpf_umf_before_disable_cart_upload_button') != 1)
                add_filter( 'woocommerce_cart_item_name', array($this, 'add_cart_item_data'), 10, 3 );
            //register_activation_hook($this->plugin_file, array($this, 'check_main_plugin'));
            //register_deactivation_hook($this->plugin_file, array($this, 'remove_admin_notifications_schedule'));



        }

    }

    /*
     * On init, start session, perform some checks and perform the html upload
     */

    public function init()
    {

        global $wpf_uploads_instance;
        global $woocommerce;
        global $post;

        // We need sessions to store our temp upload data
        if (session_id() == '')
            session_start();

        if (!class_exists('WPF_Uploads') || empty($wpf_uploads_instance)) {
            //die('Fatal error: Main class not found');
        }

        $this->wpf_uploads_instance = $wpf_uploads_instance;

        add_action('wpf_umf_before_upload_save', array($this, 'save_temp_upload_data'));
        add_action('wpf_umf_before_upload_delete', array($this, 'delete_temp_upload_data'));
        add_action('wpf_umf_before_upload_description', array($this, 'before_upload_description_render'));
        add_action('wpf_umf_general_settings_before', array($this, 'add_to_settings_page'));

        // Html upload
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wpf_umf_upload_mode']) && $_POST['wpf_umf_upload_mode'] == 'before') {

            $this->cart_product_data = array(
                'cart_item_key' => $_POST['wpf_umf_cart_item_key'],
                'product_id' => $_POST['wpf_umf_product_id'],
                'variation_id' => $_POST['wpf_umf_variation_id'],
                'qty' => $_POST['wpf_umf_qty'],
                'name' => $_POST['wpf_umf_name'],
            );

            add_action('woocommerce_before_single_product', array($this, 'before_single_product'));
            add_action('woocommerce_after_single_product', array($this, 'after_single_product'));

        }


    }

    public function wp_init()
    {

        if (get_query_var('show') == 'uploads') {

            add_action('woocommerce_before_single_product', array($this, 'before_single_product'));
            add_action('woocommerce_after_single_product', array($this, 'after_single_product'));
            add_filter( 'body_class', array($this, 'add_body_class'));

        }



    }

    /*
     * Add extra class to body tag when on upload page
     * @param $classes array Array of current classes
     *
     * @since 1.0.8
     * @return array New classes
     */


    public function add_body_class($classes)
    {

	    $classes[] = 'has_uploads';
	    return $classes;

    }

    public function translation_load_textdomain()
    {

        load_plugin_textdomain($this->plugin_id, false, dirname( $this->plugin_file) . '/langs/');
        load_plugin_textdomain('woocommerce-uploads', false, 'woocommerce-uploads/langs/');


    }

    /*
     * Returns custom query vars
     *
     * @return array The query vars
     */

    public function add_query_vars_filter($vars) {

        $vars[] = 'show';
        $vars[] = 'vpid';
        $vars[] = 'ck';
        return $vars;

    }

     /*
     * Registers the plugin scripts and styles
     *
     * @return void
     */

    public function wp_scripts($hook)
    {

        wp_register_style($this->plugin_id.'_frontend', plugins_url($this->plugin_id.'/assets/css/style.css'));
    	wp_enqueue_style($this->plugin_id.'_frontend');

        wp_register_script($this->plugin_id.'_frontend', plugins_url($this->plugin_id.'/assets/js/main.js'), array('jquery'), null, true);
        wp_localize_script($this->plugin_id.'_frontend', 'wpf_umf_before_main', array(
                'plugin_url' => plugins_url($this->plugin_id),
                'add_to_cart_text' => __( 'Add to cart', 'woocommerce' ),
                'add_to_cart_with_upload_text' => __('Add to cart & upload files', $this->plugin_id),
        ));
        wp_enqueue_script($this->plugin_id.'_frontend');

    }

    /*
     * Registers the plugin scripts and styles for the administration panel
     *
     * @return void
     */

    public function admin_scripts($hook)
    {

        global $wp_scripts;

        // For admin page only
        if ($hook == 'woocommerce_page_'.$this->plugin_slug) {

            wp_register_script($this->plugin_id.'_admin', plugins_url($this->plugin_id.'/assets/js/admin.js'), array('jquery'));
            wp_enqueue_script($this->plugin_id.'_admin');

        }


    }

    /*
     * Add extra settings to WooCommerce Uploads setting page
     */

     public function add_to_settings_page()
     {

        include_once($this->plugin_dir.'/pages/admin/extra-settings.php');

     }

    /*
     * Check if uploads are enabled for current product, if so, change the button text
     */

    public function check_for_uploads()
    {

        global $post;

        if (get_post_meta($post->ID, '_wpf_umf_upload_enable', true) == 1) {

            add_filter( 'woocommerce_product_single_add_to_cart_text', array($this, 'wc_custom_add_to_cart_text'));

        }
    }

    /*
     * Saves the upload data temporary in session
     *
     * @param $data array Array containing all the upload data
     */

    public function save_temp_upload_data($data)
    {

        $_SESSION['wpf_umf_temp_data'] = order_uploads_merge_recursive($_SESSION['wpf_umf_temp_data'], $data);

    }

    /*
     * Removes the temporary upload data
     *
     * @param $data array Array|string containing the upload data
     * @param $cart_item_only boolean Whether to remove all files or one cart_item (product) only
     */

    public function delete_temp_upload_data($data)
    {

        global $woocommerce;

        if (is_array($data) && isset($data['product_id']) && !empty($data['product_id'])) {

            $upload = $_SESSION['wpf_umf_temp_data'][$data['product_id']][$data['item_number']][$data['uploader_type']][$data['file_number']];

            unset($_SESSION['wpf_umf_temp_data'][$data['product_id']][$data['item_number']][$data['uploader_type']][$data['file_number']]);

            @unlink($upload['thumb']);
            @unlink($upload['path']);

        } elseif (!empty($data)) {

            $cart_info = $woocommerce->cart->cart_contents[$data];

            $product_id = (!empty($cart_info['variation_id']))?$cart_info['variation_id']:$cart_info['product_id'];

            unset($_SESSION['wpf_umf_temp_data'][$product_id]);

        }


    }

    /*
     * Processes the temporary stored data and attach it to an order
     *
     * @param $order_id integer The order id
     * @return integer Amount of updated uploads
     */

    public function process_temp_uploads($order_id)
    {

        $temp_data = $_SESSION['wpf_umf_temp_data'];

        if (!empty($_SESSION['wpf_umf_temp_data']) && is_array($temp_data) && is_numeric($order_id)) {

  
            if (get_option('wpf_umf_order_number_type') == 'order_number') {
                $order = new WC_Order($order_id);
                $dir_prefix = sanitize_title($order->get_order_number());
            } else {
                $dir_prefix = $order_id;
            }

            $dest_file_path = get_option('wpf_umf_upload_path') . $dir_prefix;
            $dest_file_path_thumbs = $dest_file_path.'/thumbs';

            $c=0;
            // Create main dir
            if (wp_mkdir_p($dest_file_path)) {

                // Create thumbs dir
                if (wp_mkdir_p($dest_file_path_thumbs)) {

                      foreach ($temp_data AS $product_id => $item_numbers) {

                          foreach ($item_numbers AS $item_number => $upload_types) {

                              foreach ($upload_types AS $upload_type => $file_numbers) {

                                  foreach ($file_numbers AS $file_number => $data) {

                                        // Copy files to new location
                                        $filename = pathinfo($data['path'], PATHINFO_BASENAME);
                                        $old_filepath = pathinfo($data['path'], PATHINFO_DIRNAME);

                                        $new_file_path = $dest_file_path.'/'.str_replace('temp', $order_id, $filename);
                                        $new_file_path_thumb  = $dest_file_path_thumbs.'/'.str_replace('temp', $order_id, $filename);

                                        if (file_exists($data['path']))
                                            rename($data['path'], $new_file_path );

                                        if (file_exists($data['thumb'])) {
                                            rename($data['thumb'],  $new_file_path_thumb);
                                        } else {
                                            $new_file_path_thumb = null;
                                        }

                                        $data['path'] = $new_file_path;
                                        $data['thumb'] = $new_file_path_thumb;

                                        // Set new data
                                        $new_data[$product_id][$item_number][$upload_type][$file_number] = $data;
                                        $c++;
                                  }

                              }

                          }

                      }
                }

            }

            if (is_array($new_data)) {

                // Save to database
                $upload_data = new WPF_Uploads_Data($this->wpf_uploads_instance->plugin_id);
                $upload_data->meta_data = $new_data;
                $upload_data->save_order_meta_data($order_id);

            }

            // Delete all temporary files and directories
            array_map('unlink', glob($old_filepath.'/*.*'));
            array_map('unlink', glob($old_filepath.'/thumbs/*.*'));
            @rmdir($old_filepath.'/thumbs');
            @rmdir($old_filepath);

            // Remove from temp session
            unset($_SESSION['wpf_umf_temp_data']);

        }

        return $c;

    }

    /*
     * Shows the main plugin not activated message
     * @return void
     */

    public function main_plugin_not_active()
    {

        echo '<div class="error">
            <p>'.sprintf(__('WooCommerce Uploads and/or WooCommerce is not active. Please activate WooCommerce and WooCommerce Uploads before using %s.', $this->plugin_id), '<b>'.$this->plugin_name.'</b>').'</p>
        </div>';

    }

    /*
     * Before the single product is viewed
     * @return void
     */

    public function before_single_product()
    {

        global $post;
        global $woocommerce;

        if (get_post_meta($post->ID, '_wpf_umf_upload_enable', true) != 1) {
            return false;
        }

        $mp = $this->wpf_uploads_instance;

        // Html upload post
        $html_post_response = $mp->upload_html_post();

        /*
        $cart_product = $this->cart_product_data;
        $products[0] = $cart_product;
        */

        $cart_contents = $woocommerce->cart->cart_contents;

        $vpid_var = get_query_var('vpid');
        $var_check_id = (!empty($vpid_var))?$vpid_var:null;

        $key_check_id = get_query_var('ck');

        foreach ($cart_contents AS $key => $value) {

            if ($value['product_id'] == $post->ID && $key_check_id == $key) {

                if (!empty($value['variation_id']) && $var_check_id == $value['variation_id']) {

                    $cart_info[$key] = $value;
                    break;

                } elseif(empty($value['variation_id'])) {

                    $cart_info[$key] = $value;
                    break;

                }

            }

        }

        // If item is not in cart, redirect to product page
        if (empty($cart_info))
            wp_redirect(get_permalink($post->ID));


        if (is_array($cart_info)) {

            foreach ($cart_info AS $key => $product_cart_info) {

                $product = new WC_Product($product_cart_info['product_id']);

                // For variation display
                $variation = $woocommerce->cart->get_item_data($product_cart_info, true);

                $products[$key] = array(
                    'product_id' => $product_cart_info['product_id'],
                    'variation_id' => $product_cart_info['variation_id'],
                    'qty' => $product_cart_info['quantity'],
                    'name' => $product->get_title(),
                );

                $cart_product_data = array(
                    'product' => $product,
                    'cart_info' => $product_cart_info,
                    'variation' => $variation,
                );

            }

        }



        // Upload boxes
        $upload_products = WPF_Uploads::create_upload_boxes_array($products);

        // Current uploads
        if (isset($_SESSION['wpf_umf_temp_data']))
            $current_uploads = $_SESSION['wpf_umf_temp_data'];

        $upload_mode = 'before';

        if (is_array($upload_products)) {

            $this_plugin_id = $this->plugin_id;

            $this->plugin_id = $mp->plugin_id; // Quick fix for translations

            include_once($mp->plugin_dir . 'pages/frontend/upload-boxes.php');
            $this->plugin_id = $this_plugin_id; // Revert quick fix
        }

        // Hide the actual page
        echo '<div style="display: none;">';

    }

    /*
     * After the single product is viewed
     * @return void
     */

    public function after_single_product()
    {
        global $post;

        if (get_post_meta($post->ID, '_wpf_umf_upload_enable', true) != 1) {
            return false;
        }

        echo '</div>';

    }

    /*
     * When a product is added to the cart, redirect to upload page
     *
     * @return void
     */

    public function add_to_cart_redirect($url)
    {

        global $woocommerce;

        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );

        $variation_id = $_REQUEST['variation_id'];

        $check_id = (!empty($variation_id))?$variation_id:$product_id;

        if (WPF_Uploads::product_needs_upload($check_id, true)) {

            $query_args['show'] = 'uploads';

            if (isset($_SESSION['latest_cart_key']) && !empty($_SESSION['latest_cart_key'])) {
              $query_args['ck'] = $_SESSION['latest_cart_key'];
              unset($_SESSION['latest_cart_key']);
            }

            if (!empty($variation_id))
                $query_args['vpid'] = $variation_id;


           return add_query_arg($query_args, get_permalink($product_id));


        }

        return $url;

    }

    /*
     * The alternative button text
     *
     * @return string Button text
     */

    public function wc_custom_add_to_cart_text()
    {

        return __('Add to cart & upload files', $this->plugin_id);

    }

    /*
     * On the product upload page, show a short summary of the product where the customer uploads their files for
     *
     * @param array $product_data The product the customer needs to upload for
     * @return void
     */

    public function before_upload_description_render($product_data)
    {

        $product = $product_data['product'];
        $cart = $product_data['cart_info'];
        $variation = $product_data['variation'];

        include_once($this->plugin_dir.'/pages/frontend/upload-boxes-header.php');

    }

    /*
     * Extra message on the bottom of the cart
     */

    public function cart_contents_uploads($order)
    {
        $custom_cart_message = get_option('wpf_umf_before_custom_cart_message');
        echo '<tr class="wpf-umf-upload-totals"><td colspan="8">'.((!empty($custom_cart_message))?$custom_cart_message:__('You can always add, change or remove your uploads after checkout.', $this->plugin_id)).'</td></tr>';


    }

    /*
     * Adds a button for each ordered product to the cart overview table
     *
     * @param string $item_titke The current item title
     * @param array $cart_item The current cart item (product)
     *
     * @return string The data to be rendered
     */

    public function add_cart_item_data($item_title, $cart_item = null, $cart_item_key = null)
    {

        global $woocommerce;

        if($cart_item == null)
            return $item_title;

        $cart_data = $cart_item['data'];

        $url_args['show'] = 'uploads';

        if (!empty($cart_item['variation_id'])) {
          $url_args['vpid'] = $cart_item['variation_id'];
        }

        $url_args['ck'] = $cart_item_key;

        $return = $item_title;

        if (!empty($cart_item['variation_id'])) {
          $need_uploads = WPF_Uploads::product_needs_upload($cart_item['variation_id'], true);
        } else {
          $need_uploads = WPF_Uploads::product_needs_upload($cart_data->post->ID);
        }

        if ($need_uploads)
            $return .= '<br /><a href="'.add_query_arg($url_args, get_post_permalink($cart_data->post->ID)).'" class="wpf-umf-cart-upload-button button">'.__('Upload / View files', $this->plugin_id).'</a>';

        return $return;

    }

    /*
     * Renders a div before the cart
     */

     public function render_before_cart()
     {


        echo '<div id="wpf-umf-uploads-cart">';

        if (get_option('wpf_umf_before_uploads_required') == 1) {

            $uploads_needed = $this->uploads_needed_for_cart();

            include_once($this->plugin_dir.'/pages/frontend/uploads-needed.php');

        }


     }

     /*
      * Renders the closing div after the cart
      */

     public function render_after_cart()
     {

        echo '</div>';

     }

     /*
      * Checks which products needs upload(s) for the current cart
      */

     public function uploads_needed_for_cart()
     {

        global $woocommerce;

        // Check if products in cart needs upload

        $cart_contents = $woocommerce->cart->cart_contents;

        if (is_array($cart_contents)) {

            // Each product in cart
            foreach ($cart_contents AS $key => $data) {

                $main_product_id = (!empty($data['variation_id']))?$data['variation_id']:$data['product_id'];

                $unique_product_key = self::get_unique_product_key($main_product_id, $key);

                $quantity = $data['quantity'];

                // Get upload set for product
                $post_meta = get_post_meta($data['product_id'], '_wpf_umf_upload_set');
                $post_meta = $post_meta[0];

                // Count current uploaded files
                if (isset($_SESSION['wpf_umf_temp_data'][$unique_product_key]))
                    $uploaded = $_SESSION['wpf_umf_temp_data'][$unique_product_key];

                $current_count = 0;

                if (isset($uploaded) && is_array($uploaded)) {

                  foreach ($uploaded AS $item_number => $upload_types) {
                      foreach ($upload_types AS $upload_type => $file_numbers) {
                         foreach ($file_numbers AS $file_number => $upload_info) {

                            if (!empty($upload_info['name']))
                                $current_count++;

                         }
                      }
                  }


                }

                // Count at least needed uploads
                if (get_option('wpf_umf_before_use_amount') == 1) {

                      $at_least_needed = WPF_Uploads::product_needs_upload($main_product_id, true);

                } else {

                    if (!empty($data['variation_id'])) {

                        if (WPF_Uploads::product_needs_upload($main_product_id, true)) {
                            $at_least_needed = 1;
                        }

                    } else {
                        $at_least_needed = WPF_Uploads::product_needs_upload($main_product_id);
                    }
                }

                // Per quantity or not, depending on upload procedure setting
                if (get_option('wpf_umf_upload_procedure') == 'multiple' && get_option('wpf_umf_before_use_upload_procedure') == 1) {

                  $at_least_needed = $at_least_needed * $quantity;

                }



                if ($current_count < $at_least_needed) {

                    $name = $data['data']->get_title();

                    $extra = $woocommerce->cart->get_item_data($data, true);

                    if (!empty($extra)) {
                      $name .= ' ( '.$extra.' )';
                    }

                    $array[$unique_product_key] = array(
                      'name' => $name,
                    );

                }

                unset($uploaded);

            }

            if (isset($array))
                return $array;
            else
                return false;


        }

     }

     /*
      * Add upload info to available variations
      *
      * @return array Variations
      * @since 1.1.6
      */

     public function variation_uploads_check($variation) {

        $variation['can_upload'] = WPF_Uploads::product_needs_upload($variation['variation_id'], true);

        return $variation;

     }

     /*
     * Create a unique product key
     * @since 1.0.8
     */

    public static function get_unique_product_key($product_id, $unique_id)
    {

        if (get_option('wpf_umf_dynamic_variation') == 1) {

            // Support for older versions
            if (!empty($unique_id))
                $unique_product_key = $product_id.'#'.$unique_id;
            else
                $unique_product_key = $product_id;

        } else {

            $unique_product_key = $product_id;

        }

        return $unique_product_key;

    }

    /*
     * Prepare session data for attaching it to an order
     *
     * @since 1.0.8
     */

    public function prepare_session_data($item_id, $values, $cart_item_key)
    {

        if (isset($_SESSION['wpf_umf_temp_data']) && is_array($_SESSION['wpf_umf_temp_data'])) {

            $main_product_id = (!empty($values['variation_id']))?$values['variation_id']:$values['product_id'];

            $old_key = self::get_unique_product_key($main_product_id, $cart_item_key);
            $new_key = self::get_unique_product_key($main_product_id, $item_id);

            $_SESSION['wpf_umf_temp_data'][$new_key] = $_SESSION['wpf_umf_temp_data'][$old_key];

            if ($old_key != $new_key)
                unset($_SESSION['wpf_umf_temp_data'][$old_key]);

        }

    }

    /*
     * Save latest added cart key into session, needed for correct redirection
     *
     * @since 1.0.8
     */

     public function save_latest_added_cart_key($cart_item_key)
     {

        $_SESSION['latest_cart_key'] = $cart_item_key;

     }


}

?>