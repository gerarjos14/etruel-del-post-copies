<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

if (!class_exists('wpedpc_run_campaign')) :

	class wpedpc_run_campaign {

		function __construct() {

			add_filter('wpedpc_run_campaign', array(&$this, 'options_quickdo'), 10, 3);
			add_filter('wpedpc_parseImages', array(&$this, 'wpedpc_parseImages'), 10, 2);
			add_filter('wpedpc_getRelativeUrl', array(&$this, 'wpedpc_getRelativeUrl'), 10, 2);
			add_filter('wpedpc_getReadUrl', array(&$this, 'wpedpc_getReadUrl'), 10, 1);
			add_filter('wpedpc_get_domain', array(&$this, 'wpedpc_get_domain'), 10, 1);
		}

		function options_quickdo($post_id, $quickdo = 'WPdpc_auto', $response = array()) {
			if (!is_array($response)) {
				$response = array();
			}
			$error = false;
			if ($quickdo == 'WPdpc_logerase') {
				if (delete_post_meta($post_id, 'logs')) {
					$response['message'] = __('Logs of campaign deleted', 'etruel-del-post-copies');
				} else {
					$response['message'] = __('Something goes wrong.  The log was not deleted.', 'etruel-del-post-copies');
					$error = true;
				}
			} elseif ($quickdo == 'WPdpc_now') {
				$response = $this->run('now', $post_id, $response);
			} elseif ($quickdo == 'WPdpc_show') {
				$response = $this->run('show', $post_id, $response);
			} elseif ($quickdo == 'WPdpc_counter') {
				$response = $this->run('counter', $post_id, $response);
			} elseif ($quickdo == 'WPdpc_auto') {
				$response = $this->run('auto', $post_id, $response);
			}
			if (!$error) {
				$response['success'] = true;
			}
			return $response;
		}

		function run($mode = 'auto', $post_id, $response = array()) {
			global $wpdb, $wp_locale, $current_blog, $wpedpc_options;

			$wpedpc_campaign = new WPEDPC_Campaign($post_id);
			if (!$wpedpc_campaign->active AND $mode == 'auto') {
				return false;
			}
			if ($wpedpc_campaign->doingcron) {
				return false;
			}
			if (!$wpedpc_options) {
				$wpedpc_options = wpedpc_get_settings();
			}

			if ($mode == 'auto') {
				update_post_meta($wpedpc_campaign->ID, 'doingcron', true);
				$wpedpc_campaign->doingcron = true;
			}

			$limite = (intval($wpedpc_campaign->wpedpc_limit) > 0 && $mode <> 'counter') ? " LIMIT 0, " . strval(intval($wpedpc_campaign->wpedpc_limit)) : "";

			$cpostypes = $wpedpc_campaign->cpostypes;
			$aposttypes = array();
			foreach ($cpostypes as $postype => $value) {
				$aposttypes[] = "'" . $postype . "'";
			}
			unset($cpostypes);
			$cpostypes = implode(',', $aposttypes);

			$cposstatuses = $wpedpc_campaign->cposstatuses;
			$apoststatuses = array();
			foreach ($cposstatuses as $postat => $value) {
				$apoststatuses[] = "'" . $postat . "'";
			}
			$cposstatuses = implode(',', $apoststatuses);

			$deletemedia = $wpedpc_campaign->deletemedia;
			$delimgcontent = $wpedpc_campaign->delimgcontent;
			$movetotrash = $wpedpc_campaign->movetotrash;
			$force_delete = !$movetotrash;

			$MINMAX = $wpedpc_campaign->minmax;
			if (is_null($MINMAX)) {
				$MINMAX = "MIN";
			}
			if (is_array($wpedpc_campaign->categories)) {
				$categories = implode(",", $wpedpc_campaign->categories);
			} else {
				$categories = '-1';
			}
			if (count($wpedpc_campaign->categories) == 0) {
				$categories = '-1';
			}


			$excluded_ids = $wpedpc_campaign->excluded_ids . ',' . $wpedpc_options['excluded_ids'];
			if (empty($wpedpc_campaign->excluded_ids)) {
				$excluded_ids = '-1';
			} else {
				$arrayExcludeIds = explode(',', $excluded_ids);
				$arrayExcludeIds = array_filter($arrayExcludeIds);
				$excluded_ids = implode(',', $arrayExcludeIds);
			}



			$timenow = time();
			$mtime = explode(' ', microtime());
			$time_start = $mtime[1] + $mtime[0];
			$date = date('m.d.y-H.i.s', $timenow);
			$wp_posts = $wpdb->prefix . "posts";
			$wp_terms = $wpdb->prefix . "terms";
			$wp_term_taxonomy = $wpdb->prefix . "term_taxonomy";
			$wp_term_relationships = $wpdb->prefix . "term_relationships";

			$results = $message = '';

			if ($mode == 'now') {
				$message .= 'Deleting: ';
			}

			$fields2compare = " ";
			if ($wpedpc_campaign->allcat) {

				if ($wpedpc_campaign->titledel && $wpedpc_campaign->contentdel) {
					$fields2compare = "AND (good_rows.post_title = bad_rows.post_title OR good_rows.post_content = bad_rows.post_content) ";
				} elseif ($wpedpc_campaign->contentdel) {
					$fields2compare = "AND good_rows.post_content = bad_rows.post_content ";
				} else {
					$fields2compare = "AND good_rows.post_title = bad_rows.post_title ";
				}
				$query = "SELECT bad_rows.*, ok_id, post_date, ok_date
				FROM {$wp_posts} AS bad_rows
				INNER JOIN (
					SELECT $wp_posts.post_title,$wp_posts.post_content, $wp_posts.id, $wp_posts.post_date AS ok_date, $MINMAX( $wp_posts.id ) AS ok_id
					FROM $wp_posts
					WHERE (
						$wp_posts.post_status IN ($cposstatuses) 
						AND $wp_posts.post_type IN (" . $cpostypes . ") 
					)
					GROUP BY post_title
					having count(*) > 1
					) AS good_rows ON good_rows.ok_id <> bad_rows.id $fields2compare
				WHERE (
					bad_rows.post_status IN (" . $cposstatuses . ") 
					AND bad_rows.id NOT IN (" . $excluded_ids . ")
					AND bad_rows.post_type IN (" . $cpostypes . ") 
				)
				ORDER BY post_title " . $limite;
			} else {  // with selected categories
				if ($wpedpc_campaign->titledel && $wpedpc_campaign->contentdel) {
					$fields2compare = "(good_rows.post_title = bad_rows.post_title OR good_rows.post_content = bad_rows.post_content) ";
				} elseif ($wpedpc_campaign->contentdel) {  //only content
					$fields2compare = "good_rows.post_content = bad_rows.post_content ";
				} else { //only title
					$fields2compare = "good_rows.post_title = bad_rows.post_title ";
				}
				$query = "SELECT bad_rows.post_title, bad_rows.post_content, bad_rows.id as ID, bad_rows.post_date, $wp_terms.term_id, ok_id, ok_date, okcateg_id
				FROM $wp_terms 
				INNER JOIN $wp_term_taxonomy ON $wp_terms.term_id = $wp_term_taxonomy.term_id
				INNER JOIN $wp_term_relationships ON $wp_term_relationships.term_taxonomy_id = $wp_term_taxonomy.term_taxonomy_id
				INNER JOIN $wp_posts AS bad_rows ON bad_rows.ID = $wp_term_relationships.object_id
				INNER JOIN (
					SELECT $wp_posts.post_title,$wp_posts.post_content, $wp_posts.id, $wp_posts.post_date AS ok_date, $MINMAX( $wp_posts.id ) AS ok_id, $wp_terms.term_id AS okcateg_id
					FROM $wp_terms
						INNER JOIN $wp_term_taxonomy ON $wp_terms.term_id = $wp_term_taxonomy.term_id
						INNER JOIN $wp_term_relationships ON $wp_term_relationships.term_taxonomy_id = $wp_term_taxonomy.term_taxonomy_id
						INNER JOIN $wp_posts ON $wp_posts.ID = $wp_term_relationships.object_id
					WHERE taxonomy =  'category'
						AND $wp_posts.post_type IN ( $cpostypes ) 
						AND post_status IN ($cposstatuses) 
						 AND ($wp_terms.term_id IN ( $categories ))
					GROUP BY post_title, $wp_terms.term_id 
					HAVING COUNT(*) > 1
				) AS good_rows ON $fields2compare AND good_rows.ok_id <> bad_rows.id AND good_rows.okcateg_id = $wp_terms.term_id
				WHERE taxonomy =  'category'
					AND bad_rows.post_type IN ( $cpostypes ) 
					AND bad_rows.post_status IN ($cposstatuses) 
					AND bad_rows.id NOT IN ($excluded_ids) 
					AND ($wp_terms.term_id IN ( $categories ))
				ORDER BY post_title ASC " . $limite;
			}
			$query = apply_filters('wpedpc_after_query', $query, $wpedpc_campaign);

			if ($mode == 'show') {
				$dupes = $wpdb->get_results($query);
				$dispcount = 0;
				$results .= '<div class="wrap">
			<h2>' . __('Showing posts to delete', 'etruel-del-post-copies') . '</h2>';
				$results .= '<table class="widefat"><thead>
			  <tr>
				<th></th>
				<th scope="col">' . __('Post ID', 'etruel-del-post-copies') . '</th>
				<th scope="col">' . __('Title', 'etruel-del-post-copies') . '</th>
				<th scope="col">' . __('Category', 'etruel-del-post-copies') . '</th>
				<th scope="col">' . __('Post Date', 'etruel-del-post-copies') . '</th>
				<th></th>
				<th scope="col">' . __('Correct Post ID', 'etruel-del-post-copies') . '</th>
				<th scope="col">' . __('Correct Post Date', 'etruel-del-post-copies') . '</th>
			  </tr>
			</thead>
			<tbody id="delbox">';

				if (!empty($dupes))
					foreach ($dupes as $dupe) {
						$postid = $dupe->ID;
						$title = $dupe->post_title;
						$wpcontent = $dupe->post_content;

						$cat_id = ($wpedpc_campaign->allcat) ? '' : $dupe->term_id;
						if ($cat_id != '') {
							$cat_name = get_cat_name($cat_id);
						} else {
							$cat_name = get_the_category_list(', ', '', $postid);
						}

						$postdate = date(get_option('date_format') . ' H:i:s', strtotime($dupe->post_date));
						$perma = get_permalink($postid);
						$okpostid = $dupe->ok_id;
						$okpostdate = date(get_option('date_format') . ' H:i:s', strtotime($dupe->ok_date));
						$okperma = get_permalink($okpostid);
						$mensaje = "";
						if ($postid <> '') {  // Muestro una linea con el mensaje
							$cmedias = "";
							if ($deletemedia) {  //images attached
								$media = get_children(array('post_parent' => $postid, 'post_type' => 'attachment'));
								if (!empty($media)) {
									foreach ($media as $file) {
										$cmedias .= "<b>Attached Media</b> ID: '{$file->ID}', title: '{$file->guid}'<br>";
									}
								}
							}
							if ($delimgcontent) {  //images in content					
								$images = apply_filters('wpedpc_parseImages', array(), $wpcontent);
								$itemUrl = $perma;  //self::getReadUrl($perma);
								$images = array_values(array_unique($images));
								if (sizeof($images)) { // Si hay alguna imagen en el contenido
									$img_new_url = array();
									foreach ($images as $imagen_src) {
										$imagen_src_real = apply_filters('wpedpc_getRelativeUrl', $itemUrl, $imagen_src);
										$cmedias .= "<b>Img in content</b>: $imagen_src_real";
										if ($this->wpedpc_get_domain($imagen_src) == $this->wpedpc_get_domain(home_url())) {
											$file = $_SERVER['DOCUMENT_ROOT'] . str_replace(home_url(), "", $imagen_src_real);
											//$cmedias .= "<br>".$_SERVER['DOCUMENT_ROOT'] .$file ."<br>";
											if (file_exists($file))
												$cmedias .= "<br>" . __("Exist: ", "etruel-del-post-copies");
											else
												$cmedias .= __("Don't Exist: ", "etruel-del-post-copies");
											$cmedias .= "Img in folder: " . $file . "<br>";
										} else {
											$cmedias .= "<b> External. Different domain.</b><br>";
										}
									}
								}
							}

							$custom_field_keys = get_post_custom_keys($postid);
							$claves = "";
							if (isset($custom_field_keys)) { // (is_array($custom_field_keys))
								foreach ($custom_field_keys as $key => $value) {
									$valuet = trim($value);
									if ('_' == $valuet{0})
										continue;
									$claves .= "<b>Meta key</b>: '$key', value: '$value'<br>";
								}
							}
							$mensaje = '<tr class="' . $postid . ' ' . $okpostid . '">
						<td class="' . $postid . '_loading_td">' . $this->wpedpc_get_delete_post_link('X', '<div class="postdel" rel="' . $postid . '">', '</div>', $postid, true) . '</td>
						<td><a href="' . $perma . '" target="_Blank" title="' . __('Open post in new tab.', 'etruel-del-post-copies') . '">' . $postid . '</a></td>
						<td><a title="' . __('View Details.', 'etruel-del-post-copies') . '" class="clickdetail">' . $title . '</a><br><span class="rowdetail" style="display: none;">' . $claves . $cmedias . '</span></td>
						<td>' . $cat_name . '</td>
						<td>' . $postdate . '</td>
						<td class="' . $okpostid . '_loading_td">' . $this->wpedpc_get_delete_post_link('X', '<div class="postdel" rel="' . $okpostid . '">', '</div>', $okpostid, true) . '</td>
						<td><a href="' . $okperma . '" target="_Blank">' . $okpostid . '</a></td>
						<td>' . $okpostdate . '</td>
					</tr>';
							$dispcount++;
						}
						$results .= $mensaje;
					}
				$results .= '<tr>
			<td colspan="6">' . __('Total', 'etruel-del-post-copies') . ': ' . $dispcount . '</td></tr>
			</tbody>
			</table>
			</div>';
			} elseif ($mode == 'counter') {

				$dispcount = $wpdb->get_col($wpdb->prepare("SELECT SUM(base.countr) AS mycounter FROM (SELECT SUM(1) AS countr, post_title FROM wp_posts WHERE (`post_status` = 'published') OR (`post_status` = 'publish' ) GROUP BY post_title HAVING COUNT(*) > 1) AS base"));
			} else {  //*************************************  mode = DELETE   *********************
				$dupes = $wpdb->get_results($query);
				$dispcount = 0;
				$statuserr = 0;
				foreach ($dupes as $dupe) {

					$postid = $dupe->ID;
					$title = $dupe->post_title;
					$wpcontent = $dupe->post_content;
					$perma = get_permalink($postid);
					$mensaje = "";
					if ($postid <> '') {
						if ($deletemedia) {

							$sql = "SELECT ID FROM $wp_posts WHERE post_parent = $postid AND post_type = 'attachment'";
							$ids = $wpdb->get_col($sql);
							foreach ($ids as $id) {
								wp_delete_attachment($id, $force_delete);
								if ($force_delete)
									unlink(get_attached_file($id));
								$mensaje .= sprintf(__("-- Post image id:'%s' Deleted! ", "etruel-del-post-copies"), $id) . "<br>";
							}
						}
						if ($delimgcontent) {  //images in content					
							$images = apply_filters('wpedpc_parseImages', array(), $wpcontent);
							$itemUrl = $perma;  //self::getReadUrl($perma);
							$images = array_values(array_unique($images));
							if (sizeof($images)) { // Si hay alguna imagen en el contenido
								$img_new_url = array();
								foreach ($images as $imagen_src) {
									$imagen_src_real = apply_filters('wpedpc_getRelativeUrl', $itemUrl, $imagen_src);
									if ($this->wpedpc_get_domain($imagen_src) == $this->wpedpc_get_domain(home_url())) {
										$file = $_SERVER['DOCUMENT_ROOT'] . str_replace(home_url(), "", $imagen_src_real);
										if (file_exists($file)) {
											unlink($file);
											$mensaje .= sprintf(__("-- img file:'%s' Deleted! ", "etruel-del-post-copies"), $file) . "<br>";
										}
									} else {
										// image are external. Different domain.";
									}
								}
							}
						}

						$custom_field_keys = get_post_custom_keys($postid);
						foreach ($custom_field_keys as $key => $value) {
							delete_post_meta($postid, $key, '');
							$mensaje .= sprintf(__("-- Post META key:'%s', value: '%s'. Deleted! ", "etruel-del-post-copies"), $key, $value) . "<br>";
						}
						$result = wp_delete_post($postid, $force_delete);
						if (!$result) {
							$mensaje = sprintf(__("!! Problem deleting post %s - %s !!", "etruel-del-post-copies"), $postid, $perma) . "<br>" . $mensaje;
							$statuserr++;
						} else {
							$mensaje = sprintf(__("'%s' (ID #%s) Deleted!", "etruel-del-post-copies"), $title, $postid) . "<br>" . $mensaje;
							$dispcount++;
						}
						if ($mode == 'now') {
							$message .= $mensaje;
						}
					}
				}
				$mtime = explode(' ', microtime());
				$time_end = $mtime[1] + $mtime[0];
				$time_total = $time_end - $time_start;
				$status_mode = (($mode == 'auto') ? '0' : '1');

				$wpedpc_campaign->logs[] = array('started' => current_time('timestamp'), 'took' => $time_total, 'mode' => $status_mode, 'status' => $statuserr, 'removed' => $dispcount);
				$wpedpc_campaign->schedule = edel_post_copies::wpedpc_cron_next($wpedpc_campaign->period);

				if ($mode == 'auto') {
					$wpedpc_campaign->doingcron = false;
				}

				$wpedpc_campaign->__save();
			}
			if ($mode == 'now') {
				$message .= 'Total: ' . $dispcount . ' ' . __('deleted posts copies!', 'WP_del_post_copies');
			}
			if ($mode == 'show') {
				$message .= '<p>Total: <strong>' . $dispcount . '</strong> ' . __('Posts copies!', 'WP_del_post_copies') . '<br/>' . __('Go to Campaign Results to see the list.', 'WP_del_post_copies') . '</p>';
			}
			if ($mode == 'counter') {
				$message .= '<div class="updated fade"><p>Total: <strong>' . $dispcount . '</strong> ' . __('posts copies!', 'WP_del_post_copies') . '</p></div>';
			}

			$response['message'] = $message;
			$response['results'] = $results;
			return ($mode == 'auto' ? array('success' => true) : $response );
		}

		function wpedpc_parseImages($images, $text) {
			preg_match_all('/<img(.+?)src=\"(.+?)\"(.*?)>/', $text, $images);  //for tag img
			preg_match_all('/<link rel=\"(.+?)\" type=\"image\/jpg\" href=\"(.+?)\"(.+?)\/>/', $text, $images2); // for rel=enclosure
			array_push($images, $images2);  // sum all items to array 
			return $images[2];
		}

		function wpedpc_getRelativeUrl($baseUrl, $relative) {
			$schemes = array('http', 'https', 'ftp');
			foreach ($schemes as $scheme) {
				if (strpos($relative, "{$scheme}://") === 0) //if not relative
					return $relative;
			}

			$urlInfo = parse_url($baseUrl);

			$basepath = $urlInfo['path'];
			$basepathComponent = explode('/', $basepath);
			$resultPath = $basepathComponent;
			$relativeComponent = explode('/', $relative);
			$last = array_pop($relativeComponent);
			foreach ($relativeComponent as $com) {
				if ($com === '') {
					$resultPath = array('');
				} else if ($com == '.') {
					$cur = array_pop($resultPath);
					if ($cur === '') {
						array_push($resultPath, $cur);
					} else {
						array_push($resultPath, '');
					}
				} else if ($com == '..') {
					if (count($resultPath) > 1)
						array_pop($resultPath);
					array_pop($resultPath);
					array_push($resultPath, '');
				} else {
					if (count($resultPath) > 1)
						array_pop($resultPath);
					array_push($resultPath, $com);
					array_push($resultPath, '');
				}
			}
			array_pop($resultPath);
			array_push($resultPath, $last);
			$resultPathReal = implode('/', $resultPath);
			return $urlInfo['scheme'] . '://' . $urlInfo['host'] . $resultPathReal;
		}

		function wpedpc_getReadUrl($url) {
			$headers = get_headers($url);
			foreach ($headers as $header) {
				$parts = explode(':', $header, 2);
				if (strtolower($parts[0]) == 'location')
					return trim($parts[1]);
			}
			return $url;
		}

		function wpedpc_get_domain($url) {
			$pieces = parse_url($url);
			$domain = isset($pieces['host']) ? $pieces['host'] : '';
			if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
				return $regs['domain'];
			}
			return false;
		}

		function wp_delete_post_link($link = 'Delete', $before = '', $after = '') {
			echo wpedpc_get_delete_post_link($link, $before, $after);
		}

		function wpedpc_get_delete_post_link($link = 'Delete', $before = '', $after = '', $postid = 0, $ajaxcall = false) {
			$nogo = '';
			if ($postid == 0) {
				global $post;
				if ($post->post_type == 'page') {
					if (!current_user_can('edit_page', $post->ID)) {
						return '';
					}
				} else {
					if (!current_user_can('edit_post', $post->ID)) {
						return '';
					}
				}
				$link = '<a href="' . wp_nonce_url(get_bloginfo('url') . '/wp-admin/post.php?action=delete&amp;post=' . $post->ID, 'delete-post_' . $post->ID) . '">' . $link . '</a>';
			} else {
				if (!current_user_can('edit_post', $postid)) {
					return '';
				}
				$link = '<a href="' . wp_nonce_url(get_bloginfo('url') . '/wp-admin/post.php?action=delete&amp;post=' . $postid, 'delete-post_' . $postid) . '">' . $link . '</a>';
			}
			return $before . $link . $after;
		}

	}

	endif;

$run_campaign = new wpedpc_run_campaign();
