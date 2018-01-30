<?php
/*
 * Plugin Name: My auctions allegro FREE
 * Version: 1.6.1
 * Description: Plugin can display auctions from allegro.pl and similar stores.
 * Author: Grojan Team
 * Author URI: http://www.grojanteam.pl/
 * Text Domain: gj_myauctions_allegro
 * Domain Path: /
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define( 'GJMAA_PATH', plugin_dir_path( __FILE__ ) );
define( 'GJMAA_URL', plugin_dir_url(__FILE__));


require_once GJMAA_PATH. 'model/models_include.php';
require_once GJMAA_PATH. 'lib/AllegroWebAPI.php';

/**
 * Install tables to database for plugin My auctions allegro
 */
function gjmaa_install() {
    /** @var $gjMyAuctions GjmaaMyAuctionsAllegro */
    $gjMyAuctions = new GjmaaMyAuctionsAllegro();
    $gjMyAuctions->install();

    /** @var $gjAuctionsItem GjmaaAuctionsItem */
	$gjAuctionsItem = new GjmaaAuctionsItem();
    $gjAuctionsItem->install();

    /** @var $gjAuctionsItem GjmaaAuctionItem */
    $gjAuctionItem = new GjmaaAuctionItem();
    $gjAuctionItem->install();

    $gjCategory = new GjmaaAuctionCategory();
    $gjCategory->install();

    /** @var $gjSettings GjmaaSettings */
    $gjSettings = new GjmaaSettings();
    $gjSettings->install();
}

register_activation_hook(__FILE__, 'gjmaa_install');
 
 /**
 * Remove tables from database for plugin My auctions allegro
 */
function gjmaa_uninstall() {
    /** @var $gjMyAuctions GjmaaMyAuctionsAllegro */
    $gjMyAuctions = new GjmaaMyAuctionsAllegro();
    $gjMyAuctions->uninstall();

    /** @var $gjAuctionsItem GjmaaAuctionsItem */
    $gjAuctionsItem = new GjmaaAuctionsItem();
    $gjAuctionsItem->uninstall();

    /** @var $gjAuctionItem GjmaaAuctionItem */
    $gjAuctionItem = new GjmaaAuctionItem();
    $gjAuctionItem->uninstall();

    $gjCategory = new GjmaaAuctionCategory();
    $gjCategory->uninstall();

    /** @var $gjSettings GjmaaSettings */
    $gjSettings = new GjmaaSettings();
    $gjSettings->uninstall();
}

register_deactivation_hook(__FILE__, 'gjmaa_uninstall');

function gjmaa_update($version){
    /** @var $gjAuctionsAllegro GjmaaAuctionsItem */
    $gjAuctionsAllegro = new GjmaaMyAuctionsAllegro();
    $gjAuctionsAllegro->update($version);
}

/**
 * Add item to admin menu
 */
function gjmaa_plugin_menu() {
    add_menu_page( __('My auctions allegro','gj_myauctions_allegro'), __('My auctions allegro','gj_myauctions_allegro'),'administrator', 'gjmaa_auctions_allegro', 'gjmaa_auctions_allegro');
	
	add_submenu_page( 'gjmaa_auctions_allegro', __('Settings of auctions','gj_myauctions_allegro'), __('Settings of auctions','gj_myauctions_allegro'), 'administrator','gjmaa_auction_settings','gjmaa_auction_settings');
	add_submenu_page( 'gjmaa_auctions_allegro', __('Import auctions','gj_myauctions_allegro'), __('Import auctions','gj_myauctions_allegro'), 'administrator', 'gjmaa_auctions_import', 'gjmaa_auctions_import');
    add_submenu_page( 'gjmaa_auctions_allegro', __('Plugin settings','gj_myauctions_allegro'), __('Plugin settings','gj_myauctions_allegro'), 'administrator', 'gjmaa_settings', 'gjmaa_settings');
}

add_action('admin_menu', 'gjmaa_plugin_menu');

/**
 * Translate for plugin (only PL for now)
 */
