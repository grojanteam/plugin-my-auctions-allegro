jQuery(document).ready(function(){

    jQuery('#settingsForm input[type=submit]').attr('disabled',true);

    jQuery('#allegro-dialog').dialog({
        autoOpen : false,
        width: 350,
        modal: true,
        closeOnEscape: false,
        open: function(event, ui) { jQuery(".ui-dialog-titlebar",ui.dialog | ui).hide(); jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide(); }
    });

    function prepareFields(){
        var fields = new Array();
        fields['allegro_site'] = jQuery('#allegro_site');
        fields['allegro_api'] = jQuery('#allegro_api');
        fields['allegro_username'] = jQuery('#allegro_username');
        fields['allegro_password'] = jQuery('#allegro_password');
        return fields;
    }

    function setFieldsDepends(field,value){
        var allFields = prepareFields();
        switch(field){
            case 'allegro_site':
            case 'allegro_api':
            case 'allegro_username':
            case 'allegro_password':
                if(allFields['allegro_site'].val() != '' && allFields['allegro_username'].val() != '' && (allFields['allegro_password'].val() != '' && allFields['allegro_password'].val() != '********')){
                    var data = {
                        'action' : 'gjmaa_check_api_allegro_connect',
                        'site_allegro' : allFields['allegro_site'].val(),
                        'api_allegro' : allFields['allegro_api'].val(),
                        'user_auth' : allFields['allegro_username'].val(),
                        'password_auth' : allFields['allegro_password'].val()
                    };

                    jQuery.post(ajaxurl,data,function(response) {
                        data = jQuery.parseJSON(response);
                        prepareNotification(data.status,data.message);
                    });
                }
                break;
        }
    }

    function prepareNotification(status,message){
        var notification = '<div id="allegro_notification" class="'+(status == 1 ? 'updated' : 'error')+' notice"><p>'+message+'</p></div>';
        jQuery('#main-section').prepend(notification);
        if(status == 1) jQuery('#settingsForm input[type=submit]').removeAttr('disabled'); else jQuery('#settingsForm input[type=submit]').attr('disabled',true);
        setTimeout(removeNotification,10000);
    }

    function removeNotification(){
        jQuery('#allegro_notification').remove();
    }

    var fields = prepareFields();
    jQuery(fields['allegro_site']).on('change',function(){
        setFieldsDepends('allegro_site',fields['allegro_site'].val());
    });

    jQuery(fields['allegro_username']).on('blur',function(){
        setFieldsDepends('allegro_username',fields['allegro_username'].val());
    });

    jQuery(fields['allegro_password']).on('blur',function(){
        setFieldsDepends('allegro_password',fields['allegro_password'].val());
    });

    jQuery(fields['allegro_api']).on('blur',function(){
        setFieldsDepends('allegro_api',fields['allegro_api'].val());
    });

    jQuery('#allegro-dialog').ajaxStart(function(){
        jQuery(this).dialog('open');
    });

    jQuery("#allegro-dialog").ajaxStop(function(e){
        jQuery(this).dialog('close');
    });
});