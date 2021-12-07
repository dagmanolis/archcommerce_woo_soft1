=== ArchCommerce ===
Contributors: dagmanolis
Tags: woocommerce, softone, wpml
Requires at least: 5.6
Tested up to: 5.6
Stable tag: 2.4.2
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

= 2.4.2 =
* bugfix: options not updating after plugin update

= 2.4.1 =
2021/12/06
* bugfix: plugin not updating 

= 2.4.0 =
2021/12/06
* feature: sync products active
* feature: api v1.1

= 2.3.0 =
2021/12/05
* transitional version (migrated from bitbucket to github)

= 2.2.1 =
2021/03/15
* change: new api url

= 2.1.1 =
2021/03/04
* bugfix: (new option !== old_option) causing rescheduling of sync process
* change: start cron after 10sec if starting date is older than now

= 2.1.0 =
2021/03/03
* feature: sslverify false if wp debug enabled
* bugfix: wp query orders limited posts returned
* bugfix: removed default cases in product update

= 2.0.0 =
2021/02/06
* feature: new pricing model
* feature: enable/disable sync processes
* feature: added soft1 customization support
* feature: ui imporvments
* feature: complete sync orders 
* bugfix: many bugfixes

= 1.2.0 =
2021/01/29
* feature: increase curl timeout

= 1.0.1 =
2021/01/28
* bugfix: wrong repositroy name

= 1.0.0 =
2021/01/27
* first stable version

== Upgrade Notice ==

