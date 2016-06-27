<?php
/**
 * Plugin Name: SSP Custom Plugin for CreationStationPrinting.com
 * Plugin URI: http://stephensherrardplugins.com
 * Description: Custom functions for the Creation Station Priting web site
 * Version: 0.0.2
 * Author: Stephen Sherrard
 * Author URI: http://stephensherrardplugins.com
 * Requires at least: 3.8
 * Tested up to: 4.3.1
 *
 * Text Domain: ssp-custom-plugin
 * Domain Path: /languages/
 *
 */

/*
 * ADMIN FUNCTIONS
 */

if(is_admin()) {

    function ssp_format_file_size($bytes) {
        $result = "";

        if ($bytes < 1024) {
            $result = $bytes . " Bytes";
        } elseif (($bytes >= 1024) && ($bytes < (1024 * 1024))) {
            $kilobytes = round($bytes / 1024);
            $result = $kilobytes . " KB";
        } elseif (($bytes >= 1024 * 1024) && ($bytes < (1024 * 1024 * 1024))) {
            $megabytes = round($bytes / (1024 * 1024), 2);
            $result = $megabytes . " MB";
        }

        return($result);
    }
    
    function ssp_empty_file($orderId, $itemId, $side) {
        $result = array(
            'id'=>'',
            'name'=>'',
            'bc_name'=>'',
            'order_id'=> $orderId,
            'item_id' => $itemId,
            'status' =>'missing',
            'size' => '0',
            'admin' =>'',
            'side' => $side,
            'comment' => '',
        );

        return($result);
    }

    function get_proof_pdo() {
        $db['host'] = PROOF_DB_HOST;
        $db['name'] = PROOF_DB_NAME;
        $db['user'] = PROOF_DB_USER;
        $db['pass'] = PROOF_DB_PASS;

        $pdo = null;

        $mysql = "mysql:host={$db['host']};dbname={$db['name']};";
        $opt = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        try {
            $pdo = new PDO($mysql, $db['user'], $db['pass'], $opt);
        } catch (PDOException $e) {
            die('Db connection failed: ' . $e->getMessage());
        }

        return $pdo;
    }

    function ssp_render_shop_order_columns($column) {
        global $post, $woocommerce, $the_order;

        $order = $the_order;

        if ( empty( $order ) || $order->id != $post->ID ) {
            $order = wc_get_order( $post->ID );
        }

        if('order_title' === $column) {
            ?>
            <a class="ssp_trigger" data-id="<?php echo absint( $post->ID );?>">
            <img src='http://creationstationprinting.com//wp-content/plugins/woocommerce/includes/admin/plus-sign.png' width='25' height='25' border="0">
            </a>
            <?php
        } else if('order_actions' === $column) {
            ?>
            </td>
            </tr>
            <tr>
                <td colspan="9" style="margin: 0px;padding: 0px;">
                <?php 

                    $orderAltEmail = "";
                    $pdo = get_proof_pdo();
                    $proofOrderId = $post->ID + 100000;
                    $files = $pdo->query("SELECT files.id, files.side, files.name, files.size, files.status, files.item_id, files.order_id, file_comments.comment FROM files LEFT JOIN file_comments ON file_comments.file_id = files.id WHERE order_id = {$proofOrderId} AND status NOT IN ('archived', 'deleted', 'uploaded', 'upload_backup')")->fetchAll();

                    $filesGroupedByItem = array();

                    $orderInfo = $pdo->query("SELECT `alt_email` FROM orders WHERE id = {$proofOrderId}")->fetch();
                    $orderHistory = $pdo->query("SELECT * FROM order_history WHERE order_id = {$proofOrderId} ORDER BY id DESC")->fetchAll();

                    // var_dump($orderHistory);

                    if ($orderInfo['alt_email']) {
                        $orderAltEmail= $orderInfo['alt_email'];
                    }

                    foreach ($files as $file) {
                        $index = 0;
                        if ($file['side'] == 'back') {
                            $index = 1;
                        }
                        $filesGroupedByItem[$file['item_id']][$index] = $file;
                    }
                    // echo "<pre>";

                    // var_dump($filesGroupedByItem);
                    // echo "</pre>";
                    // die;

                    foreach ($filesGroupedByItem as $itemId => $file) {
                        if(!isset($file[0])) {
                            $filesGroupedByItem[$itemId][0] = ssp_empty_file($proofOrderId, $itemId, 'front');
                        }
                        if(!isset($file[1])) {
                            $filesGroupedByItem[$itemId][1] = ssp_empty_file($proofOrderId, $itemId, 'back');
                        }

                        ksort($filesGroupedByItem[$itemId]);
                    }

                ?>
                <div id='ssp_order_info_<?php echo absint( $post->ID );?>' style="border-bottom: 8px solid rgb(236, 236, 236); display:none;">
                <!-- <div id='ssp_order_info_<?php echo absint( $post->ID );?>' style="border-bottom: 8px solid rgb(236, 236, 236);"> -->

                <div class="woocommerce_order_items_wrapper wc-order-items-editable">
                <table cellpadding="0" cellspacing="0" class="woocommerce_order_items" style="border-top: 1px solid rgb(234, 234, 234);">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="item"><?php _e( 'Item', 'woocommerce' ); ?></th>

                            <?php do_action( 'woocommerce_admin_order_item_headers' ); ?>

                            <th class="quantity"><?php _e( 'Qty', 'woocommerce' ); ?></th>

                            <th class="line_cost"><?php _e( 'Total', 'woocommerce' ); ?></th>

                            <th class="wc-order-edit-line-item" width="1%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody id="order_line_items">
                        <?php

                        $line_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item'));

                        foreach ($line_items as $item_id => $item) {
                            $_product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
                            $item_meta = $order->get_item_meta($item_id);

                            include(__DIR__ . '/html-order-item.php');
                            do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item);
                        }

                        ?>

                        <tr>
                        <td colspan="6" style="background-color: #f8f8f8;">
                        <?php include(__DIR__ . '/proofing-block.php'); ?>
                        </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <!-- End of items hidden wrapper -->
                </div> 
                <?php
        }
    }

    function ssp_delete_item($item_id) {
        $a = wp_remote_post(PROOF_DOMAIN_NAME . '/api/delete-item/' . $item_id);
    }    

    function ssp_add_item($item_id, $item) {
        $a = wp_remote_post(PROOF_DOMAIN_NAME . '/api/pull-single-item/'. $item_id);
    }

    function ssp_update_items($orderId) {
        $a = wp_remote_post(PROOF_DOMAIN_NAME . '/api/update-items/' . $orderId);
    }

    add_action( 'manage_shop_order_posts_custom_column', 'ssp_render_shop_order_columns', 5);
    add_action( 'woocommerce_ajax_add_order_item_meta', 'ssp_add_item', 10, 2);
    add_action( 'woocommerce_saved_order_items', 'ssp_update_items', 10, 2);
    add_action( 'woocommerce_delete_order_item', 'ssp_delete_item', 10, 1);
    add_action( 'woocommerce_update_order_item', 'ssp_update_item', 10, 1);

    function ssp_enqueue_admin_scripts($hook) {
        global $post;

        if ( $hook == 'edit.php'  ) {
            if ( 'shop_order' === $post->post_type ) {
                wp_enqueue_script('ssp-upload-1', trailingslashit(plugin_dir_url( __FILE__)).'/assets/js/jquery.iframe-transport.js' );
                wp_enqueue_script('ssp-upload-2', trailingslashit(plugin_dir_url( __FILE__)).'/assets/js/jquery.fileupload.js' );
                wp_enqueue_script('ssp-admin-script', trailingslashit(plugin_dir_url( __FILE__)).'/assets/js/ssp-admin-script.js' );
                wp_enqueue_style('ssp-admin-styles', trailingslashit(plugin_dir_url( __FILE__)).'/assets/css/ssp-admin-styles.css' );

                $jsData = array(
                    'thisDomain' => PROOF_DOMAIN_NAME,
                );

                wp_localize_script( 'ssp-admin-script', 'phpVars', $jsData);
            }
        }
    }
    add_action('admin_enqueue_scripts', 'ssp_enqueue_admin_scripts');

} // End Admin Functions

function ssp_create_order($order_id) {
    wp_remote_post(PROOF_DOMAIN_NAME . '/api/create-order/' . $order_id);
}

function ssp_add_file($order_id, $product_id, $full_file_path, $mode) {
    wp_remote_post(PROOF_DOMAIN_NAME . '/api/update-files/' . $order_id);
}

add_action( 'woocommerce_checkout_order_processed', 'ssp_create_order', 10, 1);
add_action( 'wpf_upload_complete', 'ssp_add_file', 10, 4);

if(!function_exists('wc_tax_enabled')) {
    function wc_tax_enabled() {
        return get_option( 'woocommerce_calc_taxes' ) === 'yes';
    }
}
