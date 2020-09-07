<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Offerstatus extends GJMAA_Source {
    
    const INACTIVE = 'INACTIVE';
    const ACTIVATING = 'ACTIVATING';
    const ACTIVE = 'ACTIVE';
    const ENDED = 'ENDED';
    
    public function getOptions($param = null){
        return [
            self::INACTIVE => __('Inactive',GJMAA_TEXT_DOMAIN),
            self::ACTIVATING => __('Activating',GJMAA_TEXT_DOMAIN),
            self::ACTIVE => __('Active',GJMAA_TEXT_DOMAIN),
            self::ENDED => __('Ended',GJMAA_TEXT_DOMAIN),
        ];
    }
}
?>