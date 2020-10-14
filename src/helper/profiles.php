<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Profiles
{

    public function getFieldsData($type = 'form')
    {
        $form = [
            'profile_id' => [
                'type' => 'hidden',
                'name' => 'profile_id'
            ],
            'profile_setting_id' => [
                'id' => 'setting_id',
                'type' => 'select',
                'name' => 'profile_setting_id',
                'label' => 'API Settings',
                'source' => 'settings',
                'required' => true,
                'help' => __('Choose API settings that you will used to import',GJMAA_TEXT_DOMAIN)
            ],
            'profile_name' => [
                'type' => 'text',
                'name' => 'profile_name',
                'label' => 'Name',
                'required' => true,
                'help' => __('Type here your custom profile name',GJMAA_TEXT_DOMAIN)
            ],
            'profile_type' => [
                'id' => 'profile_type',
                'type' => 'select',
                'name' => 'profile_type',
                'label' => 'Type of auctions',
                'source' => 'allegro_type',
                'required' => true,
                'help' => __('Choose type of auction that will be imported',GJMAA_TEXT_DOMAIN)
            ],
            'profile_auctions' => [
                'type' => 'number',
                'name' => 'profile_auctions',
                'label' => 'Count of auctions',
                'required' => true,
                'help' => __('Type number of auctions',GJMAA_TEXT_DOMAIN)
            ],
            'profile_category' => [
                'id' => 'category',
                'type' => 'select',
                'name' => 'profile_category',
                'label' => 'Category',
                'source' => $this->getSourceByType($type),
                'help' => __('Choose allegro category that will be filtered during import',GJMAA_TEXT_DOMAIN)
            ],
            'profile_category_hidden' => [
                'id' => 'category_hidden',
                'type' => 'hidden',
                'name' => 'profile_category_hidden'
            ],
            'profile_sort' => [
                'type' => 'select',
                'name' => 'profile_sort',
                'label' => 'Sort',
                'source' => 'allegro_sort',
                'help' => __('Choose type of sorting that will be imported',GJMAA_TEXT_DOMAIN)
            ],
            'profile_user' => [
                'id' => 'profile_user',
                'type' => 'text',
                'name' => 'profile_user',
                'label' => 'Seller ID',
                'help' => __('<strong>IMPORTANT!</strong> This is temporary change...', GJMAA_TEXT_DOMAIN) . '<br />'
                    . __('If you want to import your auctions, avoid to use type "Search" or "Auctions of user", use instead "My auctions"',GJMAA_TEXT_DOMAIN) . '<br />'
                    . __('If you really need to use different type than "My auctions", type here Seller ID (numeric value)', GJMAA_TEXT_DOMAIN) . '<br />' 
                    . sprintf(__('Go to <a href="%s" target="_blank">allegro.pl</a> in search box type login, on the right side choose "Users" and click "Search"', GJMAA_TEXT_DOMAIN), 'https://allegro.pl') . '<br />'
                    . __('Go to any offer, and click "Question to the seller" in section "Check", in address bar you can find "userId=...&", just copy this number and paste here', GJMAA_TEXT_DOMAIN),                    
                'disabled' => true
            ],
            'profile_search_query' => [
                'id' => 'profile_search_query',
                'type' => 'text',
                'name' => 'profile_search_query',
                'label' => 'Query',
                'disabled' => true,
                'help' => __('Type here query that you want to find',GJMAA_TEXT_DOMAIN)
            ],
            'profile_last_sync' => [
                'type' => 'text',
                'name' => 'profile_last_sync',
                'label' => 'Last synchronization',
                'disabled' => true,
                'help' => __('Time of last synchronization',GJMAA_TEXT_DOMAIN)
            ]
        ];

        if(GJMAA::getService('woocommerce')->isEnabled()) {
            $form += [
            	'profile_sync_price' => [
	                'type' => 'select',
	                'name' => 'profile_sync_price',
	                'label' => 'Update prices?',
	                'source' => 'yesno',
	                'help' => __('Choose that you want to update auction prices to WooCommerce Product',GJMAA_TEXT_DOMAIN),
	                'value' => 0
	            ],
	            'profile_to_woocommerce' => [
		            'id' => 'profile_to_woocommerce',
	                'type' => 'select',
	                'name' => 'profile_to_woocommerce',
	                'label' => 'WooCommerce?',
	                'source' => 'yesno',
	                'help' => __('Choose that you want import auctions from allegro to WooCommerce',GJMAA_TEXT_DOMAIN)
	            ],
	            'profile_save_woocommerce_category_level' => [
		            'type' => 'text',
		            'name' => 'profile_save_woocommerce_category_level',
		            'label' => __('WooCommerce Category Level (0 - 3)'),
		            'help' => __('Choose from which level category should be saved for product',GJMAA_TEXT_DOMAIN)
	            ]
	        ];
        }

	    $form += [
	        'profile_cron_sync' => [
		        'type' => 'select',
		        'name' => 'profile_cron_sync',
		        'label' => 'Sync with CRON',
		        'source' => 'yesno',
		        'help' => __('Choose that you want import auctions from allegro with CRON',GJMAA_TEXT_DOMAIN)
	        ],
	        'profile_clear_auctions' => [
		        'type' => 'select',
		        'name' => 'profile_clear_auctions',
		        'label' => 'Clear auctions',
		        'source' => 'yesno',
		        'help' => __('Clear auctions during every import?')
	        ],
	        'save' => [
		        'type' => 'submit',
		        'name' => 'save',
		        'label' => 'Save'
	        ]
        ];

        return $form;
    }

    public function getTotalProfiles()
    {
        return GJMAA::getModel('profiles')->getCountAll();
    }
    
    public function getSourceByType($type)
    {
        switch ($type) {
            case 'table':
                return 'allegro_category_tree';
            default:
                return 'allegro_category';
        }
    }
}
?>