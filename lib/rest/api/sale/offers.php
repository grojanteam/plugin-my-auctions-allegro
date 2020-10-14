<?php
require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Sale_Offers extends GJMAA_Lib_Rest_Api_Abstract
{
    
    protected $request;

    protected $auctionId;

    protected $headerAccept = 'application/vnd.allegro.public.v1+json';

    public function getMethod()
    {
        return 'GET';
    }

    public function setAuctionId($auctionId)
    {
        $this->auctionId = $auctionId;
        return $this;
    }

    public function prepareRequest()
    {
        return $this->request;
    }

    public function parseResponse($response)
    {
        return $response;
    }

    public function getUrl()
    {
        if(!$this->auctionId){
            $this->request['publication.status'] = [
                'ACTIVE',
                'ENDED'
            ];
            return '/sale/offers';
        }
        
        return '/sale/offers/'.$this->auctionId;
    }
    
    public function setOffset($offset = 0)
    {
        $this->request['offset'] = $offset;
    }
    
    public function setLimit($limit = 20)
    {
        $this->request['limit'] = $limit;
    }

    public function setCategoryId($categoryId)
    {
    	$this->request['category.id'] = $categoryId;
    }
    
    public function setSort($sort)
    {
        $sortData = explode('_',$sort);
        
        if(count($sortData) > 1){
            list($sortName, $sortOrder) = $sortData;
        } else {
            $sortName = $sortData[0];
        }
        
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
}
?>