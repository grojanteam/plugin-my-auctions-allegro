<?php
/**
 * My auctions allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */
defined('ABSPATH') or die();

require_once GJMAA_PATH . 'lib/rest/api/abstract.php';

class GJMAA_Lib_Rest_Api_Sale_Categories_Parameters extends GJMAA_Lib_Rest_Api_Abstract
{

    protected $headerAccept = 'application/vnd.allegro.public.v1+json';

    protected $categoryId;

    public function getUrl()
    {
        return sprintf('/sale/categories/%s/parameters', $this->getCategoryId());
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function prepareRequest()
    {
        return [];
    }

    public function parseResponse($response)
    {
        $parameters = [];

        foreach ($response['parameters'] as $parameter) {
            $parameters['attribute_' . $parameter['id']] = $parameter;
        }

        return $parameters;
    }

    public function getMethod()
    {
        return 'GET';
    }
}