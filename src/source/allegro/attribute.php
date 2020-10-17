<?php
declare(strict_types=1);

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Attribute extends GJMAA_Source
{
	protected $settings;

	public function getOptions( $param = null ) {
		$categoryId = $param['category_id'] ?? null;
		$countryId  = $param['country_id'] ?? 1;

		/** @var GJMAA_Model_Allegro_Attribute $model */
		$model = GJMAA::getModel('allegro_attribute');
		$result = $model->loadByCategoryId($categoryId);
		if(empty($result)) {
			$this->getAttributes($categoryId, $countryId);
			$result = $model->loadByCategoryId($categoryId);
		}

		foreach ( $result as $attributeId => $attribute ) {
			$result[ $attribute['attribute_allegro_id'] ] = $attribute;
		}

		return $result;
	}

	public function getAttributes( $categoryId, $countryId = 1 ) {
		$result = [];

		if ( ! $categoryId ) {
			return $result;
		}

		if ( $countryId == 1 ) {
			$result = $this->getAttributesFromAPI( $categoryId, $countryId );
		}

		return $result;
	}

	public function getAttributesFromAPI( $categoryId, $countryId = 1 ) {
		/** @var GJMAA_Service_Attribute $attributeService */
		$attributeService = GJMAA::getService( 'attribute' );

		$attributeService->setOptions( [
			'category_id' => $categoryId
		] );

		if ( $settings = $this->getSettings() ) {
			$response = $attributeService->execute((int) $settings->getId());
		} else {
			$response = [];
		}

		return $response;
	}

	public function setSettings( $settings ) {
		$this->settings = $settings;

		return $this;
	}

	public function getSettings() {
		return $this->settings;
	}
}