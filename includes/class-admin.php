<?php
/**
 * Admin pages controller.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPA_Admin {

	/**
	 * Calculator.
	 *
	 * @var WPA_Calculator
	 */
	private $calculator;

	/**
	 * Product profit.
	 *
	 * @var WPA_Product_Profit
	 */
	private $product_profit;

	/**
	 * Constructor.
	 *
	 * @param WPA_Calculator    $calculator Calculator.
	 * @param WPA_Product_Profit $product_profit Product module.
	 */
	public function __construct( WPA_Calculator $calculator, WPA_Product_Profit $product_profit ) {
		$this->calculator    = $calculator;
		$this->product_profit = $product_profit;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_wpa_export_orders_csv', array( $this, 'handle_export_csv' ) );
	}

	/**
	 * Menu registration.
	 *
	 * @return void
	 */
	public function register_menu() {
		$capability = 'manage_woocommerce';
		$parent     = 'wc-profit-analyzer';

		add_menu_page(
			esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
			esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
			$capability,
			$parent,
			array( $this, 'render_dashboard_page' ),
			'dashicons-chart-line',
			56
		);

		add_submenu_page(
			$parent,
			esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
			esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
			$capability,
			'wc-profit-analyzer',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			$parent,
			esc_html__( 'Analyse de rentabilite - Orders', 'wc-profit-analyzer' ),
			esc_html__( 'Rentabilite commandes', 'wc-profit-analyzer' ),
			$capability,
			'wc-profit-analyzer-orders',
			array( $this, 'render_orders_page' )
		);

		add_submenu_page(
			$parent,
			esc_html__( 'Analyse de rentabilite - Products', 'wc-profit-analyzer' ),
			esc_html__( 'Rentabilite produits', 'wc-profit-analyzer' ),
			$capability,
			'wc-profit-analyzer-products',
			array( $this, 'render_products_page' )
		);

		add_submenu_page(
			$parent,
			esc_html__( 'Analyse de rentabilite - Settings', 'wc-profit-analyzer' ),
			esc_html__( 'Parametres rentabilite', 'wc-profit-analyzer' ),
			$capability,
			'wc-profit-analyzer-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			$parent,
			esc_html__( 'Analyse de rentabilite - Guide', 'wc-profit-analyzer' ),
			esc_html__( 'Guide utilisateur', 'wc-profit-analyzer' ),
			$capability,
			'wc-profit-analyzer-guide',
			array( $this, 'render_guide_page' )
		);
	}

	/**
	 * Dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Vous n'avez pas les droits pour acceder a cette page.', 'wc-profit-analyzer' ) );
		}
		$range = $this->get_range_params();
		$data  = $this->get_orders_metrics_for_period( $range['from'], $range['to'] );
		include WPA_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Orders report page.
	 *
	 * @return void
	 */
	public function render_orders_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Vous n'avez pas les droits pour acceder a cette page.', 'wc-profit-analyzer' ) );
		}
		$range = $this->get_range_params();
		$data  = $this->get_orders_metrics_for_period( $range['from'], $range['to'] );
		include WPA_PLUGIN_DIR . 'admin/views/orders.php';
	}

	/**
	 * Products report page.
	 *
	 * @return void
	 */
	public function render_products_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Vous n'avez pas les droits pour acceder a cette page.', 'wc-profit-analyzer' ) );
		}
		$range = $this->get_range_params();
		$rows  = $this->product_profit->get_products_report( $range['from'], $range['to'] );
		include WPA_PLUGIN_DIR . 'admin/views/products.php';
	}

	/**
	 * Settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Vous n'avez pas les droits pour acceder a cette page.', 'wc-profit-analyzer' ) );
		}
		$settings = WPA_Settings::get();
		include WPA_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Guide page.
	 *
	 * @return void
	 */
	public function render_guide_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Vous n'avez pas les droits pour acceder a cette page.', 'wc-profit-analyzer' ) );
		}
		include WPA_PLUGIN_DIR . 'admin/views/guide.php';
	}

	/**
	 * CSV export for orders page.
	 *
	 * @return void
	 */
	public function handle_export_csv() {
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			wp_die( esc_html__( 'Methode de requete invalide.', 'wc-profit-analyzer' ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permissions insuffisantes.', 'wc-profit-analyzer' ) );
		}
		check_admin_referer( 'wpa_export_orders_csv' );

		$range = $this->get_range_params( 'post' );
		$data  = $this->get_orders_metrics_for_period( $range['from'], $range['to'] );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=wpa-orders-' . gmdate( 'Ymd-His' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Commande', 'Date', 'Chiffre d'affaires', 'Cout produit', 'Cout expedition', 'Frais de paiement', 'Cout additionnel', 'Profit net', 'Marge %' ) );

		foreach ( $data['orders'] as $row ) {
			fputcsv(
				$output,
				array(
					'#' . $row['order_id'],
					$row['date'],
					$row['revenue'],
					$row['product_cost'],
					$row['shipping'],
					$row['payment'],
					$row['extra'],
					$row['net_profit'],
					$row['margin'],
				)
			);
		}
		fclose( $output );
		exit;
	}

	/**
	 * Build date range.
	 *
	 * @return array
	 */
	private function get_range_params( $method = 'get' ) {
		$settings = WPA_Settings::get();
		$range    = wpa_get_request_text( 'range', $method );
		if ( ! $range ) {
			$range = $settings['dashboard_default_range'] ?? '30_days';
		}

		$today = new DateTimeImmutable( 'today', wp_timezone() );
		$from  = '';
		$to    = $today->format( 'Y-m-d' );

		switch ( $range ) {
			case 'today':
				$from = $today->format( 'Y-m-d' );
				break;
			case '7_days':
				$from = $today->sub( new DateInterval( 'P6D' ) )->format( 'Y-m-d' );
				break;
			case '30_days':
				$from = $today->sub( new DateInterval( 'P29D' ) )->format( 'Y-m-d' );
				break;
			case 'month':
				$from = $today->modify( 'first day of this month' )->format( 'Y-m-d' );
				break;
			case 'custom':
				$from = wpa_sanitize_date( wpa_get_request_text( 'from', $method ) );
				$to   = wpa_sanitize_date( wpa_get_request_text( 'to', $method ) );
				if ( ! $from || ! $to ) {
					$from  = $today->sub( new DateInterval( 'P29D' ) )->format( 'Y-m-d' );
					$to    = $today->format( 'Y-m-d' );
					$range = '30_days';
				} elseif ( $from > $to ) {
					$tmp  = $from;
					$from = $to;
					$to   = $tmp;
				}
				break;
			default:
				$range = '30_days';
				$from  = $today->sub( new DateInterval( 'P29D' ) )->format( 'Y-m-d' );
				break;
		}

		return array(
			'range' => $range,
			'from'  => $from,
			'to'    => $to,
		);
	}

	/**
	 * Aggregate order metrics for period.
	 *
	 * @param string $from Start date.
	 * @param string $to End date.
	 * @return array
	 */
	private function get_orders_metrics_for_period( $from, $to ) {
		$orders = wc_get_orders(
			array(
				'status'       => array( 'wc-processing', 'wc-completed' ),
				'limit'        => 500,
				'paginate'     => true,
				'date_created' => $from && $to ? $from . '...' . $to : '',
			)
		);
		$orders = isset( $orders->orders ) ? $orders->orders : $orders;

		$rows   = array();
		$totals = array(
			'revenue'      => 0.0,
			'product_cost' => 0.0,
			'net_profit'   => 0.0,
			'margin_sum'   => 0.0,
			'count'        => 0,
		);

		foreach ( $orders as $order ) {
			$m = $this->calculator->get_order_metrics( $order );

			$row = array(
				'order_id'     => $order->get_id(),
				'date'         => $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y-m-d H:i' ) : '',
				'revenue'      => $m['revenue'],
				'product_cost' => $m['product_cost'],
				'shipping'     => $m['shipping'],
				'payment'      => $m['payment'],
				'extra'        => $m['extra'],
				'net_profit'   => $m['net_profit'],
				'margin'       => $m['margin'],
			);
			$rows[] = $row;

			$totals['revenue']      += $m['revenue'];
			$totals['product_cost'] += $m['product_cost'];
			$totals['net_profit']   += $m['net_profit'];
			$totals['margin_sum']   += $m['margin'];
			$totals['count']++;
		}

		usort(
			$rows,
			static function( $a, $b ) {
				return $b['net_profit'] <=> $a['net_profit'];
			}
		);

		$totals['avg_margin'] = $totals['count'] ? ( $totals['margin_sum'] / $totals['count'] ) : 0;

		return array(
			'orders'   => $rows,
			'totals'   => $totals,
			'top'      => array_slice( $rows, 0, 5 ),
			'worst'    => array_slice( array_reverse( $rows ), 0, 5 ),
			'from'     => $from,
			'to'       => $to,
		);
	}
}
