<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Oosdecision extends GJMAA_Source {
    
    const DONT_CHANGE_ANYTHING = 0;
    const UPDATE_TO_OUT_OF_STOCK = 1;
    const REMOVE_PRODUCT_FROM_WOOCOMMERCE = 2;
    
    public function getOptions($param = null) {
		return [
		    self::DONT_CHANGE_ANYTHING => __('Don\'t change anything', GJMAA_TEXT_DOMAIN),
		    self::UPDATE_TO_OUT_OF_STOCK => __('Update to out of stock', GJMAA_TEXT_DOMAIN),
		    self::REMOVE_PRODUCT_FROM_WOOCOMMERCE => __('Remove product from WooCommerce',GJMAA_TEXT_DOMAIN)
		];
	}
}

?>