<?php
/**
 * My auctions allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */
defined('ABSPATH') or die();

require_once 'abstract.php';

class GJMAA_Service_Attribute extends GJMAA_Service_Abstract
{

	protected $options;

	public function setOptions($options = [])
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getOption($option = null)
	{
		return isset($this->options[$option]) ? $this->options[$option] : false;
	}

	public function execute(int $settingsId)
	{
		$this->setSettings($settingsId);

		$settings = $this->connect($settingsId);

		try {
			/** @var GJMAA_Lib_Rest_Api_Sale_Categories_Parameters $restApiCategoryParamters */
			$restApiCategoryParamters = GJMAA::getLib( 'rest_api_sale_categories_parameters' );
			$restApiCategoryParamters->setSandboxMode( $settings->getData( 'setting_is_sandbox' ) );
			$restApiCategoryParamters->setToken( $settings->getData( 'setting_client_token' ) );
			if ( $categoryId = $this->getOption( 'category_id' ) ) {
				$restApiCategoryParamters->setCategoryId( $categoryId );
			}

			$result = $restApiCategoryParamters->execute();
		} catch (Exception $exception) {
			error_log($exception->getMessage());
			$result = [];
		}

		return $this->parseResult($result);
	}

	public function parseResult($result)
	{
		/** @var GJMAA_Model_Allegro_Attribute $model */
		$model = GJMAA::getModel('allegro_attribute');
		$prepareForSave = [];
		foreach($result as $id => $attributeData) {
			$row = $model->loadByAttributeAndCategory($attributeData['id'], $this->getOption('category_id'));
			$prepareForSave[] = [
				'attribute_id' => $row->getId(),
				'attribute_category_allegro_id' => $this->getOption('category_id'),
				'attribute_allegro_id' => $attributeData['id'],
				'attribute_name' => sprintf('%s %s', $attributeData['name'], (is_null($attributeData['unit']) ? '' : '['.$attributeData['unit'].']')),
				'attribute_type' => $attributeData['type'],
				'attribute_required' => $attributeData['required'],
				'attribute_options' => json_encode($attributeData['options']),
				'attribute_dictionary' => json_encode($attributeData['dictionary'] ?? ''),
				'attribute_restrictions' => json_encode($attributeData['restrictions'])
			];
		}

		$model->saveMultiple($prepareForSave);
		$model->unsetData();

		$result = $model->loadByCategoryId($this->getOption('category_id'));

		return $result;
	}
}