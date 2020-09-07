<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Service_Categories
{

    protected $settings;

    protected $client;

    public function makeRequest()
    {
        if (! $this->client) {
            $api = GJMAA::getLib('rest_api_sale_categories');
            $this->client = $api;
        }
    }

    public function sendRequest()
    {
        $this->client->setToken($this->getSettings()
            ->getData('setting_client_token'));
        $this->client->setSandboxMode($this->getSettings()
            ->getData('setting_is_sandbox'));
        return $this->client->execute();
    }

    public function getCategoryByIdOrParentId($categoryId = null, $parentCategoryId = null)
    {
        if ($this->getSettings()->getData('setting_site') == 1) {
            if ($this->connect()) {
                $this->makeRequest();
                $this->client->setCategoryId($categoryId);
                $this->client->setParentCategoryId($parentCategoryId);
                $response = $this->sendRequest();
                return $this->parseResponse($response);
            }

            throw new Exception(__('Please check connection with REST API', GJMAA_TEXT_DOMAIN));
        } else {
            $settingsHelper = GJMAA::getHelper('settings');
            $settingsHelper->getCategoriesFromWebAPI($this->getSettings());
        }
        return [];
    }

    public function getFullTreeForCategory($categoryId)
    {
        $tree = [];
        $categories = $this->getCategoryByIdOrParentId($categoryId);
        $tree[] = $categories[0];
        if ($categories[0]['category_parent_id']) {
            do {
                $categories = $this->getCategoryByIdOrParentId($categories[0]['category_parent_id']);
                $tree[] = $categories[0];
            } while ($categories[0]['category_parent_id'] != 0);
        }

        return $tree;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function connect()
    {
        $settings = $this->getSettings();
        if (! $settings) {
            return false;
        }

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

    public function refreshToken($settings)
    {
	    /** @var GJMAA_Helper_Settings $helper */
	    $helper = GJMAA::getHelper('settings');

	    return $helper->refreshToken($settings);
    }

    public function parseResponse($response)
    {
        $allegroCategories = isset($response['categories']) ? $response['categories'] : [
            $response
        ];
        $categories = [];
        foreach ($allegroCategories as $category) {
            $categories[] = [
                'category_id' => $category['id'],
                'category_parent_id' => $category['parent']['id'] ?: 0,
                'name' => $category['name'],
                'country_id' => $this->getSettings()->getData('setting_site'),
                'leaf' => $category['leaf'] ? 1 : 0
            ];
        }
        return $categories;
    }
}
?>