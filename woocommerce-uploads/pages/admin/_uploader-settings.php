<div class="row">

    <label for="wpf_umf_uploader_ajax" class="main-label"><?php _e('Uploader type:', $this->plugin_id); ?></label>

    <div class="wpf-umf-fields">

        <input name="wpf_umf_uploader" id="wpf_umf_uploader_ajax" type="radio" value="ajax" <?php checked( get_option('wpf_umf_uploader'), 'ajax'); ?>/>
        <label for="wpf_umf_uploader_ajax"><?php _e('AJAX enabled upload', $this->plugin_id); ?></label>

        <input name="wpf_umf_uploader" id="wpf_umf_uploader_html" type="radio" value="html" <?php checked( get_option('wpf_umf_uploader'), 'html'); ?>/>
        <label for="wpf_umf_uploader_html"><?php _e('HTML upload (old)', $this->plugin_id); ?></label>

    </div>

    <div class="clear"></div>

</div>

<div id="wpf-umf-uploader-box"<?php echo (get_option('wpf_umf_uploader') != 'ajax')?' class="hidden"':''; ?>>

    <div class="row wpf-umf-border-top">

        <label for="wpf_umf_uploader_dropzone" class="main-label"><?php _e('Show dropzone:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input type="hidden" name="wpf_umf_uploader_dropzone" value="0" />
            <input type="checkbox" name="wpf_umf_uploader_dropzone" id="wpf_umf_uploader_dropzone" value="1" <?php checked( get_option('wpf_umf_uploader_dropzone')); ?> />
            <label for="wpf_umf_uploader_dropzone"><?php _e('Enable', $this->plugin_id); ?></label>

            <div class="description"><?php _e('Check this option if you want to show a dropzone on your upload box(es). If this option is checked, customers can drag and drop their images easily to an area to upload the files. ', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div class="row">

        <label for="wpf_umf_uploader_autostart" class="main-label"><?php _e('Autostart uploading:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input type="hidden" name="wpf_umf_uploader_autostart" value="0" />
            <input type="checkbox" name="wpf_umf_uploader_autostart" id="wpf_umf_uploader_autostart" value="1" <?php checked( get_option('wpf_umf_uploader_autostart')); ?> />
            <label for="wpf_umf_uploader_autostart"><?php _e('Enable', $this->plugin_id); ?></label>

            <div class="description"><?php _e('Check this option if you want to automatically start uploading when files are added. ', $this->plugin_id); ?></div>

        </div>

        <div class="clear"></div>

    </div>

    <div class="row">

        <label for="wpf_umf_uploader_chunksize" class="main-label"><?php _e('Max. chunk size:', $this->plugin_id); ?></label>

        <div class="wpf-umf-fields">

            <input type="number" name="wpf_umf_uploader_chunksize" id="wpf_umf_uploader_chunksize" class="small-text" value="<?php echo get_option('wpf_umf_uploader_chunksize'); ?>" required /> MB

            <div class="description"><?php printf(__('Specify maximum upload chunk size in MegaBytes. Large files will be split in multiple parts (chunks) to handle the upload process. Don\'t set this higher then %s.', $this->plugin_id), WPF_Uploads::get_max_upload_size()); ?></div>

        </div>

        <div class="clear"></div>

    </div>

</div>