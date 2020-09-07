<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

/**
 * class control all things related to plugin auctions
 * @author grojanteam
 *
 */
class GJMAA_Controller_Categories extends GJMAA_Controller
{

    protected $content;

    protected $parent = 'gjmaa_dashboard';

    public function get_categories()
    {
        $categories = [];

        $allegroSiteSource = GJMAA::getSource('allegro_site');

        $countryId = $allegroSiteSource::ALLEGRO_PL_SITE;

        $categoryFieldName = $this->getParam('category_field_name') ?: 'profile_category';

        $setting_id = $this->getParam('setting_id');
        $settingsModel = GJMAA::getModel('settings');
        if (! $setting_id) {
            $settingsModel->getFirstLive();
            $setting_id = $settingsModel->getId();
        }
        
        if (! $setting_id) {
            $settingsModel->getFirstSandbox();
            $setting_id = $settingsModel->getId();
        }

        if (! $setting_id) {
            return $categories;
        }

        $settingsModel->load($setting_id);

        $countryId = $settingsModel->getData('setting_site');

        $categoryParentId = $this->getParam('category_parent_id') ?: 0;

        if ($countryId == $allegroSiteSource::AUKRO_CZ_SITE) {
        	/** @var GJMAA_Helper_Settings $settingsHelper */
            $settingsHelper = GJMAA::getHelper('settings');
            $settingsHelper->getCategoriesFromWebAPI($settingsModel);
        }

        $source = GJMAA::getSource('allegro_category');
        $source->setSettings($settingsModel);
        $options = $source->getOptions([
            'category_parent_id' => $categoryParentId,
            'country_id' => $countryId
        ]);

        $categoryAllegroModel = GJMAA::getModel('allegro_category');
        $currentCategory = $categoryAllegroModel->load([
            $categoryParentId,
            $countryId
        ], [
            'category_id',
            'country_id'
        ]);

        $currentCategoryParentId = $currentCategory->getData('category_parent_id') ?: 0;
        $currentCategoryId = $currentCategory->getData('category_id');

        $categories = [
            $currentCategoryParentId => ! is_null($currentCategoryId) ? "&lArr; " . __('Back', GJMAA_TEXT_DOMAIN) : "&rArr; " . __('Choose', GJMAA_TEXT_DOMAIN) . " &lArr;"
        ];

        if ($currentCategoryId !== null) {
            $categories[$currentCategoryId] = $currentCategory->getData('name');
        }

        $categories += $options;

        $this->renderSelect($categories, $categoryParentId, $categoryFieldName);
    }

    public function initAjaxHooks()
    {
        if (is_admin()) {
            add_action('wp_ajax_gjmaa_get_categories', [
                $this,
                'get_categories'
            ]);
        }
    }

    public function addSubmenu()
    {
        return;
    }

    public function renderSelect($categories, $categoryParentId = 0, $categoryFieldName = 'profile_category')
    {
        $field = GJMAA::getFormField('select');
        $field->setInfo([
            'id' => 'category',
            'type' => 'select',
            'name' => $categoryFieldName,
            'label' => 'Category',
            'help' => __('Choose allegro category that will be filtered during import', GJMAA_TEXT_DOMAIN),
            'options' => $categories,
            'value' => $categoryParentId
        ]);

        echo "<th>" . $field->toHtml() . "</td>";
        wp_die();
    }
}

?>