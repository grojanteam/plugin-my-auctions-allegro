<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Import extends GJMAA_Form {
	public function prepareForm()
	{
		parent::prepareForm();
		
		$this->setFields(GJMAA::getHelper('import')->getFieldsData());
		
		return $this;
	}
	
	public function getAction()
	{
		return admin_url('admin.php?page=gjmaa_import&action=import');
	}
}