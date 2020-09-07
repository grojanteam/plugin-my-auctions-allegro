/**
* @version 2.0.0
* @package MyAuctionsAllegro
* @copyright Copyright (C) 2016 - 2019 GroJan Team, All rights reserved.
* @license https://grojanteam.pl/licencje/gnu-gpl
* @author url: https://grojanteam.pl
* @author email l.grochal@grojanteam.pl
*/
jQuery(document).ready(function($) {
	$('.wrap .help').each(function(index,value){
		$(value).tooltip({
			show : null,
	      position: {
	          my: "center bottom-20",
	          at: "center top",
	          using: function( position, feedback ) {
	            $( this ).css( position );
	            $( "<div>" )
	              .addClass( "arrow" )
	              .addClass( feedback.vertical )
	              .addClass( feedback.horizontal )
	              .appendTo( this );
	          }
	        }
		});
	});
});