<?php
/**
 * Ajax Actions
 *
 * @package     WPEDPC
 * @subpackage  Ajax Functions
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get HTML table of logs via AJAX.
 *
 * @since 5.0
 * @return logs in html format
 */
class wpedpc_ajax_actions {
	
	function __construct() {
		
		add_action('wp_ajax_wpedpc_show_logs_campaign', array('wpedpc_ajax_actions', 'show_logs_campaign'));
		add_action('wpedpc_show_logs_campaign', array('wpedpc_ajax_actions', 'show_logs'));
		add_action('wp_ajax_wpedpc_run',  array('wpedpc_ajax_actions', 'run_campaign'));
		add_action('wp_ajax_wpdpc_now',  array('wpedpc_ajax_actions', 'run_campaign'));
		add_action('wp_ajax_wpdpc_logerase',  array('wpedpc_ajax_actions', 'erase_logs'));
		add_action('wp_ajax_wpdpc_show',  array('wpedpc_ajax_actions', 'show'));
		add_action('wp_ajax_wpedpc_delapost', array('wpedpc_ajax_actions', 'del_post'));
		
	}
	static function show_logs_campaign() {
		if ( isset( $_POST['post_id'] ) ) {
			$post_id = $_POST['post_id'];
			do_action('wpedpc_show_logs_campaign', $post_id );
		}
	}
	static function show_logs($post_id){
		if( ! current_user_can( 'manage_options' ) ) {
			return false;
		}		
		$wpedpc_logs = get_post_meta($post_id, 'logs', true);
		$echoHtml = '<h2>'.__('Delete Post Copies Logs', 'etruel-del-post-copies').'</h2>
				<div id="poststuff" class="metabox-holder">
				<div id="side-info-column" class="inner-sidebar">
				</div>
				<div id="post-body-content">
				<div class="postbox">
				<div class="inside" style="padding-top: 6px;">
					<table class="widefat" style="overflow-y: scroll; overflow-x: hidden; max-height: 310px;">
						<tr>
							<th scope="col">#</th>
							<th scope="col">'.__('Date', 'etruel-del-post-copies').'</th>
							<th scope="col">'.__('Mode', 'etruel-del-post-copies').'</th>
							<th scope="col">'.__('Status', 'etruel-del-post-copies').'</th>
							<th scope="col">'.__('Finished In', 'etruel-del-post-copies' ).'</th>
							<th scope="col">'.__('Removed', 'etruel-del-post-copies').'</th>
						</tr>';
		if(!empty($wpedpc_logs)){
			$i = 0;
			foreach(array_reverse($wpedpc_logs) as $log) {
				$i++;
				$rk = $i;
						
				$echoHtml .= '<tr>
								<td>'.$rk.'</td>
								<td>'.date('Y-m-d H:i:s', $log['started']).'</td>
								<td>'.((intval($log['mode']) == 1) ? __('Manual', 'etruel-del-post-copies') : __('Auto', 'etruel-del-post-copies')).'</td>
								<td>'.((intval($log['status']) == 0) ? __('OK', 'etruel-del-post-copies') : sprintf(
									// translators: %s represents the number of errors found.	
									__('%s errors', 'etruel-del-post-copies'), intval($log['status']))).'</td>
								<td>'.round($log['took'], 3).' '.__('seconds', 'etruel-del-post-copies').'</td>
								<td>'.sprintf(
									// translators: %s represents the number of errors found.	
									__('%s posts', 'etruel-del-post-copies'), intval($log['removed'])).'</td>
							</tr>'; 
				if($i >= 40) {
					break;
				}
			}
		}

		$echoHtml .= '</table></div></div></div></div>';
		wp_die(esc_html($echoHtml));

	}
	static function run_campaign() {
			
		$response_run = array();
		$response_run['message'] = '';
		$response_run['success'] = false;	
		$quickdo = 'WPdpc_now';
			
		$response = new WP_Ajax_Response;
		if(!isset($_POST['campaign_ID'])) {
			$response->add(array('data' => 'error',
									'supplemental' => array(
										'message' => __('Campaign ID must exist.', 'etruel-del-post-copies')
									)
								)); 
			$response->send();
		} else {
			$post_id = $_POST['campaign_ID'];
		}
		$response_run = apply_filters('wpedpc_run_campaign', $post_id, $quickdo, $response_run );
		$response->add(array('data' => ($response_run['success']? 'success' : 'error'),
									'supplemental' => array(
										'message' => $response_run['message']
									)
								)); 
		$response->send();
			
	}
	public static function erase_logs() {
        // Create response object
        $response = new WP_Ajax_Response();
        
        // 1. Verify nonce for CSRF protection
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'wpdpc_erase_logs')) {
            $response->add(array(
                'data' => 'error',
                'supplemental' => array(
                    'message' => __('Security check failed', 'etruel-del-post-copies')
                )
            ));
            $response->send();
            exit;
        }

        // 2. Check if user has proper capabilities
        if (!current_user_can('manage_options')) {
            $response->add(array(
                'data' => 'error',
                'supplemental' => array(
                    'message' => __('Insufficient permissions', 'etruel-del-post-copies')
                )
            ));
            $response->send();
            exit;
        }

        // 3. Validate and sanitize campaign ID
        $campaign_id = isset($_REQUEST['campaign_ID']) ? absint($_REQUEST['campaign_ID']) : 0;
        if (!$campaign_id) {
            $response->add(array(
                'data' => 'error',
                'supplemental' => array(
                    'message' => __('Invalid campaign ID', 'etruel-del-post-copies')
                )
            ));
            $response->send();
            exit;
        }

        // 4. Verify campaign exists and user has permission to modify it
        $campaign = get_post($campaign_id);
        if (!$campaign || !current_user_can('edit_post', $campaign_id)) {
            $response->add(array(
                'data' => 'error',
                'supplemental' => array(
                    'message' => __('Campaign not found or permission denied', 'etruel-del-post-copies')
                )
            ));
            $response->send();
            exit;
        }

        // 5. Perform the log erasure
        if (update_post_meta($campaign_id, 'logs', array())) {
            $response->add(array(
                'data' => 'success',
                'supplemental' => array(
                    'message' => __('Logs of campaign deleted', 'etruel-del-post-copies')
                )
            ));
        } else {
            $response->add(array(
                'data' => 'error',
                'supplemental' => array(
                    'message' => __('Something went wrong. The log was not deleted.', 'etruel-del-post-copies')
                )
            ));
        }

        $response->send();
        exit;
    }

	static function show() {
		$response_run = array();
		$response_run['message'] = '';
		$response_run['success'] = false;	
		$quickdo = 'WPdpc_show';
			
		$response = new WP_Ajax_Response;
		if(!isset($_POST['campaign_ID'])) {
			wp_die(esc_html__('Campaign ID must exist.', 'etruel-del-post-copies'));
		}
		$post_id = $_POST['campaign_ID'];
		$response_run = apply_filters('wpedpc_run_campaign', $post_id, $quickdo, $response_run );
		wp_die(esc_html($response_run['results']));
	}
	static function del_post() {
		global $wpdb, $wpedpc_options;
		$url = parse_url($_POST['url']);
		parse_str($url['query'], $path);
		$nonce = $path['_wpnonce'];
		$post_id = $_POST['post_id'];
		$response['postid'] = $post_id;
		
		$response_ajax = new WP_Ajax_Response;
		
		if (!wp_verify_nonce( $nonce, 'delete-post_' . $post_id ) ) {
			$response_ajax->add(array('data' => 'error',
										'supplemental' => array(
											'message' => __('Security check.', 'etruel-del-post-copies')
										)
								)); 
			$response_ajax->send();
			wp_die();
		}

		$response['success'] = false;
		$response['message'] = '';
		
		$campaign = new WPEDPC_Campaign($_POST['campaign_ID']);
		if (!$campaign) {
			$response_ajax->add(array('data' => 'error',
										'supplemental' => array(
											'message' => __('Security check.', 'etruel-del-post-copies')
										)
								)); 
			$response_ajax->send();
			wp_die();
		}
		$deletemedia = $campaign->deletemedia ;
		$delimgcontent = $campaign->delimgcontent ;
		$movetotrash = $campaign->movetotrash ;
		$force_delete = !$campaign;
		
		
		$dupe = get_post($post_id);
		$postid		= $dupe->ID;
		$title		= $dupe->post_title;
		$wpcontent	= $dupe->post_content;
		$perma		= get_permalink($postid);
		$wp_posts 				= $wpdb->prefix . "posts";
		$wp_terms 				= $wpdb->prefix . "terms";
		$wp_term_taxonomy 		= $wpdb->prefix . "term_taxonomy";
		$wp_term_relationships 	= $wpdb->prefix . "term_relationships";
		
		if ($postid != ''){
			if($deletemedia) {

				$attachments = get_children([
					'post_parent' => $postid,
					'post_type'   => 'attachment',
					'fields'      => 'ids',
				]);
				
				$ids = $attachments ? array_values($attachments) : [];
				foreach ( $ids as $id ) {		
					wp_delete_attachment($id, $force_delete);
					if($force_delete) {
						unlink(get_attached_file($id));
					}
				}
			}
			if($delimgcontent) {  //images in content					
				$images = apply_filters('wpedpc_parseImages', array(), $wpcontent );
				$itemUrl = $perma;  //self::getReadUrl($perma);
				$images = array_values(array_unique($images));
				if(sizeof($images)) { // Si hay alguna imagen en el contenido
					$img_new_url = array();
					foreach($images as $imagen_src) {
						$imagen_src_real = apply_filters('wpedpc_getRelativeUrl', $itemUrl, $imagen_src);
						if(self::wpedpc_get_domain($imagen_src) == self::wpedpc_get_domain(home_url())){
							$file = $_SERVER['DOCUMENT_ROOT'] .str_replace( home_url(), "",$imagen_src_real );
							if (file_exists( $file )) {
								unlink($file);
							}
						} else {
							// image are external. Different domain.";
						}
					}
				}
			}

			$custom_field_keys = get_post_custom_keys($postid);
			foreach ( $custom_field_keys as $key => $value ) {
				delete_post_meta($postid, $key, '');
			}
			$result = wp_delete_post($postid, $force_delete);
			if (!$result) {  
				// translators: %1$s is the post ID, %2$s is the permalink of the post.
$response['message'] = sprintf(__('!! Problem deleting post %1$s - %2$s !!', 'etruel-del-post-copies'), $postid, $perma);

				$response['success'] = false;
			} else {  
				// translators: %1$s is the post title, %2$s is the post ID.
$response['message'] = sprintf(__("'%1$s' (ID #%2$s) Deleted!", 'etruel-del-post-copies'), $title, $postid);
				$response['success'] = true;
			}
		}
		$response_ajax->add(array('data' => ($response['success']? 'success' : 'error'),
										'supplemental' => array(
											'message' => $response['message']
										)
							)); 
		$response_ajax->send();
		wp_die();
	}


	
}

$wpedpc_ajax_actions = new wpedpc_ajax_actions();






?>