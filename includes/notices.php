<?php
/**
 * Notices
 *
 * @package     WPEDPC
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_init', 'wpedpc_show_notices');
function wpedpc_show_notices() {
	global $wp_version, $user_ID, $wpedpc_admin_message;
//		$notice = delete_option('wpedpc_notices');
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}
	$notice = get_option('wpedpc_notices');
	if (!empty($notice)) {
		foreach($notice as $key => $mess) {
			if($mess['user_ID'] == $user_ID) {
				$class = ($mess['error']) ? "notice notice-error" : "notice notice-success";
				$class .= ($mess['is-dismissible']) ? " is-dismissible" : "";
				$class .= ($mess['below-h2']) ? " below-h2" : "";
				$wpedpc_admin_message .= '<div id="message" class="'.$class.'"><p>'.$mess['text'].'</p></div>';
				unset( $notice[$key] );
			}
		}
		update_option('wpedpc_notices',$notice);
	}

	if (!empty($wpedpc_admin_message)) {
//		add_action('admin_notices', 'wpedpc_admin_notice');
		//send response to admin notice : ejemplo con la función dentro del add_action
		add_action('admin_notices', function() use ($wpedpc_admin_message) {
			echo $wpedpc_admin_message;
		}); 
	}
}

/*//Admin header notify
function wpedpc_admin_notice() {
	global $wpedpc_admin_message;
	echo $wpedpc_admin_message;
}*/
	
/** wpedpc_add_admin_notice
 * 
 * @param type mixed array/string  $new_notice 
 *	optional   ['user_ID'] to shows the notice default = currentuser
 *	optional   ['error'] true or false to define style. Default = false
 *	optional   ['is-dismissible'] true or false to hideable. Default = true
 *	optional   ['below-h2'] true or false to shows above page Title. Default = true
 *	   ['text'] The Text to be displayed. Default = ''
 * 
 */
function wpedpc_add_admin_notice($new_notice) {
	if(is_string($new_notice)) $adm_notice['text'] = $new_notice;
		else $adm_notice['text'] = (!isset($new_notice['text'])) ? '' : $new_notice['text'];
	$adm_notice['error'] = (!isset($new_notice['error'])) ? false : $new_notice['error'];
	$adm_notice['below-h2'] = (!isset($new_notice['below-h2'])) ? true : $new_notice['below-h2'];
	$adm_notice['is-dismissible'] = (!isset($new_notice['is-dismissible'])) ? true : $new_notice['is-dismissible'];
	$adm_notice['user_ID'] = (!isset($new_notice['user_ID'])) ? get_current_user_id() : $new_notice['user_ID'];

	$notice = get_option('wpedpc_notices');
	$notice[] = $adm_notice;
	update_option('wpedpc_notices',$notice);
}