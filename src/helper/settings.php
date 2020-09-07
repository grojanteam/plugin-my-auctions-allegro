<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined( 'ABSPATH' ) or die;

class GJMAA_Helper_Settings {
	protected $webapiClient;

	public function getFieldsData() {
		$fields = [
			'setting_id'            => [
				'id'   => 'setting_id',
				'type' => 'hidden',
				'name' => 'setting_id'
			],
			'setting_name'          => [
				'id'       => 'setting_name',
				'type'     => 'text',
				'name'     => 'setting_name',
				'label'    => 'Name',
				'help'     => __( 'Custom name of your API settings', GJMAA_TEXT_DOMAIN ),
				'required' => true
			],
			'setting_site'          => [
				'id'       => 'setting_site',
				'type'     => 'select',
				'name'     => 'setting_site',
				'label'    => 'Setting Site',
				'source'   => 'allegro_site',
				'help'     => __( 'Choose auction site', GJMAA_TEXT_DOMAIN ),
				'required' => true
			],
			'setting_is_sandbox'    => [
				'id'     => 'setting_is_sandbox',
				'type'   => 'select',
				'name'   => 'setting_is_sandbox',
				'label'  => 'Sandbox Mode',
				'source' => 'yesno',
				'help'   => __( 'Do you want to use test environment?', GJMAA_TEXT_DOMAIN )
			],
			'setting_login'         => [
				'id'       => 'setting_login',
				'type'     => 'text',
				'name'     => 'setting_login',
				'label'    => 'Login',
				'help'     => __( 'Type login that you use', GJMAA_TEXT_DOMAIN ),
				'required' => true
			],
			'setting_password'      => [
				'id'       => 'setting_password',
				'type'     => 'password',
				'name'     => 'setting_password',
				'label'    => 'Password',
				'help'     => __( 'Type here password generated from aukro', GJMAA_TEXT_DOMAIN ),
				'disabled' => true
			],
			'setting_webapi_key'    => [
				'id'       => 'setting_webapi_key',
				'type'     => 'text',
				'name'     => 'setting_webapi_key',
				'label'    => 'WebAPI Key',
				'help'     => __( 'Type here your webapi key from allegro/aukro', GJMAA_TEXT_DOMAIN ),
				'disabled' => true
			],
			'setting_client_id'     => [
				'id'       => 'setting_client_id',
				'type'     => 'text',
				'name'     => 'setting_client_id',
				'label'    => 'Client ID',
				'help'     => __( 'Get Client ID by clicking on &#34;Create App&#34; button', GJMAA_TEXT_DOMAIN ),
				'disabled' => true
			],
			'setting_client_secret' => [
				'id'       => 'setting_client_secret',
				'type'     => 'password',
				'name'     => 'setting_client_secret',
				'label'    => 'Client Secret',
				'help'     => __( 'Get Client Secret by clicking on &#34;Create App&#34; button', GJMAA_TEXT_DOMAIN ),
				'disabled' => true
			],
			'setting_client_token'  => [
				'type'     => 'text',
				'name'     => 'setting_client_token',
				'label'    => 'Token',
				'disabled' => true,
				'help'     => __( 'Get Token by click on &#34;Connect&#34; button, Connect button will show when you fill Client ID and Secret', GJMAA_TEXT_DOMAIN )
			]
		];

		$fields = apply_filters( 'gjmaa_helper_setting_fields', $fields );

		/** @var GJMAA_Service_Woocommerce $wooCommerceService */
		$wooCommerceService = GJMAA::getService( 'woocommerce' );

		if ( $wooCommerceService->isEnabled() ) {
			$fields += [
				'setting_auction_closed' => [
					'id'     => 'setting_auction_closed',
					'type'   => 'select',
					'name'   => 'setting_auction_closed',
					'label'  => __( 'Product update', GJMAA_TEXT_DOMAIN ),
					'source' => 'oosdecision',
					'help'   => __( 'What you want to do, when auction will be closed?', GJMAA_TEXT_DOMAIN )
				]
			];
		}

		$fields += [
			'save' => [
				'type'  => 'submit',
				'name'  => 'save',
				'label' => 'Save'
			]
		];

		return $fields;
	}

	public function isConnectedApi( $data ) {
		if ( ! isset( $data['setting_client_token'] ) || isset( $data['setting_client_token'] ) && is_null( $data['setting_client_token'] ) ) {
			return false;
		}

		return true;
	}

