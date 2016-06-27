<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'plugins_loaded', 'gg_enabled_for');
function gg_enabled_for() {
	if (GG_Core::getInstance()->enabledFor()) {
		// Erstelle HTML-Gerüst für alle Backend-Seiten
		add_action( 'admin_footer', 'gg_search_action_footer' );
		add_action( 'wp_footer', 'gg_search_action_footer' );
		
		// Styles und Javascripts einbinden 
		add_action( 'admin_enqueue_scripts', 'gg_search_my_enqueue' );
		if (GG_Core::getInstance()->getCfgGeneral()->frontend == true) { // option to allow in Frontend
			add_action( 'wp_enqueue_scripts', 'gg_search_my_enqueue' );
		}
	}
}

function gg_search_action_footer() {
	// General Settings
	$general = GG_Core::getInstance()->getCfgGeneral();
	$classes = "";
	if ($general->point)
		$classes .= " gg-general-point";
	if ($general->adminmenu)
		$classes .= " gg-general-adminmenu";
	if ($general->frontend)
		$classes .= " gg-general-frontend";
	?>
	
	<div id="gg-search" class="<?php echo $classes; ?>">
	    <input type="text" placeholder="<?php echo (GG_Core::getInstance()->getCfgGeneral()->placeholder == "") ? __('Search', "ggsearch") . "..." : GG_Core::getInstance()->getCfgGeneral()->placeholder; ?>" />
	    <i class="fa fa-circle-o-notch fa-spin"></i>
	    <div class="gg-not-found"><?php _e('No Results.', 'ggsearch'); ?></div>
	    <div class="rows"></div>
	    <div class="fix"></div>
	    
	    <?php
	    $extras = apply_filters("gg_extra", new GG_Extra());
	    $scripts = array();
	    
	    if ($extras->hasExtras()) {
	    	$extras->sort();
	    	
	    	echo '<div class="gg-extras">
		    	<div class="gg-group gg-extra">' . __('Extras', 'ggsearch') . '</div>';
		    	
		   	foreach ($extras->getExtras() as $value) {
		   		if (GG_Extra::output($value) !== false) {
		   			$scripts[$value["name"]] = $value["script"];
		   		}
		   	}
		    	
		    echo '</div>';
		    
		    if (count($scripts) > 0) {
		    	echo '<script type="text/javascript">
			    "use strict";
			    jQuery(document).ready(function($) {
			    	if (typeof getParameterByName === "undefined") return;
			    
			        var term;';
			        
			    foreach ($scripts as $key => $value) {
			    	echo '
			    	term = getParameterByName("' . $key . '");
			        if (term != "") {
			            ' . $value . '
			        }
			    	';
			    }
			        
			    echo '
			    });
    			</script>';
		    }
	    }
	    
	    //GG_Core::print_r(GG_Core::getInstance()->getCfgGroups());
	    ?>
	    
	    <div class="gg-info">
	    	<a href="<?php echo admin_url('options-general.php?page=gg-search.php'); ?>"><i class="fa fa-cog"></i> <?php _e('Settings'); ?></a>
	    </div>
	    <?php do_action("gg_search_box_end"); ?>
	</div>
	
	<?php
}

function gg_search_my_enqueue($hook) {
	wp_enqueue_script('jquery-viewport', plugins_url( 'assets/js/jquery.viewport.js', GGSEARCH_FILE ), array('jquery'), GGSEARCH_VERSION);
	wp_enqueue_script('gg-main-script', plugins_url( 'assets/js/main.js', GGSEARCH_FILE ), array('jquery-viewport'), GGSEARCH_VERSION);
	wp_enqueue_script('gg-hook-script', plugins_url( 'assets/js/hooks.js', GGSEARCH_FILE ), array('gg-main-script'), GGSEARCH_VERSION);
	wp_enqueue_style('font-awesome',  'http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', GGSEARCH_VERSION);
	wp_enqueue_style('gg-main-style',  plugins_url( 'assets/css/main.css', GGSEARCH_FILE ), GGSEARCH_VERSION);
	
	if ( !is_admin_bar_showing() ) {
	    ?>
	    <style>
	    	#gg-search { top: 0px !important; }
	    </style>
	    <?php
	}
	
	do_action("gg_search_enqueue");
	
	wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}