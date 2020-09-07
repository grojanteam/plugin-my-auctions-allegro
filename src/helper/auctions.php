<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Auctions
{

    protected $model;

    protected $auctions = [];

    public function getCountOfAllAuctions()
    {
        return $this->getModel()->getCountAll();
    }

    public function getCountOfTotalAuctionClicks()
    {
        return $this->getModel()->getCountOfTotalAuctionClicks();
    }

    public function getCountOfTotalAuctionVisits()
    {
        return $this->getModel()->getCountOfTotalAuctionVisits();
    }

    public function getMostPopularAuctions($count = 5)
    {
        return $this->getModel(true)->getMostPopularAuctions($count);
    }

    public function getNewestAuctions($count = 5)
    {
        return $this->getModel(true)->getNewestAuctions($count);
    }

    public function getLastMinuteAuctions($count = 5)
    {
        return $this->getModel(true)->getLastMinuteAuctions($count);
    }

    public function getLowStockAuctions($count = 5)
    {
        return $this->getModel(true)->getLowStockAuctions($count);
    }

    public function getModel($newInstance = false)
    {
        if (! $this->model || $newInstance) {
            $this->model = GJMAA::getModel('auctions');
        }

        return $this->model;
    }

    public function getFieldsData($type = 'form')
    {
        return [
            'id' => [
                'type' => 'hidden',
                'name' => 'id'
            ],
            'auction_id' => [
                'type' => 'hidden',
                'name' => 'auction_id'
            ],
            'auction_profile_id' => [
                'type' => 'select',
                'name' => 'auction_profile_id',
                'label' => 'Profile',
                'source' => 'profiles'
            ],
            'auction_name' => [
                'type' => 'text',
                'name' => 'auction_name',
                'label' => 'Name'
            ],
            'auction_price' => [
                'type' => 'text',
                'name' => 'auction_price',
                'label' => 'Price (buy now)'
            ],
            'auction_bid_price' => [
                'type' => 'text',
                'name' => 'auction_bid_price',
                'label' => 'Price (bid)'
            ],
            'auction_quantity' => [
                'type' => 'text',
                'name' => 'auction_quantity',
                'label' => 'Quantity'
            ],
            'auction_categories' => [
                'type' => 'select',
                'name' => 'auction_categories',
                'label' => 'Category',
                'source' => $this->getSourceByType($type)
            ],
            'auction_time' => [
                'type' => 'text',
                'name' => 'auction_time',
                'label' => 'Time'
            ],
            'auction_seller' => [
                'type' => 'text',
                'name' => 'auction_seller',
                'label' => 'Seller'
            ],
            'auction_clicks' => [
                'type' => 'text',
                'name' => 'auction_clicks',
                'label' => 'Clicks'
            ],
            'auction_visits' => [
                'type' => 'text',
                'name' => 'auction_visits',
                'label' => 'Visits'
            ],
            'auction_status' => [
                'type' => 'select',
                'name' => 'auction_status',
                'label' => 'Status',
                'source' => 'allegro_offerstatus'
            ],
            'auction_in_woocommerce' => [
                'type' => 'select',
                'name' => 'auction_in_woocommerce',
                'label' => 'WooCommerce?',
                'source' => 'yesnoskip'
            ],
            'save' => [
                'type' => 'submit',
                'name' => 'save',
                'label' => 'Save'
            ]
        ];
    }

    public function getAuctionUrl($auctionId)
    {
        $auction = $this->getAuction($auctionId);

        if (! $auction->getId()) {
            throw new Exception(__('Auction does not exist', GJMAA_TEXT_DOMAIN));
        }

        $site = $this->getSettingSiteId($auction->getData('auction_profile_id'));
        $id = $this->getSettingId($auction->getData('auction_profile_id'));

        $source = GJMAA::getSource('allegro_site');
        $optionSource = $source->getOptions();

        /** @var GJMAA_Helper_Settings $settings */
        $settings = GJMAA::getHelper('settings');
        $isSandbox = $settings->isSandbox($id);

        return sprintf('https://%s/show_item.php?item=%s', $optionSource[$site] . ($isSandbox ? '.allegrosandbox.pl' : ''), $auction->getData('auction_id'));
    }

    public function getAuctionPrice($auctionId)
    {
        $auction = $this->getAuction($auctionId);

        $site = $this->getSettingSiteId($auction->getData('auction_profile_id'));

        $source = GJMAA::getSource('allegro_currency');
        $currencyOptions = $source->getOptions();
        
        $bidPrice = $auction->getData('auction_bid_price');
        
        if($bidPrice === '0.00')
        {
            $bidPrice = null;
        }
        
        return (!is_null($bidPrice) ? $bidPrice : $auction->getData('auction_price')) . $currencyOptions[$site];
    }
    
    public function getAuctionTime($auctionId) {
        $auction = $this->getAuction($auctionId);
        
        return $auction->getData('auction_time') ? $this->convertSecondsToHumanTime($auction->getData('auction_time')) : __('No time limit',GJMAA_TEXT_DOMAIN);
    }

    public function getSettingSiteId($profileId)
    {
        $profile = GJMAA::getModel('profiles');
        $profile->load($profileId);

        $settings = GJMAA::getModel('settings');
        $settings->load($profile->getData('profile_setting_id'));

        return $settings->getData('setting_site');
    }
    
    public function getSettingId($profileId)
    {
        $profile = GJMAA::getModel('profiles');
        $profile->load($profileId);
        
        $settings = GJMAA::getModel('settings');
        $settings->load($profile->getData('profile_setting_id'));
        
        return $settings->getData('setting_id');
    }

    public function getAuction($auctionId)
    {
        if (! isset($this->auctions[$auctionId])) {
            $auction = GJMAA::getModel('auctions');
            $this->auctions[$auctionId] = $auction->load($auctionId);
        }

        return $this->auctions[$auctionId];
    }

    public function getSourceByType($type)
    {
        switch ($type) {
            case 'table':
                return 'allegro_category_tree';
            default:
                return 'allegro_category';
        }
    }
    
    public function convertSecondsToHumanTime($time)
    {
        $time = strtotime($time) - time();
        
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
                    $time_to_end = $humanTime->number . ' ' . __('minute',GJMAA_TEXT_DOMAIN);
                    break;
                case 'hours':
                    $time_to_end = $humanTime->number . ' ' . __('hour', GJMAA_TEXT_DOMAIN);
                    break;
                case 'days':
                    $time_to_end = $humanTime->number . ' ' . __('day',GJMAA_TEXT_DOMAIN);
                    break;
            }
        } else {
            $r2 = $r1 % 10;
            if (($r2 > 1 && $r2 < 5) && ($r1 < 12 || $r1 > 14)) {
                switch ($humanTime->period) {
                    case 'minutes':
                        $time_to_end = $humanTime->number . ' ' . __('minutes',GJMAA_TEXT_DOMAIN);
                        break;
                    case 'hours':
                        $time_to_end = $humanTime->number . ' ' . __('hours',GJMAA_TEXT_DOMAIN);
                        break;
                    case 'days':
                        $time_to_end = $humanTime->number . ' ' . __('days',GJMAA_TEXT_DOMAIN);
                        break;
                }
            } else {
                switch ($humanTime->period) {
                    case 'minutes':
                        $time_to_end = $humanTime->number . ' ' . __('minutes', GJMAA_TEXT_DOMAIN);
                        break;
                    case 'hours':
                        $time_to_end = $humanTime->number . ' ' . __('hours',GJMAA_TEXT_DOMAIN);
                        break;
                    case 'days':
                        $time_to_end = $humanTime->number . ' ' . __('days',GJMAA_TEXT_DOMAIN);
                        break;
                }
            }
        }
        
        return $time_to_end;
    }
}

?>