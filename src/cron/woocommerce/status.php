<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Woocommerce_Status
{
    
    public function execute()
    {
        error_log(sprintf('[%s] Run cron', 'STATUS WOOCOMMERCE'));
        $wooCommerceService = GJMAA::getService('woocommerce');
        if (! $wooCommerceService->isEnabled()) {
            return;
        }
        
        $profileModel = GJMAA::getModel('profiles');
        $wooCommerceProfileIds = $profileModel->getWooCommerceProfileIds();
        
        if (empty($wooCommerceProfileIds)) {
            return;
        }
        
        $profilesToUpdate = 10;
        
        foreach ($wooCommerceProfileIds as $profileId) {
            if (! $this->validateForExecute($profileId)) {
                error_log(sprintf('[%s] Skip profile: %d', 'STATUS WOOCOMMERCE', $profileId));
                continue;
            }
            
            $filters = [
                'WHERE' => sprintf('auction_profile_id = %d', $profileId)
            ];
            
            $auctionsModel = GJMAA::getModel('auctions');
            $auctions = $auctionsModel->getAllBySearch($filters);
            
            if (empty($auctions)) {
                $this->updateLastSync($profileId);
                error_log(sprintf('[%s] Skip profile: %d', 'STATUS WOOCOMMERCE', $profileId));
                return;
            }
            
            error_log(sprintf('[%s] Run profile: %d', 'STATUS WOOCOMMERCE', $profileId));
            error_log(sprintf('[%s] Count of auctions %d for profile: %d', 'STATUS WOOCOMMERCE', count($auctions), $profileId));
            
            $wooCommerceService = GJMAA::getService('woocommerce');
            
            foreach ($auctions as $auction) {
                $sku = $auction['auction_id'];
                $productId = $wooCommerceService->getProductIdByAuctionId($sku);
                if (0 === $productId && ! $auction['auction_in_woocommerce']) {
                    continue;
                }
                
                if (0 !== $productId && $auction['auction_in_woocommerce'] == 1) {
                    if(!empty($auction['auction_woocommerce_id'])) {
                        continue;
                    }
                }
                
                $auctionsModel->updateAttribute($auction['id'], 'auction_woocommerce_id', $productId);
                $auctionsModel->updateAttribute($auction['id'], 'auction_in_woocommerce', 0 !== $productId ? 1 : 0);                
            }
            
            $this->updateLastSync($profileId);
            
            if ($profilesToUpdate <= 0) {
                break;
            }
            
            $profilesToUpdate --;
        }
        
        error_log(sprintf('[%s] End cron', 'STATUS WOOCOMMERCE'));
    }
    
    public function validateForExecute($profileId)
    {
        $profileModel = GJMAA::getModel('profiles');
        $profile = $profileModel->load($profileId);
        
        $lastStatusUpdate = $profile->getData('profile_sync_status_date');
        return ! $lastStatusUpdate || (strtotime($lastStatusUpdate) <= (time() - 900));
    }
    
    public function updateLastSync($profileId)
    {
        $profileModel = GJMAA::getModel('profiles');
        $profile = $profileModel->load($profileId);
        
        $profile->updateAttribute($profileId, 'profile_sync_status_date', date('Y-m-d H:i:s'));
    }
    
    public static function run()
    {
        (new self())->execute();
    }
}
?>