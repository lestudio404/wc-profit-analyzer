<?php
/**
 * Products view.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpa-wrap">
	<h1><?php echo esc_html__( 'Analyse de rentabilite - Produits', 'wc-profit-analyzer' ); ?></h1>

	<form method="get" class="wpa-filter-bar">
		<input type="hidden" name="page" value="wc-profit-analyzer-products" />
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

	<table class="widefat striped">
		<thead>
		<tr>
			<th><?php echo esc_html__( 'Produit', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'SKU', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Coût achat', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Qté vendue', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'CA', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Profit estimé', 'wc-profit-analyzer' ); ?></th>
			<th><?php echo esc_html__( 'Marge %', 'wc-profit-analyzer' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if ( empty( $rows ) ) : ?>
			<tr><td colspan="7"><?php echo esc_html__( 'Aucune donnée sur cette période.', 'wc-profit-analyzer' ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $row['product_id'] ) . '&action=edit' ) ); ?>"><?php echo esc_html( $row['name'] ); ?></a></td>
					<td><?php echo esc_html( $row['sku'] ?: '-' ); ?></td>
					<td><?php echo wp_kses_post( wpa_price( $row['purchase_cost'] ) ); ?></td>
					<td><?php echo esc_html( (string) $row['qty'] ); ?></td>
					<td><?php echo wp_kses_post( wpa_price( $row['revenue'] ) ); ?></td>
					<td><?php echo wp_kses_post( wpa_price( $row['profit'] ) ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $row['margin'], 2 ) ); ?>%</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
