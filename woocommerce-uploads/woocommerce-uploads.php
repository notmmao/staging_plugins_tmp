<?php
/*
Plugin Name: WooCommerce Uploads
Plugin URI: http://www.wpfortune.com
Description: Upload files in WooCommerce
Version: 1.1.7
Author: WP Fortune
Author URI: http://www.wpfortune.com/
Text Domain: woocommerce-uploads
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/*  Copyright 2014  WP Fortune  (email : info@wpfortune.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once(plugin_dir_path( __FILE__ ) . 'classes/class-wpf-uploads.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/helper.php');

/*
 * Settings needed for correct use with the WPFortune API
 */

$settings_plugin_name      =    'WooCommerce Uploads';
$settings_plugin_version   =    '1.1.7';
$settings_plugin_id        =    'woocommerce-uploads'; // Needed to work with the WPFortune updater
$settings_plugin_slug      =    'wpf_umf';
$settings_plugin_file      =    plugin_basename( __FILE__ );
$settings_plugin_dir       =    plugin_dir_path( __FILE__ );
$settings_upgrade_url      =    'https://www.wpfortune.com';
$settings_renew_url        =    'https://www.wpfortune.com/my-account/';
$settings_docs_url         =    'https://www.wpfortune.com/documentation/plugins/woocommerce-uploads/';
$settings_support_url      =    'https://support.wpfortune.com';

$wpf_uploads_instance = new WPF_Uploads($settings_plugin_name, $settings_plugin_version, $settings_plugin_id, $settings_plugin_slug, $settings_plugin_dir, $settings_plugin_file, $settings_upgrade_url, $settings_renew_url, $settings_docs_url, $settings_support_url);

/*
 * Temporary message for WP Fortune plugin update
 */

if (!function_exists('show_wpfortune_update') && is_admin()) {

    add_action('admin_init', 'show_wpfortune_update');

    function show_wpfortune_update() {
        $path = plugin_dir_path(__FILE__).'../wpfortune/wpfortune.php';

        if (file_exists($path)) {

            $t = get_plugin_data($path);

            if (version_compare($t['Version'], '1.0.2', '<')) {

                add_action( 'admin_notices', 'show_wpfortune_update_message' );

            }

        }

    }

    function show_wpfortune_update_message()
    {
         echo '<div class="update-nag">
              Please update your WP Fortune plugin to the latest version. <a href="https://www.wpfortune.com/downloads/wpfortune.zip">Click here to download</a>
          </div>';

    }

}
?>