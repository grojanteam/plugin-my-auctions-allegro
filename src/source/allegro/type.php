<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Type extends GJMAA_Source
{

    public function getOptions($param = null)
    {
        return [
            'my_auctions' => __('My auctions', GJMAA_TEXT_DOMAIN),
            'search' => __('Search', GJMAA_TEXT_DOMAIN),
            'auctions_of_user' => __('Auctions of user', GJMAA_TEXT_DOMAIN)
        ];
    }
}

?>