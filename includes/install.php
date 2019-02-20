<?php
/**
 * Install Function
 *
 * @package     WPEDPC
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types,
 * flushing rewrite rules to initiate the new 'wpedpcampaigns' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the WPEDPC Welcome
 * screen.
 *
 * @since 5.0
 * @global $wpdb
 * @global $wpedpc_options
 * @global $wp_version
 * @return void
 */
function wpedpc_install() {    // on activate
	global $wpdb, $wpedpc_options, $wp_version;

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'wpedpc_version' );
	if ( $current_version ) {
		update_option( 'wpedpc_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();


	// Populate some default values
/*	foreach( wpedpc_get_registered_settings() as $tab => $settings ) {

		foreach ( $settings as $option ) {

			if( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
				$options[ $option['id'] ] = '1';
			}

		}

	}*/
//	update_option( 'wpedpc_settings', array_merge( $wpedpc_options, $options ) );
	update_option( 'wpedpc_version', WPEDPC_VERSION );

	//wp_clear_scheduled_hook('wpedpc_cron');
	wp_clear_scheduled_hook('wpedpc_cron_callback');
//	wp_schedule_event( 0, 'wpedpcint', 'wpedpc_cron_callback' );
		
	// Add a temporary option to note that WPEDPC pages have been created
//	set_transient( '_wpedpc_installed', $options, 30 );

	// Add the transient to redirect
//	set_transient( '_wpedpc_activation_redirect', true, 30 );
}
register_activation_hook( WPEDPC_PLUGIN_FILE, 'wpedpc_install' );


/**
 * Deactivate
 *
 *  *
 * @since 5.0
 * @return void
 */
function wpedpc_deactivate() {
	wp_clear_scheduled_hook('wpedpc_cron_callback');
	// NO borro opciones ni campañas
}
register_deactivation_hook( WPEDPC_PLUGIN_FILE, 'wpedpc_deactivate' );



/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * wpedpc_after_install hook.
 *
 * @since 1.7
 * @return void
 */

/*function wpedpc_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$wpedpc_options = get_transient( '_wpedpc_installed' );

	// Exit if not in admin or the transient doesn't exist
	if ( false === $wpedpc_options ) {
		return;
	}

	// Delete the transient
	delete_transient( '_wpedpc_installed' );

	do_action( 'wpedpc_after_install', $wpedpc_options );
}
add_action( 'admin_init', 'wpedpc_after_install' );

*/ 

?>