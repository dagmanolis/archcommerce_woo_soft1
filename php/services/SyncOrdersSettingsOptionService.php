<?php

namespace webxl\archcommerce\services;

class SyncOrdersSettingsOptionService
{
    private $settings;
    public function __construct()
    {
        $this->reload();
    }
    public function get_cronjob_starting_time()
    {
        $this->reload();
        return $this->settings["cronjob_starting_time"];
    }
    public function get_cronjob_custom_interval()
    {
        $this->reload();
        return $this->settings["cronjob_interval"];
    }
    private function update()
    {
        update_option("archcommerce_sync_orders_settings", $this->settings);
        $this->reload();
    }
    public function reload()
    {
        $this->settings = get_option("archcommerce_sync_orders_settings");
    }
}
