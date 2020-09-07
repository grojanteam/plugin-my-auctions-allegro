<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

/**
 * display tables related to allegro profiles
 * @author grojanteam
 */
class GJMAA_Table_Profiles extends GJMAA_Table {
	protected $page = 'gjmaa_profiles';
	
	protected $singular = 'profile';
	
	protected $object = 'profiles';
	
	protected $actions = [
	    'action=edit&{model_entity_id}={model_entity_value_id}' => 'Edit',
	    'action=delete&{model_entity_id}={model_entity_value_id}' => 'Delete',
	    'action=clear&{model_entity_id}={model_entity_value_id}' => 'Clear auctions'
	];
	
	public function get_columns() {
	    $columns = parent::get_columns();
	    
		$columns += [
				'profile_id' => __('ID',GJMAA_TEXT_DOMAIN),
				'profile_setting_id' => __('API',GJMAA_TEXT_DOMAIN),
				'profile_name' => __('Name',GJMAA_TEXT_DOMAIN),
				'profile_type' => __('Type',GJMAA_TEXT_DOMAIN),
				'profile_category' => __('Category', GJMAA_TEXT_DOMAIN),
				'profile_sort' => __('Sort',GJMAA_TEXT_DOMAIN),
				'profile_user' => __('User',GJMAA_TEXT_DOMAIN),
				'profile_search_query' => __('Query',GJMAA_TEXT_DOMAIN),
		        'profile_last_sync' => __('Last synchronization',GJMAA_TEXT_DOMAIN),
				'action' => __('Action',GJMAA_TEXT_DOMAIN)
		];
		
		return $columns;
	}
	
	public function get_hidden_columns(){
		return [];
	}
	
	public function get_sortable_columns() {
		return [];
	}
	
	public function showSearch(){
		return true;
	}
	
	public function getFilters(){
	    return [
	        'profile_setting_id' => [
	            'id' => 'profile_setting_id',
	            'name' => __('API',GJMAA_TEXT_DOMAIN),
	            'source' => 'settings'
	        ],
	        'profile_type' => [
	            'id' => 'profile_type',
	            'name' => __('Type',GJMAA_TEXT_DOMAIN),
	            'source' => 'allegro_type'
	        ]
	    ];
	}
}

?>