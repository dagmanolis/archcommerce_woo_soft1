<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\classes\WooProduct;
use webxl\archcommerce\services\abstracts\WooCommerceServiceBase;

class WooCommerceService extends WooCommerceServiceBase
{
    public function __construct(ArchCommerceApiService $archCommerceApiService, ArchOrderBuilderService $archOrderBuilderService)
    {
        parent::__construct($archCommerceApiService, $archOrderBuilderService);
    }
    protected function update_product($arch_product, $woo_product_id)
    {
        $woo_product = new WooProduct($woo_product_id);
        //first call set_regular_price and then set_sale_price in order to set price correctly
        $woo_product->set_regular_price($arch_product->regular_price);
        $woo_product->set_sale_price($arch_product->sale_price);
        $woo_product->set_tags($arch_product->tags);
        $woo_product->set_categories($arch_product->categories);
        //set_stock sets manage_stock = true , while set_stock_status sets manage_stock = false
        $woo_product->set_stock($arch_product->stock);
        $woo_product->set_stock_status($arch_product->stock_status);
        $woo_product->set_catalog_visibility($arch_product->catalog_visibility);
        $woo_product->set_is_featured($arch_product->is_featured);
        $woo_product->save();
    }
}
