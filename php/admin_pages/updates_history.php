<?php

use webxl\archcommerce\services\UpdatesHistoryService;

global $archcommerce_requestService;
$updatesHistoryService = new UpdatesHistoryService($archcommerce_requestService);
$response = $updatesHistoryService->get_history();

?>

<div class="wrap">
    <span class="dashicons dashicons-text-page"></span>
    <h1 class="wp-heading-inline"><?php _e("Updates History", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <?php if (isset($response)) : ?>
        <table class="history">
            <thead>
                <th><?php _e("date", "archcommerce"); ?></th>
                <th><?php _e("type", "archcommerce"); ?></th>
                <th><?php _e("count", "archcommerce"); ?></th>
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
                    echo $item->type;
                    echo "</td>";
                    echo "<td>";
                    echo $item->count;
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>