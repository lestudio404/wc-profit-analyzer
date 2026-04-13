=== WooCommerce Profit Analyzer ===
Contributors: st404
Tags: woocommerce, profit, margin, analytics, reporting
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Analyze WooCommerce profitability by order and product with configurable costs and margin tracking.

== Description ==

WooCommerce Profit Analyzer helps you understand real profitability, not just revenue.

MVP features:

* Purchase cost fields on simple and variation products.
* Order profitability metrics:
  * Revenue (HT or TTC setting)
  * Product purchase costs
  * Shipping cost
  * Payment fee
  * Extra manual cost
  * Gross profit
  * Net profit
  * Margin %
* Profit metabox on WooCommerce order edit screen.
* Net profit and margin columns in WooCommerce orders list.
* Admin dashboard with period filters and profitability cards.
* Product profitability report by period.
* Settings page for fee and calculation behavior.
* CSV export for order profits.

== Installation ==

1. Upload the `wc-profit-analyzer` folder to `/wp-content/plugins/`.
2. Activate plugin through the WordPress admin.
3. Ensure WooCommerce is active.
4. Go to `WooCommerce > Profit Analyzer`.
5. Configure options in `WooCommerce > Profit Settings`.

== Frequently Asked Questions ==

= Does it support HPOS? =
Yes, the plugin declares compatibility with WooCommerce custom order tables (HPOS).

= What if a product has no purchase cost set? =
The plugin treats missing purchase cost as 0.

== Changelog ==

= 1.1.9 =
* Calculs : le chiffre d'affaires inclut maintenant l'expedition payee par le client ; le cout d'expedition reste un cout reel saisi manuellement.

= 1.1.8 =
* Correctif commandes : metabox unique (plus de doublon) et sauvegarde des champs (HPOS inclus).

= 1.1.7 =
* Mises a jour GitHub plus reactives : requete releases/latest non bloquee 15 min sur tableau de bord, Extensions et Mises a jour ; TTL de secours 5 min ; User-Agent type WordPress ; auth Bearer via ST404_WPA_GITHUB_TOKEN / WPA_GITHUB_TOKEN ou filtre wpa_github_api_token (evite le plafond API sans jeton sur certains hebergeurs).

= 1.1.6 =
* Publication de test pour verifier la detection de mise a jour (1.1.5 -> 1.1.6).

= 1.1.5 =
* Correctif mises a jour GitHub : filtre site_transient_update_plugins (comme Plugin Update Checker) pour afficher la mise a jour sans attendre le cycle du cache WordPress (~12 h).

= 1.1.4 =
* Publication de verification : la mise a jour doit apparaitre dans Extensions > Mises a jour (cache GitHub rafraichi cote site apres ~15 min ou en rouvrant la page).

= 1.1.3 =
* Bump de version pour publication.

= 1.1.2 =
* Mecanisme d'update GitHub aligne sur le comportement des autres plugins ST404: fallback auth interne + check force en admin si cache absent.

= 1.1.1 =
* Version de test pour verifier le mecanisme de mise a jour GitHub en conditions reelles.

= 1.1.0 =
* Stabilisation complete post-correctifs: anti-collisions de symboles PHP, apostrophes echappees, interface FR harmonisee et reactivation des mises a jour GitHub.

= 1.0.10 =
* Reactivation du module de mise a jour GitHub avec les protections anti-collision et anti-redeclaration conservees.

= 1.0.9 =
* Correctif fatal PHP : apostrophes echappees dans plusieurs chaines FR (class-order-profit, class-admin, class-product-profit, guide).

= 1.0.8 =
* Correctif anti-fatal additionnel : helpers encapsules par function_exists pour eviter les redeclarations si plusieurs copies du plugin sont chargees.

= 1.0.7 =
* Correctif anti-collision : renommage global des classes/fonctions PHP avec prefixe unique ST404_WPA / st404_wpa.

= 1.0.6 =
* Correctif d'activation : chargement du module de mise a jour GitHub desactive temporairement pour eliminer la source de fatal.

= 1.0.5 =
* Durcissement anti-fatal : garde class_exists autour de l'updater GitHub et bootstrap defensif.

= 1.0.4 =
* Correctif activation : renommage unique de la classe d'updater GitHub pour eviter les conflits de classes entre plugins.

= 1.0.3 =
* Standardisation de l'interface admin en francais (menus, libelles, messages).

= 1.0.2 =
* Added a dedicated User Guide admin page under Profit Analyzer.

= 1.0.1 =
* Menu admin reorganized under a single parent Profit Analyzer entry.

= 1.0.0 =
* Initial MVP release.
