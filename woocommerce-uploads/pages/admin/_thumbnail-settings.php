<div class="row">

    <label for="wpf_umf_thumbnail_enable" class="main-label"><?php _e('Preview thumbnails:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input type="hidden" name="wpf_umf_thumbnail_enable" value="0" />
        <input type="checkbox" name="wpf_umf_thumbnail_enable" id="wpf_umf_thumbnail_enable" value="1" <?php checked( get_option('wpf_umf_thumbnail_enable'),true, true ); ?> />
        <label for="wpf_umf_thumbnail_enable"><?php _e('Enable', $this->plugin_id); ?></label>

        <div class="description"><?php _e('Check this option if you want to show the customer a thumbnail in case the uploaded file is an image.', $this->plugin_id); ?></div>

    </div>

    <div class="clear"></div>

</div>

<div id="wpf-umf-thumbnail-box"<?php echo (get_option('wpf_umf_thumbnail_enable') == 0)?' class="hidden"':''; ?>>

    <div class="row wpf-umf-border-top">

        <label for="wpf_umf_thumbnail_size_width" class="main-label"><?php _e('Preview thumbnail size:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <label for="wpf_umf_thumbnail_size_width"><?php _e('Width', $this->plugin_id); ?>:</label>
            <input type="number" name="wpf_umf_thumbnail_size_width" id="wpf_umf_thumbnail_size_width" class="small-text" value="<?php echo get_option('wpf_umf_thumbnail_size_width'); ?>" /> px

            <label for="wpf_umf_thumbnail_size_height" style="margin-left: 20px;"><?php _e('Height', $this->plugin_id); ?>:</label>
            <input type="number" name="wpf_umf_thumbnail_size_height" id="wpf_umf_thumbnail_size_height" class="small-text" value="<?php echo get_option('wpf_umf_thumbnail_size_height'); ?>"/> px

            <div class="description"><?php _e('Select the size of the preview thumbnail images. Sizes must be given in pixels.', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div id="wpf-umf-thumbnail-wp-box" class="wpf-umf-thumbnail-x-box">

        <div class="row">

            <label for="wpf_umf_thumbnail_wp_crop" class="main-label"><?php _e('Crop thumbnails:', $this->plugin_id); ?></label>

            <div class="wpf-umf-fields">

                <input type="hidden" name="wpf_umf_thumbnail_wp_crop" value="0" />
                <input type="checkbox" name="wpf_umf_thumbnail_wp_crop" id="wpf_umf_thumbnail_wp_crop" value="1" <?php checked( get_option('wpf_umf_thumbnail_wp_crop'),true, true ); ?> />
                <label for="wpf_umf_thumbnail_wp_crop"><?php _e('Enable', $this->plugin_id); ?></label>


                <div class="description"><?php _e('Do you want to crop your thumbnails to above defined max width and height?', $this->plugin_id); ?></div>

            </div>

            <div class="clear"></div>

        </div>

        <div class="row">

            <label for="wpf_umf_thumbnail_wp_quality" class="main-label"><?php _e('Thumbnail quality:', $this->plugin_id); ?></label>

            <div class="wpf-umf-fields">

                <div class="wpf-umf-left" style="width: 80%;">
                    <div id="wpf_umf_thumbnail_quality_slider"></div>
                </div>

                <div class="wpf-umf-left" style="width: 17%; margin-left: 3%;">
                    <input type="number" name="wpf_umf_thumbnail_wp_quality" id="wpf_umf_thumbnail_wp_quality" class="small-text" value="<?php echo get_option('wpf_umf_thumbnail_wp_quality'); ?>" /> %
                </div>

                <div class="clear"></div>

                <div class="description"><?php _e('Quality of the generated thumbnails in %. A value between 0 - 100 must be given. How higher the quality, how bigger the filesize will be.', $this->plugin_id); ?></div>

            </div>

            <div class="clear"></div>

        </div>

    </div>

</div>