function gjmaa_translate(){
	load_plugin_textdomain(
		'gj_myauctions_allegro',
		false,
		dirname(plugin_basename(__FILE__))
	);
}

add_action('init','gjmaa_translate');

/**
 * function to import scripts for plugin
 * @param string $hook
 */
function gjmaa_add_import_script( $hook ) {
    switch($hook){
        case 'moje-aukcje-na-allegro_page_gjmaa_settings':
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_script( 'gjmaa_add_new_settings', GJMAA_URL . 'js/plugin_settings.js' );
            break;
		case 'moje-aukcje-na-allegro_page_gjmaa_auctions_import':
            wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'gjmaa_import_allegro_script', GJMAA_URL . 'js/import_allegro.js' );
            wp_enqueue_style( 'gjmaa_jquery_ui_css', GJMAA_URL . 'css/auction_import.css' );
			break;
		case 'moje-aukcje-na-allegro_page_gjmaa_auction_settings':
            wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'gjmaa_add_new_settings', GJMAA_URL . 'js/add_new_settings.js' );
			break;
		case 'toplevel_page_gjmaa_auctions_allegro':
			wp_enqueue_style( 'gjmaa_auction_items', GJMAA_URL . 'css/auction_items.css' );
			break;
		default: 
			return;
	}
}

add_action('admin_enqueue_scripts', 'gjmaa_add_import_script');

/**
 * Settings for plugin ( API ALLEGRO )
 */
function gjmaa_settings(){
    global $title;
    $html = gjmaa_full_version_notice();
    $html .= '<h1>'.$title.'</h1>';
    $models = array('gj_settings'=> new GjmaaSettings(),'my_auctions_allegro'=>new GjmaaMyAuctionsAllegro());
    if(!empty($_POST)){
        $result = $models['gj_settings']->save($_POST);
        if($result)
            $html .= gjmaa_success_result(__('Settings was updated','gj_myauctions_allegro'));
        else
            $html .= gjmaa_fault_result(__('Settings was not updated','gj_myauctions_allegro'));
    }
    $fieldsModel = new GjmaaMyFieldsSettingsForm($models);
    $html .= $fieldsModel->showSettingsForm()
        .'<div id="allegro-dialog"><div id="loading"><img src="'.GJMAA_URL.'img/loading.gif" width="330" height="100%" /></div></div>';
    gjmaaContainer($html);
}

function gjmaaCheckSettings(){
    $gjSettings = new GjmaaSettings();
    $isConfigured = $gjSettings->getSettingByKey('allegro_username');
    if(!$isConfigured){
        $gjSettings->save([
            'version' => '1.6'
        ]);
        echo "<meta http-equiv=\"refresh\" content=\"0; url=".admin_url('admin.php?page=gjmaa_settings')."\" />";
        exit;
    } else {
        $version = $gjSettings->getSettingByKey('version');
        if(!$version){
            $oldSettings = $gjSettings->getOldSettings();
            $gjSettings->save(
                [
                    'allegro_password' => $oldSettings['allegro_password'],
                    'version' => '1.6'
                ]
            );
            gjmaa_deleteFeedbackTable($gjSettings);
        } elseif((float)$version <= 1.4){
            gjmaa_deleteFeedbackTable($gjSettings);
        } elseif((float)$version < 1.6) {
            try {
                gjmaa_update('1.6');
                $gjSettings->save(
                    [
                        'version' => '1.6'
                    ]
                );
            } catch( Exception $e) {
            }
        }
    }

    $gjCategory = new GjmaaAuctionCategory();
    $gjCategory->updateCategories($gjSettings);

    return true;
}

function gjmaa_deleteFeedbackTable($gjSettings){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $feedbackTable = $prefix . "gj_allegro_feedback";
    $wpdb->query('DROP TABLE '.$feedbackTable);
    $gjSettings->save([
        'version' => '1.5'
    ]);
}

/**
 * Function to show auction settings page
 */
