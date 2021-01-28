<?php

namespace webxl\archcommerce\services\abstracts;

use webxl\archcommerce\services\ArchCommerceApiService;
use webxl\archcommerce\services\contracts\IWooCommerceService;
use webxl\archcommerce\services\OrderProcessService;

abstract class WooCommerceServiceBase implements IWooCommerceService
{
    private ArchCommerceApiService $archCommerceApiService;
    private OrderProcessService $orderProcessService;
    public function __construct(
        ArchCommerceApiService $archCommerceApiService,
        OrderProcessService $orderProcessService
    ) {
        $this->archCommerceApiService = $archCommerceApiService;
        $this->orderProcessService = $orderProcessService;
    }

    public function update_products($arch_products)
    {
        $products_updated = 0;

        foreach ($arch_products as $_arch_product) {
            try {
                $arch_product = json_decode($_arch_product);

                if (!$arch_product)
                    throw new \Exception("Cannot json decode soft1 product");

                if (empty($arch_product->sku))
                    throw new \Exception("Product sku is empty");

                $woo_product_id = wc_get_product_id_by_sku($arch_product->sku);
                if ($woo_product_id > 0) {
                    $this->update_product($arch_product, $woo_product_id);
                    $products_updated += 1;
                }
            } catch (\Exception $ex) {
                error_log("Failed to update woo product: " . $ex->getMessage());
            }
        }

        return $products_updated;
    }
    public function on_woocommerce_thankyou($order_id)
    {
        $arch_order = $this->create_arch_order($order_id);
        $response = $this->archCommerceApiService->insert_order($arch_order);
        $response_code = intval(wp_remote_retrieve_response_code($response));
        if ($response_code !== 200)
            error_log("Error inserting order: " . print_r($response, true));
    }
    protected function create_arch_order($order_id)
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
    protected abstract function update_product($arch_product, $woo_product_id);
}
