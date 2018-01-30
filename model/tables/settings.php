<?php 

class GjmaaWPSettingsTable extends WP_List_Table {
	
	public $auctionSettings = array();
	public $per_page = 20;
	
	public function get_columns(){
		return array(
			'id' => __('ID','gj_myauctions_allegro'),
			'site' => __('Site','gj_myauctions_allegro'),
			'type_of_auctions' => __('Type of auctions','gj_myauctions_allegro'),
			'user_allegro' => __('User allegro','gj_myauctions_allegro'),
			'category' => __('Category','gj_myauctions_allegro'),
			'sort' => __('Sort','gj_myauctions_allegro'),
			'count_of_auctions' => __('Count of auctions','gj_myauctions_allegro'),
			'show_price'=>__('Show price','gj_myauctions_allegro'),
			'show_time'=>__('Show time','gj_myauctions_allegro'),
			'action'=>__('Action','gj_myauctions_allegro')
		);
	}
	
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->auction_settings();
	}
	
	public function column_default($item, $column_name) {
		return $item[$column_name];
	}
	
	public function auction_settings(){
		$auctions_result = array();
		$modelAuctions = new GjmaaMyAuctionsAllegro();
		$auctions = $modelAuctions->getAll();
        $gjSettingsModel = new GjmaaSettings();
        $gjCategory = new GjmaaAuctionCategory();
        $gjSettings = $gjSettingsModel->getSettings();
		$columns = $this->get_columns();
		if(count($auctions) > 0):
			foreach($auctions as $auction):
				$auctions_result[$auction['id']] = array();
				foreach($columns as $column => $title_of_column):
					$value = '';
					switch($column):
						case 'id': $value = $auction['id']; break;
						case 'site': $value = $modelAuctions->getNameOfSite($auction['site_allegro']); break;
						case 'type_of_auctions': $value = $modelAuctions->getTypeOfAuctions($auction['type_of_auctions']); break;
						case 'user_allegro': $value = $auction[($auction['type_of_auctions'] == 'my_auctions' ? 'user_auth' : 'item_'.$auction['type_of_auctions'].'_user')]; break;
						case 'category': $value = $gjCategory->getCategoryNameById($auction['site_allegro'],$auction['item_'.$auction['type_of_auctions'].'_category']); break;
						case 'sort': $value = $modelAuctions->getNameOfSort($auction['item_'.$auction['type_of_auctions'].'_sort']); break;
						case 'count_of_auctions': $value = $auction['count_of_auctions']; break;
						case 'show_price': $value = $modelAuctions->booleanFields($auction['show_price']); break;
						case 'show_time': $value = $modelAuctions->booleanFields($auction['show_time']); break;
						case 'action' : $value = '<a href="'.admin_url('admin.php?page=gjmaa_auction_settings&action=edit&sid='.$auction['id']).'" title="'.__('Edit').'">'.__('Edit').'</a>' . ' | ' . '<a href="'.admin_url('admin.php?page=gjmaa_auction_settings&action=delete&sid='.$auction['id']).'" title="'.__('Delete').'">'.__('Delete').'</a>'; break;
						default:
							$value = '';
							break;
					endswitch;
					$auctions_result[$auction['id']][$column] = $value;
				endforeach;
			endforeach;
		endif;
		return $auctions_result;
	}
	
	public function get_auction_columns(){
		return array(
			'auction_id' => __('ID','gj_myauctions_allegro'),
			'auction_image' => __('','gj_myauctions_allegro'),
			'auction_name' => __('Name','gj_myauctions_allegro'),
			'auction_price' => __('Price','gj_myauctions_allegro'),
			'auction_end' => __('End auction','gj_myauctions_allegro'),
			'auction_user' => __('User','gj_myauctions_allegro')
		);
	}
	
	public function prepare_auction_items(){
		$api_allegro = new GjmaaAllegroWebApi();
		extract($_GET);
		$search = isset($_POST['s']) ? $_POST['s'] : '';
		$auction_items = array();
		$modelAuctions = new GjmaaMyAuctionsAllegro();
		$current_page = $this->get_pagenum();
		$sort = isset($orderby) ? $orderby . ' ' . $order : '';
		$columns = $this->get_auction_columns();
		$total_items = $this->calculateAllAuctions($search,htmlspecialchars($sort),$this->per_page,($current_page - 1)*$this->per_page,$columns,$api_allegro);
		$current_page = $this->get_pagenum();
		$this->set_pagination_args(array(
			'total_items' => $total_items,                  
			'per_page'    => $this->per_page
	    ));
		$hidden = array();
		$sortable = array(
			'auction_id'  => array('auction_id',false),
			'auction_name' => array('auction_name',false),
			'auction_price'   => array('auction_price',false)
		);
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->auction_items($api_allegro);
	}
	
	public function auction_items($api_allegro){
		extract($_GET);
		$search = isset($_POST['s']) ? $_POST['s'] : '';
		$auction_items = array();
		$modelAuctions = new GjmaaMyAuctionsAllegro();
		$current_page = $this->get_pagenum();
		$sort = isset($orderby) ? $orderby . ' ' . $order : '';
		$columns = $this->get_auction_columns();
		$auctions = $modelAuctions->getAuctions('normal',$search,htmlspecialchars($sort),$this->per_page,($current_page - 1)*$this->per_page,$columns,$api_allegro);
		if(count($auctions) > 0):
			foreach($auctions as $auction):
				$auctionSettings = $this->_getAuctionSettings($modelAuctions,$auction);
				foreach($columns as $index => $column):
					if($index == 'auction_image'){
						$auction_items[$auction['auction_id']][$index] = '<img src="'.esc_url($auction[$index]).'" alt="'.esc_html($auction['auction_name']).'" />';
					} elseif ($index == 'auction_user') {
						$auction_items[$auction['auction_id']][$index] = esc_html(isset($api_allegro->userLogin[$auction[$index]]) ? $api_allegro->userLogin[$auction[$index]] : $api_allegro->getUserLogin($auction[$index]));
					} elseif ($index == 'auction_price') {
						$auction_items[$auction['auction_id']][$index] = esc_html(number_format($auction[$index],2) . $modelAuctions->getCurrency($auctionSettings['site_allegro']));
					} else {
						$auction_items[$auction['auction_id']][$index] = esc_html($auction[$index]);
					}
				endforeach;
			endforeach;
		endif;
		return $auction_items;
	}
	
	public function calculateAllAuctions($search,$sort,$limit,$offset,$columns,$api_allegro){
		$modelAuctions = new GjmaaMyAuctionsAllegro();
		return $modelAuctions->getAuctions('count',$search,$sort,$limit,$offset,$columns,$api_allegro);
	}
	
	protected function _getAuctionSettings($modelAuctions,$auction){
		return !isset($this->auctionSettings[$auction['auction_settings_id']]) ? $this->auctionSettings[$auction['auction_settings_id']] = $modelAuctions->getById($auction['auction_settings_id']) : $this->auctionSettings[$auction['auction_settings_id']];
	}
}