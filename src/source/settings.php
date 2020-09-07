<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Settings extends GJMAA_Source {
    public function getOptions($param = null) {
		$model = GJMAA::getModel('settings');
		$allSettings = $model->getAll();
		
		$result = [];
		foreach($allSettings as $setting){
			$result[$setting['setting_id']] = $setting['setting_name'];
		}
		
		return $result;
	}
}

?>