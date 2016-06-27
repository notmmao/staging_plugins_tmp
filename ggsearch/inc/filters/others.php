<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('pre_user_query', '__gg_user_search');

// the actual improvement of the query
function __gg_user_search($wp_user_query) {
	$s = isset($_GET["s"]) ? $_GET["s"] : "";
	if (empty($s)) {
	    $s = $wp_user_query->query_vars["search"];
	}
	
    if(false === strpos($wp_user_query->query_where, '@') && !empty($s)) {
        global $wpdb;
        
        $ids = array();
		$searches = esc_sql($s);
		if (false !== strpos($searches, ' ')) {
			$searches = explode(' ', $searches);
		}else{
			$searches = array($searches);
		}
	
		foreach ($searches as $search_value) {
			// First read from meta
	        $read = $wpdb->get_results("SELECT DISTINCT user_id
	        	FROM $wpdb->usermeta
	        	WHERE
	        		(meta_key='first_name'
	        		 OR meta_key='last_name')
	        		AND LOWER(meta_value) LIKE '%" . $search_value . "%'"
	    	);
	        foreach($read as $uobj)
	            if(!in_array($uobj->user_id, $ids)) array_push($ids, $uobj->user_id);
	
			// Then read from users table the nicename, because
			// we replace it.
	        $read = $wpdb->get_results("SELECT DISTINCT ID
	        	FROM $wpdb->users
	        	WHERE LOWER(user_nicename) LIKE '%".$search_value."%'
	        	OR LOWER(user_email) LIKE '%".$search_value."%'"
			);
	        foreach($read as $uobj)
	            if(!in_array($uobj->ID, $ids)) array_push($ids, $uobj->ID);
		}
		
		// Replace the where string
        $idimp = implode(",", $ids);
		if (!empty($idimp))
		{
            $wp_user_query->query_where = str_replace(
        		//"user_nicename LIKE '%" . $s . "%'",
        		//"ID IN(" . $idimp . ")",
        		"WHERE 1=1 AND (",
        		"WHERE 1=1 AND (ID IN(" . $idimp . ") OR ",
        		$wp_user_query->query_where
    		);
		}
    }
    return $wp_user_query;
}

// Benutzer
add_filter("gg_filter", "gg_filter_user");
function gg_filter_user($collection) {
    $collection->add(array(
        "name" =>  "user",
        "priority" => 8,
        "category" => __("Users"),
        "title_col" => "ID",
        "cap" => "list_users",
        "search" => function($term, $opt) {
            // Normal
            $args = array(
    			'search'         => $term,
    			'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ),
    			'fields' => array("ID", "user_login"),
    			'number' => $opt["limit"]
		    );
            
	        $user_query = new WP_User_Query($args);
	        return $user_query->results;
        },
        "title_format" => function($title, $term, $row) {
            $firstName = get_the_author_meta("first_name", $title);
            $lastName = get_the_author_meta("last_name", $title);
            
            if (empty($firstName) || empty($lastName)) {
                return $row["user_login"];
            }else{
                return $lastName . ", " . $firstName . " (" . $row["user_login"] . ")";
            }
        },
        "link" => function($row) {
            return admin_url( 'user-edit.php?user_id=' . $row["ID"] );
        }
    ));
    
    return $collection;
}