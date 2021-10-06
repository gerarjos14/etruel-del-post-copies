<?php
/**
 * @package WordPress_Plugins
 * @subpackage WP-eDel post copies
 * @a file just to load external extensions
*/
//error_reporting(0);
if(!defined('WP_ADMIN')) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

add_action( 'wpedpc_settings_tab_settings', 'wpedpc_settings' );
function wpedpc_settings(){
	global $wpedpc_options;
	$extensions = wpedpc_extensions()

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
		<h2><?php _e('Global Settings', 'etruel-del-post-copies' ); ?></h2>
		<form method="post" id="edpcsettings" action="">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<?php include('myplugins.php');	?>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php wp_nonce_field('wpedpc-settings'); ?>
							<div id="exluded-post" class="postbox">
								<button type="button" class="handlediv button-link" aria-expanded="true">
									<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
									<span class="toggle-indicator" aria-hidden="true"></span>
								</button>
								<h3 class="hndle ui-sortable-handle"><span class="dashicons dashicons-welcome-write-blog"></span> <span><?php _e('Exclude Posts Settings', 'etruel-del-post-copies'); ?></span></h3>
								<div class="inside">
									<p><b><?php _e('Exclude Posts (types) by ID separated by commas:', 'etruel-del-post-copies' ); ?></b></p>
									<input class="large-text" type="text" value="<?php echo $wpedpc_options['excluded_ids'] ?>" name="excluded_ids">
									<p class="description"><?php _e('If you want some posts/pages never be deleted by any campaign of this plugin, you can type here its IDs, and will be excluded from ALL delete queries.', 'etruel-del-post-copies' ); ?><br>
										<?php _e('To get Post IDs Go to Posts in your WordPress admin, and click the post you need the ID of. Then, if you look in the address bar of your browser, you\'ll see something like this:', 'etruel-del-post-copies' ); ?><br>
										<code><?php echo admin_url('/post.php') ?>?post=<b>1280</b>&action=edit</code> <?php _e('The number, in this case 1280, is the post ID.', 'etruel-del-post-copies' ); ?>
										<?php //echo "<pre>".  print_r($_SERVER,1)."</pre>" ?>
									</p>
								</div>
							</div>

							<div class="clear" /></div>
							<?php do_action('wpedpc_global_settings_form'); ?>
							<div class="clear" /></div>

							<div class="postbox">
								<button type="button" class="handlediv button-link" aria-expanded="true">
									<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
									<span class="toggle-indicator" aria-hidden="true"></span>
								</button>
								<h3 class="hndle ui-sortable-handle"><span class="dashicons dashicons-admin-tools"></span> <span><?php _e('Uninstalling Options', 'etruel-del-post-copies'); ?></span></h3>
								<div class="inside">
									<p><b><?php _e("Uninstalling Plugin Delete Post Copies.", 'etruel-del-post-copies' ); ?></b></p>
									<label><input class="checkbox-input" type="checkbox" value="1" name="wpedpc_uninstall_plugin">
									<?php _e("Delete all options and also delete all campaigns of this plugin.", 'etruel-del-post-copies' ); ?></label>
									<p class="description">
										<?php _e("By checking this option you will delete all data and campaigns of this plugin and deactivate it when save changes.", 'etruel-del-post-copies' ); ?><br>
										<strong><?php _e("CAUTION: ", 'etruel-del-post-copies' ); ?></strong> <?php _e("This action can't be undo.", 'etruel-del-post-copies' ); ?><br>
									</p>
								</div>
							</div>

							<div class="clear" /></div>

							<input type="hidden" name="wpedpc_action" value="save_settings" />
							<input type="hidden" name="do" value="WPdpc_setup" />
							<input id="submit" type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'etruel-del-post-copies' ); ?>" /> 
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
}



add_action( 'wpedpc_save_settings', 'wpedpc_settings_save' );
function wpedpc_settings_save() {
	if(check_admin_referer('wpedpc-settings')==false) {
		wp_die( __('Try again', 'etruel-del-post-copies' ) );
	}
	if ( 'POST' === $_SERVER[ 'REQUEST_METHOD' ] ) {
//		if ( get_magic_quotes_gpc() ) {
//			$_POST = array_map( 'stripslashes_deep', $_POST );
//		}
		//delete all Options and campaigns and redirect to plugins page to deactivate
		if( isset($_POST['wpedpc_uninstall_plugin']) && ($_POST['wpedpc_uninstall_plugin']) ) {
			//deactivate_plugins( plugin_basename( WPEDPC_PLUGIN_FILE ) );
			add_action('admin_notices', 'wpedpc_deactivating_notice'); 
			//wp_redirect( admin_url( 'plugins.php#wp-delete-post-copies') );
			return;
		}
			
//		$errlev = error_reporting();
//		error_reporting(E_ALL & ~E_NOTICE);  // desactivo los notice que aparecen con los _POST

		$cfg = apply_filters('wpedpc_clean_settings',$_POST);
		
		if( wpedpc_update_settings($cfg) ) {
			wpedpc_add_admin_notice(array('text' => __('Settings saved.', 'etruel-del-post-copies' ), 'below-h2'=>false ));
		}
		
//		error_reporting($errlev);

	}
}

	

//Admin header notify
function wpedpc_deactivating_notice() {
	
	//Delete all plugin campaigns
	$args = array(
		'post_type'   => 'wpedpcampaign',
		'post_status' => get_post_stati(),
		'numberposts'   => -1,
	);
	$campaigns = get_posts($args);
	$ccount= 0;
	$statuserr=0;
	foreach ($campaigns as $campaign) {
		$postid		= $campaign->ID;
		if ($postid<>''){
			$custom_field_keys = get_post_custom_keys($postid);
			foreach ( $custom_field_keys as $key => $value ) {
				delete_post_meta($postid, $key, '');
			}
			$error = wp_delete_post($postid, true);
			if (!$error) {  
				$statuserr++;
			}else {  
				$ccount++;
			}
		}
	}
	delete_option( 'wpedpc_settings' );
	$mess = sprintf(__('All Settings and %s campaigns were deleted and the plugin was deactivated.', 'etruel-del-post-copies' ),$ccount);
	$mess .= '<br />';
	if( $statuserr>0 ) {
		$mess = sprintf(__('There was %s errors when the campaigns were being deleted.', 'etruel-del-post-copies' ),$statuserr );
		$mess .= '<br />';
	}
	$mess .= __('Now you can uninstall WP Delete Post Copies from plugins Page.', 'etruel-del-post-copies' );
	$mess .= '<br />';
	$mess .= '<a href="'.admin_url( 'plugins.php#wp-delete-post-copies').'">'.__('Go To Plugins Page to uninstall now.', 'etruel-del-post-copies' ).'</a>';
	$class = "notice"; // notice-success"; //	$class = "notice notice-error";
	$class .= " is-dismissible"; //$class .= "";
	$class .= " below-h2"; //$class .= "";

	$wpedpc_message = '<div id="message" class="'.$class.'"><p>'.$mess.'</p></div>';
	
	
	deactivate_plugins( plugin_basename( WPEDPC_PLUGIN_FILE ) );
	
	echo $wpedpc_message;
	
	exit;
}
?>