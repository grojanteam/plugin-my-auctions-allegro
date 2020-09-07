<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Woocommerce_Price
{
    
    public function execute()
    {
        error_log(sprintf('[%s] Cron run','WOOCOMMERCE PRICE'));
        error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
        
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
                error_log(sprintf('[%s] Skip profile %d','WOOCOMMERCE PRICE', $profileId));
                error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
                continue;
            }
            
            error_log(sprintf('[%s] Run profile %d','WOOCOMMERCE PRICE', $profileId));
            error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
            
            $filters = [
                'WHERE' => sprintf('auction_profile_id = %d AND auction_woocommerce_id != 0', $profileId)
            ];
            
            $auctionsModel = GJMAA::getModel('auctions');
            $auctions = $auctionsModel->getAllBySearch($filters);
            
            if (empty($auctions)) {
                $this->updateLastSync($profileId);
                return;
            }
            
            error_log(sprintf('[%s] Count of auctions %d for profile %d','WOOCOMMERCE PRICE', count($auctions), $profileId));            
            error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
            
            $priceUpdateData = [];
            foreach ($auctions as $auction) {
                if($auction['auction_price'] < 0.0001) {
                    continue;
                }
                
                $priceUpdateData[$auction['auction_id']] = [
                    'price' => $auction['auction_price'],
                    'profile_id' => $auction['auction_profile_id'],
                    'product_id' => $auction['auction_woocommerce_id']
                ];
            }
            
            error_log(sprintf('[%s] Updating profile %d','WOOCOMMERCE PRICE', $profileId));
            error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
            
            $wooCommerceService->updatePrices($priceUpdateData);
            
            error_log(sprintf('[%s] Profile %d updated','WOOCOMMERCE PRICE', $profileId));
            error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
            
            $this->updateLastSync($profileId);
            
            if ($profilesToUpdate <= 0) {
                break;
            }
            
            $profilesToUpdate --;
        }
        
        error_log(sprintf('[%s] Cron end','WOOCOMMERCE PRICE'));
        error_log(sprintf('[%s] Memory %s','WOOCOMMERCE PRICE',$this->convert_filesize(memory_get_usage(true))));
    }
    
    public function validateForExecute($profileId)
    {
        $profileModel = GJMAA::getModel('profiles');
        $profile = $profileModel->load($profileId);
        
        $isEnabledProfilePriceSync = $profile->getData('profile_sync_price');
        if(!$isEnabledProfilePriceSync) {
            return false;
        }
        
        $lastPriceUpdate = $profile->getData('profile_sync_price_date');
        return ! $lastPriceUpdate || (strtotime($lastPriceUpdate) <= (time() - 600));
    }
    
    public function updateLastSync($profileId)
    {
        $profileModel = GJMAA::getModel('profiles');
        $profile = $profileModel->load($profileId);
        
        $profile->updateAttribute($profileId, 'profile_sync_price_date', date('Y-m-d H:i:s'));
    }
    
    public function convert_filesize($bytes, $decimals = 2){
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
    
    public static function run()
    {
        (new self())->execute();
    }
}
?>