<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class GG_Filter {
    
    private $filters = array();
    
    public function add($filter) {
        if (empty($filter["name"])
            || $this->isAlreadyDefined($filter["name"])) {
            return false;
        }
        
        $filters = $this->getFilters();
        $new = array_merge(array(
                "name" => false,
                "priority" => 5,
                "category" => __("Unknown", "ggsearch"),
                "title_col" => "",
                "search" => false,
                "limit" => GG_Core::getInstance()->getCfgGeneral()->maxlimit,
                "cap" => "read",
                "rows" => array(),
                "desc" => __("No Description.", "ggsearch"),
                "link" => "",
                "title_format" => false,
                "output" => false
            ),
            $filter);
        
        // Nachträgliches ändern dieses Filters
        $new = apply_filters( 'gg_filter_' . $filter["name"], $new );
        $new["name"] = $filter["name"]; // Name darf nicht verändert werden
        
        // Priorität, wenn geändert in den Gruppen-Einstellungen, ändern
        $prioChanged = $this->readPriorityFromConfig($new["name"]);
        if ($prioChanged !== null) {
            $new["priority"] = $prioChanged;
        }
        
        // Speichern
        $filters[] = $new;
        $this->setFilters($filters);
        
        return true;
    }
    
    private function readPriorityFromConfig($name) {
        $prio = GG_Core::getInstance()->getCfgGroups()->prioritys;
        
        if (is_array($prio)) {
            if (isset($prio[$name]) && is_numeric($prio[$name])) {
                return (int) $prio[$name];
            }
        }
        
        return null;
    }
    
    public function isAlreadyDefined($name) {
        foreach ($this->getFilters() as $key => $value) {
            if ($value["name"] == $name) {
                return true;
            }
        }
        return false;
    }
    
    public function sort() {
        $filters = $this->getFilters();
        usort($filters, function($a, $b) {
            return ($a["priority"] <= $b["priority"]) ? -1 : 1;
        });
        $this->setFilters($filters);
    }
    
    public function collect($term) {
        $filters = array();
        $results = false;
        
        foreach ($this->getFilters() as $key => $value) {
            // Prüfe, ob diese gesammelt werden darf (aus Config)
            if (in_array($value["name"], GG_Core::getInstance()->getCfgGroups()->hidden)) {
                continue;
            }
            
            $results = self::search($value, $term);
            if (is_array($results)) {
                $filters[] = $results;
            }
        }
        
        $this->setFilters($filters);
    }
    
    public function output($term = "") {
        $result = array();
        
        foreach ($this->getFilters() as $key => $value) {
            $i = count($result);
            $result[$i] = array(
                "name" => $value["name"],
                "category" => $value["category"],
                "rows" => array()
            );
            
            // Resultate laden
            foreach ($value["rows"] as $row) {
                $next = array(
                    "title" => $row[$value["title_col"]],
                    "link" => call_user_func($value["link"], $row)
                );
                
                if (is_callable($value["title_format"])) {
                    $next["title"] = call_user_func($value["title_format"], $next["title"], $term, $row);
                }
                
                if (is_callable($value["output"])) {
                    $next["output"] = call_user_func($value["output"], $row);
                }
                
                $result[$i]["rows"][] = $next;
            }
        }
        return $result;
    }
    
    public static function object_to_array($data) {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = self::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
    
    public static function isHidden($name) {
        foreach (GG_Core::getInstance()->getCfgGroups()->hidden as $value) {
            if ($value == $name) {
                return true;
            }
        } 
        
        return false;
    }
    
    public static function search($filter, $term) {
        // Prüfe, ob dieser Benutzer die Berechtigung hat
        if (!current_user_can($filter["cap"]) || !is_callable($filter["search"])) {
            return false;
        }
        
        // Suchen
        $results = call_user_func($filter["search"], $term, $filter);
        $cnt = count($results);
        $filter["category"] .= " (" . $cnt . ")";
        if (!is_array($results) || $cnt == 0) {
            return false;
        }
        
        // Zuschneiden
        if (is_numeric($filter["limit"]) && $cnt > $filter["limit"]) {
            $results = array_slice($results, 0, $filter["limit"]);
        }
        
        $results = self::object_to_array($results);
        
        $filter["rows"] = array_merge($filter["rows"], $results);
        
        return $filter;
    }
    
    public function getFilters() {
        return $this->filters == null ? array() : $this->filters;
    }
    
    private function setFilters($f) {
        $this->filters = $f;
    }
    
}