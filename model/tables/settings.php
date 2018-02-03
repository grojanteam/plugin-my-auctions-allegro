<?php 

class GjmaaWPSettingsTable extends WP_List_Table {
	
	public $auctionSettings = array();
	public $per_page = 20;
	
	public function get_columns(){
	    $columns = array(
            'id' => __('ID','my-auctions-allegro-free-edition'),
            'site' => __('Site','my-auctions-allegro-free-edition'),
            'type_of_auctions' => __('Type of auctions','my-auctions-allegro-free-edition'),
            'user_allegro' => __('User allegro','my-auctions-allegro-free-edition'),
            'category' => __('Category','my-auctions-allegro-free-edition'),
            'sort' => __('Sort','my-auctions-allegro-free-edition'),
            'count_of_auctions' => __('Count of auctions','my-auctions-allegro-free-edition'),
            'show_price'=>__('Show price','my-auctions-allegro-free-edition'),
            'show_time'=>__('Show time','my-auctions-allegro-free-edition')
        );

	    $wooCommerceService = new GjmaaServiceWoocommerce();
	    if($wooCommerceService->isEnabled()){
            $columns['woocommerce'] = __('WooCommerce','my-auctions-allegro-free-edition');
        }

        $columns['action'] = __('Action','my-auctions-allegro-free-edition');

		return $columns;
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
        $gjCategory = new GjmaaAuctionCategory();
        $wooCommerceService = new GjmaaServiceWoocommerce();
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
                        case 'woocommerce': $value = $wooCommerceService->isEnabled() ? $modelAuctions->booleanFields($auction['to_woocommerce'] ? : false) : null; break;
						case 'action' : $value = '<a href="'.admin_url('admin.php?page=gjmaa_auction_settings&action=edit&sid='.$auction['id']).'" title="'.__('Edit', 'my-auctions-allegro-free-edition').'">'.__('Edit', 'my-auctions-allegro-free-edition').'</a>' . ' | ' . '<a href="'.admin_url('admin.php?page=gjmaa_auction_settings&action=delete&sid='.$auction['id']).'" title="'.__('Delete', 'my-auctions-allegro-free-edition').'">'.__('Delete', 'my-auctions-allegro-free-edition').'</a>'; break;
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
			'auction_id' => __('ID','my-auctions-allegro-free-edition'),
			'auction_image' => __('','my-auctions-allegro-free-edition'),
			'auction_name' => __('Name','my-auctions-allegro-free-edition'),
			'auction_price' => __('Price','my-auctions-allegro-free-edition'),
			'auction_end' => __('End auction','my-auctions-allegro-free-edition'),
			'auction_user' => __('User','my-auctions-allegro-free-edition')
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