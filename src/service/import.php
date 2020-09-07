<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Import
{

    protected $profile;

    protected $settings;

    protected $apiToken;

    protected $client;

    protected $webapi;

    const PRICE_BUY_NOW_FORMAT = 'BUY_NOW';

    const PRICE_BIDDING_FORMAT = 'AUCTION';

    const PRICE_ADVERTISMENT_FORMAT = 'ADVERTISEMENT';

    public function setProfile(GJMAA_Model_Profiles $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function makeRequest()
    {
        if (! $this->client) {
            throw new Exception(__('No set required parameters'));
        }

        if ($this->getProfileStep() != 2) {
            $limit_per_request = 100;
            $imported = intval(ceil($this->getProfile()->getData('profile_imported_auctions') / $limit_per_request));
            $offset = $imported * $limit_per_request;

            $profileAuctions = $this->getProfile()->getData('profile_auctions');

            $limit = $profileAuctions != 0 && $profileAuctions < $limit_per_request ? $profileAuctions : $limit_per_request;

            $this->client->setOffset($offset);
            $this->client->setLimit($limit);
            $this->client->setSort($this->getProfile()
                ->getData('profile_sort') ?: 'price_desc');
        }
    }

    public function connect()
    {
        $settings = $this->getSettings();

        $helper = GJMAA::getHelper('settings');

        if (! $helper->isConnectedApi($settings->getData())) {
            return false;
        }

        if ($helper->isExpiredToken($settings->getData())) {
            $settings = $this->refreshToken($settings);

            $this->setSettings($settings);
        }

        return true;
    }

    public function getSettings()
    {
        if (! $this->settings) {
            $this->settings = GJMAA::getModel('settings')->load($this->getProfile()
                ->getData('profile_setting_id'));
        }
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function refreshToken($settings)
    {
    	/** @var GJMAA_Helper_Settings $helper */
    	$helper = GJMAA::getHelper('settings');

        return $helper->refreshToken($settings);
    }

    public function run()
    {
        if (! $this->getProfile()) {
            throw new Exception(__('Profile not set', GJMAA_TEXT_DOMAIN));
        }

        if ($this->connect()) {
            $this->makeRequest();
            $response = $this->sendRequest();

            if ($this->getProfileStep() == 2) {
                $auctionId = $this->getAuctionIdByProfile();
            }
        } else {
            $import = GJMAA::getService('webapi_import');
            $import->setProfile($this->getProfile());
            if ($import->connect()) {
                return $import->run();
            } else {
                throw new Exception(__('Can\'t connect to API', GJMAA_TEXT_DOMAIN));
            }
        }

        return $this->parseResponse($response, (isset($auctionId) ? $auctionId : null));
    }

    public function sendRequest()
    {
        if ($this->getProfileStep() != 2) {
            $this->client->setToken($this->getSettings()
                ->getData('setting_client_token'));
            $this->client->setSandboxMode($this->getSettings()
                ->getData('setting_is_sandbox'));
            return $this->client->execute();
        } else {
            $auctionId = $this->getAuctionIdByProfile();
            return $this->client->getItemAuction($auctionId);
        }
    }

    public function connectToWebAPI()
    {
        if (! $this->webapi) {
            $settings = $this->getSettings();

            $token = $settings->getData('setting_client_token');
            $webapiKey = $settings->getData('setting_webapi_key');
            $countryId = $settings->getData('setting_site');

            $webapi = GJMAA::getLib('webapi');
            $webapi->setSandbox($settings->getData('setting_is_sandbox'));
            $webapi->connectByToken($token, $webapiKey, $countryId);
            $this->webapi = $webapi;
        }

        return $this->webapi;
    }

    public function getUserId()
    {
        $userToken = $this->getSettings()->getData('setting_client_token');

        if (! $userToken) {
            return null;
        }

        $tokenSplit = explode('.', $userToken);
        $decodedUserData = isset($tokenSplit[1]) ? json_decode(base64_decode($tokenSplit[1]), true) : [];

        return isset($decodedUserData['user_name']) ? $decodedUserData['user_name'] : null;
    }

    public function parseUserToSellerId($user)
    {
        $setting_login = $this->getSettings()->getData('setting_login');

        $userId = null;

        if ($setting_login == $user || $this->getProfile() === 'my_auctions') {
            $userId = $this->getUserId();
        } else {
            $userId = $user;
        }
        
        return $userId;
    }

    public function getProfileStep()
    {
        return $this->getProfile()->getData('profile_import_step');
    }

    public function getAuctionDetails()
    {
        $this->client = $this->connectToWebAPI();
    }

    public function getAuctionIdByProfile()
    {
        $first = true;
        do {
            $auctionsModel = GJMAA::getModel('auctions');

            if($first) {
                $profileImportedAuctions = $this->getProfile()->getData('profile_imported_auctions');
                $first = false;
            } else {
                $profileImportedAuctions++;
            }
            $gjmaaStatusOffer = GJMAA::getSource('allegro_offerstatus');

            $filters = [
                'WHERE' => 'auction_profile_id = ' . $this->getProfile()->getId(),
                'LIMIT' => 1,
                'OFFSET' => $profileImportedAuctions
            ];
            
            error_log($profileImportedAuctions);

            $auction = $auctionsModel->getRowBySearch($filters);
            
            if($auction->getData('auction_status') !== $gjmaaStatusOffer::ACTIVE) {
                $auction->setData('auction_in_woocommerce', 2);
                $auction->save();
                if(($this->getProfile()->getData('profile_all_auctions')-1) <= $profileImportedAuctions) {
                    break;
                } 
            }
        } while ($auction->getData('auction_status') !== $gjmaaStatusOffer::ACTIVE);
        
        $this->getProfile()->setData('profile_imported_auctions', $profileImportedAuctions);

        return $auction->getData('auction_id');
    }

    public function recalculateProgressData($result, $count)
    {
        $result['step'] = $this->getProfileStep();
        $result['all_steps'] = $this->getProfile()->getData('profile_to_woocommerce') ? 2 : 1;

        $progress_all = 100;
        if ($result['all_steps'] > 1) {
            $progress_all = $result['all_auctions'] > 0 ? 50 : 100;
        }

        $result['imported_auctions'] += $count;
        $result['progress_step'] = $result['all_auctions'] > 0 ? number_format(($result['imported_auctions'] / $result['all_auctions']) * 100, 2) : 100;
        $result['progress'] = $progress_all == 100 ? $result['progress_step'] : ($this->getProfileStep() != 2 ? $result['progress_step'] / 2 : number_format($progress_all + ($result['progress_step'] / 2), 2));

        return $result;
    }

    public function parseResponse($response, $auctionId = null)
    {
        $result = [
            'auctions' => [],
            'all_auctions' => $this->getProfile()->getData('profile_all_auctions') ?: 0,
            'imported_auctions' => $this->getProfile()->getData('profile_imported_auctions') ?: 0,
            'progress' => 100
        ];

        if ($this->getProfileStep() != 2) {
            $regular = $response['items']['regular'];
            $promoted = $response['items']['promoted'];

            $auctions = array_merge($regular, $promoted);
            $countOfAuctions = count($auctions);

            $collection = [];

            $allegroOfferStatus = GJMAA::getSource('allegro_offerstatus');
            if ($countOfAuctions > 0) {
                foreach ($auctions as $auction) {
                    $collection[] = [
                        'auction_id' => $auction['id'],
                        'auction_profile_id' => $this->getProfile()->getId(),
                        'auction_name' => $auction['name'],
                        'auction_price' => $auction['sellingMode']['format'] == self::PRICE_BUY_NOW_FORMAT ? $auction['sellingMode']['price']['amount'] : ($auction['sellingMode']['format'] == self::PRICE_BIDDING_FORMAT && isset($auction['sellingMode']['fixedPrice']) ? $auction['sellingMode']['fixedPrice']['amount'] : ($auction['sellingMode']['format'] == self::PRICE_ADVERTISMENT_FORMAT ? $auction['sellingMode']['price']['amount'] : 0)),
                        'auction_bid_price' => $auction['sellingMode']['format'] == self::PRICE_BIDDING_FORMAT ? $auction['sellingMode']['price']['amount'] : 0,
                        'auction_images' => json_encode($auction['images']),
                        'auction_seller' => $auction['seller']['id'],
                        'auction_categories' => $auction['category']['id'],
                        'auction_status' => $allegroOfferStatus::ACTIVE,
                        'auction_time' => isset($auction['publication']) ? $auction['publication']['endingAt'] : null,
                        'auction_quantity' => $auction['stock']['available']
                    ];
                }
                $result['auctions'] = $collection;
                $profileAuctions = $this->getProfile()->getData('profile_auctions');
                $allAuctions = $profileAuctions != 0 && $profileAuctions <= $response['searchMeta']['totalCount'] ? $profileAuctions : ($response['searchMeta']['totalCount'] > 6000 ? 6000 : $response['searchMeta']['totalCount']);
                $result['all_auctions'] = $allAuctions;
                $result = $this->recalculateProgressData($result, $countOfAuctions);
            } else {
                $result['all_auctions'] = 0;
                $result = $this->recalculateProgressData($result, $countOfAuctions);
            }
        } else {
            $auctionDetails = $response->arrayItemListInfo->item;

            $serviceWooCommerce = GJMAA::getService('woocommerce');
            $serviceWooCommerce->setSettingId($this->getSettings()
                ->getId());
            $serviceWooCommerce->saveProducts([
                $auctionDetails
            ]);

            $result['auctions'][] = [
                'auction_id' => $auctionDetails ? $auctionDetails->itemInfo->itId : $auctionId,
                'auction_profile_id' => $this->getProfile()->getId(),
                'auction_in_woocommerce' => $auctionDetails ? 1 : 2
            ];

            $result['all_auctions'] = $this->getProfile()->getData('profile_all_auctions');
            $result = $this->recalculateProgressData($result, 1);
        }

        return $result;
    }
}

?>