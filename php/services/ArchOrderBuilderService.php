<?php

namespace webxl\archcommerce\services;

class ArchOrderBuilderService
{
    private OrderProcessService $orderProcessService;
    public function __construct(
        OrderProcessService $orderProcessService
    ) {
        $this->orderProcessService = $orderProcessService;
    }

    public function create_arch_order($order_id)
    {
        $wc_order = wc_get_order($order_id);

        $arch_order = array();
        $arch_order['status'] = $this->orderProcessService->process_order_status($wc_order);
        $arch_order['data'] = $this->orderProcessService->process_order_data($wc_order);
        $arch_order['items'] = $this->orderProcessService->process_order_items($wc_order);

        $arch_customer = array();
        $arch_customer['info'] = $this->orderProcessService->process_customer_info($wc_order);
        $arch_customer['note'] = $this->orderProcessService->process_customer_note($wc_order);
        $arch_customer["billing"] = $this->orderProcessService->process_customer_billing($wc_order);
        $arch_customer["shipping"] = $this->orderProcessService->process_customer_shipping($wc_order);

        $result = array("order" => $arch_order, "customer" => $arch_customer);
        return $result;
    }
}
