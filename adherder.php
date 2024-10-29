<?php
/*
Plugin Name: AdHerder
Plugin URI: http://grasshopperherder.com/
Description: Displays call to actions, tracks their performance and optimizes placement
Version: 1.3
Author: Tristan Kromer, Peter Backx
Author URI: http://grasshopperherder.com/
License: GPLv2

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

if ( !defined('ADHERDER_VERSION') ) {
	define('ADHERDER_VERSION', 'adherder_version');
}
if ( !defined('ADHERDER_VERSION_NUM') ) {
	define('ADHERDER_VERSION_NUM', '1.2');
}
add_option(ADHERDER_VERSION, ADHERDER_VERSION_NUM);

if ( !defined('ADHERDER_FEEDBACK_LINK') ) {
	define('ADHERDER_FEEDBACK_LINK', 'mailto:tristan@grasshopperherder.com');
}
if ( !defined('ADHERDER_DONATE_EMAIL') ) {
	define('ADHERDER_DONATE_EMAIL', 'accounts@tristankromer.com');
}
	
// code that should always be loaded
require_once(plugin_dir_path(__FILE__)."/includes/database.php");
require_once(plugin_dir_path(__FILE__)."/includes/display.php");
require_once(plugin_dir_path(__FILE__)."/includes/functions.php");
require_once(plugin_dir_path(__FILE__)."/includes/ajax.php");

// register AdHerder post type
add_action( 'init', 'adherder_register_post_type');

// register widget
add_action('widgets_init', create_function('', 'return register_widget("Adherder_Widget");'));

// add the administrative functions only when in the admin interface
if ( is_admin() ) {
	require_once(plugin_dir_path(__FILE__).'/includes/admin.php' );
	add_action('admin_menu', 'adherder_admin_menu');
	add_action('admin_init', 'adherder_admin_init');
}

// install click tracking database table on activation
register_activation_hook(__FILE__, 'adherder_database_install');
register_activation_hook(__FILE__, 'adherder_database_init_options');

// add Ajax action to the public blog
adherder_ajax_init();
?>
