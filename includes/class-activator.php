<?php
/**
 * Plugin activator.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Activator {

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		$defaults = array(
			'calc_mode'               => 'ht',
			'enable_order_columns'    => 'yes',
			'payment_fee_method'      => 'manual',
			'payment_fee_fixed'       => '0',
			'payment_fee_percent'     => '0',
			'enable_shipping_cost'    => 'yes',
			'dashboard_default_range' => '30_days',
		);
		$stored   = get_option( 'wpa_settings', array() );
		update_option( 'wpa_settings', wp_parse_args( $stored, $defaults ) );
	}
}
