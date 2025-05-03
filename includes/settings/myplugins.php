			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button> 
					<h3 class="hndle ui-sortable-handle"> <span><?php _e('About', 'etruel-del-post-copies'); ?></span></h3>
					<div class="inside">
						<p><strong><?php echo '► WP Delete Post Copies ' . WPEDPC_VERSION . ' ◄'; ?></strong></p>
						<p><?php _e('Thanks for test, use and enjoy this plugin.', 'etruel-del-post-copies'); ?></p>
						<p><?php _e('If you like it, I really appreciate a donation.', 'etruel-del-post-copies'); ?></p>
						<p>
							<input type="button" class="button-primary" name="donate" value="<?php _e('Click here to make a Donation', 'etruel-del-post-copies'); ?>" onclick="javascript:window.open('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VTNR4BH8XPDR6');return false;"/>
						</p>
						<p><?php /* _e('Help', 'etruel-del-post-copies'  ); ?> */ ?>
						</p>
						<p><?php _e('Or you can', 'etruel-del-post-copies'); ?>  <a href="https://wordpress.org/support/view/plugin-reviews/etruel-del-post-copies?filter=5&amp;rate=5#postform" target="_blank"><?php _e('rate it'); ?></a> <?php _e('on'); ?> <a href="https://wordpress.org/support/view/plugin-reviews/etruel-del-post-copies?filter=5&amp;rate=5#postform" target="_blank">WordPress.org</a></p>
						<p></p>
					</div>
				</div>
				<div class="postbox">
					<button type="button" class="handlediv button-link" aria-expanded="true">
						<span class="screen-reader-text"><?php _e('Click to toggle'); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h3 class="hndle ui-sortable-handle"> <span><?php _e('Other extensions and plugins', 'etruel-del-post-copies'); ?></span></h3>
					<?php
					foreach ($extensions as $id => $extension) {
						$utm = '#utm_source=etruel-del-post-copies-config&utm_medium=banner&utm_campaign=sidebar-banners';
						?>
						<div class="inside">
							<img loading="lazy" class="aligncenter" style="width: 100%;" src="<?php echo $extension->banner; ?>" alt="Banner Delete Post Copies PRO">
							<div class="inside">
								<div class="extension <?php echo esc_attr($id); ?>">
									<h4 class="extension-title"><a target="_blank" href="<?php echo esc_url($extension->url . $utm); ?>">
											<?php echo esc_html($extension->title); ?>
										</a></h4>

									<p><?php echo esc_html($extension->desc); ?></br>
									<?php _e('And/Or you can establish a period with a cron job to continuously deleting old posts and just remains that period on database.');	?>
									</p>

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

							<div class="wpeplugname" id="wpebanover"><a href="http://wordpress.org/plugins/wpecounter/" target="_Blank" class="wpelinks">WPeCounter</a>
								<div id="wpecounterdesc" class="">Visits Post(types) counter. Shown in a sortable column the number of visits on lists of posts, pages, etc. Very lightweight.</div></div>
							<p></p>
							<div class="wpeplugname" id="WPeMatico"><a href="http://wordpress.org/plugins/wpematico/" target="_Blank" class="wpelinks">WPeMatico</a>
								<div id="WPeMaticodesc" class="">WPeMatico is autoblogging in the blink of an eye! On complete autopilot WPeMatico gets new contents regularly for your site! </div></div>
							<p></p>
						</div>
						<?php
					} //endforeach
					?>
				</div>
			</div>
