<div class="postbox">

    <h3><?php _e('Uploads before checkout', $this->plugin_id); ?></h3>

    <div class="inside">

      <div class="row">

        <label for="wpf_umf_before_custom_cart_message" class="main-label"><?php _e('Cart message:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <?php $custom_cart_message = get_option('wpf_umf_before_custom_cart_message'); ?>

            <input name="wpf_umf_before_custom_cart_message" id="wpf_umf_before_custom_cart_message" class="regular-text" type="text" value="<?php echo (!empty($custom_cart_message))?esc_attr(stripslashes($custom_cart_message)):__('You can always add, change or remove your uploads after checkout.', $this->plugin_id); ?>" />

            <div class="description"><?php _e('Please enter the text you want to display on the cart page. If empty, the default message will be shown.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

      </div>

        <div class="row">

            <label for="wpf_umf_before_uploads_required" class="main-label"><?php _e('Uploads required:', $this->plugin_id); ?></label>

            <div class="wpf-umf-fields">

                <input type="hidden" name="wpf_umf_before_uploads_required" value="0" />
                <input type="checkbox" name="wpf_umf_before_uploads_required" id="wpf_umf_before_uploads_required" value="1" <?php checked( get_option('wpf_umf_before_uploads_required')); ?> />
                <label for="wpf_umf_before_uploads_required"><?php _e('Enable', $this->plugin_id); ?></label>

                <div class="description"><?php _e('Uploads are required before the customer can proceed to checkout', $this->plugin_id); ?></div>

            </div>

            <div class="clear"></div>

        </div>

        <div id="wpf-umf-upload-before-box"<?php echo (get_option('wpf_umf_before_uploads_required') == 0)?' class="hidden"':''; ?>>

            <div class="row wpf-umf-border-top">

                <label for="wpf_umf_before_use_amount" class="main-label"><?php _e('Strict mode:', $this->plugin_id); ?></label>

                <div class="wpf-umf-fields">

                    <input type="hidden" name="wpf_umf_before_use_amount" value="0" />
                    <input type="checkbox" name="wpf_umf_before_use_amount" id="wpf_umf_before_use_amount" value="1" <?php checked( get_option('wpf_umf_before_use_amount')); ?> />
                    <label for="wpf_umf_before_use_amount"><?php _e('Enable', $this->plugin_id); ?></label>

                    <div class="description"><?php _e('If you enable strict mode, customers must exactly upload the amount of files corresponding the \'Number of uploads\' for a specific upload box.', $this->plugin_id); ?></div>

                </div>

                <div class="clear"></div>

            </div>

            <div class="row">

                <label for="wpf_umf_before_use_upload_procedure" class="main-label"><?php _e('Mutiple ordered items:', $this->plugin_id); ?></label>

                <div class="wpf-umf-fields">

                    <input type="hidden" name="wpf_umf_before_use_upload_procedure" value="0" />
                    <input type="checkbox" name="wpf_umf_before_use_upload_procedure" id="wpf_umf_before_use_upload_procedure" value="1" <?php checked( get_option('wpf_umf_before_use_upload_procedure')); ?> />
                    <label for="wpf_umf_before_use_upload_procedure"><?php _e('Enable', $this->plugin_id); ?></label>

                    <div class="description"><?php _e('If a customer orders multiple items of the same product, the customer must upload files for each item before they can proceed to checkout (the \'Upload procedure\' setting must be set to \'Multiple uploads for each product\').', $this->plugin_id); ?></div>

                </div>

                <div class="clear"></div>

            </div>

            <div class="row">

                <label for="wpf_umf_before_disable_cart_upload_button" class="main-label"><?php _e('Hide upload button in cart:', $this->plugin_id); ?></label>

                <div class="wpf-umf-fields">

                    <input type="hidden" name="wpf_umf_before_disable_cart_upload_button" value="0" />
                    <input type="checkbox" name="wpf_umf_before_disable_cart_upload_button" id="wpf_umf_before_disable_cart_upload_button" value="1" <?php checked( get_option('wpf_umf_before_disable_cart_upload_button')); ?> />
                    <label for="wpf_umf_before_disable_cart_upload_button"><?php _e('Hide', $this->plugin_id); ?></label>

                    <div class="description"><?php _e('Check this option if you want to hide the upload button for each product in the cart', $this->plugin_id); ?></div>

                </div>

                <div class="clear"></div>

            </div>

        </div>

    </div>

</div>

<input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" />