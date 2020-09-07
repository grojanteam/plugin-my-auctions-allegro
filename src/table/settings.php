<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

/**
 * display tables related to plugin settings
 * @author grojanteam
 */
class GJMAA_Table_Settings extends GJMAA_Table {
	
	protected $page = 'gjmaa_settings';
	
	protected $singular = 'setting';
	
	protected $object = 'settings';
	
	protected $actions = [
	    'action=edit&{model_entity_id}={model_entity_value_id}' => 'Edit',
	    'action=delete&{model_entity_id}={model_entity_value_id}' => 'Delete'
	];
	
	public function get_columns() {
	    $columns = parent::get_columns();
	    
		$columns += [
				'setting_id' => __('ID',GJMAA_TEXT_DOMAIN),
				'setting_name' => __('Name',GJMAA_TEXT_DOMAIN),
		        'setting_site' => __('Site',GJMAA_TEXT_DOMAIN),
				'setting_login' => __('Login Allegro',GJMAA_TEXT_DOMAIN),
				'setting_is_sandbox' => __('Sandbox', GJMAA_TEXT_DOMAIN),
				'action' => __('Action',GJMAA_TEXT_DOMAIN)
		];
		
		return $columns;
	}
	
	public function get_hidden_columns(){
		return ['setting_id'];
	}
	
	public function get_sortable_columns() {
		return [];
	}
	
	public function showSearch(){
		return true;
	}
	
	public function getFilters()
	{
	    return [
	        'setting_site' => [
	            'id' => 'setting_site',
	            'name' => __('Site',GJMAA_TEXT_DOMAIN),
	            'source' => 'allegro_site'
	        ],
	        'setting_is_sandbox' => [
	            'id' => 'setting_is_sandbox',
	            'name' => __('Sandbox',GJMAA_TEXT_DOMAIN),
	            'source' => 'yesno'
	        ]
	    ];
	}
}

?>