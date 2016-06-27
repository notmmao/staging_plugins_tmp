<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Singleton Core Class
 */
class GG_Core {
    
    private static $me = null;
    // General configs
    private $cfgGroups;
    private $cfgExtras;
    private $cfgGeneral;

    public function __construct() {
        include_once(GGSEARCH_PATH . "/inc/class/GG_Config.class.php");
        $this->initConfig();
    }
    
    /**
     * Reset or initialize general configs
     * @param $forceWrite Forces to rewrite the config
     * @param $forceConfigs When $forceWrite is true, set here an array which configs should be rewrite
     */
    public function initConfig($forceWrite = false, $forceConfigs = array()) {
        if (in_array("general", $forceConfigs) || $forceWrite == false) {   
            $this->cfgGeneral = new GG_Config("general",
            array(
                "placeholder" => "",
                "point" => true,
                "maxlimit" => 5,
                "adminmenu" => true,
                "frontend" => true,
                "roles" => array("administrator", "editor", "author", "contributor")
            ), $forceWrite);
        }
        
        if (in_array("groups", $forceConfigs) || $forceWrite == false) {
            $this->cfgGroups = new GG_Config("groups",
            array("prioritys" => array(),
                "hidden" => array(
                    "cpt_attachment", "cpt_revision", "cpt_nav_menu_item", "cpt_template"
                )
            ), $forceWrite);
        }
         
        if (in_array("extras", $forceConfigs) || $forceWrite == false) {   
            $this->cfgExtras = new GG_Config("extras",
            array(
                "prioritys" => array(),
                "hidden" => array()
            ), $forceWrite);
        }
    }
    
    /**
     * Include all necessery files and classes
     */
    public function include_all() {
        $pathes = array(
            "inc/class/GG_Filter.class.php",
            "inc/class/GG_Extra.class.php",
            "inc/class/GG_List_Table.class.php",
            "inc/class/GG_Extra_Table.class.php",
            "inc/filters/general.php",
            "inc/filters/customtypes.php",
            "inc/filters/singleextras.php",
            "inc/filters/others.php",
            "inc/frontend.php",
            "inc/ajax.php");
        
        for ($i = 0; $i < count($pathes); $i++) {
            require_once(GGSEARCH_PATH . '/' . $pathes[$i]);
        }
    }
    
    
    /**
     * Starts filters and actions
     */
    public function paging() {
        add_action('admin_menu', array($this, 'plugin_menu'));
        add_action('wp_head', array($this, 'wp_head'));
        add_action('admin_bar_menu', array($this, 'toolbar_link_to_search'), 999);
        if (get_option("gg_search_admin_notice") === false && $_GET["page"] != "gg-search.php") {
        	add_action( 'admin_notices', array($this, 'admin_notice') );
        }
    }
    
    public function toolbar_link_to_search( $wp_admin_bar ) {
    	$args = array(
    		'id'    => 'gg_search',
    		'title' => '<i class="fa fa-search"></i>',
    		'href'  => '#',
    		'meta'  => array( 'class' => 'gg-toolbar-button' )
    	);
    	$wp_admin_bar->add_node( $args );
    }
    
    public function wp_head() {
        ?>
        <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
        </script>
        <?php
    }
    
    /**
     * Add "GG Search" menu point
     */
    public function plugin_menu() {
        add_action( 'admin_enqueue_scripts', 'gg_search_my_enqueue_settings' );
        function gg_search_my_enqueue_settings($hook) {
        	wp_enqueue_script('gg-sortabl-script', plugins_url( 'assets/js/Sortable.min.js', GGSEARCH_FILE ));
        }
        
        add_options_page('GG Search', 'GG Search', 'manage_options', 'gg-search.php', array($this, "plugin_page"));
    }
    
