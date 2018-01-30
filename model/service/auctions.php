<?php

class GjmaaServiceAuctions
{
    protected $_client = null;

    protected $_apiSettings = null;

    public function importAuctions($request)
    {
        $modelAuctions = new GjmaaMyAuctionsAllegro();

        $allegroSettingsId = isset($request['settings_of_auctions']) ? $request['settings_of_auctions'] : $request['allegro_setting_id'];
        $prepareFiltersForRequest = $this->getFiltersForRequest($allegroSettingsId);

        $response = array();
        $response['nonce'] = isset($request['nonce']) ? $request['nonce'] : null;
        $response['action'] = isset($request['action']) ? $request['action'] : null;
        $response['all_auctions'] = isset($request['all_auctions']) ? $request['all_auctions'] : 0;
        $response['start'] = isset($request['start']) ? $request['start'] : 0;
        $response['limit'] = 10;
        $response['end'] = false;
        $response['allegro_setting_id'] = $allegroSettingsId;

        $result['resultOffset'] = ($response['start'] * $response['limit']);
        $result['resultSize'] = $response['limit'];

        if ($response['all_auctions'] === 0) {
            $modelAuctions->removeAuctionsBySettings($allegroSettingsId);
        }

        $auctions = $this->doRequestToAllegro($prepareFiltersForRequest['filters'], $prepareFiltersForRequest['sort'], $result);
        $response = $this->checkLimitAndSave($response);

        $apiSettings = $this->getApiSettings();
        $userName = $apiSettings['allegro_username'];

        $allegroUserId = $this->getAllegroUserId($userName);

        $userId = isset($prepareFiltersForRequest['filters']['userId']) ? $prepareFiltersForRequest['filters']['userId'] : $allegroUserId;

        if ($auctions->itemsCount > 0) {
            $result = $modelAuctions->saveAuctions($auctions->itemsList->item, $this->connectApiAllegro(), $allegroSettingsId,$userId);
            foreach ($result as $index_result => $item_result) {
                $response[$index_result] = $item_result;
            }
        }

        return $response;
    }

    public function checkLimitAndSave($response)
    {
        $response['end'] = true;
        $response['message'] = __('No auctions to import', 'gj_myauctions_allegro');

        return $response;
    }

    public function getFiltersForRequest($auctionSettingsId)
    {
        /** @var $modelAuctions GjmaaMyAuctionsAllegro */
        $modelAuctions = new GjmaaMyAuctionsAllegro();
        $settingData = $modelAuctions->getById($auctionSettingsId);

        $apiSettings = $this->getApiSettings();
        $type_of_auctions = $settingData['type_of_auctions'];

        $filters = array();
        if($settingData['item_' . $type_of_auctions . '_category']) {
            $filters['category'] = $settingData['item_' . $type_of_auctions . '_category'];
        }
        $username = $type_of_auctions == 'my_auctions' ? $apiSettings['allegro_username'] : $settingData['item_' . $type_of_auctions . '_user'];
        if($username) {
            $filters['userId'] = $this->getAllegroUserId($username);
        }

        if ($type_of_auctions == 'search') {
            $filters['search'] = $settingData['item_' . $type_of_auctions . '_query'];
        }

        $sorting_exploded = explode('_', $settingData['item_' . $type_of_auctions . '_sort']);
        $sort = array();
        $sort['sortType'] = $sorting_exploded[0];
        $sort['sortOrder'] = $sorting_exploded[1];

        return [
            'filters' => $filters,
            'sort' => $sort
        ];
    }

    public function getAllegroUserId($username)
    {
        $api_allegro = $this->connectApiAllegro();
        return $api_allegro ? $api_allegro->getUserID($username) : null;
    }

    public function importAuctionsWithDetails($request)
    {
        /** @var $itemAuctionsModel GjmaaAuctionsItem */
        $itemAuctionsModel = new GjmaaAuctionsItem();

        /** @var $itemAuctionModel GjmaaAuctionItem */
        $itemAuctionModel = new GjmaaAuctionItem();

        $response = array();
        $response['allegro_setting_id'] = isset($request['settings_of_auctions']) ? $request['settings_of_auctions'] : $request['allegro_setting_id'];
        $response['nonce'] = $request['nonce'];
        $response['action'] = $request['action'];
        $response['all_auctions'] = (isset($request['all_auctions']) AND $request['all_auctions'] > 0) ? $request['all_auctions'] : $itemAuctionsModel->getCountOfAuctionsBySettingId($response['allegro_setting_id']);
        $response['start'] = isset($request['start']) ? intval($request['start']) : 0;
        $response['limit'] = 10;
        $response['end'] = $response['all_auctions'] <= ($response['limit'] * (intval($response['start']) + 1)) ? true : false;

        $auctionIds = $itemAuctionsModel->getIdsAuctionsBySettingsId($response['allegro_setting_id'], $response['limit'], $response['limit'] * (intval($response['start'])));

        /** @var $api_allegro GjmaaAllegroWebApi */
        $api_allegro = $this->connectApiAllegro();
        if (!empty($auctionIds)) {
            $itemAuctionModel->removeItemAuctionsIds($auctionIds);
        }

        $items = $api_allegro->getItemAuction($auctionIds);
        $result = $itemAuctionModel->saveItems($items->arrayItemListInfo->item);
        foreach ($result as $ind => $res) {
            $response[$ind] = $res;
        }

        return $response;
    }

    public function connectApiAllegro()
    {
        if (null === $this->_client) {
            $apiSettings = $this->getApiSettings();
            $this->_client = new GjmaaAllegroWebApi($apiSettings['allegro_site'], (isset($apiSettings['allegro_api']) ? $apiSettings['allegro_api'] : null), $apiSettings['allegro_username'], $apiSettings['allegro_password']);
        }

        return $this->_client;
    }

    public function getApiSettings()
    {
        if (null === $this->_apiSettings) {
            /** @var $plgSettingsModel GjmaaSettings */
            $plgSettingsModel = new GjmaaSettings();
            $this->_apiSettings = $plgSettingsModel->getSettings();
        }

        return $this->_apiSettings;
    }

    public function doRequestToAllegro($filters, $sort, $result)
    {
        /** @var $api_allegro GjmaaAllegroWebApi */
        $client = $this->connectApiAllegro();
        return $client->doGetSearchItems($filters, $sort, $result);
    }
}