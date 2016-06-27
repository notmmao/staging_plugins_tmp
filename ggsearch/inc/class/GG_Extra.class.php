<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class GG_Extra {
    
    private $extras = array();
    
    public function add($extra) {
        if (empty($extra["name"])
            || $this->isAlreadyDefined($extra["name"])) {
            return false;
        }
        
        $extras = $this->getExtras();
        $new = array_merge(array(
                "name" => "",
                "title" => __("Unknown", "ggsearch"),
                "priority" => 5,
                "link" => "",
                "script" => "",
                "cap" => "read_posts",
                "desc" => __("No Description.", "ggsearch"),
                "condition" => false
            ),
            $extra);
            
        // Nachträgliches ändern dieses Filters
        $new = apply_filters( 'gg_extra_' . $extra["name"], $new );
        $new["name"] = $extra["name"]; // Name darf nicht verändert werden
        
        // Priorität, wenn geändert in den Gruppen-Einstellungen, ändern
        $prioChanged = $this->readPriorityFromConfig($new["name"]);
        if ($prioChanged !== null) {
            $new["priority"] = $prioChanged;
        }
        
        // Speichern
        $extras[] = $new;
        $this->setExtras($extras);
        
        return true;
    }
    
    private function readPriorityFromConfig($name) {
        $prio = GG_Core::getInstance()->getCfgExtras()->prioritys;
        
        if (is_array($prio)) {
            if (isset($prio[$name]) && is_numeric($prio[$name])) {
                return (int) $prio[$name];
            }
        }
        
        return null;
    }
    
    public function sort() {
        $extras = $this->getExtras();
        usort($extras, function($a, $b) {
            return ($a["priority"] <= $b["priority"]) ? -1 : 1;
        });
        $this->setExtras($extras);
    }
    
    public function isAlreadyDefined($name) {
        foreach ($this->getExtras() as $key => $value) {
            if ($value["name"] == $name) {
                return true;
            }
        }
        return false;
    }
    
    public static function output($extra) {
        // Prüfe, ob diese gesammelt werden darf (aus Config)
        if (in_array($extra["name"], GG_Core::getInstance()->getCfgExtras()->hidden)) {
            return false;
        }
        
        echo '<a class="gg-item ' . ((is_callable($extra["condition"]) !== false) ? "gg-condition" : "") . ' gg-extra gg-extra-' . $extra["name"] . '"
                data-filter="' . $extra["name"] . '"
                data-title="' . $extra["title"] . '"
                data-link="' . $extra["link"] . '"
                data-name="extra"
                href="#"></a>';
        return true;
    }
    
    public static function isHidden($name) {
        foreach (GG_Core::getInstance()->getCfgExtras()->hidden as $value) {
            if ($value == $name) {
                return true;
            }
        } 
        
        return false;
    }
    
    public function hasExtras() {
        return count($this->extras) > 0;
    }
    
    public function getExtras() {
        return $this->extras == null ? array() : $this->extras;
    }
    
    private function setExtras($e) {
        $this->extras = $e;
    }
}