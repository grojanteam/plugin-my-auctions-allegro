<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Form_Field {

	protected $type = 'text';
	
	protected $label = '';
	
	protected $input = '';
	
	protected $info;
	
	protected $excludedHelpType = ['hidden','button','submit'];
	
	public function toHtml()
	{
	    $help = !in_array($this->type,$this->excludedHelpType) && $this->getInfo('help') ? $this->getInfo('help') : '';
	    return $this->getLabel() .'</th><td>'. $this->getInput() .($help ? '<br><span class="description">'.$help.'</span>' : '');
	}
	
	public function getLabel()
	{
		return '<label for="'.$this->getInfo('name').'">' . __($this->getInfo('label'),GJMAA_TEXT_DOMAIN) . '</label>';
	}
	
	public function getInput()
	{
	    $id = $this->getInfo('id') ? ' id="'.$this->getInfo('id').'"' : '';
	    $type = ' type="' . $this->getInfo('type') . '"';
	    $name = ' name="' . $this->getInfo('name') . '"';
	    $value = ' value="' . $this->getInfo('value') . '"';
	    $disabled = $this->getInfo('disabled') ? ' disabled="true"' : '';
	    $class = $this->getInfo('class') ? ' class="' . $this->getInfo('class') . '"' : '';
	    $required = $this->getInfo('required') ? ' required="true"' : '';
	    
		return "<input{$id}{$type}{$name}{$value}{$disabled}{$class}{$required} />";
	}
	
	public function setInfo($info)
	{
		$this->info = $info;
	}
	
	public function getInfo($key = null)
	{
		return $key ? (isset($this->info[$key]) ? $this->info[$key] : '') : $this->info;
	}
}