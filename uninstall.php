<?php
/**
 * Run the uninstall routine
 *
 * @package upfvp
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'upfvp-auth-invalid' );
delete_option( 'upfvp_settings' );
