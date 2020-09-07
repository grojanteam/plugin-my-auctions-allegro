<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Field_Multiselect extends GJMAA_Form_Field {

	protected $type = 'multiselect';

	public function getInput()
	{
		$input = '<select id="'.$this->getInfo('id').'" name="'.$this->getInfo('name').'" multiple size="'.$this->getInfo('size').'">';
		$options = $this->getInfo('options');
		$values = explode(',',$this->getInfo('value') ? : "");
		foreach($options as $value => $label)
		{
		    $selected = in_array($value,$values) ? ' selected="selected"' : '';
			$input .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
		}
		$input .= '</select>';
		return $input;
	}
}