<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Handler für die Ausgabe wenn AJAX aufruft
add_action( 'wp_ajax_gg_search_action', 'gg_search_action_callback' );
function gg_search_action_callback() {
	global $wpdb;
	
	$output = array("entries" => array(), "extraShow" => array());
	
	$term = trim($_POST["term"]);
	if (!empty($term)) {
	    // Filter laden
	    $collection = new GG_Filter();
        $collection = apply_filters( 'gg_filter', $collection );
        $collection->sort(); // Sortiere nach Priorität
        
        $collection->collect($term);
        $output["entries"] = $collection->output($term);
        
        // Extras, die nach Condition wieder angezeigt werden sollten
        $collection = new GG_Extra();
        $collection = apply_filters('gg_extra', $collection);
        foreach ($collection->getExtras() as $value) {
            if (is_callable($value["condition"])) {
                if (call_user_func($value["condition"], $term) === true) {
                    $output["extraShow"][] = $value["name"];
                }
            }
        }
        
        echo json_encode($output);
	}
    
	wp_die();
}