<?php
declare(strict_types=1);

require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Sale_Offervariants extends GJMAA_Lib_Rest_Api_Abstract
{
	protected $setId;

	protected $request;

	protected $headerAccept = 'application/vnd.allegro.public.v1+json';

	public function getMethod()
	{
		return 'GET';
	}

	public function setSetId($setId)
	{
		$this->setId = $setId;

		return $this;
	}

	public function getSetId()
	{
		return $this->setId;
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
		if(!$this->getSetId()){
			return '/sale/offer-variants';
		}

		return '/sale/offer-variants/'.$this->getSetId();
	}

	public function setOffset($offset = 0)
	{
		$this->request['offset'] = $offset;
	}

	public function setLimit($limit = 50)
	{
		$this->request['limit'] = $limit;
	}
}