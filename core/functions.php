<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

/**
 General Class App for manage plugin
 */
class GJMAA
{

    public static $defaultClass = 'GJMAA';

    public static $defaultController = 'dashboard';

    /**
     *
     * @param
     *            $model
     * @return mixed
     */
    public static function getModel($model)
    {
        return self::getInstance($model);
    }

    /**
     *
     * @param
     *            $form
     * @return mixed
     */
    public static function getForm($form)
    {
        return self::getInstance($form, 'Form');
    }

    /**
     *
     * @param
     *            $formField
     * @return mixed
     */
    public static function getFormField($formField)
    {
        return self::getInstance($formField, 'Form_Field');
    }

    /**
     *
     * @param
     *            $helper
     * @return mixed
     */
    public static function getHelper($helper)
    {
        return self::getInstance($helper, 'Helper');
    }

    public static function getCron($cron)
    {
        return self::getInstance($cron, 'Cron');
    }

    /**
     *
     * @param
     *            $controller
     * @return bool
     */
    public static function getController($controller)
    {
        return self::getInstance($controller, 'Controller');
    }

    /**
     *
     * @param
     *            $service
     * @return bool
     */
    public static function getService($service)
    {
        return self::getInstance($service, 'Service');
    }

    /**
     *
     * @param
     *            $lib
     * @return boolean
     */
    public static function getLib($lib)
    {
        return self::getInstance($lib, 'Lib');
    }

