<?php

class GjmaaServiceCron {
    public function execute(){
        $listOfSettings = $this->getListOfAuctionSettings();

        $serviceAuctions = new GjmaaServiceAuctions();
        foreach($listOfSettings as $allegroSetting){
            try {
                $lastSync = isset($allegroSetting['last_sync']) ? $allegroSetting['last_sync'] : null;
                $today = strtotime(date('Y-m-d') . ' 00:00:00');
                if($lastSync && strtotime($lastSync . ' 00:00:00') >= $today){
                    continue;
                }

                do {
                    $requestData = $allegroSetting;
                    $requestData['allegro_setting_id'] = $requestData['id'];

                    $response = $serviceAuctions->importAuctions($requestData);
                    sleep(2);
                } while (!$response['end']);
                $this->updateLastSync($requestData['allegro_setting_id']);
                break;
            } catch(Exception $exception){
                continue;
            }
        }
    }

    public function updateLastSync($allegroSettingsId){
        $auctionSettingsModel = new GjmaaMyAuctionsAllegro();
        $settings = $auctionSettingsModel->getById($allegroSettingsId);
        $settings['last_sync'] = date('Y-m-d');
        $auctionSettingsModel->saveAuctionSettings($settings);
    }

    protected function getListOfAuctionSettings(){
        $auctionSettingsModel = new GjmaaMyAuctionsAllegro();
        return $auctionSettingsModel->getAll();
    }
}