<?php

namespace webxl\archcommerce\services\contracts;

interface IWooCommerceService
{
    public function update_products($arch_products);
    public function on_woocommerce_thankyou($order_id);
}
