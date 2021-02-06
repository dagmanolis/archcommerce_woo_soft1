<?php
global $archcommerce_syncOrdersProcessService;
global $archcommerce_subscriptionService;
if (isset($_REQUEST["init_orders_sync_process"])) {
    $result = $archcommerce_syncOrdersProcessService->init_sync_process();
    if ($result === false)
        $result_html = sprintf('<p style="color:red;">%s</p>', __("An error occured", "archcommerce"));
    else if ($result === 0)
        $result_html = sprintf('<p style="color:gray;">%s</p>', sprintf(__("Zero (0) orders inserted to Soft1", "archcommerce"), $result));
    else
        $result_html = sprintf('<p>%s</p>', sprintf(__("Inserted <strong>%s</strong> order(s) to Soft1", "archcommerce"), $result));
} else {
    $result_html = "";
}

if ($archcommerce_subscriptionService->is_insert_orders_active()) {
    $order_statuses = wc_get_order_statuses();
    unset($order_statuses["wc-cancelled"]);
    unset($order_statuses["wc-refunded"]);
    unset($order_statuses["wc-failed"]);

    $order_wp_query_args = array(
        "post_type" => "shop_order",
        'post_status' => array_keys($order_statuses),
        "meta_query" => array(
            array(
                "key" => "_archcommerce_soft1_id",
                "value" => "",
                "compare" => "NOT EXISTS"
            )
        )
    );
    $order_wp_query = new \WP_Query($order_wp_query_args);
    $orders_unsynced = $order_wp_query->found_posts;
    wp_reset_postdata();
}
?>

<div class="wrap">
    <span class="dashicons dashicons-cart"></span>
    <h1 class="wp-heading-inline"><?php _e("Sync orders", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <?php if ($archcommerce_subscriptionService->is_insert_orders_active()) : ?>
        <form method="post" action="<?php echo admin_url("admin.php?page=" . $_REQUEST["page"]); ?>">
            <input type="hidden" name="init_orders_sync_process" />
            <button class="button-primary"><?php _e("sync orders now", "archcommerce"); ?></button>
        </form>
        <p> <?php echo sprintf(__('There are <strong>%s</strong> unsynced order(s)', 'archcommerce'), $orders_unsynced); ?> </p>
        <?php echo $result_html; ?>
    <?php else : ?>
        <p><?php _e("Sync orders is not enabled in your subscription", "archcommerce"); ?></p>
    <?php endif; ?>
</div>