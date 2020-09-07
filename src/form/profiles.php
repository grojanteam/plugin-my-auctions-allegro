<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Profiles extends GJMAA_Form {
	public function prepareForm()
	{
		parent::prepareForm();
		
		$helper = GJMAA::getHelper('profiles');
		
		$this->setFields(
			$helper->getFieldsData()
		);
		
		return $this;
	}
	
	public function getAction()
	{
		return admin_url('admin.php?page=gjmaa_profiles&action=save');
	}
}
?>