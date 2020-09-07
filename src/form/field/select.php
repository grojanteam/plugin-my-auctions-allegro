<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Field_Select extends GJMAA_Form_Field {

	protected $type = 'select';

	public function getInput()
	{
	    $id = $this->getInfo('id') ? ' id="'.$this->getInfo('id').'"' : '';
	    $name = ' name="' . $this->getInfo('name') . '"';
	    $disabled = $this->getInfo('disabled') ? ' disabled="true"' : '';
	    $class = $this->getInfo('class') ? ' class="' . $this->getInfo('class') . '"' : '';
	    $required = $this->getInfo('required') ? ' required="true"' : '';
	    
	    $options = $this->getInfo('options');
	    $source = $this->getInfo('source');
	    $values = $this->getInfo('value');
	    
	    if(empty($options) && !empty($source)){
	        $options = GJMAA::getSource($source)->getAllOptions();
	    }
	    
	    
	    $input = "<select{$id}{$name}{$class}{$disabled}{$required}>";
		foreach($options as $value => $label)
		{
            $selected = (is_numeric($value) ? (int)$value : $value) === (is_numeric($values) ? (int)$values : $values)  ? ' selected="selected"' : '';
			$input .= '<option value="'.$value.'"'.$selected.'>'.__($label,GJMAA_TEXT_DOMAIN).'</option>';
		}
		$input .= '</select>';
		return $input;
	}
}