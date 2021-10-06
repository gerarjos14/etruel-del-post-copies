<?php

/**
 * Run Function
 *
 * @package     WPEDPC
 * @subpackage  Functions/Run
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

function run_campaign_cron($mode = 'auto', $post_id = 0, $response=array(), $obj=array()) {
	global $wpdb, $wp_locale, $current_blog, $wpedpc_options;

	error_log("Executed run_campaign_cron()");
}

// Scheduled Action Hook
function wpedpc_cron_callback() {

	$args = array('post_type' => 'wpedpcampaign', 'orderby' => 'ID', 'order' => 'ASC', 'numberposts' => -1);
	$campaigns = get_posts($args);

	error_log("Executed wpedpc_cron_callback()");
	foreach ($campaigns as $post) {
		$campaign = new stdclass();

		$custom_field_keys = get_post_custom($post->ID);
		foreach ($custom_field_keys as $key => $value) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('wpedpc_clean_campaign_fields', $custom_field_keys);
		foreach ($custom_field_keys as $key => $value) {
			$campaign->$key = $value;
		}

		//error_log(var_export($campaign, true));

		if ($campaign->active) {
			if ($campaign->schedule <= current_time('timestamp')) {
				run_campaign_cron('auto', $post->ID, array(), $campaign);
				//run_campaign_cron($post->ID);
				//$response = wpedpc_campaign_options_quickdo($post->ID, 'WPdpc_auto');
			}
		}



		/*

		  $activated = $campaign['activated'];
		  $cronnextrun = $campaign['cronnextrun'];
		  if ( !$activated )
		  continue;
		  if ( $cronnextrun <= current_time('timestamp') ) {
		  //error_log("Campaing executed!");
		  //wpedpc_dojob( $post->ID );
		  //$response = apply_filters('wpedpc_run_campaign', $post->ID, 'WPdpc_auto' );
		  }
		 */
	}
}

add_action('wpedpc_func_event', 'wpedpc_cron_callback');
/* function wpedpc_cron_callback(  ) {
  return wp_mail( 'estebanmdp@gmail.com', 'Notification TEST', 'TEST', null );
  }
 */

// Custom Cron Recurrences
function wpedpc_cron_recurrence($schedules) {
	$schedules['wpedpc_interval'] = array(
		'display' => __('Every Five Minutes', 'textdomain'),
		'interval' => 5,
	);
	return $schedules;
}

add_filter('cron_schedules', 'wpedpc_cron_recurrence');

// Schedule Cron Job Event
function wpedpc_custom_cron() {

	if (!wp_next_scheduled('wpedpc_func_event')) {
		wp_schedule_event(current_time('timestamp'), 'wpedpc_interval', 'wpedpc_func_event');
	}
}

if (defined('WPEDPC_INCLUDED_FILES')) {
	add_action('init', 'wpedpc_custom_cron');
}


/* * ************************************************************************** */

/**
 * 
 * @param string $cronstring like UNIX CRON time string
 * @return mixed boolean if fail / timestamp if can calculate next run time
 */
function wpedpc_cron_next($cronstring) {
	error_log("Executed wpedpc_cron_next()");
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
			$step = 1;
			if (strstr($value, '/'))
				list($value, $step) = explode('/', $value, 2);
			//replase weekeday 7 with 0 for sundays
			if ($cronarraykey == 'wday')
				$value = str_replace('7', '0', $value);
			//ranges
			if (strstr($value, '-')) {
				list($first, $last) = explode('-', $value, 2);
				if (!is_numeric($first) or!is_numeric($last) or $last > 60 or $first > 60) //check
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
					$value = 1;
				if (strtolower($value) == 'feb')
					$value = 2;
				if (strtolower($value) == 'mar')
					$value = 3;
				if (strtolower($value) == 'apr')
					$value = 4;
				if (strtolower($value) == 'may')
					$value = 5;
				if (strtolower($value) == 'jun')
					$value = 6;
				if (strtolower($value) == 'jul')
					$value = 7;
				if (strtolower($value) == 'aug')
					$value = 8;
				if (strtolower($value) == 'sep')
					$value = 9;
				if (strtolower($value) == 'oct')
					$value = 10;
				if (strtolower($value) == 'nov')
					$value = 11;
				if (strtolower($value) == 'dec')
					$value = 12;
				//Week Day names
				if (strtolower($value) == 'sun')
					$value = 0;
				if (strtolower($value) == 'sat')
					$value = 6;
				if (strtolower($value) == 'mon')
					$value = 1;
				if (strtolower($value) == 'tue')
					$value = 2;
				if (strtolower($value) == 'wed')
					$value = 3;
				if (strtolower($value) == 'thu')
					$value = 4;
				if (strtolower($value) == 'fri')
					$value = 5;
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
