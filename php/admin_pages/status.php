<?php

use webxl\archcommerce\services\CurrentMonthStatusService;

global $archcommerce_dataOptionService;
global $archcommerce_apiService;
global $archcommerce_requestService;
global $archcommerce_subscriptionService;
$currentMonthStatusService = new CurrentMonthStatusService($archcommerce_requestService);

if (isset($_REQUEST["clear_token"]))
    $archcommerce_dataOptionService->clear_token();

if (isset($_REQUEST["refresh_subscription"]))
    $archcommerce_subscriptionService->refresh();


$check_credentials_response = $archcommerce_apiService->check_credentials();
$check_credentials_response_code = wp_remote_retrieve_response_code($check_credentials_response);

switch ($check_credentials_response_code) {
    case 200:
        $connection_status = '<span style="color:green;">' . __("connected", "archcommerce") . '</span>';
        $credentials_are_valid = true;
        break;
    case 401:
        $connection_status = '<span style="color:orange;">' . __("invalid credentials", "archcommerce") . '</span>';
        $credentials_are_valid = false;
        break;
    case 500:
    default:
        $connection_status = '<span style="color:red;">' . __("disconnected", "archcommerce") . '</span>';
        $credentials_are_valid = false;
        break;
}
if ($credentials_are_valid) {

    $subscription_status = $archcommerce_subscriptionService->get_subscription_status();
    switch ($subscription_status) {
        case "active":
            $subscription_days_left = $archcommerce_subscriptionService->get_subscription_days_left();
            $subscription_status = '<span>' .
                __(
                    sprintf(
                        "your subscription expires in <strong>%s</strong> day(s)",
                        $archcommerce_subscriptionService->get_subscription_days_left()
                    ),
                    "archcommerce"
                )
                . '</span>';
            break;
        case "expired":
            $subscription_status = '<span style="color:red;">' .
                __(
                    sprintf(
                        "subscription expired <strong>%s</strong> day(s) ago",
                        abs($archcommerce_subscriptionService->get_subscription_days_left())
                    ),
                    "archcommerce"
                ) . '</span>';
            break;
        case null:
        default:
            $subscription_status = '<span style="color:gray;">' . __("no active subscription", "archcommerce") . '</span>';
            break;
    }

    $current_month_status = $currentMonthStatusService->get_status();

    $current_month_status_products_count = isset($current_month_status->products_count) ? $current_month_status->products_count : 0;
    $current_month_status_orders_count = isset($current_month_status->orders_count) ? $current_month_status->orders_count : 0;

    $epoch = wp_next_scheduled('archcommerce_init_sync_products_process');
    if ($epoch) {
        $date = new \DateTime();
        $date->setTimestamp($epoch);
        $date->setTimezone(wp_timezone());
        $products_cronjob_starting_time = $date->format("Y-m-d H:i:s");
    } else {
        $products_cronjob_starting_time = __("not scheduled", "archcommerce");
    }

    $epoch = wp_next_scheduled('archcommerce_init_sync_orders_process');
    if ($epoch) {
        $date = new \DateTime();
        $date->setTimestamp($epoch);
        $date->setTimezone(wp_timezone());
        $orders_cronjob_starting_time = $date->format("Y-m-d H:i:s");
    } else {
        $orders_cronjob_starting_time = __("not scheduled", "archcommerce");
    }

    if (!defined("DISABLE_WP_CRON"))
        $cronjob_wp_disable = __("not defined", "archcommerce");
    else if (defined("DISABLE_WP_CRON") && DISABLE_WP_CRON === true)
        $cronjob_wp_disable = __("disabled", "archcommerce");
    else if (defined("DISABLE_WP_CRON") && DISABLE_WP_CRON === false)
        $cronjob_wp_disable = __("enabled", "archcommerce");

    $insertOrdersStatus = '<p>' . __("insert orders status:", "archcommerce") . '&nbsp; <strong style="%s">%s</strong><p>';
    if ($archcommerce_subscriptionService->is_insert_orders_active()) {
        $insertOrdersStatus = sprintf($insertOrdersStatus, "color:black;", __("active", "archcommerce"));
    } else {
        $insertOrdersStatus = sprintf($insertOrdersStatus, "color:gray;", __("inactive", "archcommerce"));
    }

    $soft1CustomizationStatus = '<p>' . __("soft1 customization:", "archcommerce") . '&nbsp; <strong style="%s">%s</strong><p>';
    if ($archcommerce_subscriptionService->is_customization_active()) {
        $soft1CustomizationStatus = sprintf($soft1CustomizationStatus, "color:black;", __("active", "archcommerce"));
    } else {
        $soft1CustomizationStatus = sprintf($soft1CustomizationStatus, "color:gray;", __("inactive", "archcommerce"));
    }
}

