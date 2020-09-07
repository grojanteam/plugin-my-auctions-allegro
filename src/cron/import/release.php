<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Cron_Import_Release
{
    public function execute()
    {
        $profiles = GJMAA::getModel('profiles');
        $allprofiles = $profiles->getAllLockedProfiles();
        
        if (empty($allprofiles)) {
            return;
        }
        
        foreach ($allprofiles as $profileId) {
            $this->releaseLockForProfile($profileId);
        }
        
        return;
    }
    
    public function releaseLockForProfile($profileId) {
        GJMAA::getModel('profiles')->updateAttribute($profileId, 'profile_import_lock', 0);
    }
    
    public static function run()
    {
        (new self())->execute();
    }
}

?>