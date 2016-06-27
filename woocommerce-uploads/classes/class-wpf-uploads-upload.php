<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once('class-wpf-uploads-data.php');

/*
 * This class performs all upload related stuff such as saving images and creating thumbs
 */


class WPF_Uploads_Upload {

    public $plugin_id;
    public $file;
    public $order_id;
    public $product_id;
    public $product_item_number;
    public $unique_product_id;
    public $unique_product_key;
    public $upload_type;
    public $file_number;
    public $file_path;
    public $file_extension;
    public $file_name;
    public $file_main_name;
    public $full_file_path;
    public $set_data;
    public $mode;
    public $thumb;

    public function __construct($plugin_id, $file, $order_id = null, $product_id, $product_item_number, $upload_type, $file_number, $file_path, $set_data = array(), $mode = 'after')
    {

        $prod_ord = WPF_Uploads::split_raw_product_id($product_id);

        $this->plugin_id = $plugin_id;
        $this->file = $file;
        $this->order_id = $order_id;
        $this->product_id = $prod_ord['product_id'];
        $this->unique_product_id = $prod_ord['unique_product_key'];
        $this->unique_product_key = WPF_Uploads::get_unique_product_key($this->product_id, $this->unique_product_id);
        $this->product_item_number = $product_item_number;
        $this->upload_type = $upload_type;
        $this->file_number = $file_number;
        $this->file_path = $file_path;
        $this->set_data = $set_data;
        $this->mode = (empty($mode))?'after':$mode; // Before or after order creation

    }

    /*
     * Uploads a single file locally
     */

    public function upload_local()
    {

        if (empty($this->file) || $this->file['error']) {

            $response['error'] = __('There was an error with this file', $this->plugin_id);

        } else {

            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

            if (!empty($_REQUEST['file_name']))
                $this->file['name'] = $_REQUEST['file_name'];

            $file_in_progress = 0;

            if (!$chunks || $chunk == $chunks - 1) {
                $validate = $this->validate();
            } else {
                $file_in_progress = 1;
            }

            if ($validate['success'] || $file_in_progress) {

                  $this->file_extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));

                  $this->file_main_name = $this->file['name'];

                  $extra = '';

                  // Preserve filename filter
                  if (function_exists('wpf_uploads_preserve_filename')) {

                        if (wpf_uploads_preserve_filename()) {
                            $extra = '_'.pathinfo($this->file['name'], PATHINFO_FILENAME);
                        }

                  }

                  if (!empty($this->order_id)) {

                      if (get_option('wpf_umf_order_number_type') == 'order_number') {
                        $order = new WC_Order($this->order_id);
                        $dir_prefix = sanitize_title($order->get_order_number());
                      } else {
                        $dir_prefix = $this->order_id;
                      }

                      $file_path = $this->file_path . $dir_prefix.'/';
                      $this->file_name = $this->order_id.'_'.str_replace('#', '-', $this->unique_product_key).'_'.$this->product_item_number.'_'.$this->upload_type.'_'.$this->file_number . $extra . '.'.$this->file_extension;
                  } else {
                      $file_path = $this->file_path . 'temp/'.session_id().'/';
                      $this->file_name = 'temp_'.str_replace('#', '-', $this->unique_product_key).'_'.$this->product_item_number.'_'.$this->upload_type.'_'.$this->file_number. $extra . '.'.$this->file_extension;
                  }

                  $this->full_file_path = $file_path . $this->file_name;

                  // Create / check directory
                  if (wp_mkdir_p($file_path)) {

                      // Open temp file
                      $out = @fopen("{$this->full_file_path}.part", $chunk == 0 ? "wb" : "ab");

                      if ($out) {

                          // Read binary input stream and append it to temp file
                          $in = @fopen($this->file['tmp_name'], "rb");

                          if ($in) {

                              while ($buff = fread($in, 4096))
                                  fwrite($out, $buff);

                          } else {

                              $response['error'] = 'Failed to open input stream';

                          }

                          @fclose($in);
                          @fclose($out);

                          @unlink($this->file['tmp_name']);

                        } else {

                          $response['error'] = 'Failed to open output stream';

                        }


                        // Check if file has been uploaded
                        if (!$chunks || $chunk == $chunks - 1) {

                            // Strip the temp .part suffix off
                            rename("{$this->full_file_path}.part",  $this->full_file_path);

                        }

                      }

              } else {

                $response['error'] = $validate['error'];

              }

