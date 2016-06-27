<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * WPFortune base class.
 * Performs the update process of this plugin.
 *
 * @copyright   Copyright (c) WPFortune
 */

// If class does not exists yet

if (!class_exists('WPFortune_Base')):

class WPFortune_Base {

    public $plugin_name;
    public $plugin_slug;
    public $plugin_id;
    public $plugin_file;
    public $plugin_dir;
    public $plugin_docs_url;
    public $plugin_support_url;
	public $version;
	public $plugin_url;

    /*
     * Perform update check on instantiaton of this class
     */

	public function __construct($settings_plugin_name, $settings_plugin_version, $settings_plugin_id, $settings_plugin_slug, $settings_plugin_dir, $settings_plugin_file, $settings_upgrade_url, $settings_renew_url, $settings_docs_url, $settings_support_url)
    {

        // Set plugin wide properties
        $this->plugin_name = $settings_plugin_name;
        $this->plugin_slug = $settings_plugin_slug;
        $this->plugin_id = $settings_plugin_id;
        $this->plugin_file = $settings_plugin_file;
        $this->plugin_dir = $settings_plugin_dir;
        $this->plugin_docs_url = $settings_docs_url;
        $this->plugin_support_url = $settings_support_url;
        $this->plugin_url = plugins_url($this->plugin_id).'/';
        $this->version = $settings_plugin_version;

		if ( is_admin() ) {

			require_once( plugin_dir_path( __FILE__ ) . 'class-wc-plugin-update.php' );

			$options = get_option('wpfortune_pld_'.$this->plugin_id);

            add_filter('plugin_action_links_'.$this->plugin_file, array($this, 'plugin_links'));

			// Check for software updates

			if ( ! empty( $options ) && $options !== false ) {

                $instance = get_option('wpfortune_instance');
                $wpfortune_version = get_option('wpfortune_version');

                if ($wpfortune_version != false && version_compare($wpfortune_version, '1.0.4', '>=')) {

                    $instance = get_option('wpfortune_instance') . substr(md5($this->plugin_name), 0, -16);

                }



				$this->update_check(
					$settings_upgrade_url,
					untrailingslashit($this->plugin_file),
					$this->plugin_name,
				    $options['license_key'],
					$options['license_email'],
					$settings_renew_url,
					$instance,
					home_url(),
					$this->version,
					'plugin',
					$this->plugin_slug
				);

			}

		}

	}

    public function plugin_links($links)
    {

        $new = array(
            'settings' => '<a href="'.admin_url('admin.php?page='.$this->plugin_slug).'">'.__('Settings', $this->plugin_slug).'</a>',
            'docs' => '<a href="'.$this->plugin_docs_url.'" target="_blank">'.__('Docs', $this->plugin_slug).'</a>',
            'support' => '<a href="'.$this->plugin_support_url.'" target="_blank">'.__('Support', $this->plugin_slug).'</a>',
        );
		return wp_parse_args($links, $new);

    }

	/**
	 * Update Check Class.
	 *
	 * @return UpdateAPICheck
	 */
	public function update_check( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra = '' ) {

		return UpdateAPICheck::instance( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra );
	}

    /*
     * Returns the url of this plugin
     *
     * @return string The plugin url
     */

	public function plugin_url() {

		return $this->plugin_url;
	}

}

endif;

?>