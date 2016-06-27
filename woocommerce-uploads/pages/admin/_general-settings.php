<?php $pages = get_pages(); ?>

<div class="row">

    <label for="wpf_umf_enable_default" class="main-label"><?php _e('Default upload:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_enable_default" value="0" />
        <input type="checkbox" name="wpf_umf_enable_default" id="wpf_umf_enable_default" value="1" <?php checked( get_option('wpf_umf_enable_default')); ?> />
        <label for="wpf_umf_enable_default"><?php _e('Default enable upload', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Default enable file upload for new products.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_enable_default_old" class="main-label"><?php _e('Enable upload all products:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

         <a href="<?php echo admin_url('admin.php?page='.$_GET['page']); ?>&enable_current_products=1" class="button button-green" id="wpf-umf-enable-current-products"><?php _e('Enable upload for all current products', $this->plugin_id); ?></a>

         <div class="description"><?php _e('Enable file upload for all current products in your webshop. The default upload set will be used for this.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>


<div class="row">

    <label for="wpf_umf_enable_styling" class="main-label"><?php _e('Styling:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_enable_styling" value="0" />
        <input type="checkbox" name="wpf_umf_enable_styling" id="wpf_umf_enable_styling" value="1" <?php checked( get_option('wpf_umf_enable_styling')); ?> />
        <label for="wpf_umf_enable_styling"><?php printf(__('Enable %s CSS', $this->plugin_id), $this->plugin_name); ?></label>

        <div class="description"><?php _e('We\'ve made some default styling for the frontend. Do you want to use it?', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_order_detail_page" class="main-label"><?php _e('Order tracking page:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <select name="wpf_umf_order_detail_page" id="wpf_umf_order_detail_page">

            <option value="0"><?php _e('- Select order tracking page -', $this->plugin_id); ?></option>
            <?php
            if (is_array($pages)):
                foreach ($pages AS $page):
            ?>

            <option value="<?php echo $page->ID; ?>" <?php selected(get_option('wpf_umf_order_detail_page'), $page->ID); ?>><?php echo $page->post_title; ?></option>

            <?php
                endforeach;
            endif;
            ?>

        </select>

        <div class="description"><?php _e('Select the order tracking page.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_position" class="main-label"><?php _e('Position:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <select name="wpf_umf_position" id="wpf_umf_position">

            <option value="before" <?php selected( get_option('wpf_umf_position'), 'before' ); ?>><?php _e('Before order item table', $this->plugin_id); ?></option>
            <option value="after" <?php selected( get_option('wpf_umf_position'), 'after' ); ?>><?php _e('After order item table', $this->plugin_id); ?></option>

        </select>

        <div class="description"><?php _e('Where do you want to show the upload fields on the order detail page?', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_thank_you_page" class="main-label"><?php _e('Thank you page:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_thank_you_page" value="0" />
        <input type="checkbox" name="wpf_umf_thank_you_page" id="wpf_umf_thank_you_page" value="1" <?php checked( get_option('wpf_umf_thank_you_page')); ?> />
        <label for="wpf_umf_thank_you_page"><?php _e('Show on thank you page', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Show upload fields on thank you page, directly after payment.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_upload_path" class="main-label"><?php _e('Upload path:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input name="wpf_umf_upload_path" id="wpf_umf_upload_path" class="regular-text" type="text" value="<?php echo stripslashes(get_option('wpf_umf_upload_path')); ?>" required />

        <div class="description"><?php _e('Upload path for new uploads. Subfolders with the order-ID will be created within this directory.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_upload_procedure_single" class="main-label"><?php _e('Upload procedure:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <div>
            <input name="wpf_umf_upload_procedure" id="wpf_umf_upload_procedure_single" value="single" type="radio" <?php checked( get_option('wpf_umf_upload_procedure'), 'single'); ?> /> <label for="wpf_umf_upload_procedure_single"><?php _e('One upload for each product', $this->plugin_id); ?></label>
            <div class="description"><?php _e('If a customer orders multiple items of the same product, only one upload box is shown.', $this->plugin_id); ?></div>
        </div>

        <div class="wpf-mar-top-10">
            <input name="wpf_umf_upload_procedure" id="wpf_umf_upload_procedure_multiple" value="multiple" type="radio" <?php checked( get_option('wpf_umf_upload_procedure'), 'multiple'); ?> /> <label for="wpf_umf_upload_procedure_multiple"><?php _e('Multiple uploads for each product', $this->plugin_id); ?></label>
            <div class="description"><?php _e('If a customer orders multiple items of the same product, for each item an upload box is shown.', $this->plugin_id); ?></div>
        </div>


    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_order_number_type_id" class="main-label"><?php _e('Order numbering:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input name="wpf_umf_order_number_type" id="wpf_umf_order_number_type_id" type="radio" value="order_id" <?php checked( get_option('wpf_umf_order_number_type'), 'order_id'); ?>/>
        <label for="wpf_umf_order_number_type_id"><?php _e('Order ID', $this->plugin_id); ?></label>

        <input name="wpf_umf_order_number_type" id="wpf_umf_order_number_type_number" type="radio" value="order_number" <?php checked( get_option('wpf_umf_order_number_type'), 'order_number'); ?>/>
        <label for="wpf_umf_order_number_type_number"><?php _e('Order number', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Default files are stored in a directory named as the order id. Some plugins let you choose your own order numbers, choose \'Order number\' in this case.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_customer_delete" class="main-label"><?php _e('Customer delete:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_customer_delete" value="0" />
        <input type="checkbox" name="wpf_umf_customer_delete" id="wpf_umf_customer_delete" value="1" <?php checked( get_option('wpf_umf_customer_delete'),true, true ); ?> />
        <label for="wpf_umf_customer_delete"><?php _e('Let customers delete uploaded files', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Let customers delete files until they are reviewed by admin.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_customer_download" class="main-label"><?php _e('Customer download:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_customer_download" value="0" />
        <input type="checkbox" name="wpf_umf_customer_download" id="wpf_umf_customer_download" value="1" <?php checked( get_option('wpf_umf_customer_download'),true, true ); ?> />
        <label for="wpf_umf_customer_download"><?php _e('Let customers download uploaded files', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Let customers download their own files.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_statuses_1" class="main-label"><?php _e('Required status(es):', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <?php
        // WC 2.2 support
        if (function_exists('wc_get_order_statuses')):

            $statuses = wc_get_order_statuses();
            ksort($statuses);

            $i = 1;
    		foreach( $statuses as $status => $status_name ):
                $status = str_replace('wc-', '', $status);  ?>
                <input type="checkbox" name="wpf_umf_statuses[]" id="wpf_umf_statuses_<?php echo $i; ?>" value="<?php echo $status; ?>" <?php echo (in_array($status, get_option('wpf_umf_statuses')))?'checked':''; ?>/> <label for="wpf_umf_statuses_<?php echo $i; ?>"><?php _e($status_name, 'woocommerce'); ?></label> <br />
            <?php
            $i++;
            endforeach;

        else:

            $statuses = get_terms( 'shop_order_status', array( 'hide_empty' => false ) );

                $i = 1;
    		    foreach( $statuses as $status ): ?>
                    <input type="checkbox" name="wpf_umf_statuses[]" id="wpf_umf_statuses_<?php echo $i; ?>" value="<?php echo $status->slug; ?>" <?php echo (in_array($status->slug, get_option('wpf_umf_statuses')))?'checked':''; ?>/> <label for="wpf_umf_statuses_<?php echo $i; ?>"><?php _e($status->name, 'woocommerce'); ?></label> <br />
                <?php
                $i++;
                endforeach;

        endif;
        ?>

        <div class="description"><?php _e('Specify which order statuses will allow customers to upload files.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_dynamic_variation" class="main-label"><?php _e('Dynamic variation support', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_dynamic_variation" value="0" />
        <input type="checkbox" name="wpf_umf_dynamic_variation" id="wpf_umf_dynamic_variation" value="1" <?php checked( get_option('wpf_umf_dynamic_variation'),true, true ); ?> />
        <label for="wpf_umf_dynamic_variation"><?php _e('Enable', $this->plugin_id); ?></label>

        <div class="description"><?php printf(__('Check this option if you are using plugins that support dynamic variations (e.g. WooCommerce (Gravity Forms) Product Add-ons, Woo Table Based Pricing etc.). Default enabled. Got any problems with this setting? Please read more about it in our %s.', $this->plugin_id), '<a href="https://support.wpfortune.com/hc/en-us/articles/201072531-Frequently-Asked-Questions-FAQ-" target="_blank">FAQ</a>'); ?></div>

    </div>

    <div class="clear"></div>

</div>