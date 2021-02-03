<?php
global $archcommerce_syncOrdersProcessService;
if (isset($_REQUEST["init_orders_sync_process"])) {
    $result = $archcommerce_syncOrdersProcessService->init_sync_process();
    if ($result === false)
        $result_html = sprintf('<p style="color:red;">%s</p>', __("An error occured", "archcommerce"));
    else if ($result === 0)
        $result_html = sprintf('<p style="color:gray;">%s</p>', sprintf(__("Zero orders inserted to Soft1", "archcommerce"), $result));
    else
        $result_html = sprintf('<p>%s</p>', sprintf(__("Inserted %d order(s) to Soft1", "archcommerce"), $result));
} else {
    $result_html = "";
}

?>
<div class="wrap">
    <span class="dashicons dashicons-cart"></span>
    <h1 class="wp-heading-inline"><?php _e("Sync orders", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <form method="post" action="<?php echo admin_url("admin.php?page=" . $_REQUEST["page"]); ?>">
        <input type="hidden" name="init_orders_sync_process" />
        <button class="button-primary"><?php _e("sync orders now", "archcommerce"); ?></button>
    </form>
    <?php echo $result_html; ?>
</div>