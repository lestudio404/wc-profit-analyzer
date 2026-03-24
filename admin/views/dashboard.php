<?php
/**
 * Dashboard view.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpa-wrap">
	<h1><?php echo esc_html__( 'WooCommerce Analyse de rentabilite', 'wc-profit-analyzer' ); ?></h1>

	<form method="get" class="wpa-filter-bar">
		<input type="hidden" name="page" value="wc-profit-analyzer" />
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

	<div class="wpa-cards">
		<div class="wpa-card"><h3><?php echo esc_html__( 'Chiffre d’affaires', 'wc-profit-analyzer' ); ?></h3><p><?php echo wp_kses_post( st404_wpa_price( $data['totals']['revenue'] ) ); ?></p></div>
		<div class="wpa-card"><h3><?php echo esc_html__( 'Coût d’achat', 'wc-profit-analyzer' ); ?></h3><p><?php echo wp_kses_post( st404_wpa_price( $data['totals']['product_cost'] ) ); ?></p></div>
		<div class="wpa-card"><h3><?php echo esc_html__( 'Profit net', 'wc-profit-analyzer' ); ?></h3><p><?php echo wp_kses_post( st404_wpa_price( $data['totals']['net_profit'] ) ); ?></p></div>
		<div class="wpa-card"><h3><?php echo esc_html__( 'Marge moyenne', 'wc-profit-analyzer' ); ?></h3><p><?php echo esc_html( number_format_i18n( $data['totals']['avg_margin'], 2 ) ); ?>%</p></div>
		<div class="wpa-card"><h3><?php echo esc_html__( 'Commandes analysées', 'wc-profit-analyzer' ); ?></h3><p><?php echo esc_html( (string) $data['totals']['count'] ); ?></p></div>
	</div>

	<div class="wpa-grid-2">
		<div class="wpa-panel">
			<h2><?php echo esc_html__( 'Commandes les plus rentables', 'wc-profit-analyzer' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th><?php echo esc_html__( 'Commande', 'wc-profit-analyzer' ); ?></th><th><?php echo esc_html__( 'Profit net', 'wc-profit-analyzer' ); ?></th><th><?php echo esc_html__( 'Marge', 'wc-profit-analyzer' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $data['top'] as $row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $row['order_id'] ) . '&action=edit' ) ); ?>">#<?php echo esc_html( (string) $row['order_id'] ); ?></a></td>
						<td><?php echo wp_kses_post( st404_wpa_price( $row['net_profit'] ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( $row['margin'], 2 ) ); ?>%</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="wpa-panel">
			<h2><?php echo esc_html__( 'Commandes les moins rentables', 'wc-profit-analyzer' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th><?php echo esc_html__( 'Commande', 'wc-profit-analyzer' ); ?></th><th><?php echo esc_html__( 'Profit net', 'wc-profit-analyzer' ); ?></th><th><?php echo esc_html__( 'Marge', 'wc-profit-analyzer' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $data['worst'] as $row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $row['order_id'] ) . '&action=edit' ) ); ?>">#<?php echo esc_html( (string) $row['order_id'] ); ?></a></td>
						<td><?php echo wp_kses_post( st404_wpa_price( $row['net_profit'] ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( $row['margin'], 2 ) ); ?>%</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
