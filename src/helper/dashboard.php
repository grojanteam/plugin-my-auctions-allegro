<?php 
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Dashboard {

	public function isCompatibleWordpressVersion(){
		$wp_version = $this->getWordPressVersion();
		
		return $this->compareVersion($wp_version,'5.0');
	}
	
	public function isCompatiblePHPVersion(){
		$phpVersion = phpversion();
		
		return $this->compareVersion($phpVersion, '7.2');
	}
	
	public function getWordPressVersion(){
		global $wp_version;
		return $wp_version;
	}
	
	public function isCurlEnabled(){
		return function_exists('curl_version');
	}
	
	public function isPhpFopenEnable() {
	    return ini_get('allow_url_fopen') == 1 ? true : false;
	}
	
	public function compareVersion($current,$min){
		return version_compare($current,$min,'>=');
	}
	
	public function getAllProfileErrors(){
	    $profiles = GJMAA::getModel('profiles');
	    return $profiles->getAllProfileErrors();
	}

	public function checkForNotConnectedAccounts()
	{
		/** @var GJMAA_Helper_Settings $helper */
		$helper = GJMAA::getHelper('settings');

		/** @var GJMAA_Model_Settings $model */
		$model = GJMAA::getModel('settings');

		$ids = $model->getAllIds();

		$notConnected = [];

		foreach($ids as $id) {
			$model->unsetData();
			$settings = $model->load($id);

			if($helper->isConnected($settings)) {
				continue;
			}

			if($helper->checkWebAPIConnection($settings->getData())) {
				continue;
			}

			$notConnected[] = $id;
		}

		return $notConnected;
	}
}

?>