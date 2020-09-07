<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Import_Auctions
{

    public function execute()
    {
        $profiles = GJMAA::getModel('profiles');
        $allprofiles = $profiles->getAll();

        if (empty($allprofiles)) {
            return;
        }

        $helperImport = GJMAA::getHelper('import');

        $i = 0;
        foreach ($allprofiles as $profile) {
            if(!$profile['profile_cron_sync']) {
                continue;
            }
            
            if($profile['profile_errors'] > 5){
                continue;
            }
            
            if(!$this->validateForExecute($profile['profile_id'])) {
                error_log(sprintf('[%s] Skip profile ID: %d', 'CRON AUCTION IMPORT', $profile['profile_id']));
                continue;
            }
            
            $this->lockProfile($profile['profile_id']);
            error_log(sprintf('[%s] Run profile ID: %d', 'CRON AUCTION IMPORT', $profile['profile_id']));
            error_log(sprintf('[%s] Memory usage: %d', 'CRON AUCTION IMPORT', $this->convert_filesize(memory_get_usage(true))));
            do {
                try {
                    $profileId = $profile['profile_id'];
                    $response = $helperImport->runImportByProfileId($profileId, 'cron');
                    if ((isset($response['progress_step']) && $response['progress_step'] == 100) || $response['all_auctions'] == 0) {
                        error_log(sprintf('[%s] After profile ID: %d', 'CRON AUCTION IMPORT', $profileId));
                        error_log(sprintf('[%s] Memory usage: %d', 'CRON AUCTION IMPORT', $this->convert_filesize(memory_get_usage(true))));
                        break;
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    break;
                }
                $i ++;
                sleep(1);
            } while ($i < 150);
        }
    }
    
    public function lockProfile($profileId) {
        GJMAA::getModel('profiles')->updateAttribute($profileId, 'profile_import_lock', 1);
    }
    
    public function validateForExecute($profileId)
    { 
        $profileModel = GJMAA::getModel('profiles');
        $profile = $profileModel->load($profileId);
        
        $lastStockUpdate = $profile->getData('profile_last_sync');
        $isLocked = $profile->getData('profile_import_lock');
        return (!$lastStockUpdate || (strtotime($lastStockUpdate) <= (time() - 120))) && !$isLocked;
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