jQuery(document).ready(function(){
    var el = jQuery('.allegro_dialog').first();
    el.dialog({
        autoOpen : false,
        width: '90%',
        modal: true,
        closeOnEscape: true,
        position : {
            my: "center",
            at: "top",
            of: window
        },
        open: function(event, ui) { }
    });

    var allegro_auction = false;
    jQuery('.show_auction_details').on('click',function(e){
        e.preventDefault();
        var allegro_id = jQuery(this).parent().attr('allegro_id');
        var data = {
            'action' : 'gjmaa_get_auction_detail',
            'allegro_id' : allegro_id
        };

        if(allegro_auction)
            return;

        allegro_auction = jQuery.post(MyAjax.ajaxurl,data,function(response) {
            var result = jQuery.parseJSON(response);
            if(result != '') {
                var title = result.itemInfo.itName;
                var description = result.itemInfo.itDescription;
                openDialog(title, description);
            } else {
                alert('No detials');
            }
            allegro_auction = false;
        });
    });

    function openDialog(title,description) {
        el.dialog('open');

        el.dialog({
            height: window.innerHeight - (window.innerHeight*0.05),
            title:title
        });

        el.find('.allegro_description').html('<div id="user_field">'+description+'</div>');
    }
});