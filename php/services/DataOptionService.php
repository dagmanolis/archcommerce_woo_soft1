<?php

namespace webxl\archcommerce\services;

class DataOptionService
{
    private $data;
    public function __construct()
    {
        $this->reload();
    }
    public function is_insert_orders_active()
    {
        return isset($this->data["insert_orders_active"]) ? $this->data["insert_orders_active"] === true : false;
    }
    public function is_sync_products_active()
    {
        return isset($this->data["sync_products_active"]) ? $this->data["sync_products_active"] === true : false;
    }
    public function set_insert_orders_active(bool $is_active)
    {
        $this->data['insert_orders_active'] = $is_active;
        $this->update();
    }
    public function set_sync_products_active(bool $is_active)
    {
        $this->data['sync_products_active'] = $is_active;
        $this->update();
    }
    public function get_token()
    {
        $this->reload();
        return $this->data['token'];
    }
    public function set_token($token)
    {
        $this->data['token'] = $token;
        $this->update();
    }
    public function clear_token()
    {
        $this->data['token'] = "";
        $this->update();
    }
    public function get_subscription_expiration_date(): ?\DateTime
    {
        $this->reload();
        return empty($this->data['subscription_expiration_date']) ? null : $this->data['subscription_expiration_date'];
    }
    public function set_subscription_expiration_date(\DateTime $date)
    {
        $this->data['subscription_expiration_date'] = $date;
        $this->update();
    }
    private function update()
    {
        update_option("archcommerce_data", $this->data);
        $this->reload();
    }
    private function reload()
    {
        $this->data = get_option("archcommerce_data");
    }
}
