<?php

class GjmaaAuctionCategory {

    private $tableName;
    private $wpdb;
    protected $_categories;

    public function __construct(){
        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->tableName = $prefix . "gj_auction_category";
        $this->wpdb = $wpdb;
    }

    public function install(){
        $wpdb = $this->wpdb;
        $tableName = $this->tableName;
        $db_version = "1.0";

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
            $query = "CREATE TABLE " . $tableName . " (
                category_id BIGINT NOT NULL,
                parent_category_id BIGINT NOT NULL,
                name TEXT NOT NULL,
                country_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (category_id,country_id))
                CHARACTER SET utf8 COLLATE utf8_bin";

            $wpdb->query($query);

            add_option("gjmaa_db_version", $db_version);
        }
    }

    public function uninstall(){
        return $this->wpdb->query('DROP TABLE '.$this->tableName);
    }

    public function installCategories($countryId){
        $this->removeAllCategories($countryId);
        if(isset($this->_categories[$countryId])){
            $last = count($this->_categories[$countryId]->catsList->item);
            $i = 1;
            $split = 50;
            $query = '';
            foreach($this->_categories[$countryId]->catsList->item as $index => $category){
                $query .= $this->prepareSaveCategory($category,$countryId,($i == 1 || $i % $split == 0 ? true : false),((($i+1)%$split == 0) || $i == $last ? true : false));
                if(($i+1)%$split == 0 || $i == $last){
                    $this->wpdb->query($query);
                    $query = '';
                }
                $i++;
            }
        }
    }

    /**
     * @param $gjSettings GjmaaSettings
     */
    public function updateCategories($gjSettings){
        $auctions = new GjmaaMyAuctionsAllegro();

        foreach($auctions->getNameOfSite() as $countryId => $site) {
            $lastCatUpdate = $gjSettings->getSettingByKey('last_category_check_' . $countryId);
            $today = strtotime(date('Y-m-d'));
            if(($lastCatUpdate && $lastCatUpdate < $today) || !$lastCatUpdate) {
                $apiAllegro = new GjmaaAllegroWebApi($countryId);
                if (!isset($this->_categories[$countryId]) && !$apiAllegro->error)
                    $this->_categories[$countryId] = $apiAllegro->getCategories($countryId);

                $actVer = $gjSettings->getSettingByKey('category_version_' . $countryId);
                if (isset($this->_categories[$countryId]) && $this->_categories[$countryId]->verStr != $actVer) {

                    $this->installCategories($countryId);
                    $gjSettings->save(
                        [
                            'category_version_' . $countryId => $this->_categories[$countryId]->verStr,
                            'last_category_check_' .$countryId => $today
                        ]
                    );
                } else {
                    $gjSettings->save(
                        [
                            'last_category_check_' .$countryId => $today
                        ]
                    );
                }
            }
        }
    }

    public function prepareSaveCategory($category,$countryId = null,$first = false, $last = false){
        return ($first ? 'INSERT INTO ' .
            $this->tableName  .
            ' (`category_id`, `parent_category_id`, `name`, `country_id`, `created_at`)'.
            ' VALUES (' : ' (' ) .
            $category->catId.','.
            $category->catParent.',"'.
            addSlashes($category->catName).'",'.
            $countryId . ',"' .
            date('Y-m-d') . ' 00:00:00"' .
            ($last ? '); ' : '), ');
    }

    public function removeAllCategories($countryId = 1){
        return $this->wpdb->query("DELETE FROM ". $this->tableName . " WHERE country_id = ". $countryId);
    }

    public function getCategoryById($id = 0,$countryId = 1){
        return $this->wpdb->get_row("SELECT * FROM " . $this->tableName . " WHERE category_id =" .$id  . " AND country_id = " . $countryId);
    }

    public function getCategoriesByParentCategoryId($parentCategoryId = 0,$countryId = 1)
    {
        return $this->wpdb->get_results("SELECT * FROM " . $this->tableName . " WHERE parent_category_id =" . $parentCategoryId . " AND country_id = " . $countryId . " ORDER BY name ASC");
    }

    public function getCategoryNameById($countryId,$id){
        return $this->wpdb->get_var("SELECT name FROM " . $this->tableName . " WHERE category_id =" .$id  . " AND country_id = " . $countryId);
    }
}
