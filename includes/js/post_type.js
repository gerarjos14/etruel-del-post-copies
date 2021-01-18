jQuery(document).ready(function($){
	
	$('div.tablenav.top').prepend(wpedpc_object_post_type.runallbutton);
	$('span:contains("'+wpedpc_object_post_type.slug_msg+'")').each(function (i) {
		$(this).parent().hide();
	});
	$('span:contains("'+wpedpc_object_post_type.password_msg+'")').each(function (i) {
		$(this).parent().parent().hide();
	});
	$('select[name="_status"]').each(function (i) {
		$(this).parent().parent().parent().parent().hide();
	});
	$('span:contains("'+wpedpc_object_post_type.date_msg+'")').each(function (i) {
		$(this).parent().hide();
	});
	$('.inline-edit-date').each(function (i) {
		$(this).hide();
	});
	$('.inline-edit-col-left').append(	$('#optionscampaign').html() );
	$('#optionscampaign').remove();
			
	$('#post-query-submit').remove();

	$('#screen-meta-links').append(wpedpc_object_post_type.clockabove);
	
	
	jQuery('.run_camp_btn').click(function(e) {
		jQuery('html').css('cursor','wait');
		jQuery("div[id=fieldserror]").remove();
		var msgdev = '<p><img width="16" src="'+wpedpc_object_post_type.img_loading+'"/> <span style="vertical-align: top;margin: 10px;">'+wpedpc_object_post_type.msg_loading_campaign+'</span></p>';
		
		jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+msgdev+'</div>');
		var data = {
			campaign_ID: jQuery(this).data("id"),
			action: "wpedpc_run"
		};
		jQuery.post(ajaxurl, data, function(response) {  //si todo ok devuelve LOG sino 0
			
			jQuery('#fieldserror').remove();
			if(jQuery(response).find('response_data').text() == 'error'){
				jQuery("#poststuff").prepend('<div id="fieldserror" class="error fade">'+jQuery(response).find('supplemental message').text()+'</div>');
			} else {
				
				jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+jQuery(response).find('supplemental message').text()+'</div>');
			}
			jQuery('html').css('cursor','auto');
			jQuery('#gosubmit').prop('disabled',false); 
		});
		e.preventDefault();
	});

});
function toggle_click() {
	jQuery('#delbox').toggle();
}

function run_all() {
	
	var selectedItems = new Array();
	jQuery("input[name='post[]']:checked").each(function() {selectedItems.push(jQuery(this).val());});
	if (selectedItems.length == 0) {
		alert(wpedpc_object_post_type.select_to_run_msg); 
		return false; 
	}

	jQuery('html').css('cursor','wait');
	jQuery('#fieldserror').remove();

	var msgdev = '<p><img width="16" src="'+wpedpc_object_post_type.img_loading+'"/> <span style="vertical-align: top;margin: 10px;">'+wpedpc_object_post_type.msg_loading_campaign+'</span></p>';
		
	jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade ajaxstop">'+msgdev+'</div>');
	jQuery("input[name='post[]']:checked").each(function() {
		c_id = jQuery(this).val();
		var data = {
			campaign_ID: c_id ,
			action: "wpedpc_run"
		};
                var running = jQuery.post(ajaxurl, data);
                running.done(function(msgdev){
                    var $xml  = jQuery(msgdev);
                    var response_data = $xml.find('response_data').text();
                    var response_message = $xml.find('message').text();
			if( response_data == 'ERROR' ){
				jQuery(".subsubsub").before('<div id="fieldserror" class="error fade">'+response_message+'</div>');
			}else{
				jQuery(".subsubsub").before('<div id="fieldserror" class="updated fade">'+response_message+'</div>');
				
				 if(selectedItems[selectedItems.length - 1]){        
                    			jQuery('html').css('cursor','auto');
                    			jQuery('.ajaxstop').remove().ajaxStop();    
                		}
			} 
                });
	}).ajaxStop(function() {
			jQuery('html').css('cursor','auto');
			jQuery('.ajaxstop').remove().ajaxStop();
	});
}
