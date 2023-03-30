<?php
/**
 * Post Type Functions
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
if ( ! class_exists( 'post_type_campaign' ) ) :
class post_type_campaign {
	function __construct() {
		
		add_action( 'init', array('post_type_campaign', 'setup'), 1 );
		add_filter( 'enter_title_here', array('post_type_campaign', 'wpedpc_change_default_title'));
		add_filter( 'post_updated_messages', array('post_type_campaign', 'wpedpc_updated_messages'));
		add_filter( 'bulk_post_updated_messages', array('post_type_campaign', 'wpedpc_bulk_updated_messages'), 10, 2 );
		add_action('admin_init',  array('post_type_campaign', 'wpedpcampaigns_init'));
		
	}
	static function setup() {
		$slug     = defined( 'WPEDPC_SLUG' ) ? WPEDPC_SLUG : 'wpedpcampaigns';
		$rewrite  = defined( 'WPEDPC_DISABLE_REWRITE' ) && WPEDPC_DISABLE_REWRITE ? false : array('slug' => $slug, 'with_front' => false);

		$wpedpcampaign_labels =  apply_filters( 'wpedpc_wpedpcampaign_labels', array(
			'name'               => _x( '%2$s', 'wpedpcampaign post type name', 'etruel-del-post-copies' ),
			'singular_name'      => _x( '%1$s', 'singular wpedpcampaign post type name', 'etruel-del-post-copies' ),
			'add_new'            => __( 'Add New', 'etruel-del-post-copies' ),
			'add_new_item'       => __( 'Add New %1$s', 'etruel-del-post-copies' ),
			'edit_item'          => __( 'Edit %1$s', 'etruel-del-post-copies' ),
			'new_item'           => __( 'New %1$s', 'etruel-del-post-copies' ),
			'all_items'          => __( 'All Campaigns', 'etruel-del-post-copies' ),
			'view_item'          => __( 'View %1$s', 'etruel-del-post-copies' ),
			'search_items'       => __( 'Search %2$s', 'etruel-del-post-copies' ),
			'not_found'          => __( 'No %2$s found', 'etruel-del-post-copies' ),
			'not_found_in_trash' => __( 'No %2$s found in Trash', 'etruel-del-post-copies' ),
			'parent_item_colon'  => '',
			'menu_name'          => _x( 'Deletes', 'wpedpcampaign post type menu name', 'etruel-del-post-copies' )
		) );

		foreach ( $wpedpcampaign_labels as $key => $value ) {
		   $wpedpcampaign_labels[ $key ] = sprintf( $value, self::wpedpc_get_label_singular(), self::wpedpc_get_label_plural() );
		}

		$wpedpcampaign_args = array(
			'labels'             => $wpedpcampaign_labels,
			'public'			 => false,
			'exclude_from_search'=> true,
			'publicly_queryable' => false,
			'show_ui'			 => true, 
			'show_in_menu'		 => true, 
			'query_var'			 => true,
			'rewrite'            => $rewrite,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => apply_filters( 'wpedpc_wpedpcampaign_supports', array( 'title' ) ),
			'menu_icon'			 => esc_url(plugins_url( '/images/wpedpc.ico.png', __FILE__ ) ), 
			'register_meta_box_cb' => array('meta_boxes_campaign', 'register_meta_boxes'),
		);
		register_post_type( 'wpedpcampaign', apply_filters( 'wpedpc_wpedpcampaign_post_type_args', $wpedpcampaign_args ) );
		
	}
	
	static function wpedpc_get_default_labels() {
		$defaults = array(
		   'singular' => __( 'Campaign', 'etruel-del-post-copies' ),
		   'plural'   => __( 'Campaigns of Deletes','etruel-del-post-copies' )
		);
		return apply_filters( 'wpedpc_default_wpedpcampaigns_name', $defaults );
	}
	
	static function wpedpc_get_label_singular( $lowercase = false ) {
		$defaults = self::wpedpc_get_default_labels();
		return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
	}

	
	static function wpedpc_get_label_plural( $lowercase = false ) {
		$defaults = self::wpedpc_get_default_labels();
		return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
	}

	
	static function wpedpc_change_default_title( $title ) {

		 $screen = get_current_screen();

		 if ( 'wpedpcampaign' == $screen->post_type ) {
			$label = self::wpedpc_get_label_singular();
			$title = sprintf( __( 'Enter %s name here', 'etruel-del-post-copies' ), $label );
		 }

		 return $title;
	}
	static function wpedpc_updated_messages( $messages ) {
		global $post, $post_ID;


		$messages['wpedpcampaign'] = array(
			1 => sprintf( __( '%1$s updated.', 'etruel-del-post-copies' ), self::wpedpc_get_label_singular() ),
			4 => sprintf( __( '%1$s updated.', 'etruel-del-post-copies' ), self::wpedpc_get_label_singular() ),
			6 => sprintf( __( '%1$s published.', 'etruel-del-post-copies' ), self::wpedpc_get_label_singular() ),
			7 => sprintf( __( '%1$s saved.', 'etruel-del-post-copies' ), self::wpedpc_get_label_singular() ),
			8 => sprintf( __( '%1$s submitted.', 'etruel-del-post-copies' ), self::wpedpc_get_label_singular() )
		);

		return $messages;
	}
	static function wpedpc_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
		$singular = self::wpedpc_get_label_singular();
		$plural   = self::wpedpc_get_label_plural();
		$bulk_messages['wpedpcampaign'] = array(
			'updated'   => sprintf( _n( '%1$s %2$s updated.', '%1$s %3$s updated.', $bulk_counts['updated'], 'etruel-del-post-copies' ), $bulk_counts['updated'], $singular, $plural ),
			'locked'    => sprintf( _n( '%1$s %2$s not updated, somebody is editing it.', '%1$s %3$s not updated, somebody is editing them.', $bulk_counts['locked'], 'etruel-del-post-copies' ), $bulk_counts['locked'], $singular, $plural ),
			'deleted'   => sprintf( _n( '%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $bulk_counts['deleted'], 'etruel-del-post-copies' ), $bulk_counts['deleted'], $singular, $plural ),
			'trashed'   => sprintf( _n( '%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $bulk_counts['trashed'], 'etruel-del-post-copies' ), $bulk_counts['trashed'], $singular, $plural ),
			'untrashed' => sprintf( _n( '%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $bulk_counts['untrashed'], 'etruel-del-post-copies' ), $bulk_counts['untrashed'], $singular, $plural )
		);

		return $bulk_messages;
	}
	static function wpedpcampaigns_init() {
		global $pagenow;
		add_action('wp_ajax_wpedpc_run', 'wpedpc_action_run' );
		//QUICK ACTIONS
		add_action('admin_action_wpedpc_toggle_campaign', array('post_type_campaign', 'wpedpc_toggle_campaign'));
		add_action('admin_action_wpedpc_reset_campaign', array('post_type_campaign', 'wpedpc_reset_campaign'));

		if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'wpedpcampaign') ) {
			add_filter('post_row_actions',  array('post_type_campaign', 'wpedpc_quick_actions'), 10, 2);
			add_filter('disable_months_dropdown', array('post_type_campaign', 'wpedpc_disable_months_dropdown'),999,2 );
			add_action('admin_print_styles-edit.php', array('post_type_campaign', 'wpedpc_list_admin_styles') );
			add_action('admin_print_scripts-edit.php', array('post_type_campaign', 'wpedpc_list_admin_scripts') );
		}
	}
	static function wpedpc_disable_months_dropdown($disabled, $typenow) {
		return true;
	}
	static function wpedpc_list_admin_styles(){
		
	}

	static function wpedpc_list_admin_scripts(){
		add_action('admin_head', array('post_type_campaign', 'wpedpc_campaigns_admin_head') );
	}
	static function wpedpc_campaigns_admin_head() {
		global $post, $post_type;
		if($post_type != 'wpedpcampaign') {
			return $post->ID;
		}
		wp_enqueue_script( 'wpedpc-post-type', plugins_url('/js/post_type.js',__FILE__), array( 'jquery' ), WPEDPC_VERSION, true );
		
		wp_localize_script('wpedpc-post-type', 'wpedpc_object_post_type',
				array(	'runallbutton' => '<div style="margin: 2px 5px 0 0;float:left;font-weight:bold; color: #444; background-color: yellow; text-shadow: none;" id="run_all" onclick="javascript:run_all();" class="button-primary">'. __('Run Selected Campaigns', 'etruel-del-post-copies'  ) . '</div>' ,
						'clockabove' => '<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">'
											. '<button type="button" id="show-clock" class="button show-clock" aria-controls="clock-wrap" aria-expanded="false">'
											. date_i18n( get_option('date_format').' '. get_option('time_format') )
											. '</button>'
											. '</div>',
						'slug_msg' => __('Slug'),
						'password_msg' => __('Password'),
						'date_msg' => __('Date'),
						'img_loading' => get_bloginfo('wpurl').'/wp-admin/images/wpspin_light.gif',
						'msg_loading_campaign' => __('Running Campaign...', 'etruel-del-post-copies'),
						'select_to_run_msg' => __('Please select campaign(s) to Run.', 'etruel-del-post-copies')
				) );

	}
	static function wpedpc_copy_duplicate_campaign($post, $status = '', $parent_id = '') {
		if ($post->post_type != 'wpedpcampaign') {
			return false;
		} 
		$prefix = '';
		$suffix = __('(Copy)',  'etruel-del-post-copies' ) ;
		if (!empty($prefix)) $prefix.= ' ';
		if (!empty($suffix)) $suffix = ' '.$suffix;
		$status = 'publish';

		$new_post = array(
			'menu_order' => $post->menu_order,
			'guid' => $post->guid,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'pinged' => $post->pinged,
			'post_author' => @$post->author,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent' => $post->post_parent,
			'post_password' => $post->post_password,
			'post_status' => $status,
			'post_title' => $prefix.$post->post_title.$suffix,
			'post_type' => $post->post_type,
			'to_ping' => $post->to_ping, 
			'post_date' => $post->post_date,
			'post_date_gmt' => get_gmt_from_date($post->post_date)
		);	

		$new_post_id = wp_insert_post($new_post);

		$post_meta_keys = get_post_custom_keys($post->ID);
		if (!empty($post_meta_keys)) {
			foreach ($post_meta_keys as $meta_key) {
				$meta_values = get_post_custom_values($meta_key, $post->ID);
				foreach ($meta_values as $meta_value) {
					$meta_value = maybe_unserialize($meta_value);
					add_post_meta($new_post_id, $meta_key, $meta_value);
				}
			}
		}
		return $new_post_id;
	}
	static function wpedpc_copy_campaign($status = '') {
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpedpc_copy_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'etruel-del-post-copies' ));
		}

		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$post = get_post($id);

		// Copy the post and insert it
		if (isset($post) && $post!=null) {
			$new_id =  self::wpedpc_copy_duplicate_campaign($post, $status);

			if ($status == ''){
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
			}
			exit;

		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die(esc_attr(__('Copy campaign failed, could not find original:',  'etruel-del-post-copies' )) . ' ' . $id);
		}
	}
	static function wpedpc_toggle_campaign($status = ''){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpedpc_toggle_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'etruel-del-post-copies' ));
		}
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$active = get_post_meta( $id, 'active', true );
		
		if(update_post_meta( $id, 'active', !$active )) {
			$notice= (!$active) ? __('Campaign schedule activated',  'etruel-del-post-copies' ) : __('Campaign schedule Deactivated',  'etruel-del-post-copies' );
		}else {
			$notice= __("Can't change campaign status.  You can try to refresh the page or inside campaign's edit.",  'etruel-del-post-copies' );
		}
		wpedpc_add_admin_notice( array('text' => $notice .' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );

		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpedpcampaign') );
	}
	static function wpedpc_reset_campaign($status = ''){
		if (!(isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpedpc_reset_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'etruel-del-post-copies' ));
		}
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		if(update_post_meta($id, 'logs', array())) {
			wpedpc_add_admin_notice(array('text' => __('Reset Campaign',  'etruel-del-post-copies' ).' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );
		}else {
			wpedpc_add_admin_notice(array('text' => __("Can't change campaign status.  You can try to refresh the page or inside campaign's edit.",  'etruel-del-post-copies' ).'', 'below-h2'=>false ) );
		}
		
		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpedpcampaign') );
	}
	static function wpedpc_clear_campaign(){
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'wpedpc_clear_campaign' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No campaign ID has been supplied!',  'etruel-del-post-copies' ));
		}

		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$campaign_data =   WPeMatico :: get_campaign( $id );

		$campaign_data['cronnextrun']= WPeMatico :: time_cron_next($campaign_data['cron']); //set next run
		$campaign_data['stoptime']   = current_time('timestamp');
		$campaign_data['lastrun']  	 = $campaign_data['starttime'];
		$campaign_data['lastruntime']= $campaign_data['stoptime']-$campaign_data['starttime'];
		$campaign_data['starttime']  = '';

		//WPeMatico :: update_campaign( $id, $campaign_data );
		wpedpc_add_admin_notice( array('text' => __('Campaign cleared',  'etruel-del-post-copies' ).' <b>'.  get_the_title($id).'</b>', 'below-h2'=>false ) );

		// Redirect to the post list screen
		wp_redirect( admin_url( 'edit.php?post_type=wpedpcampaign') );
	}
	static function wpedpc_action_link( $id, $context = 'display', $actionslug = '' ) {
		global $post;
		if ( !$post == get_post( $id ) ) return;
		if ( $actionslug == '' ) return;
		switch ($actionslug){ 
		case 'copy':
			$action_name = 'wpedpc_copy_campaign';
			break;
		case 'toggle':
			$action_name = 'wpedpc_toggle_campaign';
			break;
		case 'reset':
			$action_name = 'wpedpc_reset_campaign';
			break;
		case 'delhash':
			$action_name = 'wpedpc_delhash_campaign';
			break;
		case 'clear':
			$action_name = 'wpedpc_clear_campaign';
			break;			
		}
		if ( 'display' == $context ) 
			$action = '?action='.$action_name.'&amp;post='.$post->ID;
		else 
			$action = '?action='.$action_name.'&post='.$post->ID;

		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object )	return;

		return apply_filters('wpedpc_action_link', admin_url("admin.php".$action), $post->ID, $context );
	}
	static function wpedpc_quick_actions( $actions ) {
		global $post;
		if( $post->post_type == 'wpedpcampaign' ) {
			$can_edit_post = current_user_can( 'edit_post', $post->ID );

			unset( $actions['inline hide-if-no-js'] );

			$actions = array();
			if ( $can_edit_post && 'trash' != $post->post_status ) {
				$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
			}
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				$post_type_object = get_post_type_object( $post->post_type );
				if ( 'trash' == $post->post_status ) {
					$actions['untrash'] = '<a title="'. esc_attr( __( 'Restore this item from the Trash' ) ) .'" href="'. wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ).'">'. __( 'Restore' ).'</a>';
				} elseif (EMPTY_TRASH_DAYS ) {
					$actions['trash'] = '<a class="submitdelete" title="'. esc_attr( __( 'Move this item to the Trash' ) ) .'" href="'.get_delete_post_link( $post->ID ).'">'. __( 'Trash' ) .'</a>';
				}
				if ('trash' == $post->post_status || !EMPTY_TRASH_DAYS ) {
					$actions['delete'] = '<a class="submitdelete" title="'.esc_attr( __( 'Delete this item permanently')).'" href="'.get_delete_post_link( $post->ID, '', true).'">'. __( 'Delete Permanently' ).'</a>';
				}
			}
			if ('trash' != $post->post_status ) {

				$acnow = apply_filters( 'wpedpc_is_campaign_active', $post->ID );
				$atitle = ( $acnow ) ? esc_attr(__('Deactivate this campaign', 'etruel-del-post-copies' )) : esc_attr(__("Activate schedule", 'etruel-del-post-copies' ));
				$alink = ($acnow) ? __('Deactivate', 'etruel-del-post-copies' ): __('Activate','etruel-del-post-copies' );
				$actions['toggle'] = '<a href="'. self::wpedpc_action_link( $post->ID , 'display','toggle').'" title="' . $atitle . '">' .  $alink . '</a>';
				//$actions['copy'] = '<a href="'. self::wpedpc_action_link( $post->ID , 'display','copy').'" title="' . esc_attr(__('Clone this item', 'etruel-del-post-copies' )) . '">' .  __('Copy', 'etruel-del-post-copies' ) . '</a>';
				$actions['reset'] = '<a href="'. self::wpedpc_action_link( $post->ID , 'display','reset').'" title="' . esc_attr(__('Reset post count', 'etruel-del-post-copies' )) . '">' .  __('Reset', 'etruel-del-post-copies' ) . '</a>';
				$actions['runnow'] = '<a href="#" class="run_camp_btn" data-id="'.$post->ID.'" title="' . esc_attr(__('Run Now this campaign', 'etruel-del-post-copies' )) . '">' .  __('Run Now', 'etruel-del-post-copies' ) . '</a>';
					
			}
		}
		return $actions;
	}	
	
} 

endif;

$post_type_campaign = new post_type_campaign();

?>