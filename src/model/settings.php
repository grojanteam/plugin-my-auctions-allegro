<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Model_Settings extends GJMAA_Model
{

    protected $tableName = 'gjmaa_settings';

    protected $oldTableName = 'gj_allegro_settings';

    protected $defaultPK = 'setting_id';

    protected $model = 'settings';

    protected $mapColumns = [
        'allegro_api' => 'setting_webapi_key',
        'allegro_username' => 'setting_login',
        'allegro_password' => 'setting_password',
        'allegro_site' => 'setting_site',
        'allegro_sync' => 'setting_sync'
    ];

    protected $mapAsOption = [
        'last_category_check_1',
        'last_category_check_56',
        'category_version_1',
        'category_version_56'
    ];

    protected $columns = [
        'setting_id' => [
            'schema' => [
                'INT',
                'AUTO_INCREMENT',
                'NOT NULL'
            ],
            'format' => '%d'
        ],
        'setting_name' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'setting_site' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'setting_is_sandbox' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'setting_webapi_key' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_login' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_password' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_client_id' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_client_secret' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_client_token' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_client_token_expires_at' => [
            'schema' => [
                'DATETIME',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_client_refresh_token' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_user_country' => [
            'schema' => [
                'VARCHAR (2)',
                'NULL',
                'DEFAULT "PL"'
            ],
            'format' => '%s'
        ],
        'setting_user_city' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_user_province' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_user_postcode' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
        'setting_auction_closed' => [
            'schema' => [
                'INT(2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'setting_sync' => [
            'schema' => [
                'INT',
                'NULL',
                'DEFAULT 1'
            ],
            'format' => '%d'
        ]
    ];

    public function update($currentVersion)
    {
        if (version_compare($currentVersion, '2.0.0') < 0) {
            $this->migerateFromOldToNew();
        }

        if (version_compare($currentVersion, '2.0.25') < 0) {
            $this->addUserInformation();
        }

        if (version_compare($currentVersion, '2.1.2') < 0) {
            $this->addCountryToDatabase();
        }

        if (version_compare($currentVersion, '2.2.0') < 0) {
            $this->addColumnWhatToDoWithOutOfStockProducts();
        }

        return $this;
    }

    public function migerateFromOldToNew()
    {
        $this->install();
        $mainTable = $this->tableName;
        $this->tableName = $this->oldTableName;
        $allEntries = $this->getAll();
        $this->tableName = $mainTable;

        foreach ($allEntries as $key => $config) {
            if (isset($this->mapColumns[$config['key']])) {
                $this->setData($this->mapColumns[$config['key']], $config['value']);
            }

            if (in_array($config['key'], $this->mapAsOption)) {
                if (! get_option('gjmaa_' . $config['key'], false)) {
                    add_option('gjmaa_' . $config['key'], $config['value']);
                } else {
                    update_option('gjmaa_' . $config['key'], $config['value']);
                }
            }
        }

        $this->setData('setting_name', 'Migrated Account');

        $this->save();
        return true;
    }

    public function getFirstLive()
    {
        $query = sprintf("SELECT * FROM %s WHERE setting_is_sandbox <> %d", $this->getTable(), 1);

        $setting = $this->getWpdb()->get_row($query, ARRAY_A);

        $this->setData($setting);

        return $this;
    }

    public function getFirstSandbox()
    {
        $query = sprintf("SELECT * FROM %s WHERE setting_is_sandbox = %d", $this->getTable(), 1);

        $setting = $this->getWpdb()->get_row($query, ARRAY_A);

        $this->setData($setting);

        return $this;
    }

    public function getProductDecisionByProfileId($profileId)
    {
        $query = sprintf("SELECT setting_auction_closed FROM %s INNER JOIN %s as %s ON %s WHERE %s", $this->getTable(), GJMAA::getModel('profiles')->getTable(), 'profiles', 'profiles.profile_setting_id = setting_id', sprintf('profiles.profile_id = %d', $profileId));
        
        $decision = $this->getWpdb()->get_var($query);
        
        return $decision;
    }

    public function addUserInformation()
    {
        $this->addColumn($this->getTable(), 'setting_user_city', [
            'TEXT',
            'NULL'
        ], 'setting_client_refresh_token');

        $this->addColumn($this->getTable(), 'setting_user_province', [
            'TEXT',
            'NULL'
        ], 'setting_user_city');

        $this->addColumn($this->getTable(), 'setting_user_postcode', [
            'TEXT',
            'NULL'
        ], 'setting_user_province');
    }

    public function addCountryToDatabase()
    {
        $this->addColumn($this->getTable(), 'setting_user_country', [
            'VARCHAR (2)',
            'NULL',
            'DEFAULT "PL"'
        ], 'setting_client_refresh_token');
    }

    public function addColumnWhatToDoWithOutOfStockProducts()
    {
        $this->addColumn($this->getTable(), 'setting_auction_closed', [
            'INT(2)',
            'NULL',
            'DEFAULT 0'
        ], 'setting_user_country');
    }
}

?>