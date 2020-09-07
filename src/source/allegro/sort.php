<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Sort extends GJMAA_Source 
{
    public function getOptions($param = null) 
	{
		return [
		        'relevance' => __( 'Relevance',GJMAA_TEXT_DOMAIN),
				'endTime_asc' => __ ( 'Time to end of auction (Ascending)', GJMAA_TEXT_DOMAIN ),
				'endTime_desc' => __ ( 'Time to end of auction (Descending)', GJMAA_TEXT_DOMAIN ),
				'popularity_asc' => __ ( 'Count of offers (Ascending)', GJMAA_TEXT_DOMAIN ),
				'popularity_desc' => __ ( 'Count of offers (Descending)', GJMAA_TEXT_DOMAIN ),
				'price_asc' => __ ( 'Current price (Ascending)', GJMAA_TEXT_DOMAIN ),
				'price_desc' => __ ( 'Current price (Descending)', GJMAA_TEXT_DOMAIN ),
				'name_asc' => __ ( 'Name of auction (Ascending)', GJMAA_TEXT_DOMAIN ),
				'name_desc' => __ ( 'Name of auction (Descending)', GJMAA_TEXT_DOMAIN ),
				'startTime_asc' => __ ( 'Time of auction create (Ascending)', GJMAA_TEXT_DOMAIN ),
				'startTime_desc' => __ ( 'Time of auction create (Descending)', GJMAA_TEXT_DOMAIN ),
				'withDeliveryPrice_asc' => __ ( 'Price with delivery (Ascending)', GJMAA_TEXT_DOMAIN ),
				'withDeliveryPrice_desc' => __ ( 'Price with delivery (Descending)', GJMAA_TEXT_DOMAIN )
		];
	}
}

?>