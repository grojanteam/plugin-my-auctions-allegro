<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Templates extends GJMAA_Source {
    public function getOptions($param = null){
        $files = scandir(GJMAA_PATH.'/views/front/widgets/');
        $templates = [];
        foreach($files as $file) {
            if(!in_array($file,['.','..'])){
                $label = str_replace('.phtml','',$file);
                $templates[$file] = $label;
            }
        }
        return $templates;
    }
}
?>