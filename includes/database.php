<?php
/*
Copyright 2011 Tristan Kromer, Peter Backx (email : tristan@grasshopperherder.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

function adherder_database_init_options() {
    $options = array(
      'normal_weight' => 2,
      'converted_weight' => 1,
      'seen_weight' => 1,
      'seen_limit' => 3,
      'track_logged_in' => true,
      'ajax_widget' => false
    );
    $dbOptions = get_option("adherder_options");
    if(!empty($dbOptions)) {
      foreach($dbOptions as $key => $option) {
        $options[$key] = $option;
      }
    }
    update_option("adherder_options", $options);
}

function adherder_database_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'adherder_tracking';
    $sql = "CREATE TABLE " . $table_name . " (
  	    id mediumint(9) NOT NULL AUTO_INCREMENT,
   	    track_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	    post_id int NOT NULL,
	    user_id varchar(50) NULL,
	    track_type varchar(10) NOT NULL,
	    PRIMARY KEY  (id)
    );";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function adherder_database_track($id, $type) {
	global $wpdb;
	$uid = $_COOKIE['ctopt_uid'];
	$sql = 'INSERT INTO ' . $wpdb->prefix . 'adherder_tracking(post_id, user_id, track_type) VALUES ('
         . esc_sql($id) . ",'" . esc_sql($uid) . "','" . esc_sql($type) . "')";
	$wpdb->query($sql);
}

function adherder_database_clean() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'adherder_tracking';
	$sql = "DELETE FROM " . $table_name . " WHERE track_time < DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)";
	$wpdb->query($sql);
}

function adherder_database_clean_for_post($ad_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adherder_tracking';
    $sql = "DELETE FROM " . $table_name . " WHERE POST_ID = " . esc_sql($ad_id);
    $wpdb->query($sql); 
}

function adherder_database_has_converted($uid, $ad_id) {
    if(!preg_match("/^ctopt-uid-/", $uid))
      return false;

    global $wpdb;
    $table_name = $wpdb->prefix . 'adherder_tracking';
    $sql = "SELECT * FROM " . $table_name . "
             WHERE user_id = '" . esc_sql($uid) . "'
               AND track_type = 'click'
               AND post_id = " . esc_sql($ad_id);
    $conversions = $wpdb->get_results($sql);
    return !empty($conversions);
}

function adherder_database_has_seen($uid, $ad_id, $times) {
    if(!preg_match("/^ctopt-uid-/", $uid))
      return false;

    global $wpdb;
    $table_name = $wpdb->prefix . 'adherder_tracking';
    $sql = "SELECT COUNT(1) >= " . esc_sql($times) . " FROM " . $table_name . "
             WHERE user_id = '" . esc_sql($uid) . "'
               AND track_type = 'impression'
               AND post_id = " . esc_sql($ad_id);
    return $wpdb->get_var($sql); 
}

function adherder_database_find_reports() {
    global $wpdb;
    $reports = $wpdb->get_results("SELECT 
      id, post_title, post_status, 
      IFNULL((select meta_value from wp_postmeta where post_id = id and meta_key = 'adherder_impressions'),0) as impressions, 
      IFNULL((select meta_value from wp_postmeta where post_id = id and meta_key = 'adherder_clicks'), 0) as clicks, 
      IFNULL((select meta_value from wp_postmeta where post_id = id and meta_key = 'adherder_in_report'), 0) as in_report
      FROM wp_posts p WHERE post_type = 'adherder_ad'");
    foreach($reports as $report) {
      $conversion = 0;
      $confidence = 0;
      if($report->impressions != '0') {
        $conversion = ($report->clicks * 100) / $report->impressions;
        $conversion = round($conversion, 2);
        if($conversion > 100) {
			$conversion = 100; // filter out edge cases
		}
        
        $confidence = sqrt(($conversion * (100 - $conversion)) / $report->impressions);
        $confidence = round($confidence, 2);
      }
      $report->conversion = $conversion;
      $report->confidence = $confidence;
    }
    
    //calculate relevance
    foreach($reports as $report) {
		if(!$report->in_report) {
			$report->relevant = "null";
			$report->min = "null";
			$report->max = "null";
			$report->opening = "null";
			$report->closing = "null";
			continue;
		}
		$relevant = true;
		foreach($reports as $comp_report) {
			if($report->id == $comp_report->id || !$comp_report->in_report) {
				continue;
			}
			$diff      = abs($comp_report->conversion - $report->conversion);
			$conf_diff = $comp_report->confidence + $report->confidence;
			
			if($diff <= $conf_diff) {
				$relevant = false;
			}
		}
		$report->relevant = $relevant ? "true" : "false";
		
		$report->min = $report->conversion - $report->confidence;
		$report->max = $report->conversion + $report->confidence;
		if($relevant) {
			$report->opening = $report->min;
			$report->closing = $report->max;
		} else {
			$report->opening = $report->max;
			$report->closing = $report->min;
		}
	}
    return $reports;
}
?>
