=== ArchCommerce ===
Contributors: dagmanolis
Tags: woocommerce, softone, wpml
Requires at least: 5.6
Tested up to: 5.6
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A bridge between WooCommerce and SoftOne ERP.

== Description ==

* Updates woocommerce products using SoftOne ERP as a source, based on a schedule.
* Syncronizes orders in real time, in one direction, from WooCommerce to SoftOne. (Optional)
* Supports WPML.

== Installation ==

* Go to settings and enter email and password, 
* Then enter the sync process interval and starting date time.


== Frequently Asked Questions ==

* How the app decides which products to update?
It matches woocommerce product SKU with any pre-selected field of SoftOne. Thus, all products must have SKU (including product variations), except for the parent of variable products.

== Screenshots ==


== Changelog ==

= 1.0.0 =
2021/01/27
* first stable version

== Upgrade Notice ==

