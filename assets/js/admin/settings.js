/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function($){
	var setting_id = $('#setting_id'), 
	setting_name = $('#setting_name'),
	setting_site = $('#setting_site'),
	setting_is_sandbox = $('#setting_is_sandbox'),
	setting_login = $('#setting_login'),
	setting_password = $('#setting_password'),
	setting_webapi_key = $('#setting_webapi_key'),
	setting_client_id = $('#setting_client_id'),
	setting_client_secret = $('#setting_client_secret'),
	setting_user_country = $('#setting_user_country'),
	setting_user_province = $('#setting_user_province'),
	setting_user_postcode = $('#setting_user_postcode');
	
	checkFieldsState = function(){
		if(!setting_id.val()){
			return;
		}
		
		setting_webapi_key.removeAttr('disabled');

		switch(parseInt(setting_site.val())){
			case 1:
				setting_client_id.removeAttr('disabled');
				setting_client_id.attr('required',true);
				setting_client_secret.removeAttr('disabled');
				setting_client_secret.attr('required',true);
				
				setting_password.attr('disabled',true);
				setting_password.removeAttr('required');
				
				if(setting_user_country.length > 0 && setting_user_country.val() == 'PL') {
					setting_user_province.attr('required',true);
					setting_user_province.removeAttr('disabled');
				} else {
					setting_user_province.removeAttr('required',true);
					setting_user_province.attr('disabled',true);
				}
				break;
			case 56:
				setting_client_id.attr('disabled',true);
				setting_client_id.removeAttr('required');
				setting_client_secret.attr('disabled',true);
				setting_client_secret.removeAttr('required');
				setting_is_sandbox.attr('disabled',true);
				setting_is_sandbox.removeAttr('required');
				setting_password.removeAttr('disabled');
				setting_password.attr('required',true);
				break;
		}
	}
	
	setting_site.on('change',function(){
		var val = $(this).val();
		
		checkFieldsState();
	
		if(!setting_id.val()){
			if(val == 1){
				setting_is_sandbox.attr('required',true);
				setting_is_sandbox.removeAttr('disabled');
			} else {
				setting_is_sandbox.attr('disabled',true);
				setting_is_sandbox.removeAttr('required');
			}
		}
	});
	
	setting_user_country.on('change',function() {
		checkFieldsState();
	});
	
	checkFieldsState();
});