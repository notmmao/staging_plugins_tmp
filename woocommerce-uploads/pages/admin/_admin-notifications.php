<div class="row">

    <label for="wpf_umf_notifications_enable" class="main-label"><?php _e('Admin notifications:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_notifications_enable" value="0" />
        <input type="checkbox" name="wpf_umf_notifications_enable" id="wpf_umf_notifications_enable" value="1" <?php checked(get_option('wpf_umf_notifications_enable')); ?>/>
        <label for="wpf_umf_notifications_enable"><?php _e('Enable', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Send a message to the admin when files are uploaded / changed by the user.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div id="wpf-umf-notifications-box" <?php echo (get_option('wpf_umf_notifications_enable') == 0)?' class="hidden"':''; ?>>

    <div class="row">

        <label for="wpf_umf_notifications_recurrence" class="main-label"><?php _e('Recurrence:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <select name="wpf_umf_notifications_recurrence" id="wpf_umf_notifications_recurrence">

                <option value="hourly" <?php selected(get_option('wpf_umf_notifications_recurrence'), 'hourly'); ?>>Hourly</option>
                <option value="twicedaily" <?php selected(get_option('wpf_umf_notifications_recurrence'), 'twicedaily'); ?>>Twice a day</option>
                <option value="daily" <?php selected(get_option('wpf_umf_notifications_recurrence'), 'daily'); ?>>Daily</option>

            </select>

            <div class="description"><?php _e('Choose how many times you want to receive an e-mail.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div class="row">

        <label for="wpf_umf_notifications_email" class="main-label"><?php _e('Recipients:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input name="wpf_umf_notifications_email" id="wpf_umf_notifications_email" class="regular-text" type="text" value="<?php echo esc_attr(stripslashes(get_option('wpf_umf_notifications_email'))); ?>" />

            <div class="description"><?php echo sprintf(__( 'Enter recipients (comma separated) for this email. Defaults to %s.', $this->plugin_id ),'<code>'.get_option('admin_email').'</code>');?></div>

        </div>

        <div class="clear"></div>

    </div>

</div>