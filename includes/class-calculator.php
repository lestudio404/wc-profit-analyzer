<?php
/**
 * Centralized profitability calculations.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ST404_WPA_Calculator {

	/**
	 * Return full order metrics array.
	 *
	 * @param WC_Order|int $order Order.
	 * @return array
	 */
	public function get_order_metrics( $order ) {
		$order = is_numeric( $order ) ? wc_get_order( (int) $order ) : $order;
		if ( ! $order instanceof WC_Order ) {
			return array();
		}

		$revenue      = $this->get_order_revenue( $order );
		$product_cost = $this->get_order_product_cost( $order );
		$shipping     = $this->get_order_shipping_cost( $order );
		$payment      = $this->get_order_payment_fee( $order );
		$extra        = $this->get_order_extra_cost( $order );
		$gross        = $revenue - $product_cost;
		$net          = $revenue - $product_cost - $shipping - $payment - $extra;
		$margin       = $revenue > 0 ? ( $net / $revenue ) * 100 : 0;

		return array(
			'revenue'      => $revenue,
			'product_cost' => $product_cost,
			'shipping'     => $shipping,
			'payment'      => $payment,
			'extra'        => $extra,
			'gross_profit' => $gross,
			'net_profit'   => $net,
			'margin'       => $margin,
		);
	}

	/**
	 * Revenue (products + shipping paid by customer) HT/TTC based on settings.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_revenue( $order ) {
		$settings = ST404_WPA_Settings::get();
		$mode     = $settings['calc_mode'] ?? 'ht';
		$revenue  = 0.0;

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$line_total = (float) $item->get_total();
			$line_tax   = (float) $item->get_total_tax();
			$revenue   += 'ttc' === $mode ? ( $line_total + $line_tax ) : $line_total;
		}

		// Shipping paid by the customer is part of revenue (distinct from the merchant carrier cost field).
		$shipping_total = (float) $order->get_shipping_total();
		$shipping_tax   = (float) $order->get_shipping_tax();
		$revenue       += 'ttc' === $mode ? ( $shipping_total + $shipping_tax ) : $shipping_total;

		return max( 0, $revenue );
	}

	/**
	 * Total purchase cost from product/variation metadata.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_product_cost( $order ) {
		$total = 0.0;

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$qty          = (float) $item->get_quantity();
			$unit_cost    = 0.0;

			if ( $variation_id ) {
				$unit_cost = st404_wpa_sanitize_decimal( get_post_meta( $variation_id, '_wpa_variation_purchase_cost', true ) );
			}
			if ( $unit_cost <= 0 && $product_id ) {
				$unit_cost = st404_wpa_sanitize_decimal( get_post_meta( $product_id, '_wpa_purchase_cost', true ) );
			}

			$total += max( 0, $unit_cost ) * max( 0, $qty );
		}

		return $total;
	}

	/**
	 * Shipping cost from manual meta.
	 *
	 * Important: WooCommerce "shipping_total" is what the customer pays (revenue),
	 * not the merchant's carrier cost. Using it as a cost would skew profitability.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_shipping_cost( $order ) {
		$settings = ST404_WPA_Settings::get();
		if ( 'yes' !== ( $settings['enable_shipping_cost'] ?? 'yes' ) ) {
			return 0.0;
		}

		$manual = $order->get_meta( '_wpa_shipping_cost', true );
		if ( '' !== $manual && null !== $manual ) {
			return max( 0, st404_wpa_sanitize_decimal( $manual ) );
		}
		return 0.0;
	}

	/**
	 * Payment fee based on settings and optional per-order override.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_payment_fee( $order ) {
		$manual = $order->get_meta( '_wpa_payment_fee', true );
		if ( '' !== $manual && null !== $manual ) {
			return max( 0, st404_wpa_sanitize_decimal( $manual ) );
		}

		$settings = ST404_WPA_Settings::get();
		$method   = $settings['payment_fee_method'] ?? 'manual';
		$revenue  = $this->get_order_revenue( $order );
		$fixed    = st404_wpa_sanitize_decimal( $settings['payment_fee_fixed'] ?? '0' );
		$percent  = st404_wpa_sanitize_decimal( $settings['payment_fee_percent'] ?? '0' );

		if ( 'percent_global' === $method ) {
			return max( 0, $revenue * ( $percent / 100 ) );
		}
		if ( 'fixed_plus_percent' === $method ) {
			return max( 0, $fixed + ( $revenue * ( $percent / 100 ) ) );
		}

		return 0.0;
	}

	/**
	 * Extra manual cost.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_extra_cost( $order ) {
		return max( 0, st404_wpa_sanitize_decimal( $order->get_meta( '_wpa_extra_cost', true ) ) );
	}

	/**
	 * Gross profit.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_gross_profit( $order ) {
		$m = $this->get_order_metrics( $order );
		return (float) ( $m['gross_profit'] ?? 0 );
	}

	/**
	 * Net profit.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_net_profit( $order ) {
		$m = $this->get_order_metrics( $order );
		return (float) ( $m['net_profit'] ?? 0 );
	}

	/**
	 * Margin percent.
	 *
	 * @param WC_Order $order Order.
	 * @return float
	 */
	public function get_order_margin_percent( $order ) {
		$m = $this->get_order_metrics( $order );
		return (float) ( $m['margin'] ?? 0 );
	}

	/**
	 * Cache order profit values in post/order meta.
	 *
	 * @param WC_Order|int $order Order object or ID.
	 * @return void
	 */
	public function refresh_cached_metrics( $order ) {
		$order = is_numeric( $order ) ? wc_get_order( (int) $order ) : $order;
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$m = $this->get_order_metrics( $order );
		$order->update_meta_data( '_wpa_net_profit_cached', (string) $m['net_profit'] );
		$order->update_meta_data( '_wpa_margin_percent_cached', (string) $m['margin'] );
		$order->save_meta_data();
	}
}
