<?php

/**
 * Plugin Name: ArchCommerce
 * Plugin URI: https://webxl.gr/archcommerce
 * Description: A bridge between WooCommerce and SoftOne ERP.
 * Version: 2.6.0
 * Author: webxl.gr
 * Author URI: https://webxl.gr
 * Text Domain: archcommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 3.0.0
 * WC tested up to: 6.2.1
 **/

namespace webxl\archcommerce;

use webxl\archcommerce\services\AjaxFunctionsService;
use webxl\archcommerce\services\ArchCommerceApiService;
use webxl\archcommerce\services\ArchCommerceRequestService;
use webxl\archcommerce\services\DataOptionService;
use webxl\archcommerce\services\WpAdminPagesService;
use webxl\archcommerce\services\WpSettingsBuilderService;
use webxl\archcommerce\services\EncryptService;
use webxl\archcommerce\services\OrderProcessService;
use webxl\archcommerce\services\OrdersSyncProcessService;
use webxl\archcommerce\services\OrdersWpCronSchedulerService;
use webxl\archcommerce\services\PluginUpdaterService;
use webxl\archcommerce\services\SettingsOptionService;
use webxl\archcommerce\services\SyncProductsSettingsOptionService;
use webxl\archcommerce\services\SubscriptionService;
use webxl\archcommerce\services\ProductsSyncProcessOptionService;
use webxl\archcommerce\services\ProductsSyncProcessService;
use webxl\archcommerce\services\ProductsSyncTablesService;
use webxl\archcommerce\services\WooCommerceService;
use webxl\archcommerce\services\WooCommerceWpmlService;
use webxl\archcommerce\services\ProductsWpCronSchedulerService;
use webxl\archcommerce\services\SyncOrdersSettingsOptionService;
use webxl\archcommerce\services\WpmlService;
use webxl\archcommerce\services\ArchOrderBuilderService;

if (!defined('ARCHCOMMERCE_ADMIN_OPTIONS_PAGE_SLUG'))
    define('ARCHCOMMERCE_ADMIN_OPTIONS_PAGE_SLUG', 'archcommerce-settings');

if (!defined('ARCHCOMMERCE_PLUGIN_FULL_FILE'))
    define('ARCHCOMMERCE_PLUGIN_FULL_FILE', __FILE__);

