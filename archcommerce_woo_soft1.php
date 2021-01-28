<?php

/**
 * Plugin Name: ArchCommerce
 * Plugin URI: https://webxl.gr/archcommerce
 * Description: A bridge between WooCommerce and SoftOne ERP.
 * Version: 1.0.0
 * Author: webxl.gr
 * Author URI: https://webxl.gr
 * Text Domain: archcommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 3.0.0
 * WC tested up to: 4.9.2
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
use webxl\archcommerce\services\PluginUpdaterService;
use webxl\archcommerce\services\SettingsOptionService;
use webxl\archcommerce\services\SubscriptionService;
use webxl\archcommerce\services\SyncProcessOptionService;
use webxl\archcommerce\services\SyncProcessService;
use webxl\archcommerce\services\SyncTablesService;
use webxl\archcommerce\services\WooCommerceService;
use webxl\archcommerce\services\WooCommerceWpmlService;
use webxl\archcommerce\services\WpCronSchedulerService;
use webxl\archcommerce\services\WpmlService;

if (!defined('ARCHCOMMERCE_ADMIN_OPTIONS_PAGE_SLUG'))
    define('ARCHCOMMERCE_ADMIN_OPTIONS_PAGE_SLUG', 'archcommerce-settings');

if (!defined('ARCHCOMMERCE_PLUGIN_FULL_FILE'))
    define('ARCHCOMMERCE_PLUGIN_FULL_FILE', __FILE__);

if (!defined('ARCHCOMMERCE_PLUGIN_DIR'))
    define('ARCHCOMMERCE_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('ARCHCOMMERCE_PLUGIN_URL'))
    define('ARCHCOMMERCE_PLUGIN_URL',  plugin_dir_url(__FILE__));

if (!defined('ARCHCOMMERCE_BITBUCKET_OAUTH_PUBLIC_KEY'))
    define('ARCHCOMMERCE_BITBUCKET_OAUTH_PUBLIC_KEY', 'XmHweQFUSPbqaZMtMg');

if (!defined('ARCHCOMMERCE_BITBUCKET_OAUTH_SECRET_KEY'))
    define('ARCHCOMMERCE_BITBUCKET_OAUTH_SECRET_KEY', 'VDrPxQbcHZyaYrPkcAT4BvLJzvtrS9d4');

if (!defined('ARCHCOMMERCE_SECRET_KEY'))
    define('ARCHCOMMERCE_SECRET_KEY', ARCHCOMMERCE_BITBUCKET_OAUTH_PUBLIC_KEY);

if (!defined('ARCHCOMMERCE_SECRET_IV'))
    define('ARCHCOMMERCE_SECRET_IV', ARCHCOMMERCE_BITBUCKET_OAUTH_SECRET_KEY);

if (!defined('ARCHCOMMERCE_SERVICE_URL'))
    define('ARCHCOMMERCE_SERVICE_URL', 'https://archcommerce.webxl.gr');

require_once(plugin_dir_path(__FILE__) . 'php/services/contracts/IWooCommerceService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/abstracts/WooCommerceServiceBase.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpAdminPagesService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/OrderProcessService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpSettingsBuilderService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SettingsOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/DataOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SyncProcessOptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpCronSchedulerService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/EncryptService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ArchCommerceRequestService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SubscriptionService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/CurrentMonthStatusService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/UpdatesHistoryService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/BillingHistoryService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SyncProcessService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/ArchCommerceApiService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/SyncTablesService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WooCommerceService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WooCommerceWpmlService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/WpmlService.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/AjaxFunctionsService.php');
require_once(plugin_dir_path(__FILE__) . 'php/classes/WooProduct.php');
require_once(plugin_dir_path(__FILE__) . 'php/ArchCommerce.php');
require_once(plugin_dir_path(__FILE__) . 'libs/plugin-update-checker/plugin-update-checker.php');
require_once(plugin_dir_path(__FILE__) . 'php/services/PluginUpdaterService.php');

$archcommerce_settingsOptionService = new SettingsOptionService();
$archcommerce_dataOptionService = new DataOptionService();
$archcommerce_syncProcessOptionService = new SyncProcessOptionService();
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
$archcommerce_wpmlService = new WpmlService();
if ($archcommerce_wpmlService->woocom_multilingual_exists_and_active())
    $archcommerce_wooCommerceService = new WooCommerceWpmlService(
        $archcommerce_apiService,
        $archcommerce_orderProcessService,
        $archcommerce_wpmlService
    );
else
    $archcommerce_wooCommerceService = new WooCommerceService(
        $archcommerce_apiService,
        $archcommerce_orderProcessService
    );

$archcommerce_wpCronSchedulerService = new WpCronSchedulerService($archcommerce_settingsOptionService);




$archcommerce_subscriptionService = new SubscriptionService($archcommerce_requestService, $archcommerce_dataOptionService);
$archcommerce_settingsBuilderService = new WpSettingsBuilderService(
    $archcommerce_encryptService,
    $archcommerce_wpCronSchedulerService,
    $archcommerce_subscriptionService,
    $archcommerce_dataOptionService
);
$archcommerce_syncTablesService = new SyncTablesService();

$archcommerce_syncProcessService = new SyncProcessService(
    $archcommerce_apiService,
    $archcommerce_syncTablesService,
    $archcommerce_wooCommerceService,
    $archcommerce_syncProcessOptionService,
    $archcommerce_settingsOptionService,
    $archcommerce_wpCronSchedulerService,
    $archcommerce_subscriptionService
);

$archcommerce_ajaxFunctionsService = new AjaxFunctionsService(
    $archcommerce_wpCronSchedulerService,
    $archcommerce_syncTablesService,
    $archcommerce_syncProcessOptionService
);

$archcommerce_adminPagesService = new WpAdminPagesService();
$archcommerce_pluginUpdaterService = new PluginUpdaterService();
$archcommerce_arch = new ArchCommerce(
    $archcommerce_adminPagesService,
    $archcommerce_settingsBuilderService,
    $archcommerce_syncProcessService,
    $archcommerce_ajaxFunctionsService,
    $archcommerce_syncTablesService,
    $archcommerce_wpCronSchedulerService,
    $archcommerce_pluginUpdaterService,
    $archcommerce_wooCommerceService,
    $archcommerce_settingsOptionService,
    $archcommerce_subscriptionService,
    $archcommerce_dataOptionService
);

$archcommerce_arch->init();
