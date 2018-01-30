jQuery(document).ready(function(){
	jQuery('#allegro-dialog').dialog({
		autoOpen : false,
		width: 350,
		modal: true,
		closeOnEscape: false,
		open: function(event, ui) { jQuery(".ui-dialog-titlebar",ui.dialog | ui).hide(); jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide(); }
	});
	
	jQuery('#importAuctions').on('submit',function(){
	    importForm(false);
        sendImportData(jQuery(this).serialize());
		return false;
	});

    function prepareProcessFields(){
        var processFields = new Array();
        processFields['submit_import'] = jQuery('#submit_import');
        processFields['step_import'] = jQuery('#step_import');
        processFields['processing_import'] = jQuery('#processing_import');
        processFields['imported_auctions'] = jQuery('#imported_auctions');
        processFields['all_auctions'] = jQuery('#all_auctions');
        processFields['import_details'] = jQuery('#import_details');
        return processFields;
    }



    function sendImportData(data){
        var fields = prepareProcessFields();
        var getDetails = fields['import_details'].is(':checked') ? true : false;
        jQuery.post(ajaxurl,data,function(response) {
            result = jQuery.parseJSON(response);
            processFirstData(result);
            if(result['end'] && result['all_auctions'] > 0){
                result['action'] = 'gjmaa_do_import_auction_details';
                result['start'] = 0;
                if(getDetails)
                    sendSecondImportData(result);
                else {
                    alert(result['message']);
                    importForm(true);
                }
            } else if(!result['end']){
                setTimeout(function(){
                    result['start'] = parseInt(result['start']) + 1;
                    sendImportData(result);
                },500);
            } else {
                alert(result['message']);
                importForm(true);
            }
        });
    }

    function sendSecondImportData(data){
        jQuery.post(ajaxurl,data,function(response) {
            result = jQuery.parseJSON(response);
            processSecondData(result);
            if(result['end']){
                alert(result['message']);
                importForm(true);
            } else
                setTimeout(function(){
                    result['start'] = parseInt(result['start']) + 1;
                    sendSecondImportData(result);
                },500);
        });
    }

    function importForm(enable) {
        var fields = prepareProcessFields();
        if(enable){
            fields['import_details'].removeAttr('disabled');
            fields['submit_import'].removeAttr('disabled');
        } else {
            fields['import_details'].attr('disabled', true);
            fields['submit_import'].attr('disabled', true);
        }
    }

    function processFirstData(data){
        var fields = prepareProcessFields();
        var allsteps = fields['import_details'].is(':checked') ? 2 : 1;
        fields['step_import'].html('1 / '+ allsteps);
        fields['processing_import'].html((data['end'] ? (data['all_auctions'] > 0 ? data['all_auctions'] / data['all_auctions'] : 1) * 100 : parseInt(((parseInt(data['limit']) * (parseInt(data['start']) + 1)) * 100) / data['all_auctions'])) + '%')
        fields['imported_auctions'].html(data['end'] ? parseInt(data['all_auctions']) : parseInt(data['limit']) * (parseInt(data['start']) + 1));
        fields['all_auctions'].html(data['all_auctions']);
    }

    function processSecondData(data){
        var fields = prepareProcessFields();
        var allsteps = fields['import_details'].is(':checked') ? 2 : 1;
        fields['step_import'].html('2 / ' + allsteps);
        fields['processing_import'].html((data['end'] ? (data['all_auctions'] > 0 ? data['all_auctions'] / data['all_auctions'] : 1) * 100 : parseInt(((parseInt(data['limit']) * (parseInt(data['start']) + 1)) * 100) / data['all_auctions'])) + '%')
        fields['imported_auctions'].html(data['end'] ? parseInt(data['all_auctions']) : parseInt(data['limit']) * (parseInt(data['start']) + 1));
        fields['all_auctions'].html(data['all_auctions']);
    }

    jQuery('#import_details').on('change',function () {
        var allsteps = jQuery(this).is(':checked') ? 2 : 1;
        jQuery('#step_import').html('0 / ' + allsteps);
    });

	jQuery('#allegro-dialog').ajaxStart(function(){
		jQuery(this).dialog('open');
	});
	jQuery("#allegro-dialog").ajaxStop(function(){
		jQuery(this).dialog('close');
	}); 
});
