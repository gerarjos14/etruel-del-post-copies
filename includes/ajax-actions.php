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
 * @return  in html format
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
		if(!isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], 'etruel-del-post-copies')){
			return false;
		}

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
		wp_die($echoHtml);

	}
	public static function run_campaign() {
		if(!isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], 'etruel-del-post-copies')){
			return false;
		}
		// Verify campaign_ID exists and sanitize input
		if (!isset($_POST['campaign_ID'])) {
			wp_send_json_error(array('message' => __('Campaign ID must exist.', 'etruel-del-post-copies')));
		}
	
		$post_id = absint($_POST['campaign_ID']); // Ensure it's a valid integer
		$quickdo = 'WPdpc_now';
	
		// Apply the campaign filter
		$response_run = apply_filters('wpedpc_run_campaign', $post_id, $quickdo, array(
			'message' => '',
			'success' => false
		));
	
		// Send JSON response based on the filter result
		if ($response_run['success']) {
			wp_send_json_success(array('message' => $response_run['message']));
		} else {
			wp_send_json_error(array('message' => $response_run['message']));
		}
	}
	public static function erase_logs() {
		// 1. Verify nonce for CSRF protection
		if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'etruel-del-post-copies')) {
			wp_send_json_error(array('message' => __('Security check failed', 'etruel-del-post-copies')));
		}
	
		// 2. Check if user has proper capabilities
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('Insufficient permissions', 'etruel-del-post-copies')));
		}
	
		// 3. Validate and sanitize campaign ID
		$campaign_id = isset($_REQUEST['campaign_ID']) ? absint($_REQUEST['campaign_ID']) : 0;
		if (!$campaign_id || get_post_status($campaign_id) === false) {
			wp_send_json_error(array('message' => __('Invalid campaign ID or campaign not found', 'etruel-del-post-copies')));
		}
	
		// 4. Check if user can edit the campaign
		if (!current_user_can('edit_post', $campaign_id)) {
			wp_send_json_error(array('message' => __('Permission denied', 'etruel-del-post-copies')));
		}
	
		// 5. Delete the logs meta key
		if (delete_post_meta($campaign_id, 'logs')) {
			wp_send_json_success(array('message' => __('Logs of campaign deleted', 'etruel-del-post-copies')));
		} else {
			wp_send_json_error(array('message' => __('Something went wrong. The log was not deleted.', 'etruel-del-post-copies')));
		}
	}

	public static function show() {
		if(!isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], 'etruel-del-post-copies')){
			return false;
		}
		// Verify and sanitize campaign_ID
		if (!isset($_POST['campaign_ID'])) {
			wp_send_json_error(array('message' => __('Campaign ID must exist.', 'etruel-del-post-copies')));
		}
	
		$post_id = absint($_POST['campaign_ID']); // Ensure it's an integer
		$quickdo = 'WPdpc_show';
	
		// Apply campaign filter
		$response_run = apply_filters('wpedpc_run_campaign', $post_id, $quickdo, array());
	
		// Return the results
		wp_send_json_success(array('results' => $response_run['results']));
	}
	
	public static function del_post() {
		if(!isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce'], 'etruel-del-post-copies')){
			return false;
		}
		// Verify nonce for security
		if (!isset($_POST['url'], $_POST['post_id'], $_POST['campaign_ID'])) {
			wp_send_json_error(array('message' => __('Missing required parameters.', 'etruel-del-post-copies')));
		}
	
		// Extract nonce from URL
		$url = esc_url_raw($_POST['url']);
		parse_str(parse_url($url, PHP_URL_QUERY), $path);
		$nonce = $path['_wpnonce'] ?? '';
	
		$post_id = absint($_POST['post_id']);
		$campaign_id = absint($_POST['campaign_ID']);
	
		// Security check
		if (!wp_verify_nonce($nonce, 'delete-post_' . $post_id)) {
			wp_send_json_error(array('message' => __('Security check failed.', 'etruel-del-post-copies')));
		}
	
		// Validate campaign exists
		$campaign = get_post_meta($campaign_id, '_campaign_settings', true);
		if (!$campaign) {
			wp_send_json_error(array('message' => __('Campaign not found.', 'etruel-del-post-copies')));
		}
	
		// Extract deletion settings
		$deletemedia = $campaign['deletemedia'] ?? false;
		$delimgcontent = $campaign['delimgcontent'] ?? false;
		$force_delete = empty($campaign['movetotrash']);
	
		// Get post details
		$post = get_post($post_id);
		if (!$post) {
			wp_send_json_error(array('message' => __('Post not found.', 'etruel-del-post-copies')));
		}
	
		$post_title = $post->post_title;
		$permalink = get_permalink($post_id);
	
		// Delete media attachments if required
		if ($deletemedia) {
			$attachments = get_children([
				'post_parent' => $post_id,
				'post_type'   => 'attachment',
				'fields'      => 'ids',
			]);
	
			foreach ($attachments as $attachment_id) {
				wp_delete_attachment($attachment_id, $force_delete);
			}
		}
	
		// Delete images inside content
		if ($delimgcontent) {
			$images = apply_filters('wpedpc_parseImages', array(), $post->post_content);
			foreach ($images as $image_src) {
				$image_path = str_replace(home_url(), ABSPATH, $image_src);
				if (file_exists($image_path)) {
					@unlink($image_path);
				}
			}
		}
	
		// Delete all custom fields in one go
		delete_post_meta_by_key($post_id);
	
		// Delete the post
		$result = wp_delete_post($post_id, $force_delete);
	
		if (!$result) {
			wp_send_json_error(array('message' => sprintf(__('Error deleting post %1$s - %2$s', 'etruel-del-post-copies'), $post_id, $permalink)));
		}
	
		wp_send_json_success(array('message' => sprintf(__("'%1$s' (ID #%2$s) Deleted!", 'etruel-del-post-copies'), $post_title, $post_id)));
	}
}

$wpedpc_ajax_actions = new wpedpc_ajax_actions();






?>