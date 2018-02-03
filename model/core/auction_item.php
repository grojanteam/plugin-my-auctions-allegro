<?php

class GjmaaAuctionItem {

    private $tableName;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->tableName = $prefix . "gj_auction_item";
        $this->wpdb = $wpdb;
    }

    public function install(){
        $wpdb = $this->wpdb;
        $tableName = $this->tableName;
        $db_version = "1.0";

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
            $query = "CREATE TABLE " . $tableName . " (
                item_id BIGINT NOT NULL,
                item_details TEXT NOT NULL,
                PRIMARY KEY (item_id))
                CHARACTER SET utf8 COLLATE utf8_bin";

            $wpdb->query($query);

            add_option("gjmaa_db_version", $db_version);
        }
    }

    public function uninstall(){
        return $this->wpdb->query('DROP TABLE '.$this->tableName);
    }

    public function saveItems($items){
        if(count($items) > 0){
	    $items = is_array($items) ? $items : [$items];
            $query = "INSERT IGNORE INTO " . $this->tableName;
            $first = true;
            foreach($items as $item){
                $query .= ($first ? " VALUES" : "") . "(".$item->itemInfo->itId.",'" . addslashes(json_encode($item)) . "'),";
                $first = false;
            }

            $query = rtrim($query,',') . ';';
            $this->wpdb->query($query);

            if(!empty($this->wpdb->last_error))
                return array('result'=>0,'message'=>$this->wpdb->last_error,'query'=>$query);
            return array('result'=>1,'message'=>__('Auctions was imported','my-auctions-allegro-free-edition'));
        }
        return array('result'=>1,'message'=>__('Auctions was imported','my-auctions-allegro-free-edition'));
    }

    public function removeItemAuctionsIds($auctionIds){
        return $this->wpdb->query("DELETE FROM ". $this->tableName . " WHERE item_id IN (".implode(',',$auctionIds).");");
    }

    public function getItemById($id){
        return $this->wpdb->get_var("SELECT item_details FROM " . $this->tableName . " WHERE item_id =" .$id);
    }
}
