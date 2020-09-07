/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function(){
	
	jQuery('#import_form').on('submit',function(){
		var data = jQuery(this).serialize();
	    importForm(false);
        sendImportData(data);
		return false;
	});

    function prepareProcessFields(){
        var processFields = new Array();
        processFields['profile_id'] = jQuery('#profile_id');
        processFields['submit_import'] = jQuery('#submit_import');
        processFields['step'] = jQuery('#step');
        processFields['auction'] = jQuery('#auction');
        processFields['progress'] = jQuery('#progress');
        return processFields;
    }



    function sendImportData(data){
        var fields = prepareProcessFields();
        jQuery.post(ajaxurl,data,function(response) {
            var result = jQuery.parseJSON(response);
            processFirstData(result);
            if(result['error']){
                importForm(true);
                showError(result);
            } else if(result['progress'] < 100.00){
                setTimeout(function(){
                    sendImportData(data);
                },500);
            } else {
                importForm(true);
                showSuccess(result);
            }
        });
    }

    function importForm(enable) {
        var fields = prepareProcessFields();
        if(enable){
            fields['profile_id'].removeAttr('disabled');
            fields['submit_import'].removeAttr('disabled');
        } else {
            fields['profile_id'].attr('disabled', true);
            fields['submit_import'].attr('disabled', true);
        }
        
        jQuery('#notifications').html('');
        
        if(!enable){
        	title = jQuery(document).find('title').text();
        } else {
        	jQuery(document).find('title').text(title);
        }
        
        if(!enable){
        	resetStyles();
        }
    }

    function processFirstData(data){
        var fields = prepareProcessFields();
        var stepBar = fields['step'].find('.line');
        var progressBar = fields['progress'].find('.line');
        var auctionBar = fields['auction'].find('.line');
        var stepBarProgress = (100 / data['all_steps']) * data['step'];
        
        var titleProgress = parseInt(data['progress']);
        jQuery(document).find('title').text('('+titleProgress.toFixed(0)+'%) ' + title);
        
        stepBar.html(data['step'] + '/' + data['all_steps']).css('width',stepBarProgress+'%');
        auctionBar.html(data['imported_auctions'] + '/' + data['all_auctions']).css('width',data['progress_step']+'%');
        progressBar.html(data['progress'] + '%').css('width',data['progress']+'%');
    }
    
    function resetStyles(){
    	var fields = prepareProcessFields();
        var stepBar = fields['step'].find('.line');
        var progressBar = fields['progress'].find('.line');
        var auctionBar = fields['auction'].find('.line');
        stepBar.removeAttr('style');
        progressBar.removeAttr('style');
        auctionBar.removeAttr('style');
    }
    
    function showError(result){
    	var fields = prepareProcessFields();
        var stepBar = fields['step'].find('.line');
        var progressBar = fields['progress'].find('.line');
        var auctionBar = fields['auction'].find('.line');
        var errorStyles = {'background-color':'red','width':'100%'}; 
        stepBar.css(errorStyles);
        progressBar.css(errorStyles);
        auctionBar.css(errorStyles);
    	
    	jQuery('#notifications').html('<div class="notice notice-error dismissable"><p>'+result['error_message']+'</p></div>');
    }
    
    function showSuccess(result){
    	jQuery('#notifications').html('<div class="notice notice-success dismissable"><p>'+result['message']+'</p></div>');
    }
});
