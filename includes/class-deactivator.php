<?php
/**
 * Plugin deactivator.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Deactivator {

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Intentionally empty for MVP.
	}
}
