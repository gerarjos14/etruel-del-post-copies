<?php
/**
 * Metabox Functions
 *
 * @package     wpedpc
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Campaigns custom post type
 *
 * @since 1.0
 * @return void
 */
if ( ! class_exists( 'meta_boxes_campaign' ) ) :
class meta_boxes_campaign {
	
	function __construct() {
		/** Campaign Settings **/
		add_action('add_meta_boxes', array('meta_boxes_campaign', 'register_meta_boxes'));
		add_action('admin_print_styles-post.php', array('meta_boxes_campaign', 'admin_styles'));
		add_action('admin_print_styles-post-new.php', array('meta_boxes_campaign', 'admin_styles'));
		add_action('admin_print_scripts-post.php', array('meta_boxes_campaign', 'admin_scripts') );
		add_action('admin_print_scripts-post-new.php', array('meta_boxes_campaign', 'admin_scripts') );  
		add_action('transition_post_status', array('meta_boxes_campaign', 'default_fields'), 10, 3);
		add_filter('wpedpc_clean_campaign_fields', array('meta_boxes_campaign', 'metabox_fields'), 10 ,1);
		add_action('save_post', array('meta_boxes_campaign', 'meta_box_save'), 10, 2 );
		add_action('wpedpc_meta_box_actions_options', array('meta_boxes_campaign', 'render_campaign_actions_row'), 20 );
		add_action('wpedpc_meta_box_settings_fields', array('meta_boxes_campaign', 'render_campaign_limit_row'), 20 );
		add_action('wpedpc_meta_box_settings_fields', array('meta_boxes_campaign', 'render_campaign_movetotrash_row'), 25 );
		add_action('wpedpc_meta_box_settings_fields', array('meta_boxes_campaign', 'render_campaign_images_row'), 30);
		add_action('wpedpc_meta_box_settings_fields', array('meta_boxes_campaign', 'render_jobschedule_row'), 30 );
		add_action('wpedpc_meta_box_duplicated_fields',  array('meta_boxes_campaign', 'render_whatremain_row'), 20 );
		add_action('wpedpc_meta_box_duplicated_fields', array('meta_boxes_campaign', 'render_whatsee_row'), 20 );
		add_action('wpedpc_meta_box_included_fields', array('meta_boxes_campaign', 'render_posttype_row'), 20 );
		add_action('wpedpc_meta_box_included_fields', array('meta_boxes_campaign', 'render_poststati_row'), 25 );
		add_action('wpedpc_meta_box_included_fields', array('meta_boxes_campaign', 'render_excluded_row'), 30 );
		add_action('wpedpc_meta_box_categories', array('meta_boxes_campaign', 'meta_box_categories_list'), 25 );
		add_action('wpedpc_meta_box_categories', array('meta_boxes_campaign', 'meta_box_ignore_categories'), 20 );
	}
	static function register_meta_boxes() {
		$post_type = 'wpedpcampaign';
		add_meta_box( 'wpedpc_campaign_settings', sprintf( __( '%1$s Settings', 'etruel-del-post-copies' ), post_type_campaign::wpedpc_get_label_singular(), post_type_campaign::wpedpc_get_label_plural() ),  array('meta_boxes_campaign', 'render_settings_meta_box'), $post_type, 'normal', 'default' );
		add_meta_box( 'wpedpc_campaign_included', __( 'Include/Exclude Posts', 'etruel-del-post-copies' ),  array('meta_boxes_campaign', 'render_included_meta_box'), $post_type, 'normal', 'default' );
		add_meta_box( 'wpedpc_campaign_runactions', __( 'Actions to do', 'etruel-del-post-copies' ),  array('meta_boxes_campaign', 'render_actions_meta_box'), $post_type, 'side', 'high' );
		add_meta_box( 'wpedpc_campaign_duplicated', __( 'Duplicated Posts', 'etruel-del-post-copies' ),  array('meta_boxes_campaign', 'render_duplicated_meta_box'), $post_type, 'side', 'default' );
		add_meta_box( 'wpedpc_campaign_categories', __( 'Delete only in Categories', 'etruel-del-post-copies' ), array('meta_boxes_campaign', 'render_categories_meta_box'), $post_type, 'side', 'default' );
		do_action('wpedpc_campaign_metaboxes');
	}
	static function admin_styles() {
		global $post;
		if($post->post_type != 'wpedpcampaign') {
			return $post->ID;
		} 
		wp_dequeue_style('jquery-style');
		wp_enqueue_style('tabs-style', plugins_url('/css/tabs-style.css',__FILE__));
		add_action('admin_head', array('meta_boxes_campaign', 'admin_head_style')) ;
	}
	static function admin_head_style() {
		wp_enqueue_style('head_style', plugins_url('/css/meta-boxes-admin-head.css',__FILE__));
	}
	static function admin_scripts() {
		global $post;
		if($post->post_type != 'wpedpcampaign') {
			return $post->ID;
		}
		wp_dequeue_script( 'autosave' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		add_action('admin_head',array('meta_boxes_campaign', 'admin_head_scripts') );
	}
	static function admin_head_scripts() {
		wp_enqueue_script('wpedpc-meta-boxes-admin-head', plugins_url('/js/meta-boxes-admin-head.js',__FILE__), array( 'jquery' ), WPEDPC_VERSION, false );
		wp_localize_script('wpedpc-meta-boxes-admin-head', 'wpedpc_object_meta_boxes',
				array(	'msg_campaign' => __('Campaign', 'etruel-del-post-copies'),
						'msg_campaign_result' => __('Campaign Results', 'etruel-del-post-copies'),
						'msg_logs' => __('Logs', 'etruel-del-post-copies'),
						'msg_click_to_load_campaigns' => __('Click here to load Logs', 'etruel-del-post-copies'),
						'visibility_trans' => __('Public'),
						'visibility' => 'public',
						'msg_loading' => __('Loading...', 'etruel-del-post-copies' ),
						'img_loading' => get_bloginfo('wpurl').'/wp-admin/images/wpspin_light.gif',
						'msg_error_has_occurred' => __('An error has occurred while attempting to execute this action', 'etruel-del-post-copies' ),
						'msg_loading_campaign' => __('Running Campaign...', 'etruel-del-post-copies'),
						'msg_before_go' => __('You must Save Changes below before "Go"', 'etruel-del-post-copies' ),
						'msg_before_del' => __('Are you sure you want to delete the post with ID:', 'etruel-del-post-copies' )
				) );
	
	}
	static function default_fields( $new_status, $old_status, $post ) {
		if( $post->post_type == 'wpedpcampaign' && $old_status == "new"){		
			$fields['active'] = true;
			$fields['movetotrash'] = true;
			$fields['deletemedia'] = true;
			$fields['period'] = '0 3 * * *';
			$fields['schedule'] = time();
			$fields = apply_filters('wpedpc_clean_campaign_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !empty( $value ) ) {
					$new = apply_filters( 'wpedpc_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	static function metabox_fields($postfields = array()) {
		//$fields['active'] = isset($postfields['active'])		? (bool)$postfields['active']:true;
		
		$fields['active']  = isset($postfields['active']) ? 1 : 0; 
		
		
		$fields['movetotrash']	= isset($postfields['movetotrash'])	? (bool)$postfields['movetotrash']:false;
		$fields['deletemedia']	= isset($postfields['deletemedia'])	? (bool)$postfields['deletemedia']:false;
		$fields['delimgcontent']= isset($postfields['delimgcontent'])? (bool)$postfields['delimgcontent']:false;
		
		
		// ****************** Cron Data
		if(isset($postfields['period']) && !empty($postfields['period']) ) {
			$fields['period'] = $postfields['period'];
		}else{
			if ($postfields['cronminutes'][0]=='*' or empty($postfields['cronminutes'])) {
				if (!empty($postfields['cronminutes'][1]))
					$postfields['cronminutes']=array('*/'.$postfields['cronminutes'][1]);
				else
					$postfields['cronminutes']=array('*');
			}
			if ($postfields['cronhours'][0]=='*' or empty($postfields['cronhours'])) {
				if (!empty($postfields['cronhours'][1]))
					$postfields['cronhours']=array('*/'.$postfields['cronhours'][1]);
				else
					$postfields['cronhours']=array('*');
			}
			if ($postfields['cronmday'][0]=='*' or empty($postfields['cronmday'])) {
				if (!empty($postfields['cronmday'][1]))
					$postfields['cronmday']=array('*/'.$postfields['cronmday'][1]);
				else
					$postfields['cronmday']=array('*');
			}
			if ($postfields['cronmon'][0]=='*' or empty($postfields['cronmon'])) {
				if (!empty($postfields['cronmon'][1]))
					$postfields['cronmon']=array('*/'.$postfields['cronmon'][1]);
				else
					$postfields['cronmon']=array('*');
			}
			if ($postfields['cronwday'][0]=='*' or empty($postfields['cronwday'])) {
				if (!empty($postfields['cronwday'][1]))
					$postfields['cronwday']=array('*/'.$postfields['cronwday'][1]);
				else
					$postfields['cronwday']=array('*');
			}
			$fields['period'] = implode(",",$postfields['cronminutes']).' '.implode(",",$postfields['cronhours']).' '.implode(",",$postfields['cronmday']).' '.implode(",",$postfields['cronmon']).' '.implode(",",$postfields['cronwday']);
		}
		//********* end cron data
		$fields['wpedpc_limit'] = (isset($postfields['wpedpc_limit']) && !empty($postfields['wpedpc_limit']) ) ? intval($postfields['wpedpc_limit']) : 100;
		$fields['titledel']	  = (isset($postfields['titledel']) && !empty($postfields['titledel']) ) ? $postfields['titledel'] : false;
		$fields['contentdel'] = (isset($postfields['contentdel']) && !empty($postfields['contentdel']) ) ? $postfields['contentdel'] : false;
		$fields['allcat']	  = (isset($postfields['allcat']) && !empty($postfields['allcat'])) ? 1 : 0; 
		if (isset($postfields['logs'])) {
			$fields['logs'] = (is_array($postfields['logs'])? $postfields['logs']:array());
		}
		
		
		if(isset($postfields['categories']) && is_array($postfields['categories'])) {
			$fields['categories'] = (array)$postfields['categories'];
		} else if(isset($postfields['post_category']) && is_array($postfields['post_category'])) { 
			$fields['categories'] = (array)$postfields['post_category'];
		} else {
			$fields['categories'] = array();
		}
		
	
		

		if(isset($postfields['cpostypes'])) {
			$fields['cpostypes'] =(array)$postfields['cpostypes'];
		} else {
			$fields['cpostypes'] = array('post' => 1);
		} 
			

		if(isset($postfields['cposstatuses'])) {
			$fields['cposstatuses'] = (array)$postfields['cposstatuses'];
		} else {
			$fields['cposstatuses'] = array('publish' => 1);
		}
			

		if(isset($postfields['minmax']))  $fields['minmax']=$postfields['minmax'];
			else $fields['minmax']='MIN';

		if(isset($postfields['excluded_ids'])) {
			$arrayExcludeIds = explode(',', $postfields['excluded_ids']);
			foreach ($arrayExcludeIds as $key => $value) {
				if (!is_numeric($value)) {
					$arrayExcludeIds[$key] = '';
				} else {
					$arrayExcludeIds[$key] = intval($value);
				}
			}
			$arrayExcludeIds = array_filter($arrayExcludeIds);
			$fields['excluded_ids'] = implode(',', $arrayExcludeIds);

		} else {
			$fields['excluded_ids'] = '';
		}

		$fields['schedule'] = edel_post_copies::wpedpc_cron_next($fields['period']); 


		return apply_filters('wpedpc_metabox_fields',$fields);
	}
	
	static function meta_box_save( $post_id, $post ) {
		if ( ! isset( $_POST['wpedpc_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wpedpc_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return false;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		// The default fields that get saved
		
		$fields = apply_filters('wpedpc_clean_campaign_fields',$_POST);
		$fields = apply_filters('wpedpc_before_save',$fields);

		
		foreach ( $fields as $field => $value ) {
			
			if ( '_wpedpc_limit' == $field ) {
			
			} else {
				if ( !empty( $value ) ) {
					$new = apply_filters('wpedpc_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post_id, $field, $new );
					
				} else {
					delete_post_meta( $post_id, $field );
				}
			}
		}
		do_action( 'wpedpc_save_campaign', $post_id, $post );
	}
	static function render_actions_meta_box() {
		global $post;
		wp_nonce_field( basename( __FILE__ ), 'wpedpc_meta_box_nonce' );
		do_action( 'wpedpc_meta_box_actions_options', $post->ID );
	}
	static function quickoptions() {
		$quickoptions=array(
			//array( 'value'=> "WPdpc_counter",'text' => __('Count Copies (do nothing)', 'etruel-del-post-copies' ) ),
			array( 'value'=> 'wpdpc_show','text' => __('Show table of post copies', 'etruel-del-post-copies' ) ),
			array( 'value'=> 'wpdpc_now','text' => __('Delete Copies Right Now', 'etruel-del-post-copies' ) ),
			array( 'value'=> 'wpdpc_logerase','text' => __('Erase Logs', 'etruel-del-post-copies' ) ),
		);
		$quickoptions = apply_filters('wpedpc_quickdo_options', $quickoptions);

		return $quickoptions;
	}
	static function render_campaign_actions_row($post_id) {
		if(!current_user_can('manage_options' )) {
			return false;
		}
		//<img id="goman" src="'.esc_url(plugins_url( '/images/goman.png', __FILE__ ) ).'"/>
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		$echoHtml = '<div id="wpedpc_limit_wrap"'.$display.'>
						
						<select id="quickdo" name="quickdo" style="display:inline;">
						';
		$quickoptions = self::quickoptions();
		foreach($quickoptions as $key => $option) {
			$echoHtml .= '<option value="'.$option["value"].'">'.$option["text"].'</option>';
		}
		$echoHtml .= '</select>
						<input type="button" name="gosubmit" id="gosubmit" title="'.__('Click to do the selected action.', 'etruel-del-post-copies' ).'" class="button" value="'.__('Go', 'etruel-del-post-copies' ).'" /> 
					</div>';
		echo $echoHtml;
			
	}
	static function render_settings_meta_box() {
		global $post;
		wp_nonce_field( basename( __FILE__ ), 'wpedpc_meta_box_nonce' );
		do_action( 'wpedpc_meta_box_settings_fields', $post->ID );
	}
	static function render_campaign_limit_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		}
		$wpedpc_limit = get_post_meta( $post_id, 'wpedpc_limit', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		$echoHtml = '<div id="wpedpc_limit_wrap"'.$display.'>
			<label>'.__('Limit per time:', 'etruel-del-post-copies' ).' <input class="small-text" type="number" min="0" value="'.$wpedpc_limit.'" name="wpedpc_limit"></label> 
			<p class="description">'.__('The amount of posts queried every time. 0 delete ALL copies at once.(Not Recommended)', 'etruel-del-post-copies' ).'</p>
		</div>';
		echo $echoHtml;
	}
	static function render_campaign_movetotrash_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		}
		$movetotrash = get_post_meta( $post_id, 'movetotrash', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		$echoHtml = '<div id="movetotrash_wrap"'.$display.'>
			<label><input type="checkbox" name="movetotrash" value="1" '.checked($movetotrash, 1, false).' /> <b>'.__('Move to Trash:', 'etruel-del-post-copies' ).'</b><br /></label>
			<p class="description">'.__('If checked, the posts are moved to trash, if not, the posts will be deleted permanently.', 'etruel-del-post-copies' ).'</p>
		</div>';
		echo $echoHtml;
	}
	static function render_campaign_images_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		}
		
		$deletemedia = get_post_meta( $post_id, 'deletemedia', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		
		$echoHtml = '<div id="deletemedia_wrap"'.$display.'>
			<label><input type="checkbox" name="deletemedia" value="1" '.checked($deletemedia, 1, false).' /> <b>'.__('Also delete media attachments:', 'etruel-del-post-copies').'</b></label><br />
			<p class="description">'.__('If checked, the images and all media attached to the post will be deleted or moved to trash.', 'etruel-del-post-copies' ).'</p>
		</div>';
		$delimgcontent = get_post_meta( $post_id, 'delimgcontent', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		$echoHtml .= '<div id="delimgcontent_wrap"'.$display.'>
			<label><input type="checkbox" name="delimgcontent" value="1" '.checked($delimgcontent, 1, false).' /> <b>'.__('Also search and delete images in content:', 'etruel-del-post-copies' ).'</b></label> <br />
			<p class="description">'.__('If checked, all images into the post content will be deleted before delete post. CAUTION: this haven\'t trash.', 'etruel-del-post-copies' ).'</p>
		</div>';
		
		echo $echoHtml;
	}
	static function render_jobschedule_row($post_id) {
    
		if(!current_user_can('manage_options')) {
			return false;
		}
		$active = get_post_meta($post_id, 'active', true );
		$active = (!empty($active)) ? 1 : 0;	
		
		$period = get_post_meta( $post_id, 'period', true );
		$period = (isset($period) && !empty($period) ) ? $period : '0 3 * * *';
		$schedule = get_post_meta( $post_id, 'schedule', true );
		$schedule = (isset($schedule) && !empty($schedule) ) ? $schedule : time(); 
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		
		list($cronstr['minutes'],$cronstr['hours'],$cronstr['mday'],$cronstr['mon'],$cronstr['wday'])=explode(' ',$period,5); 
		
		if (strstr($cronstr['wday'],'*/')) {
			$wday=explode('/',$cronstr['wday']);
		} else {
			$wday=explode(',',$cronstr['wday']);
		}
		
		if (strstr($cronstr['mday'],'*/')) {
			$mday=explode('/',$cronstr['mday']);
		} else {
			$mday=explode(',',$cronstr['mday']);
		}
						
		if (strstr($cronstr['mon'],'*/')) {
			$mon=explode('/',$cronstr['mon']);
		} else {
			$mon=explode(',',$cronstr['mon']);
		}
		
		if (strstr($cronstr['hours'],'*/')) {
			$hours=explode('/',$cronstr['hours']);
		} else {
			$hours=explode(',',$cronstr['hours']);
		}
		if (strstr($cronstr['minutes'],'*/')) {
			$minutes=explode('/',$cronstr['minutes']);
		} else {
			$minutes=explode(',',$cronstr['minutes']);				
		}
						
		$echoHtml = '<div id="jobschedule"'.$display.'>
			<p><strong>'.__('Schedule','etruel-del-post-copies' ).'</strong></p>
			<div class="inside">
				<b>'.__('Active:', 'etruel-del-post-copies' ).'</b> 
				<input type="checkbox" id="active_schedule" name="active" value="1" '.($active ? 'checked="checked"' : '').' /><br />
				
				<div id="timetable" style="display:'.($active ? 'block' : 'none').'">
				<div style="width:130px; float: left;">
					<b>'.__('Weekday:','etruel-del-post-copies').'</b><br />
					<select name="cronwday[]" id="cronwday" style="height:135px;" multiple="multiple">
						<option value="*"'.selected(in_array('*',$wday,true),true,false).'>'.__('Any (*)','etruel-del-post-copies' ).'</option>
						<option value="0"'.selected(in_array('0',$wday,true),true,false).'>'.__('Sunday').'</option>
						<option value="1"'.selected(in_array('1',$wday,true),true,false).'>'.__('Monday').'</option>
						<option value="2"'.selected(in_array('2',$wday,true),true,false).'>'.__('Tuesday').'</option>
						<option value="3"'.selected(in_array('3',$wday,true),true,false).'>'.__('Wednesday').'</option>
						<option value="4"'.selected(in_array('4',$wday,true),true,false).'>'.__('Thursday').'</option>
						<option value="5"'.selected(in_array('5',$wday,true),true,false).'>'.__('Friday').'</option>
						<option value="6"'.selected(in_array('6',$wday,true),true,false).'>'.__('Saturday').'</option>
						</select>
					</div>
					<div style="width:85px; float: left;">
					<b>'.__('Days:','etruel-del-post-copies' ).'</b><br />
						
					<select name="cronmday[]" id="cronmday" style="height:135px;" multiple="multiple">
						<option value="*"'.selected(in_array('*',$mday,true),true,false).'>'.__('Any (*)','etruel-del-post-copies').'</option>
					';

			for ($i=1;$i<=31;$i++) {
				$echoHtml .= '<option value="'.$i.'"'.selected(in_array("$i",$mday,true),true,false).'>'.$i.'</option>';
			}

			$echoHtml .= '
				</select>
				</div>					
				<div style="width:130px; float: left;">
					<b>'.__('Months:','etruel-del-post-copies' ).'</b><br />
					
					
					<select name="cronmon[]" id="cronmon" style="height:135px;" multiple="multiple">
					<option value="*"'.selected(in_array('*',$mon,true),true,false).'>'.__('Any (*)','etruel-del-post-copies' ).'</option>
					<option value="1"'.selected(in_array('1',$mon,true),true,false).'>'.__('January').'</option>
					<option value="2"'.selected(in_array('2',$mon,true),true,false).'>'.__('February').'</option>
					<option value="3"'.selected(in_array('3',$mon,true),true,false).'>'.__('March').'</option>
					<option value="4"'.selected(in_array('4',$mon,true),true,false).'>'.__('April').'</option>
					<option value="5"'.selected(in_array('5',$mon,true),true,false).'>'.__('May').'</option>
					<option value="6"'.selected(in_array('6',$mon,true),true,false).'>'.__('June').'</option>
					<option value="7"'.selected(in_array('7',$mon,true),true,false).'>'.__('July').'</option>
					<option value="8"'.selected(in_array('8',$mon,true),true,false).'>'.__('Augest').'</option>
					<option value="9"'.selected(in_array('9',$mon,true),true,false).'>'.__('September').'</option>
					<option value="10"'.selected(in_array('10',$mon,true),true,false).'>'.__('October').'</option>
					<option value="11"'.selected(in_array('11',$mon,true),true,false).'>'.__('November').'</option>
					<option value="12"'.selected(in_array('12',$mon,true),true,false).'>'.__('December').'</option>
					</select>
				</div>
				<div style="width:85px; float: left;">
					<b>'.__('Hours:','etruel-del-post-copies' ).'</b><br />
					
					<select name="cronhours[]" id="cronhours" style="height:135px;" multiple="multiple">
					<option value="*"'.selected(in_array('*',$hours,true),true,false).'>'.__('Any (*)','etruel-del-post-copies').'</option>';
					
					
					for ($i=0;$i<24;$i++) {
						$echoHtml .= '<option value="'.$i.'"'.selected(in_array("$i",$hours,true),true,false).'>'.$i.'</option>';
					}
					
					
				$echoHtml .= '</select>
				</div>					
				<div style="width:85px; float: left;">
					<b>'.__('Minutes: ','etruel-del-post-copies' ).'</b><br />
					
					<select name="cronminutes[]" id="cronminutes" style="height:135px;" multiple="multiple">
					<option value="*"'.selected(in_array('*',$minutes,true),true,false).'>'.__('Any (*)','etruel-del-post-copies' ).'</option>';
					
					for ($i=0;$i<60;$i=$i+5) {
						$echoHtml .= '<option value="'.$i.'"'.selected(in_array("$i",$minutes,true),true,false).'>'.$i.'</option>';
					}
					
				$echoHtml .= '</select>
				</div>
				<br class="clear" />
				'.__('Working as <a href="http://wikipedia.org/wiki/Cron" target="_blank">Cron</a> job schedule:','etruel-del-post-copies' ).'<i>'.$period.'</i><br />
				<br />
				'.__('Time 	    :').' '.date('D, j M Y H:i',current_time('timestamp')).' ('.current_time('timestamp').')
				<br />
				'.__('H. scheduled:').' '.date('D, j M Y H:i',$schedule).' ('.$schedule.')
				<br />
				'.__('Next runtime:').' '.date('D, j M Y H:i', edel_post_copies::wpedpc_cron_next($period) ).' ('.edel_post_copies::wpedpc_cron_next($period).')
				<br />
				'.__('wp next scheduled:').' '.date('D, j M Y H:i',wp_next_scheduled('wpedpc_func_event') ).' ('.wp_next_scheduled( 'wpedpc_func_event').')
				</div>
				</div>
				</div>
				'; 
		echo $echoHtml;
				
	}
	static function render_duplicated_meta_box() {
		global $post;
		do_action( 'wpedpc_meta_box_duplicated_fields', $post->ID );
	}
	static function render_whatremain_row($post_id) {
		if(!current_user_can( 'manage_options')) {
			return false;
		}
		$minmax = get_post_meta( $post_id, 'minmax', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		$echoHtml = '<div id="whatremain_wrap"'.$display.'>
			<label class="checkbox"><input type="radio" name="minmax" value="MIN"'.checked('MIN', $minmax, false).' /> '.__('Remain First Post ID.', 'etruel-del-post-copies' ).'</label><br />
			<label class="checkbox"><input type="radio" name="minmax" value="MAX"'.checked('MAX', $minmax, false).' /> '.__('Remain Last Post ID.', 'etruel-del-post-copies' ).'</label><br /> 
			<p class="description">'.__('By default always remains first post added and others are deleted.', 'etruel-del-post-copies' ).'<br />
			'.__('If you want to get the lastone check 2nd option.', 'etruel-del-post-copies' ).'</p>
		</div>';
		echo $echoHtml;
	}
	static function render_whatsee_row( $post_id ) {
		global $pagenow;
		if(!current_user_can( 'manage_options')) {
			return false;
		}
			
		$titledel = ( in_array( $pagenow, array( 'post-new.php' ) ) ) ? '1' : get_post_meta( $post_id, 'titledel', true );
		$contentdel = get_post_meta( $post_id, 'contentdel', true );
		$display = is_null( $post_id ) ? ' display: none;' : '';
		
		$echoHtml = '<div id="whatsee_wrap" style="border:1px #aaa solid;padding: 3px;'.$display.'">
			<label class="checkbox"><input type="checkbox" value="1" '.checked($titledel, 1, false).' name="titledel" id="titledel" /> '.__('Look at on Title.', 'etruel-del-post-copies' ).'</label><br/>
			<label class="checkbox"><input type="checkbox" value="1" '.checked($contentdel, 1, false).' name="contentdel" id="contentdel" /> '. __('Look at on Content.', 'etruel-del-post-copies' ).'</label>
		</div>';
		echo $echoHtml;
	}
	static function render_included_meta_box() {
		global $post;
		do_action( 'wpedpc_meta_box_included_fields', $post->ID);
	}
	static function render_posttype_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		}	
		$cpostypes = get_post_meta( $post_id, 'cpostypes', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';

		$echoHtml = '<div id="posttype_wrap" class="postbox edel-box"'.$display.'>
			<h3>'.__('Select Post types to include in deleted.', 'etruel-del-post-copies' ).'</h3>';
			
			// publicos y privados por si se quiere borrar duplicados internos
			//$output = 'names'; // names or objects, note names is the default
			$args = array();
			if( isset($cpostypes['attachment']) ) unset($cpostypes['attachment']);
			$output = 'objects'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'
			$post_types=get_post_types($args,$output,$operator); 
			foreach ($post_types  as $post_type_obj ) {
				$post_type = $post_type_obj->name;
				$post_label = $post_type_obj->labels->name;
				if ($post_type == 'wpedpcampaign') {
					 continue;
				}
				if ($post_type == 'attachment') {
					continue;
				}	  
				$echoHtml .= '<label class="checkbox"><input type="checkbox" class="checkbox" name="cpostypes[' . $post_type . ']" value="1" ';
				if(!isset($cpostypes[$post_type])) {
					$cpostypes[$post_type] = false;
				}
				$echoHtml .= checked($cpostypes[$post_type], 1, false);
				$echoHtml .= ' /> ' . __( $post_label ) .' ('. __( $post_type ) .')</label><br />';
			}
		
		$echoHtml .= '</div>';
		echo $echoHtml;
	}
	static function render_poststati_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		} 
		$cposstatuses = get_post_meta( $post_id, 'cposstatuses', true );
		$display = is_null( $post_id ) ? ' style="display: none;"' : '';
		
		$echoHtml = '<div id="poststati_wrap" class="postbox edel-box"'.$display.'>
			<h3>'.__('Select Post Status to include in deleted.', 'etruel-del-post-copies' ).'</h3>';

		
		$args = array();
		$output = 'objects'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		$post_statuses = get_post_stati($args,$output,$operator); 
		foreach($post_statuses as $post_status_obj) {
				//print_r($post_status_obj);			continue;
			$post_status = $post_status_obj->name;
			$post_label = $post_status_obj->label;
			$echoHtml .= '<label class="checkbox"><input type="checkbox" class="checkbox" name="cposstatuses[' . $post_status . ']" value="1" ';
			if(!isset($cposstatuses[$post_status])) {
				$cposstatuses[$post_status] = false;
			}
				
			$echoHtml .= checked($cposstatuses[$post_status], 1, false);
			$echoHtml .=' /> ' . $post_label . ' ('. __( $post_status ) .')</label><br />';
		}
		
		$echoHtml .='</div>';
		echo $echoHtml;

	}
	static function render_excluded_row($post_id) {
		if(!current_user_can('manage_options')) {
			return false;
		}
		$excluded_ids = get_post_meta( $post_id, 'excluded_ids', true );
		$display = is_null( $post_id ) ? ' display: none;' : '';
		
		$echoHtml = '<div id="excludeposts_wrap"'.$display.'>
			<h3>'.__('Exclude Posts (types) by ID separated by commas:', 'etruel-del-post-copies').'</h3> 
			<input class="large-text" type="text" value="'.$excluded_ids.'" name="excluded_ids">
			<p class="description">'.__('If you want some posts/pages never be deleted by plugin, you can type here its IDs, and will be excluded from delete queries.', 'etruel-del-post-copies' ).'<br>
				'.__('To get Post IDs Go to Posts in your WordPress admin, and click the post you need the ID of. Then, if you look in the address bar of your browser, you\'ll see something like this:', 'etruel-del-post-copies' ).'<br>
				<code>'.admin_url('/post.php').'?post=<b>1280</b>&action=edit</code> '. __('The number, in this case 1280, is the post ID.', 'etruel-del-post-copies' ).'
			</p>
		</div>';
		echo $echoHtml;
	}
	static function render_categories_meta_box() {
		global $post;
		do_action( 'wpedpc_meta_box_categories',$post->ID);
	}
	static function meta_box_categories_list($post_id) {
		$allcat = get_post_meta( $post_id, 'allcat', true ); //allcat
		$campaign_categories = get_post_meta( $post_id, 'categories', true );
		$display =  $allcat ? ' style="display: none;"' : '';
	
		$args = array(
			'descendants_and_self' => 0,
			'selected_cats' => $campaign_categories,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => 'category',
			'checked_ontop' => true
		);
		
		$echoHtml = '<div id="categories_wrap"'.$display.'>
			<span style="float:left; padding: 08px 30px 0 5px; ">
				<input type="checkbox" id="select_all_category" name="todas" value="1" class="catbox">
				<b>'.__('Select all', 'etruel-del-post-copies' ).'</b> 
			</span>
			<h3 class="hndle" style="margin: 0pt; padding: 6px; height: 16px;"><span>'.__('Categories', 'etruel-del-post-copies' ).'</span></h3>
			<div class="inside" style="overflow-y: scroll; overflow-x: hidden; max-height: 500px;">
				<ul id="categories" class="checkbox_cat">';
		echo $echoHtml;
			wp_terms_checklist(0, $args);
		$echoHtml = '</ul>
					</div>
					</div>';
		echo $echoHtml;
	}
	static function meta_box_ignore_categories( $post_id ) {
		$allcat = get_post_meta($post_id, 'allcat', true ); //allcat
		$display = is_null($post_id) ? ' style="display: none;"' : '';
		
		$echoHtml = '<div id="ignore_categories" class=""'.$display.'>
			<label>
			<input type="checkbox" id="allcat" name="allcat" value="1" '.($allcat ? 'checked="checked"' : '').' />
			<b>'.__('Ignore Categories', 'etruel-del-post-copies' ).'</b></label>
			<p class="description">'.__('Ignore post categories in delete queries. Recommended as better performance.', 'etruel-del-post-copies' ).'</p>
			<hr>
		</div>';
		echo $echoHtml;
	}
}
endif;


$wpedpc_meta_boxes_campaign = new meta_boxes_campaign();


?>
