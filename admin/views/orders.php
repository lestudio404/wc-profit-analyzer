<?php
/**
 * Orders view.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpa-wrap">
	<h1><?php echo esc_html__( 'Analyse de rentabilite - Commandes', 'wc-profit-analyzer' ); ?></h1>

	<form method="get" class="wpa-filter-bar">
		<input type="hidden" name="page" value="wc-profit-analyzer-orders" />
		<select name="range" class="wpa-range-select">
			<option value="today" <?php selected( $range['range'], 'today' ); ?>><?php echo esc_html__( "Aujourd'hui", 'wc-profit-analyzer' ); ?></option>
			<option value="7_days" <?php selected( $range['range'], '7_days' ); ?>><?php echo esc_html__( '7 jours', 'wc-profit-analyzer' ); ?></option>
			<option value="30_days" <?php selected( $range['range'], '30_days' ); ?>><?php echo esc_html__( '30 jours', 'wc-profit-analyzer' ); ?></option>
			<option value="month" <?php selected( $range['range'], 'month' ); ?>><?php echo esc_html__( 'Ce mois', 'wc-profit-analyzer' ); ?></option>
			<option value="custom" <?php selected( $range['range'], 'custom' ); ?>><?php echo esc_html__( 'Personnalisée', 'wc-profit-analyzer' ); ?></option>
		</select>
		<input type="date" name="from" value="<?php echo esc_attr( $range['from'] ); ?>" class="wpa-custom-date" />
		<input type="date" name="to" value="<?php echo esc_attr( $range['to'] ); ?>" class="wpa-custom-date" />
		<button class="button button-primary"><?php echo esc_html__( 'Filtrer', 'wc-profit-analyzer' ); ?></button>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:12px 0;">
		<?php wp_nonce_field( 'wpa_export_orders_csv' ); ?>
		<input type="hidden" name="action" value="wpa_export_orders_csv" />
		<input type="hidden" name="range" value="<?php echo esc_attr( $range['range'] ); ?>" />
		<input type="hidden" name="from" value="<?php echo esc_attr( $range['from'] ); ?>" />
		<input type="hidden" name="to" value="<?php echo esc_attr( $range['to'] ); ?>" />
		<button type="submit" class="button"><?php echo esc_html__( 'Exporter CSV', 'wc-profit-analyzer' ); ?></button>
	</form>

	<table class="widefat striped">
		<thead>
		<tr>
			<th><?php echo esc_html__( 'Commande', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Date', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'CA', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Coût achat', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Expédition', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Paiement', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Extra', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Profit net', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Marge %', 'wc-profit-analyzer' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $data['orders'] as $row ) : ?>
			<tr>
				<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $row['order_id'] ) . '&action=edit' ) ); ?>">#<?php echo esc_html( (string) $row['order_id'] ); ?></a></td>
				<td><?php echo esc_html( $row['date'] ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['revenue'] ) ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['product_cost'] ) ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['shipping'] ) ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['payment'] ) ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['extra'] ) ); ?></td>
				<td><?php echo wp_kses_post( st404_wpa_price( $row['net_profit'] ) ); ?></td>
				<td><?php echo esc_html( number_format_i18n( $row['margin'], 2 ) ); ?>%</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
