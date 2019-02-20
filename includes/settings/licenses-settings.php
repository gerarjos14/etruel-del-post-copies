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

add_action( 'wpedpc_settings_tab_licenses', 'wpedpc_licenses' );
function wpedpc_licenses(){

	$extensions = array(
		'wpedpc-oldest-posts' => (object) array(
			'url'       => 'http://etruel.com/downloads/wp-edel-oldest-post/',
			'title'     => __( 'WP Delete Oldest Posts', 'etruel-del-post-copies' ),
			'desc'      => __( 'Adds to WP Delete Post Copies plugin a feature to use it as a remover of posts to delete entries by date instead of searching duplicates.', 'etruel-del-post-copies' ),
			'installed' => false,
		)
	);

	if ( class_exists( 'DPCOldestPosts' ) ) {
		$extensions['wpedpc-oldest-posts']->installed = true;
	}
	//echo ('<pre>'.print_r($cfg,1).'</pre>'); 
	?>
	<script type="text/javascript" charset="utf8" >
		jQuery(document).ready(function($) {
			$("#licensestabs").tabs();
		});
	</script>
	<div id="post-body">
	<div id="post-body-content">
	<div class="wrap wpedpc_table_page">
		<div id="licensestabs">
		<ul class="tabNavigation"><h2 id="wpedpc-title" class="nav-tab-wrapper" ><?php _e( 'WP Delete Post Copies Extensions', 'etruel-del-post-copies' ); ?></h2>
			<li><a href="#premium"><?php _e( 'Premium Extensions', 'etruel-del-post-copies' ); ?></a></li>
			<li><a href="#licenses"><?php _e( 'Licenses', 'etruel-del-post-copies' ); ?></a></li>
		</ul>
			<div id="premium">
				<?php
				foreach ( $extensions as $id => $extension ) {
					$utm = '#utm_source=etruel-del-post-copies-config&utm_medium=banner&utm_campaign=extension-page-banners';
					?>
					<div class="extension <?php echo esc_attr( $id ); ?>">
						<a target="_blank" href="<?php echo esc_url( $extension->url . $utm ); ?>">
							<h3><?php echo esc_html( $extension->title ); ?></h3>
						</a>

						<p><?php echo esc_html( $extension->desc ); ?></p>

						<p>
							<?php if ( $extension->installed ) : ?>
								<button class="button-primary installed">Installed</button>
							<?php else : ?>
								<a target="_blank" href="<?php echo esc_url( $extension->url . $utm ); ?>" class="button-primary">
									<?php _e( 'Get this extension', 'etruel-del-post-copies' ); ?>
								</a>
							<?php endif; ?>
						</p>
					</div>
				<?php
				}
				unset( $extensions, $id, $extension, $utm );
				?>
			</div>
			<div id="licenses">
				<?php
				/**
				 * Display license page
				 */
				settings_errors();
				if ( ! has_action( 'wpedpc_licenses_forms' ) ) {
					echo '<div class="msg"><p>', __( 'This is where you would enter the license keys for one of our premium plugins, should you activate one.', 'etruel-del-post-copies' ), '</p></div>';
				}
				else {
					do_action( 'wpedpc_licenses_forms' );
				}
				?>
			</div>
		</div>
	</div>
	</div>
	</div>
<?php 
}
?>