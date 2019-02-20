<?php
/**
 * Custom Actions
 *
 * @package     WPEDPC
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks WPEDPC actions, when present in the $_GET superglobal. Every wpedpc_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on admin_init.
 *
 * @since 5.0
 * @return void
*/
function wpedpc_get_actions() {
	if ( isset( $_GET['wpedpc_action'] ) ) {
		do_action( 'wpedpc_' . $_GET['wpedpc_action'], $_GET );
	}
}
add_action( 'admin_init', 'wpedpc_get_actions' );

/**
 * Hooks WPEDPC actions, when present in the $_POST superglobal. Every wpedpc_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on admin_init.
 *
 * @since 5.0
 * @return void
*/
function wpedpc_post_actions() {
	if ( isset( $_POST['wpedpc_action'] ) ) {
		do_action( 'wpedpc_' . $_POST['wpedpc_action'], $_POST );
	}
}
add_action( 'admin_init', 'wpedpc_post_actions' );

