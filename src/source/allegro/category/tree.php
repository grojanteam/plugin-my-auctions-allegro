<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Source_Allegro_Category_Tree extends GJMAA_Source 
{
    protected $categories;
    
    public function getOptions($param = null) 
	{
	    $model = GJMAA::getModel('allegro_category');
	    $options = $model->getAll();
		$result = [];
		foreach($options as $option){
		    $result[$option['category_id']] = $this->getFullPath($option['category_id'],$options);
		}
		
		return $result;
	}
	
	public function getCategories($options){
	    if(!$this->categories){
    	    $categories = [];
    	    
    	    foreach($options as $option){
    	        $categories[$option['category_id']] = [
    	            'parent' => $option['category_parent_id'],
    	            'name' => $option['name']
    	        ];
    	    }
    	    
    	    $this->categories = $categories; 
	    }
	    
	    return $this->categories;
	}
	
	public function getFullPath($categoryId,$options){
	    $categories = $this->getCategories($options);
	    $parent = $categories[$categoryId]['parent'];
	    if($parent != 0){
	        $name = $this->getFullPath($parent,$options);
	    }
	    return !isset($name) ? $categories[$categoryId]['name'] : $name .' / '. $categories[$categoryId]['name'];
	}
}

?>