function gjmaa_auction_settings() {
    if(gjmaaCheckSettings()){
        global $title;
        $html = '';
        $action = isset($_GET['action']) ? $_GET['action'] : 'view';
        $modelAuctions = new GjmaaMyAuctionsAllegro();
        echo gjmaa_full_version_notice();
        switch($action):
            case 'edit':
                $id = isset($_GET['sid']) ? $_GET['sid'] : 0;
                if(!empty($_POST)){
                    $result = $modelAuctions->saveAuctionSettings($_POST);
                    if($result)
                        $_SESSION['gjmaa_status'] = 1;
                    else
                        $_SESSION['gjmaa_status'] = 0;

                    if($id == 0) {
                        wp_redirect(admin_url('admin.php?page=gjmaa_auction_settings&action=edit&sid=' . $result));
                    }
                }

                if(isset($_SESSION['gjmaa_status'])){
                    if($_SESSION['gjmaa_status'] == 1)
                        $html = gjmaa_success_result(__('Settings was updated','gj_myauctions_allegro'));
                    else
                        $html = gjmaa_fault_result(__('Settings was not updated','gj_myauctions_allegro'));

                    unset($_SESSION['gjmaa_status']);
                }
                $html .= '<h1>'.$title.'</h1>';
                $fieldsModel = new GjmaaMyFieldsSettingsForm(array('my_auctions_allegro'=>$modelAuctions,'gj_settings'=> new GjmaaSettings()));

                $html .= $fieldsModel->showAuctionSettingsForm($id)
                .'<div id="allegro-dialog"><div id="loading"><img src="'.GJMAA_URL.'img/loading.gif" width="330" height="100%" /></div></div>';
                gjmaaContainer($html);
                break;
            case 'delete':
                $id = isset($_GET['sid']) ? $_GET['sid'] : 0;
                if($id != 0){
                    $modelAuctions->removeAuctionsBySettings($id);
                    $result = $modelAuctions->removeAuctionSetting($id);
                    if($result)
                        echo gjmaa_success_result(__("Settings was deleted",'gj_myauctions_allegro'));
                    else
                        echo gjmaa_fault_result(__("Settings was not deleted",'gj_myauctions_allegro'));
                }
            case 'view':
            default:
                echo '<div class="wrap"><h1>' . $title . '<a class="page-title-action" href="'.admin_url('admin.php?page=gjmaa_auction_settings&action=edit').'">'.__('Add').'</a></h1>';
                $slt = new GjmaaWPSettingsTable();
                $slt->prepare_items();
                $slt->display();
                echo '</div>';
        endswitch;
    }
}


/**
 * Function to show auctions imported from allegro
 */
function gjmaa_auctions_allegro() {
    if(gjmaaCheckSettings()){
        global $title;
        echo gjmaa_full_version_notice();
        echo '<div class="wrap"><h1>' . $title . '<a class="page-title-action" href="'.admin_url('admin.php?page=gjmaa_auctions_import').'">'.__('Import auctions','gj_myauctions_allegro').'</a></h1>';
        $slt = new GjmaaWPSettingsTable();
        $slt->prepare_auction_items();
        echo '<form method="post"><input type="hidden" name="page" value="gjmaa_auctions_allegro" />';
        $slt->search_box('search', 'search_id');
        $slt->display();
        echo '</form></div>';
    }
}

function gjmaaContainer($html){
	echo '<div class="wrap" id="main-section">'.$html.'</div>';
}


function gjmaa_success_result($message = 'Ok'){
	return '<div class="updated notice"><p>'.$message.'!</p></div>';
}

function gjmaa_full_version_notice(){
    return '<div class="updated notice"><p><a href="https://grojanteam.pl/pl/pobierz/wordpress/dodatki/moje-aukcje-allegro" target="_blank">'.__('Get Full Version','gj_myauctions_allegro').'</a></p></div>';
}

function gjmaa_fault_result($message = 'Not ok'){
	return '<div class="error notice"><p>'.__('Something went wrong','gj_myauctions_allegro').'! '.$message.'.</p></div>';
}

