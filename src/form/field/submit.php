<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Field_Submit extends GJMAA_Form_Field
{
	protected $type = 'submit';
	
	public function getLabel()
	{
		return '';
	}
	
	public function getInput()
	{
		return '<button id="'.$this->getInfo('id').'"  type="'.$this->getInfo('type').'">'.__($this->getInfo('label'),GJMAA_TEXT_DOMAIN).'</button>';
	}
}