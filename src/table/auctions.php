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
class GJMAA_Table_Auctions extends GJMAA_Table
{

    protected $page = 'gjmaa_auctions';
    
    protected $singular = 'auction';

    protected $object = 'auctions';

    protected $actions = [
        'action=showOnAllegro&{model_entity_id}={model_entity_value_id}' => 'Show on Allegro',
//         'action=exportToWooCommerce&{model_entity_id}={model_entity_value_id}' => 'To Woocommerce'
    ];

    public function get_columns()
    {
        $columns = parent::get_columns();
        
        $columns += [
            'id' => __('ID',GJMAA_TEXT_DOMAIN),
            'auction_id' => __('Auction ID', GJMAA_TEXT_DOMAIN),
            'auction_profile_id' => __('Profile', GJMAA_TEXT_DOMAIN),
            'auction_name' => __('Name', GJMAA_TEXT_DOMAIN),
            'auction_price' => __('Price (Buy Now)', GJMAA_TEXT_DOMAIN),
            'auction_bid_price' => __('Price (Bid)', GJMAA_TEXT_DOMAIN),
            'auction_quantity' => __('Quantity', GJMAA_TEXT_DOMAIN),
            'auction_categories' => __('Category', GJMAA_TEXT_DOMAIN),
            'auction_seller' => __('Seller', GJMAA_TEXT_DOMAIN),
            'auction_time' => __('Time', GJMAA_TEXT_DOMAIN),
            'auction_clicks' => __('Clicks', GJMAA_TEXT_DOMAIN),
            'auction_visits' => __('Visits', GJMAA_TEXT_DOMAIN),
            'auction_status' => __('Status', GJMAA_TEXT_DOMAIN),
            'auction_in_woocommerce' => __('In woocommerce?', GJMAA_TEXT_DOMAIN),
            'action' => __('Action', GJMAA_TEXT_DOMAIN)
        ];
        
        return $columns;
    }

    public function get_hidden_columns()
    {
        return ['id'];
    }

    public function get_sortable_columns()
    {
        return [
            'auction_id' => [
                'auction_id',
                false
            ],
            'auction_name' => [
                'auction_name',
                false
            ],
            'auction_profile_id' => [
                'auction_profile_id',
                false
            ],
            'auction_price' => [
                'auction_price',
                false
            ],
            'auction_bid_price' => [
                'auction_bid_price',
                false
            ],
            'auction_quantity' => [
                'auction_quantity',
                false
            ],
            'auction_time' => [
                'auction_time',
                false
            ],
            'auction_clicks' => [
                'auction_clicks',
                false
            ],
            'auction_status' => [
                'auction_status',
                true
            ],
            'auction_visits' => [
                'auction_visits',
                false
            ],
            'auction_in_woocommerce' => [
                'auction_in_woocommerce',
                true
            ],
            'auction_sort_order' => [
                'auction_sort_order',
                true
            ]
        ];
    }

    public function showSearch()
    {
        return true;
    }
    
    public function getFilters(){
        return [
            'auction_profile_id' => [
                'id' => 'auction_profile_id',
                'name' => __('Profile',GJMAA_TEXT_DOMAIN),
                'source' => 'profiles'
            ],
            'auction_status' => [
                'id' => 'auction_status',
                'name' => __('Status',GJMAA_TEXT_DOMAIN),
                'source' => 'allegro_offerstatus'
            ],
            'auction_in_woocommerce' => [
                'id' => 'auction_in_woocommerce',
                'name' => __('In WooCommerce?'),
                'source' => 'yesnoskip'
            ]
        ];
    }
}

?>