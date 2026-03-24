<?php
/**
 * Order-level profit UI and list columns.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST404_WPA_Order_Profit {

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
		add_action( 'add_meta_boxes', array( $this, 'register_order_metabox' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_profit_fields' ) );
		add_action( 'woocommerce_after_order_object_save', array( $this, 'refresh_cache_after_order_save' ) );

		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_columns' ), 30 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_order_columns' ), 30, 2 );

		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_hpos_order_columns' ), 30 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_hpos_order_columns' ), 30, 2 );
	}

	/**
	 * Add metabox.
	 *
	 * @return void
	 */
	public function register_order_metabox() {
		add_meta_box(
			'wpa_order_profit',
			esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
			array( $this, 'render_order_metabox' ),
			'shop_order',
			'side',
			'default'
		);

		if ( function_exists( 'wc_get_page_screen_id' ) ) {
			add_meta_box(
				'wpa_order_profit_hpos',
				esc_html__( 'Analyse de rentabilite', 'wc-profit-analyzer' ),
				array( $this, 'render_order_metabox' ),
				wc_get_page_screen_id( 'shop-order' ),
				'side',
				'default'
			);
		}
	}

	/**
	 * Render metabox content.
	 *
	 * @param WP_Post|WC_Order $object Object.
	 * @return void
	 */
	public function render_order_metabox( $object ) {
		$order = $object instanceof WC_Order ? $object : wc_get_order( $object->ID ?? 0 );
		if ( ! $order ) {
			echo '<p>' . esc_html__( 'Commande introuvable.', 'wc-profit-analyzer' ) . '</p>';
			return;
		}

		$metrics = $this->calculator->get_order_metrics( $order );
		$fields  = array(
			'shipping' => $order->get_meta( '_wpa_shipping_cost', true ),
			'payment'  => $order->get_meta( '_wpa_payment_fee', true ),
			'extra'    => $order->get_meta( '_wpa_extra_cost', true ),
			'note'     => $order->get_meta( '_wpa_profit_note', true ),
		);

		wp_nonce_field( 'wpa_save_order_profit', 'wpa_order_profit_nonce' );
		?>
		<div class="wpa-order-box">
			<p><strong><?php echo esc_html__( 'Chiffre d\'affaires', 'wc-profit-analyzer' ); ?>:</strong> <?php echo wp_kses_post( st404_wpa_price( $metrics['revenue'] ) ); ?></p>
			<p><strong><?php echo esc_html__( 'Cout produit', 'wc-profit-analyzer' ); ?>:</strong> <?php echo wp_kses_post( st404_wpa_price( $metrics['product_cost'] ) ); ?></p>
			<p><strong><?php echo esc_html__( 'Profit brut', 'wc-profit-analyzer' ); ?>:</strong> <?php echo wp_kses_post( st404_wpa_price( $metrics['gross_profit'] ) ); ?></p>
			<p><strong><?php echo esc_html__( 'Profit net', 'wc-profit-analyzer' ); ?>:</strong> <?php echo wp_kses_post( st404_wpa_price( $metrics['net_profit'] ) ); ?></p>
			<p><strong><?php echo esc_html__( 'Marge %', 'wc-profit-analyzer' ); ?>:</strong> <?php echo esc_html( number_format_i18n( $metrics['margin'], 2 ) ); ?>%</p>
			<hr />
			<p>
				<label for="wpa_shipping_cost"><strong><?php echo esc_html__( 'Cout expedition', 'wc-profit-analyzer' ); ?></strong></label>
				<input type="text" name="wpa_shipping_cost" id="wpa_shipping_cost" value="<?php echo esc_attr( $fields['shipping'] ); ?>" class="widefat" />
			</p>
			<p>
				<label for="wpa_payment_fee"><strong><?php echo esc_html__( 'Frais de paiement', 'wc-profit-analyzer' ); ?></strong></label>
				<input type="text" name="wpa_payment_fee" id="wpa_payment_fee" value="<?php echo esc_attr( $fields['payment'] ); ?>" class="widefat" />
			</p>
			<p>
				<label for="wpa_extra_cost"><strong><?php echo esc_html__( 'Cout additionnel', 'wc-profit-analyzer' ); ?></strong></label>
				<input type="text" name="wpa_extra_cost" id="wpa_extra_cost" value="<?php echo esc_attr( $fields['extra'] ); ?>" class="widefat" />
			</p>
			<p>
				<label for="wpa_profit_note"><strong><?php echo esc_html__( 'Note de rentabilite', 'wc-profit-analyzer' ); ?></strong></label>
				<textarea name="wpa_profit_note" id="wpa_profit_note" rows="3" class="widefat"><?php echo esc_textarea( $fields['note'] ); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Save order fields.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function save_order_profit_fields( $order_id ) {
		if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'edit_shop_order', $order_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wpa_order_profit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpa_order_profit_nonce'] ) ), 'wpa_save_order_profit' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$order->update_meta_data( '_wpa_shipping_cost', (string) st404_wpa_sanitize_decimal( st404_wpa_get_request_text( 'wpa_shipping_cost', 'post' ) ) );
		$order->update_meta_data( '_wpa_payment_fee', (string) st404_wpa_sanitize_decimal( st404_wpa_get_request_text( 'wpa_payment_fee', 'post' ) ) );
		$order->update_meta_data( '_wpa_extra_cost', (string) st404_wpa_sanitize_decimal( st404_wpa_get_request_text( 'wpa_extra_cost', 'post' ) ) );
		$order->update_meta_data( '_wpa_profit_note', sanitize_textarea_field( wp_unslash( $_POST['wpa_profit_note'] ?? '' ) ) );
		$order->save();

		$this->calculator->refresh_cached_metrics( $order );
	}

	/**
	 * Refresh cached values after order save.
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 */
	public function refresh_cache_after_order_save( $order ) {
		$this->calculator->refresh_cached_metrics( $order );
	}

	/**
	 * Add order list columns.
	 *
	 * @param array $columns Existing.
	 * @return array
	 */
	public function add_order_columns( $columns ) {
		$settings = ST404_WPA_Settings::get();
		if ( 'yes' !== ( $settings['enable_order_columns'] ?? 'yes' ) ) {
			return $columns;
		}
		$columns['wpa_net_profit'] = esc_html__( 'Profit net', 'wc-profit-analyzer' );
		$columns['wpa_margin']     = esc_html__( 'Marge %', 'wc-profit-analyzer' );
		return $columns;
	}

	/**
	 * Render order columns.
	 *
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_order_columns( $column, $post_id ) {
		if ( ! in_array( $column, array( 'wpa_net_profit', 'wpa_margin' ), true ) ) {
			return;
		}
		$order = wc_get_order( $post_id );
		$this->render_column_value( $column, $order );
	}

	/**
	 * Add HPOS columns.
	 *
	 * @param array $columns Existing.
	 * @return array
	 */
	public function add_hpos_order_columns( $columns ) {
		return $this->add_order_columns( $columns );
	}


	/**
	 * Render HPOS columns.
	 *
	 * @param string   $column Column.
	 * @param WC_Order $order Order object.
	 * @return void
	 */
	public function render_hpos_order_columns( $column, $order ) {
		$this->render_column_value( $column, $order );
	}

	/**
	 * Render formatted values.
	 *
	 * @param string        $column Column.
	 * @param WC_Order|null $order Order.
	 * @return void
	 */
	private function render_column_value( $column, $order ) {
		if ( ! $order instanceof WC_Order ) {
			echo '&ndash;';
			return;
		}
		$metrics = $this->calculator->get_order_metrics( $order );
		if ( 'wpa_net_profit' === $column ) {
			echo wp_kses_post( $this->get_profit_badge( $metrics['net_profit'] ) );
			return;
		}
		if ( 'wpa_margin' === $column ) {
			echo esc_html( number_format_i18n( $metrics['margin'], 2 ) . '%' );
		}
	}

	/**
	 * Bonus badge by profitability level.
	 *
	 * @param float $profit Net profit.
	 * @return string
	 */
	private function get_profit_badge( $profit ) {
		$class = 'wpa-badge-neutral';
		if ( $profit > 0 ) {
			$class = $profit >= 10 ? 'wpa-badge-positive' : 'wpa-badge-low';
		} elseif ( $profit < 0 ) {
			$class = 'wpa-badge-negative';
		}
		return sprintf(
			'<span class="wpa-profit-badge %1$s">%2$s</span>',
			esc_attr( $class ),
			wp_kses_post( st404_wpa_price( $profit ) )
		);
	}
}
