<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Category extends GJMAA_Source
{

    public function getOptions($param = null)
    {
        $categoryParentId = isset($param['category_parent_id']) ? $param['category_parent_id'] : null;
        $countryId = isset($param['country_id']) ? $param['country_id'] : null;
        $result = [];
        $options = $this->prepareOptions($categoryParentId,$countryId);
        foreach ($options as $option) {
            $result[$option['category_id']] = $option['name'];
        }

        return $result;
    }

    public function prepareOptions($categoryParentId = 0,$countryId = 1)
    {
        if($countryId == 1){
            $this->getCategoriesFromAPI($categoryParentId);
        }
        $allegroCategoryModel = GJMAA::getModel('allegro_category');
        $categories = $allegroCategoryModel->getResultsByFilters([
            'category_parent_id'
        ], ['category_parent_id'=>$categoryParentId,'country_id'=>$countryId]);
        return $categories;
    }

    public function getCategoriesFromAPI($categoryId)
    {
        $categoryService = GJMAA::getService('categories');
        
        if($settings = $this->getSettings()){
            $categoryService->setSettings($settings);
        }
        
        if ($categoryId === 0) {
            $categoryId = null;
        }

        $categories = $categoryService->getCategoryByIdOrParentId(null, $categoryId);

        if(!empty($categories)){
            $modelAllegroCategory = GJMAA::getModel('allegro_category');
            $modelAllegroCategory->saveFullTree($categories);
        }
    }
    
    public function setSettings($settings){
        $this->settings = $settings;
    }
    
    public function getSettings(){
        return $this->settings;
    }
}

?>