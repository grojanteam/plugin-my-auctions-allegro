<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Woocommerce_Clearmedia 
{
    
    public function execute() 
    {
        $wooCommerceService = GJMAA::getService('woocommerce');
        if(!$wooCommerceService->isEnabled()) {
            return;
        }
        
        $wooCommerceService->removeAllNotAssignedMedia();
        $wooCommerceService->productsToRemove();
        $wooCommerceService->removeAllMediaThatProductNotExist();
    }
    
    public static function run()
    {
        (new self())->execute();
    }
}

?>