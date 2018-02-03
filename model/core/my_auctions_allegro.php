<?php

class GjmaaMyAuctionsAllegro {
	
	private $tableName;
    private $wpdb;
	
	public function __construct(){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$this->tableName = $prefix . "gj_auctions_allegro";
        $this->wpdb = $wpdb;
	}

    public function install(){
        $wpdb = $this->wpdb;
        $tableName = $this->tableName;
        $db_version = "1.0";

        if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
            $query = "CREATE TABLE " . $tableName . " (
			id int(9) NOT NULL AUTO_INCREMENT,
			api_allegro TEXT,
			site_allegro int NOT NULL,
			type_of_auctions varchar(250) NOT NULL,
			user_auth varchar(250) NOT NULL,
			password_auth TEXT NOT NULL,
			count_of_auctions INT NOT NULL,
			item_search_user VARCHAR(250),
			item_search_query VARCHAR(250),
			item_search_category INT,
			item_search_sort VARCHAR(250),
			item_my_auctions_category INT,
			item_my_auctions_sort VARCHAR(250),
			item_auctions_of_user_category INT,
			item_auctions_of_user_user VARCHAR(250),
			item_auctions_of_user_sort VARCHAR(250),
			show_price INT,
			show_time INT,
			title_color_allegro VARCHAR(250),
			bold_color_allegro VARCHAR(250),
			border_item_allegro VARCHAR(250),
			thumbnail_allegro VARCHAR(250),
			thumbnail_width INT,
			thumbnail_height INT,
			thumbnail_size VARCHAR(250),
			show_copyright INT,
			last_sync DATE NULL,
			to_woocommerce INT NULL,
			PRIMARY KEY (id))
			CHARACTER SET utf8 COLLATE utf8_bin";

            $wpdb->query($query);

