<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Settings extends GJMAA_Form {
	public function prepareForm()
	{
		parent::prepareForm();
		
		$fields = GJMAA::getHelper('settings');
		
		$this->setFields(
			$fields->getFieldsData()
		);
		
		return $this;
	}
	
	public function getAction()
	{
		return admin_url('admin.php?page=gjmaa_settings&action=save');
	}
}
?>