if (!defined('ARCHCOMMERCE_PLUGIN_DIR'))
    define('ARCHCOMMERCE_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('ARCHCOMMERCE_PLUGIN_URL'))
    define('ARCHCOMMERCE_PLUGIN_URL',  plugin_dir_url(__FILE__));

if (!defined('ARCHCOMMERCE_GITHUB_PERSONAL_ACCESS_TOKEN'))
    define('ARCHCOMMERCE_GITHUB_PERSONAL_ACCESS_TOKEN', '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');

if (!defined('ARCHCOMMERCE_SECRET_KEY'))
    define('ARCHCOMMERCE_SECRET_KEY', "XmHweQFUSPbqaZMtMg");

if (!defined('ARCHCOMMERCE_SECRET_IV'))
    define('ARCHCOMMERCE_SECRET_IV', "VDrPxQbcHZyaYrPkcAT4BvLJzvtrS9d4");

if (!defined('ARCHCOMMERCE_SERVICE_URL'))
    define('ARCHCOMMERCE_SERVICE_URL', 'https://archcommerce.local');

if (!defined('ARCHCOMMERCE_API_VERSION'))
    define('ARCHCOMMERCE_API_VERSION', "1.1");

if (!defined('ARCHCOMMERCE_PLUGIN_VERSION'))
    define('ARCHCOMMERCE_PLUGIN_VERSION', "2.6.0");

require_once(plugin_dir_path(__FILE__) . 'php/services/contracts/IWooCommerceService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/abstracts/WooCommerceServiceBase.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpAdminPagesService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/OrderProcessService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpSettingsBuilderService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SettingsOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SyncProductsSettingsOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SyncOrdersSettingsOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/DataOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ArchOrderBuilderService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ProductsSyncProcessOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ProductsWpCronSchedulerService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/OrdersWpCronSchedulerService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/EncryptService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ArchCommerceRequestService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SubscriptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/CurrentMonthStatusService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ProductsSyncProcessService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/OrdersSyncProcessService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ArchCommerceApiService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ProductsSyncTablesService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WooCommerceService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WooCommerceWpmlService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpmlService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/AjaxFunctionsService.php');
require_once(plugin_dir_path(__FILE__) . 'php/classes/WooProduct.php');
require_once(plugin_dir_path(__FILE__) . 'php/ArchCommerce.php');
require_once(plugin_dir_path(__FILE__) . 'libs/plugin-update-checker/plugin-update-checker.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/PluginUpdaterService.php');

$archcommerce_settingsOptionService = new SettingsOptionService();
$archcommerce_syncProductsSettingsOptionService = new SyncProductsSettingsOptionService();
$archcommerce_syncOrdersSettingsOptionService = new SyncOrdersSettingsOptionService();
$archcommerce_dataOptionService = new DataOptionService();
$archcommerce_productsSyncProcessOptionService = new ProductsSyncProcessOptionService();
$archcommerce_encryptService = new EncryptService();

$archcommerce_requestService = new ArchCommerceRequestService(
    $archcommerce_encryptService,
    $archcommerce_dataOptionService,
    $archcommerce_settingsOptionService
);


$archcommerce_apiService = new ArchCommerceApiService(
    $archcommerce_encryptService,
    $archcommerce_requestService,
    $archcommerce_settingsOptionService
);

$archcommerce_orderProcessService = new OrderProcessService();
$archcommerce_archOrderBuilderService = new ArchOrderBuilderService($archcommerce_orderProcessService);

$archcommerce_wpmlService = new WpmlService();
if ($archcommerce_wpmlService->woocom_multilingual_exists_and_active())
    $archcommerce_wooCommerceService = new WooCommerceWpmlService(
        $archcommerce_apiService,
        $archcommerce_archOrderBuilderService,
        $archcommerce_wpmlService
    );
else
    $archcommerce_wooCommerceService = new WooCommerceService(
        $archcommerce_apiService,
        $archcommerce_archOrderBuilderService
    );



$archcommerce_subscriptionService = new SubscriptionService(
    $archcommerce_requestService,
    $archcommerce_dataOptionService
);
$archcommerce_settingsBuilderService = new WpSettingsBuilderService($archcommerce_encryptService);
$archcommerce_productsSyncTablesService = new ProductsSyncTablesService();

$archcommerce_productsWpCronSchedulerService = new ProductsWpCronSchedulerService($archcommerce_syncProductsSettingsOptionService);

$archcommerce_syncProductsProcessService = new ProductsSyncProcessService(
    $archcommerce_apiService,
    $archcommerce_productsSyncTablesService,
    $archcommerce_wooCommerceService,
    $archcommerce_productsSyncProcessOptionService,
    $archcommerce_syncProductsSettingsOptionService,
    $archcommerce_productsWpCronSchedulerService,
    $archcommerce_subscriptionService
);

$archcommerce_syncOrdersProcessService = new OrdersSyncProcessService($archcommerce_apiService, $archcommerce_archOrderBuilderService);

$archcommerce_ajaxFunctionsService = new AjaxFunctionsService(
    $archcommerce_productsWpCronSchedulerService,
    $archcommerce_productsSyncTablesService,
    $archcommerce_productsSyncProcessOptionService
);

$archcommerce_ordersWpCronSchedulerService = new OrdersWpCronSchedulerService($archcommerce_syncOrdersSettingsOptionService);
$archcommerce_adminPagesService = new WpAdminPagesService();
$archcommerce_pluginUpdaterService = new PluginUpdaterService();
$archcommerce_arch = new ArchCommerce(
    $archcommerce_adminPagesService,
    $archcommerce_settingsBuilderService,
    $archcommerce_syncProductsProcessService,
    $archcommerce_syncOrdersProcessService,
    $archcommerce_ajaxFunctionsService,
    $archcommerce_productsSyncTablesService,
    $archcommerce_productsWpCronSchedulerService,
    $archcommerce_ordersWpCronSchedulerService,
    $archcommerce_pluginUpdaterService,
    $archcommerce_wooCommerceService,
    $archcommerce_settingsOptionService,
    $archcommerce_subscriptionService,
    $archcommerce_dataOptionService,
    $archcommerce_syncProductsSettingsOptionService,
    $archcommerce_syncOrdersSettingsOptionService
);

$archcommerce_arch->init();