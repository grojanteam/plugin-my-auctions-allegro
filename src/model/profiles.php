<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

class GJMAA_Model_Profiles extends GJMAA_Model
{

    protected $tableName = 'gjmaa_profiles';

    protected $oldTableName = 'gj_auctions_allegro';

    protected $defaultPK = 'profile_id';

    protected $model = 'profiles';

    protected $mapColumns = [
        'column_id' => 'profile_id',
        'val_1' => 'profile_setting_id',
        'column_profile_name' => 'profile_name',
        'column_type_of_auctions' => 'profile_type',
        'column_item_x_category' => 'profile_category',
        'column_item_x_sort' => 'profile_sort',
        'column_item_x_user' => 'profile_user',
        'column_item_search_query' => 'profile_search_query',
        'column_last_sync' => 'profile_last_sync',
        'column_count_of_auctions' => 'profile_auctions',
        'column_all_auctions' => 'profile_all_auctions',
        'column_to_woocommerce' => 'profile_to_woocommerce'
    ];

    protected $columns = [
        'profile_id' => [
            'schema' => [
                'INT',
                'AUTO_INCREMENT',
                'NOT NULL'
            ],
            'format' => '%d'
        ],
        'profile_setting_id' => [
            'schema' => [
                'INT',
                'NOT NULL'
            ],
            'format' => '%d'
        ],
        'profile_name' => [
            'schema' => [
                'VARCHAR (255)',
                'NOT NULL'
            ],
            'format' => '%s'
        ],
        'profile_type' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_category' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_sort' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_user' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_search_query' => [
            'schema' => [
                'VARCHAR (255)',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_last_sync' => [
            'schema' => [
                'DATETIME',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_auctions' => [
            'schema' => [
                'INT',
                'NULL'
            ],
            'format' => '%d'
        ],
        'profile_import_step' => [
            'schema' => [
                'INT',
                'NULL'
            ],
            'format' => '%d'
        ],
        'profile_import_lock' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'profile_imported_auctions' => [
            'schema' => [
                'INT',
                'NULL'
            ],
            'format' => '%d'
        ],
        'profile_all_auctions' => [
            'schema' => [
                'INT',
                'NULL'
            ],
            'format' => '%d'
        ],
        'profile_clear_auctions' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'profile_to_woocommerce' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'profile_sync_price' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'profile_sync_stock_date' => [
            'schema' => [
                'DATETIME',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_sync_status_date' => [
            'schema' => [
                'DATETIME',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_sync_price_date' => [
            'schema' => [
                'DATETIME',
                'NULL'
            ],
            'format' => '%s'
        ],
        'profile_cron_sync' => [
            'schema' => [
                'INT (2)',
                'NULL',
                'DEFAULT 1'
            ],
            'format' => '%d'
        ],
        'profile_errors' => [
            'schema' => [
                'INT (11)',
                'NULL',
                'DEFAULT 0'
            ],
            'format' => '%d'
        ],
        'profile_error_message' => [
            'schema' => [
                'TEXT',
                'NULL'
            ],
            'format' => '%s'
        ],
	    'profile_save_woocommerce_category_level' => [
	    	'schema' => [
	    		'INT',
			    'NULL'
		    ],
		    'format' => '%d'
	    ]
    ];

    public function update($currentVersion)
    {
        if (version_compare($currentVersion, '2.0.0') < 0) {
            $this->migerateFromOldToNew();
        }

        if (version_compare($currentVersion, '2.0.16') < 0) {
            $this->installErrorColumns();
        }

        if (version_compare($currentVersion, '2.0.20') < 0) {
            $this->installClearAuctionSwitchColumn();
        }

        if (version_compare($currentVersion, '2.1.1') < 0) {
            $this->installProfileCronSyncSetting();
        }

        if (version_compare($currentVersion, '2.3.0') < 0) {
            $this->addSyncStockAndStatusPerProfile();
        }

        if (version_compare($currentVersion, '2.3.2') < 0) {
            $this->addColumnProfileImportLock();
        }
        
        if(version_compare($currentVersion, '2.3.11') < 0) {
            $this->addSyncPriceColumns();
        }

	    if(version_compare($currentVersion, '2.6.0') < 0) {
		    $this->addSaveWoocommerceCategoryLevelColumn();
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

        foreach ($allEntries as $config) {
            $this->unsetData();
            foreach ($this->mapColumns as $index => $newColumnName) {
                list ($type, $oldColumnName) = explode('_', $index, 2);

                switch ($type) {
                    case 'val':
                        $result = $oldColumnName;
                        break;
                    case 'column':
                    default:
                        $oldColumnName = str_replace('x', $config['type_of_auctions'], $oldColumnName);
                        $result = isset($config[$oldColumnName]) ? $config[$oldColumnName] : null;
                        break;
                }

                $this->setData($newColumnName, $result);
            }
            $this->migrate();
        }
        return true;
    }

    public function getWooCommerceProfileIds()
    {
        $where = "WHERE profile_to_woocommerce = 1";

        $prepareQuery = "SELECT profile_id FROM {$this->getTable()} {$where}";

        $profiles = $this->getWpdb()->get_results($prepareQuery, ARRAY_A);

        $wooProfiles = [];

        foreach ($profiles as $profile) {
            $wooProfiles[] = $profile['profile_id'];
        }

        return $wooProfiles;
    }

    public function getAllProfileErrors()
    {
        $errors = [];

        $profiles = $this->getAll();

        foreach ($profiles as $profile) {
            if ($profile['profile_errors'] > 0) {
                $errors[$profile['profile_id']] = [
                    'error' => $profile['profile_error_message'],
                    'count' => $profile['profile_errors'],
                    'name' => $profile['profile_name']
                ];
            }
        }

        return $errors;
    }
    
    public function getAllLockedProfiles()
    {
        $where = "WHERE profile_import_lock = 1 AND profile_last_sync <= UTC_TIMESTAMP() - INTERVAL 30 MINUTE";
        
        $prepareQuery = "SELECT profile_id FROM {$this->getTable()} {$where}";
        
        $profiles = $this->getWpdb()->get_results($prepareQuery, ARRAY_A);
        
        $wooProfiles = [];
        
        foreach ($profiles as $profile) {
            $wooProfiles[] = $profile['profile_id'];
        }
        
        return $wooProfiles;
    }

    public function installErrorColumns()
    {
        $this->addColumn($this->getTable(), 'profile_errors', [
            'INT (11)',
            'NULL',
            'DEFAULT 0'
        ]);

        $this->addColumn($this->getTable(), 'profile_error_message', [
            'TEXT',
            'NULL'
        ]);
    }

    public function installClearAuctionSwitchColumn()
    {
        $this->addColumn($this->getTable(), 'profile_clear_auctions', [
            'INT (2)',
            'NULL',
            'DEFAULT 0'
        ], 'profile_all_auctions');
    }

    public function installProfileCronSyncSetting()
    {
        $this->addColumn($this->getTable(), 'profile_cron_sync', [
            'INT (2)',
            'NULL',
            'DEFAULT 1'
        ], 'profile_to_woocommerce');
    }

    public function addSyncStockAndStatusPerProfile()
    {
        $this->addColumn($this->getTable(), 'profile_sync_stock_date', [
            'DATETIME',
            'NULL'
        ], 'profile_to_woocommerce');

        $this->addColumn($this->getTable(), 'profile_sync_status_date', [
            'DATETIME',
            'NULL'
        ], 'profile_sync_stock_date');
    }

    public function addColumnProfileImportLock()
    {
        $this->addColumn($this->getTable(), 'profile_import_lock', [
            'INT (2)',
            'NULL',
            'DEFAULT 0'
        ], 'profile_import_step');
    }
    
    public function addSyncPriceColumns()
    {
        $this->addColumn($this->getTable(), 'profile_sync_price', [
            'INT (2)',
            'NULL',
            'DEFAULT 0'
        ], 'profile_to_woocommerce');
        
        $this->addColumn($this->getTable(), 'profile_sync_price_date', [
            'DATETIME',
            'NULL'
        ], 'profile_sync_status_date');
    }

    public function addSaveWoocommerceCategoryLevelColumn()
    {
	    $this->addColumn($this->getTable(), 'profile_save_woocommerce_category_level', [
		    'INT',
		    'NULL'
	    ], 'profile_error_message');
    }
}

?>