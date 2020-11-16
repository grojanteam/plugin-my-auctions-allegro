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
    	/** @var GJMAA_Service_Woocommerce $wooCommerceService */
        $wooCommerceService = GJMAA::getService('woocommerce');
        if (! $wooCommerceService->isEnabled()) {
            return;
        }

        /** @var GJMAA_Model_Profiles $profileModel */
        $profileModel = GJMAA::getModel('profiles');
        $wooCommerceProfileIds = $profileModel->getWooCommerceProfileIds();
        
        if (empty($wooCommerceProfileIds)) {
            return;
        }
        
        $filters = [
            'WHERE' => sprintf('auction_profile_id IN (%s) AND ((auction_in_woocommerce IS NULL OR auction_in_woocommerce = 0 OR auction_in_woocommerce = 2) AND auction_status = \'ACTIVE\') AND (auction_time > NOW() OR auction_time IS NULL)', implode(',', $wooCommerceProfileIds)),
            'LIMIT' => 100
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
        
        $profileModel->unsetData();
        
        foreach($auctionsData as $profileId => $auctionIds) {
            $profile = $profileModel->load($profileId);
            if(!$profile->getId()) {
                continue;
            }

            /** @var GJMAA_Model_Settings $settings */
	        $settings = GJMAA::getModel('settings');
	        $settings->load($profile->getData('profile_setting_id'));

	        $settingsSite = $settings->getData('setting_site');
	        if($profile->getData('profile_type') !== 'my_auctions' && $settingsSite == 1) {
	        	continue;
	        }

	        try {
		        if ( $settingsSite == 1 ) {
			        /** @var GJMAA_Service_Import_Auctions $restApiImport */
			        $restApiImport = GJMAA::getService( 'import_auctions' );
			        $profile->setData( 'profile_import_step', 2 );
			        $restApiImport->setProfile( $profile );
			        $restApiImport->setSettings( $settings );
			        $restApiImport->setAuctions( $auctionIds );
			        $restApiImport->run();
		        } else {
		        	/** @var GJMAA_Service_Import $restApiImport */
			        $restApiImport = GJMAA::getService('import');
			        $restApiImport->setProfile($profile);
			        $isRestConnected = $restApiImport->connect();
			        $webapiImport = null;
                    if ($isRestConnected) {
                    	/** @var GJMAA_Lib_Webapi $webapiImport */
	                    $webapiImport = $restApiImport->connectToWebAPI();
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

			        $wooCommerceService->setSettingId($profile->getData('profile_setting_id'));
                    $productIds = $wooCommerceService->saveProducts($auctionDetails);
			        $this->saveUpdatedAuctions($auctions, $auctionsToSkip, $productIds);
		        }
	        } catch ( Exception $e ) {
		        error_log( sprintf( __( 'Problem with creating products on profile: %s with error %s', GJMAA_TEXT_DOMAIN ), $profileId, $e->getMessage() ) );
	        }
        }

	    if(!empty($auctionsToSkip)) {
		    $this->saveUpdatedAuctions($auctions, $auctionsToSkip);
	    }
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