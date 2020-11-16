<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Webapi_Import
{

    protected $profile;

    protected $settings;

    protected $client;

    const PRICE_BUY_NOW_FORMAT = 'buyNow';
    const PRICE_BIDDING_FORMAT = 'bidding';

    public function run()
    {
        if ($this->getProfileStep() != 2) {
            $filters = [
                'category' => $this->getProfile()->getData('profile_category') ?: null,
                'userId' => $this->getProfile()->getData('profile_user') ?: null,
                'search' => $this->getProfile()->getData('profile_search_query') ?: null
            ];

            $profileSort = $this->getProfile()->getData('profile_sort');
            if(empty($profileSort)){
                $profileSort = 'price_desc';
            }

            list ($sortType, $sortOrder) = explode("_", $profileSort);

            $sort = [
                'sortType' => $sortType,
                'sortOrder' => $sortOrder
            ];

            $imported = intval(ceil($this->getProfile()->getData('profile_imported_auctions') / 1000));
            $offset = ! is_null($imported) ? $imported * 1000 : 0;

            $result = [
                'resultOffset' => $offset,
                'resultSize' => $this->getProfile()->getData('profile_auctions') <= 1000 ? $this->getProfile()->getData('profile_auctions') : 1000
            ];
            
            $response = $this->doRequest($filters, $sort, $result);
            if(!$response){
                throw new Exception($this->client->getError());
            }
        } else {
            $auctionId = $this->getAuctionIdByProfile();
            $response = $this->client->getItemAuction($auctionId);
        }

        return $this->parseResult($response,isset($auctionId) ? $auctionId : null);
    }

    public function connect()
    {
        if ($this->client === null) {
            $settings = $this->getSettingsByProfile();

            $login = $settings->getData('setting_login');
            $password = $settings->getData('setting_password');
            $webapiKey = $settings->getData('setting_webapi_key');

            if (! $login || ! $password) {
                return false;
            }

            $webApiLib = GJMAA::getLib('webapi');
            $webApiLib->setCountry($settings->getData('setting_site'));
            $webApiLib->connectByLogin($login, $password, $webapiKey);

            if ($webApiLib->getError()) {
                throw new Exception('[WEBAPI ERROR]' . $webApiLib->getError());
            }

            $this->client = $webApiLib;
        }

        return $this->client;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getSettingsByProfile()
    {
        if ($this->settings === null) {
            $settingId = $this->getProfile()->getData('profile_setting_id');

            $this->settings = GJMAA::getModel('settings')->load($settingId);
        }
        return $this->settings;
    }

    public function doRequest($filters, $sort, $result)
    {
        return $this->client->doGetSearchItems($filters, $sort, $result);
    }

    public function parseResult($response, $auctionId = null)
    {
        $result = [
            'auctions' => [],
            'all_auctions' => $this->getProfile()->getData('profile_all_auctions') ?: 0,
            'imported_auctions' => $this->getProfile()->getData('profile_imported_auctions') ?: 0,
            'progress' => 100
        ];
        if ($this->getProfileStep() != 2) {
            if (! empty($response->itemsList->item)) {
                $regular = is_array($response->itemsList->item) ? $response->itemsList->item : [
                    $response->itemsList->item
                ];
            } else {
                $regular = [];
            }

            $countOfAuctions = count($regular);

            $collection = [];
            if ($countOfAuctions > 0) {
                foreach ($regular as $auction) {
                    $collection[] = [
                        'auction_id' => $auction->itemId,
                        'auction_profile_id' => $this->getProfile()->getId(),
                        'auction_name' => $auction->itemTitle,
                        'auction_price' => $this->getBuyNowPrice($auction->priceInfo),
                        'auction_bid_price' => $this->getBidPrice($auction->priceInfo),
                        'auction_images' => $this->parseImages($auction->photosInfo->item),
                        'auction_seller' => $auction->sellerInfo->userId,
                        'auction_categories' => $auction->categoryId,
                        'auction_time' => $auction->endingTime,
                        'auction_quantity' => $auction->leftCount
                    ];
                } 

                $result['auctions'] = $collection;
                $result['all_auctions'] = $response->itemsCount >= $this->getProfile()->getData('profile_auctions') ? $this->getProfile()->getData('profile_auctions') : $response->itemsCount;

                $result = $this->recalculateProgressData($result, $countOfAuctions);
            }
        } else {
            $auctionDetails = $response->arrayItemListInfo->item;

            $serviceWooCommerce = GJMAA::getService('woocommerce');
            $serviceWooCommerce->saveProducts([
                $auctionDetails
            ]);

            $result['auctions'][] = [
                'auction_id' => $auctionDetails->itemInfo->itId ? : $auctionId,
                'auction_profile_id' => $this->getProfile()->getId(),
                'auction_in_woocommerce' => 1
            ];

            $result['all_auctions'] = $this->getProfile()->getData('profile_all_auctions');
            $result = $this->recalculateProgressData($result, 1);
        }

        return $result;
    }

    public function getBuyNowPrice($priceInfo)
    {
        foreach ($priceInfo->item as $price) {
            if ($price->priceType == self::PRICE_BUY_NOW_FORMAT) {
                return $price->priceValue;
            }
        }

        return 0;
    }

    public function getBidPrice($priceInfo)
    {
        foreach ($priceInfo->item as $price) {
            if ($price->priceType == self::PRICE_BIDDING_FORMAT) {
                return $price->priceValue;
            }
        }
        return 0;
    }

    public function getProfileStep()
    {
        return $this->getProfile()->getData('profile_import_step');
    }

    public function recalculateProgressData($result, $count)
    {
        $result['step'] = $this->getProfileStep();
        $result['all_steps'] = $this->getProfile()->getData('profile_to_woocommerce') ? 3 : 1;

        $progress_all = 100;
        if ($result['all_steps'] > 1) {
            $progress_all = 100 / $result['all_steps'];
        }

        $result['imported_auctions'] += $count;
        $result['progress_step'] = number_format(($result['imported_auctions'] / $result['all_auctions']) * 100, 2);
        $result['progress'] = $progress_all == 100 ? $result['progress_step'] : ($this->getProfileStep() == 1 ? $result['progress_step'] / $result['all_steps'] : number_format($progress_all + ($result['progress_step'] / $result['all_steps']), 2));

        return $result;
    }

    public function getAuctionIdByProfile()
    {
        $auctionsModel = GJMAA::getModel('auctions');

        $filters = [
            'WHERE' => 'auction_profile_id = ' . $this->getProfile()->getId(),
            'LIMIT' => 1,
            'OFFSET' => $this->getProfile()->getData('profile_imported_auctions')
        ];

        $auction = $auctionsModel->getRowBySearch($filters);
        return $auction->getData('auction_id');
    }

    public function parseImages($images)
    {
        $allegroImages = is_array($images) ? $images : [
            $images
        ];
        $auctionImages = [];
        $count = 0;
        foreach ($allegroImages as $index => $image) {
            if ($image->photoSize != 'large')
                continue;
            $auctionImages[$count] = new stdClass();
            $auctionImages[$count]->url = $image->photoUrl;
            $count ++;
        }

        return json_encode($auctionImages);
    }
    
    public function getClient(){
        return $this->client;
    }
}
?>