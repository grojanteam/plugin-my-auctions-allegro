<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

abstract class GJMAA_Source {
	abstract public function getOptions($param = null);
	
	public function getAllOptions($addEmpty = true) {		
		$result = [];
		if($addEmpty){
			$result[''] = '';
		}
		$result += $this->getOptions();
		
		return $result;
	}
}
?>