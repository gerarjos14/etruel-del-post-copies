jQuery(document).ready(function($){

	$('#wpbody-content .wrap').attr('id','wpedpc_campaign_tab');
	wpedpc_tabs = '<div id="wpedpc_tabs"><ul class="tabNavigation">' +
	'<li><a href="#wpedpc_campaign_tab">'+wpedpc_object_meta_boxes.msg_campaign+'</a></li>'+
	'<li><a href="#wpedpc_results_tab">'+wpedpc_object_meta_boxes.msg_campaign_result+'</a></li>'+
	'<li><a href="#wpedpc_logs_tab">'+wpedpc_object_meta_boxes.msg_logs+'</a></li>'+
	'</ul>';
	wpedpc_campaign_tab = $('#wpbody-content .wrap').prop('outerHTML');
	wpedpc_results_tab = '<div id="wpedpc_results_tab">Here will be displayed the campaigns results if click the button Run Now</div>';
	wpedpc_logs_tab = '<div id="wpedpc_logs_tab">'+wpedpc_object_meta_boxes.msg_click_to_load_campaigns+'</div>	</div>';
			
	$('#wpbody-content .wrap').html(wpedpc_tabs + wpedpc_campaign_tab + wpedpc_results_tab + wpedpc_logs_tab);

	$("#wpedpc_tabs").tabs();
	$('#title').keypress(function(e) {
		$('#title-prompt-text').addClass('screen-reader-text');
	});	
	
	$('#post-visibility-display').text(wpedpc_object_meta_boxes.visibility_trans);
	$('#hidden-post-visibility').val(wpedpc_object_meta_boxes.visibility);
	$('#visibility-radio-'+wpedpc_object_meta_boxes.visibility+'').attr('checked', true);

	$('#wpedpc_logs_tab').click(function() {
		c_ID = $('#post_ID').val();
		var data = {
			post_id: $('#post_ID').val(),
			action: "wpedpc_show_logs_campaign"
		};
		$('#wpedpc_logs_tab').html('<p><img width="12" src="'+wpedpc_object_meta_boxes.img_loading+'" class="mt2">'+wpedpc_object_meta_boxes.msg_loading+'</p>');
		$.post(ajaxurl, data, function(msgdev) {  //si todo ok devuelve LOG sino 0
			if(msgdev == 'ERROR' ){
				$("#poststuff").prepend('<div id="fieldserror" class="error fade">'+wpedpc_object_meta_boxes.msg_error_has_occurred+'</div>');
			} else {
				$('#wpedpc_logs_tab').html(msgdev);
			}
		});
	});
			
	$('#gosubmit').click(function() {
		
		$(this).prop('disabled',true); 
		$('html').css('cursor','wait');
		$('#fieldserror').remove();
		msgdev = '<p><img width="12" src="'+wpedpc_object_meta_boxes.img_loading+'" class="mt2">'+wpedpc_object_meta_boxes.msg_loading_campaign+'</p>';

		$("#poststuff").prepend('<div id="fieldserror" class="updated fade ">'+msgdev+'</div>');
		var data = {
			action: jQuery('#quickdo').val(),
			campaign_ID: jQuery('#post_ID').val()
		};
				

		$.post(ajaxurl, data, function(response) {
			if (data.action == 'wpdpc_now') {
				run(response);
			} else if (data.action == 'wpdpc_logerase') {
				erase_logs(response);
			} else {
				show_tables(response);
			}
		});
		
	});
			

			
	disable_run_now = function() {
		//$('#run_now').attr('disabled','disabled');
		$('#submit').css('background-color','coral');
		$('#gosubmit').prop('disabled',true);
		$('#gosubmit').attr('title', wpedpc_object_meta_boxes.msg_before_go);
	}

	// End Go button actions to delete now, show posts etc.

			
	$(document).on("click", '.notice-dismiss', function(event) {
		$(this).parent().remove();
	});
			
			// Table list actions  ***********************
	$(document).on("click", ".clickdetail", function(){
		$(this).next().next('.rowdetail').fadeToggle();
	});
	
	$(document).on("click", ".postdel", function(e){
		if (confirm(wpedpc_object_meta_boxes.msg_before_del+$(this).attr('rel')+'?') == false) {
			e.preventDefault();
			return false;
		}
	
		jQuery('.'+$(this).attr('rel')+'_loading_td').html('<img width="12" src="'+wpedpc_object_meta_boxes.img_loading+'" class="mt2"/>')
		
		var data = {
			url: $(this).children('a').attr('href'),
			post_id: $(this).attr('rel'),
			campaign_ID: jQuery('#post_ID').val(),
			action: 'wpedpc_delapost'
		}
		
		
		$.post(ajaxurl, data, function(response) {
			if(jQuery(response).find('response_data').text() == 'error'){
				alert(jQuery(response).find('supplemental message').text());
			} else {
				jQuery('.'+data.post_id+'').fadeOut();
			}	
		});
		
		e.preventDefault();
	});	

			// End Table list actions  ***********************
	
	
	jQuery("#active_schedule").click(function(e) {
		if(jQuery("#allcat").is(":checked")){
			jQuery('.catbox').attr('disabled','true');
		} else {
			jQuery('.catbox').removeAttr('disabled');
		}
		if(this.checked == 1){ 
			jQuery('#timetable').show();
		} else {
			jQuery('#timetable').hide();
		}
		
	}); 
	
	jQuery("#allcat").click(function(e) {
		if(this.checked == 0) {
			jQuery('#categories_wrap').fadeIn();
		} else {
			jQuery('#categories_wrap').fadeOut();
		}
	}); 
	jQuery("#select_all_category").click(function(e) {
		jQuery('.checkbox_cat li label input').attr('checked', this.checked);
	}); 
	

});

function run(response) {

	if ( !jQuery('#allcat').attr('checked') ){
		var selectedItems = new Array();
		jQuery("input[name='post_category[]']:checked").each(function() {selectedItems.push(jQuery(this).val());});
		if (selectedItems.length == 0) {
		  jQuery('html').css('cursor','auto');
		  jQuery('#gosubmit').prop('disabled',false);
		  jQuery('#fieldserror').remove();
		  alert(wpedpc_object_meta_boxes.select_to_something_msg);  
		  
		}else{
			jQuery('#fieldserror').remove();
			jQuery("#poststuff").prepend('<div id="fieldserror" class="updated fade">'+jQuery(response).find('supplemental message').text()+'</div>');
			jQuery('html').css('cursor','auto');
			jQuery('#gosubmit').prop('disabled',false); 
		}
	}else{
		jQuery('#fieldserror').remove();
		if(jQuery(response).find('response_data').text() == 'error'){
			jQuery("#poststuff").prepend('<div id="fieldserror" class="error fade">'+jQuery(response).find('supplemental message').text()+'</div>');
		} else {
			jQuery("#poststuff").prepend('<div id="fieldserror" class="updated fade">'+jQuery(response).find('supplemental message').text()+'</div>');
		}
	
		jQuery('html').css('cursor','auto');
		jQuery('#gosubmit').prop('disabled',false); 
	}	
} 
function show_tables(response) {
	jQuery('#fieldserror').remove();
	jQuery('a[href="#wpedpc_results_tab"]').trigger("click");
	jQuery('#wpedpc_results_tab').html(response);
	jQuery('html').css('cursor','auto');
	jQuery('#gosubmit').prop('disabled',false); 
}
