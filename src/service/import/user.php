<?php
require_once __DIR__ . '/../import.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Import_User extends GJMAA_Service_Import
{
    
    protected $type = 'user';

    public function makeRequest()
    {
        if($this->getProfileStep() != 2){
            if (! $this->client) {            
                $api = GJMAA::getLib('rest_api_listing_search');
                
                $user = $this->getProfile()->getData('profile_user');
                if($this->getProfile()->getData('profile_type') == 'my_auctions'){
                    $user = $this->getSettings()->getData('setting_login');
                }
    
                $sellerId = $this->parseUserToSellerId($user);
                if(!$sellerId){
                    throw new Exception(
                        __('Wrong seller name or problem with API!', GJMAA_TEXT_DOMAIN)
                    );
                }
    
                $api->setSeller($sellerId);
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