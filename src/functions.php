<?php
/**
 * In this file you find functions
 *
 * @package upfvp
 */

/**
 * Use this function in your plugin or theme to activate frontend Ajax API requests.
 * An input field with the class .upfvp-phone will be validated before the form gets send.
 */
function upfvp_activate_third_party() {

	// The ajax request endpoint is public now.
	add_filter( 'upfvp_api_is_private', '__return_false' );
	add_action( 'wp_enqueue_scripts', array( UPFVP_Plugin::get_instance(), 'enqueue_frontend' ), 11 );
	add_action( 'wp_footer', array( UPFVP_Plugin::get_instance(), 'footer_styles' ) );
}