	public function isExpiredToken( $data ) {
		if ( ( strtotime( $data['setting_client_token_expires_at'] ) - 7200 ) < time() ) {
			return true;
		}

		return false;
	}

	public function getWebApiClient() {
		return $this->webapiClient;
	}

	public function checkWebAPIConnection( $data ) {
		/** @var GJMAA_Lib_Webapi $webApiLib */
		$webApiLib = GJMAA::getLib( 'webapi' );
		$webApiLib->setCountry( $data['setting_site'] );
		$webApiLib->connectByLogin( $data['setting_login'], $data['setting_password'], $data['setting_webapi_key'] );
		if ( $error = $webApiLib->getError() ) {
			error_log( $error );

			return false;
		} else {
			$this->webapiClient = $webApiLib;

			return true;
		}
	}

	/**
	 * @param GJMAA_Model_Settings $settings
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isConnected( $settings ) {
		if ( ! $settings ) {
			throw new Exception( __( 'Please, set settings import', GJWA_PRO_TEXT_DOMAIN ) );
		}

		if ( ! $this->isConnectedApi( $settings->getData() ) ) {
			return false;
		}

		if ( $this->isExpiredToken( $settings->getData() ) ) {
			$this->refreshToken( $settings );
		}

		return true;
	}

	/**
	 * @param $settings
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function refreshToken( $settings ) {
		try {
			/** @var GJMAA_Lib_Rest_Api_Auth_Refresh $restLibConnect */
			$restLibConnect = GJMAA::getLib( 'rest_api_auth_refresh' );
			$restLibConnect->setSandboxMode( $settings->getData( 'setting_is_sandbox' ) );
			$restLibConnect->setClientId( $settings->getData( 'setting_client_id' ) );
			$restLibConnect->setClientSecret( $settings->getData( 'setting_client_secret' ) );
			$restLibConnect->settRefreshToken( $settings->getData( 'setting_client_refresh_token' ) );
			$restLibConnect->setRedirectUri( admin_url( 'admin.php?page=gjmaa_settings&action=authorisedCode' ) );
			$response = $restLibConnect->execute();

			$settings->setData( 'setting_client_token', $response['token'] );
			$settings->setData( 'setting_client_token_expires_at', date( 'Y-m-d H:i:s', time() + $response['expiresIn'] ) );
			$settings->setData( 'setting_client_refresh_token', $response['refreshToken'] );
			$settings->save();
		} catch ( Exception $exception ) {
			throw new Exception(__(sprintf('Please refresh manually connection to allegro. Token is expired <a href="%s" target="_blank">click here</a> and refresh token.', admin_url( 'admin.php?page=gjmaa_settings&action=edit&setting_id='.$settings->getId())), GJMAA_TEXT_DOMAIN));
		} 

		return $settings;
	}

	public function getCategoriesFromWebAPI( $settingsModel ) {
		$countryId = $settingsModel->getData( 'setting_site' );

		$isConnected = $this->checkWebAPIConnection( $settingsModel->getData() );
		if ( $isConnected ) {
			$apiCategories = $this->getWebApiClient()->getCategories();
			if ( $apiCategories ) {
				$verKey     = $apiCategories->verKey;
				$currentVer = get_option( 'gjmaa_category_' . $countryId, false );
				if ( ! $currentVer || $currentVer != $verKey ) {
					$categoryTree = [];
					$catsList     = is_array( $apiCategories->catsList->item ) ? $apiCategories->catsList->item : [
						$apiCategories->catsList->item
					];

					foreach ( $catsList as $apiCategory ) {
						if ( ! isset( $categoryTree[ $apiCategory->catId ] ) ) {
							$categoryTree[ $apiCategory->catId ] = [];
						}

						$categoryTree[ $apiCategory->catId ] = [
							'category_id'        => $apiCategory->catId,
							'category_parent_id' => $apiCategory->catParent,
							'name'               => $apiCategory->catName,
							'country_id'         => $countryId
						];
					}

					$categoriesModel = GJMAA::getModel( 'allegro_category' );
					$categoriesModel->saveFullTree( $categoryTree );

					add_option( 'gjmaa_category_' . $countryId, $verKey );
				}
			}
		}
	}

	public function isSandbox( $settingId ) {
		$settings = GJMAA::getModel( 'settings' )->load( $settingId );

		return $settings->getData( 'setting_is_sandbox' );
	}
}