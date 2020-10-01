/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function($){
	let category_hidden = $('#category_hidden'),
	setting_id = $('#setting_id'),
	profile_type = $('#profile_type'),
	profile_user = $('#profile_user'),
	profile_search_query = $('#profile_search_query'),
	setting_site = 1,
	profile_to_woocommerce = $('#profile_to_woocommerce');
	
    $(document).on('change','#category',function(){    	
    	ajaxLoad();
    });
    
    setting_id.on('change',function(){
    	ajaxLoad();
    });
    
    profile_type.on('change',function(){    	
    	checkEnabledFields();
    });
    
    function checkEnabledFields(){
    	var type = profile_type.val();
    	
    	profile_user.attr('disabled',true);
    	profile_search_query.attr('disabled',true);
		$('#category').attr('disabled', true);
    	profile_user.removeAttr('required');
    	profile_search_query.removeAttr('required');
		$('#category').removeAttr('required');
		profile_to_woocommerce.attr('disabled', true);
    	
    	switch(type){
    		case 'search':
    			profile_user.removeAttr('disabled');
    	    	profile_search_query.removeAttr('disabled');
    	    	profile_search_query.attr('required',true);
    	    	if(setting_site != 1) {
					$('#category').removeAttr('disabled');
					profile_to_woocommerce.removeAttr('disabled');
				}
    			break;
    		case 'auctions_of_user':
    			profile_user.removeAttr('disabled');
    			profile_user.attr('required',true);
				if(setting_site != 1) {
					$('#category').removeAttr('disabled');
					profile_to_woocommerce.removeAttr('disabled');
				}
    			break;
			default:
				if(setting_site != 1) {
					$('#category').removeAttr('disabled');
				}
				profile_to_woocommerce.removeAttr('disabled');
				break;
    	}
    }
    
    function ajaxLoad(){
    	$('#category').attr('disabled',true);
    	if(setting_id.val() != undefined && !setting_id.val()){
    		return false;
    	}
    	
    	var categoryId = $('#category').val() !== '' ? $('#category').val() : category_hidden.val();
    	
    	var data = {
    		'category_parent_id' : categoryId,
    		'controller' : 'categories',
    		'action' : 'gjmaa_get_categories',
    		'setting_id' : setting_id.val(),
    		'category_field_name' : $('#category').attr('name')
    	}
    	
    	$.post(ajaxurl,data,function(response){
    		let decoded = $.parseJSON(response);

    		if(!_.isUndefined(decoded) && !_.isUndefined(decoded.category_response)) {
				$('#category').parent().parent().html(decoded.category_response);
			}

    		setting_site = decoded.setting_site;
			checkEnabledFields();
    	});
    }
    
    
    ajaxLoad();
    checkEnabledFields();
});
