<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * This class performs all database related stuff such as saving of settings and upload sets
 */


class WPF_Uploads_Data {

    /*
     * @var array $settings Array of settings that need to be saved / updated
     */

    public $settings;

    public $meta_data;


    /*
     * @var string $plugin_id The current plugin id
     */

    public $plugin_id;


    /*
     * @param string $plugin_id The current plugin id, mainly used for translation
     */

    public function __construct($plugin_id)
    {

        $this->plugin_id = $plugin_id;

    }

    /*
     * Save post meta data
     *
     * @param int $post_id The post id
     * @return int $success Amount of succesfully updated / added settings
     */

    public function save_post_meta_data($post_id)
    {

        if (is_numeric($post_id)) {

            $success = 0;

            if (is_array($this->meta_data)) {

                $upload_set = $this->meta_data['wpf_umf_upload'];

                // Filter out some fields that doesn't need to be saved
                unset($this->meta_data['wpf_umf_upload']);
                unset($this->meta_data['wpf_umf_last_upload_id']);

                $this->meta_data['wpf_umf_upload_set'] = $this->process_upload_sets($upload_set);


                foreach ($this->meta_data AS $setting => $value) {

                    update_post_meta($post_id, '_'.$setting, $value);

                }


            }

        }

        return $success;

    }

    /*
     * Save all settings
     *
     * @return int $success Amount of succesfully updated / added settings
     */

    public function save_settings()
    {

        $success = 0;

        if (is_array($this->settings)) {

            $default_upload_set = $this->settings['wpf_umf_upload'];

            // Filter out default upload set
            unset($this->settings['wpf_umf_upload']);

            $this->settings['wpf_umf_default_upload_set'] = $this->process_upload_sets($default_upload_set);

            foreach ($this->settings AS $setting => $value) {

                // Filter out all not settings related values, just to be sure
                if (substr(strtolower($setting), 0, 7) == 'wpf_umf') {

                    // Upload path slash check
                    if ($setting == 'wpf_umf_upload_path') {

                        if (substr($value, -1) != '/')
                            $value = $value.'/';
                                                                      
                        // Fallback if going outside root
                        if (!function_exists('wpf_uploads_path_check_disable')) {

                            if (strpos($value, get_home_path()) === false)
                                $value = get_home_path().'wp-content/uploads/umf/';

                        }

                    }

                    if ($this->save_option($setting, $value))
                        $success++;

                }

            }

        }

        return $success;

    }

    /*
     * Saves a single option
     *
     * @param string $setting The option name
     * @param string|array $value The option value
     * @return boolean Whether the saving was successfull
     */

     private function save_option($setting, $value) {

        if (get_option($setting) === false) {
            if(add_option($setting, $value))
                return true;
        } else {
            if (update_option($setting, $value))
                return true;
        }

     }

     /*
      * Checks the upload sets raw input data
      *
      * @param aray $upload_sets Array of upload sets that needs processing
      * @return array Array of upload sets, ready for database
      */

     private function process_upload_sets($upload_sets)
     {

        $new_upload_sets = array();

        if (is_array($upload_sets)) {

            foreach ($upload_sets AS $id => $data) {

                // Check if title isn't empty
                if (!empty($data['title'])) {
                    $new_upload_sets[$id] = $data;
                }

            }

        }

        // Sort
        ksort($new_upload_sets);

        return $new_upload_sets;

     }


     /*
      * Saves the order meta data
      *
      * @param integer $order_id The order id
      * @return boolean Whether the meta data was saved successfully
      */


     public function save_order_meta_data($order_id)
     {

        if (is_array($this->meta_data)) {

            // Merge the uploads order meta data
            $order_meta_data = get_post_meta($order_id, '_wpf_umf_uploads');

            if (is_array($order_meta_data[0])) {
                $new_data = order_uploads_merge_recursive($order_meta_data[0], $this->meta_data);
            } else {
                $new_data = $this->meta_data;
            }

            return update_post_meta($order_id, '_wpf_umf_uploads', $new_data);

        }

     }

     /*
      * Get all uploads (old and new ones) for a specific order
      *
      * @param integer $order_id The order id
      * @return array Array containing all the upload data
      */

