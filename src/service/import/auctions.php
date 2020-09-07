<?php
require_once __DIR__ . '/../import.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Import_Auctions extends GJMAA_Service_Import
{

    protected $type = 'my_auctions';

    public function makeRequest()
    {
        if ($this->getProfileStep() != 2) {
            if (! $this->client) {
                $api = GJMAA::getLib('rest_api_sale_offers');

                $this->client = $api;
            }
            
            return parent::makeRequest();
        }

        return $this->getAuctionDetails();
    }

    public function parseResponse($response,$auctionId = null)
    {
        $result = [
            'auctions' => [],
            'all_auctions' => $this->getProfile()->getData('profile_all_auctions') ?: 0,
            'imported_auctions' => $this->getProfile()->getData('profile_imported_auctions') ?: 0,
            'progress' => 100
        ];

        if ($this->getProfileStep() != 2) {
            $auctions = $response['offers'];

            $countOfAuctions = count($auctions);

            $collection = [];

            if ($countOfAuctions > 0) {
                foreach ($auctions as $auction) {                    
                    $collection[] = [
                        'auction_id' => $auction['id'],
                        'auction_profile_id' => $this->getProfile()->getId(),
                        'auction_name' => $auction['name'],
                        'auction_price' => $auction['sellingMode']['price']['amount'],
                        'auction_bid_price' => GJMAA_Service_Import::PRICE_BIDDING_FORMAT ? (!empty($auction['saleInfo']['currentPrice']) ? $auction['saleInfo']['currentPrice']['amount'] : ( !empty($auction['sellingMode']['minimalPrice']['amount']) ? $auction['sellingMode']['minimalPrice']['amount'] : $auction['sellingMode']['startingPrice']['amount'])) : 0,
                        'auction_images' => json_encode([$auction['primaryImage']]),
                        'auction_seller' => $this->getUserId(),
                        'auction_status' => $auction['publication']['status'],
                        'auction_categories' => $auction['category']['id'],
                        'auction_time' => isset($auction['publication']) ? $auction['publication']['endingAt'] : null,
                        'auction_quantity' => $auction['stock']['available']
                    ];
                }
                
                $result['auctions'] = $collection;
                $profileAuctions = $this->getProfile()->getData('profile_auctions');
                $allAuctions = $profileAuctions != 0 && $profileAuctions <= $response['totalCount'] ? $profileAuctions : $response['totalCount'];
                $result['all_auctions'] = $allAuctions;
                $result = $this->recalculateProgressData($result, $countOfAuctions);
            } else {
                $result['all_auctions'] = 0;
                $result = $this->recalculateProgressData($result, $countOfAuctions);
            }
        } else {
            
            if(isset($response->arrayItemListInfo->item)) {
                $auctionDetails = $response->arrayItemListInfo->item;
                
                $serviceWooCommerce = GJMAA::getService('woocommerce');
                $serviceWooCommerce->setSettingId($this->getSettings()->getId());
                $serviceWooCommerce->saveProducts([
                    $auctionDetails
                ]);
            }
            
            $result['auctions'][] = [
                'auction_id' => $auctionDetails ? $auctionDetails->itemInfo->itId : $auctionId,
                'auction_profile_id' => $this->getProfile()->getId(),
                'auction_in_woocommerce' => $auctionDetails ? 1 : 2
            ];
            
            $result['all_auctions'] = $this->getProfile()->getData('profile_all_auctions');
            $result = $this->recalculateProgressData($result, 1);
            
            
//             $auctionDetails = [];

//             $product = [];
//             $product['id'] = $response['id'];
//             $product['name'] = $response['name'];
//             $product['description'] = $this->prepareDescription($response['description']);
//             $product['stock'] = $response['stock']['available'];
//             $product['status'] = $response['publication']['status'];
//             $product['images'] = $response['images'];
//             $product['categories'] = $response['category']['id'];
//             $product['attributes'] = $response['parameters'];

//             $auctionDetails[] = $product;

//             $serviceWooCommerce = GJMAA::getService('woocommerce');
//             $serviceWooCommerce->setSettingId($this->getSettings()
//                 ->getId());
//             $serviceWooCommerce->saveProducts([
//                 $auctionDetails,
//                 true
//             ]);

//             $result['auctions'][] = [
//                 'auction_id' => $response['id'],
//                 'auction_profile_id' => $this->getProfile()->getId(),
//                 'auction_in_woocommerce' => 1
//             ];

//             $result['all_auctions'] = $this->getProfile()->getData('profile_all_auctions');
//             $result = $this->recalculateProgressData($result, 1);
        }

        return $result;
    }

//     public function prepareDescription($description)
//     {
//         $content = '<div>';

//         foreach ($description['sections'] as $items) {
//             $content .= '<div class="row">';
//             $twoCols = count($items['items']) > 1;
//             $class = $twoCols ? 'col-6' : 'col-12';
//             foreach ($items['items'] as $item) {
//                 $isImage = strtolower($item['type']) == 'image';

//                 $content .= '<div class="' . $class . '">' . ($isImage ? '<img src="' . $item['url'] . '" />' : $item['content']) . '</div>';
//             }
//             $content .= '</div>';
//         }

//         $content .= '</div>';

//         return $content;
//     }
}

?>