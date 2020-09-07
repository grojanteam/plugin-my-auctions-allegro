/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function($){
	$('.collect_allegro_click a').on('click',function(e){
		var divElement = $(this).parents('.collect_allegro_click');
		var id = $(divElement).data('auction-id');
		var profile_id = $(divElement).data('profile-id');
		
		var data = {
			'auction_id' : id,
			'profile_id' : profile_id,
			'action' : 'gjmaa_collect_click',
			'controller' : 'auctions',
			'nonce' : gjmaa_ajax_url.nonce
		};
		
		jQuery.post(gjmaa_ajax_url.ajax_url,data, function(){
			
		});
	});
	
	const observer = lozad(); // lazy loads elements with default selector as '.lozad'
	observer.observe();
	
	var inner_allegro = $('#inner_allegro');
	closedSlider(inner_allegro,{'margin-left':'-280px'},{'margin-left':'10px'});
	
	function closedSlider(item,slidebox_on,slidebox_off){
			$(document).on('mouseover','.slidebox_image',function(){
				$(document).off('mouseover','.slidebox_image');
				item.animate(slidebox_on,'slow',function(){
					openedSlider(item,slidebox_on,slidebox_off);
				});
			});
	}
	function openedSlider(item,slidebox_on,slidebox_off){
			$(document).on('mouseleave','.slidebox',function(e){
				$(document).off('mouseleave','.slidebox');
				item.animate(slidebox_off,'slow',function(){
					closedSlider(item,slidebox_on,slidebox_off);
				});
			});
	}

});