              if (empty($response['error']) && !$file_in_progress) {

                // Create thumb if necassery
                if (get_option('wpf_umf_thumbnail_enable') == 1) {

                    $this->thumb = $this->create_thumbnail_wp();

                }

                $this->save_upload_data();

                if (!empty($this->order_id))
                    update_post_meta($this->order_id, '_wpf_umf_uploads_changed', 1);

                do_action('wpf_upload_complete', $this->order_id, $this->product_id, $this->full_file_path, $this->mode);

                return array('OK' => 1, 'info' => _('Upload successful'));

              } elseif ($file_in_progress) {

                return array('OK' => 1, 'info' => 'Chunk uploaded');

              } else {

                return array('OK' => 0, 'info' => $response['error']);

              }


        }

    }

    /*
    * Saves the uploaded file data and link it to an order
    */

    public function save_upload_data()
    {

        $upload_type_name = WPF_Uploads::get_upload_set_data($this->product_id, $this->upload_type);
        $upload_type_name = $upload_type_name['title'];


        if (is_numeric($this->order_id)) {

            if ($this->mode == 'after') {

                // Save to database
                $upload_data = new WPF_Uploads_Data($this->plugin_id);
                $upload_data->meta_data[$this->unique_product_key][$this->product_item_number][$this->upload_type][$this->file_number] = array(
                    'name' => $this->file_main_name,
                    'extension' => strtolower($this->file_extension),
                    'path' =>  $this->full_file_path,
                    'thumb' => $this->thumb,
                    'status' => 'on-hold',
                    'type' => $upload_type_name,
                );
                $upload_data->save_order_meta_data($this->order_id);

            }

        }

        if ($this->mode == 'before') {

            $upload_data[$this->unique_product_key][$this->product_item_number][$this->upload_type][$this->file_number] = array(
                    'name' => $this->file_main_name,
                    'extension' => strtolower($this->file_extension),
                    'path' =>  $this->full_file_path,
                    'thumb' => $this->thumb,
                    'status' => 'on-hold',
                    'type' => $upload_type_name,
            );

            do_action('wpf_umf_before_upload_save', $upload_data);

        }



    }

    /*
     * Validates data, specified by the upload set data (set by $this->set_data)
     */

    private function validate()
    {

        $response['success'] = 0;

        $set_data = $this->set_data;

        $file = $this->file;

        // File extension check

        $current_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($set_data['filetypes'] != '*') {

            $extensions = explode(',', $set_data['filetypes']);

            foreach($extensions AS $extension) {

                $extension = strtolower(trim($extension));

                if ($set_data['blocktype'] == 'disallow') {

                    if ($extension == $current_extension)
                        $response['error'] = sprintf(__('The %s filetype is not allowed.', $this->plugin_id), '.'.$current_extension);

                } else {

                    if ($extension == $current_extension) {
                      unset($response['error']);
                      break;
                    } else {
                      $response['error'] = sprintf(__('The %s filetype is not allowed.', $this->plugin_id), '.'.$current_extension);
                    }

                }

            }

        }

        // File size check
        $size_check = floatval($set_data['maxuploadsize']) * 1048576;
        if ($file['size'] > $size_check)
            $response['error'] = sprintf( __('The file %s is too big.', $this->plugin_id), $file['name'] );

        // Resolution check if uploaded file is an image

        if ($this->is_image($file['tmp_name'])) {

            list($width, $height) = getimagesize($file['tmp_name']);

            if (!empty($set_data['min_resolution_width']) && $width < $set_data['min_resolution_width'])
                $response['error'] = sprintf(__('The width of this image must be at least %s.', $this->plugin_id), $set_data['min_resolution_width'].'px');
            if (!empty($set_data['min_resolution_height']) && $height < $set_data['min_resolution_height'])
                $response['error'] = sprintf(__('The height of this image must be at least %s.', $this->plugin_id), $set_data['min_resolution_height'].'px');
            if (!empty($set_data['max_resolution_width']) && $width > $set_data['max_resolution_width'])
                $response['error'] = sprintf(__('The width of this image could not exceeds %s.', $this->plugin_id), $set_data['max_resolution_width'].'px');
            if (!empty($set_data['max_resolution_height']) && $height > $set_data['max_resolution_height'])
                $response['error'] = sprintf(__('The height of this image could not exceeds %s.', $this->plugin_id), $set_data['max_resolution_height'].'px');

        }

        // Max uploads check

        $max_amount = $set_data['amount'];

        if (is_numeric($this->order_id)) {

            $order_meta = get_post_meta($this->order_id, '_wpf_umf_uploads');
            $order_meta = $order_meta[0];

            $current_count = count($order_meta[$this->unique_product_key][$this->product_item_number][$this->upload_type]);

        } else {

            if (isset($_SESSION['wpf_umf_temp_data'][$this->unique_product_key][$this->product_item_number][$this->upload_type]))
                $current_count = count($_SESSION['wpf_umf_temp_data'][$this->unique_product_key][$this->product_item_number][$this->upload_type]);
            else
                $current_count = 0;

        }

        if ($current_count >= $max_amount) {
          $response['error'] = __('You have reached the maximum amount of uploads');
        }



        if (empty($response['error']))
            $response['success'] = 1;

        return $response;

    }

    /*
     * Checks if a file is an image
     *
     * @param string $file
     * @return boolean Whether the file is an image
     */

    private function is_image($file)
    {

        if (function_exists('finfo_open')) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $file);
        } else {
          $size = getimagesize($file);
          $mime = $size['mime'];
        }

        if (substr(strtolower($mime), 0, 5) == 'image')
            return true;
        else
            return false;


    }

    /*
     * Creates a thumbnail in the WordPress way
     *
     * @param $file string The image file
     * @param $width integer The width of the thumb
     * @param $height integer The height of the thumb
     *
     * @return string The full path of the created thumb
     */

    private function create_thumbnail_wp() {

        if ($thumb_file = $this->create_thumb_dir_from_path($this->full_file_path)) {

            $image = wp_get_image_editor($this->full_file_path);

            if ( ! is_wp_error( $image ) ) {

                $thumb_file = $thumb_file.'.png';

                $option_width = get_option('wpf_umf_thumbnail_size_width');
                $option_height = get_option('wpf_umf_thumbnail_size_height');

                $width = (!empty($option_width) && is_numeric($option_width))?$option_width:100;
                $height = (!empty($option_height) && is_numeric($option_height))?$option_height:100;

                $quality = get_option('wpf_umf_thumbnail_wp_quality');
                $quality = (!empty($quality))?$quality:76;

                $image->resize( $width, $height, get_option('wpf_umf_thumbnail_wp_crop') );
                $image->set_quality($quality);
                if ($image->save($thumb_file, 'image/png'))
                    return $thumb_file;

            } else {

                return false;

            }

        }

    }

    /*
     * Creates a thumbs directory
     *
     * @param $file string The full path where the main file is located
     * @return boolean|string The newly created path + filename or false if an error occurs
     */

    private function create_thumb_dir_from_path($file) {

        $pathinfo = pathinfo($file);
        $newpath = $pathinfo['dirname'].'/thumbs';

        if (wp_mkdir_p($newpath))
            return $newpath.'/'.$pathinfo['filename'];
        else
            return false;

    }

}