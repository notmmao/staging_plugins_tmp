<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once('class-wpfortune-base.php');
require_once('class-wpf-uploads-data.php');
require_once('class-wpf-uploads-upload.php');

class WPF_Uploads extends WPFortune_Base {

    /*
     * @var $upload_mode string the mode, used to upload a file
     */

    public $upload_mode = 'local';

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

        // Cron
        register_activation_hook($this->plugin_file, array($this, 'create_admin_notifications_schedule'));
        register_deactivation_hook($this->plugin_file, array($this, 'remove_admin_notifications_schedule'));

        add_action('wpf_umf_send_admin_notifications', array($this, 'send_admin_notifications'));

        add_action('plugins_loaded', array($this, 'translation_load_textdomain'));

        if (!in_array('woocommerce/woocommerce.php',get_option('active_plugins'))) {

            add_action('admin_notices', array($this, 'woocommerce_not_active'));

        } else {

            add_filter('woocommerce_locate_template', array($this, 'locate_plugin_template'), 10, 3 );

            if (is_admin()) {

                // Frontend - ajax upload
                add_action('wp_ajax_wpf_umf_ajax_upload',  array($this, 'upload_ajax_post'));
                add_action('wp_ajax_nopriv_wpf_umf_ajax_upload',  array($this, 'upload_ajax_post'));

                // Front - ajax delete upload
                add_action('wp_ajax_wpf_umf_ajax_delete_upload',  array($this, 'upload_ajax_delete'));
                add_action('wp_ajax_nopriv_wpf_umf_ajax_delete_upload',  array($this, 'upload_ajax_delete'));

                // Backend upload approve
                add_action('wp_ajax_wpf_umf_uploads_approve',  array($this, 'admin_ajax_uploads_approve'));

                // Backend upload approve email
                add_action('wp_ajax_wpf_umf_order_uploads_email',  array($this, 'admin_ajax_uploads_approve_email'));

                $this->install_update();

                add_action('admin_menu', array($this, 'admin_menu'));
                add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
                add_action('add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
                add_action('save_post_product', array ( $this, 'uploadset_post'));


            } else {

                add_action('wp', array($this, 'init_boxes') );
                add_action('wp_enqueue_scripts', array($this, 'wp_scripts'));

            }

            add_action('woocommerce_email_after_order_table', array($this, 'add_order_email_data'));

        }


    }

    public function init_boxes()
    {

         $this->disable_cache();

         // Show upload boxes
         if (get_option('wpf_umf_thank_you_page') == 1 && is_checkout()) {

            add_action('woocommerce_order_details_after_order_table', array($this, 'upload_boxes_all_products_render'));

         } elseif (!is_checkout()) {

            if (get_option('wpf_umf_position') == 'after') {
                add_action('woocommerce_order_details_after_order_table', array($this, 'upload_boxes_all_products_render'));
            } else {
                add_action('woocommerce_view_order', array($this, 'upload_boxes_all_products_render'), 5);
            }

         }

    }

    /*
     * Disable caching
     * @since 1.0.9
     */

    private function disable_cache()
    {

        if (!defined( 'DONOTCACHEPAGE'))
		    define( "DONOTCACHEPAGE", "true" );

    }


    public function translation_load_textdomain()
    {

        load_plugin_textdomain($this->plugin_id, false, dirname( $this->plugin_file) . '/langs/');

    }

    /*
     * Sets the woocommerce template directory
     *
     * @param string $template The current template
     * @param string $template_name Template name
     * @param string $template_path The template path
     *
     * @return string The template to use
     */


