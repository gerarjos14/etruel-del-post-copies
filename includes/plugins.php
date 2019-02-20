<?php
/**
 * Admin Plugins
 *
 * @package     WP Delete Post Copies
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2016, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugins row action links
 *
 * @since 5.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function wpedpc_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'edit.php?post_type=wpedpcampaign&page=edpc_options' ) . '">' . esc_html__( 'General Settings', 'etruel-del-post-copies' ) . '</a>';
	if ( $file == 'etruel-del-post-copies/edel-post-copies.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'wpedpc_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @since 5.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function wpedpc_plugin_row_meta( $input, $file ) {
	if ( $file != 'etruel-del-post-copies/edel-post-copies.php' )
		return $input;

	$wpedpc_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'wpedpc-row',
			'utm_campaign' => 'admin',
		), 'http://etruel.com' )
	);

	$links = array(
		'<a title="'. __( 'View more plugins by etruel', 'etruel-del-post-copies' ) .'" href="' . $wpedpc_link . '">' . esc_html__( 'etruel\'s Store', 'etruel-del-post-copies' ) . '</a>'
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'wpedpc_plugin_row_meta', 10, 2 );
