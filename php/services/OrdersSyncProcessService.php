<?php

namespace webxl\archcommerce\services;


class OrdersSyncProcessService
{

    private ArchCommerceApiService $archCommerceApiService;
    private ArchOrderBuilderService $archOrderBuilderService;
    public function __construct(
        ArchCommerceApiService $archCommerceApiService,
        ArchOrderBuilderService $archOrderBuilderService
    )
    {
        $this->archCommerceApiService = $archCommerceApiService;
        $this->archOrderBuilderService = $archOrderBuilderService;
    }
    public function init_sync_process()
    {
        try
        {
            $orders_inserted = 0;
            $order_statuses = wc_get_order_statuses();
            unset($order_statuses["wc-cancelled"]);
            unset($order_statuses["wc-refunded"]);
            unset($order_statuses["wc-failed"]);
            unset($order_statuses["wc-completed"]);
            unset($order_statuses["wc-pending"]);

            $order_wp_query_args = array(
                "post_type" => "shop_order",
                'posts_per_page' => -1,
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
            if ($order_wp_query->have_posts())
            {
                while ($order_wp_query->have_posts())
                {
                    $order_wp_query->the_post();
                    $order_id = get_the_ID();
                    $attempts = intval(get_post_meta($order_id, '_archcommerce_insert_order_attempts', true));
                    $attempts += 1;
                    if ($attempts > 5)
                    {
                        add_post_meta($order_id, '_archcommerce_soft1_id', 0, true);
                    }
                    else
                    {
                        update_post_meta($order_id, '_archcommerce_insert_order_attempts', $attempts, true);
                        $arch_order = $this->archOrderBuilderService->create_arch_order($order_id);
                        $response = $this->archCommerceApiService->insert_order($arch_order);
                        $response_code = intval(wp_remote_retrieve_response_code($response));
                        $body = json_decode(wp_remote_retrieve_body($response));
                        $success = $response_code === 200 && $body && $body->success && $body->data && $body->data->soft1_order_id;

                        if ($success)
                        {
                            add_post_meta($order_id, '_archcommerce_soft1_id', $body->data->soft1_order_id, true);
                            $orders_inserted += 1;
                        }
                        else
                        {
                            $error = $body && $body->error ? $body->error : "n/a";
                            throw new \Exception($error);
                        }
                    }
                }
            }
        }
        catch (\Throwable $t)
        {
            error_log("Faile to insert order with id: " . $order_id . "\r\nError: "  . $t->getMessage());
        }
        finally
        {
            wp_reset_postdata();
        }

        return $orders_inserted;
    }
}
