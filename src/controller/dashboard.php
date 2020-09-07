<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Controller_Dashboard extends GJMAA_Controller {
	
	protected $template = 'dashboard.phtml';
	
	public function getName(){
		return 'Dashboard';
	}
	
	public function getMenuName(){
		return 'Dashboard';
	}
	
	public function getSlug(){
		return 'gjmaa_dashboard';
	}
}
?>