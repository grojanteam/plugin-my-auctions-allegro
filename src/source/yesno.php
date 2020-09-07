<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Yesno extends GJMAA_Source {
    public function getOptions($param = null) {
		return [
			0 => __('No',GJMAA_TEXT_DOMAIN),
		    1 => __('Yes',GJMAA_TEXT_DOMAIN)
		];
	}
}

?>