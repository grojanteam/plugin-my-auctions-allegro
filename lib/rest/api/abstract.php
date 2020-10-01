<?php

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

abstract class GJMAA_Lib_Rest_Api_Abstract
{

    const LIVE_URL = 'https://allegro.pl';

    const API_LIVE_URL = 'https://api.allegro.pl';
    
    const UPLOAD_LIVE_URL = 'https://upload.allegro.pl';

    const SANDBOX_URL = 'https://allegro.pl.allegrosandbox.pl';

    const API_SANDBOX_URL = 'https://api.allegro.pl.allegrosandbox.pl';
    
    const UPLOAD_SANDBOX_URL = 'https://upload.allegro.pl.allegrosandbox.pl';

    const REDIRECT_URI = '/';

    protected $api = true;

    protected $sandbox = false;

    protected $curl_request = true;

    protected $json = false;
    
    protected $upload = false;

    protected $redirectUri;

    protected $token;

    protected $error = false;

    protected $error_messages = [];

    protected $headerAccept = null;
    
    protected $contentType = null;
    
    protected $clientId;
    
    protected $clientSecret;

    abstract public function prepareRequest();

    abstract public function parseResponse($response);

    abstract public function getUrl();

    abstract public function getMethod();

    /**
     * check that request will be send to sandbox environment
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return (bool) $this->sandbox;
    }

    /**
     * enable sandbox mode
     *
     * @param bool $sandbox
     */
    public function setSandboxMode($sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * set token for api requests
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * get token for requests
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    public function setCurlRequest($curl_request)
    {
        $this->curl_request = $curl_request;
        return $this;
    }

    public function getCurlRequest()
    {
        return $this->curl_request;
    }

    /**
     * run api processing
     */
    public function execute()
    {
        $baseUrl = $this->isSandbox() ? self::API_SANDBOX_URL : self::API_LIVE_URL;

        if (! $this->api) {
            $baseUrl = $this->isSandbox() ? self::SANDBOX_URL : self::LIVE_URL;
        }
        
        if($this->upload){
            $baseUrl = $this->isSandbox() ? self::UPLOAD_SANDBOX_URL : self::UPLOAD_LIVE_URL;
        }

        $url = $baseUrl . $this->getUrl();

        $request = $this->prepareRequest();

        if (! $this->getCurlRequest()) {
            return $url . '?' . http_build_query($request);
        }

        return $this->sendRequest($url, $request);
    }

    /**
     * just send request
     *
     * @param string $url
     * @param array $request
     */
    public function sendRequest($url, $request)
    {
        $opts = [
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . ($this->getToken() ? 'Bearer ' . $this->getToken() : 'Basic ' . $this->getAuthorizationBasic())
            ],
            CURLOPT_RETURNTRANSFER => true
        ];

        if ($this->headerAccept) {
            $opts[CURLOPT_HTTPHEADER][] = sprintf('Accept: %s', $this->headerAccept);
            $opts[CURLOPT_HTTPHEADER][] = sprintf('Content-Type: %s', ($this->contentType ? : $this->headerAccept));
            $opts[CURLOPT_HTTPHEADER][] = 'Accept-Language: pl-PL';
        }
        
        if(!$this->headerAccept && $this->contentType) {
            $opts[CURLOPT_HTTPHEADER][] = sprintf('Content-Type: %s', $this->contentType);
        }

        if ($this->getMethod() == 'GET') {
            if (! empty($request)) {
                $url .= '?' . http_build_query($request);
            }
        } else {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $this->json ? json_encode($request) : $request;
        }

	    $opts[CURLOPT_SSL_VERIFYHOST] = false;
	    $opts[CURLOPT_SSL_VERIFYPEER] = false;

        if ($this->getMethod() == 'PUT') {
            $opts[CURLOPT_CUSTOMREQUEST] = "PUT";
        }

        $opts[CURLOPT_TIMEOUT] = 30;
        
        $ch = curl_init($url);

        // set request data to send
        curl_setopt_array($ch, $opts);

        // execute request
        $response = curl_exec($ch);

        $result = $this->parseResult($response);

        // get information about response
        $requestInfo = curl_getinfo($ch);

        // close curl request
        curl_close($ch);
        switch ($requestInfo['http_code']) {
            case 200:
            case 201:
                break;
            case 400:
            case 401:
            case 403:
            case 404:
            case 422:
                return $this->parseError($result, $url);
                break;
            default:
                throw new Exception(sprintf(__('Unexpected error: %s for url %s and method %s', GJMAA_TEXT_DOMAIN), implode(PHP_EOL, $this->error_messages), $url, $this->getMethod()));
        }

        return $this->parseResponse($result);
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getAuthorizationBasic()
    {
        return base64_encode($this->getClientId() . ':' . $this->getClientSecret());
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function parseResult($result)
    {
        $json = json_decode($result, true);

        if (isset($json['errors'])) {
            foreach ($json['errors'] as $error) {
                $this->error = true;
                $this->error_messages[] = $error['code'] . ': ' . $error['path'] . ' ' . $error['userMessage'];
            }
        }

        return $json;
    }
    
    public function parseError($result, $url = '/')
    {
        throw new Exception(sprintf(__('Unexpected error: %s for url %s and method %s', GJMAA_TEXT_DOMAIN), implode(PHP_EOL, $this->error_messages), $url, $this->getMethod()));
    }
}

?>