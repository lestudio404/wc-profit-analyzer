<?php
/**
 * Plugin Name: WooCommerce Profit Analyzer
 * Description: Analyze WooCommerce order and product profitability with configurable costs and margin insights.
 * Version: 1.1.9
 * Author: ST404
 * Text Domain: wc-profit-analyzer
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPA_VERSION', '1.1.9' );
define( 'WPA_PLUGIN_FILE', __FILE__ );
define( 'WPA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WPA_PLUGIN_DIR . 'includes/helpers.php';
require_once WPA_PLUGIN_DIR . 'includes/class-activator.php';
require_once WPA_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once WPA_PLUGIN_DIR . 'includes/class-assets.php';
require_once WPA_PLUGIN_DIR . 'includes/class-database.php';
require_once WPA_PLUGIN_DIR . 'includes/class-settings.php';
require_once WPA_PLUGIN_DIR . 'plugin-update-checker-loader.php';
require_once WPA_PLUGIN_DIR . 'includes/class-calculator.php';
require_once WPA_PLUGIN_DIR . 'includes/class-order-profit.php';
require_once WPA_PLUGIN_DIR . 'includes/class-product-profit.php';
require_once WPA_PLUGIN_DIR . 'includes/class-admin.php';
require_once WPA_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'ST404_WPA_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ST404_WPA_Deactivator', 'deactivate' ) );

/**
 * Boot plugin.
 *
 * @return void
 */
function st404_wpa_boot_plugin() {
	ST404_WPA_Plugin::instance()->run();
}

st404_wpa_boot_plugin();
