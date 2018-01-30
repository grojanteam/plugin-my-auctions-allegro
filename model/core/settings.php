<?php

class GjmaaSettings {

    private $tableName;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->tableName = $prefix . "gj_allegro_settings";
        $this->wpdb = $wpdb;
    }

    public function install(){
        $wpdb = $this->wpdb;
        $tableName = $this->tableName;
        $db_version = "1.0";

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
            $query = "CREATE TABLE " . $tableName . " (
			`key` varchar(250) NOT NULL,
			`value` TEXT NOT NULL,
			PRIMARY KEY (`key`))
			CHARACTER SET utf8 COLLATE utf8_bin";

            $wpdb->query($query);

            $wpdb->query('INSERT INTO ' . $tableName . " VALUES('plugin_author_url','http://grojanteam.pl');");

            add_option("gjmaa_db_version", $db_version);
        }
    }

    public function uninstall(){
        return $this->wpdb->query('DROP TABLE '.$this->tableName);
    }

    public function getSettings(){
        $settings = array();
        $items = $this->wpdb->get_results("SELECT * FROM ". $this->tableName,ARRAY_A);
        foreach($items as $item){
            $settings[$item['key']] = $item['value'];
        }
        return $settings;
    }

    public function getOldSettings(){
        $settings = array();
        $items = $this->wpdb->get_results("SELECT * FROM ". $this->tableName,ARRAY_A);
        foreach($items as $item){
            $settings[$item['key']] = $item['key'] == 'allegro_password' ? $this->_decryptPass($item['value']) : $item['value'];
        }
        return $settings;
    }

    public function getSettingByKey($key){
        return $this->wpdb->get_var("SELECT `value` FROM ". $this->tableName . " WHERE `key`='" . $key . "'");
    }

    public function save($data){
        $settings = $this->getSettings();
        unset($data['nonce']);
        $queries = array();
        foreach($data as $key => $value){
            $queries[] = isset($settings[$key]) ? $this->_prepareUpdateQuery($key,($key == 'allegro_password' ? $this->_encryptPass($value) : $value)) : $this->_prepareInsertQuery($key,($key == 'allegro_password' ? $this->_encryptPass($value) : $value));
        }

        foreach($queries as $query){
            $this->wpdb->query($query);
            if($this->wpdb->last_error)
                return $this->wpdb->last_error;
        }

        return true;
    }

    private function _encryptPass($value){
        return base64_encode( hash('sha256', $value, true) );
    }

    private function _decryptPass($value){
        list($host,$pass) = explode('_gjmaa_', base64_decode($value));
        return $pass;
    }

    public function encrypt($password){
        return base64_encode( hash('sha256', $password, true) );
    }

    private function _prepareInsertQuery($key,$value){
        return 'INSERT INTO ' . $this->tableName .  ' VALUES("'.$key.'","'.$value.'")';
    }

    private function _prepareUpdateQuery($key,$value){
        return 'UPDATE ' . $this->tableName . ' SET `value`="'.$value.'" WHERE `key`="'.$key.'"';
    }
}