<?php
/**
 * Admin Options Page
 *
 * @package     WPEDPC
 * @subpackage  Admin/Settings 
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Retrieve tools tabs
* @since       1.2.4
* @return      array
*/
function wpedpc_get_settings_tabs() {
	$tabs               = array();
	$tabs['settings']   = __( 'Settings', 'etruel-del-post-copies'  );
	$tabs['licenses']   = __( 'Licenses', 'etruel-del-post-copies'  );
	return apply_filters( 'wpedpc_settings_tabs', $tabs );
}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 5.0
 * @return void
 */
function wpedpc_options_page() {
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], wpedpc_get_settings_tabs() ) ? $_GET['tab'] : 'settings';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( wpedpc_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
				<?php
				do_action( 'wpedpc_settings_tab_' . $active_tab );
				?>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}


/* Get an option from global options
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 5.0
 * @return mixed
 */
function wpedpc_get_option( $key = '', $default = false ) {
	global $wpedpc_options;
	$value = ! empty( $wpedpc_options[ $key ] ) ? $wpedpc_options[ $key ] : $default;
	$value = apply_filters( 'wpedpc_get_option', $value, $key, $default );
	return apply_filters( 'wpedpc_get_option_' . $key, $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an wpedpc setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the wpedpc_options array.
 *
 * @since 5.0
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 */
function wpedpc_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = wpedpc_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'wpedpc_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'wpedpc_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'wpedpc_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $wpedpc_options;
		$wpedpc_options[ $key ] = $value;

	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an wpedpc setting value in both the db and the global variable.
 *
 * @since 5.0
 * @param string $key The Key to delete
 * @return boolean True if updated, false if not.
 */
function wpedpc_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'wpedpc_settings' );

	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'wpedpc_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $wpedpc_options;
		$wpedpc_options = $options;
	}

	return $did_update;
}


/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 5.0
 * @return array WPEDPC settings
 */
function wpedpc_get_settings() {

	$settings = get_option( 'wpedpc_settings' );
	if( empty( $settings ) || false == $settings ) {
		$settings = wpedpc_cleaner_settings(); // if doesn't exist add option
	}
	return apply_filters( 'wpedpc_get_settings', $settings );
}


/**
 * Add all settings sections and fields
 *
 * @since 5.0
 * @return void
*/
function wpedpc_update_settings($settings) {
	global $wpedpc_options;
	$settings = apply_filters('wpedpc_pre_save_settings',$settings);
	$wpedpc_options = $settings;
	
	return update_option( 'wpedpc_settings', $settings );	
}	

/**
 * Clean the options received and just the settings fields
 *
 * @since 5.0
 * @return array Plugin Settings fields
*/
function wpedpc_cleaner_settings( $options = array() ) {
	//All default values
	$settings['excluded_ids'] = (isset($options['excluded_ids']) && !empty($options['excluded_ids']) ) ? $options['excluded_ids'] :  "";
	
	if ( false == get_option( 'wpedpc_settings' ) ) {
		add_option( 'wpedpc_settings', $settings );
	}

	return apply_filters( 'wpedpc_cleaner_settings', $settings );
	
}
add_filter('wpedpc_clean_settings', 'wpedpc_cleaner_settings');


?>