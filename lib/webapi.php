<?php

/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Lib_Webapi
{

    const ALLEGRO_PL = 1;

    const AUKRO_CZ = 56;

    protected $webApi = 'fcacc5a4';

    public $session = null;

    protected $client = null;

    protected $version = array();

    public $error = false;

    public $error_mess = '';

    public $error_code = '';
 
    protected $countryCode = self::ALLEGRO_PL;
    
    protected $sandbox = false;

    public $countryDetails = array(
        self::ALLEGRO_PL => array(
            'site' => 'allegro.pl',
            'sandbox' => 'allegro.pl.allegrosandbox.pl',
            'service' => 'service.php?wsdl',
            'currency' => 'PLN',
            'webapi' => 'fcacc5a4'
        ),
        self::AUKRO_CZ => array(
            'site' => 'aukro.cz',
            'sandbox' => 'aukro.cz',
            'service' => '?wsdl',
            'currency' => 'CZK',
            'webapi' => '8160a4e6-ed61-4f71-bf5c-1c9bd674be37'
        )
    );

    public $userLogin = array();

    const random = 0;

    const asc = 1;

    const desc = 2;

    public function getUrl()
    {
        return 'https://webapi.' . $this->countryDetails[$this->getCountry()][$this->getTypeSite()] . '/' . $this->countryDetails[$this->getCountry()]['service'];
    }

    public function prepareClient()
    {
        $this->client = new SoapClient($this->getUrl(),['trace' => 1]);
    }
    
    public function getTypeSite(){
        return $this->isSandbox() ? 'sandbox' : 'site';
    }
    
    public function isSandbox(){
        return $this->getSandbox();
    }
    
    public function setSandbox($sandbox = false){
        $this->sandbox = $sandbox;
        
        return $this;
    }
    
    public function getSandbox(){
        return $this->sandbox;
    }

    public function connectByLogin($login, $password, $webApiKey)
    {
        try {
            $this->prepareClient();

            if ($webApiKey) {
                $this->webApi = $webApiKey;
            }

            $version = $this->client->doQueryAllSysStatus(array(
                'countryId' => $this->getCountry(),
                'webapiKey' => $this->webApi
            ));

            $this->_saveVersion($version);

            $userHashPassword = base64_encode(hash('sha256', $password, true));

            $this->session = $this->client->doLoginEnc([
                'userLogin' => $login,
                'userHashPassword' => $this->getCountry() == self::ALLEGRO_PL ? $password : $userHashPassword,
                'countryCode' => $this->getCountry(),
                'webapiKey' => $this->webApi,
                'localVersion' => $this->version[$this->getCountry()]->verKey
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }

        return $this;
    }

    public function connectByToken($token, $webApiKey = null, $countryCode = self::ALLEGRO_PL)
    {
        try {
            if(! is_null($webApiKey)){
                $this->webApi = $webApiKey;
            }
            
            $this->prepareClient();
            
            $version = $this->client->doQueryAllSysStatus(array(
                'countryId' => $countryCode,
                'webapiKey' => ! is_null($webApiKey) && ! empty($webApiKey) ? $webApiKey : $this->webApi
            ));

            $this->_saveVersion($version);

            $this->session = $this->client->doLoginWithAccessToken([
                'accessToken' => $token,
                'countryCode' => $countryCode,
                'webapiKey' => ! is_null($webApiKey) && ! empty($webApiKey) ? $webApiKey : $this->webApi
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }

        return $this;
    }

    private function _saveVersion($version)
    {
        $versions = is_array($version->sysCountryStatus->item) ? $version->sysCountryStatus->item : [
            $version->sysCountryStatus->item
        ];

        foreach ($versions as $item) {
            $this->version[$item->countryId] = $item;
        }
    }

    public function setCountry($country)
    {
        $this->countryCode = $country;
    }

    public function getCountry()
    {
        return $this->countryCode;
    }

    public function getError()
    {
        return $this->error ? sprintf('[%s] %s', $this->error_code, $this->error_mess) : false;
    }

    public function getUserID($login)
    {
        try {
            $this->error = false;
            
            $user = $this->client->doGetUserID(array(
                'countryId' => $this->countryCode,
                'userLogin' => $login,
                'userEmail' => '',
                'webapiKey' => $this->webApi
            ));
            return $user->userId;
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getUserLogin($userId)
    {
        try {
            $this->error = false;
            $user = $this->client->doGetUserLogin(array(
                'countryId' => $this->countryCode,
                'userId' => $userId,
                'webapiKey' => $this->webApi
            ));
            $this->userLogin[$userId] = $user->userLogin;
            return $this->userLogin[$userId];
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getUserItems($count_of_item, $sort, $filters)
    {
        try {
            $this->error = false;
            $result = array(
                'resultSize' => $count_of_item,
                'resultOffset' => 0
            );
            return $this->doGetSearchItems($filters, $sort, $result);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function doGetMyData($session)
    {
        try {
            $this->error = false;
            return $this->client->doGetMyData($this->session['session-handle-part']);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function doGetSearchItems($filters, $sort = array(), $result = array())
    {
        try {
            $this->error = false;
            
            $requestFilter = $this->_prepareFilters($filters);

            $request = array(
                'webapiKey' => $this->webApi,
                'countryId' => $this->countryCode,
                'filterOptions' => $requestFilter,
                'resultScope' => 3
            );

            if (isset($sort) and ! empty($sort)) {
                $request['sortOptions'] = $sort;
            } else {
                $request['sortOptions'] = array(
                    'sortType' => 'startingTime',
                    'sortOrder' => 'asc'
                );
            }

            if (isset($result) and ! empty($result)) {
                if ($result['resultSize'] > 1000)
                    $result['resultSize'] = 1000;
                $request['resultSize'] = $result['resultSize'];
                $request['resultOffset'] = $result['resultOffset'];
            }
            
            return $this->client->doGetItemsList($request);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    private function _prepareFilters($filters)
    {
        $filtersOrder = array(
            'userId' => 'int',
            'category' => 'int',
            'search' => 'string'
        );
        $requestFilter = array();
        $filterC = 0;

        foreach ($filters as $index => $item) {
            if ($index == 'userId')
                $item = $item === null ? null : (is_int($item) ? $item : $this->getUserID($item));
            if (empty($item))
                continue;
            $requestFilter[$filterC]['filterId'] = $index;
            $requestFilter[$filterC]['filterValueId'][] = $item;
            $filterC ++;
        }

        return $requestFilter;
    }

    public function getPrice($prices, $type = 'buyNow')
    {
        foreach ($prices->item as $index => $item) {
            if ($item->priceType != $type)
                continue;

            return $item->priceValue;
        }

        return 0;
    }

    public function convertSecondsToHumanTime($time)
    {
        $minute = 60;
        $hour = 60 * 60;
        $day = 60 * 60 * 24;

        $humanTime = new stdClass();

        if ($time >= $day) {
            $humanTime->number = number_format(floor($time / $day), 0, '', '');
            $humanTime->period = 'days';
        } elseif ($time < $day and $time >= $hour) {
            $humanTime->number = number_format(floor($time / $hour), 0, '', '');
            $humanTime->period = 'hours';
        } elseif ($time < $hour and $time >= $minute) {
            $humanTime->number = number_format(floor($time / $minute), 0, '', '');
            $humanTime->period = 'minutes';
        } else {
            $humanTime->number = 0;
            $humanTime->period = 'minutes';
        }

        $r1 = $humanTime->number % 100;
        if ($r1 == 1 && $humanTime->number < 100) {
            switch ($humanTime->period) {
                case 'minutes':
                    $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_MINUTE');
                    break;
                case 'hours':
                    $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_HOUR');
                    break;
                case 'days':
                    $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_DAY');
                    break;
            }
        } else {
            $r2 = $r1 % 10;
            if (($r2 > 1 && $r2 < 5) && ($r1 < 12 || $r1 > 14)) {
                switch ($humanTime->period) {
                    case 'minutes':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_MINUTES2');
                        break;
                    case 'hours':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_HOURS2');
                        break;
                    case 'days':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_DAYS');
                        break;
                }
            } else {
                switch ($humanTime->period) {
                    case 'minutes':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_MINUTES');
                        break;
                    case 'hours':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_HOURS');
                        break;
                    case 'days':
                        $time_to_end = $humanTime->number . ' ' . JText::_('MOD_GJALLEGRO_DAYS');
                        break;
                }
            }
        }

        return $time_to_end;
    }

    public function getUserData($login)
    {
        try {
            $this->error = false;
            return $this->client->doShowUser(array(
                'webapiKey' => $this->webApi,
                'countryId' => $this->countryCode,
                'userLogin' => $login
            ));
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function switchTextPoints($rating)
    {
        $text = '';
        $r1 = $rating % 100;
        if ($r1 == 1 && $rating < 100) {
            $text = 'MOD_GJALLEGRO_POINT';
        } else {
            $r2 = $r1 % 10;
            if (($r2 > 1 && $r2 < 5) && ($r1 < 12 || $r1 > 14)) {
                $text = 'MOD_GJALLEGRO_POINTS';
            } else {
                $text = 'MOD_GJALLEGRO_POINTS2';
            }
        }

        return $text;
    }

    public function switchRatingToImage($rating)
    {
        if ($rating > 12500) {
            $class_image = 'blue_star_allegro';
        } elseif ($rating <= 12500 and $rating > 2500) {
            $class_image = 'gold_star_allegro';
        } elseif ($rating <= 2500 and $rating > 250) {
            $class_image = 'silver_star_allegro';
        } elseif ($rating <= 250 and $rating > 50) {
            $class_image = 'brown_star_allegro';
        } elseif ($rating <= 50 and $rating > 5) {
            $class_image = 'white_star_allegro';
        } else {
            $class_image = 'green_leaf_allegro';
        }

        return $class_image;
    }

    public function getCategories($country = null)
    {
        try {
            $this->error = false;
            if ($country)
                $this->setCountry($country);

            return $this->client->doGetCatsData(array(
                'countryId' => $this->getCountry(),
                'localVersion' => null,
                'webapiKey' => $this->webApi
            ));
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getCategoryById($id)
    {
        try {
            $this->error = false;
            return $this->client->doGetCategoryPath(array(
                'sessionId' => $this->session->sessionHandlePart,
                'categoryId' => $id
            ));
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getItemAuction($id_auction)
    {
        $id_auction = is_array($id_auction) ? $id_auction : [
            $id_auction
        ];
        
        foreach($id_auction as $index => $data){
            $id_auction[$index] = (float)$data;
        }
        
        try {
            $this->error = false;
            $request = array(
                'sessionHandle' => $this->session->sessionHandlePart,
                'itemsIdArray' => $id_auction,
                'getImageUrl' => 1,
                'getDesc' => 1,
                'getAttribs' => 1,
                'getPostageOptions' => 1,
                'getCompanyInfo' => 1
            );
            return $this->client->doGetItemsInfo($request);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getMySellRatings()
    {
        try {
            $this->error = false;
            $request = array(
                'sessionHandle' => $this->session->sessionHandlePart
            );
            return $this->client->doGetMySellRating($request);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getFeedback($user_id)
    {
        try {
            $this->error = false;
            $request = array(
                'sessionHandle' => $this->session->sessionHandlePart,
                'feedbackFrom' => 0,
                'feedbackTo' => $user_id
            );
            return $this->client->doGetFeedback($request);
        } catch (SoapFault $error) {
            $this->error = true;
            $this->error_mess = $error->getMessage();
            $this->error_code = $error->faultcode;
        }
    }

    public function getAllImages($images)
    {
        $convertedImages = array();
        foreach ($images as $image) {
            if ($image->photoIsMain) {
                $convertedImages[$image->photoSize] = $image->photoUrl;
            }
        }

        return $convertedImages;
    }
}