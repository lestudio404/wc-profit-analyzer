<?php
/**
 * Database wrapper for future extensions.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Database {

	/**
	 * Init db layer.
	 *
	 * @return void
	 */
	public function init() {
		// No custom tables needed for MVP.
	}
}