            add_option("gjmaa_db_version", $db_version);
        }
    }

    public function uninstall(){
        return $this->wpdb->query('DROP TABLE '.$this->tableName);
    }

    public function update($version){
        switch($version){
            case '1.6':
                $this->wpdb->query('ALTER TABLE '.$this->tableName.' ADD COLUMN last_sync DATE NULL AFTER show_copyright');
                break;
            case '1.7':
                $this->wpdb->query('ALTER TABLE '.$this->tableName.' ADD COLUMN to_woocommerce SMALLINT NULL AFTER last_sync');
                break;
        }
    }
	
	public function getById($id){
		$query = "SELECT * FROM " . $this->tableName . " WHERE id = " .$id;
		return $this->wpdb->get_row($query, ARRAY_A);
	}
	
	public function getByIdWithSpecifiedCols($id,$cols){
		$query = "SELECT " . implode(',',$cols) . " FROM " . $this->tableName . " WHERE id = " .$id;
		return $this->wpdb->get_row($query, ARRAY_A);
	}
	
	public function getAll(){
		$query = "SELECT * FROM " . $this->tableName;
		return $this->wpdb->get_results($query, ARRAY_A);
	}
	
	public function getAllAuctions($type = 'normal'){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$query = "SELECT ".($type == 'count' ? 'COUNT(*)' : '*'). " FROM " . $tableName;
		return $type == 'count' ? $this->wpdb->get_var($query) : $this->wpdb->get_results($query, ARRAY_A);
	}
	
	public function getAuctions($type = 'normal',$search,$sort,$limit,$offset,$columns,$api_allegro){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$search_string = '';
		$search_array = array();
		if(isset($search) && !empty($search)){
			$search_string .= ' WHERE ';
            try
            {
                $userIds = $this->getAllUserIdsFromAuctions($tableName, $api_allegro);
            }
            catch(Exception $e)
            {
                $userIds = null;
            }

            foreach($columns as $column => $label){
				if($column == 'auction_user'){
					$userToSearch = array();
					foreach($userIds as $userId => $user){
						if(strpos(strtolower($user),strtolower($search)) !== false)
							$userToSearch[] = $userId;
					}
					if(!empty($userToSearch))
						$search_array[] = $column . " IN (".implode(',',$userToSearch).")";
				} else
				$search_array[] = "lower(".$column.")" . ' LIKE "%' . strtolower($search) . '%"';
			}
			$search_string .= implode(' OR ',$search_array) . " ";
		}
		$query = "SELECT ".($type == 'count' ? 'COUNT(*)' : '*'). " FROM " . $tableName . $search_string . (!empty($sort) ? " ORDER BY " . $sort . " " : " ") . ($type == 'count' ? '' : "LIMIT " . $limit . " OFFSET " . esc_sql($offset));
		return $type == 'count' ? $this->wpdb->get_var($query) : $this->wpdb->get_results($query, ARRAY_A);
	}

	public function getAllUserIdsFromAuctions($tableName,$api_allegro){
		$result = $this->wpdb->get_results("SELECT auction_user FROM " . $tableName . " GROUP BY auction_user", ARRAY_A);
		$users = array();
		foreach($result as $user){
			$users[$user['auction_user']] = isset($api_allegro->userAllegro['auction_user']) ? $api_allegro->userAllegro['auction_user'] : $api_allegro->getUserLogin($user['auction_user']);
		}
		return $users;
	}
	
	public function getAuctionsBySettingsId($sid,$limit = 5,$offset = 0){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$query = "SELECT * FROM " . $tableName . " WHERE auction_settings_id = " . $sid . " LIMIT " . $limit . " OFFSET " . $offset;
		return $this->wpdb->get_results($query, ARRAY_A);
	}
	
	public function saveAuctionSettings($data) {
		$result = false;
		if($data['id'] == 0){
			$data = $this->convertDataToDb($data,true);
			$keys = array_keys($data);
			$sql = "INSERT INTO ".$this->tableName."(".implode(",",$keys).") VALUES(";
			$values_string = '';
			foreach($data as $value){
				$values_string .= '"'.esc_sql($value).'",';
			}
			$values_string = rtrim($values_string,',');
			$sql .= $values_string .')';
			$this->wpdb->query($sql);
		} else {
			$data = $this->convertDataToDb($data);
			$keys = array_keys($data);
			$oldData = $this->getById($data['id']);
			$sql = "UPDATE ".$this->tableName." SET ";
			$changes = array();
			foreach($data as $index => $item):
				if($item != $oldData[$index])
					$changes[] = $index . ' = "' . $item . '" ';
			endforeach;
			$sql .= implode(',',$changes);
			$sql .= "WHERE id=".$data['id'];
			if(count($changes) > 0)
				$this->wpdb->query($sql);
		}
		return $this->wpdb->last_error ? false : (!isset($data['id']) || (isset($data['id']) && $data['id'] == 0) ? $this->wpdb->insert_id : true);
	}
	
	public function convertDataToDb($data,$new = false){
		$dbData = array();
		foreach($data as $index => $item):
			if((!$new || ($new && $index != 'id')) && $index != 'nonce')
				$dbData[str_replace('_x_','_'.$data['type_of_auctions'].'_',$index)] = $item;
		endforeach;
		return $dbData;
	}
	
	public function removeAuctionsBySettings($sId){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$query = "DELETE FROM " . $tableName . " WHERE auction_settings_id = " . esc_sql($sId);
		return $wpdb->query($query);
	}
	
	public function removeAuctionSetting($sId){
		$query = "DELETE FROM " . $this->tableName . " WHERE id = " . esc_sql($sId);
		return $this->wpdb->query($query);
	}
	
	public function removeAuctionsByUserId($userId){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$query = "DELETE FROM " . $tableName . " WHERE auction_user = " . esc_sql($userId);
		$wpdb->query($query);
	}
	
	public function saveAuctions($auctions,$api,$settings_id,$myLoginId = null){
		$wpdb = $this->wpdb;
		$prefix = $wpdb->prefix;
		$tableName = $prefix . "gj_auctions_item";
		$query = "INSERT IGNORE INTO " . $tableName;
		$first = true;
		$userId = null;
        $auctions = is_array($auctions) ? $auctions : [$auctions];
		foreach($auctions as $auction){
			$images = isset($auction->photosInfo) ? $api->getAllImages($auction->photosInfo->item) : $auction->itemThumbnailUrl;
			$query .= ($first ? " VALUES(" : " (")
			. $auction->itemId . ","
			. $settings_id . ",'"
			. addSlashes($auction->itemTitle) . "',"
			. $this->getPriceData($auction) . ",'" 
			. (isset($auction->timeToEnd) ? $auction->timeToEnd : $auction->itemEndTimeLeft) . "','"
			. (is_array($images) ? $images['large'] : $images) . "',"
			. $myLoginId . "),";

			$first = false;
		}

		$query = rtrim($query,',') . ';';
		$wpdb->query($query);

		if(!empty($wpdb->last_error))
			return array('result'=>0,'message'=>$wpdb->last_error,'query'=>$query);
		return array('result'=>1,'message'=>__('Auctions was imported','my-auctions-allegro-free-edition'));
	}
	
	
	
	public function getNameOfSite($site = null){
		$sites = array(
			1 => 'allegro.pl',
//			56 => 'aukro.cz',
//			209 => 'aukro.ua'
		);
		
		return !is_null($site) ? $sites[$site] : $sites;
	}
	
	public function getNameOfSort($sort = null){
		$sorts = array(
			'1_0' => __('Time to end of auction (Ascending)','my-auctions-allegro-free-edition'),
			'1_1' => __('Time to end of auction (Descending)','my-auctions-allegro-free-edition'),
			'2_0' => __('Count of offers (Ascending)','my-auctions-allegro-free-edition'),
			'2_1' => __('Count of offers (Descending)','my-auctions-allegro-free-edition'),
			'4_0' => __('Current price (Ascending)','my-auctions-allegro-free-edition'),
			'4_1' => __('Current price (Descending)','my-auctions-allegro-free-edition'),
			'8_0' => __('Name of auction (Ascending)','my-auctions-allegro-free-edition'),
			'8_1' => __('Name of auction (Descending)','my-auctions-allegro-free-edition'),
            '16_0' => __('Time of auction create (Ascending)','my-auctions-allegro-free-edition'),
            '16_1' => __('Time of auction create (Descending)','my-auctions-allegro-free-edition')
		);
					
		return !is_null($sort) ? (isset($sorts[$sort])?$sorts[$sort]:'---') : $sorts;
	}
	
	public function getCategories($site = 1,$category = null){
	    if(!$site)
	        $site = 1;

	    if(!$category)
            $category = 0;

        $categoryModel = new GjmaaAuctionCategory();
        $currentCategory = $categoryModel->getCategoryById($category,$site);
        $categories = $categoryModel->getCategoriesByParentCategoryId($category,$site);

        $select = array();
        if($currentCategory) {
            $select[$currentCategory->parent_category_id] = ' <= ' . __('Back', 'my-auctions-allegro-free-edition');
            $select[$currentCategory->category_id] = $currentCategory->name;
        }
        foreach($categories as $category) {
            $select[$category->category_id] = $currentCategory ? '-- ' . $category->name : $category->name;
        }

		/* exists module but countryId not set(default to allegro.pl) */
		return $select;
	}

	public function assignRecursive($categoryModel, &$select,$parent_id = 0, $site = 1)
    {
        $categories = $this->getChildrens($categoryModel, $parent_id,$site);
        if($categories) {
            foreach ($categories as $category) {
                if(!isset($select[$category->category_id]))
                    $select[$category->category_id] = [];
                $select[$category->category_id]['id']= $category->category_id;
                $select[$category->category_id]['parent']= $category->parent_category_id;
                $select[$category->category_id]['name']= $category->name;
                $select[$category->category_id]['children']=[];
                $this->assignRecursive($categoryModel, $select[$category->category_id]['children'], $category->category_id,$site);
            }
        }
    }

    public function getChildrens($categoryModel,$parent_id,$site)
    {
        return $categoryModel->getCategoriesByParentCategoryId($parent_id,$site);
    }
	
	public function searchInCategories($tree,$categorySearch){
		$value = '---';
		foreach($tree as $categoryId => $data){
			if($categoryId == $categorySearch){
				return $data->name;
			}
			elseif($data->children){
				$value = $this->searchInCategories($data->children,$categorySearch);
			}
			if($value != '---')
				return $value;
		}
		return $value;
	}
	
	public function booleanFields($option = null){
		$value = '---';
		$booleanFields = array(
			0 => __('No','my-auctions-allegro-free-edition'),
			1 => __('Yes','my-auctions-allegro-free-edition')
		);
		
		return !is_null($option) ? (isset($booleanFields[$option]) ? $booleanFields[$option] : $value) : $booleanFields;
	}
	
	public function getPriceData($auction){
		$cPrice = null;
		$prices = isset($auction->priceInfo) ? $auction->priceInfo->item : $auction->itemPrice;
		foreach($prices as $index => $price){
			if($price->priceType == 'buyNow' || $price->priceType == 1){
				$cPrice = (float)$price->priceValue;
				break;
			}
			else
				$cPrice = (float)$price->priceValue;
		}
		return $cPrice;
	}
	
	public function getTypeOfAuctions($type = null){
		$type_of_auctions = array(
			'my_auctions' => __('My auctions','my-auctions-allegro-free-edition'),
			'search' => __('Search','my-auctions-allegro-free-edition'),
			'auctions_of_user' => __('Auctions of user', 'my-auctions-allegro-free-edition')
		);
		
		return !is_null($type) ? (isset($type_of_auctions[$type])?$type_of_auctions[$type]:'---') : $type_of_auctions;
	}
	
	public function getLayouts($layout = null){
		$layouts = array(
			'block' => __('Block','my-auctions-allegro-free-edition'),
			'slidebox' => __('Slidebox','my-auctions-allegro-free-edition')
		);
		
		return !is_null($layout) ? (isset($layouts[$layout])?$layouts[$layout]:'---') : $layouts;
	}
	
	public function getCurrency($site){
		$currencyDetails = array(
			1 => 'zł',
			56 => 'Kč',
			93 => 'Ft',
			168 => 'руб',
			209 => 'грн'
		);
		
		return $currencyDetails[$site];
	}
	
	
	function showAuctionsFromSettings($attrs,$type = 'widget'){
		extract($attrs);
		$id = $attrs['id'];
		$settingsById = $this->getByIdWithSpecifiedCols($id,array('site_allegro','show_price','show_time'));
		if(isset($count))
			$settingsById['count_of_auctions'] = $count < 10 ? $count : 10;
        else
            $settingsById['count_of_auctions'] = 10;
		
		$auctions = $this->getAuctionsBySettingsId($id,(!is_null($settingsById['count_of_auctions']) ? $settingsById['count_of_auctions'] : 10 ));
		return $this->renderViewAuctions($attrs,$auctions,$settingsById,$type);
	}

    public function getISOCurrency($site){
        $currencyDetails = array(
            1 => 'PLN',
//            56 => 'Kč',
//            93 => 'Ft',
//            168 => 'руб',
//            209 => 'грн'
        );

        return $currencyDetails[$site];
    }

    function renderViewAuctions($attrs,$auctions,$settingsById,$type){
        if($type != 'widget' && isset($attrs['title'])) $html = '<h2>' . $attrs['title'] . '</h2>'; else $html = '';
        $item_allegro_model = new GjmaaAuctionItem();
        $html .= '<div class="module_max_width"'.($type != 'widget' ? ' itemscope itemtype="http://schema.org/ItemList"' : '').'>';
        $count = count($auctions);
        if($count > 0){
            $html .= '<meta itemprop="numberOfItems" content="'.$count.'" />';
            $user_id = null;
            $first = true;
            foreach($auctions as $index => $auction){
                $id = $auction['auction_id'];
                $url = 'http://'.$this->getNameOfSite($settingsById['site_allegro']).'/show_item.php?item='.$auction['auction_id'];
                $title = $auction['auction_name'];
                if($first){
                    $user_id = $auction['auction_user'];
                    $first = false;
                }
                $html .=
                    '<div class="item item-top"'.($type != 'widget' ? ' id="allegroId'.$id.'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"' : '').'>'
                    .'<meta itemprop="position" content="'.($index+1).'" />'
                    .'<p class="allegro_auction" allegro_id="'.$id.'"'.($type != 'widget' ? ' itemprop="item" itemscope itemtype="http://schema.org/Product"':'').'>';
                if($type != 'widget') {
                    $html .= '<meta itemprop="sku" content="' . $id . '" />'
                        . '<meta itemprop="image" content="' . $auction['auction_image'] . '" />'
                        . '<meta itemprop="model" content="http://schema.org/ProductModel" />'
                        . '<meta itemprop="url" content="'.(is_home() ? home_url() . '/' : get_permalink()).'#allegroId'.$id.'" />'
                        . '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer" style="display:none">'
                        . '<meta itemprop="availability" content="http://schema.org/InStock" />'
                        . '<meta itemprop="price" content="' . number_format($auction['auction_price'], 2, '.', '') . '" />'
                        . '<meta itemprop="priceCurrency" content="' . $this->getISOCurrency($settingsById['site_allegro']) . '" />'
                        . '</span>';
                }
                $html .= '<a class="image_allegro top" target="_blank" href="'.$url.'" title="'.$title.'" style="width: 150px; height: 115px;"><span style="background-image: url(\''.$auction['auction_image'].'\');" alt="'.$title .'"/></a>'
                    .'<a class="title_allegro" target="_blank" href="'.$url.'" title="'.$title.'"><span itemprop="name">'.$title.'</span></a>'
                    .((!isset($attrs['show_price']) && $settingsById['show_price'] == 1) || (isset($attrs['show_price']) && $attrs['show_price'] == 1) ? '<span class="price_allegro" title="">'.number_format($auction['auction_price'],2,'.','').$this->getCurrency($settingsById['site_allegro']).'</span>' : '')
                    .((!isset($attrs['show_time']) && $settingsById['show_time'] == 1) || (isset($attrs['show_time']) && $attrs['show_time'] == 1) ? '<span class="time_allegro" title="">'.$auction['auction_end'].'</span>' : '')
                    .((isset($attrs['show_details']) && $attrs['show_details'] == 1) ? '<a href="'.$url.'" class="title_allegro '.($item_allegro_model->getItemById($id) ? "show_auction_details" : "").'" title="'.$title.'" target="_blank">'.__('Details','my-auctions-allegro-free-edition_pro').'</a>' : '')
                    .'</p>'
                    .'</div>';
            }
        } else {
            $html .= '<p class="no_offers">'.__('No offers').'</p>';
        }
        return $html .'</div><div class="allegro_dialog"><div class="allegro_description"></div></div>';
    }


}
