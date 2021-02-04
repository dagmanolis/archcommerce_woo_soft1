<?php

namespace webxl\archcommerce;

use webxl\archcommerce\services\WpAdminPagesService;
use webxl\archcommerce\services\WpSettingsBuilderService;
use webxl\archcommerce\services\ProductsSyncProcessService;
use webxl\archcommerce\services\AjaxFunctionsService;
use webxl\archcommerce\services\contracts\IWooCommerceService;
use webxl\archcommerce\services\DataOptionService;
use webxl\archcommerce\services\OrdersSyncProcessService;
use webxl\archcommerce\services\OrdersWpCronSchedulerService;
use webxl\archcommerce\services\PluginUpdaterService;
use webxl\archcommerce\services\SettingsOptionService;
use webxl\archcommerce\services\SubscriptionService;
use webxl\archcommerce\services\ProductsSyncTablesService;
use webxl\archcommerce\services\ProductsWpCronSchedulerService;

class ArchCommerce
{
    private WpAdminPagesService $wpAdminPagesService;
    private WpSettingsBuilderService $WpSettingsBuilderService;
    private ProductsSyncProcessService $productsSyncProcessService;
    private OrdersSyncProcessService $ordersSyncProcessService;
    private AjaxFunctionsService $ajaxFunctionsService;
    private ProductsSyncTablesService $productsSyncTablesService;
    private ProductsWpCronSchedulerService $productsWpCronSchedulerService;
    private OrdersWpCronSchedulerService $ordersWpCronSchedulerService;
    private PluginUpdaterService $pluginUpdaterService;
    private IWooCommerceService $wooCommerceService;
    private SettingsOptionService $settingsOptionService;
    private SubscriptionService $subscriptionService;
    private DataOptionService $dataOptionService;
    public function __construct(
        WpAdminPagesService $wpAdminPagesService,
        WpSettingsBuilderService $WpSettingsBuilderService,
        ProductsSyncProcessService $productsSyncProcessService,
        OrdersSyncProcessService $ordersSyncProcessService,
        AjaxFunctionsService $ajaxFunctionsService,
        ProductsSyncTablesService $productsSyncTablesService,
        ProductsWpCronSchedulerService $productsWpCronSchedulerService,
        OrdersWpCronSchedulerService $ordersWpCronSchedulerService,
        PluginUpdaterService $pluginUpdaterService,
        IWooCommerceService $wooCommerceService,
        SettingsOptionService $settingsOptionService,
        SubscriptionService $subscriptionService,
        DataOptionService $dataOptionService
    ) {
        $this->wpAdminPagesService = $wpAdminPagesService;
        $this->WpSettingsBuilderService = $WpSettingsBuilderService;
        $this->productsSyncProcessService = $productsSyncProcessService;
        $this->ordersSyncProcessService = $ordersSyncProcessService;
        $this->ajaxFunctionsService = $ajaxFunctionsService;
        $this->productsSyncTablesService = $productsSyncTablesService;
        $this->productsWpCronSchedulerService = $productsWpCronSchedulerService;
        $this->ordersWpCronSchedulerService = $ordersWpCronSchedulerService;
        $this->pluginUpdaterService = $pluginUpdaterService;
        $this->wooCommerceService = $wooCommerceService;
        $this->settingsOptionService = $settingsOptionService;
        $this->subscriptionService = $subscriptionService;
        $this->dataOptionService = $dataOptionService;
    }
    public function init()
    {
        add_action('plugins_loaded', array($this->pluginUpdaterService, 'check_for_updates'));
        add_action('http_api_curl', array($this, 'http_api_curl'), 100, 1);
        add_filter('http_request_timeout', array($this, 'custom_http_request_timeout'), 9999);
        add_filter('http_request_args', array($this, 'custom_http_request_args'), 9999, 1);
        add_filter('cron_schedules', array($this, 'register_sync_products_custom_interval'), 100);
        add_filter('cron_schedules', array($this, 'register_sync_orders_custom_interval'), 110);
        add_action('updated_option', function ($option_name, $old_value, $new_value) {
            switch ($option_name) {
                case "archcommerce_settings":
                    $this->on_settings_option_updated($old_value, $new_value);
                    break;
                case "archcommerce_sync_products_settings":
                    $this->on_sync_products_settings_option_updated($old_value, $new_value);
                    break;
                case "archcommerce_sync_orders_settings":
                    $this->on_sync_orders_settings_option_updated($old_value, $new_value);
                    break;
            }
        }, 10, 3);
        //add_action('update_option_archcommerce_settings', array($this->WpSettingsBuilderService, 'on_update_option_archcommerce_settings'));

        if (defined('DOING_AJAX') && DOING_AJAX) {
            if (is_admin()) {
                add_action('wp_ajax_archcommerce_get_active_products_sync_process', array($this->ajaxFunctionsService, 'get_active_products_sync_process'));
                add_action('wp_ajax_archcommerce_init_products_sync_process', array($this->ajaxFunctionsService, 'init_products_sync_process'));
                add_action('wp_ajax_archcommerce_cancel_products_sync_process', array($this->ajaxFunctionsService, 'cancel_products_sync_process'));
            }
        } else {
            if ($this->woocommerce_exists_and_active()) {
                register_activation_hook(ARCHCOMMERCE_PLUGIN_FULL_FILE, array($this, 'on_plugin_activated'));
                register_deactivation_hook(ARCHCOMMERCE_PLUGIN_FULL_FILE, array($this, 'on_plugin_deactivated'));
                add_action("archcommerce_init_sync_products_process", array($this->productsSyncProcessService, "init_sync_process"));
                add_action("archcommerce_process_sync_products_process", array($this->productsSyncProcessService, "process_sync_process"));
                add_action("archcommerce_init_sync_orders_process", array($this->ordersSyncProcessService, "init_sync_process"));
                register_uninstall_hook(__FILE__, 'delete_table');
                if (
                    $this->subscriptionService->is_insert_orders_active() &&
                    $this->settingsOptionService->has_sync_orders_realtime_enabled()
                )
                    add_action('woocommerce_thankyou', array($this->wooCommerceService, 'on_woocommerce_thankyou'));
                if (is_admin()) {
                    add_action('admin_init', array($this, 'admin_init'), 10);
                    add_action('admin_init', array($this->WpSettingsBuilderService, 'register_settings'), 20);
                    add_action('admin_init', array($this->WpSettingsBuilderService, 'create_fields'), 40);
                    add_action('admin_menu', array($this->wpAdminPagesService, 'create_admin_menu'));
                    add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
                    add_action('wp_ajax_archcommerce_get_active_products_sync_process', array($this->ajaxFunctionsService, 'get_active_products_sync_process'));
                    add_action('wp_ajax_archcommerce_init_products_sync_process', array($this->ajaxFunctionsService, 'init_products_sync_process'));
                    add_action('wp_ajax_archcommerce_cancel_products_sync_process', array($this->ajaxFunctionsService, 'cancel_products_sync_process'));
                }
            } else {
                add_action('admin_notices', function () {
                    $this->admin_notice(
                        "error",
                        __("ArchCommerce plugin cannot function because WooCommerce is not installed or it is deactivated. Please install and activate WooCommerce.", "archcommerce")
                    );
                });
            }
        }
    }
    public function admin_init()
    {
        if ($this->subscriptionService->get_subscription_status() === "expired")
            add_action('admin_notices', function () {
                $this->admin_notice(
                    "warning",
                    __("Your subscription has expired", "archcommerce")
                );
            });

        if ($this->WpSettingsBuilderService->email_password_are_empty())
            add_action('admin_notices', function () {
                $this->admin_notice(
                    "warning",
                    __("You must enter ArchCommerce email and password", "archcommerce")
                );
            });

        if ($this->productsWpCronSchedulerService->is_init_sync_process_unscheduled())
            add_action('admin_notices', function () {
                $this->admin_notice(
                    "warning",
                    __("ArchCommerce: You need to setup cron job for initializing prodcuts sync process", "archcommerce")
                );
            });

        if ($this->ordersWpCronSchedulerService->is_init_sync_process_unscheduled())
            add_action('admin_notices', function () {
                $this->admin_notice(
                    "warning",
                    __("ArchCommerce: You need to setup cron job for initializing orders sync process", "archcommerce")
                );
            });
    }
    public function http_api_curl($handle)
    {
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    }
    public function custom_http_request_args($r)
    {
        $r['timeout'] = 30;
        return $r;
    }
    public function custom_http_request_timeout($timeout_value)
    {
        return 30;
    }
    public function on_settings_option_updated($old_option, $new_option)
    {
        //refresh subscription and token if new credentials entered
        if (
            !empty($new_option["password"])  &&
            !empty($new_option["email"]) &&
            ($new_option["email"] !== $old_option["email"] ||
                $new_option["password"] !== $old_option["password"])
        ) {
            $this->dataOptionService->clear_token();
            $this->subscriptionService->refresh();
        }
    }
    public function on_sync_products_settings_option_updated($old_option, $new_option)
    {

        //schedule init sync process cron job
        if (
            $new_option["cronjob_starting_time"] instanceof \DateTime &&
            $new_option["cronjob_starting_time"] !== $old_option["cronjob_starting_time"]
        )
            $this->productsWpCronSchedulerService->schedule_init_sync_process($new_option["cronjob_starting_time"]);
    }
    public function on_sync_orders_settings_option_updated($old_option, $new_option)
    {
        //schedule init sync process cron job
        if (
            $new_option["cronjob_starting_time"] instanceof \DateTime &&
            $new_option["cronjob_starting_time"] !== $old_option["cronjob_starting_time"]
        )
            $this->ordersWpCronSchedulerService->schedule_init_sync_process($new_option["cronjob_starting_time"]);
    }
    public function on_plugin_activated()
    {
        if ($this->WpSettingsBuilderService->options_exists()) {
            $this->productsWpCronSchedulerService->schedule_init_sync_process();
            $this->ordersWpCronSchedulerService->schedule_init_sync_process();
        } else {
            $this->WpSettingsBuilderService->create_options();
        }

        $this->productsSyncTablesService->create_table();
    }
    public function on_plugin_deactivated()
    {
        $this->productsWpCronSchedulerService->unschedule_process_sync_process();
        $this->productsWpCronSchedulerService->unschedule_init_sync_process();
        $this->ordersWpCronSchedulerService->unschedule_init_sync_process();
    }
    public function register_sync_products_custom_interval($schedules)
    {
        return $this->productsWpCronSchedulerService->register_custom_interval($schedules);
    }
    public function register_sync_orders_custom_interval($schedules)
    {
        return $this->ordersWpCronSchedulerService->register_custom_interval($schedules);
    }
    public function admin_enqueue_scripts($hook)
    {
        if (strpos($hook, "archcommerce_page") !== false) {
            wp_enqueue_style("archcommerce_style", ARCHCOMMERCE_PLUGIN_URL . 'css/style.css', array(), '1.0.0');
        }
        if (strpos($hook, "sync_products") !== false) {
            wp_register_script("archcommerce_sync_products_process_script", ARCHCOMMERCE_PLUGIN_URL . 'js/sync_products.js', array('jquery'), '1.0.0');

            wp_localize_script('archcommerce_sync_products_process_script', 'wpobj', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("quote_wp_nonce"),
                'areyousure_message' => __("Are you sure you want to start a sync process?", "archcommerce"),
                'areyousure_cancel_message' => __("Are you sure you want to cancel this sync process?", "archcommerce")
            ));

            wp_enqueue_script("archcommerce_sync_products_process_script");
        } else if (strpos($hook, "update_history") !== false) {
            wp_register_script(
                "archcommerce_sync_orders_process_script",
                ARCHCOMMERCE_PLUGIN_URL . 'js/update_history.js',
                array('jquery', 'archcommerce_datatables_script'),
                '1.0.0'
            );
            wp_register_script(
                "archcommerce_datatables_script",
                "//cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js",
                ["jquery"],
                "1.10.23"
            );
            wp_enqueue_style(
                "archcommerce_datatables_style",
                "//cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css",
                [],
                "1.10.23"
            );
            wp_enqueue_script("archcommerce_datatables_script");
            wp_enqueue_script("archcommerce_sync_orders_process_script");
        }
    }
    private function woocommerce_exists_and_active()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
    private function admin_notice($class, $message)
    {
        echo "<div class=\"notice notice-$class is-dismissible\"><p>$message</p> </div>";
    }
    public function delete_table()
    {
        $this->productsSyncTablesService->delete_table();
    }
}
