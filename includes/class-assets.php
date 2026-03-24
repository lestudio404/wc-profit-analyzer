<?php
/**
 * Admin assets.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST404_WPA_Assets {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue assets on plugin screens.
	 *
	 * @param string $hook Current hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$is_plugin_screen = false !== strpos( $hook, 'wc-profit-analyzer' );
		$order_screen_ids = array( 'shop_order' );
		if ( function_exists( 'wc_get_page_screen_id' ) ) {
			$order_screen_ids[] = wc_get_page_screen_id( 'shop-order' );
		}
		$is_order_screen = in_array( $screen->id, $order_screen_ids, true );

		if ( ! $is_plugin_screen && ! $is_order_screen ) {
			return;
		}

		wp_enqueue_style(
			'wpa-admin',
			WPA_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WPA_VERSION
		);

		wp_enqueue_script(
			'wpa-admin',
			WPA_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			WPA_VERSION,
			true
		);
	}
}
