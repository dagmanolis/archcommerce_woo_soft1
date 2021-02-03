<?php

namespace webxl\archcommerce\services;


class OrdersSyncProcessService
{

    public function __construct()
    {
    }
    public function init_sync_process()
    {
        try {
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
            if ($order_wp_query->have_posts()) {
                while ($order_wp_query->have_posts()) {
                    $order_wp_query->the_post();
                    $order_id = get_the_ID();
                }
            }
            $orders_inserted = $order_wp_query->found_posts;
            wp_reset_postdata();
            return $orders_inserted;
        } catch (\Throwable $t) {
            return false;
        }
        return false;
    }
}
