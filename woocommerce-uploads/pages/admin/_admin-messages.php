<div class="row">

    <label for="wpf_umf_message_enable" class="main-label"><?php _e('Custom messages:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_message_enable" value="0" />
        <input type="checkbox" name="wpf_umf_message_enable" id="wpf_umf_message_enable" value="1" <?php checked(get_option('wpf_umf_message_enable')); ?> />
        <label for="wpf_umf_message_enable"><?php _e('Enable', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Check this option if you want to set your own messages, that will be showed to the customer after uploading a file.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div id="wpf-umf-custom-messages-box" <?php echo (get_option('wpf_umf_message_enable') == 0)?' class="hidden"':''; ?>>

    <div class="row">

        <label for="wpf_umf_message_not_checked" class="main-label"><?php _e('Not checked:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input name="wpf_umf_message_not_checked" id="wpf_umf_message_not_checked" class="regular-text" type="text" value="<?php echo esc_attr(stripslashes(get_option('wpf_umf_message_not_checked'))); ?>" />

            <div class="description"><?php _e('Please enter the text you want to display after upload.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div class="row">

        <label for="wpf_umf_message_declined_files" class="main-label"><?php _e('Declined files:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input name="wpf_umf_message_declined_files" id="wpf_umf_message_declined_files" class="regular-text" type="text" value="<?php echo esc_attr(stripslashes(get_option('wpf_umf_message_declined_files'))); ?>" />

            <div class="description"><?php _e('Please enter the text you want to display after you\'ve rejected a file.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div class="row">

        <label for="wpf_umf_message_accepted_files" class="main-label"><?php _e('Accepted files:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input name="wpf_umf_message_accepted_files" id="wpf_umf_message_accepted_files" class="regular-text" type="text" value="<?php echo esc_attr(stripslashes(get_option('wpf_umf_message_accepted_files'))); ?>"/>

            <div class="description"><?php _e('Please enter the text you want to display after you\'ve accepted a file.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

</div>

<div class="row">

    <label for="wpf_umf_message_upload_description" class="main-label"><?php _e('Upload description:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <?php wp_editor(stripslashes(get_option('wpf_umf_message_upload_description')), 'wpf_umf_message_upload_description',array('editor_class' => 'wpf-umf-editor', 'textarea_name' => 'wpf_umf_message_upload_description', 'media_buttons' => false) ); ?>

        <div class="description"><?php _e('Show a description below the upload title, leave empty to hide it.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>