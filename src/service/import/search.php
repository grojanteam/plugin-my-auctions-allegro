<?php
require_once __DIR__ . '/../import.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Import_Search extends GJMAA_Service_Import
{

    protected $type = 'search';

    public function makeRequest()
    {
        if($this->getProfileStep() != 2){
            if (! $this->client) {
                $api = GJMAA::getLib('rest_api_listing_search');
                $api->setQuery($this->getProfile()
                    ->getData('profile_search_query'));
    
                if ($user = $this->getProfile()->getData('profile_user')) {
                    $sellerId = $this->parseUserToSellerId($user);
    
                    $api->setSeller($sellerId);
                }
    
                if ($category = $this->getProfile()->getData('profile_category')) {
                    $api->setCategory($category);
                }
    
                $this->client = $api;
            }

            return parent::makeRequest();
        }
        
        return $this->getAuctionDetails();
    }
}

?>