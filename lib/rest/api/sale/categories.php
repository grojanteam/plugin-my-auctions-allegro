<?php
require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Sale_Categories extends GJMAA_Lib_Rest_Api_Abstract
{

    protected $categoryId;
    
    protected $parentCategoryId;

    protected $headerAccept = 'application/vnd.allegro.public.v1+json';

    public function getMethod()
    {
        return 'GET';
    }

    public function setParentCategoryId($parentCategoryId)
    {
        $this->parentCategoryId = $parentCategoryId;
        return $this;
    }
    
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function prepareRequest()
    {
        return !$this->parentCategoryId ? [] : ['parent.id' => $this->parentCategoryId];
    }

    public function parseResponse($response)
    {
        return $response;
    }

    public function getUrl()
    {
        $category = !$this->categoryId ? '' : '/' . $this->categoryId;
        return '/sale/categories'.$category;
    }
}
?>