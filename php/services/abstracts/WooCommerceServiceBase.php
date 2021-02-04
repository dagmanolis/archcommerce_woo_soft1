<?php

namespace webxl\archcommerce\services\abstracts;

use webxl\archcommerce\services\ArchCommerceApiService;
use webxl\archcommerce\services\ArchOrderBuilderService;
use webxl\archcommerce\services\contracts\IWooCommerceService;

abstract class WooCommerceServiceBase implements IWooCommerceService
{
    private ArchCommerceApiService $archCommerceApiService;
    private ArchOrderBuilderService $archOrderBuilderService;
    public function __construct(
        ArchCommerceApiService $archCommerceApiService,
        ArchOrderBuilderService $archOrderBuilderService
    ) {
        $this->archCommerceApiService = $archCommerceApiService;
        $this->archOrderBuilderService = $archOrderBuilderService;
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
        try {
            //check if order has been inserted
            if (!empty(get_post_meta($order_id, '_archcommerce_soft1_id', true)))
                return;
            $arch_order = $this->archOrderBuilderService->create_arch_order($order_id);
            $response = $this->archCommerceApiService->insert_order($arch_order);
            $response_code = intval(wp_remote_retrieve_response_code($response));
            if ($response_code === 200) {
                $body = json_decode(wp_remote_retrieve_body($response));
                if ($body->success) {
                    $data = $body->data;
                    add_post_meta($order_id, '_archcommerce_soft1_id', $data->soft1_order_id, true);
                } else {
                    throw new \Exception(print_r($response, true));
                }
            } else {
                throw new \Exception(print_r($response, true));
            }
        } catch (\Throwable $t) {
            error_log("Error inserting order with id: " . $order_id . ". Error:"  . print_r($t, true));
        }
    }

    protected abstract function update_product($arch_product, $woo_product_id);
}
