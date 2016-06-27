<div id="wpf-umf-upload-container">

    <?php if ($upload_set_type == 'product'): ?>

        <?php wp_nonce_field($this->plugin_slug.'_uploadset_'.$upload_set_product_id, '_'.$this->plugin_slug.'_uploadset_nonce'); ?>

        <div class="alignleft">
            <input type="hidden" name="wpf_umf_upload_enable" value="0" />
            <input type="checkbox" name="wpf_umf_upload_enable" id="wpf_umf_upload_enable" value="1" <?php echo (get_post_meta($post_id, '_wpf_umf_upload_enable', true) == '')?((get_option('wpf_umf_enable_default') == 1)?'checked':''):checked(get_post_meta($post_id, '_wpf_umf_upload_enable', true), true, false); ?> />
            <label for="wpf_umf_upload_enable"><?php _e('Enable upload', $this->plugin_id); ?></label>
        </div>

    <?php endif; ?>

    <a href="#" class="button button-green right" id="wpf-umf-upload-add-set" title="<?php _e('Add a new upload box to this set', $this->plugin_id); ?>"><?php _e('Add new upload box', $this->plugin_id); ?></a>

    <div class="clear"></div>

    <div id="wpf-umf-upload-boxes">

        <?php foreach ($upload_sets AS $id => $data): ?>

        <div id="wpf-umf-upload-box-<?php echo $id; ?>" class="wpf-umf-upload-box" data-id="<?php echo $id; ?>">

            <a class="wpf-umf-upload-box-delete button button-red"><?php _e('Delete', $this->plugin_id); ?></a>
            <a class="wpf-umf-upload-box-advanced button button-secondary" style="width: 120px;"><span class="dashicons dashicons-arrow-down"></span><?php _e('More settings', $this->plugin_id); ?></a>


            <div class="wpf-umf-upload-row">

                <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_title"><?php _e('Upload title:', $this->plugin_id); ?></label>

                <div class="wpf-umf-upload-field">

                    <input id="wpf_umf_upload_<?php echo $id; ?>_title" name="wpf_umf_upload[<?php echo $id; ?>][title]" type="text" class="regular-input" value="<?php echo $data['title']; ?>" required />

                </div>

                <div class="clear"></div>

            </div>

            <div class="wpf-umf-upload-box-collapse hidden">

                <div class="wpf-umf-upload-row">

                    <label  class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_description"><?php _e('Upload description:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">
                        <textarea id="wpf_umf_upload_<?php echo $id; ?>_description" name="wpf_umf_upload[<?php echo $id; ?>][description]" class="regular-input" rows="4"><?php echo $data['description']; ?></textarea>
                    </div>

                    <div class="clear"></div>

                </div>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_amount"><?php _e('Number of uploads:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">

                        <input name="wpf_umf_upload[<?php echo $id; ?>][amount]" id="wpf_umf_upload_<?php echo $id; ?>_amount" type="number" value="<?php echo $data['amount']; ?>" class="small-text">



                    </div>

                    <div class="clear"></div>


                </div>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_filetypes"><?php _e('(Dis)Allowed file types:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">

                        <select name="wpf_umf_upload[<?php echo $id; ?>][blocktype]">
                            <option value="allow" <?php selected($data['blocktype'], 'allow'); ?>>Allow</option>
                            <option value="disallow" <?php selected($data['blocktype'], 'disallow'); ?>>Disallow</option>
                        </select>

                        <input name="wpf_umf_upload[<?php echo $id; ?>][filetypes]" type="text" id="wpf_umf_upload_<?php echo $id; ?>_filetypes" style="width: 164px;" value="<?php echo $data['filetypes']; ?>" />


                    </div>

                    <div class="clear"></div>

                </div>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_maxuploadsize"><?php _e('Max. upload size:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">
                        <input type="number" name="wpf_umf_upload[<?php echo $id; ?>][maxuploadsize]" id="wpf_umf_upload_<?php echo $id; ?>_maxuploadsize" class="small-text" value="<?php echo $data['maxuploadsize']; ?>" /> MB
                    </div>

                    <div class="clear"></div>


                </div>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_min_resolution_width"><?php _e('Min. resolution:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">

                        <label for="wpf_umf_upload_<?php echo $id; ?>_min_resolution_width"><?php _e('Width', $this->plugin_id); ?>:</label>
                        <input type="number" name="wpf_umf_upload[<?php echo $id; ?>][min_resolution_width]" id="wpf_umf_upload_<?php echo $id; ?>_min_resolution_width" class="small-text" value="<?php echo $data['min_resolution_width']; ?>" /> px

                        <label for="wpf_umf_upload_<?php echo $id; ?>_min_resolution_height" style="margin-left: 20px;"><?php _e('Height', $this->plugin_id); ?>:</label>
                        <input type="number" id="wpf_umf_upload_<?php echo $id; ?>_min_resolution_height" name="wpf_umf_upload[<?php echo $id; ?>][min_resolution_height]" class="small-text" value="<?php echo $data['min_resolution_height']; ?>" /> px


                    </div>

                    <div class="clear"></div>


                </div>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_max_resolution_width"><?php _e('Max. resolution:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field">

                        <label for="wpf_umf_upload_<?php echo $id; ?>_max_resolution_width"><?php _e('Width', $this->plugin_id); ?>:</label>
                        <input type="number" name="wpf_umf_upload[<?php echo $id; ?>][max_resolution_width]" id="wpf_umf_upload_<?php echo $id; ?>_max_resolution_width" class="small-text" value="<?php echo $data['max_resolution_width']; ?>" /> px

                        <label for="wpf_umf_upload_<?php echo $id; ?>_max_resolution_height" style="margin-left: 20px;"><?php _e('Height', $this->plugin_id); ?>:</label>
                        <input type="number" id="wpf_umf_upload_<?php echo $id; ?>_max_resolution_height" name="wpf_umf_upload[<?php echo $id; ?>][max_resolution_height]" class="small-text" value="<?php echo $data['max_resolution_height']; ?>" /> px

                    </div>

                    <div class="clear"></div>

                </div>

                <?php if ($upload_set_type == 'product' && is_array($product_variations) && count($product_variations) > 0): ?>

                <div class="wpf-umf-upload-row">

                    <label class="main-label" for="wpf_umf_upload_<?php echo $id; ?>_variation_show"><?php _e('Show for variations:', $this->plugin_id); ?></label>

                    <div class="wpf-umf-upload-field clearfix">

                        <div class="wpf-umf-left" style="width: 70px;">
                            <input type="radio" id="wpf_umf_upload_<?php echo $id; ?>_variation_show_0" name="wpf_umf_upload[<?php echo $id; ?>][variation_show]" value="0" <?php echo (empty($data['variation_show']))?'CHECKED':''; ?> /> <?php _e('All', $this->plugin_id); ?>
                        </div>

                        <div class="wpf-umf-left" style="width: 100px;">
                            <b>Or choose:</b>
                        </div>

                        <div class="wpf-umf-left">

                            <select name="wpf_umf_upload[<?php echo $id; ?>][variation_show][]" id="wpf_umf_upload_<?php echo $id; ?>_variation_show" multiple>

                                <?php foreach ($product_variations AS $product_variation_id => $product_variation):

                                    $selected = (is_array($data['variation_show']) && in_array($product_variation_id, $data['variation_show']) )?'SELECTED':'';

                                ?>

                                    <option value="<?php echo $product_variation_id; ?>" <?php echo $selected; ?>><?php echo $product_variation; ?></option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <div class="clear"></div>

                </div>

                <?php endif; ?>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <div class="clear"></div>

    <?php if (get_option('wpf_umf_uploader') == 'html'): ?>

        <?php printf(__('Please check the combined upload max sizes don\'t override your PHP settings of %s.', $this->plugin_id), '<b>'.WPF_Uploads::get_max_upload_size().'</b>'); ?>

    <?php endif; ?>


</div>
