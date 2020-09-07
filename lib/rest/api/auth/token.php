<?php
require_once __DIR__ . '/../abstract.php';

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Rest_Api_Auth_Token extends GJMAA_Lib_Rest_Api_Abstract
{

    protected $clientId;

    protected $clientSecret;

    protected $code;
    
    protected $api = false;

    public function prepareRequest()
    {
        if (! $this->getClientId() && ! $this->getClientSecret() && ! $this->getCode()) {
            throw new Exception(sprintf(__("Missing arguments: %s or %s or %s", GJMAA_TEXT_DOMAIN), __('Client ID', GJMAA_TEXT_DOMAIN), __('Client Secret', GJMAA_TEXT_DOMAIN), __('Authorization Code', GJMAA_TEXT_DOMAIN)));
        }

        return [
            'code' => $this->getCode(),
            'grant_type' => $this->getGrantType(),
            'redirect_uri' => $this->getRedirectUri()
        ];
    }

    public function parseResponse($response)
    {
        $token = $response['access_token'];
        $expires_in = $response['expires_in'];
        $refreshToken = $response['refresh_token'];

        return [
            'token' => $token,
            'refreshToken' => $refreshToken,
            'expiresIn' => $expires_in
        ];
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getUrl()
    {
        return '/auth/oauth/token';
    }

    public function getGrantType()
    {
        return 'authorization_code';
    }

    public function getMethod()
    {
        return 'POST';
    }
}
?>