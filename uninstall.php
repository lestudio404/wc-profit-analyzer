<?php
/**
 * Uninstall handler.
 *
 * @package WPA
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wpa_settings' );