     public static function get_uploads_by_order($order_id)
     {

        // Migrate old uploads to new storing way, if not did before
        if (get_post_meta($order_id, '_wpf_umf_old_uploads_check', true) == 0) {

            $old_uploads = self::get_old_uploads_by_order($order_id);

            if (is_array($old_uploads))
                update_post_meta($order_id, '_wpf_umf_uploads', $old_uploads);

            update_post_meta($order_id, '_wpf_umf_old_uploads_check', 1);
        }

        return self::get_new_uploads_by_order($order_id);


     }

     /*
      * Get uploads by order id
      *
      * @param integer $order_id The order id where we should look up the uploads
      * @return boolean|array False if no uploads were found. An array if there were uploads found.
      */


     private static function get_new_uploads_by_order($order_id)
     {

        $uploads = get_post_meta($order_id, '_wpf_umf_uploads');

        if ($uploads)
            $uploads = $uploads[0];

        if (is_array($uploads))
            return $uploads;
        else
            return false;

        return $uploads;

     }

     /*
      * Checks if there is a previous version of this plugin is installed.
      * If so, try to get old order meta data and convert it to a readable array
      *
      * @param integer $order_id The order id where we should look up the old uploads
      * @return boolean|array False if no old uploads were found. An array if there were old uploads found.
      */


     private static function get_old_uploads_by_order($order_id)
     {


        // If old version of plugin is found
        if (get_option('woocommerce_umf_version') !== false) {

            $meta_data = get_post_meta($order_id);

            // Old data
            foreach ($meta_data AS $meta_key => $meta_value) {

                if (strpos($meta_key, '_woo_umf_uploaded') !== false) {

                    $exp = explode('_', $meta_key);

                    if (strpos($meta_key, '_woo_umf_uploaded_approve') !== false) {
                        $meta_value = unserialize($meta_value[0]);
                        $old_array[$exp[5]]['status'] = $meta_value;
                    } else {
                        $old_array[$exp[6]][$exp[5]] = $meta_value;
                    }

                }

            }



            if (isset($old_array) && is_array($old_array))
                ksort($old_array);

            // Build up new array
            $order = new WC_Order($order_id);

            $i = 1;

            foreach ($order->get_items() AS $item) {

                // For each item number
                for ($qty=1; $qty<=$item['qty']; $qty++) {

                    $uploads_per_product = explode('|',get_post_meta($item['product_id'], '_woo_umf_titles', true));

                    // For each upload per item number
                    for ($c=1; $c<=count($uploads_per_product); $c++) {

                        // Check if path is set, check for a slash
                        if (strpos($old_array[$i]['path'][0], '/') !== false) {

                            foreach ($old_array[$i] AS $key => $value) {
                              $old_data[$key] = $value[0];
                            }
                            $old_data['extension'] = strtolower(pathinfo($old_data['name'], PATHINFO_EXTENSION));
                            $old_data['type'] = $uploads_per_product[$c-1];
                            $array[$item['product_id']][$qty][$c][1] = $old_data;
                        }


                        $i++;

                    }

                }

            }

            return $array;

        } else {
          return false;
        }

     }

    /*
     * Default settings for this plugin.
     *
     * It will check if there are previously stored (older version) settings available. If so, it will use those settings.
     * If not, it will load some default settings.
     *
     * @param boolean $use_old_settings Whether to use settings from previous version, default to true
     * @param array $args Optional override default settings
     *
     * @return array Default settings
     */

     public function default_settings($use_old_settings = true, $args = array())
     {

        if (!function_exists('get_home_path')) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
        }

