jQuery(document).ready(function(){
	jQuery('#allegro-dialog').dialog({
		autoOpen : false,
		width: 350,
		modal: true,
		closeOnEscape: false,
		open: function(event, ui) { jQuery(".ui-dialog-titlebar",ui.dialog | ui).hide(); jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide(); }
	});
	
	function prepareFields(){
		var fields = new Array();
		fields['id'] = jQuery('#id');
		fields['site_allegro'] = jQuery('#site_allegro');
		fields['type_of_auctions'] = jQuery('#type_of_auctions');
		fields['api_allegro'] = jQuery('#api_allegro');
		fields['user_auth'] = jQuery('#user_auth');
		fields['password_auth'] = jQuery('#password_auth');
		fields['item_x_category'] = jQuery('#item_x_category');
		fields['item_x_sort'] = jQuery('#item_x_sort');
		fields['item_x_user'] = jQuery('#item_x_user');
		fields['item_x_query'] = jQuery('#item_x_query');
        fields['count_of_auctions'] = jQuery('#count_of_auctions');
		return fields;
	}
	
	function setFieldsDepends(field,value){
		var allFields = prepareFields();
		switch(field){
			case 'site_allegro':
			case 'item_x_category':
				if(value != ''){
					showAllegroApiConnect();
				} else {
					hideAllegroApiConnect();
				}
				var data = { 
					'action' : 'gjmaa_get_categories_by_country',
					'parent_category_id' : allFields['item_x_category'].val(),
					'site_allegro' : allFields['site_allegro'].val(),
					'setting_id' : allFields['id'].val()
				};
				jQuery.post(ajaxurl,data,function(response) {
					allFields['item_x_category'].html(response);
				});
				break;
			case 'type_of_auctions':
				hideAuctionImportFields();
				switch(value){
					case 'my_auctions': 
						jQuery('#auction_import_settings').removeClass('hidden');
						allFields['item_x_category'].parent().parent().removeClass('hidden'); 
						allFields['item_x_sort'].parent().parent().removeClass('hidden'); 
						allFields['item_x_category'].removeAttr('disabled'); 
						allFields['item_x_sort'].removeAttr('disabled'); 
						break;
					case 'search':
						jQuery('#auction_import_settings').removeClass('hidden');
						allFields['item_x_category'].parent().parent().removeClass('hidden'); 
						allFields['item_x_sort'].parent().parent().removeClass('hidden'); 
						allFields['item_x_user'].parent().parent().removeClass('hidden'); 
						allFields['item_x_query'].parent().parent().removeClass('hidden'); 
						allFields['item_x_category'].removeAttr('disabled'); 
						allFields['item_x_sort'].removeAttr('disabled'); 
						allFields['item_x_user'].removeAttr('disabled'); 
						allFields['item_x_query'].removeAttr('disabled');
						allFields['item_x_user'].removeAttr('required');
						break;
					case 'auctions_of_user': 
						jQuery('#auction_import_settings').removeClass('hidden');
						allFields['item_x_category'].parent().parent().removeClass('hidden'); 
						allFields['item_x_sort'].parent().parent().removeClass('hidden'); 
						allFields['item_x_user'].parent().parent().removeClass('hidden');
						allFields['item_x_category'].removeAttr('disabled'); 
						allFields['item_x_sort'].removeAttr('disabled'); 
						allFields['item_x_user'].removeAttr('disabled');
                        allFields['item_x_user'].attr('required',true);
						break;
				}
				break;
			case 'user_auth': 
			case 'password_auth':
			case 'api_allegro':
				if(allFields['user_auth'].val() != '' && allFields['password_auth'].val() != ''){
					var data = { 
						'action' : 'gjmaa_check_api_allegro_connect',
						'site_allegro' : allFields['site_allegro'].val(),
						'api_allegro' : allFields['api_allegro'].val(),
						'user_auth' : allFields['user_auth'].val(),
						'password_auth' : allFields['password_auth'].val()
					};
					
					jQuery.post(ajaxurl,data,function(response) {
						data = jQuery.parseJSON(response);
						prepareNotification(data.status,data.message);
					});
				}
				break;
		}
	}
	
	function hideAuctionImportFields(){
		jQuery('#auction_import_settings').addClass('hidden');
		jQuery('#auction_import_settings select,#auction_import_settings input').attr('disabled',true);
		jQuery('#auction_import_settings select,#auction_import_settings input').parent().parent().addClass('hidden');
	}
	
	function hideAllegroApiConnect(){
		jQuery('#allegro_api_connect').addClass('hidden');
	}
	
	function showAllegroApiConnect(){
		jQuery('#allegro_api_connect').removeClass('hidden');
	}
	
	function prepareNotification(status,message){
		var notification = '<div id="allegro_notification" class="'+(status == 1 ? 'updated' : 'error')+' notice"><p>'+message+'</p></div>';
		jQuery('#main-section').prepend(notification);
		setTimeout(removeNotification,10000);
	}
	
	function removeNotification(){
		jQuery('#allegro_notification').remove();
	}
	
	var fields = prepareFields();
    fields['count_of_auctions'].val(10);
	setFieldsDepends('site_allegro',fields['site_allegro'].val());
	setFieldsDepends('type_of_auctions',fields['type_of_auctions'].val());
	
	jQuery(fields['type_of_auctions']).on('change',function(){
		setFieldsDepends('type_of_auctions',fields['type_of_auctions'].val());
	});
	
	jQuery(fields['site_allegro']).on('change',function(){
		setFieldsDepends('site_allegro',fields['site_allegro'].val());
	});

    jQuery(fields['count_of_auctions']).on('blur',function(){
        fields['count_of_auctions'].val(10);
    });
	
	jQuery(fields['user_auth']).on('blur',function(){
		setFieldsDepends('user_auth',fields['user_auth'].val());
	});
	
	jQuery(fields['password_auth']).on('blur',function(){
		setFieldsDepends('password_auth',fields['password_auth'].val());
	});
	
	jQuery(fields['api_allegro']).on('blur',function(){
		setFieldsDepends('api_allegro',fields['api_allegro'].val());
	});

    jQuery(fields['item_x_category']).on('change',function(){
        setFieldsDepends('site_allegro',fields['site_allegro'].val());
    });
	
	jQuery('#allegro-dialog').ajaxStart(function(){
        jQuery(this).dialog('open');
	});

	jQuery("#allegro-dialog").ajaxStop(function(){
		jQuery(this).dialog('close');
	});


});