<?php
/**
 * @package WordPress_Plugins
 * @subpackage WP-eDel post copies
 * @a file just to load external extensions
 */ 
//error_reporting(0);
if (!defined('WP_ADMIN')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

function wpedpc_extensions() {
	$extensions = array(
		'etruel-delete-post-copies-pro' => (object) array(
			'url' => 'https://etruel.com/downloads/etruel-del-post-copies-pro/',
			'buynowURI' => 'https://etruel.com/checkout?edd_action=add_to_cart&download_id=34&edd_options[price_id]=2',
			'title' => 'WP Delete Post Copies PROs',
			'banner' => WPEDPC_PLUGIN_URL .'includes/images/Delete-Older-Post-500x250.jpg',
			'desc' => __('Add-On to enabled WP-Delete Post Copies plugin to delete posts by dates instead of duplicates. As prior certain date or prior to certains months ago.', 'etruel-del-post-copies'),

			'installed' => false,
		)
	);

	if (class_exists('DPCOldestPosts')) {
		$extensions['etruel-delete-post-copies-pro']->installed = true;
	}
	return apply_filters('wpedpc_extensions', $extensions);
}


add_action('wpedpc_settings_tab_licenses', 'wpedpc_licenses');

function wpedpc_licenses() {
	$extensions = wpedpc_extensions()
	//echo ('<pre>'.print_r($cfg,1).'</pre>'); 
	?>
	<script type="text/javascript" charset="utf8" >
		jQuery(document).ready(function ($) {
			$("#licensestabs").tabs();
		});
	</script>
	<style>
		#licensestabs.ui-tabs{
			padding: 10px 0 0;
			font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav{
			border-bottom: 0;
			padding: 0;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav li{
			background-color: initial;
			border: 0;
			font-weight: 600;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav li{
			margin-right: 0;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav li:not(:last-child):after{
			content: '|';
			margin: 0 5px;
			font-size: 17px;
			font-weight: 600;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav .ui-state-active{
			background-color: initial;
			border: 0;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav li a{
			color: #2271b1;
			text-decoration: underline;
			padding: 0;
			font-size: 17px;
			font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
		}
		#licensestabs.ui-tabs .tabNavigation.ui-tabs-nav .ui-state-active a{
			color: red;
		}
		#licensestabs.ui-tabs .ui-tabs-panel{
			padding: 2em 0;
		}
		.extension p{
			margin-top: 0;
			margin-bottom: 15px;
		}
		.extension p:last-child{
			margin-bottom: 0;
		}
		.extension-title {
			font-size: 17px;
			margin-bottom: 10px;
		}
		.extension-title a{
			color: #1d2327;
			text-decoration: none;
		}
		.extension-message{
			background: #fff;
			border: 1px solid #dba617;
			border-left-width: 4px;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
			margin: 5px 0 2px;
			padding: 1px 12px;
		}
		.extension-message p{
			margin: .5em 0;
			padding: 2px;
		}
	</style>
	<div id="post-body">
		<div id="post-body-content">
			<div class="metabox-holder">
				<div class="wrap wpedpc_table_page">
					<h2 id="wpedpc-title"><?php _e('WP Delete Post Copies Extensions', 'etruel-del-post-copies'); ?></h2>
					<div id="licensestabs">
						<div id="premium">
							<?php
							foreach ($extensions as $id => $extension) {
								$utm = '#utm_source=etruel-del-post-copies-config&utm_medium=banner&utm_campaign=extension-page-banners';
								?>
								<div class="postbox" style="width:33%;max-width:500px;">
									<img loading="lazy" class="aligncenter" style="width: 100%;" src="<?php echo $extension->banner; ?>" alt="Banner Delete Post Copies PRO">
									<div class="inside">
										<div class="extension <?php echo esc_attr($id); ?>">
											<h4 class="extension-title"><a target="_blank" href="<?php echo esc_url($extension->url . $utm); ?>">
													<?php echo esc_html($extension->title); ?>
												</a></h4>

											<p><?php echo esc_html($extension->desc); ?></p>

											<p>
												<?php if ($extension->installed) : ?>
													<button class="button">Installed</button>
												<?php else : ?>
													<a target="_blank" href="<?php echo esc_url($extension->url . $utm); ?>" class="button button-secondary">
														<?php _e('See More', 'etruel-del-post-copies'); ?>
													</a>
													<a target="_blank" href="<?php echo esc_url($extension->buynowURI . $utm); ?>" class="button button-primary">
														<?php _e('Get this extension', 'etruel-del-post-copies'); ?>
													</a>
												<?php endif; ?>
											</p>
										</div>
									</div>
								</div>
								<?php if ($extension->installed) : ?>
									<div id="">
										<?php
										/**
										 * Display license page
										 */
										settings_errors();
										if (!has_action('wpedpc_licenses_forms')) {
											echo '<div class="msg extension-message"><p>', esc_html__('This is where you would enter the license keys for one of our premium plugins, should you activate one.', 'etruel-del-post-copies'), '</p></div>';
										} else {
											do_action('wpedpc_licenses_forms');
										}
										?>
									</div>
									<?php endif; ?>
								<?php
							}
							unset($extensions, $id, $extension, $utm);
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>