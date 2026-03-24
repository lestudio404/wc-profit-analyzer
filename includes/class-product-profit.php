<?php
/**
 * Product purchase cost fields and product reports.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST404_WPA_Product_Profit {

	/**
	 * Calculator.
	 *
	 * @var ST404_WPA_Calculator
	 */
	private $calculator;

	/**
	 * Constructor.
	 *
	 * @param ST404_WPA_Calculator $calculator Calculator.
	 */
	public function __construct( ST404_WPA_Calculator $calculator ) {
		$this->calculator = $calculator;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_product_options_pricing', array( $this, 'add_purchase_cost_field_simple' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_purchase_cost_field_simple' ) );
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'add_purchase_cost_field_variation' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_purchase_cost_field_variation' ), 10, 2 );
	}

	/**
	 * Add cost field for simple product.
	 *
	 * @return void
	 */
	public function add_purchase_cost_field_simple() {
		woocommerce_wp_text_input(
			array(
				'id'                => '_wpa_purchase_cost',
				'label'             => esc_html__( 'Cout d\'achat', 'wc-profit-analyzer' ),
				'type'              => 'text',
				'desc_tip'          => true,
				'description'       => esc_html__( 'Cout d\'achat unitaire utilise pour les calculs de rentabilite.', 'wc-profit-analyzer' ),
				'custom_attributes' => array(
					'step' => '0.01',
					'min'  => '0',
				),
			)
		);
	}

	/**
	 * Save simple product field.
	 *
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function save_purchase_cost_field_simple( $product_id ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		if ( ! current_user_can( 'edit_post', $product_id ) ) {
			return;
		}
		$value = st404_wpa_sanitize_decimal( st404_wpa_get_request_text( '_wpa_purchase_cost', 'post' ) );
		update_post_meta( $product_id, '_wpa_purchase_cost', (string) $value );
	}

	/**
	 * Add variation field.
	 *
	 * @param int     $loop Loop index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation Variation post.
	 * @return void
	 */
	public function add_purchase_cost_field_variation( $loop, $variation_data, $variation ) {
		$value = get_post_meta( $variation->ID, '_wpa_variation_purchase_cost', true );
		?>
		<p class="form-row form-row-full">
			<label><?php echo esc_html__( 'Cout d\'achat', 'wc-profit-analyzer' ); ?></label>
			<input
				type="text"
				class="short"
				name="wpa_variation_purchase_cost[<?php echo esc_attr( $loop ); ?>]"
				value="<?php echo esc_attr( $value ); ?>"
				placeholder="0.00"
			/>
		</p>
		<?php
	}

	/**
	 * Save variation field.
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $loop Loop index.
	 * @return void
	 */
	public function save_purchase_cost_field_variation( $variation_id, $loop ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}
		$raw   = $_POST['wpa_variation_purchase_cost'][ $loop ] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = st404_wpa_sanitize_decimal( wp_unslash( $raw ) );
		update_post_meta( $variation_id, '_wpa_variation_purchase_cost', (string) $value );
	}

	/**
	 * Build product profitability report for a date range.
	 *
	 * @param string $date_from Start Y-m-d.
	 * @param string $date_to End Y-m-d.
	 * @return array
	 */
	public function get_products_report( $date_from, $date_to ) {
		$settings = ST404_WPA_Settings::get();
		$mode     = $settings['calc_mode'] ?? 'ht';
		$order_ids = wc_get_orders(
			array(
				'status'       => array( 'wc-processing', 'wc-completed' ),
				'limit'        => 500,
				'paginate'     => true,
				'return'       => 'ids',
				'date_created' => $date_from && $date_to ? $date_from . '...' . $date_to : '',
			)
		);
		$order_ids = isset( $order_ids->orders ) ? $order_ids->orders : $order_ids;

		$rows = array();
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				$product_id = $item->get_product_id();
				if ( ! $product_id ) {
					continue;
				}
				if ( ! isset( $rows[ $product_id ] ) ) {
					$product              = wc_get_product( $product_id );
					$rows[ $product_id ]  = array(
						'product_id'    => $product_id,
						'name'          => $product ? $product->get_name() : __( '(Deleted product)', 'wc-profit-analyzer' ),
						'sku'           => $product ? $product->get_sku() : '',
						'purchase_cost' => st404_wpa_sanitize_decimal( get_post_meta( $product_id, '_wpa_purchase_cost', true ) ),
						'qty'           => 0,
						'revenue'       => 0,
						'cost_total'    => 0,
					);
				}

				$variation_id = $item->get_variation_id();
				$qty          = (float) $item->get_quantity();
				$line_revenue = (float) $item->get_total();
				if ( 'ttc' === $mode ) {
					$line_revenue += (float) $item->get_total_tax();
				}

				$unit_cost = 0.0;
				if ( $variation_id ) {
					$unit_cost = st404_wpa_sanitize_decimal( get_post_meta( $variation_id, '_wpa_variation_purchase_cost', true ) );
				}
				if ( $unit_cost <= 0 ) {
					$unit_cost = $rows[ $product_id ]['purchase_cost'];
				}

				$rows[ $product_id ]['qty']        += $qty;
				$rows[ $product_id ]['revenue']    += $line_revenue;
				$rows[ $product_id ]['cost_total'] += ( $unit_cost * $qty );
			}
		}

		foreach ( $rows as &$row ) {
			$profit        = $row['revenue'] - $row['cost_total'];
			$row['profit'] = $profit;
			$row['margin'] = $row['revenue'] > 0 ? ( $profit / $row['revenue'] ) * 100 : 0;
		}
		unset( $row );

		usort(
			$rows,
			static function( $a, $b ) {
				return $b['profit'] <=> $a['profit'];
			}
		);

		return $rows;
	}
}
