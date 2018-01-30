<?php

class GjmaaAuctionsItem {

    private $tableName;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->tableName = $prefix . "gj_auctions_item";
        $this->wpdb = $wpdb;
    }

    public function install(){
        $wpdb = $this->wpdb;
        $tableName = $this->tableName;
        $db_version = "1.0";

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
            $query = "CREATE TABLE " . $tableName . " (
                auction_id BIGINT NOT NULL,
                auction_settings_id INT NOT NULL,
                auction_name TEXT NOT NULL,
                auction_price FLOAT NOT NULL,
                auction_end TEXT NOT NULL,
                auction_image TEXT NOT NULL,
                auction_user INT,
                PRIMARY KEY (auction_id,auction_settings_id))
                CHARACTER SET utf8 COLLATE utf8_bin";

            $wpdb->query($query);

            add_option("gjmaa_db_version", $db_version);
        }
    }

    public function uninstall(){
        return $this->wpdb->query('DROP TABLE '.$this->tableName);
    }

    public function getCountOfAuctionsBySettingId($sid){
        return $this->wpdb->get_results('SELECT COUNT(*) FROM ' . $this->tableName . ' WHERE auction_settings_id = '. $sid);
    }

    public function getIdsAuctionsBySettingsId($sid,$limit = 5,$offset = 0){
        $auctions_sql = $this->wpdb->get_results("SELECT auction_id FROM " . $this->tableName . " WHERE auction_settings_id = " . $sid . " LIMIT " . $limit . " OFFSET " . $offset, ARRAY_A);
        $auctions = array();
        foreach($auctions_sql as $auction){
            $auctions[] = doubleval($auction['auction_id']);
        }
        return $auctions;
    }
}