/**
 * Function dynamically change title for plugin pages
 * @return array|string|void
 */
function gjmaa_edit_page_title() {
    global $title, $current_screen;
	switch($current_screen->id){
		case 'moje-aukcje-na-allegro_page_gjmaa_auction_settings':
			$action = isset($_GET['action']) ? $_GET['action'] : 'view';
			switch($action){
				case 'edit':
					$id = isset($_GET['sid']) ? $_GET['sid'] : 0;
					if($id == 0)
						$title = __('Add new settings','gj_myauctions_allegro');
					else
						$title = __('Edit settings','gj_myauctions_allegro');
					break;
				case 'view':
				default: 
					$title = __('Settings of auctions','gj_myauctions_allegro');
            }
			break;
		case 'moje-aukcje-na-allegro_page_gjmaa_auction_allegro': 
			$title = __('My auctions allegro','gj_myauctions_allegro');
			break;
		case 'moje-aukcje-na-allegro_page_gjmaa_auctions_import':
			$title = __('Import auctions','gj_myauctions_allegro');
			break;
    }

    return $title;
}

add_action( 'admin_title', 'gjmaa_edit_page_title' );


function gjmaa_display_auctions(){}

/**
 * Function to show import auctions page
 */
function gjmaa_auctions_import () {
    if(gjmaaCheckSettings()){
        global $title;
        echo gjmaa_full_version_notice();
        $modelAuctions = array('my_auctions_allegro' => new GjmaaMyAuctionsAllegro(),'gj_settings' => new GjmaaSettings());
        $fieldsModel = new GjmaaMyFieldsImportForm($modelAuctions);

        $newData = $_POST;
        if(!empty($newData) && isset($newData['ajax'])){
            $response = new WP_Ajax_Response;
            $response->send();
            wp_die();
        } else {
            $html = '<h1>' . $title . '</h1>'
            .$fieldsModel->createImportForm()
            .$fieldsModel->getImportSelect()
            .$fieldsModel->getImportButton()
            .$fieldsModel->generateProcessingFields()
            .$fieldsModel->endImportForm()
            .'<div id="allegro-dialog"><div id="loading"><img src="'.GJMAA_URL.'img/loading.gif" width="330" height="100%" /></div></div>';
            gjmaaContainer($html);
        }
    }
}

/**
 * function import auctions from allegro
 */
function gjmaa_do_import_auctions(){
    $service = new GjmaaServiceAuctions();
    $response = $service->importAuctions($_REQUEST);
    echo json_encode($response);
	wp_die();
}
add_action( 'wp_ajax_gjmaa_do_import_auctions', 'gjmaa_do_import_auctions' );
add_action( 'wp_ajax_nopriv_gjmaa_do_import_auctions', 'gjmaa_do_import_auctions' );

/**
 * function import auctions from allegro
 */
function gjmaa_do_import_auction_details(){
    $requestData = $_REQUEST;
		$gjmaaServiceAuctions = new GjmaaServiceAuctions();
		
		$response = $gjmaaServiceAuctions->importAuctionsWithDetails($requestData);

    echo json_encode($response);
    wp_die();
}
add_action( 'wp_ajax_gjmaa_do_import_auction_details', 'gjmaa_do_import_auction_details' );
add_action( 'wp_ajax_nopriv_gjmaa_do_import_auction_details', 'gjmaa_do_import_auction_details' );

/**
 * add shortcode to page/post using form
 */
