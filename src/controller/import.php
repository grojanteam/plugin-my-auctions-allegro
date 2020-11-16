<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Controller_Import extends GJMAA_Controller
{

    protected $template = 'import.phtml';

    protected $content;

    protected $parent = 'gjmaa_dashboard';

    public function getName()
    {
        return __('Import auctions');
    }

    public function getMenuName()
    {
        return __('Import auctions');
    }

    public function getSlug()
    {
        return 'gjmaa_import';
    }

    public function import_action()
    {
        $profileId = $this->getParam('profile_id');
        if (! $profileId) {
            $errorMessage = __('No profile selected.', GJMAA_TEXT_DOMAIN);
            return $this->sendErrorJsonResponse([
                'error_message' => $errorMessage
            ]);
        }

        $profile = GJMAA::getModel('profiles');
        $profile->load($profileId);

        try {
        	/** @var GJMAA_Helper_Import $importHelper */
            $importHelper = GJMAA::getHelper('import');
            $response = $importHelper->runImportByProfileId($profileId);
            $response['action'] = 'import_action';

            if ($response['all_auctions'] > 0 && $response['progress'] == 100) {
                $response['message'] = __('Auctions was imported', GJMAA_TEXT_DOMAIN);
            } else {
                $response['message'] = __('No auctions to import', GJMAA_TEXT_DOMAIN);
            }

            $this->sendSuccessJsonResponse($response);
        } catch (Exception $e) {
            $this->sendErrorJsonResponse([
                'all_auctions' => $profile->getData('profile_all_auctions'),
                'step' => $profile->getData('profile_import_step') ?: ($profile->getData('profile_to_woocommerce') ? 3 : 1),
                'all_steps' => $profile->getData('profile_to_woocommerce') ? 3 : 1,
                'imported_auctions' => $profile->getData('profile_imported_auctions') ?: 0,
                'progress' => ($profile->getData('profile_imported_auctions') / ($profile->getData('profile_all_auctions') > 0 ? $profile->getData('profile_all_auctions') : 1) * 100),
                'error_message' => sprintf(__('Something went wrong: %s', GJMAA_TEXT_DOMAIN), $e->getMessage())
            ]);
        }
    }

    public function initAjaxHooks()
    {
        if (is_admin()) {
            add_action('wp_ajax_gjmaa_import_action', [
                $this,
                'import_action'
            ]);
        }
    }
}
?>