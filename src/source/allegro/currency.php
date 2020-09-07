<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Currency extends GJMAA_Source 
{
    
    const ALLEGRO_PL_SITE = 1;
    const AUKRO_CZ_SITE = 56;
    
    public function getOptions($param = null) 
	{
		return [
			self::ALLEGRO_PL_SITE => 'zł',
			self::AUKRO_CZ_SITE => 'Kč'
		];
	}
}

?>