<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Seiten
if (post_type_exists("page")) {
    add_filter("gg_filter", "gg_filter_page");
    function gg_filter_page($collection) {
        $collection->add(array(
            "name" =>  "page",
            "priority" => 2,
            "category" => __("Sites"),
            "title_col" => "post_title",
            "cap" => "edit_pages",
            "search" => function($term, $opt) {
                $query = new WP_Query( array(
                    "post_type" => "page",
                    "s" => $term,
                    "post_per_page" => $opt["limit"]
                ) );
                return $query->get_posts();
            },
            "link" => function($row) {
                return admin_url( 'post.php?post=' . $row["ID"] ) . '&action=edit';;
            },
            "output" => function($row) {
                return array(
                        "status" => ($row["post_status"] == "publish") ? "" : GG_Core::getInstance()->_ePostStatus($row["post_status"]),
                        "id" => $row["ID"]
                    );
            }
        ));
        
        return $collection;
    }
    
    /* Demonstration: Ändern eines einzelnen Filters gg_filter_%NAME%
    add_filter("gg_filter_page", function($filter) {
        $filter["limit"] = 7;
        return $filter;
    });
    */
    
    
    // Extra registrieren, um Beitrag gleich zu erstellen
    add_filter("gg_extra", "gg_extra_page");
    function gg_extra_page($collection) {
        $collection->add(array(
            "name" => "page-create",
            "priority" => 2,
            "title" => __("Create Page &quot;{0}&quot;", "ggsearch"),
            "link" => admin_url( 'post-new.php?page-create={0}&post_type=page' ),
            "script" => '$(\'input[type="text"][name="post_title"]\').val(term);',
            "cap" => "edit_posts"
        ));
        
        return $collection;
    }
}



if (post_type_exists("post")) {
    // Beiträge
    add_filter("gg_filter", "gg_filter_post");
    function gg_filter_post($collection) {
        $collection->add(array(
            "name" =>  "post",
            "priority" => 3,
            "category" => __("Posts"),
            "title_col" => "post_title",
            "cap" => "edit_posts",
            "search" => function($term, $opt) {
                $query = new WP_Query( array(
                    "post_type" => "post",
                    "s" => $term,
                    "post_per_page" => $opt["limit"]
                ) );
                return $query->get_posts();
            },
            "link" => function($row) {
                return admin_url( 'post.php?post=' . $row["ID"] ) . '&action=edit';;
            },
            "output" => function($row) {
                return array(
                        "status" => ($row["post_status"] == "publish") ? "" : GG_Core::getInstance()->_ePostStatus($row["post_status"]),
                        "id" => $row["ID"]
                    );
            }
        ));
        
        return $collection;
    }
    // Extra registrieren, um Beitrag gleich zu erstellen
    add_filter("gg_extra", "gg_extra_post");
    function gg_extra_post($collection) {
        $collection->add(array(
            "name" => "post-create",
            "title" => __("Create Post &quot;{0}&quot;", "ggsearch"),
            "priority" => 3,
            "link" => admin_url( 'post-new.php?post-create={0}' ),
            "script" => '$(\'input[type="text"][name="post_title"]\').val(term);',
            "cap" => "edit_posts"
        ));
        
        return $collection;
    }
}