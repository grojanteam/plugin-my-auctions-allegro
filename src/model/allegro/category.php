<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Model_Allegro_Category extends GJMAA_Model
{
    protected $oldTable = 'gj_auction_category';
    
    protected $map = [
        'category_id' => 'category_id',
        'parent_category_id' => 'category_parent_id',
        'name' => 'name',
        'country_id' => 'country_id',
        'created_at' => 'created_at'
    ];
    
    protected $tableName = 'gjmaa_categories';

    protected $defaultPK = 'id';
    
    protected $model = 'allegro_category';

    protected $columns = [
        'id' => [
            'schema' => [
                'INT',
                'AUTO_INCREMENT',
                'NOT NULL'
            ],
            'format' => '%d'
        ],
        'category_id' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'category_parent_id' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'name' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'country_id' => [
            'schema' => [
                'INT',
                'NULL'
            ],
            'format' => '%d'
        ],
        'leaf' => [
            'schema' => [
                'SMALLINT',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'created_at' => [
            'schema' => [
                'DATETIME',
                'DEFAULT NOW()'
            ],
            'format' => '%s'
        ],
    ];

    public function getChildCategoriesById()
    {}

    public function getParentCategoryById()
    {}
    
    public function update($currentVersion)
    {
        if(version_compare($currentVersion,'2.0.0') < 0){
            $this->migerateFromOldToNew();
        }
        
        if(version_compare($currentVersion,'2.0.25') < 0) {
            $this->addLeafParameter();
        }
        
        return $this;
    }
    
    public function addLeafParameter(){
        $this->addColumn(
            $this->getTable(),
            'leaf',
            [
                'SMALLINT',
                'NULL',
                'DEFAULT 0'
            ],
            'country_id'
        );
    }
    
    public function migerateFromOldToNew()
    {
        $this->install();
        $mainTable = $this->tableName;
        $this->tableName = $this->oldTable;
        $categories = $this->getAll();
        $this->tableName = $mainTable;
        
        foreach($categories as $category){
            $result = [];
            foreach($this->map as $oldColumnName => $newColumnName){
                $result[$newColumnName] = $category[$oldColumnName];
            }
                
            $this->unsetData();
            $this->setData($result);
            $this->save();
        }
    }
    
    public function existsInDatabase($categoryId){
        return $this->getWpdb()->get_var(
            sprintf(
                "SELECT %s FROM %s WHERE category_id = '%s'",
                $this->getDefaultPk(),
                $this->getTable(),
                $categoryId
            )
        ) ? : false;
    }
    
    public function saveFullTree($tree){
        foreach($tree as $category){
            $this->unsetData();
            
            $id = $this->existsInDatabase($category['category_id']);
            if(false !== $id){
                $category['id'] = $id;
            }
            
            $this->setData($category);
            $this->save();
        }
    }
    
    public function getResultsByFilters($columns,$filters){
        $categoryParentId = isset($filters['category_parent_id']) ? $filters['category_parent_id'] : 0;
        $countryId = isset($filters['country_id']) ? $filters['country_id'] : 1;
        $where = "WHERE category_parent_id = '{$categoryParentId}' AND country_id = {$countryId}";
        
        $prepareQuery = "SELECT * FROM {$this->getTable()} {$where}";
        
        return $this->getWpdb()->get_results($prepareQuery,ARRAY_A);
        
    }
    
    public function deleteAllCategories(){
        $this->getWpdb()->query(
            sprintf('DELETE FROM %s', $this->getTable())
        );
    }
}

?>