<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GG_Extra_Table extends WP_List_Table {
    function get_columns(){
        $columns = array(
        'cb'   => '<input type="checkbox" />',
        'name' => __('Title', 'ggsearch'),
        'uid'  => 'ID',
        'link'=> __('Link', 'ggsearch'),
        'priority' => __('Priority', 'ggsearch'),
        'desc' => __('Description', 'ggsearch'),
        'perm' => __('Permission', 'ggsearch')
        );
        return $columns;
    }
    
    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
      
        $collection = new GG_Extra();
        $collection = apply_filters( 'gg_extra', $collection );
        $collection->sort();
        
        $this->items = array();
        foreach ($collection->getExtras() as $value) {
            $this->items[] = array(
                "name" => $value["title"],
                "uid" => $value["name"],
                "link" => $value["link"],
                "priority" => $value["priority"],
                "desc" => $value["desc"] . 
                    ((!empty($value["script"])) ?
                    "<br /><a href=\"#\" onclick=\"jQuery(this).next().next().slideToggle();\">:: " . __('Show Javascript', 'ggsearch') . "</a><br /><code style=\"display:none;\">" . $value["script"] . "</code>"
                    : "") .
                    ((is_callable($value["condition"])) ?
                    "<br />:: " . __('Contains Conditions', 'ggsearch')
                    : ""),
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
            '<input type="checkbox" name="active[]" value="%s" ' . ((!GG_Extra::isHidden($item["uid"])) ? 'checked="checked"' : '') . ' />', $item['uid']
        );
    }
    
    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'link':
            case 'name':
                return str_replace("{0}", "<code>{0}</code>", $item[$column_name]);
            case 'uid':
            case 'perm':
            case 'desc':
              return $item[ $column_name ];
            default:
              return "";
        }
    }
}
?>