<?php
/**
 * @package WordPress_Plugins
 * @subpackage WP-eDel post copies
 * @file Admin settings: sanitized and escaped version
 */

// Exit if not in WP Admin context
if ( ! defined( 'WP_ADMIN' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Hook to render settings tab
 */
add_action( 'wpedpc_settings_tab_settings', 'wpedpc_settings' );
function wpedpc_settings() {
	global $wpedpc_options;

	// Load saved options (fallback to empty array)
	$wpedpc_options = get_option( 'wpedpc_settings', array() );

	$extensions = function_exists( 'wpedpc_extensions' ) ? wpedpc_extensions() : array();
	?>
	<style>
		.postbox .handlediv{
			float: right;
			text-align: center;
		}
		.postbox .hndle {
		  border-bottom: 1px solid #ccd0d4;
		}
		#poststuff .stuffbox > h3, #poststuff h2, #poststuff h3.hndle {
			font-size: 14px;
			padding: 8px 12px;
			margin: 0;
			line-height: 1.4;
		}
		@media (max-width: 850px){
			#wpbody-content #post-body.columns-2 #postbox-container-1 {
		    margin-right: 0;
		    width: 100%;
			}
			#poststuff #post-body.columns-2 #side-sortables {
				min-height: 0;
				width: auto;
			}
		}
	</style>

	<div class="metabox-holder">
		<div class="wrap">
			<h2><?php _e( 'Global Settings', 'etruel-del-post-copies' ); ?></h2>

			<!-- Form: method POST, action empty (same page). -->
			<form method="post" id="edpcsettings" action="">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<?php
							// Keep your external "myplugins.php" include if present
							if ( file_exists( __DIR__ . '/myplugins.php' ) ) {
								include __DIR__ . '/myplugins.php';
							}
							?>
						</div>

						<div id="postbox-container-2" class="postbox-container">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
								<?php wp_nonce_field( 'wpedpc-settings' ); ?>

								<div id="exluded-post" class="postbox">
									<button type="button" class="handlediv button-link" aria-expanded="true">
										<span class="screen-reader-text"><?php _e( 'Click to toggle' ); ?></span>
										<span class="toggle-indicator" aria-hidden="true"></span>
									</button>

									<h3 class="hndle ui-sortable-handle">
										<span class="dashicons dashicons-welcome-write-blog"></span>
										<span><?php _e( 'Exclude Posts Settings', 'etruel-del-post-copies' ); ?></span>
									</h3>

									<div class="inside">
										<p><b><?php _e( 'Exclude Posts (types) by ID separated by commas:', 'etruel-del-post-copies' ); ?></b></p>

										<!-- Escaped value to prevent execution on output -->
										<input class="large-text" type="text"
										       value="<?php echo esc_attr( $wpedpc_options['excluded_ids'] ?? '' ); ?>"
										       name="excluded_ids">

										<p class="description"><?php _e( 'If you want some posts/pages never be deleted by any campaign of this plugin, you can type here its IDs, and will be excluded from ALL delete queries.', 'etruel-del-post-copies' ); ?><br>
											<?php _e( 'To get Post IDs Go to Posts in your WordPress admin, and click the post you need the ID of. Then, if you look in the address bar of your browser, you\'ll see something like this:', 'etruel-del-post-copies' ); ?><br>
											<code><?php echo esc_html( admin_url( '/post.php' ) ); ?>?post=<b>1280</b>&action=edit</code> <?php _e( 'The number, in this case 1280, is the post ID.', 'etruel-del-post-copies' ); ?>
										</p>
									</div>
								</div>

								<div class="clear"></div>

								<?php do_action( 'wpedpc_global_settings_form' ); ?>

								<div class="clear"></div>

								<div class="postbox">
									<button type="button" class="handlediv button-link" aria-expanded="true">
										<span class="screen-reader-text"><?php _e( 'Click to toggle' ); ?></span>
										<span class="toggle-indicator" aria-hidden="true"></span>
									</button>

									<h3 class="hndle ui-sortable-handle">
										<span class="dashicons dashicons-admin-tools"></span>
										<span><?php _e( 'Uninstalling Options', 'etruel-del-post-copies' ); ?></span>
									</h3>

									<div class="inside">
										<p><b><?php _e( "Uninstalling Plugin Delete Post Copies.", 'etruel-del-post-copies' ); ?></b></p>
										<label>
											<input class="checkbox-input" type="checkbox" value="1" name="wpedpc_uninstall_plugin">
											<?php _e( "Delete all options and also delete all campaigns of this plugin.", 'etruel-del-post-copies' ); ?>
										</label>
										<p class="description">
											<?php _e( "By checking this option you will delete all data and campaigns of this plugin and deactivate it when save changes.", 'etruel-del-post-copies' ); ?><br>
											<strong><?php _e( "CAUTION: ", 'etruel-del-post-copies' ); ?></strong> <?php _e( "This action can't be undo.", 'etruel-del-post-copies' ); ?><br>
										</p>
									</div>
								</div>

								<div class="clear"></div>

								<input type="hidden" name="wpedpc_action" value="save_settings" />
								<input type="hidden" name="do" value="WPdpc_setup" />
								<input id="submit" type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', 'etruel-del-post-copies' ); ?>" />
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>
	</div>
	<?php
}

