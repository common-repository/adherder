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

function adherder_client_scripts() {
	wp_enqueue_script('adherder', plugins_url('/adherder/js/adherder.js'), array('jquery'), ADHERDER_VERSION_NUM);
	wp_localize_script( 'adherder', 'AdHerder', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

function adherder_ajax_init() {
	add_action('wp_enqueue_scripts', 'adherder_client_scripts');
	add_action('wp_ajax_nopriv_adherder_track_conversion', 'adherder_track_conversion');
	add_action('wp_ajax_adherder_track_conversion', 'adherder_track_conversion');
	add_action('wp_ajax_nopriv_adherder_track_impression', 'adherder_track_impression');
	add_action('wp_ajax_adherder_track_impression', 'adherder_track_impression');
	add_action('wp_ajax_nopriv_adherder_display_ajax', 'adherder_display_ajax');
	add_action('wp_ajax_adherder_display_ajax', 'adherder_display_ajax');
}

function adherder_track_conversion() {
  $ad_id = absint($_POST['ad_id']);
  adherder_store_click($ad_id);

  $response = json_encode( array( 'ad_id' => $ad_id, 'success' => true ) );
  header( "Content-Type: application/json" );
  echo $response;
  die();
}

function adherder_track_impression() {
  $ad_id = absint($_POST['ad_id']);
  adherder_store_impression($ad_id);

  $response = json_encode( array( 'ad_id' => $ad_id, 'success' => true ) );
  header( "Content-Type: application/json" );
  echo $response;
  die(); 
}

function adherder_display_ajax() {
	echo adherder_display();
	die();
}
?>
