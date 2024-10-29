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
 * Register a custom AdHerder post type that will hold the ads
 * 
 */
function adherder_register_post_type() {
	$labels = array(
		'name' => __('Ads'),
		'singular_name' => __('Ad'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Ad (click help for FAQ)'),
		'edit_item' => __('Edit Ad'),
		'new_item' => __('New Ad'),
		'view_item' => __('View Ad'),
		'search_items' => __('Search Ads'),
		'not_found' =>  __('No Ads found'),
		'not_found_in_trash' => __('No Ads found in Trash'), 
		'parent_item_colon' => '',
		'menu_name' => __('Ads'),
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => false,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','editor','author')
	); 
	register_post_type('adherder_ad',$args);
}

function hadherder_store_admin() {
  if(!is_user_logged_in()) {
    return true; // always track users that are not logged in
  }
  if(!current_user_can('manage_options')) {
	  return true; // also track users that aren't admins
  }
  $options = get_option('adherder_options');
  return $options['track_logged_in']; // only track admins when the option says so
}

function adherder_store_impression($id) {
	if (hadherder_store_admin()) {
		if(get_post_custom_keys($id)&&in_array('adherder_impressions',get_post_custom_keys($id))){
			$adherder_impressions = get_post_meta($id,'adherder_impressions',true);
		}
		if (!isset($adherder_impressions)){
			$adherder_impressions = 0;
		}
		$adherder_impressions++;
		update_post_meta($id, 'adherder_impressions', $adherder_impressions);

		adherder_database_track($id, 'impression');
	}
}

function adherder_store_click($id) {
	if (hadherder_store_admin()) {
		if(get_post_custom_keys($id)&&in_array('adherder_clicks',get_post_custom_keys($id))){
			$adherder_clicks = get_post_meta($id,'adherder_clicks',true);
		}
		if (!isset($adherder_clicks)){
			$adherder_clicks = 0;
		}
		$adherder_clicks++;
		update_post_meta($id, 'adherder_clicks', $adherder_clicks);

		adherder_database_track($id, 'click');
	}
}

function adherder_get_impressions($id) {
	if(get_post_custom_keys($id)&&in_array('adherder_impressions',get_post_custom_keys($id))){
		return get_post_meta($id,'adherder_impressions',true);
	} else {
	   return 0;
	}
}
function adherder_get_clicks($id) {
	if(get_post_custom_keys($id)&&in_array('adherder_clicks',get_post_custom_keys($id))){
		return get_post_meta($id,'adherder_clicks',true);
	} else {
	   return 0;
	}
}

?>
