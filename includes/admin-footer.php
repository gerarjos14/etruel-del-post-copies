<?php
/**
 * Admin Footer
 *
 * @package     WPEDPC
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add rating links to the admin dashboard
 *
 * @since	    5.0
 * @global		string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function wpedpc_admin_rate_us( $footer_text ) {
	global $typenow;

	if ( $typenow == 'wpedpcampaign' ) {
		$rate_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">WP Delete Post Copies</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'etruel-del-post-copies' ),
			'http://etruel.com',
			'https://wordpress.org/support/view/plugin-reviews/etruel-del-post-copies?filter=5&rate=5#postform'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $rate_text . '</span>';
	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'wpedpc_admin_rate_us' );
