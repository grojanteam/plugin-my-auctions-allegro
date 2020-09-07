<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Profiles extends GJMAA_Source {
    public function getOptions($param = null) {
		$model = GJMAA::getModel('profiles');
		$allProfiles = $model->getAll();
		
		$result = [];
		foreach($allProfiles as $profile){
			$result[$profile[$model->getDefaultPk()]] = $profile['profile_name'];
		}
		
		return $result;
	}
}

?>