    public function locate_plugin_template($template, $template_name, $template_path)
    {

        global $woocommerce;

        $_template = $template;

        if ( ! $template_path ) $template_path = $woocommerce->template_url;

        $plugin_path  = $this->plugin_dir . '/templates/';

        $template = locate_template(array(
            $template_path . $template_name,
            $template_name
        ));


        if ( ! $template && file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;

        // Use default template

        if ( ! $template )

          $template = $_template;

        // Return what we found

        return $template;

    }

    /*
     * Renders the upload boxes
     *
     * @param $order WC_Order
     * @return void
     */

    public function upload_boxes_all_products_render($order)
    {

        if (is_numeric($order)) {

            $order = new WC_Order($order);

        }

        if (!in_array($order->status, get_option('wpf_umf_statuses')))
            return false;

        $order_number = $order->id;

        $html_post_response = $this->upload_html_post($order_number);

        // Get current uploads
        $current_uploads = WPF_Uploads_Data::get_uploads_by_order($order->id);

        // All products for this order id
        $products = $order->get_items();

        // Upload boxes
        $upload_products = self::create_upload_boxes_array($products);

        if (is_array($upload_products)) {

            include_once($this->plugin_dir . 'pages/frontend/upload-boxes.php');

        }

    }


    /*
     * When an ajax upload is performed
     *
     * @return string Json data
     */

    public function upload_ajax_post()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Set session id
            if (isset($_REQUEST['wpf_umf_sid']) && !empty($_REQUEST['wpf_umf_sid'])) {

                $old_sid = session_id();
                session_id($_REQUEST['wpf_umf_sid']);

            }

            if(!wp_verify_nonce($_POST['_wpf_umf_nonce'], 'uploads'))
                die('Security error');

            $set_data = self::get_upload_set_data(WPF_Uploads::split_raw_product_id($_POST['product_id'], 'product_id'), $_POST['upload_type']);

            $upload = new WPF_Uploads_Upload($this->plugin_id, $_FILES['file'], $_POST['order_id'], $_POST['product_id'], $_POST['product_item_number'], $_POST['upload_type'], $_POST['file_number'], get_option('wpf_umf_upload_path'), $set_data, $_POST['upload_mode']);

            if ($this->upload_mode == 'local') {
                $response = $upload->upload_local();
            }

            // Get newly uploaded file
            if (!empty($_POST['order_id'])) {

                $order_uploads = get_post_meta($_POST['order_id'], '_wpf_umf_uploads');
                $order_upload = $order_uploads[0];
                
                $upload = $order_upload[$_POST['product_id']][$_POST['product_item_number']][$_POST['upload_type']][$_POST['file_number']];

            }  else {

                $upload = $_SESSION['wpf_umf_temp_data'][$_POST['product_id']][$_POST['product_item_number']][$_POST['upload_type']][$_POST['file_number']];

            }

            $upload_info = array(
                'unique_product_key' => $_POST['product_id'],
                'product_id' => $_POST['product_id'],
                'item_number' => $_POST['product_item_number'],
                'uploader_type' => $_POST['upload_type'],
                'file_number' => $_POST['file_number'],
            );

            $upload_mode = $_POST['upload_mode'];

            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

            // If fully uploaded (no chunk)
            if (!$chunks || $chunk == $chunks - 1) {
                $response['html'] = include $this->plugin_dir . 'pages/frontend/_single-uploaded-file.php';
            }

            echo json_encode($response);

            die();

        } else {

            die('Security error');

        }

    }

    /*
     * Delete a single file
     */

    public function upload_ajax_delete()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Set session id
            if (isset($_REQUEST['wpf_umf_sid']) && !empty($_REQUEST['wpf_umf_sid'])) {

                $old_sid = session_id();
                session_id($_REQUEST['wpf_umf_sid']);

            }

            if(!wp_verify_nonce($_POST['_wpf_umf_nonce'], 'uploads-delete'))
                die('Security error');

            if ($_POST['uploadmode'] == 'before') {

                $data = array(
                    'product_id' => $_POST['product_id'],
                    'item_number' => $_POST['item_number'],
                    'uploader_type' => $_POST['uploader_type'],
                    'file_number' => $_POST['file_number'],
                );

                do_action('wpf_umf_before_upload_delete', $data);

                $success = 1;

            } elseif (is_numeric($_POST['order_id']) && !empty($_POST['product_id']) && is_numeric($_POST['item_number']) && is_numeric($_POST['uploader_type']) && is_numeric($_POST['file_number'])) {

                if (self::order_owner_check($_POST['order_id'])) {

                    $order_meta = get_post_meta($_POST['order_id'], '_wpf_umf_uploads');
                    $order_meta = $order_meta[0];

                    $upload = $order_meta[$_POST['product_id']][$_POST['item_number']][$_POST['uploader_type']][$_POST['file_number']];

                    if (isset($upload)) {

                      // Remove from array
                      unset($order_meta[$_POST['product_id']][$_POST['item_number']][$_POST['uploader_type']][$_POST['file_number']]);

                      // Remove file
                      @unlink($upload['path']);

                      if (!empty($upload['thumb']))
                            @unlink($upload['thumb']);

                      update_post_meta($_POST['order_id'], '_wpf_umf_uploads', $order_meta);

                      update_post_meta($_POST['order_id'], '_wpf_umf_uploads_changed', 1);

                      $success= true;

                    }

                } else {
                   $response['error'] = sprintf(__('Error #%s - Please contact the owner.', $this->plugin_id), '1');
                }

            } else {
                $response['error'] = sprintf(__('Error #%s - Please contact the owner.', $this->plugin_id), '2');
            }

            if (empty($response['error']) && $success) {
                $response['success'] = 1;
            } else {
                $response['success'] = 0;
            }

            echo json_encode($response);

            die();

        }


    }

    /*
     * When a html upload is performed
     *
     * @param $order_number string|int The order number, used to create the upload directory / filename
     *
     * @return array Response data
     */

    public function upload_html_post($order_number = null)
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_wpf_umf_nonce'])) {

            if(!wp_verify_nonce($_POST['_wpf_umf_nonce'], 'uploads'))
                die('Security error');

            $files = self::fix_files_array($_FILES['wpf_upload']);

            if (is_array($files)) {

                // Product
                foreach ($files AS $product_id => $product_item_numbers) {

                    // Product item number
                    foreach ($product_item_numbers AS $product_item_number => $uploadtype) {

                        // Single upload set
                        foreach ($uploadtype AS $uploadtype_key => $uploadtype_files) {

                            $set_data = self::get_upload_set_data(WPF_Uploads::split_raw_product_id($product_id, 'product_id'), $uploadtype_key);

                            // Each file in this set
                            foreach ($uploadtype_files AS $key => $file) {

                                if (!empty($file['name'])) {

                                    if ($this->upload_mode == 'local') {

                                        $upload = new WPF_Uploads_Upload($this->plugin_id, $file, $order_number, $product_id, $product_item_number, $uploadtype_key, $key, get_option('wpf_umf_upload_path'), $set_data, $_POST['wpf_umf_upload_mode']);
                                        $result = $upload->upload_local();

                                        if ($result['OK']) {
                                            $response[$product_id][$product_item_number][$uploadtype_key][$key]['success'] = 1;
                                        } else {
                                            $response[$product_id][$product_item_number][$uploadtype_key][$key]['success'] = 0;
                                            $response[$product_id][$product_item_number][$uploadtype_key][$key]['error'] = $result['info'];
                                        }

                                    }
                                }
                            }
                        }
                    }
                }

            }

            return $response;
        }

    }

    /*
     * Check if files need uploads, if so, add some text to the customer email
     *
     * @param $order WC_Order
     */

    public function add_order_email_data($order)
    {

        $upload_check = self::order_needs_upload($order, true);

        $email_link = get_option('wpf_umf_email_link');

        if ($upload_check > 0) {

            $email_text_option = get_option('wpf_umf_email_text');

            // Get link text
            if ($upload_check > 1) {
                $email_text = (!empty($email_text_option))?$email_text_option['plural']:__('Login to upload your files and attach them to your order.', $this->plugin_id);
            } else {
                $email_text = (!empty($email_text_option))?$email_text_option['singular']:__('Login to upload your file and attach them to your order.', $this->plugin_id);
            }

            // Determine link
            switch ($email_link) {

                case 0:
                    echo '<h2>'.__('Upload file(s)', $this->plugin_id).'</h2>';
                    echo '<a href="'.$order->get_view_order_url().'">'.$email_text.'</a>';
                    break;
                case 1:
                    echo '<h2>'.__('Upload file(s)', $this->plugin_id).'</h2>';
                    echo '<a href="'.get_permalink(get_option('wpf_umf_order_detail_page')).'">'.$email_text.'</a>';
                    break;

            }

        }

    }

    /*
     * Creates a secret file url
     *
     * @param string @file_path The full file path
     *
     * @return string The url to be showed
     */

    public function create_secret_url($file_path)
    {
        $url = $this->plugin_url().'includes/get-file.php?p=';
        return $url . base64_encode($file_path) . substr(md5(rand(0, 999999)), 0, 10);

    }

    /*
     * Creates a secret image url
     *
     * @param string @file_path The full file path
     *
     * @return string The url to be showed
     */

    public function create_secret_image_url($file_path)
    {
        $url = $this->plugin_url().'includes/get-image.php?p=';
        return $url . base64_encode($file_path) . substr(md5(rand(0, 999999)), 0, 10);

    }

    /*
     * Checks if currently logged in user has access to this order id
     *
     * @param integer $order_id The order id
     * @return boolean Whether the order is of a logged in user
     */

    public static function order_owner_check($order_id) {

        $order = new WC_Order( $order_id );
        $user_id = $order->user_id;

        if ($user_id == get_current_user_id())
            return true;
        else
            return false;

    }

    /*
     * Get the main product id
     * If it's a child post, the parent post id is returned.
     * Else the current post id is returned
     *
     * @param integer $product_id The product id
     * @return integer The main product id
     */

    public static function get_main_product_id($product_id)
    {

        $parent_id = wp_get_post_parent_id($product_id);

        return (!empty($parent_id))?$parent_id:$product_id;
    }

    /*
     * Get upload set data for specific product / uploadtype combination
     *
     * @param int $product_id The product id
     * @param int $uploadtype The upload type id
     *
     * @return array Array containing all the upload set data
     */

    public static function get_upload_set_data($product_id, $uploadtype)
    {

        $product_id = self::get_main_product_id($product_id);

        $upload_set = get_post_meta($product_id, '_wpf_umf_upload_set');

        return $upload_set[0][$uploadtype];

    }

    /*
     * Creates a normal multidimensional array out of a $_FILES array
     *
     * @param $files array The $_FILES[xxx] array
     * @return array The converted array
     */

    public static function fix_files_array($files)
    {

        if (isset($files['name']) && is_array($files['name'])) {

            foreach ($files['name'] AS $product_id => $item_numbers) {

                foreach ($item_numbers AS $item_number => $upload_types) {

                    foreach ($upload_types AS $upload_type => $file_numbers) {

                        foreach ($file_numbers AS $file_number => $value) {

                            $new_array[$product_id][$item_number][$upload_type][$file_number] = array(
                                'name' => $files['name'][$product_id][$item_number][$upload_type][$file_number],
                                'tmp_name' => $files['tmp_name'][$product_id][$item_number][$upload_type][$file_number],
                                'size' => $files['size'][$product_id][$item_number][$upload_type][$file_number],
                                'type' => $files['type'][$product_id][$item_number][$upload_type][$file_number],
                                'error' => $files['error'][$product_id][$item_number][$upload_type][$file_number],
                            );

                        }

                    }

                }

            }


        }

        return $new_array;
    }

    /*
     * Creates an array which contains all the necessary upload boxes
     *
     * @param $products array Array of the products (of an order for example) or a single product
     * @return array Array of upload sets
     */

    public static function create_upload_boxes_array($products)
    {


        if (is_array($products)) {

            // For each product in this order
            foreach ($products AS $order_item => $product) {

                $product_meta = get_post_meta($product['product_id']);

                // Variation support
                if (!empty($product['variation_id'])) {
                    $product_id = $product['variation_id'];
                    $is_variation = true;
                } else {
                    $product_id = $product['product_id'];
                }

                if (isset($product['item_meta'])) {
                    $item_meta = new WC_Order_Item_Meta( $product['item_meta'] );
                    $variation = $item_meta->display($flat=true,$return=true);
                } else {
                    $variation = null;
                }

                // If upload for this product is enabled
                if ($product_meta['_wpf_umf_upload_enable'][0] == 1) {

                    $unique_product_key = self::get_unique_product_key($product_id, $order_item);

                    $array[$unique_product_key] = array(
                        'name' => $product['name'],
                        'variation' => $variation,
                        'quantity' => $product['qty'],
                    );

                    // Depending on the upload procedure setting
                    $upload_sets_per_product = (get_option('wpf_umf_upload_procedure') == 'multiple')?(int)$product['qty']:1;

                    $upload_set = unserialize($product_meta['_wpf_umf_upload_set'][0]);

                    for($i=1; $i<=$upload_sets_per_product; $i++) {

                        foreach ($upload_set AS $upload_box_id => $upload_box_data) {

                            $continue = true;

                            // Variation support, @since 1.1.0
                            if (isset($is_variation) && $is_variation) {

                                if (is_array($upload_box_data['variation_show'])) {
                                    if (!in_array($product_id, $upload_box_data['variation_show'])) {
                                      $continue = false;
                                    }
                                }

                            }

                            if ($continue)
                                $array[$unique_product_key]['boxes'][$i][$upload_box_id] = $upload_box_data;

                            $continue = true;

                        }

                    }

                }

            }

        }

        return $array;

    }

    /*
     * Get the name of a upload type
     *
     * @param $product_id integer The product id
     * @param $upload_type_id The id of the upload type
     *
     * @return false|string Name of the upload type if found, or false if not found
     */

    public static function get_upload_type_name($product_id, $upload_type_id) {

        $meta_data = get_post_meta($product_id, '_wpf_umf_upload_set');
        $upload_set_data = $meta_data[0];

        if (is_array($upload_set_data)) {
            return $upload_set_data[$upload_type_id]['title'];
        } else {
            return false;
        }

    }

    /*
     * Registers the plugin scripts and styles for the administration panel
     *
     * @return void
     */

    public function wp_scripts($hook)
    {

        if (get_option('wpf_umf_enable_styling') == 1) {
            wp_register_style($this->plugin_id.'_frontend', plugins_url($this->plugin_id.'/assets/css/style.css'));
    	    wp_enqueue_style($this->plugin_id.'_frontend');
        }

        wp_enqueue_style('dashicons');

        // Only needed when the ajax laoder is active, saves resources
        if (get_option('wpf_umf_uploader') == 'ajax') {

            wp_register_script($this->plugin_id.'_frontend_uploader', plugins_url($this->plugin_id.'/assets/js/uploader.js'), array('jquery', 'plupload-all'), null, true);
            wp_localize_script($this->plugin_id.'_frontend_uploader', 'wpf_umf_uploader', array(
                'plugin_url' => plugins_url($this->plugin_id),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                'max_amount_uploads_reached' => __('You have reached the maximum amount of uploads', $this->plugin_id),
                'max_chunk_size' => get_option('wpf_umf_uploader_chunksize'),
                'dropzone' => get_option('wpf_umf_uploader_dropzone'),
                'autostart' => get_option('wpf_umf_uploader_autostart'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('uploads'),
                'sid' => session_id(),
            ));
            wp_enqueue_script($this->plugin_id.'_frontend_uploader');

        }

        wp_register_script($this->plugin_id.'_frontend_main', plugins_url($this->plugin_id.'/assets/js/main.js'), array('jquery'), null, true);
        wp_localize_script($this->plugin_id.'_frontend_main', 'wpf_umf_main', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('uploads-delete'),
                'delete_confirm' => __('Are you sure you want to delete this file?', $this->plugin_id),
                'sid' => session_id(),


        ));
        wp_enqueue_script($this->plugin_id.'_frontend_main');


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
        if ($hook == 'woocommerce_page_'.$this->plugin_slug || $hook == 'post.php') {

            wp_register_style($this->plugin_id.'_admin', plugins_url($this->plugin_id.'/assets/css/admin.css'));
            wp_enqueue_style($this->plugin_id.'_admin');

            wp_register_script($this->plugin_id.'_admin', plugins_url($this->plugin_id.'/assets/js/admin.js'), array('jquery', 'jquery-ui-slider'));
            wp_localize_script($this->plugin_id.'_admin', 'wpf_umf_admin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'approve_email_success' => __('E-mail successfully sent', $this->plugin_id),
                'enable_current_products_confirm' => __('Are you sure you want to enable uploads for all current products? The default uploadset will be used for this.'),
            ));

            wp_enqueue_script($this->plugin_id.'_admin');

            wp_enqueue_style("jquery-ui-css", "http://ajax.googleapis.com/ajax/libs/jqueryui/{$wp_scripts->registered['jquery-ui-core']->ver}/themes/ui-lightness/jquery-ui.min.css");

        }

        wp_register_style($this->plugin_id.'_uploadset', plugins_url($this->plugin_id.'/assets/css/uploadset.css'));
    	wp_enqueue_style($this->plugin_id.'_uploadset');

        wp_register_script($this->plugin_id.'_uploadset', plugins_url($this->plugin_id.'/assets/js/uploadset.js'), array('jquery', 'jquery-ui-sortable'));

        $translation_array = array(
            'cant_delete_upload_box' => __('Can\'t delete upload box. There must be at least one upload box.', $this->plugin_id),
            'less_info' => __('Less settings', $this->plugin_id),
            'more_info' => __('More settings', $this->plugin_id),
        );

        wp_localize_script($this->plugin_id.'_uploadset', 'uploadset_message', $translation_array );
        wp_enqueue_script($this->plugin_id.'_uploadset');

    }

    /*
     * Check and install / update the plugin settings
     */

     public function install_update()
     {

        if (get_option('wpf_umf_installed') === false) {

            // Save default or old (if a previous version is installed) settings
            $upload_data = new WPF_Uploads_Data($this->plugin_id);
            $upload_data->settings = $upload_data->default_settings(true, array('wpf_umf_version' => $this->version));
            $upload_data->save_settings();

            // Old upload sets
            WPF_Uploads_Data::update_old_upload_sets();

            if (get_option('wpf_umf_installed') == 1)
                add_action('admin_notices', array($this, 'plugin_installed_ok_notice'));
            else
                add_action('admin_notices', array($this, 'plugin_installed_error_notice'));
        }

     }

     /*
      * Notice if plugin was successfully installed
      */

     public function  plugin_installed_ok_notice()
     {

        echo '<div class="update-nag">
            '.sprintf(__('%s was installed successfully. If we found settings of a previous version, we tried to restore your settings. Please check this before you use this plugin.', $this->plugin_id), '<b>'.$this->plugin_name.'</b>').'
        </div>';

     }

     /*
      * Notice if plugin there was an error on installing the plugin
      */

     public function  plugin_installed_error_notice()
     {

        echo '<div class="error">
            '.sprintf(__('There was an error installing %s. Please contact the owner of this plugin.', $this->plugin_id), '<b>'.$this->plugin_name.'</b>').'
        </div>';

     }

     /*
      * Notice if WooCommerce isn't installed yet
      */

    public function woocommerce_not_active()
    {
        echo '<div class="error">
            <p>'.sprintf(__('WooCommerce is not active. Please activate WooCommerce before using %s.', $this->plugin_id), '<b>'.$this->plugin_name.'</b>').'</p>
        </div>';
    }


    /*
     * Creates the admin menu
     *
     * @return void
     */

    public function admin_menu()
    {

        add_submenu_page('woocommerce', $this->plugin_name, __( 'Uploads', $this->plugin_id), 'manage_woocommerce', $this->plugin_slug, array($this, 'admin_render'));

        // Enable old products
        if (isset($_GET['enable_current_products']) && $_GET['enable_current_products'] == 1) {
          $this->enable_upload_current_products();
        }

        $this->admin_post();

    }

    /*
     * Add meta boxes to the administration sections
     *
     * @return void
     */


    public function add_meta_boxes()
    {

		// Show upload sets on product page
		add_meta_box('wpf-umf-uploadset', __('Uploads', $this->plugin_id ), array($this, 'uploadset_render'), 'product', 'normal', 'high' );

        // Show uploads on order page
        add_meta_box('wpf-umf-order-uploads', __( 'Uploaded Files', $this->plugin_id), array($this, 'order_uploads_render'), 'shop_order', 'side', 'default' );

    }

    /*
     * Renders the admin page
     *
     * @return void
     */

    public function admin_render()
    {

        $this->catch_admin_actions();

        include_once($this->plugin_dir . 'pages/admin/main.php');

    }

    /*
     * Catch specific actions on plugin settings page
     */

    private function catch_admin_actions()
    {

        if (isset($_GET['download-all']) && isset($_GET['order']) && $_GET['download-all'] == 'true' && is_numeric($_GET['order'])) {

            $uploads = WPF_Uploads_Data::get_uploads_by_order($_GET['order']);

            if (is_array($uploads)) {

                 // Loop through it
                foreach ($uploads AS $product_id => $item_numbers) {
                  foreach ($item_numbers AS $item_number => $upload_types) {
                    foreach ($upload_types AS $upload_type => $file_numbers) {
                      foreach ($file_numbers AS $file_number => $data) {
                            $zip_files[$data['path']] = $item_number.'_'.$file_number.'_'.$data['name'];
                      }
                    }
                  }
                }

                self::zip_files_and_download($zip_files, 'uploads-'.$_GET['order'].'.zip');


            }

        }

    }


    /*
     * Save settings / upload sets on admin page
     *
     * @return void
     */

    private function admin_post()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wpf_umf_post_page']) && $_POST['wpf_umf_post_page'] == 'settings') {

            if (check_admin_referer($this->plugin_slug.'_post')) {

                $upload_data = new WPF_Uploads_Data($this->plugin_id);
                $upload_data->settings = $_POST;
                $success = $upload_data->save_settings();

                // Cron activation / deactivation stuff
                if (get_option('wpf_umf_notifications_enable') == 1) {
                    $this->create_admin_notifications_schedule();
                } else {
                    $this->remove_admin_notifications_schedule();
                }

                add_action('admin_notices', array($this, 'admin_success'));

            } else {
                die('Security error');
            }


        }

    }

    /*
     * Shows the success message
     *
     * @return void
     */

     public function admin_success()
     {

        echo '<div class="updated">
                  <p>'. __('Your settings have been saved.', $this->plugin_id ).'</p>
              </div>';

     }

     /*
      * Ajax save approve uoloads for an order
      *
      * @return string Json result
      */

     public function admin_ajax_uploads_approve()
     {

        check_admin_referer('approve-uploads', '_wpf_umf_uploads_nonce');

        $order_id = $_POST['wpf_umf_uploaded_order_id'];

        if (is_numeric($order_id)) {

            $post_meta_data = get_post_meta($order_id, '_wpf_umf_uploads');
            $post_meta = $post_meta_data[0];

            if (is_array($_POST['wpf_umf_uploaded_file'])) {

                // Loop through it
                foreach ($_POST['wpf_umf_uploaded_file'] AS $product_id => $item_numbers) {
                  foreach ($item_numbers AS $item_number => $upload_types) {
                    foreach ($upload_types AS $upload_type => $file_numbers) {
                      foreach ($file_numbers AS $file_number => $data) {
                         if ($data['status'] == 1) {

                            if (!empty($post_meta[$product_id][$item_number][$upload_type][$file_number])) {
                                $post_meta[$product_id][$item_number][$upload_type][$file_number]['status'] = ($_POST['wpf_umf_uploaded_file_approve'] == 'accept')?'approved':'declined';
                            }

                         }
                      }
                    }
                  }
                }

                if (update_post_meta($order_id, '_wpf_umf_uploads', $post_meta)) {
                  $response['success'] = 1;
                } else {
                    // If there were changes but post meta data ain't saved
                    if (!is_array($post_meta)) {
                        $response['success'] = 0;
                    } else {
                        $response['success'] = 1;
                    }
                }

            } else {
              $response['success'] = 1;
            }



            echo json_encode($response);
        }
        die();

     }

     /*
      * Ajax save approve uoloads for an order
      *
      * @return string Json result
      */

     public function admin_ajax_uploads_approve_email()
     {

        check_admin_referer('approve-uploads-email', '_wpf_umf_uploads_email_nonce');

        $response['success'] = 0;

        if (empty($_POST['wpf_umf_order_uploads_email'])) {
            $response['error'] = __('The reason cannot be empty', $this->plugin_id);


        } elseif (!is_numeric($_POST['wpf_umf_uploads_email_order_id'])) {
            $response['error'] = __('Error while sending mail', $this->plugin_id);
        }

        if (empty($response['error'])) {

            $message = $_POST['wpf_umf_order_uploads_email'];

            $order = new WC_Order($_POST['wpf_umf_uploads_email_order_id']);

            $reason = ($_POST['wpf_umf_order_uploads_email_reason'] == 'approved')?__('Files accepted', $this->plugin_id):__('Files rejected', $this->plugin_id);

            $send_email = $this->admin_uploads_approve_email($order, $reason, $message);

            //$response['error'] = __('Error while sending mail', $this->plugin_id);

            if ($send_email)
                $response['success'] = 1;
            else
                $response['error'] = __('Error while sending mail', $this->plugin_id);

        }

        echo json_encode($response);

        die();

     }

     /*
      * Sends the approve reason mail to the customer
      *
      * @param WC_Order $order The order object
      * @param string $reason The reason (file accepted / declined)
      * @param string $message The actual reason text
      *
      * @return boolean Whether the e-mail was sent successfully
      */

     public function admin_uploads_approve_email($order, $reason, $message)
     {

        global $woocommerce;

        $user_email = $order->billing_email;

        if (!empty($user_email)) {

            $mailer = $woocommerce->mailer();

            ob_start();

            // Get mail template
            wc_get_template('emails/uploads-approve.php', array(
                'plugin_id' => $this->plugin_id,
                'order' => $order,
                'reason' => $reason,
                'message' => $message,
                'my_order_url' => $order->get_view_order_url(),
            ));

            // Get contents
            $body = ob_get_clean();

            $subject = $reason.' - Order '.$order->get_order_number();

            $mailer->send( $user_email, $subject, $body);

            return true;

        } else {

            return false;

        }

     }

     /*
      * Enable upload for old products
      */

     public function enable_upload_current_products()
     {

        $args = array(
           'posts_per_page' => -1,
           'post_status'=>'publish',
           'post_type' => 'product',
           'meta_query' => array(
                array(
                    'key' => '_wpf_umf_upload_enable',
                    'compare' => 'NOT EXISTS'
                ),
           ));

        $posts = get_posts($args);

        $default_uploadset = get_option('wpf_umf_default_upload_set');

        $c=0;
        foreach ($posts AS $post) {

            add_post_meta($post->ID, '_wpf_umf_upload_enable', 1);
            add_post_meta($post->ID, '_wpf_umf_upload_set', $default_uploadset);
            add_post_meta($post->ID, '_wpf_umf_uploads_changed', 0);

            $c++;

        }

        if ($c > 0) {
            add_action('admin_notices', array($this, 'current_products_enabled_message'));
        } else {
            add_action('admin_notices', array($this, 'current_products_enabled_message_error'));
        }

     }

     public function current_products_enabled_message()
     {

        echo '<div class="updated">
                  <p>'. __('Uploads for current products are now enabled.', $this->plugin_id ).'</p>
              </div>';

     }

     public function current_products_enabled_message_error()
     {

        echo '<div class="update-nag">
                  '. __('No products without upload settings were found. No products are updated.', $this->plugin_id ).'
              </div>';

     }

    /*
     * Render the upload set section
     *
     * @return void
     */

    public function uploadset_render()
    {

        $current_screen = get_current_screen();

        // When on product page
        if ($current_screen->base == 'post' && $current_screen->id == 'product' && is_numeric(get_the_ID())) {

            $post_id = get_the_ID();

            // Variation support
            $product_variations = $this->get_product_variations($post_id);

            $upload_set_type = 'product';
            $upload_set_product_id = $post_id;
            $upload_sets = get_post_meta($post_id, '_wpf_umf_upload_set');

            if (isset($upload_sets[0]))
                $upload_sets = $upload_sets[0];

        }
        // On settings page
        else {

            $upload_set_type = 'setting';
        }

        // If no upload sets are found, use default upload set
        if (!isset($upload_sets) || !count($upload_sets)) {
            $upload_sets = get_option('wpf_umf_default_upload_set');
        }

        include_once($this->plugin_dir . 'pages/admin/uploadset.php');

    }

    /*
     * Get product variations for single product
     *
     * @param integer $product_id The product id
     * @return array Variation id => name pairs
     * @since 1.1.0
     */

    public function get_product_variations($product_id)
    {

        $product = new WC_Product_Variable($product_id);
        $product_variations = $product->get_available_variations();

        if (is_array($product_variations)) {

            foreach ($product_variations AS $product_variation) {

                if (is_array($product_variation['attributes'])) {

                    $new_name = '';

                    foreach ($product_variation['attributes'] AS $name => $value) {

                        if (!empty($value))
                            $new_name .= ucfirst(str_replace('attribute_', '', $name)).' '.ucfirst($value).', ';

                    }

                    $new_name = substr($new_name, 0, -2);

                    $return[$product_variation['variation_id']] = $new_name;

                }

            }

        }

        if (isset($return))
            return $return;

    }

    /*
     * Saves the upload set for a single product
     *
     * @param integer $post_id The post id
     * @return void
     */

    public function uploadset_post($post_id)
    {

        if (isset($_POST['_'.$this->plugin_slug.'_uploadset_nonce'])) {
            $nonce = $_POST['_'.$this->plugin_slug.'_uploadset_nonce'];
        }
        // Check if our nonce is set.
		if ( ! isset( $nonce) )
			return $post_id;

        // Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->plugin_slug.'_uploadset_'.$post_id ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

        foreach ($_POST AS $key => $value) {

            if (substr(strtolower($key), 0,7) == 'wpf_umf') {
                $meta_data[$key] = $value;
            }

        }

        // Save the post meta data
        $upload_data = new WPF_Uploads_Data($this->plugin_id);
        $upload_data->meta_data = $meta_data;
        $upload_data->save_post_meta_data($post_id);

    }

    /*
    * Renders the uploads on the admin order page
    *
    * @param object $post The order object
    * @return void
    */

    public function order_uploads_render($post)
    {

        $order_uploads = WPF_Uploads_Data::get_uploads_by_order($post->ID);

        $order = new WC_Order($post->ID);

        update_post_meta($order->id, '_wpf_umf_uploads_changed', 0);

        $products = $order->get_items();

        include_once($this->plugin_dir . 'pages/admin/order_uploads.php');

    }

    /*
     * Register the admin notifications cron
     */

    public function create_admin_notifications_schedule()
    {

        $timestamp = wp_next_scheduled( 'wpf_umf_send_admin_notifications' );

        if( $timestamp == false ){
            $recurrence = get_option('wpf_umf_notifications_recurrence');
            $recurrence = (!empty($recurrence))?$recurrence:'daily';
            wp_schedule_event( time(), $recurrence, 'wpf_umf_send_admin_notifications' );
        }

    }

    /*
     * Removes the admin notifications cron
     */

    public function remove_admin_notifications_schedule() {
      wp_clear_scheduled_hook('wpf_umf_send_admin_notifications' );
    }

    /*
     * Sends out the admin notifications
     */

    public function send_admin_notifications()
    {
        global $woocommerce;

        if (get_option('wpf_umf_notifications_enable') == 1) {

            $args = array(
        		'post_type' => 'shop_order',
                'meta_key' => '_wpf_umf_uploads_changed',
        		'orderby' => 'date',
        		'order' => 'DESC',
        		'posts_per_page'=>-1,
        		'meta_query' => array(
        			array(
        				'key' => '_wpf_umf_uploads_changed',
        				'value' => 1,
        			)
        		)
        	);

            $post_orders = get_posts($args);

            foreach ($post_orders AS $order) {

                if (get_option('wpf_umf_order_number_type') == 'order_number') {

                    $the_order = new WC_Order($order->ID);
                    $orders[$order->ID] = $the_order->get_order_number();

                } else {

                    $orders[$order->ID] = '#'.$order->ID;

                }

            }

            if (count($orders)) {

                foreach ($orders AS $order => $order_number) {

                    // Update changed status
                    update_post_meta($order, '_wpf_umf_uploads_changed', 0);

                }

                $subject = __('New / Modified file(s)', $this->plugin_id);

                $mailer = $woocommerce->mailer();

                ob_start();

                // Get mail template
                wc_get_template('emails/admin-notifications.php', array(
                    'plugin_id' => $this->plugin_id,
                    'orders' => $orders,
                    'heading' => $subject,
                ));


                // Get email adress(es)
                $emails = new WC_Emails();
            	$woo_recipient=$emails->emails['WC_Email_New_Order']->recipient;

            	if(!get_option( 'wpf_umf_notifications_email' )) {

            	    if(!empty($woo_recipient)) {
            		    $user_email = esc_attr($woo_recipient);
            		} else {
            		    $user_email = get_option( 'admin_email' );
            		}

            	} else {
            	    $user_email = get_option( 'wpf_umf_notifications_email' );
            	}

                // Get contents
                $body = ob_get_clean();

                if (!empty($user_email)) {
                    $mailer->send($user_email, $subject, $body);
                }

            }

        }

    }

    /*
     * Checks if product upload is enabled
     *
     * @param $product_id integer The product / variation id
     * @param $count boolean Whether to count the actual needed uploads
     *
     * @return integer Actual upload count
     */

    public static function product_needs_upload($product_id, $count = false, $use_variation = false) {

        $check_product_id = $product_id;

        $product_id = self::get_main_product_id($product_id);
        $r_count = 0;

        if (is_numeric($product_id)) {

            if (get_post_meta($product_id, '_wpf_umf_upload_enable', true)) {

                if ($count) {

                    $upload_sets = get_post_meta($product_id, '_wpf_umf_upload_set');
                    $upload_sets = $upload_sets[0];

                    // Variation stuff, @since 1.1.0
                    $check_product = new WC_Product($check_product_id);

                    if ($check_product->post->post_type == 'product_variation') {
                      $is_variation = true;

                    }
                    // End variation stuff

                    foreach ($upload_sets AS $key => $upload_set) {

                        $continue = true;

                        if (isset($is_variation) && $is_variation) {

                            if (is_array($upload_set['variation_show'])) {
                                if (!in_array($check_product_id, $upload_set['variation_show'])) {
                                  $continue = false;
                                }
                            }

                        }

                        if ($continue)
                            $r_count = $r_count + $upload_set['amount'];

                        $continue = true;

                    }

                    return $r_count;

                } else {

                    return 1;

                }

            } else {

                return 0;

            }

        }

    }

    /*
     * Checks if products in an order need uploads
     *
     * @param $order WC_Order
     * @param $count boolean Whether to count the actual needed uploads
     *
     * @return boolean|integer If $count = false, return boolean, if $count = true, return actual upload count
     */

    public static function order_needs_upload($order, $count = false)
    {

        $r_count = 0;

        foreach ($order->get_items() AS $item) {

            if (get_post_meta($item['product_id'], '_wpf_umf_upload_enable', true) == 1) {

                if ($count) {

                    if (get_option('wpf_umf_upload_procedure') == 'multiple') {

                        for ($qty=1; $qty<=$item['qty']; $qty++) {

                            $r_count = $r_count + self::product_needs_upload($item['product_id'], true);

                        }

                    } else {

                        $r_count = $r_count + self::product_needs_upload($item['product_id'], true);

                    }

                } else {

                    $r_count = true;
                    break;

                }
            }

        }

        return $r_count;

    }

    /*
     * Get upload count for single order
     *
     * @param integer $order_id The order id
     * @return integer Amount of current uploaded files for $order_id
     */

    public static function order_get_upload_count($order_id) {

        $order_meta = get_post_meta($order_id, '_wpf_umf_uploads');
        $order_meta = $order_meta[0];

        $count = 0;

        if (is_array($order_meta)) {

            foreach ($order_meta AS $product_id => $item_numbers) {

                foreach ($item_numbers AS $item_number => $upload_types) {

                    foreach ($upload_types AS $upload_type => $file_numbers) {

                          $count = $count + count($file_numbers);

                    }
                }

            }

        }

        return $count;

    }

    public static function get_max_upload_size()
    {

        return ini_get('post_max_size').'B';

    }

    /*
     * Splits product ID and order line item id / cart key
     */

    public static function split_raw_product_id($product_id, $type = null)
    {

        $exp = explode('#', $product_id);

        $array = array(
            'product_id' => $exp[0],
            'unique_product_key' => $exp[1]
        );

        if ($type == null)
            return $array;
        else
            return $array[$type];

    }

    /*
     * Create a unique product key
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
     * Zip files and force download
     *
     * @since 1.1.6
     */


    public static function zip_files_and_download($file_names, $archive_file_name)
    {

        if (class_exists('ZipArchive')) {

            //create the object
            $zip = new ZipArchive();

            //create the file and throw the error if unsuccessful
            if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE )!==TRUE) {
              exit("cannot open <$archive_file_name>\n");
            }

            //add each files of $file_name array to archive
            foreach($file_names as $path => $file)
            {
                $zip->addFile($path,$file);
            }
            $zip->close();

            //then send the headers to foce download the zip file
            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=$archive_file_name");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile("$archive_file_name");
            exit;

        }
    }


}

?>