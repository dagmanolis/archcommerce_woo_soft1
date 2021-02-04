<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\classes\WooProduct;
use webxl\archcommerce\services\abstracts\WooCommerceServiceBase;

class WooCommerceWpmlService extends WooCommerceServiceBase
{
    private $wpmlService;
    private $lang_codes;
    public function __construct(ArchCommerceApiService $archCommerceApiService, ArchOrderBuilderService $archOrderBuilderService, WpmlService $wpmlService)
    {
        parent::__construct($archCommerceApiService, $archOrderBuilderService);
        $this->wpmlService = $wpmlService;
        $this->lang_codes = $this->wpmlService->get_active_lang_codes();
    }
    protected function update_product($arch_product, $woo_product_id)
    {
        foreach ($this->lang_codes as $lang_code) :
            $wpml_object_id = $this->wpmlService->get_wpml_object_id($woo_product_id, 'product', $lang_code);
            if ($wpml_object_id) {
                $woo_product = new WooProduct($wpml_object_id);
                //first call set_regular_price and then set_sale_price in order to set price correctly
                $woo_product->set_regular_price($arch_product->regular_price);
                $woo_product->set_sale_price($arch_product->sale_price);
                //set_stock sets manage_stock = true , while set_stock_status sets manage_stock = false
                $woo_product->set_stock($arch_product->stock);
                $woo_product->set_stock_status($arch_product->stock_status);
                $woo_product->set_catalog_visibility($arch_product->catalog_visibility);
                $woo_product->set_is_featured($arch_product->is_featured);
                $woo_product->save();
            }
        endforeach;
    }
}
