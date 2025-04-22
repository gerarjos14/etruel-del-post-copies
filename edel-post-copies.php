<?php
/**
 * Plugin Name: WP Delete Post Copies
 * Plugin URI: https://etruel.com/downloads/wp-delete-post-copies/
 * Description: Maker of Campaigns of deletes. With every campaign can search and delete duplicated posts (types) by title or content on different categories.
 *  and can permanently delete them with images or send them to the trash in manual mode or automatic squeduled with Wordpress cron.
 * Author: Etruel Developments LLC
 * Author URI: https://etruel.com 
 * Version: 6.0
 * Text Domain: etruel-del-post-copies
 * Domain Path: languages
 *
 * WP delete post copies is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP delete post copies is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP delete post copies. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WPEDPC
 * @category Core
 * @author Esteban Truelsegaard
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;
// Plugin version
if (!defined('WPEDPC_VERSION'))
	define('WPEDPC_VERSION', '6.0');

//require_once 'includes/cron-functions.php';

if (!class_exists('edel_post_copies')) :

	/**
	 * Main WP delete post copies Class

	 */
	class edel_post_copies {

		private static $instance   = null;
		public static $prorequired = '2.5';

		public static function get_instance() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		function __construct() {
			$this->setup_globals();
			$this->register_autoloader();
			$this->includes();
			$this->setup_actions();
			//$this->load_textdomain();
		}

		private function setup_actions() {
			add_action('admin_menu', array($this, 'add_submenu_page'), 10);
			add_action('wpedpc_func_event', array($this, 'wpedpc_cron_callback'));
			add_filter('cron_schedules', array($this, 'wpedpc_cron_recurrence'));
			add_action('init', array($this, 'wpedpc_custom_cron'));
			add_action('init', array($this, 'load_textdomain'));
			add_filter('wpedpc_env_checks', array($this, 'wpedpc_env_checks'));
			do_action('wpedpc_setup_actions');
			add_action('admin_head', array(__CLASS__, 'admin_icon_style'));
		}

		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'etruel-del-post-copies'), '1.6');
		}

		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'etruel-del-post-copies'), '1.6');
		}

		private function setup_globals() {

			// Plugin Folder Path
			if (!defined('WPEDPC_PLUGIN_DIR'))
				define('WPEDPC_PLUGIN_DIR', plugin_dir_path(__FILE__));

			// Plugin Folder URL
			if (!defined('WPEDPC_PLUGIN_URL'))
				define('WPEDPC_PLUGIN_URL', plugin_dir_url(__FILE__));

			// Plugin Root File
			if (!defined('WPEDPC_PLUGIN_FILE'))
				define('WPEDPC_PLUGIN_FILE', __FILE__);

			// Make sure CAL_GREGORIAN is defined
			if (!defined('CAL_GREGORIAN'))
				define('CAL_GREGORIAN', 1);
		}

		private function includes() {
			global $wpedpc_options;
			require_once WPEDPC_PLUGIN_DIR . 'includes/post-types.php';
			if (file_exists(WPEDPC_PLUGIN_DIR . 'includes/deprecated-functions.php')) {
				require_once WPEDPC_PLUGIN_DIR . 'includes/deprecated-functions.php';
			}
			require_once WPEDPC_PLUGIN_DIR . 'includes/settings/display-settings.php';
			require_once WPEDPC_PLUGIN_DIR . 'includes/class-wpedpc-campaign.php';
			require_once WPEDPC_PLUGIN_DIR . 'includes/run-campaign.php';
			do_action('wpedpc_include_files');

			if (is_admin()) {
				require_once WPEDPC_PLUGIN_DIR . 'includes/admin-actions.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/ajax-actions.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/notices.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/admin-footer.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/plugins.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/meta-boxes-campaign.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/class-wpedpc-select2.php';
				$wpedpc_options = wpedpc_get_settings();
				require_once WPEDPC_PLUGIN_DIR . 'includes/settings/wpedpc_settings.php';
				require_once WPEDPC_PLUGIN_DIR . 'includes/settings/licenses-settings.php';
				do_action('wpedpc_include_admin_files');
			}
			require_once WPEDPC_PLUGIN_DIR . 'includes/install.php';
		}

		/**
		 * Register the built-in autoloader
		 * 
		 * @codeCoverageIgnore
		 */
		public static function register_autoloader() {
			spl_autoload_register(array('edel_post_copies', 'autoloader'));
		}

		/**
		 * Register autoloader.
		 * 
		 * @param String $class_name Class to load
		 */
		public static function autoloader($class_name) {
			$class = strtolower(str_replace('_', '-', $class_name));
			$file  = plugin_dir_path(__FILE__) . '/includes/class-' . $class . '.php';
			if (file_exists($file)) {
				require_once $file;
			}
		}

		public function load_textdomain() {
			// textdomain directory
			$lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
			// Load the default language files
			load_plugin_textdomain('etruel-del-post-copies', false, $lang_dir);
		}

		static function admin_icon_style() {
			?><style type="text/css">
				#adminmenu .menu-icon-wpedpcampaign .wp-menu-image img {
					padding-top: 3px;
				}
			</style><?php
		}

		public function add_submenu_page() {
			global $edpc_opt_page;
			$edpc_opt_page = add_submenu_page(
					'edit.php?post_type=wpedpcampaign',
					__('Settings', 'etruel-del-post-copies'),
					__('Settings', 'etruel-del-post-copies'),
					'manage_options',
					'edpc_options',
					'wpedpc_options_page'
			);
			add_action('admin_print_styles-' . $edpc_opt_page, array($this, 'admin_css'));
		}

		function wpedpc_cron_recurrence($schedules) {
			$schedules['wpedpc_interval'] = array(
				'display'  => __('Every Five Minutes', 'textdomain'),
				'interval' => 300,
			);
			return $schedules;
		}

		function wpedpc_custom_cron() {

			if (!wp_next_scheduled('wpedpc_func_event')) {
				wp_schedule_event(current_time('timestamp'), 'wpedpc_interval', 'wpedpc_func_event');
			}
		}

		function wpedpc_cron_callback() {

			$args	   = array('post_type' => 'wpedpcampaign', 'orderby' => 'ID', 'order' => 'ASC', 'numberposts' => -1);
			$campaigns = get_posts($args);
			foreach ($campaigns as $post) {
				$campaign = new WPEDPC_Campaign($post->ID);
				if ($campaign->active) {
					if ($campaign->schedule <= current_time('timestamp')) {
						$response = apply_filters('wpedpc_run_campaign', $post->ID, 'WPdpc_auto');
					}
				}
			}
		}

		static function wpedpc_cron_next($cronstring) {
			list($cronstr['minutes'], $cronstr['hours'], $cronstr['mday'], $cronstr['mon'], $cronstr['wday']) = explode(' ', $cronstring, 5);
			//make arrys form string
			foreach ($cronstr as $key => $value) {
				if (strstr($value, ','))
					$cronarray[$key] = explode(',', $value);
				else
					$cronarray[$key] = array(0 => $value);
			}
			//make arrys complete with ranges and steps
			foreach ($cronarray as $cronarraykey => $cronarrayvalue) {
				$cron[$cronarraykey] = array();
				foreach ($cronarrayvalue as $key => $value) {
					//steps
					$step  = 1;
					if (strstr($value, '/'))
						list($value, $step) = explode('/', $value, 2);
					//replase weekeday 7 with 0 for sundays
					if ($cronarraykey == 'wday')
						$value = str_replace('7', '0', $value);
					//ranges
					if (strstr($value, '-')) {
						list($first, $last) = explode('-', $value, 2);
						if (!is_numeric($first) or !is_numeric($last) or $last > 60 or $first > 60) //check
							return false;
						if ($cronarraykey == 'minutes' and $step < 5)  //set step ninmum to 5 min.
							$step = 5;
						$range = array();
						for ($i = $first; $i <= $last; $i = $i + $step)
							$range[] = $i;
						$cron[$cronarraykey] = array_merge($cron[$cronarraykey], $range);
					} elseif ($value == '*') {
						$range = array();
						if ($cronarraykey == 'minutes') {
							if ($step < 5) //set step ninmum to 5 min.
								$step = 5;
							for ($i = 0; $i <= 59; $i = $i + $step)
								$range[] = $i;
						}
						if ($cronarraykey == 'hours') {
							for ($i = 0; $i <= 23; $i = $i + $step)
								$range[] = $i;
						}
						if ($cronarraykey == 'mday') {
							for ($i = $step; $i <= 31; $i = $i + $step)
								$range[] = $i;
						}
						if ($cronarraykey == 'mon') {
							for ($i = $step; $i <= 12; $i = $i + $step)
								$range[] = $i;
						}
						if ($cronarraykey == 'wday') {
							for ($i = 0; $i <= 6; $i = $i + $step)
								$range[] = $i;
						}
						$cron[$cronarraykey] = array_merge($cron[$cronarraykey], $range);
					} else {
						//Month names
						if (strtolower($value) == 'jan')
							$value				 = 1;
						if (strtolower($value) == 'feb')
							$value				 = 2;
						if (strtolower($value) == 'mar')
							$value				 = 3;
						if (strtolower($value) == 'apr')
							$value				 = 4;
						if (strtolower($value) == 'may')
							$value				 = 5;
						if (strtolower($value) == 'jun')
							$value				 = 6;
						if (strtolower($value) == 'jul')
							$value				 = 7;
						if (strtolower($value) == 'aug')
							$value				 = 8;
						if (strtolower($value) == 'sep')
							$value				 = 9;
						if (strtolower($value) == 'oct')
							$value				 = 10;
						if (strtolower($value) == 'nov')
							$value				 = 11;
						if (strtolower($value) == 'dec')
							$value				 = 12;
						//Week Day names
						if (strtolower($value) == 'sun')
							$value				 = 0;
						if (strtolower($value) == 'sat')
							$value				 = 6;
						if (strtolower($value) == 'mon')
							$value				 = 1;
						if (strtolower($value) == 'tue')
							$value				 = 2;
						if (strtolower($value) == 'wed')
							$value				 = 3;
						if (strtolower($value) == 'thu')
							$value				 = 4;
						if (strtolower($value) == 'fri')
							$value				 = 5;
						if (!is_numeric($value) or $value > 60) //check
							return false;
						$cron[$cronarraykey] = array_merge($cron[$cronarraykey], array(0 => $value));
					}
				}
			}

			//calc next timestamp
			$currenttime = current_time('timestamp');
			foreach (array(date('Y'), date('Y') + 1) as $year) {
				foreach ($cron['mon'] as $mon) {
					foreach ($cron['mday'] as $mday) {
						foreach ($cron['hours'] as $hours) {
							foreach ($cron['minutes'] as $minutes) {
								$timestamp = mktime($hours, $minutes, 0, $mon, $mday, $year);
								if (in_array(date('w', $timestamp), $cron['wday']) and $timestamp > $currenttime) {
									return $timestamp;
								}
							}
						}
					}
				}
			}
			return false;
		}

		public function admin_css() {
			global $pagenow, $typenow;
			global $edpc_opt_page;
			$active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], wpedpc_get_settings_tabs()) ? $_GET['tab'] : 'settings';
			if ($active_tab == 'licenses') {
				wp_enqueue_script('jquery-ui-tabs');
				wp_enqueue_style('jquery-style', WPEDPC_PLUGIN_URL . 'includes/css/tabs-style.css');
			}
		}

		static function wpedpc_env_checks($checks) {
			global $wp_version, $user_ID, $wpedpc_admin_message;
			$message = $wpedpc_admin_message = '';
			if (!is_admin()) {
				return false;
			}
			if (version_compare($wp_version, '3.1', '<')) { // check WP Version
				$message .= __('- WordPress 3.1 or higher needed!', 'etruel-del-post-copies') . '<br />';
				$checks	 = false;
			}
			if (version_compare(phpversion(), '5.4.0', '<')) { // check PHP Version
				$message .= __('- PHP 5.4.0 or higher needed!', 'etruel-del-post-copies') . '<br />';
				$checks	 = false;
			}
			//put massage if one
			if (!empty($message)) {
				$wpedpc_admin_message = '<div id="message" class="error fade"><strong>WP Deletes Post Copies:</strong><br />' . $message . '</div>';
				add_action('admin_notices', array(__CLASS__, 'wpedpc_env_checks_notice'));
			}
			return $checks;
		}

		static function wpedpc_env_checks_notice() {
			global $wpedpc_admin_message;
			echo wp_kses_post($wpedpc_admin_message);
		}
	}

	endif; // End if class_exists check

function WPEDPC() {
	//if( !apply_filters('wpedpc_env_checks', true) ) {
	//return false;
	//}
	return edel_post_copies::get_instance();
}

// Get WPEDPC Running
WPEDPC();
?>
