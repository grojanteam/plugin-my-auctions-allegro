<?php
require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Listing_Search extends GJMAA_Lib_Rest_Api_Abstract
{

    protected $request = [
        'include' => 'all',
        'fallback' => 'false'
    ];

    protected $headerAccept = 'application/vnd.allegro.public.v1+json';

    public function getMethod()
    {
        return 'GET';
    }

    public function setCategory($category)
    {
        $this->request['category.id'] = $category;
    }

    public function setSeller($seller)
    {
        $this->request['seller.id'] = $seller;
    }

    public function setQuery($query)
    {
        $this->request['phrase'] = $query;
    }

    public function setOffset($offset = 0)
    {
        $this->request['offset'] = $offset;
    }

    public function setLimit($limit = 100)
    {
        $this->request['limit'] = $limit;
    }

    public function setSearchMode($searchMode = 'REGULAR')
    {
        $this->request['searchMode'] = $searchMode;
    }

    public function setSort($sort)
    {
        list($sortName, $sortOrder) = explode('_',$sort);
        
        switch($sortName){
            case 'startingTime':
                $sortName = 'startTime';
                break;
            case 'endingTime':
                $sortName = 'endTime';
                break;
            case 'deliveryPrice':
                $sortName = 'withDeliveryPrice';
                break;
        }
        
        $restSort = $sortOrder == 'asc' ? '+' : '-';
        $restSort .= $sortName;
        
        $this->request['sort'] = $restSort;
    }

    public function prepareRequest()
    {
        if (! $this->request) {
            throw new Exception('Missing required fields');
        }

        return $this->request;
    }

    public function parseResponse($response)
    {
        return $response;
    }

    public function getUrl()
    {
        return '/offers/listing';
    }
}
?>