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

/**
 * Setup the admin functions for AdHerder.
 * 
 * Registers a custom post type, called "Ad". Adds menu items
 *  & sorting options
 * 
 */
function adherder_admin_menu() {
	// add options and reporting menu items.
	$reportsMenu = add_submenu_page('edit.php?post_type=adherder_ad', 'Ad Herder reports', 'Reports', 'edit_posts', 'co-reporting-menu', 'adherder_reporting_page');
	$optionsMenu = add_submenu_page('edit.php?post_type=adherder_ad', 'AdHerder Options', 'Options', 'manage_options', 'adherder_options', 'adherder_options_page');

	// customize the columns in the admin interface
	add_filter('manage_edit-adherder_ad_sortable_columns', 'adherder_column_register_sortable');
	add_filter('posts_orderby', 'adherder_column_orderby', 10, 2);
	add_action('manage_posts_custom_column', 'adherder_column');
	add_filter('manage_edit-adherder_ad_columns', 'adherder_columns');

	// add JavaScript for reporting only
	add_action('load-'.$reportsMenu, 'adherder_report_scripts');
	add_action('admin_print_styles', 'adherder_help_styles');
	
	add_action('load-'.$reportsMenu, 'adherder_help_switch');
	add_action('load-'.$optionsMenu, 'adherder_help_switch');
}

function adherder_help_switch() {
	$screen = get_current_screen();
	//old style of help menu
	if(!method_exists($screen, 'add_help_tab')) {
		add_action('contextual_help', 'adherder_help', 10, 3);
	} else {
		$screen->add_help_tab(array(
			'id' => 'adherder-help',
			'title' => 'FAQ',
			'callback' => adherder_help_new
		));
	}
}

function adherder_help($contextual_help, $screen_id, $screen) {
	if(strstr($screen_id,'adherder_ad')) {
		include(plugin_dir_path(__FILE__).'/../template/help.php');
		return '';
	}
	return $contextual_help;
}

function adherder_help_new() {
	include(plugin_dir_path(__FILE__).'/../template/help.php');
}

function adherder_admin_init() {
	register_setting('adherder_options', 'adherder_options', 'adherder_validate_options');
	
	add_settings_section('adherder_ad_selection', 'Ad selection', 'adherder_ad_selection_text', 'edit.php?post_type=adherder_ad');
	add_settings_field('adherder_normal_weight', 'Normal/New Ad', 'adherder_normal_weight_input', 'edit.php?post_type=adherder_ad', 'adherder_ad_selection');
	add_settings_field('adherder_converted_weight', 'Ad for which a user has already converted', 'adherder_converted_weight_input', 'edit.php?post_type=adherder_ad', 'adherder_ad_selection');
	add_settings_field('adherder_seen_weight', 'Ad that has been seen (see below)', 'adherder_seen_weight_input', 'edit.php?post_type=adherder_ad', 'adherder_ad_selection');
	
	add_settings_section('adherder_display_limit', 'Display limit', 'adherder_display_limit_text', 'edit.php?post_type=adherder_ad');
	add_settings_field('adherder_seen_limit', 'Number', 'adherder_seen_limit_input', 'edit.php?post_type=adherder_ad', 'adherder_display_limit');

	add_settings_section('adherder_track_logged_in_section', 'Track administrators', 'adherder_track_logged_in_text', 'edit.php?post_type=adherder_ad');
	add_settings_field('adherder_track_logged_in', 'Track administrators?', 'adherder_track_logged_in_input', 'edit.php?post_type=adherder_ad', 'adherder_track_logged_in_section');

	add_settings_section('adherder_ajax_widget_section', 'Use Ajax to display widget?', 'adherder_ajax_widget_section_text', 'edit.php?post_type=adherder_ad');
	add_settings_field('adherder_ajax_widget', 'Use Ajax', 'adherder_ajax_widget_input', 'edit.php?post_type=adherder_ad', 'adherder_ajax_widget_section');
}

function adherder_ad_selection_text() {
	echo '<p>The different weights (numeric and >0) with which to display the ads. A higher value means they are more likely to be displayed. It is not suggested to put any of them at 0, but it is possible (they won\'t be displayed)</p>';
}

function adherder_normal_weight_input() {
	$options = get_option('adherder_options');
	echo "<input id='normal_weight' name='adherder_options[normal_weight]' type='text' value='{$options['normal_weight']}' />";
}

function adherder_converted_weight_input() {
	$options = get_option('adherder_options');
	echo "<input id='converted_weight' name='adherder_options[converted_weight]' type='text' value='{$options['converted_weight']}' />";
}

function adherder_seen_weight_input() {
	$options = get_option('adherder_options');
	echo "<input id='seen_weight' name='adherder_options[seen_weight]' type='text' value='{$options['seen_weight']}' />";
}

function adherder_display_limit_text() {
	echo '<p>Enter the number of times an ad is displayed before it is considered "seen"</p>';
}

function adherder_seen_limit_input() {
	$options = get_option('adherder_options');
	echo "<input id='seen_limit' name='adherder_options[seen_limit]' type='text' value='{$options['seen_limit']}' />";
}

function adherder_track_logged_in_text() {
	echo '<p>When this option is disabled, AdHerder will not store tracking data or impressions/click counts for administrators.</p>';
}

function adherder_track_logged_in_input() {
	$options = get_option('adherder_options');
	echo "<input id='track_logged_in' name='adherder_options[track_logged_in]' "; 
	checked($options['track_logged_in'], 1);
	echo " type='checkbox'  />";
}

