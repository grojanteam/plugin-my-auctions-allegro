<?php 

class GjmaaMyFieldsSettingsForm {
	
	private $modelData;
	
	public function __construct($modelData = null){
		$this->modelData = $modelData;
	}
	
	public function showAuctionSettingsForm($id){
		$auctions = $this->modelData['my_auctions_allegro'];
        $gjSettingsModel = $this->modelData['gj_settings'];
        $gjSettings = $gjSettingsModel->getSettings();
		$auction_data = false;
		if($id != 0)
			$auction_data = $auctions->getById($id);

		$categories = $auctions->getCategories($auction_data['site_allegro']);
		$html = $this->createForm(admin_url('admin.php?page=gjmaa_auction_settings&action=edit'.($id == 0 ? '' : '&sid='.$id)))
		.'<table class="form-table">'
		.'<tr valign="top"><td colspan="2"><h2>'.__('Basic settings','gj_myauctions_allegro').'</h2></td></tr>'
        .'<tr valign="top"><th scope="row">'.__('Site','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('site_allegro',$auctions->getNameOfSite(),true,($auction_data ? $auction_data['site_allegro'] : null)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Type of auctions','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('type_of_auctions',$auctions->getTypeOfAuctions(),true,($auction_data ? $auction_data['type_of_auctions'] : null)).'</td></tr>'
		.'</table>'
		.'<table class="form-table" id="auction_import_settings">'
		.'<tr valign="top"><td colspan="2"><h2>'.__('Auction import settings','gj_myauctions_allegro').'</h2></td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Category','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('item_x_category',$categories,false,($auction_data && isset($auction_data['item_'.$auction_data['type_of_auctions'].'_category']) ? $auction_data['item_'.$auction_data['type_of_auctions'].'_category'] : null)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Sort','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('item_x_sort',$auctions->getNameOfSort(),false,($auction_data && isset($auction_data['item_'.$auction_data['type_of_auctions'].'_sort']) ? $auction_data['item_'.$auction_data['type_of_auctions'].'_sort'] : null)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('User','gj_myauctions_allegro').'</th><td>'.$this->generateTextInput('item_x_user',true,($auction_data && isset($auction_data['item_'.$auction_data['type_of_auctions'].'_user']) ? $auction_data['item_'.$auction_data['type_of_auctions'].'_user'] : null)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Query','gj_myauctions_allegro').'</th><td>'.$this->generateTextInput('item_x_query',true,($auction_data && isset($auction_data['item_'.$auction_data['type_of_auctions'].'_query']) ? $auction_data['item_'.$auction_data['type_of_auctions'].'_query'] : null)).'</td></tr>'
		.'</table>'
		.'<table class="form-table" id="additional_settings">'
		.'<tr valign="top"><td colspan="2"><h2>'.__('Additional settings','gj_myauctions_allegro').'</h2></td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Count of auctions','gj_myauctions_allegro').'</th><td>'.$this->generateNumberInput('count_of_auctions',false,($auction_data && isset($auction_data['count_of_auctions']) ? $auction_data['count_of_auctions'] : 10)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Show price','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('show_price',$auctions->booleanFields(),false,($auction_data ? $auction_data['show_price'] : 0)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Show time','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('show_time',$auctions->booleanFields(),false,($auction_data ? $auction_data['show_time'] : 0)).'</td></tr>'
		.'<tr valign="top"><th scope="row">'.__('Show copyright','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('show_copyright',$auctions->booleanFields(),false,($auction_data ? $auction_data['show_copyright'] : 1)).'</td></tr>'
		.'</table>'
		.$this->generateHiddenInput('id',$id)
		.$this->getSaveButton()
		.$this->endForm();
		return $html;
	}

    public function showSettingsForm(){
        /** @var $auctionsModel GjmaaMyAuctionsAllegro */
        $auctionsModel = $this->modelData['my_auctions_allegro'];

        /** @var $plgSettings GjmaaSettings */
        $plgSettingsModel = $this->modelData['gj_settings'];
        $plgSettings = $plgSettingsModel->getSettings();

        $html = $this->createForm(admin_url('admin.php?page=gjmaa_settings'),'settingsForm')
            .'<table class="form-table" id="allegro_api_connect">'
            .'<tr valign="top"><td colspan="2"><h2>'.__('Allegro API Connect','gj_myauctions_allegro').'</h2></td></tr>'
            .'<tr valign="top"><th scope="row">'.__('Site','gj_myauctions_allegro').'</th><td>'.$this->generateSelect('allegro_site',$auctionsModel->getNameOfSite(),true,(isset($plgSettings['allegro_site']) ? $plgSettings['allegro_site'] : null)).'</td></tr>'
            .'<tr valign="top"><th scope="row">'.__('API Key','gj_myauctions_allegro').'</th><td>'.$this->generateTextInput('allegro_api',false,(isset($plgSettings['allegro_api']) ? $plgSettings['allegro_api'] : null)).'</td></tr>'
            .'<tr valign="top"><th scope="row">'.__('Allegro User','gj_myauctions_allegro').'</th><td>'.$this->generateTextInput('allegro_username',true,(isset($plgSettings['allegro_username']) ? $plgSettings['allegro_username'] : null)).'</td></tr>'
            .'<tr valign="top"><th scope="row">'.__('Allegro Password','gj_myauctions_allegro').'</th><td>'.$this->generatePasswordInput('allegro_password',true,(isset($plgSettings['allegro_password']) ? $plgSettings['allegro_password'] : null)).'</td></tr>'
            .'</table>'
            .$this->getSaveButton()
            .$this->endForm();
        return $html;
    }
	
	public function getSaveButton(){
		return '<input class="button button-primary button-large" type="submit" value="'.__('Save','gj_myauctions_allegro').'" />';
	}
	
	public function createForm($action,$id = 'importAuctions',$method = 'POST'){
		return '<form id="'.$id.'" action="'.$action.'" method="'.$method.'">';
	}
	
	public function endForm(){
		return '<input type="hidden" name="nonce" value="'.wp_create_nonce( 'gjmaa_do_import_auctions' ) . '" /></form>';
	}
	
	public function generateSelect($name,$options,$required = null,$value = null,$class = ''){
		$select = '<select class="'.$class.'" id="'.$name.'" name="'.$name.'"'.($required?' required':'').'>'
		.'<option value="">' . __('Choose','gj_myauctions_allegro') . '</option>';
		foreach($options as $option_value => $name){
			$select .= '<option value="'.$option_value.'"'.($option_value == $value ? 'selected="selected"' : '').'>'.$name.'</option>';
		}
		$select .= '</select>';
		
		return $select;
	}
	
	public function generateCategoryTree($name,$options,$required = null,$value = null){
		$select = '<select id="'.$name.'" name="'.$name.'"'.($required?' required':'').'>'
		.'<option value="">' . __('Choose','gj_myauctions_allegro') . '</option>';		
		$select .= $this->createSelectInput($options,'',0,$value);
		$select .= '</select>';
		
		return $select;
	}
	
	public function createSelectInput($tree,$options = '',$parent = 0,$value = null){
		$space = '';
		for($i = 0; $i<4*$parent; $i++)
			$space .= '&nbsp;';
		foreach($tree as $index => $item) {
			$selected = '';
			if($item->id == $value)
				$selected = ' selected="selected"';
			$options .= '<option value="'.$index.'"'.$selected.' parent="'.$parent.'">'.$space.$item.'</option>';
			$parent++;
		}
		
		return $options;
	}
	
	public function generateHiddenInput($name,$value = null){
		return '<input autocomplete="off" type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'" />';
	}
	
	public function generateTextInput($name,$required = null,$value = null,$class = ''){
		return '<input autocomplete="off" class="'.$class.'" id="'.$name.'" type="text" name="'.$name.'" value="'.$value.'"'.($required?' required':'').'/>';
	}
	
	public function generateNumberInput($name,$required = null,$value = null,$class = '',$min=1,$max=10){
		return '<input autocomplete="off" class="'.$class.'" id="'.$name.'" min="'.$min.'" max="'.$max.'" type="number" name="'.$name.'" value="'.$value.'"'.($required?' required':'').'/>';
	}
	
	public function generatePasswordInput($name,$required = null,$value = null){
		return '<input autocomplete="off" id="'.$name.'" type="password" name="'.$name.'" value="'.$value.'"'.(!$value && $required ? ' required':'').'/>';
	}
}