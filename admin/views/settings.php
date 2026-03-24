<?php
/**
 * Settings view.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpa-wrap">
	<h1><?php echo esc_html__( 'Analyse de rentabilite - Parametres', 'wc-profit-analyzer' ); ?></h1>

	<form method="post" action="options.php" class="wpa-settings-form">
		<?php settings_fields( 'wpa_settings_group' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Mode de calcul', 'wc-profit-analyzer' ); ?></th>
				<td>
					<select name="wpa_settings[calc_mode]">
						<option value="ht" <?php selected( $settings['calc_mode'], 'ht' ); ?>><?php echo esc_html__( 'HT', 'wc-profit-analyzer' ); ?></option>
						<option value="ttc" <?php selected( $settings['calc_mode'], 'ttc' ); ?>><?php echo esc_html__( 'TTC', 'wc-profit-analyzer' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Devise WooCommerce', 'wc-profit-analyzer' ); ?></th>
				<td><code><?php echo esc_html( get_woocommerce_currency() ); ?></code></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Colonnes profit dans la liste commandes', 'wc-profit-analyzer' ); ?></th>
				<td><label><input type="checkbox" name="wpa_settings[enable_order_columns]" value="yes" <?php checked( $settings['enable_order_columns'], 'yes' ); ?> /> <?php echo esc_html__( 'Activer', 'wc-profit-analyzer' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Méthode frais de paiement', 'wc-profit-analyzer' ); ?></th>
				<td>
					<select name="wpa_settings[payment_fee_method]">
						<option value="manual" <?php selected( $settings['payment_fee_method'], 'manual' ); ?>><?php echo esc_html__( 'Manuel par commande', 'wc-profit-analyzer' ); ?></option>
						<option value="percent_global" <?php selected( $settings['payment_fee_method'], 'percent_global' ); ?>><?php echo esc_html__( 'Pourcentage global', 'wc-profit-analyzer' ); ?></option>
						<option value="fixed_plus_percent" <?php selected( $settings['payment_fee_method'], 'fixed_plus_percent' ); ?>><?php echo esc_html__( 'Fixe + pourcentage', 'wc-profit-analyzer' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Frais paiement fixes', 'wc-profit-analyzer' ); ?></th>
				<td><input type="text" name="wpa_settings[payment_fee_fixed]" value="<?php echo esc_attr( $settings['payment_fee_fixed'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Frais paiement (%)', 'wc-profit-analyzer' ); ?></th>
				<td><input type="text" name="wpa_settings[payment_fee_percent]" value="<?php echo esc_attr( $settings['payment_fee_percent'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Prendre en compte le coût d’expédition', 'wc-profit-analyzer' ); ?></th>
				<td><label><input type="checkbox" name="wpa_settings[enable_shipping_cost]" value="yes" <?php checked( $settings['enable_shipping_cost'], 'yes' ); ?> /> <?php echo esc_html__( 'Activer', 'wc-profit-analyzer' ); ?></label></td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
