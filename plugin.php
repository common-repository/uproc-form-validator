<?php
/*
The UProc for Wordpress start file. Here we do initialize the plugin.

Plugin Name: UProc for Wordpress
Plugin URI: https://uproc.io/uproc_for_wordpress
Version: 1.0.8
Description: UProc for Wordpress plugin allows to validate any form field data using UProc data quality service.
Author: UProc
Author URI: https://uproc.io
Text Domain: uproc-form-validator
Domain Path: /languages
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

*/

require_once( dirname( __FILE__ ) . '/src/functions.php' );
require_once( dirname( __FILE__ ) . '/src/class-upfvp-plugin.php' );
add_action( 'after_setup_theme', 'upfvp_load', 11 );

/**
 * Initialize the plugin
 *
 * @return void
 */
function upfvp_load() {
	$plugin = UPFVP_Plugin::get_instance();
	$plugin->plugin_setup();
}