function adherder_ajax_widget_section_text() {
	echo '<p>This will load the widget\'s content via an Ajax call. If you are using any kind of caching plugin and want correct results, you need to turn this on. But keep in mind that you might need to rewrite some ads that use JavaScript.</p>';
}

function adherder_ajax_widget_input() {
	$options = get_option('adherder_options');
	echo "<input id='ajax_widget' name='adherder_options[ajax_widget]' "; 
	checked($options['ajax_widget'], 1);
	echo " type='checkbox'  />";
}

function adherder_validate_options( $input ) {
	$valid   = array();
	$options = get_option('adherder_options');
	
	$input_normal_weight = $input['normal_weight'];
	if(is_numeric($input_normal_weight)) {
		$valid['normal_weight'] = absint($input_normal_weight);
	} else {
		add_settings_error('adherder_normal_weight', 'adherder_options_error', 'Weight must be >= 0');
		$valid['normal_weight'] = $options['normal_weight'];
	}
	
	$input_converted_weight = $input['converted_weight'];
	if(is_numeric($input_converted_weight)) {
		$valid['converted_weight'] = absint($input_converted_weight);
	} else {
		add_settings_error('adherder_converted_weight', 'adherder_options_error', 'Weight must be >= 0');
		$valid['converted_weight'] = $options['converted_weight'];
	}

	$input_seen_weight = $input['seen_weight'];
	if(is_numeric($input_seen_weight)) {
		$valid['seen_weight'] = absint($input_seen_weight);
	} else {
		add_settings_error('adherder_seen_weight', 'adherder_options_error', 'Weight must be >= 0');
		$valid['seen_weight'] = $options['seen_weight'];
	}
		
	$input_seen_limit = $input['seen_limit'];
	if(is_numeric($input_seen_limit)) {
		$valid['seen_limit'] = absint($input_seen_limit);
	} else {
		add_settings_error('adherder_seen_limit', 'adherder_options_error', 'Limit must be >= 0');
		$valid['seen_limit'] = $options['seen_limit'];
	}
	
	$valid['track_logged_in'] = isset($input['track_logged_in']);
	$valid['ajax_widget'] = isset($input['ajax_widget']);
	
	return $valid;
}

function adherder_options_page() {
	include(plugin_dir_path(__FILE__).'/../template/options.php');
}

/**
 * Enqueue the JavaScript used by the admin interface
 * 
 */
function adherder_report_scripts() {
	// the google chart API is used for reporting
	wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi');
}

/**
 * css for the help display
 */
function adherder_help_styles() {
	wp_enqueue_style('adherder-help', plugins_url('/adherder/css/adherder-help.css'));
}

function adherder_reporting_page() {
  $message = ''; 
  if(isset($_POST['adherder_bulk_action'])) {
    $ad_ids = explode(',',$_POST['adherder_bulk_ad_ids']);
    $action = $_POST['adherder_bulk_action'];
    foreach ($ad_ids as $ad_id) {
		if($action === 'publish' || $action === 'pending') {
			$ad_post = get_post($ad_id);
			if($ad_post && $ad_post->post_type == 'adherder_ad') {
				$post_update       = array();
				$post_update['ID'] = $ad_id;
				$post_changed      = false;
				if($ad_post->post_status == 'publish' && $action === 'pending') {
					$post_update['post_status'] = 'pending';
					$post_changed = true;
				} else if($ad_post->post_status == 'pending' && $action === 'publish') {
					$post_update['post_status'] = 'publish';
					$post_changed = true;
				}
				if($post_changed) {
					wp_update_post($post_update);
				}
			} else {
				$message .= "Ad id $ad_id is incorrect.<br/>";
			}
		}
		if($action === 'in_report' || $action === 'not_in_report') {
			$ad_in_report = get_post_meta($ad_id, 'adherder_in_report', true);
			update_post_meta($ad_id, 'adherder_in_report', ($action === 'in_report' ? 1 : 0));
		}
		if($action === 'clear_data') {
			$ad_post = get_post($ad_id);
			if($ad_post && $ad_post->post_type == 'adherder_ad') {
				adherder_database_clean_for_post($ad_id);
				update_post_meta($ad_id, 'adherder_impressions', 0);
				update_post_meta($ad_id, 'adherder_clicks', 0);
				$message = 'Cleared all data for ad with id ' . $ad_id;
			}
		}
	}
  }
/*	if(isset($_POST['adherder_cleanup_old_data'])) {
		adherder_database_clean();
		$message = 'Older impression data cleared';
		return array();
	} */
  $reports = adherder_database_find_reports();
  include(plugin_dir_path(__FILE__).'/../template/report.php');
}

function adherder_columns($columns)
{
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Action Title",
		"impressions" => "Impressions",
		"clicks" => "Clicks",
		"author" => "Author",
		"categories" => "Categories",
		"date" => "Date"
	);
	return $columns;
}

function adherder_column($column)
{
	global $post;
	if ("ID" == $column) echo $post->ID;
	elseif ("impressions" == $column) echo adherder_get_impressions($post->ID);
	elseif ("clicks" == $column)  echo adherder_get_clicks($post->ID);
}

function adherder_column_orderby($orderby, $wp_query) {
	global $wpdb;
 
	$wp_query->query = wp_parse_args($wp_query->query);
 
	if ( 'impressions' == @$wp_query->query['orderby'] )
		$orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'adherder_impressions') " . $wp_query->get('order');
 	
	if ( 'clicks' == @$wp_query->query['orderby'] )
		$orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'adherder_clicks') " . $wp_query->get('order');
		
	return $orderby;
}

function adherder_column_register_sortable($columns) {
	$columns['impressions'] = 'impressions';
 	$columns['clicks'] = 'clicks';
	return $columns;
}
?>