        $defaults = array(
            'wpf_umf_installed' => 1,
            'wpf_umf_uploader' => 'ajax',
            'wpf_umf_uploader_dropzone' => 0,
            'wpf_umf_uploader_chunksize' => 4,
            'wpf_umf_enable_default' => 1,
            'wpf_umf_enable_styling' => 1,
            'wpf_umf_order_detail_page' => false,
            'wpf_umf_position' => 'before',
            'wpf_umf_thank_you_page' => 0,
            'wpf_umf_upload_path' => get_home_path().'wp-content/uploads/umf/',
            'wpf_umf_upload_procedure' => 'single',
            'wpf_umf_customer_delete' => 1,
            'wpf_umf_email_link' => 0,
            'wpf_umf_email_text' => array(
                'singular' => __('Login to upload your file and attach it to your order.', $this->plugin_id),
                'plural' => __('Login to upload your files and attach them to your order.', $this->plugin_id),
            ),
            'wpf_umf_statuses' => array('processing', 'completed'),
            'wpf_umf_order_number_type' => 'order_id',
            'wpf_umf_thumbnail_enable' => 1,
            'wpf_umf_thumbnail_size_width' => '60',
            'wpf_umf_thumbnail_size_height' => '60',
            //'wpf_umf_thumbnail_resize_method' => 'wp',
            'wpf_umf_thumbnail_wp_crop' => 1,
            'wpf_umf_thumbnail_wp_quality' => 76,
            //'wpf_umf_thumbnail_im_adobe_size' => 5,
            'wpf_umf_message_enable' => 1,
            'wpf_umf_message_not_checked' => __('Your file will be manually verified.', $this->plugin_id),
            'wpf_umf_message_declined_files' => __('We have found a problem with this file. Please upload a new file.', $this->plugin_id),
            'wpf_umf_message_accepted_files' => __('Your file is approved.', $this->plugin_id),
            'wpf_umf_message_upload_description' => null,
            'wpf_umf_notifications_enable' => 1,
            'wpf_umf_notifications_recurrence' => 'hourly',
            'wpf_umf_dynamic_variation' => 1,
            'wpf_umf_notifications_email' => get_option('admin_email'),
            'wpf_umf_upload' => array(
                1 => array(
                    'title' => 'Upload box 1',
                    'description' => '',
                    'amount' => 1,
                    'blocktype' => 'allow',
                    'filetypes' => 'jpg, png',
                    'maxuploadsize' => 5,
                    'min_resolution_width' => '',
                    'min_resolution_height' => '',
                    'max_resolution_width' => '',
                    'max_resolution_height' => '',
                ),
            )

        );

        if ($use_old_settings) {

            $old_settings = $this->convert_old_settings();

            if (is_array($old_settings)) {

                $defaults = $old_settings;

            }

        }   

