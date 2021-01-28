<?php

use webxl\archcommerce\services\BillingHistoryService;

global $archcommerce_requestService;
$billingHistoryService = new BillingHistoryService($archcommerce_requestService);
$response = $billingHistoryService->get_history();
?>

<div class="wrap">
    <span class="dashicons dashicons-money-alt"></span>
    <h1 class="wp-heading-inline"><?php _e("Billing History", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <?php if (isset($response)) : ?>
        <table class="history">
            <thead>
                <th><?php _e("date", "archcommerce"); ?></th>
                <th><?php _e("count", "archcommerce"); ?></th>
                <th><?php _e("amount", "archcommerce"); ?></th>
            </thead>
            <tbody>
                <?php
                $body = json_decode(wp_remote_retrieve_body($response));
                foreach ($body as $item) {
                    echo "<tr>";
                    echo "<td>";
                    echo $item->created_at;
                    echo "</td>";
                    echo "<td>";
                    echo $item->updates_count;
                    echo "</td>";
                    echo '<td style="text-align:right;">';
                    echo number_format((float)$item->amount, 2, ",", ".") . "&euro;";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>