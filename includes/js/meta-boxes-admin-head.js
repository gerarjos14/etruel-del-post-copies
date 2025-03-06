jQuery(document).ready(function ($) {

	$('#wpbody-content .wrap').attr('id', 'wpedpc_campaign_tab');
	wpedpc_tabs = '<div id="wpedpc_tabs"><ul class="tabNavigation">' +
			'<li><a href="#wpedpc_campaign_tab">' + wpedpc_object_meta_boxes.msg_campaign + '</a></li>' +
			'<li><a href="#wpedpc_results_tab">' + wpedpc_object_meta_boxes.msg_campaign_result + '</a></li>' +
			'<li><a href="#wpedpc_logs_tab">' + wpedpc_object_meta_boxes.msg_logs + '</a></li>' +
			'</ul>';
	wpedpc_campaign_tab = $('#wpbody-content .wrap').prop('outerHTML');
	wpedpc_results_tab = '<div id="wpedpc_results_tab"><div class="msg extension-message"><p>Here will be displayed the campaigns results if click the button Run Now</p></div></div>';
	wpedpc_logs_tab = '<div id="wpedpc_logs_tab">' + wpedpc_object_meta_boxes.msg_click_to_load_campaigns + '</div>	</div>';

	$('#wpbody-content .wrap').html(wpedpc_tabs + wpedpc_campaign_tab + wpedpc_results_tab + wpedpc_logs_tab);

	$("#wpedpc_tabs").tabs();
	$('#title').keypress(function (e) {
		$('#title-prompt-text').addClass('screen-reader-text');
	});

	$('#post-visibility-display').text(wpedpc_object_meta_boxes.visibility_trans);
	$('#hidden-post-visibility').val(wpedpc_object_meta_boxes.visibility);
	$('#visibility-radio-' + wpedpc_object_meta_boxes.visibility + '').attr('checked', true);

	$('#wpedpc_logs_tab').click(function () {
		c_ID = $('#post_ID').val();
		var data = {
			post_id: $('#post_ID').val(),
			action: "wpedpc_show_logs_campaign"
		};
		$('#wpedpc_logs_tab').html('<p><img width="12" src="' + wpedpc_object_meta_boxes.img_loading + '" class="mt2">' + wpedpc_object_meta_boxes.msg_loading + '</p>');
		$.post(ajaxurl, data, function (msgdev) {  //si todo ok devuelve LOG sino 0
			if (msgdev == 'ERROR') {
				$("#poststuff").prepend('<div id="fieldserror" class="error fade">' + wpedpc_object_meta_boxes.msg_error_has_occurred + '</div>');
			} else {
				$('#wpedpc_logs_tab').html(msgdev);
			}
		});
	});

	$('#gosubmit').click(function () {

		$(this).prop('disabled', true);
		$('html').css('cursor', 'wait');
		$('#fieldserror').remove();
		msgdev = '<p><img width="12" src="' + wpedpc_object_meta_boxes.img_loading + '" class="mt2">' + wpedpc_object_meta_boxes.msg_loading_campaign + '</p>';

		$("#poststuff").prepend('<div id="fieldserror" class="updated fade ">' + msgdev + '</div>');
		var data = {
			action: jQuery('#quickdo').val(),
			campaign_ID: jQuery('#post_ID').val(),
			_wpnonce: jQuery('#wpdpc_erase_logs').val()
		};


		$.post(ajaxurl, data, function (response) {
			if (data.action == 'wpdpc_now') {
				run(response);
			} else if (data.action == 'wpdpc_logerase') {
				erase_logs(response);
			} else {
				show_tables(response);
			}
		});

	});



	disable_run_now = function () {
		//$('#run_now').attr('disabled','disabled');
		$('#submit').css('background-color', 'coral');
		$('#gosubmit').prop('disabled', true);
		$('#gosubmit').attr('title', wpedpc_object_meta_boxes.msg_before_go);
	}

	// End Go button actions to delete now, show posts etc.


	$(document).on("click", '.notice-dismiss', function (event) {
		$(this).parent().remove();
	});

	// Table list actions  ***********************
	$(document).on("click", ".clickdetail", function () {
		$(this).next().next('.rowdetail').fadeToggle();
	});

	jQuery(document).on("click", ".postdel", function (e) {
		e.preventDefault(); // Prevent default action early
	
		let $this = jQuery(this);
		let post_id = $this.attr('rel');
		let campaign_ID = jQuery('#post_ID').val();
		let url = $this.children('a').attr('href');
	
		// Validate necessary data before proceeding
		if (!post_id || !campaign_ID || !url) {
			alert(wpedpc_object_meta_boxes.msg_before_del);
			return false;
		}
	
		// Confirm deletion
		if (!confirm(wpedpc_object_meta_boxes.msg_before_del + post_id + '?')) {
			return false;
		}
	
		let $loading = jQuery('.' + post_id + '_loading_td');
		$loading.html(`<img width="12" src="${wpedpc_object_meta_boxes.img_loading}" class="mt2"/>`);
	
		let data = {
			url: url,
			post_id: post_id,
			campaign_ID: campaign_ID,
			action: 'wpedpc_delapost'
		};
	
		jQuery.post(ajaxurl, data, function (response) {
			if (response.success) {
				jQuery('.' + post_id).fadeOut(); // Remove the post row smoothly
			} else {
				alert(response.data?.message || wpedpc_object_meta_boxes.msg_before_del);
			}
		})
		.fail(function () {
			alert(__('An error occurred. Please try again.', 'etruel-del-post-copies'));
		})
		.always(function () {
			$loading.html(''); // Clear loading icon after response
		});
	});
	

	// End Table list actions  ***********************


	jQuery("#active_schedule").click(function (e) {
		if (jQuery("#allcat").is(":checked")) {
			jQuery('.catbox').attr('disabled', 'true');
		} else {
			jQuery('.catbox').removeAttr('disabled');
		}
		if (this.checked == 1) {
			jQuery('#timetable').show();
		} else {
			jQuery('#timetable').hide();
		}

	});

	jQuery("#allcat").click(function (e) {
		if (this.checked == 0) {
                    jQuery('#categories_wrap').fadeIn();
		} else {
                    jQuery('#categories_wrap').find('input:checked').each(function (e) {
                       $(this).prop('checked', false); 
                    });
                    setTimeout(function(){
                         jQuery('#categories_wrap').fadeOut();
                    },1000);
		}
	});
        jQuery(".checkbox_cat li label input").click(function (e) {
            if( this.checked ){
                jQuery('#allcat').prop('checked',false);
            }else{
                jQuery('.checkbox_cat li label input').each(function(c){
                        if ( this.checked ){
                            jQuery('#allcat').prop('checked',false);
                            return false;
                        }else{
                        jQuery('#allcat').prop('checked',true);
                    }
                });     
            }
        });
        jQuery("#select_all_category").change(function(){
            if( this.checked){
                jQuery('#allcat').prop('checked',false);
                jQuery('.checkbox_cat li label input').prop('checked', true); 
            }else{
                jQuery('#allcat').prop('checked',true);
                jQuery('.checkbox_cat li label input').prop('checked', false);                 
            }
        });
	$('#titledel').change(function(){
            if( !this.checked){
                $('#contentdel').prop( "checked", true );
            }
        });
	$('#contentdel').change(function(){
            if( !this.checked){
                $('#titledel').prop( "checked", true );
            }
        }); 

});

function run(response) {
	jQuery('#fieldserror').remove();
	if (!response.success) {
		jQuery("#poststuff").prepend('<div id="fieldserror" class="error fade">' + response.data.message + '</div>');
	} else {
		jQuery("#poststuff").prepend('<div id="fieldserror" class="updated fade">' + response.data.message + '</div>');
	}

	jQuery('html').css('cursor', 'auto');
	jQuery('#gosubmit').prop('disabled', false);
}
function erase_logs(response) {
	jQuery('#fieldserror').remove();
	if (!response.success) {
		jQuery("#poststuff").prepend('<div id="fieldserror" class="error fade">' + response.data.message + '</div>');
	} else {
		jQuery("#poststuff").prepend('<div id="fieldserror" class="updated fade">' + response.data.message + '</div>');
	}
	jQuery('html').css('cursor', 'auto');
	jQuery('#gosubmit').prop('disabled', false);
}
function show_tables(response) {
	jQuery('#fieldserror').remove();
	jQuery('a[href="#wpedpc_results_tab"]').trigger("click");
	jQuery('#wpedpc_results_tab').html(response.data.results);
	jQuery('html').css('cursor', 'auto');
	jQuery('#gosubmit').prop('disabled', false);
}
