<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class GG_Config {
    
    private $absolute;
    
    public function __construct($fileName, $pre = array(), $forceWrite = false) {
        
        $fileName = "gg_" . $fileName;
        $this->absolute = $fileName;
        add_option($fileName, json_encode($pre));
        
        if ($forceWrite) {
            update_option($fileName, json_encode($pre));
        }
        
        /*
        $this->absolute = GGSEARCH_PATH . "/config/" . $fileName;
        
        if (!file_exists($this->absolute) || $forceWrite) {
            file_put_contents($this->absolute, json_encode($pre));
        }
        */
        
        $this->read();
    }
    
    private function read() {
        /*
        $content = file_get_contents($this->absolute);
        */
        $content = get_option($this->absolute);
        $content = json_decode($content, true);
        
        foreach ($content as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public function save() {
        $json = json_encode(GG_Core::get_object_vars_from_public($this));
        update_option($this->absolute, $json);
        
        //file_put_contents($this->absolute, $json);
    }
    
}
?>