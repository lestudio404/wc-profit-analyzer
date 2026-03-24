<?php
/**
 * User guide view.
 *
 * @package WPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wpa-wrap">
	<h1><?php echo esc_html__( 'Analyse de rentabilite - Guide utilisateur', 'wc-profit-analyzer' ); ?></h1>

	<div class="wpa-panel">
		<h2><?php echo esc_html__( '1) Configuration initiale', 'wc-profit-analyzer' ); ?></h2>
		<ol>
			<li><?php echo esc_html__( 'Ouvrez WooCommerce > Parametres rentabilite.', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Choisissez le mode de calcul HT ou TTC.', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Configurez la méthode de frais de paiement (manuel, %, fixe + %).', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Activez ou non la prise en compte du coût d’expédition.', 'wc-profit-analyzer' ); ?></li>
		</ol>
	</div>

	<div class="wpa-panel" style="margin-top:16px;">
		<h2><?php echo esc_html__( '2) Définir le coût d’achat des produits', 'wc-profit-analyzer' ); ?></h2>
		<p><?php echo esc_html__( 'Dans chaque fiche produit WooCommerce, renseignez le champ "Cout d\'achat". Pour les produits variables, renseignez le cout par variation si necessaire.', 'wc-profit-analyzer' ); ?></p>
		<p><?php echo esc_html__( 'Si une variation n’a pas de coût défini, le plugin tente un fallback sur le coût du produit parent. Sinon, le coût est considéré à 0.', 'wc-profit-analyzer' ); ?></p>
	</div>

	<div class="wpa-panel" style="margin-top:16px;">
		<h2><?php echo esc_html__( '3) Analyse d’une commande', 'wc-profit-analyzer' ); ?></h2>
		<p><?php echo esc_html__( 'Dans l\'edition d\'une commande WooCommerce, la boite "Analyse de rentabilite" affiche les indicateurs : Chiffre d\'affaires, Cout produit, Profit brut, Profit net, Marge %.', 'wc-profit-analyzer' ); ?></p>
		<p><?php echo esc_html__( 'Vous pouvez saisir des couts reels par commande : Cout expedition, Frais de paiement, Cout additionnel, ainsi qu\'une note interne.', 'wc-profit-analyzer' ); ?></p>
	</div>

	<div class="wpa-panel" style="margin-top:16px;">
		<h2><?php echo esc_html__( '4) Tableau de bord et rapports', 'wc-profit-analyzer' ); ?></h2>
		<ul>
			<li><?php echo esc_html__( 'Analyse de rentabilite : vue synthetique (CA, cout d\'achat total, profit net, marge moyenne).', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Rentabilite commandes : liste detaillee des commandes + export CSV.', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Rentabilite produits : performance produit sur la periode.', 'wc-profit-analyzer' ); ?></li>
		</ul>
		<p><?php echo esc_html__( 'Utilisez les filtres de période pour comparer vos performances (jour, 7 jours, 30 jours, mois, personnalisé).', 'wc-profit-analyzer' ); ?></p>
	</div>

	<div class="wpa-panel" style="margin-top:16px;">
		<h2><?php echo esc_html__( '5) Formules utilisées', 'wc-profit-analyzer' ); ?></h2>
		<ul>
			<li><code><?php echo esc_html__( 'Profit brut = Chiffre d\'affaires - Cout produit', 'wc-profit-analyzer' ); ?></code></li>
			<li><code><?php echo esc_html__( 'Profit net = Chiffre d\'affaires - Cout produit - Cout expedition - Frais de paiement - Cout additionnel', 'wc-profit-analyzer' ); ?></code></li>
			<li><code><?php echo esc_html__( 'Marge % = (Profit net / Chiffre d\'affaires) * 100', 'wc-profit-analyzer' ); ?></code></li>
		</ul>
	</div>

	<div class="wpa-panel" style="margin-top:16px;">
		<h2><?php echo esc_html__( 'Bonnes pratiques', 'wc-profit-analyzer' ); ?></h2>
		<ul>
			<li><?php echo esc_html__( 'Renseigner systématiquement les coûts d’achat pour éviter les marges surévaluées.', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Utiliser les coûts réels de paiement et de transport sur les commandes importantes.', 'wc-profit-analyzer' ); ?></li>
			<li><?php echo esc_html__( 'Exporter régulièrement en CSV pour archivage ou analyse externe.', 'wc-profit-analyzer' ); ?></li>
		</ul>
	</div>
</div>