/**
 * Hook to save settings (existing hook in your plugin)
 * Ensure capability + nonce and proper sanitization
 */
add_action( 'wpedpc_save_settings', 'wpedpc_settings_save' );
function wpedpc_settings_save() {
	// Capability check
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to perform this action.', 'etruel-del-post-copies' ) );
	}

	// Nonce check
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'wpedpc-settings' ) ) {
		wp_die( __( 'Try again', 'etruel-del-post-copies' ) );
	}

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

		// Handle uninstall option (same behavior you had)
		if ( isset( $_POST['wpedpc_uninstall_plugin'] ) && $_POST['wpedpc_uninstall_plugin'] ) {
			add_action( 'admin_notices', 'wpedpc_deactivating_notice' );
			return;
		}

		// Allow other filters to process/clean the settings. We added a filter below to sanitize excluded_ids.
		$cfg = apply_filters( 'wpedpc_clean_settings', wp_unslash( $_POST ) );

		// Update settings through your update function (preserve your function)
		if ( function_exists( 'wpedpc_update_settings' ) ) {
			if ( wpedpc_update_settings( $cfg ) ) {
				wpedpc_add_admin_notice( array( 'text' => __( 'Settings saved.', 'etruel-del-post-copies' ), 'below-h2' => false ) );
			}
		} else {
			// Fallback: directly update option (sanitized by filter)
			update_option( 'wpedpc_settings', $cfg );
			wpedpc_add_admin_notice( array( 'text' => __( 'Settings saved (fallback).', 'etruel-del-post-copies' ), 'below-h2' => false ) );
		}
	}
}

/**
 * Sanitize settings via filter - specifically sanitize excluded_ids field.
 * This is hooked into 'wpedpc_clean_settings' so your existing save flow can use it.
 */
add_filter( 'wpedpc_clean_settings', 'wpedpc_sanitize_excluded_ids_field' );
function wpedpc_sanitize_excluded_ids_field( $settings ) {
	// Only sanitize the expected field, leave other fields intact (or sanitize them here)
	if ( isset( $settings['excluded_ids'] ) ) {
		$settings['excluded_ids'] = wpedpc_sanitize_excluded_ids( $settings['excluded_ids'] );
	}
	return $settings;
}

/**
 * Convert a raw string into a canonical comma-separated list of positive integers.
 *
 * @param string|array $raw Raw input (string or array). Strings like "<script>..." will be removed.
 * @return string Comma-separated integers or empty string.
 */
