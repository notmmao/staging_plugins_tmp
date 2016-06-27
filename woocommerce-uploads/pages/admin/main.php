<div id="wpf-umf" class="wrap">

    <h2><?php echo $this->plugin_name; ?></h2>

    <form action="<?php echo admin_url('admin.php?page='.$_GET['page']); ?>" method="post" id="wpf-umf-main-form" class="wpf-umf-form">

        <?php wp_nonce_field($this->plugin_slug.'_post'); ?>
        <input type="hidden" name="wpf_umf_post_page" value="settings" />
        <div id="poststuff">

            <div id="post-body">

                <div id="wpf-umf-container-1" class="wpf-umf-left postbox-container">

                    <h2 id="wpf-umf-settings-tabs" class="nav-tab-wrapper">
                    	<a href="#1" id="wpf-umf-settings-tab-1" data-id="1" class="nav-tab nav-tab-active"><?php _e('General settings', $this->plugin_id); ?></a>
                    	<a href="#2" id="wpf-umf-settings-tab-2" data-id="2" class="nav-tab"><?php _e('Default upload set', $this->plugin_id); ?></a>
                    	<a href="#3" id="wpf-umf-settings-tab-3" data-id="3" class="nav-tab"><?php _e('Messages & Notifications', $this->plugin_id); ?></a>
                        <?php do_action('wpf-umf-tab-4'); ?>
                    </h2>

                    <div id="wpf-umf-settings-container-1" class="wpf-umf-settings-container">

                        <?php do_action('wpf_umf_general_settings_before'); ?>

                        <div class="postbox">

                            <h3><?php _e('Uploader', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_uploader-settings.php'); ?>

                            </div>

                        </div>

                        <input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" />

                        <div class="postbox">

                            <h3><?php _e('General settings', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_general-settings.php'); ?>

                            </div>

                        </div>

                        <input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" />

                        <div class="postbox">

                            <h3><?php _e('Preview thumbnail settings', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_thumbnail-settings.php'); ?>

                            </div>

                        </div>

                    </div>

                    <div id="wpf-umf-settings-container-2" class="wpf-umf-settings-container hidden">


                        <div class="postbox">

                            <h3><?php _e('Default upload set', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php $this->uploadset_render(); ?>

                            </div>

                        </div>

                    </div>

                    <div id="wpf-umf-settings-container-3" class="wpf-umf-settings-container hidden">

                        <div class="postbox">

                            <h3><?php _e('Messages', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_admin-messages.php'); ?>

                            </div>

                        </div>

                        <input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" />

                        <div class="postbox">

                            <h3><?php _e('Customer notifications', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_customer-notifications.php'); ?>

                            </div>

                        </div>

                        <input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" /> 

                        <div class="postbox">

                            <h3><?php _e('Admin notifications', $this->plugin_id); ?></h3>

                            <div class="inside">

                                <?php include_once('_admin-notifications.php'); ?>

                            </div>

                        </div>

                    </div>

                    <?php do_action('wpf-umf-tab-content-4'); ?>

                    <input type="submit" value="<?php _e('Save settings', $this->plugin_id); ?>" class="button button-primary submit-button" />

                </div>

                <div id="wpf-umf-container-2" class="wpf-umf-right postbox-container">

                    <div class="postbox">

                        <h3><?php _e('Need support?', $this->plugin_id); ?></h3>

                        <div class="wpf-support inside">

                            <div class="wpf-left" style="width: 75%; margin-right: 5%;">

                                <?php _e('If you\'re having any problems with this plugin, or if you got any questions related to this plugin, you can visit our documentation or contact us via our helpdesk'); ?>

                            </div>

                            <div class="wpf-left" style="width: 20%;">

                                <img src="<?php echo plugins_url($this->plugin_id.'/assets/img/wpfortune-character.png'); ?>" alt="" style="width: 100%;" />

                            </div>

                           <div class="clear"></div>

                           <div class="wpf-plugin-links wpf-mar-top-10">

                                <b><?php echo $this->plugin_name; ?></b>

                                <ul>
                                    <?php if (!class_exists('WPF_Uploads_Before')): ?>
                                    <li><a href="http://wpfortune.com/shop/plugins/woocommerce-uploads-add-on/"><?php _e('Want to upload before checkout?', $this->plugin_id); ?></a></li>
                                    <?php endif; ?>

                                    <li><a href="<?php echo $this->plugin_upgrade_url; ?>"><?php _e('About this plugin', $this->plugin_id); ?></a></li>
                                    <li><a href="<?php echo $this->plugin_docs_url; ?>"><?php _e('Documentation', $this->plugin_id); ?></a></li>
                                    <li><a href="<?php echo $this->plugin_support_url; ?>"><?php _e('FAQ', $this->plugin_id); ?></a></li>
                                    <li><a href="<?php echo $this->plugin_support_url; ?>"><?php _e('Support', $this->plugin_id); ?></a></li>

                                </ul>

                           </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>