        return wp_parse_args($defaults, $args);

     }



     /*
      * Find and convert settings from a previous version of this plugin
      *
      * @return array onverted settings that can be directly used in the database
      */

     private function convert_old_settings()
     {

        $wpf_umf_uploader_old = get_option('woo_umf_uploader_type');

            // If at least one old setting is found, try to load all old settings
            if ($wpf_umf_uploader_old !== false) {

                $array['wpf_umf_installed'] = 1;
                $wpf_umf_uploader = get_option('woo_umf_uploader_type');
                $array['wpf_umf_uploader'] = ($wpf_umf_uploader !== false) ? (($wpf_umf_uploader == 1) ? 'ajax' : 'html') : false;

                $array['wpf_umf_uploader_dropzone'] = get_option('woo_umf_dropzone');
                $array['wpf_umf_uploader_chunksize'] = get_option('woocommerce_umf_chunksize');
                $array['wpf_umf_enable_default'] = get_option('woocommerce_umf_default_enable');

                $wpf_umf_enable_styling = get_option('woocommerce_umf_use_style');
                $array['wpf_umf_enable_styling'] = ($wpf_umf_enable_styling !== false) ? (($wpf_umf_enable_styling == 'on') ? 1 : 0) : false;
                $array['wpf_umf_order_detail_page'] = get_option('wc_umf_order_tracking');
                $array['wpf_umf_position'] = get_option('woo_umf_orderdetail_pos');
                $array['wpf_umf_thank_you_page'] = false;
                $array['wpf_umf_upload_path'] = get_option('woocommerce_umf_upload_path');
                $array['wpf_umf_order_number_type'] = 'order_id';
                $array['wpf_umf_dynamic_variation'] = 1;
                $wpf_umf_upload_procedure = get_option('woocommerce_umf_upload_procedure');
                $array['wpf_umf_upload_procedure'] = ($wpf_umf_upload_procedure !== false) ? (($wpf_umf_upload_procedure == 1) ? 'multiple' : 'single') : false;

                $array['wpf_umf_customer_delete'] = get_option('woo_umf_cusdelete');
                $array['wpf_umf_statuses'] = get_option('woocommerce_umf_status');

                $woocommerce_umf_imagemethod = get_option('woocommerce_umf_imagemethod');
                $array['wpf_umf_thumbnail_enable'] = ($woocommerce_umf_imagemethod !== false) ? ((!empty($woocommerce_umf_imagemethod)) ? 1 : 0) : false;

                $array['wpf_umf_thumbnail_size_width'] = get_option('woocommerce_umf_thumb_width');
                $array['wpf_umf_thumbnail_size_height'] = get_option('woocommerce_umf_thumb_height');
                //$array['wpf_umf_thumbnail_resize_method'] = get_option('woocommerce_umf_imagemethod');
                $array['wpf_umf_thumbnail_wp_crop'] = get_option('woo_umf_thumb_cropdev');
                $array['wpf_umf_thumbnail_wp_quality'] = get_option('woo_umf_thumb_quality');
                //$array['wpf_umf_thumbnail_im_adobe_size'] = get_option('woo_umf_max_thumb');

                $wpf_umf_message_enable = get_option('woocommerce_umf_approve');
                $array['wpf_umf_message_enable'] = ($wpf_umf_message_enable !== false) ? (($wpf_umf_message_enable == 'on') ? 1 : 0) : false;
                $array['wpf_umf_message_not_checked'] = get_option('woocommerce_umf_approve_non');
                $array['wpf_umf_message_declined_files'] = get_option('woocommerce_umf_approve_nok');
                $array['wpf_umf_message_accepted_files'] = get_option('woocommerce_umf_approve_ok');
                $array['wpf_umf_message_upload_description'] = get_option('woo_umf_descr');
                $array['wpf_umf_notifications_enable'] = get_option('woo_umf_notifications');
                $array['wpf_umf_notifications_recurrence'] = get_option('woo_umf_not_rec');
                $array['wpf_umf_notifications_email'] = get_option('woo_umf_not_email');

                $array['wpf_umf_email_link'] = get_option('wc_umf_email_link');
                $array['wpf_umf_email_text'] = get_option('wc_umf_email_text');

                // Create new default upload set from old settings

                $default_upload_title = get_option('woocommerce_umf_default_title');

                if (!empty($default_upload_title)) {

                    $upload_boxes = explode('|', $default_upload_title);

                    foreach ($upload_boxes AS $key => $upload_title) {

                        $upload_set[$key+1] = array(
                            'title' => $upload_title,
                            'description' => '',
                            'amount' => 1,
                            'blocktype' => (get_option('woocommerce_umf_whitelist') == 'blacklist')?'disallow':'allow',
                            'filetypes' => get_option('woocommerce_umf_allowed_file_types'),
                            'maxuploadsize' => get_option('woocommerce_umf_max_uploadsize'),
                            'min_resolution_width' => '',
                            'min_resolution_height' => '',
                            'max_resolution_width' => '',
                            'max_resolution_height' => '',
                        );

                    }

                }

                // If no upload set could be found for some reason, create a default one
                if (empty($upload_set)) {

                      $upload_set[1] =  array(
                        'title' => 'Upload box 1',
                        'description' => '',
                        'amount' => 1,
                        'blocktype' => 'allow',
                        'filetypes' => 'jpg, png',
                        'maxuploadsize' => 5,
                        'min_resolution_width' => '',
                        'min_resolution_height' => '',
                        'max_resolution_width' => '',
                        'max_resolution_height' => '',
                    );
                }

                $array['wpf_umf_upload'] = $upload_set;

            }

            return $array;

     }

     /*
      * For each product check if there is an old upload set available.
      * If so, update it to the new one
      */

      public static function update_old_upload_sets()
      {

        global $wpdb;

        $old_uploads = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_woo_umf_titles'");

        foreach ($old_uploads AS $old_upload) {

            $upload_sets = explode('|', $old_upload->meta_value);

            if (!empty($old_upload->meta_value) && is_array($upload_sets)) {

                foreach($upload_sets AS $key => $title) {

                    $filetypes = get_post_meta($old_upload->post_id, '_woo_umf_filetypes', true);

                    $upload_set[$key+1] = array(
                        'title' => $title,
                        'description' => '',
                        'amount' => 1,
                        'blocktype' => (get_option('woocommerce_umf_whitelist') == 'blacklist')?'disallow':'allow',
                        'filetypes' => (!empty($filetypes))?$filetypes:get_option('woocommerce_umf_allowed_file_types'),
                        'maxuploadsize' => get_option('woocommerce_umf_max_uploadsize'),
                        'min_resolution_width' => '',
                        'min_resolution_height' => '',
                        'max_resolution_width' => '',
                        'max_resolution_height' => '',
                    );

                    unset($filetypes);

                }

                update_post_meta($old_upload->post_id, '_wpf_umf_upload_enable', ((get_post_meta($old_upload->post_id, '_woo_umf_enable', true) == 'yes')?1:0));

                if (is_array($upload_set)) {
                    update_post_meta($old_upload->post_id, '_wpf_umf_upload_set', $upload_set);
                }

            }

            unset($upload_sets);

        }

      }



}

?>