/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function($){
	var category_hidden = $('#category_hidden'),
	setting_id = $('#setting_id'),
	profile_type = $('#profile_type'),
	profile_user = $('#profile_user'),
	profile_search_query = $('#profile_search_query');
	
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
    	profile_user.removeAttr('required');
    	profile_search_query.removeAttr('required');
    	
    	switch(type){
    		case 'search':
    			profile_user.removeAttr('disabled');
    	    	profile_search_query.removeAttr('disabled');
    	    	profile_search_query.attr('required',true);
    			break;
    		case 'auctions_of_user':
    			profile_user.removeAttr('disabled');
    			profile_user.attr('required',true);
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
    		$('#category').parent().parent().html(response);
    	});
    }
    
    
    ajaxLoad();
    checkEnabledFields();
});
