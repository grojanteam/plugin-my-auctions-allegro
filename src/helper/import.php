<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Helper_Import
{

    public function getFieldsData()
    {
        return [
            'controller' => [
                'type' => 'hidden',
                'name' => 'controller',
                'value' => 'import'
            ], 
            'action' => [
                'type' => 'hidden',
                'name' => 'action',
                'value' => 'gjmaa_import_action'
            ],
            'nonce' => [
                'type' => 'hidden',
                'name' => 'nonce',
                'value' => wp_create_nonce('import_action')
            ],
            'profile_id' => [
                'id' => 'profile_id',
                'type' => 'select',
                'name' => 'profile_id',
                'label' => 'Profile',
                'source' => 'profiles'
            ],
            'import' => [
                'id' => 'submit_import',
                'type' => 'submit',
                'name' => 'import',
                'label' => 'Import'
            ]
        ];
    }

    public function runImportByProfileId($profileId, $type = 'user')
    {
        $profile = GJMAA::getModel('profiles');
        $profile->load($profileId);

        if ($type == 'cron') {
            if ($profile->getData('profile_import_step') == 2) {
                $profile->setData('profile_import_step', 1);
                $profile->setData('profile_imported_auctions', 0);
            }
        }

        if (! $profile->getId()) {
            $errorMessage = __('Selected profile does not exist.', GJMAA_TEXT_DOMAIN);
            if($type != 'cron'){
                return $this->sendErrorJsonResponse([
                    'error_message' => $errorMessage
                ]);
            }
            error_log($errorMessage);
            return;
        }

        $settingId = $profile->getData('profile_setting_id');
        /** @var GJMAA_Model_Settings $settingsModel */
        $settingsModel = GJMAA::getModel('settings');
        $settingsModel->load($settingId);

        $isAllegro = true;
        if($settingsModel->getData('setting_site') != 1) {
        	$isAllegro = false;
        }


        switch ($profile->getData('profile_type')) {
            case 'auctions_of_user':
                $service = 'import_user';
                break;
            case 'search':
                $service = 'import_search';
                break;
            case 'my_auctions':
            default:
            	if($isAllegro) {
		            $service = 'import_auctions';
	            } else {
		            $service = 'import_user';
	            }
                break;
        }

        $sort = $profile->getData('profile_imported_auctions');
        
        if($profile->getData('profile_import_step') == 1) {
            if($sort == 0 && $profile->getData('profile_clear_auctions') == 1){
                $auctionsModel = GJMAA::getModel('auctions');
                $auctionsModel->deleteByProfileId($profile->getId());
            }
        }
        
        try {
            $importService = GJMAA::getService($service);
            $importService->setProfile($profile);
            $response = $importService->run();
            $fullTree = [];
            
            if (! empty($response['auctions'])) {
                foreach ($response['auctions'] as $auction) {
                    $category = isset($auction['auction_categories']) ? $auction['auction_categories'] : null;
                    if ($category && ! isset($fullTree[$category])) {
                        if (! GJMAA::getModel('allegro_category')->existsInDatabase($category)) {
                            $categoryService = GJMAA::getService('categories');
                            $settingsModel = GJMAA::getModel('settings');
                            $categoryService->setSettings($settingsModel->load($profile->getData('profile_setting_id')));
                            $fullTree[$category] = $categoryService->getFullTreeForCategory($category);
                            GJMAA::getModel('allegro_category')->saveFullTree($fullTree[$category]);
                        }
                    }
                    $auction['auction_sort_order'] = $sort;
                    $sort++;
                    $model = GJMAA::getModel('auctions');
                    $model->load([
                        $auction['auction_id'],
                        $auction['auction_profile_id']
                    ], [
                        'auction_id',
                        'auction_profile_id'
                    ]);
                    
                    $model->setData($auction);
                    $model->save();
                    $model->unsetData();
                }
            }
        } catch (Exception $e) {
            $profile->setData('profile_error_message', $e->getMessage());
            $profile->setData('profile_errors', ((int)$profile->getData('profile_errors'))+1);
            $profile->setData('profile_import_lock', 0);
            $profile->save();
            throw $e;
        }

        unset($response['auctions']);

        if (! isset($response['step'])) {
            $response['step'] = 1;
            $response['all_steps'] = $profile->getData('profile_to_woocommerce') ? 3 : 1;
        }

        $profile->setData('profile_imported_auctions', $response['imported_auctions']);
        $profile->setData('profile_all_auctions', $response['all_auctions']);
        if (! $profile->getData('profile_import_step')) {
            $profile->setData('profile_import_step', 1);
        }

        if ($response['imported_auctions'] >= $response['all_auctions'] || $response['progress'] == 100) {
            $profile->setData('profile_imported_auctions', 0);

            if (($profile->getData('profile_to_woocommerce') && $profile->getData('profile_import_step') == 3) || ! $profile->getData('profile_to_woocommerce')) {
                $profile->setData('profile_import_step', 1);
                $profile->setData('profile_last_sync', date('Y-m-d H:i'));
            } elseif ($profile->getData('profile_to_woocommerce') && $profile->getData('profile_import_step') == 1) {
                if ($type != 'cron') {
                    $profile->setData('profile_import_step', 2);
                } else {
                    $profile->setData('profile_import_step', 1);
                    $profile->setData('profile_last_sync', date('Y-m-d H:i'));
                    $profile->setData('profile_import_lock', 0);
                }
            } elseif ($profile->getData('profile_to_woocommerce') && $profile->getData('profile_import_step') == 2) {
	            if ($type != 'cron') {
		            $profile->setData('profile_import_step', 3);
	            } else {
		            $profile->setData('profile_import_step', 1);
		            $profile->setData('profile_last_sync', date('Y-m-d H:i'));
		            $profile->setData('profile_import_lock', 0);
	            }
            }
        }
        $profile->setData('profile_error_message', null);
        $profile->setData('profile_errors', 0);
        $profile->save();

        return $response;
    }
}
?>