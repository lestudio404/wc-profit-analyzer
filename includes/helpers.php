<?php
/**
 * Helper functions.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely sanitize decimal values.
 *
 * @param mixed $value Raw value.
 * @return float
 */
if ( ! function_exists( 'st404_wpa_sanitize_decimal' ) ) {
	/**
	 * Safely sanitize decimal values.
	 *
	 * @param mixed $value Raw value.
	 * @return float
	 */
	function st404_wpa_sanitize_decimal( $value ) {
		if ( is_string( $value ) ) {
			$value = str_replace( ',', '.', $value );
		}
		if ( function_exists( 'wc_format_decimal' ) ) {
			return (float) wc_format_decimal( $value );
		}
		return (float) preg_replace( '/[^0-9\.\-]/', '', (string) $value );
	}
}

/**
 * Fetch and sanitize request key.
 *
 * @param string $key Request key.
 * @param string $method get|post.
 * @return string
 */
if ( ! function_exists( 'st404_wpa_get_request_text' ) ) {
	/**
	 * Fetch and sanitize request key.
	 *
	 * @param string $key Request key.
	 * @param string $method get|post.
	 * @return string
	 */
	function st404_wpa_get_request_text( $key, $method = 'get' ) {
		$source = 'post' === strtolower( $method ) ? $_POST : $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $source[ $key ] ) ) {
			return '';
		}
		return sanitize_text_field( wp_unslash( $source[ $key ] ) );
	}
}

/**
 * Format money with store currency.
 *
 * @param float $amount Amount.
 * @return string
 */
if ( ! function_exists( 'st404_wpa_price' ) ) {
	/**
	 * Format money with store currency.
	 *
	 * @param float $amount Amount.
	 * @return string
	 */
	function st404_wpa_price( $amount ) {
		return function_exists( 'wc_price' ) ? wc_price( (float) $amount ) : number_format_i18n( (float) $amount, 2 );
	}
}

/**
 * Normalize date Y-m-d.
 *
 * @param string $value Raw date.
 * @return string
 */
if ( ! function_exists( 'st404_wpa_sanitize_date' ) ) {
	/**
	 * Normalize date Y-m-d.
	 *
	 * @param string $value Raw date.
	 * @return string
	 */
	function st404_wpa_sanitize_date( $value ) {
		$value = sanitize_text_field( (string) $value );
		$dt    = DateTime::createFromFormat( 'Y-m-d', $value );
		return $dt && $dt->format( 'Y-m-d' ) === $value ? $value : '';
	}
}
