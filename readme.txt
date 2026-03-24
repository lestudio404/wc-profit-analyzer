=== WooCommerce Profit Analyzer ===
Contributors: st404
Tags: woocommerce, profit, margin, analytics, reporting
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.4
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