    /**
     * Content for options page
     */
    public function plugin_page() {
        // Setze Option für die Admin Notiz
        add_option("gg_search_admin_notice", true);
        
        // Tab holen
        $tab = isset($_GET["tab"]) ? $_GET["tab"] : "";
        if (empty($tab)) {
            $tab = "general";
        }
        $tab_path = GGSEARCH_PATH . "/pages/" . $tab . ".php";
        if (file_exists($tab_path)) {
            echo '<div class="gg-wrapper wrap">';
            $this->nav($tab);
            include_once($tab_path);
            echo '</div>';
        }else{
            echo "Unknown Error: Tab page not found.";
        }
    }
    
    /**
     * Create navigation menu
     */
    public function nav($tab) {
        ?>
        <h2>GG Search</h2>
        <div class="wp-filter">
        	<ul class="filter-links">
            	<li><a href="?page=gg-search.php&tab=general" class="<?php if ($tab == "general") echo "current"; ?>"><?php _e('Settings'); ?></a> </li>
            	<li><a href="?page=gg-search.php&tab=groups" class="<?php if ($tab == "groups") echo "current"; ?>"><?php _e('Groups', "ggsearch"); ?></a> </li>
            	<li><a href="?page=gg-search.php&tab=extras" class="<?php if ($tab == "extras") echo "current"; ?>"><?php _e('Extras', "ggsearch"); ?></a> </li>
            	<li><a href="https://de.wordpress.org/plugins/search.php?q=GG+Search" target="_blank"><?php _e('Extensions', "ggsearch"); ?></a> </li>
            	<li><a href="http://matthias-web.de/gg-search/filter-referencegg_filter/" target="_blank"><?php _e('API for developers', "ggsearch"); ?></a> </li>
        	</ul>
        </div>
        <?php
    }
    
    /**
     * Admin notice for getting start
     */
    public function admin_notice() {
	    ?>
	    <div class="updated">
	        <p><b><?php _e('Congratulations, you have GG Search enabled!', 'ggsearch'); ?></b> <?php _e("Discover the advantages of global admin search, it helps you all the elaborate search works.", "ggsearch"); ?></p>
	        <p><?php _e("First, you should config your search to your needs.", "ggsearch"); ?>  <a href="<?php echo admin_url('options-general.php?page=gg-search.php'); ?>"><?php _e("Settings", "ggsearch"); ?></a></p>
	        <p><?php _e("If you want to start searching, click two times the <code>G (G + G)</code> Key.", "ggsearch"); ?></p>
	    </div>
	    <?php
	}
    
    /**
     * Checks, if a user have a role
     * @param $role Role
     * @param $user User Object
     */
    public function hasRole($role, $user) {
        foreach ($user->roles as $value) {
            if ($value == $role) return true;
        }
        return false;
    }
    
    /**
     * Check, if a user is allowed to user GG Search
     */
    public function enabledFor() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $current_user = wp_get_current_user();
        $roles = $this->getCfgGeneral()->roles;
        if (!is_array($roles)) return false;
        
        foreach ($roles as $value) {
            if ($this->hasRole($value, $current_user)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get localized text for a post status
     * @param $status Status
     * @return Localized string
     */
    public function _ePostStatus($status) {
        switch ( $status ) {
        	case 'private':
        		return __('Privately Published');
        	case 'publish':
        		return __('Published');
        	case 'future':
        		return __('Scheduled');
        	case 'pending':
        		return __('Pending Review');
        	case 'draft':
        	case 'auto-draft':
        		return __('Draft');
        }
    }
    
    public function getCfgGroups() {
        return $this->cfgGroups;
    }
    
    public function getCfgExtras() {
        return $this->cfgExtras;
    }
    
    public function getCfgGeneral() {
        return $this->cfgGeneral;
    }
    
    /**
     * Starts the plugin settings
     */
    public static function start() {
        
        $instance = self::getInstance();
        $instance->include_all();
        $instance->paging();
        
    }
    
    public static function print_r($row) {
        echo '<pre>';
        print_r($row);
        echo '</pre>';
    }
    
    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new GG_Core();
        }
        return self::$me;
    }
    
    public static function get_object_vars_from_public($obj) {
        return get_object_vars($obj);
    }
    
}