function gjmaa_add_shortcode_form(){
	$modelAuctions = new GjmaaMyAuctionsAllegro();
    $gjSettingsModel = new GjmaaSettings();
	$importFields = new GjmaaMyFieldsImportForm(array('my_auctions_allegro'=>$modelAuctions,'gj_settings'=>$gjSettingsModel));
	$fields = new GjmaaMyFieldsSettingsForm(array('my_auctions_allegro'=>$modelAuctions,'gj_settings' => $gjSettingsModel));
	
	$form = '<p>'
			.'<label for="settings_of_auctions">'.__('Settings of auctions', 'gj_myauctions_allegro').':</label>'
			.$importFields->getImportSelect('','settings_of_auctions','settings_of_auctions','widefat')
			.'</p>'
			.'<p>'
			.'<label for="allegro_title">'.__('Title', 'gj_myauctions_allegro').':</label>'
			.$fields->generateTextInput('allegro_title',false,'','widefat')
			.'</p>'
			.'<p>'
			.'<label for="count">'.__('Count of auctions', 'gj_myauctions_allegro').':</label>'
			.$fields->generateNumberInput('count',false,10,'widefat',1,10)
			.'</p>'
			.'<p>'
			.'<label for="show_price">'.__('Show price', 'gj_myauctions_allegro').':</label>'
			.$fields->generateSelect('show_price',$modelAuctions->booleanFields(),false,0,'widefat')
			.'</p>'
			.'<p>'
			.'<label for="show_time">'.__('Show time', 'gj_myauctions_allegro').':</label>'
			.$fields->generateSelect('show_time',$modelAuctions->booleanFields(),false,0,'widefat')
			.'</p>'
            .'<p>'
            .'<label for="show_details">'.__('Show details', 'gj_myauctions_allegro').':</label>'
            .$fields->generateSelect('show_details',$modelAuctions->booleanFields(),false,0,'widefat')
            .'</p>';
			
			$buttons = array(__('Add'),__('Cancel'));
			
			echo json_encode(array('form'=>$form,'buttons'=>$buttons));
			wp_die();
}
add_action( 'wp_ajax_gjmaa_add_shortcode_form', 'gjmaa_add_shortcode_form' );
add_action( 'wp_ajax_nopriv_gjmaa_add_shortcode_form', 'gjmaa_add_shortcode_form' );

/**
 * get categories by country ID
 */
function gjmaa_get_categories_by_country(){
    $site_allegro = $_REQUEST['site_allegro'];
    $setting_id = $_REQUEST['setting_id'];
    $parent_category_id = $_REQUEST['parent_category_id'];
    if($parent_category_id === "")
        $parent_category_id = null;

    $categories = array();
    $modelAuctions = new GjmaaMyAuctionsAllegro();
    $fieldSettings = new GjmaaMyFieldsSettingsForm(array('my_auctions_allegro'=>$modelAuctions));
    $settings_data = null;
    if(!empty($setting_id)) {
        $settings_data = $modelAuctions->getById($setting_id);
        if(is_null($parent_category_id))
            $parent_category_id = $settings_data['item_'.$settings_data['type_of_auctions'].'_category'];
    }
    if(!empty($site_allegro)){
        $categories = $modelAuctions->getCategories($site_allegro,$parent_category_id);
    }
    echo $fieldSettings->generateSelect(
        'item_x_category',
        $categories,
        false,
        ($parent_category_id ?
            $parent_category_id :
            ( $settings_data ? $settings_data['item_'.$settings_data['type_of_auctions'].'_category'] : null )
        ),
        'category'
    );
    wp_die();
}
add_action( 'wp_ajax_gjmaa_get_categories_by_country','gjmaa_get_categories_by_country');
add_action( 'wp_ajax_nopriv_gjmaa_get_categories_by_country','gjmaa_get_categories_by_country');

/**
 * check api connection during configure allegro settings
 */
