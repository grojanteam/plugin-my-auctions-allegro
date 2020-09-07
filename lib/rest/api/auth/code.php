<?php
require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Auth_Code extends GJMAA_Lib_Rest_Api_Abstract
{

    protected $clientId;

    protected $clientSecret;

    protected $code;
    
    protected $api = false;

    public function prepareRequest()
    {
        if (! $this->getClientId()) {
            throw new Exception(__("Missing client id argument", GJMAA_TEXT_DOMAIN));
        }

        $this->setCurlRequest(false);

        return [
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri()
        ];
    }

    public function getUrl()
    {
        return '/auth/oauth/authorize';
    }

    public function parseResponse($param)
    {
        return;
    }

    public function getMethod()
    {
        return 'GET';
    }
}
?>