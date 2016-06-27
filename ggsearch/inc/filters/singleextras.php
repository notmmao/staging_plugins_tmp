<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Extra registrieren, um Beitrag gleich zu erstellen
add_filter("gg_extra", "gg_extra_plugin_install");
function gg_extra_plugin_install($collection) {
    $collection->add(array(
        "name" => "plugin-install",
        "title" => __("Search Plugin &quot;{0}&quot;...", "ggsearch"),
        "link" => admin_url( 'plugin-install.php?s={0}&tab=search' ),
        "cap" => "install_plugins"
    ));
    
    return $collection;
}

// Extra registrieren, um nach vorhandenen Schlagworten zu suchen
add_filter("gg_extra", "gg_extra_post_tag");
function gg_extra_post_tag($collection) {
    $collection->add(array(
        "name" => "post-tag",
        "title" => __("Show all Posts with tag &quot;{0}&quot;...", "ggsearch"),
        "link" => admin_url( 'edit.php?tag={0}' ),
        "cap" => "edit_posts",
        "condition" => function($term) {
            return term_exists($term, 'post_tag') > 0;
        }
    ));
    
    return $collection;
}

// Extra registrieren, um nach vorhandenen Kategorien zu suchen
add_filter("gg_extra", "gg_extra_category");
function gg_extra_category($collection) {
    $collection->add(array(
        "name" => "category",
        "title" => __("Show all Posts with category &quot;{0}&quot;...", "ggsearch"),
        "link" => admin_url( 'edit.php?category_name={0}' ),
        "cap" => "edit_posts",
        "condition" => function($term) {
            return term_exists($term, 'category') > 0;
        }
    ));
    
    return $collection;
}
?>