    /**
     *
     * @param
     *            $table
     * @return boolean
     */
    public static function getTable($table)
    {
        if (! class_exists('WP_List_Table')) {
            require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        return self::getInstance($table, 'Table');
    }

    /**
     *
     * @param
     *            $source
     * @return boolean
     */
    public static function getSource($source)
    {
        return self::getInstance($source, 'Source');
    }

    public static function getWidget($widget)
    {
        if (! class_exists('WP_Widget')) {
            require_once (ABSPATH . 'wp-includes/class-wp-widget.php');
        }

        return self::getInstance($widget, 'Widget');
    }

    public static function getShortcode($shortcode)
    {
        return self::getInstance($shortcode, 'Shortcode');
    }

    public static function getView($view, $type = 'front', $variables = [])
    {
        extract($variables);
        $view = strtolower($view);
        $view = str_replace('_', '/', $view);
        if(file_exists(GJMAA_PATH . 'views/' . $type . '/' . $view)) {
	        include( GJMAA_PATH . 'views/' . $type . '/' . $view );
        }
    }

    /**
     *
     * @param string $instance
     * @param string $type
     * @param bool $rec
     * @return mixed
     */
    public static function getInstance($instance, $type = 'Model', $rec = false)
    {
        $className = call_user_func_array(self::$defaultClass.'::parseToClass',[$instance, $type]);
        $path = call_user_func_array(self::$defaultClass.'::parseToFile',[$instance, $type]);

        if (class_exists($className)) {
            return new $className();
        } elseif (file_exists($path) && ! $rec) {
            require_once ($path);
            return self::getInstance($instance, $type, true);
        }

        return false;
    }

    /**
     *
     * @param string $string
     * @param string $type
     * @return string
     */
    public static function parseToClass($string, $type = 'Model')
    {
        $part = explode('_', $string);
        foreach ($part as $index => $classPart) {
            $part[$index] = ucfirst(strtolower($classPart));
        }

        return self::$defaultClass . '_' . $type . '_' . implode('_', $part);
    }

    /**
     *
     * @param string $string
     * @param string $type
     * @return string
     */
    public static function parseToFile($string, $type = 'Model')
    {
        $pathCode = $type == 'Lib' ? GJMAA_PATH : GJMAA_PATH_CODE;
        $abstractFile = GJMAA_PATH . 'core/' . strtolower($type) . '.php';
        $instancePath = $pathCode . str_replace('_', '/', strtolower($type));
        if (file_exists($abstractFile)) {
            require_once ($abstractFile);
        }
        return $instancePath . '/' . str_replace('_', '/', $string) . '.php';
    }

	/**
	 * method to install all needed database tables
	 *
	 * @throws Exception
	 */
    public static function install()
    {
        $settingsModel = self::getModel('settings');
        $settingsModel->install();

        $profileModel = self::getModel('profiles');
        $profileModel->install();

        $auctionsModel = self::getModel('auctions');
        $auctionsModel->install();

        $allegroCategoryModel = self::getModel('allegro_category');
        $allegroCategoryModel->install();

        $pluginDbVersion = get_option('gjmaa_database_version', false);
        if (! $pluginDbVersion) {
            add_option('gjmaa_database_version', self::getVersion());
        }
    }

    /**
     * method to uninstall all tables
     */
    public static function uninstall()
    {
        $settingsModel = self::getModel('settings');
        $settingsModel->uninstall();

        $profileModel = self::getModel('profiles');
        $profileModel->uninstall();

        $auctionsModel = self::getModel('auctions');
        $auctionsModel->uninstall();

        $allegroCategoryModel = self::getModel('allegro_category');
        $allegroCategoryModel->uninstall();

        $pluginDbVersion = get_option('gjmaa_database_version', false);
        if ($pluginDbVersion) {
            delete_option('gjmaa_database_version');
        }
    }
    
    public static function restartSystem()
    {
        self::uninstall();
        self::install();
    }

    /**
     * update DB version
     */
    public static function update()
    {
        $pluginDbVersion = get_option('gjmaa_database_version', false);
        $oldDbVersion = get_option('gjmaa_db_version',false);
        if ((! $pluginDbVersion && !$oldDbVersion) || self::getVersion() == $pluginDbVersion) {
            return;
        }

        $settingsModel = self::getModel('settings');
        $settingsModel->update($pluginDbVersion);

        $profileModel = self::getModel('profiles');
        $profileModel->update($pluginDbVersion);

        $auctionsModel = self::getModel('auctions');
        $auctionsModel->update($pluginDbVersion);

        $allegroCategoryModel = self::getModel('allegro_category');
        $allegroCategoryModel->update($pluginDbVersion);

        if (version_compare($pluginDbVersion, '2.0.0') < 0) {
            self::migrateWidgets();
        }

        if (! $pluginDbVersion)
            add_option('gjmaa_database_version', self::getVersion());

        update_option('gjmaa_database_version', self::getVersion());
    }

    /**
     * adding to wp menu needed items
     */
    public static function initPlugin()
    {
    	if(self::checkCompatibility()) {

		    add_action( 'admin_menu', array(
			    'GJMAA',
			    'initPluginMenu'
		    ) );

		    self::addTranslations();
		    self::initStaticAjaxHooks();
		    self::initShortcodes();
		    self::initCron();
		    self::checkInstallation();
	    }
    }
    
    public static function checkInstallation()
    {
    	/** @var GJMAA_Model_Settings $settingsModel */
        $settingsModel = self::getModel('settings');
        $tableName = $settingsModel->getTable();
        if(!$settingsModel->tableExists($tableName)){
            self::install();
        }
    }

    public static function addTranslations()
    {
        include_once(GJMAA_PATH.'core/translation.php');
        load_plugin_textdomain(GJMAA_TEXT_DOMAIN, false, GJMAA_PATH . '/lang/');
    }

    public static function initPluginMenu()
    {
        $allControllers = self::getAllControllers();

        foreach ($allControllers as $controllerName) {
        	/** @var GJMAA_Controller $controller */
            $controller = self::getController($controllerName);
            if($controller){
                $controller->addSubmenu();
            }
        }

        self::addStylesAction();
        self::update();
    }
    
    public static function getControllerPath($dir = 'controller'){
        return GJMAA_PATH_CODE . $dir;
    }

    public static function getAllControllers($dir = 'controller', $excludeMain = true, $parent = null, $child = false)
    {
        $controllerPath = call_user_func_array(self::$defaultClass.'::getControllerPath',[$dir]);
        $allFiles = scandir($controllerPath);
        $controllers = [];
        foreach ($allFiles as $file) {
            $controllerName = str_replace('.php', '', $file);
            if(! in_array($controllerName, [
                '.',
                '..',
                self::$defaultController
            ])) {
                if(is_dir($controllerPath . '/' . $file)){
                    $controllers = array_merge(call_user_func_array(self::$defaultClass.'::getAllControllers',[$dir.'/' . $file, true, $file, true]),$controllers);
                    
                } else {
                    $controllers[] = ($parent ? $parent . '_' : '') . $controllerName;
                }
            }
        }
        
        asort($controllers);

        return $controllers;
    }

    public static function getVersion()
    {
        $pluginData = self::getInfo();
        return $pluginData['Version'];
    }

    public static function getInfo()
    {
        return get_plugin_data(GJMAA_PATH . 'my-auctions-allegro-free-edition.php');
    }

    public static function addStylesToAdminPanel($hook)
    {
        $replace = [
            'my-auctions-allegro_',
            'toplevel_',
            'moje-aukcje-allegro_'
        ];
        $page = str_replace($replace, '', $hook);
        switch ($page) {
            case 'page_gjmaa_dashboard':
            case 'page_gjmaa_support':
                // css styles
                wp_enqueue_style('gjmaa_admin_dashboard', GJMAA_URL . 'assets/css/admin/dashboard.css');
                wp_enqueue_style('gjmaa_admin_bootstrap', GJMAA_URL . 'assets/css/admin/bootstrap.min.css');
                break;
            case 'page_gjmaa_settings':
                // js scripts
                wp_enqueue_script('gjmaa_admin_help', GJMAA_URL . 'assets/js/admin/help.js', [
                    'jquery-ui-tooltip',
                    'jquery'
                ]);
                wp_enqueue_script('gjmaa_admin_category', GJMAA_URL . 'assets/js/admin/category_ajax.js');
                wp_enqueue_script('gjmaa_admin_settings', GJMAA_URL . 'assets/js/admin/settings.js');

                // css styles
                wp_enqueue_style('gjmaa_admin_help', GJMAA_URL . 'assets/css/admin/help.css');
                break;
            case 'page_gjmaa_profiles':
	        case 'page_gjmaa_categorymap':
                // js scripts
                wp_enqueue_script('gjmaa_admin_help', GJMAA_URL . 'assets/js/admin/help.js', [
                    'jquery-ui-tooltip',
                    'jquery'
                ]);
                wp_enqueue_script('gjmaa_admin_category', GJMAA_URL . 'assets/js/admin/category_ajax.js', [
                	'underscore'
                ]);

                // css styles
                wp_enqueue_style('gjmaa_admin_help', GJMAA_URL . 'assets/css/admin/help.css');
                break;
            case 'page_gjmaa_import':
                // js scripts
                wp_enqueue_script('gjmaa_admin_import', GJMAA_URL . 'assets/js/admin/import_allegro.js');

                // css styles
                wp_enqueue_style('gjmaa_admin_template', GJMAA_URL . 'assets/css/admin/template.css');
                break;
        }
        wp_enqueue_style('gjmaa_admin_font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');
        wp_enqueue_style('gjmaa_admin_always', GJMAA_URL . 'assets/css/admin/always.css');
    }

    public static function addStylesAction()
    {
        add_action('admin_enqueue_scripts', [
            self::class,
            'addStylesToAdminPanel'
        ]);

        add_filter('admin_footer_text', [
            self::class,
            'footerCopyright'
        ], 1);
    }

	/**
	 * @return bool
	 */
    public static function initStaticAjaxHooks()
    {
        if (isset($_REQUEST['controller'])) {
            $controllerName = $_REQUEST['controller'];
            /** @var GJMAA_Controller $controller */
            $controller = GJMAA::getController($controllerName);
            if (! $controller) {
                return false;
            }

            $controller->initAjaxHooks();
        }

        return true;
    }

    public static function migrateWidgets()
    {
        if ($widgets = get_option('widget_gjmaa_allegro_widget')) {
            $newWidgets = [];
            foreach ($widgets as $index => $widget) {
                if (! empty($widget['settings_of_auctions'])) {
                    $newWidget = [];
                    $newWidget['title'] = $widget['title'];
                    $newWidget['profile_id'] = $widget['settings_of_auctions'];
                    $newWidget['count_of_auctions'] = $widget['count'];
                    $newWidget['show_price'] = $widget['show_price'];
                    $newWidget['show_time'] = $widget['show_time'];
                    $newWidget['show_search'] = $widget['show_search'];
                    $newWidget['show_sort'] = $widget['show_sort'];
                    $newWidget['template'] = 'default.phtml';
                    $newWidgets[$index] = $newWidget;
                }
            }

            $newWidgets['_multiwidget'] = $widgets['_multiwidget'];

            if (! get_option('widget_gjmaa_auctions')) {
                add_option('widget_gjmaa_auctions', $newWidgets);
            } else {
                update_option('widget_gjmaa_auctions', $newWidgets);
            }
            delete_option('widget_gjmaa_allegro_widget');
        }
    }

    public static function initWidgets()
    {
        $widgetAuctions = self::getWidget('auctions');
        $widgetAuctions->register();
    }

    public static function initShortcodes()
    {
        add_shortcode('gjmaa', [
            self::class,
            'addAcutionsShortcode'
        ]);
    }

    public static function addAcutionsShortcode($attrs)
    {
        $shortcode = GJMAA::getShortcode('auctions');
        return $shortcode->execute($attrs);
    }

    public static function initCron()
    {
        add_filter('cron_schedules', [
            self::class,
            'cronFrequencyList'
        ]);

        $cronList = self::cronList();

        foreach ($cronList as $cronJob => $cronSchema) {
            if (! wp_next_scheduled($cronJob)) {
                wp_schedule_event(time(), $cronSchema['frequency'], $cronJob);
            }

            add_action($cronJob, $cronSchema['method'], 10, 2);
        }
    }

    /**
     * execute cron job for import
     */
    public static function cronList()
    {
        $importCron = self::getCron('import_auctions');
        $releaseLock = self::getCron('import_release');
        $cronList = [
            'gjmaa_cron_import_auctions' => [
                'frequency' => 'gjmaa_five_minutes',
                'method' => [
                    get_class($importCron),
                    'run'
                ]
            ],
            'gjmaa_cron_import_release_lock' => [
                'frequency' => 'gjmaa_every_thirty_minutes',
                'method' => [
                    get_class($releaseLock),
                    'run'
                ]
            ]
        ];

        /** @var GJMAA_Service_Woocommerce $wooService */
        $wooService = self::getService('woocommerce');
        $wooCronList = [];
        if ($wooService->isEnabled()) {
            $wooCronNewProducts = self::getCron('woocommerce_new');
            $wooStockCron = self::getCron('woocommerce_stock');
            $wooPriceCron = self::getCron('woocommerce_price');
            $wooStateCron = self::getCron('woocommerce_status');
            $wooClearMedia = self::getCron('woocommerce_clearmedia');

            $wooCronList = [
                'gjmaa_cron_update_woocommerce_new' => [
                    'frequency' => 'gjmaa_every_minute',
                    'method' => [
                        get_class($wooCronNewProducts),
                        'run'
                    ]
                ],
                'gjmaa_cron_update_woocommerce_stock' => [
                    'frequency' => 'gjmaa_every_minute',
                    'method' => [
                        get_class($wooStockCron),
                        'run'
                    ]
                ],
                'gjmaa_cron_update_in_woocommerce_status' => [
                    'frequency' => 'gjmaa_every_minute',
                    'method' => [
                        get_class($wooStateCron),
                        'run'
                    ]
                ],
                'gjmaa_cron_update_woocommerce_price' => [
                    'frequency' => 'gjmaa_five_minutes',
                    'method' => [
                        get_class($wooPriceCron),
                        'run'
                    ]
                ],
                'gjmaa_cron_clear_media_in_woocommerce' => [
                    'frequency' => 'hourly',
                    'method' => [
                        get_class($wooClearMedia),
                        'run'
                    ]
                ]
            ];
        }

        return array_merge($cronList, $wooCronList);
    }

    public static function cronFrequencyList($schedules)
    {
        $schedules += [
            'gjmaa_every_thirty_minutes' => [
                'interval' => 30 * 60,
                'display' => __('Every 30 minutes', GJMAA_TEXT_DOMAIN)
            ],
            'gjmaa_five_minutes' => [
                'interval' => 5 * 60,
                'display' => __('Every 5 minutes', GJMAA_TEXT_DOMAIN)
            ],
            'gjmaa_every_minute' => [
                'interval' => 60,
                'display' => __('Every minute', GJMAA_TEXT_DOMAIN)
            ]
        ];
        
        return $schedules;
    }

    public static function footerCopyright($footer_text)
    {
        global $current_screen;

        $replace = [
            'my-auctions-allegro_',
            'toplevel_',
            'moje-aukcje-allegro_'
        ];
        $page = str_replace($replace, '', $current_screen->id);
        switch ($page) {
            case 'page_gjmaa_auctions':
            case 'page_gjmaa_dashboard':
            case 'page_gjmaa_settings':
            case 'page_gjmaa_profiles':
            case 'page_gjmaa_import':
            case 'page_gjmaa_categorymap':
            case 'page_gjmaa_support':
                $pluginData = self::getInfo();
                $footer_text = sprintf(__('%1$s developed by %2$s. If you like this plugin, please rate us on %3$s. Thank you.', 'my-auctions-allegro-free-edition'), $pluginData['Title'] . ' v' . $pluginData['Version'], $pluginData['Author'], '<a href="https://wordpress.org/support/plugin/my-auctions-allegro-free-edition/reviews?rate=5#new-post" target="_blank">WordPress</a>');
                break;
        }

        return $footer_text;
    }

	/**
	 * @throws Exception
	 */
    public static function checkCompatibility()
    {
	    /** @var GJMAA_Helper_Dashboard $helper */
	    $helper = self::getHelper('dashboard');
	    if(!$helper->isCompatiblePHPVersion()) {
	    	echo '<div class="notice notice-error">'.__(sprintf('Your PHP version isn\'t enough for using plugin %s. Minimum requirements is PHP 7.2', __('My auctions allegro', GJMAA_TEXT_DOMAIN)), GJMAA_TEXT_DOMAIN).'</div>';
		    return false;
	    }

	    if(!$helper->isCompatibleWordpressVersion()) {
		    echo '<div class="notice notice-error">'.__(sprintf('Your WordPress version isn\'t enough for using plugin %s. Minimum requirements is v5.0.0',__('My auctions allegro', GJMAA_TEXT_DOMAIN)), GJMAA_TEXT_DOMAIN).'</div>';
		    return false;
	    }

	    return true;
    }
}