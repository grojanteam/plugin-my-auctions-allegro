<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Sync extends GJMAA_Source 
{
    public function getOptions($param = null) 
	{
		return [
			1 => 'Every hour',
			24 => 'Every day',
			720 => 'Every month'
		];
	}
}

?>