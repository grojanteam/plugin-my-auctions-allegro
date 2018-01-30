(function() {
	prepareFormToAdd();
    tinymce.create('tinymce.plugins.gjMyAllegro', {
        init : function(ed, url) {
            ed.addButton('gjmaa', {
                title : 'My Allegro',
                cmd : 'gjmaaCmd',
                image : url + '/../img/allegro-icon.png'
            });
 
            ed.addCommand('gjmaaCmd', function() {
				var data = {
					'action' : 'gjmaa_add_shortcode_form'
				}
				jQuery( "#allegro-form" ).dialog({
					width: 350,
					modal: true
				});
				
				addLoadingIcon(url);
				jQuery.post(ajaxurl,data,function(response){
					data = jQuery.parseJSON(response);
					jQuery('#allegro-form').html(data.form);
					jQuery('#allegro-form').dialog("option", "buttons", [
						{
							text:data.buttons[0].toString(),
							click : function() { 
								acceptParams(ed,url,this);
							} 
						},
						{
							text:data.buttons[1].toString(),
							click : function() { 
								jQuery(this).dialog('close');
							} 
						}]
					);
					
				});
            });
        }
    });
    // Register plugin
    tinymce.PluginManager.add( 'gjmaa', tinymce.plugins.gjMyAllegro );
	
	
	function prepareFormToAdd(){
		jQuery('#post-body').append('<div id="allegro-form"></div>');
	}
	
	function addLoadingIcon(url){
		jQuery('#allegro-form').html('<img src="'+url+'/../img/loading.gif" width="330" height="100%" />');
	}
	
	function acceptParams(ed,url,block){
		var errors = false;
		jQuery('#allegro-form input,#allegro-form select').removeAttr('style');
		shortcode = '[gjmaa';
		jQuery('#allegro-form input,#allegro-form select').each(function(index,value){
			var id = jQuery(value).attr('id');
			var value_field = jQuery(value).val();
			if(value_field != ''){
				if(id == 'settings_of_auctions')
					shortcode += ' id="' + value_field + '"';
				else
				if(id == 'allegro_title')
					shortcode += ' title="' + value_field + '"';
				else
					shortcode += ' ' + id + '="' + value_field + '"';
			} else {
				if(jQuery(value).is(':required')){
					errors = true;
					jQuery(value).attr('style','border:1px solid #ff0000;');
				}
			}
		});
		shortcode += ' /]';
		if(!errors){
			ed.execCommand('mceInsertContent', 0, shortcode);
			jQuery( block ).dialog( "close" );
		}
	}
})();