<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GG_List_Table extends WP_List_Table {
    function get_columns(){
        $columns = array(
        'cb'   => '<input type="checkbox" />',
        'name' => __("Name", "ggsearch"),
        'uid'  => 'ID',
        'limit'=> __("Limit", "ggsearch"),
        'priority' => __("Priority", "ggsearch"),
        'desc' => __("Description", "ggsearch"),
        'perm' => __("Permission", "ggsearch")
        );
        return $columns;
    }
    
    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
      
        $collection = new GG_Filter();
        $collection = apply_filters( 'gg_filter', $collection );
        $collection->sort();
        
        $this->items = array();
        foreach ($collection->getFilters() as $value) {
            $this->items[] = array(
                "name" => $value["category"],
                "uid" => $value["name"],
                "limit" => $value["limit"],
                "priority" => $value["priority"],
                "desc" => $value["desc"],
                "perm" => $value["cap"]
                );
        }
    }
    
    public function getItems() {
        return $this->items;
    }
    
    function column_priority($item) {
        return sprintf(
        '<input type="text" size="3" name="priority[%s]" value="%s" />',
             $item["uid"],
             $item['priority'] 
          );
    }
    
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="active[]" value="%s" ' . ((!GG_Filter::isHidden($item["uid"])) ? 'checked="checked"' : '') . ' />', $item['uid']
        );
    }
    
    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'name':
            case 'uid':
            case 'limit':
            case 'desc':
            case 'perm':
              return $item[ $column_name ];
            default:
              return "";
        }
    }
}
?>