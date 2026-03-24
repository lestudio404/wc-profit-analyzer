<?php
/**
 * Main plugin orchestrator.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var WPA_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Calculator.
	 *
	 * @var WPA_Calculator
	 */
	private $calculator;

	/**
	 * Get singleton.
	 *
	 * @return WPA_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->calculator = new WPA_Calculator();
	}

	/**
	 * Run plugin.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init_modules' ), 20 );
	}

	/**
	 * Declare HPOS compatibility.
	 *
	 * @return void
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WPA_PLUGIN_FILE, true );
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wc-profit-analyzer', false, dirname( WPA_PLUGIN_BASENAME ) . '/languages/' );
	}

	/**
	 * Bootstrap modules if WooCommerce exists.
	 *
	 * @return void
	 */
	public function init_modules() {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_orders' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		( new WPA_Database() )->init();
		( new WPA_Assets() )->init();
		( new WPA_Settings() )->init();

		$product_profit = new WPA_Product_Profit( $this->calculator );
		$product_profit->init();

		$order_profit = new WPA_Order_Profit( $this->calculator );
		$order_profit->init();

		$admin = new WPA_Admin( $this->calculator, $product_profit );
		$admin->init();
	}

	/**
	 * WooCommerce dependency notice.
	 *
	 * @return void
	 */
	public function woocommerce_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'WooCommerce Analyse de rentabilite necessite que WooCommerce soit actif.', 'wc-profit-analyzer' );
		echo '</p></div>';
	}
}