?>
<div class="wrap">
    <span class="dashicons dashicons-info"></span>
    <h1 class="wp-heading-inline"><?php _e("ArchCommerce Status", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <div id="connection_status">
        <h3><?php _e("Connection Status", "archcommerce"); ?></h3>
        <p><?php _e("connection to ArchCommerce service:", "archcommerce"); ?>&nbsp;<strong><?php echo $connection_status; ?></strong></p>
        <form method="post" action="<?php echo admin_url("admin.php?page=" . $_REQUEST["page"]); ?>">
            <input type="hidden" name="clear_token" />
            <input type="submit" class="button-primary" value="<?php _e("clear token", "archcommerce"); ?>" />
        </form>
    </div>
    <?php if ($credentials_are_valid) : ?>
        <div id="subscription_status">
            <h3><?php _e("Subscription Status", "archcommerce"); ?></h3>
            <?php echo $subscription_status; ?>
            <?php echo $insertOrdersStatus; ?>
            <?php echo $soft1CustomizationStatus; ?>
            <form method="post" action="<?php echo admin_url("admin.php?page=" . $_REQUEST["page"]); ?>">
                <input type="hidden" name="refresh_subscription" />
                <input type="submit" class="button-primary" value="<?php _e("refresh subscription", "archcommerce"); ?>" />
            </form>
        </div>
        <div id="current_month_status">
            <h3><?php _e("Current Month Status", "archcommerce"); ?></h3>
            <p><?php _e("total product updates:", "archcommerce"); ?>&nbsp;<strong><?php echo number_format((int)$current_month_status_products_count, 0, ',', '.'); ?></strong></p>
            <p><?php _e("total order updates:", "archcommerce"); ?>&nbsp;<strong><?php echo number_format((int)$current_month_status_orders_count, 0, ',', '.'); ?></strong></p>
        </div>
        <div id="sync_process">
            <h3><?php _e("Sync Processes", "archcommerce"); ?></h3>
            <p><?php _e("next products sync:", "archcommerce"); ?>&nbsp;<strong><?php echo $products_cronjob_starting_time; ?></strong></p>
            <p><?php _e("next orders sync:", "archcommerce"); ?>&nbsp;<strong><?php echo $orders_cronjob_starting_time; ?></strong></p>
        </div>
        <div id="cron_job">
            <h3><?php _e("Cron Info", "archcommerce"); ?></h3>
            <p><?php _e("DISABLE_WP_CRON:", "archcommerce"); ?>&nbsp;<strong><?php echo $cronjob_wp_disable; ?></strong></p>
            <p>
            <h4><?php _e("Server custom cron job instructions:", "archcommerce"); ?></h4>
            <ol>
                <li>
                    <?php _e("Add this to your wp-config.php file:", "archcommerce") ?>
                    <pre>define('DISABLE_WP_CRON', true);</pre>
                </li>
                <li>
                    <?php _e("Add one of the following cron jobs:", "archcommerce") ?>
                    <pre>*/1 * * * * wget -q -O - <?php echo site_url(); ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1</pre>
                    <pre>*/1 * * * * curl <?php echo site_url(); ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1</pre>
                </li>
            </ol>
            </p>
        </div>
    <?php endif; ?>
</div>