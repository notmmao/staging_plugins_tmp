<?php
/**
 * Plugin Name: GG Backend Search
 * Plugin URI: http://matthias-web.de/gg-search
 * Description: Adds a double GG-Key-Tab backend search.
 * Version: 1.1.4
 * Author: Matthias Günter
 * Author URI: http://matthias-web.de
 * License: GPL2
 * Text Domain: ggsearch
 */
 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define('GGSEARCH_PATH', dirname ( __FILE__ ));
define('GGSEARCH_FILE', __FILE__);
define('GGSEARCH_VERSION', '1.1.4');

// Localize the plugin
add_action( 'plugins_loaded', "gg_search_plugins_laoded" );
function gg_search_plugins_laoded() {
    load_plugin_textdomain( 'ggsearch', FALSE, dirname(plugin_basename(__FILE__)).'/languages/' );
}

// Load core
require_once(dirname ( __FILE__ ) . '/GG_Core.class.php');
GG_Core::start();

// Matthias advert
include_once('advert.php');
register_activation_hook( __FILE__, 'matthiasweb_advert_activation' );
?>