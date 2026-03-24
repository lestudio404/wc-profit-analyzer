<?php
/**
 * Settings handler.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Settings {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'calc_mode'               => 'ht',
			'enable_order_columns'    => 'yes',
			'payment_fee_method'      => 'manual',
			'payment_fee_fixed'       => '0',
			'payment_fee_percent'     => '0',
			'enable_shipping_cost'    => 'yes',
			'dashboard_default_range' => '30_days',
		);
	}

	/**
	 * Get full settings.
	 *
	 * @return array
	 */
	public static function get() {
		return wp_parse_args( (array) get_option( 'wpa_settings', array() ), self::get_defaults() );
	}

	/**
	 * Register setting.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'wpa_settings_group',
			'wpa_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::get_defaults(),
			)
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$defaults = self::get_defaults();
		$output   = array();

		$output['calc_mode'] = in_array( $input['calc_mode'] ?? 'ht', array( 'ht', 'ttc' ), true ) ? $input['calc_mode'] : 'ht';

		$output['enable_order_columns'] = isset( $input['enable_order_columns'] ) ? 'yes' : 'no';
		$output['enable_shipping_cost'] = isset( $input['enable_shipping_cost'] ) ? 'yes' : 'no';

		$methods                       = array( 'manual', 'percent_global', 'fixed_plus_percent' );
		$method                        = $input['payment_fee_method'] ?? 'manual';
		$output['payment_fee_method']  = in_array( $method, $methods, true ) ? $method : 'manual';
		$output['payment_fee_fixed']   = (string) wpa_sanitize_decimal( $input['payment_fee_fixed'] ?? '0' );
		$output['payment_fee_percent'] = (string) wpa_sanitize_decimal( $input['payment_fee_percent'] ?? '0' );

		$ranges                           = array( 'today', '7_days', '30_days', 'month', 'custom' );
		$default_range                    = $input['dashboard_default_range'] ?? '30_days';
		$output['dashboard_default_range'] = in_array( $default_range, $ranges, true ) ? $default_range : '30_days';

		return wp_parse_args( $output, $defaults );
	}
}