function gjmaa_check_api_allegro_connect(){
    $gjSettings = new GjmaaSettings();
    $settings = $gjSettings->getSettings();
	$user_allegro = $_REQUEST['user_auth'] ? : null;
	$password_allegro = $_REQUEST['password_auth'] ? $gjSettings->encrypt($_REQUEST['password_auth']) : (isset($settings['password_allegro']) ? $settings['password_allegro'] :  null);
	$site_allegro = $_REQUEST['site_allegro'] ? : null;
	$web_api = $_REQUEST['api_allegro'] ? : null;
	$api_allegro = new GjmaaAllegroWebApi($site_allegro,$web_api,$user_allegro,$password_allegro);
	$result = array(
		'status' => 1,
		'message' => __('Allegro API Connected Successfully','gj_myauctions_allegro')
	);

	if($api_allegro->error){
		$result = array(
			'status' => 0,
			'message' => __($api_allegro->error_mess)
		);
	}
	
	echo json_encode($result);
	wp_die();
}
add_action( 'wp_ajax_gjmaa_check_api_allegro_connect','gjmaa_check_api_allegro_connect');
add_action( 'wp_ajax_nopriv_gjmaa_check_api_allegro_connect','gjmaa_check_api_allegro_connect');

/**
 * add styles and js to plugin
 * @param $atts
 * @return string
 */
function gjmaa_func( $atts ) {
    $style = 'gjmaa-allegro-widget-style';
    if( ( ! wp_style_is( $style, 'queue' ) ) && ( ! wp_style_is( $style, 'done' ) ) )
        wp_enqueue_style( $style, GJMAA_URL . 'css/allegro-widget.css' );
    wp_enqueue_style( 'gjmaa-jquery_ui_css', GJMAA_URL . 'css/jquery/jquery-ui.min.css' );
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_script('gjmaa-allegro-popup',GJMAA_URL .'js/allegro-popup.js');
    wp_localize_script( 'gjmaa-allegro-popup', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );


	return gjmaa_show_auctions_from_shortcode($atts);
}
add_shortcode( 'gjmaa', 'gjmaa_func' );


/**
 * show auctions from shortcode
 * @param $attributes_for_auctions
 * @return string
 */
function gjmaa_show_auctions_from_shortcode($attributes_for_auctions){
	$modelAuctions = new GjmaaMyAuctionsAllegro();

    /** @var $plgSettingsModel GjmaaSettings */
    $plgSettingsModel = new GjmaaSettings();
    $plgSettings = $plgSettingsModel->getSettings();
    $api_allegro = new GjmaaAllegroWebApi($plgSettings['allegro_site'],(isset($plgSettings['allegro_api']) ? $plgSettings['allegro_api'] : null),$plgSettings['allegro_username'],$plgSettings['allegro_password']);
	return $modelAuctions->showAuctionsFromSettings($attributes_for_auctions,'shortcode',$api_allegro);
}

/**
 * get auction description by allegro id
 */
function gjmaa_get_auction_description(){
    $auction_id = $_REQUEST['auction_id'];
    $auctionModel = new GjmaaAuctionItem();
    $data = $auctionModel->getItemById($auction_id);
    echo $data->itemInfo->itDescription;
    wp_die();
}
add_action( 'wp_ajax_get_auction_description','gjmaa_get_auction_description');
add_action( 'wp_ajax_nopriv_get_auction_description','gjmaa_get_auction_description');


/**
 * get auction details by allegro auction id
 */
function gjmaa_get_auction_detail(){
    $allegro_id = $_REQUEST['allegro_id'] ? : null;
    $modelAuction = new GjmaaAuctionItem();
    $allegro = $modelAuction->getItemById($allegro_id);

    if(!$allegro)
        echo json_encode([]);
    else
        echo $allegro;
    wp_die();
}
add_action( 'wp_ajax_gjmaa_get_auction_detail','gjmaa_get_auction_detail');
add_action( 'wp_ajax_nopriv_gjmaa_get_auction_detail','gjmaa_get_auction_detail');

/**
 * CRON METHOD USING TO AUTOMATIC IMPORT AUCTIONS FROM ALLEGRO
 */
if ( ! wp_next_scheduled( 'gjmaa_cron_import_auctions' ) ) {
  wp_schedule_event( time(), 'hourly', 'gjmaa_cron_import_auctions' );
}

add_action( 'gjmaa_cron_import_auctions', 'gjmaa_import_allegro_auction' ,10, 2);

function gjmaa_import_allegro_auction() {
  $cronExecute = new GjmaaServiceCron();
  $cronExecute->execute();
  return true;
}
