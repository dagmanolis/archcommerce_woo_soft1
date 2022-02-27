<?php

namespace webxl\archcommerce\services;


class OrderProcessService
{
    public function process_order_items($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            $items = $wc_order->get_items();
            $result = array();
            foreach ($items as $item) :
                $product = $item->get_product();
                $result[] = array(
                    'sku' => $product->get_sku(),
                    'qty' => sprintf("%s", $item['qty']),
                    'price' => $product->get_price(),
                    'comments' => ""
                );
            endforeach;
            return $result;
        }
    }
    public function process_order_data($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            $result = array();
            $result['shipping_method'] = $wc_order->get_shipping_method();
            $result['payment_method_title'] = $wc_order->get_payment_method_title();
            $result['payment_method'] = $wc_order->get_payment_method();
            $result['cart_tax'] = $wc_order->get_cart_tax();
            $result['currency'] = $wc_order->get_currency();
            $result['discount_tax'] = $wc_order->get_discount_tax();
            $result['discount_total'] = $wc_order->get_discount_total();
            $result['shipping_tax'] = $wc_order->get_shipping_tax();
            $result['shipping_total'] = $wc_order->get_shipping_total();
            $result['subtotal'] = $wc_order->get_subtotal();
            $result['total'] = $wc_order->get_total();
            $result['total_discount'] = $wc_order->get_total_discount();
            $result['total_tax'] = $wc_order->get_total_tax();
            $result['cod_payment_fee'] = 0;
            return $result;
        }
    }
    public function process_order_status($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            return $wc_order->get_status();
        }
    }
    public function process_customer_note($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            return $wc_order->get_customer_note();
        }
    }
    public function process_customer_info($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            $result = array();
            $result['ip_address'] = $wc_order->get_customer_ip_address();
            $result['user_agent'] = $wc_order->get_customer_user_agent();
            return $result;
        }
    }

    public function process_customer_shipping($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            $result = array();
            $result['first_name'] = $wc_order->get_shipping_first_name();
            $result['last_name'] = $wc_order->get_shipping_last_name();
            $result['company'] = $wc_order->get_shipping_company();
            $result['address_1'] = $wc_order->get_shipping_address_1();
            $result['address_2'] = $wc_order->get_shipping_address_2();
            $result['city'] = $wc_order->get_shipping_city();
            $result['state'] = $wc_order->get_shipping_state();
            $result['postcode'] = $wc_order->get_shipping_postcode();
            $result['country'] = $wc_order->get_shipping_country();
            return $result;
        }
    }
    public function process_customer_billing($wc_order)
    {
        if (function_exists("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__)) {
            return call_user_func("webxl\archcommerce\orderprocess\archcommerce_" . __FUNCTION__, $wc_order);
        } else {
            $result = array();
            $result['first_name'] = $wc_order->get_billing_first_name();
            $result['last_name'] = $wc_order->get_billing_last_name();
            $result['company'] = $wc_order->get_billing_company();
            $result['address_1'] = $wc_order->get_billing_address_1();
            $result['address_2'] = $wc_order->get_billing_address_2();
            $result['city'] = $wc_order->get_billing_city();
            $result['state'] = $wc_order->get_billing_state();
            $result['postcode'] = $wc_order->get_billing_postcode();
            $result['country'] = $wc_order->get_billing_country();
            $result['email'] = $wc_order->get_billing_email();
            $result['phone'] = $wc_order->get_billing_phone();
            return $result;
        }
    }
}
