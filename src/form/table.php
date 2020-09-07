<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Table extends GJMAA_Form {
	public function prepareForm() {
		parent::prepareForm();
		
		$this->addField('page',[
			'type' => 'hidden',
			'name' => 'page',
			'value' => $this->getPageName()
		]);
	}
}
?>