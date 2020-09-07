<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Woocommerce_New
{
    
    public function execute()
    {
        $wooCommerceService = GJMAA::getService('woocommerce');
        if (! $wooCommerceService->isEnabled()) {
            return;
        }
        
        $profileModel = GJMAA::getModel('profiles');
        $wooCommerceProfileIds = $profileModel->getWooCommerceProfileIds();
        
        if (empty($wooCommerceProfileIds)) {
            return;
        }
        
        $filters = [
            'WHERE' => sprintf('auction_profile_id IN (%s) AND ((auction_in_woocommerce IS NULL OR auction_in_woocommerce = 0 OR auction_in_woocommerce = 2) AND auction_status = \'ACTIVE\') AND (auction_time > NOW() OR auction_time IS NULL)', implode(',', $wooCommerceProfileIds)),
            'LIMIT' => 10
        ];
        
        $auctionsModel = GJMAA::getModel('auctions');
        $auctions = $auctionsModel->getAllBySearch($filters);
        
        if (empty($auctions)) {
            return;
        }
        
        $auctionsData = $auctionsToSkip = [];
        
        $sourceOfferStatus = GJMAA::getSource('allegro_offerstatus');
        
        foreach ($auctions as $auction) {
            if($auction['auction_status'] !== $sourceOfferStatus::ACTIVE) {
                $auctionsToSkip[] = $auction['auction_id'];
                continue;
            }
            
            if(!isset($auctionsData[$auction['auction_profile_id']])) {
                $auctionsData[$auction['auction_profile_id']] = [];
            }
            $auctionsData[$auction['auction_profile_id']][] = $auction['auction_id'];
        }
        
        if (count($auctionsData) == 0) {
            if(!empty($auctionsToSkip)) {
                $this->saveUpdatedAuctions($auctions, $auctionsToSkip);
            }
            return;
        }
        
        $profileModel->unsetData();
        
        foreach($auctionsData as $profileId => $auctionIds) {
            $profile = $profileModel->load($profileId);
            
            if(!$profile->getId()) {
                continue;
            }
            
            try {
                $restApiImport = GJMAA::getService('import');
                $restApiImport->setProfile($profile);
                $isRestConnected = $restApiImport->connect();
                $webapiImport = null;
                if ($isRestConnected) {
                    $webapiImport = $restApiImport->connectToWebAPI();
                } else {
                    error_log(__('Problem with connection to REST API',GJMAA_TEXT_DOMAIN));
                    continue;
                }
                
                $response = $webapiImport->getItemAuction($auctionIds);
                if ($message = $webapiImport->getError()) {
                    error_log(sprintf(__('Problem with getting details about auctions: %s',GJMAA_TEXT_DOMAIN)), $message);
                    continue;
                }
                
                $items = $response->arrayItemListInfo->item;
                $auctionDetails = is_array($items) ? $items : [
                    $items
                ];
                
                $serviceWooCommerce = GJMAA::getService('woocommerce');
                $serviceWooCommerce->setSettingId($profile->getData('profile_setting_id'));
                $productIds = $serviceWooCommerce->saveProducts($auctionDetails);
            } catch (Exception $e) {
                error_log(sprintf(__('Problem with creating products on profile: %s with error %s',GJMAA_TEXT_DOMAIN), $profileId, $e->getMessage()));
            }
        }
        
        $this->saveUpdatedAuctions($auctions, $auctionsToSkip, $productIds);
    }
    
    public function saveUpdatedAuctions($auctions, $auctionsToSkip = [], $productIds = []) {
        $auctionsModel = GJMAA::getModel('auctions');
        
        foreach ($auctions as $auction) {
            $auctionsModel->unsetData();
            
            if(in_array($auction['auction_id'],$auctionsToSkip)) {
                $auction['auction_in_woocommerce'] = 2;
            } else {
                $auction['auction_in_woocommerce'] = 1;
            }
            
            $auction['auction_woocommerce_id'] = isset($productIds[$auction['auction_id']]) ? $productIds[$auction['auction_id']] : 0;
            
            $auctionsModel->setData($auction);
            $auctionsModel->save();
        }
    }
    
    public static function run()
    {
        (new self())->execute();
    }
}
?>