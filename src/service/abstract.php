<?php
declare(strict_types=1);

/**
 * My auctions allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */
defined('ABSPATH') or die();

abstract class GJMAA_Service_Abstract
{

	protected $settings = [];

	/**
	 * set account connected to get data
	 *
	 * @param int $settingsId
	 * @return GJWA_PRO_Service_Allegro_Abstract
	 */
	public function setSettings(int $settingsId)
	{
		if(!isset($this->settings[$settingsId])) {
			$settingsModel = GJMAA::getModel('settings');
			$settings = $settingsModel->load($settingsId);

			if(!$settings->getId()) {
				throw new RuntimeException(__('Provided account does not exist'));
			}

			$this->settings[$settingsId] = $settings;
		}

		return $this;
	}

	public function unsetSettings(int $settingsId)
	{
		if(isset($this->settings[$settingsId])) {
			unset($this->settings[$settingsId]);
		}
	}

	/**
	 * get connected account
	 *
	 * @param int $settingsId
	 * @return null|GJMAA_Model_Settings
	 */
	public function getSettings(int $settingsId) : ?GJMAA_Model_Settings
	{
		return $this->settings[$settingsId] ?? null;
	}

	/**
	 * method check connection between WP and Allegro REST API
	 *
	 * if any account is connected it will refresh token after expire
	 *
	 * @param int $settingsId
	 * @throws Exception
	 * @return GJMAA_Model_Settings
	 */
	public function connect(int $settingsId)
	{
		$settings = $this->getSettings($settingsId);
		if (! $settings) {
			throw new RuntimeException(__('Your account is not set or does not exist anymore in plugin settings', GJWA_PRO_TEXT_DOMAIN));
		}

		if ($settings->getData('setting_site') != 1) {
			throw new RuntimeException(__('This service isn\'t supported for other sites.', GJWA_PRO_TEXT_DOMAIN));
		}

		/** @var GJMAA_Helper_Settings $helper */
		$helper = GJMAA::getHelper('settings');
		$settingsData = $settings->getData();

		if (! $helper->isConnectedApi($settingsData)) {
			throw new RuntimeException(__('Your account are not set up properly', GJWA_PRO_TEXT_DOMAIN));
		}

		if ($helper->isExpiredToken($settingsData)) {
			$this->refreshToken($settings);
			$this->unsetSettings($settingsId);
			$this->setSettings($settingsId);
		}

		return $settings;
	}

	/**
	 * method using to refresh expired token
	 *
	 * @param GJMAA_Model_Settings $settings
	 */
	public function refreshToken(GJMAA_Model_Settings  $settings)
	{
		/** @var GJMAA_Helper_Settings $helper */
		$helper = GJMAA::getHelper('settings');
		$helper->refreshToken($settings);
	}

	public function getUserId(int $settingsId)
	{
		$userToken = $this->getSettings($settingsId)->getData('setting_client_token');
		$tokenSplit = explode('.',$userToken);

		$decodedUserData = isset($tokenSplit[1]) ? json_decode(base64_decode($tokenSplit[1]),true) : [];

		return isset($decodedUserData['user_name']) ? $decodedUserData['user_name'] : null;
	}

	abstract public function execute(int $settingsId);
}