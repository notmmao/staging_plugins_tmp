<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_filter("gg_filter", "gg_filter_custom_types");
function gg_filter_custom_types($collection) {
    $types = get_post_types();
    
    foreach ($types as $key => $value) {
        $obj = get_post_type_object($key);
        
        if (in_array($obj->name, array("post", "page"))) {
            continue;
        }
        
        $collection->add(array(
            "name" =>  'cpt_' . $obj->name,
            "priority" => 5,
            "category" => $obj->label,
            "title_col" => "post_title",
            "cap" => "edit_posts",
            "invoke" => array(
                "query_var" => $obj->query_var
            ),
            "search" => function($term, $opt) {
                $query = new WP_Query( array(
                    "post_type" => (empty($opt["invoke"]["query_var"]) ? $opt["name"] : $opt["invoke"]["query_var"]),
                    "s" => $term,
                    "posts_per_page" => $opt["limit"]
                ) );
                return $query->get_posts();
            },
            "link" => function($row) {
                return admin_url( 'post.php?post=' . $row["ID"] ) . '&action=edit';;
            },
            "output" => function($row) {
                return ($row["post_status"] == "publish") ? "" : GG_Core::getInstance()->_ePostStatus($row["post_status"]);
            }
        ));
    }
    
    return $collection;
}

?>