function wpedpc_sanitize_excluded_ids( $raw ) {
	// If it's an array (unlikely for this field), join with commas first
	if ( is_array( $raw ) ) {
		$raw = implode( ',', $raw );
	}

	$raw = trim( (string) $raw );
	if ( $raw === '' ) {
		return '';
	}

	// Split by commas and whitespace
	$parts = preg_split( '/[\s,]+/', $raw );

	$ids = array();
	foreach ( $parts as $p ) {
		$id = absint( trim( $p ) );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	// Unique and sorted
	$ids = array_values( array_unique( $ids ) );
	sort( $ids, SORT_NUMERIC );

	return empty( $ids ) ? '' : implode( ',', $ids );
}

/**
 * Optional admin-time cleanup: sanitize already-saved option values (fix stored payloads)
 * This will run on admin_init; it is safe and idempotent.
 */
add_action( 'admin_init', 'wpedpc_clean_existing_options_on_admin_init' );
function wpedpc_clean_existing_options_on_admin_init() {
	// Only run for users who can manage options (avoid unnecessary DB writes for other users)
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts = get_option( 'wpedpc_settings' );

	// Only continue if option array exists and has excluded_ids
	if ( is_array( $opts ) && isset( $opts['excluded_ids'] ) ) {
		$clean = wpedpc_sanitize_excluded_ids( $opts['excluded_ids'] );
		if ( $clean !== $opts['excluded_ids'] ) {
			$opts['excluded_ids'] = $clean;
			update_option( 'wpedpc_settings', $opts );
		}
	}
}

/**
 * Admin header notify + uninstall behavior (kept from your original code, slightly cleaned)
 */
function wpedpc_deactivating_notice() {

	// Delete all plugin campaigns
	$args = array(
		'post_type'   => 'wpedpcampaign',
		'post_status' => get_post_stati(),
		'numberposts' => -1,
	);
	$campaigns  = get_posts( $args );
	$ccount     = 0;
	$statuserr  = 0;

	foreach ( $campaigns as $campaign ) {
		$postid = $campaign->ID;
		if ( $postid != '' ) {
			$custom_field_keys = get_post_custom_keys( $postid );
			if ( is_array( $custom_field_keys ) ) {
				foreach ( $custom_field_keys as $key => $value ) {
					delete_post_meta( $postid, $key, '' );
				}
			}
			$error = wp_delete_post( $postid, true );
			if ( ! $error ) {
				$statuserr++;
			} else {
				$ccount++;
			}
		}
	}

	delete_option( 'wpedpc_settings' );
	$mess = sprintf( __( 'All Settings and %s campaigns were deleted and the plugin was deactivated.', 'etruel-del-post-copies' ), $ccount );
	$mess .= '<br />';
	if ( $statuserr > 0 ) {
		$mess = sprintf( __( 'There was %s errors when the campaigns were being deleted.', 'etruel-del-post-copies' ), $statuserr );
		$mess .= '<br />';
	}
	$mess .= __( 'Now you can uninstall WP Delete Post Copies from plugins Page.', 'etruel-del-post-copies' );
	$mess .= '<br />';
	$mess .= '<a href="' . esc_url( admin_url( 'plugins.php#wp-delete-post-copies' ) ) . '">' . __( 'Go To Plugins Page to uninstall now.', 'etruel-del-post-copies' ) . '</a>';
	$class  = "notice";
	$class .= " is-dismissible";
	$class .= " below-h2";

	$wpedpc_message = '<div id="message" class="' . esc_attr( $class ) . '"><p>' . $mess . '</p></div>';

	// Deactivate plugin
	if ( defined( 'WPEDPC_PLUGIN_FILE' ) ) {
		deactivate_plugins( plugin_basename( WPEDPC_PLUGIN_FILE ) );
	}

	echo $wpedpc_message;

	// Exit to prevent further rendering (matches original behavior)
	exit;
}
