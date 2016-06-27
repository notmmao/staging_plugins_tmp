<div class="row">

    <label for="wpf_umf_email_link_0" class="main-label"><?php _e('Link to customer e-mail:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <div>
            <input name="wpf_umf_email_link" id="wpf_umf_email_link_0" value="0" type="radio" <?php checked( get_option('wpf_umf_email_link'), 0); ?> /> <label for="wpf_umf_email_link_0"><?php _e('Add link to order detail page (requires customer account)', $this->plugin_id); ?></label>
        </div>

        <div class="wpf-mar-top-10">
            <input name="wpf_umf_email_link" id="wpf_umf_email_link_1" value="1" type="radio" <?php checked( get_option('wpf_umf_email_link'), 1); ?> /> <label for="wpf_umf_email_link_1"><?php _e('Add link to order tracking page (no account required)', $this->plugin_id); ?></label>
        </div>

        <div class="wpf-mar-top-10">
            <input name="wpf_umf_email_link" id="wpf_umf_email_link_2" value="2" type="radio" <?php checked( get_option('wpf_umf_email_link'), 2); ?> /> <label for="wpf_umf_email_link_2"><?php _e('Don\'t add link to email', $this->plugin_id); ?></label>
        </div>



    </div>

    <div class="clear"></div>

</div>

<div class="row">

    <label for="wpf_umf_email_link_0" class="main-label"><?php _e('E-mail text customer e-mail:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

         <?php $option = array(); $option = get_option('wpf_umf_email_text'); ?>

        <div>
            <input name="wpf_umf_email_text[singular]" id="wpf_umf_email_text_singular" value="<?php echo esc_attr(stripslashes($option['singular'])); ?>" type="text"  />
            <div class="description"><?php _e('Singular: One file upload', $this->plugin_id); ?></div>
        </div>

        <div class="wpf-mar-top-10">
            <input name="wpf_umf_email_text[plural]" id="wpf_umf_email_text_plural" value="<?php echo esc_attr(stripslashes($option['plural'])); ?>" type="text" />
            <div class="description"><?php _e('Plural: Multiple file upload', $this->plugin_id); ?></div>
        </div>

    </div>

    <div class="clear"></div>

</div>