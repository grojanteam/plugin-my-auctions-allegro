<?php
require_once __DIR__ . '/../import.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined( 'ABSPATH' ) or die;

class GJMAA_Service_Import_Auctions extends GJMAA_Service_Import {

	protected $type = 'my_auctions';

	public function makeRequest() {
		if($this->getProfileStep() == 3) {
			/** @var GJMAA_Lib_Rest_Api_Sale_Offervariants $api */
			$api = GJMAA::getLib('rest_api_sale_offervariants');
		} else {
			/** @var GJMAA_Lib_Rest_Api_Sale_Offers $api */
			$api        = GJMAA::getLib( 'rest_api_sale_offers' );
			$categoryId = $this->getProfile()->getData( 'profile_category' );
			if ( ! empty( $categoryId ) ) {
				$api->setCategoryId( $categoryId );
			}
		}

		$this->client = $api;

		parent::makeRequest();
	}

	public function parseResponse( $response, $auctionId = null ) {
		$result = [
			'auctions'          => [],
			'all_auctions'      => $this->getProfile()->getData( 'profile_all_auctions' ) ?: 0,
			'imported_auctions' => $this->getProfile()->getData( 'profile_imported_auctions' ) ?: 0,
			'progress'          => 100
		];

		if ( $this->getProfileStep() == 1 ) {
			$auctions = $response['offers'];

			$countOfAuctions = count( $auctions );

			$collection = [];

			if ( $countOfAuctions > 0 ) {
				foreach ( $auctions as $auction ) {
					$collection[] = [
						'auction_id'         => $auction['id'],
						'auction_profile_id' => $this->getProfile()->getId(),
						'auction_name'       => $auction['name'],
						'auction_price'      => $auction['sellingMode']['price']['amount'],
						'auction_bid_price'  => GJMAA_Service_Import::PRICE_BIDDING_FORMAT ? ( ! empty( $auction['saleInfo']['currentPrice'] ) ? $auction['saleInfo']['currentPrice']['amount'] : ( ! empty( $auction['sellingMode']['minimalPrice']['amount'] ) ? $auction['sellingMode']['minimalPrice']['amount'] : $auction['sellingMode']['startingPrice']['amount'] ) ) : 0,
						'auction_images'     => json_encode( [ $auction['primaryImage'] ] ),
						'auction_seller'     => $this->getUserId(),
						'auction_status'     => $auction['publication']['status'],
						'auction_categories' => $auction['category']['id'],
						'auction_time'       => isset( $auction['publication'] ) ? $auction['publication']['endingAt'] : null,
						'auction_quantity'   => $auction['stock']['available']
					];
				}

				$result['auctions']     = $collection;
				$profileAuctions        = $this->getProfile()->getData( 'profile_auctions' );
				$allAuctions            = $profileAuctions != 0 && $profileAuctions <= $response['totalCount'] ? $profileAuctions : $response['totalCount'];
				$result['all_auctions'] = $allAuctions;
				$result                 = $this->recalculateProgressData( $result, $countOfAuctions );
			} else {
				$result['all_auctions'] = 0;
				$result                 = $this->recalculateProgressData( $result, $countOfAuctions );
			}
		} elseif ($this->getProfileStep() == 2) {
			$auctionDetails = [];

			if ( is_array( $response )  && count($response) > 1) {
				foreach($response as $singleResponse) {
					$auctionDetails[] = $this->prepareProduct($singleResponse);
				}
			} elseif (is_array($response)) {
				$auctionDetails[] = $this->prepareProduct( $response[$auctionId] );
			} else {
				$auctionDetails = $this->prepareProduct($response);
			}

			/** @var GJMAA_Service_Woocommerce $serviceWooCommerce */
			$serviceWooCommerce = GJMAA::getService( 'woocommerce' );
			$serviceWooCommerce->setSettings( $this->getSettings() );
			$serviceWooCommerce->setSettingId( $this->getSettings()
			                                        ->getId() );
			$serviceWooCommerce->setProfile($this->getProfile());

			$productIds = $serviceWooCommerce->saveProducts(
				$auctionDetails,
				true
			);

			foreach($productIds as $auctionId => $productId) {
				$result['auctions'][] = [
					'auction_id'             => $auctionId,
					'auction_profile_id'     => $this->getProfile()->getId(),
					'auction_in_woocommerce' => $auctionId ? 1 : 2
				];

				/** @var GJMAA_Model_Auctions $auctions */
				$auctions = GJMAA::getModel('auctions');
				$auctions->load($auctionId,'auction_id');
				$this->saveUpdatedAuctions([$auctions->getData()], [], $productIds);
				$auctions->unsetData();
			}

			$result['all_auctions'] = $this->getProfile()->getData( 'profile_all_auctions' );
			$result                 = $this->recalculateProgressData( $result, 1 );

			$this->unsetAuctions();
		} elseif ( $this->getProfileStep() == 3 ) {
			$offerVariants = $response['offerVariants'] ?? false;
			if($offerVariants) {
				foreach($offerVariants as $offerVariant) {
					$this->parseVariantData($offerVariant);
				}
			}
		}

		return $result;
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

	private function prepareProduct( $response ) {
		$product                = [];
		$product['id']          = $response['id'];
		$product['name']        = $response['name'];
		$product['description'] = $this->prepareDescription( $response['description'] );
		$product['stock']       = $response['stock']['available'];
		$product['status']      = $response['publication']['status'];
		$product['images']      = $response['images'];
		$product['categories']  = $response['category']['id'];
		$product['attributes']  = $response['parameters'];
		$product['ean']         = $response['ean'];

		if ( in_array( $response['sellingMode']['format'], [ 'BUY_NOW', 'ADVERTISEMENT' ] ) ) {
			$product['price'] = $response['sellingMode']['price']['amount'];
		} else {
			$startingPrice = (float) ($response['sellingMode']['startingPrice']['amount'] ?? 0);
			$minimalPrice = (float) ($response['sellingMode']['minimalPrice']['amount'] ?? 0);
			$currentPrice = (float) ($response['sellingMode']['price']['amount'] ?? 0);

			$product['price'] = $currentPrice > $minimalPrice ? $currentPrice : $minimalPrice > $startingPrice ? $minimalPrice : $startingPrice > 0.00 ? $startingPrice : null;
		}

		return $product;
	}

	public function prepareDescription( $description ) {
		$gutenbergBlocks = $images = [];

		$content = '<div>';
		foreach ( $description['sections'] as $items ) {
			$content .= '<div class="row">';
			$twoCols = count( $items['items'] ) > 1;
			$class   = $twoCols ? 'col-6' : 'col-12';
			$colNum = 1;
			$imageCount = 0;
			foreach ( $items['items'] as $item ) {
				$isImage = strtolower( $item['type'] ) == 'image';
				if($isImage) {
					$imageCount++;
					$images[] = $item['url'];
				}
				$content .= '<div class="' . $class . '">' . ( $isImage ? '<img src="' . $item['url'] . '" />' : $item['content'] ) . '</div>';


			}
			$content .= '</div>';
		}

		$content .= '</div>';

		return [
			'description' => $content,
			'images' => $images
		];
	}

	public function parseVariantData($variant)
	{
		/** @var GJMAA_Lib_Rest_Api_Sale_Offervariants $api */
		$api = GJMAA::getLib('rest_api_sale_offervariants');
		$api->setSetId($variant['id']);
		$api->setToken($this->getSettings()->getData('setting_client_token'));
		$api->setSandboxMode($this->getSettings()->getData('setting_is_sandbox'));
		$result = $api->execute();

		return $result;
	}
}