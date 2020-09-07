<?php
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */

defined('ABSPATH') or die;

abstract class GJMAA_Controller {
	protected $mainName = 'My auctions allegro';
	protected $mainMenuName = 'My auctions allegro';
	protected $mainMenu = 'gjmaa_dashboard';
	protected $parent = null;
	protected $buttons = [ ];
	protected $template;
	public function getTitle() {
		$html = $this->showMessage();
		$html .= '<h1 class="wp-heading-inline">' . __ ( $this->getPluginName (), GJMAA_TEXT_DOMAIN ) . '</h1>';
		
		$buttons = $this->getButtons ();
		if(!empty($buttons)){
			foreach ( $buttons as $button => $url ) {
				$html .= '<a class="page-title-action" href="' . admin_url () . 'admin.php?page=' . $this->getSlug () . $url . '">' . __ ( $button, GJMAA_TEXT_DOMAIN ) . '</a>';
			}
		}
		

		$html .= '<h2>' . __ ( $this->getName (), GJMAA_TEXT_DOMAIN ) . '</h2>';

		return $html;
	}
	
	public function execute() {
		$action = isset ( $_GET ['action'] ) ? $_GET ['action'] : 'index';
		if (method_exists ( $this, $action ))
			$this->{$action} ();
		
		$this->renderView();
	}
	
	public function addMenu() {
		if (empty ( $GLOBALS ['admin_page_hooks'] [$this->mainMenu] )) {
		    add_menu_page (  __ ( $this->getDefaultName (), GJMAA_TEXT_DOMAIN ) . ' - ' . __ ( $this->getPluginName(), GJMAA_TEXT_DOMAIN ), __ ( $this->getPluginMenuName(), GJMAA_TEXT_DOMAIN ), 'administrator', $this->mainMenu, $this->getDefaultAction (),'none');
		}
	} 	
	
	public function addSubmenu() {
		$this->addMenu ();
		if (null !== $this->parent) {
			add_submenu_page ( $this->parent, __ ( $this->getName (), GJMAA_TEXT_DOMAIN ) . ' - ' .  __ ( $this->getPluginName(), GJMAA_TEXT_DOMAIN),  __ ( $this->getMenuName(), GJMAA_TEXT_DOMAIN ), 'administrator', $this->getSlug (), [ 
					$this,
					'execute'
			] );
		}
	}
	public function getSlug() {
		return strtolower ( get_class ( $this ) );
	}
	
	public function renderView(){
		return GJMAA::getView($this->getTemplate(),'admin');
	}
	
	public function getTemplate(){
		return $this->template;
	}
	
	public function getPluginName() {
		return $this->mainName;
	}
	
	public function getPluginMenuName() {
	    return $this->mainMenuName;
	}
	
	public function getDefaultName(){
		return $this->getDefaultController()->getName();
	}
	
	public function getDefaultMenuName(){
		return $this->getDefaultController()->getMenuName();
	}
	
	public function getDefaultContent(){
		return $this->getDefaultController()->getContent();
	}
	
	public function getDefaultSlug() {
		return $this->getDefaultController()->getSlug();
	}
	
	public function getDefaultController(){
		if ($this instanceof GJMAA_Controller_Dashboard)
			$controller = $this;
		else
			$controller = GJMAA::getController ( 'dashboard' );
		
		return $controller;
	}
	
	public function getDefaultAction() {
		$controller = $this->getDefaultController();

		return [ 
				$controller,
				"execute"
		];
	}
	public function getButtons() {
		return $this->buttons;
	}
	public function getParam($param) {
		return ! isset ( $_REQUEST [$param] ) ? null : $_REQUEST [$param];
	}
	public function getParams() {
		$request = $_REQUEST;
		unset($request['page']);
		unset($request['action']);
		return $request;
	}
	
	public function redirect($url){
		echo '<script type="text/javascript">
location.href = "'.$url.'";
            </script>'; 
		exit;
	}
	
	public function getIndexUrl(){
	    $referal = isset($_REQUEST['redirect_url']) ? $_REQUEST['redirect_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url('admin.php?page='.$this->getSlug()));
		return $referal;
	}
	
	public function addSessionError($message, $translate = false){
		$class = 'notice notice-error dismissable';
		$this->addMessage($class, $message,$translate);
	}
	
	public function addSessionSuccess($message, $translate = true){
		$class = 'notice notice-success dismissable';
		$this->addMessage($class, $message, $translate);
	}
	
	public function addMessage($class, $message, $translate = false){
		$_SESSION['message'] = sprintf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), ($translate ? __($message,GJMAA_TEXT_DOMAIN) : $message));
	}
	
	public function showMessage() {
		$message = '';
		if(isset($_SESSION['message'])){
			$message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
		return $message;
	}
	
	public function sendJsonResponse($response){
		echo json_encode($response);
		wp_die();
	}
	
	public function sendErrorJsonResponse($params){
		$params = !is_array($params) ? [$params] : $params;
		
		$response = array_merge(['error' => true],$params);
		$this->sendJsonResponse($response);
	}
	
	public function sendSuccessJsonResponse($params){
		$params = !is_array($params) ? [$params] : $params;
		
		$response = array_merge(['error' => false],$params);
		$this->sendJsonResponse($response);
	}
}